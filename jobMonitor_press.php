<?php
include("includes/mainmenu.php") ;
include("includes/job_helper.php") ;
include("includes/layoutGenerator.php") ;
global $pressDepartmentID;
//build a list of operators
$sql="SELECT A.* FROM users A, user_positions B WHERE A.position_id=B.id AND B.operator=1 AND A.department_id=$pressDepartmentID ORDER BY A.lastname";
$dbOperators=dbselectmulti($sql);
$operators=array();
$operators[0]="Set Operator";
if ($dbOperators['numrows']>0)
{
    foreach ($dbOperators['data'] as $operator)
    {
        $operators[$operator['id']]=$operator['firstname'].' '.$operator['lastname'];
    }   
}


print "<div id='pressMonitor' class='ui-widget ui-widget-content ui-corner-all' style='width:100%;height:100%;'>\n";
$jobData=nav();
$layoutid=$jobData['job']['layout_id'];
$pubname=$jobData['pub']['pub_name'];
$pubdate=$jobData['job']['pub_date'];
$pubday=strtolower(date("l",strtotime($jobData['job']['pub_date'])));
$jobname=$jobData['run']['run_name'];
$runid=$jobData['job']['run_id'];
$pubid=$jobData['job']['pub_id'];
$lap=$jobData['job']['lap'];
if ($lap!='none')
{
  $lap="<span style='font-weight:bold;color:red;'>$lap</span>";  
}
if($jobData['job']['slitter']){$slitter='Engaged';}else{$slitter='Disengaged';}
$folderpin=$GLOBALS['folderpins'][$jobData['job']['folder_pin']];
$overrun=$jobData['job']['overrun']+$jobData['job']['draw_other']+$jobData['job']['draw_customer'];
$gatefold=$jobData['job']['gatefold'];
if ($jobData['job']['quarterfold']){$quarter="Quarterfold";}else{$quarter="Half-fold";}
$jobname=$jobData['run']['run_name'];
$draw=$jobData['job']['draw'];
$schedstart=date("m/d H:i",strtotime($jobData['job']['startdatetime']));
$schedstop=date("m/d H:i",strtotime($jobData['job']['enddatetime']));
$jobid=$jobData['job']['id'];
$statsid=$jobData['job']['stats_id'];
$papertypeid=$jobData['job']['papertype'];

if ($GLOBALS['pressJobStartMessages'])
{
    $message=$GLOBALS['pressStartMessage'];
    if($jobData['job']['job_message']!='')
    {
        $message.="<li>".stripslashes($jobData['job']['job_message'])."</li>";
    }
    //look at the pub id and see if there is an associated run message
    $sql="SELECT pub_message FROM publications WHERE id=$pubid";
    $dbPubMessage=dbselectsingle($sql);
    $pubmessage=$dbPubMessage['data']['pub_message'];
    if($pubmessage!=''){$message.="<li>".stripslashes($pubmessage)."</li>";}
    
    //look at the run id and see if there is an associated run message
    $sql="SELECT run_message FROM publications_runs WHERE id=$runid";
    $dbRunMessage=dbselectsingle($sql);
    $runmessage=$dbRunMessage['data']['run_message'];
    if($runmessage!=''){$message.="<li>".stripslashes($runmessage)."</li>";}
    
    $message=str_replace(array("\r", "\n", "\t"), '<br>',$message);
    $message=addslashes($message);
    $message="<div style='font-size:16px;'>$message</div>";
?>   
<script>
jQuery('body').showMessage({
    thisMessage:      "<?php echo $message ?>",
    className:        'success',
    position:         'top',
    opacity:          90,
    useEsc:            true,
    displayNavigation: true,
    autoClose:        true,
    delayTime:        10000,
    closeText:         'close',
    escText:      'Esc Key or'
});
</script>
<?php
}

if($GLOBALS['stickyNoteLocation']=='press')
{
    //do something different?
}
//look in inserts for this pub date, pub id and sticky_note=1
$sql="SELECT * FROM inserts A, inserts_schedule B WHERE A.id=B.insert_id B.pub_id='$pubid' AND B.insert_date='$pubdate' AND sticky_note=1";
$dbStickyNote=dbselectsingle($sql);
if($dbStickyNote['numrows']>0)
{
    $sticky="<span style='color:red;font-weight:bold;'>1 TODAY!!!</span>";
}


//now get the standard name for this paper
$sql="SELECT * FROM paper_types WHERE id=$papertypeid";
$dbPaper=dbselectsingle($sql);
$paper=$dbPaper['data']['common_name'];
$coverpaper=$jobData['job']['papertype_cover'];
if($coverpaper!=0)
{
    //now get the standard name for this paper
    $sql="SELECT * FROM paper_types WHERE id=$coverpaper";
    $dbPaper=dbselectsingle($sql);
    $coverpaper=$dbPaper['data']['common_name'];
} else {
    $coverpaper='Same paper';
}
if($GLOBALS['askForRollSize'])
{
    $rollSize=$GLOBALS['sizes'][$jobData['job']['rollSize']]."\" roll";
}

 //lets check and see if we have a job_stat record for this job
$sql="SELECT * FROM job_stats WHERE id=$statsid";
$dbJobStats=dbselectsingle($sql);
if ($dbJobStats['numrows']>0)
{
    if ($dbJobStats['data']['startdatetime_actual']!='')
    {
        $starttime=date("H:i",strtotime($dbJobStats['data']['startdatetime_actual']));
    } else {
        $starttime='';
    }
    if ($dbJobStats['data']['stopdatetime_actual']!='')
    {
        $stoptime=date("H:i",strtotime($dbJobStats['data']['stopdatetime_actual']));
    } else {
        $stoptime='';
    }
    if ($dbJobStats['data']['goodcopy_actual']!='')
    {
        $goodtime=date("H:i",strtotime($dbJobStats['data']['goodcopy_actual']));
    } else {
        $goodtime='';
    }
    $startcounter=$dbJobStats['data']['counter_start'];
    $stopcounter=$dbJobStats['data']['counter_stop'];
    $startspoils=$dbJobStats['data']['spoils_startup'];
    $setupstart=date("H:i",strtotime($dbJobStats['data']['setup_start']));
    $setupstop=date("H:i",strtotime($dbJobStats['data']['setup_stop']));
    if($setuptime==''){$setuptime=0;}
}
       
