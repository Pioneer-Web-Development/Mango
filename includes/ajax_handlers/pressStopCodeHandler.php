<?php
  include("../functions_db.php");
  
  $jobid=$_POST['jobid'];
  $stopid=$_POST['stopid'];
  $stopinfo=$_POST['stopinfo'];
  $type=$_POST['type'];
  $stopnotes=addslashes($_POST['stopnotes']);
  $dtime=date("Y-m-d H:i:s");
  $time=strtotime($dtime);
  
  if ($type=='jobnotes')
  {
      $sql="UPDATE jobs SET notes_press='$stopnotes' WHERE id=$jobid";
      $dbUpdate=dbexecutequery($sql);
  } else {
      //get stop time
      $sql="SELECT * FROM job_stops WHERE id=$stopid";
      $dbStop=dbselectsingle($sql,true);
      $stoptime=strtotime($dbStop['data']['stop_datetime']);
      $downtime=$time-$stoptime; //now we have downtime in seconds
      $downtime=$downtime/60;
      
      $ssql="UPDATE job_stops SET stop_restartdatetime='$dtime', stop_downtime='$downtime', stop_info='$stopinfo', stop_notes='$stopnotes' WHERE id=$stopid";
      $dbStopUpdate=dbexecutequery($ssql);
      
      
      //now update the total job downtime in job_stats
      $jsql="UPDATE job_stats SET total_downtime=total_downtime+$downtime WHERE job_id=$jobid";
      $dbJobUpdate=dbexecutequery($jsql);
  }
  
  print "Jobid = $jobid<br>";
  print "Stop id= $stopid<br>";
  print "stoptime is $stoptime<br>";
  print "restart time is $dtime<br>";
  print "downtime is $downtime<br>";
  print "Job stop update sql is $ssql<br>";
  print "Job update sql is $jsql<br>";
  
  dbclose();
  
  
?>
