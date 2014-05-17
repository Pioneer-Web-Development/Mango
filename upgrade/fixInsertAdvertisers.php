<?php
  //needed to fix customer id to account id for inserts
  include('../includes/functions_db.php');
  
  $sql="UPDATE inserts SET tweaked=0";
  $dbUpdate=dbexecutequery($sql);
  
  $sql="SELECT DISTINCT advertiser_id FROM inserts";
  $dbIds=dbselectmulti($sql);
  if($dbIds['numrows']>0)
  {
      foreach($dbIds['data'] as $id)
      {
          //see what it used to be 
          $sql="SELECT * FROM dep_customers WHERE id=$id[advertiser_id]";
          $dbName=dbselectsingle($sql);
          $name=$dbName['data']['customer_name'];
          print "Looking up $id[advertiser_id] got $name<br />";
          //look up new id from accounts
          $sql="SELECT * FROM accounts WHERE account_name='".$name."'";
          $dbNew=dbselectsingle($sql);
          $newID=$dbNew['data']['id'];
          print "New id is $newID<br />";
          //update
          $sql="UPDATE inserts SET advertiser_id='$newID', tweaked=1 WHERE advertiser_id=$id[advertiser_id] AND tweaked=0";
          print "Updating with $sql<br />";
          $dbUpdate=dbexecutequery($sql);
          if($dbUpdate['error'])
          {
              print "Error updating: ".$dbUpdate['error']."<br />";
          }
      }
  }
?>