print "<div id='leftcol' style='width:460px;float:left;margin-right:10px;margin-left:10px;'>\n";
    print "<div id='pressTabsHolder' style='height:650px;'>\n";
        pressTabs($layoutid,$jobid,$operators);
    print "</div>\n";
print "</div>\n";
print "<input type='hidden' id='captureStopNotes' value='$GLOBALS[captureStopNotes]' />\n";
print "<div id='mainInfo' style='float:left;font-family:Trebuchet MS;font-size:14px;width:auto;'>\n";
if($GLOBALS['pressMonitorLayout']=='horizontal')
{

    print "<a class='pressStopButton fancybox.iframe button' href='includes/ajax_handlers/pressStopCodeDisplay.php?type=jobnotes&jobid=$jobid'>Update/View Job Notes</a>\n"; 
    print "<a href='#' class='button'onclick=\"window.open('maintenancePress.php','Press Maintenance','width=1000,height=650,toolbar=0,status=0,location=0,scrollbars=1');return false;\">Record maintenance or submit trouble ticket</a>\n";
    if($stoptime!=''){$showrecalc='';}else{$showrecalc='disabled';}
    print "<a href='#' id='recalcbtn' class='button' onclick='recalcStats($jobid);' $showrecalc;/>Re-calculate stats</a>\n";
            
    print "<fieldset>\n<legend>Job Information</legend>\n";
    print "<table>\n";
    print "<tr>";
    print "<td class='pressInfoLabel'>Publication</td><td class='pressInfoData' style='width:100px;'>$pubname</td>";
    print "<td class='pressInfoLabel' style='width:70px;'>Pub Date</td><td class='pressInfoData'>$pubdate</td>";
    print "<td class='pressInfoLabel' style='width:70px;'>Job Name</td><td class='pressInfoData'>$jobname</td>";
    print "</tr>\n";
    print "<tr>\n";
    print "<td class='pressInfoLabel'>Scheduled Start</td><td class='pressInfoData'>$schedstart</td>";
    print "<td class='pressInfoLabel' style='width:70px;'>Scheduled Stop</td><td class='pressInfoData'>$schedstop</td>";
    print "<td class='pressInfoLabel' style='width:70px;'>Paper</td><td class='pressInfoData'>$paper $rollSize</td>";
    print "</tr>\n";
    print "<tr>\n";
    print "<td class='pressInfoLabel'>Draw<input type='button' id='updatedraw' value='Edit' style='width:60px;font-size:10px;margin-left:4px;' onClick='updatePressDraw();'/></td><td class='pressInfoData'><input type='text' id='pressdraw' readonly=true value='$draw' onkeydown='return isNumberKey(event);'></td>";
    print "<td class='pressInfoLabel' style='width:70px;'>Lap</td><td class='pressInfoData'>$lap</td>";
    print "<td class='pressInfoLabel' style='width:70px;'>Cover paper</td><td class='pressInfoData'>$coverpaper</td>";
    print "<tr>\n";
    print "<td class='pressInfoLabel'>Overun</td><td class='pressInfoData'>$overrun</td>";
    print "<td class='pressInfoLabel' style='width:70px;'>Gatefold</td><td class='pressInfoData'>$gatefold</td>";
    print "<td class='pressInfoLabel' style='width:70px;'>Sticky Note</td><td class='pressInfoData'>$sticky</td>";
    print "</tr>\n";
    print "<tr>\n";
    print "<td class='pressInfoLabel'>Slitter</td><td class='pressInfoData'>$slitter</td>";
    print "<td class='pressInfoLabel' style='width:70px;'>Folder Pin</td><td class='pressInfoData'>$folderpin</td>";
    print "<td class='pressInfoLabel' style='width:70px;'>Fold</td><td class='pressInfoData'>$quarter</td>";
    print "</tr>\n";
    
    //press setup start time
    $bid='setupstart';
    $clicker="timeInit('benchmark_$bid','benchmarkChange','$bid','setupstart_error');";
    $nowbutton="<input type='button' style='margin-left:4px;width:38px;font-size:10px;' onclick=\"jobMonitorPressTimeSet('$bid');\" value='Set'>";   
    print "<tr><td class='pressInfoLabel' style='width:70px;'>Setup Start</td>";
    print "<td><input style='width:70px;' type='text' id='benchmark_$bid' value='$setupstart' onclick=\"$clicker\">$nowbutton<span class='error' class='error' id='setupstart_error'></span></td>\n";
    //press setup stop time
    $bid='setupstop';
    $clicker="timeInit('benchmark_$bid','benchmarkChange','$bid','setupstop_error');";
    $nowbutton="<input type='button' style='margin-left:4px;width:38px;font-size:10px;' onclick=\"jobMonitorPressTimeSet('$bid');\" value='Set'>";   
    print "<td class='pressInfoLabel' style='width:70px;'>Setup Stop</td>";
    print "<td><input style='width:70px;' type='text' id='benchmark_$bid' value='$setupstop' onclick=\"$clicker\">$nowbutton<span class='error' id='setupstop_error'></span></td>\n";
        
    
    
    print "</tr>\n";
    print "</table>\n";  
    print "</fieldset>\n";
    print "<div style='float:left;width:290px;'>\n";
        print "<fieldset>\n<legend>Benchmarks</legend>\n";
        benchmarks($jobid,$runid,$pubday,$operators);
        print "</fieldset>\n";
    print "</div>\n";
   
    print "<div id='startstop' style='float:left;margin-left:10px;width:440px;'>\n";
        print "<fieldset>\n<legend>Press Start/Stop</legend>\n";
        print "<table>\n";
        
        //press start time
        $bid='starttime';
        $clicker="timeInit('benchmark_$bid','benchmarkChange','$bid','starttime_error');";
        $nowbutton="<input type='button' style='margin-left:4px;width:40px;font-size:10px;' onclick=\"jobMonitorPressTimeSet('$bid');\" value='Set'>";   
        print "<tr><td class='pressInfoLabel' style='width:70px;'>Start time</td>";
        print "<td><input style='width:70px;' type='text' id='benchmark_$bid' value='$starttime' onclick=\"$clicker\">$nowbutton<span class='error' class='error' id='starttime_error'></span></td>\n";
        
        
        //press start counter
        $bid='startcounter';
        $clicker="numpad_init('benchmark_$bid','startcounter_error');";
        print "<td class='pressInfoLabel' style='width:90px;'>Start counter</td>";
        print "<td><input style='width:100px;' type='text' id='benchmark_$bid' value='$startcounter' onclick=\"$clicker\"><span class='error' id='startcounter_error'></span></td></tr>\n";
        
        //press good copy time
        $bid='goodtime';
        $clicker="timeInit('benchmark_$bid','benchmarkChange','$bid','goodtime_error');";
        $nowbutton="<input type='button' style='margin-left:4px;width:40px;font-size:10px;' onclick=\"jobMonitorPressTimeSet('$bid');\" value='Set'>";   
        print "<tr><td class='pressInfoLabel' style='width:70px;'>Good copy</td>";
        print "<td><input style='width:70px;' type='text' id='benchmark_$bid' value='$goodtime' onclick=\"$clicker\">$nowbutton<span class='error' id='goodtime_error'></span></td>\n";
        
        //press startup spoils
        $bid='startspoils';
        $clicker="numpad_init('benchmark_$bid','spoils_error');";
        print "<td class='pressInfoLabel' style='width:90px;'>Startup spoils</td>";
        print "<td><input style='width:100px;' type='text' id='benchmark_$bid' value='$startspoils' onclick=\"$clicker\"><span class='error' id='spoils_error'></span></td></tr>\n";
        
        
        //press stop time
        $bid='stoptime';
        $clicker="timeInit('benchmark_$bid','benchmarkChange','$bid','stoptime_error');";
        $nowbutton="<input type='button' style='margin-left:4px;width:40px;font-size:10px;' onclick=\"jobMonitorPressTimeSet('$bid');\" value='Set'>";   
        print "<tr><td class='pressInfoLabel' style='width:70px;'>Stop time</td>";
        print "<td><input style='width:70px;' type='text' id='benchmark_$bid' value='$stoptime' onclick=\"$clicker\">$nowbutton<span class='error' id='stoptime_error'></span></td>\n";
        
       //press stop counter
        $bid='stopcounter';
        $clicker="numpad_init('benchmark_$bid','stopcounter_error');";
        print "<td class='pressInfoLabel' style='width:90px;'>Stop Counter</td>";
        print "<td><input style='width:100px;' type='text' id='benchmark_$bid' value='$stopcounter' onclick=\"$clicker\"><span class='error' id='stopcounter_error'></span></td></tr>\n";
        
        
        print "</table>\n";
        print "</fieldset>\n";
        print "</div>\n"; 
        print "<div class='clear'></div>\n";
        print "<div style='float:left;width:600px;'>\n";
            print "<fieldset>\n<legend>Press Stop Codes</legend>\n";
               stopCodes($jobid);
            print "</fieldset>\n";
        print "</div>\n"; 
        print "<div class='clear'></div>\n";
        
    


    print "<div id='bottom'>\n";
            print "<div id='pagesout' style='float:left;width:240px;'>\n";
            print "<fieldset>\n<legend>Pages Remaining</legend>\n";
            print "<div id='pageslist' style='padding:2px;height:200px;overflow-y:auto;border:1px solid black;background-color:white'>\n";
            pageslist($jobid);
            print "</div>\n";
            print "</fieldset>\n";
            print "</div>\n";
            
            print "<div id='platesout' style='float:left;margin-left:10px;width:240px;'>\n";
            print "<fieldset>\n<legend>Plates Remaining</legend>\n";
            print "<div id='plateslist' style='padding:2px;height:200px;overflow-y:auto;border:1px solid black;background-color:white'>\n";
            plateslist($jobid);
            print "</div>\n";
            print "</fieldset>\n";
            print "</div>\n";
            
            print "<div id='remakes' style='float:left;margin-left:10px;width:240px;'>\n";
            print "<fieldset>\n<legend>Remakes</legend>\n";
            print "<div id='remakeslist' style='padding:2px;height:200px;overflow-y:auto;border:1px solid black;background-color:white'>\n";
            remakeslist($jobid);
            print "</div>\n";
            print "</fieldset>\n";
            print "</div>\n";
            

    print "</div>\n";
    print "<div class='clear'></div>\n";
} else {
    //more 'vertical' layout with about 600px of width to work with. We'll do stop codes across the top
    //then 3 columns of other stuff at 190px each with 10px gutters
    print "<input type='button' value='Update/View Job Notes' href='includes/ajax_handlers/pressStopCodeDisplay.php?type=jobnotes&jobid=$jobid' class='fixedAjaxDOMWindow' rel='$jobid'>\n"; 
    print "<input type='button' value='Record maintenance or submit trouble ticket' onclick=\"window.open('pressMaintenance.php','Press Maintenance','width=1000,height=650,toolbar=0,status=0,location=0,scrollbars=1');return false;\">\n";
    print "<div style='width:600px;'>\n";
            print "<fieldset>\n<legend>Press Stop Codes</legend>\n";
               stopCodes($jobid);
            print "</fieldset>\n";
    print "</div>\n"; 
        
    print "<div style='width:200px;margin-right:4px;float:left;'>\n";        
        print "<fieldset>\n<legend>Job Information</legend>\n";
        print "<span class='pressInfoLabel'>Publication:<br>$pubname</span><br>";
        print "<span class='pressInfoLabel'>Pub Date:<br>$pubdate</span><br>";
        print "<span class='pressInfoLabel'>Job Name:<br>$jobname</span><br>";
        print "<span class='pressInfoLabel'>Scheduled Start:<br>$schedstart</span><br>";
        print "<span class='pressInfoLabel'>Scheduled Stop:<br>$schedstop</span><br>";
        print "<span class='pressInfoLabel'>Paper:<br>$paper $rollSize</span><br>";
        print "<span class='pressInfoLabel'>Draw: <input type='text' id='pressdraw' readonly=true value='$draw' onkeydown='return isNumberKey(event);' size=3></span> <input type='button' id='updatedraw' value='Edit' style='width:60px;font-size:10px;margin-left:4px;' onClick='updatePressDraw();'/><br>";
        print "<span class='pressInfoLabel'>Lap:<br>$lap</span><br>";
        print "<span class='pressInfoLabel'>Gatefold:<br>$gatefold</span><br>\n";
        print "<span class='pressInfoLabel'>Overun:<br>$overrun</span><br>";
        print "<span class='pressInfoLabel'>Sticky Note:<br>$sticky</span><br>";
        
         //press setup start time
        $bid='setupstart';
        $clicker="timeInit('benchmark_$bid','benchmarkChange','$bid','setupstart_error');";
        $nowbutton="<input type='button' style='margin-left:4px;width:38px;font-size:10px;' onclick=\"jobMonitorPressTimeSet('$bid');\" value='Set'>";   
        print "<span class='pressInfoLabel'>Setup Start<br />";
        print "<input style='width:70px;' type='text' id='benchmark_$bid' value='$setupstart' onclick=\"$clicker\">$nowbutton<span class='error' class='error' id='setupstart_error'></span></span<br />\n";
        //press setup stop time
        $bid='setupstop';
        $clicker="timeInit('benchmark_$bid','benchmarkChange','$bid','setupstop_error');";
        $nowbutton="<input type='button' style='margin-left:4px;width:38px;font-size:10px;' onclick=\"jobMonitorPressTimeSet('$bid');\" value='Set'>";   
        print "<span class='pressInfoLabel'>Setup Stop<br />";
        print "<input style='width:70px;' type='text' id='benchmark_$bid' value='$setupstop' onclick=\"$clicker\">$nowbutton<span class='error' id='setupstop_error'></span></span><br />\n";
        
        
        
        print "</fieldset>\n";
        
        print "<fieldset>\n<legend>Benchmarks</legend>\n";
            benchmarks($jobid,$runid,$pubday,$operators);
        print "</fieldset>\n";
    
    print "</div>\n";
    
    print "<div id='startstop' style='float:left;margin-right:4px;width:200px;'>\n";

        //lets check and see if we have a job_stat record for this job
        $sql="SELECT * FROM job_stats WHERE id=$statsid";
        $dbJobStats=dbselectsingle($sql);
        if ($dbJobStats['numrows']>0)
        {
            if ($dbJobStats['data']['startdatetime_actual']!='')
            {
                $starttime=date("H:i",strtotime($dbJobStats['data']['startdatetime_actual']));
            } else {
                $starttime='';
            }
            if ($dbJobStats['data']['stopdatetime_actual']!='')
            {
                $stoptime=date("H:i",strtotime($dbJobStats['data']['stopdatetime_actual']));
            } else {
                $stoptime='';
            }
            if ($dbJobStats['data']['goodcopy_actual']!='')
            {
                $goodtime=date("H:i",strtotime($dbJobStats['data']['goodcopy_actual']));
            } else {
                $goodtime='';
            }
            $startcounter=$dbJobStats['data']['counter_start'];
            $stopcounter=$dbJobStats['data']['counter_stop'];
            $startspoils=$dbJobStats['data']['spoils_startup'];
        }
        
        print "<fieldset>\n<legend>Press Start/Stop</legend>\n";
        
        //press start time
        $bid='starttime';
        $clicker="timeInit('benchmark_$bid','benchmarkChange','$bid','starttime_error');";
        $nowbutton="<input type='button' style='margin-left:4px;width:40px;font-size:10px;' onclick=\"jobMonitorPressTimeSet('$bid');\" value='Set'>";   
        print "<span class='pressInfoLabel'>Start time: ";
        print "<input style='width:70px;' type='text' id='benchmark_$bid' value='$starttime' onclick=\"$clicker\">$nowbutton<span class='error' class='error' id='starttime_error'></span><br>\n";
        
        //press good copy time
        $bid='goodtime';
        $clicker="timeInit('benchmark_$bid','benchmarkChange','$bid','goodtime_error');";
        $nowbutton="<input type='button' style='margin-left:4px;width:40px;font-size:10px;' onclick=\"jobMonitorPressTimeSet('$bid');\" value='Set'>";   
        print "<span class='pressInfoLabel'>Good copy: ";
        print "<input style='width:70px;' type='text' id='benchmark_$bid' value='$goodtime' onclick=\"$clicker\">$nowbutton<span class='error' id='goodtime_error'></span><br>\n";
        
        //press stop time
        $bid='stoptime';
        $clicker="timeInit('benchmark_$bid','benchmarkChange','$bid','stoptime_error');";
        $nowbutton="<input type='button' style='margin-left:4px;width:40px;font-size:10px;' onclick=\"jobMonitorPressTimeSet('$bid');\" value='Set'>";   
        print "<span class='pressInfoLabel'>Stop time: ";
        print "<input style='width:70px;' type='text' id='benchmark_$bid' value='$stoptime' onclick=\"$clicker;\$('#recalcbtn').show();\">$nowbutton<span class='error' id='stoptime_error'></span><br>\n";
        
        //press start counter
        $bid='startcounter';
        $clicker="numpad_init('benchmark_$bid','startcounter_error');";
        print "<span class='pressInfoLabel'>Start counter: ";
        print "<input style='width:100px;' type='text' id='benchmark_$bid' value='$startcounter' onclick=\"$clicker\"><span class='error' id='startcounter_error'></span><br>\n";
        
        //press stop counter
        $bid='stopcounter';
        $clicker="numpad_init('benchmark_$bid','stopcounter_error');";
        print "<span class='pressInfoLabel'>Stop Counter: ";
        print "<input style='width:100px;' type='text' id='benchmark_$bid' value='$stopcounter' onclick=\"$clicker\"><span class='error' id='stopcounter_error'></span><br>\n";
        
        //press startup spoils
        $bid='startspoils';
        $clicker="numpad_init('benchmark_$bid','spoils_error');";
        print "<span class='pressInfoLabel'>Startup spoils: ";
        print "<input style='width:100px;' type='text' id='benchmark_$bid' value='$startspoils' onclick=\"$clicker\"><span class='error' id='spoils_error'></span><br>\n";
        
       print "</fieldset>\n";
       print "</div>\n"; 
      
    


print "<div id='rightrail' style='float:left;width:200px'>\n";
        print "<div id='pagesout' style='width:190px;'>\n";
            print "<fieldset>\n<legend>Pages Remaining</legend>\n";
                print "<div id='pageslist' style='padding:2px;height:200px;overflow-y:auto;border:1px solid black;background-color:white'>\n";
                pageslist($jobid);
                print "</div>\n";
            print "</fieldset>\n";
        print "</div>\n";
        
        print "<div id='platesout' style='margin-top:10px;width:190px;'>\n";
            print "<fieldset>\n<legend>Plates Remaining</legend>\n";
                print "<div id='plateslist' style='padding:2px;height:200px;overflow-y:auto;border:1px solid black;background-color:white'>\n";
                plateslist($jobid);
                print "</div>\n";
            print "</fieldset>\n";
        print "</div>\n";
        
        print "<div id='remakes' style='margin-top:10px;width:190px;'>\n";
            print "<fieldset>\n<legend>Remakes</legend>\n";
                print "<div id='remakeslist' style='padding:2px;height:200px;overflow-y:auto;border:1px solid black;background-color:white'>\n";
                remakeslist($jobid);
                print "</div>\n";
            print "</fieldset>\n";
        print "</div>\n";
print "</div>\n";       
}
print "</div><!-- closes main info area -->\n";

