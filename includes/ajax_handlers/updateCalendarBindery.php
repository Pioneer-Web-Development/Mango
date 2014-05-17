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
  $sql="SELECT * FROM bindery_runs WHERE id=$jobid";
  $dbJob=dbselectsingle($sql);
  $startdatetime=$dbJob['data']['schedule_start'];
  $enddatetime=$dbJob['data']['schedule_stop'];
  $newstartdatetime=date("Y-m-d H:i:s",strtotime("$startdatetime +$totalMinutes minutes"));
  $newenddatetime=date("Y-m-d H:i:s",strtotime("$enddatetime +$totalMinutes minutes"));
  if($_POST['type']=='move')
  {
    //means we need to update starttime and endtime
    $sql="UPDATE bindery_runs SET schedule_start='$newstartdatetime', schedule_stop='$newenddatetime' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql);
    $json['sql']=$sql;
    if ($dbUpdate['error']!='')
    {
        $json['status']='error';
        $json['message']=$dbUpdate['error']."\nstart=$startdatetime\nend=$enddatetime\nDayDelta=$dayDelta\nMinuteDelta=$minuteDelta\nTotalMinutes=$totalMinutes";        
    } else {
        $json['status']='success';
    }
    echo json_encode($json);
  } elseif($_POST['type']=='resize')
  {
    $sql="UPDATE bindery_runs SET schedule_stop='$newenddatetime' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql);
    if ($dbUpdate['error']!='')
    {
        $json['status']='error';
        $json['message']=$dbUpdate['error']."\nstart=$startdatetime\nend=$enddatetime\nDayDelta=$dayDelta\nMinuteDelta=$minuteDelta\nTotalMinutes=$totalMinutes";        
    } else {
        $json['status']='success';
    }
    echo json_encode($json);
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
      
      $sql="INSERT INTO bindery_jobs (site_id, created_datetime, created_by) VALUES('$siteID', '$dnow','$userid')";
      $dbInsert=dbinsertquery($sql);
      $jobid=$dbInsert['insertid'];
      
      //insert the schedule run
      $sql="INSERT INTO bindery_runs (bindery_id, schedule_start, schedule_stop) VALUES ('$jobid', '$jsdate', '$jfdate')";
      $dbInsert=dbinsertquery($sql);
       
      if($dbInsert['error']=='')
      {
          $json['status']='success';
          $json['jobid']=$jobid; 
      } else {
          $json['status']='error';
          $json['message']=$dbInsert['error'];
          
      }
      echo json_encode($json);
       
  } elseif($_POST['type']=='delete')
  {
      //if there is only one run for this job, then delete the job as well
      $sql="SELECT bindery_id FROM bindery_runs WHERE id=$jobid";
      $dbBid=dbselectsingle($sql);
      $bid=$dbBid['data']['bindery_id'];
      $sql="SELECT * FROM bindery_runs WHERE bindery_id=$bid";
      $dbAll=dbselectmulti($sql);
      if($dbAll['numrows']==1)
      {
          //only one, delete the job
          $sql="DELETE FROM bindery_jobs WHERE id=$bid";
          $dbDelete=dbexecutequery($sql);
          $sql="DELETE FROM bindery_runs WHERE bindery_id=$bid";
          $dbDelete=dbexecutequery($sql);
          
      } else {
          //more than one, keep the job itself
          $sql="DELETE FROM bindery_runs WHERE id=$jobid";
          $dbDelete=dbexecutequery($sql);
          
      }
      if($dbDelete['error']=='') {
          $json['status']='success';
      } else {
          $json['status']='error';
          $json['message']=$dbDelete['error'];
      }
      echo json_encode($json);
        
  } elseif($_POST['type']=='drop')
  {
      //this handles moving an unscheduled job from below the calendar to the calendar
      $date=$_POST['date'];
      $pdate=date("Y-m-d H:i",strtotime($date));
      //date format Mon Dec 03 2012 16:00:00 GMT-0700 (Mountain Standard Time) 
      
      $jobid=$_POST['jobid'];
      $json['jobid']=$jobid;
      $json['drop_date']=$date;
      
      $date=explode(" ",$date); //convert fullcalendar format to sql datetime
      $newdate=$date[1].' '.$date[2].' '.$date[3].' '.$date[4];
      
      //get length of job so we can calculate run length
      $draw=$dbJob['data']['draw'];
      //get average stitcher speed
      $sql="SELECT AVERAGE(stitcher_speed) AS speed FROM stitchers";
      $dbSpeed=dbselectsingle($sql);
      $speed=$dbSpeed['data']['speed'];
      if($speed==0 || $speed=='')
      {
          $speed=5000;
      }
      $runtime=$draw/$speed; // in hours
      $runtime=ceil($runtime*60); // in minutes
      $runstart=date("Y-m-d H:i",strtotime($newdate));
      $json['parsed_newdate']=$runstart;
      $json['running_time']=$runtime;
      $runend=date("Y-m-d H:i",strtotime($runstart."+$runtime minutes"));
      
      
      $sql="INSERT INTO bindery_runs (bindery_id, schedule_start, schedule_stop) VALUES ('$jobid', '$runstart', '$runend')";
      $dbUpdate=dbexecutequery($sql);
      $json['sql']=$sql;
      if($dbUpdate['error']=='') {
        $json['status']="success";
      } else {
        $json['status']="error";
        $json['message']=$dbUpdate['error'];
      }
      echo json_encode($json);
  } elseif($_POST['type']=='unscheduled')
  {
      $year=$_POST['year'];
      $month=$_POST['month'];
      $date=$_POST['date'];
      $start=$year.'-'.$month.'-'.$date;
      $end=date("Y-m-d",strtotime($start."+7 days"));
      
      $sql="SELECT * FROM bindery_jobs WHERE request_startdate>='$start' AND request_startdate<='$end'";
      $dbJobs=dbselectmulti($sql);
      $json['status']='success';
      $json['sql']=$sql;
      if($dbJobs['numrows']>0)
      {
          $binderyJobs=array();
          foreach($dbJobs['data'] as $job)
          {
              $due=$job['bindery_duedate'];
              $datedue=date("w",strtotime($due));
              
              //get pub and run and draw
              $sql="SELECT * FROM publications WHERE id=$job[pub_id]";
              $dbPub=dbselectsingle($sql);
              $pub=$dbPub['data'];
              
              $sql="SELECT * FROM publications_runs WHERE id=$job[run_id]";
              $dbRun=dbselectsingle($sql);
              $run=$dbRun['data'];
              
              $draw=$job['draw'];
              
              $due=date("m/d/Y",strtotime($due));
              $title=stripslashes($pub['pub_name']).' - '.stripslashes($run['run_name'])."<br>Draw: $draw<br>Due: $due";
              
              $binderyJobs[]=array('id'=>$job['id'],'title'=>$title,'dateholder'=>"usDate_".$datedue);    
          }
          $json['jobs']=$binderyJobs;
      } else {
          $json['jobs']=array();
      }
      echo json_encode($json);
  }
  dbclose();
?> 