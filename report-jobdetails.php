<?php
//<!--VERSION: .9 **||**-->

    /*
     This is a report to get details on a particular pub/run combo
     It will pull date, start & stop time, draw, overrun, spoils, press & mail people count
     and last page received, last plate approved, last plate received times
    */
    include("includes/mainmenu.php") ;
    $runs=array();
    $runs[0]="Please choose publication";
    global $pubs, $siteID, $pubids;
    if ($_POST)
    {
        $start=$_POST['jobstartdate'];     
        $end=$_POST['jobenddate'];
        $output=$_POST['output'];
        $sql="SELECT A.*, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.site_id=$siteID AND A.pub_id IN ($pubids) AND startdatetime>='$start 00:00:01' AND enddatetime<='$end 23:59:59' AND continue_id=0 AND pub_date<>'' AND A.pub_id=B.id AND A.run_id=C.id ORDER BY startdatetime DESC";
        $dbJobs=dbselectmulti($sql);
        if ($dbJobs['numrows']>0)
        {
            global $pressmen;
            print "<table class='report-clean-mango'>\n";
            //get the pub and job name
            print "<tr><th colspan=3>Job Name</th><th style='width:75px;'>Pub Date</th><th>Start</th><th>End</th><th>Draw</th>
            <th>Gross</th><th>Start Counter</th><th>End Counter</th><th>Start Spoils</th><th>Setup Time</th><th colspan=2>Last Times</th><th>Benchmarks</th></tr>\n";
            foreach($dbJobs['data'] as $job)
            {
                $jobid=$job['id'];
                $runid=$job['run_id'];
                $runname=$job['pub_name'].'-'.$job['run_name'];
                //pull all the stop for this job
                $sql="SELECT A.*, B.stop_name FROM job_stops A, stop_codes B WHERE A.job_id=$jobid AND A.stop_code=B.id ORDER BY stop_restartdatetime DESC";
                $dbStops=dbselectmulti($sql);
                if ($dbStops['numrows']>0)
                {
                    $stopinfo='';
                    foreach($dbStops['data'] as $stop)
                    {
                        $stopinfo.="<br />Stopped at ".date("H:i",strtotime($stop['stop_datetime']))." for ".$stop['stop_name'].". Restarted at ".date("H:i",strtotime($stop['stop_restartdatetime']))." for a total of ".$stop['stop_downtime']." minutes lost.";
                        if ($output=='0'){$stopinfo.="<img src='artwork/icons/cancel_16.png' border=0 onclick='removeJobStop($jobid,$stop[id]);'";}
                        //figure out the details involved
                        if($stop['stop_info']!='')
                        {
                            $stopinfo.="<br/>Units affected: ";
                            $details=explode("|",$stop['stop_info']);
                            $affected='';
                            foreach($details as $detail)
                            {
                                $sub=explode("_",$detail);
                                if ($sub[0]=='tower')
                                {
                                    $towerid=$sub[1];
                                    $sql="SELECT tower_name FROM press_towers WHERE id=$towerid";
                                    $dbTower=dbselectsingle($sql);
                                    $tname=$dbTower['data']['tower_name'];
                                    $affected.=" $tname - $sub[2] unit, ";
                                }
                            }
                            $affected=substr($affected,0,strlen($affected)-1);
                            $stopinfo.=$affected;
                            
                            
                        }
                        if ($stop['stop_notes']!='')
                        {
                            $stopinfo.="<br/>Notes: ".$stop['stop_notes'];
                            if ($output=='0')
                            {
                                $stopinfo.="<input style='font-size:10px;height:18px;' type='button' value='Edit' onclick='showGeneralModal(\"stopnotes\",$jobid,$stop[id]);'>";
                                $stopinfo.="&nbsp;<input style='font-size:10px;height:18px;' type='button' value='Delete' onclick='removeStopNote($stop[id]);'>";
                            }
                        }
                    }
                   $runname.=$stopinfo;
                }
                $statsql="SELECT * FROM job_stats WHERE id=$job[stats_id]";
                $dbStats=dbselectsingle($statsql);
                $stats=$dbStats['data'];
                $benchsql="SELECT A.*, B.benchmark_name FROM job_benchmarks A, benchmarks B WHERE A.job_id=$jobid AND A.benchmark_id=B.id";
                $dbBench=dbselectmulti($benchsql);
                
                $actualstart=date("H:i",strtotime($stats['startdatetime_actual']));
                $actualstop=date("H:i",strtotime($stats['stopdatetime_actual']));
                $gross=$stats['counter_stop']-$stats['counter_start'];
                
                print "<tr>";
                print "<td colspan=3>$runname</td>";
                print "<td>$job[pub_date]</td>";
                print "<td>$actualstart</td>";
                print "<td>$actualstop</td>";
                print "<td>$job[draw]</td>";
                print "<td>$gross</td>";
                print "<td>$stats[counter_start]</td>";
                print "<td>$stats[counter_stop]</td>";
                print "<td>$stats[spoils_startup]</td>";
                print "<td>$stats[setup_time]</td>";
                
                //times section
                print "<td colspan=2>";
                //get last page receive, last plate approve, last plate out
                $sql="SELECT ftp_receive as ptime FROM job_pages WHERE job_id=$jobid AND version=1 ORDER BY ftp_receive DESC LIMIT 1";
                $dbTime=dbselectsingle($sql);
                if ($dbTime['data']['ptime']!=''){
                print "FTP Received: ".date("H:i:s",strtotime($dbTime['data']['ptime']))."<br />\n";
                }
                $sql="SELECT page_release as ptime FROM job_pages WHERE job_id=$jobid AND version=1 ORDER BY page_release DESC LIMIT 1";
                $dbTime=dbselectsingle($sql);
                if ($dbTime['data']['ptime']!=''){
                print "Page Released: ".date("H:i:s",strtotime($dbTime['data']['ptime']))."<br />\n";
                }
                
                $sql="SELECT page_composed as ptime FROM job_pages WHERE job_id=$jobid AND version=1 ORDER BY page_composed DESC LIMIT 1";
                $dbTime=dbselectsingle($sql);
                if ($dbTime['data']['ptime']!=''){
                print "Page Composed: ".date("H:i:s",strtotime($dbTime['data']['ptime']))."<br />\n";
                }
                $sql="SELECT black_receive as ptime FROM job_plates WHERE job_id=$jobid AND version=1 ORDER BY black_receive DESC LIMIT 1";
                $dbTime=dbselectsingle($sql);
                if ($dbTime['data']['ptime']!=''){
                print "Plate Received: ".date("H:i:s",strtotime($dbTime['data']['ptime']))."<br />\n";
                }
                $sql="SELECT black_ctp as ptime FROM job_plates WHERE job_id=$jobid AND version=1 ORDER BY black_receive DESC LIMIT 1";
                $dbTime=dbselectsingle($sql);
                if ($dbTime['data']['ptime']!=''){
                print "Plate Released: ".date("H:i:s",strtotime($dbTime['data']['ptime']))."<br />\n";
                }
                print "</td>";
                
                //benchmark section
                print "<td>";
                if($dbBench['numrows']>0)
                {
                    foreach($dbBench['data'] as $bench)
                    {
                        print $bench['benchmark_name'].":&nbsp;";
                        if ($bench['benchmark_type']=='time')
                        {
                            print $bench['benchmark_actual_time'];
                        } else {
                            print $bench['benchmark_actual_number']; 
                        }
                        print "<br />\n";
                    }
                }
                print "</td>";
                
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
            make_date('jobstartdate',$jobstartdate,'Start print date');
            make_date('jobenddate',$jobenddate,'End print date');
            make_select('output','Screen',array("Screen"),'Output to');
            make_submit('submit','Run Report');
        print "</form>\n";
    }
    
    
footer();    
?>