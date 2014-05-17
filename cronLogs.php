<?php
include("includes/mainmenu.php");
//<!--VERSION: 1.0 **||**-->
global $siteID;
$sql="SELECT * FROM cron_log WHERE site_id=$siteID ORDER BY cron_datetime DESC LIMIT 100";
$dbLogs=dbselectmulti($sql);
tableStart('','Job,Date/Time',6,'');
if($dbLogs['numrows']>0)
{
    foreach($dbLogs['data'] as $log)
    {
        print "<tr>\n";
        print "<td>$log[cron_process]</td>";
        print "<td>$log[cron_datetime]</td>";
        print "<td colspan=4>$log[cron_notes]</td>";
        print "</tr>\n";
    }
} else {
    print "<tr><td colspan=6>No cron data at this time</td></tr>\n";
}
print "</table>\n"; 
tableEnd($dbLogs);
footer();
?>
