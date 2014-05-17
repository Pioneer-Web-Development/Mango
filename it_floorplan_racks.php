<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;
    
if ($_POST['submit'])
{
    $action=$_POST['submit'];
} elseif ($_GET['action'])
{
    $action=$_GET['action'];
} else {
    $action='list';
}


switch ($action)
{
    case "add":
    racks('add');
    break;
    
    case "edit":
    racks('edit');
    break;
    
    case "delete":
    racks('delete');
    break;
    
    case "list":
    racks('list');
    break;
    
    case "listregions":
    regions();
    break;
    
    case "Add Rack":
    save_rack('insert');
    break;
    
    case "Update Rack":
    save_rack('update');
    break;
}

 
function racks($action)
{
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add Rack";
        } else {
            $rackid=intval($_GET['rackid']);
            $sql="SELECT * FROM it_racks WHERE id=$rackid";
            $dbGroup=dbselectsingle($sql);
            $group=$dbGroup['data'];
            $name=stripslashes($group['rack_name']);
            $image=$group['rack_image'];
            $button="Update Rack";
        }
        print "<form method=post enctype='multipart/form-data'>\n";
        make_text('name',$name,'Rack Name','',50);
        make_file('image','Rack Image','Full image of the rack','artwork/itfloorplans/'.$image);
        make_hidden('rackid',$rackid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $rackid=intval($_GET['rackid']);
        $sql="DELETE FROM it_racks WHERE id=$rackid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if($error=='')
        {
            $sql="DELETE FROM it_rack_devices WHERE rack_id=$rackid";
            $dbDelete=dbexecutequery($sql);
        
            setUserMessage("The rack been saved deleted.",'success');
        } else {
            setUserMessage("There was a problem deleting the rack.<br>$error",'error');
        }
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM it_racks";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new rack</a>","Rack Name",4);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $id=$group['id'];
                $name=stripslashes($group['rack_name']);
                print "<tr>";
                print "<td>$name</td>";
                print "<td><a href='?action=edit&rackid=$id'>Edit</a></td>";
                print "<td><a href='?action=listregions&rackid=$id'>Devices</a></td>";
                print "<td><a href='?action=delete&rackid=$id' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbGroups);
        
    }
} 

