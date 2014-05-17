<?php
//<!--VERSION: .9 **||**-->

include("includes/mainmenu.php") ;
//error_reporting(E_ALL);
//define some regularly used arrays
$postatuses=array("killed"=>"Killed","reserved"=>"Reserved","pending"=>"Pending Approval","released"=>"Released","received"=>"Received");
//build a list of vendors
$sql="SELECT * FROM accounts WHERE account_vendor=1 ORDER BY account_name";
$dbVendors=dbselectmulti($sql);
$vendors=array();
$vendors[0]='Please select vendor';
if ($dbVendors['numrows']>0)
{
    foreach($dbVendors['data'] as $vendor)
    {
        $vendors[$vendor['id']]=$vendor['account_name'];
    }
}
    
if ($_POST)
{
    $action=$_POST['submitbutton'];
} elseif ($_GET['action'])
{
    $action=$_GET['action'];
}

switch ($action)
{
    case "add":
    po('add');
    break;
    
    case "edit":
    po('edit');
    break;
    
    case "delete":
    po('delete');
    break;
    
    case "list":
    po('list');
    break;
    
    case "approve":
    approve_po('list');
    break;
    
    case "receive":
    receive_po('list');
    break;
    
    case "Save PO":
    save_po('insert');
    break;
    
    case "Update PO":
    save_po('update');
    break;
    
    case "Approve PO":
    check_approval();
    break;
    
    case "Complete Order":
    po('list');
    break;
    
    default:
    po('list');
    break;
    
}

function approve_po()
{
    global $postatuses, $vendors, $departments;
    $poid=intval($_GET['poid']); 
    $sql="SELECT * FROM purchase_orders WHERE id=$poid";
    $dbPO=dbselectsingle($sql);
    $po=$dbPO['data'];
    $departmentid=$po['department_id'];
    $vendorid=$po['vendor_id'];
    $ordersubtotal=$po['order_subtotal'];
    $ordershipping=$po['order_shipping'];
    $ordertax=$po['order_tax'];
    $ordertotal=$po['order_total'];
    $status=$po['order_status'];
    $emailpo=$po['email_po'];
    $directorapproval=$po['director_approval'];
    $financeapproval=$po['finance_approval'];
    $publisherapproval=$po['publisher_approval'];
    print "<form method=post>\n";
    print "<div class='label'>PO #</div>\n<div class='input'>$poid</div>\n<div class='clear'></div>\n";
    print "<div class='label'>Status</div>\n<div class='input'>$postatuses[$status]</div>\n<div class='clear'></div>\n";
    print "<div class='label'>Required Approval</div>\n<div class='input'>";
    if ($directorapproval){"Department director ";}
    if ($financeapproval){"Finance director ";}
    if ($publisherapproval){"Publisher ";}
    print "</div>\n<div class='clear'></div>\n";
    print "<div class='label'>Department</div>\n<div class='input'>$departments[$departmentid]</div>\n<div class='clear'></div>\n";
    print "<div class='label'>Vendor</div>\n<div class='input'>$vendors[$vendorid]</div>\n<div class='clear'></div>\n";
    print "<div class='label'>PO Items</div><div class='input'>\n";
    print "<fieldset style='width:900px;'>\n";
    print "<legend>Purchase Order Items</legend>\n";
    print "<div id='poitems'>\n";
    
    $sql="SELECT * FROM purchase_order_items WHERE po_id=$poid";
            
    $dbItems=dbselectmulti($sql);
    if ($dbItems['numrows']>0)
    {
        foreach ($dbItems['data'] as $item)
        {
            $pid=$item['id'];
            $name=$item['part_name'];
            $taxable=$item['part_taxable'];
            $pnumber=$item['part_number'];
            $qty=$item['part_quantity'];
            $cost=$item['part_cost'];
            $linetotal=$item['line_cost'];
            print "<div id='lineitem_$pid' class='inventoryLine'>\n";
            print "<div class='partName'>$name<input type='hidden' id='partname_$pid' name='partname_$pid' value='$name'><input type='hidden' id='taxable_$pid' name='taxable_$pid' value='$taxable'></div>\n";
            print "<div id='part_$pid' name='part_$pid' class='partNumber'>P/N: $number&nbsp;<input type='hidden' id='partnumber_$pid' name='partnumber_$pid' value='$pnumber'></div>\n";
            print "<div class='partQty'>Qty: <input id='qty_$pid' name='qty_$pid' type='text' value='$qty' size=10 onkeypress='return isNumberKey(event);' onblur='calculatePOLine(\"$pid\");' /></div>\n";
            print "<div class='partPrice'>Unit cost: \$<input id='unit_$pid' name='unit_$pid' type='text' value='$cost' size=10 onkeypress='return isNumberKey(event);' onblur='calculatePOLine(\"$pid\");' /></div>\n";
            print "<div class='partTotal'>Line total: \$<input id='linetotal_$pid' size=10 name='linetotal_$pid' class='polinetotal' type='text' value='$linetotal' readonly/></div>\n";
            print "</div><div class='clear'></div>\n";
        }
    }
    print "</div>\n";
    print "</fieldset>\n";
    print "</div><div class='clear'></div>\n";
    make_hidden('poid',$poid);
    make_submit('submitbutton','Approve PO');
    print "</form>\n";
    
}

