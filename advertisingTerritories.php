<?php
include("includes/mainmenu.php") ;

if($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}

switch ($action)
{
    case "add":
        zones('add');
    break;
    
    case "edit":
        zones('edit');
    break;
    
    case "Save Territory":
        save_zone('insert');
    break;
    
    case "Update Territory":
        save_zone('update');
    break;
    
    case "drawzones":
        draw_zones();
    break;
    
    case "zoneaccounts":
        zone_accounts();
    break;
    
    case "list":
        zones('list');
    break;
        
    default:
        zones('list');
    break;   
}

function zones($action)
{
    global $sales;
    if($action=='add' || $action=='edit')
    {
        if($action=='add')
        {
            $button='Save Territory';
            $color='#cccccc'; 
            $salesid=0;   
        } else {
            $button='Update Territory';
            $id=intval($_GET['id']);
            $sql="SELECT * FROM advertising_zones WHERE id=$id";
            $dbZone=dbselectsingle($sql);
            $zone=$dbZone['data'];
            $color=stripslashes($zone['color']);
            $name=stripslashes($zone['name']);
            $salesid=stripslashes($zone['sales_rep']);
        }
        print "<form method=post>\n";
        make_text('name',$name,'Name','Descriptive name of territory');
        make_color('color',$color,'Color','Color to be used on the map');
        make_select('sales_id',$sales[$salesid],$sales,'Sales Rep','Rep assigned to this territory');
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM advertising_zones ORDER BY name";
        $dbZones=dbselectmulti($sql);
        
        tableStart("<a href='?action=add'>Add new territory</a>,<a href='?action=drawzones'>Manage Territory Map</a>,<a href='?action=zoneaccounts'>Apply Territories to accounts</a>","Name,Sales Rep",4,'',0);
        if($dbZones['numrows']>0)
        {
            foreach($dbZones['data'] as $zone)
            {
                print "<tr>";
                print "<td>".$zone['name']."</td>";
                print "<td>".$sales[$zone['sales_rep']]."</td>";
                print "<td><a href='?action=edit&id=$zone[id]'>Edit</a></td>";
                print "<td><a href='?action=delete&id=$zone[id]' class='delete'>Delete</a></td>";
                print "</tr>";
            }
        }
        tableEnd($dbZones); 
        
    }
}

