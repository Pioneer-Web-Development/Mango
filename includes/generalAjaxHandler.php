<?php
  //script for handling general ajax requests from the system
  include("functions_db.php");
  include("config.php");
  include("functions_common.php");
  
  $action=$_GET['action'];
  $type=$_GET['type'];
  $id=$_GET['id'];
  $secondid=$_GET['secondid'];
  
  switch ($type)
  {
      case "deletestopnote":
      deleteStopNote($id);
      break;
      
      case "killstop":
      deleteJobStop($id,$secondid);
      break;
      
      case "jmonitorPressman":
      monitorPressman($action,$id,$secondid);
      break;
  }
  
  
  function monitorPressman($action,$jobid,$pressmanid)
  {
      $sql="SELECT id, job_pressman_ids, job_pressman_count FROM job_stats WHERE job_id=$jobid";
      $dbStats=dbselectsingle($sql);
      $stats=$dbStats['data'];
      $count=$stats['job_pressman_count'];
      $statid=$stats['id'];
      $ids=$stats['job_pressman_ids'];
      
      if ($action=='add')
      {
          $ids.=$pressmanid."|";
          $count++;
          $sql="UPDATE job_stats SET job_pressman_ids='$ids', job_pressman_count=$count WHERE id=$statid";
          $dbUpdate=dbexecutequery($sql);
      } else {
          $pressmanids=explode("|",$ids);
          $newids='';
          foreach($pressmanids as $key=>$value)
          {
              if($value!='')
              {
                 $newids.="p$value|"; 
              }
              
          }
          $newids=str_replace("p$pressmanid|","",$newids);
          $newids=str_replace("p","",$newids);
          $count--;
          $sql="UPDATE job_stats SET job_pressman_ids='$newids', job_pressman_count=$count WHERE id=$statid";
          $dbUpdate=dbexecutequery($sql);
      }
      
  }
  
  function deleteStopNotes($stopid)
  {
      $sql="UPDATE job_stops SET stop_notes='' WHERE id=$stopid";
      $dbUpdate=dbexecutequery($sql);
  }
  
  function deleteJobStop($jobid,$stopid)
  {
      $sql="SELECT stop_downtime FROM job_stops WHERE id=$stopid";
      $dbDown=dbselectsingle($sql);
      $down=$dbDown['data']['stop_downtime'];
      $sql="DELETE FROM job_stops WHERE id=$stopid";
      $dbDelete=dbexecutequery($sql);
      $sql="UPDATE job_stats SET total_downtime=total_downtime-$down WHERE job_id=$jobid";
      $dbUpdate=dbexecutequery($sql);
  }
  
  
  dbclose();
?>
