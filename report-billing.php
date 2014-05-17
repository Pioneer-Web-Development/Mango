<?php
//<!--VERSION: .9 **||**-->
if($_POST['output']=='excel')
{
    include ("includes/functions_db.php");
    include ("includes/functions_common.php");
    include ("includes/config.php");
} else {
    include ("includes/mainmenu.php");
}


global $pubs, $siteID;
//make sure we have a logged in user...

if ($_POST)
{
    $pubid=$_POST['pub_id'];
    $startdate=$_POST['startdate'];
    $enddate=$_POST['enddate'];
    if ($pubid==0){$pub="";}else{$pub="AND A.pub_id=$pubid";}
    $sql="SELECT A.* FROM jobs A, job_stats B WHERE A.site_id=$siteID AND A.stats_id=B.id $pub AND B.startdatetime_actual>='$startdate' AND B.stopdatetime_actual<='$enddate' AND A.continue_id=0 AND A.status<>99 ORDER BY B.startdatetime_actual ASC";
    $dbJobs=dbselectmulti($sql);
    if ($dbJobs['numrows']>0)
    {
        if ($_POST['output']=='screen')
        {
            build_report('onscreen',$dbJobs['data'],$startdate,$enddate,$pubs[$pubid]);
        } else {
            build_report('excel',$dbJobs['data'],$startdate,$enddate,$pubs[$pubid]);
        }   
    } else {
        print "Sorry, there is are no matching jobs. <a href='reportBilling.php'>Please try searching with different criteria.</a>\n";
    }
    
} else {
    
    print "<form method=post>\n";
    $startdate=date("Y-m-d",strtotime("-1 month"));
    $enddate=date("Y-m-d");
    make_select('pub_id',$pubs[0],$pubs,'Select Publication');
    make_date('startdate',$startdate,'Select Start Pub Date');
    make_date('enddate',$enddate,'Select Ending Pub Date');
    make_select('output','screen',array('screen'=>'To Screen','excel'=>'To Excel'),'Ouput to');
    make_submit('submit','Generate Report');
    print "</form>\n";
    print "</div>\n";
} 


