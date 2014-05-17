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
 //build vendor list
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
$startdate=date("Y-m-d",strtotime('-1 months'));
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
    $totalreceivecount=0;
    $totalreceiveweight=0;
    $totalconsumecount=0;
    $totalconsumeweight=0;
    $totalremaincount=0;
    $totalremainweight=0;
    $report='';
    $exceldata='';
    if ($_POST['vendor']!=0)
    {
        $vid=$_POST['vendor'];
        $vname=$vendors[$vid];
        $vrolls=vendor_rolls($vid,$vname,$startdate,$enddate,$osource,$psize,$ptype,$output);
        $report.=$vrolls['output'];
        $totalstartcount+=$vrolls['vtscount'];
        $totalstartweight+=$vrolls['vtsweight'];
        $totalreceivecount+=$vrolls['vtrcount'];
        $totalreceiveweight+=$vrolls['vtrweight'];
        $totalconsumecount+=$vrolls['vtccount'];
        $totalconsumeweight+=$vrolls['vtcweight'];
        $totalremaincount+=$vrolls['vtxcount'];
        $totalremainweight+=$vrolls['vtxweight'];
        $exceldata=$vrolls['exceldata'];    
    } else {
        foreach($vendors as $vid=>$vname)
        {
            if ($vid!=0)
            {
                $vrolls=vendor_rolls($vid,$vname,$startdate,$enddate,$osource,$psize,$ptype,$output);
                $report.=$vrolls['output'];
                $totalstartcount+=$vrolls['vtscount'];
                $totalstartweight+=$vrolls['vtsweight'];
                $totalreceivecount+=$vrolls['vtrcount'];
                $totalreceiveweight+=$vrolls['vtrweight'];
                $totalconsumecount+=$vrolls['vtccount'];
                $totalconsumeweight+=$vrolls['vtcweight'];
                $totalremaincount+=$vrolls['vtxcount'];
                $totalremainweight+=$vrolls['vtxweight'];
                $exceldata.=$vrolls['exceldata'];
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
        print "<table class='grid'>\n<tr>\n<th><a href='#' onclick='window.print();'><img src='artwork/printer.png' width=32 border=0>Print</a></th><th colspan=12>$reportname</th>\n</tr>\n";
        print $report;
        print "<tr><th colspan=5>GRAND TOTALS</th><th colspan=2>Starting</th><th colspan=2>Received</th><th colspan=2>Consumed</th><th colspan=2>Remaining</th></tr>\n";
        print "<tr><th colspan=5>&nbsp;</th><th>Rolls</th><th>Weight</th><th>Rolls</th><th>Weight</th><th>Rolls</th><th>Weight</th><th>Rolls</th><th>Weight</th></tr>\n";
               
        print "<tr></tr>\n";
        print "<tr><td colspan=5>Totals</td><td>$totalstartcount</td><td>".sprintf("%.3f",$totalstartweight/1000).' MT'."</td><td>$totalreceivecount</td><td>".sprintf("%.3f",$totalreceiveweight/1000).' MT'."</td>
        <td>$totalconsumecount</td><td>".sprintf("%.3f",$totalconsumeweight/1000).' MT'."</td>
        <td>$totalremaincount</td><td>".sprintf("%.3f",$totalremainweight/1000).' MT'."</td></tr>\n";
        
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
       
       $tablestart="<tr><th colspan=13>$vendorname</th>\n</tr>\n";
       $tableend="<tr><th colspan=13></th></tr>\n";
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
    
    
    //lets start by getting all rolls
    //this will be all rolls for this site, not deleted, matching vendor, source, size and type
    $sql="SELECT A.common_name, A.roll_tag, A.roll_width, A.receive_datetime, A.batch_date, A.manifest_number, A.roll_weight, B.order_source FROM rolls A, orders B WHERE A.site_id=$siteID AND A.status<>99 AND A.order_id=B.id AND B.vendor_id=$vendorid $source $size $type ORDER BY A.manifest_number, A.common_name, A.roll_width";
    $dbRolls=dbselectmulti($sql);
    if ($dbRolls['numrows']>0)
    {
       $output=$tablestart;
       $output.=$rowstart.$headstart.'Source'.$headend;
       $output.=$headstart.'Manifest'.$headend;
       $output.=$headstart.'Receive Date'.$headend;
       $output.=$headstart.'Type'.$headend;
       $output.=$headstart.'Size'.$headend;
       $output.=$headstart.'Starting Count'.$headend;
       $output.=$headstart.'Starting Weight'.$headend;
       $output.=$headstart.'Received Count'.$headend;
       $output.=$headstart.'Received Weight'.$headend;
       $output.=$headstart.'Consumed Count'.$headend;
       $output.=$headstart.'Consumed Weight'.$headend;
       $output.=$headstart.'Ending Count'.$headend;
       $output.=$headstart.'Ending Weight'.$headend.$rowend;
       
       $ctype='';
       $cwidth='';
       $cmanifest='';
       $startcount=0;
       $startweight=0;
       $receivecount=0;
       $receiveweight=0;
       $consumecount=0;
       $consumeweight=0;
       $endingcount=0;
       $endingweight=0;
       $osource='';
       $ctype=$dbRolls['data'][0]['common_name'];
       $cmanifest=$dbRolls['data'][0]['manifest_number'];
       $cwidth=$dbRolls['data'][0]['roll_width'];
       foreach($dbRolls['data'] as $roll)
       {
           if($ctype!=$roll['common_name'] || $cwidth!=$roll['roll_width'] || $cmanifest!=$roll['manifest_number'])
           {
                //if we're here, that means that we have changed either manifest, type or size
                //now, we also only want to display if startcount, receivecount or consumecount > 0
                if ($startcount>0 || $receivecount>0 || $consumecount>0)
                {
                    $endingcount=$startcount+$receivecount-$consumecount;
                    $endingweight=$startweight+$receiveweight-$consumeweight;
                    $output.=$rowstart.$cellstart.$osource.$cellend;
                    $output.=$cellstart.$cmanifest.$cellend;
                    $output.=$cellstart.$receivedate.$cellend;
                    $output.=$cellstart.$ctype.$cellend;
                    $output.=$cellstart.$cwidth.$cellend;
                    $output.=$cellstart.$startcount.$cellend;
                    $output.=$cellstart.sprintf("%.3f",$startweight/1000).' MT'.$cellend;
                    $output.=$cellstart.$receivecount.$cellend;
                    $output.=$cellstart.sprintf("%.3f",$receiveweight/1000).' MT'.$cellend;
                    $output.=$cellstart.$consumecount.$cellend;
                    $output.=$cellstart.sprintf("%.3f",$consumeweight/1000).' MT'.$cellend;
                    $output.=$cellstart.$endingcount.$cellend;
                    $output.=$cellstart.sprintf("%.3f",$endingweight/1000).' MT'.$cellend.$rowend;
                    $ctype=$roll['common_name'];
                    $cwidth=$roll['roll_width'];
                    $cmanifest=$roll['manifest_number'];
                    $vtotalstartcount+=$startcount;
                    $vtotalstartweight+=$startweight;
                    $vtotalreceivecount+=$receivecount;
                    $vtotalreceiveweight+=$receiveweight;
                    $vtotalconsumecount+=$consumecount;
                    $vtotalconsumeweight+=$consumeweight;
                    $vtotalendingcount+=$endingcount;
                    $vtotalendingweight+=$endingweight;
                    
                    $startcount=0;
                    $startweight=0;
                    $receivecount=0;
                    $receiveweight=0;
                    $consumecount=0;
                    $consumeweight=0;
                    $endingcount=0;
                    $endingweight=0;
                }
            } 
            $receivedate=$roll['receive_datetime'];
            $receivestamp=strtotime($receivedate);
            if ($roll['batch_date']!='')
            {
                $batchstamp=strtotime($roll['batch_date']); 
            }else{
                $batchstamp=0; 
            }
            $osource=$roll['order_source'];
            if ($roll['batch_date']=='' && $receivestamp<=$startstamp)
            {
                //this is a roll that has not been consumed and was received before the starting date
                //so this goes into the "starting count"
                $startcount++;
                $startweight+=$roll['roll_weight'];
            } else if($batchstamp>=$startstamp && $batchstamp<=$endstamp)
            {
                //this is a roll that was consumed during the selected date range
                $consumecount++;
                $consumeweight+=$roll['roll_weight'];
            } else if ($batchstamp>=$startstamp && $batchstamp<=$endstamp && $receivestamp<=$startstamp)
            {
                //this is a roll that was consumed during the selected date range and was received before
                //the starting date. We need to count these as well, since at the beginning of the range
                //the roll was still unconsumed
                $startcount++;
                $startweight+=$roll['roll_weight'];
            } else if ($receivestamp>=$startstamp && $receivestamp<=$endstamp)
            {
                //this is a roll received during this period
                $receivecount++;
                $receiveweight+=$roll['roll_weight'];
            } else {
                /*
                if ($roll['manifest_number']=='OC128041')
                {
                $output.="<tr><td colspan=10>Odd roll $roll[manifest_number] tag $roll[roll_tag] - batch date $roll[batch_date], 
                receive $receivestamp batch stamp $batchstamp endstamp $endstamp startstamp $startstamp</td></tr>\n";
                }
                */
            }           
       }
       if ($startcount>0 || $receivecount>0 || $consumecount>0)
                {
                    $endingcount=$startcount+$receivecount-$consumecount;
                    $endingweight=$startweight+$receiveweight-$consumeweight;
                    $output.=$rowstart.$cellstart.$osource.$cellend;
                    $output.=$cellstart.$cmanifest.$cellend;
                    $output.=$cellstart.$receivedate.$cellend;
                    $output.=$cellstart.$ctype.$cellend;
                    $output.=$cellstart.$cwidth.$cellend;
                    $output.=$cellstart.$startcount.$cellend;
                    $output.=$cellstart.sprintf("%.3f",$startweight/1000).' MT'.$cellend;
                    $output.=$cellstart.$receivecount.$cellend;
                    $output.=$cellstart.sprintf("%.3f",$receiveweight/1000).' MT'.$cellend;
                    $output.=$cellstart.$consumecount.$cellend;
                    $output.=$cellstart.sprintf("%.3f",$consumeweight/1000).' MT'.$cellend;
                    $output.=$cellstart.$endingcount.$cellend;
                    $output.=$cellstart.sprintf("%.3f",$endingweight/1000).' MT'.$cellend.$rowend;
                    $ctype=$roll['common_name'];
                    $cwidth=$roll['roll_width'];
                    $cmanifest=$roll['manifest_number'];
                    $vtotalstartcount+=$startcount;
                    $vtotalstartweight+=$startweight;
                    $vtotalreceivecount+=$receivecount;
                    $vtotalreceiveweight+=$receiveweight;
                    $vtotalconsumecount+=$consumecount;
                    $vtotalconsumeweight+=$consumeweight;
                    $vtotalendingcount+=$endingcount;
                    $vtotalendingweight+=$endingweight;
                    
                    $startcount=0;
                    $startweight=0;
                    $receivecount=0;
                    $receiveweight=0;
                    $consumecount=0;
                    $consumeweight=0;
                    $endingcount=0;
                    $endingweight=0;
                }
       $output.=$tableend;
    }
    $totalendingcount=$vtotalstartcount+$vtotaleceivecount-$vtotalconsumecount;
    $totalendingweight=$vtotalstartweight+$rvtotaleceiveweight-$vtotalconsumeweight;
    $output.=$rowstart.$cellstart5.$cellend;
    $output.=$cellstart.$vtotalstartcount.$cellend;
    $output.=$cellstart.sprintf("%.3f",$vtotalstartweight/1000).' MT'.$cellend;
    $output.=$cellstart.$vtotalreceivecount.$cellend;
    $output.=$cellstart.sprintf("%.3f",$vtotalreceiveweight/1000).' MT'.$cellend;
    $output.=$cellstart.$vtotalconsumecount.$cellend;
    $output.=$cellstart.sprintf("%.3f",$vtotalconsumeweight/1000).' MT'.$cellend;
    $output.=$cellstart.$vtotalendingcount.$cellend;
    $output.=$cellstart.sprintf("%.3f",$vtotalendingweight/1000).' MT'.$cellend.$rowend;
    return array("output"=>$output,"vtscount"=>$vtotalstartcount,"vtsweight"=>$vtotalstartweight,
    "vtrcount"=>$vtotalreceivecount,"vtrweight"=>$vtotalreceiveweight,
    "vtccount"=>$vtotalconsumecount,"vtcweight"=>$vtotalconsumeweight,
    "vtxcount"=>$totalendingcount,"vtxweight"=>$totalendingweight);
                

}

dbclose();
?>

