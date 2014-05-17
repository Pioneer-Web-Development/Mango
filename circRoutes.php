<?php
/*
*
* THE PURPOSE OF THIS SCRIPT IS TO MANAGE ROUTES FOR CIRCULATION. TOP LEVEL IS ROUTE CREATION
* A ROUTE WILL HAVE A NAME, NOTES, NUMBER AND TYPE (MOTOR, ETC)
* 
* THEN THERE WILL BE THE ABILITY TO IMPORT A ROUTE LIST FROM DTI IN A PIPE DELIMITED FORMAT
* THIS DATA WILL BE STORED IN ANOTHER TABLE WITH LAT/LON INFORMATION AND GOOGLE OPTIMIZED STREET INFORMATION
* 
* 
*/

include("includes/mainmenu.php");

if($_POST)
{
  $action=$_POST['submit'];
} else {
  $action=$_GET['action'];
}

switch($action)
{
  case "addroute":
  routes('add');
  break;
  case "editroute":
  routes('edit');
  break;
  case "deleteroute":
  routes('delete');
  break;
  
  case "listroutes":
  routes('list');
  break;
  
  case "Save Route":
  save_route('insert');
  break;
  
  case "Update Route":
  save_route('update');
  break;
  
  
  case "addaddress":
  addresses('add');
  break;
  
  case "editaddress":
  addresses('edit');
  break;
  
  case "deleteaddress":
  addresses('delete');
  break;
  
  case "listaddresses":
  addresses('list');
  break;
  
  case "Save Address":
  save_address('insert');
  break;
  
  case "Update Address":
  save_address('update');
  break;
  
  case "import":
  import();
  break;
  
  case "Process Import":
  process_import();
  break;
  
  
  case "showmap":
  show_routemap();
  break;
  
  case "directions":
  directions();
  break;
  
  
  default:
  routes('list');
  break;
}


function routes($action)
{
    $routetypes=array("motor"=>"Motor Route",
                    "carrier"=>"Carrier Route",
                    "mail"=>"Mail Route");
                    
    if($action=='add' || $action=='edit')
    {
       if ($action=='add')
       {
           $button='Save Route';
           $routetype='motor';
       } else {
           $button='Update Route';
           $id=intval($_GET['id']);
           $sql="SELECT * FROM circ_routes WHERE id=$id";
           $dbData=dbselectsingle($sql);
           $data=$dbData['data'];
           $name=stripslashes($data['route_name']);
           $number=stripslashes($data['route_number']);
           $type=stripslashes($data['route_type']);
           $notes=stripslashes($data['route_notes']);
       }
       print "<form method=post>\n";
       make_select('type',$routetypes[$routetype],$routetypes,'Route Type','Type of route (mail, carrier, motor)');
       make_text('name',$name,'Route Name','Descriptive name of route');
       make_text('number',$number,'Route Number','Route number');
       make_textarea('notes',$notes,'Notes','Notes about the route');
       make_hidden('id',$id);
       make_submit('submit',$button);
       print "</form>\n"; 
    } elseif($action=='delete')
    {
       $id=intval($_GET['id']);
       $sql="DELETE FROM circ_routes WHERE id=$id";
       $dbDelete=dbexecutequery($sql);
       $error=$dbDelete['error'];
       if ($error!='')
       {
           setUserMessage('There was a problem deleting the route.<br>'.$error,'error');
       } else {
           setUserMessage('The route was successfully deleted.','success');
       }
       redirect("?action=listroutes"); 
    } else {
       $sql="SELECT * FROM circ_routes ORDER BY route_number";
       $dbData=dbselectmulti($sql);
       $search="";
       $options="<a href='?action=addroute'>Add new route</a>";
       $headers="Number,Name";
       tableStart($options,$headers,7,$search);
       if($dbData['numrows']>0)
       {
         foreach($dbData['data'] as $data)
         {
             print "<tr>\n";
             print "<td>".stripslashes($data['route_number'])."</td>\n";
             print "<td>".stripslashes($data['route_name'])."</td>\n";
             print "<td><a href='?action=editroute&id=$data[id]'>Edit</a></td>\n";
             print "<td><a href='?action=listaddresses&routeid=$data[id]'>Addresses</a></td>\n";
             print "<td><a href='?action=showmap&routeid=$data[id]'>Show map</a></td>\n";
             print "<td><a href='?action=directions&routeid=$data[id]'>Show Directions</a></td>\n";
             print "<td><a href='?action=deleteroute&id=$data[id]' class='delete'>Delete</a></td>\n";
             print "</tr>\n";
         }    
       }
       tableEnd($dbData);
    }  
}

