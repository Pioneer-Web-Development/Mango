<?php
//<!--VERSION: .9 **||**-->

if ($_POST['submit']=='To Excel')
{
    global $siteID;
    include("includes/functions_db.php");
    generate_excel();
} else {
    include("includes/mainmenu.php") ;
    $scriptpath='http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'] ;
    if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}
    print "<body> <div id='wrapper'>";
     //build vendor list
    $sql="SELECT * FROM vendors WHERE site_id=$siteID AND status=1 ORDER BY vendor_name";
    $dbVendors=dbselectmulti($sql);
    $vendors=array();
    $vendors[0]="Please choose a vendor";
    if ($dbVendors['numrows']>0)
    {
        foreach($dbVendors['data'] as $vendor)
        {
            $vendors[$vendor['id']]=$vendor['vendor_name'];
        }

    }
    //paper types
    $sql="SELECT * FROM paper_types ORDER BY common_name";
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
    $sql="SELECT * FROM paper_sizes ORDER BY width ASC";
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
    if ($_POST['submit']=='Generate Report')
        {
            $enddate=$_POST['enddate'];
            $startdate=$_POST['startdate'];
            if ($_POST['vendor']!=0){
                $vendor=" AND A.vendor_id=$_POST[vendor]";
            } else {
                $vendor="";
            }
             if ($_POST['source']!=0){
                $source=" AND A.order_source=$_POST[source]";
            } else {
                $source="";
            }
            if ($_POST['status']==0)
            { 
                $status="";
            } else {
                $status=" AND A.status=$_POST[status] ";
            }
        } else {
            $enddate=date("Y-m-d");
            $startdate=date("Y-m-d",strtotime('-3 months'));
            $vendor="";
            $status="";
        }

    print "<form action='$_SERVER[PHP_SELF]' method=post>\n";
        print "<div id='search' style='padding-left:20px;'>Vendor: \n";
            print input_select('vendor',$vendors[0],$vendors)."<br>\n";
            print "Order Source: ";
            print input_select('source',$ordersources[0],$ordersources)."<br>\n";
            print "Paper type: ";
            print input_select('ptype',$papertypes[0],$papertypes)."<br>\n";
            print "Paper size: ";
            print input_select('psize',$papersizes[0],$papersizes)."<br>\n";
            print "<div style='float:left;'>Start date: <div><script>DateInput('startdate', true, 'YYYY-MM-DD','$startdate')</script></div>\n</div>\n";
            print "<div style='float:left;'>End date: <div><script>DateInput('enddate', true, 'YYYY-MM-DD','$enddate')</script></div>\n</div>\n";
            print "<div style='float:left;margin-left:20px;'><input type='submit' name='submit' value='Generate Report' /></div>\n";
            print "<div style='float:left;margin-left:20px;'><input type='submit' name='submit' value='To Excel' /></div>\n";
            print "<div style='clear:both;'></div>\n";
            
        print "</div>\n";
    print "</form>\n";
    $startdate.=" 00:00:01";
    $enddate.=" 23:59:59";

    $totalreceivecount=0;
    $totalreceiveweight=0;
    $totalconsumecount=0;
    $totalconsumeweight=0;
    $totalremaincount=0;
    $totalremainweight=0;
        

    if ($_POST['vendor']==0)
    {
        foreach($vendors as $vid=>$vname)
        {
            if ($vid!=0)
            {
                $vrolls=vendor_rolls($vid,$vname,$startdate,$enddate,$_POST['source']);
                $totalreceivecount+=$vrolls['vtrcount'];
                $totalreceiveweight+=$vrolls['vtrweight'];
                $totalconsumecount+=$vrolls['vtccount'];
                $totalconsumeweight+=$vrolls['vtcweight'];
                $totalremaincount+=$vrolls['vtxcount'];
                $totalremainweight+=$vrolls['vtxweight'];
            }
        }
    } else {
        $vrolls=vendor_rolls($_POST['vendor'],$vendors[$_POST['vendor']],$startdate,$enddate,$_POST['source']);
        $totalreceivecount+=$vrolls['vtrcount'];
        $totalreceiveweight+=$vrolls['vtrweight'];
        $totalconsumecount+=$vrolls['vtccount'];
        $totalconsumeweight+=$vrolls['vtcweight'];
        $totalremaincount+=$vrolls['vtxcount'];
        $totalremainweight+=$vrolls['vtxweight'];
    }
    print "<table>\n";
           print "<tr><th colspan=4>&nbsp;</th><th colspan=2>Received</th><th colspan=2>Consumed</th><th colspan=2>Remaining</th></tr>\n";
           print "<tr><th>Source</th><th>Manifest</th><th>Type</th><th>Size</th><th>Rolls</th><th>Weight</th><th>Rolls</th><th>Weight</th><th>Rolls</th><th>Weight</th></tr>\n";
           
    print "<tr><td colspan=4>Totals</td><td>$totalreceivecount</td><td>$totalreceiveweight</td>
    <td>$totalconsumecount</td><td>$totalconsumeweight</td>
    <td>$totalremaincount</td><td>$totalremainweight</td></tr>\n";
    print "</table>\n";



    print "</div>
    </body>
    </html>";

}

