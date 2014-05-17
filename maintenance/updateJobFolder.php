<?php
  //sets all jobs to specified folder
  include("../includes/functions_db.php");
  $sql="UPDATE jobs SET folder=1";
  $dbUpdate=dbexecutequery($sql);
  print "Updated ".$dbUpdate['numrows']." records.";
  