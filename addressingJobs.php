<?php
  //this script is to handle bindery jobs
  include("includes/mainmenu.php");
  
 
if($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
 
switch($action)
{
    case "add":
        jobs('add');
    break;
    
    case "edit":
        jobs('edit');
    break;
    
    case "delete":
        jobs('delete');
    break;
    
    case "Save Job":
        save_job('insert');
    break;
    
    case "Update Job":
        save_job('update');
    break;
    
    default:
        jobs('list');
    break;
} 


function jobs($action)
{
    global $siteID,$pubs, $mailingClasses;
    
    $id=intval($_GET['id']);
    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Save Job';
            $pubid=0;
            $runid=0;
            $draw=0;
            $start=date("Y-m-d H:i");
            $finish=date("Y-m-d H:i",strtotime("+8 hours"));
            $pubdate=date("Y-m-d",strtotime("+2 days"));
            $duedate=date("Y-m-d",strtotime("+1 day"));
            $duePostoffice=date("Y-m-d",strtotime("+3 days"));
            $inhomedate=date("Y-m-d",strtotime("+5 days"));
            $class=0;
            $mailReports='';
        } else {
            $button='Update Job';
            $sql="SELECT * FROM addressing_jobs WHERE id=$id";
            $dbJob=dbselectsingle($sql);
            $job=$dbJob['data'];
            
            $pubid=$job['pub_id'];
            if ($pubid!=0)
            {
                //means we have an existing pub, need to pull in runs
                $sql="SELECT id, run_name FROM publications_runs WHERE pub_id=$pubid";
                $dbRuns=dbselectmulti($sql);
                if ($dbRuns['numrows']>0)
                {
                    foreach($dbRuns['data'] as $run)
                    {
                        $runs[$run['id']]=$run['run_name'];
                    }
                }
            }
            $runid=$job['run_id'];
            $pubdate=$job['pub_date'];
            $duedate=$job['due_date'];
            $start=$job['schedule_start'];
            $finish=$job['schedule_finish'];
            $draw=$job['draw'];
            $class=$job['mailing_class'];
            $duePostoffice=$job['due_postoffice'];
            $inhomedate=$job['inhome_date'];
            $mailReports=$job['mail_reports'];
            $notes=stripslashes($job['notes']);
            $file=stripslashes($job['original_filename']);
            
        }
        
        print "<form method=post enctype='multipart/form-data'>\n";
            make_select('pub_id',$pubs[$pubid],$pubs,'Publication');
            print "<div class='label'>Run</div>\n";
            print "<div class='input'>";
            print input_select('run_id',$runs[$runid],$runs);
            print "<br />If your run does not exist in the list please enter it:<br> ";
            print "Run Name: <input type='text' id='run_special' name='run_special' size=30> Product Code: <input type='text' id='run_special_productcode' name='run_special_productcode' size=5>\n";
            print "</div>\n";
            print '
            <script type="text/javascript">
            $("#pub_id").selectChain({
                target: $("#run_id"),
                type: "post",
                url: "includesajax_handlers/fetchRuns.php",
                data: { ajax: true }
            });
            </script>
            ';
            print "<div class='clear'></div>\n";
            make_date('pubdate',$pubdate,'Pub date');
            make_number('draw',$draw,'Quantity to produce');
            make_date('duedate',$duedate,'Delivery Due','When is the addressing due to complete?');
            make_date('inhomedate',$inhomedate,'Due in home','When is the product required to be in home?');
            make_date('duePostoffice',$duePostoffice,'Due at P.O.','When is the product due to the Post Office?');
            make_select('class',$mailingClasses[$class],$mailingClasses,'Mailing Class','Mailing class for the product');
            make_checkbox('reports',$mailReports,'Mail Report','Check if we need to generate the mail report');
            make_datetime('start',$start,'Scheduled Start','Scheduled start time');
            make_datetime('finish',$finish,'Scheduled Finish','Scheduled finish time');
            make_file('original','Addressing File','Please specify the address source file.');
            if($file!='')
            {
                print "<div class='label'></div><div class='input'>Filename: $file <input type=checkbox name='removeFile' /><label for='removeFile'> Check to remove this file</label></div><div class='clear'></div>\n";
            }
            make_textarea('notes',$notes,'Notes','Miscellaneous notes &amp; instructions','60','10',false);  
        
            make_hidden('id',$id);
            make_submit('submit',$button);
        print "</div>\n";
         
    } elseif ($action=='delete')
    {
        $sql="DELETE FROM addressing_jobs WHERE id=$binderyid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the addressing job.<br>'.$error,'error');
        } else {
            setUserMessage('The addressing job has been successfully deleted','success');
        }
        redirect("?action=list");
    } else {
        global $pubids;
        $sql="SELECT A.*, B.pub_name, C.run_name FROM addressing_jobs A, publications B, publications_runs C 
        WHERE A.pub_id IN ($pubids) AND A.pub_id=B.id AND A.run_id=C.id ORDER BY A.due_date DESC";
        $dbJobs=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new addressing job</a>","Publication,Job Name,Start Date,Due Date",6);
        if ($dbJobs['numrows']>0)
        {
            foreach($dbJobs['data']as $job)
            {
                $id=$job['id'];
                $pubname=$job['pub_name'];
                $jobname=$job['run_name'];
                $due=date("D, M d",strtotime($job['due_date']));
                $start=date("D, M d",strtotime($job['schedule_start']));
                print "<tr>\n";
                print "<td>$pubname</td>\n";
                print "<td>$jobname</td>\n";
                print "<td>$start</td>\n";
                print "<td>$due</td>\n";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=delete&id=$id'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbJobs);
    }
}

function save_job($action)
{
    global $siteID;
    $id=$_POST['id'];
    $pubid=$_POST['pub_id'];
    $runid=$_POST['run_id'];
    
    $inhomedate=$_POST['inhomedate'];
    $duePostoffice=$_POST['duePostoffice'];
    $class=$_POST['class'];
    if($_POST['reports']){$mailReports=1;}else{$mailReports=0;}
            
    
    if ($runid==0 && $_POST['run_special']!='')
    {
        $runname=addslashes($_POST['run_special']);
        $productcode=addslashes($_POST['run_special_productcode']);
        $sql="INSERT INTO publications_runs (pub_id,run_name, run_productcode) VALUES ('$pubid','$runspecial', '$productcode')";
        $dbInsert=dbinsertquery($sql);
        $runid=$dbInsert['numrows'];
    }
    $pubdate=$_POST['pubdate'];
    $duedate=$_POST['duedate'];
    $start=$_POST['start'];
    $finish=$_POST['finish'];
    $draw=$_POST['draw'];
    $notes=addslashes($_POST['notes']);
    
    if($_POST['removeFile'])
    {
        $sql="SELECT * FROM addressing_jobs WHERE id=$id";
        $dbJob=dbselectsingle($sql);
        $file=$dbJob['data']['label_file'];
        if(unlink("circdata/addressfiles/".$file))
        {
           $sql="UPDATE addressing_jobs SET label_file='', original_filename='' WHERE id=$id";
           $dbUpdate=dbexecutequery($sql);
                  
        } else {
            $error="Unable to remove file";
        }
    
    }
    
    $by=$_SESSION['cmsuser']['userid'];
    $dt=date("Y-m-d H:i");
    if ($action=='insert')
    {
        $sql="INSERT INTO addressing_jobs (pub_id, run_id, pub_date, due_date, draw, schedule_start, schedule_finish, 
        notes, created_by, created_datetime, due_postoffice, inhome_date, mail_reports, mailing_class) VALUES 
        ('$pubid', '$runid', '$pubdate', '$duedate', '$draw', '$start', '$finish', '$notes', '$by', '$dt', 
        '$duePostoffice', '$inhomedate', '$mailReports', '$class')";
        $dbInsert=dbinsertquery($sql);
        $id=$dbInsert['insertid'];
        $error.=$dbInsert['error'];
    } else {
        //update
        $sql="UPDATE addressing_jobs SET due_date='$duedate', pub_date='$pubdate', draw='$draw', schedule_start='$start', schedule_finish='$finish', notes='$notes', 
        created_by='$by', created_datetime='$dt', due_postoffice='$duePostoffice', inhome_date='$inhomedate', 
        mail_reports='$mailReports', mailing_class='$class' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error.=$dbUpdate['error'];
    }
    
    if(isset($_FILES))
    {
        if(isset($_FILES)) { //means we have browsed for a valid file
        // check to make sure files were uploaded
        foreach($_FILES as $file) {
            switch($file['error']) {
                case 0: // file found
                if($file['name'] != NULL && okFileType($file['type'],'image',$file['name']) != false)  {
                    //get the new name of the file
                    //to do that, we need to push it into the database, and return the last record ID
                    if ($id!=0) {
                        $filename=$file['name'];
                        $ofile=$filename;
                        $ext=end(explode(".",$filename));
                        $filename='labelfile_'.$id.'.'.$ext;
                        if(processFile($file,"circdata/addressfiles/",$filename) == true) {
                            $sql="UPDATE addressing_jobs SET label_file='$filename', original_filename='$ofile' WHERE id=$id";
                            $result=dbexecutequery($sql);
                        } else {
                           $error.= 'There was an error processing the addressing file: '.$file['name'];  
                        }
                    } else {
                        $error.= 'There was an error because the main record insertion failed.';
                    }
                }
                break;

                case (1|2):  // upload too large
                $error.= 'file upload is too large for '.$file['name'];
                break;

                case 4:  // no file uploaded
                break;

                case (6|7):  // no temp folder or failed write - server config errors
                $error.= 'internal error - flog the webmaster on '.$file['name'];
                break;
            }
        }
     }
        
        
        
    }
    
    if($_POST['popup']=='true')
    {
        ?>
        <script>
        window.close();
        </script>
        <?php
    } else {
        if ($error!='')
        {
            setUserMessage('There was a problem saving the bindery job.<br>'.$error,'error');
        } else {
            setUserMessage('The bindery job has been successfully saved','success');
        }
        redirect("?action=list");
    }
}

footer(); 
?>
