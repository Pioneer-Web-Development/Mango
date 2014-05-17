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
        case "Save Size":
        save_size('insert');
        break;
        
        case "Update Size":
        save_size('update');
        break;
        
        case "add":
        setup_sizes('add');
        break;
        
        case "edit":
        setup_sizes('edit');
        break;
        
        case "delete":
        setup_sizes('delete');
        break;
        
        case "list":
        setup_sizes('list');
        break;
        
        default:
        setup_sizes('list');
        break;
        
    } 
    
    
function setup_sizes($action)
{
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Size";
        } else {
            $button="Update Size";
            $sizeid=intval($_GET['sizeid']);
            $sql="SELECT * FROM paper_sizes WHERE id=$sizeid";
            $dbSize=dbselectsingle($sql);
            $size=$dbSize['data'];
            $size_name=stripslashes($size['width']);
            $display=stripslashes($size['display']);
        }
        print "<form action=\"$_SERVER[PHP_SELF]\" method=post>\n";
        
        print "<div class=\"label\">Size Name</div>\n";
        print "<div class=\"input\"><small>Please enter in inches</small><br>";
        print input_text('size',$size_name,'50',false,'','','','return isNumberKey(event);');
        print "</div>\n";
        print "<div class=\"clear\"></div>\n";
        make_checkbox('display',$display,'Display','Allow this option to be chosen');
       print "<input type=\"hidden\" name=\"sizeid\" value=\"$sizeid\" />\n";
        print "<input type=submit name=submit value=\"$button\" />\n";
        print "</form>\n";  
    } elseif($action=='delete') {
        $sizeid=intval($_GET['sizeid']);
        $sql="UPDATE paper_sizes SET status=99 WHERE id=$sizeid";
        $dbUpdate=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM paper_sizes WHERE status=1 ORDER BY width ASC";
        $dbSizes=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new paper size</a>","Size",3);
        if ($dbSizes['numrows']>0)
        {
            foreach($dbSizes['data'] as $paper)
            {
                $name=$paper['width']."\"";
                $sizeid=$paper['id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&sizeid=$sizeid'>Edit</a></td>\n";
                print "<td><a href='?action=delete&sizeid=$sizeid' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbSizes);
        
    }
}

function save_size($action)
{
    $sizeid=$_POST['sizeid'];
    $size=addslashes($_POST['size']);
    if ($_POST['display']){$display=1;}else{$display=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO paper_sizes (width, status, display) VALUES ('$size', 1, '$display')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE paper_sizes SET width='$size', display='$display' WHERE id=$sizeid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the size','error');
    } else {
        setUserMessage('Paper size successfully saved','success');
    }
    redirect("?action=list");
}

footer();
?>
