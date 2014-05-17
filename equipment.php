<?php
include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
   $id=intval($_GET['id']);
    switch ($action)
    {
        case "Save Equipment":
        save_equipment('insert');
        break;
        
        case "Update Equipment":
        save_equipment('update');
        break;
        
        case "copycompponents":
        copy_components();
        break;
        
        case "Copy Components":
        copy_components_execute();
        break;
        
        case "add":
        setup_equipment('add');
        break;
        
        case "edit":
        setup_equipment('edit');
        break;
        
        case "delete":
        setup_equipment('delete');
        break;
        
        case "list":
        setup_equipment('list');
        break;
        
        default:
        setup_equipment('list');
        break;
        
    } 
    
    
function setup_equipment($action)
{
    global $departments, $siteID;
    $id=intval($_GET['id']);
    $specialistEquipment['generic']='Please select';
    $specialistEquipment['stacker']='Stacker';
    $specialistEquipment['strapper']='Strapper';
    $specialistEquipment['counterveyor']='Conterveyor';
    $specialistEquipment['splicer']='Splicer';
    
    $ties['generic-0']='No tie';
    
    $sql="SELECT * FROM press ORDER BY name";
    $dbPresses=dbselectmulti($sql);
    if($dbPresses['numrows']>0)
    {
        foreach($dbPresses['data'] as $press)
        {
            $ties['press-'.$press['id']]=stripslashes($press['name']);
        }
    }
    $sql="SELECT * FROM inserters ORDER BY inserter_name";
    $dbInserters=dbselectmulti($sql);
    if($dbInserters['numrows']>0)
    {
        foreach($dbInserters['data'] as $inserter)
        {
            $ties['inserter-'.$inserter['id']]=stripslashes($inserter['inserter_name']);
        }
    }
    $sql="SELECT * FROM stitchers ORDER BY stitcher_name";
    $dbStitchers=dbselectmulti($sql);
    if($dbStitchers['numrows']>0)
    {
        foreach($dbStitchers['data'] as $stitcher)
        {
            $ties['stitcher-'.$stitcher['id']]=stripslashes($stitcher['stitcher_name']);
        }
    }
    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Equipment";
            $tie='generic-0';
            $specialist=0;
        } else {
            $button="Update Equipment";
            $sql="SELECT * FROM equipment WHERE id=$id";
            $dbEquipment=dbselectsingle($sql);
            $equipment=$dbEquipment['data'];
            $name=$equipment['equipment_name'];
            $notes=$equipment['equipment_notes'];
            $department=$equipment['equipment_department'];
            $tietype=$equipment['equipment_tie_type'];
            $tieid=$equipment['equipment_tie_id'];
            $specialist=$equipment['specialist_type'];
            $tie=$tietype."-".$tieid;
        }
        print "<form method=post>\n";
        make_text('name',$name,'Equipment Name','Name of equipment or category for parts',50);
        make_select('department',$departments[$department],$departments,'Department','To which department does this piece of equipment belong?');
        make_select('equipmenttie',$ties[$tie],$ties,'Tie to','Tie this piece of equipment to a major press/inserter/stitcher.<br>It will then receive automatic wear &amp; tear.');
        make_select('specialisttype',$specialistEquipment[$specialist],$specialistEquipment,'Specialist Equipment','Setting this along with tying the equipment allows you to tie this directly to a station or tower.');
        make_textarea('notes',$notes,'Notes','',60,20);
        make_hidden('equipmentid',$id);
        make_submit('submit',$button);
       print "</form>\n";  
    } elseif($action=='delete') {
        $sql="SELECT * FROM equipment WHERE id=$id";
        $dbEquipment=dbselectsingle($sql);
        $equipment=$dbEquipment['data'];
        $sql="DELETE FROM equipment WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $sql="DELETE FROM equipment_component WHERE equipment_id=$id";
        $dbDelete=dbexecutequery($sql);
        /**********************************************************************************************
        *
        * NEED ALSO CLEAN UP ANY ASSOCIATED COMPONENTS WHEN DELETING
        * PLEASE NOTE THAT THIS TYPE OF FUNCTION IS ALSO USED IN PRESS-TOWERS WHEN DELETING EQUIPMENT
        ** 
        */
        redirect("?action=list");
    } else {
        //we need to add in all default press, inserter, bindery and sheetfed presses at this time
        $sql="SELECT * FROM press WHERE site_id=$siteID";
        $dbPresses=dbselectmulti($sql);
        $sql="SELECT * FROM inserters WHERE site_id=$siteID";
        $dbInserters=dbselectmulti($sql);
        $sql="SELECT * FROM stitchers WHERE site_id=$siteID";
        $dbStitchers=dbselectmulti($sql);
        $sql="SELECT * FROM equipment WHERE site_id=$siteID ORDER BY equipment_name ASC";
        $dbEquipment=dbselectmulti($sql);
        
        tableStart("<a href='?&action=add'>Add a new piece of equipment</a>","Department,Name",6);
        if ($dbPresses['numrows']>0)
        {
            foreach($dbPresses['data'] as $press)
            {
                $name=$press['name'];
                $id=$press['id'];
                print "<tr><td>Press</td><td>$name</td>";
                print "<td><a href='pressConfig.php?action=edit&pressid=$id'>Edit Press</a></td>\n";
                print "<td><a href='equipmentComponents.php?action=list&equipmentid=$id&parentid=0&type=press'>Manage Components</a></td>\n";
                print "<td></td><td></td></tr>\n"; 
            
            }
        }
        if ($dbInserters['numrows']>0)
        {
            foreach($dbInserters['data'] as $inserter)
            {
                $name=$inserter['inserter_name'];
                $id=$inserter['id'];
                print "<tr><td>Mailroom</td><td>$name</td>";
                print "<td><a href='inserterSetup.php?action=edit&inserterid=$id'>Edit Inserter</a></td>\n";
                print "<td><a href='equipmentComponents.php?action=list&equipmentid=$id&parentid=0&type=inserter'>Manage Components</a></td>\n";
                print "<td></td><td></td></tr>\n"; 
            
            }
        }
        if ($dbStitchers['numrows']>0)
        {
            foreach($dbStitchers['data'] as $stitcher)
            {
                $name=$stitcher['stitcher_name'];
                $id=$stitcher['id'];
                print "<tr><td>Mailroom</td><td>$name</td>";
                print "<td><a href='stitcherSetup.php?action=edit&stitcherid=$id'>Edit Stitcher</a></td>\n";
                print "<td><a href='equipmentComponents.php?action=list&equipmentid=$id&parentid=0&type=stitcher'>Manage Components</a></td>\n";
                
                print "<td></td><td></td></tr>\n";
            
            }
        }
        if ($dbEquipment['numrows']>0)
        {
            foreach($dbEquipment['data'] as $equipment)
            {
                $name=$equipment['equipment_name'];
                $department=$departments[$equipment['equipment_department']];
                $id=$equipment['id'];
                print "<tr><td>$department</td><td>$name</td>";
                print "<td><a href='?action=edit&id=$id'>Edit Equipment</a></td>\n";
                print "<td><a href='equipmentComponents.php?action=list&equipmentid=$id&parentid=0&type=generic'>Manage Components</a></td>\n";
                print "<td><a href='?action=copycompponents&equipmentid=$id&type=generic'>Copy Components</a></td>\n";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
            }
        }
        tableEnd($dbEquipment);
        
    }
}

