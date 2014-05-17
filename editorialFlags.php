<?php
include("includes/mainmenu.php") ;

if($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
switch($action)
{
    case "Add":
    save_item('insert');
    break;
    
    case "Update":
    save_item('update');
    
    case "add":
    items('add');
    break;
    
    case "edit":
    items('edit');
    break;
    
    case "delete":
    items('delete');
    break;
    
    default:
    items('list');
    break;
}


function items($action)
{
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Add';
        } else {
            $id=intval($_GET['id']);
            $sql="SELECT * FROM editorial_flags WHERE id=$id";
            $dbCheck=dbselectsingle($sql);
            $check=$dbCheck['data'];
            $name=stripslashes($check['flag_name']);
            $button="Update";
        }
        print "<form method=post>\n";
        make_text('name',$name,'Flag Name','Matching the Town News Flag',50);
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="DELETE FROM editorial_flags WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        redirect('?action=list');
    } else {
        //list the privileges
        $sql="SELECT * FROM editorial_flags ORDER BY flag_name";
        $dbItems=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new flag</a>","Flag Name",3);
        if ($dbItems['numrows']>0)
        {
            foreach($dbItems['data'] as $item)
            {
                $name=$item['flag_name'];
                $id=$item['id'];
                print "<tr>";
                print "<td>$name</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</td>";
                print "<td><a class='delete' href='?action=delete&id=$id'>Delete</a></td>";
                print "</tr>\n";
            
            }
        }
        tableEnd($dbItems);
    }

}


function save_item($action)
{
    global $siteID;
    $name=addslashes($_POST['name']);
    $id=$_POST['id'];
    
    if ($action=='insert')
    {
        $sql="INSERT INTO editorial_flags (flag_name) VALUES ('$name')";
        $db=dbinsertquery($sql);
        $error=$db['error'];
    } else {
        $sql="UPDATE editorial_flags SET flag_name='$name' WHERE id=$id";
        $db=dbexecutequery($sql);
        $error=$db['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the flag.<br>'.$error,'error');
    } else {
        setUserMessage('Flag successfully saved','success');
    }
    redirect("?action=list");
    
}
footer();
?>
