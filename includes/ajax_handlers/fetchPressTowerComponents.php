<?php
include("../functions_db.php");
$table=$_POST['pressid'];
$json = array();
$sql="SELECT * FROM equipment_component WHERE equipment_type='press' AND equipment_id=$pressid AND parent_id=0";
$dbComponents=dbselectmulti($sql);

$json[] = '{"id" : "0", "label" : "Please choose"}';            
if($dbComponents['numrows']>0)
{
  foreach($dbComponents['data'] as $component)
  {
      $json[] = '{"id" : "' . $field['id'] . '", "label" : "' . $field['component_name'] . '"}';
  } 
} else {
    $json[]='{"id" : "0", "label" : "No components found for this press"}';  
} 
echo '[' . implode(',', $json) . ']';
dbclose();        
?>