print "<div class='clear'></div>\n";
print "</div><!-- closes holder -->\n";

function remakeslist($jobid)
{
    $cache=checkCache('jobBoxes'.$jobid,'remakes');
    if($cache)
    {
        print $cache;
    } else {
        $sql="SELECT DISTINCT(page_number), section_code, version, workflow_receive FROM job_pages WHERE job_id=$jobid AND version>1 ORDER BY page_number ASC, version DESC";
        $dbPages=dbselectmulti($sql);
        $data='';
        if ($dbPages['numrows']>0)
        {
            $data.="<table>\n";
            $data.= "<tr><th>Section</th><th>Page</th><th>Version</th><th>Receive Time</th></tr>\n";
            foreach($dbPages['data'] as $page)
            {
                $data.= "<tr>\n";
                $data.= "<td>".$page['section_code']."</td>\n";
                $data.= "<td>".$page['page_number']."</td>\n";
                $data.= "<td>".$page['version']."</td>\n";
                $data.= "<td>";
                if ($page['workflow_receive']!='')
                {
                    $data.= date("H:i:s",strtotime($page['workflow_receive']));
                } else {
                    $data.= "Not received";
                }
                $data.= "</td>";
                $data.= "</tr>\n";
            
            }
            $data.= "</table>\n";
        } else {
            $data.= "No remakes at this time.";
        }
        
        print $data;
        setCache('jobBoxes'.$jobid,'remakes',$data);
    }
}