function check_approval()
{
    global $siteID;
    $sql="SELECT publisherID, financeDepartmentID FROM core_preferences WHERE site_id=$siteID";
    $dbPref=dbselectsingle($sql);
    $publisherid=$dbPref['data']['publisherID'];
    $financeDept=$dbPref['data']['financeDepartmentID'];
    
    
    $poid=$_POST['poid'];
    $userid=$_SESSION['cmsuser']['userid'];
    
    //ok, lets figure out which approvals are needed
    $sql="SELECT * FROM purchase_orders WHERE id=$poid";
    $dbPO=dbselectsingle($sql);
    $po=$dbPO['data'];
    
    $directorapproval=$po['director_approval'];
    $financeapproval=$po['finance_approval'];
    $publisherapproval=$po['publisher_approval'];
    $departmentid=$po['department_id'];
    $emailsent=$po['email_sent'];
    $emailpo=$po['email_po'];
    $vendorid=$po['vendor_id'];
    
    
    $sql="SELECT * FROM user_departments WHERE id=$departmentid";
    $dbCheckDept=dbselectsingle($sql);
    if ($dbCheckDept['numrows']>0)
    {
        if ($dbCheckDept['data']['parent_id']!=0)
        {
            $checkdep=$dbCheckDept['data']['parent_id'];
            $sql="SELECT * FROM user_departments WHERE parent_id=$checkdep";
            $dbSubs=dbselectmulti($sql);
            if ($dbSubs['numrows']>0)
            {
                $departmentid=$checkdep;
                foreach($dbSubs['data'] as $sub)
                {
                    $departmentid.=",".$sub['id'];
                }   
            }    
        }
        
    }
    $sql="SELECT A.id, A.email FROM users A, user_positions B WHERE A.department_id IN($departmentid) AND A.position_id=B.id AND B.director=1";
    $dbDirector=dbselectsingle($sql);
    $directorid=$dbDirector['data']['id'];
    
    //finally, get the id of the finance department director
    $sql="SELECT A.id, A.email FROM users A, user_positions B WHERE A.department_id=($financeDept) AND A.position_id=B.id AND B.director=1";
    $dbDirector=dbselectsingle($sql);
    $financeid=$dbDirector['data']['id'];
    $dap='';
    //ok, now do the checking
    if ($userid==$directorid){$directorapproved=1;$dap='director_approved=1, ';$message.="Approved by department director<br>";}else{$directorapproved=0;}
    if ($userid==$financeid){$financeapproved=1;$dap.='finance_approved=1, ';$message.="Approved by finance director<br>";}else{$financeapproved=0;}
    if ($userid==$publisherid){$directorapproved=1;$dap.='publisher_approved=1, ';$message.="Approved by the publisher<br>";}else{$publisherapproved=0;}
    $emailwassent=0;
    //ok, lets see if we match up
    if ($directorapproved==$directorapproval && $financeapproved==$financeapproval && $publisherapproved==$publisherapproval)
    {
        $status="order_status='released',";
        //send the email to the vendor now
        $message.="Released for ordering";
        if ($emailpo==1)
        {
            mailPO($vendorid,$poid);
            $emailwassent=1;
        }
    }
    
    
    //update the record
    $sql="UPDATE purchase_orders SET $dap $status email_sent='$emailwassent' WHERE id=$poid";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    if($error!='')
    {
        setUserMessage('There was a problem in the approval process.<br />'.$error,'error');
    } else {
        setUserMessage($message,'success');
    }
    
    redirect("?action=list");
        
}


