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
    case "Save PM":
    save_pm('insert');
    break;
    
    case "Update PM":
    save_pm('update');
    break;
    
    case "add":
    setup_pm('add');
    break;
    
    case "edit":
    setup_pm('edit');
    break;
    
    case "delete":
    setup_pm('delete');
    break;
    
    case "list":
    setup_pm('list');
    break;
    
    case "Add PM":
    add_existing();
    break;
    
    case "remove":
    remove_pm();
    break;
    
    
    
    default:
    setup_pm('list');
    break;   
} 


function add_existing()
{
    $type=$_POST['type'];
    $componentid=intval($_POST['componentid']);
    $equipmentid=intval($_POST['equipmentid']);
    $parentid=intval($_POST['parentid']);
    $pmid=intval($_POST['pmid']);
    $sql="INSERT INTO equipment_pm_xref (equipment_type, equipment_id, component_id, pm_id, parent_id) VALUES 
    ('$type', '$equipmentid', '$componentid', '$pmid', '$parentid')";
    $dbAdd=dbinsertquery($sql);
    if($dbAdd['error']!='')
    {
        setUserMessage('There was a problem adding the existing PM Task to this component.<br>'.$dbAdd['error'],'error');
    } else {
        setUserMessage('The PM Task has been added to this component.','success');
    }
    setup_pm('list'); 
}


function remove_pm()
{
    $type=$_GET['type'];
    $componentid=intval($_GET['componentid']);
    $equipmentid=intval($_GET['equipmentid']);
    $parentid=intval($_GET['parentid']);
    $id=intval($_GET['pmid']);
    $xrefid=intval($_GET['xrefid']);
    $sql="DELETE FROM equipment_pm_xref WHERE id=$xrefid";
    $dbUpdate=dbexecutequery($sql);
    if($dbUpdate['error']!='')
    {
        setUserMessage('There was a problem removing the existing PM task from this component.<br>'.$dbUpdate['error'],'error');
    } else {
        setUserMessage('The PM Task has been removed from this component.','success');
    }
    setup_pm('list');
}
    
