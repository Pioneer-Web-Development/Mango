<?php
include("includes/mainmenu.php");
show_map();
function show_map()
{
    global $sales;
    $sql="SELECT * FROM core_preferences";
    $dbPrefs=dbselectsingle($sql);
    $prefs=$dbPrefs['data'];
    
    $sales[0]='All sales staff';
    
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
       $minZone=0;
    }
    $zones['all']='All zones';
    
     
    $sql="SELECT DISTINCT(category) FROM advertising_account_mapping ORDER BY category";
    $dbCats=dbselectmulti($sql);
    $categories['all']='Show all categories'; 
    if($dbCats['numrows']>0)
    {
        foreach($dbCats['data'] as $cat)
        {
            if(trim($cat['category'])!='')
            {
                $categories[$cat['category']]=$cat['category'];
            } else {
                $categories['blank']='No Category';
            }
        }    
    }
    if($_POST['category'])
    {
        $category=$_POST['category'];
    } else {
        $category='all';
    } 
    if($_POST['revenue'])
    {
        $revenue=$_POST['revenue'];
    } else {
        $revenue='all';
    } 
    if($_POST['zone'])
    {
        $zone=$_POST['zone'];
    } else {
        $zone='all';
    }
     
    $revenues=array("all"=>"Any revenue","0"=>"No revenue","1000"=>"Over \$1000","2500"=>"Over \$2500","5000"=>"Over \$5000","10000"=>"Over \$10000");
    
    print "<div style='padding:10px;border: 1px solid black;background-coloe:#efefef;margin-bottom:10px;'>\n";
    print "<form method=post>\n";
    print "<div style='float:left;width:500px;'>\n";
    make_select('salesid',$sales[$_POST['salesid']],$sales,'Sales Rep');
    make_select('zone',$zones[$zone],$zones,'Zone');
    make_select('category',$categories[$category],$categories,'Category');
    print "</div>\n";
    print "<div style='float:left;width:500px;'>\n";
    make_select('revenue',$revenues[$revenue],$revenues,'YTD Revenue');
    make_checkbox('print',0,'Large size','Check for a large print-size map');
    print "</div><div class='clear'></div>\n";
    make_submit('submit','Filter Results');
    print "</form>\n";
    print "</div>\n";
    $sql="SELECT * FROM advertising_map_defaults";
    $dbSettings=dbselectsingle($sql);
    $settings=$dbSettings['data'];
    
    
    if($dbPrefs['data']['officeLat']!='' && $dbPrefs['data']['officeLat']!=0) { 
        $defaultLat=$dbPrefs['data']['officeLat'];
        $defaultLon=$dbPrefs['data']['officeLon'];
    } else {
        $defaultLat=43.57939;
        $defaultLon=-116.55910;
    }
    
    if($_POST)
    {
        if($_POST['salesid']!=0)
        {
            $salesid=" AND sales_id=".$_POST['salesid']; 
        }
        if($_POST['revenue']!='')
        {
            if($_POST['revenue']=='all')
            {
                $searchrevenue=" AND ytd_revenue>0";
            } elseif($_POST['revenue']=='0')
            {
                $searchrevenue=" AND ytd_revenue=0";
            } else {
                $searchrevenue=" AND ytd_revenue>".$_POST['revenue'];
            } 
        }
        if($_POST['category']!='all')
        {
            if($_POST['category']=='blank')
            {
                $searchcategory=" AND category=''";
            } else {
                $searchcategory=" AND category='".$_POST['category']."'";
            }
        }
        if($_POST['zone']!='all')
        {
            $searchzone=" AND zone_id='".$_POST['zone']."'";
        }
        $accountsql="SELECT * FROM advertising_account_mapping WHERE lat<>'' AND lon<>'' $salesid $searchcategory $searchrevenue $searchzone";
    } else {
        $searchcategory=" AND category<>''";
        $searchrevenue=" AND ytd_revenue>0";
        $accountsql="SELECT * FROM advertising_account_mapping WHERE lat<>'' AND lon<>'' $searchcategory $searchrevenue";
    }
    //print "Account sql is $accountsql<br>";
    ?>

