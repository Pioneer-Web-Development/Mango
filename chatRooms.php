<?php
include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
    switch ($action)
    {
        case "Save Room":
        save_room('insert');
        break;
        
        case "Update Room":
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
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Room";
        } else {
            $button="Update Room";
            $roomid=intval($_GET['roomid']);
            $sql="SELECT * FROM chat_rooms WHERE id=$roomid";
            $dbRoom=dbselectsingle($sql);
            $room=$dbRoom['data'];
            $default=stripslashes($room['default_room']);
            $name=stripslashes($room['room_name']);
        }
        print "<form method=post>\n";
        make_text('name',$name,'Name','Name of chat room',50);
        make_checkbox('default',$default,'Default','Check if this is the default room');
        make_hidden('roomid',$roomid);
        make_submit('submit',$button);
        print "</form>\n";      
    } elseif($action=='delete') {
        $roomid=intval($_GET['roomid']);
        $sql="DELETE FROM chat_rooms WHERE id=$roomid";
        $dbUpdate=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM chat_rooms";
        $dbRooms=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new room</a>","Room Name",3);
        if ($dbRooms['numrows']>0)
        {
            foreach($dbRooms['data'] as $room)
            {
                $name=stripslashes($room['room_name']);
                $roomid=$room['id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&roomid=$roomid'>Edit</a></td>\n";
                print "<td><a href='?action=delete&roomid=$roomid' class='delete'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbRooms);
        
    }
}

function save_room($action)
{
    $roomid=$_POST['roomid'];
    $name=addslashes($_POST['name']);
    if ($_POST['default']){$default=1;}else{$default=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO chat_rooms (room_name, default_room) VALUES ('$name', '$default')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE chat_rooms SET room_name='$name', default_room='$default' WHERE id=$roomid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the chat room.','error');
    } else {
        setUserMessage('Chat room successfully saved','success');
    }
    redirect("?action=list");
    
}

footer();