function pageslist($jobid)
{
    $cache=checkCache('jobBoxes'.$jobid,'missingpages');
    if($cache)
    {
        print $cache;
    } else {
        $data='';
        $sql="SELECT DISTINCT(page_number), section_code, page_number, color FROM job_pages WHERE job_id=$jobid AND page_release is Null ORDER BY section_code ASC, page_number ASC";
        $dbPages=dbselectmulti($sql);
        if ($dbPages['numrows']>0)
        {
            $data="<table>\n";
            $data.="<tr><th>Section</th><th>Page</th><th>Color</th></tr>\n";
            foreach($dbPages['data'] as $page)
            {
                $data.= "<tr>\n";
                $data.= "<td>".$page['section_code']."</td>\n";
                $data.= "<td>".$page['page_number']."</td>\n";
                if ($page['color']){$data.=  "<td>Full color</td>";}else{$data.=  "<td>Black</td>";}
                $data.= "</tr>\n";
            }
            $data.= "</table>\n";
        } else {
            $data.= "All pages have been received.";
        }
        print $data;
        setCache('jobBoxes'.$jobid,'missingpages',$data);
    }
    
}

function plateslist($jobid)
{
    $cache=checkCache('jobBoxes'.$jobid,'missingplates');
    if($cache)
    {
        print $cache;
    } else {
        $data='';
        $sql="SELECT DISTINCT(low_page), section_code, low_page, color FROM job_plates WHERE job_id=$jobid AND black_receive is Null ORDER BY section_code ASC, low_page ASC";
        $dbPages=dbselectmulti($sql);
        if ($dbPages['numrows']>0)
        {
            $data.="<table>\n";
            $data.="<tr><th>Section</th><th>Plate</th><th>Color</th></tr>\n";
            foreach($dbPages['data'] as $page)
            {
                $data.="<tr>\n";
                $data.= "<td>".$page['section_code']."</td>\n";
                $data.="<td>".$page['low_page']."</td>\n";
                if ($page['color']){$data.=  "<td>Full color</td>";}else{$data.="<td>Black</td>";}
                $data.=  "</tr>\n";
            }
            $data.="</table>\n";
        } else {
            $data.="All plates have been received.";
        }
        print $data;
        setCache('jobBoxes'.$jobid,'missingplates',$data);
    }
}

