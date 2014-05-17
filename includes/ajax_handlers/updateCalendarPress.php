<?php
  session_start();
  include("../functions_db.php");
  include("../functions_common.php");
  include("../config.php");
  global $siteID, $sizes, $papertypes, $defaultPressID;
  $jobid=intval($_POST['jobid']);
  $dayDelta=intval($_POST['dayDelta']);
  //convert days to minutes
  $dayDelta=$dayDelta*24*60;
  $minuteDelta=intval($_POST['minuteDelta']);
  $totalMinutes=$dayDelta+$minuteDelta;
  
  //get current start/end for the job
  $sql="SELECT * FROM jobs WHERE id=$jobid";
  $dbJob=dbselectsingle($sql);
  $startdatetime=$dbJob['data']['startdatetime'];
  $enddatetime=$dbJob['data']['enddatetime'];
  
  $newstartdatetime=date("Y-m-d H:i:s",strtotime("$startdatetime +$totalMinutes minutes"));
  $newenddatetime=date("Y-m-d H:i:s",strtotime("$enddatetime +$totalMinutes minutes"));
  
  $json['dayDelta']=$dayDelta;
  $json['minuteDelta']=$minuteDelta;
  $json['totalMinues']=$totalMinutes;
  $json['oldstart']=$startdatetime;
  $json['oldend']=$enddatetime;
  $json['newstart']=$newstartdatetime;
  $json['newend']=$newenddatetime;
  $json['action']=$_POST['type'];
  if($_POST['type']=='move')
  {
    //means we need to update starttime and endtime
    $sql="UPDATE jobs SET startdatetime='$newstartdatetime', enddatetime='$newenddatetime' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql);
    $json['sql']=$sql;
    if ($dbUpdate['error']!='')
    {
        $json['status']='error';
        $json['message']=$dbUpdate['error'];        
    } else {
        $json['status']='success';
    }
    //clear any cached calendar files
    clearCache('presscalendar');
    
    echo json_encode($json);
    
  } elseif($_POST['type']=='resize')
  {
    $sql="UPDATE jobs SET enddatetime='$newenddatetime' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql);
    $json['sql']=$sql;
    if ($dbUpdate['error']!='')
    {
        $json['status']='error';
        $json['message']=$dbUpdate['error'];
    } else {
        $json['status']='success';
    }
    //clear any cached calendar files
    clearCache('presscalendar');
    
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
      
      $sql="INSERT INTO jobs (startdatetime,enddatetime, site_id, cover_date_due, cover_date_print, cover_date_output, page_release, page_rip, bindery_startdate, bindery_duedate, scheduled_time, colorset_time, drawset_time, updated_time, dataset_time, layoutset_time, created_time, created_by, folder_pin, slitter, folder, press_id) VALUES('$jsdate', '$jfdate', '$siteID', '$hdate', '$hdate',
      '$hdate','$hdate','$hdate','$hdate','$hdate','$hdate','$hdate','$hdate','$hdate','$hdate','$hdate','$dnow', '$userid', '$GLOBALS[pressDefaultFolderPin]', '$GLOBALS[pressDefaultSlitter]', '$GLOBALS[defaultFolder]','$defaultPressID')";
      $dbInsert=dbinsertquery($sql);
      $jobid=$dbInsert['insertid'];
      if($dbInsert['error']=='')
      {
          $json['status']='success';
          $json['jobid']=$jobid; 
      } else {
          $json['status']='error';
          $json['message']=$dbInsert['error'];
          
      }
      $sql="INSERT INTO job_stats (job_id, added_by) VALUES ($jobid, 'updateCalendarPress.php - ajax_handlers - line 91')";
      $dbStat=dbinsertquery($sql);
      if ($dbStat['error']!='')
      {
          $statsid=$dbStat['insertid'];
          $sql="UPDATE jobs SET stats_id='$statsid' WHERE id=$jobid";
          $dbUpdate=dbexecutequery($sql);
      }
      echo json_encode($json);
       
  } elseif($_POST['type']=='delete')
  {
      $sql="UPDATE jobs SET status=99 WHERE id=$jobid";
      $dbDelete=dbexecutequery($sql);
      $json['sql']=$sql;
      if ($dbDelete['error']=='')
      {
          $sql="DELETE FROM job_pages WHERE job_id=$jobid";
          //$dbDelete=dbexecutequery($sql);
          $sql="DELETE FROM job_plates WHERE job_id=$jobid";
          //$dbDelete=dbexecutequery($sql);
          $json['status']='success';
      } else {
          $json['status']='error';
          $json['message']=$dbDelete['error'];
      }
      //clear any cached calendar files
      clearCache('presscalendar');
    
      echo json_encode($json);
        
  } else if($_POST['type']=='drop')
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
      $runstart=date("Y-m-d H:i",strtotime($newdate));
      
      //get length of job so we can calculate run length
      $draw=$dbJob['data']['draw'];
      $runtime=$draw/($GLOBALS['pressSpeed']/60); //this should give us a number of minutes;
      $runtime=round($runtime,0);
      $runtime+=$GLOBALS['pressSetup'];
      $runend=date("Y-m-d H:i",strtotime($runstart."+$runtime minutes"));
      $sql="UPDATE jobs SET startdatetime='$runstart', enddatetime='$runend' WHERE id=$jobid";
      $dbUpdate=dbexecutequery($sql);
      
      $json['running_time']=$runtime;
      $json['sql']=$sql;
      $json['start']=$runstart;
      $json['end']=$runend;
      if($dbUpdate['error']=='') {
        $json['status']="success";
      } else {
        $json['status']="error";
        $json['message']=$dbUpdate['error'];
      }
      //clear any cached calendar files
      clearCache('presscalendar');
    
      echo json_encode($json);
  } else if($_POST['type']=='unscheduled')
  {
      $year=$_POST['year'];
      $month=$_POST['month'];
      $date=$_POST['date'];
      $start=$year.'-'.$month.'-'.$date;
      $end=date("Y-m-d",strtotime($start."+7 days"));
      
      $sql="SELECT * FROM jobs WHERE startdatetime IS Null AND request_printdate>='$start' AND request_printdate<='$end'";
      $dbJobs=dbselectmulti($sql);
      $json['status']='success';
      $json['sql']=$sql;
      if($dbJobs['numrows']>0)
      {
          $binderyJobs=array();
          foreach($dbJobs['data'] as $job)
          {
              $due=$job['request_printdate'];
              $datedue=date("w",strtotime($due));
              
              //get pub and run and draw
              $sql="SELECT * FROM publications WHERE id=$job[pub_id]";
              $dbPub=dbselectsingle($sql);
              $pub=$dbPub['data'];
              
              $sql="SELECT * FROM publications_runs WHERE id=$job[run_id]";
              $dbRun=dbselectsingle($sql);
              $run=$dbRun['data'];
              
              $draw=$job['draw'];
              $folder=$job['folder'];
            
              $due=date("m/d/Y",strtotime($due));
              $title=stripslashes($pub['pub_name']).' - '.stripslashes($run['run_name'])."<br>Draw: $draw<br>Request: $due";
              if($job['quarterfold']){$fold="Fold: quarter-fold";}else{$fold="Fold: half-fold";}
              if($job['stitch'] || $job['trim']){$stitch="Stitch &amp; Trim: Yes";}else{$stitch="Stitch &amp; Trim: No";}
              $sql="SELECT count(id) as pcount FROM job_pages WHERE version=1 AND job_id=$job[id]";
              $dbPages=dbselectsingle($sql);
              $pagecount=$dbPages['data']['pcount'];
              if($pagecount==0){$tpages="Total Pages: not set";}else{$tpages="Total pages: ".$pagecount;}
              if($job['rollSize']!=0)
              {
                $papersize="Roll Size: ".$sizes[$job['rollSize']];
                  
              } else {
                $papersize="Roll Size: not set";
                  
              }
              if($job['papertype']!=0)
              {
                $papertype="Paper Type: ".$papertypes[$job['papertype']];
                  
              } else {
                $papertype="Paper Type: not set";
                  
              }
              
            //get sections and types
            $sql="SELECT * FROM jobs_sections WHERE job_id=$job[id]";
            $dbSections=dbselectsingle($sql);
            if($dbSections['numrows']>0)
            {
                $ptypes=array();
                $scodes=array();
                $sections=$dbSections['data'];
                for($i=1;$i<=3;$i++)
                {
                    $rawpages=0;
                    $rawcolorpages=0;
                    $rawspotpages=0;
                    if($sections['section'.$i.'_used']==1)
                    {
                        $sectionformat=$sections['section'.$i.'_producttype'];
                        $sectioncode=$sections['section'.$i.'_code'];
                        $sectioncode=str_replace("0","",$sectioncode);
                        $sectioncode=str_replace(" ","",$sectioncode);
                        switch($sectionformat)
                        {
                            case 0:
                                if(!in_array('Bdsht',$ptypes)){$scodes[]=$sectioncode.'-Bdsht';$ptypes[]='Bdsht';}
                            break;
                            
                            case 1:
                                $broadsheetpages+=$rawpages/2;
                                $broadsheetcolorpages+=$rawcolorpages/2;
                                $broadsheetspotpages+=$rawspotpages/2;
                                if(!in_array('Tab',$ptypes)){$scodes[]=$sectioncode.'-Tab';$ptypes[]='Tab';}
                            break;
                            
                            case 2:
                                $broadsheetpages+=$rawpages/2;
                                $broadsheetcolorpages+=$rawcolorpages/2;
                                $broadsheetspotpages+=$rawspotpages/2;
                                if(!in_array('Tab',$ptypes)){$scodes[]=$sectioncode.'-Tab';$ptypes[]='Tab';}
                            break;
                            
                            case 3:
                                $broadsheetpages+=$rawpages/4;
                                $broadsheetcolorpages+=$rawcolorpages/4;
                                $broadsheetspotpages+=$rawspotpages/4;
                                if(!in_array('Flexi',$ptypes)){$scodes[]=$sectioncode.'-Flexi';$ptypes[]='Flexi';}
                            break;
                        }
                    }
                    
                    
                }
                if(count($scodes)>0){
                    $scodes=trim(implode(",",$scodes),',');
                } else {
                    $scodes='None set';
                }
            }
            
              $tooltip="Folder: $folder<br />".stripslashes($pub['pub_name']).' - '.stripslashes($run['run_name'])."<br>Draw: $draw<br />Pub Date: ".date("m/d/Y",strtotime($job['pub_date']));
              $tooltip.="<br>Request: $due<br />$fold<br />$papertype<br /><br />$papersize<br />$stitch<br />$tpages<br />Sections: $scodes<br />JobID: $job[id]";
                
              $binderyJobs[]=array('id'=>$job['id'],'title'=>$title,'tooltip'=>$tooltip,'dateholder'=>"usDate_".$datedue);    
          }
          $json['jobs']=$binderyJobs;
      } else {
          $json['jobs']=array();
      }
      
      echo json_encode($json);
  } else if($_POST['type']=='unschedule')
  {
      $rdate=$_POST['rdate'];
      if($rdate==''){$rdate=date("m-d-Y");}
      $rdate=date("Y-m-d",strtotime($rdate));
      $sql="UPDATE jobs SET startdatetime=NULL, enddatetime=NULL, request_printdate='$rdate' WHERE id=$jobid";
      $dbUpdate=dbexecutequery($sql);
      if($dbUpdate['error']=='')
      {
          $json['status']='success';
      } else {
          $json['status']='error';
          $json['sql']=$sql;
      }
      //clear any cached calendar files
      clearCache('presscalendar');
    
      echo json_encode($json); 
  }
  dbclose();