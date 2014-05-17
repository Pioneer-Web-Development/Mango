<?php
//<!--VERSION: .9 **||**-->
if($_GET['action']=='printedi')
{
    include("includes/functions_db.php");
    include("includes/functions_common.php");
    include("includes/config.php");  
} else {
    include("includes/mainmenu.php") ;
    
}

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
    switch ($action)
    {
        case "Save Manifest":
        save_manifest();
        break;
        
        case "Search":
        receive_newsprint('list');
        break;
        
        case "rolls":
        receive_newsprint('rolls');
        break;
        
        case "list":
        receive_newsprint('list');
        break;
        
        case "batch":
        batch_import();
        break;
        
        case "Import Batch":
        process_batch_import();
        break;
        
        case "complete":
        complete_order();
        break;
        
        case "verifyedi":
        verify_edi();
        break;
        
        case "printedi":
        print_edi();
        break;
        
        case "deleteedi":
        delete_edi();
        break;
        
        case "Verify EDI":
        process_edi_verify();
        break;
        
        case "edi":
        show_edi();
        break;
        
        default:
        receive_newsprint('list');
        break;
        
    } 

function delete_edi()
{
    $orderid=intval($_GET['orderid']);
    $sql="DELETE FROM edi_order WHERE order_id='$orderid'";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM edi_order_items WHERE order_id='$orderid'";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM edi_rolls WHERE order_id='$orderid'";
    $dbDelete=dbexecutequery($sql);
    setUserMessage('EDI manifest has been removed','success');
    redirect("?action=edi");
}
    
function batch_import()
{
    $orderid=intval($_GET['orderid']);
    $itemid=intval($_GET['itemid']);
    
    $date=date("Ymd");
    print "<form method=post enctype='multipart/form-data'>\n";
    make_date('batch_date',$date,'Batch Date','What date should be used for these rolls?');
    make_text('manifest_number','batch_'.$date,'Manifest #','What manifest number should we use?');
    make_file('edi','Batch Import File','Please select the CSV file containing the rolls tags and weights<br>Format of file should be Rolltag,Weight(kg) with one roll per line.');
    make_hidden('orderid',$orderid);
    make_hidden('itemid',$itemid);
    make_submit('submit','Import Batch');
    print "</form>\n"; 
}   

function process_batch_import()
{
    global $siteID;
    $file=$_FILES['edi']['tmp_name'];
   $contents=file_get_contents($file);
   $lines=explode("\n",$contents);
   $manifest=$_POST['manifest_number'];
   $bdate=$_POST['batch_date'];
   $orderid=$_POST['orderid'];  
   $itemid=$_POST['itemid'];
   
   //get the paper type info (common name, roll width and brightness for the rolls);
   $sql="SELECT A.*, B.common_name, C.width FROM order_items A, paper_types B, paper_sizes C WHERE A.id=$itemid AND A.paper_type_id=B.id AND A.size_id=C.id";
   $dbOrderInfo=dbselectsingle($sql); 
   if(count($lines)>0)
   {
       $orderinfo=$dbOrderInfo['data'];
       foreach($lines as $line)
       {
            //explode the line by comma. rolltag should be in first column, weight in second
            $parts=explode(",",$line);
            $rolltag=$parts[0];
            $weight=$parts[1];
            $rolltag=trim($rolltag);
            $weight=trim($weight);
            //check first to make sure that rolltag hasn't been already added to the database
            $sql="SELECT * FROM rolls WHERE roll_tag='$rolltag'";
            $dbCheck=dbselectsingle($sql);
            if($dbCheck['numrows']==0)
            {
                //add it to the database
                $sql="INSERT INTO rolls (order_id, order_item_id, common_name, roll_width, paper_brightness, paper_weight, status, receive_datetime, 
                roll_tag, butt_roll, roll_weight, parent_tag, manifest_number, site_id, validated, imported) VALUES ('$orderid', '$itemid', 
                '$orderinfo[common_name]', '$orderinfo[roll_width]', '$orderinfo[paper_brightness]', '$orderinfo[paper_weight]', 1, '$bdate', 
                '$rolltag', 0, '$weight', '', '$manifest', '$siteID', 1, 1)";
                $dbInsertRoll=dbinsertquery($sql);
                if($dbInsertRoll['error']!='')
                {
                    print "There was a problem inserting the  roll into the roll table.<br>&nbsp;&nbsp;&nbsp;$sql<br>";
                }
                
            } else {
                print "Rolltag $rolltag was already in the roll database. You may be uploading old records.<br>";
            }
       }
   } else {
       print "Sorry, the file was empty.<br>";
   }
   print "<br><a href='?action=list'>Return to list</a>"; 
} 
    
