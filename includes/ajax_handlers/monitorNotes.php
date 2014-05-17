<?php
  //this script checks for new alerts and presents them to the users
  
  include("../functions_db.php");
  
  $action=$_POST['action'];
  $jobid=$_POST['jobid'];
  if($_POST['mode']=='page')
  {
      $mode='page';
  }else {
      $mode='plate';
  }
  $notes=addslashes($_POST['notes']);
  
  $sql="UPDATE jobs SET ".$mode."_notes='$notes' WHERE id=$jobid";
  $dbUpdate=dbexecutequery($sql);
  if($dbUpdate['error']=='')
  {
      $json['status']='success';
  } else {
      $json['status']='error';
      $json['message']=$dbUpdate['error'];
  }
  dbclose();
  
  echo json_encode($json);