<?php
//<!--VERSION: .9 **||**-->
//types of special sections
include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submitbutton'];
} else {
    $action=$_GET['action'];
}
    switch ($action)
    {
        case "Save Type":
        save_type('insert');
        break;
        
        case "Update Type":
        save_type('update');
        break;
        
        case "add":
        setup_types('add');
        break;
        
        case "edit":
        setup_types('edit');
        break;
        
        case "delete":
        setup_types('delete');
        break;
        
        case "list":
        setup_types('list');
        break;
        
        default:
        setup_types('list');
        break;
        
    } 
    
    
function setup_types($action)
{
    global $siteID;
    $id=intval($_GET['id']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Type";
            
        } else {
            $button="Update Type";
            $sql="SELECT * FROM special_section_types WHERE id=$id";
            $dbType=dbselectsingle($sql);
            $type=$dbType['data'];
            $name=$type['product_name'];
        }
        print "<form method=post>\n";
        make_text('name',$name,'Product Type Name','',20);
        make_submit('submitbutton',$button);
        make_hidden('typeid',$id);
        print "</form>\n";  
    } elseif($action=='delete') {
        $sql="DELETE FROM special_section_types WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM special_section_types WHERE site_id=$siteID ORDER BY product_name";
        $dbTypes=dbselectmulti($sql);
        tableStart("<a href='?&action=add'>Add new product type</a>","Product Type",3);
        if ($dbTypes['numrows']>0)
        {
            foreach($dbTypes['data'] as $type)
            {
                $name=$type['product_name'];
                $id=$type['id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbTypes);
        
    }
}

function save_type($action)
{
    global $siteID;
    $id=$_POST['typeid'];
    $name=addslashes($_POST['name']);
    if ($action=='insert')
    {
        $sql="INSERT INTO special_section_types (product_name, site_id) VALUES ('$name', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE special_section_types SET product_name='$name'  WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!="")
    {
        print $error;
    } else {
        redirect("?action=list");
    }
}

footer();
?>