function save_route($action)
{
    $id=$_POST['id'];
    $name=addslashes($_POST['name']);
    $number=addslashes($_POST['number']);
    $notes=addslashes($_POST['notes']);
    $type=addslashes($_POST['type']);
    if ($action=='insert')
    {
        $sql="INSERT INTO circ_routes (route_name, route_type, route_number, route_notes) VALUES 
        ('$name', '$type', '$number', '$notes')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE circ_routes SET route_name='$name', route_number='$number', route_type='$type', route_notes='$notes' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the route.<br>'.$error,'error');
    } else {
        setUserMessage('The route was successfully saved.','success');
    }
    redirect("?action=listroutes"); 
}


function addresses($action)
{
    $routeid=intval($_GET['routeid']);
                    
    if($action=='add' || $action=='edit')
    {
       if ($action=='add')
       {
           $button='Save Address';
           $state=defaultstate;
           $monday=0;
           $tuesday=0;
           $wednesday=0;
           $thursday=0;
           $friday=0;
           $saturday=0;
           $sunday=0;
           
       } else {
           $button='Update Address';
           $id=intval($_GET['id']);
           $sql="SELECT * FROM circ_routes_addresses WHERE id=$id";
           $dbData=dbselectsingle($sql);
           $data=$dbData['data'];
           $monday=stripslashes($data['monday']);
           $tuesday=stripslashes($data['tuesday']);
           $wednesday=stripslashes($data['wednesday']);
           $thursday=stripslashes($data['thursday']);
           $friday=stripslashes($data['friday']);
           $saturday=stripslashes($data['saturday']);
           $sunday=stripslashes($data['sunday']);
           $accountnumber=stripslashes($data['account_number']);
           $streetnumber=stripslashes($data['street_number']);
           $streetdirection=stripslashes($data['street_direction']);
           $streetname=stripslashes($data['street_name']);
           $streettype=stripslashes($data['street_type']);
           $streetunit=stripslashes($data['street_unit']);
           $city=stripslashes($data['city']);
           $state=stripslashes($data['state']);
           $zip=stripslashes($data['zip']);
           $package=stripslashes($data['package']);
           $pub=stripslashes($data['pub']);
           $instructions=stripslashes($data['instructions']);
           $notes=stripslashes($data['notes']);
       }
       print "<form method=post>\n";
       make_number('account',$accountnumber,'Account #','Account number in DTI');
       make_number('number',$streetnumber,'Street #','Number part of street address');
       make_text('direction',$streetdirection,'Direction','N(orth), S(outh), etc');
       make_text('name',$streetname,'Street Name','Name of the street');
       make_text('type',$streettype,'Type','Blvd, St, Ave, Way, Cir, etc. Please use abbreviations');
       make_text('unit',$streetunit,'Unit','Used for apartment, suite, lot, trailer, etc.');
       make_text('city',$city,'City');
       make_state('state',$state);
       make_number('zip',$zip,'Zipcode');
       make_text('package',$package,'Delivery package','Be consistent with what is used in DTI');
       make_text('pub',$pub,'Pub','Pub to match data in DTI');
       make_number('monday',$monday,'Monday paper count','Number of papers to deliver on Monday.');
       make_number('tuesday',$tuesday,'Tuesday paper count','Number of papers to deliver on Tuesday.');
       make_number('wednesday',$wednesday,'Wednesday paper count','Number of papers to deliver on Wednesday.');
       make_number('thursday',$thursday,'Thursday paper count','Number of papers to deliver on Thursday.');
       make_number('friday',$friday,'Friday paper count','Number of papers to deliver on Friday.');
       make_number('saturday',$saturday,'Saturday paper count','Number of papers to deliver on Saturday.');
       make_number('sunday',$sunday,'Sunday paper count','Number of papers to deliver on Sunday.');
       make_textarea('instructions',$instructions,'Instructions','Instructions for this address. WILL BE DISPLAYED!');
       make_textarea('notes',$notes,'Notes about the address. WILL NOT BE DISPLAYED ON OUTPUT');
       make_hidden('id',$id);
       make_hidden('routeid',$routeid);
       make_submit('submit',$button);
       print "</form>\n"; 
    } elseif($action=='delete')
    {
       $id=intval($_GET['id']);
       $sql="DELETE FROM circ_routes_addresses WHERE id=$id ";
       $dbDelete=dbexecutequery($sql);
       $error=$dbDelete['error'];
       if ($error!='')
       {
           setUserMessage('There was a problem deleting the route.<br>'.$error,'error');
       } else {
           setUserMessage('The route was successfully deleted.','success');
       }
       redirect("?action=listaddresses&routeid=$routeid"); 
    } else {
       $sql="SELECT * FROM circ_routes_addresses WHERE route_id=$routeid ORDER BY street_name, street_direction, street_number";
       $dbData=dbselectmulti($sql);
       $search="";
       $options="<a href='?action=listroutes'>Return to route list</a>,";
       $options.="<a href='?action=addaddress&routeid=$routeid'>Add new address</a>,";
       $options.="<a href='?action=showmap&routeid=$routeid'>Show map of addresses</a>,";
       $options.="<a href='?action=directions&routeid=$routeid'>Show directions</a>,";
       $options.="<a href='?action=import&routeid=$routeid'>Batch import from DTI</a>";
       $headers="Address,City,State";
       tableStart($options,$headers,5,$search);
       if($dbData['numrows']>0)
       {
         foreach($dbData['data'] as $data)
         {
             print "<tr>\n";
             $address=stripslashes($data['street_number'].' '.$data['street_direction'].' '.$data['street_name'].' '.$data['street_type']);
             if($data['street_unit']!=''){
                 $address.=" #".$data['street_unit'];
             }
             print "<td>$address</td>\n";
             print "<td>".stripslashes($data['city'])."</td>\n";
             print "<td>".stripslashes($data['state'])."</td>\n";
             print "<td><a href='?action=editaddress&id=$data[id]&routeid=$routeid'>Edit</a></td>\n";
             print "<td><a href='?action=deleteaddress&id=$data[id]&routeid=$routeid' class='delete'>Delete</a></td>\n";
             print "</tr>\n";
         }    
       }
       tableEnd($dbData);
    }  
}

