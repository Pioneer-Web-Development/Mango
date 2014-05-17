<?php

global $pubs;
//make sure we have a logged in user...

if ($_POST)
{
    if ($_POST['output']==0)
    {
        include("includes/mainmenu.php");
    } else {
         include ("includes/functions_db.php");
         include ("includes/config.php");
    }
    global $siteID;
    $pubid=$_POST['pub_id'];
    $startdate=$_POST['startdate'];
    $enddate=$_POST['enddate'];
    $folder=$_POST['folder'];
    $sql="SELECT * FROM job_stats WHERE startdatetime_actual>='$startdate 00:00' AND stopdatetime_actual<='$enddate 23:59' AND folder=$folder ORDER BY counter_start ASC";
    //print "Searching with $sql<br />\n";
    $dbJobs=dbselectmulti($sql);
    if ($dbJobs['numrows']>0)
    {
        if ($_POST['output']=='0')
        {
            build_report('onscreen',$dbJobs['data'],$startdate,$enddate);
        } else {
            build_report('excel',$dbJobs['data'],$startdate,$enddate);
        }   
    } else {
        print "Sorry, there are no matching jobs. <a href='report-counter.php'>Please try searching with different criteria.</a>\n";
    }
    
} else {
    include ("includes/mainmenu.php");
    print "This report is designed to provide you with start/stop counters for all products printed on the press between the specified dates.<br />";
    print "<form method=post>\n";
    $startdate=date("Y-m-d",strtotime("-1 month"));
    $enddate=date("Y-m-d");
    make_date('startdate',$startdate,'Select Start Pub Date');
    make_date('enddate',$enddate,'Select Ending Pub Date');
    make_select('output','Screen',array("Screen","Excel"),'Output to');
    make_select('folder',$GLOBALS['folders'][$GLOBALS['defaultFolder']],$GLOBALS['folders'],'Which folder');
    make_submit('submitbutton','Generate Report');
    print "</form>\n";
    
} 


function build_report($output,$allstats,$startdate,$enddate)
{
    global $pressmen, $papertypes, $siteID;
    
    $reportname="Counter report from $startdate to $enddate"; 
    if ($output=='excel')
   {
        
        $filename="Counter_report";
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=".$filename.".xls;");
        header("Content-Type: application/ms-excel");
        header("Pragma: no-cache");
        header("Expires: 0");
    
      
       $tablestart="<?xml version='1.0'?>
    <?mso-application progid='Excel.Sheet'?>
    <Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet' xmlns:html='http://www.w3.org/TR/REC-html40'>
    <Worksheet ss:Name='$reportname'>
    <Table>";
       $tableend="</Table></Worksheet></Workbook>";


       $printer="";
       $rowstart="<Row>";
       $rowend="</Row>";
       $cellstart="<Cell><Data ss:Type='String'>";
       $cellend="</Data></Cell>";
       
   } else {
       
       $tablestart="<table class='report-clean-mango'>
       <tr>\n<th><a href='#' onclick='window.print();'><img src='artwork/printer.png' width=32 border=0>Print</a></th>
       <th colspan=9>$reportname</th>\n</tr>\n";
       $tableend="</table></body>\n</html>\n";
       $rowstart="<tr>";
       $rowend="</tr>\n";
       $cellstart="<td>";
       $cellend="</td>";
       
   }
   
   //first, lets create the header and headings
   print $tablestart;
   print "$rowstart";
   print "$cellstart Job Name $cellend";
   print "$cellstart Run Date $cellend";
   print "$cellstart Pub Date $cellend";
   print "$cellstart Starting Counter $cellend";
   print "$cellstart Ending Counter  $cellend";
   print "$cellstart Counter Gap $cellend";
   print "$cellstart Draw $cellend";
   print "$cellstart Gross $cellend";
   print "$cellstart Overage $cellend";
   print "$cellstart Overage % $cellend";
   print $rowend;
   
   $i=0;
   foreach($allstats as $stats)
   {
       $sql="SELECT * FROM jobs WHERE id=$stats[job_id]";
       $dbJob=dbselectsingle($sql);
       $job=$dbJob['data'];
       
       $sql="SELECT * FROM publications WHERE id=$job[pub_id]";
       $dbPub=dbselectsingle($sql);
       $pubinfo=$dbPub['data'];
       
       $sql="SELECT * FROM publications_runs WHERE id=$job[run_id]";
       $dbRun=dbselectsingle($sql);
       $runinfo=$dbRun['data'];
       
       print $rowstart;
       print $cellstart."<a href='pressJobs.php?action=pressdata&jobid=$job[id]' target='blank'>".$pubinfo['pub_code']."-".$runinfo['run_name']."</a>".$cellend;
       print $cellstart.date("Y-m-d H:i",strtotime($stats['startdatetime_actual'])).$cellend;
       print $cellstart.$job['pub_date'].$cellend;
       print $cellstart.$stats['counter_start'].$cellend;
       print $cellstart.$stats['counter_stop'].$cellend;
       if ($i!=0)
       {
           $i=$stats['counter_start']-$i;
           print $cellstart.$i.$cellend;
           $i=$stats['counter_stop'];
       } else {
           $i=$stats['counter_stop'];
       }
       
       $overage=$stats['gross']-$stats['draw'];
       if($stats['draw']>0)
       {
            $overageper=round($overage/$stats['draw'],1)."%";
       } else {
           $overageper='N/A';
       }
       print $cellstart.$stats['draw'].$cellend;
       print $cellstart.$stats['gross'].$cellend;
       print $cellstart.$overage.$cellend;
       print $cellstart.$overageper.$cellend;
       
       
       print $rowend;
   } 
   print $tableend; 
}
 
if($_POST['output']==0)
{
    footer();  
} else {
    dbclose();  
}

?>