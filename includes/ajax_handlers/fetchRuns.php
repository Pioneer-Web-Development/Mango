<?php
include("../functions_db.php");
if($_POST['origin_pub_id'])
{
    $pubid=intval($_POST['origin_pub_id']);
} elseif($_POST['dest_pub_id'])
{
    $pubid=intval($_POST['dest_pub_id']);
} else {
    $pubid=intval($_POST['pub_id']);
}
$json = array();
$sql="SELECT * FROM publications_runs WHERE pub_id='$pubid' AND run_status=1 ORDER BY run_name";
$dbRuns=dbselectmulti($sql);
if($_GET['zero']==1 || $_POST['zero']==1)
{
    //$json[]='{"id" : "0", "label" : "Please choose"}';
    $json[]=array("id"=>0,"label"=>"Please choose");
} elseif($_GET['all']==1 || $_POST['all']==1)
{
    //$json[]='{"id" : "0", "label" : "Show all"}';
    $json[]=array("id"=>0,"label"=>"Show all");
}
if($dbRuns['numrows']>0)
{
  foreach($dbRuns['data'] as $run)
  {
      //$json[] = '{"id" : "' . $run['id'] . '", "label" : "' . $run['run_name'] . '"}';
      $json[]=array("id"=>$run['id'],"label"=>$run['run_name']);
  } 
} else {
    //$json[]='{"id" : "0", "label" : "No runs set up"}'; 
    $json=array("id"=>0,"label"=>"No runs set up"); 
} 
//echo '[' . implode(',', $json) . ']';
echo json_encode($json);
dbclose();        
?>