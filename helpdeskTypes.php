<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
    switch ($action)
    {
        case "Save Type":
        save_type('insert');
        break;
        
        case "Update Type":
        save_type('update');
        break;
        
        case "add":
        setup_types('add');
        break;
        
        case "edit":
        setup_types('edit');
        break;
        
        case "delete":
        setup_types('delete');
        break;
        
        case "list":
        setup_types('list');
        break;
        
        default:
        setup_types('list');
        break;
        
    } 
    
    
function setup_types($action)
{
    $sql="SELECT * FROM user_groups ORDER BY group_name";
    $dbGroups=dbselectmulti($sql);
    $groups=array();
    $groups[0]='Please choose';
    if ($dbGroups['numrows']>0)
    {
        foreach($dbGroups['data'] as $group)
        {
            $groups[$group['id']]=$group['group_name'];
        }
    }
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Type";
            $pims=0;
            $groupid=0;
            $production=0;
        } else {
            $button="Update Type";
            $id=intval($_GET['id']);
            $sql="SELECT * FROM helpdesk_types WHERE id=$id";
            $dbType=dbselectsingle($sql);
            $type=$dbType['data'];
            $name=$type['type_name'];
            $pims=$type['pims_specific'];
            $production=$type['production_specific'];
            $groupid=$type['group_responsible'];
        }
        print "<form method=post>\n";
        make_text('typename',$name,'Type Name','Name of help desk group');
        make_select('groupid',$groups[$groupid],$groups,'Group Responsible','Group responsible for administration of this type of issue');
        make_checkbox('pims',$pims,'Mango Specific','Check if this topic is specific to Mango (ie Mango Bug Report)');
        make_checkbox('production',$production,'Production Specific','Check if this topic is specific to the production department');
        make_hidden('typeid',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=intval($_GET['id']);
        $sql="DELETE FROM helpdesk_types WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem saving the type.<br />'.$error,'error');
        } else {
            setUserMessage('The type has been successfully saved.','success');
        }
    
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM helpdesk_types ORDER BY type_name";
        $dbTypes=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new help category</a>","Name,Group",4);
        if ($dbTypes['numrows']>0)
        {
            foreach($dbTypes['data'] as $type)
            {
                $group=$groups[$type['group_responsible']];
                $name=$type['type_name'];
                $id=$type['id'];
                if ($type['pims_specific']==1){$type='Mango';}else{$type='General';}
                print "<tr><td>$name</td><td>$group</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
            }
        }
        tableEnd($dbTypes);
    }
}

function save_type($action)
{
    global $siteID;
    $id=$_POST['typeid'];
    $name=addslashes($_POST['typename']);
    $groupid=addslashes($_POST['groupid']);
    if ($_POST['pims']){$pims=1;}else{$pims=0;}
    if ($_POST['production']){$production=1;}else{$production=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO helpdesk_types (type_name, production_specific, pims_specific, group_responsible, site_id)
         VALUES ('$name', '$production', '$pims', '$groupid', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE helpdesk_types SET site_id='$siteID', production_specific='$production', type_name='$name', pims_specific='$pims', group_responsible='$groupid' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the type.<br />'.$error,'error');
    } else {
        setUserMessage('The type has been successfully saved.','success');
    }
    redirect("?action=list");
    
}


footer();
?>
