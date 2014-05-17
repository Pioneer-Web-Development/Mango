<?php
include("includes/mainmenu.php") ;


if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
    switch ($action)
    {
        case "Search":
        manage_orders('list');
        break;
        
        case "Save Order":
        save_order('insert');
        break;
        
        case "Update Order":
        save_order('update');
        break;
        
        case "add":
        manage_orders('add');
        break;
        
        case "edit":
        manage_orders('edit');
        break;
        
        case "delete":
        manage_orders('delete');
        break;
        
        case "list":
        manage_orders('list');
        break;
        
        case "import":
        import('live');
        break;

        case "checkmanifest":
        import('test');
        break;
        
        case "Import Manifest":
        import_manifest();
        break;
        
        default:
        manage_orders('list');
        break;
        
    } 
    
    


function manage_orders($action)
{
    global $orderstatuses, $ordersources, $newsprintVendors, $siteID;
    //order status
    /*
      1 == Ordered
      2 == Received
      3 == Processed
      4 == Completed
      99 == cancelled
    
    
    */
     
    
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
    $sizes=array();
    $sizes[0]="Size";
    if ($dbSizes['numrows']>0)
    {
        foreach($dbSizes['data'] as $size)
        {
            $sizes[$size['id']]=$size['width'];
        }
    }
    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Order";
            $validated=false;
            $order_source="pioneer";
            $orderdatetime=date("Y-m-d");
            //insert a blank order so we have a good order id to work with
            $sql="INSERT INTO orders (vendor_id, order_by, order_code, order_source, total_tonnage, order_datetime, validated, site_id) VALUES ('0','".$_SESSION['cmsuser']['userid']."','','$order_source',0,'$orderdatetime',0, '$siteID')";
            $dbInsert=dbinsertquery($sql);
            $orderid=$dbInsert['insertid'];
            //print "Order id is $orderid<br>$sql";
            
        } else {
            $button="Update Order";
            $orderid=$_GET['orderid'];
            $sql="SELECT * FROM orders WHERE id=$orderid";
            $dbOrder=dbselectsingle($sql);
            $order=$dbOrder['data'];
            $vendor_id=stripslashes($order['vendor_id']);
            $order_code=stripslashes($order['order_code']);
            $order_source=stripslashes($order['order_source']);
            $total_weight=stripslashes($order['total_tonnage']);
            $orderdatetime=$order['order_datetime'];
            $validated=stripslashes($order['validated']);
        
        }
        if ($_GET['error']){displayMessage("There are problems with your order, please check that you have specified a size and paper type for each item.",'error');}
        print "<form method=post>\n";
        make_select('vendor_id',$newsprintVendors[$vendor_id],$newsprintVendors,'Vendor Name','Who is this being ordered from?');
        make_select('order_source',$ordersources[$order_source],$ordersources,'Order Source','Which company is the source of this order?');
        make_text('order_code',$order_code,'Order Code/Ref. Number','Vendor order number or reference code for this order');
        make_date('orderdatetime',$orderdatetime,'Order Date');
        print "<div class='label'>Order Items</div>\n";
        print "<div id='orderitems' class='input'>\n";
        //now, we need to add a call to get all existing items for this order
        $sql="SELECT * FROM order_items WHERE order_id=$orderid ORDER BY itemdisplay_order ASC";
        $dbItems=dbselectmulti($sql);
                    
        if ($dbItems['numrows']>0)
        {
            foreach ($dbItems['data'] as $item)
            {
                $i=$item['id'];
                print "<div id='item_$i'><span style='float:left;width:28px;'><span id='success_$i' style='display:none;'><img src='artwork/icons/accepted_48.png' width=20 border=0 /></span></span>\n";
                print input_select('paper_'.$i,$papertypes[$item['paper_type_id']],$papertypes)."&nbsp;&nbsp;";
                print input_select('size_'.$i,$sizes[$item['size_id']],$sizes)." Tons: ";
                print "<input type=text name='tonnage_$i' id='tonnage_$i' value='$item[tonnage_request]' class='ton' size=5 onChange='calcTonnage();' onBlur='newsprintOrderItemSave(\"$i\",\"$orderid\");' onKeyPress='return isNumberKey(event);' />MT\n";
                print "<a href='#' onclick='newsprintOrderItemSave(\"$i\",\"$orderid\");' style='text-decoration:none;'><img src='artwork/icons/folder_48.png' width=20 border=0 />&nbsp;Save</a>\n";
                print "<a href='#' onclick='newsprintOrderItemDelete(\"$i\",\"$orderid\");' style='text-decoration:none;'><img src='artwork/icons/cancel_48.png' width=20 border=0 />&nbsp;Delete</a>\n";
                print "</div>\n";
            }
        }
        print "</div>\n";
        print "<div class='clear'></div>\n";
        print "</div>\n"; //closes itemHolder
        
        make_button('addItemButton','Add another item to order','',false,"newsprintOrderItemAdd('$orderid')");
        make_text('total_weight',$total_weight,'Total Weight','Total weight of order',5,'',false);
        make_hidden('orderid',$orderid);
        make_submit('submit',$button,'',false,'calcTonnage();');
        print "</form>\n";
         
    } elseif($action=='delete') {
        $orderid=intval($_GET['orderid']);
        $sql="UPDATE orders SET status=99 WHERE id=$orderid";
        $dbUpdate=dbexecutequery($sql);
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the order','error');
        } else {
            setUserMessage('Newsprint order deleted saved','success');
        }
        redirect("?=action=list");
    } else {
        global $orderstatuses, $newsprintVendors, $users;
        
        //be default, we're going to assume a 3 month set of dates
        if ($_POST['submit']=='Search')
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
                $status=" AND A.status<>99";
            } else {
                $status=" AND A.status=$_POST[status] ";
            }
        } else {
            $enddate=date("Y-m-d");
            $startdate=date("Y-m-d",strtotime('-3 months'));
            $vendor="";
            $status="";
        }
        
        $search="<form method=post>\n";
        $search.="<b>Vendor:</b><br />\n";
        $search.=input_select('vendor',$newsprintVendors[$_POST['vendor']],$newsprintVendors)."<br>\n";
        $search.="<br /><b>Order Status:</b><br /> ";
        $search.=input_select('status',$orderstatuses[$_POST['status']],$orderstatuses)."<br>\n";
        $search.="<br /><b>Order Source:</b><br />";
        $search.=input_select('source',$ordersources[$_POST['source']],$ordersources)."<br>\n";
        $search.='<br /><b>Start Date:</b><br />'.make_date('startdate',$startdate);
        $search.='<br /><b>End Date:</b><br />'.make_date('enddate',$enddate);
        $search.="<br /><input type='submit' id='submit' name='submit' value='Search' />\n";
        $search.="</form>\n";
        $startdate.=" 00:00:01";
        $enddate.=" 23:59:59";
        
        $sql="SELECT A.*, B.account_name FROM orders A, accounts B WHERE A.vendor_id=B.id AND A.order_datetime>='$startdate' 
         AND A.order_datetime<='$enddate' $status $vendor $source ORDER BY A.order_datetime DESC, B.account_name DESC, A.order_source DESC";
        
        $dbOrders=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new order</a>,<a href='?action=checkmanifest'>Check an EDI manifest</a>","Order #,Date Time,Ordered By,Vendor,Order Source,Tonnage,Status",10,$search);
        if ($dbOrders['numrows']>0)
        {
            foreach($dbOrders['data'] as $order)
            {
                $name=$order['account_name'];
                $date=date("m/d/Y",strtotime($order['order_datetime']));
                $orderid=$order['id'];
                $status=$order['status'];
                $tons=$order['total_tonnage'];
                $status=$orderstatuses[$status];
                $source=$order['order_source'];
                if($order['order_by']!=''){$person=$users[$order['order_by']];}else{$person='Unknown';}
                print "<tr><td>$orderid</td><td>$date</td><td>$person</td><td>$name</td><td>$source</td><td>$tons</td><td>$status</td>";
                print "<td><a href='?action=import&orderid=$orderid'>Import EDI Manifest</a></td>\n";
                print "<td><a href='?action=edit&orderid=$orderid'>Edit</a></td>\n";
                print "<td><a href='?action=delete&orderid=$orderid' class='delete'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbOrders,'',0,'desc');
        
    }
}




