<?php
//this would need to be updated.... 
//@todo need to come up with a better way to reference this...
//<!--VERSION: .9 **||**-->
if($_GET['mode']=='manual')
{
    include("../includes/functions_db.php");
    include("../includes/functions_formtools.php");
    include("../includes/mail/htmlMimeMail.php");
    include("../includes/config.php");
    $filename=$_SERVER['SCRIPT_NAME'];
} else {
    $filename=$GLOBALS['scriptFilename'];
}        
$sql="SELECT * FROM email_reports WHERE report_filename='".addslashes($filename)."'";
$dbReportData=dbselectsingle($sql);
$reportid=$dbReportData['data']['id'];

if ($_POST)
{
    $rundate=$_POST['date'];
    $enddate=$rundate." 6:00";
    $startdate=date("Y-m-d H:i",strtotime($enddate." -1 day"));
} else {
    if ($_GET['mode']=='test')
    {
        $startdate=date("Y-m-d",strtotime("-1 day"))." 6:00";
        $enddate=date("Y-m-d")." 6:00";
        $rundate=date("Y-m-d");
        
    }elseif($_GET['mode']=='manual')
    {
        print "<form method=post>\n";
        print "<p>Manually generating email blast</p>\n";
        make_date('date',date("Y-m-d"),'Pub Date');
        make_text('to','','Recipient','Send report to email address');
        make_checkbox('noemail',0,'Show on screen','Checked, report will be displayed on screen');
        make_hidden('mode','manual');
        print "<input type='submit' name='submit' value='Send Email'>\n";
        print "</form>\n";
        die(); 
    } else {
        $startdate=date("Y-m-d",strtotime("-1 day"))." 6:00";
        $enddate=date("Y-m-d")." 6:00";
        $rundate=date("Y-m-d");
    }

}
global $siteID;
//ok, start by getting a list of users that get this report
$sql="SELECT email
FROM
users AS A ,
user_reports AS B
WHERE
A.id = B.user_id AND
B.report_id = $reportid";
$dbRecipients=dbselectmulti($sql);
print "Running with ".$dbRecipients['numrows']." folks to send to.<br />Startdate: $startdate<br />";
if ($dbRecipients['numrows']>0 || $_POST['to']!='' || $_POST['mode']=='manual')
{
    $email=build_email($reportid);
    if($_POST['to']!='')
    {
            $emailaddresses[]=$_POST['to'];
    } else {
        foreach($dbRecipients['data'] as $recipient)
        {
           $emailaddresses[]=stripslashes($recipient['email']); 
        }
    
    }
    $mail = new htmlMimeMail();
    $summary='';
    $details='';        
    $message="<html><head></head><body>";
    if(count($email)>0)
    {
        foreach($email as $part)
        {
            $summary.=$part['summary']."<br>";
            $details.=$part['job']."<br>";
        }
    }
    $message.=$summary.$details."</body></html>\n";
    $mail->setHtml($message);
    
    /**
    * Sends the message.
    */
    $mail->setFrom($GLOBALS['systemEmailFromAddress']);
    $mail->setSubject("Daily production run summary for $rundate");
    
    if($_POST['noemail'])
    {
        //dont send this!
        print $message;
        print "Running once with $rundate as date<br>";
        print "<pre>";
        print_r($emailaddresses);
        print "</pre>";
    
        print "Sent message to $emailaddresses[0]<br><br>";
            
    } else {
        $result = $mail->send($emailaddresses);
    }
            
} else {
    print "No users found";    
}
  
