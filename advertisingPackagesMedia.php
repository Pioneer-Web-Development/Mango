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
    media('add');
    break;
    
    case "edit":
    media('edit');
    break;
    
    case "delete":
    media('delete');
    break;
    
    case "Save":
    save_media('insert');
    break;
    
    case "Update":
    save_media('update');
    break;

    default:
    media('list');
    break;
}


function media($action)
{
    $id=intval($_GET['id']);
    $packageid=intval($_GET['packageid']);
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
            $sql="SELECT * FROM adv_packages_media WHERE id=$id";
            $dbPackage=dbselectsingle($sql);
            $package=$dbPackage['data'];
            $name=stripslashes($package['media_title']);
            $description=stripslashes($package['media_description']);
            $filename=stripslashes($package['media_filename']);
        }
        print "<form method=post>\n";
        make_text('title',$name,'Title');
        make_textarea('description',$description,'Description','',80,20);
        make_file('mediafile','Media File','','artwork/advertising/mediafiles/'.$packageid.'/'.stripslashes($filename));
        make_submit('submit',$button);
        make_hidden('id',$id);
        make_hidden('packageid',$packageid);
        print "</form>\n";
    }elseif ($action=='delete')
    {
        $sql="SELECT * FROM adv_packages_media WHERE id=$id";
        $dbMedia=dbselectsingle($sql);
        $filename=stripslashes($dbMedia['data']['media_filename']);
        if(unlink('artwork/advertising/mediafiles/'.$packageid.'/'.$filename))
        {
            $sql="DELETE FROM adv_packages_media WHERE id=$id";
            $dbDelete=dbexecutequery($sql);
            $error=$dbDelete['error'];
            if ($error!='')
            {
                setUserMessage('There was a problem deleting the package.<br />'.$error,'error');
            } else {
                setUserMessage('The package was deleted successfully.','success');
            }
        } else {
            setUserMessage('There was a problem removing the media file '.$filename.'from the server.','error');
        }
        
    
        redirect("?action=list&packageid=$packageid");
    } else {
        //show all the pubs
       $sql="SELECT * FROM adv_packages WHERE package_id=$packageid ORDER BY media_title";
       $dbBenchmarks=dbselectmulti($sql);
       tableStart("<a href='?action=add&packageid=$packageid'>Add Media File</a>,<a href='advertisingPackages.php?action=list'>Return to Package list</a>","Media Title",3);
       if ($dbBenchmarks['numrows']>0)
       {
            foreach($dbBenchmarks['data'] as $benchmark)
            {
                $id=$benchmark['id'];
                $name=stripslashes($benchmark['media_title']);
                print "<tr><td>$name</td>\n";
                print "<td><a href='?action=edit&id=$id&packageid=$packageid'>Edit</a</td>\n";
                print "<td><a class='delete' href='?action=delete&id=$id&packageid=$packageid'>Delete</a</td>\n";
                print "</tr>\n";
            }
       }
       tableEnd($dbBenchmarks);
    }


}



function save_media($action)
{
    $id=$_POST['id'];
    $packageid=$_POST['packageid'];
    $name=addslashes($_POST['name']);
    $desc=addslashes($_POST['description']);
    if ($action=='insert')
    {
        $sql="INSERT INTO adv_packages_media (package_id, media_title, media_description)
         VALUES ('$packageid', '$name', '$desc')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $packageid=$dbInsert['insertid'];
    } else {
        $sql="UPDATE adv_packages_media SET media_title='$name', media_description='$desc' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    if(isset($_FILES)) { //means we have browsed for a valid file
        // check to make sure files were uploaded
        foreach($_FILES as $file) {
            switch($file['error']) {
                case 0: // file found
                    if ($packageid!=0) {
                        $filename=$file['name'];
                        //check for folder, if not present, create it
                        if(!file_exists("artwork/advertising/"))
                        {
                            mkdir("artwork/advertising/");
                        }
                        if(!file_exists("artwork/advertising/mediafiles"))
                        {
                            mkdir("artwork/advertising/mediafiles");
                        }
                        if(!file_exists("artwork/advertising/mediafiles/".$packageid))
                        {
                            mkdir("artwork/advertising/mediafiles/".$packageid);
                        }
                        $filetype=$file['type'];
                        if(processFile($file,"artwork/advertising/mediafiles/".$packageid.'/',$filename) == true) {
                            $filename=addslashes($filename);
                            $sql="UPDATE it_device_files SET media_filename='$filename', media_type='$filetype' WHERE id=$packageid";
                            $result=dbexecutequery($sql);
                            $error.=$result['error'];
                        } else {
                           $error.= 'There was an error processing the file: '.$file['name'];  
                        }
                    } else {
                        $error.= 'There was an error because the main record insertion failed.';
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
    if ($error!='')
    {
        setUserMessage('There was a problem saving the media file.<br />'.$error,'error');
    } else {
        setUserMessage('The media file was successfully saved','success');
    }
    redirect("?action=list&packageid=$packageid");  
}

footer();
?>

