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
    icons('add');
    break;
    
    case "edit":
    icons('edit');
    break;
    
    case "delete":
    icons('delete');
    break;
    
    case "list":
    icons('list');
    break;
    
    
    case "Add Icon":
    save_icon('insert');
    break;
    
    case "Update Icon":
    save_icon('update');
    break;
}

 
function icons($action)
{
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add Icon";
            $rack=0;
        } else {
            $iconid=intval($_GET['iconid']);
            $sql="SELECT * FROM it_floorplan_icons WHERE id=$iconid";
            $dbGroup=dbselectsingle($sql);
            $group=$dbGroup['data'];
            $name=stripslashes($group['icon_name']);
            $rack=$group['rack_icon'];
            $image=$group['icon_image'];
            $button="Update Icon";
        }
        print "<form method=post enctype='multipart/form-data'>\n";
        make_text('name',$name,'Icon Name','',50);
        make_checkbox('rack',$rack,'Rack','Check if this icon is for racks');
        make_file('image','Icon','Icon Image','artwork/iticons/'.$image);
        
        make_hidden('iconid',$iconid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $iconid=intval($_GET['iconid']);
        $sql="DELETE FROM it_floorplan_icons WHERE id=$iconid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if($error=='')
        {
            setUserMessage("The icon been saved deleted.",'success');
        } else {
            setUserMessage("There was a problem deleting the icon.<br>$error",'error');
        }
    } else {
        $sql="SELECT * FROM it_floorplan_icons";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new icon</a>","Icon Name",3);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $id=$group['id'];
                $name=stripslashes($group['icon_name']);
                print "<tr>";
                print "<td>$name</td>";
                print "<td><a href='?action=edit&iconid=$id'>Edit</a></td>";
                print "<td><a href='?action=delete&iconid=$id' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbGroups);
        
    }
} 
 
function save_icon($action)
{
    $iconid=$_POST['iconid'];
    $name=addslashes($_POST['name']);
    if($_POST['rack']){$rack=1;}else{$rack=0;}
    if($action=='insert')
    {
        $sql="INSERT INTO it_floorplan_icons (icon_name, rack_icon) VALUES ('$name', '$rack')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $iconid=$dbInsert['insertid'];
    } else {
        $sql="UPDATE it_floorplan_icons SET icon_name='$name', rack_icon='$rack' WHERE id=$iconid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    
   if(isset($_FILES)) { //means we have browsed for a valid file
    // check to make sure files were uploaded
    foreach($_FILES as $file) {
        switch($file['error']) {
            case 0: // file found
            if($file['name'] != NULL && okFileType($file['type'],'image',$file['name']) != false)  {
                //get the new name of the file
                //to do that, we need to push it into the database, and return the last record ID
                if ($iconid!=0) {
                    $ext=end(explode(".",$file['name']));
                    $filename='icon_'.$iconid.'.'.$ext;
                    //check for folder, if not present, create it
                    if(processFile($file,"artwork/iticons/",$filename) == true) {
                        $sql="UPDATE it_floorplan_icons SET icon_image='$filename' WHERE id=$iconid";
                        $result=dbexecutequery($sql);
                    } else {
                       $error.= 'There was an error processing the file: '.$file['name'];  
                    }
                } else {
                    $error.= 'There was an error because the main record insertion failed.';
                }
            }
            break;

            case (1|2):  // upload too large
            $error.= 'file upload is too large for '.$file['name'];
            break;

            case 4:  // no file uploaded
            break;

            case (6|7):  // no temp folder or failed write - server config errors
            $error.= 'internal error - flog the webmaster on '.$file['name'];
            break;
        }
    }
 } 
    if($error=='')
    {
        setUserMessage("The icon has been saved successfully.",'success');
    } else {
        setUserMessage("There was a problem saving the icon.<br>$error",'error');
    }
    redirect("?action=list");
}  

footer();
?>