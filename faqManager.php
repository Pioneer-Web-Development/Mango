<?php
//<!--VERSION: .9 **||**-->

  //this is the faq setup system
  //pretty basic, just nested

include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}

switch ($action)
{
    case "addfaq":
    faq('add');
    break;
    
    case "editfaq":
    faq('edit');
    break;
    
    case "delete":
    faq('delete');
    break;
    
    case "Save Faq":
    save_faq('insert');
    break;
    
    case "Update Faq":
    save_faq('update');
    break;
    
    case "addstep":
    faqstep('add');
    break;
    
    case "editstep":
    faqstep('edit');
    break;
    
    case "deletestep":
    faqstep('delete');
    break;
    
    case "liststep":
    faqstep('list');
    break;
    
    case "Save Step":
    save_step('insert');
    break;
    
    case "Update Step":
    save_step('update');
    break;
    
    case "addimage":
    faqimage('add');
    break;
    
    case "editimage":
    faqimage('edit');
    break;
    
    case "deleteimage":
    faqimage('delete');
    break;
    
    case "listimage":
    faqimage('list');
    break;
    
    case "Save Image":
    save_image('insert');
    break;
    
    case "Update Image":
    save_image('update');
    break;
    
    default:
    faq('list');
    break;
}  
  
function faq($action)
{
    $parentid=intval($_GET['parentid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Faq";
            $sql="SELECT max(weight) as maxweight FROM faq WHERE parentid=$parentid";
            $dbMax=dbselectsingle($sql);
            $weight=$dbMax['data']['maxweight']+1;
        } else {
            $button="Update Faq";
            $faqid=intval($_GET['faqid']);
            $sql="SELECT * FROM faq WHERE id=$faqid";
            $dbFaq=dbselectsingle($sql);
            $faq=$dbFaq['data'];
            $keywords=stripslashes($faq['keywords']);
            $title=stripslashes($faq['title']);
            $weight=$faq['weight'];
            $faqtext=stripslashes($faq['faq_text']);
        }
        print "<form method=post>\n";
        make_slider('weight',$weight,'Order','Sort order for the FAQ item');
        make_text('title',$title,'Title','If this is meant to be a container of sub-faqs, then this is all you need');
        make_text('keywords',$keywords,'Keywords','',50);
        make_textarea('faqtext',$faqtext,'Faq','',80,30);
        make_hidden("parentid",$parentid);
        make_hidden('faqid',$faqid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        //this is only available at the end of a chain, so no worries about children elements
        $faqid=intval($_GET['faqid']);
        $parentid=intval($_GET['parentid']);
        $sql="DELETE FROM faq WHERE id=$faqid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleted the FAQ.<br>'.$error,'error');
        } else {
            setUserMessage('FAQ item was deleted successfully.','success');
        }
        redirect("?action=list&parentid=$parentid");
    } else {
        if ($_GET['parentid'])
        {
            $parentid=intval($_GET['parentid']);
        } else {
            $parentid=0;
        }
        $sql="SELECT * FROM faq WHERE parentid=$parentid ORDER BY weight";
        $dbFaqs=dbselectmulti($sql);
        tableStart("<a href='?action=addfaq&parentid=$parentid'>Add new faq or sub-category</a>,<a href='?action=list&parentid=$parentid'>Return to FAQ list</a>,<a href='?action=list'>Return to FAQ root</a>","Order,Title",6);
        if ($dbFaqs['numrows']>0)
        {
            foreach ($dbFaqs['data'] as $faq)
            {
                //check for a sub. if it exists then we're not allowing delete, just list
                $sql="SELECT * FROM faq WHERE parentid=$faq[id]";
                $dbSub=dbselectmulti($sql);
                if ($dbSub['numrows']>0)
                {
                    $act="<td><a href='?action=list&parentid=$faq[id]'>List Subs</a></td>";
                } else {
                    $act="<td><a href='?action=delete&parentid=$parentid&faqid=$faq[id]' class='delete'>Delete</a></td>";
                }
                $faqid=$faq['id'];
                $title=$faq['title'];
                print "<tr>";
                print "<td>$faq[weight]</td>";
                print "<td>$title</td>";
                print "<td><a href='?action=editfaq&parentid=$parentid&faqid=$faqid'>Edit</a></td>";
                print "<td><a href='?action=list&parentid=$faqid'>Subs</a></td>";
                print "<td><a href='?action=liststep&faqid=$faqid&parentid=$parentid'>Steps</a></td>";
                print $act;
                print "</tr>\n";
            }
        }
        tableEnd($dbFaqs);
    }   
}  

