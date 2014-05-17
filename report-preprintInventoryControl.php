<?php
/*
This report generates a list of inserts that have been received, sorted by receive date
 Account    Publicaton    Control #    Date Rec.    Amount    # Pallets    Insert Date    Type    Paper    Runability    Date Ran
Comics    PO    6199    2/3/2014    17,356    1    2/9/2014    R    Th    8    

*/
if($_POST['output']=='csv')
{  
    include("includes/functions_db.php");
    include("includes/config.php");
} else {
    $output='screen';
    include ("includes/mainmenu.php");    
}
if ($_POST)
{
    $output=$_POST['output'];
    if($output=='screen')
    {
        print "<div class='noprint'><a href='#' onclick='window.print();'><img src='artwork/printer.png' border=0 width=32>Print Report</a><br />\n";
        print "<a href='?action=runagain'>Run another report</a><br /></div>\n";
    }
    $pubid=$_POST['pub_id'];
    $startdate=$_POST['startdate'];
    $enddate=$_POST['enddate'];
    if ($pubid==0)
    {
        $sql="SELECT A.*, B.pub_name, C.account_name FROM inserts_received A, publications B, accounts C WHERE A.receive_date>='$startdate' AND A.receive_date<='$enddate' AND A.insert_pub_id=B.id AND A.advertiser_id=C.id GROUP BY A.insert_pub_id ORDER BY B.pub_name, A.receive_date DESC";
    } else {
        //need to decide how to pull against inserts_schedule 
        //maybe just pull all the matching inserts, then check for a schedule in the loop and only show it if it's the same pub???
        $sql="SELECT A.*, B.pub_name, C.account_name FROM inserts_received A, publications B, accounts C WHERE A.insert_pub_id=$pubid AND A.receive_date>='$startdate' AND A.receive_date<='$enddate' AND A.insert_pub_id=B.id AND A.advertiser_id=C.id ORDER BY B.pub_name, A.receive_date DESC";
    }
    $dbJobs=dbselectmulti($sql);
    if ($dbJobs['numrows']>0)
    {
        buildReport($dbJobs['data'],$output,$pubdate);
    }
     
} else {
    global $pubs;
    $outputs=array('screen'=>'Display on screen','csv'=>'Output to excel');
    print "<form method=post>\n";
    
    $startdate=date("Y-m-d",strtotime("-1 month"));
    make_select('pub_id',$pubs[0],$pubs,'Select Publication');
    make_select('output',$outputs[0],$outputs,'Output to');
    make_date('startdate',$startdate,'Select Start Receive Date');
    make_date('enddate',$enddate,'Select Ending Receive Date');
    make_submit('submit','Run report');
    print "</form>\n";
}  
  
  
  