function save_order($action)
{
    //grab the easy pieces first
    global $siteID;
    $orderid=$_POST['orderid'];
    $total_weight=$_POST['total_weight'];
    $vendor_id=$_POST['vendor_id'];
    $order_code=addslashes($_POST['order_code']);
    $source=$_POST['order_source'];
    if ($source=="0"){$source='pioneer';}
    $values="";
    $processed=array();
    $order_datetime=$_POST['orderdatetime'];
    //$order_datetime=date("Y-m-d H:i:s");
    
    $sql="UPDATE orders SET vendor_id='$vendor_id', order_code='$order_code', total_tonnage='$total_weight',
     status='1', order_source='$source', order_datetime='$order_datetime' WHERE id=$orderid";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    
    if ($error!='')
    {
        setUserMessage('There was a problem saving the order','error');
    } else {
        setUserMessage('Newsprint order successfully saved','success');
    }
    if ($flag)
    {
        redirect("?action=edit&orderid=$orderid&error=true");
    } else {
        redirect("?action=list");
    }
}

function import($mode='live')
{
    $orderid=intval($_GET['orderid']);
    //lets see if we have already imported an edi manifest for this order
    $sql="SELECT * FROM edi_order WHERE order_id=$orderid";
    $dbCheck=dbselectsingle($sql);
    print "<form method=post enctype='multipart/form-data'>\n";
    make_file('edi','Newsprint Manifest','Please select the newsprint manifest to be imported.');
    make_hidden('mode',$mode);
    make_hidden('orderid',$orderid);
    make_submit('submit','Import Manifest');
    print "</form>\n";
    
    if($dbCheck['numrows']>0)
    {
        $ediorder=$dbCheck['data'];
        print "<p>There is an existing EDI manifest for this order. If you Import a new one it will be removed and re-imported.</p>";
        //get the order items
        $sql="SELECT A.*, B.common_name, C.width FROM edi_order_items A, paper_types B, paper_sizes C WHERE A.order_id=$orderid AND A.paper_type_id=B.id AND A.size_id=C.id";
        $dbEDIitems=dbselectmulti($sql);
        if($dbEDIitems['numrows']>0)
        {
            foreach($dbEDIitems['data'] as $ediitem)
            {
                print "<ul>\n";
                print $ediitem['common_name'].' in '.$ediitem['width'].' width. Total weight '.$ediitem['tonnage_request'].'<br>';
                $sql="SELECT * FROM edi_rolls WHERE order_id=$orderid AND order_item_id=$ediitem[id]";
                $dbRolls=dbselectmulti($sql);
                if($dbRolls['numrows']>0)
                {
                    foreach($dbRolls['data'] as $roll)
                    {
                        print "<li>".$roll['common_name'].' - weight: '.$roll['roll_weight'].' - width: '.$roll['roll_width'].' - Tag# '.$roll['roll_tag']."</li>\n";
                    }
                }
                print "</ul>\n";
            }
        }   
    }
   
}

