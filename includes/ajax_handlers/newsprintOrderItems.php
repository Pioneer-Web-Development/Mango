<?php
  //this script handles addition and deletion of items in a newsprint order
  include("../functions_db.php");
  include("../functions_formtools.php");
  include("../config.php");
  include("../functions_common.php");
  
  if($_POST)
  {
      $action=$_POST['action'];
  } else {
      $action=$_GET['action'];
  }
  
  
  switch($action)
  {
      case "deleteorderitem":
      deleteorderitem();
      break;
      
      case "saveorderitem":
      saveorderitem();
      break;
      
      case "addorderitem":
      addorderitem();
      break;
  }
  
function addorderitem()
{
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


    $orderid=$_POST['orderid'];
    $i='new';
    print "success|||";
    print "<div id='item_$i'><span style='float:left;width:28px;'><span id='success_$i' style='display:none;'><img src='artwork/icons/accepted_48.png' width=20 border=0 /></span></span>\n";
    print input_select('paper_'.$i,$papertypes[$item['paper_type_id']],$papertypes)."&nbsp;&nbsp;";
    print input_select('size_'.$i,$sizes[$item['size_id']],$sizes)." Tons: ";
    print "<input type=text name='tonnage_$i' id='tonnage_$i' value='0' class='ton' size=5 onBlur='newsprintOrderItemSave(\"$i\",\"$orderid\");' onChange='calcTonnage();' onKeyPress='return isNumberKey(event);' />MT\n";
    print "<a href='#' onclick='newsprintOrderItemSave(\"$i\",\"$orderid\");' style='text-decoration:none;'><img src='artwork/icons/folder_48.png' width=20 border=0 />&nbsp;Save</a>\n";
    print "<a href='#' onclick='newsprintOrderItemDelete(\"$i\",\"$orderid\");' style='text-decoration:none;'><img src='artwork/icons/cancel_48.png' width=20 border=0 />&nbsp;Delete</a>\n";
    print "</div>\n";
}
  
  function saveorderitem()
  {
      global $siteID;
      $orderid=$_POST['orderid'];
      $paper=$_POST['paper'];
      $size=$_POST['size'];
      $tonnage=$_POST['tonnage'];
      $itemid=$_POST['itemid'];
      
      if($itemid=='new')
      {
          //adding new item, we'll need to be sure to return the insert id
          //get the max display order and add 1 to it
          $sql="SELECT MAX(itemdisplay_order) as mo FROM order_items WHERE order_id='$orderid'";
          $dbMax=dbselectsingle($sql);
          $max=$dbMax['data']['mo'];
          $max++;
          $sql="INSERT INTO order_items (order_id, paper_type_id, size_id,tonnage_request, itemdisplay_order, site_id) VALUES 
          ('$orderid','$paper','$size','$tonnage','$max','$siteID')";
          $dbInsert=dbinsertquery($sql);
          $newid=$dbInsert['insertid'];
          if($dbInsert['error']!='')
          {
              print "error|".$dbInsert['error'];
          } else {
              print "success|$newid|";
          }
      } else {
          //updating an existing one
          $sql="UPDATE order_items SET paper_type_id='$paper', size_id='$size', tonnage_request='$tonnage' WHERE id=$itemid";
          $dbUpdate=dbexecutequery($sql);
          if($dbUpdate['error']!='')
          {
              print "error|".$dbUpdate['error'];
          } else {
              print "success|";
          }
      }
  }
  
  function deleteorderitem()
  {
      $orderid=$_POST['orderid'];
      $itemid=str_replace("del_","",$_POST['itemid']);
      $sql="DELETE FROM order_items WHERE id=$itemid";
      $dbDelete=dbexecutequery($sql);
      if($dbDelete['error']!='')
      {
          print "error|".$dbDelete['error'];
          print_r($_POST);
      } else {
          print "success|";
      }
  }
  
  function addline()
  {
     print "<div id='item_$i'>\n";
     print input_select('paper_'.$i,$papertypes[$item['paper_type_id']],$papertypes)."&nbsp;&nbsp;";
     print input_select('size_'.$i,$sizes[$item['size_id']],$sizes)." Tons: ";
     print "<input type=text name=\"tonnage_$i\" id=\"tonnage_$i\" value=\"$item[tonnage_request]\" class=\"ton\" size=5 onChange='calcTonnage();' onKeyPress='return isNumberKey(event);' />MT\n";
     print "<a href='#' class='deleteOrderItem' id='del_$item[id]' rel='$orderid' ><img src='artwork/icons/cancel_48.png' width=24 border=0 />&nbsp;Delete</a>\n";
     $i++;
  }
 
 dbclose();
?>
