<?php
include("../functions_db.php");
$eqid=intval($_POST['equipmentid']);
$json = array();
$sql="SELECT * FROM equipment_component WHERE equipment_type='generic' AND equipment_id='$eqid' ORDER BY component_name";
$dbRuns=dbselectmulti($sql);
if($dbRuns['numrows']>0)
{
  $json[]=array('id'=>0,'label'=>'Select component');  
  foreach($dbRuns['data'] as $run)
  {
      $json[] = array("id"=>$run['id'],"label"=>$run['component_name']);
  } 
} else {
    $json[]=array('id'=>0,'label'=>'No components set up');  
} 
echo json_encode($json);
dbclose();        
?>
