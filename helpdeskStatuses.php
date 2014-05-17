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
        case "Save Status":
        save_status('insert');
        break;
        
        case "Update Status":
        save_status('update');
        break;
        
        case "add":
        setup_status('add');
        break;
        
        case "edit":
        setup_status('edit');
        break;
        
        case "delete":
        setup_status('delete');
        break;
        
        case "list":
        setup_status('list');
        break;
        
        default:
        setup_status('list');
        break;
        
    } 
    
    
function setup_status($action)
{
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Status";
        } else {
            $button="Update Status";
            $id=$_GET['id'];
            $sql="SELECT * FROM helpdesk_statuses WHERE id=$id";
            $dbStatus=dbselectsingle($sql);
            $status=$dbStatus['data'];
            $name=$status['status_name'];
            $order=$status['status_order'];
        }
        print "<form method=post>\n";
        make_text('status_name',$name,'Status Name');
        make_slider('status_order',$order,'Status Order','',1,100);
        make_hidden('statusid',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=intval($_GET['id']);
        $sql="DELETE FROM helpdesk_statuses WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM helpdesk_statuses ORDER BY status_order ASC";
        $dbStatuses=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new status</a>","Order,Name",4);
        if ($dbStatuses['numrows']>0)
        {
            foreach($dbStatuses['data'] as $status)
            {
                $name=$status['status_name'];
                $order=$status['status_order'];
                $id=$status['id'];
                print "<tr><td>$order</td><td>$name</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbStatuses);
        
    }
}

function save_status($action)
{
    global $siteID;
    $id=$_POST['statusid'];
    $name=addslashes($_POST['status_name']);
    $order=addslashes($_POST['status_order']);
    if ($action=='insert')
    {
        $sql="INSERT INTO helpdesk_statuses (status_name, status_order, site_id) VALUES ('$name', '$order', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE helpdesk_statuses SET site_id='$siteID', status_name='$name', status_order='$order' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!="")
    {
        print "An error occurred: <br>$error<br>The sql was $sql<br>";
    } else {
        redirect("?action=list");
    }
}


footer();
?>