function show_edi()
{
    global $orderstatuses, $ordersources, $siteID;
    $sql="SELECT * FROM vendors WHERE site_id=$siteID AND newsprint=1 ORDER BY vendor_name";
    $dbVendors=dbselectmulti($sql);
    $vendors=array();
    $vendors[0]="Choose Vendor";
    if ($dbVendors['numrows']>0)
    {
        foreach($dbVendors['data'] as $vendor)
        {
            $vendors[$vendor['id']]=$vendor['vendor_name'];
        }
    }
        
        //display a selection of all edi orders that haven't been received yet.
    $sql="SELECT A.*, B.vendor_name, C.order_source, C.order_datetime, C.status FROM edi_order A, orders C, vendors B 
    WHERE A.order_id=C.id AND C.vendor_id=B.id";
    $dbOrders=dbselectmulti($sql);
    //    print $sql;    
    tableStart("<a href='?action=list'>Return to order list</a>,<a href='newsprintOrders.php'>Import a new EDI manifest</a>",'Order,Date',5);
    if ($dbOrders['numrows']>0)
    {
        foreach($dbOrders['data'] as $order)
        {
            print "<tr>";
            $orderid=$order['order_id'];
            $vendorname=$order['vendor_name'];
            $os=$order['order_source'];
            //get all the item
            $sql="SELECT A.itemdisplay_order, A.id as itemid, A.tonnage_request, B.common_name, B.paper_weight, B.paper_brightness, C.width FROM order_items A, paper_types B, paper_sizes C WHERE A.order_id=$orderid AND A.paper_type_id=B.id AND A.size_id=C.id ORDER BY A.itemdisplay_order";
            $dbItems=dbselectmulti($sql);
            $orderdate=date("m/d/Y",strtotime($order['order_datetime']));
            print "<td>Order # $orderid, ordered on $orderdate from $vendorname for $os - current status ".$orderstatuses[$order['status']]."</td>\n";
            print "<td><a href='?action=deleteedi&orderid=$orderid'>Delete EDI manifest</a></td>";    
            print "<td><a href='?action=printedi&orderid=$orderid'>Print EDI manifest</a></td>";    
            print "<td><a href='?action=verifyedi&orderid=$orderid'>Verify EDI manifest</a></td>";    
            print "</tr>\n";
        }
    }
    tableEnd($dbOrders);
}

    
function complete_order()
{
    $orderid=$_GET['orderid'];
    $sql="UPDATE orders SET status=4 WHERE id=$orderid";
    $dbUpdate=dbexecutequery($sql);
    redirect("?action-list");
    
} 
    
