<?php
include("../functions_db.php");
$base=$_POST['equipment_id'];

//see what kind of equipment we are dealing with
$e=explode("_",$base);
$equipmentid=intval($e[1]);
$etype=$e[0];
switch($etype)
{
    case "press":
    //components are going to be the towers
    $sql="SELECT id, tower_name AS ename FROM press_towers WHERE press_id='$equipmentid' ORDER BY tower_order";
    $preface='';
    break;
    
    case "inserter":
    $sql="SELECT id, hopper_number AS ename FROM inserters_hoppers WHERE inserter_id='$equipmentid' ORDER BY hopper_number";
    $preface='Station: ';  
    break;
    
    case "stitcher":
    $sql="SELECT id, hopper_number AS ename FROM stitchers_hoppers WHERE stitcher_id='$equipmentid' ORDER BY hopper_number";
    $preface='Station: ';  
    
    break;
    
    case "e":
    $sql="SELECT id, component_name AS ename FROM equipment_component WHERE equipment_type='generic' AND equipment_id='$equipmentid' ORDER BY component_name";
    $preface='';  
    
    break;
}
$dbComponents=dbselectmulti($sql);
$json = array();
$json[]=array("id"=>0,"label"=>"Show all components");
if($dbComponents['numrows']>0)
{
    foreach($dbComponents['data'] as $component)
    {
        $json[]=array("id"=>$component['id'],"label"=>$preface.$component['ename']);
    }
}
echo json_encode($json);
dbclose();     