function save_faq($action)
{
    $parentid=$_POST['parentid'];
    $faqid=$_POST['faqid'];
    $weight=$_POST['weight'];
    $title=addslashes($_POST['title']);
    $faqtext=addslashes($_POST['faqtext']);
    $keywords=addslashes($_POST['keywords']);
    $keywords=str_replace(";"," ",$keywords);
    $keywords=str_replace(","," ",$keywords);
    $keywords=str_replace("  "," ",$keywords);
    if ($action=='insert')
    {
       $sql="INSERT INTO faq (parentid,title,keywords,faq_text,weight) VALUES 
       ('$parentid', '$title', '$keywords', '$faqtext', '$weight')";
       $dbInsert=dbinsertquery($sql);
       $error=$dbInsert['error']; 
    } else {
       $sql="UPDATE faq SET title='$title', keywords='$keywords', faq_text='$faqtext', 
       weight='$weight' WHERE id=$faqid";
       $dbUpdate=dbexecutequery($sql);
       $error=$dbUpdated['error']; 
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the FAQ.<br>'.$error,'error');
    } else {
        setUserMessage('FAQ item was saved successfully.','success');
    }
    redirect("?action=list&parentid=$parentid");
}


function faqstep($action)
{
    $faqid=intval($_GET['faqid']);
    $parentid=intval($_GET['parentid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Step";
            $sql="SELECT max(step_order) as maxweight FROM faq_steps WHERE faq_id=$faqid";
            $dbMax=dbselectsingle($sql);
            $weight=$dbMax['data']['maxweight']+1;
        } else {
            $button="Update Step";
            $stepid=intval($_GET['stepid']);
            $sql="SELECT * FROM faq_steps WHERE id=$stepid";
            $dbFaq=dbselectsingle($sql);
            $faq=$dbFaq['data'];
            $title=stripslashes($faq['step_title']);
            $text=stripslashes($faq['step_text']);
            $weight=$faq['step_order']; 
        }
        print "<form method=post>\n";
        make_slider('weight',$weight,'Order','Sort order for the FAQ step');
        make_text('title',$title,'Title','Step Title');
        make_textarea('steptext',$text,'Step Description','',80,20);
        make_hidden('stepid',$stepid);
        make_hidden('faqid',$faqid);
        make_hidden('parentid',$parentid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        //this is only available at the end of a chain, so no worries about children elements
        $stepid=intval($_GET['stepid']);
        $sql="DELETE FROM faq_steps WHERE id=$stepid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        
        $sql="SELECT * FROM faq_step_images WHERE faq_id=$faqid";
        $dbImage=dbselectmulti($sql);
        if($dbImage['numrows']>0)
        {
            foreach($dbImage['data'] as $image)
            {
                $imageid=$image['id'];
                if(unlink('/artwork/faq/'.$image['image']))
                {
                    $sql="DELETE FROM faq_step_images WHERE id=$imageid";
                    $dbDelete=dbexecutequery($sql);
                    $error.=$dbDelete['error'];   
                } else {
                    $error.="Unable to delete the image ".$image['image']." from the server.<br>";
                }    
            }
        }
        if ($error!='')
        {
            setUserMessage('There was a problem deleted the FAQ.<br>'.$error,'error');
        } else {
            setUserMessage('FAQ item was deleted successfully.','success');
        }
        redirect("?action=liststep&faqid=$faqid&parentid=$parentid");
    } else {
        $sql="SELECT * FROM faq_steps WHERE faq_id=$faqid ORDER BY step_order";
        $dbFaqs=dbselectmulti($sql);
        tableStart("<a href='?action=addstep&faqid=$faqid&parentid=$parentid'>Add new FAQ step</a>,<a href='?action=list&faqid=$faqid&parentid=$parentid'>Return to FAQ list</a>,<a href='?action=list'>Return to FAQ root</a>","Order,Title",5);
        if ($dbFaqs['numrows']>0)
        {
            foreach ($dbFaqs['data'] as $faq)
            {
                $stepid=$faq['id'];
                $title=$faq['step_title'];
                print "<tr>";
                print "<td>$faq[step_order]</td>";
                print "<td>$title</td>";
                print "<td><a href='?action=editstep&stepid=$stepid&parentid=$parentid&faqid=$faqid'>Edit</a></td>";
                print "<td><a href='?action=deletestep&stepid=$stepid&parentid=$parentid&faqid=$faqid' class='delete'>Delete</a></td>";
                print "<td><a href='?action=listimage&stepid=$stepid&faqid=$faqid&parentid=$faqid'>Images</a></td>";
                print "</tr>\n";
            }
        }
        tableEnd($dbFaqs);
    } 
}

function save_step($action)
{
    $parentid=$_POST['parentid'];
    $faqid=$_POST['faqid'];
    $stepid=$_POST['stepid'];
    $weight=$_POST['weight'];
    $title=addslashes($_POST['title']);
    $text=addslashes($_POST['steptext']);
    
    if ($action=='insert')
    {
       $sql="INSERT INTO faq_steps (faq_id,step_title,step_text,step_order) VALUES 
       ('$faqid', '$title', '$text', '$weight')";
       $dbInsert=dbinsertquery($sql);
       $error=$dbInsert['error']; 
    } else {
       $sql="UPDATE faq_steps SET step_title='$title', step_text='$text', 
       step_order='$weight' WHERE id=$stepid";
       $dbUpdate=dbexecutequery($sql);
       $error=$dbUpdated['error']; 
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the FAQ.<br>'.$error,'error');
    } else {
        setUserMessage('FAQ item was saved successfully.','success');
    }
    redirect("?action=liststep&faqid=$faqid&parentid=$parentid");
}
  
  
function faqimage($action)
{
    $faqid=intval($_GET['faqid']);
    $parentid=intval($_GET['parentid']);
    $stepid=intval($_GET['stepid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Image";
            $sql="SELECT max(image_order) as maxweight FROM faq_step_images WHERE step_id=$stepid";
            $dbMax=dbselectsingle($sql);
            $weight=$dbMax['data']['maxweight']+1;
        } else {
            $button="Update Image";
            $imageid=intval($_GET['imageid']);
            $sql="SELECT * FROM faq_step_images WHERE id=$imageid";
            $dbFaq=dbselectsingle($sql);
            $faq=$dbFaq['data'];
            $image=stripslashes($faq['image']);
            $caption=stripslashes($faq['caption']);
            $weight=$faq['image_order']; 
        }
        print "<form method=post enctype='multipart/form-data' >\n";
        make_slider('weight',$weight,'Order','Sort order for the images');
        make_file('image','Image','Select an image','/artwork/faq/'.$image);
        make_textarea('caption',$caption,'Caption','',80,20);
        make_hidden('imageid',$imageid);
        make_hidden('stepid',$stepid);
        make_hidden('faqid',$faqid);
        make_hidden('parentid',$parentid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        //this is only available at the end of a chain, so no worries about children elements
        $imageid=intval($_GET['imageid']);
        $sql="SELECT * FROM faq_step_images WHERE id=$imageid";
        $dbImage=dbselectsingle($sql);
        $image=$dbImage['data']['image'];
        if(unlink('/artwork/faq/'.$image))
        {
            $sql="DELETE FROM faq_step_images WHERE id=$imageid";
            $dbDelete=dbexecutequery($sql);
            $error.=$dbDelete['error'];   
        } else {
            $error.="Unable to delete the image from the server.";
        }
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the Step image.<br>'.$error,'error');
        } else {
            setUserMessage('Step image was deleted successfully.','success');
        }
        redirect("?action=listimage&stepid=$stepid&faqid=$faqid&parentid=$parentid");
    } else {
        $sql="SELECT * FROM faq_step_images WHERE step_id=$stepid ORDER BY image_order";
        $dbFaqs=dbselectmulti($sql);
        tableStart("<a href='?action=addimage&stepid=$stepid&faqid=$faqid&parentid=$parentid'>Add new image</a>,<a href='?action=liststep&faqid=$faqid&parentid=$parentid'>Return to FAQ steps</a>,<a href='?action=list&faqid=$faqid&parentid=$parentid'>Return to FAQ list</a>,<a href='?action=list'>Return to FAQ root</a>","Order,Image",4);
        if ($dbFaqs['numrows']>0)
        {
            foreach ($dbFaqs['data'] as $faq)
            {
                $imageid=$faq['id'];
                $image=$faq['image'];
                print "<tr>";
                print "<td>$faq[image_order]</td>";
                print "<td><img src='artwork/faq/$image' alt='$image' height=200 /></td>";
                print "<td><a href='?action=editimage&imageid=$imageid&stepid=$stepid&parentid=$parentid&faqid=$faqid'>Edit</a></td>";
                print "<td><a href='?action=deleteimage&imageid=$imageid&stepid=$stepid&parentid=$parentid&faqid=$faqid' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
        }
        tableEnd($dbFaqs);
    } 
}  

function save_image($action)
{
    $parentid=$_POST['parentid'];
    $faqid=$_POST['faqid'];
    $stepid=$_POST['stepid'];
    $imageid=$_POST['imageid'];
    $weight=$_POST['weight'];
    $caption=addslashes($_POST['caption']);
    
    if ($action=='insert')
    {
       $sql="INSERT INTO faq_step_images (faq_id, step_id, caption, image_order) VALUES 
       ('$faqid', '$stepid', '$caption', '$weight')";
       $dbInsert=dbinsertquery($sql);
       $error=$dbInsert['error'];
       $imageid=$dbInsert['insertid']; 
    } else {
       $sql="UPDATE faq_step_images SET faq_id='$faqid', step_id='$stepid', caption='$caption', 
       image_order='$weight' WHERE id=$imageid";
       $dbUpdate=dbexecutequery($sql);
       $error=$dbUpdated['error']; 
    }
    
    if(isset($_FILES))
    {
        // check to make sure files were uploaded
        foreach($_FILES as $file) {
            switch($file['error']) {
                case 0: // file found
                if($file['name'] != NULL && okFileType($file['type'],'image',$file['name']) != false)  {
                    //get the new name of the file
                    //to do that, we need to push it into the database, and return the last record ID
                    if ($imageid!=0) {
                        // process the file
                        $newname="f".$faqid."_s".$stepid.'_'.$file['name'];
                        if(processFile($file,"artwork/faq/",$newname) == true) {
                            $picsql="UPDATE faq_step_images SET image='$newname' WHERE id=$imageid";
                            $result=dbexecutequery($picsql);
                            $error.=$result['error'];
                        } else {
                           $error.= 'There was an error processing the file '.$file['name'];  
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
    if ($error!='')
    {
        setUserMessage('There was a problem saving the FAQ.<br>'.$error,'error');
    } else {
        setUserMessage('FAQ item was saved successfully.','success');
    }
    redirect("?action=listimage&faqid=$faqid&stepid=$stepid&parentid=$parentid");
}
footer();
  
?>
