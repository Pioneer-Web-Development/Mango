<?php
//<!--VERSION: 1.0 **||**-->

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
            $andrun="AND A.run_id=$runid";
        }
        //get pub name and run name
        $sql="SELECT pub_name FROM publications WHERE id=$pubid";
        $dbPub=dbselectsingle($sql);
        $pubname=$dbPub['data']['pub_name'];
        
        
        $sql="SELECT run_name FROM publications_runs WHERE id=$runid";
        $dbPub=dbselectsingle($sql);
        $runname=$dbPub['data']['run_name'];
        
        //we are going to work from day to day
        $workingday=$start;
        $dow=date("N",strtotime($workingday));
        print "<table class='report-clean-mango'>\n";
        print "<tr><th>Pub/Run Name</th><th>Starting Date</th><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th><th>Sunday</th></tr>\n";
        //now, add the first row and blanks until the first day
        print "<tr><td>$pubname - $runname</td><td>$start</td>";
        for($i=1;$i<$dow;$i++)
        {
            print "<td></td>";
        }
        while(strtotime($workingday)<=strtotime($end))
        {
            $dow=date("N",strtotime($workingday));
            $sql="SELECT A.draw FROM jobs A, job_stats B WHERE A.pub_id=$pubid $andrun AND A.pub_date='$workingday' AND A.id=B.job_id";
            $dbJobs=dbselectsingle($sql);
            if($dbJobs['numrows']>0)
            {
                $draw=$dbJobs['data']['draw'];
            } else {
                $draw=0;
            }
            if ($dow==1)
            {
                print "<tr><td>$pubname - $runname</td><td>$workingday</td><td>$draw</td>";
            } elseif($dow==7)
            {
                print "<td>$draw</td></tr>\n";
            } else {
                print "<td>$draw</td>";
            }
            $workingday=date("Y-m-d",strtotime($workingday."+1 day"));
        }
        if ($dow<7)
        {
            while($dow<7)
            {
                print "<td></td>";
                $dow++;
            }
            print "</tr>\n";
        }
        print "</table>\n";     
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
                data: { ajax: true, zero:1 }
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