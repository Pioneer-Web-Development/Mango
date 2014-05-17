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
        font-size: 9px;
    }
    .calbox{
        width:123px;
        height:100px;
        border:1px solid black;
        margin-left:-1px;
        margin-top:-1px;
        padding:2px;
        float:left;
        overflow:hidden;
    }
    .datebar{
        width:123px;
        font-weight:bold;
        font-size:12px;
        border:1px solid black;
        margin-left:-1px;
        padding:2px;
        float:left;
        text-align:center;
    }
    .datebox{
        width:123px;
        font-size:7px;
        border-bottom:1px solid black;
        padding:2px;
        font-weight:bold;
        text-align:center;
        background-color:black;
        height:9px;
        color:white;
    }
    .clear{
        clear:both;
        height:0px;
    }
    .header{
        width:883px;
        border:2px solid black;
        padding:5px;
        text-align:center;
        font-size:14px;
        font-weight:bold;
        margin-left:-1px;
        color:white;
        background-color:black;
    }
</style>\n</head>";
    global $pubids;
    print "<body onload='window.print();'>\n";
    $start=$_GET['start'];
    $stop=$_GET['stop'];
    print "<div style='width:900px;height:670px;'>\n";
    
    //print header
    print "<div class='header'>Monthly Press Calendar for $start to $stop</div>\n";
    //ok we're running from start to stop, getting 1 day at a time
    //we'll figure out what position we are in
    //get start col
    $col=date("w",strtotime($start));
    print "<div class='datebar'>Sunday</div>\n";
    print "<div class='datebar'>Monday</div>\n";
    print "<div class='datebar'>Tuesday</div>\n";
    print "<div class='datebar'>Wednesday</div>\n";
    print "<div class='datebar'>Thursday</div>\n";
    print "<div class='datebar'>Friday</div>\n";
    print "<div class='datebar'>Saturday</div>\n";
    print "<div class='clear'></div>\n";
    $j=1;
    $i=0;
    for ($j=1;$j<=6;$j++) //rows
    {
        for ($i=0;$i<=6;$i++) //columns = day of week
        {
            if ($j==1 && $i<$col)
            {
                print "<div id='".$j."_".$i."i' class='calbox'>\n";
                print "<div class='datebox'>&nbsp;</div>\n";
                print "&nbsp;";
                print "</div>\n";
            } else {
                if ($j==1 && $i==$col)
                {
                    $cdate=date("Y-m-d",strtotime($start));
                } else {
                    $cdate=date("Y-m-d",strtotime($cdate."+1 day"));
                }
                if (strtotime($cdate)<=strtotime($stop))
                {
                    $date=date("M d",strtotime($cdate));
                    print "<div id='".$j."_".$i."i' class='calbox'>\n";
                        print "<div class='datebox'>$date</div>\n";
                        getjobs($cdate);
                    print "</div>\n";
                } else {
                    
                    print "<div id='".$j."_".$i."i' class='calbox'>\n";
                    print "<div class='datebox'>&nbsp;</div>\n";
                    print "&nbsp;";
                    print "</div>\n";
                }
                 
            }   
        }
        print "<div class='clear'></div>\n";
    }
    
} else {
    include("includes/mainmenu.php") ;
    global $pubids;
    print "<body>\n";
    //make sure we have a logged in user...
    if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}
    print "<div id='wrapper' style='width:670px;font-family:Tahoma,Arial;'>\n";
    print "<form method=post>\n";
    make_date('start',date("Y-m-d"),'Start Date');
    make_date('stop',date("Y-m-d",strtotime("+1 month")),'Stop Date');
    make_submit('submit','Show Calendar');
    print "</form>\n";
    if ($_POST['submit'])
    {
        $start=$_POST['start'];
        $stop=$_POST['stop'];
    } else {
        $start=date("Y-m-d");
        $stop=date("Y-m-d",strtotime("+1 month"));
    }
    global $siteID;
     
    $sql="SELECT A.*, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.site_id=$siteID AND A.pub_id=B.id AND A.run_id=C.id AND A.continue_id=0 AND A.status<>99 AND A.startdatetime>='$start' AND A.startdatetime<='$stop' AND A.pub_id=B.id AND A.pub_id IN ($pubids) ORDER BY A.startdatetime ASC";
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

function getjobs($date)
{
    global $pubids, $siteID;
    $sql="SELECT A.*, B.pub_name, B.pub_code, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.site_id=$siteID AND A.pub_id=B.id AND A.run_id=C.id AND A.continue_id=0 AND A.status<>99 AND A.startdatetime>='$date 00:00:01' AND A.startdatetime<='$date 23:59:59' AND A.pub_id=B.id AND A.pub_id IN ($pubids) ORDER BY A.startdatetime ASC";
    $dbJobs=dbselectmulti($sql);

    if ($dbJobs['numrows']>0)
    {
        foreach($dbJobs['data'] as $job)
        {
            print $job['pub_code']." - ".$job['run_name']." - ".date("m/d",strtotime($job['pub_date']))."<br />\n";        
            
        }
        
    }
}            
dbclose();
print "</div>\n";
print "</body>\n";
print "</html>\n";
?>