<?php
  session_start();
  include('../includes/functions_mobile.php');
  
  mobile_init();
  
  function mobile_page()
  {
      print "<pre>";
      print_r($_SESSION);
      print "</pre>";
      
  }
?>