function stopCodes($jobid)
{
    $sql="SELECT * FROM stop_codes ORDER BY stop_order";
    $dbCodes=dbselectmulti($sql);
    if ($dbCodes['numrows']>0)
    {
        $i=1;
        foreach($dbCodes['data'] as $code)
        {
            print "<a class='pressStopButton fancybox.iframe button' href='includes/ajax_handlers/pressStopCodeDisplay.php?type=stopcode&stopid=$code[id]&jobid=$jobid' rel='$jobid'>$code[stop_name]</a>\n"; 
            if ($i<=5)
            {
                $i++;
            } else {
                $i=1;
                print "<div class='clear'></div>\n";
            }
        }
    
    
    }


}


function benchmarks($jobid,$runid,$pubday,$operators)
{
    //at the top, specify the job operator
    $sql="SELECT job_pressoperator FROM job_stats A, jobs B WHERE A.id=B.stats_id AND B.id=$jobid";
    $dbOp=dbselectsingle($sql);
    if ($dbOp['numrows']>0)
    {
        if ($dbOp['data']['job_pressoperator']!=0){$opid=$dbOp['data']['job_pressoperator'];}else{$opid=0;}
    } else {
        $opid=0;
    }
    //sign off sections
    print "Principal Operator on this job:<br>\n";
    print input_select('jobOperator',$operators[$opid],$operators,false,"benchmarkChange(this.id,'');");
    print "<br>\n";
    
    //get a list of all benchmarks for press that are in "displaylist" 
    $sql="SELECT A.id, A.benchmark_name, A.benchmark_type, B.$pubday FROM benchmarks A, run_benchmarks B  
    WHERE A.benchmark_category='press' AND A.benchmark_displaylist=1 AND A.id=B.benchmark_id AND B.run_id=$runid ORDER BY A.benchmark_order";
    //print "Working with $sql";
    $dbBenchmarks=dbselectmulti($sql);
    if ($dbBenchmarks['numrows']>0)
    {
        print "<table style='width:98%;'>\n";
        print "<tr><th style='width:80px;font-size:12px;'>Benchmark</th><th style='width:80px;font-size:12px;'>Goal</th><th style='width:120px;font-size:12px;'>Actual</th></tr>\n";
        foreach($dbBenchmarks['data'] as $benchmark)
        {
            $bid=$benchmark['id'];
            $name=$benchmark['benchmark_name'];
            $type=$benchmark['benchmark_type'];
            $goal=$benchmark[$pubday];
            //see if we have an actual for this benchmark in the job_benchmarks table
            $sql="SELECT * FROM job_benchmarks WHERE job_id=$jobid AND benchmark_id=$bid";
            $dbJobBenchmark=dbselectsingle($sql);
            if ($dbJobBenchmark['numrows']>0)
            {
                if ($type=='time')
                {
                    $actual=$dbJobBenchmark['data']['benchmark_actual_time'];
                    $actual=date("H:i",strtotime($actual));
                } else {
                    $actual=$dbJobBenchmark['data']['benchmark_actual_number'];
                }
            }else {
                if ($type=='time')
                {
                    $actual="00:00";
                } else {
                    $actual="";
                }
            }
            
            
            print "<tr>\n";
            print "<td style='width:80px;font-size:12px;'>$name</td>";
            print "<td style='width:80px;font-size:12px;'>$goal</td>";
            if ($type=='time')
            {
                $clicker="timeInit('benchmark_$bid','benchmarkChange','$bid');";
                $nowbutton="<input type='button' style='margin-left:4px;width:40px;font-size:10px;' onclick=\"timeSet('$bid');\" value='Set'>";   
            } else {
                $clicker="numpad_init('benchmark_$bid','ares');";
                $nowbutton="";
            }
            print "<td><input style='width:50px;' type='text' id='benchmark_$bid' value='$actual' onclick=\"$clicker\">$nowbutton</td>";
            print "</tr>\n";
        }
        print "</table>\n";
    }

}


