<?php
include("includes/mainmenu.php") ;

//setup drop-downs for clock
$everyhours=array();
for ($i=0;$i<=24;$i++){
    $everyhours[$i]=$i;
}
$everyminutes=array();
for ($i=0;$i<=55;$i=$i+5){
    if ($i<10){$i="0".$i;}
    $everyminutes[$i]=$i;
}
$dailyhours=$everyhours;
$dailyminutes=$everyminutes;
$weeklyhours=$everyhours;
$weeklyminutes=$everyminutes;
$monthlyhours=$everyhours;
$monthlyminutes=$everyminutes;



if ($_POST['submit']=='Add'){
        save_cron('insert');
    } elseif ($_POST['submit']=='Update'){
       save_cron('update'); 
    } else { 
        show_cron();
    }

function show_cron() {
    $cronid=intval($_GET['cronid']);
    $jobid=intval($_GET['jobid']);
    if ($_GET['action']=='viewlog'){
        $sql="SELECT * from core_cronexecution WHERE id=$jobid";
        $dbresult=dbselectsingle($sql);
        $record=$dbresult['data'];
        print "Job was scheduled for $record[exectime] and actually ran at $record[actualtime]<br />";
        print "The following are the notes from the job<br/><br />";
        $notes=explode(";",$record['jobnotes']);
        foreach($notes as $key=>$note){
            print $note."<br />";
        }
        print "<br /><br />";
        print "<a href='?action=view&cronid=$record[cronid]'>Back to job log</a>"; 
    } elseif ($_GET['action']=='run')
    {
        print "<a href='?action=list' class='submit'>Return to cron job list</a><br>";
        print "<iframe src='cronprocessorSingle.php?cronid=$cronid' frameborder=0 width=650 height=600></iframe>\n";     
      
    } elseif ($_GET['action']=='delete'){
    $sql="DELETE FROM core_cron where id=$cronid";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM core_cronexecution where cronid=$cronid";
    $dbDelete=dbexecutequery($sql);
    if ($error!='')
    {
        setUserMessage('There was a problem deleting the cron job.<br>'.$error,'error');
    } else {
        setUserMessage('Cron job has been successfully deleted','success');
    }
    redirect("?action=list");
  
  }elseif ($_GET['action']=='clearpending'){
    $sql="DELETE FROM core_cronexecution";
    $dbDelete=dbexecutequery($sql);
    $error=$dbDelete['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem clearing all pending jobs.<br>'.$error,'error');
    } else {
        setUserMessage('All future cron jobs have been successfully purged.','success');
    }
    redirect("?action=list");
  
  }elseif ($_GET['action']=='view'){
    
    $sql="SELECT * FROM core_cronexecution WHERE cronid=$cronid";
    $dbLog=dbselectmulti($sql);
    tableStart("<a href='?action=add'>Add new cron job</a>,<a href='?action=list'>Return to job list</a>","Scheduled Time,Actual Time",4);
    if ($dbLog['numrows']>0){
        foreach ($dbLog['data'] as $record) {
            $exectime=stripslashes($record['exectime']);
            $actualtime=stripslashes($record['actualtime']);
            $id=$record['id'];
            print "<tr><td>$exectime</td><td>$actualtime</td>";
            print "<td><a href='?action=viewlog&jobid=$id'>View Notes</a></td>";
            print "</tr>\n";
            }
    }
    tableEnd($dbLog);

  }elseif ($_GET['action']=='edit' || $_GET['action']=='add'){
    if ($_GET['action']=='edit'){  
        $sql="SELECT * FROM core_cron WHERE id=$cronid";
        $dbresult=dbselectsingle($sql);
        $rc=$dbresult['numrows'];
        $record=$dbresult['data'];
        $description=stripslashes($record['description']);
        $title=stripslashes($record['title']);
        $everyminutes=stripslashes($record['everyminutes']);
        $daily=stripslashes($record['dailytime']);
        $dailyhour=date("G",strtotime($daily));
        $dailyminute=date("i",strtotime($daily));
        $weekly=stripslashes($record['weeklytime']);
        $weeklyhour=date("G",strtotime($weekly));
        $weeklyminute=date("i",strtotime($weekly));
        $monthly=stripslashes($record['monthlytime']);
        $monthlyhour=date("G",strtotime($monthly));
        $monthlyminute=date("i",strtotime($monthly));
        $frequencytype=stripslashes($record['frequencytype']);
        $script=stripslashes($record['script']);
        $enabled=stripslashes($record['enabled']);
        $dailyday=stripslashes($record['dailyday']);
        $monthlyday=stripslashes($record['monthlyday']);
        $date_offset=stripslashes($record['date_offset']);
        $params=stripslashes($record['params']);
        $function=stripslashes($record['function']);
        $mon=stripslashes($record['mon']);
        $tue=stripslashes($record['tue']);
        $wed=stripslashes($record['wed']);
        $thu=stripslashes($record['thu']);
        $fri=stripslashes($record['fri']);
        $sat=stripslashes($record['sat']);
        $sun=stripslashes($record['sun']);
        $button="Update";
        $temp=stripslashes($record['startdate']);
        $startdate=date("Y-m-d",strtotime($temp));
        
    } else {
        $mon=1;
        $tue=1;
        $wed=1;
        $thu=1;
        $fri=1;
        $sat=1;
        $sun=1;
        $everyhour="0";
        $everyminute="00";
        $dailyhour="0";
        $dailyminute="00";
        $weeklyhour="0";
        $weeklyminute="00";
        $monthlyhour="0";
        $monthlyminute="00";
        $ampm="am";
        $enabled=1;
        $frequency=1;
        $frequencytype=1;
        $button="Add";
        $startdate=date("Y-m-d",strtotime('today'));
        $date_offset=0;
        $everyminutes=15;
        $dailyday=1;
        $monthlyday=1;
    }
    $minutetype=0;
    $dailytype=0;
    $weeklytype=0;
    $monthlytype=0;
    if ($frequencytype==1){$minutetype=1;}
    if ($frequencytype==2){$dailytype=1;}
    if ($frequencytype==3){$weeklytype=1;}
    if ($frequencytype==4){$monthlytype=1;}
    print "<form method=post>\n";
    make_checkbox('enabled',$enabled,'Active','Check to enable this cron job');
    make_text('title',$title,'Name','Name of the cron job',50);
    make_textarea('description',$description,'Description','',60,10);
    make_text('script',$script,'Script','What is the name of the script to be included from the cron jobs folder?',50);
    make_text('function',$function,'Function','What is the name of the function to call in the script?',50);
    make_text('params',$params,'Parameters','What parameters should be passed to the function?',50);
    make_number('date_offset',$date_offset,'Date offset','Offset for date if used by function. By default, dates are calculated as \'today\' (Ex. 1 equals tomorrow, 0 equals today, -1 equals yesterday)');
    make_date('startdate',$startdate,'Start date','What date should this job start?');
    
    print "<div class='label'>Run Schedule</div>";
    
    print "<div class='input'>";
    if ($minutetype){
        print "<input type=radio name='frequencytype' value='minute' checked></input>";
    }else{
        print "<input type=radio name='frequencytype' value='minute'></input>";
    }
    print 'Run every '.input_text('everyminutes',$everyminutes,5).' minutes(s)</div>';
    print '<div class="clear"></div>';
    
    print '<div class="label"></div><div class="input">';
    if ($dailytype){
        print "<input type=radio name='frequencytype' value='daily' checked></input>";
    }else{
        print "<input type=radio name='frequencytype' value='daily'></input>";
    }
    print 'Run every '.input_text('dailyday',$dailyday,5).' day(s) at';
    print input_select('dailyhours',$dailyhour,$GLOBALS['dailyhours']).":";
    print input_select('dailyminutes',$GLOBALS['dailyminutes'][$dailyminute],$GLOBALS['dailyminutes']);
    print '</div>';
    print '<div class="clear"></div>';
    
    
    print '<div class="label"></div><div class="input">';
    if ($weeklytype){
        print "<input type=radio name='frequencytype' value='weekly' checked></input>";
    }else{
        print "<input type=radio name='frequencytype' value='weekly'></input>";
    }
    print 'Run weekly (choose days below) at ';
    print input_select('weeklyhours',$weeklyhour,$GLOBALS['weeklyhours']).":";
    print input_select('weeklyminutes',$GLOBALS['weeklyminutes'][$weeklyminute],$GLOBALS['weeklyminutes']);
    print '<div>';
    print input_checkbox('mon',$mon);
    print ' Monday</div>';
    print '<div>';
    print input_checkbox('tue',$tue);
    print ' Tuesday</div>';
    print '<div>';
    print input_checkbox('wed',$wed);
    print ' Wednesday</div>';
    print '<div>';
    print input_checkbox('thu',$thu);
    print ' Thursday</div>';
    print '<div>';
    print input_checkbox('fri',$fri);
    print ' Friday</div>';
    print '<div>';
    print input_checkbox('sat',$sat);
    print ' Saturday</div>';
    print '<div>';
    print input_checkbox('sun',$sun);
    print ' Sunday</div>';
    print '</div>';
    print '<div class="clear"></div>';
    
    print '<div class="label"></div><div class="input">';
    if ($monthlytype){
        print "<input type=radio name='frequencytype' value='monthly' checked></input>";
    }else{
        print "<input type=radio name='frequencytype' value='monthly'></input>";
    }
    print 'Run once each month on day '.input_text('monthlyday',$monthlyday,5).' of the month at ';
    print input_select('monthlyhours',$monthlyhour,$GLOBALS['monthlyhours']).":";
    print input_select('monthlyminutes',$GLOBALS['monthlyminutes'][$monthlyminute],$GLOBALS['monthlyminutes']);
    print "<br /><small>You may use the word 'last' to specify the last day of the month.</small>";
    print '</div>';
    print "<div class='clear'></div>\n"; 
    make_submit('submit',$button,'Save job');
    make_hidden('cronid',$cronid);
    print "</form>\n";
  } else {
    $sql="SELECT cronLastCall, cronSystemEnabled FROM core_preferences";
    $dbLastCall=dbselectsingle($sql);
    $lastCall=$dbLastCall['data']['cronLastCall'];
    $systemEnabled=$dbLastCall['data']['cronSystemEnabled'];
    if($systemEnabled){$system="Cron system is enabled";}else{$system="Cron system has been disabled!";}
    $sql="SELECT * FROM core_cron";
    $dbCronJobs=dbselectmulti($sql);
    tableStart("<a href='?action=add'>Create new cron job</a>,<a href='?action=clearpending'>Clear all pending jobs</a>,<hr>,<b>$system</b>,System last ran at $lastCall","Cron job name,Last Successful Run,Next Scheduled Run",7);
    if ($dbCronJobs['numrows']>0){
        foreach ($dbCronJobs['data'] as $record) {
            $title=stripslashes($record['title']);
            $id=$record['id'];
            //get the last run and next scheduled run for this job
            $sql="SELECT MIN(exectime) as jtime FROM core_cronexecution WHERE status=0 AND cronid=$id";
            $dbNext=dbselectsingle($sql);
            $nexttime=$dbNext['data']['jtime'];
            $sql="SELECT MAX(exectime) as jtime FROM core_cronexecution WHERE status=1 AND cronid=$id";
            $dbLast=dbselectsingle($sql);
            $lasttime=$dbLast['data']['jtime'];
            
            print "<tr><td>$title</td><td>$lasttime</td><td>$nexttime</td>";
            print "<td><a href='?action=edit&cronid=$id'>Edit</a></td>";
            print "<td><a href='?action=run&cronid=$id'>Run job</a></td>";
            print "<td><a href='?action=view&cronid=$id'>View Log</a></td>";
            print "<td><a href='?action=delete&cronid=$id' class='delete'>Delete</a></td>";
            print "</tr>\n";
        }
    }
    tableEnd($dbCronJobs);
  }
    
    
}

  
function save_cron($action) {
    global $scriptpath;
    $id=$_POST['cronid'];
    $description=addslashes($_POST['description']);
    $title=addslashes($_POST['title']);
    $dailytime=($GLOBALS['dailyhours'][$_POST['dailyhours']].":".$GLOBALS['dailyminutes'][$_POST['dailyminutes']]);
    $weeklytime=($GLOBALS['weeklyhours'][$_POST['weeklyhours']].":".$GLOBALS['weeklyminutes'][$_POST['weeklyminutes']]);
    $monthlytime=($GLOBALS['monthlyhours'][$_POST['monthlyhours']].":".$GLOBALS['monthlyminutes'][$_POST['monthlyminutes']]);
    $dailyday=addslashes($_POST['dailyday']);
    if ($dailyday==''){$dailyday=0;}
    $monthlyday=addslashes($_POST['monthlyday']);
    if ($monthlyday==''){$monthlyday=0;}
    $everyminutes=addslashes($_POST['everyminutes']);
    if ($everyminutes==''){$everyminutes=0;}
    if ($dailytime==':'){$dailytime="0:00";}
    if ($weeklytime==':'){$weeklytime="0:00";}
    if ($monthlytime==':'){$monthlytime="0:00";}
    $frequencytype=addslashes($_POST['frequencytype']);
    if ($frequencytype=='minute'){$frequencytype=1;}
    if ($frequencytype=='daily'){$frequencytype=2;}
    if ($frequencytype=='weekly'){$frequencytype=3;}
    if ($frequencytype=='monthly'){$frequencytype=4;}
    if($_POST['mon']){$mon=1;}else{$mon=0;}
    if($_POST['tue']){$tue=1;}else{$tue=0;}
    if($_POST['wed']){$wed=1;}else{$wed=0;}
    if($_POST['thu']){$thu=1;}else{$thu=0;}
    if($_POST['fri']){$fri=1;}else{$fri=0;}
    if($_POST['sat']){$sat=1;}else{$sat=0;}
    if($_POST['sun']){$sun=1;}else{$sun=0;}
    $startdate=$_POST['startdate'];
    $script=addslashes($_POST['script']);
    $function=addslashes($_POST['function']);
    $params=addslashes($_POST['params']);
    $date_offset=addslashes($_POST['date_offset']);
    if($_POST['enabled']){$enabled=1;}else{$enabled=0;}
    if ($action=='insert'){
       $sql="INSERT INTO core_cron (date_offset, description, title, enabled, frequencytype, mon, tue, wed, thu, fri, sat, 
       sun, script, dailyday, dailytime, weeklytime, monthlyday, monthlytime, everyminutes, startdate, function, params) 
       VALUES('$date_offset', '$description', '$title', $enabled, $frequencytype, $mon, $tue, $wed, $thu, $fri, $sat, $sun, '$script', $dailyday, '$dailytime', 
       '$weeklytime', '$monthlyday', '$monthlytime', $everyminutes, '$startdate', '$function', '$params')";
       $dbresult=dbinsertquery($sql);
    } else {
       $sql="UPDATE core_cron SET date_offset='$date_offset', description='$description', title='$title', enabled=$enabled, params='$params', function='$function',
       script='$script', frequencytype=$frequencytype, mon=$mon, tue=$tue, wed=$wed, thu=$thu,
       fri=$fri, sat=$sat, sun=$sun, dailyday=$dailyday, dailytime='$dailytime', weeklytime='$weeklytime', monthlyday='$monthlyday',
       monthlytime='$monthlytime', everyminutes=$everyminutes, startdate='$startdate' WHERE id=$id";
       $dbresult=dbexecutequery($sql);
   }
   $error=$dbresult['error'];
   if ($error!='')
    {
        setUserMessage('There was a problem saving the cron job.<br>'.$error,'error');
    } else {
        setUserMessage('Cron job has been successfully saved','success');
    }
    redirect("?action=list");
    
}

footer();
?>