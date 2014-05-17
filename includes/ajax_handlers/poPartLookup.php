<?php
  /*************************************
  * THIS SCRIPT IS FOR AJAX RECALL OF PARTS
  * FOR THE PURCHASE ORDER SCRIPT
  */
  include("../functions_db.php");
  include("../config.php");
  global $siteID;
  if($_POST)
  {
      $action=$_POST['action'];
  } else {
      $action=$_GET['action'];
  }
  
  switch($action)
  {
      case 'addPOPart':
       if($_POST['partname']!='')
        {
            $pname=addslashes($_POST['partname']);
            $pnumber=addslashes($_POST['partnumber']);
            $pcost=addslashes($_POST['partcost']);
            $ptaxable=addslashes($_POST['taxable']);
            $sql="INSERT INTO equipment_part (part_name, part_number, part_cost, part_taxable, site_id) VALUES ('$pname','$pnumber','$pcost','$ptaxable', '$siteID')";
            $dbInsert=dbinsertquery($sql);
            if ($dbInsert['error']=='')
            {
                print "success|The part was added successfully.";
            } else {
                print "error|".$dbInsert['error'];
            }
        }
      
      break;
      
      
      case "addpart":
        $type=$_GET['type'];
        $partid=intval($_POST['partid']);
        if ($type=='service')
        {
            $sql="SELECT * FROM equipment_service WHERE id=$partid";
            $dbPart=dbselectsingle($sql);
            if ($dbPart['numrows']>0)
            {
                $part=$dbPart['data'];
                $pid=$part['id'];
                $partname=$part['service_name'];
                $partcost=$part['part_cost'];
                $parttaxable=$part['part_taxable'];
                print "success|";
            } else {
                print "error|";
            } 
        } else {
            $sql="SELECT * FROM equipment_part WHERE id=$partid";
            $dbPart=dbselectsingle($sql);
            if ($dbPart['numrows']>0)
            {
                $part=$dbPart['data'];
                $pid=$part['id'];
                $partname=$part['part_name'];
                $partnumber=$part['part_number'];
                $partcost=$part['part_cost'];
                $parttaxable=$part['part_taxable'];
                print "success|";
            } else {
                print "error|";
            } 
        }
        
        //build the select box
        $sql="SELECT * FROM general_ledgers ORDER BY gl_number ASC";
        $dbGL=dbselectmulti($sql);
        if($dbGL['numrows']>0)
        {
            $glselect="<div class='partGL'>GL# <select id='partgl_".$pid."' name='partgl_".$pid."'>";
            foreach($dbGL['data'] as $gl)
            {
                $glselect.="<option id='".$gl['id']."' name='".$gl['id']."' value='".$gl['id']."'>".$gl['gl_number'].' - '.$gl['gl_description']."</option>";
            }
            $glselect.="</select></div>";
        }
        $newItem="<div id='lineitem_".$pid."' class='inventoryLine'><div class='partName'><a href='#' onclick=\"window.open('partPopup.php?partid=".$pid."','Part Viewer','width=600,height=650,toolbar=no,status=no,location=no,scrollbars=no');return false;\">".$partname."</a><input type='hidden' id='partname_".$pid."' name='partname_".$pid."' value='".$partname."'><input type='hidden' id='taxable_".$pid."' name='taxable_".$pid."' value='".$parttaxable."'></div><div class='clear'></div>";
        $newItem.=$glselect;
        $newItem.="<input type='hidden' id='partnumber_".$pid."' name='partnumber_".$pid."' value='".$parttaxable."'>";
        $newItem.="<div class='partQty'> Qty: <input id='qty_".$pid."' name='qty_".$pid."' type='text' value='0' size=5 onkeypress='return isNumberKey(event);' onblur='calculatePOLine(".$pid.");' /></div><div class='partPrice'>Unit cost: \$<input id='unit_".$pid."' name='unit_".$pid."' type='text' value='".$partcost."' size=8 onkeypress='return isNumberKey(event);' onblur='calculatePOLine(".$pid.");' /></div><div class='partTotal'>Line total: \$<input id='linetotal_".$pid."' size=10 name='linetotal_".$pid."' class='polinetotal' type='text' value='0.00' readonly/></div><img src='artwork/icons/cancel_48.png' border=0 height=24 onclick='deleteInventoryItem(".$pid.");'></div><div class='clear'></div>";
        print $newItem;
      break;
      
      
      case 'lookup':
          switch($_GET['type'])
          {
             case "name":
                $letters = $_GET['term'];
                $letters = preg_replace("/[^a-z0-9 ]/si","",$letters);
                $sql="SELECT id, part_name FROM equipment_part WHERE part_name LIKE '$letters%'";
                $dbParts=dbselectmulti($sql);
                if ($dbParts['numrows']>0)
                {
                    foreach($dbParts['data'] as $part)
                    {
                        $json[] = '{"id" : "' . $part['id'] . '", "label" : "' . $part['part_name'] . '"}';
                    }
                } else {
                    $json[]='{"id" : "0", "label" : "No matches"}';  
                } 
                               
                echo '[' . implode(',', $json) . ']';

             break; 
             
             case "numbers":
                 $letters = $_GET['term'];
                $letters = preg_replace("/[^a-z0-9 ]/si","",$letters);
                $sql="SELECT id, part_name FROM equipment_part WHERE part_number LIKE '$letters%'";
                $dbParts=dbselectmulti($sql);
                if ($dbParts['numrows']>0)
                {
                    foreach($dbParts['data'] as $part)
                    {
                        print $part['id']."###".$part['part_name']."|";
                    }
                } else {
                    print "0###Nothing Found|";
                }
             break;
             
             case "pm":
                 $letters = $_GET['term'];
                $letters = preg_replace("/[^a-z0-9 ]/si","",$letters);
                $sql="SELECT id, part_name FROM equipment_services WHERE service_name LIKE '$letters%'";
                $dbParts=dbselectmulti($sql);
                if ($dbParts['numrows']>0)
                {
                    foreach($dbParts['data'] as $part)
                    {
                        print $part['id']."###".$part['part_name']."|";
                    }
                } else {
                    print "0###Nothing Found|";
                }
            break;
          }
      break;
      
      
      case 'receivepoitem':
        $poid=$_POST['poid'];
        $partid=$_POST['partid'];
        $received=$_POST['received'];
        $ordered=$_POST['ordered'];
        $sql="SELECT * FROM purchase_order_items WHERE po_id=$poid AND part_id=$partid";
        $dbItem=dbselectsingle($sql);
        $error=$dbItem['error'];
        $item=$dbItem['data'];
        
        $orrec=$item['receive_quantity'];
        $itemid=$item['id'];
        
        
        $invinc=$received-$orrec;  //this is in case we received a partial shipment and are updating
        
        
        //do the updates
        $sql="UPDATE purchase_order_items SET receive_quantity=$received WHERE id=$itemid";
        $dbUpdate=dbexecutequery($sql);
        $error.=$dbUpdate['error'];
        //now update the part 
        $sql="UPDATE equipment_part SET part_inventory_quantity=part_inventory_quantity+$invinc WHERE id=$partid";
        $dbUpdate=dbexecutequery($sql);
        $error.=$dbUpdate['error'];
        
        //now lets see if all items have been received fully, if so, update the status
        $sql="SELECT part_quantity, receive_quantity FROM purchase_order_items WHERE po_id=$poid";
        $dbAll=dbselectmulti($sql);
        if ($dbAll['numrows']>0)
        {
            $alldone=true;
            foreach ($dbAll['data'] as $check)
            {
                if ($check['part_quantity']!=$check['receive_quantity'])
                {
                    $alldone=false;
                }    
            }
            if ($alldone)
            {
                //excellent, lets update the status
                $sql="UPDATE purchase_orders SET order_status='complete' WHERE id=$poid";
                $dbUpdate=dbexecutequery($sql);
            }
        } 
        if ($error=='')
        {
            print "success|";
        } else {
            print "error|".$error;
        }
      break;
  }

dbclose();
?>
