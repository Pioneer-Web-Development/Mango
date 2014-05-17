<?php
//<!--VERSION: .9 **||**-->
  //summary report
  //this report duplicates the function in emailsummary.php
if($_POST['output']=='csv')
{  
    include("includes/functions_db.php");
    include("includes/config.php");
} else {
    $output='screen';
    include ("includes/mainmenu.php");    
}
if ($_POST)
{
    $output=$_POST['output'];
    if($output=='screen')
    {
        print "<div class='noprint'><a href='#' onclick='window.print();'><img src='artwork/printer.png' border=0 width=32>Print Report</a><br />\n";
        print "<a href='?action=runagain'>Run another report</a><br /></div>\n";
    }
    $pubid=$_POST['pub_id'];
    $pubdate=$_POST['pubdate'];
    $startdate=date("Y-m-d",strtotime($pubdate." -1 day"))." 06:00";
    $enddate=$pubdate." 06:00";
    if ($pubid==0)
    {
        //$sql="SELECT A.* FROM jobs A, job_stats B WHERE B.job_id=A.id AND B.startdatetime_actual>='$startdate' AND B.stopdatetime_actual<='$enddate' AND A.pub_date<>'' ORDER BY A.pub_id, A.pub_date";
        $sql="SELECT * FROM jobs WHERE startdatetime>='$startdate' AND enddatetime<='$enddate' AND pub_date<>'' ORDER BY pub_id, pub_date";
    } else {
        $sql="SELECT A.* FROM jobs A, job_stats B WHERE A.pub_id=$pubid AND B.job_id=A.id  AND B.startdatetime_actual>='$startdate' AND B.stopdatetime_actual<='$enddate' AND A.pub_date<>'' ORDER BY A.pub_id, A.pub_date";
    }
    if(debug){print $sql;}
    $dbJobs=dbselectmulti($sql);
    if ($dbJobs['numrows']>0)
    {
        buildReport($dbJobs['data'],$output,$pubdate);
    }
     
} else {
    global $pubs;
    $outputs=array('screen'=>'Display on screen','csv'=>'Output to excel');
    print "<form method=post>\n";
    $date=date("Y-m-d");
    make_select('pub_id',$pubs[0],$pubs,'Select Publication');
    make_select('output',$outputs[0],$outputs,'Output to');
    make_date('pubdate',$date,'Select Ending Print Date');
    make_submit('submit','Run report');
    print "</form>\n";
}  
  
  
  
