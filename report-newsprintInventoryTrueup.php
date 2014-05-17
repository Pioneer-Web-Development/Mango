<?php
//<!--VERSION: .9 **||**-->

if ($_POST['submit']=='To Excel')
{
    global $siteID;
    include("includes/functions_db.php");
    $title=str_replace(":","","Newsprint_Consumption - ".date("Y-m-d H:i"));
    $title=str_replace(" ","_",$title);
    $data = "<?xml version='1.0'?>
    <?mso-application progid='Excel.Sheet'?>
    <Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet' xmlns:html='http://www.w3.org/TR/REC-html40'>
    <Worksheet ss:Name='Consumption Report'>
    <Table>";
    //create the header section
     //Final XML Blurb
    
} else {
    include("includes/mainmenu.php") ;
    $scriptpath='http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'] ;
    if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}
    print "<body> <div id='wrapper'>";
     //build vendor list
    $sql="SELECT * FROM vendors WHERE status=1 AND newsprint=1 ORDER BY vendor_name";
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
            $inventorydate=$_POST['inventorydate'];
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
        } else {
            $inventorydate=date("Y-m-d",strtotime('-3 months'));
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
            print "<div style='float:left;'>Inventory date: <div><script>DateInput('inventorydate', true, 'YYYY-MM-DD','$inventorydate')</script></div>\n</div>\n";
            print "<div style='float:left;margin-left:20px;'><input type='submit' name='submit' value='Generate Report' /></div>\n";
            print "<div style='float:left;margin-left:20px;'><input type='submit' name='submit' value='To Excel' /></div>\n";
            print "<div style='clear:both;'></div>\n";
            
        print "</div>\n";
    print "</form>\n";
    $totalreceivecount=0;
    $totalreceiveweight=0;
    $totalconsumecount=0;
    $totalconsumeweight=0;
    $totalremaincount=0;
    $totalremainweight=0;
}       

    if ($_POST['vendor']==0 || !$_POST)
    {
        foreach($vendors as $vid=>$vname)
        {
            if ($vid!=0)
            {
                $vrolls=vendor_rolls($vid,$vname,$inventorydate,$source,$psize,$ptype);
                $data.=$vrolls['exceldata'];
            }
        }
    } else {
        $vrolls=vendor_rolls($_POST['vendor'],$vendors[$_POST['vendor']],$inventorydate,$source,$psize,$ptype);
        $data.=$vrolls['exceldata']; 
    }
    
if ($_POST['toexcel'])
{
    $data .= "</Table></Worksheet></Workbook>";
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=$title.xls;");
    header("Content-Type: application/ms-excel");
    header("Pragma: no-cache");
    header("Expires: 0");
} else {
    print "</div>
    </body>
    </html>";
}

function vendor_rolls($vendorid,$vendorname,$inventorydate,$source='',$size='',$type='',$excel=false)
{
    $exceldata="";
    if ($source!=''){$source=" AND order_source='$source'";}else{$source="";}
    if ($size!=''){$size=" AND roll_width='$size'";}else{$size="";}
    if ($type!=''){$type=" AND common_name='$type'";}else{$type="";}
    //ok, find any all orders for this vendor id
    $sql="SELECT * FROM orders WHERE vendor_id=$vendorid $source ORDER BY order_datetime DESC";
    $dbOrders=dbselectmulti($sql);
    if ($dbOrders['numrows']>0)
    {
       
       $exceldata.="<Row>";
       $exceldata.= "<Cell><Data ss:Type='String'>$vendorname</Data></Cell>";
       $exceldata.="</Row>";
       
       if (!$excel){
           print "<table class='report'>\n";
           print "<tr><th colspan=8><h1>$vendorname</h1></th></tr>\n";
           print "<tr><th>Source</th><th>Order ID</th><th>Manifest</th><th>Roll Tag</th><th>Receive Date</th><th>Type</th><th>Size</th><th>Status</th></tr>\n";
       }
        $exceldata .= "<Row>";
        $exceldata .="<Cell><Data ss:Type='String'>Source</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Order ID</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Manifest</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Roll Tag</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Receive Date</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Paper Type</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Paper Size</Data></Cell>";
        $exceldata .="<Cell><Data ss:Type='String'>Status</Data></Cell>";
        $exceldata .= "</Row>";
        $orderid=$dbOrders['data']['0']['id'];
        $sql="SELECT B.vendor_name FROM orders A, vendors B WHERE A.id=$orderid AND A.vendor_id=B.id";
        $dbVendor=dbselectsingle($sql);
        $vname=$dbVendor['data']['vendor_name'];
        //print "<tr><td>The query here returned $vname</td></tr>";
       foreach($dbOrders['data'] as $order)
       {
            $sql="SELECT roll_tag, common_name as paper, roll_width as width, order_item_id, validated, 
            validation_error, receive_datetime, manifest_number, status FROM rolls 
            WHERE validated=0 AND receive_datetime<='$inventorydate' AND status<>99 AND status<>9 AND order_id=$order[id] 
            $size GROUP BY manifest_number, common_name, roll_width ORDER BY width ASC";
            //print "<tr><td> roll sql is $sql</td></tr>\n";
            $dbRolls=dbselectmulti($sql);
            if ($dbRolls['numrows']>0)
            {
                foreach ($dbRolls['data'] as $roll)
                {
                            
                    $manifest=$roll['manifest_number'];
                    //print "<tr><td>unique manifest number here --$manifest-- for order $order[id]</td></tr>\n";
                    $rdate=date("m/d/Y",strtotime($roll['receive_datetime']));
                    //now look up the rolls
                    $rolltag=$roll['roll_tag'];
                    $name=$roll['paper'];
                    $width=$roll['width'];
                    switch($roll['validation_error'])
                    {
                        case 0:
                        $status='OK';
                        break;
                        
                        case 1:
                        $status='Vendor conflict';
                        break;
                        
                        case 2:
                        $status='Type conflict';
                        break;
                        
                        case 3:
                        $status='Vendor & type conflict';
                        break;
                        
                        case 4:
                        $status='Size conflict';
                        break;
                        
                        case 5:
                        $status='Vendor & size conflict';
                        break;
                        
                        case 6:
                        $status='Type & size confict';
                        break;
                        
                        case 7:
                        $status='Vendor, type & size conflict';
                        break;                        
                    }
                    if ($roll['validated']=='0')
                    {
                        $status='NOT VALIDATED';
                    }
                    if (!$excel){
                        print "<tr><td>$order[order_source]</td><td>$order[id]</td><td>$manifest</td><td>$rolltag</td><td>$rdate</td><td>$name</td><td>$width</td><td>$status</td></tr>\n";
                    }
                    $exceldata.="<Row>";
                    $exceldata.= "<Cell><Data ss:Type='String'>$order[order_source]</Data></Cell>";
                    $exceldata.= "<Cell><Data ss:Type='String'>$order[id]</Data></Cell>";
                    $exceldata.= "<Cell><Data ss:Type='String'>$manifest</Data></Cell>";
                    $exceldata.= "<Cell><Data ss:Type='String'>$rolltag</Data></Cell>";
                    $exceldata.= "<Cell><Data ss:Type='String'>$rdate</Data></Cell>";
                    
                    $exceldata.= "<Cell><Data ss:Type='String'>$name</Data></Cell>";
                    $exceldata.= "<Cell><Data ss:Type='String'>$width</Data></Cell>";
                    $exceldata.= "<Cell><Data ss:Type='String'>$status</Data></Cell>";
                    $exceldata.= "</Row>";
                    
                }
            }
       }
    }
            
    return array("exceldata"=>$exceldata);
}

dbclose();
?>

