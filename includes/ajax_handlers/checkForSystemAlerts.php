<?php
  //this script checks for new alerts and presents them to the users
  
  include("../functions_db.php");
  
  if($_GET['action']=='get')
  {
    $dt=date("Y-m-d H:i:s");
    $sql="SELECT * FROM system_alerts";
    $dbAlerts=dbselectmulti($sql);
    if($dbAlerts['numrows']>0)
    {
        $alerts='';
        foreach($dbAlerts['data'] as $alert)
        {
            $alerts.="<span id='systemalert_$alert[id]'>$alert[alert_message] <a href='#' onclick='clearSystemAlerts($alert[id]);return false;'>Clear alert</a></span>";
        }
        
    }
    if($alerts!='')
    {
        print "success|$alerts";
    } else {
        print "noalerts|";
    }
    
  } else {
      //clearing an alert, will be passed an id
      $id=intval($_GET['id']);
      $sql="DELETE FROM system_alerts WHERE id=$id";
      $dbDelete=dbexecutequery($sql);
      if($dbDelete['error']=='')
      {
          print "success|";
      } else {
          print "error|";
      }
  }
  
  dbclose();
?>
