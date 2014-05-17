<?php
  //build inserts for specific job
  $jobid=$_GET['jobid'];
  
  include("../includes/functions_db.php");
  include("../includes/functions_common.php");
  include("../includes/config.php");
  
  printJob2Inserter($jobid,0,true);
  
?>
