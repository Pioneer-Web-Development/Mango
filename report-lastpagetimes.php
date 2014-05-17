<?php
//<!--VERSION: 1.0 **||**-->

    /*
     This is a report to get last page and plate times on a particular pub/run combo
    */
    include("includes/mainmenu.php") ;
    if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}
    print "<body> <div id='wrapper'>";
    $runs=array();
    $runs[0]="Please choose publication";
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
        //get pub name and run name
        $sql="SELECT pub_name FROM publications WHERE id=$pubid";
        $dbPub=dbselectsingle($sql);
        $pubname=$dbPub['data']['pub_name'];
        
        // get our job information for this range
        $sql="SELECT id, pub_date FROM jobs WHERE pub_date>='$start' AND pub_date<='$end' AND pub_id=$pubid $andrun";
        $dbJobs=dbselectmulti($sql);
        if ($dbJobs['numrows']>0)
        {
            print "<a href='$_SERVER[PHP_SELF]'>Run another report</a><br />";
        
            print "<table class='report-clean-mango'>\n";
            print "<tr><th>Pub/Run Name</th><th>Pub Date</th><th>Press Start</th><th>Press Finish</th><th>Draw</th><th>Number of pages</th><th>Last Page</th><th>Last Plate</th></tr>\n";
            foreach($dbJobs['data'] as $job)
            {
                $jobid=$job['id'];
                $pubdate=$job['pub_date'];
                $sql="SELECT last_plate, startdatetime_actual, stopdatetime_actual, pages_color, pages_bw, draw, plateroom_lastpage, plateroom_lastpage_time FROM job_stats WHERE id=$job[stats_id]";
                $dbStats=dbselectsingle($sql);
                $draw=$dbStats['data']['draw'];
                $pages=$dbStats['data']['pages_bw']+$dbStats['data']['pages_color'];
                $lastpage=$dbStats['data']['plateroom_lastpage'];
                $plateroomlastpagetime=$dbStats['data']['plateroom_lastpage_time'];
                $starttime=$dbStats['data']['startdatetime_actual'];
                $stoptime=$dbStats['data']['stopdatetime_actual'];
                $lplate=$dbStats['data']['last_plate'];
                
                $sql="SELECT max(page_release) AS pr FROM job_pages WHERE job_id=$jobid AND version=1";
                $dbPage=dbselectsingle($sql);
                $lastpagetime=$dbPage['data']['pr'];
                
                $sql="SELECT max(plate_approval) AS plateapp FROM job_plates WHERE job_id=$jobid AND version=1";
                $dbPlate=dbselectsingle($sql);
                $lastplatetime=$dbPlate['data']['plateapp'];
                if ($lastplatetime=='')
                {
                    $lastplatetime=$lplate;
                }
                
                $runid=$job['run_id'];
                $sql="SELECT run_name FROM publications_runs WHERE id=$runid";
                $dbRun=dbselectsingle($sql);
                $runname=$dbRun['data']['run_name'];
                print "<tr><td>$pubname - $runname</td>";
                print "<td>$pubdate</td>";
                print "<td>$starttime</td>";
                print "<td>$stoptime</td>";
                print "<td>$draw</td>";
                print "<td>$pages</td>";
                print "<td>Release: $lastpagetime<br />Plateroom last page: $plateroomlastpagetime<br />
                Page: $lastpage</td>";
                print "<td>$lastplatetime</td>";
                   
            }
            print "</table>\n";
        } else {
            print "No jobs matching that criteria. <a href='$_SERVER[PHP_SELF]'>Click here to try with different settings</a>.";
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
                data: { ajax: true,all:1 }
            });
            </script>
            ';
            make_date('jobstartdate',$jobstartdate,'Start pub date');
            make_date('jobenddate',$jobenddate,'End pub date');
            print "<input type='submit' name='submit' value='Run Report' />\n";
        print "</form>\n";
    }
    
footer();
?>