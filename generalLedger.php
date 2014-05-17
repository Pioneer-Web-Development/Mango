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
    gl('add');
    break;
    
    case "edit":
    gl('edit');
    break;
    
    case "delete":
    gl('delete');
    break;
    
    case "list":
    gl('list');
    break;
    
    case "Save GL":
    save_gl('insert');
    break;
    
    case "Update GL":
    save_gl('update');
    break;
    
    default:
    gl('list');
    break;
    
}

function gl($action)
{
    global $siteID;
    $glid=intval($_GET['glid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Save GL';
        } else {
            $button='Update GL';
            $sql="SELECT * FROM general_ledgers WHERE id=$glid";
            $dbGL=dbselectsingle($sql);
            $gl=$dbGL['data'];
            $glnumber=$gl['gl_number'];
            $gldescription=$gl['gl_description'];
       }
        print "<form method=post>\n";
        make_text('glnumber',$glnumber,'GL #','',10);
        make_text('gldescription',$gldescription,'Description','',40);
        make_hidden('glid',$glid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $sql="DELETE FROM general_ledgers WHERE id=$glid";
        $dbDelete=dbexecutequery($sql);
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the GL number.','error');
        } else {
            setUserMessage('GL number successfully saved','success');
        }
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM general_ledgers WHERE site_id=$siteID ORDER BY gl_number";
        $dbGls=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new GL #</a>","GL Number,Description",4);
        if ($dbGls['numrows']>0)
        {
            foreach ($dbGls['data'] as $gl)
            {
                $id=$gl['id'];
                $number=$gl['gl_number'];
                $name=$gl['gl_description'];
                print "<tr><td>$number</td>";
                print "<td>$name</td>";
                print "<td><a href='?action=edit&glid=$id'>Edit</a></td>";
                print "<td><a href='?action=delete&glid=$id' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbGls);
    }
    
}




function save_gl($action)
{
    $siteID=$GLOBALS['siteID'];
    $glid=$_POST['glid'];
    $number=addslashes($_POST['glnumber']);
    $description=addslashes($_POST['gldescription']);
    if ($action=='insert')
    {
        $sql="INSERT INTO general_ledgers (gl_number, gl_description, site_id) VALUES ('$number','$description', $siteID)";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE general_ledgers SET gl_number='$number', gl_description='$description' WHERE id=$glid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the GL number.','error');
    } else {
        setUserMessage('GL # successfully saved','success');
    }
    redirect("?action=list");                          
   
}

footer();
?>