function receive_newsprint($action)
{
    global $orderstatuses, $ordersources, $siteID;
    if ($action=='rolls')
    {
        //here is where we receive individuals rolls for a particular order and size/type combo
        $order=intval($_GET['orderid']);
        $item=intval($_GET['itemid']);
        $menu=$_GET['menu'];
        //by default, we'll load in all rolls matching, so editing will be in the same place as adding
        
        //get the order date to use as the default receive date
        $sql="SELECT * FROM orders WHERE id=$order";
        $dbOrder=dbselectsingle($sql);
        $orderdate=$dbOrder['data']['order_datetime'];
        //get the setup info, paper type, size, & info
        $sql="SELECT B.common_name, B.paper_weight, B.paper_brightness, B.id as typeid, C.id as sizeid, C.width FROM order_items A, paper_types B, paper_sizes C WHERE A.id=$item AND A.paper_type_id=B.id AND A.size_id=C.id";
        $dbInfo=dbselectsingle($sql);
        $info=$dbInfo['data'];
        
        //now get any potential existing rolls
        $sql="SELECT * FROM rolls WHERE order_item_id=$item AND status<>99";
        $dbRolls=dbselectmulti($sql);
        print "<form action=\"$_SERVER[PHP_SELF]\" method=post>\n";
        print "<p>Please enter roll tags and weights for the rolls for this order.<br>This is for $info[common_name], $info[paper_weight] gsm $info[paper_brightness] bright, roll width of $info[width] inches.</p>\n";
        $mannum=$dbRolls['data'][0]['manifest_number'];
        make_text('manifest_number',$mannum,'Bill of Lading (BOL) Number:');
        make_date('receive_date',$orderdate,'Received date');
        print "<br>\n";
        if($dbRolls['numrows']>0)
        {
            foreach($dbRolls['data'] as $roll)
            {
                print "Roll tag: ".input_text('roll_'.$roll['id'],$roll['roll_tag'],20,true,'','','',"newsprintKeyCapture(this.id,event,false,'weight_$roll[id]');return false;");
                print " Weight (kg): ".input_text('weight_'.$roll['id'],$roll['roll_weight'],8,true,'','','',"newsprintKeyCapture(this.id,event,true,'addbusroll');return false;");
                print " <input type=checkbox name='delete_$roll[id]' id='delete_$roll[id]' /> Check to delete";
                print "<br>\n";    
            }
        }
        print "<div id='rolls'>\n";
        print "Roll tag: ".input_text('newroll_1','',20,false,'','','',"newsprintKeyCapture(this.id,event,false,'newweight_1');return false;");
        print " Weight (kg): ".input_text('newweight_1','',8,false,'','','',"newsprintKeyCapture(this.id,event,true,'addbusroll');return false;");
        print " <input type=checkbox name='delete_$roll[id]' id='delete_$roll[id]' /> Check to delete";
        print "<br>\n";
        print "</div>\n";
        print "<input type=\"hidden\" name=\"lastroll\" id=\"lastroll\" value=\"2\" />\n";
        
        print "<input type=button name='addroll' id='addroll' value='Add roll' onClick='addRoll();'/>\n";        
        
        print "<input type=\"hidden\" name=\"menu\" value=\"$menu\" />\n";
        print "<input type=\"hidden\" name=\"itemid\" value=\"$item\" />\n";
        print "<input type=\"hidden\" name=\"orderid\" value=\"$order\" />\n";
        if (!$validated){
            print "<input type=submit name=submit value=\"Save Manifest\" />\n";
        }
        print "</form>\n";
    } else {
        //by default, show all, otherwise, filter by order id, vendor, open/processed, or by date
        //get list of vendors
        global $newsprintVendors;
        //be default, we're going to assume a 6 month set of dates
        if ($_POST['submit']=='Search')
        {
            $enddate=$_POST['enddate'];
            $startdate=$_POST['startdate'];
            if ($_POST['vendor']!=0)
            {
                $searchvendor="AND A.vendor_id=$_POST[vendor]";
            } else {
                $searchvendor="";
            }
            if ($_POST['status']!=0)
            {
                $searchstatus="AND A.status=$_POST[status]";
            } else {
                $searchstatus="";
            }
             if ($_POST['source']!=0){
                $searchsource=" AND A.order_source=$_POST[source]";
            } else {
                $searchsource="";
            }
            
        } else {
            $enddate=date("Y-m-d");
            $startdate=date("Y-m-d",strtotime('-6 months'));
            $searchstatus="AND A.status=1";
            $searchvendor="";
            $searchsource="";
        }
        $search="<form method=post>\n";
        $search.="<b>Vendor:</b><br />";
        $search.=input_select('vendor',$newsprintVendors[0],$newsprintVendors)."<br>\n";
        $search.="<b>Order Status:</b><br />";
        $search.=input_select('status',$orderstatuses[0],$orderstatuses)."<br>\n";
        $search.="<b>Order Source:</b><br />";
        $search.=input_select('source',$ordersources[0],$ordersources)."<br>\n";
        $search.="<b>Start date:</b><br />";
        $search.=input_date('startdate',$startdate);
        $search.="<br /><b>End date:</b><br />";
        $search.=input_date('enddate',$enddate);
        $search.="<input type='submit' name='submit' value='Search' /></div>\n";
        $search.="</form>\n";
        
        $startdate.=" 00:00:01";
        $enddate.=" 23:59:59";
        
        $sql="SELECT A.*, B.account_name FROM orders A, accounts B WHERE A.site_id=$siteID AND A.order_datetime<='$enddate' AND A.order_datetime>='$startdate' AND A.vendor_id=B.id $searchvendor $searchstatus $searchsource ORDER BY A.order_datetime DESC";
        //print $sql;
        $dbOrders=dbselectmulti($sql);
        tableStart("<a href='?action=edi'>EDI Manifests</a>",'Order,Items',4,$search);
        if ($dbOrders['numrows']>0)
        {
            foreach($dbOrders['data'] as $order)
            {
                $orderid=$order['id'];
                $vendorname=$order['account_name'];
                $os=$order['order_source'];
                //get all the item
                $sql="SELECT A.itemdisplay_order, A.id as itemid, A.tonnage_request, B.common_name, B.paper_weight, B.paper_brightness, C.width FROM order_items A, paper_types B, paper_sizes C WHERE A.order_id=$orderid AND A.paper_type_id=B.id AND A.size_id=C.id ORDER BY A.itemdisplay_order";
                $dbItems=dbselectmulti($sql);
                $orderdate=date("m/d/Y",strtotime($order['order_datetime']));
                print "<tr><td>Order # $order[id], ordered on $orderdate from $vendorname for $os - current status ".$orderstatuses[$order['status']]."</td>\n";
                if ($dbItems['numrows']>0)
                {
                    print "<td>";
                    $actions='';
                    $items='';
                    foreach ($dbItems['data'] as $item)
                    {
                        $recorded="";
                        $sql="SELECT * FROM rolls WHERE order_item_id=$item[itemid] AND status<>99";
                        $dbRolls=dbselectmulti($sql);
                        $mannum=$item['manifest_number'];
                        if ($dbRolls['numrows']>0)
                        {
                            $count=0;
                            $tons=0;
                            $mannum="";
                            foreach($dbRolls['data'] as $roll)
                            {
                                $t=$roll['roll_weight'];
                                $tons+=$t;
                                $count++;
                                if ($roll['manifest_number']!=''){$mannum=$roll['manifest_number'];}
                            }
                            $tons=$tons/1000;
                            $recorded="Rolls of this type recorded so far for manifest #$mannum: $count rolls totalling $tons MT"; 
                        } 
                        
                        $line="$item[common_name], $item[paper_weight] gsm/$item[paper_brightness] brightness/$item[width]<br />Requested: $item[tonnage_request]MT";
                        $items.="$line<br />$recorded<hr>";
                        $actions.="<a href='?action=rolls&orderid=$orderid&itemid=$item[itemid]'>Manage</a><br />\n";
                        $actions.="<a href='?action=batch&orderid=$orderid&itemid=$item[itemid]'>Bulk Import</a><hr>\n";
                        
                    }
                    print $items;
                    print "</td>\n";
                    print "<td>$actions</td>\n";
                    print "<td><a href='?action=complete&orderid=$orderid'>Set order to complete</a></td></tr>\n";
                } else {
                    print "<td>No items have been added to this order yet.</td><td><a href='newsprintOrders.php?action=edit&orderid=$order[id]'>Add item to order</a></td></tr>\n";
                }
            }
        }
        tableEnd($dbOrders);    
    }
}

