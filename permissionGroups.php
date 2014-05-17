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
        
        case "Set Permissions":
        save_permissions('update');
        break;
        
        case "add":
        setup_group('add');
        break;
        
        case "edit":
        setup_group('edit');
        break;
        
        case "permissions":
        permissions();
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
            $sql="SELECT * FROM core_permission_groups WHERE id=$id";
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
        $sql="DELETE FROM core_permission_groups WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the group.<br />'.$error,'error');
        } else {
            $sql="DELETE FROM core_permission_group_xref WHERE group_id=$id";
            $dbDelete=dbexecutequery($sql);
            setUserMessage('The group has been successfully deleted.','success');
        }
    
        redirect("?action=list");
    } else {
        global $siteID;
        $sql="SELECT * FROM core_permission_groups ORDER BY group_name";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new group</a>","Name",4);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $name=$group['group_name'];
                $id=$group['id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=permissions&id=$id'>Permissions</a></td>\n";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbGroups);
        
    }
}

function permissions()
{
    $groupid=intval($_GET['id']);
    $sql="SELECT * FROM core_permission_list WHERE type='cms' ORDER BY displayname";
    $dbPermissions=dbselectmulti($sql);
    $sql="SELECT * FROM core_permission_group_xref WHERE group_id=$groupid";
    $dbStaff=dbselectmulti($sql);
    $staffpermissions=$dbStaff['data'];
    if ($dbPermissions['numrows']>0)
    {
        print "<h4>Please select permissions for this group:</h4>";
        print "<form method=post>\n";
        $i=1;
        //split 3 columns
        $col=round($dbPermissions['numrows']/3,0);
        print "<div style='float:left;width:250px;margin-right:10px;'>\n";
        foreach($dbPermissions['data'] as $permission)
        {
            $pvalue=0;
            if ($dbStaff['numrows']>0)
            {
                foreach($staffpermissions as $staffpermission)
                {
                    if ($permission['id']==$staffpermission['permission_id'])
                    {
                        if ($staffpermission['value']==1)
                        {
                            $pvalue=1;
                        }        
                    }
                }
            }
            print input_checkbox('permission_'.$permission['id'],$pvalue);
            print "<label for='permission_$permission[id]'>&nbsp;&nbsp;".$permission['displayname']."</label><br>";
            if ($i==$col)
            {
                $i=1;
                print "</div>\n";
                print "<div style='float:left;width:250px;margin-right:10px;'>\n";
            } else {
                $i++;
            }
        }
        print "</div><div class='clear'></div>\n";
        print "<div class='label'></div><div class='input'>\n";
        make_hidden('groupid',$groupid);
        make_submit('submit','Set Permissions');
        print "</form>\n";
         print "</div><div class='clear'></div>\n";
        
        
    } else {
       print "Sorry, no permissions have been defined yet.";
    }
    
}



function save_permissions()
{
    $groupid=$_POST['groupid'];
    //start by deleting all existing permissions for this gropu
    $sql="DELETE FROM core_permission_group_xref WHERE group_id=$groupid";
    $dbDelete=dbexecutequery($sql);
    $sql="SELECT * FROM core_permission_list WHERE type='cms' ORDER BY weight";
    $dbPermissions=dbselectmulti($sql);
    $value="";
    foreach ($dbPermissions['data'] as $permission)
    {
        $pvalue=0;
        if ($_POST["permission_$permission[id]"])
        {
            $pvalue=1;
        }
        $value.="('$permission[id]','$groupid','$pvalue'),";
    }
    $value=substr($value,0,strlen($value)-1);
    $sql="INSERT INTO core_permission_group_xref (permission_id, group_id, value) VALUES $value";
    $dbInsert=dbinsertquery($sql);
    $error=$dbInsert['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem saving the group permissions.<br>'.$error,'error');
    } else {
        setUserMessage('Group permissions have been successfully saved','success');
    }
    redirect("?action=list");  
}

function save_group($action)
{
    global $siteID;
    $id=$_POST['groupid'];
    $name=addslashes($_POST['group_name']);
    if ($action=='insert')
    {
        $sql="INSERT INTO core_permission_groups (group_name) VALUES ('$name')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE core_permission_groups SET group_name='$name' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the group.<br />'.$error,'error');
    } else {
        setUserMessage('The group has been successfully saved.','success');
    }
    
    redirect("?action=list");
    
}

footer();
?>
