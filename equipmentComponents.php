<?php
include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
switch ($action)
{
    case "Save Component":
    save_component('insert');
    break;
    
    case "Update Component":
    save_component('update');
    break;
    
    case "add":
    addedit_component('add');
    break;
    
    case "edit":
    addedit_component('edit');
    break;
    
    case "delete":
    setup_component('delete');
    break;
    
    case "list":
    setup_component('list');
    break;
    
    default:
    setup_component('list');
    break;
    
} 



function addedit_component($action)
{
    $presstypes=array('printing'=>'Printing unit',
    'folder'=>'Folder',
    'former'=>'Former Board',
    'ribbon'=>'Ribbon deck',
    'console'=>'Press Console',
    'pressgeneric'=>'Generic Press Component');
    $insertertypes=array('inserterpocket'=>'Feed station','inserterhopper'=>'Hopper loader','inserterframe'=>'Frame & Structure','inserterelectronics'=>'Electonics','insertergeneric'=>'Generic Inserter Component');
    $stitchertypes=array('stitcherpocket'=>'Feed station','stitchergeneric'=>'Generic Stitcher Component');
    
    
    if ($_GET['parentid'])
    {
        $parentid=intval($_GET['parentid']);
    } else {
        $parentid=0;
    }
    $equipmentid=intval($_GET['equipmentid']);
    $type=$_GET['type'];
    if ($action=='add')
    {
        $button="Save Component";
        $sql="SELECT max(component_order) as maxo FROM equipment_component WHERE equipment_id=$equipmentid AND parent_id=$parentid AND type=$type";
        $dbMax=dbselectsingle($sql);
        $order=$dbMax['data']['maxo']+1;
        $componenttype='generic';
    } else {
        $button="Update Component";
        $id=intval($_GET['componentid']);
        $sql="SELECT * FROM equipment_component WHERE id=$id";
        $dbComponent=dbselectsingle($sql);
        $component=$dbComponent['data'];
        $name=$component['component_name'];
        $notes=$component['component_notes'];
        $image=$component['component_image'];
        $order=$component['component_order'];
        $componenttype=$component['component_type'];
    }
    print "<form method=post enctype='multipart/form-data'>\n";
    make_text('name',$name,'Component Name','Descriptive name of the component',50);
    if($type=='press')
    {
        make_select('component_type',$presstypes[$componenttype],$presstypes,'Component Type','Tie to specific press component?');
    } else if($type=='inserter')
    {
        make_select('component_type',$insertertypes[$componenttype],$insertertypes,'Component Type','Tie to specific inserter component?');
    } else if($type=='stitcher')
    {
        make_select('component_type',$stitchertypes[$componenttype],$stitchertypes,'Component Type','Tie to specific stitcher component?');
        
    } else {
        make_hidden('component_type','generic');
    }
    make_number('order',$order,'Component Order');
    make_file('image','Image','Image of the component','artwork/equipmentImages/'.$image);
    make_textarea('notes',$notes,'Notes','Notes or details about the component',80,20);
    make_hidden('componentid',$id);
    make_hidden('equipmentid',$equipmentid);
    make_hidden('parentid',$parentid);
    make_hidden('type',$type);
    make_submit('submit',$button);
    print "</form>\n";  
}
    
function setup_component($action)
{
    
    $equipmentid=intval($_GET['equipmentid']);
    $parentid=intval($_GET['parentid']);
    $type=$_GET['type'];
    
    if ($action=='delete') {
        $id=intval($_GET['componentid']);
        $sql="DELETE FROM equipment_component WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
         if ($error!='')
        {
            setUserMessage('There was a problem deleting this component.<br />'.$error,'error');
        } else {
            setUserMessage('The component has been successfully deleted.','success');
        }
        redirect("?action=list&parentid=$parentid&equipmentid=$equipmentid&type=$type");
    } else {
        $sql="SELECT A.component_order AS corder, A.component_name AS name, A.id, A.parent_id FROM equipment_component A WHERE equipment_id=$equipmentid AND equipment_type='$type' AND parent_id=$parentid ORDER BY component_order ASC, component_name ASC";
        $dbComponents=dbselectmulti($sql);
        tableStart("<a href='?action=add&parentid=$parentid&equipmentid=$equipmentid&type=$type'>Add new component</a>,<a href='equipment.php?action=list'>Return to equipment list</a>","Component ID,Name,Order",7);
        
        if ($dbComponents['numrows']>0)
        {
            foreach($dbComponents['data'] as $component)
            {
                $name=$component['name'];
                $order=$component['corder'];
                $id=$component['id'];
                print "<tr>\n";
                print "<td>$id</td><td>$name</td><td>$order</td>";
                print "<td><a href='?action=edit&equipmentid=$equipmentid&parentid=$parentid&componentid=$id&type=$type'>Edit Component</a></td>\n";
                print "<td><a href='equipmentParts.php?action=list&equipmentid=$equipmentid&parentid=$parentid&componentid=$id&type=$type'>Manage Parts</a></td>\n";
                print "<td><a href='equipmentPM.php?action=list&equipmentid=$equipmentid&parentid=$parentid&componentid=$id&type=$type'>Manage PM</a></td>\n";
                if($component['component_core']==0)
                {
                 print "<td><a href='?action=delete&equipmentid=$equipmentid&parentid=$parentid&componentid=$id&type=$type' class='delete'>Delete</a></td>\n";          
                   
                } else {
                    print "<td>Not deleteable</td>";
                }
                print "</tr>\n";
            }
        }
        tableEnd($dbComponents);
        
    }
}