function regions()
{
    ?>
    <style type='text/css'>
    .region{
        background-color:rgba(255,0,0,.2);
        position:relative;
        top:0;
        left:0;
        z-index:10;
    }
    .activeRegion{
        background-color:rgba(255,0,0,.5);
        position:relative;
        top:0;
        left:0;
        z-index:10;
        width:150px;
        height:150px;
    }
    
    </style>
    <?php
    $rackid=intval($_GET['rackid']);
    
    $sql="SELECT * FROM it_devices ORDER BY device_type, device_name";
    $dbDevices=dbselectmulti($sql);
    if($dbDevices['numrows']>0)
    {
        $devices[0]='Select associated device';
        foreach($dbDevices['data'] as $device)
        {
            $devices[$device['id']]=stripslashes($device['device_type'].' - '.$device['device_name'].' - '.$device['device_ip']);
        }
    } else {
        $devices[0]='No devices defined yet.';
    }
    
    
    $sql="SELECT * FROM it_racks WHERE id=$rackid";
    $dbFloor=dbselectsingle($sql);
    $image=stripslashes($dbFloor['data']['rack_image']);
    print "<div id='mainImage' style='float:left;width:340px;position:relative;top:0px;left:40px;z-index:1;'>
    <img src='artwork/itfloorplans/$image' border=0 width=300 />";
    //get all regions for this map
    $sql="SELECT * FROM it_rack_devices WHERE rack_id=$rackid";
    $dbRegions=dbselectmulti($sql);
    if($dbRegions['numrows']>0)
    {
        foreach($dbRegions['data'] as $region)
        {
            print "<div id='region_$region[id]' class='region' style='position:absolute;top:$region[region_top]px;left:$region[region_left]px;width:$region[region_width]px;height:$region[region_height]px;'></div>\n";
        }
    }
    print "</div>\n";
    print "<div style='float:left;width:500px;margin-left:40px;'>\n";
        print "<input type='hidden' id='currentRegion' value=0 />";
        print "<input type='hidden' id='rackid' value=$rackid />";
        print "<div style='margin-bottom:20px;border-bottom:1px solid black;padding-bottom:20px;'>";
        print "<input id='addDeviceButton' type='button' onclick='addDevice()' value='Add a new Device Region' /><br /><br />";
            print "<div id='regionList' >\n";
            //get all regions for this map
            $sql="SELECT A.*, B.device_name FROM it_rack_devices A, it_devices B WHERE A.rack_id=$rackid AND A.device_id=B.id";
            $dbRegions=dbselectmulti($sql);
            if($dbRegions['numrows']>0)
            {
                foreach($dbRegions['data'] as $region)
                {
                    print "<div id='list_$region[id]' data-name='$region[device_name]' data-device='$region[device_id]'>$region[device_name] <button onclick='editDevice($region[id]);'>Edit</button> <button onclick='deleteDevice($region[id]);'>Delete</button>  </div>";
                
                }    
            }
            print "</div>\n";
        print "</div>\n";
        print "<div>\n";
            print "Top: <span id='region_top'>0</span><br />\n";
            print "Left: <span id='region_left'>0</span><br />\n";
            print "Width: <span id='region_width'>0</span><br />\n";
            print "Height: <span id='region_height'>0</span><br />\n";
            print "<select id='device'>\n";
            foreach($devices as $pid=>$pname)
            {
                print "<option value='$pid'>$pname</option>\n";
            }
            print "</select><br />\n";
            print "<input type='button' onclick='saveDevice()' value='Save Device' />";
        print "</div>\n";
        print "<div class='clear'></div>\n";
    print "</div>\n";
    print "<div class='clear'></div>\n";
    ?>
    <script>
    function addDevice()
    {
        $('#currentRegion').val(0);
        $('#mainImage').append("<div id='newRegion' class='activeRegion'></div>");
        $('#newRegion').css('position','absolute');
        $('#newRegion').css('top',0);
        $('#newRegion').css('left',0);
        $('#newRegion').draggable().resizable();
        $('#region_width').html(150);
        $('#region_height').html(150);
        $('#addDeviceButton').prop('disabled', true);
        $("#newRegion").on( "resize", function( event, ui ) {$('#region_width').html(ui.size.width);$('#region_height').html(ui.size.height);} );     
        $("#newRegion").on( "drag", function( event, ui ) {$('#region_top').html(ui.position.top);$('#region_left').html(ui.position.left);} );     
    }
    
    function saveDevice()
    {
        var id=$('#currentRegion').val();
        $.ajax({
            type: "POST",
            url: 'includes/ajax_handlers/rackDeviceHandler.php',
            data: {action:'save',id:id,rackid:$('#rackid').val(),top:$('#region_top').html(),left:$('#region_left').html(),width:$('#region_width').html(),height:$('#region_height').html(),deviceid:$('#device').val()},
            dataType: "json",
            success: function(response){
                if(response.status=='success')
                {
                    id=response.id;
                    
                    if(response.addnew=='true')
                    {
                        $('#regionList').append("<div id='list_"+id+"' data-name='"+response.name+"' data-device='"+$('#device').val()+"'>"+response.name+"<button onclick='editDevice("+id+");'>Edit</button> <button onclick='deleteDevice("+id+");'>Delete</button>  </div>");        
                        $('#newRegion').addClass('region');
                        $('#newRegion').removeClass('activeRegion');
                        $('#newRegion').attr('id','region_'+id);
                    }  else {
                        $('#region_'+id).addClass('region');
                        $('#region_'+id).removeClass('activeRegion');
                        $('#region_'+id).draggable('destroy');
                        $('#region_'+id).resizable('destroy');
                    }
                    
                    $('#addDeviceButton').prop('disabled', false);
                    $('#region_width').html(0);
                    $('#region_height').html(0);
                    $('#region_top').html(0);
                    $('#region_left').html(0);
                    $('#device').val(0);
                }
            }
            
        })
    }
    
    function editDevice(id)
    {
        $.ajax({
            type: "POST",
            url: 'includes/ajax_handlers/rackDeviceHandler.php',
            data: {action:'save',id:id,rackid:$('#rackid').val()},
            dataType: "json",
            success: function(response){
                if(response.status=='success')
                {
                    $('#addDeviceButton').prop('disabled', true);
                    $('#region_'+id).addClass('activeRegion');        
                    $('#region_'+id).removeClass('region');
                    $('#currentRegion').val(id);
                    $('#region_width').html($('#region_'+id).css('width'));
                    $('#region_height').html($('#region_'+id).css('height'));
                    $('#region_top').html($('#region_'+id).css('top'));
                    $('#region_left').html($('#region_'+id).css('left'));
                    $('#device').val($('#list_'+id).data('device'));        
                    $('#region_'+id).draggable().resizable();
                    $('#region_'+id).on( "resize", function( event, ui ) {$('#region_width').html(ui.size.width);$('#region_height').html(ui.size.height);} );     
                    $('#region_'+id).on( "drag", function( event, ui ) {$('#region_top').html(ui.position.top);$('#region_left').html(ui.position.left);} );     

                }
            }
        })
    }
        
    function deleteDevice(id)
    {
         $.ajax({
            type: "POST",
            url: 'includes/ajax_handlers/rackDeviceHandler.php',
            data: {action:'delete',id:id,rackid:$('#rackid').val()},
            dataType: "json",
            success: function(response){
                if(response.status=='success')
                {
                    $('#addDeviceButton').prop('disabled', false);
                    $('#region_'+id).remove();        
                    $('#list_'+id).empty();        
                    $('#list_'+id).remove();        
                    $('#currentRegion').val(0);
                    $('#region_width').html(0);
                    $('#region_height').html(0);
                    $('#region_top').html(0);
                    $('#region_left').html(0);
                    $('#device').val(0);
                }
            }
         }) 
    }
    </script>
    <?php 
} 

