<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;
 
    
if ($_POST['submit'])
{
    $action=$_POST['submit'];
} elseif ($_GET['action'])
{
    $action=$_GET['action'];
}


switch ($action)
{
    case "add":
    workers('add');
    break;
    
    case "edit":
    workers('edit');
    break;
    
    case "delete":
    workers('delete');
    break;
    
    case "Add":
    save_worker('insert');
    break;
    
    case "Update":
    save_worker('update');
    break;
    
    default:
    workers('list');
    break;
}

 
function workers($action)
{
    global $siteID;
    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add";
        } else {
            $id=intval($_GET['id']);
            $sql="SELECT * FROM temp_agencies WHERE id=$id";
            $dbGroup=dbselectsingle($sql);
            $group=$dbGroup['data'];
            $agency=$group['agency'];
            $button="Update";
        }
        print "<form method=post>\n";
        make_text('agency',$agency,'Agency','',50);
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="DELETE FROM temp_agencies WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM temp_agencies ORDER BY agency";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new agency</a>","Agency",3);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $id=$group['id'];
                $agency=$group['agency'];
                print "<tr>";
                print "<td>$agency</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>";
                print "<td><a class='delete' href='?action=delete&id=$gid'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbGroups);
        
    }
} 

function save_worker($action)
{
    global $siteID;
    $id=$_POST['id'];
    $agency=addslashes($_POST['agency']);
    
    if($action=='insert')
    {
        $sql="INSERT INTO temp_agencies (agency) VALUES 
        ('$agency')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE temp_workers SET agency='$agency' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the agency','error');
    } else {
        setUserMessage('Agency successfully saved','success');
    }
    redirect("?action=list");    
}
  
footer();
?>