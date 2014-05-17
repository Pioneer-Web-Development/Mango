<?php
  include("../includes/functions_db.php");
  include("../includes/functions_common.php");
  include("../includes/config.php");
  
  //get job id
  $jobid=intval($_GET['jobid']);
  
  pressWearTear($jobid,true);