function save_address($action)
{
    $id=$_POST['id'];
    $account=addslashes($_POST['account']);
    $name=addslashes($_POST['name']);
    $number=addslashes($_POST['number']);
    $direction=addslashes($_POST['direction']);
    $type=addslashes($_POST['type']);
    $unit=addslashes($_POST['unit']);
    $city=addslashes($_POST['city']);
    $state=addslashes($_POST['state']);
    $zip=addslashes($_POST['zip']);
    $package=addslashes($_POST['package']);
    $pub=addslashes($_POST['pub']);
    $monday=addslashes($_POST['monday']);
    $tuesday=addslashes($_POST['tuesday']);
    $wednesday=addslashes($_POST['wednesday']);
    $thursday=addslashes($_POST['thursday']);
    $friday=addslashes($_POST['friday']);
    $saturday=addslashes($_POST['saturday']);
    $sunday=addslashes($_POST['sunday']);
    $total=$monday+$tuesday+$wednesday+$thursday+$friday+$saturday+$sunday;
    $notes=addslashes($_POST['notes']);
    $instructions=addslashes($_POST['instructions']);
    $dt=date("Y-m-d H:i:s");
    if ($action=='insert')
    {
        $sql="INSERT INTO circ_routes_addresses (route_id, account_number, street_number, street_direction, street_name, 
        street_unit, street_type, city, state, zip, package, pub, monday, tuesday, wednesday, thursday, friday, saturday, 
        sunday, total_papers, imported, notes, instructions, google_address, lat, lon) VALUES 
        ('$routeid', '$account', '$number', '$direction', '$name', '$unit', '$type', '$city', '$state', '$zip', '$package', 
        '$pub', '$monday', '$tuesday', '$wednesday', '$thursday', '$friday', '$saturday', '$sunday', '$total', '$dt', 
        '$notes', '$instructions', '$googleaddress', '$lat', '$lon')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE circ_routes_addresses SET account_number='$account', street_number='$number', street_direction='$direction',
        street_name='$name', street_unit='$unit', street_type='$type', city='$city', state='$state', zip='$zip', 
        package='$package', pub='$pub', monday='$monday', tuesday='$tuesday', wednesday='$wednesday', thursday='$thursday', 
        friday='$friday', saturday='$saturday', sunday='$sunday', total_papers='$total', notes='$notes', 
        instructions='$instructions', google_address='$googleaddress', lat='$lat', lon='$lon' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the address.<br>'.$error,'error');
    } else {
        setUserMessage('The address was successfully saved.','success');
    }
    redirect("?action=listaddresses&routeid=$routeid"); 
}


