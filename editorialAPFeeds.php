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
    save_feed('insert');
    break;
    
    case "Update":
    save_feed('update');
    
    case "add":
    feeds('add');
    break;
    
    case "edit":
    feeds('edit');
    break;
    
    case "delete":
    feeds('delete');
    break;
    
    default:
    feeds('list');
    break;
}


function feeds($action)
{
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Add';
        } else {
            $id=intval($_GET['id']);
            $sql="SELECT * FROM editorial_ap_feeds WHERE id=$id";
            $dbCheck=dbselectsingle($sql);
            $check=$dbCheck['data'];
            $name=stripslashes($check['feed_name']);
            $section=stripslashes($check['tn_section']);
            $button="Update";
        }
        print "<form method=post>\n";
        make_text('name',$name,'Feed Name','Exact name of the AP feed from AP Exchange',50);
        make_text('section',$section,'Section Name','Full Town News section (ex: news/local)',50);
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="DELETE FROM editorial_ap_feeds WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the feed.<br>'.$error,'error');
        } else {
            setUserMessage('The feed was successfully deleted.','success');
        }
    
        redirect('?action=list');
    } else {
        //list the privileges
        $sql="SELECT * FROM editorial_ap_feeds ORDER BY feed_name";
        $dbItems=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new feed</a>","Feed Name",3);
        if ($dbItems['numrows']>0)
        {
            foreach($dbItems['data'] as $item)
            {
                $name=$item['feed_name'];
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


function save_feed($action)
{
    global $siteID;
    $name=addslashes($_POST['name']);
    $section=addslashes($_POST['section']);
    $id=$_POST['id'];
    
    if ($action=='insert')
    {
        $sql="INSERT INTO editorial_ap_feeds (feed_name, tn_section) VALUES ('$name', '$section')";
        $db=dbinsertquery($sql);
        $error=$db['error'];
    } else {
        $sql="UPDATE editorial_ap_feeds SET feed_name='$name', tn_section='$section' WHERE id=$id";
        $db=dbexecutequery($sql);
        $error=$db['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the feed.<br>'.$error,'error');
    } else {
        setUserMessage('Feed successfully saved','success');
    }
    redirect("?action=list");
    
}
footer();
?>