function save_manifest()
{
    global $siteID;
    $manifest_number=$_POST['manifest_number'];
    $new_number=$_POST['new_number'];
    $orderid=$_POST['orderid'];
    $menu=$_POST['menu'];
    $itemid=$_POST['itemid'];
    $receive=$_POST['receive_date'];
    //get some info about the item so it can be stored
    $sql="SELECT A.paper_type_id, A.size_id, B.common_name as name, B.paper_weight, B.paper_brightness, C.width FROM order_items A, paper_types B, paper_sizes C WHERE A.id=$itemid AND A.paper_type_id=B.id AND A.size_id=C.id";
    $dbItem=dbselectsingle($sql);
    $info=$dbItem['data'];
    //all we need is to worry about new rolls, as we can't modify an existing roll, except to "delete" it, which is just a status change
    //lets go through all existing rolls first to see if any of them are checked
    $deleteids="";
    $rolls="";
    foreach ($_POST as $key=>$value)
    {
        //check for "delete_rollid"
        if (strpos($key,"lete_")>0)
        {
            //means we found a delete item
            $did=str_replace("delete_","",$key);
            $deleteids.="$did,";
        }
    
        //now look for new rolls
        if (strpos($key,"wroll_")>0)
        {
            //have a new roll
            $rollid=str_replace("newroll_","",$key);
            $rolltag=strtoupper($_POST['newroll_'.$rollid]);
            $rollweight=$_POST['newweight_'.$rollid];
            if ($rolltag!="" && $rollweight!="")
            {
            $rolls.="('$manifest_number','$orderid','$itemid','$info[name]', '$info[paper_weight]', '$info[width]', '$info[paper_brightness]', '$receive','$rolltag','$rollweight','',1,0,$siteID),";
            }
        }
    
    }
    $deleteids=substr($deleteids,0,strlen($deleteids)-1);    
    $rolls=substr($rolls,0,strlen($rolls)-1);
    $error="";
    //update the status of all "deleted" rolls
    if ($deleteids!="")
    {
        $usql="UPDATE rolls SET status=99 WHERE id IN ($deleteids)";
        $dbUpdate=dbexecutequery($usql);
        $error.=$dbUpdate['error'];
    }
    if ($rolls!="")
    {
        //now, add new rolls
        $sql="INSERT INTO rolls (manifest_number,order_id, order_item_id, common_name, paper_weight, roll_width, paper_brightness, receive_datetime, roll_tag, roll_weight, parent_tag, status, butt_roll,site_id) VALUES $rolls";
        $dbInsert=dbinsertquery($sql);
        $error.=$dbInsert['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the manifest','error');
    } else {
        setUserMessage('Newsprint manifest successfully saved','success');
    }
    redirect("?action=list");
 
}