function import()
{
    $routeid=intval($_GET['routeid']);
    print "<form method=post enctype='multipart/form-data'>\n";
    print "<div class='label'>ALERT</div><div class='input'><h2>Please note that importing a route list will remove all existing addresses for this route.<br>If you need to just add an address, you can do that <a href='?addaddress&routeid=$routeid'>by clicking here.</a></h2></div><div class='clear'></div>\n";
    make_file('routes','Route manifest');
    make_hidden('routeid',$routeid);
    make_submit('submit','Process Import');
    print "</form>\n";    
}

function process_import()
{
    $routeid=$_POST['routeid'];
    $file=$_FILES['routes']['tmp_name'];
    $contents=file_get_contents($file);
    //break into multiple lines
    $contents=explode("\n",$contents);
    if(count($contents)>0)
    {
        //clear existing routes
        $sql="DELETE FROM circ_routes_addresses WHERE route_id=$routeid";
        $dbDelete=dbexecutequery($sql);
        set_time_limit(2880);
        $i=0;
        ob_implicit_flush(true);
        print "There are a total of ".count($contents)." potential records to import<br>";
        print "Begining file processing...imported: <span id='importcount'></span><br>";
        foreach($contents as $line)
        {
            $elements=explode("|",$line);
            if(trim($elements[0])!='')
            {
                $accountnumber=$elements[0];
                $streetnumber=$elements[2];
                $streetdirection=$elements[3];
                $streetname=$elements[4];
                $streettype=$elements[5];
                $streetunit=$elements[7];
                $city=$elements[9];
                $state=$elements[10];
                $zip=$elements[11];
                $package=$elements[16];
                $pub=$elements[17];
                $monday=$elements[18];
                $tuesday=$elements[19];
                $wednesday=$elements[20];
                $thursday=$elements[21];
                $friday=$elements[22];
                $saturday=$elements[23];
                $sunday=$elements[24];
                $total=$monday+$tuesday+$wednesday+$thursday+$friday+$saturday+$sunday;
                
                $routes[$i]['account_number']=$accountnumber;
                $routes[$i]['street_number']=$streetnumber;
                $routes[$i]['street_direction']=$streetdirection;
                $routes[$i]['street_name']=$streetname;
                $routes[$i]['street_type']=$streettype;
                $routes[$i]['street_unit']=$streetunit;
                $routes[$i]['city']=$city;
                $routes[$i]['state']=$state;
                $routes[$i]['zip']=$zip;
                $routes[$i]['package']=$package;
                $routes[$i]['pub']=$pub;
                $routes[$i]['monday']=$monday;
                $routes[$i]['tuesday']=$tuesday;
                $routes[$i]['wednesday']=$wednesday;
                $routes[$i]['thursday']=$thursday;
                $routes[$i]['friday']=$friday;
                $routes[$i]['saturday']=$saturday;
                $routes[$i]['sunday']=$sunday;
                $routes[$i]['total']=$total; 
                
                $i++;
                if($l==25)
                {
                    $l=0;
                    print "<script>\$('#importcount').html('$i')</script>";
                    for($k = 0; $k < 320000; $k++){echo ' ';} // extra spaces to fill up browser buffer
                } else {
                    $l++;
                }
            }
        }
        print "<script>\$('#importcount').html('".($i)."')</script>";
        
        if(count($routes)>0)
        {
            print "<br><br>Begining address processing of ".count($routes)." addresses...<br>";
            print "<br><span id='curadd'></span>";
                
            foreach($routes as $key=>$route)
            {
                $address=$route['street_number'].' '.$route['street_direction'].' '.$route['street_name'].' '.$route['street_type'];
                if($route['street_unit']!=''){
                    $address.=' #'.$route['street_unit'];
                }
                $addresses[$key]['street']=$address;   
                $addresses[$key]['city']=$route['city'];   
                $addresses[$key]['state']=$route['state'];   
                $addresses[$key]['zip']=$route['zip'];   
            }
        
            $addresses=batch_geocode($addresses,'0','0',true,'curadd');
            $dt=date("Y-m-d H:i:s");
            foreach($routes as $key=>$account)
            {
                $routes[$key]['lat']=$addresses[$key]['lat'];        
                $routes[$key]['lon']=$addresses[$key]['lon'];
                if($addresses[$key]['status']=='success'){$success++;}
                
                $accountnumber=$routes[$key]['account_number'];
                $streetnumber=$routes[$key]['street_number'];
                $streetname=$routes[$key]['street_name'];
                $streetdirection=$routes[$key]['street_direction'];
                $streettype=$routes[$key]['street_type'];
                $streetunit=$routes[$key]['street_unit'];
                $city=$routes[$key]['city'];
                $state=$routes[$key]['state'];
                $zip=$routes[$key]['zip'];
                $package=$routes[$key]['package'];
                $pub=$routes[$key]['pub'];
                $monday=$routes[$key]['monday'];
                $tuesday=$routes[$key]['tuesday'];
                $wednesday=$routes[$key]['wednesday'];
                $thursday=$routes[$key]['thursday'];
                $friday=$routes[$key]['friday'];
                $saturday=$routes[$key]['saturday'];
                $sunday=$routes[$key]['sunday'];
                $total=$routes[$key]['total'];
                $lat=$addresses[$key]['lat'];
                $lon=$addresses[$key]['lon'];
                $values.="('$routeid', '$accountnumber', '$streetnumber', '$streetdirection', '$streetname', '$streetunit', '$streettype',
                 '$city', '$state', '$zip', '$package', '$pub', '$monday', '$tuesday', '$wednesday', '$thursday', '$friday', 
                 '$saturday', '$sunday', '$total', '$dt', '$lat', '$lon'),";
                $inserting++;
            }
            
            $values=substr($values,0,strlen($values)-1);
            
            $sql="INSERT INTO circ_routes_addresses (route_id, account_number, street_number, street_direction, street_name, 
            street_unit, street_type, city, state, zip, package, pub, monday, tuesday, wednesday, thursday, friday, saturday, 
            sunday, total_papers, imported, lat, lon) VALUES $values";
            $dbInsert=dbinsertquery($sql);
            $error=$dbInsert['error'];
            if($error=='')
            {
                print "Successfully imported the records. There were a total of $inserting records imported.<br>";
            } else {
                print "There was an error inserting the addresses.<br>".$error;
            }
        }
        
    } else {
        print "No data found in the file.";
    } 
    print "<a href='?listaddresses&routeid=$routeid'>Return to addresses</a>\n"; 
}


