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
        case "move":
        changeParent('add');
        break;
        
        case "Move Item":
        save_move();
        break;
        
        case "Save Location":
        save_location('insert');
        break;
        
        case "Update Location":
        save_location('update');
        break;
        
        case "add":
        setup_location('add');
        break;
        
        case "edit":
        setup_location('edit');
        break;
        
        case "delete":
        setup_location('delete');
        break;
        
        case "list":
        setup_location('list');
        break;
        
        default:
        setup_location('list');
        break;
        
    } 
    
    
function setup_location($action)
{
    global $siteID;
    $locationid=intval($_GET['locationid']);
    $parentid=intval($_GET['parentid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Location";
            $order=1;
        } else {
            $button="Update Location";
            $sql="SELECT * FROM insert_storage_locations WHERE id=$locationid";
            $dbLocations=dbselectsingle($sql);
            $location=$dbLocations['data'];
            $name=$location['location_name'];
            $order=$location['location_order'];
        }
        print "<form method=post>\n";
        make_text('location_name',$name,'Location Name');
        make_number('location_order',$order,'Location Order');
        make_hidden('locationid',$locationid);
        make_hidden('parentid',$parentid);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $sql="DELETE FROM insert_storage_locations WHERE id=$locationid";
        $dbUpdate=dbexecutequery($sql);
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the storage location.<br />'.$error,'error');
        } else {
            setUserMessage('The storage location has been successfully deleted.','success');
        }
        redirect("?action=list&parentid=$parentid");
    } else {
        if ($_GET['parentid'])
        {
            $parentid=intva($_GET['parentid']);
        } else {
            $parentid=0;
        }
        $temp=buildInsertLocations();
        make_select('sample',$temp[0],$temp,'This is a sample');
        $sql="SELECT * FROM insert_storage_locations WHERE parent_id=$parentid ORDER BY location_order";
        $dbLocations=dbselectmulti($sql);
        tableStart("<a href='?action=list'>Return to top level</a>,<a href='?action=add&parentid=$parentid'>Add new location</a>","Name",5);
        if ($dbLocations['numrows']>0)
        {
            foreach($dbLocations['data'] as $location)
            {
                $name=$location['location_name'];
                $locationid=$location['id'];
                $parentid=$location['parent_id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&locationid=$locationid&parentid=$parentid'>Edit</a></td>\n";
                print "<td><a href='?action=list&parentid=$locationid'>Subs</a></td>\n";
                print "<td><a href='?action=move&locationid=$locationid&parentid=$parentid'>Move to new parent</a></td>\n";
                print "<td><a href='?action=delete&locationid=$locationid&parentid=$parentid' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbLocations);
        
    }
}

function save_location($action)
{
    global $siteID;
    $locationid=$_POST['locationid'];
    $parentid=$_POST['parentid'];
    $name=addslashes($_POST['location_name']);
    $order=addslashes($_POST['location_order']);
    if ($action=='insert')
    {
        $sql="INSERT INTO insert_storage_locations (location_name, location_order, parent_id, site_id) VALUES ('$name', '$order', '$parentid', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE insert_storage_locations SET location_name='$name', location_order='$order' WHERE id=$locationid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the storage location.<br />'.$error,'error');
    } else {
        setUserMessage('The storage location has been successfully saved.','success');
    }
    redirect("?action=list&parentid=$parentid");
    
}



function changeParent()
{
    global $siteID;
    
    $parentid=$_GET['parentid'];
    $locationid=$_GET['locationid'];
    $sql="SELECT id, location_name FROM insert_storage_locations WHERE parent_id=0 AND site_id=$siteID ORDER BY location_order";
    $dbParents=dbselectmulti($sql);
    $parents[0]="TOP LEVEL";
    if ($dbParents['numrows']>0)
    {
        foreach($dbParents['data'] as $p)
        {
            $parents[$p['id']]=$p['location_name'];    
        }
    }
    print "<form method=post>\n";
    make_select('newparent',$parents[$parentid],$parents,'New parent','Select the new parent for this item');
    make_hidden('parentid',$parentid);
    make_hidden('locationid',$locationid);
    make_submit('submit','Move Item');
    print "</form>\n";    
}

function save_move()
{
    $parentid=$_POST['parentid'];
    $locationid=$_POST['locationid'];
    $newparent=$_POST['newparent'];
    $sql="UPDATE insert_storage_locations SET parent_id=$newparent WHERE id=$locationid";
    $dbUpdate=dbexecutequery($sql);
    if ($error!='')
    {
        setUserMessage('There was a problem updating the location.<br />'.$error,'error');
    } else {
        setUserMessage('The location has been successfully updated.','success');
    }
    redirect("?action=list&parentid=$newparent");
    
}


footer();
?>
