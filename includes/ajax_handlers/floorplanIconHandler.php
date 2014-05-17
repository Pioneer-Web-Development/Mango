<?php
  include("../functions_db.php");
  if($_POST)
  {
      $action=$_POST['action'];
  } else {
      $action=$_GET['action'];
  }
  switch($action)
  {
      case "add":
        $planid=intval($_POST['planid']);
        $icon=intval($_POST['icon']);
        $device=intval($_POST['device']);
        $dtype=$_POST['dtype'];
        
        if($dtype=='rack')
        {
            //need to look up icon image
            $sql="SELECT * FROM it_floorplan_icons WHERE rack_icon=1 LIMIT 1";
        } else {
            //need to look up icon image
            $sql="SELECT * FROM it_floorplan_icons WHERE id=$icon";
        }
        $dbIcon=dbselectsingle($sql);
        $image='artwork/iticons/'.stripslashes($dbIcon['data']['icon_image']);
        $icon=$dbIcon['data']['id'];
        //adding new device
        $sql="INSERT INTO it_floorplan_devices (plan_id, icon_id, device_id, device_type, icon_top, icon_left) 
        VALUES ('$planid', '$icon', '$device', '$dtype', 0, 0)";
        $dbInsert=dbinsertquery($sql);
        $id=$dbInsert['insertid'];
        if($dbInsert['error']=='')
        {
            $json=array('status'=>'success','id'=>$id,'addnew'=>'true','image'=>$image);
        } else{
            $json=array('status'=>'error','message'=>$dbInsert['error']);
        }
        
      break;
      
      case "move":
        $iconid=intval($_POST['icon_id']);
        $top=intval($_POST['top']);
        $left=intval($_POST['left']);
        $sql="UPDATE it_floorplan_devices SET icon_top='$top', icon_left='$left' WHERE id=$iconid";
        $dbUpdate=dbexecutequery($sql);
        if($dbUpdate['error']=='')
        {
            $json=array('status'=>'success');
        } else{
            $json=array('status'=>'error','message'=>$dbUpdate['error']);
        }
      break;
      
      case "details":
        $iconid=intval($_POST['icon_id']);
        $sql="SELECT A.* FROM it_devices A, it_floorplan_devices B WHERE A.id=B.device_id AND B.id=$iconid";
        $dbDevice=dbselectsingle($sql);
        if($dbDevice['numrows']>0)
        {
            $data=$dbDevice['data'];
            $details="<b>Device Name: </b>".stripslashes($data['device_name'])."<br />";
            $details.="<b>Device IP: </b>".stripslashes($data['device_ip'])."<br />";
            $details.="<b>Device MFG: </b>".stripslashes($data['device_mfg'])."<br />";
            $details.="<b>Device Serial: </b>".stripslashes($data['device_serial'])."<br />";
            $details.="<b>Device Type: </b>".stripslashes($data['device_type'])."<br />";
            $details.="<b>Device Admin: </b>".stripslashes($data['device_admin'])."<br />";
            $details.="<b>Device Password: </b>".stripslashes($data['device_password'])."<br />";
            $details.="<b>Device Notes: </b>".stripslashes($data['device_notes'])."<br />";
            //see if it is assigned to a user
            if($data['assigned_to']!=0)
            {
                //look up user
                $sql="SELECT firstname, lastname, extension,email FROM users WHERE id=$data[assigned_to]";
                $dbUser=dbselectsingle($sql);
                $u=$dbUser['data'];
                $user="<h3>Assigned to:</h3><b>Name: </b>".stripslashes($u['firstname'].' '.$u['lastname'])."<br />";
                $user.="<b>Email: </b>".stripslashes($u['email'])."<br />";
                $user.="<b>Extension: </b>".stripslashes($u['extension'])."<br />";
            } else {
                $user="<br />No user assigned";
            }
            $details.=$user;
            if($data['device_image']!='')
            {
                $details.="<br /><img src='artwork/itdevices/".stripslashes($data['device_image'])."' border=0 width=300 />";
            }
        } else {
            $details="No device is associated with this icon."; 
        }
        
        if($dbUpdate['error']=='')
        {
            $json=array('status'=>'success','details'=>$details);
        } else{
            $json=array('status'=>'error','message'=>$dbUpdate['error']);
        }
      break;
      
      case "detailsfbox":
        $iconid=intval($_GET['icon_id']);
        $sql="SELECT A.* FROM it_devices A, it_floorplan_devices B WHERE A.id=B.device_id AND B.id=$iconid";
        $dbDevice=dbselectsingle($sql);
        if($dbDevice['numrows']>0)
        {
            $data=$dbDevice['data'];
            $details="<div style='float:left;width:350px;'>\n";
            $details.="<b>Device Name: </b>".stripslashes($data['device_name'])."<br />";
            $details.="<b>Device IP: </b>".stripslashes($data['device_ip'])."<br />";
            $details.="<b>Device MFG: </b>".stripslashes($data['device_mfg'])."<br />";
            $details.="<b>Device Serial: </b>".stripslashes($data['device_serial'])."<br />";
            $details.="<b>Device Type: </b>".stripslashes($data['device_type'])."<br />";
            $details.="<b>Device Admin: </b>".stripslashes($data['device_admin'])."<br />";
            $details.="<b>Device Password: </b>".stripslashes($data['device_password'])."<br />";
            $details.="<b>Device Notes: </b>".stripslashes($data['device_notes'])."<br />";
            //see if it is assigned to a user
            if($data['assigned_to']!=0)
            {
                //look up user
                $sql="SELECT firstname, lastname, extension,email FROM users WHERE id=$data[assigned_to]";
                $dbUser=dbselectsingle($sql);
                $u=$dbUser['data'];
                $user="<h3>Assigned to:</h3><b>Name: </b>".stripslashes($u['firstname'].' '.$u['lastname'])."<br />";
                $user.="<b>Email: </b>".stripslashes($u['email'])."<br />";
                $user.="<b>Extension: </b>".stripslashes($u['extension'])."<br />";
            } else {
                $user="<br />No user assigned";
            }
            $details.=$user;
            $details.="</div>\n";
            if($data['device_image']!='')
            {
                $details.="<div style='float:left;width:360px;margin-left:20px;'>\n";
                $details.="<br /><img src='../../artwork/itdevices/".stripslashes($data['device_image'])."' border=0 width=350 />";
                $details.="</div>\n";
            }
            $details.="<div class='clear'></div>\n";
        } else {
            $details="No device is associated with this icon."; 
        }
        print $details;
        dbclose();
        die();
      break;
      
      case "delete":
        $id=intval($_POST['id']);
        $planid=intval($_POST['planid']);
        $sql="DELETE FROM it_floorplan_devices WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        if($dbDelete['error']=='')
        {
            $json=array('status'=>'success');
        } else {
            $json=array('status'=>'error','message'=>$dbDelete['error']);
        }
      break;
      
      case "getrackimage":
        $id=intval($_POST['id']);
        $sql="SELECT A.* FROM it_racks A, it_floorplan_devices B WHERE A.id=B.device_id AND B.id=$id";
        $dbRack=dbselectsingle($sql);
        $rackid=$dbRack['data']['id'];
        $image=stripslashes($dbRack['data']['rack_image']);
        $image='artwork/itfloorplans/'.$image;
        $json=array('status'=>'success','image'=>"<img src='$image' border=0 width=300 />",'id'=>$id,'rackid'=>$rackid);
      break;
      
      case "getrackregions":
        $id=intval($_POST['id']);
        //get all regions for this map
        $sql="SELECT * FROM it_rack_devices WHERE rack_id=$id";
        $dbRegions=dbselectmulti($sql);
        if($dbRegions['numrows']>0)
        {
            $regions=array();
            foreach($dbRegions['data'] as $region)
            {
                $devid=$region['device_id'];
                $sql="SELECT * FROM it_devices WHERE id=$devid";
                $dbDevice=dbselectsingle($sql);
                if($dbDevice['numrows']>0)
                {
                    $data=$dbDevice['data'];
                    $details="<b>Device Name: </b>".stripslashes($data['device_name'])."<br />";
                    $details.="<b>Device IP: </b>".stripslashes($data['device_ip'])."<br />";
                    $details.="<b>Device MFG: </b>".stripslashes($data['device_mfg'])."<br />";
                    $details.="<b>Device Serial: </b>".stripslashes($data['device_serial'])."<br />";
                    $details.="<b>Device Type: </b>".stripslashes($data['device_type'])."<br />";
                    $details.="<b>Device Admin: </b>".stripslashes($data['device_admin'])."<br />";
                    $details.="<b>Device Password: </b>".stripslashes($data['device_password'])."<br />";
                    $details.="<b>Device Notes: </b>".stripslashes($data['device_notes'])."<br />";
                    if($data['device_image']!='')
                    {
                        $details.="<br /><img src='artwork/itdevices/".stripslashes($data['device_image'])."' border=0 width=300 />";
                    }
                } else {
                    $details="No device is associated with this region."; 
                }
                $regions[]=array('id'=>$region['id'],
                                 'deviceid'=>$region['device_id'],
                                 'top'=>$region['region_top'],
                                 'left'=>$region['region_left'],
                                 'width'=>$region['region_width'],
                                 'height'=>$region['region_height'],
                                 'details'=>$details
                                 );
            }
            
        }
        $json=array('status'=>'success','regions'=>$regions);
      break;
  }
  
  echo json_encode($json);
  
  dbclose();