function save_component($action)
{
    $id=$_POST['componentid'];
    $type=$_POST['type'];
    $equipmentid=$_POST['equipmentid'];
    $parentid=$_POST['parentid'];
    $name=addslashes($_POST['name']);
    $notes=addslashes($_POST['notes']);
    $order=addslashes($_POST['order']);
    $componenttype=$_POST['component_type'];
    if ($action=='insert')
    {
        $sql="INSERT INTO equipment_component (equipment_type, equipment_id, parent_id, component_name,
         component_notes, component_order,component_type) VALUES ('$type', '$equipmentid', '$parentid', '$name', '$notes', '$order', '$componenttype')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $id=$dbInsert['insertid'];
    } else {
        $sql="UPDATE equipment_component SET component_order='$order', component_name='$name', component_notes='$notes', 
        equipment_type='$type', component_type='$componenttype' WHERE id=$id";
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
                        if ($id!=0) {
                            // process the file
                            $ext=explode(".",$file['name']);
                            $ext=$ext[count($ext)-1];
                            $datesuffix=date("YmdHi");
                            $newname="component_".$id."_".$datesuffix.".".$ext;
                            
                            /*
                             if($name==str_replace("C:\\fakepath\\","",$_POST['secondary_hidden']))
                            {
                                $field='secondary_graphic';
                            } elseif($name==str_replace('C:\\fakepath\\',"",$_POST['third_hidden'])) {
                                $field='third_graphic';
                            }
                                $sizeArray[0]['destpath']=$pathtosave;
                                $sizeArray[0]['filename']=$newname;
                                $sizeArray[0]['format']="jpg";
                                $sizeArray[0]['width']="125";
                                doThumbs($newname,$sizeArray);
                                */                               
                                
                            
                            if(processFile($file,"artwork/equipmentImages/",$newname) == true) {
                                $picsql="update equipment_component SET component_image='$newname' WHERE id=$id";
                                $result=dbexecutequery($picsql);
                            } else {
                               $error.= 'There was an error processing the file '.$file['name'];  
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
    if ($error!='')
    {
        setUserMessage('There was a problem saving this component.<br />'.$error,'error');
    } else {
        setUserMessage('The component has been successfully saved.','success');
    }
    redirect("?action=list&equipmentid=$equipmentid&parentid=$parentid&type=$type");
    
}


function default_components($equipmentid,$type)
{
    switch($type)
    {
        case "press":
            global $defaultPressComponents;
            //convert them to an array
            $defaults=commaReturnTextBlockToArray($defaultPressComponents,"name,description");
        break;
        
        case "inserter":
            global $defaultInserterComponents;
            //convert them to an array
            $defaults=commaReturnTextBlockToArray($defaultInserterComponents,"name,description");
            
        break;
        
        case "stitcher":
            global $defaultStitcherComponents;
            //convert them to an array
            $defaults=commaReturnTextBlockToArray($defaultStitcherComponents,"name,description");
            
        break;
    }
    $order=1;
    foreach($defaults as $defaultitem)
    {
        $name=$defaultitem['name'];
        $notes=$defaultitem['description'];
        $values.="('$type', '$equipmentid',0, '$name', '$notes', '$order', 1),";
        $order++;
    }
    $values=substr($values,0,strlen($values)-1);
    $sql="INSERT INTO equipment_component (equipment_type, equipment_id, parent_id, component_name,
         component_notes, component_order, component_core) VALUES $values";
    $dbInsert=dbinsertquery($sql);
    //print "Defaults with $sql<br>".$dbInsert['error'];    
}

footer();  
?>
