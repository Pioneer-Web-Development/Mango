<?php
//<!--VERSION: .7 **||**-->
  //this script is meant to build recurring press jobs
  //$recurFrequencies=array("Every Week","Every Other Week","Every 3rd Week","Every 4th Week", "On the first","On the second", "On the third", "On the fourth", "On the last");

if ($_GET['mode']=='manual')
{
    $notes='';
    error_reporting(E_ERROR && E_WARNING);
    include('../includes/functions_db.php');
    include("../includes/mail/htmlMimeMail.php");
    include("../includes/config.php");
    include("../includes/functions_common.php");
    if($_GET['specific'])
    {
        init_recurringJob($_GET['specific']);
    } else {
        init_recurringJob();
    }
    print $GLOBALS['notes'];
}   

function init_recurringJob($jobid=0)
{
    $GLOBALS['notes'].= "Starting recurrence creation process. Got a jobid of $jobid<br />\n";
    global $siteID, $recurFrequencies;
    //first, we load all recurring jobs that are active
    if ($jobid!=0)
    {
        $sql="SELECT * FROM jobs_recurring WHERE id=$jobid";
    } else {
        $sql="SELECT * FROM jobs_recurring WHERE active=1";
    }
    $dbRecurring=dbselectmulti($sql);
    $GLOBALS['notes'].="Finding jobs with $sql<br />Found ".$dbRecurring['numrows'].' jobs.<br />';
    if ($dbRecurring['numrows']>0)
    {
        foreach($dbRecurring['data'] as $recJob)
        {
            $check=0;
            $pubdate=date("Y-m-d");;
            $specifiedDate=0;
            //now, we're going to loop out "DaysOut" days, checking each day to see if that
            //day of week is in the 'daysofweek' array. If we find one, we'll add it
            $startdate=$recJob['start_date'];
            $enddate=$recJob['end_date'];
            $enddatecheck=$recJob['end_date_checked'];
            $specifiedDate=$recJob['specified_date'];
            $frequency=$recJob['recur_frequency'];
            $daysout=$recJob['days_out'];
            $daysprev=$recJob['days_prev'];
            $daysofweek=explode("|",$recJob['days_of_week']);
            $dowstring=$recJob['days_of_week'];
            $dayfind=0;
            $GLOBALS['notes'].= "Rec job details for $recJob[id]...<br />".var_log($recJob)."</pre>";
            //first thing is to get the jobs in the system with this recurring id
            //and find the max pub date
            $sql="SELECT * FROM jobs WHERE recurring_id=$recJob[id] ORDER BY pub_date DESC LIMIT 1";
            //print "Max date sql is $sql<br />\n";
            $dbMaxDate=dbselectsingle($sql);
            if ($dbMaxDate['numrows']>0)
            {
                $maxDate=$dbMaxDate['data']['pub_date'];
                $new=0;
            } else {
                //must be new
                //use tomorrows's date as a default, 
                //since we cant print anything that publishes same day
                $maxDate=date("Y-m-d", strtotime("+1 day"));
                $new=1;
            }
            if($maxDate>$enddate && $enddatecheck)
            {
                $maxDate=$enddate;
            }
            if($maxDate<date("Y-m-d",strtotime("+$daysout days")))
            {
                //need to create a padding factor based on frequency
                $GLOBALS['notes'].= "Creating recurrences with a max date of $maxDate, start of $startdate, end of $enddate, and looking $daysout days out on days: ".implode(",",$daysofweek)." of the week. New is set to $new.<br />";   
                
                switch ($frequency)
                {
                      //$recurFrequencies=array("Every Week","Every Other Week","Every 3rd Week","Every 4th Week", 
                      //"On the first","On the second", "On the third", "On the fourth", "On the last");
                    case "0":
                        //every week
                        $daypad=0;
                        $skip=0;
                    break;
                    
                    case "1":
                        //every other week
                        $daypad=7;
                        $skip=2;
                    break;
                    
                    case "2":
                        //every 3rd week
                        $daypad=14;
                        $skip=3;
                    break;
                    
                    case "3":
                        //every 4th week
                        $daypad=21;
                        $skip=4;
                    break;
                    
                    case "4":
                        //first occurrence of that day
                        //$specifiedDate=get_specifiedday($daysofweek[0],1,date("m"),date("Y"));
                        $specifiedMonthlyDates=get_specifiedMonthlyDates($daysofweek[0],1,$maxDate,$daysout,$enddate);
                        $skip=0;
                    break;
                    
                    case "5":
                        //second occurrence of that day
                        //$specifiedDate=get_specifiedday($daysofweek[0],2,date("m"),date("Y"));
                        $specifiedMonthlyDates=get_specifiedMonthlyDates($daysofweek[0],2,$maxDate,$daysout,$enddate);
                        $skip=0;
                    break;
                    
                    case "6":
                        //third occurrence of that day
                        //$specifiedDate=get_specifiedday($daysofweek[0],3,date("m"),date("Y"));
                        $specifiedMonthlyDates=get_specifiedMonthlyDates($daysofweek[0],3,$maxDate,$daysout,$enddate);
                        $skip=0;
                    break;
                    
                    case "7":
                        //fourth occurrence of that day
                        //$specifiedDate=get_specifiedday($daysofweek[0],4,date("m"),date("Y"));
                        $specifiedMonthlyDates=get_specifiedMonthlyDates($daysofweek[0],4,$maxDate,$daysout,$enddate);
                        $skip=0;
                    break;
                    
                    case "8":
                        //last occurrence of that day
                        //$specifiedDate=get_specifiedday($daysofweek[0],'last',date("m"),date("Y"));
                        $specifiedMonthlyDates=get_specifiedMonthlyDates($daysofweek[0],'last',$maxDate,$daysout,$enddate);
                        $skip=0;
                    break;
                    
                }
                $GLOBALS['notes'].="Have specified date of $specifiedDate. Skip is $skip. and Frequency is $frequency.<br />";   
                
                //use the daypad to adjust the maxdate
                //$maxDate=date("Y-m-d",strtotime($maxDate."+$daypad days"));
                $loop++;
                if($loop>5){break;}
                //if we have a specified date, use that, otherwise, lets loop till we find one
                if ($specifiedDate!='0' && count($specifiedMonthlyDates)>0)
                {
                    $GLOBALS['notes'].= "Working on a specified date of $specifiedDate<br />";
                    if($specifiedDate>$maxDate)
                    {
                        //we'll only bother if we are looking past the current max date
                        insertRecurring($specifiedDate,$recJob);
                    } else {
                        $GLOBALS['notes'].= "Specified date did not exceed the current max date. <br />";
                        
                    }
                } elseif(count($specifiedMonthlyDates)>0){
                    foreach($specifiedMonthlyDates as $specifiedDate)
                    {
                        $GLOBALS['notes'].="Working on a specified date of $specifiedDate<br />";
                        if($specifiedDate>$maxDate)
                        {
                            //we'll only bother if we are looking past the current max date
                            insertRecurring($specifiedDate,$recJob);
                        } else {
                            $GLOBALS['notes'].=print "Specified date did not exceed the current max date. <br />";
                            
                        }
                    }
                } elseif($skip==0) {
                    $GLOBALS['notes'].=print "Working on regular weekly jobs<br />\n";
                    for($i=1;$i<=$daysout+1;$i++)
                    {
                        $check=date("w",strtotime("+$i days"));
                        $pubdate=date("Y-m-d",strtotime("+$i days"));
                        $GLOBALS['notes'].=print "Working $i days in advance, so $pubdate and maxdate $maxDate with $check against $dowstring<br />\n";
                        if (strtotime($pubdate)>strtotime($maxDate))
                        {
                            //print "Over date<br />\n";
                            //process only if we are looking at a date beyond the current oldest job
                            if (in_array($check,$daysofweek))
                            {
                                $GLOBALS['notes'].="Found a regular weekly job to insert for pubdate=$pubdate!<br />\n"; 
                                //ok, we found one
                                if($enddatecheck==1 && strtotime($pubdate)>strtotime($enddate))
                                {
                                   //do not process since it's past the end point  
                                } else {
                                   insertRecurring($pubdate,$recJob);
                                }
                                
                            } else {
                                $GLOBALS['notes'].=print "&nbsp;&nbsp;&nbsp;$check is not in $dowstring<br />";
                            }    
                        }
                    }
                } elseif($skip>0)
                {
                   get_skipping_weeks($daysout,$maxDate,$skip,$daysofweek,$recJob,$new,$startdate,$enddatecheck,$enddate); 
                }
            } else {
                $GLOBALS['notes'].= "We're already past the max future date for this job.<br />";
                
            }
        }
           
    } else {
        $GLOBALS['notes'].="No jobs found that are active...<br />";
    }   
    
    //handle any jobs that have already been created, but now need an insert
    if ($jobid==0){handleMissingInsertJobs();}
}

