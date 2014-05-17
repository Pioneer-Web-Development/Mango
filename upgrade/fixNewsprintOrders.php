<?php
  //needed to fix customer id to account id for inserts
  include('../includes/functions_db.php');
  
  $sql="UPDATE orders SET tweaked=0";
  $dbUpdate=dbexecutequery($sql);
  
  $sql="SELECT DISTINCT vendor_id FROM orders";
  $dbIds=dbselectmulti($sql);
  if($dbIds['numrows']>0)
  {
      foreach($dbIds['data'] as $id)
      {
          //see what it used to be 
          $sql="SELECT * FROM dep_vendors WHERE id=$id[vendor_id]";
          $dbName=dbselectsingle($sql);
          $name=$dbName['data']['vendor_name'];
          print "For id=$id[vendor_id] Old name was $name<br />";
          //look up new id from accounts
          $sql="SELECT * FROM accounts WHERE account_name='".$name."'";
          print "Looking for new id with $sql<br />";
          $dbNew=dbselectsingle($sql);
          $newID=$dbNew['data']['id'];
          print "New id is $newID<br />";
          //update
          $sql="UPDATE orders SET vendor_id='$newID', tweaked=1 WHERE vendor_id=$id[vendor_id] AND tweaked=0";
          print "Update sql is $sql<br />"; 
          $dbUpdate=dbexecutequery($sql);
      }
  }
?>
