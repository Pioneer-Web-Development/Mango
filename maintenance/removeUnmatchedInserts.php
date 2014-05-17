<?php
  include("../includes/functions_db.php");
  
  //get all future inserts
  $sql="SELECT id, weprint_id FROM inserts WHERE weprint_id>0";
  $dbInsert=dbselectmulti($sql);
  if($dbInsert['numrows']>0)
  {
      $ids='';
      foreach($dbInsert['data'] as $insert)
      {
          //see if the job id still exists
          $id=$insert['id'];
          $weid=$insert['weprint_id'];
          $sql="SELECT id FROM jobs WHERE id=$weid";
          $dbJob=dbselectsingle($sql);
          if($dbJob['numrows']==0)
          {
              $ids.="$id,";
          }
      }
      if($ids!='')
      {
          $ids=rtrim($ids,',');
          print "Would remove inserts with id of<br />$ids";
          $sql="DELETE FROM inserts WHERE id IN($ids)";
          $dbDelete=dbexecutequery($sql);
          $sql="DELETE FROM inserts_schedule WHERE insert_id IN($ids)";
          $dbDelete=dbexecutequery($sql);
          
      }
  }
?>
