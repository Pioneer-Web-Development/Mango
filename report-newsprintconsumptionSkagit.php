<?php
//<!--VERSION: .9 **||**-->

if ($_POST['submit']=='To Excel')
{
    global $siteID;
    include("includes/functions_db.php");
    generate_excel();
} else {
    include("includes/mainmenu.php") ;
    
    //build vendor list
    $sql="SELECT * FROM vendors WHERE status=1 ORDER BY vendor_name";
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
                $vendor=" AND vendor_id=$_POST[vendor]";
            } else {
                $vendor="";
            }
             if ($_POST['source']!=0){
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
        } else {
            $enddate=date("Y-m-d");
            $startdate=date("Y-m-d",strtotime('-3 months'));
            $vendor="";
            $status="";
            $validated='';
            $manifestzero=0;
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
            print input_checkbox('validated',$validated)." Show only rolls that have been validated<br />";
            print input_checkbox('manifest',$manifestzero)." Eliminate manifests with no balance of uncomsumed rolls<br />";
            print 'Start Date '.make_date('startdate',$startdate)."<br>";
            print 'End Date '.make_date('enddate',$enddate)."<br>";
            
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
                $vrolls=vendor_rolls($vid,$vname,$startdate,$enddate,$source,$psize,$ptype,$validated,$manifestzero);
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
            }
        }
    } else {
        $vrolls=vendor_rolls($_POST['vendor'],$vendors[$_POST['vendor']],$startdate,$enddate,$source,$psize,$ptype,$validated,$manifestzero);
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
    }
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
        foreach($rollbytype as $rtype)
        {
            asort($rtype);
            foreach($rtype as $type=>$remaining)
            {
             print "<li>$type - $remaining</li>\n";
             $total+=$remaining;   
            }
            
        }
        print "</ul>\n"; 
        print "<br />Total of $total rolls in inventory<br />";
    }


    print "</div>
    </body>
    </html>";

}