function vendor_rolls($vendorid,$vendorname,$startdate,$enddate,$source='',$excel=false)
{
    global $siteID;
    $vtotalreceivecount=0;
    $vtotalreceiveweight=0;
    $vtotalconsumecount=0;
    $vtotalconsumeweight=0;
    $vtotalremaincount=0;
    $vtotalremainweight=0;
    $exceldata="";
    if ($source!=''){$source=" AND order_source='$source'";}else{$source="";}
    //ok, find any all orders for this vendor id
    $sql="SELECT * FROM orders WHERE site_id=$siteID AND vendor_id=$vendorid $source ORDER BY order_datetime DESC";
    $dbOrders=dbselectmulti($sql);
    if ($dbOrders['numrows']>0)
    {
       
       if (!$excel){print "<hr><h1>$vendorname</h1>\n";}
       $exceldata.="<Row>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vendorname</Data></Cell>";
       $exceldata.="</Row>";
       
       if (!$excel){
           print "<table>\n";
           print "<tr><th colspan=4>&nbsp;</th><th colspan=2>Received</th><th colspan=2>Consumed</th><th colspan=2>Remaining</th></tr>\n";
           print "<tr><th>Source</th><th>Manifest</th><th>Type</th><th>Size</th><th>Rolls</th><th>Weight</th><th>Rolls</th><th>Weight</th><th>Rolls</th><th>Weight</th></tr>\n";
       }
        $exceldata .= "<Row>";
        $exceldata .="<Cell><Data ss:Type='String'></Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'></Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'></Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'></Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Received</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'></Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Consumed</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'></Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Remaining</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'></Data></Cell>";
        $exceldata .= "</Row>";
        
        $exceldata .= "<Row>";
        $exceldata .="<Cell><Data ss:Type='String'>Source</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Manifest</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Paper Type</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Paper Size</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Count</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Weight</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Count</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Weight</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Count</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Weight</Data></Cell>";
        $exceldata .= "</Row>";
        
       foreach($dbOrders['data'] as $order)
       {
            //now, find the manifests received during the time period
            $sql="SELECT DISTINCT(manifest_number) FROM rolls 
            WHERE order_id=$order[id] AND receive_datetime>='$startdate' AND receive_datetime<='$enddate'
             ORDER BY receive_datetime DESC";
            $dbReceiveManifests=dbselectmulti($sql);
            if ($dbReceiveManifests['numrows']>0)
            {
                foreach($dbReceiveManifests['data'] as $rmanifest)
                {
                    $manifest=$rmanifest['manifest_number'];
                    if (!$excel){
                        print "<tr><td>$order[order_source]</td><td>$manifest</td></tr>\n";
                    }
                    $exceldata.="<Row>";
                    $exceldata.= "<Cell><Data ss:Type='String'>$order[order_source]</Data></Cell>";
                    $exceldata.= "<Cell><Data ss:Type='String'>$manifest</Data></Cell>";
                    $exceldata.="</Row>";
                    //now look up the rolls
                    $sql="SELECT DISTINCT(common_name) as paper, roll_width as width, count(id) as rollcount, sum(roll_weight) as totalweight 
                    FROM rolls WHERE order_id=$order[id] AND manifest_number='$manifest' GROUP BY common_name, roll_width ORDER BY width ASC";
                    $dbRollTotal=dbselectmulti($sql);
                    if ($dbRollTotal['numrows']>0)
                    {
                        foreach ($dbRollTotal['data'] as $rolltotal)
                        {
                            $receivecount=$rolltotal['rollcount'];
                            $receiveweight=$rolltotal['totalweight'];
                            $name=$rolltotal['paper'];
                            $width=$rolltotal['width'];
                            
                            //now, get the number of these rolls where batch_date is between our target dates
                            $sql="SELECT count(id) as rollcount, sum(roll_weight) as totalweight FROM rolls WHERE order_id=$order[id] 
                            AND manifest_number='$manifest' AND batch_date>='$startdate' AND batch_date<='$enddate' AND common_name='$name'
                            AND roll_width='$width'";
                            $dbRollConsume=dbselectsingle($sql);
                            $consumecount=0;
                            $consumeweight=0;
                            if ($dbRollConsume['numrows']>0)
                            {
                                $consumecount=$dbRollConsume['data']['rollcount'];
                                $consumeweight=$dbRollConsume['data']['totalweight'];
                            }
                            $remaincount=$receivecount-$consumecount;
                            $remainweight=$receiveweight-$consumeweight;
                            
                            $vtotalreceivecount=$vtotalreceivecount+$receivecount;
                            $vtotalreceiveweight=$vtotalreceiveweight+$receiveweight;
                            $vtotalconsumecount=$vtotalconsumecount+$consumecount;
                            $vtotalconsumeweight=$vtotalconsumeweight+$consumeweight;
                            $vtotalremaincount=$vtotalremaincount+$remaincount;
                            $vtotalremainweight=$vtotalremainweight+$remainweight;
                            
                            if (!$excel){
                                print "<tr><td></td><td></td><td>$name</td><td>$width</td><td>$receivecount</td><td>$receiveweight</td>
                                <td>$consumecount</td><td>$consumeweight</td><td>$remaincount</td><td>$remainweight</td></tr>\n";
                            }
                            $exceldata.="<Row>";
                            $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$name</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$width</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$receivecount</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$receiveweight</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$consumecount</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$consumeweight</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$remaincount</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$remainweight</Data></Cell>";
                            $exceldata .= "</Row>";
                        }
                    }
                }
            }
            //now find manifests received earlier or later, but consumed during this time
            $sql="SELECT DISTINCT(manifest_number) FROM rolls 
            WHERE order_id=$order[id] AND receive_datetime<='$startdate' AND receive_datetime>='$enddate'
            AND batch_date>='$startdate' AND batch_date<='$enddate'
             ORDER BY receive_datetime DESC";
            $dbConsumedManifests=dbselectmulti($sql);
            if ($dbConsumedManifests['numrows']>0)
            {
                if (!$excel){
                    print "<tr><td colspan=8>These are rolls consumed during this period, but received earlier</td></tr>\n";
                }
                $exceldata.="<Row>";
               $exceldata.= "<Cell><Data ss:Type='String'>These are rolls consumed during this period, but received earlier</Data></Cell>";
               $exceldata.="</Row>";
               
                foreach($dbConsumedManifests['data'] as $cmanifest)
                {
                    $manifest=$cmanifest['manifest_number'];
                    print "<tr><td>$order[order_source]</td><td>$manifest</td></tr>\n";
                    $exceldata.="<Row>";
                    $exceldata.= "<Cell><Data ss:Type='String'>$order[order_source]</Data></Cell>";
                    $exceldata.= "<Cell><Data ss:Type='String'>$manifest</Data></Cell>";
                    $exceldata.="</Row>";
                    
                    //now look up the rolls
                    $sql="SELECT DISTINCT(common_name) as paper, roll_width as width, count(id) as rollcount, sum(roll_weight) as totalweight 
                    FROM rolls WHERE order_id=$order[id] AND manifest_number='$manifest' GROUP BY common_name, roll_width ORDER BY width ASC";
                    $dbRollTotal=dbselectmulti($sql);
                    if ($dbRollTotal['numrows']>0)
                    {
                        foreach ($dbRollTotal['data'] as $rolltotal)
                        {
                            $receivecount=$rolltotal['rollcount'];
                            $receiveweight=$rolltotal['totalweight'];
                            $name=$rolltotal['paper'];
                            $width=$rolltotal['width'];
                            
                            //now, get the number of these rolls where batch_date is between our target dates
                            $sql="SELECT count(id) as rollcount, sum(roll_weight) as totalweight FROM rolls WHERE order_id=$order[id] 
                            AND manifest_number='$manifest' AND batch_date>='$startdate' AND batch_date<='$enddate' AND common_name='$name'
                            AND roll_width='$width'";
                            $dbRollConsume=dbselectsingle($sql);
                            $consumecount=0;
                            $consumeweight=0;
                            if ($dbRollConsume['numrows']>0)
                            {
                                $consumecount=$dbRollConsume['data']['rollcount'];
                                $consumeweight=$dbRollConsume['data']['totalweight'];
                            }
                            $remaincount=$receivecount-$consumecount;
                            $remainweight=$receiveweight-$consumeweight;
                            
                            $vtotalreceivecount=$vtotalreceivecount+$receivecount;
                            $vtotalreceiveweight=$vtotalreceiveweight+$receiveweight;
                            $vtotalconsumecount=$vtotalconsumecount+$consumecount;
                            $vtotalconsumeweight=$vtotalconsumeweight+$consumeweight;
                            $vtotalremaincount=$vtotalremaincount+$remaincount;
                            $vtotalremainweight=$vtotalremainweight+$remainweight;
                            
                            if (!$excel){
                                print "<tr><td></td><td></td><td>$name</td><td>$width</td><td>$receivecount</td><td>$receiveweight</td>
                            <td>$consumecount</td><td>$consumeweight</td><td>$remaincount</td><td>$remainweight</td></tr>\n";
                            }
                            $exceldata.="<Row>";
                            $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$name</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$width</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$receivecount</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$receiveweight</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$consumecount</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$consumeweight</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$remaincount</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$remainweight</Data></Cell>";
                            $exceldata.= "</Row>";
                        }
                    }
                }
            }
            
       }
       if (!$excel){
           print "<tr style='font-weight:bold;font-size:12px'><td colspan=4>Vendor subtotal</td><td>$vtotalreceivecount</td><td>$vtotalreceiveweight</td>
                                <td>$vtotalconsumecount</td><td>$vtotalconsumeweight</td><td>$vtotalremaincount</td><td>$vtotalremainweight</td></tr>\n";
           print "</table>\n";
       }
       $exceldata.="<Row>";
       $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>Vendor Subtotal</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vtotalreceivecount</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vtotalreceiveweight</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vtotalconsumecount</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vtotalconsumeweight</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vtotalremaincount</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vtotalremainweight</Data></Cell>";
       $exceldata.= "</Row>";
    }
    return array("vtrcount"=>$vtotalreceivecount,"vtrweight"=>$vtotalreceiveweight,
                "vtccount"=>$vtotalconsumecount,"vtcweight"=>$vtotalconsumeweight,
                "vtxcount"=>$vtotalremaincount,"vtxweight"=>$vtotalremainweight,
                "exceldata"=>$exceldata);
                

}


