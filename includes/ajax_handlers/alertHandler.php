<?php
  include("../functions_db.php");
  
  $name=$_POST['name'];
  $sql="DELETE FROM alerts WHERE alert_name='$name'";
  $dbDelete=dbexecutequery($sql);
  
  dbclose();
?>