function get_skipping_weeks($daysout,$maxDate,$skip,$daysofweek,$recJob,$new,$startdate,$enddatecheck,$enddate)
{
    $GLOBALS['notes'].="Start date is $startdate<br>Max date is $maxDate<br>";
    $GLOBALS['notes'].="Getting skipping weeks for $daysout days out, with max date of $maxDate.<br />";
    
    //this function is built to return a set of dates for every other, 3rd or 4th week style recurrences
    //skip is the number of days ahead of maxDate
    $skip=7*$skip;//convert to number of days
    if ($new)
    {
       $maxDate=strtotime($recJob['start_date']);
       //lets see if there is a day this week that qualifies as a start, first, get number of day of maxdate
       $d=date("N",$maxDate);
       $GLOBALS['notes'].= "$recJob[start_date] is on $d of the week<br />\n";
       $hold=$maxDate;
       for($i=$d;$i<=7;$i++)
       {
           $dc=strtotime(date("Y-m-d",$hold)."+$i days");
           $check=date("w",$dc);
           $pubdate=date("Y-m-d",$dc);
           if (in_array($check,$daysofweek))
           {
              if($enddatecheck==1 && strtotime($pubdate)>strtotime($enddate))
              {
               //do not process since it's past the end point  
              } else {
                 insertRecurring($pubdate,$recJob);
              }
              //set max date to the new pubdate
              $maxDate=strtotime($pubdate);
              //print "Max date is now set to $pubdate<br />\n";
           }  
       }    
    } else {
       $maxDate=strtotime($maxDate);
       if(strtotime($startdate)>$maxDate)
       {
           $maxDate=strtotime($startdate);
           $d=date("N",$maxDate);
           $GLOBALS['notes'].= "$recJob[start_date] is on $d of the week<br />\n";
           $check=date("w",strtotime($startdate));
           if (in_array($check,$daysofweek))
           {
              $pubdate=date("Y-m-d",strtotime($startdate));
              if($enddatecheck==1 && strtotime($pubdate)>strtotime($enddate))
              {
               //do not process since it's past the end point  
              } else {
                 insertRecurring($pubdate,$recJob);
              }
              //set max date to the new pubdate
              $maxDate=strtotime($pubdate);
              $GLOBALS['notes'].= "The start date was used as a pubdate $pubdate<br />\n";
           }  
           
           $hold=$maxDate;
           for($i=$d;$i<=7;$i++)
           {
               $dc=strtotime(date("Y-m-d",$hold)."+$i days");
               $check=date("w",$dc);
               $pubdate=date("Y-m-d",$dc);
               if (in_array($check,$daysofweek))
               {
                  if($enddatecheck==1 && strtotime($pubdate)>strtotime($enddate))
                  {
                   //do not process since it's past the end point  
                  } else {
                     insertRecurring($pubdate,$recJob);
                  }
              //set max date to the new pubdate
                  $maxDate=strtotime($pubdate);
                  $GLOBALS['notes'].= "Max date is now set to $pubdate<br />\n";
               }  
           } 
       }
    }
    $GLOBALS['notes'].="We are starting with a date of ".date("Y-m-d",$maxDate)." and skipping $skip days.<br />";
    $dc=$maxDate;
    for ($i=0;$i<=$daysout;$i++)
    {
        $dc=strtotime(date("Y-m-d",$dc)."+1 day");
        //print "DC is ".date("Y-m-d",$dc)."<br />\n";
        $check=date("w",$dc);
        $pubdate=date("Y-m-d",$dc);
        if (in_array($check,$daysofweek))
        {
            $sep=dayDiff($dc,$maxDate);
            //print "Found a day with separation of $sep!<br />\n";
            if ($dc>$maxDate)  //means we have moved beyond the max scheduled instance now
            {
                if ($sep>=$skip) //means
                {
                    //ok, we found one
                    if($enddatecheck==1 && strtotime($pubdate)>strtotime($enddate))
                      {
                       //do not process since it's past the end point  
                      } else {
                         insertRecurring($pubdate,$recJob);
                      }
              //set max date to the new pubdate
                    $maxDate=strtotime($pubdate);
                }    
            } elseif($dc==$maxDate) //should only be true if this is a brand new instance
            {
                if (in_array($check,$daysofweek))
                {
                    //print "Inserting a date for a new start!<br />\n";
                    //ok, we found one
                    if($enddatecheck==1 && strtotime($pubdate)>strtotime($enddate))
                      {
                       //do not process since it's past the end point  
                      } else {
                         insertRecurring($pubdate,$recJob); 
                      }
              
                }
            } 
        }  
    }
}




