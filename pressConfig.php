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
    $action="listpress";
}

switch ($action)
{
    case "Save Press":
    save_press('insert');
    break;
    
    case "Update Press":
    save_press('update');
    break;
    
    case "Save Tower":
    save_tower('insert');
    break;
    
    case "Update Tower":
    save_tower('update');
    break;

    case "Save Equipment":
    save_tower_equipment();
    break;

    case "listtowers":
    show_towers('list');
    break;
    
    case "addtower":
    show_towers('add');
    break;
    
    case "edittower":
    show_towers('edit');
    break;
    
    case "deletetower":
    delete_tower();
    break;
    
    case "addpress":
    show_press('add');
    break;
    
    case "editpress":
    show_press('edit');
    break;
    
    case "deletepress":
    delete_press();
    break;
    
    case "equipment":
    tower_equipment('list');
    break;
    
    case "addequipment":
    tower_equipment('add');
    break;
    
    case "editequipment":
    tower_equipment('edit');
    break;
    
    case "deleteequipment":
    tower_equipment('delete');
    break;
    
    default:
    show_press('list');
    break;

}


function show_press($action)
{
    global $siteID;
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Save Press';
            $consoles=1;
            $chillers=0;
            $drives=0;
        } else {
            $button='Update Press';
            $pressid=intval($_GET['pressid']);
            $sql="SELECT * FROM press WHERE id=$pressid";
            $dbPress=dbselectsingle($sql);
            $press=$dbPress['data'];
            $name=stripslashes($press['name']);
            $notes=stripslashes($press['notes']);
            $consoles=$press['consoles'];
            $drives=$press['drives'];
            $chillers=$press['chillers'];
        }
        print "<form method=post>\n";
        make_text('name',$name,'Press Name','',30);
        make_textarea('notes',$notes,'Notes','',50,20,false);
        make_number('consoles',$consoles,'Consoles','How many console stations are there on this press?');
        make_number('chillers',$chillers,'Chillers','How many chillers are there on this press?');
        make_number('drives',$drives,'Drives','How many drive units are there on this press?');
        make_hidden('pressid',$pressid);
        make_submit('submit',$button);
        print "</form>\n";
    } else {
        $sql="SELECT * FROM press WHERE site_id=$siteID ORDER BY name";
        $dbPress=dbselectmulti($sql);
        tableStart("<a href='?action=addpress'>Add new press</a>","Press Name",8);
        if ($dbPress['numrows']>0)
        {
            foreach($dbPress['data'] as $press)
            {
                $pressid=$press['id'];
                $name=$press['name'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=editpress&pressid=$pressid'>Edit</a></td>\n";
                print "<td><a href='?action=listtowers&pressid=$pressid'>Towers</a></td>\n";
                print "<td><a href='?action=equipment&type=splicer&pressid=$pressid'>Splicers</a></td>\n";
                print "<td><a href='?action=equipment&type=stacker&pressid=$pressid'>Stackers</a></td>\n";
                print "<td><a href='?action=equipment&type=strapper&pressid=$pressid'>Strappers</a></td>\n";
                print "<td><a href='?action=equipment&type=counterveyor&pressid=$pressid'>Count-o-veyors</a></td>\n";
                print "<td><a href='?action=deletepress&pressid=$pressid' class='delete'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbPress);
    }

}

function show_towers($action)
{
    global $orders, $colorconfigs, $folderconfigs, $slitterconfigs, $towertypes, $siteID;
    $pressid=intval($_GET['pressid']);
    $towers=array();
    $towers[0]='Please select';
    $sql="SELECT * FROM press_towers WHERE press_id=$pressid ORDER BY tower_order";
    $dbTowers=dbselectmulti($sql);
    if ($dbTowers['numrows']>0)
    {
        foreach($dbTowers['data'] as $t)
        {
            $towers[$t['id']]=$t['tower_name'];
        }
    }
    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Save Tower';
            $folder=0;
            $order=1;
            $colorconfig="Y/M/C/K";
            $folderconfig="2";
            $slitterconfig="center";
            $stackon=0;
            $ribbonconfig=0;
            $componentid=0;
            $stackers=array();
            $strappers=array();
            $conterveyors=array();
            $splicers=array();
        } else {
            $button='Update Tower';
            $towerid=intval($_GET['towerid']);
            $sql="SELECT * FROM press_towers WHERE id=$towerid";
            $dbTower=dbselectsingle($sql);
            $tower=$dbTower['data'];
            $order=$tower['tower_order'];
            $type=$tower['tower_type'];
            $colorconfig=$tower['color_config'];
            $folder=$tower['folder'];
            $stackon=$tower['stack_on'];
            $status=$tower['tower_status'];
            $towername=$tower['tower_name'];
            $folderconfig=$tower['folder_config'];
            $slitterconfig=$tower['slitter_config'];
            
            $stackers=explode("|",$tower['stackers']);
            $strappers=explode("|",$tower['strappers']);
            $splicers=explode("|",$tower['splicers']);
            $counterveyors=explode("|",$tower['counterveyors']);
            $ribbonconfig=$tower['ribbon_config'];
            $componentid=$tower['component_id'];
        }
        print "<form method=post>\n";
        make_text('name',$towername,'Tower Name','Name of tower/folder',30);
        make_select('order',$order,$orders,'Position','Position number, left to right from operator side');
        make_select('type',$type,$towertypes,'Type of tower','Printing unit, folder, ribbon deck');
        make_select('colorconfig',$colorconfig,$colorconfigs,'Tower Configuration','Not used if this tower is a folder');
        make_select('slitterconfig',$slitterconfig,$slitterconfigs,'Slitter Configuration','Specify where the slitter is for this web');
        make_select('folderconfig',$folderconfig,$folderconfigs,'Folder Configuration', 'Specifiy number of former boards');
        make_number('ribbonconfig',$ribbonconfig,'Ribbon Deck','Number of webs that can ENTER this ribbon deck');
        make_select('stackon',$towers[$stackon],$towers,'Stack on top','Does this tower stack on another one? If so, select the tower it stacks on.');
        make_checkbox('status',$status,'Enabled?','Is this tower in operating condition?');
        
        //here we will pull in a list of equipment of specialist type = 'stacker' that is tied to this press
        $sql="SELECT * FROM equipment WHERE specialist_type='stacker' AND equipment_tie_id='$pressid' ORDER BY equipment_name";
        $dbStackers=dbselectmulti($sql);
        if($dbStackers['numrows']>0)
        {
            print "<div class='label'>Stackers tied to this tower</div><div class='input'>\n";
            foreach($dbStackers['data'] as $stacker)
            {
                if(in_array($stacker['id'],$stackers)){$checked='checked';}else{$checked='';}
                print "<input type='checkbox' name='stacker_$stacker[id]'id='stacker_$stacker[id]' $checked><label for='stacker_$stacker[id]'>$stacker[equipment_name]</label><br>";
            }
            print "</div><div class='clear'></div>\n";
        }
        
        //here we will pull in a list of equipment of specialist type = 'strapper' that is tied to this press
        $sql="SELECT * FROM equipment WHERE specialist_type='strapper' AND equipment_tie_id='$pressid' ORDER BY equipment_name";
        $dbStackers=dbselectmulti($sql);
        if($dbStackers['numrows']>0)
        {
            print "<div class='label'>Strapper tied to this tower</div><div class='input'>\n";
            foreach($dbStackers['data'] as $stacker)
            {
                if(in_array($stacker['id'],$strappers)){$checked='checked';}else{$checked='';}
                print "<input type='checkbox' name='strapper_$stacker[id]'id='strapper_$stacker[id]' $checked><label for='strapper_$stacker[id]'>$stacker[equipment_name]</label><br>";
            }
            print "</div><div class='clear'></div>\n";
        }
        
        //here we will pull in a list of equipment of specialist type = 'splicer' that is tied to this press
        $sql="SELECT * FROM equipment WHERE specialist_type='splicer' AND equipment_tie_id='$pressid' ORDER BY equipment_name";
        $dbStackers=dbselectmulti($sql);
        if($dbStackers['numrows']>0)
        {
            print "<div class='label'>Splicer(s) tied to this tower</div><div class='input'>\n";
            foreach($dbStackers['data'] as $stacker)
            {
                if(in_array($stacker['id'],$splicers)){$checked='checked';}else{$checked='';}
                print "<input type='checkbox' name='splicer_$stacker[id]'id='splicer_$stacker[id]' $checked><label for='splicer_$stacker[id]'>$stacker[equipment_name]</label><br>";
            }
            print "</div><div class='clear'></div>\n";
        }
        
        //here we will pull in a list of equipment of specialist type = 'counterveyor' that is tied to this press
        $sql="SELECT * FROM equipment WHERE specialist_type='counterveyor' AND equipment_tie_id='$pressid' ORDER BY equipment_name";
        $dbStackers=dbselectmulti($sql);
        if($dbStackers['numrows']>0)
        {
            print "<div class='label'>Conterveyor tied to this tower</div><div class='input'>\n";
            foreach($dbStackers['data'] as $stacker)
            {
                if(in_array($stacker['id'],$counterveyors)){$checked='checked';}else{$checked='';}
                print "<input type='checkbox' name='counterveyor_$stacker[id]'id='counterveyor_$stacker[id]' $checked><label for='counterveyor_$stacker[id]'>$stacker[equipment_name]</label><br>";
            }
            print "</div><div class='clear'></div>\n";
        }
        
        make_hidden('pressid',$pressid);
        make_hidden('towerid',$towerid);
        make_submit('submit',$button);
        print "</form>\n";
    } else {
        $sql="SELECT * FROM press_towers WHERE site_id=$siteID AND press_id=$pressid ORDER BY tower_order ASC";
        $dbTowers=dbselectmulti($sql);
        tableStart("<a href='?action=listpress'>Return to press list</a>,<a href='?action=addtower&pressid=$pressid'>Add new tower</a>","Name,Order,Type,Config",6);
        if ($dbTowers['numrows']>0)
        {
            foreach($dbTowers['data'] as $tower)
            {
                $towerid=$tower['id'];
                $order=$tower['tower_order'];
                $name=$tower['tower_name'];
                $type=$tower['tower_type'];
                if ($tower['tower_type']=='folder')
                {
                    $config=$tower['folder_config']." formers";
                } else {
                    $config=$tower['color_config'];
                }
                print "<tr><td>$name</td><td>$order</td><td>$type</td><td>$config</td>\n";
                print "<td><a href='?action=edittower&towerid=$towerid&pressid=$pressid'>Edit</a></td>\n";
                print "<td><a href='?action=deletetower&towerid=$towerid&pressid=$pressid' class='delete'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbTowers);
    }
}

function save_press($action)
{
    global $siteID;
    $pressid=$_POST['pressid'];
    $name=addslashes($_POST['name']);
    $notes=addslashes($_POST['notes']);
    if($_POST['consoles']!='')
    {
        $consoles=$_POST['consoles'];
    } else {
        $consoles=0;
    }
    if($_POST['chillers']!='')
    {
        $chillers=$_POST['chillers'];
    } else {
        $chillers=0;
    }
    if($_POST['drives']!='')
    {
        $drives=$_POST['drives'];
    } else {
        $drives=0;
    }
    
    
    if ($action=='insert')
    {
        $sql="INSERT INTO press (name, notes, consoles, chillers, drives, site_id) VALUES ('$name', '$notes', '$consoles', '$chillers','$drives', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE press SET name='$name', notes='$notes', consoles='$consoles', chillers='$chillers', drives='$drives' WHERE id=$pressid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the press.<br>'.$error,'error');
    } else {
        setUserMessage('Press successfully saved','success');
    }
    redirect("?action=listpress");
    
}

function save_tower($action)
{
    global $orders, $colorconfigs, $folderconfigs, $siteID;
    $stackon=$_POST['stackon'];
    $pressid=$_POST['pressid'];
    $towerid=$_POST['towerid'];
    $order=$orders[$_POST['order']];
    $colorconfig=$colorconfigs[$_POST['colorconfig']];
    $folderconfig=$folderconfigs[$_POST['folderconfig']];
    $slitterconfig=$_POST['slitterconfig'];
    $towertype=$_POST['type'];
    if ($_POST['status']){$status=1;}else{$status=0;}
    $name=addslashes($_POST['name']);
    if($_POST['ribbonconfig']=='')
    {
        $ribbonconfig=0;
    } else{
        $ribbonconfig=intval($_POST['ribbonconfig']);
    }
    
    foreach($_POST as $key=>$value)
    {
        $value=end(explode("_",$key));
        if(substr($key,0,9)=='strapper_')
        {
            $strappers.="$value|";   
        }
        if(substr($key,0,8)=='stacker_')
        {
            $stackers.="$value|";   
        }
        if(substr($key,0,8)=='splicer_')
        {
            $splicers.="$value|";   
        }
        if(substr($key,0,13)=='counterveyor_')
        {
            $counterveyors.="$value|";   
        }
    }
    /* 
    print "<pre>\n";
    print_r($_POST);
    print "</pre>\n";
    */
    $splicers=substr($splicers,0,strlen($splicers)-1);
    $stackers=substr($stackers,0,strlen($stackers)-1);
    $counterveyors=substr($counterveyors,0,strlen($counterveyors)-1);
    $strappers=substr($strappers,0,strlen($strappers)-1);
    if ($action=='insert')
    {
        $sql="INSERT INTO press_towers (press_id, tower_order, color_config, tower_type, folder_config, 
        tower_status, tower_name, slitter_config, impressions, stack_on, ribbon_config, site_id, splicers, strappers, 
        stackers, counterveyors)
         VALUES ('$pressid', '$order', '$colorconfig', '$towertype', '$folderconfig', '$status', '$name', 
         '$slitterconfig',0, '$stackon', '$ribbonconfig', '$siteID', '$splicers', '$strappers', '$stackers', 
         '$counterveyors')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE press_towers SET tower_order='$order', color_config='$colorconfig', tower_type='$towertype', 
        folder_config='$folderconfig', stack_on='$stackon', tower_status='$status', tower_name='$name', 
        slitter_config='$slitterconfig', ribbon_config='$ribbonconfig', stackers='$stackers', splicers='$splicers', 
        strappers='$strappers', counterveyors='$counterveyors' WHERE id=$towerid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error']; 
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the tower','error');
    } else {
        setUserMessage('Press tower successfully saved','success');
    }
    redirect("?action=listtowers&pressid=$pressid");
       
}

function delete_press()
{
    $pressid=intval($_GET['pressid']);
    $sql="DELETE FROM press WHERE id=$pressid";
    $dbDelete=dbexecutequery($sql);
    $error=$dbDelete['error'];
    $sql="DELETE FROM press_towers WHERE press_id=$pressid";
    $dbDelete=dbexecutequery($sql);
    $error.=$dbDelete['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem deleting the press.'.$error,'error');
    } else {
        setUserMessage('Press successfully deleted. Be aware that you need a press for the system to function properly.','success');
    }
    redirect("?action=listpress");
}

