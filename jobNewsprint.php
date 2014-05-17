<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;
if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
    switch ($action)
    {
        case "Create Butts":
        save_butts();
        break;
        
        case "Save Job":
        save_job('insert');
        break;
        
        case "Update Job":
        save_job('update');
        break;
        
        case "add":
        load_paper('add');
        break;
        
        case "edit":
        load_paper('edit');
        break;
        
        case "delete":
        load_paper('delete');
        break;
        
        case "list":
        load_paper('list');
        break;
        
        default:
        load_paper('list');
        break;
        
    } 
  
function load_paper($action)
{
    //later on, we'll need to pull in a list of active jobs from the cms for today's print date
    $menu=$_GET['menu'];
    if ($action=='edit' || $action=='add')
    {
      if ($action=='add')
      {
        $button="Save Job";
        $jobdate=date("Y-m-d");
      } else {
        $button="Update Job";
        $jobid=$_GET['jobid'];
        $sql="SELECT * FROM jobs WHERE id=$jobid";
        $dbJob=dbselectsingle($sql);
        $job=$dbJob['data'];
        $jobname=stripslashes($job['job_name']);
        $jobdate=stripslashes($job['job_date']);
        $rolltender=stripslashes($job['roll_tender']);
        $rollsql="SELECT A.roll_id, A.reel, B.common_name,B.butt_roll, B.roll_tag FROM job_rolls A, rolls B WHERE A.job_id=$jobid AND A.roll_id=B.id";
        $dbRolls=dbselectmulti($rollsql);
      }
       print "<form action=\"$_SERVER[PHP_SELF]\" method=post>\n";
       print "<div class='label'>Job Name</div>\n";
       print "<div class='input'>\n";
       print input_text('jobname',$jobname,30);
       print "</div>\n";
       print "<div class='clear'></div>\n";
       print "<div class='label'>Job Date</div>\n";
       print "<div class='input'>\n";
       print "<div><script>DateInput('jobdate', true, 'YYYY-MM-DD','$jobdate')</script></div>";
       print "</div>\n";
       print "<div class='clear'></div>\n";
       print "<div class='label'>Roll Tender Name</div>\n";
       print "<div class='input'>\n";
       print input_text('rolltender',$rolltender,30);
       print "</div>\n";
       print "<div class='clear'></div>\n";
       
       //now the rolls
       if($dbRolls['numrows']>0)
        {
            foreach($dbRolls['data'] as $roll)
            {
                print "Roll tag: ".input_text('roll_'.$roll['id'],$roll['roll_tag'],20,true);
                print " Reel: ".input_text('reed_'.$roll['id'],$roll['reel'],5);
                print " <input type=hidden name='rollid_$roll[id]' id='rollid_$roll[id]' value='$roll[id]' /> ";
                print " <input type=checkbox name='delete_$roll[id]' id='delete_$roll[id]' /> Check to delete";
                print " <input type=checkbox name='butt_$roll[id]' id='butt_$roll[id]' /> Check to convert to butt roll";
                print " <span id='msg_$roll[id]'></span>";
                print "<br>\n";    
            }
        }
        print "<div id='rolls'>\n";
        print "Roll tag: ".input_text('newroll_1','',20,false,'','','checkRollTag(1);');
        print " Reel: ".input_text('newreel_1','',5);
        print " <input type=hidden name='rollid_1' id='rollid_1' value='' /> ";
        print " <input type=checkbox name='delete_1' id='delete_1' /> Check to delete";
        print " <input type=checkbox name='butt_1' id='butt_1' /> Check to convert to butt roll";
        print " <span id='msg_$roll[id]'></span>";
        print "<br>\n";
        print "</div>\n";
        print "<input type=\"hidden\" name=\"lastroll\" id=\"lastroll\" value=\"1\" />\n";
        
        print "<input type=button name='addjobroll' id='addjobroll' value='Add roll' onClick='addJobRoll();'/>\n";        
       print "<input type='hidden' name='menu' value='$menu' />\n";
       print "<input type='hidden' name='jobid' value='$jobid' />\n";
       print "<div class='label'></div>\n";
       print "<div class='input'>\n";
       print "<input type='submit' name='submit' id='submit' value='$button' />\n";
       print "</div>\n";
       print "<div class='clear'></div>\n";
       print "</form>\n";
    } elseif ($action=='complete') {
       $jobid=$_GET['jobid'];
       $menu=$_GET['menu'];
       $sql="UPDATE jobs SET status=2 WHERE id=$jobid";
       $dbUpdate=dbexecutequery($sql);
       if ($dbUpdate['error']!="")
       {
            print "An error occurred: $dbUpdate[error]<br>The sql was $sql<br>";
       } else {
            redirect("?menu=$menu");
       } 
    } else {
        global $siteID;
    //list all open jobs
        $sql="SELECT * FROM jobs WHERE site_id=$siteID AND status=1";
        $dbJobs=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new job</a>","Job Name,Job Date",4);
        if ($dbJobs['numrows']>0)
        {
            foreach($dbJobs['data'] as $job)
            {
                $jobid=$job['id'];
                $jobname=$job['job_name'];
                $date=date("m/d/Y",strtotime($job['job_date']));
                print "<tr><td>$jobname</td><td>$date</td>";
                print "<td><a href='?action=edit&jobid=$jobid'>Enter Rolls</a></td>";
                print "<td><a href='?action=complete&jobid=$jobid'>Complete</a></td>";
                
            
            }
        }
        tableEnd($dbJobs);
    }
}



