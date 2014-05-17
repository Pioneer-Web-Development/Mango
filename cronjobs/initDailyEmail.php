<?php
//<!--VERSION: .9 **||**-->
if($_GET['mode']=='test')
{
    include("../includes/functions_db.php");
    include("../includes/mail/htmlMimeMail.php");
    include("../includes/functions_common.php");
    include("../includes/config.php");
    init_dailyEmail();
}

function init_dailyEmail()
{
    global $siteID, $systemEmailFromAddress;
    $startdate=date("Y-m-d",strtotime("-1 day"))." 6:00";
    $enddate=date("Y-m-d")." 6:00";
    $rundate=date("Y-m-d");
    $successMail=0;
    //ok, start by getting a list of groups that get email summaries
    $sql="SELECT * FROM email_groups WHERE group_active=1";
    $dbGroups=dbselectmulti($sql);

    if ($dbGroups['numrows']>0)
    {
        //we have people!
        foreach($dbGroups['data'] as $group)
        {
            $email=build_email($group,$startdate,$enddate);
            if ($email=='nopubs')
            {
                $GLOBALS['notes'][]="No publications were found.";
                if($_GET['mode']=='test'){print "No publications were found.";}  
            } elseif ($email=='nojobs')
            {
                if($_GET['mode']=='test'){print "No jobs were found.";}
                $GLOBALS['notes'][]="No jobs were found.";  
            } elseif ($email!='')
            {
                //build and send the email
                $emailaddress=stripslashes($group['group_email']);
                $mail = new htmlMimeMail();
                
                $message="<html><head></head><body>";
                $message.=$email."</body></html>\n";
                $mail->setText($text);
                $mail->setHtml($message);
                
            /**
            * Sends the message.
            */
                $mail->setFrom($systemEmailFromAddress);
                $mail->setSubject("Daily production run summary for $rundate");
                if ($_GET['mode']=='test'){$emailaddress='jhansen69@gmail.com';}
                $result = $mail->send(array($emailaddress));
                if ($result){
                    $successMail++;
                    
                
                if ($_GET['mode']=='test'){print "Sent message from $systemEmailFromAddress to $emailaddress<br>".stripslashes($email)."<br><br>";}
                $GLOBALS['notes'].="Sent message to $emailaddress<br><br>";
                } else {
                    if($_GET['mode']=='tes'){print "Problem sending message to $emailaddress<br>";}
                    $GLOBALS['notes'].="Problem sending message to $emailaddress<br>";
                }
                /****************************************************
                * NOTE: WE ARE KILLING THE PROCESS AFTER ONE EMAIL!!!
                *****************************************************/
            } else {
                if($_GET['mode']=='test'){print "No email found";}
                $GLOBALS['notes'].="No email message built.";
            }
        }
    }
} 
function build_email($group,$startdate,$enddate)
{
    //ok, we need to get the publications that are accessible for this user
    $groupid=$group['id'];
    $sql="SELECT * FROM email_groups_publications WHERE group_id=$groupid AND value=1";
    $dbPubs=dbselectmulti($sql);
    if ($dbPubs['numrows']>0)
    {
        $email="";
        //found pubs for this user
        //now, look through the pubs to find jobs
        foreach($dbPubs['data'] as $pub)
        {
            $sql="SELECT * FROM jobs WHERE pub_id=$pub[pub_id] AND startdatetime>='$startdate' AND enddatetime<='$enddate' ORDER BY startdatetime DESC";
            //if ($_GET['mode']=='test'){print $sql."<br>";}
            $dbJobs=dbselectmulti($sql);
            if ($dbJobs['numrows']>0)
            {
                foreach ($dbJobs['data'] as $job)
                {
                    $email.=getJobDetails($job);    
                }
            }
        
        
        }
        //now the same thing for inserter jobs
        foreach($dbPubs['data'] as $pub)
        {
            $sql="SELECT * FROM jobs_inserter_plans WHERE pub_id=$pub[pub_id] AND pub_date='$startdate' AND continue_id=0 ORDER BY startdatetime DESC";
            //if ($_GET['mode']=='test'){print $sql."<br>";}
            $dbJobs=dbselectmulti($sql);
            if ($dbJobs['numrows']>0)
            {
                foreach ($dbJobs['data'] as $job)
                {
                    $email.=getInserterJobDetails($job);    
                }
            }
        
        
        }
        if ($email=='')
        {
            return 'nojobs';
        } else {
            return $email;
        }
    } else {
        return 'nopubs';
    }
}


