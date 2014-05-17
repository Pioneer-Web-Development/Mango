<?php
//<!--VERSION: .9 **||**-->
error_reporting(0);
include("includes/functions_db.php");
include("includes/mail/htmlMimeMail.php");
include("includes/config.php");

if ($_POST)
{
    $rundate=$_POST['year']."-".$_POST['month']."-".$_POST['day'];
    $enddate=$rundate." 6:00";
    $startdate=date("Y-m-d H:i",strtotime($enddate." -1 day"));
} else {
    if ($_GET['mode']=='test')
    {
        $startdate="2009-08-04 6:00";
        $enddate="2009-08-05 6:00";
        $rundate="2009-08-05";
    }elseif($_GET['mode']=='manual')
    {
        $y=date("Y");
        $m=date("m");
        $d=date("d");
        print "<form method=post>\n";
        print "<p>Manually generating email blast</p>\n";
        print "Year: <input name='year' value='$y'><br />"; 
        print "Month: <input name='month' value='$m'><br />"; 
        print "Day: <input name='day' value='$d'><br />"; 
        print "<input type='submit' name='submit' value='Send Email'>\n";
        print "</form>\n";
    } else {
        $startdate=date("Y-m-d",strtotime("-1 day"))." 6:00";
        $enddate=date("Y-m-d")." 6:00";
        $rundate=date("Y-m-d");

    }

}
global $siteID;
//ok, start by getting a list of groups that get email summaries
$sql="SELECT * FROM email_groups WHERE site_id=$siteID AND group_active=1";
$dbGroups=dbselectmulti($sql);

if ($dbGroups['numrows']>0)
{
    //we have people!
    foreach($dbGroups['data'] as $group)
    {
        $email=build_email($group);
        if ($email=='nopubs')
        {
            if($_GET['mode']=='test'){print "No publications were found.";}  
        } elseif ($email=='nojobs')
        {
            if($_GET['mode']=='test'){print "No jobs were found.";}  
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
            $mail->setFrom($GLOBALS['systemEmailFromAddress']);
            $mail->setSubject("Daily production run summary for $rundate");
            if ($_GET['mode']=='test'){$emailaddress='jhansen69@gmail.com';}
            $result = $mail->send(array($emailaddress));
            
            
            if ($_GET['mode']=='test'){print "Sent message to $emailaddress<br>$email<br><br>";}
            print "Sent message to $emailaddress<br>$email<br><br>";
            
            /****************************************************
            * NOTE: WE ARE KILLING THE PROCESS AFTER ONE EMAIL!!!
            *****************************************************/
        } else {
            if($_GET['mode']=='test'){print "No email found";}
        }
    }
} else {
    if($_GET['mode']=='test'){print "No users found";}
    
}
  