function build_report($output,$jobs,$startdate,$enddate,$pubname)
{
    global $pressmen, $papertypes, $producttypes, $siteID;
    if ($pubname=='Please choose'){$pubname="ALL PUBLICATIONS";}
    $reportname="Billing report for $pubname printing between $startdate and $enddate"; 
    if ($output=='excel')
   {
        
        $filename="Billing_report_".str_replace(" ","_",$pubname);
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=".$filename.".xls;");
        header("Content-Type: application/ms-excel");
        header("Pragma: no-cache");
        header("Expires: 0");
    
      
       $tablestart="<?xml version='1.0'?>
    <?mso-application progid='Excel.Sheet'?>
    <Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet' xmlns:html='http://www.w3.org/TR/REC-html40'>
    <Worksheet ss:Name='Billing_Report'>
    <Table>";
       $tableend="</Table>
       </Worksheet>
       </Workbook>";


    
       $rowstart="<Row>";
       $rowend="</Row>";
       $cellstart="<Cell><Data ss:Type='String'>";
       $cellend="</Data></Cell>";
       $headstart="<Cell><Data ss:Type='String'>";
       $headend="</Data></Cell>";
       $break=" ";
   } else {
       
       $tablestart="<table class='report-clean-mango'>\n<tr>\n<th colspan=26>$reportname</th>\n</tr>\n";
       $tableend="</table>\n";
       $rowstart="<tr>";
       $rowend="</tr>\n";
       $cellstart="<td>";
       $cellend="</td>";
       $headstart="<th>";
       $headend="</th>";
       $break="<br />\n";
   }
   
   //first, lets create the header and headings
   print $tablestart;
   
   print "$rowstart";
   print "$headstart Job Name $headend";
   print "$headstart Run Date $headend";
   print "$headstart Pub Date $headend";
   print "$headstart Run Identity $headend";
   print "$headstart Shift Operator $headend";
   print "$headstart # Press $headend";
   print "$headstart # Mailroom $headend";
   print "$headstart # Runs $headend";
   print "$headstart Requested Press Order $headend";
   print "$headstart Starting Counter $headend";
   print "$headstart Ending Counter  $headend";
   print "$headstart B&W Pages $headend";
   print "$headstart Color Pages $headend";
   print "$headstart Style $headend";
   print "$headstart Base Plates $headend";
   print "$headstart Remake Plates $headend";
   print "$headstart Waste Plates $headend";
   print "$headstart Setup Time $headend";
   print "$headstart Newsprint Type $headend";
   print "$headstart Newsprint Desc $headend";
   print "$headstart Sticky Note $headend";
   print "$headstart Addressing $headend";
   print "$headstart Pallets $headend";
   print "$headstart Insert Setups $headend";
   print "$headstart Insert Total Pieces $headend";
   print "$headstart Bindery Setups $headend";
   print "$headstart Bindery Total Pieces $headend";
   print $rowend;
   
   
   foreach($jobs as $job)
   {
       $sql="SELECT * FROM job_stats WHERE id=$job[stats_id]";
       $dbStats=dbselectsingle($sql);
       $stats=$dbStats['data'];
       
       $sql="SELECT * FROM publications WHERE id=$job[pub_id]";
       $dbPub=dbselectsingle($sql);
       $pubinfo=$dbPub['data'];
       
       $sql="SELECT * FROM publications_runs WHERE id=$job[run_id]";
       $dbRun=dbselectsingle($sql);
       $runinfo=$dbRun['data'];
       
       $sql="SELECT * FROM jobs_sections WHERE job_id=$job[id]";
       $dbSections=dbselectsingle($sql);
       $sectioninfo=$dbSections['data'];
       $sec="";
       if ($sectioninfo['section1_lowpage']>0)
       {
           $sec.="Sec#1: ".($sectioninfo['section1_highpage']-$sectioninfo['section1_lowpage']+1).", ".$producttypes[$sectioninfo['section1_producttype']];
           if ($sectioninfo['section1_overrun']>0)
           {
               $sec.=" Overrun ".$sectioninfo['section1_overrun'];
           }
           
       }
       if ($sectioninfo['section2_lowpage']>0)
       {
           $sec.="$break Sec#2: ".($sectioninfo['section2_highpage']-$sectioninfo['section2_lowpage']+1).", ".$producttypes[$sectioninfo['section2_producttype']];
           if ($sectioninfo['section2_overrun']>0)
           {
               $sec.=" Overrun ".$sectioninfo['section2_overrun'];
           }
           
       }
       if ($sectioninfo['section3_lowpage']>0)
       {
           $sec.="$break Sec#3: ".($sectioninfo['section3_highpage']-$sectioninfo['section3_lowpage']+1).", ".$producttypes[$sectioninfo['section3_producttype']];
           if ($sectioninfo['section3_overrun']>0)
           {
               $sec.=" Overrun ".$sectioninfo['section3_overrun'];
           }
           
       }
       
       
       print $rowstart;
       print $cellstart.$pubinfo['pub_code']."-".$runinfo['run_name'].$cellend;
       print $cellstart.date("Y-m-d",strtotime($job['startdatetime'])).$cellend;
       print $cellstart.$job['pub_date'].$cellend;
       print $cellstart.$pubinfo['pub_code'].$cellend;
       print $cellstart.$pressmen[$stats['job_pressoperator']].$cellend;
       print $cellstart.$stats['job_pressman_count'].$cellend;
       print $cellstart."MAILROOM COUNT".$cellend;
       print $cellstart."1".$cellend;
       print $cellstart.$job['draw'].$cellend;
       print $cellstart.$stats['counter_start'].$cellend;
       print $cellstart.$stats['counter_stop'].$cellend;
       print $cellstart.$stats['pages_bw'].$cellend;
       print $cellstart.$stats['pages_color'].$cellend;
       print $cellstart.$sec.$cellend;
       print $cellstart.($stats['plates_bw']+$stats['plates_color']).$cellend;
       print $cellstart.$stats['plates_remake'].$cellend;
       print $cellstart.$stats['plates_waste'].$cellend;
       print $cellstart.$stats['setup_time'].$cellend;
       
       //look up billing code for specific paper types
       $sql="SELECT * FROM paper_types WHERE id=$job[papertype]";
       $dbPaper=dbselectsingle($sql);
       print $cellstart.$dbPaper['data']['billing_code'].$cellend;
       print $cellstart.$dbPaper['data']['common_name'].$cellend;
       
       //this section needs to wait for mailroom tie in piece
       print $cellstart."Stick$break Info TK".$cellend;
       print $cellstart."Addressing$break Info TK".$cellend;
       print $cellstart."Pallet$break Info TK".$cellend;
       print $cellstart."Hopper$break Info TK".$cellend;
       print $cellstart."Total Pieces $break Info TK".$cellend;
       print $cellstart."Binder Hopper$break Info TK".$cellend;
       print $cellstart."Binder Pieces$break Info TK".$cellend;
       
       
       
       $sql="SELECT * FROM jobs_inserter_runData where job_id=$jobid";
       $mailstats=dbselectsingle($sql);
                                                                 
       
       
       print $rowend;
   } 
   print $tableend; 
}
 
if($_POST['output']=='excel')
{
    dbclose();
} else {
    footer();
} 
?>