function print_edi()
{
    $orderid=intval($_GET['orderid']);
    print "<html><body onload='window.print()'>";
    $orderid=intval($_GET['orderid']);
    $sql="SELECT * FROM edi_order WHERE order_id=$orderid";
    $dbCheck=dbselectsingle($sql);
    if($dbCheck['numrows']>0)
    {
        $ediorder=$dbCheck['data'];
        //get the order items
        $sql="SELECT A.*, B.common_name, C.width FROM edi_order_items A, paper_types B, paper_sizes C WHERE A.order_id=$orderid AND A.paper_type_id=B.id AND A.size_id=C.id";
        $dbEDIitems=dbselectmulti($sql);
        if($dbEDIitems['numrows']>0)
        {
            foreach($dbEDIitems['data'] as $ediitem)
            {
                print "<span style='font-weight:bold;font-size:18px;'>".$ediitem['common_name'].' in '.$ediitem['width'].' width. Total weight '.$ediitem['tonnage_request'].'</span><br>';
                $sql="SELECT * FROM edi_rolls WHERE order_id=$orderid AND order_item_id=$ediitem[id]";
                $dbRolls=dbselectmulti($sql);
                if($dbRolls['numrows']>0)
                {
                    foreach($dbRolls['data'] as $roll)
                    {
                        print "<span style='height:24px;border-bottom:thin solid black;width:800px;margin-bottom:6px;font-size:12px;'>Tag# ".$roll['roll_tag'].' - '.$roll['common_name'].' - weight: '.$roll['roll_weight'].' - width: '.$roll['roll_width']."</span><br>\n";
                    }
                }
                print "<br><br>";
            }
        }   
    }
    print "</body></html>";
}

