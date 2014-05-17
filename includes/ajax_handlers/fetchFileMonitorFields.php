<?php
include("../functions_db.php");
$table=$_POST['table'];
$json = array();
$dbFields=dbgetfields($table);
if($dbFields['numrows']>0)
{
  foreach($dbFields['fields'] as $field)
  {
      $json[] = '{"id" : "' . $field['Field'] . '", "label" : "' . $field['Field'] . '"}';
  } 
} else {
    $json[]='{"id" : "0", "label" : "No fields found for '.$table.'"}';  
} 
echo '[' . implode(',', $json) . ']';
dbclose();        
?>