function build_email($reportid)
{
    global $startdate, $enddate;
    //ok, we need to get the publication that is selected for this report
    $sql="SELECT pub_id, run_id FROM email_reports WHERE id=$reportid";
    $dbPubs=dbselectsingle($sql);
    $pubid=$dbPubs['data']['pub_id'];
    $email=array();
    if($pubid!=0)
    {
        $sql="SELECT A.* FROM jobs A, publications_runs B WHERE A.run_id=B.id AND B.reportable=1 AND A.pub_id=$pubid AND A.startdatetime>='$startdate' AND A.enddatetime<='$enddate' AND A.pub_date<>'' ORDER BY A.startdatetime DESC";
    } else {
      $sql="SELECT A.* FROM jobs A, publications_runs B WHERE A.run_id=B.id AND B.reportable=1 AND A.startdatetime>='$startdate' AND A.enddatetime<='$enddate' AND A.pub_date<>'' ORDER BY A.startdatetime DESC";  
    }
    if ($_GET['mode']=='test' || $_POST['mode']=='manual'){print $sql."<br>";}
    $dbJobs=dbselectmulti($sql);
    if ($dbJobs['numrows']>0)
    {
        foreach ($dbJobs['data'] as $job)
        {
            $email[]=getJobDetails($job);    
        }
    }
    return $email;
     
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
    $run=$dbRun['data'];
    //get total number of plate sets
	$sql="SELECT * FROM job_plates WHERE current=1 AND job_id=$jobid";
	$dbTotalPlates=dbselectmulti($sql);
	$totalplates=$dbTotalPlates['numrows'];
	
    //what day of the week is the pub date... will affect which leadtimes to grab from
    $dow=date("N",strtotime($job['pub_date']));
    $xlastcolor=$run['last_colorpage_leadtime_'.$dow];
    $xlastpage=$run['last_page_leadtime_'.$dow];
    $xlastplate=$run['last_plate_leadtime_'.$dow];
    $xlast2=$run['plates_2_left_leadtime_'.$dow];
    $xlast3=$run['plates_3_left_leadtime_'.$dow];
    $xlast4=$run['plates_4_left_leadtime_'.$dow];
    $xlast5=$run['plates_5_left_leadtime_'.$dow];
    $xlast6=$run['plates_6_left_leadtime_'.$dow];
    $xschedule=$run['schedule_leadtime_'.$dow]; //hours
    $xchaseplate=$run['chase_plate_aftertime_'.$dow];
    $xchasestart=$run['chase_start_aftertime_'.$dow];
    $xrunlength=$run['run_length_'.$dow];
    
	//lets calculate page flow  --get how many at press start time
    $pstart=date("Y-m-d H:i:s",strtotime($job['startdatetime']));
    //how many plates out after this point?
    $sql="SELECT * FROM job_plates WHERE remake=0 AND current=1 AND (plate_approval>='$pstart' OR plate_approval IS NULL) AND job_id=$jobid";
    $dbPlates=dbselectmulti($sql);
    $platesatstart=$dbPlates['numrows'];
    
    $platesatstartTotal=0;
    if($dbPlates['numrows']>0)
    {
        foreach($dbPlates['data'] as $temp)
        {
            if($temp['color'])
            {
                $platesatstartTotal=$platesatstartTotal+4;
            } else {
                $platesatstartTotal++;
            }
        }
    }
        
    //how many pages out after this point?
    $sql="SELECT * FROM job_pages WHERE remake=0 AND (workflow_receive>='$pstart' OR workflow_receive IS NULL) AND job_id=$jobid";
    $dbPages=dbselectmulti($sql);
    $pagesatstart=$dbPages['numrows'];
    
    //lets calculate page flow  --get how many at 5 minutes beforepress start time
	$pstartminus5=date("Y-m-d H:i:s",strtotime($job['startdatetime']." -5 minutes"));
    //how many plates out after this point?
    $sql="SELECT * FROM job_plates WHERE remake=0 AND (plate_approval>='$pstartminus5' OR plate_approval IS NULL) AND job_id=$jobid";
    $dbPlates=dbselectmulti($sql);
    $platesatstartminus5=$dbPlates['numrows'];
    
    $platesatstartTotalMinus5=0;
    if($dbPlates['numrows']>0)
    {
        foreach($dbPlates['data'] as $temp)
        {
            if($temp['color'])
            {
                $platesatstartTotalMinus5=$platesatstartTotalMinus5+4;
            } else {
                $platesatstartTotalMinus5++;
            }
        }
    }    
    
    //how many pages out after this point?
    $sql="SELECT * FROM job_pages WHERE remake=0 AND (page_release>='$pstartminus5' OR page_release IS NULL) AND job_id=$jobid";
    $dbPages=dbselectmulti($sql);
    $pagesatstartminus5=$dbPages['numrows'];
    
    
    if($xlastplate!=0)
	{
		$lastplatetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-$xlastplate minutes"));
		//how many plates out after this point?
		$sql="SELECT * FROM job_plates WHERE remake=0 AND (plate_approval>='$lastplatetime' OR plate_approval IS NULL) AND job_id=$jobid";
		//print "starttiem: $job[startdatetime] leadtime:".$dbRun['data']['last_plate_leadtime'].'<br>'.$sql."<br>";
		$dbPlates=dbselectmulti($sql);
		$lastplatecount=$dbPlates['numrows'];
		if($lastplatecount>1)
        {
            $outlast="<span style='color:red;font-weight:bold'>$lastplatecount SETS OUT</span>";
        } else {
            $outlast="<span style='color:green;font-weight:bold'>GOAL MET</span>";
        }
        
        $lastplatecountTotal=0;
        if($dbPlates['numrows']>0)
        {
            foreach($dbPlates['data'] as $temp)
            {
                if($temp['color'])
                {
                    $lastplatecountTotal=$lastplatecountTotal+4;
                } else {
                    $lastplatecountTotal++;
                }
            }
        }
        
        //how many pages out after this point?
		$sql="SELECT * FROM job_pages WHERE remake=0 AND (page_release>='$lastplatetime' OR page_release IS NULL) AND job_id=$jobid";
		//print "starttiem: $job[startdatetime] leadtime:".$dbRun['data']['last_plate_leadtime'].'<br>'.$sql."<br>";
		$dbPages=dbselectmulti($sql);
		$lastpagecount=$dbPages['numrows'];
	}
	if($xlast2!=0)
	{
		$last2platetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-$xlast2 minutes"));
		//how many plates out after this point?
		$sql="SELECT * FROM job_plates WHERE remake=0 AND (plate_approval>='$last2platetime' OR plate_approval IS NULL) AND job_id=$jobid";
		$dbPlates=dbselectmulti($sql);
		$last2platecount=$dbPlates['numrows'];
		if($last2platecount>2)
        {
            $outlast2="<span style='color:red;font-weight:bold'>$last2platecount SETS OUT</span>";
        } else {
            $outlast2="<span style='color:green;font-weight:bold'>GOAL MET</span>";
        }
        
        $last2platecountTotal=0;
        if($dbPlates['numrows']>0)
        {
            foreach($dbPlates['data'] as $temp)
            {
                if($temp['color'])
                {
                    $last2platecountTotal=$last2platecountTotal+4;
                } else {
                    $last2platecountTotal++;
                }
            }
        }
        
        
		$sql="SELECT * FROM job_pages WHERE remake=0 AND (page_release>='$last2platetime' OR page_release IS NULL) AND job_id=$jobid";
		$dbPages=dbselectmulti($sql);
		$last2pagecount=$dbPages['numrows'];
	}
	if($xlast3!=0)
	{
		$last3platetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-$xlast3 minutes"));
		//how many plates out after this point?
		$sql="SELECT * FROM job_plates WHERE remake=0 AND (plate_approval>='$last3platetime' OR plate_approval IS NULL) AND job_id=$jobid";
		$dbPlates=dbselectmulti($sql);
		$last3platecount=$dbPlates['numrows'];
		if($last3platecount>3)
        {
            $outlast3="<span style='color:red;font-weight:bold'>$last3platecount SETS OUT</span>";
        } else {
            $outlast3="<span style='color:green;font-weight:bold'>GOAL MET</span>";
        }
        
        $last3platecountTotal=0;
        if($dbPlates['numrows']>0)
        {
            foreach($dbPlates['data'] as $temp)
            {
                if($temp['color'])
                {
                    $last3platecountTotal=$last3platecountTotal+4;
                } else {
                    $last3platecountTotal++;
                }
            }
        }
        
		$sql="SELECT * FROM job_pages WHERE remake=0 AND (page_release>='$last3platetime' OR page_release IS NULL) AND job_id=$jobid";
		$dbPages=dbselectmulti($sql);
		$last3pagecount=$dbPages['numrows'];
	}
	if($xlast4!=0)
	{
		$last4platetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-$xlast4 minutes"));
		//how many plates out after this point?
		$sql="SELECT * FROM job_plates WHERE remake=0 AND (plate_approval>='$last4platetime' OR plate_approval IS NULL) AND job_id=$jobid";
		$dbPlates=dbselectmulti($sql);
		$last4platecount=$dbPlates['numrows'];
		if($last4platecount>4)
        {
            $outlast4="<span style='color:red;font-weight:bold'>$last4platecount SETS OUT</span>";
        } else {
            $outlast4="<span style='color:green;font-weight:bold'>GOAL MET</span>";
        }
        $last4platecountTotal=0;
        if($dbPlates['numrows']>0)
        {
            foreach($dbPlates['data'] as $temp)
            {
                if($temp['color'])
                {
                    $last4platecountTotal=$last4platecountTotal+4;
                } else {
                    $last4platecountTotal++;
                }
            }
        }
        $sql="SELECT * FROM job_pages WHERE remake=0 AND (page_release>='$last4platetime' OR page_release IS NULL) AND job_id=$jobid";
		$dbPages=dbselectmulti($sql);
		$last4pagecount=$dbPages['numrows'];
	}
	if($xlast5!=0)
	{
		$last5platetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-$xlast5 minutes"));
		//how many plates out after this point?
		$sql="SELECT * FROM job_plates WHERE remake=0 AND (plate_approval>='$last5platetime' OR plate_approval IS NULL) AND job_id=$jobid";
		$dbPlates=dbselectmulti($sql);
		$last5platecount=$dbPlates['numrows'];
		if($last5platecount>5)
        {
            $outlast5="<span style='color:red;font-weight:bold'>$last5platecount SETS OUT</span>";
        } else {
            $outlast5="<span style='color:green;font-weight:bold'>GOAL MET</span>";
        }
        
		$last5platecountTotal=0;
        if($dbPlates['numrows']>0)
        {
            foreach($dbPlates['data'] as $temp)
            {
                if($temp['color'])
                {
                    $last5platecountTotal=$last5platecountTotal+4;
                } else {
                    $last5platecountTotal++;
                }
            }
        }
        
        $sql="SELECT * FROM job_pages WHERE remake=0 AND (page_release>='$last5platetime' OR page_release IS NULL) AND job_id=$jobid";
		$dbPages=dbselectmulti($sql);
		$last5pagecount=$dbPages['numrows'];
	}
	if($xlast6!=0)
	{
		$last6platetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-$xlast6 minutes"));
		//how many plates out after this point?
		$sql="SELECT * FROM job_plates WHERE remake=0 AND (plate_approval>='$last6platetime' OR plate_approval IS NULL) AND job_id=$jobid";
		$dbPlates=dbselectmulti($sql);
		$last6platecount=$dbPlates['numrows'];
		if($last6platecount>6)
        {
            $outlast6="<span style='color:red;font-weight:bold'>$last6platecount SETS OUT</span>";
        } else {
            $outlast6="<span style='color:green;font-weight:bold'>GOAL MET</span>";
        }
        
		$last6platecountTotal=0;
        if($dbPlates['numrows']>0)
        {
            foreach($dbPlates['data'] as $temp)
            {
                if($temp['color'])
                {
                    $last6platecountTotal=$last6platecountTotal+4;
                } else {
                    $last6platecountTotal++;
                }
            }
        }
        
        $sql="SELECT * FROM job_pages WHERE remake=0 AND (page_release>='$last6platetime' OR page_release IS NULL) AND job_id=$jobid";
		$dbPages=dbselectmulti($sql);
		$last6pagecount=$dbPages['numrows'];
	}
	if($xchaseplate!=0)
	{
		$chaseplatetime=date("Y-m-d H:i:s",strtotime($job['startdatetime']."-$xchaseplate minutes"));
		//how many plates out after this point?
		$sql="SELECT * FROM job_plates WHERE remake=1 AND current=1 AND (black_approval>='$chaseplatetime' OR black_approval IS NULL) AND job_id=$jobid";
		$dbPlates=dbselectmulti($sql);
		$chaseplatecount=$dbPlates['numrows'];
	}

    
	//calculate last remake page information
    $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND remake=1 ORDER BY page_release DESC LIMIT 1";
    $dbMaxPage=dbselectsingle($sql);
    $lastchasepage=$dbMaxPage['data']['page_number'].$dbMaxPage['data']['section_code'];
    $alastchasepagetime=$dbMaxPage['data']['page_release'];

    //calculate last page information
	$sql="SELECT * FROM job_pages WHERE job_id=$jobid AND remake=0 ORDER BY workflow_receive DESC LIMIT 1";
	$dbMaxPage=dbselectsingle($sql);
	$lastpage=$dbMaxPage['data']['page_number'].$dbMaxPage['data']['section_code'];
	$alastpagetime=$dbMaxPage['data']['workflow_receive'];

	//calculate last color page information
	$sql="SELECT * FROM job_pages WHERE job_id=$jobid AND remake=0 AND color=1 ORDER BY workflow_receive DESC LIMIT 1";
	$dbMaxColorPage=dbselectsingle($sql);
	$lastcolorpage=$dbMaxColorPage['data']['page_number'].$dbMaxColorPage['data']['section_code'];
	$alastcolorpagetime=$dbMaxColorPage['data']['workflow_receive'];

	//calculate last plate information
	$sql="SELECT low_page, black_approval, plate_approval, section_code FROM job_plates WHERE job_id=$jobid AND remake=0 ORDER BY plate_approval DESC LIMIT 1";
	$dbMaxPage=dbselectsingle($sql);
	$lastplate=$dbMaxPage['data']['low_page'].$dbMaxPage['data']['section_code'];
	$alastplatetime=$dbMaxPage['data']['black_approval'];

	$sql="SELECT * FROM job_stats WHERE id=$job[stats_id]";
	$dbStats=dbselectsingle($sql);
	$stats=$dbStats['data'];
	
    $counterstart=$stats['counter_start'];
    $counterstop=$stats['counter_stop'];
    $startupspoils=$stats['spoils_startup'];
    $draw=$job['draw'];
    $runningspoils=$stats['spoils_running'];
    $totalspoils=$stats['spoils_total'];
    $operator=$pressmen[$stats['job_pressoperator']];
    $paperDb=dbselectsingle("SELECT * FROM paper_types WHERE id=$job[papertype]");
    $paper=$paperDb['data']['common_name'];

    $targetstop=date("Y-m-d H:i:s",strtotime($stats['startdatetime_goal']."+$xrunlength minutes"));
    $actualstop=$stats['stopdatetime_actual'];
    if(strtotime($actualstop)>strtotime($stats['startdatetime_goal']."+$xrunlength minutes"))
    {
        $stopgoal="<span style='color:red;font-weight:bold;'>LATE: ".getMinVar(strtotime($stats['startdatetime_goal']."+$xrunlength minutes"),strtotime($actualstop),false)."</span>\n";
    } else {
        $stopgoal="<span style='color:green;font-weight:bold;'>ON TIME</span>";
    }
    
    if($stats['start_offset']>0)
    {
        $late="<span style='color:red;font-weight:bold;'>LATE: ".getMinVar(strtotime($stats['startdatetime_goal']),strtotime($stats['startdatetime_actual']),false)."</span>\n";
    } else {
        $late="<span style='color:green;font-weight:bold;'>ON TIME</span>";
    }
    //create the little summary box
    $summary="<span style='font-size:16px;font-weight:bold;'>".$dbPub['data']['pub_name'].' - '.$dbRun['data']['run_name'].' publishing on '.$job['pub_date']."</span><br>\n";
    $summary.="<table style='font-family:Trebuchet MS;font-size:12px;width:400px;border-style:solid;border-color:black;border-width:2px;'>\n";
    $summary.="<tr><th colspan=2 style='background-color:#CCC;color:black;'>Process/Procedure</th>
    <th style='background-color:#CCC;color:black;'>Target</th><th style='background-color:#CCC;color:black;'>Message</th></tr>\n";
    $summary.="<tr><td colspan=2>3 Plate Sets Remaining</td><td>".date("H:i:s",strtotime($last3platetime))."</td><td>$outlast3</td></tr>\n";
    $summary.="<tr><td colspan=2>2 Plate Sets Remaining</td><td>".date("H:i:s",strtotime($last2platetime))."</td><td>$outlast2</td></tr>\n";
    $summary.="<tr><td colspan=2>Last plate set received</td><td>".date("H:i:s",strtotime($lastplatetime))."</td><td>$outlast</td></tr>\n";
    $summary.="<tr><td colspan=2>Press Start</td><td>".date("H:i:s",strtotime($stats['startdatetime_goal']))."</td><td>$late</td></tr>\n";
    if($lastchasepage=='')
    {
        $summary.="<tr><td colspan=2>Last chase page released</td><td>No chase pages</td></tr>\n";
    } else {
        $summary.="<tr><td colspan=2>Last chase page released</td><td>$lastchasepage at ".date("H:i:s",strtotime($alastchasepagetime))."</td></tr>\n";
    }
    
    $summary.="<tr><td colspan=2>Press Stop</td><td>$targetstop</td><td>$stopgoal</td></tr>\n";
    $summary.="</table>\n";
	//now the table
	$jobdetails="<small>JOB ID: $job[id]</small><br />\n";
	$jobdetails.="<table border=1 style='font-family:Trebuchet MS;font-size:12px;width:800px;border-style:solid;border-color:black;border-width:2px;'>\n";
	
	$jobdetails.="<tr><th colspan=6 style='font-size:16px;font-weight:bold;background-color:#CCC;color:black;'>".$dbPub['data']['pub_name'].' - '.$dbRun['data']['run_name'].' publishing on '.$job['pub_date']."</th></tr>\n";

	$jobdetails.="<tr><th colspan=3><b>Lead Operator:</b> $operator</th><th colspan=3><b>Base paper:</b> $paper</th></tr>\n";
	$jobdetails.="<tr><td colspan=3><b>Counter Start:</b> $counterstart</td><td colspan=3><b>Counter Stop:</b> $counterstop</td></tr>\n";
	$jobdetails.="<tr><td colspan=2><b>Startup Spoils:</b> $startupspoils</td><td colspan=2><b>Running Spoils:</b> $runningspoils</td><td colspan=2><b>Total Spoils:</b> $totalspoils</td></tr>\n";
	$jobdetails.="<tr><th colspan=3 style='font-weight:bold;background-color:#CCC;color:black;'>Press Times</th><th style='font-weight:bold;background-color:#CCC;color:black;'>Goal</th><th colspan=1 style='font-weight:bold;background-color:#CCC;color:black;'>Actual</th><th colspan=1 style='font-weight:bold;background-color:#CCC;color:black;'>Variance</th></tr>\n";
    //first some info about the job
	$jobdetails.="<tr><td colspan=3><b>Start Time</b></td><td>$stats[startdatetime_goal]</td><td>$stats[startdatetime_actual]</td><td>".getMinVar(strtotime($stats['startdatetime_goal']),strtotime($stats['startdatetime_actual']),true)."</td></tr>\n";
    $jobdetails.="<tr><td colspan=3><b>Good Copy</b></td><td colspan=3>".$stats['goodcopy_actual']."</td></tr>\n";
    $jobdetails.="<tr><td colspan=3><b>Stop Time</b></td><td>$targetstop</td><td>$stats[stopdatetime_actual]</td><td>".getMinVar(strtotime($stats['startdatetime_goal']."+$xrunlength minutes"),strtotime($stats['stopdatetime_actual']),true)."</td></tr>\n";
    $jobdetails.="<tr><td colspan=3><b>Draw Request</b></td><td >$job[draw]</td><td>$stats[gross]</td><td>$stats[spoils_total]</td></tr>\n";
    $jobdetails.="</tr>\n"; 
	
    $jobdetails.="<tr><th colspan=2 style='background-color:#CCC;color:black;'><b>Page & Plate Flow</b></th><th style='background-color:#CCC;color:black;'><b>Value</b></th>";
    $jobdetails.="</tr>\n"; 
    $jobdetails.="<td colspan=2><b>Last Plate</b></td><td>$lastplate at $alastplatetime</td>\n";
	$jobdetails.="</tr>\n";

    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2><b>Last Page (received at workflow)</b></td><td>$lastpage at $alastpagetime</td>\n";
    $jobdetails.="</tr>\n";

	$jobdetails.="<tr>";
	$jobdetails.="<td colspan=2><b>Last Color Page</b></td><td>$lastcolorpage at $alastcolorpagetime</td>\n";
    $jobdetails.="</tr>\n";
    $jobdetails.="</table>\n";
    
    
    //starting new table here
    $jobdetails.="<table border=1 style='font-family:Trebuchet MS;font-size:12px;width:800px;border-style:solid;border-color:black;border-width:2px;'>\n";
    
	$jobdetails.="<tr><th colspan=2 style='background-color:#CCC;color:black;'><b>Last Page & Plate times</b></th><th style='background-color:#CCC;color:black;'><b>Plate Sets Remaining</b></th>
    	<th style='background-color:#CCC;color:black;'><b>Plates Remaining</b></th><th style='background-color:#CCC;color:black;'><b>Pages Remaining</b></th></tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2><b>At press start time</b></td><td>$platesatstart</td>
    <td>$platesatstartTotal</td><td>$pagesatstart</td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2><b>5 minutes before start</b></td><td>$platesatstartminus5</td>
    <td>$platesatstartTotalMinus5</td><td>$pagesatstartminus5</td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
	$jobdetails.="<td colspan=2><b>$xlast2 minutes before start</b></td><td>$last2platecount</td>
    <td>$last2platecountTotal</td><td>$last2pagecount</td>\n";
    $jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
	$jobdetails.="<td colspan=2><b>$xlast3 minutes before start</b></td><td>$last3platecount</td>
    <td>$last3platecountTotal</td><td>$last3pagecount</td>\n";
    $jobdetails.="</tr>\n";

    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2><b>$xlast4 minutes before start</b></td><td>$last4platecount</td>
    <td>$last4platecountTotal</td><td>$last4pagecount</td>\n";
    $jobdetails.="</tr>\n";

    $jobdetails.="<tr>";
	$jobdetails.="<td colspan=2><b>$xlast5 minutes before start</b></td><td>$last5platecount</td>
    <td>$last5platecountTotal</td><td>$last5pagecount</td>\n";
    $jobdetails.="</tr>\n";

	$jobdetails.="<tr>";
	$jobdetails.="<td colspan=2><b>$xlast6 minutes before start</b></td><td>$last6platecount</td>
    <td>$last6platecountTotal</td><td>$last6pagecount</td>\n";
	$jobdetails.="</tr>\n";
    
    $jobdetails.="<tr>";
    $jobdetails.="<td colspan=2><b>Chase plates $xchaseplate minutes after start</b></td>
    <td>$chaseplatecount</td><td></td><td></td>\n";
    $jobdetails.="</tr>\n";
    $jobdetails.="</table>\n";
    
    
    //starting new table here
    
	$jobdetails.="<table border=1 style='font-family:Trebuchet MS;font-size:12px;width:800px;border-style:solid;border-color:black;border-width:2px;'>\n";
    $jobdetails.="<tr><th style='font-weight:bold;background-color:#CCC;color:black;'>Plate Details</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Pages on plate</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Last page for<br>plate released</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Waiting Approval</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Approved</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Black at CTP</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Wait Time</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Process Time</th>
    </tr>\n";
    $sql="SELECT * FROM job_plates WHERE job_id=$job[id] AND remake=0 ORDER BY section_code ASC, low_page ASC";
    $dbPlates=dbselectmulti($sql);
    if ($dbPlates['numrows']>0)
    {
        foreach($dbPlates['data'] as $plate)
        {
            //get all the pages that are on this plate
            $pid=$plate['id'];
            $sql="SELECT DISTINCT(page_number), color, ftp_receive, page_release FROM job_pages WHERE plate_id=$pid AND page_number<>0 AND remake=0 ORDER BY page_number ASC";
            $dbPlatePages=dbselectmulti($sql);
            $platecolor='B/W';
            $tptime=strtotime("1/1/71");
            $trtime=strtotime("1/1/71");
            $ppages="";
            if ($dbPlatePages['numrows']>0)
            {
                foreach ($dbPlatePages['data'] as $platepage)
                {
                    if(strtotime($platepage['ftp_receive'])>$tptime)
                    {
                        $tptime=strtotime($platepage['ftp_receive']);
                    }
                    if(strtotime($platepage['page_release'])>$trtime)
                    {
                        $trtime=strtotime($platepage['page_release']);
                    }
                    $ppages.=" ".$platepage['page_number'];
                    if($platepage['color']){
						$platecolor='Color';
					}
                } 
            } else {
                //have an oddness, lets look for a remake plate
                $backupsql="SELECT * FROM job_plates WHERE job_id=$job[id] AND remake=1 
                AND pub_id='$plate[pub_id]' AND pub_date='$plate[pub_date]' 
                AND section_code='$plate[section_code]' AND low_page='$plate[low_page]'";
                $dbBackup=dbselectsingle($backupsql);
                if($dbBackup['numrows']>0)
                {
                    $pid=$dbBackup['data']['id'];
                
                    $sql="SELECT DISTINCT(page_number), color, ftp_receive, page_release FROM job_pages WHERE plate_id=$pid AND page_number<>0 AND remake=0 ORDER BY page_number ASC";
                    $dbPlatePages=dbselectmulti($sql);
                    $platecolor='Black';
                    if ($dbPlatePages['numrows']>0)
                    {
                        foreach ($dbPlatePages['data'] as $platepage)
                        {
                            if(strtotime($platepage['ftp_receive'])>$tptime)
                            {
                                $tptime=strtotime($platepage['ftp_receive']);
                            }
                            if(strtotime($platepage['page_release'])>$trtime)
                            {
                                $trtime=strtotime($platepage['page_release']);
                            }
                            $ppages.=" ".$platepage['page_number'];
                            if($platepage['color']){
                                $platecolor='Full Color';
                            }
                        }
                    }
                }                       
            }
            if($plate['plate_waiting']!='')
            {
                $pw=date("H:i:s",strtotime($plate['plate_waiting']));
                print "Process time calculated with ".date("H:i:s",$tptime).' and '.$plate['plate_waiting']."<br>";
                $processtime=getMinVar($tptime,strtotime($plate['plate_waiting']),true);
            } else {
                $pw='n/a';
                $processtime='n/a';
            }
            if($plate['plate_approval']!='')
            {
                $pa=date("H:i:s",strtotime($plate['plate_approval']));
                $await=getMinVar(strtotime($plate['plate_waiting']),strtotime($plate['plate_approval']),true);
            } else {
                $pa='n/a';
            }
            if($plate['black_ctp']!='')
            {
                $bc=date("H:i:s",strtotime($plate['black_ctp']));
            } else {
                $bc='n/a';
            }
            if($plate['black_receive']!='')
            {
                $br=date("H:i:s",strtotime($plate['black_receive']));
            } else {
                $br='n/a';
            }
            if($plate['cyan_receive']!='')
            {
                $cr=date("H:i:s",strtotime($plate['cyan_receive']));
            } else {
                $cr='n/a';
            }
            $jobdetails.= "<tr><td><b>$plate[section_code] - $plate[low_page] $platecolor</b></td>
            <td>$ppages</td>
            <td>".date("H:i:s",$trtime)."</td>
            <td>".$pw."</td>
            <td>".$pa."</td>
            <td>".$bc."</td>
            <td>".$await."</td>
            <td>".$processtime."</td>
            </tr>\n";
        }
    } else {
    	$jobdetails.="<tr><th colspan=6>No plates defined for this run</th></tr>\n";
    }
    
    
    $jobdetails.="<tr><th style='font-weight:bold;background-color:#CCC;color:black;'>Page Details</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Color</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>FTP Receive</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Workflow Receive</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Page Ripped</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Page Release</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Color Release</th>
    </tr>\n"; 
    $sql="SELECT * FROM job_pages WHERE job_id=$job[id] AND remake=0 ORDER BY section_code ASC, page_number ASC";
    $dbPages=dbselectmulti($sql);
    if ($dbPages['numrows']>0)
    {
         foreach($dbPages['data'] as $page)
         {
         	if($page['page_release']!='')
            {
                $pr=date("H:i:s",strtotime($page['page_release']));
            } else {
                $pr='n/a';
            }
            if($page['color_release']!='')
            {
                $cr=date("H:i:s",strtotime($page['color_release']));
            } else {
                $cr='n/a';
            }
            if($page['ftp_receive']!='')
            {
                $fr=date("H:i:s",strtotime($page['ftp_receive']));
            } else {
                $fr='n/a';
            }
            if($page['workflow_receive']!='')
            {
                $wr=date("H:i:s",strtotime($page['workflow_receive']));
            } else {
                $wr='n/a';
            }
            if($page['page_ripped']!='')
            {
                $par=date("H:i:s",strtotime($page['page_ripped']));
            } else {
                $par='n/a';
            }
            if($page['color']){$color='color';}else{$color='b/w';}
            $jobdetails.= "<tr><td><b>$page[section_code] - $page[page_number]</b></td>
            <td>".$color."</td>
            <td>".$fr."</td>
            <td>".$wr."</td>
            <td>".$par."</td>
            <td>".$pr."</td>
            <td>".$cr."</td>
            </tr>\n";
         }
    } else {
        $jobdetails.= "<tr><th colspan=6>No pages defined for this run</th></tr>\n";
    }
    
    $jobdetails.="<tr><th style='font-weight:bold;background-color:#CCC;color:black;'>Page Remakes</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Page Release</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Color Release</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>FTP Receive</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Workflow Receive</th>
    <th style='font-weight:bold;background-color:#CCC;color:black;'>Page Ripped</th>
    </tr>\n";
    $sql="SELECT * FROM job_pages WHERE job_id=$job[id] AND remake=1 ORDER BY section_code ASC, page_number ASC";
    $dbPages=dbselectmulti($sql);
    if ($dbPages['numrows']>0)
    {
         foreach($dbPages['data'] as $page)
         {
             if($page['page_release']!='')
            {
                $pr=date("H:i:s",strtotime($page['page_release']));
            } else {
                $pr='n/a';
            }
            if($page['color_release']!='')
            {
                $cr=date("H:i:s",strtotime($page['color_release']));
            } else {
                $cr='n/a';
            }
            if($page['ftp_receive']!='')
            {
                $fr=date("H:i:s",strtotime($page['ftp_receive']));
            } else {
                $fr='n/a';
            }
            if($page['workflow_receive']!='')
            {
                $wr=date("H:i:s",strtotime($page['workflow_receive']));
            } else {
                $wr='n/a';
            }
            if($page['page_ripped']!='')
            {
                $par=date("H:i:s",strtotime($page['page_ripped']));
            } else {
                $par='n/a';
            }
            $jobdetails.= "<tr><td><b>$page[section_code] - $page[page_number]</b></td>
            <td>".$pr."</td>
            <td>".$cr."</td>
            <td>".$fr."</td>
            <td>".$wr."</td>
            <td>".$par."</td>
            </tr>\n";
         }
    } else {
        $jobdetails.= "<tr><th colspan=6>No remakes for this run</th></tr>\n";
    }
    
    if ($enableJobStops)
    {
        $jobdetails.="<tr><th colspan=6 style='font-size:10pt;font-weight:bold;background-color:#CCC;color:black;'>Job stops</th></tr>\n";
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
	   $details['job']=$jobdetails;
       $details['summary']=$summary;
       return $details;
}

function getInserterJobDetails($job)
{
    $jobdetails="<br />\n<br />\nInserter JOB goes here<br />\n";
    return $jobdetails;
}

function getMinVar($startdatetime,$enddatetime,$concise=false)
{
    $timePassed = $enddatetime-$startdatetime; //time passed in seconds
    if($timePassed<0)
    {
        $minus=true;
        $timePassed=-$timePassed;
    }
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
        if(strlen($hours)==1){$hours="0".$hours;}
        if($concise)
        {
            $elapsedString .= $hours.":";
        } else {
            $elapsedString .= $hours." hours, ";
        }
    }
    if($timePassed > 60)
    {
        $minutes = floor($timePassed / 60);
        $timePassed -= $minutes * 60;
        if(strlen($minutes)==1){$minutes="0".$minutes;}
        if($concise)
        {
            $elapsedString .= $minutes.":"; 
        } else {
            $elapsedString .= $minutes." minutes, "; 
        }
        
    }
    if(strlen($timePassed)==1){$timePassed="0".$timePassed;}
    if($concise)
    {
        $elapsedString .= $timePassed; 
    } else {
        $elapsedString .= $timePassed." seconds";
    }
    if($minus)
    {
        $elapsedString="-".$elapsedString;
    }
    return $elapsedString;
}
 
?>