function pressTabs($layoutid,$jobid,$operators)
{
    print "<div id='tabs'>\n";
    print "<ul id='pressTabs'>
        <li><a href='#jobnotes'>Notes</a></li>
        <li><a href='#layoutTab'>Layout</a></li>
        <li><a href='#checklistTab'>Checklist</a></li>
        <li><a href='#presscrewTab'>Crew</a></li>
        <li><a href='#paperTab'>Newsprint</a></li>
        ";
    print "</ul>\n";
        print "<div id='jobnotes'>\n";
            jobnotes($jobid);
        print "</div>\n";
        print "<div id='layoutTab'>\n";
        configure($layoutid,true,false,true);
        print "</div>\n";
        print "<div id='checklistTab'>\n";
            checklist($jobid,$operators);
        print "</div>\n";
        print "<div id='presscrewTab'>\n";
            crew($jobid);
        print "</div>\n";
        print "<div id='paperTab'>\n";
            jobnewsprint($jobid);
        print "</div>\n";
        
    print "</div>\n";
    ?>
    <script type='text/javascript'>
   $(function() {
        $( '#tabs' ).tabs();
    });
    </script>
    <?php

}

function jobnotes($jobid)
{
    
    $sql="SELECT press_id, notes_press, notes_job FROM jobs WHERE id=$jobid";
    $dbJobInfo=dbselectsingle($sql);
    $jobinfo=$dbJobInfo['data'];
    print "<h3>Messages</h3>\n";
    print $GLOBALS['message'];
    print "<h3>Notes</h3>\n";
    print stripslashes($jobinfo['notes_job']."\n".$jobinfo['notes_press'])."<br />";
          
}

