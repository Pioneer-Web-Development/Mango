<?php
  include('../functions_db.php');
  
  if($_POST['action']=='add')
  {
      $vendorid=intval($_POST['vendorid']);
      $partid=$_POST['partid'];
      if(strpos($partid,'-')>0)
      {
          $temp=explode("-",$partid);
          $partid=0;
          $newid=$temp[1];
      } else {
          $newid='';
      }
      $number=$_POST['number'];
      $cost=$_POST['cost'];
      if($cost==''){$cost='0.00';}
      
      $sql="INSERT INTO equipment_part_vendor (vendor_id, part_id, part_number, part_cost, newid) VALUES ('$vendorid', '$partid', '$number', '$cost', '$newid')";
      $dbInsert=dbinsertquery($sql);
      $error=$dbInsert['error'];
      $partvendorid=$dbInsert['insertid'];
      
      //lookup vendor name
      $sql="SELECT vendor_name FROM vendors WHERE id='$vendorid'";
      $dbVendor=dbselectsingle($sql);
      $vendorname=stripslashes($dbVendor['data']['vendor_name']);
      
      $output="<div id='vendor_$partvendorid' style='margin-bottom:2px;padding-bottom:2px;border-bottom:thin solid black;width:700px;'>";
      $output.="<div style='width: 300px;float:left;'>$vendorname</div>";
      $output.="<div style='width: 150px;float:left;'><input type='text' id='part_number_$partvendorid' size=10 value='$number' /></div>";
      $output.="<div style='width: 150px;float:left;'>\$<input type='text' id='part_cost_$partvendorid' size=10 value='$cost' /></div>";
      $output.="<div style='float:left;width:200px;'><div style='float:left;'><input type='button' value='Update' onclick='updatePartVendor(\"$partvendorid\");' style='height:20px;padding:2px;margin-left:4px;font-size:12px;padding-bottom:4px;'><input type='button' value='Delete' onclick='deletePartVendor(\"$partvendorid\");' style='height:20px;padding:2px;margin-left:4px;font-size:12px;padding-bottom:4px;'></div><div id='update_$partvendorid' style='display:none;float:left;'><img src='artwork/icons/accepted_48.png' border=0 height=20 /></div></div>";
      $output.="<div class='clear'></div></div><script>\$(function() {\$(\"input:button\").button();});</script>";
      print "success|".$output;                    
  } elseif($_POST['action']=='edit')
  {
      $partvendorid=intval($_POST['partvendorid']);
      $number=$_POST['number'];
      $cost=$_POST['cost'];
      if($cost==''){$cost='0.00';}
      
      $sql="UPDATE equipment_part_vendor SET part_number='$number', part_cost='$cost' WHERE id='$partvendorid'";
      $dbUpdate=dbexecutequery($sql);
      $error=$dbUpdate['error'];
      if($error=='')
      {
          print "success|$sql";
      } else {
          print "error|$error";
      }
  } elseif($_POST['action']=='delete')
  {
      $partid=intval($_POST['vendorid']);
      $sql="DELETE FROM equipment_part_vendor WHERE id='$partid'";
      $dbDelete=dbexecutequery($sql);
      $error=$dbDelete['error'];
      if($error=='')
      {
          print "success|";
      } else {
          print "error|$error";
      }
  }
  
  
  dbclose();
?>