function getJobDetails($job)
{
    global $enableJobStops, $enableBenchmarks, $producttypes, $pressmen;
    $jobid=$job['id'];
    //here we display the job data and all it's stats, we'll use a table layout
    //we'll need to get lots of other data pieces at this point
    $sql="SELECT pub_name FROM publications WHERE id=$job[pub_id]";
    $dbPub=dbselectsingle($sql);
    
    $sql="SELECT * FROM publications_runs WHERE id=$job[run_id]";
    $dbRun=dbselectsingle($sql);
    
    
    //lets calculate page flow
    if($dbRun['data']['last_plate_leadtime']!=0)
    {
        $lastplatetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-".$dbRun['data']['last_plate_leadtime']." minutes"));
        //how many plates out after this point?
        $sql="SELECT * FROM job_plates WHERE current=1 AND (plate_approval>='$lastplatetime' OR plate_approval IS NULL) AND job_id=$jobid";
        //print "starttiem: $job[startdatetime] leadtime:".$dbRun['data']['last_plate_leadtime'].'<br>'.$sql."<br>";
        $dbPlates=dbselectmulti($sql);
        $lastplatecount=$dbPlates['numrows']; 
    }
    if($dbRun['data']['plates_2_left_leadtime']!=0)
    {
        $last2platetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-".$dbRun['data']['plates_2_left_leadtime']." minutes"));
        //how many plates out after this point?
        $sql="SELECT * FROM job_plates WHERE current=1 AND (plate_approval>='$last2platetime' OR plate_approval IS NULL) AND job_id=$jobid";
        $dbPlates=dbselectmulti($sql);
        $last2platecount=$dbPlates['numrows']; 
    }
    if($dbRun['data']['plates_3_left_leadtime']!=0)
    {
        $last3platetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-".$dbRun['data']['plates_3_left_leadtime']." minutes"));
        //how many plates out after this point?
        $sql="SELECT * FROM job_plates WHERE current=1 AND (plate_approval>='$last3platetime' OR plate_approval IS NULL) AND job_id=$jobid";
        $dbPlates=dbselectmulti($sql);
        $last3platecount=$dbPlates['numrows']; 
    }
    if($dbRun['data']['plates_4_left_leadtime']!=0)
    {
        $last4platetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-".$dbRun['data']['plates_4_left_leadtime']." minutes"));
        //how many plates out after this point?
        $sql="SELECT * FROM job_plates WHERE current=1 AND (plate_approval>='$last4platetime' OR plate_approval IS NULL) AND job_id=$jobid";
        $dbPlates=dbselectmulti($sql);
        $last4platecount=$dbPlates['numrows']; 
    }
    if($dbRun['data']['plates_5_left_leadtime']!=0)
    {
        $last5platetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-".$dbRun['data']['plates_5_left_leadtime']." minutes"));
        //how many plates out after this point?
        $sql="SELECT * FROM job_plates WHERE current=1 AND (plate_approval>='$last5platetime' OR plate_approval IS NULL) AND job_id=$jobid";
        $dbPlates=dbselectmulti($sql);
        $last5platecount=$dbPlates['numrows']; 
    }
    if($dbRun['data']['plates_6_left_leadtime']!=0)
    {
        $last6platetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-".$dbRun['data']['plates_6_left_leadtime']." minutes"));
        //how many plates out after this point?
        $sql="SELECT * FROM job_plates WHERE current=1 AND (plate_approval>='$last6platetime' OR plate_approval IS NULL) AND job_id=$jobid";
        $dbPlates=dbselectmulti($sql);
        $last6platecount=$dbPlates['numrows']; 
    }
    if($dbRun['data']['chase_plate_aftertime']!=0)
    {
        $chaseplatetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-".$dbRun['data']['chase_plate_aftertime']." minutes"));
        //how many plates out after this point?
        $sql="SELECT * FROM job_plates WHERE remake=1 AND current=1 AND (black_approval>='$chaseplatetime' OR black_approval IS NULL) AND job_id=$jobid";
        $dbPlates=dbselectmulti($sql);
        $chaseplatecount=$dbPlates['numrows']; 
    }
    
    //calculate last page information
    $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND remake=0 ORDER BY workflow_receive DESC LIMIT 1";
    $dbMaxPage=dbselectsingle($sql);
    $lastpage=$dbMaxPage['data']['page_number'].$dbMaxPage['data']['section_code'];
    $lastpagetime=$dbMaxPage['data']['workflow_receive'];
    
    //calculate last page information
    $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND remake=0 AND color=1 ORDER BY workflow_receive DESC LIMIT 1";
    $dbMaxPage=dbselectsingle($sql);
    $lastcolorpage=$dbMaxPage['data']['page_number'].$dbMaxPage['data']['section_code'];
    $lastcolorpagetime=$dbMaxPage['data']['workflow_receive'];
    
    //calculate last plate information
    $sql="SELECT low_page, black_approval, plate_approval, section_code FROM job_plates WHERE job_id=$jobid AND remake=0 ORDER BY plate_approval DESC LIMIT 1";
    $dbMaxPage=dbselectsingle($sql);
    $lastplate=$dbMaxPage['data']['low_page'].$dbMaxPage['data']['section_code'];
    $lastplatetime=$dbMaxPage['data']['black_approval'];
    
    $sql="SELECT A.benchmark_name, A.benchmark_category, A.benchmark_order, B.* FROM benchmarks A, job_benchmarks B WHERE A.id=B.benchmark_id AND B.job_id=$job[id] ORDER BY A.benchmark_category, A.benchmark_order ASC";
    $dbBenchmarks=dbselectmulti($sql);
    $benchmarks=$dbBenchmarks['data'];
    
    $sql="SELECT * FROM job_stats WHERE id=$job[stats_id]";
    $dbStats=dbselectsingle($sql);
    $stats=$dbStats['data'];
    //print_r($stats);
    //first, print out the name of the publication in biggish letters
    
    //now the table
    $jobdetails="<small>JOB ID: $job[id]</small><br />\n";
    $jobdetails.="<table border=1 style='font-family:Trebuchet MS;font-size:12px;width:800px;border-style:solid;border-color:black;border-width:2px;'>\n";
    $counterstart=$stats['counter_start'];
    $counterstop=$stats['counter_stop'];
    $startupspoils=$stats['spoils_startup'];
    $draw=$job['draw'];
    $runningspoils=$stats['spoils_running'];
    $totalspoils=$stats['spoils_total'];
    $operator=$pressmen[$stats['job_pressoperator']];
    $paperDb=dbselectsingle("SELECT * FROM paper_types WHERE id=$job[papertype]");
    $paper=$paperDb['data']['common_name'];
    
    $jobdetails.="<tr><th colspan=6 style='font-size:16px;font-weight:bold;background-color:black;color:white;'>".$dbPub['data']['pub_name'].' - '.$dbRun['data']['run_name'].' publishing on '.$job['pub_date']."</th></tr>\n";
    
    $jobdetails.="<tr><th colspan=3><b>Lead Operator:</b> $operator</th><th colspan=3><b>Base paper:</b> $paper</th></tr>\n"; 
    $jobdetails.="<tr><td colspan=3><b>Counter Start:</b> $counterstart</td><td colspan=3><b>Counter Stop:</b> $counterstop</td></tr>\n";
    $jobdetails.="<tr><td colspan=2><b>Startup Spoils:</b> $startupspoils</td><td colspan=2><b>Running Spoils:</b> $runningspoils</td><td colspan=2><b>Total Spoils:</b> $totalspoils</td></tr>\n";
    $jobdetails.="<tr><th colspan=3 style='font-weight:bold;background-color:black;color:white;'>Press Times</th><th style='font-weight:bold;background-color:black;color:white;'>Goal</th><th colspan=1 style='font-weight:bold;background-color:black;color:white;'>Actual</th><th colspan=1 style='font-weight:bold;background-color:black;color:white;'>Variance</th></tr>\n"; 
    //first some info about the job
    $jobdetails.="<tr><td colspan=3><b>Start Time</b></td><td>$stats[startdatetime_goal]</td><td>$stats[startdatetime_actual]</td><td>$stats[start_offset]</td></tr>\n";
    $jobdetails.="<tr><td colspan=3><b>Good Copy</b></td><td colspan=3>".$stats['goodcopy_actual']."</td></tr>\n";
    $jobdetails.="<tr><td colspan=3><b>Stop Time</b></td><td>$stats[stopdatetime_goal]</td><td>$stats[stopdatetime_actual]</td><td>$stats[finish_offset]</td></tr>\n";
    $jobdetails.="<tr><td colspan=3><b>Draw Request</b></td><td >$job[draw]</td><td>$stats[gross]</td><td>$stats[spoils_total]</td></tr>\n";
    
    //now just a bunch of stats
    $jobdetails.="<tr>";
    $jobdetails.="<th colspan=6 style='background-color:black;color:white;'><b>Statistics</b></th>";
    $jobdetails.="</tr>";
    $jobdetails.="<th colspan=2 style='background-color:black;color:white;'><b>Statistics</b></th><th style='background-color:black;color:white;'><b>Value</b></th>";
    $jobdetails.="<th colspan=2 style='background-color:black;color:white;'><b>Page & Plate Flow</b></th><th style='background-color:black;color:white;'><b>Value</b></th>";
    $jobdetails.="</tr>\n"; 
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2><b>Waste %</b></td><td>$stats[waste_percent]%</td>";
    
    //need last plate calculation
    $jobdetails.="<td colspan=2><b>Last Plate</b></td><td>$lastplate at $lastplatetime</td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2><b>Downtime</b></td><td>$stats[total_downtime] minutes</td>\n";
    $jobdetails.="<td colspan=2><b>Last Page</b></td><td>$lastpage at $lastpagetime</td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2><b>Running Time</b></td><td>$stats[run_time] minutes</td>\n";
    $jobdetails.="<td colspan=2><b>Last Color Page</b></td><td>$lastcolorpage at $lastcolorpagetime</td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2><b>Scheduled Running Time</b></td><td>$stats[sched_runtime] minutes</td>\n";
    $jobdetails.="<td colspan=2><b>Plates out at $last2platetime</b></td><td>$last2platecount</td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2><b>Gross average speed</b></td><td colspan=1>$stats[run_speed] copies/hr</td>\n";
    $jobdetails.="<td colspan=2><b>Plates out at $last3platetime</b></td><td>$last3platecount</td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2><b>Net average speed</b></td><td colspan=1>$stats[good_runspeed] copies/hr</td>\n";
    $jobdetails.="<td colspan=2><b>Plates out at $last4platetime</b></td><td>$last4platecount</td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2><b>Total Pressman</b></td><td colspan=1>$stats[job_pressman_count] pressman</td>\n";
    $jobdetails.="<td colspan=2><b>Plates out at $last5platetime</b></td><td>$last5platecount</td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2>Pages BW</td><td colspan=1>$stats[pages_bw] pages (broadsheet eqv.)</td>\n";
    $jobdetails.="<td colspan=2><b>Plates out at $last6platetime</b></td><td>$last6platecount</td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2>Pages Color</td><td colspan=1>$stats[pages_color] pages (broadsheet eqv.)\n";
    $jobdetails.="<td colspan=2><b>Chase plates out at $chaseplatetime</b></td><td>$chaseplatecount</td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2>Plates BW</td><td colspan=1>$stats[plates_bw] plates</td>\n";
    //HOLDER
    $jobdetails.="<td colspan=2><b></b></td><td></td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<tr><td colspan=2>Plates Color</td><td colspan=1>$stats[plates_color] plates</td>\n";
    //HOLDER
    $jobdetails.="<td colspan=2><b></b></td><td></td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2>Plates remade</td><td colspan=1>$stats[plates_remake] plates</td>\n";
    //HOLDER
    $jobdetails.="<td colspan=2><b></b></td><td></td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2>Plates wasted</td><td colspan=1>$stats[plates_waste] plates</td>\n";
    //HOLDER
    $jobdetails.="<td colspan=2><b></b></td><td></td>\n";
    $jobdetails.="</tr>\n";
    
    
    $jobdetails.= "<tr><td colspan=6 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:center;'>Section Information</td></tr>\n";
    //get sectioninfo
    $sql="SELECT * FROM jobs_sections WHERE job_id=$job[id]";
    $dbSections=dbselectsingle($sql);
    if ($dbSections['numrows']>0)
    {
        $jobdetails.= "<tr><td colspan=6>\n";
        $sections=$dbSections['data'];
        $totalpages=0;
        //ok, lets get how many color/bw pages by section
        //section1
        $section1_overrun=$sections['section1_overrun'];
        $section1_name=$sections['section1_name'];
        $section1_code=$sections['section1_code'];
        $section1_totalpages=0;
        $section1_colorpages=0;
        $section1_bwpages=0;
        $section1_format=$producttypes[$sections['section1_producttype']];
        $section1_lead=$leadtypes[$sections['section1_leadtype']];
        if ($sections['section1_gatefold']){$section1_gate='Has gatefold';}else{$section1_gate='';}
        if ($sections['section1_doubletruck']){$section1_double='Has doubletruck';}else{$section1_double='';}
        $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='$sections[section1_code]' AND version=1 ORDER BY page_number ASC";
        $dbPages=dbselectmulti($sql);
        if ($dbPages['numrows']>0)
        {
            foreach($dbPages['data'] as $page)
            {
                $section1_totalpages++;
                if ($page['color']){$section1_colorpages++;}else{$section1_bwpages++;}
            }   
        }
        $totalpages+=$section1_totalpages;
        //section2
        $section2_overrun=$sections['section2_overrun'];
        $section2_name=$sections['section2_name'];
        $section2_code=$sections['section2_code'];
        $section2_totalpages=0;
        $section2_colorpages=0;
        $section2_bwpages=0;
        $section2_format=$producttypes[$sections['section2_producttype']];
        $section2_lead=$leadtypes[$sections['section2_leadtype']];
        if ($sections['section2_gatefold']){$section2_gate='Has gatefold';}else{$section2_gate='';}
        if ($sections['section2_doubletruck']){$section2_double='Has doubletruck';}else{$section2_double='';}
        $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='$sections[section2_code]' AND version=1 ORDER BY page_number ASC";
        $dbPages=dbselectmulti($sql);
        if ($dbPages['numrows']>0)
        {
            foreach($dbPages['data'] as $page)
            {
                $section2_totalpages++;
                if ($page['color']){$section2_colorpages++;}else{$section2_bwpages++;}
            }   
        }
        $totalpages+=$section2_totalpages;
        //section3
        $section3_overrun=$sections['section3_overrun'];
        $section3_name=$sections['section3_name'];
        $section3_code=$sections['section3_code'];
        $section3_totalpages=0;
        $section3_colorpages=0;
        $section3_bwpages=0;
        $section3_format=$producttypes[$sections['section3_producttype']];
        $section3_lead=$leadtypes[$sections['section3_leadtype']];
        if ($sections['section3_gatefold']){$section3_gate='Has gatefold';}else{$section3_gate='';}
        if ($sections['section3_doubletruck']){$section3_double='Has doubletruck';}else{$section3_double='';}
        $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='$sections[section3_code]' AND version=1 ORDER BY page_number ASC";
        $dbPages=dbselectmulti($sql);
        if ($dbPages['numrows']>0)
        {
            foreach($dbPages['data'] as $page)
            {
                $section3_totalpages++;
                if ($page['color']){$section3_colorpages++;}else{$section3_bwpages++;}
            }   
        }
        $totalpages+=$section3_totalpages;
        $jobdetails.=  "<table>\n";
        $jobdetails.=  "<tr><th>Section Name</th><th>Letter</th><th>Format</th><th>Pages</th><th>Color</th><th>BW</th><th>Overrun</th></tr>\n";
        $jobdetails.=  "<tr>";
        $jobdetails.=  "<td>$section1_name</td>";
        $jobdetails.=  "<td>$section1_code</td>";
        $jobdetails.=  "<td>$section1_format</td>";
        $jobdetails.=  "<td>$section1_totalpages</td>";
        $jobdetails.=  "<td>$section1_colorpages</td>";
        $jobdetails.=  "<td>$section1_bwpages</td>";
        $jobdetails.=  "<td>$section1_overrun</td>";
        $jobdetails.=  "</tr>\n";
        
        $jobdetails.=  "<tr>";
        $jobdetails.=  "<td>$section2_name</td>";
        $jobdetails.=  "<td>$section2_code</td>";
        $jobdetails.=  "<td>$section2_format</td>";
        $jobdetails.=  "<td>$section2_totalpages</td>";
        $jobdetails.=  "<td>$section2_colorpages</td>";
        $jobdetails.=  "<td>$section2_bwpages</td>";
        $jobdetails.=  "<td>$section2_overrun</td>";
        $jobdetails.=  "</tr>\n";
        
        $jobdetails.=  "<tr>";
        $jobdetails.=  "<td>$section3_name</td>";
        $jobdetails.=  "<td>$section3_code</td>";
        $jobdetails.=  "<td>$section3_format</td>";
        $jobdetails.=  "<td>$section3_totalpages</td>";
        $jobdetails.=  "<td>$section3_colorpages</td>";
        $jobdetails.=  "<td>$section3_bwpages</td>";
        $jobdetails.=  "<td>$section3_overrun</td>";
        $jobdetails.=  "</tr>\n";
        
        $jobdetails.=  "<tr>";
        $jobdetails.=  "<td>Totals:</td>";
        $jobdetails.=  "<td></td>";
        $jobdetails.=  "<td></td>";
        $jobdetails.=  "<td>$totalpages</td>";
        $jobdetails.=  "<td>".($section1_colorpages+$section2_colorpages+$section3_colorpages)."</td>";
        $jobdetails.=  "<td>".($section1_bwpages+$section2_bwpages+$section3_bwpages)."</td>";
        $jobdetails.=  "<td>----</td>";
        $jobdetails.= "</tr>\n";
        
        $jobdetails.= "</table>\n";
        $jobdetails.= "</td></tr>\n";
    } else {
        $jobdetails.= "<tr><td colspan=6>No sections defined at this time</td></tr>\n";
    }
    
    
    
    
    if ($enableBenchmarks)
    {
        if ($dbBenchmarks['numrows']>0)
        {
            foreach($benchmarks as $benchmark)
            {
                $bname=$benchmark['benchmark_name'];
                if ($benchmark['benchmark_type']=='time')
                {
                    $goal=date("H:i",strtotime($benchmark['benchmark_goal_time']));
                    $actual=date("H:i",strtotime($benchmark['benchmark_actual_time']));
                    $difference=date("H:i",strtotime($benchmark['benchmark_difference']));
                } else {
                    $goal=$benchmark['benchmark_goal_number'];
                    $actual=$benchmark['benchmark_actual_number'];
                    $difference=$benchmark['benchmark_difference'];
                }
                $jobdetails.="<tr><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$bname</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$goal</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$actual</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$difference</td></tr>\n";
            }
        }
    }
    $jobdetails.="<tr><th style='font-weight:bold;background-color:black;color:white;'>Plate Details</th><th style='font-weight:bold;background-color:black;color:white;'>Pages on plate</th><th style='font-weight:bold;background-color:black;color:white;'>Approved</th><th style='font-weight:bold;background-color:black;color:white;'>Black at CTP</th><th style='font-weight:bold;background-color:black;color:white;'>Black out of bender</th><th style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Color out of bender</th></tr>\n";
    $sql="SELECT * FROM job_plates WHERE job_id=$job[id] ORDER BY section_code ASC, low_page ASC";
    $dbPlates=dbselectmulti($sql);
    if ($dbPlates['numrows']>0)
    {
        foreach($dbPlates['data'] as $plate)
        {
            //get all the pages that are on this plate
            $pid=$plate['id'];
            $sql="SELECT DISTINCT(page_number), color FROM job_pages WHERE plate_id=$pid AND page_number<>0 ORDER BY page_number ASC";
            $dbPlatePages=dbselectmulti($sql);
            $platecolor='Black';
            if ($dbPlatePages['numrows']>0)
            {
                $ppages="";
                foreach ($dbPlatePages['data'] as $platepage)
                {
                    $ppages.=" ".$platepage['page_number'];
                    if($platepage['color']){$platecolor='Full Color';}
                }
            }
            
            
            $jobdetails.= "<tr><td><b>$plate[section_code] - $plate[low_page]</b></td><td>$ppages</td><td>$plate[black_approval]</td><td>$plate[black_ctp]</td><td>$plate[black_receive]</td><td>$plate[cyan_receive]</td></tr>\n";
        }
    } else {
        $jobdetails.="<tr><th colspan=6>No plates defined for this run</th></tr>\n";
    }
    $jobdetails.="<tr><th style='font-weight:bold;background-color:black;color:white;'>Page Details</th><th style='font-weight:bold;background-color:black;color:white;'>Page Release</th><th style='font-weight:bold;background-color:black;color:white;'>Color Release</th><th style='font-weight:bold;background-color:black;color:white;'>Workflow Receive</th><th style='font-weight:bold;background-color:black;color:white;'>Page Ripped</th></tr>\n";
    $sql="SELECT * FROM job_pages WHERE job_id=$job[id] AND remake=0 ORDER BY section_code ASC, page_number ASC";
    $dbPages=dbselectmulti($sql);
    if ($dbPages['numrows']>0)
    {
        foreach($dbPages['data'] as $page)
        {
            $jobdetails.= "<tr><td><b>$page[section_code] - $page[page_number]</b></td><td>$page[page_release]</td><td>$page[color_release]</td><td>$page[workflow_receive]</td><td>$page[page_ripped]</td></tr>\n";
        }
    } else {
        $jobdetails.= "<tr><th colspan=6>No pages defined for this run</th></tr>\n";
    }
    
    if ($enableJobStops)
    {
        $jobdetails.="<tr><th colspan=6 style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Job stops</th></tr>\n";
        $sql="SELECT A.*, B.stop_name FROM job_stops A, stop_codes B WHERE A.job_id=$job[id] AND A.stop_code=B.id ORDER BY A.stop_datetime DESC";
        $dbStops=dbselectmulti($sql);
        if ($dbStops['numrows']>0)
        {
            foreach($dbStops['data'] as $stop)
            {
                $stoptime=date("H:i",strtotime($stop['stop_datetime']));
                $restarttime=date("H:i",strtotime($stop['stop_restartdatetime']));
                $downtime=($stop['stop_downtime']/60)." minutes";
                $name=$stop['stop_name'];
                $jobdetails.= "<tr><td colspan=3><b>$name</b></td>
                <td>$stoptime</td>
                <td>$restarttime</td>
                <td>$downtime</td>
                </tr>\n";
            }
        } else {
            $jobdetails.= "<tr><th colspan=4>No stops defined for this run</th></tr>\n";
        }
    }
    $jobdetails.= "</table>\n";
    
    //now add any notes about the job
    $jobdetails.="<br /><b>Job Notes:</b><br />".$job['notes_job'];
    $jobdetails.="<br /><br /><b>Press Notes:</b><br />".$job['notes_press'];
    $jobdetails.="<br /><br /><hr><br />\n";
    return $jobdetails; 
} 


function getInserterJobDetails($job)
{
    $jobdetails="<br />\n<br />\nInserter JOB goes here<br />\n";
    return $jobdetails;
}

function getMinVar($startdatetime,$enddatetime)
{
    $timePassed = $enddatetime-$startdatetime; //time passed in seconds
    // Minute == 60 seconds
    // Hour == 3600 seconds
    // Day == 86400
    // Week == 604800
    $elapsedString = "";
    if($timePassed > 604800)
    {
    $weeks = floor($timePassed / 604800);
    $timePassed -= $weeks * 604800;
    $elapsedString = $weeks." weeks, ";
    }
    if($timePassed > 86400)
    {
    $days = floor($timePassed / 86400);
    $timePassed -= $days * 86400;
    $elapsedString .= $days." days, ";
    }
    if($timePassed > 3600)
    {
    $hours = floor($timePassed / 3600);
    $timePassed -= $hours * 3600;
    $elapsedString .= $hours." hours, ";
    }
    if($timePassed > 60)
    {
    $minutes = floor($timePassed / 60);
    $timePassed -= $minutes * 60;
    $elapsedString .= $minutes." minutes, ";
    }
    $elapsedString .= $timePassed." seconds";

    return $elapsedString;
}
?>
