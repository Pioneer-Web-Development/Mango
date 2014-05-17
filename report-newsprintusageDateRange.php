<?php
//<!--VERSION: .9 **||**-->

if ($_POST && $_POST['output']=='excel')
{
    global $siteID;
    include("includes/functions_db.php");
    include("includes/config.php");
} else {
    include("includes/mainmenu.php") ;
}
global $newsprintVendors;
 
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
} else 
{
    $startdate=date("Y-m-d",strtotime("-1 month"));
    $enddate=date("Y-m-d");
    $totalreceivecount=0;
    $totalreceiveweight=0;
    $totalconsumecount=0;
    $totalconsumeweight=0;
    $totalremaincount=0;
    $totalremainweight=0;
}
if ($_POST['vendor']!=0){
    $vendor=" AND vendor_id=$_POST[vendor]";
} else {
    $vendor="";
}

 if ($_POST['source']!='0'){
    $source=$_POST['source'];
} else {
    $source="";
}
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
if ($_POST['status']==0)
{ 
    $status="";
} else {
    $status=" AND status=$_POST[status] ";
}
if ($_POST['validated'])
{ 
    $validated=" AND validated=1";
} else {
    $validated="";
}
if ($_POST['manifest'])
{ 
    $manifestzero=1;
} else {
    $manifestzero=0;
}

if ($_POST['output']!='excel')
{
    print "<div class='noprint'>\n";
    $outputs=array("screen"=>"To Screen","excel"=>"To Excel");
    print "<div style='float:left;width: 500px;'>\n";
    print "<form method=post>\n";
        print "<div id='search' style='padding-left:20px;'>Vendor: \n";
            print input_select('vendor',$newsprintVendors[$_POST['vendor']],$newsprintVendors)."<br>\n";
            print "Order Source: ";
            print input_select('source',$ordersources[$_POST['source']],$ordersources)."<br>\n";
            print "Paper type: ";
            print input_select('ptype',$papertypes[$_POST['ptype']],$papertypes)."<br>\n";
            print "Paper size: ";
            print input_select('psize',$papersizes[$_POST['psize']],$papersizes)."<br>\n";
            print input_checkbox('validated',$validated)." Show only rolls that have been validated<br />";
            print input_checkbox('manifest',$manifestzero)." Eliminate manifests with no balance and no consumption during specified period.<br />";
            make_date('startdate',$startdate,'Start Date');
            make_date('enddate',$enddate,'End Date');
            print "<br />Output to: ";
            print input_select('output',$outputs['screen'],$outputs)."<br /><br />\n";
            print "<div style='float:left;margin-left:20px;'><input type='submit' name='submit' value='Generate Report' /></div>\n";
            print "<div style='clear:both;'></div>\n";
            
        print "</div>\n";
    print "</form>\n";
    print "</div>\n";
    print "<div style='float:left;width: 300px;margin-left:20px;border:2px solid black;padding:10px;'>\n";
    print "<h2>Newsprint usage during specified period</h2>\n";
    print "<p>This report select all manifests matching the selected criteria and find those manifests that meet
    the following criteria:<br />
    Have remaining balances <br />
    Were received during the specified time periods OR <br />
    Had consumption of rolls during the specified time periods.</p>\n";
    print "</div>\n";
    print "<div class='clear'></div>\n";
    print "</div>\n";
}
        
