<?php
  session_start();
  $live=array('response'=>'Your site access has been renewed');
  
  echo json_encode($live);
?>
