<?php
//<!--VERSION: .9 **||**-->
if ($_POST['output']!='excel')
{
    
        
    include("includes/mainmenu.php") ;
    $scriptpath='http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'] ;
    if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}
    print "<body> <div id='wrapper'>";
} else {
    require ('includes/functions_db.php');
    require ('includes/functions_formtools.php');
    require ('includes/functions_graphics.php');
    require ('includes/config.php');
    require ('includes/functions_common.php');
}
global $newsprintVendors;
//paper types
$sql="SELECT * FROM paper_types WHERE status<>99 ORDER BY common_name";
$dbPaper=dbselectmulti($sql);
$papertypes=array();
$papertypes[0]="Type";
if ($dbPaper['numrows']>0)
{
    foreach($dbPaper['data'] as $paper)
    {
        $papertypes[$paper['id']]=$paper['common_name'];
    }
}

//paper sizes
$sql="SELECT * FROM paper_sizes WHERE status<>99 ORDER BY width ASC";
$dbSizes=dbselectmulti($sql);
$papersizes=array();
$papersizes[0]="Size";
if ($dbSizes['numrows']>0)
{
    foreach($dbSizes['data'] as $size)
    {
        $papersizes[$size['id']]=$size['width'];
    }
}
$enddate=date("Y-m-d");
$startdate=date("Y")."-1-1";
$vendor="";
$status=""; 
if ($_POST['output']!='excel')
{
  
print "<form action='$_SERVER[PHP_SELF]' method=post>\n";
    print "<div id='search' style='padding-left:20px;'>Vendor: \n";
        print input_select('vendor',$newsprintVendors[$_POST['vendor']],$newsprintVendors)."<br>\n";
        print "Order Source: ";
        print input_select('osource',$ordersources[$_POST['osource']],$ordersources)."<br>\n";
        print "Paper type: ";
        print input_select('ptype',$papertypes[$_POST['ptype']],$papertypes)."<br>\n";
        print "Paper size: ";
        print input_select('psize',$papersizes[$_POST['psize']],$papersizes)."<br>\n";
        print "<div style='float:left;'>Start date: <div><script>DateInput('startdate', true, 'YYYY-MM-DD','$startdate')</script></div>\n</div>\n";
        print "<div style='float:left;'>End date: <div><script>DateInput('enddate', true, 'YYYY-MM-DD','$enddate')</script></div>\n</div>\n";
        
        print "<div style='float:left;margin-left:20px;'>\n";
        print "Output to: ".input_select('output','Screen',array('screen'=>'Screen','excel'=>'Excel'));
        print "</div>\n";
        print "<div style='float:left;margin-left:20px;'><input type='submit' name='submit' value='Generate Report' /></div>\n";
        print "<div style='clear:both;'></div>\n";
        
    print "</div>\n";
print "</form>\n";
}
if ($_POST['submit']) {
    $enddate=$_POST['enddate'];
    $startdate=$_POST['startdate'];
    if ($_POST['psize']!=0){
        $psize=$papersizes[$_POST['psize']];
    } else {
        $psize="";
    }
     if ($_POST['ptype']!=0){
        $ptype=$papertypes[$_POST['ptype']];
    } else {
        $ptype="";
    }
    $osource=$_POST['osource'];
    if ($osource=='0'){$osource='';}
    $output=$_POST['output'];
    $totalconsumecount=0;
    $totalconsumeweight=0;
    $report='';
    $exceldata='';
    if ($_POST['vendor']!=0)
    {
        $vid=$_POST['vendor'];
        $vname=$newsprintVendors[$vid];
        $vrolls=vendor_rolls($vid,$vname,$startdate,$enddate,$osource,$psize,$ptype,$output);
        $report.=$vrolls['output'];
        $exceldata=$vrolls['exceldata'];
        $totalconsumecount+=$vrolls['vtccount'];
        $totalconsumeweight+=$vrolls['vtcweight'];    
    } else {
        foreach($newsprintVendors as $vid=>$vname)
        {
            if ($vid!=0)
            {
                $vrolls=vendor_rolls($vid,$vname,$startdate,$enddate,$osource,$psize,$ptype,$output);
                $report.=$vrolls['output'];
                $exceldata.=$vrolls['exceldata'];
                $totalconsumecount+=$vrolls['vtccount'];
                $totalconsumeweight+=$vrolls['vtcweight'];
            }
            
        }
    }
    
    if ($output=='excel')
    {
        $title=str_replace(":","","Newsprint_Consumption - ".date("Y-m-d H:i"));
        $title=str_replace(" ","_",$title);
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$title.xls;");
        header("Content-Type: application/ms-excel");
        header("Pragma: no-cache");
        header("Expires: 0");
        $tablestart="<?xml version='1.0'?>
        <?mso-application progid='Excel.Sheet'?>
        <Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet' xmlns:html='http://www.w3.org/TR/REC-html40'>
        <Worksheet ss:Name='Consumption Report'>
        <Table>";
        $tableend="</Table></Worksheet></Workbook>";
        echo $tablestart.$exceldata.$tableend; 
    } else {
        print "<table class='grid'>\n<tr>\n<th><a href='#' onclick='window.print();'><img src='artwork/printer.png' width=32 border=0>Print</a></th><th colspan=3>$reportname</th>\n</tr>\n";
        print $report;
        print "<tr><th colspan=2>GRAND TOTALS</th><th colspan=2>Consumed</th></tr>\n";
        print "<tr><th colspan=2>&nbsp;</th><th>Rolls</th><th>Weight</th></tr>\n";
               
        print "<tr></tr>\n";
        print "<tr><td colspan=2>Totals</td><td>$totalconsumecount</td><td>".sprintf("%.3f",$totalconsumeweight/1000).' MT'."</td>
        </tr>\n";
        
        print "</table>\n";
        print "</div>
        </body>
        </html>";
        
    }
}  