function vendor_rolls($vendorid,$vendorname,$startdate,$enddate,$source='',$size='',$type='',$validated='',$manifestzero=false)
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
            $sql="SELECT DISTINCT(manifest_number), receive_datetime FROM rolls 
            WHERE status IN ($rollstats) AND order_id=$order[id] AND receive_datetime>='$startdate' AND receive_datetime<='$enddate' $size $type ORDER BY receive_datetime DESC";
            $dbReceiveManifests=dbselectmulti($sql);
            //print "<tr><td>Receive manifest sql is $sql</td></tr>\n";
            if ($dbReceiveManifests['numrows']>0)
            {
                foreach($dbReceiveManifests['data'] as $rmanifest)
                {
                    $manifest=$rmanifest['manifest_number'];
                    //print "<tr><td>unique manifest number here --$manifest-- for order $order[id]</td></tr>\n";
                    $rdate=date("m/d/Y",strtotime($rmanifest['receive_datetime']));
                    //now look up the rolls
                    $sql="SELECT DISTINCT(common_name) as paper, roll_width as width, 
                    count(id) as rollcount, sum(roll_weight) as totalweight, 
                    order_item_id FROM rolls WHERE status IN ($rollstats) AND (batch_date<='$enddate' OR batch_date IS Null) AND order_id=$order[id] AND manifest_number='$manifest' $size $validated GROUP BY common_name, roll_width ORDER BY width ASC";
                    //print "<tr><td> roll sql is $sql</td></tr>\n";
                    $dbRollTotal=dbselectmulti($sql);
                    if ($dbRollTotal['numrows']>0)
                    {
                        foreach ($dbRollTotal['data'] as $rolltotal)
                        {
                            if (!$excel){
                                print "<tr><td>$order[order_source]</td><td>$manifest</td><td>$rdate</td>\n";
                            }
                            $exceldata.="<Row>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$order[order_source]</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$manifest</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$rdate</Data></Cell>";
                            
                            $receivecount=$rolltotal['rollcount'];
                            $receiveweight=$rolltotal['totalweight'];
                            $name=$rolltotal['paper'];
                            $width=$rolltotal['width'];
                            $orderitemid=$rolltotal['order_item_id'];
                            
                            //now, get the number of these rolls where batch_date is between our target dates
                            $sql="SELECT count(id) as rollcount, sum(roll_weight) as totalweight 
                            FROM rolls WHERE order_id=$order[id] AND status IN ($rollstats) 
                            AND order_item_id=$orderitemid AND manifest_number='$manifest' 
                            AND batch_date>='$startdate' AND batch_date<='$enddate' $validated $type $size";
                            $dbRollConsume=dbselectsingle($sql);
                            //print "<tr><td>Consume roll sql is $sql</td></tr>\n";
                            $consumecount=0;
                            $consumeweight=0;
                            if ($dbRollConsume['numrows']>0)
                            {
                                $consumecount=$dbRollConsume['data']['rollcount'];
                                $consumeweight=$dbRollConsume['data']['totalweight'];
                            } else {
                                $consumecount=0;
                                $consumeweight=0;
                            }
                            $remaincount=$receivecount-$consumecount;
                            $remainweight=$receiveweight-$consumeweight;
                            
                            $vtotalreceivecount=$vtotalreceivecount+$receivecount;
                            $vtotalreceiveweight=$vtotalreceiveweight+$receiveweight;
                            $vtotalconsumecount=$vtotalconsumecount+$consumecount;
                            $vtotalconsumeweight=$vtotalconsumeweight+$consumeweight;
                            $vtotalremaincount=$vtotalremaincount+$remaincount;
                            $vtotalremainweight=$vtotalremainweight+$remainweight;
                            
                            $vtotaldatereceivecount=$vtotaldatereceivecount+$receivecount;
                            $vtotaldatereceiveweight=$vtotaldatereceiveweight+$receiveweight;
                            $vtotaldateconsumecount=$vtotaldateconsumecount+$consumecount;
                            $vtotaldateconsumeweight=$vtotaldateconsumeweight+$consumeweight;
                            $vtotaldateremaincount=$vtotaldateremaincount+$remaincount;
                            $vtotaldateremainweight=$vtotaldateremainweight+$remainweight;
                            
                            $rtype[$name.' - '.$width]+=$remaincount;
                            
                            
                            if (!$excel){
                                print "<td>$name</td><td>$width</td><td>$receivecount</td><td>".sprintf("%.3f",$receiveweight/1000)."</td>
                                <td>$consumecount</td><td>".sprintf("%.3f",$consumeweight/1000)."</td>
                                <td>$remaincount</td><td>".sprintf("%.3f",$remainweight/1000)."</td></tr>\n";
                            }
                            
                            $exceldata.= "<Cell><Data ss:Type='String'>$name</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$width</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$receivecount</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$receiveweight/1000)."</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$consumecount</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$consumeweight/1000)."</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$remaincount</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$remainweight/1000)."</Data></Cell>";
                            $exceldata .= "</Row>";
                        }
                    }
                }
            }
            //now find manifests received earlier or later, but consumed during this time
            $sql="SELECT DISTINCT(manifest_number), receive_datetime FROM rolls 
            WHERE status IN ($rollstats) AND order_id=$order[id] AND (receive_datetime<='$startdate' OR receive_datetime>='$enddate')
            AND batch_date>='$bstartdate' AND batch_date<='$benddate' ORDER BY receive_datetime DESC";
            //print "Checking rolls received before the specified period with $sql<br>";
            $dbConsumedManifests=dbselectmulti($sql);
            if ($dbConsumedManifests['numrows']>0)
            {
                if (!$excel){
                    print "<tr><td colspan=9>These are rolls consumed during this period, but received earlier</td></tr>\n";
                }
                $exceldata.="<Row>";
               $exceldata.= "<Cell><Data ss:Type='String'>These are rolls consumed during this period, but received earlier</Data></Cell>";
               $exceldata.="</Row>";
               
                foreach($dbConsumedManifests['data'] as $cmanifest)
                {
                    $manifest=$cmanifest['manifest_number'];
                    $rdate=date("m/d/Y",strtotime($cmanifest['receive_datetime']));
                    //now look up the rolls
                    $sql="SELECT DISTINCT(common_name) as paper, order_item_id, roll_width as width, 
                    count(id) as rollcount, sum(roll_weight) as totalweight FROM rolls 
                    WHERE status IN ($rollstats) AND order_id=$order[id] AND manifest_number='$manifest' $validated $type $size GROUP BY common_name, roll_width ORDER BY width ASC";
                    $dbRollTotal=dbselectmulti($sql);
                    //print "Consumed roll sql is $sql<br>";
                    if ($dbRollTotal['numrows']>0)
                    {
                        foreach ($dbRollTotal['data'] as $rolltotal)
                        {
                            if (!$excel)
                            {
                                print "<tr><td>$order[order_source]</td><td>$manifest</td><td>$rdate</td>\n";
                            }
                            $exceldata.="<Row>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$order[order_source]</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$manifest</Data></Cell>";
                            $receivecount=$rolltotal['rollcount'];
                            $receiveweight=$rolltotal['totalweight'];
                            $name=$rolltotal['paper'];
                            $width=$rolltotal['width'];
                            $orderitemid=$rolltotal['order_item_id'];
                            //now, get the number of these rolls where batch_date is between our target dates
                            $sql="SELECT count(id) as rollcount, sum(roll_weight) as totalweight FROM rolls WHERE order_id=$order[id] AND status<>99 AND order_item_id=$orderitemid AND manifest_number='$manifest' AND batch_date>='$bstartdate' AND batch_date<='$benddate' AND common_name='$name' AND roll_width='$width'";
                            $dbRollConsume=dbselectsingle($sql);
                            $consumecount=0;
                            $consumeweight=0;
                            if ($dbRollConsume['numrows']>0)
                            {
                                $consumecount=$dbRollConsume['data']['rollcount'];
                                $consumeweight=$dbRollConsume['data']['totalweight'];
                            } else {
                                $consumecount=0;
                                $consumeweight=0;
                            }
                            $remaincount=$receivecount-$consumecount;
                            $remainweight=$receiveweight-$consumeweight;
                            
                            $vtotalreceivecount+=$receivecount;
                            $vtotalreceiveweight+=$receiveweight;
                            $vtotalconsumecount+=$consumecount;
                            $vtotalconsumeweight+=$consumeweight;
                            $vtotalremaincount+=$remaincount;
                            $vtotalremainweight+=$remainweight;
                            $rtype[$name.' - '.$width]+=$remaincount;
                            
                            if (!$excel){
                                print "<td>$name</td><td>$width</td><td>$receivecount</td><td>".sprintf("%.3f",$receiveweight/1000)."</td>
                                <td>$consumecount</td><td>".sprintf("%.3f",$consumeweight/1000)."</td>
                                <td>$remaincount</td><td>".sprintf("%.3f",$remainweight/1000)."</td></tr>\n";
                            }
                            
                            $exceldata.= "<Cell><Data ss:Type='String'>$name</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$width</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$receivecount</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$receiveweight/1000)."</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$consumecount</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$consumeweight/1000)."</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>$remaincount</Data></Cell>";
                            $exceldata.= "<Cell><Data ss:Type='String'>".sprintf("%.3f",$remainweight/1000)."</Data></Cell>";
                            $exceldata .= "</Row>";
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


function generate_excel()
{
    
//build vendor list
$sql="SELECT * FROM vendors WHERE status=1 ORDER BY vendor_name";
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
        $status=" AND A.status=$_POST[status] ";
    }
    if ($_POST['manifest'])
    { 
        $manifestzero=1;
    } else {
        $manifestzero=0;
    }
} else {
    $enddate=date("Y-m-d");
    $startdate=date("Y-m-d",strtotime('-3 months'));
    $vendor="";
    $status="";
    $manifestzero=0;
}    
    $startdate.=" 00:00:01";
    $enddate.=" 23:59:59";

    $totalreceivecount=0;
    $totalreceiveweight=0;
    $totalconsumecount=0;
    $totalconsumeweight=0;
    $totalremaincount=0;
    $totalremainweight=0;
        

    
    
    
    $title=str_replace(":","","Newsprint_Consumption - ".date("Y-m-d H:i"));
    $title=str_replace(" ","_",$title);
    $data = "<?xml version='1.0'?>
    <?mso-application progid='Excel.Sheet'?>
    <Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet' xmlns:html='http://www.w3.org/TR/REC-html40'>
    <Worksheet ss:Name='Consumption Report'>
    <Table>";
    //create the header section
    
    
    if ($_POST['vendor']==0)
    {
        foreach($vendors as $vid=>$vname)
        {
            if ($vid!=0)
            {
                $vrolls=vendor_rolls($vid,$vname,$startdate,$enddate,$source,$psize,$ptype,true);
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
                $data.=$vrolls['exceldata'];
                $data.="<Row></Row>";
            }
        }
    } else {
        $vrolls=vendor_rolls($_POST['vendor'],$vendors[$_POST['vendor']],$startdate,$enddate,$source,$psize,$ptype,true);
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
        
        $data.=$vrolls['exceldata']; 
    }
    
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

dbclose();
?>