function delete_tower()
{
    $towerid=intval($_GET['towerid']);
    $pressid=intval($_GET['pressid']);
    $sql="DELETE FROM press_towers WHERE id=$towerid";
    $dbDelete=dbexecutequery($sql);
    $error=$dbDelete['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem deleting the tower','error');
    } else {
        setUserMessage('Insert run successfully saved','success');
    }
    redirect("?action=listtowers&pressid=$pressid");

}


function tower_equipment($action)
{
    $type=$_GET['type'];
    $pressid=intval($_GET['pressid']);
    if($action=='add' || $action=='edit')
    {
        if($action=='add')
        {
            
        } else {
            $id=intval($_GET['id']);
            $sql="SELECT * FROM equipment WHERE id=$id";
            $dbEquipment=dbselectsingle($sql);
            $equipment=$dbEquipment['data'];
            
            $name=$equipment['equipment_name'];
            $notes=$equipment['equipment_notes'];    
        }
        print "<form method=post>\n";
        make_text('name',$name,'Equipment Name','Name of equipment',50);
        make_textarea('notes',$notes,'Notes','',60,20);
        make_submit('submit','Save Equipment');
        make_hidden('id',$id);
        make_hidden('pressid',$pressid);
        make_hidden('type',$type);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="SELECT * FROM equipment WHERE id=$id";
        $dbEquipment=dbselectsingle($sql);
        $equipment=$dbEquipment['data'];
        $sql="DELETE FROM equipment WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error.=$dbUpdate['error'];
        $sql="DELETE FROM equipment_component WHERE equipment_id=$id";
        $dbDelete=dbexecutequery($sql);
        $error.=$dbDelete['error'];
        if($error=='')
        {
            setUserMessage("The piece of equipment has been successfully removed.",'success');
        } else {
            setUserMessage("There was a problem deleting the piece of equipment.<br>$error",'error');
        }
        redirect("?action=equipment&type=$type&pressid=$pressid&towerid=$towerid");
    } else {
        $sql="SELECT * FROM equipment WHERE equipment_tie_type='press' AND equipment_tie_id='$pressid' AND specialist_type='$type' ORDER BY equipment_name";
        $dbEquipment=dbselectmulti($sql);
        tableStart("Once equipment is added, you must still go back into the individual towers and enable them.<br>Any components or parts that you wish to add to the equipment must be done through the \"Setup Equipment\" menu item under \"Maintenance\".<br>,<a href='?action=addequipment&pressid=$pressid&type=$type'>Add new $type</a>,<a href='?action=listtowers&pressid=$pressid'>Return to tower list</a>,<a href='?action=list'>Return to press list</a>",'Equipment',3);
        if($dbEquipment['numrows']>0)
        {
            foreach($dbEquipment['data'] as $equipment)
            {
                print "<tr>\n";
                print "<td>".stripslashes($equipment['equipment_name'])."</td>";
                print "<td><a href='?action=editequipment&id=$equipment[id]&pressid=$pressid&type=$type'>Edit</a></td>\n";
                print "<td><a href='?action=deleteequipment&id=$equipment[id]&pressid=$pressid&type=$type' class='delete'>Delete</a></td>\n";
            }
        }
        tableEnd($dbEquipment);
    }  
}

function save_tower_equipment()
{
    global $siteID,$pressDepartmentID;
    $id=$_POST['id'];
    $pressid=$_POST['pressid'];
    $type=$_POST['type'];
    $name=addslashes($_POST['name']);
    $notes=addslashes($_POST['notes']);
    if ($id==0)
    {
        $sql="INSERT INTO equipment (equipment_name, equipment_department, equipment_notes, specialist_type, site_id, 
        equipment_tie_type, equipment_tie_id)
        VALUES ('$name', '$pressDepartmentID', '$notes', '$type', '$siteID', 'press', '$pressid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        //we don't allow changing of equipment type, you have to delete the equipment and start again
        $sql="UPDATE equipment SET equipment_name='$name', equipment_notes='$notes' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    if($error=='')
    {
        setUserMessage("The equipment has been saved successfully.",'success');
    } else {
        setUserMessage("There was a problem saving the equipment.<br>$error",'error');
    }
    redirect("?action=equipment&type=$type&pressid=$pressid");
}

footer();  
?>