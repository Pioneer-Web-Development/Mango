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
    plans('add');
    break;
    
    case "edit":
    plans('edit');
    break;
    
    case "delete":
    plans('delete');
    break;
    
    case "list":
    plans('list');
    break;
    
    case "listregions":
    regions();
    break;
    
    case "Add Plan":
    save_plan('insert');
    break;
    
    case "Update Plan":
    save_plan('update');
    break;
}

 
function plans($action)
{
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add Plan";
            $primary=0;
        } else {
            $planid=intval($_GET['planid']);
            $sql="SELECT * FROM it_floorplans WHERE id=$planid";
            $dbGroup=dbselectsingle($sql);
            $group=$dbGroup['data'];
            $name=stripslashes($group['plan_name']);
            $primary=$group['plan_primary'];
            $image=$group['plan_image'];
            $button="Update Plan";
        }
        print "<form method=post enctype='multipart/form-data'>\n";
        make_text('name',$name,'Plan Name','',50);
        make_radiocheck('checkbox','primary',$primary,'Primary','Check if this is the primary map');
        make_file('image','Floorplan','Floorplan Image','artwork/itfloorplans/'.$image);
        make_hidden('planid',$planid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $planid=intval($_GET['planid']);
        $sql="DELETE FROM it_floorplans WHERE id=$planid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if($error=='')
        {
            $sql="DELETE FROM it_floorplan_regionss WHERE plan_id=$planid";
            $dbDelete=dbexecutequery($sql);
            setUserMessage("The plan been saved deleted.",'success');
        } else {
            setUserMessage("There was a problem deleting the plan.<br>$error",'error');
        }
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM it_floorplans";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new floorplan</a>","Plan Name",4);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $id=$group['id'];
                $name=stripslashes($group['plan_name']);
                print "<tr>";
                print "<td>$name</td>";
                print "<td><a href='?action=edit&planid=$id'>Edit</a></td>";
                print "<td><a href='?action=listregions&planid=$id'>Regions</a></td>";
                print "<td><a href='?action=delete&planid=$id' class='delete'>Delete</a></td>";
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
        background-color:rgba(0,0,0,.2);
        position:relative;
        top:0;
        left:0;
        z-index:10;
    }
    .activeRegion{
        background-color:rgba(0,0,0,.5);
        position:relative;
        top:0;
        left:0;
        z-index:10;
        width:150px;
        height:150px;
    }
    
    </style>
    <?php
    $planid=intval($_GET['planid']);
    
    //get all other floorplans for this to link to
    $sql="SELECT * FROM it_floorplans WHERE id<>$planid ORDER BY plan_name";
    $dbPlans=dbselectmulti($sql);
    $plans[0]='Please select linking plan';
    if($dbPlans['numrows']>0)
    {
        foreach($dbPlans['data'] as $plan)
        {
            $plans[$plan['id']]=stripslashes($plan['plan_name']);
        }
    } else {
        $plans['0']='No other plans loaded yet';
    }
    
    
    $sql="SELECT * FROM it_floorplans WHERE id=$planid";
    $dbFloor=dbselectsingle($sql);
    $image=stripslashes($dbFloor['data']['plan_image']);
    print "<div id='mainImage' style='position:relative;top:0px;left:40px;'>
    <img src='artwork/itfloorplans/$image' border=0 width=1200 />";
    //get all regions for this map
    $sql="SELECT * FROM it_floorplan_regions WHERE plan_id=$planid";
    $dbRegions=dbselectmulti($sql);
    if($dbRegions['numrows']>0)
    {
        foreach($dbRegions['data'] as $region)
        {
            print "<div id='region_$region[id]' class='region' style='position:absolute;top:$region[region_top]px;left:$region[region_left]px;width:$region[region_width]px;height:$region[region_height]px;'></div>\n";
        }
    }
    print "</div>\n";
    print "<input type='hidden' id='currentRegion' value=0 />";
    print "<input type='hidden' id='planid' value=$planid />";
    print "<div style='position:relative'>\n";
        print "<div style='float:left;width:300px;'>";
            print "<div id='regionList' >\n";
            //get all regions for this map
            $sql="SELECT * FROM it_floorplan_regions WHERE plan_id=$planid";
            $dbRegions=dbselectmulti($sql);
            if($dbRegions['numrows']>0)
            {
                foreach($dbRegions['data'] as $region)
                {
                    print "<div id='list_$region[id]' data-name='$region[region_name]' data-link='$region[region_link]'>$region[region_name] <button onclick='editRegion($region[id]);'>Edit</button> <button onclick='deleteRegion($region[id]);'>Delete</button>  </div>";
                
                }    
            }
            print "</div>\n";
            print "<input id='addRegionButton' type='button' onclick='addRegion()' value='Add Region' />";
        print "</div>\n";
        print "<div style='float:left;width:300px;'>\n";
            print "Top: <span id='region_top'>0</span><br />\n";
            print "Left: <span id='region_left'>0</span><br />\n";
            print "Width: <span id='region_width'>0</span><br />\n";
            print "Height: <span id='region_height'>0</span><br />\n";
            print "<input type='text' id='region_name' placeholder='Region Name' /><br />\n";
            print "<select id='region_link'>\n";
            foreach($plans as $pid=>$pname)
            {
                print "<option value='$pid'>$pname</option>\n";
            }
            print "</select><br />\n";
            print "<input type='button' onclick='saveRegion()' value='Save Region' />";
        print "</div>\n";
        print "<div class='clear'></div>\n";
    print "</div>\n";
    print "<div class='clear'></div>\n";
    ?>
    <script>
    function addRegion()
    {
        $('#currentRegion').val(0);
        $('#mainImage').append("<div id='newRegion' class='activeRegion'></div>");
        $('#newRegion').css('position','absolute');
        $('#newRegion').css('top',0);
        $('#newRegion').css('left',0);
        $('#newRegion').draggable().resizable();
        $('#region_width').html(150);
        $('#region_height').html(150);
        $('#addRegionButton').prop('disabled', true);
        $("#newRegion").on( "resize", function( event, ui ) {$('#region_width').html(ui.size.width);$('#region_height').html(ui.size.height);} );     
        $("#newRegion").on( "drag", function( event, ui ) {$('#region_top').html(ui.position.top);$('#region_left').html(ui.position.left);} );     
    }
    
    function saveRegion()
    {
        var id=$('#currentRegion').val();
        $.ajax({
            type: "POST",
            url: 'includes/ajax_handlers/floorplanHandler.php',
            data: {action:'save',id:id,planid:$('#planid').val(),top:$('#region_top').html(),left:$('#region_left').html(),width:$('#region_width').html(),height:$('#region_height').html(),name:$('#region_name').val(),link:$('#region_link').val()},
            dataType: "json",
            success: function(response){
                if(response.status=='success')
                {
                    id=response.id;
                    
                    if(response.addnew=='true')
                    {
                        $('#regionList').append("<div id='list_"+id+"' data-name='"+$('#region_name').val()+"' data-link='"+$('#region_link').val()+"'>"+$('#region_name').val()+"<button onclick='editRegion("+id+");'>Edit</button> <button onclick='deleteRegion("+id+");'>Delete</button>  </div>");        
                        $('#newRegion').addClass('region');
                        $('#newRegion').removeClass('activeRegion');
                        $('#newRegion').attr('id','region_'+id);
                    }  else {
                        $('#region_'+id).addClass('region');
                        $('#region_'+id).removeClass('activeRegion');
                        $('#region_'+id).draggable('destroy');
                        $('#region_'+id).resizable('destroy');
                    }
                    
                    $('#addRegionButton').prop('disabled', false);
                    $('#region_width').html(0);
                    $('#region_height').html(0);
                    $('#region_top').html(0);
                    $('#region_left').html(0);
                    $('#region_name').val('');
                    $('#region_link').val('');
                }
            }
            
        })
    }
    
    function editRegion(id)
    {
        $.ajax({
            type: "POST",
            url: 'includes/ajax_handlers/floorplanHandler.php',
            data: {action:'save',id:id,planid:$('#planid').val()},
            dataType: "json",
            success: function(response){
                if(response.status=='success')
                {
                    $('#addRegionButton').prop('disabled', true);
                    $('#region_'+id).addClass('activeRegion');        
                    $('#region_'+id).removeClass('region');
                    $('#currentRegion').val(id);
                    $('#region_width').html($('#region_'+id).css('width'));
                    $('#region_height').html($('#region_'+id).css('height'));
                    $('#region_top').html($('#region_'+id).css('top'));
                    $('#region_left').html($('#region_'+id).css('left'));
                    $('#region_name').val($('#list_'+id).data('name'));        
                    $('#region_link').val($('#list_'+id).data('link'));
                    $('#region_'+id).draggable().resizable();
                    $('#region_'+id).on( "resize", function( event, ui ) {$('#region_width').html(ui.size.width);$('#region_height').html(ui.size.height);} );     
                    $('#region_'+id).on( "drag", function( event, ui ) {$('#region_top').html(ui.position.top);$('#region_left').html(ui.position.left);} );     

                }
            }
        })
    }
        
    function deleteRegion(id)
    {
         $.ajax({
            type: "POST",
            url: 'includes/ajax_handlers/floorplanHandler.php',
            data: {action:'delete',id:id,planid:$('#planid').val()},
            dataType: "json",
            success: function(response){
                if(response.status=='success')
                {
                    $('#addRegionButton').prop('disabled', false);
                    $('#region_'+id).remove();        
                    $('#list_'+id).empty();        
                    $('#list_'+id).remove();        
                    $('#currentRegion').val(0);
                    $('#region_width').html(0);
                    $('#region_height').html(0);
                    $('#region_top').html(0);
                    $('#region_left').html(0);
                    $('#region_name').val('');        
                    $('#region_link').val(0);
                }
            }
         }) 
    }
    </script>
    <?php 
} 

function save_plan($action)
{
    $planid=$_POST['planid'];
    $name=addslashes($_POST['name']);
    if($_POST['primary']){$primary=1;}else{$primary=0;}
    if($action=='insert')
    {
        $sql="INSERT INTO it_floorplans (plan_name, plan_primary) VALUES ('$name', '$primary')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $planid=$dbInsert['insertid'];
    } else {
        $sql="UPDATE it_floorplans SET plan_name='$name', plan_primary='$primary' WHERE id=$planid";
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
                if ($planid!=0) {
                    $ext=end(explode(".",$file['name']));
                    $filename='plan_'.$planid.'.'.$ext;
                    //check for folder, if not present, create it
                    if(!file_exists("artwork/itfloorplans/"))
                    {
                        mkdir("artwork/itfloorplans/");
                    }
                    if(processFile($file,"artwork/itfloorplans/",$filename) == true) {
                        $sql="UPDATE it_floorplans SET plan_image='$filename' WHERE id=$planid";
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
        setUserMessage("The floorplan has been saved successfully.",'success');
    } else {
        setUserMessage("There was a problem saving the floorplan.<br>$error",'error');
    }
    redirect("?action=list");
}  

footer();
?>