function get_specifiedday($day,$pos,$month,$year)
{
    $count=0;
    $maxdays=date("t",mktime(0,0,0,$month,1,$year)); //gives us number of days in given month
    if ($pos=='last')
    {
        for ($i=$maxdays;$i>=1;$i--)
        {
            $num = date("w",mktime(0,0,0,$month,$i,$year));
            if ($num==$day)
            {
                $date=date("Y-m-d",mktime(0,0,0,$month,$i,$year));
                break;
            }
        }
    } else {
        for ($i=1;$i<=$maxdays;$i++)
        {
            $num = date("w",mktime(0,0,0,$month,$i,$year));
            if ($num==$day)
            {
                $count++;
                if ($count==$pos)
                {
                    $date=date("Y-m-d",mktime(0,0,0,$month,$i,$year));
                }
            }
        
        }
    }
    if (strtotime($date)<time())
    {
        if ($month<12)
        {
            $month++;
        } else {
            $month=1;
            $year++;
        }
        return get_specifiedday($day,$pos,$month,$year);
    } else {
        return $date; 
    }     
}

function get_specifiedMonthlyDates($day,$pos,$maxDate,$daysOut,$enddate)
{
    $GLOBALS['notes'].="Getting specicified monthly dates with day=$day, pos=$pos, maxDate=$maxDate, daysOut=$daysOut, and enddate of $enddate<br />\n";
    $capdate=date("Y-m-d",strtotime($maxDate." + $daysOut days"));
    if($capdate>$enddate){$capdate=$enddate;}
    while($maxDate<=$capdate)
    {
        $month=date("m",strtotime($maxDate));
        $year=date("Y",strtotime($maxDate));    
        $cdate=get_specifiedday($day,$pos,$month,$year);
        $maxDate=date("Y-m-d",strtotime($maxDate." + 1 month"));
        if(!in_array($cdate,$dates))
        {
            $dates[]=$cdate;
        }
        $GLOBALS['notes'].= "Max date is now $maxDate and adding $cdate<br />";
        
    }
    $GLOBALS['notes'].= "Found the following dates starting from $maxDate and looking $daysOut days:<br />\n";
    $GLOBALS['notes'].=var_log($dates)."<br />\n";;
    
    return $dates;     
}