if ($_POST['submit']=='Generate Report')
{
    if($_POST['output']=='excel')
    {
        $excel=true;
    } else {
        $excel=false;
    }
    if ($_POST['vendor']==0)
    {
        foreach($newsprintVendors as $vid=>$vname)
        {
            if ($vid!=0)
            {
                $vrolls=vendor_rolls($vid,$vname,$startdate,$enddate,$source,$psize,$ptype,$validated,$manifestzero,$excel);
                $totalreceivecount+=$vrolls['vtrcount'];
                $totalreceiveweight+=$vrolls['vtrweight'];
                $totalconsumecount+=$vrolls['vtccount'];
                $totalconsumeweight+=$vrolls['vtcweight'];
                $totalremaincount+=$vrolls['vtxcount'];
                $totalremainweight+=$vrolls['vtxweight'];
                
                $totaldatereceivecount+=$vrolls['vtrdatecount'];
                $totaldatereceiveweight+=$vrolls['vtrdateweight'];
                $totaldateconsumecount+=$vrolls['vtcdatecount'];
                $totaldateconsumeweight+=$vrolls['vtcdateweight'];
                $totaldateremaincount+=$vrolls['vtxdatecount'];
                $totaldateremainweight+=$vrolls['vtxdateweight'];
                $rollbytype[]=$vrolls['rtype'];
                $exceldata.=$vrolls['exceldata']; 
            }
        }
    } else {
        $vrolls=vendor_rolls($_POST['vendor'],$newsprintVendors[$_POST['vendor']],$startdate,$enddate,$source,$psize,$ptype,$validated,$manifestzero,$excel);
        $totalreceivecount+=$vrolls['vtrcount'];
        $totalreceiveweight+=$vrolls['vtrweight'];
        $totalconsumecount+=$vrolls['vtccount'];
        $totalconsumeweight+=$vrolls['vtcweight'];
        $totalremaincount+=$vrolls['vtxcount'];
        $totalremainweight+=$vrolls['vtxweight'];
        
        $totaldatereceivecount+=$vrolls['vtrdatecount'];
        $totaldatereceiveweight+=$vrolls['vtrdateweight'];
        $totaldateconsumecount+=$vrolls['vtcdatecount'];
        $totaldateconsumeweight+=$vrolls['vtcdateweight'];
        $totaldateremaincount+=$vrolls['vtxdatecount'];
        $totaldateremainweight+=$vrolls['vtxdateweight'];
        $rollbytype[]=$vrolls['rtype'];
        $exceldata.=$vrolls['exceldata']; 
    }
    if($_POST['output']=='screen')
    {
        print "<table class='report'>\n";
        print "<tr><th colspan=5>&nbsp;</th><th colspan=2>Received</th><th colspan=2>Consumed</th><th colspan=2>Remaining</th></tr>\n";
        print "<tr><th colspan=5>&nbsp;</th><th>Rolls</th><th>Weight</th><th>Rolls</th><th>Weight</th><th>Rolls</th><th>Weight</th></tr>\n";
               
        print "<tr></tr>\n";
        print "<tr><td colspan=5>Totals</td><td>$totalreceivecount</td><td>".sprintf("%.3f",$totalreceiveweight/1000)."</td>
        <td>$totalconsumecount</td><td>".sprintf("%.3f",$totalconsumeweight/1000)."</td>
        <td>$totalremaincount</td><td>".sprintf("%.3f",$totalremainweight/1000)."</td></tr>\n";
        
        print "<tr><td colspan=5>Totals for date period</td><td>$totaldatereceivecount</td><td>".sprintf("%.3f",$totaldatereceiveweight/1000)."</td>
        <td>$totaldateconsumecount</td><td>".sprintf("%.3f",$totaldateconsumeweight/1000)."</td>
        <td>$totaldateremaincount</td><td>".sprintf("%.3f",$totaldateremainweight/1000)."</td></tr>\n";
        print "</table>\n";

        if (count($rollbytype)>0)
        {
            print "<ul>";
            $total=0;
            asort($rollbytype);
            foreach($rollbytype as $rtype)
            {
                foreach($rtype as $type=>$remaining)
                {
                 print "<li>$type - $remaining</li>\n";
                 $total+=$remaining;   
                }
                
            }
            print "</ul>\n"; 
            print "<br />Total of $total rolls in inventory<br />";
        }
    } else {
        $title=str_replace(":","","Newsprint_Consumption - ".date("Y-m-d H:i"));
        $title=str_replace(" ","_",$title);
        $data = "<?xml version='1.0'?>
        <?mso-application progid='Excel.Sheet'?>
        <Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet' xmlns:html='http://www.w3.org/TR/REC-html40'>
        <Worksheet ss:Name='Consumption Report'>
        <Table>";
        $data.=$exceldata;
        //grand total
        $data .= "<Row>";
        $data .="<Cell><Data ss:Type='String'></Data></Cell>";
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
        $data .="<Cell><Data ss:Type='String'></Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>Count</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>Weight</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>Count</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>Weight</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>Count</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>Weight</Data></Cell>";
        $data .= "</Row>";
        $data .= "<Row>";
        $data .= "</Row>";
        $data .= "<Row>";
        $data .="<Cell><Data ss:Type='String'>Total Newsprint </Data></Cell>";
        $data .="<Cell><Data ss:Type='String'></Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>$startdate</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>to</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>$enddate</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>$totalreceivecount</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>".sprintf("%.3f",$totalreceiveweight/1000)."</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>$totalconsumecount</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>".sprintf("%.3f",$totalconsumeweight/1000)."</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>$totalremaincount</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>".sprintf("%.3f",$totalremainweight/1000)."</Data></Cell>";
        $data .= "</Row>";
        $data .= "<Row>";
        $data .="<Cell><Data ss:Type='String'>Total Newsprint for selected dates</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'></Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>$startdate</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>to</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>$enddate</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>$totaldatereceivecount</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>".sprintf("%.3f",$totaldatereceiveweight/1000)."</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>$totaldateconsumecount</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>".sprintf("%.3f",$totaldateconsumeweight/1000)."</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>$totaldateremaincount</Data></Cell>";
        $data .="<Cell><Data ss:Type='String'>".sprintf("%.3f",$totaldateremainweight/1000)."</Data></Cell>";
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
}
if($_POST['output']!='excel')
{
    print "</div>
    </body>
    </html>";
}
dbclose();


function vendor_rolls($vendorid,$vendorname,$startdate,$enddate,$source='',$size='',$type='',$validated='',$manifestzero=false,$excel=false)
{
    global $siteID;
    $rtype=array();
    $vtotalreceivecount=0;
    $vtotalreceiveweight=0;
    $vtotalconsumecount=0;
    $vtotalconsumeweight=0;
    $vtotalremaincount=0;
    $vtotalremainweight=0;
    $vtotalremainweight=0;
    $vtotaldatereceivecount=0;
    $vtotaldatereceiveweight=0;
    $vtotaldateconsumecount=0;
    $vtotaldateconsumeweight=0;
    $vtotaldateremaincount=0;
    $vtotaldateremainweight=0;
    $vtotaldateremainweight=0;
    $exceldata="";
    $bstartdate=explode(" ",$startdate);
    $bstartdate=$bstartdate[0];
    $benddate=explode(" ",$enddate);
    $benddate=$benddate[0];
    if ($source!=''){$source=" AND order_source='$source'";}else{$source="";}
    if ($size!=''){$size=" AND roll_width='$size'";}else{$size="";}
    if ($type!=''){$type=" AND common_name='$type'";}else{$type="";}
    //ok, find any all orders for this vendor id
    $sql="SELECT * FROM orders WHERE vendor_id=$vendorid $source ORDER BY order_datetime DESC";
    //print "Order sql is $sql<br>";
    $dbOrders=dbselectmulti($sql);
    if ($dbOrders['numrows']>0)
    {
       
       $exceldata.="<Row>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vendorname</Data></Cell>";
       $exceldata.="</Row>";
       
       if (!$excel){
           print "<table class='report'>\n";
           print "<tr><th><h1>$vendorname</h1></th></tr>\n";
           print "<tr><th colspan=5>&nbsp;</th><th colspan=2>Received</th><th colspan=2>Consumed</th><th colspan=2>Remaining</th></tr>\n";
           print "<tr><th>Source</th><th>Manifest</th><th>Receive Date</th><th>Type</th><th>Size</th><th>Rolls</th><th>Weight</th><th>Rolls</th><th>Weight</th><th>Rolls</th><th>Weight</th></tr>\n";
       }
        $exceldata .= "<Row>";
        $exceldata .="<Cell><Data ss:Type='String'></Data></Cell>";
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
        $exceldata .="<Cell><Data ss:Type='String'>Receive Date</Data></Cell>";
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
            if ($manifestzero)
            {
                $rollstats="1";
            } else {
                $rollstats="1,9";
            }
            /*
            $sql="SELECT DISTINCT(manifest_number), receive_datetime FROM rolls 
            WHERE order_id=$order[id] $size $type AND ((receive_datetime>='$startdate' 
            AND receive_datetime<='$enddate') OR (batch_date>='$startdate' AND batch_date<='$enddate')) 
             ORDER BY receive_datetime DESC";
             */
            $sql="SELECT DISTINCT(manifest_number), receive_datetime FROM rolls 
            WHERE order_id=$order[id] $size $type
            ORDER BY receive_datetime DESC";
            $dbReceiveManifests=dbselectmulti($sql);
            //print "<tr><td>Manifest sql is $sql</td></tr>\n";
            if ($dbReceiveManifests['numrows']>0)
            {
                foreach($dbReceiveManifests['data'] as $rmanifest)
                {
                    $manifest=$rmanifest['manifest_number'];
                    //print "<tr><td>unique manifest number here --$manifest-- for order $order[id]</td></tr>\n";
                    $rdate=date("m/d/Y",strtotime($rmanifest['receive_datetime']));
                    //now look up the rolls
                    $sql="SELECT DISTINCT(common_name) as paper FROM rolls WHERE 
                    order_id=$order[id] AND manifest_number='$manifest' $size $type ORDER BY common_name";
                    $dbNames=dbselectmulti($sql);
                    //print "<tr><td>Names sql: $sql - $dbNames[error]</td></tr>\n";
                    if ($dbNames['numrows']>0)
                    {
                        foreach($dbNames['data'] as $rollname)
                        {
                            $pname=$rollname['paper'];
                            $sql="SELECT DISTINCT(roll_width) as width FROM rolls WHERE 
                            order_id=$order[id] AND manifest_number='$manifest' AND common_name='$pname' $size ORDER BY roll_width";
                            $dbSizes=dbselectmulti($sql);
                            //print "<tr><td>Sizes sql: $sql - $dbSizes[error]</td></tr>\n";
                            if ($dbSizes['numrows']>0)
                            {
                                foreach($dbSizes['data'] as $rollsize)
                                {
                                    $psize=$rollsize['width'];
                                    //first pass - get all rolls matching that type/size that are not status 99
                                    //this will be the number of received rolls
                                    $sql="SELECT count(id) as rollcount, sum(roll_weight) as totalweight 
                                        FROM rolls WHERE order_id=$order[id] AND manifest_number='$manifest' AND common_name='$pname'
                                        AND roll_width='$psize' AND (receive_datetime>='$startdate' AND receive_datetime<='$enddate')
                                        AND status<>99 $validated";
                                    $dbRollReceived=dbselectsingle($sql);
                                    //print "<tr><td>Received sql: $sql - $dbRollReceived[error]</td></tr>\n";
                                    $rollsreceived=$dbRollReceived['data']['rollcount'];
                                    $weightreceived=$dbRollReceived['data']['totalweight'];
                                    
                                    //now get how many have been consumed in the requested time period
                                    $sql="SELECT count(id) as rollcount, sum(roll_weight) as totalweight 
                                        FROM rolls WHERE order_id=$order[id] AND manifest_number='$manifest' AND common_name='$pname'
                                        AND roll_width='$psize' AND status=9 AND batch_date>='$startdate' 
                                        AND batch_date<='$enddate' $validated $type $size";
                                    $dbRollConsumed=dbselectsingle($sql);
                                    //print "<tr><td>Consumed sql: $sql - $dbRollConsumed[error]</td></tr>\n";
                                    $rollsconsumed=$dbRollConsumed['data']['rollcount'];
                                    $weightconsumed=$dbRollConsumed['data']['totalweight'];
                                    
                                    //now get how many have are still unconsumed in the requested time period
                                    $sql="SELECT count(id) as rollcount, sum(roll_weight) as totalweight 
                                        FROM rolls WHERE order_id=$order[id] AND manifest_number='$manifest' AND common_name='$pname'
                                        AND receive_datetime<='$enddate' 
                                        AND roll_width='$psize' $validated $type $size
                                        AND ((status=1 AND batch_date IS Null) OR (batch_date>'$enddate'))";
                                    $dbRollRemaining=dbselectsingle($sql);
                                    //print "<tr><td>Remaining sql: $sql - $dbRollRemaining[error]</td></tr>\n";
                                    $rollsremaining=$dbRollRemaining['data']['rollcount'];
                                    $weightremaining=$dbRollRemaining['data']['totalweight'];
                                    
                                    if($rollsreceived==0 && $rollsconsumed==0 && $rollsremaining==0)
                                    {
                                        //skip this one
                                        //print "<tr><td>Skipping</td></tr>\n";
                                    } else {
                                        if (!$excel){
                                            print "<tr><td>$order[order_source]</td><td>$manifest</td><td>$rdate</td>\n";
                                        }
                                        $exceldata.="<Row>";
                                        $exceldata.= "<Cell><Data ss:Type='String'>$order[order_source]</Data></Cell>";
                                        $exceldata.= "<Cell><Data ss:Type='String'>$manifest</Data></Cell>";
                                        $exceldata.= "<Cell><Data ss:Type='String'>$rdate</Data></Cell>";
                                        
                                        $vtotalreceivecount=$vtotalreceivecount+$rollsreceived;
                                        $vtotalreceiveweight=$vtotalreceiveweight+$weightreceived;
                                        $vtotalconsumecount=$vtotalconsumecount+$rollsconsumed;
                                        $vtotalconsumeweight=$vtotalconsumeweight+$weightconsumed;
                                        $vtotalremaincount=$vtotalremaincount+$rollsremaining;
                                        $vtotalremainweight=$vtotalremainweight+$weightremaining;
                                        
                                        $vtotaldatereceivecount=$vtotaldatereceivecount+$rollsreceived;
                                        $vtotaldatereceiveweight=$vtotaldatereceiveweight+$weightreceived;
                                        $vtotaldateconsumecount=$vtotaldateconsumecount+$rollsconsumed;
                                        $vtotaldateconsumeweight=$vtotaldateconsumeweight+$weightconsumed;
                                        $vtotaldateremaincount=$vtotaldateremaincount+$rollsremaining;
                                        $vtotaldateremainweight=$vtotaldateremainweight+$weightremaining;
                                        
                                        if($rollsremaining>0)
                                        {
                                            $rtype[$pname.' - '.$psize.' '.$order['order_source']]+=$rollsremaining;
                                        }
                                        
                                        if (!$excel){
                                            print "<td>$pname</td><td>$psize</td><td>$rollsreceived</td><td>".sprintf("%.3f",$weightreceived/1000)."</td>
                                            <td>$rollsconsumed</td><td>".sprintf("%.3f",$weightconsumed/1000)."</td>
                                            <td>$rollsremaining</td><td>".sprintf("%.3f",$weightremaining/1000)."</td></tr>\n";
                                        }
                                        
                                        $exceldata.= "<Cell><Data ss:Type='String'>$pname</Data></Cell>";
                                        $exceldata.= "<Cell><Data ss:Type='String'>$psize</Data></Cell>";
                                        $exceldata.= "<Cell><Data ss:Type='String'>$rollsreceived</Data></Cell>";
                                        $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$weightreceived/1000)."</Data></Cell>";
                                        $exceldata.= "<Cell><Data ss:Type='String'>$rollsconsumed</Data></Cell>";
                                        $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$weightconsumed/1000)."</Data></Cell>";
                                        $exceldata.= "<Cell><Data ss:Type='String'>$rollsremaining</Data></Cell>";
                                        $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$weightremaining/1000)."</Data></Cell>";
                                        $exceldata .= "</Row>";
                                    }
                                    
                                    
                                }
                            }
                        }
                    }
                    
                   
                }
                 
            }
       }
       
       
       if (!$excel){
           print "<tr style='font-weight:bold;font-size:12px'><td colspan=5>Vendor subtotal</td><td>$vtotalreceivecount</td>
           <td>".sprintf("%.3f",$vtotalreceiveweight/1000)."</td><td>$vtotalconsumecount</td>
           <td>".sprintf("%.3f",$vtotalconsumeweight/1000)."</td>
           <td>$vtotalremaincount</td><td>".sprintf("%.3f",$vtotalremainweight/1000)."</td></tr>\n";
       }
       $exceldata.="<Row>";
       $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>Vendor Subtotal</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vtotalreceivecount</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$vtotalreceiveweight/1000)."</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vtotalconsumecount</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$vtotalconsumeweight/1000)."</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vtotalremaincount</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$vtotalremainweight/1000)."</Data></Cell>";
       $exceldata.= "</Row>";
       
       if (!$excel){
           print "<tr style='font-weight:bold;font-size:12px'><td colspan=5>Vendor subtotal for selected dates</td>
           <td>$vtotaldatereceivecount</td>
           <td>".sprintf("%.3f",$vtotaldatereceiveweight/1000)."</td><td>$vtotaldateconsumecount</td>
           <td>".sprintf("%.3f",$vtotaldateconsumeweight/1000)."</td>
           <td>$vtotaldateremaincount</td><td>".sprintf("%.3f",$vtotaldateremainweight/1000)."</td></tr>\n";
           print "</table>\n";
       }
       $exceldata.="<Row>";
       $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>Vendor Subtotal for selected dates</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'></Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vtotaldatereceivecount</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$vtotaldatereceiveweight/1000)."</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vtotaldateconsumecount</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$vtotaldateconsumeweight/1000)."</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vtotaldateremaincount</Data></Cell>";
       $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$vtotaldateremainweight/1000)."</Data></Cell>";
       $exceldata.= "</Row>";
    }
    return array("vtrcount"=>$vtotalreceivecount,"vtrweight"=>$vtotalreceiveweight,
                "vtccount"=>$vtotalconsumecount,"vtcweight"=>$vtotalconsumeweight,
                "vtxcount"=>$vtotalremaincount,"vtxweight"=>$vtotalremainweight,
                "exceldata"=>$exceldata,"vtrdatecount"=>$vtotaldatereceivecount,"vtrdateweight"=>$vtotaldatereceiveweight,
                "vtcdatecount"=>$vtotaldateconsumecount,"vtcdateweight"=>$vtotaldateconsumeweight,
                "vtxdatecount"=>$vtotaldateremaincount,"vtxdateweight"=>$vtotaldateremainweight,
                "exceldata"=>$exceldata,'rtype'=>$rtype);
                

}

  

dbclose();
?>

