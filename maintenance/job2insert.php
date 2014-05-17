<?php
  include("../includes/functions_db.php");
  include("../includes/functions_common.php");
  include("../includes/config.php");
  
  $jobid=$_GET['jobid'];
  $force=$_GET['force'];
  printJob2Inserter($jobid,$force,1);
  
?>
