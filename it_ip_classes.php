<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;
    
if ($_POST['submit'])
{
    $action=$_POST['submit'];
} elseif ($_GET['action'])
{
    $action=$_GET['action'];
} else {
    $action='list';
}


switch ($action)
{
    case "addgroup":
    ipgroups('add');
    break;
    
    case "editgroup":
    ipgroups('edit');
    break;
    
    case "deletegroup":
    ipgroups('delete');
    break;
    
    case "list":
    ipgroups('list');
    break;
    
    case "Add Class":
    save_group('insert');
    break;
    
    case "Update Class":
    save_group('update');
    break;
}

 
function ipgroups($action)
{
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add Class";
            $groupactive=0;
        } else {
            $classid=intval($_GET['classid']);
            $sql="SELECT * FROM it_ip_classes WHERE id=$classid";
            $dbGroup=dbselectsingle($sql);
            $group=$dbGroup['data'];
            $name=$group['ip_name'];
            $lower=$group['ip_lower'];
            $upper=$group['ip_upper'];    
            $button="Update Class";
        }
        print "<form method=post>\n";
        make_text('name',$name,'Class Name','',50);
        make_text('lower',$lower,'Lower range','',50);
        make_text('upper',$upper,'Upper range','',50);
        make_hidden('classid',$classid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $classid=intval($_GET['classid']);
        $sql="DELETE FROM it_ip_classes WHERE id=$classid";
        $dbDelete=dbexecutequery($sql);
        if ($dbDelete['error']=='')
        {
            redirect("?action=listgroups");
        } else {
            print $dbDelete['error'];
        }
        
    } else {
        $sql="SELECT * FROM it_ip_classes";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=addgroup'>Add new IP class</a>","Class Name,Lower</th><th>Upper",6);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $id=$group['id'];
                $name=$group['ip_name'];
                $lower=$group['ip_lower'];
                $upper=$group['ip_upper'];
                print "<tr>";
                print "<td>$name</td>";
                print "<td>$lower</td>";
                print "<td>$upper</td>";
                print "<td><a href='?action=editgroup&classid=$id'>Edit</a></td>";
                print "<td><a href='?action=deletegroup&classid=$id' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbGroups);
        
    }
} 

function save_group($action)
{
    $classid=$_POST['classid'];
    $name=addslashes($_POST['name']);
    $lower=addslashes($_POST['lower']);
    $upper=addslashes($_POST['upper']);
    if($action=='insert')
    {
        $sql="INSERT INTO it_ip_classes (ip_name, ip_lower, ip_upper) VALUES 
        ('$name', '$lower', '$upper')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE it_ip_classes SET ip_name='$name', ip_lower='$lower', ip_upper='$upper'
         WHERE id=$classid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        print $error;
    } else {
        redirect("?action=list");
    }
}  

print "
</div>
</body>
</html>
";
dbclose();
?>