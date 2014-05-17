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
    save_replace('insert');
    break;
    
    case "Update":
    save_replace('update');
    
    case "add":
    replacements('add');
    break;
    
    case "edit":
    replacements('edit');
    break;
    
    case "delete":
    replacements('delete');
    break;
    
    default:
    replacements('list');
    break;
}


function replacements($action)
{
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Add';
        } else {
            $id=intval($_GET['id']);
            $sql="SELECT * FROM editorial_ap_replace WHERE id=$id";
            $dbCheck=dbselectsingle($sql);
            $check=$dbCheck['data'];
            $original=stripslashes($check['find_text']);
            $new=stripslashes($check['replace_text']);
            $button="Update";
        }
        print "<form method=post>\n";
        make_text('original',$original,'Original Text','Text to be found and replaced',50);
        make_text('new',$new,'Replacement Text','Leave blank to just remove the text',50);
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="DELETE FROM editorial_ap_replace WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the replacement.<br>'.$error,'error');
        } else {
            setUserMessage('The replacement was successfully deleted.','success');
        }
        redirect('?action=list');
    } else {
        //list the privileges
        $sql="SELECT * FROM editorial_ap_replace";
        $dbItems=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new replacement</a>","Original,Replacement",4);
        if ($dbItems['numrows']>0)
        {
            foreach($dbItems['data'] as $item)
            {
                $original=$item['find_text'];
                $new=$item['replace_text'];
                $id=$item['id'];
                print "<tr>";
                print "<td>$original</td><td>$new</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</td>";
                print "<td><a class='delete' href='?action=delete&id=$id'>Delete</a></td>";
                print "</tr>\n";
            
            }
        }
        tableEnd($dbItems);
    }

}


function save_replace($action)
{
    global $siteID;
    $original=addslashes($_POST['original']);
    $new=addslashes($_POST['new']);
    $id=$_POST['id'];
    
    if ($action=='insert')
    {
        $sql="INSERT INTO editorial_ap_replace (find_text, replace_text) VALUES ('$original', '$new')";
        $db=dbinsertquery($sql);
        $error=$db['error'];
    } else {
        $sql="UPDATE editorial_ap_replace SET find_text='$original', replace_text='$new' WHERE id=$id";
        $db=dbexecutequery($sql);
        $error=$db['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the replacement.<br>'.$error,'error');
    } else {
        setUserMessage('The replacement was successfully saved','success');
    }
    redirect("?action=list");
    
}
footer();
?>
