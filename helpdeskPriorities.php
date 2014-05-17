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
        case "Save Priority":
        save_priority('insert');
        break;
        
        case "Update Priority":
        save_priority('update');
        break;
        
        case "add":
        setup_priority('add');
        break;
        
        case "edit":
        setup_priority('edit');
        break;
        
        case "delete":
        setup_priority('delete');
        break;
        
        case "list":
        setup_priority('list');
        break;
        
        default:
        setup_priority('list');
        break;
        
    } 
    
    
function setup_priority($action)
{
    global $siteID;
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Priority";
        } else {
            $button="Update Priority";
            $id=$_GET['id'];
            $sql="SELECT * FROM helpdesk_priorities WHERE id=$id";
            $dbPriority=dbselectsingle($sql);
            $priority=$dbPriority['data'];
            $name=$priority['priority_name'];
            $order=$priority['priority_order'];
            $threshold=$priority['priority_threshold'];
        }
        print "<form method=post>\n";
        make_text('priority_name',$name,'Priority Name');
        make_slider('priority_order',$order,'Priority Order','Set priority order',1,99,1);
        make_number('priority_threshold',$threshold,'Priority Threshold','How many hours before it escalates to the next higheset priority.');
        make_hidden('priorityid',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=intval($_GET['id']);
        $sql="DELETE FROM helpdesk_priorities WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the priority.<br />'.$error,'error');
        } else {
            setUserMessage('The priority has been successfully deleted.','success');
        }
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM helpdesk_priorities WHERE site_id=$siteID ORDER BY priority_order ASC";
        $dbPriorities=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new priority stage</a>","Order,Name,Escalation Threshold",5);
        if ($dbPriorities['numrows']>0)
        {
            foreach($dbPriorities['data'] as $priority)
            {
                $name=$priority['priority_name'];
                $order=$priority['priority_order'];
                $threshold=$priority['priority_threshold'];
                $id=$priority['id'];
                print "<tr><td>$order</td><td>$name</td><td>$threshold</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=delete&id=$id''>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbPriorities);
        
    }
}

function save_priority($action)
{
    global $siteID;
    $id=$_POST['priorityid'];
    $name=addslashes($_POST['priority_name']);
    $order=addslashes($_POST['priority_order']);
    $threshold=addslashes($_POST['priority_threshold']);
    if ($action=='insert')
    {
        $sql="INSERT INTO helpdesk_priorities (priority_name, priority_order, priority_threshold, site_id) VALUES ('$name', '$order', '$threshold', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE helpdesk_priorities SET priority_name='$name', priority_order='$order', priority_threshold='$threshold' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the priority.<br />'.$error,'error');
    } else {
        setUserMessage('The priority has been successfully saved.','success');
    }
    redirect("?action=list");
    
}



footer();
?>