function vendor_rolls($vendorid,$vendorname,$startdate,$enddate,$source='',$size='',$type='',$output='screen')
{
    global $siteID;
    if ($output=='excel')
   {
       $tablestart="";
       $tableend="";
       $rowstart="<Row>";
       $rowstart="<Row>";
       $rowend="</Row>";
       $headstart="<Cell>";
       $headend="</Cell>";
       $cellstart5="<Cell></Cell><Cell></Cell><Cell></Cell><Cell></Cell><Cell><Data ss:Type='String'>";
       $cellstart="<Cell><Data ss:Type='String'>";
       $cellend="</Data></Cell>";
       $break="\r\n";
   } else {
       
       $tablestart="<tr><th colspan=4>$vendorname</th>\n</tr>\n";
       $tableend="<tr><th colspan=4></th></tr>\n";
       $rowstart="<tr>";
       $rowend="</tr>\n";
       $cellstart5="<td></td><td></td><td></td><td></td><td>";
       $cellstart="<td>";
       $cellend="</td>";
       $headstart="<th>";
       $headend="</th>";
       $break="<br />\n";
   }
    $vtotalstartcount=0;
    $vtotalstartweight=0;
    $vtotalreceivecount=0;
    $vtotalreceiveweight=0;
    $vtotalconsumecount=0;
    $vtotalconsumeweight=0;
    $vtotalremaincount=0;
    $vtotalremainweight=0;
    $vtotalremainweight=0;
    $startstamp=strtotime($startdate);
    $endstamp=strtotime($enddate);
    if ($source!=''){$source=" AND B.order_source='$source'";}else{$source="";}
    if ($size!=''){$size=" AND A.roll_width='$size'";}else{$size="";}
    if ($type!=''){$type=" AND A.common_name='$type'";}else{$type="";}
    
    
    //lets start by getting all rolls that have been consumed in the given time range
    $sql="SELECT A.common_name, SUM(A.roll_weight) as totalweight, COUNT(A.id) as rollcount, B.order_source FROM rolls A, orders B WHERE A.site_id=$siteID AND A.batch_date>='$startdate' AND A.batch_date<='$enddate' AND A.status=9 AND A.order_id=B.id AND B.vendor_id=$vendorid $source $size $type GROUP BY A.common_name";
    //print $sql."<br />";
    $dbRolls=dbselectmulti($sql);
    if ($dbRolls['numrows']>0)
    {
       $output=$tablestart;
       $output.=$rowstart.$headstart.'Source'.$headend;
       $output.=$headstart.'Type'.$headend;
       $output.=$headstart.'Consumed Count'.$headend;
       $output.=$headstart.'Consumed Weight'.$headend;
       $output.=$rowend;
       
       $ctype='';
       $cwidth='';
       $consumecount=0;
       $consumeweight=0;
          foreach($dbRolls['data'] as $roll)
          {
              $ctype=$roll['common_name'];
              $osource=$roll['order_source'];
              $consumecount=$roll['rollcount'];
              $consumeweight=$roll['totalweight'];
              $output.=$rowstart.$cellstart.$osource.$cellend;
              $output.=$cellstart.$ctype.$cellend;
              $output.=$cellstart.$consumecount.$cellend;
              $output.=$cellstart.sprintf("%.3f",$consumeweight/1000).' MT'.$cellend;
              $vtotalconsumecount+=$consumecount;
              $vtotalconsumeweight+=$consumeweight;
            

          }
       $output.=$tableend;
    }
    $output.=$rowstart.$cellstart.$cellend.$cellstart.$cellend;
    $output.=$cellstart.$vtotalconsumecount.$cellend;
    $output.=$cellstart.sprintf("%.3f",$vtotalconsumeweight/1000).' MT'.$cellend;
    return array("output"=>$output,"vtccount"=>$vtotalconsumecount,"vtcweight"=>$vtotalconsumeweight);
                

}

dbclose();
?>