function setup_pm($action)
{
    global $vendors;
    $componentid=intval($_GET['componentid']);
    $equipmentid=intval($_GET['equipmentid']);
    $parentid=intval($_GET['parentid']);
    $type=$_GET['type'];
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save PM";
            $vendorid=0;
            $lifeDays=0;
            $lifeImpressions=0;
            $cost="0.00";
            $estimate="0";
        } else {
            $button="Update PM";
            $pmid=intval($_GET['pmid']);
            $sql="SELECT * FROM equipment_pm WHERE id=$pmid";
            $dbPM=dbselectsingle($sql);
            $pm=$dbPM['data'];
            $name=stripslashes($pm['pm_name']);
            $task=stripslashes($pm['pm_task']);
            $notes=stripslashes($pm['pm_notes']);
            $cost=$pm['pm_cost'];
            $estimate=$pm['pm_estimated_time'];
            $vendorid=$pm['pm_vendor'];;
            $lifeDays=$pm['pm_life_days'];
            $lifeImpressions=$pm['pm_life_impressions'];
            $lifetype=$pm['pm_life_type'];
        }
        
        $lifetypes=array("impressions"=>"impressions","days"=>"days");
        print "<form method=post>\n";
        make_text('pmname',$name,'PM Name','Descriptive name of the maintenance task',50);
        make_select('pmvendor',$vendors[$vendorid],$vendors,'PM Vendor','Is there an outside vendor that performs this PM?');
        make_textarea('task',$task,'Task','Description of the task (be as specific as possible)',70,30);
        make_textarea('notes',$notes,'Notes','Miscellaneous notes regarding the task',70,10);
        print "<div class='label'>PM Cycle</div>\n";
        print "<div class='input'>\n";
        print "What is the cycle of the the PM task: ".input_select('lifetype',$lifetypes[$lifetype],$lifetypes);
        print "<br>Cycle in days (ex: 3 months = 90):<br>\n".input_text('lifeDays',$lifeDays,'10',false,'','','','return isNumberKey(event);');
        print "<br>Cycle in impressions (ex: every 1,000,000 impressions):<br>\n".input_text('lifeImpressions',$lifeImpressions,'10',false,'','','','return isNumberKey(event);');
        print "</div>\n";
        print "<div class='clear'></div>\n";
        
        make_number('estimate',$estimate,'Estimated Time','Estimated time (in minutes) the PM task should take');
        make_number('cost',$cost,'Estimated Cost','Estimated cost of all parts and materials for the PM task');
        make_submit('submit',$button);
        make_hidden('componentid',$componentid);
        make_hidden('pmid',$pmid);
        make_hidden('parentid',$parentid);
        make_hidden('type',$type);
        make_hidden('equipmentid',$equipmentid);
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=intval($_GET['pmid']);
        $sql="DELETE FROM equipment_pm WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            $sql="DELETE FROM equipment_pm_xref WHERE part_id=$id";
            $dbDelete=dbexecutequery($sql);
            setUserMessage('There was a problem deleting this preventative maintenance task.<br />'.$error,'error');
        } else {
            setUserMessage('The preventative maintenance task has been successfully deleted.','success');
        }
        redirect("?action=list&componentid=$componentid&equipmentid=$equipmentid&type=$type");
    } else {
        $parts=array();
        $parts[0]='Select PM Task';
        $psql="SELECT * FROM equipment_pm";
        $dbExisting=dbselectmulti($psql);
        if($dbExisting['numrows']>0)
        {
            foreach($dbExisting['data'] as $existing)
            {
                $parts[$existing['id']]=stripslashes($existing['pm_name']);
            }
        }
        $addpartform="<br><form method=post>Add an existing PM Task to this component<br />";
        $addpartform.=input_select('pmid',$parts[0],$parts);
        $addpartform.="<br>";
        $addpartform.="<input type='hidden' name='type' value='$type'>";
        $addpartform.="<input type='hidden' name='componentid' value='$componentid'>";
        $addpartform.="<input type='hidden' name='equipmentid' value='$equipmentid'>";
        $addpartform.="<input type='hidden' name='parentid' value='$parentid'>";
        $addpartform.="<input type='submit' name='submit' value='Add PM' />";
        $addpartform.="</form>";
        if(isset($_GET['type']) && $_GET['type']!='')
        {
            //and look for any parts that have been cross-referenced to this component
            $sql="SELECT A.id, A.pm_name, B.id as xrefid FROM equipment_pm A, equipment_pm_xref B WHERE A.id=B.pm_id AND B.equipment_id=$equipmentid AND B.component_id=$componentid AND B.equipment_type='$type' ORDER BY A.pm_name";
            $dbExisting=dbselectmulti($sql);
            if($dbExisting['numrows']>0)
            {
                foreach($dbExisting['data'] as $record)
                {
                    $ids.=$record['id'].',';
                }
                $dbAllParts=$dbExisting;
                $ids=substr($ids,0,strlen($ids));
                if(trim($ids)!=''){
                    $ids="AND A.id IN($ids)";
                }
                $sql="SELECT A.id, A.pm_name FROM equipment_pm A WHERE A.equipment_id=$equipmentid $ids AND A.component_id=$componentid AND A.equipment_type='$type' ORDER BY A.pm_name";
                $dbParts=dbselectmulti($sql);
                if($dbParts['numrows']>0)
                {
                    foreach($dbParts['data'] as $p)
                    {
                        $dbAllParts['data'][]=$p;
                    }
                }
            } else
            {
                $sql="SELECT A.id, A.pm_name FROM equipment_pm A WHERE A.equipment_id=$equipmentid AND A.component_id=$componentid AND A.equipment_type='$type' ORDER BY A.pm_name";
                $dbAllParts=dbselectmulti($sql);
            }
            tableStart("<a href='?action=add&componentid=$componentid&equipmentid=$equipmentid&type=$type&parentid=$parentid'>Add new PM</a>,<a href='?action=list'>Show all PM Tasks</a>,<a href='equipmentComponents.php?action=list&equipmentid=$equipmentid&parentid=0&type=$type'>Return to components</a>,<a href='equipment.php'>Return to equipment</a>,$addpartform","Task ID,Task Name",4);
        } else {
            $sql="SELECT A.id, A.pm_name FROM equipment_pm A ORDER BY A.pm_name";
            $dbAllParts=dbselectmulti($sql);
            $addpartform='';
            tableStart("<a href='?action=add'>Add new PM Task</a>,<a href='?action=list'>Show all PM Tasks</a>,<a href='equipment.php'>Go to equipment</a>,$addpartform","Task ID,Task Name",4);
        }
        
        /*
        $sql="SELECT A.pm_name, A.id FROM equipment_pm A, equipment_pm_xref B WHERE B.equipment_id='$equipmentid' AND B.component_id='$componentid' AND B.equipment_type='$type' AND B.pm_id=A.id ORDER BY A.pm_name"; 
        $dbPM=dbselectmulti($sql);
        tableStart("<a href='?&action=add&componentid=$componentid&equipmentid=$equipmentid&type=$type&parentid=$parentid'>Add new PM Task</a>,<a href='equipmentComponents.php?action=list&equipmentid=$equipmentid&parentid=0&type=$type'>Return to components</a>,<a href='equipment.php'>Return to equipment</a>","Task ID,Task Name",4);
        */
        if ($dbAllParts['numrows']>0)
        {
            foreach($dbAllParts['data'] as $pm)
            {
                $pmname=$pm['pm_name'];
                $id=$pm['id'];
                print "<tr><td>$id</td><td>$pmname</td>";
                print "<td><a href='?action=edit&pmid=$id&componentid=$componentid&equipmentid=$equipmentid&type=$type&parentid=$parentid'>Edit</a></td>\n";
                if($pm['xrefid']!=0)
                {
                    $xrefid=$pm['xrefid'];
                    print "<td><a href='?action=remove&xrefid=$xrefid&pmid=$id&componentid=$componentid&equipmentid=$equipmentid&type=$type'>Remove PM Task</a></td>\n";
                } else {
                    print "<td><a href='?action=delete&pmid=$id&componentid=$componentid&equipmentid=$equipmentid&type=$type' class='delete'>Delete PM Task</a></td>\n";
                }
                print "</tr>\n";
            }
        }
        tableEnd($dbAllParts);        
    }
}


