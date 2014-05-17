<?php
//<!--VERSION: .9 **||**-->

//this script is designed to generate a snapshot of all press jobs coming up in the next
//48 hours. Job Name, Pub name, run name, pub date, draw, sections/paging, press time and paper
include("includes/mainmenu.php");
if($_POST)
{
    $start=$_POST['start'];
    $end=$_POST['end'];
} else {
    $start=date("Y-m-d H:i");
    $end=date("Y-m-d H:i",strtotime("+48 hours"));
}
print "<div class='noprint'><form method=post>\n";
make_datetime('start',$start,'Start Date');
make_datetime('end',$end,'End Date');
make_submit('submit','Run Report');
print "</form></div>\n";
$sql="SELECT A.*, B.pub_name, B.pub_code, C.run_name, C.run_productcode FROM jobs A, publications B, publications_runs C WHERE A.site_id=$siteID AND A.pub_id=B.id AND A.run_id=C.id AND A.continue_id=0 AND A.status<>99 AND A.startdatetime>='$start' AND A.startdatetime<='$end' AND A.pub_id AND A.pub_id IN ($pubids) ORDER BY A.startdatetime ASC";
$dbJobs=dbselectmulti($sql);

if ($dbJobs['numrows']>0)
{
    print "<table class='report-clean-mango'>\n";
    print "<tr><th><span class='noprint'><a href='#' onClick='window.print()'><img src='artwork/printer.png' width=32 border=0>Print</a></span></th><th colspan=4><p style='text-align:center;font-size:18px;font-weight:bold;'>Jobs scheduled between ".date("m/d",strtotime($start))." and ".date("m/d",strtotime($end))."</p></th><th><span class='noprint'><a href='default.php'>Return to system</a></span></th></tr>\n";
    print "<tr><th>Publication</th><th>Run Name</th><th>Pub Date</th><th>Press Time</th><th>Draw</th><th>Paper</th></tr>\n";
    foreach($dbJobs['data'] as $job)
    {
        print "<tr>\n";
        print "<td>Pub: $job[pub_name]<br>Pub Code: $job[pub_code]</td>";        
        print "<td>Run: $job[run_name]<br />PC: ".$job['run_productcode'].'<br>'.$folders[$job['folder']]."</td>";        
            print "<td>$job[pub_date]</td>";        
        print "<td>".date("D m/d @ H:i",strtotime($job['startdatetime']))."</td>";        
        print "<td>$job[draw]</td>";        
        print "<td>".$GLOBALS['papertypes'][$job['papertype']]."</td>";        
        print "</tr>";
        
        $jobid=$job['id'];
        //figure out the paging
        $sql="SELECT * FROM jobs_sections WHERE job_id='$jobid'";
        $dbSections=dbselectsingle($sql);
        $sections=$dbSections['data'];
        $totalpages=0;
        //ok, lets get how many color/bw pages by section
        //section1
        $section1_overrun=$sections['section1_overrun'];
        $section1_name=$sections['section1_name'];
        $section1_code=$sections['section1_code'];
        $section1_totalpages=0;
        $section1_colorpages='';
        $section1_spotpages='';
        $section1_colorpageCount=0;
        $section1_bwpageCount=0;
        $section1_spotpageCount=0;
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
                if ($page['color']){$section1_colorpageCount++;$section1_colorpages.=$page['page_number'].' ';}
                elseif($page['spot']){$section1_spotpageCount++;$section1_spotpages.=$page['page_number'].' ';}
                else{$section1_bwpageCount++;}
            }   
        }
        $totalpages+=$section1_totalpages;
        //section2
        $section2_overrun=$sections['section2_overrun'];
        $section2_name=$sections['section2_name'];
        $section2_code=$sections['section2_code'];
        $section2_totalpages=0;
        $section2_colorpages='';
        $section2_spotpages='';
        $section2_colorpageCount=0;
        $section2_bwpageCount=0;
        $section2_spotpageCount=0;
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
                if ($page['color']){$section2_colorpageCount++;$section2_colorpages.=$page['page_number'].' ';}
                elseif($page['spot']){$section2_spotpageCount++;$section2_spotpages.=$page['page_number'].' ';}
                else{$section2_bwpageCount++;}
            }   
        }
        $totalpages+=$section2_totalpages;
        //section3
        $section3_overrun=$sections['section3_overrun'];
        $section3_name=$sections['section3_name'];
        $section3_code=$sections['section3_code'];
        $section3_totalpages=0;
        $section3_colorpages='';
        $section3_spotpages='';
        $section3_colorpageCount=0;
        $section3_bwpageCount=0;
        $section3_spotpageCount=0;
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
                if ($page['color']){$section3_colorpageCount++;$section3_colorpages.=$page['page_number'].' ';}
                elseif($page['spot']){$section3_spotpageCount++;$section3_spotpages.=$page['page_number'].' ';}
                else{$section3_bwpageCount++;}
            }   
        }
        $totalpages+=$section3_totalpages;
        print "<tr><td colspan=6>";
        if($section1_name!='')
        {
            print "Section 1: $section1_name - $section1_code ";
            print "$section1_format, ";
            print "Color: $section1_colorpageCount, ";
            print "BW: $section1_bwpageCount,";
            print "Spot: $section1_spotpageCount,";
            print "Overrun: $section1_overrun<br />$section1_gate $section1_double<br>";
            print "Color Pages: $section1_colorpages | Spot Pages: $section1_spotpages</td>";
            print "</tr>\n";
        } else {
            print "<tr><td colspan=6>Section 1 is not set up.</td></tr>\n";
        }
        if($section2_name!='')
        {
            print "<tr><td colspan=6>";
            print "Section 2: $section2_name - $section2_code ";
            print "$section2_format, ";
            print "Color: $section2_colorpageCount, ";
            print "BW: $section2_bwpageCount,";
            print "Spot: $section2_spotpageCount,";
            print "Overrun: $section2_bwpageCount<br />$section2_gate $section2_double<br>";
            print "Color Pages: $section2_colorpages | Spot Pages: $section2_spotpages</td>";
            print "</tr>\n";
        } else {
            print "<tr><td colspan=6>Section 2 is not set up.</td></tr>\n";
        }
        if($section3_name!='')
        {
            print "<tr><td colspan=6>";
            print "Section 3: $section3_name - $section3_code ";
            print "$section3_format, ";
            print "Color: $section3_colorpageCount, ";
            print "BW: $section3_bwpageCount,";
            print "Spot: $section3_spotpageCount,";
            print "Overrun: $section3_bwpageCount<br />$section3_gate $section3_double<br>";
            print "Color Pages: $section3_colorpages | Spot Pages: $section3_spotpages</td>";
            print "</tr>\n";
        } else {
            print "<tr><td colspan=6>Section 3 is not set up.</td></tr>\n";
        }
        print "<tr><td style='border-top: 2px solid black;height:2px;padding:0;margin:0;' colspan=6>&nbsp;</td></tr>\n";
    }
    print "</table>\n";
    
    //lets display a box at the bottom listing any unscheduled jobs in the system
    $date=date("Y-m-d");
    $sql="SELECT A.*, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.site_id=$siteID AND A.pub_date>='$date' AND A.continue_id=0 AND A.startdatetime='' AND A.pub_id=B.id AND A.run_id=C.id ORDER BY A.pub_date";
    $dbUnscheduled=dbselectmulti($sql);
    if ($dbUnscheduled['numrows']>0)
    {
        print "<div style='width:670px;border:2 px solid black;padding:4px;font-family:Tahoma'>\n";
        print "<p style='font-weight:bold;font-size:16px;'>The following jobs are in the system but have NOT been scheduled yet.</p>\n";
        foreach($dbUnscheduled['data'] as $job)
        {
            $jobid=$job['id'];
            $pubdate=date("D, F m Y",strtotime($job['pub_date']));
            print "<p>$job[pub_name] - $job[run_name] publishing on $pubdate<a href='pressJobs.php?action=schedulejob&jobid=$jobid' target='_blank'>Click here to schedule it</a></p>\n";    
        }
        
        print "</div>\n";
    }
} else {
    print "<h2>No jobs during that selected time period.</h2>\n";
}
            
footer();
?>