function verify_edi()
{
    $orderid=intval($_GET['orderid']);
    $sql="SELECT * FROM edi_order WHERE order_id=$orderid";
    $dbCheck=dbselectsingle($sql);
    print "<form method=post enctype='multipart/form-data'>";
    print "<p>To verify these rolls, either click the check box for each verified roll, upload a text file containing a list of roll tags, or paste roll tags into the roll tag box.</p>";
    make_file('rolltags_file','Roll Tags','Select a file containing a list of roll tags from a barcode scanner');
    make_textarea('rolltags_list','','Roll Tags','Paste or enter in a list of verified roll tags',40,3,false);
    make_button('checkall','Check all rolls','Select all','',"checkAllCheckboxes('rolltags','checked');");
    if($dbCheck['numrows']>0)
    {
        $ediorder=$dbCheck['data'];
        //get the order items
        $sql="SELECT A.*, B.common_name, C.width FROM edi_order_items A, paper_types B, paper_sizes C WHERE A.order_id=$orderid AND A.paper_type_id=B.id AND A.size_id=C.id";
        $dbEDIitems=dbselectmulti($sql);
        if($dbEDIitems['numrows']>0)
        {
            print "<div class='label'>Roll Tags</div><div id='rolltags' class='input'>";
            foreach($dbEDIitems['data'] as $ediitem)
            {
                print "<b>".$ediitem['common_name'].' in '.$ediitem['width'].' width. Total weight '.$ediitem['tonnage_request'].'</b><br>';
                $sql="SELECT * FROM edi_rolls WHERE order_id=$orderid AND order_item_id=$ediitem[id]";
                $dbRolls=dbselectmulti($sql);
                if($dbRolls['numrows']>0)
                {
                    print "<b>There are a total of $dbRolls[numrows] rolls of this type.</b><br>";
                    foreach($dbRolls['data'] as $roll)
                    {
                        print "<span><LABEL FOR=roll_$roll[roll_tag]'><input type='checkbox' name='roll_$roll[roll_tag]'>Tag# $roll[roll_tag] - ".$roll['common_name'].' - weight: '.$roll['roll_weight'].' - width: '.$roll['roll_width']."</label></span><br>\n";
                    }
                }
               
            }
            print "</div><div class='clear'></div>\n";
        }   
    }
    make_hidden('orderid',$orderid);
    make_submit('submit','Verify EDI');
    print "</form>\n";
}