function crew($jobid)
{
    global $siteID,$pressDepartmentID;
    print "<div style='font-size:12px;'>\n";
    $sql="SELECT * FROM users WHERE department_id=$pressDepartmentID ORDER BY lastname";
    $dbPressman=dbselectmulti($sql);
    $sql="SELECT job_pressman_ids FROM job_stats A, jobs B WHERE A.id=B.stats_id AND B.id=$jobid";
    $dbStats=dbselectsingle($sql);
    $jobpressmanids=array();
    if($dbStats['numrows']>0)
    {
        $jobpressmanids=explode("|",$dbStats['data']['job_pressman_ids']);
    }
    if ($dbPressman['numrows']>0)
    {
        $i=0;
        foreach($dbPressman['data'] as $s)
        {
            if (in_array($s['id'],$jobpressmanids))
            {
                $checked="checklist_checked";
            } else {
                $checked="checklist_unchecked";
            }
            $pressmanid=$s['id'];
            if($i==1)
            {
                $margin='';
            } else {
                $margin='margin-right:10px;';
            }
            
            print "<div id='pressman_$pressmanid' class='$checked' onClick='pressmanChange($jobid,$pressmanid);' style='float:left;width:35%;$margin'>\n";
            print $s['firstname']." ".$s['lastname']."</div>\n";
            
            if($i==0)
            {
                $i=1;
            } else {
                $i=0;
                print "<div class='clear'></div>\n";
            }
            
        }
    }
    print "<div class='clear'></div>\n";
    print "</div>\n";  
}

function jobnewsprint($jobid)
{
    global $papertypes, $sizes, $pressid, $pressmen, $siteID, $pressDepartmentID, $jobData, $broadsheetPageHeight;
    //need all press towers
    $sql="SELECT * from press_towers WHERE press_id=$pressid AND tower_type='printing' ORDER BY tower_order";
    $dbTowers=dbselectmulti($sql);
    $towers=$dbTowers['data'];
    $sizes[0]='Roll width';
    $papertypes[0]='Type of paper';
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    $newtowers=array();
    foreach($towers as $tower)
    {
        //first thing is to see if we've already created a paper record for this job and tower in job_paper
        $sql="SELECT * FROM job_paper WHERE job_id='$jobid' AND tower_id='$tower[id]'";
        $dbExisting=dbselectsingle($sql);
        if($dbExisting['numrows']>0)
        {
            //means that already exists
            $e=$dbExisting['data'];
            $newtowers[$tower['id']]['id']=$tower['id'];
            $newtowers[$tower['id']]['tower_name']=$tower['tower_name'];    
            $newtowers[$tower['id']]['used']=1;    
            $newtowers[$tower['id']]['papertype']=$e['papertype_id'];    
            $newtowers[$tower['id']]['size']=$e['size_id'];
        } else{
            //see if this tower is used
            $towerid=$tower['id'];
            $sql="SELECT * FROM layout_sections WHERE layout_id=$job[layout_id] AND towers LIKE '%$towerid%'";
            $dbUsed=dbselectsingle($sql);
            if ($dbUsed['numrows']>0)
            {
                //lets verify because single digits can be returned by this (example Tower 1 gives 1 and 10);
                $z=$dbUsed['data']['towers'];
                $z=explode("|",$z);
                $found=0;
                foreach($z as $key=>$v)
                {
                    if($towerid==$v)
                    {
                        $found=1;
                    }   
                }
                $newtowers[$tower['id']]['used']=$found;        
            } else {
                $newtowers[$tower['id']]['used']=0;
            }
            
            $newtowers[$tower['id']]['tower_name']=$tower['tower_name'];
            $newtowers[$tower['id']]['id']=$tower['id'];
            $newtowers[$tower['id']]['papertype']=$job['papertype'];
            //now to figure out how many pages on the plate - thus the paper size
            $layoutid=$job['layout_id'];
            $towerid=$tower['id'];
            $sql="SELECT * FROM layout_page_config WHERE layout_id=$layoutid AND tower_id=$towerid AND side='10' AND tower_row=1";
            $dbPages=dbselectmulti($sql);
            $pcount=0;
            if($dbPages['numrows']>0)
            {
                foreach($dbPages['data'] as $pages)
                {
                    if ($pages['page_number']!='0'){$pcount++;}    
                }
                //we're assuming everything is a newspaper lead. for commercial
                //leads, we'll just assume the press crew will set it.
                //On 9/27/12 changed from using GLOBALS[broadsheetPageWidth] to the actual page width defined in the job
                //$rollwidth=$GLOBALS['broadsheetPageWidth']*$pcount;
                //find the index in the sizes array with this roll width
                
                $rollwidth=$job['pagewidth']*$pcount;
                $rollwidthmin=$rollwidth-.1;
                $rollwidthmax=$rollwidth+.1;
                
                //find the index in the sizes array with this roll width
                $sql="SELECT id FROM paper_sizes WHERE width>='$rollwidthmin' AND width<='$rollwidthmax' AND status=1";
                $dbSizeFind=dbselectsingle($sql);
                if($dbSizeFind['numrows']>0)
                {
                    $rollwidthid=$dbSizeFind['data']['id'];
                } else {
                    $rollwidthid='Please choose';
                }
                $newtowers[$tower['id']]['size']=$rollwidthid; 
            } else {
                $newtowers[$tower['id']]['size']='Size';
            }
            
            
            
            //add the job_paper record
            $sql="INSERT INTO job_paper (job_id, pub_id, run_id, tower_id, papertype_id, 
                size_id, pub_date, print_date, roll_width, page_width, page_length, price_per_ton, 
                factor, calculated_tonnage, calculated_cost, site_id) VALUES ('$jobid', '".$jobData['job']['pub_id']."',
                '".$jobData['job']['run_id']."', '$towerid', '".$jobData['job']['papertype']."', '$rollwidthid',
                '$pubdate', '$printdate', '$sizes[$rollwidthid]','".$jobData['job']['pagewidth']."',
                '$broadsheetPageHeight', '0', '0', '0', '0', '$siteID')";
            $dbInsertPaper=dbinsertquery($sql);
        }
    }
    $towers=$newtowers;
    
    print "<form name='jobpaper' id='jobpaper' method=post action='includes/ajax_handlers/jobmonitorPressNewsprint.php'>\n";
    if (count($towers)>0)
    {
        foreach ($towers as $tower)
        {
            if ($tower['used'])
            {
                $ptype=$tower['papertype'];
                $psize=$tower['size'];
                
            } else {
                $ptype=0;
                $psize=0;
            }
            if($tower['tower_name']!='')
            {
                print "<div style='width:100px;float:left;'>$tower[tower_name]: <input type=hidden name='tower_$tower[id]' id='tower_$tower[id]' value='$tower[tower_name]'></div>";
                print "<div style='float:left;'>\n";
                print input_select("t_$tower[id]_papertype",$papertypes[$ptype],$papertypes)."<br />";
                print input_select("t_$tower[id]_size",$sizes[$psize],$sizes);
                print "</div><div class='clear'></div>\n";
            }
        }    
    }
    print "<input type='hidden' value='$jobid' name='pjid' />\n";
    print "<input type='hidden' value='$jobid' name='pubid' />\n";
    print "<input type='hidden' value='$jobid' name='runid' />\n";
    print "<input type='hidden' value='$jobid' name='runid' />\n";
    print "<input type='submit' value='Save Newsprint' style='float:left;'/>\n";
    print "<div id='newsprintSaveMessage' style='float:left;margin-left:10px;font-weight:bold;'></div>\n";
    print "</form>\n";
    print "<div class='clear'></div>\n";
    ?>
    <script>
    $(document).ready(function() { 
    var options = { 
        target:        '#newsprintSaveMessage'   // target element(s) to be updated with server response 
        //beforeSubmit:  function(){alert('submitting');},  // pre-submit callback 
        //success:      function(){$('#newsprintSaveMessage').css('display','block')}                 // post-submit callback 

        // other available options: 
        //url:       url         // override for form's 'action' attribute 
        //type:      type        // 'get' or 'post', override for form's 'method' attribute 
        //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
        //clearForm: true        // clear all form fields after successful submit 
        //resetForm: true        // reset the form after successful submit 

        // $.ajax options can be used here too, for example: 
        //timeout:   3000 
    }; 
   
    // bind form using 'ajaxForm' 
    $('#jobpaper').ajaxForm(options);
    });
    function recalcStats(jobid)
    {
        $.ajax({
          url: "includes/ajax_handlers/jobmonitorPress.php?cb_="+Math.random(),
          type: "POST",
          data: ({type:'recalcstats',jobid:jobid}),
          dataType: "json",
          success: function(response){
                 
          }
        }) 
    }
    </script>
    <?php    
}