function save_job($action)
{
    global $siteID;
    $menu=$_POST['menu'];
    $jobid=$_POST['jobid'];
    $jobname=addslashes($_POST['jobname']);
    $jobdate=addslashes($_POST['jobdate']);
    $rolltender=addslashes($_POST['rolltender']);
    
    if ($action=="insert")
    {
        $jobsql="INSERT INTO jobs (job_name, job_date, roll_tender, status, site_id) VALUES ('$jobname', '$jobdate', '$rolltender', 1, $siteID)";
        $dbInsert=dbinsertquery($jobsql);
        if ($dbInsert['error']=="")
        {
            $jobid=$dbInsert['numrows'];
        } else {
            $error=$dbInsert['error'];
        } 
    } else {
        $jobsql="UPDATE jobs SET job_name='$jobname', job_date='$jobdate', roll_tender='$rolltender' WHERE id=$jobid";
        $dbUpdate=dbexecutequery($jobsql);
        $error=$dbUpdate['error'];
    }
    
    //now, go through rolls to gather roll tags
    $buttids=array();
    $deleteids="";
    $rolls="";
    foreach ($_POST as $key=>$value)
    {
        //check for "delete_rollid"
        if (strpos($key,"lete_")>0)
        {
            //means we found a delete item
            $did=str_replace("delete_","",$key);
            $deleteids.="$did,";
        }
        
        //check for "butt_rollid"
        if (strpos($key,"utt_")>0)
        {
            //means we found a delete item
            $bid=str_replace("butt_","",$key);
            $buttids[]=$bid;
        }
    
        //now look for new rolls
        if (strpos($key,"wroll_")>0)
        {
            //have a new roll
            $id=str_replace("newroll_","",$key);
            $rolltag=$_POST['newroll_'.$id];
            $reel=$_POST['newreel_'.$id];
            $rollid=$_POST['rollid_'.$id];
            if ($rolltag!="")
            {
                $rolls.="('$jobid','$rollid','$reel'),";
            }
        }
    
    }
    $deleteids=substr($deleteids,0,strlen($deleteids)-1);    
    $rolls=substr($rolls,0,strlen($rolls)-1);
    $error="";
    
    
    //if no error on the job, process the deletes
    if ($deleteids!="")
    {
        $deletesql="DELETE FROM job_rolls WHERE job_id=$jobid AND roll_id IN ($deleteids)";
        $dbDelete=dbexecutequery($deletesql);
        $error.=$dbDelete['error'];
    }
    //now process the new rolls
    if ($rolls!="")
    {
        $insertsql="INSERT INTO job_rolls (job_id, roll_id, reel) VALUES $rolls";
        $dbInsert=dbinsertquery($insertsql);
        $error.=$dbInsert['error'];
    }
    
    //now we're all done with updating standard rolls, lets check to see if we have an error, if not, then we'll process butts
    if ($error=="")
    {
        if (count($buttids)>0)
        {
            build_butts($buttids);
        } else {
            //no butts, move on
            redirect("?action=list");
        }
    } else {
        print "An error occurred: $error<br>jobsql is $jobsql<br>deletesql=$deletesql<br>insertsql is $insertsql<br>";
    }
}