<style type='text/css'>
<?php if ($_GET['mode']=='print' || $_POST['print'])
{
  ?>
  #mapCanvas {
    border: thin solid black;
    width: <?php echo $settings['map_width']; ?>px;
    height: <?php echo $settings['map_height']; ?>px;
}

  <?php  
} else {?>

#mapCanvas {
    border: thin solid black;
    width: 100%;
    height: 600px;
}
<?php } ?>
#legend {
    margin-top:20px;
    padding:10px;
    font-family:Trebuchet MS, Arial, sans-serif;
    font-size:10px;
}
</style>

<script type="text/javascript"> 
 
var center;
var map = null;
var currentPopup;
var icon = Array();
var overlay, image, selectedShape, 
      polys         = new Array(),
      poly          = new Array(),
      zonemapid     = 0,
      selectedColor = '',
      selectedShape = '',
      bounds        = new google.maps.LatLngBounds(),
      zoneid        = <?php echo $minZone ?>,
      zoom          = <?php echo  $settings['zoom_level']; ?>,
      drawingManager= null;
  
//need to create an icon for each rep
<?php
    
    $sales[0]="Not assigned";
    $i=1;
    print "icon[0] = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=0|00CC99|000000';\n";
    $reps.="0 - No rep assigned<br>";
    foreach($sales as $key=>$s)
    {
        print "icon[$key] = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=$i|00CC99|000000';\n";
        $reps.="$i - ".$s."<br>";
        $i++;
    }

    
?>
//var icon = "http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=A|00CC99|000000";

var bounds = new google.maps.LatLngBounds();
function addMarker(id, lat, lng, info, salesid) {
    var pt = new google.maps.LatLng(lat, lng);
    bounds.extend(pt);
    var marker = new google.maps.Marker({
        id: id,
        position: pt,
        icon: icon[salesid],
        map: map
    });
    var popup = new google.maps.InfoWindow({
        content: info,
        maxWidth: 400
    });
    google.maps.event.addListener(marker, "click", function() {
        if (currentPopup != null) {
            currentPopup.close();
            currentPopup = null;
        }
        popup.open(map, marker);
        currentPopup = popup;
    });
    google.maps.event.addListener(popup, "closeclick", function() {
        currentPopup = null;
    });
};
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
    getPolygons();
    <?php
    //get the mapping stuff
    
    $dbAccounts=dbselectmulti($accountsql);
    if($dbAccounts['numrows']>0)
    {
        $i=1;
        foreach($dbAccounts['data'] as $item)
        {
            $ballooninfo=$item['category']."<br>";
            $ballooninfo=$item['account_name']."<br>";
            $ballooninfo.=$item['address']."<br>";
            $ballooninfo.=$item['city'].' '.$item['state'].' '.$item['zip']."<br>";
            $ballooninfo.=$item['phone']."<br>";
            $ballooninfo.="Contact: ".$item['contact']."<br>";
            $ballooninfo.="YTD Revenue: ".$item['ytd_revenue']."<br>";
            $ballooninfo.="Rep: ".$sales[$item['sales_id']];
            
            print "addMarker($i,$item[lat],$item[lon],'".addslashes($ballooninfo)."',$item[sales_id]);\n";
            $i++;
        }
    }
    ?>
     
  }); 

  
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
  
</script>

<div id="mapCanvas"><div style='margin-top:50px;text-align:center;margin-left:auto;margin-right:auto;'>Loading...<br><img src='../artwork/icons/ajax-loader.gif'></div></div>
<div id='legend'>
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
    print $reps;
?>
   
    
</div>
</body>
</html>
<?php
 
}
footer();
?>