function build_email($group)
{
    global $startdate, $enddate;
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
            $sql="SELECT * FROM jobs WHERE pub_id=$pub[pub_id] AND startdatetime>='$startdate' AND enddatetime<='$enddate' AND pub_date<>'' ORDER BY startdatetime DESC";
            if ($_GET['mode']=='test'){print $sql."<br>";}
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
            $sql="SELECT * FROM jobs_inserter_plans WHERE pub_id=$pub[pub_id] AND pub_date='$startdate' ORDER BY startdatetime DESC";
            if ($_GET['mode']=='test'){print $sql."<br>";}
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
    
    $sql="SELECT run_name FROM publications_runs WHERE id=$job[run_id]";
    $dbRun=dbselectsingle($sql);
    
    $sql="SELECT A.benchmark_name, A.benchmark_category, A.benchmark_order, B.* FROM benchmarks A, job_benchmarks B WHERE A.id=B.benchmark_id AND B.job_id=$job[id] ORDER BY A.benchmark_category, A.benchmark_order ASC";
    $dbBenchmarks=dbselectmulti($sql);
    $benchmarks=$dbBenchmarks['data'];
    
    $sql="SELECT * FROM job_stats WHERE job_id=$job[id]";
    $dbStats=dbselectsingle($sql);
    $stats=$dbStats['data'];
    //print_r($stats);
    //first, print out the name of the publication in biggish letters
    
    //now the table
    $jobdetails="<small>JOB ID: $job[id]</small><br />\n";
    $jobdetails.="<table border=1 style='style='font-family:arial;font-size:10pt;background-color:black;width:650px;border-style:solid;border-color:black;border-width:2px;
'>\n";
    $counterstart=$stats['counter_start'];
    $counterstop=$stats['counter_stop'];
    $startupspoils=$stats['spoils_startup'];
    $draw=$job['draw'];
    $runningspoils=$stats['spoils_running'];
    $totalspoils=$stats['spoils_total'];
    $operator=$pressmen[$stats['job_pressoperator']];
    $paperDb=dbselectsingle("SELECT * FROM paper_types WHERE id=$job[papertype]");
    $paper=$paperDb['data']['common_name'];
    $jobdetails.="<tr><th colspan=4 style='font-size:16pt;font-weight:bold;background-color:black;color:white;'>".$dbPub['data']['pub_name']."</th></tr>\n";
    $jobdetails.="<tr><th colspan=2 style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Job Name: ".$dbRun['data']['run_name']."</th>
    <th colspan=2 style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Pub date: $job[pub_date]</th></tr>";
    $jobdetails.="<tr><th colspan=2 style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Lead Operator: $operator</th>
    <th colspan=2 style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Base paper: $paper</th></tr>\n"; 
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Counter Start: $counterstart</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Counter Stop: $counterstop</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Startup Spoils: $startupspoils</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Running Spoils: $runningspoils</td></tr>\n";
    $jobdetails.="<tr><th style='font-size:10pt;font-weight:bold;background-color:black;color:white;'></th><th style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Goal</th><th style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Actual</th><th style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Variance</th></tr>\n"; 
    //first some info about the job
    $jobdetails.="<tr><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Start Time</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[startdatetime_goal]</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[startdatetime_actual]</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[start_offset]</td></tr>\n";
    $jobdetails.="<tr><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Good Copy</td><td colspan=3 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>".$stats['goodcopy_actual']."</td></tr>\n";
    $jobdetails.="<tr><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Stop Time</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[stopdatetime_goal]</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[stopdatetime_actual]</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[finish_offset]</td></tr>\n";
    $jobdetails.="<tr><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Draw Request</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$job[draw]</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[gross]</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[spoils_total]</td></tr>\n";
    
    //now just a bunch of stats
    $jobdetails.="<tr><th colspan=2 style='font-size:10pt;font-weight:bold;background-color:black;color:white;'></th><th colspan=2 style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Value</th></tr>\n"; 
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Waste %</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[waste_percent]%</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Downtime</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[total_downtime] minutes</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Running Time</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[run_time] minutes</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Scheduled Running Time</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[sched_runtime] minutes</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Gross average speed</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[run_speed] copies/hr</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Net average speed</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[good_runspeed] copies/hr</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Total Pressman</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[job_pressman_count] pressman</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Pages BW</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[pages_bw] pages (broadsheet eqv.)</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Pages Color</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[pages_color] pages (broadsheet eqv.)</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Plates BW</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[plates_bw] plates</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Plates Color</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[plates_color] plates</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Plates remade</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[plates_remake] plates</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Plates wasted</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[plates_waste] plates</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Last Page</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[last_page]</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Last Color Page</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[last_colorpage]</td></tr>\n";
    $jobdetails.="<tr><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>Last Plate</td><td colspan=2 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stats[last_plate]</td></tr>\n";
    
    $jobdetails.= "<tr><td colspan=4 style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:center;'>Section Information</td></tr>\n";
    //get sectioninfo
    $sql="SELECT * FROM jobs_sections WHERE job_id=$job[id]";
    $dbSections=dbselectsingle($sql);
    if ($dbSections['numrows']>0)
    {
        $jobdetails.= "<tr><td colspan=4>\n";
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
        $jobdetails.= "<tr><td colspan=4>No sections defined at this time</td></tr>\n";
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
    $jobdetails.="<tr><th style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Plate Details</th><th style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Approved</th><th style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Black out of bender</th><th style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Color out of bender</th></tr>\n";
    $sql="SELECT * FROM job_plates WHERE job_id=$job[id] ORDER BY section_code ASC, low_page ASC";
    $dbPlates=dbselectmulti($sql);
    if ($dbPlates['numrows']>0)
    {
        foreach($dbPlates['data'] as $plate)
        {
            $jobdetails.= "<tr><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$plate[section_code] - $plate[low_page]</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$plate[black_approval]</td>
            <td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$plate[black_receive]</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$plate[cyan_receive]</td></tr>\n";
        }
    } else {
        $jobdetails.="<tr><th colspan=4>No plates defined for this run</th></tr>\n";
    }
    $jobdetails.="<tr><th style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Page Details</th><th style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Page Release</th><th style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Color Release</th></tr>\n";
    $sql="SELECT * FROM job_pages WHERE job_id=$job[id] AND version=1 ORDER BY section_code ASC, page_number ASC";
    $dbPages=dbselectmulti($sql);
    if ($dbPages['numrows']>0)
    {
        foreach($dbPages['data'] as $page)
        {
            $jobdetails.= "<tr><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$page[section_code] - $page[page_number]</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$page[page_release]</td><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$page[color_release]</td></tr>\n";
        }
    } else {
        $jobdetails.= "<tr><th colspan=4>No pages defined for this run</th></tr>\n";
    }
    
    if ($enableJobStops)
    {
        $jobdetails.="<tr><th colspan=4 style='font-size:10pt;font-weight:bold;background-color:black;color:white;'>Job stops</th></tr>\n";
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
                $jobdetails.= "<tr><td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$name</td>
                <td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$stoptime</td>
                <td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$restarttime</td>
                <td style='font-size:10pt;background-color:#ccc;color:black;border-style:solid;border-width:1px;text-align:right;'>$downtime</td>
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
