<?php
  session_start();
  include("../functions_db.php");
  include("../config.php");
  include("../functions_common.php");
  //save for press data
  $stype=$_POST['type'];
  $source=$_POST['source'];
  $value=$_POST['value'];
  $jobid=$_POST['jobid'];
  global $siteID;
  //check for a non-number benchmark, in which case we are dealing with a static benchmark
  if ($stype=='benchmark' && !is_numeric($source))
  {
    $stype='stat';
  }
  //lookup job info
  //lets automatically create a stat record if one does not exist for this job
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    $statsid=$job['stats_id'];
    $log='found a stat id of '.$statsid.' for job id '.$jobid; 
    $folder=$job['folder'];
    if($statsid==0)
    {
        //see first if it can be found in job_stats by job id
        $sql="SELECT * FROM job_stats WHERE job_id=$jobid";
        $dbStats=dbselectsingle($sql);
        if($dbStats['numrows']>0)
        {
            $statsid=$dbStats['data']['id'];
        } else {
           //creating a new stat record
            $sql="INSERT INTO job_stats (job_id, folder, site_id, added_by) VALUES ($jobid, $folder,$siteID, 'jobmonitorPress.php from ajax_handlers - line 28')";
            $dbInsert=dbinsertquery($sql);
            $statsid=$dbInsert['numrows'];
        }
        $sql="UPDATE jobs SET stats_id='$statsid' WHERE id=$jobid";
        $dbUpdate=dbexecutequery($sql);
    }
            
  switch ($stype)
  {
        case 'checklist':
            //see if we're updating an existing checklist item for a job, or do we need to create one
            $sql="SELECT * FROM job_checklist WHERE job_id=$jobid AND checklist_id=$source";
            $dbCheck=dbselectsingle($sql);
            if ($dbCheck['numrows']>0)
            {
                //ok, it exists, we'll just update it
                $sql="UPDATE job_checklist SET checklist_value=$value WHERE id=".$dbCheck['data']['id'];
                $dbCheck=dbexecutequery($sql);
                $error=$dbCheck['error']; 
            } else {
                //adding a new check
                $sql="INSERT INTO job_checklist (job_id, checklist_id, checklist_value) VALUES ('$jobid', '$source', '$value')";
                $dbCheck=dbinsertquery($sql);
                $error=$dbCheck['error']; 
            }
            if ($error=='')
            {
                print "success|";
            } else {
                print "error|$error";
            }
        break;
    
        case 'crew':
              //get stats id
              $sql="SELECT A.id, A.job_pressman_ids, A.job_pressman_count FROM job_stats A, jobs B WHERE A.id=B.stats_id AND B.id=$jobid";
              $pressmanid=$source;
              $dbStats=dbselectsingle($sql);
              $stats=$dbStats['data'];
              $count=$stats['job_pressman_count'];
              $statid=$stats['id'];
              $ids=$stats['job_pressman_ids'];
              
              if ($value=='1')
              {
                  $ids.=$pressmanid."|";
                  $count++;
                  $sql="UPDATE job_stats SET job_pressman_ids='$ids', job_pressman_count=$count WHERE id=$statid";
                  $dbUpdate=dbexecutequery($sql);
                  $error=$dbUpdate['error'];
              } else {
                  $pressmanids=explode("|",$ids);
                  $newids='';
                  foreach($pressmanids as $key=>$value)
                  {
                      if($value!='')
                      {
                         $newids.="p$value|"; 
                      }
                      
                  }
                  $newids=str_replace("p$pressmanid|","",$newids);
                  $newids=str_replace("p","",$newids);
                  $count--;
                  $sql="UPDATE job_stats SET job_pressman_ids='$newids', job_pressman_count=$count WHERE id=$statid";
                  $dbUpdate=dbexecutequery($sql);
                  $error=$dbUpdate['error'];
              }
              if ($error=='')
                {
                    print "success|";
                } else {
                    print "error|Passed $jobid as jobid and $source as pressman id<br>$error";
                }
        break;
    
        case 'updatedraw':
            $value=$_POST['draw'];
            if($value==''){$value=0;}
            if (isset($_SESSION['cmsuser']))
            {
                $by=$_SESSION['cmsuser']['userid'];
            } else {
                $by=$GLOBALS['defaultPressOperator'];
            }
            $time=date("Y-m-d H:i:s");
            $sql="UPDATE jobs SET draw='$value', updated_by='$by', updated_time='$time' WHERE id=$jobid";
            $dbUpdate=dbexecutequery($sql);
            $error=$dbUpdate['error'];
            if ($error=='')
            {
                print "success|";
            } else {
                print "error|$error";
            } 
        break;
        
        case 'page':
            $time=date("Y-m-d")." ".$value;
            $sql="UPDATE job_pages SET page_release='$time' WHERE id=$source";
            $dbUpdate=dbexecutequery($sql);
            $error=$dbUpdate['error']; 
        break;
        
        
        case 'plateapprove':
            $time=date("Y-m-d")." ".$value;
            $sql="UPDATE job_plates SET plate_approval='$time' WHERE id=$source";
            $dbUpdate=dbexecutequery($sql);
            $error=$dbUpdate['error'];
            if ($error=='')
            {
                print "success|";
            } else {
                print "error|$error";
            } 
        break;
        
        case 'platereceivek':
            $time=date("Y-m-d H:i:s");
            $sql="UPDATE job_plates SET black_receive='$time' WHERE id=$source";
            $dbUpdate=dbexecutequery($sql);
            $error=$dbUpdate['error'];
            if ($error=='')
            {
                print "success|";
            } else {
                print "error|$error";
            } 
        break;
        
        case 'platereceivec':
            $time=date("Y-m-d H:i:s");
            $sql="UPDATE job_plates SET cyan_receive='$time' WHERE id=$source";
            $dbUpdate=dbexecutequery($sql);
            $error=$dbUpdate['error'];
            if ($error=='')
            {
                print "success|";
            } else {
                print "error|$error";
            }
        break;
        
        case 'platereceivem':
            $time=date("Y-m-d H:i:s");
            $sql="UPDATE job_plates SET magenta_receive='$time' WHERE id=$source";
            $dbUpdate=dbexecutequery($sql);
            $error=$dbUpdate['error'];
            if ($error=='')
            {
                print "success|";
            } else {
                print "error|$error";
            } 
        break;
        
        case 'platereceivey':
            $time=date("Y-m-d H:i:s");
            $sql="UPDATE job_plates SET yellow_receive='$time' WHERE id=$source";
            $dbUpdate=dbexecutequery($sql);
            $error=$dbUpdate['error'];
            if ($error=='')
            {
                print "success|";
            } else {
                print "error|$error";
            } 
        break;
        
        case 'platereceiveall':
            $time=date("Y-m-d H:i:s");
            $sql="UPDATE job_plates SET black_receive='$time',cyan_receive='$time',magenta_receive='$time',yellow_receive='$time' WHERE id=$source";
            $dbUpdate=dbexecutequery($sql);
            $error=$dbUpdate['error'];
            if ($error=='')
            {
                print "success|";
            } else {
                print "error|$error";
            } 
        break;
        
        case 'checklistOperator':
            $sql="UPDATE job_stats SET checklist_approved='$value' WHERE job_id=$jobid";
            $dbUpdate=dbexecutequery($sql);
            $error=$dbUpdate['error'];
            if ($error=='')
            {
                print "success|";
            } else {
                print "error|$error";
            } 
        break;
    
        case 'jobOperator':
            $sql="UPDATE job_stats SET job_pressoperator='$value' WHERE id=$statsid";
            $dbUpdate=dbexecutequery($sql);
            $error=$dbUpdate['error'];
            if ($error=='')
            {
                print "success|$sql";
            } else {
                print "error|$error";
            } 
        break;
    
        case 'benchmark':
            //see if we're updating an existing benchmark item for a job, or do we need to create one
            //adding a new check
            //we'll need to gather the info about the benchmark from run_benchmarks and benchmarks so we can store them in job_benchmarks
            //need to get the pubday first
            $sql="SELECT startdatetime, enddatetime, pub_date, run_id, stats_id FROM jobs WHERE id=$jobid";
            $dbJob=dbselectsingle($sql);
            $pubday=strtolower(date("l",strtotime($dbJob['data']['pub_date'])));
            $runid=$dbJob['data']['run_id'];
            $statsid=$dbJob['data']['stats_id'];
            $bsql="SELECT A.benchmark_type, B.$pubday FROM benchmarks A, run_benchmarks B WHERE B.run_id=$runid AND B.benchmark_id=A.id AND A.id=$source"; 
            $dbBenchInfo=dbselectsingle($bsql);
            $type=$dbBenchInfo['data']['benchmark_type'];
            $goal=$dbBenchInfo['data'][$pubday];
            
            //ok, here goes the fun part
            if ($type=='time')
            {
                $startdate=date("Y-m-d",strtotime($dbJob['data']['startdatetime']));
                $enddate=date("Y-m-d",strtotime($dbJob['data']['enddatetime']));
                
                $tvalue=strtotime($enddate." ".$value);
                $tgoal=strtotime($startdate." ".$goal);
                
                //check for common sense... if hour of goal is <6 and hour of value >20, we probably have an issue
                $testv=date("H",$tvalue);
                $testg=date("H",$tgoal);
                if ($testg<6 && $testv>=20)
                {
                    //we have an issue, lets subtract a day from the value
                    $newend=date("Y-m-d",strtotime("-1 day",strtotime($enddate)));
                    $tvalue=strtotime($newend." ".$value);
                }
                
                
                $difference=$tvalue-$tgoal;
                $tvalue=date("Y-m-d H:i",$tvalue);
                $tgoal=date("Y-m-d H:i",$tgoal);
            } else {
                $difference=$value-$goal;
                $tvalue=$value;
                $tgoal=$goal;
            }
            
            
            $exsql="SELECT * FROM job_benchmarks WHERE job_id=$jobid AND benchmark_id=$source";
            $dbBench=dbselectsingle($exsql);
            if ($dbBench['numrows']>0)
            {
                //ok, it exists, we'll just update it
                if ($type=='time')
                {
                    $sql="UPDATE job_benchmarks SET benchmark_actual_time='$tvalue', benchmark_difference='$difference' WHERE id=".$dbBench['data']['id'];
                    $dbBench=dbexecutequery($sql);
                    $error=$dbBench['error']; 
                } else {
                    $sql="UPDATE job_benchmarks SET benchmark_actual_number='$tvalue', benchmark_difference='$difference' WHERE id=".$dbBench['data']['id'];
                    $dbBench=dbexecutequery($sql);
                    $error=$dbBench['error']; 
                }
                if ($error=='')
                {
                    print "success|";
                } else {
                    print "error|$error";
                }
            } else {
                if ($type=='time')
                {
                    $sql="INSERT INTO job_benchmarks (job_id, benchmark_id, benchmark_type, benchmark_goal_time, benchmark_actual_time, benchmark_difference)
                     VALUES ('$jobid', '$source', '$type', '$tgoal', '$tvalue', '$difference')";
                    $dbBench=dbinsertquery($sql);
                    $error=$dbBench['error']; 
                } else {
                    $sql="INSERT INTO job_benchmarks (job_id, benchmark_id, benchmark_type, benchmark_goal_number, benchmark_actual_number, benchmark_difference)
                     VALUES ('$jobid', '$source', '$type', '$tgoal', '$tvalue', '$difference')";
                    $dbBench=dbinsertquery($sql);
                    $error=$dbBench['error']; 
                }
                if ($error=='')
                {
                    print "success|";
                } else {
                    print "error|$error";
                }
            }
            
        break;
        
        case "recalcstats":
            press_stats($statsid,$jobid);
        break;
           
        case "stat":
            $sql="SELECT * FROM job_stats WHERE id=$statsid";
            $dbStats=dbselectsingle($sql);
            $stats=$dbStats['data'];
            //we'll switch based on the 'source', which in this case is the item, like starttime
            switch ($source)
            {
                case "starttime":
                    //we'll look at the scheduled start time
                    $sstart=$job['startdatetime'];
                    $date=date("Y-m-d");
                    $nowtime=date("H:i:s");
                    if($_POST['value']!='')
                    {
                        $nowtime=$_POST['value'];
                    }
                    if ($updating)
                    {
                        //make sure we dont have a start time after a stop time
                        $stop=strtotime($dbStats['data']['stopdatetime_actual']);
                        $tdate=strtotime($date." ".$nowtime);
                        //if ($stop<$tdate){print "<br>Stop time earlier than start!";}
                    }
                    
                    $sql="UPDATE job_stats set startdatetime_goal='$sstart', startdatetime_actual='$date $nowtime' WHERE id=$statsid";
                    $dbUpdate=dbexecutequery($sql);
                    $error=$dbUpdate['error'];
                    if($error=='')
                    {
                        print "success|$nowtime|$log";
                    } else {
                        print "error";
                    }
                    checkTextAlerts($jobid,'start');
                break;
                
                case "stoptime":
                    //we'll look at the scheduled stop time, but first need to see if we have a continuer
                    $send=$job['enddatetime'];
                    $date=date("Y-m-d");
                    $nowtime=date("H:i:s");
                    $start=strtotime($dbStats['data']['startdatetime_actual']);
                    $tdate=strtotime($date." ".$value);
                    //if ($start>$tdate){print "<br>Start time > stop time!";}
                    $sql="UPDATE job_stats set stopdatetime_goal='$send', stopdatetime_actual='$date $nowtime' WHERE id=$statsid";
                    $dbUpdate=dbexecutequery($sql);
                    $error=$dbUpdate['error']; 
                    if($error=='')
                    {
                        print "success|$nowtime"; 
                    } else {
                        print "error|";
                    }
                    $sql="SELECT * FROM job_stats WHERE id=$statsid";
                    $dbCheck=dbselectsingle($sql);
                    if ($dbCheck['data']['counter_stop']>0)
                    {
                        //ok, this means we have a viable finish for the job, lets build some stats!
                        press_stats($statsid,$jobid);
                    }
                    checkTextAlerts($jobid,'stop');
                break;
            
                case "setupstart":
                    //we'll look at the scheduled start time
                    $date=date("Y-m-d");
                    $nowtime=date("H:i:s");
                    if($_POST['value']!='')
                    {
                        $nowtime=$_POST['value'];
                    }
                    
                    $sql="UPDATE job_stats SET setup_start='$date $nowtime' WHERE id=$statsid";
                    $dbUpdate=dbexecutequery($sql);
                    $error=$dbUpdate['error'];
                    if($error=='')
                    {
                        print "success|$nowtime|$log";
                    } else {
                        print "error|$nowtime|$sql";
                    }
                   
                break;
                
                case "setupstop":
                    $date=date("Y-m-d");
                    $nowtime=date("H:i:s");
                    if($_POST['value']!='')
                    {
                        $nowtime=$_POST['value'];
                    }
                    //calculate setup time
                    $setupstart=$stats['setup_start'];
                    if($setupstart!='')
                    {
                        $setuptime=round((strtotime("$date $nowtime") - strtotime($setupstart))/60,0);
                        //if ($start>$tdate){print "<br>Start time > stop time!";}
                        $sql="UPDATE job_stats set setup_stop='$date $nowtime', setup_time='$setuptime' WHERE id=$statsid";
                        $dbUpdate=dbexecutequery($sql);
                        $error=$dbUpdate['error']; 
                        if($error=='')
                        {
                            print "success|$nowtime"; 
                        } else {
                            print "error|";
                        }
                    } else {
                        print "error|No start time set!";
                    }
                    
                    
                break;
            
                
                case "startcounter":
                    if($value==''){$value=0;}
                    $sql="UPDATE job_stats set counter_start='$value' WHERE id=$statsid";
                    $dbUpdate=dbexecutequery($sql);
                    $error=$dbUpdate['error'];
                    if ($error=='')
                    {
                        print "success|";
                    } else {
                        print "error|$error";
                    } 
                break;
            
                case "stopcounter":
                    if($value==''){$value=0;}
                    //we'll look at the scheduled start time
                    if ($value!='')
                    {
                        $cstart=$stats['counter_start'];
                        if($GLOBALS['counterCheck'])
                        {
                            if ($cstart>$value)
                            {
                                print "error|Stop/Start counter problem!|";
                            } else {
                                print "success|";
                            }
                        } else {
                            print "success|";
                        }
                        $sql="UPDATE job_stats set counter_stop='$value' WHERE id=$statsid";
                        $dbUpdate=dbexecutequery($sql);
                        $error=$dbUpdate['error']; 
                        if ($stats['stopdatetime_actual']!='')
                        {
                            //ok, this means we have a viable finish for the job, lets build some stats!
                            press_stats($statsid,$jobid); 
                        }
                    }
                break;
                
                case "startspoils":
                    if($value==''){$value=0;}
                    //we'll look at the scheduled start time
                    $sql="UPDATE job_stats set spoils_startup='$value' WHERE id=$statsid";
                    $dbUpdate=dbexecutequery($sql);
                    $error=$dbUpdate['error'];
                    if ($error=='')
                    {
                        print "success|";
                    } else {
                        print "error|$error";
                    } 
                break;
                
                case "goodtime":
                    //we'll look at the scheduled start time
                    $date=date("Y-m-d");
                    $nowtime=date("H:i:s");
                    $sql="UPDATE job_stats set goodcopy_actual='$date $nowtime'  WHERE id=$statsid";
                    $dbUpdate=dbexecutequery($sql);
                    $error=$dbUpdate['error'];
                      if($error!='')
                      {
                          print "error|$stype - $source - $value - $jobid\n$error";
                      } else {
                          print "success|$nowtime";
                      } 
                break;
            
                
            
            }
            
            
        
        
        break;
        
         
        default:
        //nada
        break;
  }
 

 
 
dbclose();
?>
