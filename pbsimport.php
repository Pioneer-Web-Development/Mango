<?php
//<!--VERSION: .9 **||**-->

include("includes/mainmenu.php") ;
$scriptpath='http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'] ;
?>

<body>

<div id="wrapper">


<?php
 //make sure we have a logged in user...
if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}
  //this script is designed to read a PBS manifest file
include ("includes/pbsManifestImport.php");
if ($_POST['submit']=='Load PBS File')
{
    process_PBSfile(true);
} elseif ($_POST['submit']=='Update trucks/routes')
{
    save_newTR();
} else {
    pbsimport('standalone','pbsimport');                                                                                             
}

function save_newTR()
{
    //this function allows us to add new trucks and routes to the standing orders
    $pubid=$_POST['pubid'];
    $runid=$_POST['runid'];
    $pubid=$_POST['pubdate'];
    
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,6)=='truck_')
        {
            //working with a new truck
            //we'll also need to save any new routes that came with the new truck
            //get the truck details
            $newtruckid=str_replace("truck_","",$key);
            $sql="SELECT * FROM jobs_inserter_trucks WHERE id=$newtruckid";
            $dbNewTruck=dbselectsingle($sql);
            $newtruck=$dbNewTruck['data'];
            
            $order=$newtruck['truck_order'];
            $tname=addslashes(stripslashes($newtruck['truck_name']));
            $tdesc=addslashes(stripslashes($newtruck['truck_description']));
            $tdraw=str_replace(",","",$newtruck['draw']);
            $zoneid=$_POST["tzone_$newtruckid"];
            
            $sql="INSERT INTO publications_inserttrucks (zone_id, pub_id, run_id, truck_order, truck_name, truck_description, average_sunday, average_monday, average_tuesday, average_wednesday, average_thursday, average_friday, average_saturday) VALUES ('$zoneid','$pubid', '$runid', '$order', '$tname', '$tdesc', '$tdraw', '$tdraw', '$tdraw', '$tdraw', '$tdraw', '$tdraw', '$tdraw')";
            $dbInsertTruck=dbinsertquery($sql);
            $truckid=$dbInsertTruck['numrows'];
            //see if there are any new routes for the new truck
            $sql="SELECT * FROM jobs_inserter_routes WHERE truck_id=$newtruckid AND newroute=1";
            $dbNewRoutes=dbselectmulti($sql);
            if ($dbNewRoutes['numrows']>0)
            {
                foreach($dbNewRoutes['data'] as $newroute)
                {
                    $bulk=$newroute['bulk'];
                    $routenum=$newroute['route_number'];
                    $notes=$newroute['route_notes'];
                    $sequence=$newroute['route_sequence'];
                    $phone=$newroute['route_phone'];
                    $routeaccount=$newroute['route_account'];
                    
                    
                    $sql="INSERT INTO publications_insertroutes (pub_id, run_id, truck_id, route_account, route_number, bulk, route_phone, route_notes, route_sequence) VALUES ('$pubid', '$runid', '$truckid', '$routeaccount', '$routenum', '$bulk', '$phone', '$notes', '$sequence')";
                    $dbInsertRoute=dbinsertquery($sql);      
                              
                }
            }      
            
        }
        if (substr($key,0,6)=='route_')
        {
            $newrouteid=str_replace("route_","",$key);
            $sql="SELECT * FROM jobs_inserter_routes WHERE id=$newrouteid";
            $dbNewRoute=dbselectsingle($sql);
            if ($dbNewRoute['numrows']>0)
            {
                $newroute=$dbNewRoute['data'];
                $bulk=$newroute['bulk'];
                $routenum=$newroute['route_number'];
                $notes=$newroute['route_notes'];
                $sequence=$newroute['route_sequence'];
                $phone=$newroute['route_phone'];
                $routeaccount=$newroute['route_account'];
                $truckid=$newroute['truck_id'];
                
                $sql="INSERT INTO publications_insertroutes (pub_id, run_id, truck_id, route_account, 
                      route_number, bulk, route_phone, route_notes, route_sequence) VALUES
                      ('$pubid', '$runid', '$truckid', '$routeaccount', '$routenum',
                       '$bulk', '$phone', '$notes', '$sequence')";
                $dbInsertRoute=dbinsertquery($sql);
            }  
        }
    }
    
    
}


dbclose();
?>
</div>
</body>
</html>