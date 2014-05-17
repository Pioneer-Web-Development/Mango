<?php
  //this script looks up roll tags and replies with either the roll id or 0.
  include("functions_db.php");
  $rollid=$_GET['rollid'];
  $status=$_GET['status'];
  if ($status==9){$cdate=", batch_date='".date("Y-m-d")."'";}else{$cdate=", batch_date=NULL";}
  $sql="UPDATE rolls SET status='$status' $cdate WHERE id='$rollid'";
  $dbRoll=dbexecutequery($sql);
  print $sql."<br>".$dbRoll['error'];
?>