function save_pm($action)
{
    $id=$_POST['pmid'];
    $type=$_POST['type'];
    $componentid=$_POST['componentid'];
    $equipmentid=$_POST['equipmentid'];
    $parentid=$_POST['parentid'];
    $name=addslashes($_POST['pmname']);
    $cost=$_POST['cost'];
    $estimate=$_POST['estimate'];
    if ($cost==''){$cost='0.00';}
    $notes=$_POST['notes'];
    $notes=addslashes($notes);
    $task=$_POST['task'];
    $task=addslashes($task);
    $vendorid=addslashes($_POST['pmvendor']);
    $lifeDays=addslashes($_POST['lifeDays']);
    $lifeDays=str_replace(" ","",$lifeDays);
    $lifeDays=str_replace("days","",$lifeDays);
    $lifeDays=str_replace("day","",$lifeDays);
    $lifeCount=addslashes($_POST['lifeImpressions']);
    $lifetype=$_POST['lifetype'];
    
    if ($action=='insert')
    {
        $sql="INSERT INTO equipment_pm (equipment_type, parent_id, equipment_id, component_id, pm_name, pm_task,
        pm_notes, pm_vendor, pm_life_days, pm_life_impressions, pm_life_type, pm_estimated_time, 
        pm_cost) VALUES ('$type', '$parentid', '$equipmentid', '$componentid', '$name', '$task', '$notes', 
        '$vendorid', '$lifeDays', '$lifeCount', '$lifetype', '$estimate', '$cost')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $id=$dbInsert['insertid'];
        $sql="INSERT INTO equipment_pm_xref (equipment_id, equipment_type, component_id, pm_id, parent_id) 
        VALUES ('$equipmentid', '$type', '$componentid', '$id', '0')";
        $dbInsert=dbinsertquery($sql);
    } else {
        $sql="UPDATE equipment_pm SET equipment_type='$type', parent_id='$parentid', equipment_id='$equipmentid', component_id='$componentid', pm_name='$name', pm_task='$task', pm_notes='$notes', pm_vendor='$vendorid', pm_life_days='$lifeDays', pm_life_impressions='$lifeCount', pm_life_type='$lifetype', pm_estimated_time='$estimate', pm_cost='$cost' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving this preventative maintenance task.<br />'.$error,'error');
    } else {
        setUserMessage('The preventative maintenance task has been successfully saved.','success');
    }
    redirect("?action=list&equipmentid=$equipmentid&componentid=$componentid&type=$type&parentid=$parentid");
    
}


footer();

?>