function import_manifest()
{
    global $siteID;
    $orderid=$_POST['orderid'];
    if($_POST['mode']=='test'){$mode='test';}else{$mode='live';}
    $file=$_FILES['edi']['tmp_name'];
    $contents=file_get_contents($file);
    $lines=explode("\n",$contents);

    $order=array();
    
    $inshipment=false;
    $inorder=false;
    $indescription=false;
    $initem=false;
    $orderitems=0;
    $roll=0;
    foreach($lines as $line)
    {
        $lineparts=explode("*",$line);
        switch($lineparts[0])
        {
            case "BSN":
                $order['manifest_number']=$lineparts[2];
                $mdate=substr($lineparts[3],0,4).'-'.substr($lineparts[3],4,2).'-'.substr($lineparts[3],6,2).' '.substr($lineparts[4],0,2).':'.substr($lineparts[4],2,2);
                $order['manifest_date']=$mdate;
            break;
            
            case "REF":
                if($lineparts[1]=='BM')
                {
                    $order['manifest_number']=$lineparts[2];
                }
            break;
            
            case "HL":
                if(trim($lineparts[3])=='S')
                {
                    $inshipment=true;
                    $inorder=false;
                    $indescription=false;
                    $initem=false;
                            
                } elseif(trim($lineparts[3])=='O')
                {
                    $inshipment=false;
                    $inorder=true;
                    $indescription=false;
                    $initem=false;

                } elseif(trim($lineparts[3])=='D')
                {
                    $inshipment=false;
                    $inorder=false;
                    $indescription=true;
                    $initem=false;
                    $orderitems++;
                    $roll=0;
                } elseif(trim($lineparts[3])=='I')
                {
                    $roll++;
                    $inshipment=false;
                    $inorder=false;
                    $indescription=false;
                    $initem=true;
                }
             break;
             
             case "N1":
                if($lineparts[1]=='SO')
                {
                    $order['sold_to']=trim($lineparts[2]);
                }elseif($lineparts[1]=='ST')
                {
                    $order['ship_to']=trim($lineparts[2]);
                }elseif($lineparts[1]=='MP')
                {
                    $order['vendor']=trim($lineparts[2]);
                }
             break;
             
             case "MEA":
                if($inshipment)
                {
                    if($lineparts[1]=='CT' && $lineparts[4]=='RL')
                    {
                        $order['total_packs']=trim($lineparts[3]);    
                    }elseif($lineparts[1]=='CT' && $lineparts[4]=='PK')
                    {
                        $order['total_rolls']=trim($lineparts[3]);
                    } elseif($lineparts[1]=='WT' && $lineparts[2]=='G')
                    {
                        $totalweight=trim($lineparts[3]);
                        if(trim(strtolower($lineparts[4]))=='lb')
                        {
                            //always convert to kg
                            $totalweight=$totalweight*0.45359237;
                            $totalweight=round($totalweight,2);    
                        }
                        $order['total_gross']=$totalweight;
                            
                    }   
                }elseif($inorder)
                {
                    if($lineparts[1]=='CT' && $lineparts[4]=='RL')
                    {
                        $order['order_packs']=trim($lineparts[3]);    
                    }elseif($lineparts[1]=='CT' && $lineparts[4]=='PK')
                    {
                        $order['order_rolls']=trim($lineparts[3]);
                    } elseif($lineparts[1]=='WT' && $lineparts[2]=='G')
                    {
                        $orderweight=trim($lineparts[3]);
                        if(trim(strtolower($lineparts[4]))=='lb')
                        {
                            //always convert to kg
                            $orderweight=$orderweight*0.45359237;
                            $orderweight=round($orderweight,2);     
                        }
                        $order['order_gross']=$orderweight;
                            
                    }
                }elseif($indescription)
                {
                    if($lineparts[1]=='CT' && $lineparts[4]=='RL')
                    {
                        $order['order_items'][$orderitems]['item_packs']=trim($lineparts[3]);    
                    }elseif($lineparts[1]=='CT' && $lineparts[4]=='PK')
                    {
                        $order['order_items'][$orderitems]['item_rolls']=trim($lineparts[3]);
                    } elseif($lineparts[1]=='WT' && $lineparts[2]=='G')
                    {
                        $itemweight=trim($lineparts[3]);
                        if(trim(strtolower($lineparts[4]))=='lb')
                        {
                            //always convert to kg
                            $itemweight=$itemweight*0.45359237;
                            $itemweight=round($itemweight,2);     
                        }
                        $order['order_items'][$orderitems]['item_gross']=$itemweight;     
                    } elseif($lineparts[1]=='WT' && $lineparts[2]=='BW')
                    {
                        $order['order_items'][$orderitems]['item_basis_weight']=trim($lineparts[3]);    
                        $order['order_items'][$orderitems]['item_basis_weight_unit']=trim($lineparts[4]);    
                    }
                }elseif($initem)
                {
                    if($lineparts[2]=='G')
                    {
                        $rollweight=trim($lineparts[3]);
                        if(trim(strtolower($lineparts[4]))=='lb')
                        {
                            //always convert to kg
                            $rollweight=$rollweight*0.45359237;
                            $rollweight=round($rollweight,2);     
                        }
                        $order['order_items'][$orderitems]['rolls'][$roll]['roll_weight']=$rollweight;
                    }
                }
             
             break;
             
             case "LIN":
                if($indescription)
                {
                   $order['order_items'][$orderitems]['grade_code']=$lineparts[3];
                   $order['order_items'][$orderitems]['grade_name']=$lineparts[5];
                   $order['order_items'][$orderitems]['grade_color']=$lineparts[7];
                } elseif($initem)
                {
                    if($lineparts[2]=='PG')
                    {
                        $order['order_items'][$orderitems]['rolls'][$roll]['roll_tag']=trim($lineparts[5]);
                    } elseif($lineparts[2]=='RO')
                    {
                        $order['order_items'][$orderitems]['rolls'][$roll]['roll_tag']=trim($lineparts[3]);
                    }
                    $order['order_items'][$orderitems]['count']++; 
                    $order['rollticker']++; 
                }
             break;
             
             case "PO4":
                 $order['order_items'][$orderitems]['rolls_per_pack']=trim($lineparts[1]);
                 $order['order_items'][$orderitems]['roll_width']=trim($lineparts[2]);
                 $order['order_items'][$orderitems]['roll_width_units']=trim($lineparts[3]);
                 $order['order_items'][$orderitems]['roll_diameter']=trim($lineparts[12]);
                 $order['order_items'][$orderitems]['roll_diameter_units']=trim($lineparts[13]);
             break;
                
        }    
    }
    /*
    print "<pre>";
    print_r($order);
    print "</pre>\n";
    */
    //import into the database
    //clear out any existing orders and stuff
    
    if($mode=='test')
    {
        print "Manifest Date: ".$order['manifest_date']."<br>";
        print "Manifest Number: ".$order['manifest_number']."<br>";
        print "Total Rolls: ".$order['rollticker']."<br>";
        print "Total Gross: ".round($order['total_gross']/1024,2)."MT<br>";
        
        
        
        foreach($order['order_items'] as $key=>$orderitem)
        {
            $gc=$orderitem['grade_code'];
            $gn=$orderitem['grade_name'];
            $sql="SELECT * FROM paper_types WHERE grade_code LIKE '%$gc%' or grade_code LIKE '%$gn%'";
            $dbGrade=dbselectsingle($sql);
            $grade=$dbGrade['data'];
            if($dbGrade['numrows']>0)
            {
                $paperTypeID=$dbGrade['data']['id'];
                $paperCommonName=$dbGrade['data']['common_name'];
                $unknown='';
            } else {
                $paperTypeID=0;
                $unknown='Grade Code: '.$gc.' or '.$gn;
            }
            
            $size=$orderitem['roll_width'];
            //convert as necessary to inches
            switch(strtolower($orderitem['roll_width_units']))
            {
                case "mm":
                $size=mm2inches($size);
                break;
                
                case "cm":
                $size=cm2inches($size);
                break;
                
                default:
                //no change
                break;
                
            }
            //$sql="SELECT * FROM paper_sizes WHERE width='$size' AND status=1";
            $sql="(select id, width
                from     paper_sizes
                where    width >= $size
                order by width asc
                limit 1
                )
                union
                (
                select   id, width
                from     paper_sizes
                where    width < $size
                order by width desc
                limit 1
                )
                order by abs(width - $size)
                limit 1";
            $dbSize=dbselectsingle($sql);
            if($dbSize['numrows']>0)
            {
                $sizeID=$dbSize['data']['id'];
                $size=$dbSize['data']['width'];
            } else {
                $sizeID=0;
            }
            
            //now find a matching order_item_id
            $sql="SELECT * FROM order_items WHERE order_id='$orderid' AND paper_type_id='$paperTypeID' AND size_id='$sizeID'";
            $dbOI=dbselectsingle($sql);
            if($dbOI['numrows']>0)
            {
                $orderitemid=$dbOI['data']['id'];
                $orderid=$dbOI['data']['order_id'];
            } else {
                $orderitemid=0;
                $orderid=0;
            }
            if($unknown!='')
            {
                print "There were $orderitem[count] of $size\" rolls that were unable to be matched for correct paper type.<br>";
                print "The manifest contains the following:<br>";
                print $unknown."<br>\n";
                print "You will need to edit paper types and add one of the grade codes to the appropriate paper type so that auto-matching can work.<br>";
            } else {
                print "Order Item: $paperCommonName - width: $size\" - ".$orderitem['count']." rolls<br>";
            }
        }
        print "Most likely belongs to Order ID $orderid<br><br>";
        
        print "<strong><a href='newsprintOrders.php'>Go to order list</a></strong>";
        die();
    }
    $sql="DELETE FROM edi_order WHERE order_id='$orderid'";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM edi_order_items WHERE order_id='$orderid'";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM edi_rolls WHERE order_id='$orderid'";
    $dbDelete=dbexecutequery($sql);
    
    
    //first create the edi order
    $sql="INSERT INTO edi_order (order_id, manifest_number, order_date) VALUES ('$orderid', '$order[manifest_number]', '$order[manifest_date]')";
    $dbInsert=dbinsertquery($sql);
    if($dbInsert['error']=='')
    {
        //now create the edi order items
        //we will need to figure out the paper type and size
        foreach($order['order_items'] as $key=>$orderitem)
        {
            $gc=$orderitem['grade_code'];
            $gn=$orderitem['grade_name'];
            $sql="SELECT * FROM paper_types WHERE grade_code LIKE '%$gc%' or grade_code LIKE '%$gn%'";
            $dbGrade=dbselectsingle($sql);
            $grade=$dbGrade['data'];
            if($dbGrade['numrows']>0)
            {
                $paperTypeID=$dbGrade['data']['id'];
            } else {
                $paperTypeID=0;
            }
            
            $size=$orderitem['roll_width'];
            //convert as necessary to inches
            switch(strtolower($orderitem['roll_width_units']))
            {
                case "mm":
                $size=mm2inches($size);
                break;
                
                case "cm":
                $size=cm2inches($size);
                break;
                
                default:
                //no change
                break;
                
            }
            //$sql="SELECT * FROM paper_sizes WHERE width='$size' AND status=1";
            $sql="(select id, width
                from     paper_sizes
                where    width >= $size
                order by width asc
                limit 1
                )
                union
                (
                select   id, width
                from     paper_sizes
                where    width < $size
                order by width desc
                limit 1
                )
                order by abs(width - $size)
                limit 1";
            $dbSize=dbselectsingle($sql);
            if($dbSize['numrows']>0)
            {
                $sizeID=$dbSize['data']['id'];
                $size=$dbSize['data']['width'];
            } else {
                $sizeID=0;
            }
            
            //now find a matching order_item_id
            $sql="SELECT * FROM order_items WHERE order_id='$orderid' AND paper_type_id='$paperTypeID' AND size_id='$sizeID'";
            $dbOI=dbselectsingle($sql);
            if($dbOI['numrows']>0)
            {
                $orderitemid=$dbOI['data']['id'];
            } else {
                $orderitemid=0;
            }
            
            $sql="INSERT INTO edi_order_items (order_id, order_item_id, paper_type_id, size_id, tonnage_request, itemdisplay_order, imported, site_id) VALUES
            ('$orderid', '$orderitemid', '$paperTypeID', '$sizeID', '$orderitem[item_gross]', '$key', '1', '$siteID')";
            $dbEDIitem=dbinsertquery($sql);
            $itemID=$dbEDIitem['insertid'];
            if($dbEDIitem['error']=='')
            {
                //now create records for each roll
                foreach($orderitem['rolls'] as $key=>$roll)
                {
                    $sql="INSERT INTO edi_rolls (imported, order_id, order_item_id, common_name, roll_width, paper_brightness, paper_weight, status, roll_tag, butt_roll, roll_weight, parent_tag, manifest_number, site_id, validated) VALUES ('1', '$orderid', '$itemID', '$grade[common_name]', '$size', '$grade[paper_brightness]', '$grade[paper_weight]', 1, '$roll[roll_tag]', 0, '$roll[roll_weight]', '', '$order[manifest_number]', '$siteID', 1)";
                    $dbRoll=dbinsertquery($sql);
                    if($dbRoll['error']!='')
                    {
                        print "<b>There was a problem creating the roll. $sql<br></b>";
                    }
                }
            } else {
                print "<b>There was a problem creating the edi order item. $sql<br></b>";
            }
        }
    } else {
        print "<b>There was a problem creating the EDI order record</b><br>";
    }
    print "<p>Manifest import has been completed.</p><p><a href='?action=list'>Return to order list</a>, <a href='newsprintReceive.php?action=printedi&orderid=$orderid'>print the EDI manifest out</a> or <a href='newsprintReceive.php?action=verifyedi&orderid=$orderid'>Verify the receipt of all rolls on this manifest</a></p>";
}
footer();
?>