function receive_po()
{
    global $postatuses, $vendors, $departments;
    $poid=intval($_GET['poid']); 
    $sql="SELECT * FROM purchase_orders WHERE id=$poid";
    $dbPO=dbselectsingle($sql);
    $po=$dbPO['data'];
    $departmentid=$po['department_id'];
    $vendorid=$po['vendor_id'];
    $ordersubtotal=$po['order_subtotal'];
    $ordershipping=$po['order_shipping'];
    $ordertax=$po['order_tax'];
    $ordertotal=$po['order_total'];
    $status=$po['order_status'];
    $emailpo=$po['email_po'];
    print "<form method=post>\n";
    print "<div class='label'>PO #</div>\n<div class='input'>$poid</div>\n<div class='clear'></div>\n";
    print "<div class='label'>Status</div>\n<div class='input'>$postatuses[$status]</div>\n<div class='clear'></div>\n";
    print "<div class='label'>Department</div>\n<div class='input'>$departments[$departmentid]</div>\n<div class='clear'></div>\n";
    print "<div class='label'>Vendor</div>\n<div class='input'>$vendors[$vendorid]</div>\n<div class='clear'></div>\n";
   print "<div class='label'>PO Items</div><div class='input'>\n";
    print "<fieldset style='width:900px;'>\n";
    print "<legend>Purchase Order Items</legend>\n";
    print "<div id='poitems'>\n";
    
    $sql="SELECT * FROM purchase_order_items WHERE po_id=$poid";
            
    $dbItems=dbselectmulti($sql);
    if ($dbItems['numrows']>0)
    {
        //build the select box
        $sql="SELECT * FROM general_ledgers ORDER BY gl_number ASC";
        $dbGL=dbselectmulti($sql);
        $gls=array();
        if($dbGL['numrows']>0)
        {
            foreach($dbGL['data'] as $gl)
            {
                $gls[$gl['id']]=$gl['gl_number'].' - '.$gl['gl_description'];
            }
        }
        
        
        foreach ($dbItems['data'] as $item)
        {
            $pid=$item['part_id'];
            $name=$item['part_name'];
            $taxable=$item['part_taxable'];
            $pnumber=$item['part_number'];
            $qty=$item['part_quantity'];
            $rec=$item['receive_quantity'];
            $cost=$item['part_cost'];
            $linetotal=$item['line_cost'];
            $gl=$item['gl_number'];
            print "<div id='lineitem_$pid' class='inventoryLine'>\n";
            print "<div class='partName'>$name</div><div class='clear'></div>\n";
            print "<div class='partGL'>GL# ";
            print make_select('partgl_'.$pid,$gls[$gl],$gls);
            print "</div>\n";
            print "<div class='partQty' style='width:80px;padding-top:4px;'>Ordered: $qty<input id='qty_$pid' name='qty_$pid' type='hidden' value='$qty''/></div>\n";
            print "<div class='partQty'>Received: <input id='received_$pid' name='received_$pid' type='text' value='$rec' size=5 onkeypress='return isNumberKey(event);' /></div>\n";
            print "<div style='float:left;margin-top:-10px'><input type='button' class='receivebutton' value='Receive' onclick='receiveInventoryItem($pid,$poid);'></div>\n";
            print "<div id='ok_$pid' style='float:left;display:none;'><img src='artwork/icons/accepted_48.png' border=0 height=24></div>\n";
            print "<div id='error_$pid' style='float:left;display:none;'><img src='artwork/icons/warning_48.png' border=0 height=24></div>\n";
            print "</div><div class='clear'></div>\n";
        }
    }
    print "</div>\n";
    print "</fieldset>\n";
    print "</div><div class='clear'></div>\n";
    make_submit('submitbutton','Complete Order');    
    make_hidden('poid',$poid);
    print "</form>\n";
}