function save_equipment($action)
{
    global $siteID;
    $id=$_POST['equipmentid'];
    $name=addslashes($_POST['name']);
    $department=addslashes($_POST['department']);
    $notes=addslashes($_POST['notes']);
    $specialist=$_POST['specialisttype'];
    $e=explode("-",$_POST['equipmenttie']);
    $tietype=$e[0];
    $tieid=$e[1];
    if ($action=='insert')
    {
        $sql="INSERT INTO equipment (equipment_name, equipment_department, equipment_notes, specialist_type, site_id, 
        equipment_tie_type, equipment_tie_id)
        VALUES ('$name', '$department', '$notes', '$specialist', '$siteID', '$tietype', '$tieid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        //we don't allow changing of equipment type, you have to delete the equipment and start again
        $sql="UPDATE equipment SET equipment_name='$name', equipment_department='$department',
         equipment_notes='$notes', equipment_tie_type='$tietype', equipment_tie_id='$tieid', 
         specialist_type='$specialist' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving this equipment.<br />'.$error,'error');
    } else {
        setUserMessage('The equipment has been successfully saved.','success');
    }
    redirect("?action=list");
    
}


function copy_components()
{
    $sql="SELECT * FROM equipment ORDER BY equipment_name";
    $dbEquipment=dbselectmulti($sql);
    $equip[0]='Please select source equipment';
    if($dbEquipment['numrows']>0)
    {
        foreach($dbEquipment['data'] as $equipment)
        {
            $equip[$equipment['id']]=stripslashes($equipment['equipment_name']);
        }
    }
    print "<div class='label'>&nbsp;</div>
    <div class='input'>
    This function will copy all components from the specified piece of equipment to this piece. All parts associated will also be moved.<br>If you specify the option to 'clear', all existing components and parts will be removed.</div>\n";
    print "<div class='clear'></div>\n";
    print "<form method=post>\n";
    make_select('equipmentid',$equip[0],$equip,'Source equipment','Select the source to copy components and parts');
    make_checkbox('clear',0,'Clear','Check to clear all existing components and parts before copying');
    make_submit('submit','Copy Components');
    make_hidden('id',intval($_GET['equipmentid']));
    print "</form>\n";    
}

