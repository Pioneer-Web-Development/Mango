<?php
if($_POST['output']=='csv')
{
    include("includes/functions_db.php");
    include("includes/functions_common.php");
    show_report('csv');
} else {
    include("includes/mainmenu.php");
    global $pubs;
    if($_POST)
    {
        $pub=$_POST['pub'];
        $start=$_POST['start'];
        $end=$_POST['end'];
    } else {
        $pub=0;
        $start=date("Y-m-d");
        $end=date("Y-m-d",strtotime("+1 week"));
    }
    print "<form method=post>\n";
        make_select('pub',$pubs[$pub],$pubs,'Publication');
        make_date('start',$start,'Start Date');
        make_date('end',$end,'End Date');
        make_select('output','screen',array('screen'=>'Screen','csv'=>'Excel'),'Output');
        make_submit('submit','Generate Report');
    print "</form>\n";
    show_report('screen');
} 
  

function show_report($output)
{
    global $pubs;
    $pub=$_POST['pub'];
    $start=$_POST['start'];
    $end=$_POST['end'];
    
    $sql="SELECT * FROM accounts";
    $dbAccounts=dbselectmulti($sql);
    if($dbAccounts['numrows']>0)
    {
        foreach($dbAccounts['data'] as $account)
        {
            $accounts[$account['id']]=stripslashes($account['account_name']);
        }
    }
    switch($output)
    {
        case 'csv':
            $tablestart="Day,Pub Date,Control #,Account Name,Std Pages,Real Pages,Description,Request,Zoning,Receive Date,Receive Qty,Receive Type/Qty\n";
            $tableend='';
            $rowstart='';
            $highlightstart='';
            $rowend="\n";
            $newcell=",";
        break;
        
        case 'screen':
            $tablestart="<table class='report-clean-mango'>
            <tr><th>Day</th><th style='width:60px;'>Pub Date</th><th style='width:60px;'>Control #</th><th>Account Name</th><th>Std Pages</th><th>Tab Pages</th><th>Description</th><th>Request</th><th>Zoning</th><th>Receive Date</th><th>Receive Qty</th><th>Receive Type/Qty</th></tr>\n";
            $tableend="</table>\n";
            $highlightstart="<tr style='background-color:#fff000;border-top:2px solid black;margin-top:10px;'><td>";
            $rowstart='<tr><td>';
            $rowend="</td></tr>\n";
            $newcell="</td><td>";
        break;
    }
    
    $sql="SELECT A.*, B.pub_id, B.run_id, B.insert_quantity, B.insert_date FROM inserts A, inserts_schedule B WHERE A.id=B.insert_id AND B.pub_id=$pub AND B.insert_date>='$start' AND B.insert_date<='$end' ORDER BY insert_date ASC";
    //print $sql;
    $dbInserts=dbselectmulti($sql);
    if($dbInserts['numrows']>0)
    {
        $report.=$tablestart;
        $cdate=date("d-M",strtotime($start));
        $first=true;
        foreach($dbInserts['data'] as $insert)
        {
            $day=date("D",strtotime($insert['insert_date']));
            $date=date("d-M",strtotime($insert['insert_date']));
            
            if($insert['weprint_id']!=0)
            {
                $account="INHOUSE - ".$pubs[$insert['pub_id']];
            } else {
                $account=$accounts[$insert['advertiser_id']];
            }
            
            $controlnumber=$insert['control_number'];
            $tabpages=$insert['tab_pages'];
            $pages=$insert['pages'];
            $tagline=stripslashes($insert['insert_tagline']);
            $insertRequest=$insert['insert_buycount'];
            $zoning='zoning t/k'; //@TODO -- work on zoning for insert report
            if($insert['receive_date']!='' && $insert['receive_date']!='Null')
            {
                $receiveDate=date("m/d/Y",strtotime($insert['receive_date']));
            } else {
                $receiveDate='Not received';
            }
            $receiveQty=$insert['receive_count'];
            $receiveType=$insert['ship_quantity'].' '.$insert['ship_type'];
            if($date!=$cdate || $first)
            {
                $cdate=$date;
                $report.=$highlightstart;
                $first=false; 
            } else {
                $report.=$rowstart;
            }
            $report.=$day;
            $report.=$newcell.$date;
            $report.=$newcell.$controlnumber;
            $report.=$newcell.$account;
            $report.=$newcell.$pages;
            $report.=$newcell.$tabpages;
            $report.=$newcell.$tagline;
            $report.=$newcell.$insertRequest;
            $report.=$newcell.$zoning;
            $report.=$newcell.$receiveDate;
            $report.=$newcell.$receiveQty;
            $report.=$newcell.$receiveType;
            $report.=$rowend;
        }
        $report.=$tableend;
        
        if($output=='csv')
        {
            $filename="insertReport-".$pubs[$pub].'-'.date("Y-m-d",strtotime($start)).".csv";
            header('Content-Type: text/plain'); // plain text file
            header('Content-Disposition: attachment; filename="'.$filename.'"');
        }
        print $report;
    } else {
        print "Sorry, there are no ads for those search criteria.";
    }
}
  
if($_POST['output']=='csv')
{
    dbclose(); 
} else {
    footer();
}
?>
