<?php
//<!--VERSION: .9 **||**-->

//this script is designed to generate a snapshot of all press jobs coming up in the next
//48 hours. Job Name, Pub name, run name, pub date, draw, sections/paging, press time and paper
if ($_GET['action']=='print')
{
    session_start();
    include ("includes/functions_db.php");
    include ("includes/config.php");
    global $pubids, $siteID;
    print "<html>
    <head>
    <link rel='stylesheet' type='text/css' href='styles/mangoCoreStyles.css' />
    <style>
    @media print {
    .noprint {
        display:none;
    }
    }
    </style>
    </head>";
    print "<body onload='window.print();'>\n"; 
} else {
    include("includes/mainmenu.php") ;
}

print "<div id='gridwrapper' style='width:800px;font-family:Trebuchet MSW, Tahoma,Arial, sans-serif;'>\n";
$start=date("Y-m-d H:i");
$end=date("Y-m-d H:i",strtotime("+96 hours"));

$sql="SELECT A.*, B.pub_name FROM jobs_inserter_packages A, publications B WHERE A.package_startdatetime>='$start' 
AND A.package_startdatetime<='$end' AND A.pub_id IN ($pubids) AND A.pub_id=B.id ORDER BY A.package_startdatetime ASC";
if($GLOBALS['debug']){print $sql;}
$dbPackages=dbselectmulti($sql);

if ($dbPackages['numrows']>0)
{
    print "<div style='float:left;'><p style='text-align:center;font-size:18px;font-weight:bold;'>
    Packages scheduled for the next 48 hours</p></div>";
    print "<div class='noprint' style='float:right;'><a class='noprint' href='?action=print' target='_blank'><img src='artwork/printer.png' width=32 border=0>Print</a>";
    print "</div>\n";
    print "<div style='clear:both;height:0px;'></div>";
    print "<table id='packageTable' class='ui-widget' style='width:100%'>\n<thead>\n";
    print "<tr><th>Publication</th><th>Run Name</th><th>Pub Date</th><th>Package Time</th><th>Draw</th><th>Inserts</tr>\n";
    print "\n</thead>
      <tbody>";
    
    
    foreach($dbPackages['data'] as $job)
    {
        print "<tr>\n";
        print "    <td>$job[pub_name]</td>\n";        
        print "    <td>$job[package_name]<br />".$inserters[$job['inserter_id']]."</td>\n";        
        print "    <td>$job[pub_date]</td>\n";        
        print "    <td>".date("D m/d \@ H:i",strtotime($job['package_startdatetime']))."</td>\n";        
        print "    <td>$job[inserter_request]</td>";        
        $jobid=$job['id'];
        $inserts='';
        //figure out the inserts
        $sql="SELECT A.*, B.account_name FROM inserts A, accounts B, jobs_inserter_packages C 
        WHERE C.id=$job[id] AND A.id=C.jacket_insert_id AND A.advertiser_id=B.id";
        $dbInsert=dbselectsingle($sql);
        if ($dbInsert['numrows']>0)
        {
            $jacket=$dbInsert['data'];
            $ji=$dbInsert['data'];
            if ($ji['received']==0)
            {
                $received="<span style='text-decoration:blink'>Not here!</span>";
            } else {
                $received='';
            }
            $inserts.="<a href='inserts.php?action=edit&insertid=$insert[id]' target='_blank'>Jacket: $ji[account_name] - $ji[insert_count] $received</a>, ";
        }
        //figure out the inserts
        $sql="SELECT A.*, B.account_name FROM inserts A, accounts B, jobs_packages_inserts C 
        WHERE C.package_id=$job[id] AND A.id=C.insert_id AND A.advertiser_id=B.id";
        $dbInsert=dbselectmulti($sql);
        if ($dbInsert['numrows']>0)
        {
            foreach($dbInsert['data'] as $insert)
            {
                if ($insert['received']==0)
                {
                    $received="<span style='text-decoration:blink'>Not here!</span>";
                } else {
                    $received='';
                }
                $inserts.="<a href='inserts.php?action=edit&insertid=$insert[id]' target='_blank'>$insert[account_name] - $insert[insert_count] $received</a>, ";
            }
            $inserts=substr($inserts,0,strlen($inserts)-2);
        
        } else {
            $inserts="No inserts booked yet for this package.";
        }
        print "    <td>";
        print $inserts;
        print "</td>\n";
        
        
        print "  </tr>\n";
    }
    print "  </tbody>\n</table>\n";
    ?>
    <script>
    $('#packageTable').dataTable( {
        "bPaginate": false,
        "sDom": '<"clear">lTfrtip',
        "iDisplayLength": 25,
        "bLengthChange": false,
        "bFilter": false,
        "bSort": true,
        "bInfo": false,
        "bJQueryUI": true,
        "bStateSave": false,
        "sPaginationType": "full_numbers",
        "bAutoWidth": true        
        } );
            
    </script>
    <?php
} else {
    print "<h2>You lucked out!<br>There are no scheduled packages in the next 96 hours...</h2>\n";
}
            
footer();
?>