function po($action)
{
    global $departments, $siteID, $poststat, $vendors, $postatuses;
    $poid=intval($_GET['poid']);
    
    if ($action=='add' || $action=='edit')
    {
        $button='Save PO';
        if ($action=='add')
        {
            $vendorid=0;
            $ordersubtotal='0.00';
            $ordershipping='0.00';
            $ordertax='0.00';
            $ordertotal='0.00';
            $emailpo=0;
            //in order to have a PO number, we need to create a record with a status of reserved. If the order gets saved
            //we'll update the status to placed. We'll need a function to run every night and delete all "reserved" pos
            //so they dont  hang out forever
            $sql="INSERT INTO purchase_orders (order_status, site_id) VALUES ('reserved', $siteID)";
            $dbInsert=dbinsertquery($sql);
            $poid=$dbInsert['numrows'];
            $status='reserved';
            $departmentid=0;
        } else {
            $sql="SELECT * FROM purchase_orders WHERE id=$poid";
            $dbPO=dbselectsingle($sql);
            $po=$dbPO['data'];
            $departmentid=$po['department_id'];
            $vendorid=$po['vendor_id'];
            $ordersubtotal=$po['order_subtotal'];
            $ordershipping=$po['order_shipping'];
            $ordertax=$po['order_tax'];
            $ordertotal=$po['order_total'];
            $status=$po['order_status'];
            $emailpo=$po['email_po'];
        }
        print "<form method=post>\n";
        print "<div id='leftside' style='float:left;'>\n";
        print "<div class='label'>PO #</div>\n<div class='input'>$poid</div>\n<div class='clear'></div>\n";
        print "<div class='label'>Order Status</div><div class='input'>\n";
        print "<span style='font-size:12px;'>$postatuses[$status]<input type='hidden' id='status' name='status' value='$status'></span></div>\n<div class='clear'></div>\n";
        make_select('departmentid',$departments[$departmentid],$departments,'Department','Which department is this PO for?');
        make_select('vendorid',$vendors[$vendorid],$vendors,'Vendor');
        make_checkbox('email_po',$emailpo,'Email PO','Send an email po to the vendor when the PO is complete and approved.');
        print "<div class='label'>Select part or service</div><div class='input'>\n";
        print "<div style='float:left;width:170px;font-size:14px;font-weight:bold'>Search by part name:</div> ";
            print "<div style='float:left;'><input type='text' name='spartname' id='spartname'>\n";
            print "<input type='button' value='Add part' onclick='addInventoryItem(\"name\");'>\n";
            print "<input type='hidden' name='spartname_ID' id='spartname_ID'>\n";
            print "</div><div class='clear'></div>\n";
        
        print "<div style='float:left;width:170px;font-size:14px;font-weight:bold'>Search by part number:</div> ";
            print "<div style='float:left;'><input type='text' name='spartnumber' id='spartnumber'>\n";
            print "<input type='hidden' name='spartnumber_ID' id='spartnumber_ID'>\n";
            print "<input type='button' value='Add part' onclick='addInventoryItem(\"number\");'>\n";
            print "</div><div class='clear'></div>\n";
        
        print "<div style='float:left;width:170px;font-size:14px;font-weight:bold'>Search by service name:</div> ";
            print "<div style='float:left;'><input type='text' name='spartpm' id='spartpm' >\n";
            print "<input type='button' value='Add part' onclick='addInventoryItem(\"service\");'>\n";
            print "<input type='hidden' name='sservicename_ID' id='sservicename_ID'>\n";
            print "</div><div class='clear'></div>\n";
        
        print "</div><div class='clear'></div>\n";
        print "</div>\n";
        ?>
        <script>
        $(document).ready(function() {
            $("#spartname").autocomplete({
                source: 'includes/ajax_handlers/poPartLookup.php?action=lookup&type=name',
                select: function(event, ui) {
                    $('#spartname_ID').val(ui.item.id)
                }
            });
        })
        </script>
        <?php
        
        //****************new part box
        print "<div style='float:right;margin-right:80px;border:1px solid #AC1D23;background-color:#FEFE78;padding:10px;'>\n";
        print "<div style='float:left;margin-left:4px;'><img src='artwork/icons/new_part_48.png' border=0></div><div style='font-weight:bold;font-size:18px;padding-left:4px;padding-top:8px;'>Create a new part</div><div class='clear'></div>\n";
        print "<div style='float:left;font-weight:bold;width:80px;'>Name: </div><div style='float:left;'><input type='text' id='newpartname' /></div><div class='clear'></div>\n";
        print "<div style='float:left;font-weight:bold;width:80px;'>Cost: </div><div style='float:left;'><input type='text' id='newpartcost' value='0.00' onkeypress='return isNumberKey(event);'/></div><div class='clear'></div>\n";
        print "<div style='float:left;font-weight:bold;width:80px;'>Part Number: </div><div style='float:left;'><input type='text' id='newpartnumber' /></div><div class='clear'></div>\n";
        print "<div style='float:left;font-weight:bold;width:80px;'>Taxable: </div><div style='float:left;'><input type='checkbox' id='newparttaxable'/> check if yes</div><div class='clear'></div>\n";
        print "<input style='margin-left:80px;'type='button' class='submit' onclick='addNewPartFromPO();' value='Add new part'></span>\n";
        print "</div>\n";
        print "<div class='clear'></div>\n";
        
        print "<div class='label'>PO Items</div><div class='input'>\n";
        print "<fieldset style='width:800px;'>\n";
        print "<legend>Purchase Order Items</legend>\n";
        print "<div id='poitems'>\n";
        /********************************************
        * THIS SPACE IS DYNAMICALLY POPULATED AS PARTS ARE ADDED
        * BUT WE WILL LOOK UP THE POSSIBLE ITEMS IF WE ARE EDITING
        */
        if ($action=='edit')
        {
            $sql="SELECT * FROM purchase_order_items WHERE po_id=$poid";
            $dbItems=dbselectmulti($sql);
            if ($dbItems['numrows']>0)
            {
                //build the select box
                $sql="SELECT * FROM general_ledgers ORDER BY gl_number ASC";
                $dbGL=dbselectmulti($sql);
                $gls=array();
                if($dbGL['numrows']>0)
                {
                    foreach($dbGL['data'] as $gl)
                    {
                        $gls[$gl['id']]=$gl['gl_number'].' - '.$gl['gl_description'];
                    }
                }
        
                
                foreach ($dbItems['data'] as $item)
                {
                    $pid=$item['part_id'];
                    $name=$item['part_name'];
                    $taxable=$item['part_taxable'];
                    $pnumber=$item['part_number'];
                    $qty=$item['part_quantity'];
                    $cost=$item['part_cost'];
                    $gl=$item['gl_number'];
                    $linetotal=$item['line_cost'];
                    print "<div id='lineitem_$pid' class='inventoryLine'>\n";
                    print "<div class='partName'><a href='#' onclick=\"window.open('partPopup.php?partid=$pid','Part Viewer','width=600,height=650,toolbar=no,status=no,location=no,scrollbars=no');return false;\">$name</a><input type='hidden' id='partname_$pid' name='partname_$pid' value='$name'><input type='hidden' id='taxable_$pid' name='taxable_$pid' value='$taxable'>\n";
                    print "<input type='hidden' id='partnumber_$pid' name='partnumber_$pid' value='$pnumber'>\n";
                    print "</div><div class='clear'></div>";
                    print "<div class='partGL'>GL# ";
                    print make_select('partgl_'.$pid,$gls[$gl],$gls);
                    print "</div>\n";
                    print "<div class='partQty'>Qty: <input id='qty_$pid' name='qty_$pid' type='text' value='$qty' size=10 onkeypress='return isNumberKey(event);' onblur='calculatePOLine(\"$pid\");' /></div>\n";
                    print "<div class='partPrice'>Unit cost: \$<input id='unit_$pid' name='unit_$pid' type='text' value='$cost' size=10 onkeypress='return isNumberKey(event);' onblur='calculatePOLine(\"$pid\");' /></div>\n";
                    print "<div class='partTotal'>Line total: \$<input id='linetotal_$pid' size=10 name='linetotal_$pid' class='polinetotal' type='text' value='$linetotal' readonly/></div>\n";
                    print "<img src='artwork/icons/cancel_48.png' border=0 height=24 onclick='deleteInventoryItem($pid);'>";
                    print "</div><div class='clear'></div>\n";
                }
            }
        }
        
        
        
        print "</div>\n";
        print "</fieldset>\n";
        print "</div><div class='clear'></div>\n";
        print "<div style='float:right;margin-right:80px;margin-top:10px;'>\n";
        print "<div style='float:left;font-weight:bold;margin-right:10px;width:100px;padding-top:4px;height:28px;'>Subtotal: </div>\n";
        print "<div style='float:left;border:1px solid #AC1D23;width:196px;height:28px;margin-bottom:4px;'>\n";
        print "<input style='width:192px;height:22px;background-color:#FFDB4F;' type='text' id='subtotal' name='subtotal' value='$ordersubtotal' readonly />\n</div>\n";
        print "<br />\n";
        
        print "<div style='float:left;font-weight:bold;margin-right:10px;width:100px;padding-top:4px;height:28px;'>Tax: </div>\n";
        print "<div style='float:left;border:1px solid #AC1D23;width:196px;height:28px;margin-bottom:4px;'>\n";
        print "<input style='width:192px;height:22px;background-color:#FFDB4F;' type='text' id='tax' name='tax' value='$ordertax' readonly /></div>\n";
        print "<br />\n";
        
        print "<div style='float:left;font-weight:bold;margin-right:10px;width:100px;padding-top:4px;height:28px;'>Shipping: </div>\n";
        print "<div style='float:left;border:1px solid #AC1D23;width:196px;height:28px;margin-bottom:4px;'>\n";
        print "<input style='width:192px;height:22px;background-color:#FFDB4F;' type='text' id='shipping' name='shipping' value='$ordershipping' onBlur='calculatePO();'></div>\n";
        print "<br />\n";
        
        print "<div style='float:left;font-weight:bold;margin-right:10px;width:100px;padding-top:4px;height:28px;'>Total: </div>\n";
        print "<div style='float:left;border:1px solid #AC1D23;width:196px;height:28px;margin-bottom:4px;'>\n";
        print "<input style='width:192px;height:22px;background-color:#FFDB4F;' type='text' id='total' name='total' value='$ordertotal' readonly /></div>\n";
        print "</div>\n";
        print "<div class='clear'></div>\n";
        make_hidden('poid',$poid);
        make_submit('submitbutton','Save PO');
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $sql="DELETE FROM purchase_orders WHERE id=$poid";
        $dbDelete=dbexecutequery($sql);
        $sql="DELETE FROM purchase_orders_items WHERE po_id=$poid";
        $dbDelete=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM purchase_orders WHERE site_id=$siteID AND order_status<>'reserved' ORDER BY order_datetime DESC";
        $dbPOS=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new Purchase Order</a>","PO #,Vendor,Order Date,Status",8);
        if ($dbPOS['numrows']>0)
        {
            foreach ($dbPOS['data'] as $po)
            {
                $id=$po['id'];
                $status=$po['order_status'];
                $odate=date("m/d/Y H:i",strtotime($po['order_datetime']));
                $vendor=$vendors[$po['vendor_id']];
                print "<tr><td>$id</td>";
                print "<td>$vendor</td>";
                print "<td>$odate</td>";
                print "<td>$status</td>";
                print "<td><a href='?action=edit&poid=$id'>Edit</a></td>";
                print "<td><a href='?action=approve&poid=$id'>Approve</a></td>";
                print "<td><a href='?action=receive&poid=$id'>Receive</a></td>";
                print "<td><a href='?action=delete&poid=$id' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbPOS);
    }
    
}
 
function save_po($action)
{
    $siteID=$GLOBALS['siteID'];
    $sql="SELECT * FROM core_preferences WHERE site_id=$siteID";
    $dbPrefs=dbselectsingle($sql);
    $prefs=$dbPrefs['data'];
    $directorAmount=$prefs['poDirectorAmount'];
    $financeAmount=$prefs['poFinanceAmount'];
    $publisherAmount=$prefs['poPublisherAmount'];
    $poEmailVendor=$prefs['poEmailVendor'];
    $poid=$_POST['poid'];
    $departmentid=$_POST['departmentid'];
    $vendorid=addslashes($_POST['vendorid']);
    $status=$_POST['status'];
    if($_POST['email_po']){$emailpo=1;}else{$emailpo=1;}
    $orderdate=date("Y-m-d H:i:s");
    $orderby=$_SESSION['cmsuser']['userid'];
        
    if($status=='reserved')
    {
        $status='pending';
        $order=", order_datetime='$orderdate', order_by='$orderby'";
    }
    $subtotal=$_POST['subtotal'];
    $tax=$_POST['tax'];
    $shipping=$_POST['shipping'];
    $total=$_POST['total'];
    
    $directorappoval=0;
    $financeapproval=0;
    $publisherapproval=0;
    $directorapproval=0;
    
    //check for approval, if the value is ok, then we'll email if needed.
    if ($total>=$directorAmount){$directorapproval=1;}
    if ($total>=$financeAmount){$financeapproval=1;}
    if ($total>=$publisherAmount){$publisherappoval=1;}
    $sql="UPDATE purchase_orders SET department_id=$departmentid, order_status='$status', order_subtotal='$subtotal', order_tax='$tax', order_shipping='$shipping', order_total='$total', vendor_id='$vendorid', director_approval='$directorapproval', finance_approval='$financeapproval', publisher_approval='$publisherapproval', director_approved=0, finance_approved=0, publisher_approved=0, email_sent=0, email_po='$emailpo' $order WHERE id=$poid";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    
    //delete any existing matching PO ITEMS
    $sql="DELETE FROM purchase_order_items WHERE po_id=$poid";
    $dbDelete=dbexecutequery($sql);
    
    $poitems="INSERT INTO purchase_order_items (po_id, part_id, department_id, order_date, part_name, part_quantity, receive_quantity, part_cost, part_taxable, part_number, line_cost, gl_number) VALUES ";
    //might make sense to also save the them items now :)
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,9)=='partname_')
        {
            $partid=str_replace("partname_","",$key);
            $taxable=$_POST['taxable_'.$partid];
            $partname=$_POST['partname_'.$partid];
            $partnumber=$_POST['partnumber_'.$partid];
            $partquantity=$_POST['qty_'.$partid];
            $unitcost=$_POST['unit_'.$partid];
            $partgl=$_POST['partgl_'.$partid];
            $linetotal=$_POST['linetotal_'.$partid];
            $poitems.="($poid, '$partid', '$departmentid', '$orderdate', '$partname', '$partquantity', '0','$unitcost','$taxable','$partnumber','$linetotal', '$partgl'),";
        }
    }
    $poitems=substr($poitems,0,strlen($poitems)-1);
    $dbInsertNew=dbinsertquery($poitems);
    $error.=$dbInsertNew['error'];
    
    if ($directorapproval==0 && $financeapproval==0 && $publisherapproval==0 && $emailpo==1)
    {
        print "trying to email vendor<br />\n";
        mailPO($vendorid,$poid);
    }
    
    if ($directorapproval==1)
    {
        //first figure out the department director to email
        $sql="SELECT * FROM user_departments WHERE id=$departmentid";
        $dbCheckDept=dbselectsingle($sql);
        if ($dbCheckDept['numrows']>0)
        {
            if ($dbCheckDept['data']['parent_id']!=0)
            {
                $checkdep=$dbCheckDept['data']['parent_id'];
                $sql="SELECT * FROM user_departments WHERE parent_id=$checkdep";
                $dbSubs=dbselectmulti($sql);
                if ($dbSubs['numrows']>0)
                {
                    $departmentid=$checkdep;
                    foreach($dbSubs['data'] as $sub)
                    {
                        $departmentid.=",".$sub['id'];
                    }   
                }    
            }
            
        }
        
        $sql="SELECT A.email FROM users A, user_positions B WHERE A.department_id IN($departmentid) AND A.position_id=B.id AND B.director=1";
        $dbDirector=dbselectsingle($sql);
        $directoremail=$dbDirector['data']['email'];
        if ($directoremail!='')
        {
            $to=$directoremail;
            $subject='You have a PO waiting for approval';
            $message="You have a PO pending approval in the system.\n";
            $message.= "<a href='".$GLOBALS['serverIPaddress'].$GLOBALS['systemRootPath']."purchaseOrders.php?action=approve&poid=$poid'>Click here to approve the purchase order</a>.";
            $from=$GLOBALS['systemEmailFromAddress'];
            $headers = 'From: '.$GLOBALS['systemEmailFromAddress'] . "\r\n" .
                'Reply-To: '.$GLOBALS['systemEmailFromAddress']. "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            
            $message = wordwrap($message, 70);
//$result=mail($to, $subject, $message, $headers);
            $mail = new htmlMimeMail();
        
            $mail->setHtml($message);
            $mail->setFrom($from);
            $mail->setSubject($subject);
            $mail->send(array($to));
            /*
            print "To: ".$directoremail."<br />\n";
            print "From: ".$from."<br />\n";
            print "<br />".$message;
            */
        } 
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the purchase order.<br />'.$error,'error');
    } else {
        setUserMessage('The purchase order has been successfully saved.','success');
    }
    redirect("?action=list");
}