function insertRecurring($pubdate,$recJob)
{
    $GLOBALS['notes'].="Inserting a recurring job on $pubdate<br />\n";
    global $siteID;
    $daysprev=$recJob['days_prev'];
    $now=date("Y-m-d H:i:s");
    $by='0';
    $layoutid=$recJob['layout_id'];
    $insertpubid=$recJob['insert_pub_id'];
    if($insertpubid==0 || $insertpubid== ""){$insertpubid=$recJob['pub_id'];}
    $startdatetime=$pubdate." ".$recJob['start_time'];
    //print "Start date time is $startdatetime<br />\n";
    //this should mean that the job is running the day before pub
    $startdatetime=date("Y-m-d H:i",strtotime($startdatetime."-$daysprev days"));
    $runtime=$recJob['draw']/($GLOBALS['pressSpeed']/60); //this should give us a number of minutes;
    $runtime=round($runtime,0);
    $runtime+=$GLOBALS['pressSetup'];
    //print "Showing a runtime of $runtime<br />\n";
    $stopdatetime=date("Y-m-d H:i",strtotime($startdatetime."+$runtime minutes"));
    //print "Stop date time is $stopdatetime<br />\n";
    if ($recJob['use_draw'])
    {
        $draw=$recJob['draw'];   
    } else {
        $draw=0;
    }
    $GLOBALS['notes'].= "We would are inserting a record right now with startdate of $startdatetime and enddate of $stopdatetime.<br />";
    
    $sql="INSERT INTO jobs (created_time, created_by, pub_id, run_id, startdatetime, enddatetime, 
    recurring_id, notes_job, papertype, papertype_cover, lap, folder, pub_date, site_id, insert_source, pagewidth, draw, layout_id, insert_pub_id, slitter, folder_pin, job_type, quarterfold, rollSize, press_id) VALUES ('$now', '$by', '$recJob[pub_id]', '$recJob[run_id]', '$startdatetime', '$stopdatetime', '$recJob[id]',  '$recJob[notes]', '$recJob[papertype]', '$recJob[papertype_cover]', '$recJob[lap]', '$recJob[folder]', '$pubdate', '$siteID', 'autorecurring', '$recJob[pagewidth]', '$draw', '$recJob[layout_id]', '$insertpubid', '$recJob[slitter]', '$recJob[folder_pin]', '$recJob[job_type]', '$recJob[quarterfold]', '$recJob[rollSize]', '$recJob[press_id]')";
    $dbInsert=dbinsertquery($sql);
    $jobid=$dbInsert['insertid'];
    $GLOBALS['notes'].="We created a new record with: <br />$sql<br /><br />";
        
    
    if ($dbInsert['error']!='')
    {
        $GLOBALS['notes'].="Error inserting this one:<br>\n";
        $GLOBALS['notes'].=$dbInsert['error']."<br /><br />\n";
    } else {
        //if section_id is not 0, lets duplicate recurring_section to job_section
        if ($recJob['section_id']!=0)
        {
            $sql="SELECT * FROM jobs_recurring_sections WHERE id=$recJob[section_id]";
            $dbRS=dbselectsingle($sql);
            $rs=$dbRS['data'];
            $fields='';
            $values='';
            foreach($rs as $key=>$value)
            {
                if ($key=='job_id')
                {
                    $fields.="job_id,";
                    $values.="'$jobid',";
                } elseif($key=='id') {
                    //skip this one
                } else {
                    $fields.="$key,";
                    $values.="'$value',";
                }           
            }
            $fields=substr($fields,0,strlen($fields)-1);
            $values=substr($values,0,strlen($values)-1);
            $sql="INSERT INTO jobs_sections ($fields) VALUES ($values)";
            $dbInsert=dbinsertquery($sql);
            //print $sql;
        }
        
        //create a stat record
        $sql="INSERT INTO job_stats (job_id, added_by) VALUES ($jobid, 'recurringPressJobs.php - line 395')";
        $dbStat=dbinsertquery($sql);
        $statsid=$dbStat['insertid'];
        
        
        $sql="UPDATE jobs SET stats_id=$statsid, scheduled_time='$scheduledtime', scheduled_by='$scheduledby', 
        enddatetime='$stopdatetime' WHERE id=$jobid";
        //print "Update sql is $sql<br />\n";
        $dbUpdate=dbexecutequery($sql);
        
        saveRecLayout($layoutid,$jobid,$siteID);
        $GLOBALS['notes'].="Sucessfully inserted a run for $pubdate at $startdatetime, finishing at $stopdatetime<br />\n";
        $GLOBALS['notes'].="Passing $jobid over to the 2Inserter function<br />\n";
        printJob2Inserter($jobid);
        $GLOBALS['notes'].="Passing $jobid over to the 2Delivery function<br />\n";
        printJob2Delivery($jobid);
        $GLOBALS['notes'].="Passing $jobid over to the 2Bindery function<br />\n";
        printJob2Bindery($jobid);
        $GLOBALS['notes'].="Passing $jobid over to the 2Addressing function<br />\n";
        printJob2Addressing($jobid);
    }
    
}

