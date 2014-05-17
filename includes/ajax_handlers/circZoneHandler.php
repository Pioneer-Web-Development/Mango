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
        $sql="SELECT * FROM circ_zones WHERE id=$id";
        $dbZone=dbselectsingle($sql);
        $zone=$dbZone['data'];
        $color=$zone['color'];
        $json['status']='success';
        $json['sql']=$sql;
        $json['data']=array("color"=>$color);
      break;
      
      case "getzonemaps":
        $sql="SELECT A.id, A.zone_id, B.color FROM circ_zone_map A, circ_zones B WHERE A.zone_id=B.id ORDER BY A.zone_id";
        $dbMaps=dbselectmulti($sql);
        if($dbMaps['numrows']>0)
        {
            $json['status']='success';
            $json['sql']=$sql;                   
            foreach($dbMaps['data'] as $map)
            {
                //get all the coordinates for the array
                $coords=array();
                $sql="SELECT * FROM circ_zone_map_points WHERE zonemap_id=$map[id]";
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
        $sql="SELECT * FROM circ_zone_map WHERE id=$id";
        $dbCheck=dbselectmulti($sql);
        if($dbCheck['numrows']==0)
        {
            // new one
            $sql="INSERT INTO circ_zone_map (zone_id) VALUES ($zoneid)";
            $dbInsert=dbinsertquery($sql);
            $id=$dbInsert['insertid'];
        } else {
            //update zone id in case it changed
            $sql="UPDATE circ_zone_map SET zone_id=$zoneid WHERE id=$id";
            $dbUpdate=dbexecutequery($sql);
        }
        //clear any points with this id
        $sql="DELETE FROM circ_zone_map_points WHERE zonemap_id=$id";
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
                $sql="INSERT INTO circ_zone_map_points (zonemap_id, lat, lon) VALUES $values";
                $dbInsert=dbinsertquery($sql);
            }
        }
        $json['status']='success';
        $json['zonemapid']=$id;
      break;
      
      case "removezonemap":
        $id=intval($_POST['mapid']);
        $sql="DELETE FROM circ_zone_map WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $sql="DELETE FROM circ_zone_map_points WHERE zonemap_id=$id";
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
        $sql="SELECT * FROM circ_racks";
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
      
      case "findnearest":
        
        $map['lat']=$_POST['slat'];
        $map['lon']=$_POST['slon'];
        /*
            $street=$_POST['street'];
            $city=$_POST['city'];
            $zip=$_POST['zip'];
            $map=geocode("$street, $city $zip");
        */
        //get all locations
        $sql="SELECT * FROM circ_racks";
        $dbRacks=dbselectmulti($sql);
        if($dbRacks['numrows']>0)
        {
            //geocode the starting address
            $mindistance=9999;
            $wid=0;
            foreach($dbRacks['data'] as $rack)
            {
                $R = 6371; // km
                $lat1=$map['lat'];
                $lat2=$rack['lat'];
                $lon1=$map['lon'];
                $lon2=$rack['lon'];
                $dist = acos(sin($lat1)*sin($lat2)+cos($lat1)*cos($lat2) * cos($lon2-$lon1)) * $R;
                $dist=$dist/1.609344; //convert miles to kilometers
                if($dist<$mindistance){$mindistance=$dist;$wid=$rack['id'];$crack=$rack;}
            }
            $json['startlat']=$map['lat'];
            $json['startlon']=$map['lon'];
            $json['endlat']=$rack['lat'];
            $json['endlon']=$rack['lon'];
            $json['distance']=$mindistance;   
            if($wid!=0)
            {
               $json['status']='success';
               $json['message']="The nearest location to pick up the paper will be at $crack[location_name], located at $crack[street] in $crack[city].";  
            } else {
               $json['status']='error';
               $json['message']='There are no nearby racks or stores. Please consider a digital only membership.'; 
            }
        } else {
            $json['status']='error';
            $json['message']='There are no racks or stores in your area. Please consider a digital only membership.';
        }
        $pointLocation = new pointLocation();
        $locPoint = array('y'=>$map['lat'],'x'=>$map['lon']);
        
        //going to have to loop through every zonemap
        $sql="SELECT * FROM circ_zone_map";
        $dbZoneMaps=dbselectmulti($sql);
        if($dbZoneMaps['numrows']>0)
        {
            $json['in_zone']='This address is outside of our delivery area.';
            foreach($dbZoneMaps['data'] as $zonemap)
            {
                $sql="SELECT * FROM circ_zone_map_points WHERE zonemap_id=$zonemap[id]";
                $dbPoints=dbselectmulti($sql);
                if($dbPoints['numrows']>0)
                {
                    $polygon=array();
                    foreach($dbPoints['data'] as $point)
                    {
                        //$polygon[]=abs($point['lat']).','.abs($point['lon']);    
                        $polygon[]=array('y'=>$point['lat'],'x'=>$point['lon']);    
                    }
                    $test=$pointLocation->pointInPolygon($locPoint, $polygon);
                    if ($test=='outside')
                    {
                        //echo 'Was not in zonemap '.$zonemap['id'].'<br>';
                         
                    } else {
                        
                        $sql="SELECT * FROM circ_zones WHERE id=".$zonemap['zone_id'];
                        $dbZone=dbselectsingle($sql);
                        $zonename=stripslashes($dbZone['data']['name']);
                        $json['in_zone']="That address is in zone $zonename";
                        //echo 'Found the point in zonemap '.$zonemap['id'].' with '.$test.'<br>';
                        
                    }    
                }
            }
        } else {
            $json['in_zone']='No zones set up.';
        }
        
      break;
  }
  echo json_encode($json);
  dbclose();


  ?>