function buildReport($jobs,$output,$date)
{
    global $enableJobStops, $enableBenchmarks, $producttypes, $pressmen,$pubs,$papertypes, $sizes;
    $span="colspan='1'";
    if($output=='csv')
    {
        $tableStart='';
        $tableEnd='';
        $rowStart='';
        $rowEnd="\n";
        $cellStart='';
        $cellEnd=',';
        $cell2Start='';
        $cell2End=',,';
        $cellStartBold="";
        $cell2StartBold="";
        $cellFullStartBold="";
        $headStart='';
        $headEnd='';
        $cellStartHead='';
        $cellEndHead=',';
        $cell2StartHead='';
        $cell2EndHead=',,';
        $cellStartBoldHead="";
        $cell2StartBoldHead="";
        $cellFullStartBoldHead="";
        $reportName="Preprint Inventory Control Log\n";
        $bold='';
    } else {
        $bold="style='font-weight:bold;'";
        
        $tableStart="\n\n<table class='report-clean-mango' style='margin-bottom:20px;'>\n";
        $tableEnd="</tbody></table>\n";
        $rowStart='<tr>';
        $rowEnd="</tr>\n";
        $cellStart='<td>';
        $cellEnd='</td>';
        $cell2Start='<td colspan=2>';
        $cell2End='</td>';
        $cellStartBold="<td $bold>";
        $cell2StartBold="<td colspan=2 $bold>";
        $cellFullStartBold="<td colspan=16 $bold>";
        $headStart="<thead>";
        $headEnd="</thead>";
        $cellStartHead='<th>';
        $cellEndHead='</th>';
        $cell2StartHead='<th colspan=2>';
        $cell2EndHead='</th>';
        $cellStartBoldHead="<th $bold>";
        $cell2StartBoldHead="<th colspan=2 $bold>";
        $cellFullStartBoldHead="<th colspan=18 $bold>";
        
        $reportName="<div style='width:100%;margin-bottom:20px;text-align:center;font-weight:bold;font-size:24px;'>Preprint Inventory Control Log</div>\n";
    }
    $pubid=0;
    //here we display the job data and all it's stats, we'll use a table layout
    $jobdetails.=$reportName;
    
    //Account    Publicaton    Control #    Date Rec.    Amount    # Pallets    Insert Date    Type    Paper    Runability    Date Ran
    //Comics    PO    6199    2/3/2014    17,356    1    2/9/2014    R    Th    8    

    
    //now the table
    $jobdetails.=$tableStart;
        
    $pubid=$job['pub_id'];
    $jobdetails.=$headStart; 
    $jobdetails.=$rowStart.$cellFullStartBoldHead.$pubs[$pubid].$cellEndHead.$rowEnd;
    $jobdetails.=$rowStart;
    $jobdetails.=$cellStartHead.'Account'.$cellEndHead;
    $jobdetails.=$cellStartHead.'Publication'.$cellEndHead;
    $jobdetails.=$cellStartHead.'Control #'.$cellEndHead;
    $jobdetails.=$cellStartHead.'Date Received'.$cellEndHead;
    $jobdetails.=$cellStartHead.'Qty Received'.$cellEndHead;
    $jobdetails.=$cellStartHead.'# Pallets'.$cellEndHead;
    $jobdetails.=$cellStartHead.'Insert Date'.$cellEndHead;
    $jobdetails.=$cellStartHead.'Type'.$cellEndHead;
    $jobdetails.=$cellStartHead.'Brdsht Pages'.$cellEndHead;
    $jobdetails.=$cellStartHead.'Runability'.$cellEndHead;
    $jobdetails.=$cellStartHead.'Other'.$cellEndHead;
    $jobdetails.=$cellStartHead.'InsertID'.$cellEndHead;
    $jobdetails.=$rowEnd;
    $jobdetails.=$headEnd;
    
    foreach($jobs as $job)
    {
        if($GLOBALS['debug']){
            print "Job info <pre>\n";
            print_r($job);
            print "</pre>\n";
        }
        
        $account=stripslashes($job['account_name']);
        $publication=stripslashes($job['pub_name']);
        $control=stripslashes($job['control_number']);
        $dateReceived=date("m/d/Y",strtotime($job['receive_datetime']));
        $insertDate=date("m/d/Y",strtotime($job['scheduled_pubdate']));
        $qtyReceived=stripslashes($job['receive_count']);
        $insertPages=$job['std_pages'];
        $insertType='';
        if($job['slick_sheet']){$insertType.="Sl ";}
        if($job['single_sheet']){$insertType.="Si ";}
        if($job['sticky_note']){$insertType.="Sn ";}
        if($insertType==''){$insertType='R';}
        if($job['ship_type']=='pallet'){$pallets=$job['ship_quantity'];}else{$pallets=$job['ship_quantity'].' boxes';}
        $runability=$job['runability'];
        $other='';
        
        
        $jobdetails.=$rowStart;
        $jobdetails.=$cellStart.$account.$cellEnd;
        $jobdetails.=$cellStart.$publication.$cellEnd;
        $jobdetails.=$cellStart.$control.$cellEnd;
        $jobdetails.=$cellStart.$dateReceived.$cellEnd;
        $jobdetails.=$cellStart.$qtyReceived.$cellEnd;
        $jobdetails.=$cellStart.$pallets.$cellEnd;
        $jobdetails.=$cellStart.$insertDate.$cellEnd;
        $jobdetails.=$cellStart.$insertType.$cellEnd;
        $jobdetails.=$cellStart.$insertPages.$cellEnd;
        $jobdetails.=$cellStart.$runability.$cellEnd;
        $jobdetails.=$cellStart.$other.$cellEnd;
        $jobdetails.=$cellStart."<a href='http://mango.newswest.com/insertsReceived.php?action=edit&id=$job[id]' target='_blank'>$job[id]</a>".$cellEnd;
        $jobdetails.=$rowEnd;
    }
    if($output=='csv')
    {
        header("Content-Type: text/csv"); // plain text file
        header('Content-Disposition: attachment; filename="dailyPressReport-'.$date.'.csv"');
    } 
    print $jobdetails;
}

if($output=='screen')
{
    footer();
} else {
    dbclose();
}
?>
