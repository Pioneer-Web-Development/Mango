<?php
  include("../functions_db.php");
  
  switch($_POST['action'])
  {
      case "save":
        $id=intval($_POST['id']);
        $planid=intval($_POST['planid']);
        $top=intval($_POST['top']);
        $left=intval($_POST['left']);
        $width=intval($_POST['width']);
        $height=intval($_POST['height']);
        $name=addslashes($_POST['name']);
        $link=addslashes($_POST['link']);
        
        if($id==0)
        {
            //adding new region
            $sql="INSERT INTO it_floorplan_regions (plan_id, region_name, region_link, region_top, region_left, region_width, region_height) 
            VALUES ('$planid', '$name', '$link', '$top', '$left', '$width', '$height')";
            $dbInsert=dbinsertquery($sql);
            $id=$dbInsert['insertid'];
            if($dbInsert['error']=='')
            {
                $json=array('status'=>'success','id'=>$id,'addnew'=>'true');
            } else{
                $json=array('status'=>'error','message'=>$dbInsert['error']);
            }
            
        } else {
            //updating existing
            $sql="UPDATE it_floorplan_regions SET region_name='$name', region_link='$link', region_top='$top', region_left='$left', 
            region_width='$width', region_height='$height' WHERE id=$id";
            $dbUpdate=dbexecutequery($sql);
            if($dbUpdate['error']=='')
            {
                $json=array('status'=>'success','id'=>$id);
            } else{
                $json=array('status'=>'error','message'=>$dbUpdate['error']);
            }
            
        }
      break;
      
      case "delete":
        $id=intval($_POST['id']);
        $planid=intval($_POST['planid']);
        $sql="DELETE FROM it_floorplan_regions WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        if($dbDelete['error']=='')
        {
            $json=array('status'=>'success');
        } else {
            $json=array('status'=>'error','message'=>$dbDelete['error']);
        }
      break;
  }
  
  echo json_encode($json);
  
  dbclose();