function buildReport($jobs,$output,$date)
{
    global $enableJobStops, $enableBenchmarks, $producttypes, $pressmen,$pubs,$papertypes, $sizes;
    $span="colspan='1'";
    if($output=='csv')
    {
        $tableStart='';
        $tableEnd='';
        $rowStart='';
        $rowEnd="\n";
        $cellStart='';
        $cellEnd=',';
        $cell2Start='';
        $cell2End=',,';
        $cellStartBold="";
        $cell2StartBold="";
        $cellFullStartBold="";
        $headStart='';
        $headEnd='';
        $cellStartHead='';
        $cellEndHead=',';
        $cell2StartHead='';
        $cell2EndHead=',,';
        $cellStartBoldHead="";
        $cell2StartBoldHead="";
        $cellFullStartBoldHead="";
        $reportName="Daily Press Run Report for jobs running for the 24 hours up to $date 06:00\n";
        $bold='';
    } else {
        $bold="style='font-weight:bold;'";
        
        $tableStart="\n\n<table class='report-clean-mango' style='margin-bottom:20px;'>\n";
        $tableEnd="</tbody></table>\n";
        $rowStart='<tr>';
        $rowEnd="</tr>\n";
        $cellStart='<td>';
        $cellEnd='</td>';
        $cell2Start='<td colspan=2>';
        $cell2End='</td>';
        $cellStartBold="<td $bold>";
        $cell2StartBold="<td colspan=2 $bold>";
        $cellFullStartBold="<td colspan=16 $bold>";
        $headStart="<thead>";
        $headEnd="</thead>";
        $cellStartHead='<th>';
        $cellEndHead='</th>';
        $cell2StartHead='<th colspan=2>';
        $cell2EndHead='</th>';
        $cellStartBoldHead="<th $bold>";
        $cell2StartBoldHead="<th colspan=2 $bold>";
        $cellFullStartBoldHead="<th colspan=18 $bold>";
        
        $reportName="<div style='width:100%;margin-bottom:20px;text-align:center;font-weight:bold;font-size:24px;'>Daily Press Run Report for jobs running for the 24 hours up to $date 06:00</div>\n";
    }
    $pubid=0;
    //here we display the job data and all it's stats, we'll use a table layout
    $jobdetails.=$reportName;
    
    //now the table
    $jobdetails.=$tableStart;
        
    foreach($jobs as $job)
    {
        if($GLOBALS['debug']){
            print "Job info <pre>\n";
            print_r($job);
            print "</pre>\n";
        }
            
        $sql="SELECT run_name FROM publications_runs WHERE id=$job[run_id]";
        $dbRun=dbselectsingle($sql);
        $runname=stripslashes($dbRun['data']['run_name']);
        
        $sql="SELECT * FROM job_stats WHERE job_id=$job[id]";
        $dbStats=dbselectsingle($sql);
        $stats=$dbStats['data'];
        
        $counterstart=$stats['counter_start'];
        $counterstop=$stats['counter_stop'];
        $gross=$counterstop-$counterstart;
        $startupspoils=$stats['spoils_startup'];
        $draw=$job['draw'];
        $runningspoils=$stats['spoils_running'];
        $totalspoils=$stats['spoils_total'];
        $operator=$pressmen[$stats['job_pressoperator']];
        $paper=$papertypes[$job['papertype']];
        $rollwidth=$sizes[$job['rollSize']];
        $starttime=date("H:i",strtotime($stats['startdatetime_actual']));
        $stoptime=date("H:i",strtotime($stats['stopdatetime_actual']));
        $pubdate=date("d-M-Y",strtotime($job['pub_date']));
        
        //calculate pages
        //need to start with section since each section could be a different format
        $broadsheetpages=0;
        $broadsheetcolorpages=0;
        $broadsheetspotpages=0;
        $ptypes=array();
        $scodes=array();
        $sql="SELECT * FROM jobs_sections WHERE job_id=$job[id]";
        if($GLOBALS['debug']){
            print "Section sql is $sql<br />";
        }
        $dbSections=dbselectsingle($sql);
        if($dbSections['numrows']>0)
        {
            $sections=$dbSections['data'];
            if($GLOBALS['debug']){
                print_r($sections);
            }
            for($i=1;$i<=3;$i++)
            {
                $rawpages=0;
                $rawcolorpages=0;
                $rawspotpages=0;
                $sectionformat=$sections['section'.$i.'_producttype'];
                $sectioncode=$sections['section'.$i.'_code'];
                if($sections['section'.$i.'_used']==1)
                {
                    //1 = broadsheet, 2 & 3 == tab, 4=flexi
                    $pagesql="SELECT * FROM job_pages WHERE job_id=$job[id] AND version=1 AND section_code='$sectioncode'";
                    if($GLOBALS['debug']){
                        print "Format is $sectionformat i is $i, code is --$sectioncode-- Page sql: $sql<br />";
                    }
                    $dbPages=dbselectmulti($pagesql);
                    if($dbPages['numrows']>0)
                    {
                        foreach($dbPages['data'] as $page)
                        {
                            if($page['color']==1)
                            {
                                $rawcolorpages++;   
                            }elseif($page['spot']==1)
                            {
                                $rawspotpages++;   
                            }
                            $rawpages++;
                        }
                        
                    }
                    $sectioncode=str_replace("0","",$sectioncode);
                    $sectioncode=str_replace(" ","",$sectioncode);
                    if(!in_array($sectioncode,$scodes)){$scodes[]=$sectioncode;}
                    switch($sectionformat)
                    {
                        case 0:
                            $broadsheetpages+=$rawpages;
                            $broadsheetcolorpages+=$rawcolorpages;
                            $broadsheetspotpages+=$rawspotpages;
                            if(!in_array('Bdsht',$ptypes)){$ptypes[]='Bdsht';}
                        break;
                        
                        case 1:
                            $broadsheetpages+=$rawpages/2;
                            $broadsheetcolorpages+=$rawcolorpages/2;
                            $broadsheetspotpages+=$rawspotpages/2;
                            if(!in_array('Tab',$ptypes)){$ptypes[]='Tab';}
                        break;
                        
                        case 2:
                            $broadsheetpages+=$rawpages/2;
                            $broadsheetcolorpages+=$rawcolorpages/2;
                            $broadsheetspotpages+=$rawspotpages/2;
                            if(!in_array('Tab',$ptypes)){$ptypes[]='Tab';}
                        break;
                        
                        case 3:
                            $broadsheetpages+=$rawpages/4;
                            $broadsheetcolorpages+=$rawcolorpages/4;
                            $broadsheetspotpages+=$rawspotpages/4;
                            if(!in_array('Flexi',$ptypes)){$ptypes[]='Flexi';}
                        break;
                    }
                }
            }
        }
        $ptypes=trim(implode(",",$ptypes),',');
        $scodes=trim(implode(",",$scodes),',');
        
        
        
        if($pubid!=$job['pub_id'])
        {
            if($pubid!=0)
            {
                $jobdetails.=$tableEnd;
                $jobdetails.=$tableStart;
            }
            //starting a new publicaton block
            $pubid=$job['pub_id'];
            $jobdetails.=$headStart; 
            $jobdetails.=$rowStart.$cellFullStartBoldHead.$pubs[$pubid].$cellEndHead.$rowEnd;
            $jobdetails.=$rowStart;
            $jobdetails.=$cell2StartHead.$cellEndHead;
            $jobdetails.=$cellStartHead.$cellEndHead;
            $jobdetails.=$cell2StartBoldHead.'Press Order'.$cellEndHead;
            $jobdetails.=$cellStartBoldHead.' '.$cellEndHead;
            $jobdetails.=$cell2StartBoldHead.'Spoils'.$cellEndHead;
            $jobdetails.=$cellStartBoldHead.' '.$cellEndHead;
            $jobdetails.=$cell2StartBoldHead.'Color Pages'.$cellEndHead;
            $jobdetails.=$cell2StartBoldHead.'Times'.$cellEndHead;
            $jobdetails.=$cellStartBoldHead.' '.$cellEndHead;
            $jobdetails.=$cellStartBoldHead.' '.$cellEndHead;
            $jobdetails.=$cellStartBoldHead.' '.$cellEndHead;
            $jobdetails.=$cellStartBoldHead.' '.$cellEndHead;
            $jobdetails.=$rowEnd;
            $jobdetails.=$rowStart;
            $jobdetails.=$cellStartHead.'Product'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Section'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Pub Date'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Press Order'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Press Ord. 1%'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Gross'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Startup'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Run'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Brdsht Pages'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Full'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Spot'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Start'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Stop'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Type'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Paper'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Full Roll Size'.$cellEndHead;
            $jobdetails.=$cellStartHead.'Operator'.$cellEndHead;
            $jobdetails.=$cellStartHead.'JobID'.$cellEndHead;
            $jobdetails.=$rowEnd;
            $jobdetails.=$headEnd;
        }
        $jobdetails.=$rowStart;
        $jobdetails.=$cellStart.$runname.$cellEnd;
        $jobdetails.=$cellStart.$scodes.$cellEnd;
        $jobdetails.=$cellStart.$pubdate.$cellEnd;
        $jobdetails.=$cellStart.$draw.$cellEnd;
        $jobdetails.=$cellStart.($draw*1.01).$cellEnd;
        $jobdetails.=$cellStart.$gross.$cellEnd;
        $jobdetails.=$cellStart.$startupspoils.$cellEnd;
        $jobdetails.=$cellStart.$runningspoils.$cellEnd;
        $jobdetails.=$cellStart.$broadsheetpages.$cellEnd;
        $jobdetails.=$cellStart.$broadsheetcolorpages.$cellEnd;
        $jobdetails.=$cellStart.$broadsheetspotpages.$cellEnd;
        $jobdetails.=$cellStart.$starttime.$cellEnd;
        $jobdetails.=$cellStart.$stoptime.$cellEnd;
        $jobdetails.=$cellStart.$ptypes.$cellEnd;
        $jobdetails.=$cellStart.$paper.$cellEnd;
        $jobdetails.=$cellStart.$rollwidth.$cellEnd;
        $jobdetails.=$cellStart.$operator.$cellEnd;
        $jobdetails.=$cellStart."<a href='http://mango.newswest.com/jobPress.php?action=editjob&jobid=$job[id]' target='_blank'>$job[id]</a>".$cellEnd;
        $jobdetails.=$rowEnd;
    }
    $jobdetails.=$tableEnd;
    if($output=='csv')
    {
        header("Content-Type: text/csv"); // plain text file
        header('Content-Disposition: attachment; filename="dailyPressReport-'.$date.'.csv"');
    } 
    print $jobdetails;
}

if($output=='screen')
{
    footer();
} else {
    dbclose();
}
?>
