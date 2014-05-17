<?php
  include('../includes/functions_db.php');
  
  $sql="SELECT * FROM jobs WHERE startdatetime>='2013-03-01' AND insert_source='autorecurring' ORDER BY id";
  $dbJobs=dbselectmulti($sql);
  foreach($dbJobs['data'] as $job)
  {
      //ok, lets see if there is another job with the same recurring id for the same day
      $recurringid=$job['recurring_id'];
      $pubdate=$job['pub_date'];
      $id=$job['id'];
      $sql="SELECT * FROM jobs WHERE pub_date='$pubdate' AND recurring_id='$recurringid' AND id>$id"; //look for jobs with a larger id number only
      $dbCheck=dbselectmulti($sql);
      if($dbCheck['numrows']>0)
      {
         foreach($dbCheck['data'] as $c)
         {
             print "Recurring job created on $c[created_time]<br>";
             $ids.=$c['id'].',';
         }    
      }
  }
  if(trim($ids)!='')
  {
      $ids=substr($ids,0,strlen($ids)-1);
      print "Deleted jobs with the following ids:<br>$ids";
      $sql="DELETE FROM jobs WHERE id IN ($ids)";
      $dbDelete=dbexecutequery($sql);
      //stats too!
      $sql="DELETE FROM jobs_stats WHERE job_id IN ($ids)";
      $dbDeleteStats=dbexecutequery($sql);
  }
  dbclose();
?>
