<?php
  include('../functions_db.php');
  
  if($_POST['action']=='add')
  {
      $userid=intval($_POST['userid']);
      $pubid=intval($_POST['pubid']);
      $runid=intval($_POST['runid']);
      $type=$_POST['type'];
      if($type=='press')
      {
          $sql="SELECT A.pub_name, B.run_name FROM publications A, publications_runs B WHERE A.id=$pubid AND B.pub_id=A.id AND B.id=$runid";
          $dbRuninfo=dbselectsingle($sql);
          $name=$dbRuninfo['data']['pub_name'].' - '.$dbRuninfo['data']['run_name'];
      } elseif($type=='inserter') {
          $sql="SELECT A.pub_name, B.run_name FROM publications A, publications_insertruns B WHERE A.id=$pubid AND B.pub_id=A.id AND B.id=$runid";
      }
      $dbRuninfo=dbselectsingle($sql);
      $name=addslashes($dbRuninfo['data']['pub_name'].' - '.$dbRuninfo['data']['run_name'].' - '.$type);
      
      $sql="INSERT INTO user_textalerts (pub_id, run_id, user_id, alert_name, type) VALUES ('$pubid', '$runid', '$userid', '$name', '$type')";
      $dbInsert=dbinsertquery($sql);
      $error=$dbInsert['error'];
      $id=$dbInsert['insertid'];
      
      $newitem="<div id='alert$id' style='width:500px;font-size:12px;padding-bottom:2px;border-bottom:thin solid black;margin-bottom:2px;'>$name <img src='artwork/icons/cancel_48.png' border=0 height=24 onclick='deleteAlert($id);' align=right></div>";
                
  } elseif($_POST['action']=='delete')
  {
      $alertid=intval($_POST['alertid']);
      $sql="DELETE FROM user_textalerts WHERE id='$alertid'";
      $dbDelete=dbexecutequery($sql);
      $error=$dbDelete['error'];
  }
  
  if($error=='')
  {
      print "success|$newitem";
  } else {
      print "error|$error";
  }
  dbclose();
?>