function process_edi_verify()
{
    $orderid=$_POST['orderid'];
    
    /*
    What we are going to do is move any found rolls over to the regular rolls table, 
    remove that roll from the edi_rolls table, and make sure the moved roll has the proper order_item_id
    Then, after all rolls are moved, we will check to see if all rolls for that edi_order_item_id are moved,
    if so, we'll delete that order_item. If all order_items are deleted we will delete the edi_order
    */
    $sql="SELECT * FROM edi_order WHERE order_id=$orderid";
    $dbEDIorder=dbselectsingle($sql);
    $EDIorder=$dbEDIorder['data'];
    
    $sql="SELECT A.*, B.common_name, C.width FROM edi_order_items A, paper_types B, paper_sizes C WHERE A.order_id=$orderid AND A.paper_type_id=B.id AND A.size_id=C.id";
    $dbOrderItems=dbselectmulti($sql);
    $orderItems=array();
    foreach($dbOrderItems['data'] as $oi)
    {
        $orderItems[$oi['id']]=$oi['order_item_id'];    
    }
    if(isset($_FILES) && $_FILES['rolltags_file']['tmp_name']!='')
    {
        print "Processing file...<br>";
        //dealing with a text file of roll tags
        $file=$_FILES['rolltags_file']['tmp_name'];
        $contents=file_get_contents($file);
        //break into multiple lines
        $contents=explode("\n",$contents);
        foreach($contents as $key=>$rolltag)
        {
            $rolltag=trim($rolltag);
            //check first to make sure that rolltag hasn't been already added to the database
            $sql="SELECT * FROM rolls WHERE roll_tag='$rolltag'";
            $dbCheck=dbselectsingle($sql);
            if($dbCheck['numrows']==0)
            {
                $sql="SELECT * FROM edi_rolls WHERE roll_tag='$rolltag' AND order_id=$orderid";
                $dbRoll=dbselectsingle($sql);
                if($dbRoll['numrows']>0)
                {
                    $roll=$dbRoll['data'];
                    //add it to the database
                    $sql="INSERT INTO rolls (order_id, order_item_id, common_name, roll_width, paper_brightness, paper_weight, status, receive_datetime, 
                    roll_tag, butt_roll, roll_weight, parent_tag, manifest_number, site_id, validated) VALUES ('$orderid', '".$orderItems[$roll['order_item_id']]."', 
                    '$roll[common_name]', '$roll[roll_width]', '$roll[paper_brightness]', '$roll[paper_weight]', 1, '".date("Y-m-d H:i")."', 
                    '$roll[roll_tag]', 0, '$roll[roll_weight]', '', '$roll[manifest_number]', '$roll[site_id]', 1)";
                    $dbInsertRoll=dbinsertquery($sql);
                    if($dbInsertRoll['error']=='')
                    {
                        //now remove this roll from the edi rolls
                        $sql="DELETE FROM edi_rolls WHERE id=$roll[id]";
                        $dbDelete=dbexecutequery($sql);
                    } else {
                        print "There was a problem inserting the edi roll into the main roll table.<br>&nbsp;&nbsp;&nbsp;$sql<br>";
                    }
                } else {
                    print "Rolltag $rolltag was not found. Please verify that it belongs on this order.<br>";
                }
            } else {
                print "Rolltag $rolltag was already in the roll database. You may be uploading old records.<br>";
            }
        }
    } elseif($_POST['rolltags_list']!='')
    {
        //dealing with data in the text list
        print "Processing list...<br>";
        $contents=explode("\n",$_POST['rolltags_list']);
        foreach($contents as $key=>$rolltag)
        {
            $rolltag=trim($rolltag);
            $sql="SELECT * FROM edi_rolls WHERE roll_tag='$rolltag' AND order_id=$orderid";
            $dbRoll=dbselectsingle($sql);
            if($dbRoll['numrows']>0)
            {
                $roll=$dbRoll['data'];
                //add it to the database
                $sql="INSERT INTO rolls (order_id, order_item_id, common_name, roll_width, paper_brightness, paper_weight, status, receive_datetime, 
                roll_tag, butt_roll, roll_weight, parent_tag, manifest_number, site_id, validated) VALUES ('$orderid', '".$orderItems[$roll['order_item_id']]."', 
                '$roll[common_name]', '$roll[roll_width]', '$roll[paper_brightness]', '$roll[paper_weight]', 1, '".date("Y-m-d H:i")."', 
                '$roll[roll_tag]', 0, '$roll[roll_weight]', '', '$roll[manifest_number]', '$roll[site_id]', 1)";
                $dbInsertRoll=dbinsertquery($sql);
                if($dbInsertRoll['error']=='')
                {
                    //now remove this roll from the edi rolls
                    $sql="DELETE FROM edi_rolls WHERE id=$roll[id]";
                    $dbDelete=dbexecutequery($sql);
                    print "Rolltag $rolltag has been successfully moved to live rolls<br>";
                } else {
                    print "There was a problem inserting the edi roll into the main roll table.<br>&nbsp;&nbsp;&nbsp;$sql<br>";
                }
            } else {
                print "Rolltag $rolltag was not found. Please verify that it belongs on this order.<br>";
            }
        }
    } else {
        //dealing with check boxes
        print "Processing checks...<br>";
        foreach($_POST as $key=>$value)
        {
            if(substr($key,0,5)=='roll_')
            {
                $rolltag=trim(str_replace("roll_","",$key));
                $sql="SELECT * FROM edi_rolls WHERE roll_tag='$rolltag' AND order_id=$orderid";
                $dbRoll=dbselectsingle($sql);
                if($dbRoll['numrows']>0)
                {
                    $roll=$dbRoll['data'];
                    //add it to the database
                    $sql="INSERT INTO rolls (order_id, order_item_id, common_name, roll_width, paper_brightness, paper_weight, status, receive_datetime, 
                    roll_tag, butt_roll, roll_weight, parent_tag, manifest_number, site_id, validated) VALUES ('$orderid', '".$orderItems[$roll['order_item_id']]."', 
                    '$roll[common_name]', '$roll[roll_width]', '$roll[paper_brightness]', '$roll[paper_weight]', 1, '".date("Y-m-d H:i")."', 
                    '$roll[roll_tag]', 0, '$roll[roll_weight]', '', '$roll[manifest_number]', '$roll[site_id]', 1)";
                    $dbInsertRoll=dbinsertquery($sql);
                    if($dbInsertRoll['error']=='')
                    {
                        //now remove this roll from the edi rolls
                        $sql="DELETE FROM edi_rolls WHERE id=$roll[id]";
                        $dbDelete=dbexecutequery($sql);
                        print "Rolltag $rolltag has been successfully moved to live rolls<br>";
                    } else {
                        print "There was a problem inserting the edi roll into the main roll table.<br>&nbsp;&nbsp;&nbsp;$sql<br>";
                    }
                } else {
                    print "Rolltag $rolltag was not found. Please verify that it belongs on this order.<br>";
                }
            }
        }
    }
    
    //ok, lets go through each order item and delete it if those rolls are gone
    foreach($dbOrderItems['data'] as $oi)
    {
        $sql="SELECT * FROM edi_rolls WHERE order_id=$orderid AND order_item_id=$oi[id]";
        $dbCheck=dbselectmulti($sql);
        if($dbCheck['numrows']>0)
        {
            print "<b>$dbCheck[numrows] rolls of ".$oi['common_name'].' in '.$oi['width']." width still are unaccounted for</b><br>";
        } else {
            $sql="DELETE FROM edi_order_items WHERE id=$oi[id]";
            $dbDelete=dbexecutequery($sql);
        }    
    }
    //now check and see if there are any order items still remaining. if not, delete the order and set the real order to "complete"
    $sql="SELECT * FROM edi_order_items WHERE order_id=$orderid";
    $dbCheck=dbselectmulti($sql);
    if($dbCheck['numrows']>0)
    {
        print "There are still order items with outstanding rolls, so this edi order will remain open.<br>";
    } else {
        $sql="DELETE FROM edi_order WHERE order_id=$orderid";
        $dbDelete=dbselectmulti($sql);
        //set the main order to complete
        $sql="UPDATE orders SET status=4 WHERE id=$orderid";
        $dbUpdate=dbexecutequery($sql);
        print "The main order has been set to complete. You are now done with this EDI manifest.<br>";
    }   
}

if($_GET['action']!='printedi')
{
    footer();
}
?>
