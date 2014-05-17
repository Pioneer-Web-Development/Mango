<?php
  include("includes/mainmenu.php");
  
  /*
  *
  * This script is built to create file parsers for any kind of file incoming and to any kind of file out
  * 
  */
  
  if($_POST)
  {
      $action=$_POST['submit'];
  } else {
      $action=$_GET['action'];
  }
  
  switch($action)
  {
      case "add":
        edit_parser();
      break;
        
      case "edit":
        edit_parser();
      break;
        
      case "delete":
        delete_parser();
      break;
        
      
      default:
        list_parsers();
      break;
  }