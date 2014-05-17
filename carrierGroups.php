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
        case "Save Group":
        save_group('insert');
        break;
        
        case "Update Group":
        save_group('update');
        break;
        
        case "add":
        setup_group('add');
        break;
        
        case "edit":
        setup_group('edit');
        break;
        
        case "delete":
        setup_group('delete');
        break;
        
        case "list":
        setup_group('list');
        break;
        
        default:
        setup_group('list');
        break;
        
    } 
    
    
function setup_group($action)
{
    $id=intval($_GET['id']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Group";
        } else {
            $button="Update Group";
            $sql="SELECT * FROM carrier_groups WHERE id=$id";
            $dbGroup=dbselectsingle($sql);
            $group=$dbGroup['data'];
            $name=$group['group_name'];
        }
        print "<form method=post>\n";
        make_text('group_name',$name,'Group Name','',30);
        make_hidden('groupid',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $sql="DELETE FROM carrier_groups WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the carrier group.<br>'.$error,'error');
        } else {
            setUserMessage('Carrier group successfully deleted.','success');
        }
        redirect("?action=list");
    } else {
        global $siteID;
        $sql="SELECT * FROM carrier_groups ORDER BY group_name";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new group</a>","Name",3);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $name=$group['group_name'];
                $id=$group['id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbGroups);
        
    }
}

function save_group($action)
{
    $id=$_POST['groupid'];
    $name=addslashes($_POST['group_name']);
    if ($action=='insert')
    {
        $sql="INSERT INTO carrier_groups (group_name) VALUES ('$name')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE carrier_groups SET group_name='$name' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the carrier group.<br>'.$error,'error');
    } else {
        setUserMessage('Carrier group successfully saved','success');
    }
    redirect("?action=list");
}

footer();
?>
