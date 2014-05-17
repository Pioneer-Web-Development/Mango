<?php
//<!--VERSION: .9 **||**-->

//this script is designed to generate a snapshot of all press jobs coming up in the next
//48 hours. Job Name, Pub name, run name, pub date, draw, sections/paging, press time and paper
if (isset($_GET['action'])){$action=$_GET['action'];}else{$action='';}
if ($action=='print')
{
    session_start();
    include ("includes/functions_db.php");
    include ("includes/config.php");
    include ("includes/functions_common.php");
    print "<html>
    <head>
    <style type='text/css'>
    body {
        font-family:Tahoma MS, sans-serif;
        font-size: 10px;
    }
    .pubname{
        float:left;
        width:150px;
    } 
    .runname{
        float:left;
        width:110px;
    } 
    .pubdate{
        float:left;
        width:80px;
    }
    .presstime{
        float:left;
        width:130px;
    }
    .draw{
        float:left;
        width:100px;
    }
    .paper{
        float:left;
        width:100px;
    }
    .clear{
        clear:both;
        height:5px;
    }
    .header{
        width:670px;
        border-top:1px solid black;
        margin-top: 4px;
        padding-top: 4px;
        padding-bottom:4px;
        text-align:center;
        font-weight:bold;
    }
</style>\n</head>";
    global $pubids, $siteID, $folders;
    print "<body onload='window.print();'>\n";
    $start=$_GET['start'];
    $stop=$_GET['stop'];
    print "<div id='wrapper' style='width:670px;height:900px;font-family:Tahoma,Arial;'>\n";
  $sql="SELECT A.*, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.site_id=$siteID AND A.pub_id=B.id AND A.run_id=C.id AND A.continue_id=0 AND A.status<>99 AND A.startdatetime>='$start' AND A.startdatetime<='$stop' AND A.pub_id AND A.pub_id IN ($pubids) ORDER BY A.startdatetime ASC";
    $dbJobs=dbselectmulti($sql);

    if ($dbJobs['numrows']>0)
    {
        print "<h2 style='text-align:center;'>Jobs scheduled between $start and $stop</h2>\n";
        print "<div class='header'></div>\n";
        print "<div class='pubname' style='font-weight:bold;'>Publication</div>\n";
        print "<div class='runname' style='font-weight:bold;'>Run Name</div>\n";
        print "<div class='pubdate' style='font-weight:bold;'>Pub Date</div>\n";
        print "<div class='presstime' style='font-weight:bold;'>Press Time</div>\n";
        print "<div class='draw' style='font-weight:bold;'>Draw</div>\n";
        print "<div class='paper' style='font-weight:bold;'>Paper</div>\n";
        print "<div class='clear'></div>\n"; 
        $cdate=$start;
        print "<div class='header'>Jobs printing: ".date("l, F jS Y",strtotime($cdate))."</div>\n";
        foreach($dbJobs['data'] as $job)
        {
            if (date("Y-m-d",strtotime($job['startdatetime']))!=$cdate)
            {
                $cdate=date("Y-m-d",strtotime($job['startdatetime']));
                print "<div class='header'>Jobs printing: ".date("l, F jS Y",strtotime($cdate))."</div>\n";
            }
            print "<div class='pubname'>$job[pub_name]</div>";        
            print "<div class='runname'>$job[run_name]<br />".$folders[$job['folder']]."</div>";        
            print "<div class='pubdate'>$job[pub_date]</div>";        
            print "<div class='presstime'>".date("D m/d",strtotime($job['startdatetime']))." at ".date("H:i",strtotime($job['startdatetime']))."</div>";        
            $draw=$job['draw'];
            if ($draw=='' || $draw==0){$draw='Not set';}
            print "<div class='draw'>$draw</div>";        
            $jobid=$job['id'];
            print "<div class='paper'>".$GLOBALS['papertypes'][$job['papertype']]."</div>";              print "<div class='clear'></div>\n";
            
            
        }
        
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
        print "<h2>You lucked out!<br>There are no scheduled jobs during that time...or something has gone horribly wrong ;)</h2>\n";
    }
    print "</div>\n";
    dbclose();
    print "</body></html>\n";
} else {
    include("includes/mainmenu.php") ;
    global $pubids;
    print "<body>\n";
    //make sure we have a logged in user...
    if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}
    print "<div id='wrapper' style='width:670px;font-family:Tahoma,Arial;'>\n";
    print "<form method=post>\n";
    make_date('start',date("Y-m-d"),'Start Date');
    make_date('stop',date("Y-m-d",strtotime("+7 days")),'Stop Date');
    make_submit('submit','Show Weekly Calendar');
    print "</form>\n";
    if ($_POST['submit'])
    {
        $start=$_POST['start'];
        $stop=$_POST['stop'];
    } else {
        $start=date("Y-m-d");
        $stop=date("Y-m-d",strtotime("+7 days"));
    } 
    $sql="SELECT A.*, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.site_id=$siteID AND A.pub_id=B.id AND A.run_id=C.id AND A.continue_id=0 AND A.status<>99 AND A.startdatetime>='$start' AND A.startdatetime<='$stop' AND A.pub_id AND A.pub_id IN ($pubids) ORDER BY A.startdatetime ASC";
    $dbJobs=dbselectmulti($sql);

    if ($dbJobs['numrows']>0)
    {
        print "<table class='grid'>\n";
        print "<tr><th><a href='?action=print&start=$start&stop=$stop' target='_blank'><img src='artwork/printer.png' width=32 border=0>Print</a></th><th colspan=4><p style='text-align:center;font-size:18px;font-weight:bold;'>Jobs scheduled</p></th><th><a href='default.php'>Return to system</a></th></tr>\n";
        print "<tr><th>Publication</th><th>Run Name</th><th>Pub Date</th><th>Press Time</th><th>Draw</th><th>Paper</th></tr>\n";
        $cdate=$start;
        print "<tr><td colspan=6 style='text-align;center;'><p style='text-align:center;font-weight:bold;'>Jobs printing: ".date("l, F jS Y",strtotime($cdate))."</p></td></tr>\n";
        foreach($dbJobs['data'] as $job)
        {
            if (date("Y-m-d",strtotime($job['startdatetime']))!=$cdate)
            {
                $cdate=date("Y-m-d",strtotime($job['startdatetime']));
                print "<tr><td style='border-top: 2px solid black;height:2px;padding:0;margin:0;' colspan=6>&nbsp;</td></tr>\n";
                print "<tr><td colspan=6 style='text-align;center;'><p style='text-align:center;font-weight:bold;'>Jobs printing: ".date("l, F jS Y",strtotime($cdate))."</p></td></tr>\n";
            
            }
            print "<tr>\n";
            print "<td>$job[pub_name]</td>";        
            print "<td>$job[run_name]<br />".$folders[$job['folder']]."</td>";        
            print "<td>$job[pub_date]</td>";        
            print "<td>".date("D m/d",strtotime($job['startdatetime']))." at ".date("H:i",strtotime($job['startdatetime']))."</td>";        
            print "<td>$job[draw]</td>";        
            $jobid=$job['id'];
            print "<td>".$GLOBALS['papertypes'][$job['papertype']]."</td>";        
            
            
            print "</tr>\n";
            
            
            
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
        print "<h2>You lucked out!<br>There are no scheduled jobs during that time...or something has gone horribly wrong ;)</h2>\n";
    }
}

            
dbclose();
print "</div>\n";
print "</body>\n";
print "</html>\n";
?>