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
        bindery('add');
    break;
    
    case "edit":
        bindery('edit');
    break;
    
    case "delete":
        bindery('delete');
    break;
    
    case "Save Bindery":
    save_bindery('insert');
    break;
    
    case "Update Bindery":
        save_bindery('update');
    break;
    
    default:
        bindery('list');
    break;
} 


function bindery($action)
{
    global $siteID,$pubs;
    
    $stitchers=array();
    $sql="SELECT * FROM stitchers WHERE site_id=$siteID";
    $db=dbselectmulti($sql);
    if ($db['numrows']>0)
    {
        foreach($db['data'] as $item)
        {
            $stitchers[$item['id']]=$item['stitcher_name'];
        }
    }
    
    $binderyid=intval($_GET['id']);
    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Save Bindery';
            $stitch=0;
            $trim=0;
            $draw=0;
            $pubid=0;
            $runid=0;
            $glossycover=0;
            $glossydraw=0;
            $glossyinside=0;
            $glossyinsidecount=0;
            $pubdate=date("Y-m-d");
            $coverduedate=date("Y-m-d");
            $coveroutputdate=date("Y-m-d");
            $coverprintdate=date("Y-m-d");
            $binderystart=date("Y-m-d");
            $binderystartdate=date("Y-m-d");
            $binderydue=date("Y-m-d");
            $starthour='8';
            $startminute='00';
            $stitcherid=1;
        } else {
            $button='Update Bindery';
            $sql="SELECT * FROM bindery_jobs WHERE id=$binderyid";
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
            $draw=$job['draw'];
            $quarterfold=$job['quarterfold'];
            $stitcherid=$job['stitcher_id'];
            $stitch=$job['stitch'];
            $trim=$job['trim'];
            $glossycover=$job['glossy_cover'];
            $glossydraw=$job['glossy_cover_draw'];
            $glossyinside=$job['glossy_insides'];
            $glossyinsidecount=$job['glossy_insides_count'];
            $coverduedate=$job['cover_date_due'];
            if ($coverduedate==''){$coverduedate=date("Y-m-d");}
            $coverprintdate=$job['cover_date_print'];
            if ($coverprintdate==''){$coverprintdate=date("Y-m-d");}
            $coveroutputdate=$job['cover_date_output'];
            if ($coveroutputdate==''){$coveroutputdate=date("Y-m-d");}
            $binderystart=$job['bindery_startdate'];
            if ($binderystart==''){$binderystart=date("Y-m-d");}
            $oldbinderystartdate=date("Y-m-d",strtotime($binderystart));
            $starthour=date("H",strtotime($binderystart));
            $startminute=date("i",strtotime($binderystart));
            $binderydue=$job['bindery_duedate'];
            if ($binderydue==''){$binderydue=date("Y-m-d");}
            $binderynotes=stripslashes($job['notes_bindery']);
            
        }
        print "<div id='tabs'>\n"; //begins wrapper for tabbed content
        
        print "<ul id='insertInfo'>\n";
        print "<li><a href='#basicInfo'>Basic Information</a></li>\n";   
        print "<li><a href='#glossyInfo'>Glossy Information</a></li>\n";   
        print "</ul>\n";
        
        print "<form method=post>\n";
        
        
        print "<div id='basicInfo'>\n";
        make_select('stitcher_id',$stitchers[$stitcherid],$stitchers,'Stitcher','Which stitcher will this run on');
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
                url: "includes/ajax_handlers/fetchRuns.php",
                data: { ajax: true }
            });
            </script>
            ';
            print "<div class='clear'></div>\n";
            make_date('pubdate',$pubdate,'Pub date');
            make_number('draw',$draw,'Quantity to produce');
            make_checkbox('quarterfold',$quarterfold,'Quarterfold','Check if this job is to be quarterfolded.');
            make_checkbox('trim',$trim,'Trim','Check if this job is trimmed.');
            make_checkbox('stitch',$stitch,'Stitch','Check if this job is stitched.');
            
            //testing timelocks
            $mindate=date("Y-m-d",strtotime("+1 day"))." 12:00";
            $maxdate=date("Y-m-d",strtotime("+1 week"))." 18:00";
            make_datetime('bindery_start',$oldbinderystartdate,'Bindery Start','When should bindery start?',1,$mindate,$maxdate);
            print "<fieldset>\n";
            print "<legend>Run times</legend>\n";
            print "<div id='jobruns'>\n";
            $sql="SELECT * FROM bindery_runs WHERE bindery_id=$binderyid ORDER BY schedule_start";
            $dbRuns=dbselectmulti($sql);
            if($dbRuns['numrows']>0)
            {
                foreach($dbRuns['data'] as $run)
                {
                    print "<div id='run_$run[id]' class='binderyRunTimes'>\n";
                    print "<div id='runinfo_$run[id]'><div style='width:200px;float:left;'><b>Scheduled start:</b> ".date("m/d/Y H:i",strtotime($run['schedule_start']))."</div>";
                    print "<div style='width:200px;float:left;'><b>Scheduled stop:</b> ".date("m/d/Y H:i",strtotime($run['schedule_stop']))."</div>";
                    print "<div style='width:200px;float:left;'><b>Production Goal:</b> ".$run['produced_goal']."</div></div>";
                    print "<input type='button' class='button' onClick='editRun($run[id]);' value='Edit'>";
                    print "<input type='button' class='button delete' onClick='deleteRun($run[id]);' value='Delete'>";
                    print "<div class='clear'></div>\n";
                    print "</div>\n";
                }
            }
            print "</div>\n";
            print "<div class='label'>Add new run</div><div class='input'>\n";
            print "<b>Start Date:</b> ".make_datetime('runstart',date("Y-m-d H:i"),'','');
            print "<b>Stop Date:</b> ".make_datetime('runstop',date("Y-m-d H:i"),'','');
            print "<b>Production Goal:</b> ".make_number('prodgoal',0,'','');
            print "<input type='hidden' id='temprunid' value='0'>";
            print "<input type='button' class='button' onClick='updateRun();' value='Save'>";
            print "</div><div class='clear'></div>\n";
            
            print "</fieldset>\n";
            
            
            make_date('bindery_due',$binderydue,'Bindery Due','When is the bindery due to complete?');
            make_textarea('notes_bindery',$binderynotes,'Bindery Notes','Bindery Instructions','60','10',false);  
        print "</div>\n";
        
        print "<div id='glossyInfo'>\n";
        make_checkbox('glossycover',$glossycover,'Glossy Cover','Check if this job has a glossy cover.');
            make_text('glossydraw',$glossydraw,'Gloss Cover Draw','How many covers (and/or insides) are needed?',10,'',false,'','','','return isNumberKey(event);');
            make_checkbox('glossyinside',$glossyinside,'Glossy Insides','Check if this job has one or more glossy inside sheets.');
            make_text('glossyinsidecount',$glossyinsidecount,'Gloss Inside Pieces','How many glossy inside sheets will there be?',10,'',false,'','','','return isNumberKey(event);');
            make_datetime('coveroutput',$coveroutputdate,'Cover Output','When do we need to output the cover?');
            make_datetime('coverprint',$coverprintdate,'Cover Prints','When will the cover print?');
            make_date('coverdue',$coverduedate,'Cover Due by','When do we need the cover back?');
        print "</div>\n";
        make_hidden('popup',$_GET['popup']);
        make_hidden('binderyid',$binderyid);
        
        make_submit('submit',$button);
        print "</form>\n";
        print "</div>\n";
        ?>
        <script type='text/javascript'>
        $(function() {
            $( '#tabs' ).tabs();
        });
        
        function editRun(runid)
        {
            $.ajax({
               url: 'includes/ajax_handlers/binderJobShifts.php',
               type: "POST",
               data: {action:'edit',jobid:$('#binderyid').val(),runid:runid},
               dataType: 'json',
               success: function(response) {
                   if(response.status=='success')
                   {
                      $('#runstart').val(response.start);
                      $('#runstop').val(response.stop);
                      $('#prodgoal').val(response.goal);
                      $('#temprunid').val(runid);
                   } else {
                      alertMessage("Job edit failed.",'error');
                   }
               }
            });
        }
        
        function deleteRun(runid)
        {
            $.ajax({
               url: 'includes/ajax_handlers/binderJobShifts.php',
               type: "POST",
               data: {action:'delete',jobid:$('#binderyid').val(),runid:runid},
               dataType: 'json',
               success: function(response) {
                   if(response.status=='success')
                   {
                      $('#run_'+runid).remove();
                   } else {
                      alertMessage("Job deletion failed",'error');
                   }
               }
            });
        }
        
        function updateRun()
        {
            var runid=$('#temprunid').val();
            $.ajax({
               url: 'includes/ajax_handlers/binderJobShifts.php',
               type: "POST",
               data: {action:'update',jobid:$('#binderyid').val(),runid:runid,runstart:$('#runstart').val(),runstop:$('#runstop').val(),goal:$('#prodgoal').val()},
               dataType: 'json',
               success: function(response) {
                   if(response.status=='success')
                   {
                      if(response.action=='insert')
                      {
                        runid=response.runid;
                        $('#jobruns').append(response.html);    
                      } else {
                        $('#runinfo_'+runid).html(response.html);  
                      }
                      $('#runstart').val('');
                      $('#runstop').val('');
                      $('#prodgoal').val(0);
                      
                   } else {
                      alertMessage("Job creation failed",'error');
                   }
               }
            });    
            
        }
        </script>
    <?php 
    } elseif ($action=='delete')
    {
        $sql="DELETE FROM bindery_jobs WHERE id=$binderyid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the bindery job.<br>'.$error,'error');
        } else {
            setUserMessage('The bindery job has been successfully deleted','success');
        }
        redirect("?action=list");
    } else {
        global $pubids,$pubs;
        if($_POST)
        {
            $pubid=$_POST['pub_id'];
            $start=$_POST['start'];
            $end=$_POST['end']; 
        } else {
            $pubid=0;
            $start=date("Y-m-d")." 00:00";
            $end=date("Y-m-d",strtotime("+1 month"));
        }
        if($start==''){$start=date("Y-m-d");}
        if($end==''){$end=date("Y-m-d",strtotime("+1 month"));}
        $search="<form method=post>\n";
        $search.="Publication:<br />";
        $search.=input_select('pub_id',$pubs[$pubid],$pubs)."<br />";
        $search.="Start date:<br />";
        $search.=input_date('start',$start)."<br />";
        $search.="End date:<br />";
        $search.=input_date('end',$end)."<br />";
        $search.="<br /><input type='submit' class='submit' name='submit' id='submit' value='Search' />\n";
        $search.="</form>\n";
        
        $start.=" 00:00";
        $end.=" 23:59";
        if($pubid>0){$pubid="AND A.pub_id=$pubid";}else{$pubid='';}
        $sql="SELECT A.*, B.pub_name, C.run_name FROM bindery_jobs A, publications B, publications_runs C 
        WHERE (A.pub_id IN ($pubids) $pubid AND A.pub_id=B.id AND A.run_id=C.id AND A.bindery_startdate>='$start' AND A.bindery_stopdate<='$end') OR (A.pub_id IN ($pubids) $pubid AND A.pub_id=B.id AND A.run_id=C.id AND A.pub_date>='$start' AND A.pub_date<='$end') ORDER BY A.bindery_startdate DESC";
        $dbJobs=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new bindery job</a>","Publication,Job Name,Pub Date,Due Date",6,$search);
        if ($dbJobs['numrows']>0)
        {
            foreach($dbJobs['data']as $job)
            {
                $id=$job['id'];
                $pubname=stripslashes($job['pub_name']);
                $jobname=stripslashes($job['run_name']);
                $pub=date("D, M d",strtotime($job['pub_date']));
                $due=date("Y-m-d",strtotime($job['bindery_duedate']));
                print "<tr>\n";
                print "<td>$pubname</td>\n";
                print "<td>$jobname</td>\n";
                print "<td>$pub</td>\n";
                print "<td>$due</td>\n";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=delete&id=$id'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbJobs);
    }
}

function save_bindery($action)
{
    global $siteID;
    $binderyid=$_POST['binderyid'];
    $pubid=$_POST['pub_id'];
    $runid=$_POST['run_id'];
    
    if ($runid==0 && $_POST['run_special']!='')
    {
        $runname=addslashes($_POST['run_special']);
        $productcode=addslashes($_POST['run_special_productcode']);
        $sql="INSERT INTO publications_runs (pub_id,run_name, run_productcode) VALUES ('$pubid','$runspecial', '$productcode')";
        $dbInsert=dbinsertquery($sql);
        $runid=$dbInsert['numrows'];
    }
    $pubdate=$_POST['pubdate'];
    $draw=$_POST['draw'];
    $stitcherid=$_POST['stitcher_id'];
    if ($_POST['stitch']){$stitch=1;}else{$stitch=0;}
    if ($_POST['trim']){$trim=1;}else{$trim=0;}
    if ($_POST['quarterfold']){$quarterfold=1;}else{$quarterfold=0;}
    if ($_POST['glossycover']){$glossycover=1;}else{$glossycover=0;}
    if ($_POST['glossyinsides']){$glossyinside=1;}else{$glossyinside=0;}
    $glossydraw=$_POST['glossydraw'];
    $glossyinsidecount=$_POST['glossyinsidecount'];
    $coverduedate=$_POST['coverdue'];
    $coverprintdate=$_POST['coverprint'];
    $coveroutputdate=$_POST['coveroutput'];
    $startdatetime=$_POST['bindery_start'];
    $binderydue=$_POST['bindery_due'];
    $binderynotes=addslashes($_POST['notes_bindery']);
    
    $runtime=$_POST['draw']/($GLOBALS['stitchSpeed']/60); //this should give us a number of minutes;
    $runtime=round($runtime,0);
    $runtime+=$GLOBALS['stitchSetup'];
    
    $stopdatetime=date("Y-m-d H:i",strtotime($startdatetime." +$runtime minutes"));
    $by=$_SESSION['cmsuser']['userid'];
    $dt=date("Y-m-d H:i");
   if ($action=='insert')
    {
        $sql="INSERT INTO bindery_jobs (pub_id, run_id, pub_date, draw, stitcher_id, stitch, trim, quarterfold, glossy_cover, 
        glossy_insides, glossy_cover_draw, glossy_insides_count,cover_date_due, cover_date_print, cover_date_output,
        bindery_startdate, bindery_stopdate,bindery_duedate,notes_bindery, site_id, created_by, created_datetime) VALUES 
        ('$pubid', '$runid', '$pubdate', '$draw', '$stitcherid',$stitch,$trim,$quarterfold,$glossycover,$glossyinside,'$glossydraw',
        '$glossyinsidecount', '$coverduedate', '$coverprintdate', '$coveroutputdate', '$startdatetime','$stopdatetime', 
        '$binderydue','$binderynotes', '$siteID', '$by', '$dt')";
        $dbInsert=dbinsertquery($sql);
        $binderyid=$dbInsert['numrows'];
        $error=$dbInsert['error'];
    } else {
        //update
        $sql="UPDATE bindery_jobs SET pub_date='$pubdate', draw='$draw', stitcher_id='$stitcherid', stitch='$stitch', trim='$trim', quarterfold='$quarterfold', glossy_cover='$glossycover', glossy_insides='$glossyinside', glossy_cover_draw='$glossydraw', glossy_insides_count='$glossyinsidecount', cover_date_due='$coverduedate', cover_date_print='$coverprintdate', cover_date_output='$coveroutputdate', bindery_startdate='$startdatetime', bindery_stopdate='$stopdatetime', bindery_duedate='$binderydue', notes_bindery='$binderynotes' WHERE id=$binderyid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
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
