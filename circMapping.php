<?php
  include("includes/mainmenu.php");
  
 display();
  
function display()
{
    $sql="SELECT google_map_key, officeLat, officeLon FROM core_preferences";
    $dbKey=dbselectsingle($sql);
    $key=stripslashes($dbKey['data']['google_map_key']);
    print "<script type='text/javascript' src='http://maps.google.com/maps/api/js?key=$key&sensor=false&libraries=drawing'></script>\n";
    if($dbKey['data']['officeLat']!='' && $dbKey['data']['officeLat']!=0) { 
        $defaultLat=$dbKey['data']['officeLat'];
        $defaultLon=$dbKey['data']['officeLon'];
    } else {
        $defaultLat=43.57939;
        $defaultLon=-116.55910;
    }
    
    ?>
    <div style='font-size:14px;'>
    <p style='font-weight:bold;'>Check if address is in delivery area:</p>
    Address: <input type='text' id='address' name='address' placeholder='Address' value='' style='display:inline;width:300px;margin-right:10px;'>    
    City: <input type='text' id='city' name='city' placeholder='City' value='' style='display:inline;width:200px;margin-right:10px;'>    
    Zip: <input type='text' id='zip' name='zip' placeholder='Zipcode' value='' style='display:inline;width:100px;margin-right:10px;'>    
    <input type='button' onclick='findNearest();' value='Check Address' /> <br /> <br />
    <span id='nearest' style='font-weight:bold;font-size:14px;'></span>
    </div>
    <div id='mapCanvas' style='width:800px;height:500px;border:thin solid black;'></div>
    <p><b>Legend:</b></p>
    Shaded areas indicate delivery zones.<br />
    <script type="text/javascript">
    var geocoder = new google.maps.Geocoder();
    var map;
    var marker;
    var currentPopup;
    var bounds = new google.maps.LatLngBounds();
    var center;
    var icon;
    
    
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
    function getPolygons() {
        $.ajax({
          url: "includes/ajax_handlers/circZoneHandler.php",
          type: "POST",
          data: ({action:'getzonemaps'}),
          dataType: "json",
          success: function(response){
              if(response.status=='success')
              {
                  if(response.maps)
                  {
                      jQuery.each(response.maps, function(i, zonemap) {
                         //now go through the points
                         poly   = new Array();
                         jQuery.each(zonemap.coords, function(i, coord) {
                            poly.push(new google.maps.LatLng(coord.lat, coord.lon));
                         });
                         drawPolygon(poly, zonemap.color );
                     
                     });
                  }
              } else {
                 //alert(response.message);
              }
              
          }
        })
      }
    
    function drawPolygon(poly, color) {
        // Construct the polygon
        // Note that we don't specify an array or arrays, but instead just
        // a simple array of LatLngs in the paths property
        var options = { paths: poly,
          strokeWeight: 0,
          fillColor: color,
          fillOpacity: 0.45,
          zIndex: 1};

          newPoly = new google.maps.Polygon(options);
          newPoly.setMap(map);
          
    }
    
    function initialize() {
        $.ajax({
          url: "includes/ajax_handlers/circZoneHandler.php",
          type: "POST",
          data: ({action:'init'}),
          dataType: "json",
          success: function(response){
              if(response.status=='success')
              {
                  var lat=response.defaultLat;
                  var lon=response.defaultLon;
                  center = new google.maps.LatLng(lat,lon);
                  var myOptions = {
                    zoom:13,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    center: center
                  }
                  map = new google.maps.Map(document.getElementById("mapCanvas"), myOptions);
                  center = bounds.getCenter();
                  if(response.locations)
                  {
                      jQuery.each(response.locations, function(i, rack) {
                         //now go through the locations
                         if(rack.icon=='pay')
                        {
                            icon="artwork/icons/newspaper_rack_24_pay.png";
                        } else {
                            icon="artwork/icons/newspaper_rack_24.png";
                        }
                        addMarker(rack.id,rack.lat,rack.lon,"<div style='font-size:12px;padding:3px;line-height:14px;'><b>"+rack.name+"</b><br><br>"+rack.info+"</div>",icon);
                
                     });
                  }
                  getPolygons();
                 
              } else {
                 //alert(response.message);
              }
              
          }
        })
    }

    // Onload handler to fire off the app.
    google.maps.event.addDomListener(window, 'load', initialize);
    
    function findNearest()
    {
        var alat, alon;
        var address = $('#address').val()+' '+$('#city').val()+' '+$('#zip').val();
        geocoder.geocode( { 'address': address}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            alat=results[0].geometry.location.Ya;
            alon=results[0].geometry.location.Za;
            $.ajax({
              url: "includes/ajax_handlers/circZoneHandler.php",
              type: "POST",
              //data: ({action:'findnearest',street:$('#address').val(),city:$('#city').val(),zip:$('#zip').val()}),
              data: ({action:'findnearest',slat:alat,slon:alon}),
              dataType: "json",
              success: function(response){
                if(response.status=='success')
                {
                    $('#nearest').html(response.message+'<br>'+response.in_zone);
                } else {
                    $('#nearest').html(response.message);
                }  
              }
            })
          } else {
             $('#nearest').html("We had trouble geocoding your address. Please double check it, make sure you include things like direction and street type (Road, Ave, etc.). Then try again.");
              //alert("Geocode was not successful for the following reason: " + status);
          }
        }); 
        
    }
    
    
    
    </script>
    <?php
}
?>