function save_zone($action)
{
    $id=intval($_POST['id']);
    $color=addslashes($_POST['color']);
    $name=addslashes($_POST['name']);
    $salesid=$_POST['sales_id'];
    if($action=='insert')
    {
        $sql="INSERT INTO advertising_zones (name, color, sales_rep) VALUES ('$name', '$color', '$salesid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE advertising_zones SET name='$name', color='$color', sales_rep='$salesid' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
     if ($error!='')
    {
        setUserMessage('There was a problem saving the territory.<br>'.$error,'error');
    } else {
        setUserMessage('The territory has been successfully saved','success');
    }
    redirect("?action=list");
}

function draw_zones()
{
    $sql="SELECT google_map_key, officeLat, officeLon FROM core_preferences";
    $dbKey=dbselectsingle($sql);
    $key=stripslashes($dbKey['data']['google_map_key']);
    if($dbKey['data']['officeLat']!='' && $dbKey['data']['officeLat']!=0) { 
        $defaultLat=$dbKey['data']['officeLat'];
        $defaultLon=$dbKey['data']['officeLon'];
    } else {
        $defaultLat=43.57939;
        $defaultLon=-116.55910;
    }
    $sql="SELECT * FROM advertising_zones ORDER BY name";
    $dbTypes=dbselectmulti($sql);
    if($dbTypes['numrows']>0)
    {
        $minZone=$dbTypes['data'][0]['id'];
        foreach($dbTypes['data'] as $loc)
        {
            $zones[$loc['id']]=stripslashes($loc['name']);
            $legend[]=array("color"=>$loc['color'],"name"=>$loc['name']);      
        }
    } else {
        die("You must first define some zones");
    }
    
    
    ?>
    <div id='mapCanvas' style='width:800px;height:600px;border:thin solid black;float:left;margin-right:10px;'></div>
    <div style="float:left;background-color: black; padding:8px;color:white;font-family: 'Squada One', cursive; color: #d2ff52;">
    <h3>Legend</h3>
    <?php
        if(count($legend)>0)
        {
            foreach($legend as $key=>$entry)
            {
                print "<div style='margin-bottom:10px;width:30px;height30px;float:left;margin-right:4px;background-color:$entry[color]'>&nbsp;</div>
                <div style='margin-bottom:10px;height30px;float:left;'>$entry[name]</div>
                <div class='clear'></div>\n";
            }
        }
    ?>
    </div>
    <div class='clear'></div>
    <button id="delete-button" onclick='removeSelection()'>Delete Selected Shape</button><br>
    <?php
        make_select('zone',$zones[0],$zones,'Zone','','',false,'getZoneInfo()');
    ?>
  <script type='text/javascript'>
      var overlay, image, selectedShape, 
      polys         = new Array(),
      map           = null,
      selectedColor = null,
      poly          = new Array(),
      zonemapid     = 0,
      bounds        = new google.maps.LatLngBounds(),
      zoom          = 12,
      zoneid        = <?php echo $minZone ?>,
      drawingManager= null;
  
  function drawPolygon(id, poly, zoneid, color) {
    // Construct the polygon
    // Note that we don't specify an array or arrays, but instead just
    // a simple array of LatLngs in the paths property
    var options = { paths: poly,
      strokeWeight: 0,
      fillColor: selectedColor,
      fillOpacity: 0.45,
      zIndex: 1
    };
      newPoly = new google.maps.Polygon(options);
      newPoly.id = id;
      newPoly.zoneid = zoneid;
      newPoly.color = color;
      newPoly.setMap(map);
      
      google.maps.event.addListener(newPoly, 'click', function() {
        this.setEditable(true);
        setSelection(this);
      });
      polys.push(newPoly);
      
  }

  function getPolygons() {
    $.ajax({
      url: "includes/ajax_handlers/advertisingTerritoryHandler.php",
      type: "POST",
      data: ({action:'getzonemaps'}),
      dataType: "json",
      success: function(response){
          if(response.status=='success')
          {
             jQuery.each(response.maps, function(i, zonemap) {
                 zoneid=zonemap.zoneid;
                 selectedColor=zonemap.color;
                 zonemapid=zonemap.zonemapid;
                 //now go through the points
                 poly   = new Array();
                 jQuery.each(zonemap.coords, function(i, coord) {
                    poly.push(new google.maps.LatLng(coord.lat, coord.lon));
                 });
                 drawPolygon(zonemapid, poly, zoneid, selectedColor );
             
             });
             
          } else {
             alert(response.message);
          }
          
      }
    })
  }

  function storePolygon(shape)
  {
    var path=shape.getPath();
    var coords='';
    var coord='';
    for (var i = 0; i < path.length; i++) {
      coord = path.getAt(i);
      coords=coords+coord.lat() + "," + coord.lng() + "|"; 
    }
    $.ajax({
      url: "includes/ajax_handlers/advertisingTerritoryHandler.php",
      type: "POST",
      data: ({action:'savezonemap',
              mapid:shape.id,
              zoneid:shape.zoneid,
              coords: coords
            }),
      dataType: "json",
      success: function(response){
          if(response.status=='success')
          {
             shape.id=response.zonemapid;
             drawingManager.setDrawingMode(null);
        
          } else {
             alert(response.message);
          }
          
      }
    })
  }
  
  function clearSelection() {
    if (!selectedShape) return;
    storePolygon(selectedShape);
    selectedShape.setEditable(false);
    selectedShape = null;
  }

  function setSelection(shape) {
    clearSelection();
    selectedShape = shape;
    shape.setEditable(true);
    $('#zone').val(selectedShape.zoneid);
  }
  
  function removeSelection()
  {
     if (selectedShape) {
         $.ajax({
          url: "includes/ajax_handlers/advertisingTerritoryHandler.php",
          type: "POST",
          data: ({action:'removezonemap',
                  mapid:selectedShape.id
                }),
          dataType: "json",
          success: function(response){
              if(response.status=='success')
              {
                  selectedShape.setMap(null);
                  selectedShape = null;
                  drawingManager.setDrawingMode(null);
              } else {
                 alert(response.message);
              }
              
          }
        })
     } 
  }

  $(function() {
    var lat=<?php echo $defaultLat ?>;
    var lon=<?php echo $defaultLon ?>;
    center = new google.maps.LatLng(lat,lon);
        
    //Basic
    var MapOptions = {
      zoom: zoom,
      center: center,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    // Init the map
    map = new google.maps.Map(document.getElementById("mapCanvas"),MapOptions);

    // Define the map styles (optional)
    /*
    var mapStyle = [{
      stylers: [{ saturation: -65 }, { gamma: 1.52 }] }, {
      featureType: "administrative", stylers: [{ saturation: -95 }, { gamma: 2.26 }] }, {
      featureType: "water", elementType: "labels", stylers: [{ visibility: "off" }] }, {
      featureType: "administrative.locality", stylers: [{ visibility: 'off' }] }, {
      featureType: "road", stylers: [{ visibility: "simplified" }, { saturation: -99 }, { gamma: 2.22 }] }, {
      featureType: "poi", elementType: "labels", stylers: [{ visibility: "off" }] }, {
      featureType: "road.arterial", stylers: [{ visibility: 'off' }] }, {
      featureType: "road.local", elementType: "labels", stylers: [{ visibility: 'off' }] }, {
      featureType: "transit", stylers: [{ visibility: 'off' }] }, {
      featureType: "road", elementType: "labels", stylers: [{ visibility: 'off' }] }, {
      featureType: "poi", stylers: [{ saturation: -55 }]
    }];

    map.setOptions({styles: mapStyle});
    */
    getPolygons();

    drawingManager = new google.maps.drawing.DrawingManager({
      drawingControl: true,
      drawingControlOptions: {
        position: google.maps.ControlPosition.TOP_RIGHT,
        drawingModes: [google.maps.drawing.OverlayType.POLYGON]
      },

      polygonOptions: {
        fillColor: selectedColor,
        fillOpacity: 0.45,
        strokeWeight: 0,
        clickable: true,
        zIndex: 10,
        editable: true
      }
    });

    drawingManager.setMap(map);

    google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
      // Add an event listener that selects the newly-drawn shape when the user
      // mouses down on it.
      var newShape = e.overlay;

      newShape.type = e.type;
      newShape.id=0;
      newShape.zoneid=zoneid;
      newShape.color=selectedColor;
      google.maps.event.addListener(newShape, 'click', function() {
        setSelection(this);
      });

      setSelection(newShape);
      storePolygon(newShape);
      newShape.setEditable(false);
    });

    google.maps.event.addListener(map, 'click', clearSelection);

      drawingManager.setDrawingMode(null);
      
  });
  
    function addMarker(id,lat, lon, info, iconType)
    {
        var pt = new google.maps.LatLng(lat, lon);
        var marker = new google.maps.Marker({
            id: id,
            position: pt,
            map: map,
            icon: iconType
        });
        var popup = new google.maps.InfoWindow({
            content: info,
            maxWidth: 300
        });
        google.maps.event.addListener(marker, "click", function() {
            popup.open(map, marker);
        });
        
    };
    
    function getZoneInfo()
    {
        zoneid=$('#zone').val();
        if(zoneid!=0)
        {
            $.ajax({
              url: "includes/ajax_handlers/advertisingTerritoryHandler.php",
              type: "POST",
              data: ({action:'getzoneinfo',
                      id:zoneid
              }),
              dataType: "json",
              success: function(response){
                  if(response.status=='success')
                  {
                     selectedColor=response.data.color;
                     var polygonOptions = drawingManager.get('polygonOptions');
                     polygonOptions.fillColor = selectedColor;
                     drawingManager.set('polygonOptions', polygonOptions);
                     if(selectedShape)
                     {
                        selectedShape.setEditable(true);
                        selectedShape.set('fillColor', selectedColor);
                        selectedShape.zoneid=zoneid;
                     }
                  } else {
                     alert(response.message);
                  }
                  
              }
            })
        } else {
            selectedColor=null;
        }
    }
    $('#drawzone').click(function(){
          if(selectedColor!=null)
          {
              drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
          } else {
              alert('Select a territory first');
          }
          
    })
    function displayPath(path)
    {
       $('#overlaypoints').html("points="+path.getPath().getArray());
    }
    </script>
    <?php
        
}

