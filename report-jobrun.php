<?php
//<!--VERSION: .9 **||**-->

    /*
     This is a report to get details on a particular pub/run combo
     It will pull date, start & stop time, draw, overrun, spoils, press & mail people count
     and last page received, last plate approved, last plate received times
    */
    if ($_POST['output']=='file')
    {
        include("includes/functions_db.php");
        include("includes/config.php");
        include("includes/functions_common.php");
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=jobrunreport.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
    } else {
        include("includes/mainmenu.php"); 
        
    }
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
        $sql="SELECT * FROM jobs WHERE pub_id=$pubid $andrun AND startdatetime>='$start 00:00:01' AND enddatetime<='$end 23:59:59' AND continue_id=0 AND pub_date<>'' ORDER BY startdatetime DESC";
        $dbJobs=dbselectmulti($sql);
        if ($dbJobs['numrows']>0)
        {
            global $pressmen;
            if ($_POST['output']=='screen')
            {
                print "<table class='report-clean-mango'>\n";
                //get the pub and job name
                print "<thead>\n";
                print "<tr><th colspan=11>Publication: ".$pubs[$pubid]."</th></tr>\n";
                print "<tr><th>Run Name</th><th>Pub Date</th><th>Start</th><th>End</th><th>Draw</th><th>Gross</th><th>Start Spoils</th><th>Running Spoils</th><th>Total Spoils</th><th>Total Spoils %</th><th colspan=2>Last Times</th><th>Benchmarks</th></tr>\n";
                print "</thead>\n";
                foreach($dbJobs['data'] as $job)
                {
                    $jobid=$job['id'];
                    $runid=$job['run_id'];
                    $sql="SELECT * FROM publications_runs WHERE id=$runid";
                    $dbRun=dbselectsingle($sql);
                    $runname=$dbRun['data']['run_name'];
                    $statsql="SELECT * FROM job_stats WHERE job_id=$jobid";
                    $dbStats=dbselectsingle($statsql);
                    $stats=$dbStats['data'];
                    $benchsql="SELECT A.*, B.benchmark_name FROM job_benchmarks A, benchmarks B WHERE A.job_id=$jobid AND A.benchmark_id=B.id";
                    $dbBench=dbselectmulti($benchsql);
                    
                    $actualstart=date("m/d H:i",strtotime($stats['startdatetime_actual']));
                    $actualstop=date("H:i",strtotime($stats['stopdatetime_actual']));
                    $gross=$stats['counter_stop']-$stats['counter_start'];
                    
                    print "<tr>";
                    print "<td>$jobid - $runname</td>";
                    print "<td>$job[pub_date]</td>";
                    print "<td>$actualstart</td>";
                    print "<td>$actualstop</td>";
                    print "<td>$job[draw]</td>";
                    print "<td>$gross</td>";
                    print "<td>$stats[spoils_startup]</td>";
                    print "<td>$stats[spoils_running]</td>";
                    print "<td>$stats[spoils_total]</td>";
                    if($job['draw']!=0)
                    {
                        $spoilsper=round($stats['spoils_total']/$job['draw'],1);
                    } else {
                        $spoilsper="N/A";
                    }
                    print "<td>$spoilsper%</td";
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
                print "Publication: ".$pubs[$pubid]."\n";
                print "Run Name,Pub Date,Start,End,Draw,Gross,Start Spoils,Running Spoils,Total Spoils,Total Spoils %,Last Times,Benchmarks\n";
                foreach($dbJobs['data'] as $job)
                {
                    $jobid=$job['id'];
                    $runid=$job['run_id'];
                    $sql="SELECT * FROM publications_runs WHERE id=$runid";
                    $dbRun=dbselectsingle($sql);
                    $runname=$dbRun['data']['run_name'];
                    $statsql="SELECT * FROM job_stats WHERE id=$job[stats_id]";
                    $dbStats=dbselectsingle($statsql);
                    $stats=$dbStats['data'];
                    $benchsql="SELECT A.*, B.benchmark_name FROM job_benchmarks A, benchmarks B WHERE A.job_id=$jobid AND A.benchmark_id=B.id";
                    $dbBench=dbselectmulti($benchsql);
                    
                    $actualstart=date("H:i",strtotime($stats['startdatetime_actual']));
                    $actualstop=date("H:i",strtotime($stats['stopdatetime_actual']));
                    $gross=$stats['counter_stop']-$stats['counter_start'];
                    
                    print "$runname,";
                    print "$job[pub_date],";
                    print "$actualstart,";
                    print "$actualstop,";
                    print "$job[draw],";
                    print "$gross,";
                    print "$stats[spoils_startup],";
                    print "$stats[spoils_running],";
                    print "$stats[spoils_total],";
                    if($job['draw']!=0)
                    {
                        $spoilsper=round($stats['spoils_total']/$job['draw'],1);
                    } else {
                        $spoilsper="N/A";
                    }
                    print "$spoilsper%,";
                    
                    //times section
                    //get last page receive, last plate approve, last plate out
                    $sql="SELECT ftp_receive as ptime FROM job_pages WHERE job_id=$jobid AND version=1 ORDER BY ftp_receive DESC LIMIT 1";
                    $dbTime=dbselectsingle($sql);
                    if ($dbTime['data']['ptime']!=''){
                    print "FTP Received: ".date("H:i:s",strtotime($dbTime['data']['ptime']))." | ";
                    }
                    $sql="SELECT page_release as ptime FROM job_pages WHERE job_id=$jobid AND version=1 ORDER BY page_release DESC LIMIT 1";
                    $dbTime=dbselectsingle($sql);
                    if ($dbTime['data']['ptime']!=''){
                    print "Page Released: ".date("H:i:s",strtotime($dbTime['data']['ptime']))." | ";
                    }
                    
                    $sql="SELECT page_composed as ptime FROM job_pages WHERE job_id=$jobid AND version=1 ORDER BY page_composed DESC LIMIT 1";
                    $dbTime=dbselectsingle($sql);
                    if ($dbTime['data']['ptime']!=''){
                    print "Page Composed: ".date("H:i:s",strtotime($dbTime['data']['ptime']))." | ";
                    }
                    $sql="SELECT black_receive as ptime FROM job_plates WHERE job_id=$jobid AND version=1 ORDER BY black_receive DESC LIMIT 1";
                    $dbTime=dbselectsingle($sql);
                    if ($dbTime['data']['ptime']!=''){
                    print "Plate Received: ".date("H:i:s",strtotime($dbTime['data']['ptime']))." | ";
                    }
                    $sql="SELECT black_ctp as ptime FROM job_plates WHERE job_id=$jobid AND version=1 ORDER BY black_receive DESC LIMIT 1";
                    $dbTime=dbselectsingle($sql);
                    if ($dbTime['data']['ptime']!=''){
                    print "Plate Released: ".date("H:i:s",strtotime($dbTime['data']['ptime']))." |  ";
                    }
                    print ",";
                    
                    //benchmark section
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
                            print " | \n";
                        }
                    }
                    
                    print "\n";
                }
            }
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
            make_select('output','Screen',array('screen'=>'Screen','file'=>'File'),'Ouput to');
            make_submit('submit','Run Report');
        print "</form>\n";
    }
    
    
    
if ($_POST['output']=='file')
{
   dbclose();  
} else {
   footer();
}
?>