function show_routemap()
{
    $routeid=intval($_GET['routeid']);
    //get the mapping stuff
    $sql="SELECT * FROM circ_routes_addresses WHERE lat<>'' AND lon<>'' AND route_id=$routeid";
    $dbRoutes=dbselectmulti($sql);
    if($dbRoutes['numrows']>0)
    {
        $i=1;
        foreach($dbRoutes['data'] as $item)
        {
            
            $popup=$item['account_number'].'<br>'.$item['street_number'].' '.$item['street_direction'].' '.$item['street_name'].' '.$item['street_type'];
            if($item['street_unit']!=''){$popup.=" #".$item['street_unit'];}
            $popup.="<br>$item[city], $item[state] $item[zip]";
            $popup.="<br>$item[package] - $item[pub]";
            $popup.="<br>Monday papers: $item[monday]";
            $popup.="<br>Tuesday papers: $item[tuesday]";
            $popup.="<br>Wednesday papers: $item[wednesday]";
            $popup.="<br>Thursday papers: $item[thursday]";
            $popup.="<br>Friday papers: $item[friday]";
            $popup.="<br>Saturday papers: $item[saturday]";
            $popup.="<br>Sunday papers: $item[sunday]";
            $popup=addslashes($popup);
            $icons.="icons[$i] = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=$i|FF0000|FFFFFF';\n";
            $markers.="addMarker($i,$item[lat],$item[lon],'".$popup."');\n";
            $i++;
            if($maxLat==0){$maxLat=$item['lat'];}
            if($minLat==0){$minLat=$item['lat'];}
            if($maxLon==0){$maxLon=$item['lon'];}
            if($minLon==0){$minLon=$item['lon'];}
            if($item['lon']>$maxLon){$maxLon=$item['lon'];}
            if($item['lon']<$minLon){$minLon=$item['lon'];}
            if($item['lat']>$maxLat){$maxLat=$item['lat'];}
            if($item['lat']<$minLat){$minLat=$item['lat'];}
                
        }
    }
    if($maxLat!=0){
        if($maxLat!=$minLat)
        {
            $defaultLat=($maxLat+$minLat)/2;
        } else {
            $defaultLat=$maxLat;
        }
    }
    if($maxLon!=0){
        if($maxLon!=$minLon)
        {
            $defaultLon=($maxLon+$minLon)/2;
        } else {
            $defaultLon=$maxLon;
        }
    }
    ?>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script> 
<script type="text/javascript"> 
 
var center;
var map = null;
var currentPopup;
var icons = Array();
var bounds = new google.maps.LatLngBounds();
function addMarker(id, lat, lng, info) {
    var pt = new google.maps.LatLng(lat, lng);
    bounds.extend(pt);
    var marker = new google.maps.Marker({
        id: id,
        position: pt,
        icon: icons[id],
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
  
</script>

<div id="map_canvas" style='width:960px;height:600px;margin-left:auto;margin-right:auto;border: thin solid black;'><div style='margin-top:50px;text-align:center;margin-left:auto;margin-right:auto;'>Loading...<br><img src='../artwork/icons/ajax-loader.gif'></div></div>
<script type='text/javascript'>
$(document).ready(function(){
    var lat=<?php echo $defaultLat ?>;
    var lon=<?php echo  $defaultLon ?>;
    center = new google.maps.LatLng(lat,lon);
    var myOptions = {
        zoom: 16,
        center: center,
        mapTypeId: google.maps.MapTypeId.HYBRID
    }
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
 
    <?php print $icons."\n".$markers ?>
    center = bounds.getCenter();
})
</script>
<?php

}

function directions()
{
  $routeid=intval($_GET['routeid']);
    //get the mapping stuff
    $sql="SELECT DISTINCT(CONCAT(street_number,street_direction,street_name,street_type)), id,lat, lon 
    FROM circ_routes_addresses 
    WHERE lat<>'' AND lon<>'' AND route_id=$routeid 
    ORDER BY zip, city, street_name, street_direction, street_type, street_number
    LIMIT 85";
    print $sql;
    $dbRoutes=dbselectmulti($sql);
    if($dbRoutes['error']!=''){print "Sql Error: $dbRoutes[error]<br><br>";}
    if($dbRoutes['numrows']>0)
    {
        $i=1;
        //set the origin point
        if(circRouteStart=='office')
        {
            $startLat=officeLat;
            $startLon=officeLon;
        } else {
            $startLat=printingLat;
            $startLon=pringingLon;
        }
        $markers="addMarker(0,$startLat,$startLon,'0');\n";
        $icons="icon[0] = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=0|#666|#FFF';\n";
        $stops="addStop($startLat,$startLon,'0');\n";
            
        foreach($dbRoutes['data'] as $item)
        {
            
            $markers.="addMarker($i,$item[lat],$item[lon],'$item[id]');\n";
            $icons.="icon[$item[id]] = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=$i|#666|#FFF';\n";
            $stops.="addStop($item[lat],$item[lon],'$item[id]');\n";
            $i++;
            if($maxLat==0){$maxLat=$item['lat'];}
            if($minLat==0){$minLat=$item['lat'];}
            if($maxLon==0){$maxLon=$item['lon'];}
            if($minLon==0){$minLon=$item['lon'];}
            if($item['lon']>$maxLon){$maxLon=$item['lon'];}
            if($item['lon']<$minLon){$minLon=$item['lon'];}
            if($item['lat']>$maxLat){$maxLat=$item['lat'];}
            if($item['lat']<$minLat){$minLat=$item['lat'];}
                
        }
    }
    if($maxLat!=0){
        if($maxLat!=$minLat)
        {
            $defaultLat=($maxLat+$minLat)/2;
        } else {
            $defaultLat=$maxLat;
        }
    }
    if($maxLon!=0){
        if($maxLon!=$minLon)
        {
            $defaultLon=($maxLon+$minLon)/2;
        } else {
            $defaultLon=$maxLon;
        }
    }
    ?>
    <div id="map_canvas" style='width:960px;height:600px;margin-left:auto;margin-right:auto;border: thin solid black;'>
    <div style='margin-top:50px;text-align:center;margin-left:auto;margin-right:auto;'>Loading...<br>
    <img src='/artwork/icons/ajax-loader.gif'></div>
    </div>
    <div id='directionsPanel' style='width:600px;margin-top:10px;'></div>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script> 
<script type="text/javascript"> 
  var directionsDisplay;
  var directionsService = new google.maps.DirectionsService();
  var map;
  var stops = [];
  var markers = [];
  var directionsVisible = true;
  var icon = Array();
  var bounds = new google.maps.LatLngBounds();
  var batches = [];
    
    
  function calcRoute(batches, directionsService, directionsDisplay)
  {
    var combinedResults;
    var unsortedResults = [{}]; // to hold the counter and the results themselves as they come back, to later sort
    var directionsResultsReturned = 0;
    var mOptions = {
        flat: true
    }
    var directionOptions = {
         markerOptions: mOptions
    };
    for (var k = 0; k < batches.length; k++) {
        var lastIndex = batches[k].length - 1;
        var start = batches[k][0].location;
        var end = batches[k][lastIndex].location;
        
        // trim first and last entry from array
        var waypts = [];
        waypts = batches[k];
        waypts.splice(0, 1);
        waypts.splice(waypts.length - 1, 1);
        
        var request = {
            origin : start,
            destination : end,
            waypoints : waypts,
            travelMode : window.google.maps.TravelMode.DRIVING
        };
        (function (kk) {
            directionsService.route(request, function (result, status) {
                if (status == window.google.maps.DirectionsStatus.OK) {
                    
                    var unsortedResult = {
                        order : kk,
                        result : result
                    };
                    unsortedResults.push(unsortedResult);
                    
                    directionsResultsReturned++;
                    
                    if (directionsResultsReturned == batches.length) // we've received all the results. put to map
                    {
                        // sort the returned values into their correct order
                        unsortedResults.sort(function (a, b) {
                            return parseFloat(a.order) - parseFloat(b.order);
                        });
                        var count = 0;
                        for (var key in unsortedResults) {
                            if (unsortedResults[key].result != null) {
                                if (unsortedResults.hasOwnProperty(key)) {
                                    if (count == 0) // first results. new up the combinedResults object
                                        combinedResults = unsortedResults[key].result;
                                    else {
                                        // only building up legs, overview_path, and bounds in my consolidated object. This is not a complete
                                        // directionResults object, but enough to draw a path on the map, which is all I need
                                        combinedResults.routes[0].legs = combinedResults.routes[0].legs.concat(unsortedResults[key].result.routes[0].legs);
                                        combinedResults.routes[0].overview_path = combinedResults.routes[0].overview_path.concat(unsortedResults[key].result.routes[0].overview_path);
                                        
                                        combinedResults.routes[0].bounds = combinedResults.routes[0].bounds.extend(unsortedResults[key].result.routes[0].bounds.getNorthEast());
                                        combinedResults.routes[0].bounds = combinedResults.routes[0].bounds.extend(unsortedResults[key].result.routes[0].bounds.getSouthWest());
                                    }
                                    console.log(unsortedResults[key].result.routes[0].legs);
                                    count++;
                                }
                            }
                        }
                        directionsDisplay.setDirections(combinedResults);
                        directionsDisplay.setOptions(directionOptions);
                    }
                }
            });
        })(k);
    }

  }
  
  function addStop(lat,lon,id)
  {
      var stoppt = new google.maps.LatLng(lat, lon);
      stops.push({location: stoppt, stopover: true });
      
  }
  
  
  function addMarker(id, lat, lon, iconid)
  {
      var pt = new google.maps.LatLng(lat, lon);
        bounds.extend(pt);
        var marker = new google.maps.Marker({
            id: id,
            position: pt,
            icon: icon[iconid],
            map: map
        });
    };
  
  function clearMarkers()
  {
    for (var i = 0; i < markers.length; i++) {
      markers[i].setMap(null);
    }
  }
  
  $(document).ready(function(){
        var lat=<?php echo $defaultLat ?>;
        var lon=<?php echo  $defaultLon ?>;
        center = new google.maps.LatLng(lat,lon);
        directionsDisplay = new google.maps.DirectionsRenderer();
        var myOptions = {
          zoom:12,
          mapTypeId: google.maps.MapTypeId.HYBRID,
          center: center
        }
        map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
        center = bounds.getCenter();
        directionsDisplay.setMap(map);
        directionsDisplay.setPanel(document.getElementById("directionsPanel"));
        
        <?php
        //print $markers;
        print $stops;
        print $icons;
        ?>
        
        var itemsPerBatch = 10; // google API max - 1 start, 1 stop, and 8 waypoints
        var itemsCounter = 0;
        var wayptsExist = stops.length > 0;

        while (wayptsExist) {
            var subBatch = [];
            var subitemsCounter = 0;

            for (var j = itemsCounter; j < stops.length; j++) {
                subitemsCounter++;
                subBatch.push({
                    location: stops[j].location,
                    stopover: stops[j].stopover
                });
                if (subitemsCounter == itemsPerBatch)
                    break;
            }

            itemsCounter += subitemsCounter;
            batches.push(subBatch);
            wayptsExist = itemsCounter < stops.length;
            // If it runs again there are still points. Minus 1 before continuing to 
            // start up with end of previous tour leg
            itemsCounter--;
        }
        calcRoute(batches, directionsService, directionsDisplay);
  })
  
  </script>
  <?php
}
 

footer();
?>