function mailPO($vendorid,$poid)
{
    global $newspaperName, $systemEmailFromAddress;
    $sql="SELECT * FROM purchase_orders WHERE id=$poid";
    $dbPO=dbselectsingle($sql);
    $po=$dbPO['data'];
    $subtotal=$po['order_subtotal'];
    $tax=$po['order_tax'];
    $shipping=$po['order_shipping'];
    $total=$po['order_total'];
    
    $orderedby=$po['order_by'];
    $sql="SELECT * FROM users WHERE id=$orderedby";
    $dbUser=dbselectsingle($sql);
    $user=$dbUser['data'];
    $name=$user['firstname'].' '.$user['lastname'];
    $userphone=$user['business'];
    $useremail=$user['email'];
    
    $sql="SELECT * FROM purchase_order_items WHERE po_id=$poid";
    $dbItems=dbselectmulti($sql);
    if ($dbItems['numrows']>0)
    {
        $sql="SELECT * FROM vendors WHERE id=$vendorid";
        $dbVendor=dbselectsingle($sql);
        if ($dbVendor['data']['email_po']==1 && $dbVendor['data']['po_email_address']!='')
        {
            //ok, everything matches up, lets build the email
            $to=$dbVendor['data']['po_email_address'];
            $subject="PO # $poid from $newspaperName";
            $message="This is an automated email containing information regarding purchase order #$poid from $newspaperName\n\n";
            $message.="The following items are being ordered:\n";
            foreach($dbItems['data'] as $poitem)
            {
                $name=$poitem['part_name'];
                $qty=$poitem['part_quantity'];
                $cost=$poitem['line_cost'];
                $message.=$name.", Qty: $qty, totalling $cost\n";
            }
            $message.="Subtotal: $subtotal\n";
            $message.="Tax: $tax\n";
            $message.="Shipping: $shipping\n";
            $message.="Grand Total: $total\n";
            $message.="\n\nIf you have questions, you can contact $name at $userphone\n";
            $message.="or by email at <a href='mailto:$useremail'>$useremail</a>.";
            print "Sent message to vendor at $to<br />$message<br /><br />\n";
            /*
            $headers = 'From: '.$systemEmailFromAddress . "\r\n" .
                'Reply-To: '.$useremail. "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            $result=mail($to, $subject, $message, $headers);
            */
            $from=$GLOBALS['systemEmailFromAddress'];
            $mail = new htmlMimeMail();
            $mail->setHtml($message);
            $mail->setFrom($from);
            $mail->setSubject($subject);
            $mail->send(array($to));
            
            $sql="UPDATE purchase_orders SET email_sent=1 WHERE id=$poid";
            $dbUpdate=dbexecutequery($sql); 
            
        } else {
            setUserMessage('Sorry, but this vendor is not configured to accept email POs');
        }
        
        
             
    }
}

footer();
?>
