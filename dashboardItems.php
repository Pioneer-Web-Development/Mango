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
    case "Save":
    save_item('insert');
    break;
    
    case "Update":
    save_item('update');
    break;
    
    case "add":
    items('add');
    break;
    
    case "edit":
    items('edit');
    break;
    
    case "delete":
    items('delete');
    break;
    
    case "list":
    items('list');
    break;
    
    default:
    items('list');
    break;
    
} 
     
function items($action)
{
    global $siteID;
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save";
            $order=1;
            $column=1;
        } else {
            $button="Update";
            $id=intval($_GET['id']);
            $sql="SELECT * FROM dashboard_items WHERE id=$id";
            $dbRecord=dbselectsingle($sql);
            $record=$dbRecord['data'];
            $name=stripslashes($record['dashboard_name']);
            $fname=stripslashes($record['function_name']);
            $order=stripslashes($record['default_order']);
            $column=stripslashes($record['default_column']);
        }
        print "<form method=post>\n";
        make_text('name',$name,'Item Name');
        make_text('fname',$fname,'Function Name','What is the function that should be called to create this item?');
        make_number('column',$column,'Column','Whic column should it appear in (1-3)');
        make_number('order',$order,'Order','Sort order in the column');
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=intval($_GET['id']);
        $sql="DELETE FROM dashboard_items WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        if ($dbUpdate['error']!='')
        {
            setUserMessage('There was a problem deleting the dashboard item.<br>'.$dbUpdate['error'],'error');
        } else {
            setUserMessage('Dashboard item successfully deleted.','success');
        }
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM dashboard_items ORDER BY default_column, default_order";
        $dbCustomers=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new dashboard item</a>","Item Name,Column,Order",5);
        if ($dbCustomers['numrows']>0)
        {
            foreach($dbCustomers['data'] as $customer)
            {
                $name=stripslashes($customer['dashboard_name']);
                $column=$customer['default_column'];
                $order=$customer['default_order'];
                $customerid=$customer['id'];
                print "<tr><td>$name</td><td>$column</td><td>$order</td>";
                print "<td><a href='?action=edit&id=$customerid'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=delete&id=$customerid'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbCustomers);
        
    }
       
  
}

function save_item($action)
{
    global $siteID;
    $id=$_POST['id'];
    $name=addslashes($_POST['name']);
    $fname=addslashes($_POST['fname']);
    $column=addslashes($_POST['column']);
    $order=addslashes($_POST['order']);
    if ($action=='insert')
    {
        $sql="INSERT INTO dashboard_items (dashboard_name, default_column, default_order, function_name) 
        VALUES ('$name', '$column', '$order', '$fname')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE dashboard_items SET function_name='$fname', dashboard_name='$name', default_column='$column', default_order='$order' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the dashboard item.<br>'.$error,'error');
    } else {
        setUserMessage('Dashboard item successfully saved','success');
    }
    redirect("?action=list");
    
}

footer();
?>