function zone_accounts()
{
    if($_GET['resetall'])
    {
        $sql="UPDATE advertising_account_mapping SET zoning_run=0";
        $dbUpdate=dbexecutequery($sql);
        print "All account reset...<br>";
    }
    $batchsize=1000;
    $sql="SELECT COUNT(id) as tcount FROM advertising_account_mapping WHERE lat IS NOT Null";
    $dbTotal=dbselectsingle($sql);
    $total=$dbTotal['data']['tcount'];
    $sql="SELECT COUNT(id) as zcount FROM advertising_account_mapping WHERE zoning_run=0";
    $dbCount=dbselectsingle($sql);
    $remaining=$dbCount['data']['zcount'];
    $tp=round($remaining/$total,2)*100;
    
    print "There are ".$remaining." accounts out of $total ($tp% of total) that have not been assigned to a territory. <br><br>";
    $sql="SELECT * FROM advertising_account_mapping WHERE zoning_run=0 ORDER BY RAND() LIMIT $batchsize";
    $dbAccounts=dbselectmulti($sql);
    if($dbAccounts['numrows']>0)
    {
        $zoned=0;
        $unzoned=0;
        $ids='';
        foreach($dbAccounts['data'] as $account)
        {
            $lat=$account['lat'];
            $lon=$account['lon'];
            $id=$account['id'];
            $ids="$id,";
            $test=checkInZone($lat,$lon);
            $zoneid=$test['zone_id'];
            $sql="UPDATE advertising_account_mapping SET zone_id=$zoneid, zoning_run=1 WHERE id=$id";
            $dbUpdate=dbexecutequery($sql);
            if($zoneid!=0)
            {
               $zoned++; 
            } else {
                $unzoned++;
            }
            
        }
        print "A total of $zoned account were zoned, while $unzoned were set to a '0' zone.<br>";  
    }
    print "<br><a href='?action=zoneaccounts'>Apply territories to another batch of $batchsize accounts</a><br>";
    print "<br><a href='?action=zoneaccounts&resetall=true'>Reset all accounts to unzoned status.</a><br>";
}

function checkInZone($lat,$lon)
{
    $pointLocation = new pointLocation();
    $locPoint = array('y'=>$lat,'x'=>$lon);
    
    $zone=0;
    //going to have to loop through every zonemap
    $sql="SELECT * FROM advertising_zone_map ORDER BY zone_id";
    $dbZoneMaps=dbselectmulti($sql);
    if($dbZoneMaps['numrows']>0)
    {
        foreach($dbZoneMaps['data'] as $zonemap)
        {
            $sql="SELECT * FROM advertising_zone_map_points WHERE zonemap_id=$zonemap[id]";
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
                    $zone=$zonemap['zone_id'];
                    //echo 'Found the point in zonemap '.$zonemap['id'].' with '.$test.'<br>';
                    
                }    
            }
        }
    }
    $sql="SELECT advertising_zone_name FROM zones WHERE id=$zone";
    $dbName=dbselectsingle($sql);
    $name=$dbName['data']['zone_name'];
    return array('zone_id'=>$zone,'name'=>$name,'sql'=>$sql);
}

footer();
?> 