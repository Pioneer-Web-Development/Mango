<?php
    /*
     This is a report to get average spoils, draw, gross run time, net run time,
     gross speed, net speed on a particular pub/run combo
    */
    include("includes/mainmenu.php") ;
    global $pubs, $siteID;
    if ($_POST)
    {
        $start=$_POST['jobstartdate'];     
        $end=$_POST['jobenddate'];
        $pubid=$_POST['pub_id'];
        $runid=$_POST['run_id'];
        if ($runid==0)
        {
            $andrun="";
        } else {
            $andrun="AND run_id=$runid";
        }
        $sql="SELECT id FROM jobs WHERE pub_id=$pubid $andrun AND startdatetime>='$start 00:00:01' AND enddatetime<='$end 23:59:59' AND continue_id=0 AND pub_date<>'' ORDER BY startdatetime DESC";
        $dbJobs=dbselectmulti($sql);
        //this now gives us an array of jobs to look at in stats
        if ($dbJobs['numrows']>0)
        {
            $jobids="";
            foreach($dbJobs['data'] as $job)
            {
                $jobids.="$job[id],";
            }
            $jobids=substr($jobids,0,strlen($jobids)-1);
        }
        //now get the stats for these jobs
        $sql="select avg(run_speed) as rspeed, avg(good_runspeed) as gspeed, avg(draw/(run_time/60)) as nspeed, avg(run_time) as rtime, avg(run_time-total_downtime) as ntime, avg(total_downtime) as downtime, avg(gross) as pgross, avg(draw) as pdraw, avg(spoils_total) as spoils, (pages_color+pages_bw) as tpages FROM job_stats WHERE job_id IN ($jobids) AND gross>0 AND run_time>0 GROUP BY (pages_color+pages_bw) desc";
        $dbStats=dbselectmulti($sql);
        if ($dbStats['numrows']>0)
        {
            print "<table class='report'>\n";
            //get the pub and job name
            print "<tr><th colspan=10>Publication: ".$pubs[$pubid]."</th></tr>\n";
            print "<tr><th>Page Count</th><th>Avg. Gross Run Time</th><th>Avg. Downtime</th><th>Avg Net Run Time</th><th>Avg. Draw</th><th>Avg. Gross</th><th>Avg Spoils</th><th>Avg Gross Speed</th><th>Avg Running Speed</th><th>Avg Net Speed</th></tr>\n";
            foreach($dbStats['data'] as $jobinfo)
            {
                print "<tr>";
                
                print "<td>$jobinfo[tpages]</td>";
                print "<td>$jobinfo[rtime]</td>";
                print "<td>$jobinfo[downtime]</td>";
                print "<td>$jobinfo[ntime]</td>";
                print "<td>$jobinfo[pdraw]</td>";
                print "<td>$jobinfo[pgross]</td>";
                print "<td>$jobinfo[spoils]</td>";
                print "<td>$jobinfo[gspeed]</td>";
                print "<td>$jobinfo[rspeed]</td>";
                print "<td>$jobinfo[nspeed]</td>";
                
                print "</tr>\n";
            }
            //now an average for <= 30 pages
            $sql="select avg(run_speed) as rspeed, avg(good_runspeed) as gspeed, avg(draw/(run_time/60)) as nspeed, avg(run_time) as rtime, avg(run_time-total_downtime) as ntime, avg(total_downtime) as downtime, avg(gross) as pgross, avg(draw) as pdraw, avg(spoils_total) as spoils, avg(pages_color+pages_bw) as tpages FROM job_stats WHERE job_id IN ($jobids) AND gross>0 AND run_time>0 AND (pages_color+pages_bw)<=30";
            $dbStats=dbselectsingle($sql);
            if ($dbStats['numrows']>0)
            {
                $jobinfo=$dbStats['data'];
                print "<tr><th colspan=10>Average for 30 or fewer pages</th></tr>\n";
                print "<tr>";
                print "<td>$jobinfo[tpages]</td>";
                print "<td>$jobinfo[rtime]</td>";
                print "<td>$jobinfo[downtime]</td>";
                print "<td>$jobinfo[ntime]</td>";
                print "<td>$jobinfo[pdraw]</td>";
                print "<td>$jobinfo[pgross]</td>";
                print "<td>$jobinfo[spoils]</td>";
                print "<td>$jobinfo[gspeed]</td>";
                print "<td>$jobinfo[rspeed]</td>";
                print "<td>$jobinfo[nspeed]</td>";
                print "</tr>\n";
            }
            //now an average for > 30 pages
            $sql="select avg(run_speed) as rspeed, avg(good_runspeed) as gspeed, avg(draw/(run_time/60)) as nspeed,avg(run_time) as rtime, avg(run_time-total_downtime) as ntime, avg(total_downtime) as downtime, avg(gross) as pgross, avg(draw) as pdraw, avg(spoils_total) as spoils, avg(pages_color+pages_bw) as tpages FROM job_stats WHERE job_id IN ($jobids) AND gross>0 AND run_time>0 AND (pages_color+pages_bw)>30";
            $dbStats=dbselectsingle($sql);
            if ($dbStats['numrows']>0)
            {
                $jobinfo=$dbStats['data'];
                print "<tr><th colspan=10>Average for more than 30 pages</th></tr>\n";
                print "<tr>";
                print "<td>$jobinfo[tpages]</td>";
                print "<td>$jobinfo[rtime]</td>";
                print "<td>$jobinfo[downtime]</td>";
                print "<td>$jobinfo[ntime]</td>";
                print "<td>$jobinfo[pdraw]</td>";
                print "<td>$jobinfo[pgross]</td>";
                print "<td>$jobinfo[spoils]</td>";
                print "<td>$jobinfo[gspeed]</td>";
                print "<td>$jobinfo[rspeed]</td>";
                print "<td>$jobinfo[nspeed]</td>";
                print "</tr>\n";
            }
            print "</table>\n";
        } else {
            print "No jobs matching that criteria.";
        }     
    } else {
        $jobstartdate=date("Y-m-d",strtotime("-1 month"));
        $jobenddate=date("Y-m-d");
        print "<form method=post>\n";
            make_select('pub_id',$pubs[0],$pubs,'Publication');
            make_select('run_id',$runs[0],$runs,'Run');
             print '
            <script type="text/javascript">
            $("#pub_id").selectChain({
                target: $("#run_id"),
                type: "post",
                url: "includes/ajax_handlers/fetchRuns.php",
                data: { ajax: true, all:1 }
            });
            </script>
            ';
            make_date('jobstartdate',$jobstartdate,'Start print date');
            make_date('jobenddate',$jobenddate,'End print date');
            print "<input type='submit' name='submit' value='Run Report' />\n";
        print "</form>\n";
    }
    
footer();
?>