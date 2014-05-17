<?php
  include("../functions_db.php");
  include("../functions_common.php");
 
  $action=$_POST['action'];
  $json['status']='executing';
  $json['action']=$action;
  switch($action)
  {
      
      case "getzoneinfo":
        $id=intval($_POST['id']);
        $sql="SELECT * FROM advertising_zones WHERE id=$id";
        $dbZone=dbselectsingle($sql);
        $zone=$dbZone['data'];
        $color=$zone['color'];
        $json['status']='success';
        $json['sql']=$sql;
        $json['data']=array("color"=>$color);
      break;
      
      case "getzonemaps":
        $sql="SELECT A.id, A.zone_id, B.color FROM advertising_zone_map A, advertising_zones B WHERE A.zone_id=B.id ORDER BY A.zone_id";
        $dbMaps=dbselectmulti($sql);
        if($dbMaps['numrows']>0)
        {
            $json['status']='success';
            $json['sql']=$sql;                   
            foreach($dbMaps['data'] as $map)
            {
                //get all the coordinates for the array
                $coords=array();
                $sql="SELECT * FROM advertising_zone_map_points WHERE zonemap_id=$map[id]";
                $dbCoords=dbselectmulti($sql);
                if($dbCoords['numrows']>0)
                {
                    foreach($dbCoords['data'] as $coord)
                    {
                        $coords[]=array('lat'=>$coord['lat'],'lon'=>$coord['lon']);
                    }
                }
                $json['maps'][]=array('zonemapid'=>$map['id'],
                                      'zoneid'=>$map['zone_id'],
                                      'color'=>$map['color'],
                                      'coords'=>$coords);        
            }
        } else {
            $json['status']='error';
            $json['message']='No zone maps found';
            $json['sql']=$sql;
        }
      break;
      
      case "savezonemap":
        $id=intval($_POST['mapid']);
        $zoneid=intval($_POST['zoneid']);
        //see if this is a new or existing map
        $sql="SELECT * FROM advertising_zone_map WHERE id=$id";
        $dbCheck=dbselectmulti($sql);
        if($dbCheck['numrows']==0)
        {
            // new one
            $sql="INSERT INTO advertising_zone_map (zone_id) VALUES ($zoneid)";
            $dbInsert=dbinsertquery($sql);
            $id=$dbInsert['insertid'];
        } else {
            //update zone id in case it changed
            $sql="UPDATE advertising_zone_map SET zone_id=$zoneid WHERE id=$id";
            $dbUpdate=dbexecutequery($sql);
        }
        //clear any points with this id
        $sql="DELETE FROM advertising_zone_map_points WHERE zonemap_id=$id";
        $dbDelete=dbexecutequery($sql);
        
        //loop through the passed points and add them
        //will come in a format of pointLat,pointLon|
        $coords=explode("|",$_POST['coords']);
        if(count($coords)>0)
        {
            foreach($coords as $key=>$coord)
            {
                if(trim($coord)!='')
                {
                    $parts=explode(",",$coord);
                    $lat=$parts[0];
                    $lon=$parts[1];
                    $values.="($id,$lat,$lon),";
                }
            }
            $values=substr($values,0,strlen($values)-1);
            if(trim($values)!='')
            {
                $sql="INSERT INTO advertising_zone_map_points (zonemap_id, lat, lon) VALUES $values";
                $dbInsert=dbinsertquery($sql);
            }
        }
        $json['status']='success';
        $json['zonemapid']=$id;
      break;
      
      case "removezonemap":
        $id=intval($_POST['mapid']);
        $sql="DELETE FROM advertising_zone_map WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $sql="DELETE FROM advertising_zone_map_points WHERE zonemap_id=$id";
        $dbDelete=dbexecutequery($sql);
        $json['status']='success';
      break;
      
      case "init":
        $json['status']='success';
        $sql="SELECT google_map_key, officeLat, officeLon FROM core_preferences";
        $dbKey=dbselectsingle($sql);
        $googlekey=stripslashes($dbKey['data']['google_map_key']);
        if($dbKey['data']['officeLat']!='' && $dbKey['data']['officeLat']!=0) { 
            $json['defaultLat']=$dbKey['data']['officeLat'];
            $json['defaultLon']=$dbKey['data']['officeLon'];
        } else {
            $json['defaultLat']=43.57939;
            $json['defaultLon']=-116.55910;
        }
        
        //get all locations for the map
        $sql="SELECT * FROM advertising_racks";
        $dbLocations=dbselectmulti($sql);
        if($dbLocations['numrows']>0)
        {
            foreach($dbLocations['data'] as $location)
            {
                $json['locations'][]=array("id"=>$location['id'],
                                                        "lat"=>$location['lat'],
                                                        "lon"=>$location['lon'],
                                                        "name"=>$location['location_name'],
                                                        "icon"=>$location['icon'],
                                                        "info"=>$location['street'].'<br>'.$location['info']);
            }
        }
      break;
      
      
  }
  echo json_encode($json);
  dbclose();

 
  
  ?>
