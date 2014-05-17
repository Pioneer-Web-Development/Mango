<?php
//<!--VERSION: .9 **||**-->
if ($_POST['output']!='excel')
{
    include("includes/mainmenu.php") ;
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
if ($_POST)
{
$inventorydate=$_POST['inventorydate'];
    
} else {
$inventorydate=date("Y-m-d");
    
}
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
        print "<div style='float:left;'>Inventory date: <div><script>DateInput('inventorydate', true, 'YYYY-MM-DD','$inventorydate')</script></div>\n</div>\n";
        
        print "<div style='float:left;margin-left:20px;'>\n";
        print "Output to: ".input_select('output','Screen',array('screen'=>'Screen','excel'=>'Excel'));
        print "</div>\n";
        print "<div style='float:left;margin-left:20px;'><input type='submit' name='submit' value='Generate Report' /></div>\n";
        print "<div style='clear:both;'></div>\n";
        
    print "</div>\n";
print "</form>\n";
}
if ($_POST['submit']) {
    $inventorydate=$_POST['inventorydate'];
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
    $totalremaincount=0;
    $totalremainweight=0;
    $report='';
    $exceldata='';
    if ($_POST['vendor']!=0)
    {
        $vid=$_POST['vendor'];
        $vname=$newsprintVendors[$vid];
        $vrolls=vendor_rolls($vid,$vname,$inventorydate,$osource,$psize,$ptype,$output);
        $report.=$vrolls['output'];
        $totalinventorycount+=$vrolls['vtxcount'];
        $totalinventoryweight+=$vrolls['vtxweight']; 
    } else {
        foreach($newsprintVendors as $vid=>$vname)
        {
            if ($vid!=0)
            {
                $vrolls=vendor_rolls($vid,$vname,$inventorydate,$osource,$psize,$ptype,$output);
                $report.=$vrolls['output'];
                $totalinventorycount+=$vrolls['vtxcount'];
                $totalinventoryweight+=$vrolls['vtxweight']; 
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
        echo $tablestart.$report.$tableend; 
    } else {
        print "<table class='grid'>\n<tr>\n<th><a href='#' onclick='window.print();'><img src='artwork/printer.png' width=32 border=0>Print</a></th><th colspan=12>$reportname</th>\n</tr>\n";
        print $report;
        print "<tr><th colspan=4>GRAND TOTALS</th><th colspan=2>Remaining</th></tr>\n";
        print "<tr><th colspan=4>&nbsp;</th><th>Rolls</th><th>Weight</th></tr>\n";
               
        print "<tr></tr>\n";
        print "<tr><td colspan=4>Totals</td><td>$totalinventorycount</td><td>".sprintf("%.3f",$totalinventoryweight/1000).' MT'."</td></tr>\n";
        
        print "</table>\n";
        print "</div>
        </body>
        </html>";
        
    }
}  


function vendor_rolls($vendorid,$vendorname,$inventorydate,$source='',$size='',$type='',$output='screen')
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
       
       $tablestart="<tr><th colspan=6>$vendorname</th>\n</tr>\n";
       $tableend="<tr><th colspan=6></th></tr>\n";
       $rowstart="<tr>";
       $rowend="</tr>\n";
       $cellstart5="<td></td><td></td><td></td><td></td><td>";
       $cellstart="<td>";
       $cellend="</td>";
       $headstart="<th>";
       $headend="</th>";
       $break="<br />\n";
   }
    $output='';
    $totalremaincount=0;
    $totalremainweight=0;
    if ($source!=''){$source=" AND B.order_source='$source'";}else{$source="";}
    if ($size!=''){$size=" AND A.roll_width='$size'";}else{$size="";}
    if ($type!=''){$type=" AND A.common_name='$type'";}else{$type="";}
    
    $sql="SELECT DISTINCT(manifest_number) FROM rolls A, orders B WHERE A.validated=1 AND A.order_id=B.id 
    AND B.vendor_id=$vendorid $source $size $type ORDER BY A.manifest_number";
    $dbManifests=dbselectmulti($sql);
    if ($dbManifests['numrows']>0)
    {
        $output=$tablestart;
       $output.=$rowstart;
       $output.=$headstart.'Manifest'.$headend;
       $output.=$headstart.'Receive Date'.$headend;
       $output.=$headstart.'Type'.$headend;
       $output.=$headstart.'Size'.$headend;
       $output.=$headstart.'Ending Count'.$headend;
       $output.=$headstart.'Ending Weight'.$headend.$rowend;
       foreach($dbManifests['data'] as $manifest)
       {
            $manifestnumber=$manifest['manifest_number'];
            
            //lets get all the unique paper types that should exist
            $sql="SELECT DISTINCT(A.common_name) FROM rolls A, orders B WHERE A.validated=1 AND A.receive_datetime<='$inventorydate' 
            AND A.manifest_number='$manifestnumber' AND A.order_id=B.id AND B.vendor_id=$vendorid $source $size $type ORDER BY common_name";
            $dbTypes=dbselectmulti($sql);
            //$output.=$rowstart.$cellstart.'Names query: '.$sql.$cellend.$rowend;
            if ($dbTypes['numrows']>0)
            {
                foreach($dbTypes['data'] as $ptype)
                {
                    //now get the possible sizes
                    $commonname=$ptype['common_name'];
                    $sql="SELECT DISTINCT(roll_width) FROM rolls WHERE validated=1 AND common_name='$commonname' 
                    AND receive_datetime<='$inventorydate' AND manifest_number='$manifestnumber' 
                    $source $size $type ORDER BY roll_width ASC";
                    $dbSizes=dbselectmulti($sql);
                    if ($dbSizes['numrows']>0)
                    {
                        foreach($dbSizes['data'] as $rsize)
                        {
                            $rollwidth=$rsize['roll_width'];
                            
                            //now get the actual count information
                            $sql="SELECT A.receive_datetime, 
                            SUM(A.roll_weight) as rollweight, COUNT(A.id) as rollcount FROM rolls A, orders B 
                            WHERE A.common_name='$commonname' AND A.roll_width='$rollwidth' AND 
                            A.validated=1 AND A.receive_datetime<='$inventorydate'
                            AND A.order_id=B.id AND B.vendor_id=$vendorid
                            AND A.manifest_number='$manifestnumber' $source $size $type";
                            $dbRolls=dbselectsingle($sql);
                            
                            //$output.=$rowstart.$cellstart.'ROLL query: '.$sql.$cellend.$rowend;
                            
                            $roll=$dbRolls['data'];
                            $endingcount=$roll['rollcount'];
                            $endingweight=$roll['rollweight'];
                            $cmanifest=$manifestnumber;
                            $receivedate=date("m/d/Y",strtotime($roll['receive_datetime']));
                            $output.=$rowstart;
                            $output.=$cellstart.$cmanifest.$cellend;
                            $output.=$cellstart.$receivedate.$cellend;
                            $output.=$cellstart.$commonname.$cellend;
                            $output.=$cellstart.$rollwidth.$cellend;
                            $output.=$cellstart.$endingcount.$cellend;
                            $output.=$cellstart.sprintf("%.3f",$endingweight/1000).' MT'.$cellend.$rowend;
                            $totalendingcount+=$endingcount;
                            $totalendingweight+=$endingweight;
                            
                            $endingcount=0;
                            $endingweight=0;
                           
                        }
                    }
                }
            }
    
            
        }
    
        $output.=$rowstart.$cellstart.'Totals'.$cellend.$cellstart.$cellend.$cellstart.$cellend.$cellstart.$cellend;
        $output.=$cellstart.$totalendingcount.$cellend.$cellstart.sprintf("%.3f",$totalendingweight/1000).' MT'.$cellend.$rowend;
        $output.=$tableend;
    }

    return array("output"=>$output,"vtxcount"=>$totalendingcount,"vtxweight"=>$totalendingweight);
                

}

dbclose();
?>