function build_butts($rolls)
{
    //ok, we have an array of roll ids, need to look up tag information and roll info
    print "<form action=\"$_SERVER[PHP_SELF]\" method=post>\n";
    print "<table class='grid'>\n";
    print "<tr><th>Original Tag #</th><th>Common Name</th><th>New Tag #</th><th>Remaining size</th></tr>\n";
    foreach($rolls as $key=>$value)
    {    
        $sql="SELECT * FROM rolls WHERE id=$value";
        $dbRoll=dbselectsingle($sql);
        $roll=$dbRoll['data'];
        $rollid=$roll['id'];
        $rolltag=$roll['roll_tag'];
        $rollname=$roll['common_name'];
        print "<tr><td><input type='text' name='rolltag_$rollid' value='$rolltag' readonly size=10/></td>";
        print "<td>$rollname</td>";
        print "<td><input type='text' name='newtag_$rollid' value='' size=10 /></td>";
        print "<td><input type='text' name='newsize_$rollid' value='' size=3 onKeyPress='return isNumberKey(event);' /> in.</td>";
        print "</tr>\n";
    }   
    print "</table>\n";
    print "<input type='submit' name='submit' value='Create Butts' />\n";
    print "</form>\n";
}

function save_butts()
{
    $butts="";
    foreach ($_POST as $key=>$value)
    {
        if (strpos($key,"olltag")>0)
        {
            $rollid=str_replace("rolltag_","",$key);
            $originaltag=$_POST['rolltag_'.$rollid];
            $newtag=$_POST['newtag_'.$rollid];
            $newsize=$_POST['newsize_'.$rollid];
        
            //now look up information about the original roll to duplicate to the new one
            $sql="SELECT * FROM rolls WHERE id=$rollid";
            $dbRoll=dbselectsingle($sql);
            $rollinfo=$dbRoll['data'];
            
            //here is where we will attempt a calculation of the new butt roll weight
            $newweight=calc_weight($newsize,$rollinfo);
            $butts.="('$rollinfo[order_id]','$rollinfo[order_item_id]','$rollinfo[common_name]','$rollinfo[roll_width]','$rollinfo[paper_weight]', '$rollinfo[paper_brightness]','$rollinfo[receive_datetime]','$rollinfo[status]','$newtag',1,'$newweight','$originaltag'),";
        }
    }
    $butts=substr($butts,0,strlen($butts)-1);
    if ($butts!="")
    {
        $sql="INSERT INTO rolls (order_id, order_item_id, common_name, roll_width, paper_weight, paper_brightness, receive_datetime, status, roll_tag, butt_roll, roll_weight, parent_tag) VALUES $butts";
        $dbInsert=dbinsertquery($sql);
        if ($dbInsert['error']!="")
        {
            print "An error occurred: $dbInsert[error]<br>sql was $sql<br>";
        } else {
            redirect("?menu=loadpaper");
        }
    } else {
        redirect("?menu=loadpaper");
    }

}

  ?>
