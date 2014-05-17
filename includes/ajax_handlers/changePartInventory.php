<?php
  include("../functions_db.php");
  $partid=$_POST['partid'];
  if ($_POST['type']=='sub')
  {
      $sql="UPDATE equipment_part SET part_inventory_quantity=part_inventory_quantity-1 WHERE id=$partid";
      $dbUpdate=dbexecutequery($sql);
  } else {
      $sql="UPDATE equipment_part SET part_inventory_quantity=part_inventory_quantity+1 WHERE id=$partid";
      $dbUpdate=dbexecutequery($sql);
  }
  if($dbUpdate['error']=='')
  {
      $json['status']='success';
      $sql="SELECT part_inventory_quantity, part_reorder_quantity FROM equipment_part WHERE id=$partid";
      $dbPart=dbselectsingle($sql);
      $part=$dbPart['data'];
      $invquantity=$part['part_inventory_quantity'];
      $reorder=$part['part_reorder_quantity'];
      if ($invquantity==0)
      {
          $json['count']="$invquantity <span style='color:red;font-weight:bold'>Alert, you are out of this part!</span>\n";
      } elseif ($invquantity<=$reorder) {
          $json['count']="$invquantity <span style='color:red;font-weight:bold'>Alert, you need to reorder!</span>\n";
      } else {
          $json['count']=$invquantity;
      }
      
  } else {
      $json['status']='error';
  }
  echo json_encode($json);
  dbclose();