function handleMissingInsertJobs()
{
    if ($_GET['mode']=='manual')
    {
        $debug=true;
    }
    //this function should look for jobs out for the next 90 days.
    $sql="SELECT id FROM jobs WHERE pub_date<='".date("Y-m-d",strtotime("+90 days"))."' AND pub_date>='".date("Y-m-d")."'";
    $dbJobs=dbselectmulti($sql);
    if($dbJobs['numrows']>0)
    {
        foreach($dbJobs['data'] as $job)
        {
            $jobid=$job['id'];
            printJob2Inserter($jobid,0,$debug);
            printJob2Delivery($jobid);
            printJob2Bindery($jobid);
            printJob2Addressing($jobid);
        }
    }
    
}

  
function saveRecLayout($layoutid,$jobid,$siteID)
{
    
    //get some pub info from the job
    $sql="SELECT A.pub_date, B.pub_code, A.pub_id FROM jobs A, publications B WHERE B.id=A.pub_id AND A.id=$jobid";
    $dbPubInfo=dbselectsingle($sql);
    $pubinfo=$dbPubInfo['data'];
    $pubcode=$pubinfo['pub_code'];
    $pubdate=date("Y-md",strtotime($pubinfo['pub_date']));
    $pubid=$pubinfo['pub_id'];
    
    
    //now, lets create the plates for this job
    //we'll need to get the pub code for the publication
    //also, pub date and section codes for all sections
    $jobsql="SELECT A.*, B.pub_code FROM jobs A, publications B WHERE A.id=$jobid AND A.pub_id=B.id";
    //print "Job select sql:<br>$jobsql<br>";
    $dbJob=dbselectsingle($jobsql);
    $job=$dbJob['data'];


    $jobsection="SELECT * FROM jobs_sections WHERE job_id=$jobid";
    //print "Job section sql:<br>$jobsection<br>";
    $dbJSection=dbselectsingle($jobsection);
    $jsection=$dbJSection['data'];
    $scode[1]=$jsection['section1_code'];
    $scode[2]=$jsection['section2_code'];
    $scode[3]=$jsection['section3_code'];
    
    //now get layout sections
    $lsql="SELECT * FROM layout_sections WHERE layout_id=$layoutid";
    //print "Job layout sections sql:<br>$lsql<br>";
    $dbLSections=dbselectmulti($lsql);


    //first, delete any potential existing job plates and pages
    $sql="DELETE FROM job_pages WHERE job_id=$jobid";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM job_plates WHERE job_id=$jobid";
    $dbDelete=dbexecutequery($sql);
    $colorconfigs=$GLOBALS['colorconfigs'];
    if ($dbLSections['numrows']>0)
    {
        foreach ($dbLSections['data'] as $lsection)
        {
            
            $section_number=$lsection['section_number'];
            $towers=$lsection['towers'];
            $towers=explode("|",$towers);
            foreach ($towers as $tower)
            {
                $created=date("Y-m-d H:i:s");
                //lets look up the color for a tower
                $sql="SELECT color_config FROM press_towers WHERE id=$tower";
                $dbColor=dbselectsingle($sql);
                if ($dbColor['numrows']>0)
                {
                    if ($dbColor['data']['color_config']=='K')
                    {
                        $color=0;
                        $possiblecolor=0;
                    }else{
                        $color=1;
                        $possiblecolor=1;
                    }
                    $tcolor=array_search($dbColor['data']['color_config'],$colorconfigs,true);
                } else {
                    $color=0;
                    $possiblecolor=0;
                    $tcolor=0;
                }
                
                $plate1="";
                $plate2="";
                $pages1=array();
                $pages2=array();
                $lowpage1=9999; //set arbitrarily high so it gets set immediately to the new page
                $lowpage2=9999; //set arbitrarily high so it gets set immediately to the new page
                //now we need the pages for this layout & tower -- 10 side, then 13 side
                $psql="SELECT * FROM layout_page_config WHERE layout_id=$layoutid AND tower_id=$tower";
                $dbPages=dbselectmulti($psql);
                if ($dbPages['numrows']>0)
                {
                    foreach ($dbPages['data'] as $page)
                    {
                        $side=$page['side'];
                        $page_num=$page['page_number'];
                        if ($page_num!=0)
                        {
                            if ($side==10)
                            {
                                if ($page_num<$lowpage1 && $page_num!=0){$lowpage1=$page_num;}
                                $pages1[]="$pubid, $jobid,'$scode[$section_number]','$pubcode','$pubdate',$color,$possiblecolor,$tower, $tcolor,$page_num, 1,'$created', '$siteID'),";
                            } else {
                                if ($page_num<$lowpage2 && $page_num!=0){$lowpage2=$page_num;}
                                $pages2[]="$pubid, $jobid,'$scode[$section_number]','$pubcode','$pubdate',$color,$possiblecolor,$tower, $tcolor, $page_num, 1,'$created', '$siteID'),";
                            }
                        }            
                    
                    }
                    //now we should have 2 items, 2 arrays with pages and a low page number for each plate
                    $plate1="INSERT INTO job_plates (pub_id, job_id, section_code, pub_code, pub_date, low_page, color, version, created, site_id) VALUES
                    ($pubid,$jobid, '$scode[$section_number]','$pubcode', '$pubdate','$lowpage1',$color,1,'$created', '$siteID')";
                    $dbPlate1=dbinsertquery($plate1);                                             
                    //print "Plate save 1 sql:<br>$plate1<br>";

                    $plate1ID=$dbPlate1['numrows'];
                    $plate2="INSERT INTO job_plates (pub_id, job_id, section_code, pub_code, pub_date, low_page, color, version, created, site_id) VALUES
                    ($pubid,$jobid, '$scode[$section_number]','$pubcode', '$pubdate','$lowpage2',$color,1,'$created', '$siteID')";
                    $dbPlate2=dbinsertquery($plate2);
                    $plate2ID=$dbPlate2['numrows'];
                    //print "Plate save 2 sql:<br>$plate2<br>";

                    //now insert the pages
                    $values1="";
                    foreach($pages1 as $page)
                    {
                        $values1.="($plate1ID,$page";    
                    }
                    $values1=substr($values1,0,strlen($values1)-1);
                    $page1="INSERT INTO job_pages (plate_id, pub_id, job_id, section_code, pub_code, pub_date, color, possiblecolor, tower_id, tower_color, page_number, version, created, site_id) VALUES $values1";
                    $dbPage1=dbinsertquery($page1);
                    //print "Page save 1 sql:<br>$page1<br>";

                    //now insert the pages
                    $values2="";
                    foreach($pages2 as $page)
                    {
                        $values2.="($plate2ID,$page";    
                    }
                    $values2=substr($values2,0,strlen($values2)-1);
                    $page2="INSERT INTO job_pages (plate_id, pub_id, job_id, section_code, pub_code, pub_date, color, possiblecolor, tower_id, tower_color, page_number, version, created, site_id) VALUES $values2";
                    $dbPage2=dbinsertquery($page2);
                    //print "Page save 2 sql:<br>$page2<br>";

                }
            }
         }
    }
  }

?>