function generate_excel()
{
global $siteID;
        
//build vendor list
$sql="SELECT * FROM vendors WHERE site_id=$siteID AND status=1 ORDER BY vendor_name";
$dbVendors=dbselectmulti($sql);
$vendors=array();
$vendors[0]="Please choose a vendor";
if ($dbVendors['numrows']>0)
{
    foreach($dbVendors['data'] as $vendor)
    {
        $vendors[$vendor['id']]=$vendor['vendor_name'];
    }

}
//paper types
$sql="SELECT * FROM paper_types ORDER BY common_name";
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
$sql="SELECT * FROM paper_sizes ORDER BY width ASC";
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
if ($_POST['submit']=='To Excel')
{
    $enddate=$_POST['enddate'];
    $startdate=$_POST['startdate'];
    if ($_POST['vendor']!=0){
        $vendor=" AND A.vendor_id=$_POST[vendor]";
    } else {
        $vendor="";
    }
     if ($_POST['source']!=0){
        $source=" AND A.order_source=$_POST[source]";
    } else {
        $source="";
    }
    if ($_POST['status']==0)
    { 
        $status="";
    } else {
        $status=" AND A.status=$_POST[status] ";
    }
} else {
    $enddate=date("Y-m-d");
    $startdate=date("Y-m-d",strtotime('-3 months'));
    $vendor="";
    $status="";
}    
    $startdate.=" 00:00:01";
    $enddate.=" 23:59:59";

    $totalreceivecount=0;
    $totalreceiveweight=0;
    $totalconsumecount=0;
    $totalconsumeweight=0;
    $totalremaincount=0;
    $totalremainweight=0;
        

    
    
    
    $title="Newsprint_Consumption";
    $data = "<?xml version='1.0'?>
    <?mso-application progid='Excel.Sheet'?>
    <Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet' xmlns:html='http://www.w3.org/TR/REC-html40'>
    <Worksheet ss:Name='".$title."'>
    <Table>";
    //create the header section
    
    
    if ($_POST['vendor']==0)
    {
        foreach($vendors as $vid=>$vname)
        {
            if ($vid!=0)
            {
                $vrolls=vendor_rolls($vid,$vname,$startdate,$enddate,$source,true);
                $totalreceivecount+=$vrolls['vtrcount'];
                $totalreceiveweight+=$vrolls['vtrweight'];
                $totalconsumecount+=$vrolls['vtccount'];
                $totalconsumeweight+=$vrolls['vtcweight'];
                $totalremaincount+=$vrolls['vtxcount'];
                $totalremainweight+=$vrolls['vtxweight'];
                $data.=$vrolls['exceldata'];
                $data.="<Row></Row>";
            }
        }
    } else {
        $vrolls=vendor_rolls($_POST['vendor'],$vendors[$_POST['vendor']],$startdate,$enddate,$source,true);
        $totalreceivecount+=$vrolls['vtrcount'];
        $totalreceiveweight+=$vrolls['vtrweight'];
        $totalconsumecount+=$vrolls['vtccount'];
        $totalconsumeweight+=$vrolls['vtcweight'];
        $totalremaincount+=$vrolls['vtxcount'];
        $totalremainweight+=$vrolls['vtxweight'];
        $data.=$vrolls['exceldata']; 
    }
    
    //grand total
    $data .= "<Row>";
    $data .="<Cell><Data ss:Type='String'></Data></Cell>";
    $data .="<Cell><Data ss:Type='String'></Data></Cell>";
    $data .="<Cell><Data ss:Type='String'></Data></Cell>";
    $data .="<Cell><Data ss:Type='String'></Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>Received</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'></Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>Consumed</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'></Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>Remaining</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'></Data></Cell>";
    $data .= "</Row>";
    
    $data .= "<Row>";
    $data .="<Cell><Data ss:Type='String'></Data></Cell>";
    $data .="<Cell><Data ss:Type='String'></Data></Cell>";
    $data .="<Cell><Data ss:Type='String'></Data></Cell>";
    $data .="<Cell><Data ss:Type='String'></Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>Count</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>Weight</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>Count</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>Weight</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>Count</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>Weight</Data></Cell>";
    $data .= "</Row>";
    $data .= "<Row>";
    $data .="<Cell><Data ss:Type='String'>Total Newsprint for period</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>$startdate</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>to</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>$enddate</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>$totalreceivecount</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>$totalreceiveweight</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>$totalconsumecount</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>$totalconsumeweight</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>$totalremaincount</Data></Cell>";
    $data .="<Cell><Data ss:Type='String'>$totalremainweight</Data></Cell>";
    $data .= "</Row>";
    
    
    
    //Final XML Blurb
    $data .= "</Table></Worksheet></Workbook>";


    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=$title.xls;");
    header("Content-Type: application/ms-excel");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    echo $data; 
}

dbclose();
?>

