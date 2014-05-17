<?php
  //handle monthly inventory
  include("../functions_db.php");
  
  //being passed a composite id set up like item[id]_$year_$month_receive/ending
  //and the value of the field
  $fieldid=$_POST['id'];
  $value=$_POST['value'];
  
  $fieldParts=explode("_",$fieldid);
  $itemid=$fieldParts[0];
  $year=$fieldParts[1];
  $month=$fieldParts[2];
  $type=$fieldParts[3];
  
  $remaining=0;
  $consumed=0;
  $received=0;
  $starting=0;
  if($type=='receive'){$received=$value;}
  if($type=='end'){$remaining=$value;}
  
  $sql="SELECT * FROM monthly_inventory WHERE item_id=$itemid AND month=$month AND year=$year";
  $dbInvValues=dbselectsingle($sql);
  if($dbInvValues['numrows']>0)
  {
      //ok, we've already created the value record, just need to update the proper item
      //and calculate the end point
      if($type=='receive')
      {
          //means we also need to update the ending count
          $starting=$dbInvValues['data']['start'];
          $consumed=$dbInvValues['data']['consumed'];
          
          $remaining=$starting+$received-$consumed;
          $consumed=$starting+$received-$remaining;
          $sql="UPDATE monthly_inventory SET received='$received', remaining=$remaining, consumed=$consumed
         WHERE item_id=$itemid AND month=$month AND year=$year";
         $dbUpdate=dbexecutequery($sql);
         $error.=$dbUpdate['error']; 
      } else {
          $starting=$dbInvValues['data']['start'];
          $received=$dbInvValues['data']['received'];
          $consumed=$starting+$received-$remaining;
          
          $sql="UPDATE monthly_inventory SET received='$received', remaining=$remaining, consumed=$consumed
          WHERE item_id=$itemid AND month=$month AND year=$year";
         $dbUpdate=dbexecutequery($sql);
         $error.=$dbUpdate['error']; 
      }
     
  } else {
      $remaining=$starting+$received-$consumed;
      $consumed=$starting+$received-$remaining;
      $sql="INSERT INTO monthly_inventory (item_id, month, year, start, received, consumed, remaining) VALUES 
      ($itemid, $month, $year, $starting, $received, $consumed, $remaining)";
      $dbInsert=dbinsertquery($sql);
      $error.=$dbInsert['error']; 
  }
  
  //make sure we have both real values
  $sql="SELECT * FROM monthly_inventory WHERE item_id=$itemid AND year=$year AND month=$month";
  $dbValues=dbselectsingle($sql);
  $values=$dbValues['data'];
  $starting=$values['start'];
  $received=$values['received'];
  $remaining=$values['remaining'];
  $consumed=$values['consumed'];
  
  $reload=0;
  //... now here is the bugger. IF! the  submitted month and year are NOT the current month and year, then
  // we need to cascade this data through any following months so that the balances are adjusted
  if($month!=date("n") || $year!=date("Y"))
  {
      //ok, we have to do this...
      if($month==12){
         $syear=$year+1;
         $smonth=1;
      } else {
          $syear=$year;
          $smonth=$month+1;
      }
      $reload=1;
      $lastRemaining=$remaining;
      for($y=$syear;$y<=date("Y");$y++)
      {
          for($m=$smonth;$m<=12;$m++)
          {
              if($y<date("Y") || $m<=date("n"))
              {
                  $sql="SELECT * FROM monthly_inventory WHERE item_id=$itemid AND year=$y AND month=$m";
                  $dbCurrent=dbselectsingle($sql);
                  //print "Checking with $sql<br>";
                  if($dbCurrent['numrows']>0)
                  {
                      $current=$dbCurrent['data'];
                      $curReceived=$current['received'];
                      $curConsumed=$current['consumed'];
                      $newRemaining=$lastRemaining+$curReceived-$curConsumed;
                      $id=$current['id'];
                      
                      $sql="UPDATE monthly_inventory SET start='$lastRemaining', remaining='$newRemaining' WHERE id=$id";
                      $dbUpdate=dbexecutequery($sql);
                      //print "Updating existing with $sql<br>"; 
                  } else {
                      $newStarting=$lastRemaining;
                      $newReceived=0;
                      $newConsumed=0;
                      $curReceived=0;
                      $curConsumed=0;
                      $newRemaining=$newStarting;
                      
                      //need to create a record. This is possible in the case of starting the count at a past date
                       $sql="INSERT INTO monthly_inventory (item_id, month, year, start, received, consumed, remaining) VALUES 
                      ($itemid, $m, $y, $newStarting, $newReceived, $newConsumed, $newRemaining)";
                      $dbInsert=dbinsertquery($sql);
                      //print "Inserting with $sql<br>";
                  }
              } //otherwise we are working into the future...
              $lastRemaining=$newRemaining;
          }
      }
  }
  
  
  if($error=='')
  {
    $json['status']='success';             
  } else {
    $json['status']='error';             
  }
  $json['field']=$itemid."_".$year."_".$month."_consumed";
  $json['starting']=$starting;
  $json['received']=$received;
  $json['remaining']=$remaining;
  $json['consumed']=$consumed;
  $json['reload']=$reload;
  $json['error']=$error;
  echo json_encode($json);
  
  
  
  
  dbclose();
?>
