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
            $sql="SELECT * FROM user_groups WHERE id=$id";
            $dbGroup=dbselectsingle($sql);
            $group=$dbGroup['data'];
            $name=$group['group_name'];
            $email=$group['group_email'];
        }
        print "<form method=post>\n";
        make_text('group_name',$name,'Group Name','',30);
        make_text('group_email',$email,'Group Email','',30);
        make_hidden('groupid',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $sql="DELETE FROM user_groups WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the user group.<br>'.$error,'error');
        } else {
            setUserMessage('User group successfully deleted.','success');
        }
        redirect("?action=list");
    } else {
        global $siteID;
        $sql="SELECT * FROM user_groups WHERE site_id=$siteID ORDER BY group_name";
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
    global $siteID;
    $id=$_POST['groupid'];
    $name=addslashes($_POST['group_name']);
    $email=addslashes($_POST['group_email']);
    if ($action=='insert')
    {
        $sql="INSERT INTO user_groups (group_name, group_email, site_id) VALUES ('$name', '$email', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE user_groups SET group_email='$email', group_name='$name' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem saving the user group.<br>'.$error,'error');
        } else {
            setUserMessage('User group successfully saved','success');
        }
    redirect("?action=list");
}

footer();
?>
