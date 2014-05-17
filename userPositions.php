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
        case "Save Position":
        save_position('insert');
        break;
        
        case "Update Position":
        save_position('update');
        break;
        
        case "add":
        setup_position('add');
        break;
        
        case "edit":
        setup_position('edit');
        break;
        
        case "delete":
        setup_position('delete');
        break;
        
        case "list":
        setup_position('list');
        break;
        
        default:
        setup_position('list');
        break;
        
    } 
    
    
function setup_position($action)
{
    $id=intval($_GET['id']);
            
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Position";
            $position=0;
            $department=0;
            $director=0;
            $manager=0;
        } else {
            $button="Update Position";
            $sql="SELECT * FROM user_positions WHERE id=$id";
            $dbPosition=dbselectsingle($sql);
            $position=$dbPosition['data'];
            $name=$position['position_name'];
            $operator=$position['operator'];
            $manager=$position['manager'];
            $director=$position['director'];
        }
        print "<form method=post>\n";
        make_text('name',$name,'Position');
        make_checkbox('director',$director,'Director','Check if this is a director position');
        make_checkbox('manager',$manager,'Manager','Check if this is a management position');
        make_checkbox('operator',$operator,'Operator','Check if this is an operator position');
        make_hidden('positionid',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $sql="DELETE FROM user_positions WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the user position.<br>'.$error,'error');
        } else {
            setUserMessage('User position successfully deleted.','success');
        }
        redirect("?action=list");
    } else {
        global $siteID;
        $sql="SELECT * FROM user_positions WHERE site_id=$siteID ORDER BY position_name";
        $dbPositions=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new position</a>","Position Name",3);
        if ($dbPositions['numrows']>0)
        {
            foreach($dbPositions['data'] as $position)
            {
                $name=$position['position_name'];
                $id=$position['id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbPositions);
        
    }
}

function save_position($action)
{
    global $siteID;
    $id=$_POST['positionid'];
    $name=addslashes($_POST['name']);
    if ($_POST['director']){$director=1;}else{$director=0;}
    if ($_POST['manager']){$manager=1;}else{$manager=0;}
    if ($_POST['operator']){$operator=1;}else{$operator=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO user_positions (position_name, director, manager, operator, site_id)
         VALUES ('$name', '$director', '$manager', '$operator', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE user_positions SET position_name='$name', director='$director', manager='$manager', 
        operator='$operator' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
        {
            setUserMessage('There was a problem saving the position.<br>'.$error,'error');
        } else {
            setUserMessage('Position successfully saved.','success');
        }
    redirect("?action=list");
    
}

footer();
?>
