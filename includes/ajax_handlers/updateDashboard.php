<?php
  //script for updating dashboard block moves
  include("../functions_db.php");
  
  if($_POST['action']=='reorder')
  {
      $userid=intval($_POST['uid']);
      $col1=$_POST['col1'];
      $col1=str_replace("&","",$col1);
      $blocks=explode("item[]=",$col1);
      foreach($blocks as $key=>$mname)
      {
          $modorder=$key+1;
          $sql="UPDATE user_dashboard SET module_column=1, module_order=$modorder WHERE module_id='$mname' AND user_id='$userid'";
          $dbUpdate=dbexecutequery($sql);
          $error.=$dbUpdate['error'];
          
      }
      $col2=$_POST['col2'];
      $col2=str_replace("&","",$col2);
      $blocks=explode("item[]=",$col2);
      foreach($blocks as $key=>$mname)
      {
          $modorder=$key+1;
          $sql="UPDATE user_dashboard SET module_column=2, module_order=$modorder WHERE module_id='$mname' AND user_id='$userid'";
          $dbUpdate=dbexecutequery($sql);
          $error.=$dbUpdate['error'];
            
      }
      $col3=$_POST['col3'];
      $col3=str_replace("&","",$col3);
      $blocks=explode("item[]=",$col3);
      foreach($blocks as $key=>$mname)
      {
          $sql="UPDATE user_dashboard SET module_column=3, module_order=$modorder WHERE module_id='$mname' AND user_id='$userid'";
          $dbUpdate=dbexecutequery($sql);
          $error.=$dbUpdate['error']; 
      }
  } elseif($_POST['action']=='toggle')
  {
      $userid=intval($_POST['uid']);
      $blockid=str_replace("toggle_","",$_POST['id']);
      //find the current state
      $sql="SELECT * FROM user_dashboard WHERE user_id=$userid AND module_id=$blockid";
      $dbState=dbselectsingle($sql);
      if($dbState['data']['collapsed']==1)
      {
          $newstate=0;
      } else {
          $newstate=1;
      }
      $sql="UPDATE user_dashboard SET collapsed=$newstate WHERE user_id=$userid AND module_id=$blockid";
      $dbUpdate=dbexecutequery($sql);
      $error=$dbUpdate['error'];
  }
  print $error;
?>
