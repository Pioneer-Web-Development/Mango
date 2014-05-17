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
    case "add":
    software('add');
    break;
    
    case "edit":
    software('edit');
    break;
    
    case "delete":
    software('delete');
    break;
    
    case "list":
    software('list');
    break;
    
    case "Add":
    save_software('insert');
    break;
    
    case "Update":
    save_software('update');
    break;
}

 
function software($action)
{
    $platforms=array('pc'=>'PC','mac'=>"Mac",'linux'=>"Linux");
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add";
            $platform='pc';
            $count=0;
        } else {
            $id=intval($_GET['id']);
            $sql="SELECT * FROM it_software WHERE id=$id";
            $dbGroup=dbselectsingle($sql);
            $group=$dbGroup['data'];
            $name=stripslashes($group['software_name']);
            $version=stripslashes($group['software_version']);
            $platform=$group['software_platform'];
            $notes=stripslashes($group['software_notes']);    
            $license=stripslashes($group['software_license']);    
            $count=stripslashes($group['software_license_count']);    
            $button="Update";
        }
        print "<form method=post>\n";
        make_text('name',$name,'Software Name','',50);
        make_text('version',$version,'Version','',50);
        make_select('platform',$platforms[$platform],$platforms,'Platform','');
        make_text('license',$license,'License #','Site license number',50);
        make_number('count',$count,'License Count','How many licenses for this software?',10);
        make_textarea('notes',$notes,'Notes','Notes about the software',60,10);
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $classid=intval($_GET['id']);
        $sql="DELETE FROM it_software WHERE id=$classid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the software.<br />'.$error,'error');
        } else {
            setUserMessage('The software has been successfully deleted.','success');
        }
        redirect("?action=list");
        
    } else {
        $sql="SELECT * FROM it_software";
        $dbGroups=dbselectmulti($sql);
         tableStart("<a href='?action=add'>Add new software package</a>","Software Name,Version,Platform",6);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $groupid=$group['id'];
                $name=$group['software_name'];
                $version=$group['software_version'];
                $platform=$platforms[$group['software_platform']];
                print "<tr>";
                print "<td>$name</td>";
                print "<td>$version</td>";
                print "<td>$platform</td>";
                print "<td><a href='?action=edit&id=$groupid'>Edit</a></td>";
                print "<td><a href='?action=delete&id=$groupid' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbGroups);
        
    }
} 

function save_software($action)
{
    $id=$_POST['id'];
    $name=addslashes($_POST['name']);
    $version=addslashes($_POST['version']);
    $platform=addslashes($_POST['platform']);
    $notes=addslashes($_POST['notes']);
    $count=addslashes($_POST['count']);
    $license=addslashes($_POST['license']);
    if($action=='insert')
    {
        $sql="INSERT INTO it_software (software_name, software_version, software_platform, software_notes, software_license, software_license_count) VALUES 
        ('$name', '$version', '$platform', '$notes', '$license', '$count')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE it_software SET software_name='$name', software_version='$version',
        software_platform='$platform', software_notes='$notes', software_license='$license', software_license_count='$count'  WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the software.<br />'.$error,'error');
    } else {
        setUserMessage('The software has been successfully saved.','success');
    }
    redirect("?action=list");
    
}  
footer();
?>