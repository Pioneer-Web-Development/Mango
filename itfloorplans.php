<?php      
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;
 
maps();
    
function maps()
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
    </style>
    <?php
    $sql="SELECT * FROM it_floorplan_icons ORDER BY icon_name";
    $dbIcons=dbselectmulti($sql);
    if($dbIcons['numrows']>0)
    {
        $icons[0]='Please select icon';
        foreach($dbIcons['data'] as $icon)
        {
            $icons[$icon['id']]=stripslashes($icon['icon_name']);
            $drawing[$icon['id']]='artwork/iticons/'.stripslashes($icon['icon_image']);
        }
    } else {
        $icons[0]='No icons defined yet.';
    }
    
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
    
    $sql="SELECT * FROM it_racks ORDER BY rack_name";
    $dbRacks=dbselectmulti($sql);
    if($dbRacks['numrows']>0)
    {
        $racks[0]='Select a rack';
        foreach($dbRacks['data'] as $rack)
        {
            $racks[$rack['id']]=stripslashes($rack['rack_name']);
        }
    } else {
        $racks[0]='No racks defined yet.';
    }
    
    
    
    if($_GET['map'])
    {
        $sql="SELECT * FROM it_floorplans WHERE id=".intval($_GET['map']);
        $dbFloor=dbselectsingle($sql);
        $planid=$dbFloor['data']['id'];
        $image=stripslashes($dbFloor['data']['plan_image']);
        $name=stripslashes($dbFloor['data']['plan_name']);
        if($dbFloor['data']['plan_primary']==1){$width=1200;}else{$width=600;}
        print "<h3>$name</h3><p><a href='?action=main'>Return to main floorplan list</a></p>\n";
        print "<input type='hidden' id='planid' value='$planid' />\n";
        print "<div style='float:left;width:640px;'>\n";
        print "<div id='mainImage' style='position:relative;top:0px;left:40px;'>\n";
        print "<img src='artwork/itfloorplans/$image' border=0 width=$width/>";
        
        //get all regions for this map
        $sql="SELECT * FROM it_floorplan_regions WHERE plan_id=$planid";
        $dbRegions=dbselectmulti($sql);
        if($dbRegions['numrows']>0)
        {
            foreach($dbRegions['data'] as $region)
            {
                print "<div id='region_$region[id]' class='region' style='cursor:pointer;position:absolute;top:$region[region_top]px;left:$region[region_left]px;width:$region[region_width]px;height:$region[region_height]px;'></div>\n";
            }
            print "<script>\n";
            print "\$(document).ready(function() {\n";
            foreach($dbRegions['data'] as $region)
            {
                print "\$('#region_$region[id]').click(function(){window.location='?map=$region[region_link]';});\n"; 
                  
            }
            print "})
    </script>\n";
        }
        
        //get all devices for this map
        $sql="SELECT * FROM it_floorplan_devices WHERE plan_id=$planid";
        $dbFDev=dbselectmulti($sql);
        if($dbFDev['numrows']>0)
        {
            foreach($dbFDev['data'] as $fdev)
            {
                print "<div id='icon_$fdev[id]' class='icon' data-type='$fdev[device_type]' style='cursor:pointer;position:absolute;top:$fdev[icon_top]px;left:$fdev[icon_left]px;width:40px;height:40px;'><img src='".$drawing[$fdev['icon_id']]."' border=0 width=40 /></div>\n";
            }
            print "<script>\n";
            print "\$(document).ready(function() {\n";
            foreach($dbFDev['data'] as $fdev)
            {
                ?>
                $('#icon_<?php echo $fdev['id'] ?>').contextMenu('iconCmenu',{
                    'View Details': {
                        click: function(element){ // element is the jquery obj clicked on when context menu launched
                            getDeviceDetails(<?php echo $fdev['id'];?>);
                        }
                    },
                    'Toggle Draggable': {
                        click: function(element){ // element is the jquery obj clicked on when context menu launched
                            if($('#icon_<?php echo $fdev['id'] ?>').data('uiDraggable'))
                            {
                               $('#icon_<?php echo $fdev['id'] ?>').draggable("destroy");
                            } else {
                               $('#icon_<?php echo $fdev['id'] ?>').draggable(); 
                               $('#icon_<?php echo $fdev['id'] ?>').on( "dragstop", function( event, ui ) {
                                    updateLocation(<?php echo $fdev['id'] ?>,ui.position.top,ui.position.left);
                               });
                            }
                        }
                    },
                    'Delete': {
                       click: function(element){ // element is the jquery obj clicked on when context menu launched
                           var $dialog = $('<div id="jConfirm"></div>')
                            .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This item will be permanently deleted and cannot be recovered. Are you sure?</p>')
                            .dialog({
                                autoOpen: true,
                                title: 'Are you sure you want to Delete?',
                                modal: true,
                                buttons: {
                                    Cancel: function() {
                                        $( this ).dialog( "close" );
                                        return false;
                                    },
                                    'Delete': function() {
                                        $( this ).dialog( "close" );
                                        $.ajax({
                                           url: 'includes/ajax_handlers/floorplanIconHandler.php',
                                           type: "POST",
                                           data: {id:<?php echo $fdev['id'] ?>,action:'delete'},
                                           dataType: 'json',
                                           success: function(response) {
                                               if(response.status=='success')
                                               {
                                                  $('#icon_<?php echo $fdev['id'] ?>').empty();
                                                  $('#icon_<?php echo $fdev['id'] ?>').remove();
                                               } else {
                                                  alertMessage("Device removal failed<br />"+response.message,'error');
                                               }
                                           }
                                       });
                                    }
                                },
                                open: function() {
                                    $('.ui-dialog-buttonpane > button:last').focus();
                                }
                           
                            });
                        }
                    }
                });
                <?php     
            }
            print "})
            </script>\n";
        }
        
        print "</div>\n";
        
        print "<div style='position:relative;'>\n";
            print "<h3>Add new device to floorplan</h3>\n";
            print "<select id='icon'>\n";
            foreach($icons as $id=>$icon)
            {
                print "<option value='$id'>$icon</option>\n";
            }
            print "</select><br />\n"; 
            print "<select id='deviceList'>\n";
            foreach($devices as $id=>$device)
            {
                print "<option value='$id'>$device</option>\n";
            }
            print "</select><br />\n"; 
            print "<button onclick='addDevice(\"device\");'>Add new device</button>\n";
            print "<br /><br /><select id='rack'>\n";
            foreach($racks as $id=>$rack)
            {
                print "<option value='$id'>$rack</option>\n";
            }
            print "</select><br />\n"; 
            print "<button onclick='addDevice(\"rack\");'>Add new rack</button>\n";
        
        print "</div>\n";
        
        print "</div><!--closing left column-->\n";
        
        print "<div class='clear'></div>\n";
        ?>
        <script>
        
        function addDevice(dtype)
        {
            if($('#icon').val()!=0 || dtype=='rack')
            {
                if(dtype=='rack')
                {
                    devid=$('#rack').val();
                } else {
                    devid=$('#deviceList').val();
                }
                $.ajax({
                    type: "POST",
                    url: 'includes/ajax_handlers/floorplanIconHandler.php',
                    data: {action:'add',dtype:dtype,planid:$('#planid').val(),icon:$('#icon').val(),device:devid},
                    dataType: "json",
                    success: function(response){
                        if(response.status=='success')
                        {
                            var did=response.id;
                            $('#icon').val(0);
                            $('#deviceList').val(0);
                            $('#rack').val(0);
                            $('#mainImage').append("<div id='icon_"+did+"' data-id='"+did+"' data-type='"+dtype+"' style='position:absolute;top:0;left:0;width:40px;'><img src='"+response.image+"' border=0 width=40 /></div>");        
                            $('#icon_'+did).draggable();
                            $('#icon_'+did).on( "dragstop", function( event, ui ) {
                                updateLocation(did,ui.position.top,ui.position.left);
                            });
                            
                            $('#icon_'+did).contextMenu('iconCmenu',{
                                'View Details': {
                                    click: function(element){ // element is the jquery obj clicked on when context menu launched
                                        getDeviceDetails(response.id);
                                    }
                                },
                               'Toggle Draggable': {
                                    click: function(element){ // element is the jquery obj clicked on when context menu launched
                                        if($('#icon_'+did).data('uiDraggable'))
                                        {
                                           $('#icon_'+did).draggable("destroy");
                                        } else {
                                           $('#icon_'+did).draggable(); 
                                           $('#icon_'+did).on( "dragstop", function( event, ui ) {
                                                updateLocation(did,ui.position.top,ui.position.left);
                                           });
                                        }
                                    }
                                },
                                'Delete': {
                                   click: function(element){ // element is the jquery obj clicked on when context menu launched
                                       var $dialog = $('<div id="jConfirm"></div>')
                                        .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This item will be permanently deleted and cannot be recovered. Are you sure?</p>')
                                        .dialog({
                                            autoOpen: true,
                                            title: 'Are you sure you want to Delete?',
                                            modal: true,
                                            buttons: {
                                                Cancel: function() {
                                                    $( this ).dialog( "close" );
                                                    return false;
                                                },
                                                'Delete': function() {
                                                    $( this ).dialog( "close" );
                                                    $.ajax({
                                                       url: 'includes/ajax_handlers/floorplanIconHandler.php',
                                                       type: "POST",
                                                       data: {id:did,action:'delete'},
                                                       dataType: 'json',
                                                       success: function(dresponse) {
                                                           if(dresponse.status=='success')
                                                           {
                                                              $('#icon_'+did).empty();
                                                              $('#icon_'+did).remove();
                                                           } else {
                                                              alertMessage("Device removal failed<br />"+dresponse.message,'error');
                                                           }
                                                       }
                                                   });
                                                }
                                            },
                                            open: function() {
                                                $('.ui-dialog-buttonpane > button:last').focus();
                                            }
                                       
                                        });
                                    }
                                }
                            });
                        }   
                    }
                })
            } else {
                alert('Please select an icon first');
            }
        }
        
        function updateLocation(id,top,left)
        {
            $.ajax({
                type: "POST",
                url: 'includes/ajax_handlers/floorplanIconHandler.php',
                data: {action:'move',icon_id:id,top:top,left:left},
                dataType: "json",
                success: function(response){
                    //updated
                        
                }
            })
        }
        
        function getDeviceDetails(id)
        {
            var type=$('#icon_'+id).data('type');
            if(type=='device')
            {
                $.fancybox.open({
                    href : 'includes/ajax_handlers/floorplanIconHandler.php?action=detailsfbox&icon_id='+id,
                    type : 'iframe',
                    padding : 5,
                    maxWidth    : 800,
                    maxHeight    : 800,
                    fitToView    : false,
                    width        : '70%',
                    height        : '70%',
                    autoSize    : false,
                    closeClick    : false,
                    openEffect    : 'none',
                    closeEffect    : 'none',
                });
                        /*
                $.ajax({
                    type: "POST",
                    url: 'includes/ajax_handlers/floorplanIconHandler.php',
                    data: {action:'details',icon_id:id},
                    dataType: "json",
                    success: function(response){
                       $('#details').html(response.details); 
                    }
                })
                */
            } else {
                $.ajax({
                    type: "POST",
                    url: 'includes/ajax_handlers/floorplanIconHandler.php',
                    data: {action:'getrackimage',id:id},
                    dataType: "json",
                    success: function(response){
                        $('#details').html(response.image);
                        //now get regions for the rack
                        var rackid=response.rackid;
                        $.ajax({
                            type: "POST",
                            url: 'includes/ajax_handlers/floorplanIconHandler.php',
                            data: {action:'getrackregions',id:rackid},
                            dataType: "json",
                            success: function(response){
                                $.each(response.regions,function(i,region){
                                    $('#details').append("<div id='reg_"+region.id+"' style='cursor:pointer;z-index:10;background-color:rgba(255,0,0,.2);position:absolute;top:"+region.top+"px;left:"+region.left+"px;width:"+region.width+"px;height:"+region.height+"px;'></div>");
                                   $("#reg_"+region.id).qtip({
                                        content: {
                                            text: region.details
                                        },
                                        position: {
                                            my: 'middle right',
                                            at: 'middle left',
                                        }    
                                    })
                                     
                                })
                            }
                        })
                                    
                    }
                })
            }
        }
        </script>
        <?php
    } else{
        $sql="SELECT * FROM it_floorplans WHERE plan_primary=1";
        $dbPlans=dbselectmulti($sql);
        if($dbPlans['numrows']>0)
        {
            print "<h3>Select a base floorplan</h3>\n";
            foreach($dbPlans['data'] as $plan)
            {
                print "<a href='?map=$plan[id]'>".stripslashes($plan['plan_name'])."</a><br />";
            }
        } else {
            print "<h4>Sorry, no floor plans have been set up as of yet</h4>\n";
        }
        
    }
    
}



  footer();