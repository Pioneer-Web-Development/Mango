<?php
  session_start();
  include("../functions_db.php");
  include("../config.php");
  global $siteID;
  $jobid=intval($_POST['jobid']);
  $dayDelta=intval($_POST['dayDelta']);
  //convert days to minutes
  $dayDelta=$dayDelta*24*60;
  $minuteDelta=intval($_POST['minuteDelta']);
  $totalMinutes=$dayDelta+$minuteDelta;
  
  //get current start/end for the job
  $sql="SELECT * FROM bindery_jobs WHERE id=$jobid";
  $dbJob=dbselectsingle($sql);
  $startdatetime=$dbJob['data']['bindery_startdate'];
  $enddatetime=$dbJob['data']['bindery_stopdate'];
  $newstartdatetime=date("Y-m-d H:i:s",strtotime("$startdatetime +$totalMinutes minutes"));
  $newenddatetime=date("Y-m-d H:i:s",strtotime("$enddatetime +$totalMinutes minutes"));
  if($_POST['type']=='move')
  {
    //means we need to update starttime and endtime
    $sql="UPDATE bindery_jobs SET bindery_startdate='$newstartdatetime', bindery_stopdate='$newenddatetime' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql);
    if ($dbUpdate['error']!='')
    {
      print str_replace("<br />","\n",$dbUpdate['error'])."\nstart=$startdatetime\nend=$enddatetime\nDayDelta=$dayDelta\nMinuteDelta=$minuteDelta\nTotalMinutes=$totalMinutes";        
    }
  } elseif($_POST['type']=='resize')
  {
    $sql="UPDATE bindery_jobs SET bindery_stopdate='$newenddatetime' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql);
    if ($dbUpdate['error']!='')
    {
      print str_replace("<br />","\n",$dbUpdate['error'])."\nstart=$startdatetime\nend=$enddatetime\nDayDelta=$dayDelta\nMinuteDelta=$minuteDelta\nTotalMinutes=$totalMinutes";        
    } else {
        //print $newenddatetime.' start:'.$starddatetime.' end:'.$enddatetime.' jobid:'.$jobid;
    }
  } elseif($_POST['type']=='add')
  {
      $temp=$_POST['date'];
      $temp=explode(" GMT",$temp);
      $temp=$temp[0];
      $jsdate=date("Y-m-d H:i",strtotime($temp));
      $jfdate=date("Y-m-d H:i",strtotime($jsdate."+30 minutes"));
      if(isset($_SESSION['cmsuser']['userid']))
      {
          $userid=$_SESSION['cmsuser']['userid'];
      } else {
          $userid=0;
      }
      if($userid==''){$userid=0;}
      $dnow=date("Y-m-d H:i:s");
      $hdate=date("Y-m-d H:i",strtotime($temp."-18 hours"));
      
      $sql="INSERT INTO bindery_jobs (bindery_startdate,bindery_stopdate, site_id, created_time, created_by) VALUES('$jsdate', '$jfdate', '$siteID', '$dnow','$userid')";
      $dbInsert=dbinsertquery($sql);
      $jobid=$dbInsert['insertid'];
      if($dbInsert['error']=='')
      {
          print "success|".$jobid;
      } else {
          print "error|".$dbInsert['error'];
      }
       
  } elseif($_POST['type']=='delete')
  {
      $sql="DELETE FROM bindery_jobs WHERE id=$jobid";
      $dbDelete=dbexecutequery($sql);
      if($dbDelete['error']=='') {
        print "success";
      } else {
        print $dbDelete['error'];
      }  
  } elseif($_POST['type']=='drop')
  {
      //this handles moving an unscheduled job from below the calendar to the calendar
      $date=$_POST['date'];
      $jobid=$_POST['jobid'];
      //date format Mon Dec 03 2012 16:00:00 GMT-0700 (Mountain Standard Time) 
      $date=strtotime($date);
      //get length of job so we can calculate run length
      $draw=$dbJob['data']['draw'];
      //get average stitcher speed
      $sql="SELECT AVERAGE(stitcher_speed) FROM stitchers";
      $dbSpeed=dbselectsingle($sql);
      $speed=$dbSpeed['data'];
      if($speed==0 || $speed=='')
      {
          $speed=5000;
      }
      $runtime=$draw/$speed; // in hours
      $runtime=ceil($runtime*60); // in minutes
      $runstart=$date("Y-m-d H:i",$date);
      $runend=$date("Y-m-d H:i",$date."+$runtime minutes");
      $sql="UPDATE bindery_jobs SET bindery_startdate='$runstart', bindery_stopdate='$runend' WHERE id=$jobid";
      $dbUpdate=dbexecutequery($sql);
      
  }
  dbclose();
?>
