<?php
include("../functions_db.php");
$pubid=intval($_POST['pub_id']);
$json = array();
$sql="SELECT * FROM publications_insertruns WHERE pub_id='$pubid' ORDER BY run_name";
$dbRuns=dbselectmulti($sql);
if($dbRuns['numrows']>0)
{
  foreach($dbRuns['data'] as $run)
  {
      $json[] = '{"id" : "' . $run['id'] . '", "label" : "' . $run['run_name'] . '"}';
  } 
} else {
    $json[]='{"id" : "0", "label" : "No runs set up"}';  
} 
echo '[' . implode(',', $json) . ']';
dbclose();        
?>
