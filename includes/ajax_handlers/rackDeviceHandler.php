<?php
  include("../functions_db.php");
  
  switch($_POST['action'])
  {
      case "save":
        $id=intval($_POST['id']);
        $rackid=intval($_POST['rackid']);
        $top=intval($_POST['top']);
        $left=intval($_POST['left']);
        $width=intval($_POST['width']);
        $height=intval($_POST['height']);
        $device=addslashes($_POST['deviceid']);
        
        $nsql="SELECT device_name FROM it_devices WHERE id=$device";
        $dbName=dbselectsingle($nsql);
        $name=stripslashes($dbName['data']['device_name']);
        
        if($id==0)
        {
            //adding new region
            $sql="INSERT INTO it_rack_devices (rack_id, device_id, region_top, region_left, region_width, region_height) 
            VALUES ('$rackid', '$device', '$top', '$left', '$width', '$height')";
            $dbInsert=dbinsertquery($sql);
            $id=$dbInsert['insertid'];
            if($dbInsert['error']=='')
            {
                $json=array('status'=>'success','id'=>$id,'addnew'=>'true','name'=>$name,'nsql'=>$nsql);
            } else{
                $json=array('status'=>'error','message'=>$dbInsert['error']);
            }
            
        } else {
            //updating existing
            $sql="UPDATE it_rack_devices SET device_id='$device', region_top='$top', region_left='$left', 
            region_width='$width', region_height='$height' WHERE id=$id";
            $dbUpdate=dbexecutequery($sql);
            if($dbUpdate['error']=='')
            {
                $json=array('status'=>'success','id'=>$id,'name'=>$name);
            } else{
                $json=array('status'=>'error','message'=>$dbUpdate['error']);
            }
            
        }
      break;
      
      case "delete":
        $id=intval($_POST['id']);
        $rackid=intval($_POST['rackid']);
        $sql="DELETE FROM it_rack_devices WHERE id=$id";
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