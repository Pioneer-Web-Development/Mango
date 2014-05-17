<?php
error_reporting(E_ERROR);
set_time_limit(3600);

include ("includes/functions_db.php");
include ("includes/mail/htmlMimeMail.php");
include ("includes/functions_common.php");
include ("includes/config.php");


$info=''; 

if ($GLOBALS['cronSystemEnabled']){ //if system is enabled, do all this stuff, otherwise... do nothing
    //now delete all cron execution tasks older than 192 hours, thats a complete list of all executed jobs in the past 8 days
    $sysRoot=trim(getcwd());
    $phpself=$_SERVER['PHP_SELF'];
    $sysRoot=str_replace("cronprocessor.php","",$phpself);
    if($sysRoot=='/'){print "fixing...";$sysRoot='';}else{print "Wtf? **$sysRoot** -- ";}
    $oldtime=date("Y-m-d",strtotime("-8 days"));
    $currenttime=date("H:i:s");
    $currentdate=date("Y-m-d",time());       
    $currenttimestamp=date("Y-m-d H:i:s",time());
    
    $sql="UPDATE core_preferences SET cronLastCall='$currenttimestamp'";
    $dbUpdate=dbexecutequery($sql);
    
    $futuredate=date("Y-m-d",strtotime("+48 hours"));
    
    $sql="DELETE FROM core_cronexecution WHERE exectime<'$oldtime'";
    $dbresult=dbexecutequery($sql);
    
    //now, grab a list of all jobs in the cronexecution table that are in the past and have not been executed
    $sql="SELECT * FROM core_cronexecution WHERE exectime<'$currenttimestamp' AND (status=0 OR status=2)";
    //print $sql."<br>";
    
    $dbCronJobs=dbselectmulti($sql);
    print "Need to run a total of $dbCronJobs[numrows] jobs...<br />From doc root of --".$sysRoot."--<br>";
    //now loop through and fire all events
    if ($dbCronJobs['numrows']>0){
        clearstatcache();
        foreach($dbCronJobs['data'] as $job){
            $sql="SELECT * FROM core_cron WHERE id=$job[cronid] and enabled=1";
            $dbCron=dbselectsingle($sql);
            $cronjob=$dbCron['data'];
            $script=stripslashes($cronjob['script']);
            $scriptFilename=end(explode("/",stripslashes($cronjob['script'])));
            $function=stripslashes($cronjob['function']);
            $params=stripslashes($cronjob['params']);
            $rundate=$currentdate." ".$exectime;
            if (strpos($script,"ttp://")>0 || substr($script,0,3)=="../" || substr($script,0,1)=="/")
            {
                //lets leave these alone as they should be either calls to an http url, or a relative url from the root
                if(substr($script,0,1)=="/")
                {
                   $script= $_SERVER{'DOCUMENT_ROOT'}.$script;
                }
            } else {
                $script=$sysRoot.'cronjobs/'.$script;
            }
            print "Now executing $script...<br />";
            
            if (file_exists($script))
            {
              
                print "Processing...<br>";
                
                include($script);     
                
                $notes="Called in $script, and executed $function with parameters of $params<br>\n";
                print $notes;
                if ($function!='')
                {
                    $function($params);                  
                }
                
                //now update the cronexecution table
                global $info;
                if ($info!=''){
                    $info=$notes."<br>".$info;
                }
                global $notes;
                if ($notes!=''){
                    $info.="<br>".$notes;
                }
                if (strip_tags($info)=='')
                {
                    $info="No notes for this job<br>";
                }
                $status=1;
                $time="actualtime='$currenttimestamp',";
            } else {
                print "Unable to locate this job file.<br>";
                $info="This job did not run because the script file - $script was not found. SysRoot is $sysRoot. phpSelf is $phpself";
                $time='';
                $status=2;
            }
            $info=addslashes($info);
            $sql="UPDATE core_cronexecution SET $time status=$status, jobnotes='$info' WHERE id=$job[id]";
            $dbUpdate=dbexecutequery($sql);
            print "This was the cron update: ".$dbUpdate['error'];
            print "<ul>$job[title] executing $script";
            print $info;
            print "</ul>";
            //now clear the info and repeat as needed
            $GLOBALS['info']='';

        }
    }

    //now we need to build new entries in cronexecute for all repeating or future jobs
    //we'll always look ahead 48 hours to schedule jobs
    //first, we grab the basic info from the cron table
    $inserting=array();
    $sql="SELECT * FROM core_cron WHERE enabled=1";
    $dbCron=dbselectmulti($sql);
    if ($dbCron['numrows']>0)
    {
        $cronjobs=$dbCron['data'];
        foreach($cronjobs as $job){
           /* notes
            frequencytype: 1= minutes, 2=daily, 3=weekly, 4=monthly
            1 uses everyminutes to store how often to execute script
            2 uses dailyday to specify how many days to skip before running again
                   dailytime to specify time to run on each day specified
            3 uses weekly time to specify time to run on each day that is selected (mon, tue, wed, thu, fri, sat, sun as boolean)
            4 uses monthlyday to specify which day of the month, 'last' can be an option here
                   monthlytime to specify which time of day to run
           */
           $frequencytype=$job['frequencytype'];
           print "working on $job[title] with frequency of $frequencytype ...<br />";
           switch ($frequencytype) {
            case 1:
                $sql="SELECT max(exectime) as startdate FROM core_cronexecution WHERE cronid=$job[id]";
                $dbMax=dbselectsingle($sql);
                if ($dbMax['data']['startdate']!='0000-00-00 00:00:00'){
                    $startdate=$dbMax['data']['startdate'];
                }else{
                    $startdate=$currenttimestamp;
                }
                if ($startdate>$futuredate){
                    //do nothing
                }else{
                    while ($startdate<=$futuredate){
                        $jobtime=$job['everyminutes'];
                        $startdate=date("Y-m-d G:i",strtotime("$startdate + $jobtime minutes"));
                        //means we have a valid day and time, lets add it to the cronexecution table
                        $sql="INSERT INTO core_cronexecution (cronid, exectime) VALUES ($job[id],'$startdate')";
                        $dbInsert=dbinsertquery($sql);
                        if ($dbInsert['error']==''){
                            $inserting[]="Inserted a new job for cronid=$job[id] at $startdate";
                        } else {
                            $inserting[]=$dbInsert['error'];
                        }
                        
                    }
                }
                break;
            case 2:
                $sql="SELECT max(exectime) as startdate FROM core_cronexecution WHERE cronid=$job[id]";
                $dbMax=dbselectsingle($sql);
                if ($dbMax['data']['startdate']!='0000-00-00 00:00:00'){
                    $startdate=$dbMax['data']['startdate'];
                }else{
                    $startdate=$currenttimestamp;
                }
                if ($starddate>$futuredate){
                    print "Starting date is greating that the future date to $futuredate";
                }else{
                    while ($startdate<=$futuredate){
                        $skipdays=$job['dailyday'];
                        if ($skipdays==0){$skipdays=1;}
                        if ($skipdays=="1"){$skipdays.=" day";}else{$skipdays.=" days";}
                        $startdate=date("Y-m-d",strtotime("$startdate + $skipdays"));
                        $jobtime=$job['dailytime'];
                        $startdate.=" $jobtime";
                            //means we have a valid day and time, lets add it to the cronexecution table
                        $sql="INSERT INTO core_cronexecution (cronid, exectime) VALUES ($job[id],'$startdate')";
                        $dbInsert=dbinsertquery($sql);
                        if ($dbInsert['error']==''){
                            $inserting[]="Inserted a new job for cronid=$job[id] at $startdate";
                        } else {
                            $inserting[]=$dbInsert['error'];
                        }
                    }
                }
                break;
            case 3:
            //weekly job, will need to check which days are selected
            //first, find the greatest time in the cronexecution table for this cron id
            
                $sql="SELECT max(exectime) as startdate FROM core_cronexecution WHERE cronid=$job[id]";
                $dbMax=dbselectsingle($sql);
                if ($dbMax['data']['startdate']!='0000-00-00 00:00:00'){
                    $startdate=$dbMax['data']['startdate'];
                }else{
                    $startdate=$currenttimestamp;
                }
                //we loop in 5 minute increments from the $startdate to the $futuredate
                if ($starddate>$futuredate){
                    //do nothing
                }else{
                    while ($startdate<=$futuredate){
                        $startdate=date("Y-m-d",strtotime("$startdate + 1 day"));
                        $jobtime=$job['weeklytime'];
                        $startdate.=" $jobtime";
                        //now we have a date and time, lets see what day it is
                        $day=strtolower(date("D",strtotime($startdate)));
                        if ($job[$day]){
                            //means we have a valid day and time, lets add it to the cronexecution table
                            $sql="INSERT INTO core_cronexecution (cronid, exectime) VALUES ($job[id],'$startdate')";
                            $dbInsert=dbinsertquery($sql);
                            if ($dbInsert['error']==''){
                            $inserting[]="Inserted a new job for cronid=$job[id] at $startdate";
                            } else {
                                $inserting[]=$dbInsert['error'];
                            }
                        }
                    
                    }
                }
            
               
                break;
            case 4:
            //monthly job
                $sql="SELECT max(exectime) as startdate FROM core_cronexecution WHERE cronid=$job[id]";
                $dbMax=dbselectsingle($sql);
                if ($dbMax['data']['startdate']!='0000-00-00 00:00:00'){
                    $startdate=$dbMax['data']['startdate'];
                }else{
                    $startdate=$currenttimestamp;
                }
                if ($starddate>$futuredate){
                    //do nothing
                }else{
                    $monthday=$job['monthlyday'];
                    $startdate=date("Y-m-d",strtotime("$startdate + 1 month"));
                    $jobtime=$job['monthlytime'];
                    $test=explode("-",$startdate);
                    $month=$test[1];
                    $year=$test[0];
                    $day=$test[2];
                    $lastday=getLastDayOfMonth($month,$day,$year);
                    if (strtolower($monthday)=='last'){
                        $startdate=date("Y-m-d", mktime(0,0,0,$month,$lastday,$year));
                    }else{
                        $startdate=date("Y-m-d", mktime(0,0,0,$month,$monthday,$year));
                    }
                    $startdate.=" $jobtime";
                    $sql="INSERT INTO core_cronexecution (cronid, exectime) VALUES ($job[id],'$startdate')";
                    $dbInsert=dbinsertquery($sql);
                    if ($dbInsert['error']==''){
                            $inserting[]="Inserted a new job for cronid=$job[id] at $startdate";
                    } else {
                        $inserting[]=$dbInsert['error'];
                    }
                    
                }
                break;    
            }
            
        }
        print "<ul>insert jobs into database";
        //print_info($inserting);
        print "</ul>";
        //now clear the info and repeat as needed
        

        
    }
} else {
    print "The cron system is disabled right now.";

}


function getLastDayOfMonth($month,$day,$year) {
    $go=true;
    while ($go){
        $day++;
        if (checkdate($month,$day,$year)){
        //continue
        } else {
           $go=false;
           $day=$day-1;
        }


    }
    return $day;
}

    
dbclose();
?>