<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;

if ($_POST['submit']=='Add'){
    save_privilege('insert');
} elseif ($_POST['submit']=='Update'){
    save_privilege('update'); 
} else {
    show_privileges();
}

function show_privileges()
{
    $action=$_GET['action'];
    $id=intval($_GET['id']);
    if ($action=='add' || $action=='edit')
    {
        $types=array("cms"=>"Mango","site"=>"System");
        if ($action=='add')
        {
            $button='Add';
            $type="cms";
            $weight=99;
            $autoenable=0;
            $includejs=0;
        } else {
            $sql="SELECT * FROM core_permission_list WHERE id=$id";
            $dbPermission=dbselectsingle($sql);
            $permission=$dbPermission['data'];
            $type=stripslashes($permission['type']);
            $displayname=stripslashes($permission['displayname']);
            $weight=stripslashes($permission['weight']);
            $autoenable=stripslashes($permission['auto_enable']);
            $includejs=stripslashes($permission['include_js']);
            $jsVarname=stripslashes($permission['js_varname']);
            $button="Update";
        }
        print "<form method=post>\n";
        make_select('type',$types[$type],$types,'Permission Type');
        make_text('displayname',$displayname,'Permission','Short but descriptive name for the permission',50);
        make_checkbox('autoenable',$autoenable,'Auto Grant','Automatically grant this permission to all new users');
        make_checkbox('includejs',$includejs,'Include as a JS variable','Set this as a variable in the head');
        make_text('js_varname',$jsVarname,'Javascript Variable','Specify the name of the javascript variable to set',50);
        make_number('weight',$weight,'Sort Order','1=highest');
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $sql="DELETE FROM core_permission_list WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        $sql="DELETE FROM core_permission_page WHERE permissionID=$id";
        $dbDelete=dbexecutequery($sql);
        $error.=$dbDelete['error'];
        $sql="DELETE FROM user_permissions WHERE permissionID=$id";
        $dbDelete=dbexecutequery($sql);
        $error.=$dbDelete['error'];
        if($error=='')
        {
            setUserMessage('Permission was deleted successfully.','success');
        } else {
            setUserMessage('There was a problem deleting the permission.<br>'.$error,'error');
        }
        redirect("?action=list");
    } else {
        //list the privileges
        $sql="SELECT * FROM core_permission_list";
        $dbPermissions=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new permission</a>","Permission",3);
        if ($dbPermissions['numrows']>0)
        {
            foreach($dbPermissions['data'] as $permission)
            {
                $display=$permission['displayname'];
                $id=$permission['id'];
                print "<tr><td>$display</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</td>";
                print "<td><a hred='?action=delete&id=$id' class='delete'>Delete</a></td>";
                print "</tr>\n";
            
            }
        }
        tableEnd($dbPermissions);
    }

}


function save_privilege($action)
{
    $displayname=addslashes($_POST['displayname']);
    $type=addslashes($_POST['type']);
    $weight=addslashes($_POST['weight']);
    $jsVarname=addslashes($_POST['js_varname']);
    $permissionID=$_POST['id'];
    if($_POST['autoenable']){$autoenable=1;}else{$autoenable=0;}
    if($_POST['includejs']){$includejs=1;}else{$includejs=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO core_permission_list (displayname, weight, type, auto_enable, include_js, js_varname) 
        VALUES ('$displayname', '$weight', '$type', '$autoenable', '$includejs', '$jsVarname')";
        $db=dbinsertquery($sql);
        $permissionID=$db['insertid'];
        $error=$db['error'];    
    } else {
        $sql="UPDATE core_permission_list SET displayname='$displayname', weight='$weight', type='$type', 
        auto_enable='$autoenable', include_js='$includejs', js_varname='$jsVarname' WHERE id=$permissionID";
        $db=dbexecutequery($sql);
        $error=$db['error'];
    }
    
    
    if($autoenable)
    {
        //delete any existing instances (do this instead of update because if the person doesn't have any...)
        $sql="DELETE FROM user_permissions WHERE permissionID=$permissionID";
        $dbUpdate=dbexecutequery($sql);
        $error.=$dbUpdate['error'];
        
        //grant this permission to all existing users
        $sql="SELECT id FROM users";
        $dbUsers=dbselectmulti($sql);
        if($dbUsers['numrows']>0)
        {
            $values='';
            foreach($dbUsers['data'] as $user)
            {
                $values.="('$permissionID', '$user[id]', 1),";   
            }
            $values=substr($values,0,strlen($values)-1);
            if($values!='')
            {
                $sql="INSERT INTO user_permissions (permissionID, user_id, value) VALUES $values";
                $dbInsert=dbinsertquery($sql);
                $error.=$dbInsert['error'];
            }
        }
    }
    
    if($error=='')
    {
        setUserMessage('Permission was saved successfully.','success');
    } else {
        setUserMessage('There was a problem saving the permission.<br>'.$error,'error');
    }
    redirect("?action=list");
    
}

footer();
?>