function save_rack($action)
{
    $rackid=$_POST['rackid'];
    $name=addslashes($_POST['name']);
    if($action=='insert')
    {
        $sql="INSERT INTO it_racks (rack_name) VALUES ('$name')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $rackid=$dbInsert['insertid'];
    } else {
        $sql="UPDATE it_racks SET rack_name='$name' WHERE id=$rackid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    
   if(isset($_FILES)) { //means we have browsed for a valid file
    // check to make sure files were uploaded
    foreach($_FILES as $file) {
        switch($file['error']) {
            case 0: // file found
            if($file['name'] != NULL && okFileType($file['type'],'image',$file['name']) != false)  {
                //get the new name of the file
                //to do that, we need to push it into the database, and return the last record ID
                if ($rackid!=0) {
                    $ext=end(explode(".",$file['name']));
                    $filename='rack_'.$rackid.'.'.$ext;
                    //check for folder, if not present, create it
                    if(!file_exists("artwork/itfloorplans/"))
                    {
                        mkdir("artwork/itfloorplans/");
                    }
                        
                    if(processFile($file,"artwork/itfloorplans/",$filename) == true) {
                        $sql="UPDATE it_racks SET rack_image='$filename' WHERE id=$rackid";
                        $result=dbexecutequery($sql);
                    } else {
                       $error.= 'There was an error processing the file: '.$file['name'];  
                    }
                } else {
                    $error.= 'There was an error because the main record insertion failed.';
                }
            }
            break;

            case (1|2):  // upload too large
            $error.= 'file upload is too large for '.$file['name'];
            break;

            case 4:  // no file uploaded
            break;

            case (6|7):  // no temp folder or failed write - server config errors
            $error.= 'internal error - flog the webmaster on '.$file['name'];
            break;
        }
    }
 } 
    if($error=='')
    {
        setUserMessage("The rack has been saved successfully.",'success');
    } else {
        setUserMessage("There was a problem saving the rack.<br>$error",'error');
    }
    redirect("?action=list");
}  

footer();
?>