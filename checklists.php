<?php
include("includes/mainmenu.php") ;

if ($_POST['submit']=='Add'){
    save_check('insert');
} elseif ($_POST['submit']=='Update'){
    save_check('update'); 
} else {
    show_checklist();
}

function show_checklist()
{
    global $siteID;
    $action=$_GET['action'];
    $cats=array("press"=>"Press","mailroom"=>"Mailroom");
    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Add';
            $value=0;
            $weight=99;
            $cat='press';
        } else {
            $id=$_GET['id'];
            $sql="SELECT * FROM checklist WHERE id=$id";
            $dbCheck=dbselectsingle($sql);
            $check=$dbCheck['data'];
            $name=stripslashes($check['checklist_item']);
            $weight=stripslashes($check['checklist_order']);
            $cat=stripslashes($check['category']);
            $button="Update";
        }
        print "<form method=post>\n";
        make_select('category',$cats[$cat],$cats,'Category','Which category does this belong to?');
        make_text('name',$name,'Checklist item','Short but description checklist item',50);
        make_number('weight',$weight,'Sort order','1=highest, higher numbers lower on the page');
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="DELETE FROM checklist WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        redirect('?action=list');
    } else {
        //list the privileges
        $sql="SELECT * FROM checklist WHERE site_id=$siteID ORDER BY checklist_order";
        $dbPermissions=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new checklist item</a>","Display name",3);
        if ($dbPermissions['numrows']>0)
        {
            foreach($dbPermissions['data'] as $permission)
            {
                $display=$permission['checklist_item'];
                $id=$permission['id'];
                print "<tr>";
                print "<td>$display</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</td>";
                print "<td><a class='delete' href='?action=delete&id=$id'>Delete</a></td>";
                print "</tr>\n";
            
            }
        }
        tableEnd($dbPermissions);
    }

}


function save_check($action)
{
    global $siteID;
    $name=addslashes($_POST['name']);
    $weight=addslashes($_POST['weight']);
    $id=$_POST['id'];
     $cat=addslashes($_POST['category']);
    
    if ($action=='insert')
    {
        $sql="INSERT INTO checklist (category, checklist_item, checklist_order, site_id) VALUES ('$cat', '$name', '$weight', '$siteID')";
        $db=dbinsertquery($sql);
        $error=$db['error'];
            
    } else {
        $sql="UPDATE checklist SET category='$cat', checklist_item='$name', checklist_order='$weight' WHERE id=$id";
        $db=dbexecutequery($sql);
        $error=$db['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the checklist item','error');
    } else {
        setUserMessage('Checklist item successfully saved','success');
    }
    redirect("?action=list");
    
}
footer();
?>