function checklist($jobid,$operators)
{
    global $siteID;
    //first, get all the checklist items
    print "<div style='font-size:12px;'>\n";
    $sql="SELECT * FROM checklist WHERE site_id=$siteID AND category='press' ORDER BY checklist_order ASC";
    $dbChecklist=dbselectmulti($sql);
    if ($dbChecklist['numrows']>0)
    {
        foreach ($dbChecklist['data'] as $checkitem)
        {
            //lets see if this one has been added and is value=true for this job    
            $checkid=$checkitem['id'];
            $checkname=$checkitem['checklist_item'];
            $sql="SELECT * FROM job_checklist WHERE job_id=$jobid AND checklist_id=$checkid";
            $dbJobCheck=dbselectsingle($sql);
            if ($dbJobCheck['numrows']>0 && $dbJobCheck['data']['checklist_value']==1)
            {
                $checked="checklist_checked";
            } else {
                $checked="checklist_unchecked";
            }
            print "<div id='check_$checkid' class='$checked' onClick='checklistChange(\"$checkid\");'>\n";
            print $checkname;
            print "</div>\n";
        }
        
        //see if a pressman has approved this checklist already
        $sql="SELECT checklist_approved FROM job_stats WHERE job_id=$jobid";
        $dbOp=dbselectsingle($sql);
        if ($dbOp['numrows']>0)
        {
            if ($dbOp['data']['checklist_approved']!=0){$opid=$dbOp['data']['checklist_approved'];}else{$opid=0;}
        } else {
            $opid=0;
        }
        //sign off sections
        print "I hereby confirm that I have personally made sure the above checklist items have been completed.<br>Signed";
        print input_select('checklistOperator',$operators[$opid],$operators,false,"benchmarkChange(this.id,'');");
    }
    print "</div>\n";



}
print "<div id='numpad' style='position:absolute;z-index:1000;'></div>\n";
print "<div id='timePopup' style='position:absolute;z-index:1000;'></div>\n";

?>
<script type="text/javascript">
setInterval("pressBoxes()",60000);    

$(".pressStopButton").fancybox({
        padding        : 5,
        openEffect     : 'elastic',
        closeEffect    : 'elastic',
        overlayColor   : '#000',
        overlayOpacity : 0.6,
        arrows         : false,
        modal          : true,
        scrolling      : 'no'
});
</script>
<?php
footer();