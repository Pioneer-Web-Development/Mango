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
    
    case "add":
    packages('add');
    break;
    
    case "edit":
    packages('edit');
    break;
    
    case "delete":
    packages('delete');
    break;
    
    case "Save":
    save_package('insert');
    break;
    
    case "Update":
    save_package('update');
    break;

    default:
    packages('list');
    break;
}


function packages($action)
{
    $id=intval($_GET['id']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save";
            $active=1;
            $start=date("Y-m-d");
            $stop=date("Y-m-d",strtotime("+1 month"));
        } else {
            $button="Update";
            $sql="SELECT * FROM adv_packages WHERE id=$id";
            $dbPackage=dbselectsingle($sql);
            $package=$dbPackage['data'];
            $name=stripslashes($package['package_name']);
            $description=stripslashes($package['package_description']);
            $start=stripslashes($package['package_start']);
            $stop=stripslashes($package['package_stop']);
            $active=stripslashes($package['package_active']);
            
        }
        print "<form method=post>\n";
        make_text('name',$name,'Package Name');
        make_checkbox('active',$active,'Active','Check if this package is active');
        make_date('start',$start,'Start Date','When does this package become available?');
        make_date('stop',$stop,'Stop Date','When does this package become end?');
        make_textarea('description',$description,'Description','',80,20);
        make_submit('submit',$button);
        make_hidden('id',$id);
        print "</form>\n";
    }elseif ($action=='delete')
    {
        $sql="UPDATE adv_packages SET package_deleted=1 WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        
        /*
        * @TODO need to clean up other package details - but we aren't actually deleting the package, just hiding it.
        */
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the package.<br />'.$error,'error');
        } else {
            setUserMessage('The package was deleted successfully.','success');
        }
    
        redirect("?action=list");
    } else {
        //show all the pubs
       $sql="SELECT * FROM adv_packages ORDER BY package_start";
       $dbBenchmarks=dbselectmulti($sql);
       tableStart("<a href='?action=add'>Add Package</a>","Package Name,Start,Stop",7);
       if ($dbBenchmarks['numrows']>0)
       {
            foreach($dbBenchmarks['data'] as $benchmark)
            {
                $id=$benchmark['id'];
                $name=stripslashes($benchmark['package_name']);
                $start=date("m/d/Y",strtotime($benchmark['package_start']));
                $stop=date("m/d/Y",strtotime($benchmark['package_stop']));
                print "<tr><td>$name</td><td>$start</td><td>$stop</td>\n";
                print "<td><a href='?action=edit&id=$id'>Edit</a</td>\n";
                print "<td><a href='advertisingPackagesComponents.php?action=list&packageid=$id'>Components</a</td>\n";
                print "<td><a href='?action=list&packageid=$id'>Media</a</td>\n";
                print "<td><a class='delete' href='?action=delete&id=$id'>Delete</a</td>\n";
                print "</tr>\n";
            }
       }
       tableEnd($dbBenchmarks);
    }


}



function save_package($action)
{
    $id=$_POST['id'];
    $name=addslashes($_POST['name']);
    $desc=addslashes($_POST['description']);
    $start=addslashes($_POST['start']);
    $stop=addslashes($_POST['stop']);
    if ($_POST['active']){$active=1;}else{$active=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO adv_packages (package_name, package_description, package_active, package_start, package_stop, package_deleted)
         VALUES ('$name', '$desc', '$active', '$start', '$stop', 0)";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE adv_packages SET package_name='$name', package_description='$desc', package_active='$active', package_start='$start', package_stop='$stop' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the package.<br />'.$error,'error');
    } else {
        setUserMessage('The package was successfully saved','success');
    }
    redirect("?action=list");  
}

footer();
?>

