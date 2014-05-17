<?php
//<!--VERSION: 1.0 **||**-->

include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
    switch ($action)
    {
        case "Save Item":
        save_room('insert');
        break;
        
        case "Update Item":
        save_room('update');
        break;
        
        case "add":
        setup_rooms('add');
        break;
        
        case "edit":
        setup_rooms('edit');
        break;
        
        case "delete":
        setup_rooms('delete');
        break;
        
        case "list":
        setup_rooms('list');
        break;
        
        default:
        setup_rooms('list');
        break;
        
    } 
    
    
function setup_rooms($action)
{
    global $inventoryUnitTypes;
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Item";
            $initValue=0;
        } else {
            $button="Update Item";
            $roomid=intval($_GET['id']);
            $sql="SELECT * FROM monthly_inventory_items WHERE id=$roomid";
            $dbRoom=dbselectsingle($sql);
            $room=$dbRoom['data'];
            $name=stripslashes($room['name']);
            $unit=stripslashes($room['unit_type']);
            $initValue=stripslashes($room['starting_value']);
        }
        print "<form method=post>\n";
        make_text('name',$name,'Name','Inventory Item',50);
        make_number('starting',$initValue,'Starting Value','Initial Value when you begin tracking this item',50);
        make_select('unit',$inventoryUnitTypes[$unit],$inventoryUnitTypes,'Unit Type','Select a unit of count for this item');
        
        make_hidden('id',$roomid);
        make_submit('submit',$button);      
    } elseif($action=='delete') {
        $roomid=intval($_GET['id']);
        $sql="DELETE FROM monthly_inventory_items WHERE id=$roomid";
        $dbUpdate=dbexecutequery($sql);           
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM monthly_inventory_items";
        $dbRooms=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new Item</a>","Inventory Item",3);
        if ($dbRooms['numrows']>0)
        {
            foreach($dbRooms['data'] as $room)
            {
                $name=stripslashes($room['name']);
                $roomid=$room['id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&id=$roomid'>Edit</a></td>\n";
                print "<td><a href='?action=delete&id=$roomid' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbRooms);
        
    }
}

function save_room($action)
{
    $roomid=$_POST['id'];
    $name=addslashes($_POST['name']);
    $unit=addslashes($_POST['unit']);
    $starting=addslashes($_POST['starting']);
    
    if ($action=='insert')
    {
        $sql="INSERT INTO monthly_inventory_items (name, unit_type, starting_value) VALUES ('$name', '$unit', '$starting')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE monthly_inventory_items SET name='$name', unit_type='$unit', starting_value='$starting' WHERE id=$roomid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the inventory item','error');
    } else {
        setUserMessage('Inventory Item successfully saved','success');
    }
    redirect("?action=list");
    
}

footer();
?>