function copy_components_execute()
{
    global $siteID;
    $sourceid=$_POST['equipmentid'];
    $destid=$_POST['id'];
    if($_POST['clear'])
    {
        $sql="DELETE FROM equipment_component WHERE equipment_id=$destid AND equipment_type='generic'";
        $dbDeleteComponent=dbexecutequery($sql);
        $sql="DELETE FROM equipment_part_xref WHERE equipment_id=$destid AND equipment_type='generic'";
        $dbDeleteParts=dbexecutequery($sql);
        setUserMessage('Components and parts successfully cleared from the destination equipment','success');    
    }
    
    //first get all components to the source equipment
    $sql="SELECT * FROM equipment_component WHERE equipment_id=$sourceid AND equipment_type='generic'";
    $dbComponents=dbselectmulti($sql);
    if($dbComponents['numrows']>0)
    {
        $componentCount=0;
        $partCount=0;
        $pmCount=0;
        foreach($dbComponents['data'] as $component)
        {
            $sql="INSERT INTO equipment_component (equipment_id, parent_id, component_name, component_notes,
            component_image, component_order, site_id, equipment_type, component_type, component_core) VALUES (
            '$destid','$component[parent_id]', '$component[component_name]', '$component[component_notes]', 
            '$component[component_image]', '$component[component_order]', '$siteID',
            '$component[equipment_type]','$component[component_type]','$component[component_core]')";
            $dbInsertComponent=dbinsertquery($sql);
            if($dbInsertComponent['error']=='')
            {
                $componentCount++;
                $newComponentid=$dbInsertComponent['insertid'];
                
                //now copy any associated parts
                $sql="SELECT * FROM equipment_part_xref WHERE equipment_id=$sourceid AND component_id=$component[id]";
                $dbParts=dbselectmulti($sql);
                if($dbParts['numrows']>0)
                {
                    foreach($dbParts['data'] as $part)
                    {
                        $sql="INSERT INTO equipment_part_xref (equipment_type, equipment_id, component_id, part_id, parent_id) 
                        VALUES ('$part[equipment_type]', $destid, $newComponentid, '$part[part_id]', '$part[parent_id]')";
                        $dbInsertPart=dbinsertquery($sql);
                        if($dbInsertPart['error']=='')
                        {
                            $partCount++;
                        } else {
                            setUserMessage('There was a problem copying a part.<br>'.$dbInsertPart['error'],'error');
                        }
                    }
                }
                
                //now copy any associated pm tasks
                $sql="SELECT * FROM equipment_pm_xref WHERE equipment_id=$sourceid AND component_id=$component[id]";
                $dbPMs=dbselectmulti($sql);
                if($dbPMs['numrows']>0)
                {
                    foreach($dbPMs['data'] as $pm)
                    {
                        $sql="INSERT INTO equipment_part_xref (equipment_type, equipment_id, component_id, pm_id, parent_id) 
                        VALUES ('$pm[equipment_type]', $destid, $newComponentid, '$pm[pm_id]', '$pm[parent_id]')";
                        $dbInsertPM=dbinsertquery($sql);
                        if($dbInsertPM['error']=='')
                        {
                            $pmCount++;
                        } else {
                            setUserMessage('There was a problem copying a PM task.<br>'.$dbInsertPM['error'],'error');
                        }
                    }
                }
            } else {
                setUserMessage('There was a problem moving component '.$component['id'].'<br>'.$dbInsertComponent['error'],'error');
            }
        }
        setUserMessage('A total of '.$componentCount.' components where copied, containing '.$partCount.' parts and '.$pmCount.' PM tasks.','success');
    } else {
        setUserMessage('There were no components on the source equipment.','error');
    }
    redirect("?action=list");
}
footer();
?> 