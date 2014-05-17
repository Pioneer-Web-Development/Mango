<?php
//<!--VERSION: .9 **||**-->

  //this is the helpdesk solution setup system
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
    case "addimage":
    images('add');
    break;
    
    case "editimage":
    images('edit');
    break;
    
    case "listimages":
    images('list');
    break;
    
    case "viewimage":
    images('view');
    break;
    
    case "deleteimage":
    images('delete');
    break;
    
    case "addsolution":
    solutions('add');
    break;
    
    case "editsolution":
    solutions('edit');
    break;
    
    case "delete":
    solutions('delete');
    break;
    
    case "topics":
    topics();
    break;
    
    case "Save Topics":
    save_topics();
    break;
    
    case "Save Solution":
    save_solution('insert');
    break;
    
    case "Update Solution":
    save_solution('update');
    break;
    
    case "Save Image":
    save_image('insert');
    break;
    
    case "Update Image":
    save_image('update');
    break;
    
    case "move":
    changeParent();
    break;
    
    case 'Move Item':
    save_move(); 
    break;
        
    default:
    solutions('list');
    break;
}  
  
function solutions($action)
{
    global $siteID;
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Solution";
            $weight=1;
            $public=1;
        } else {
            $button="Update Solution";
            $solutionid=$_GET['solutionid'];
            $sql="SELECT * FROM helpdesk_solutions WHERE id=$solutionid";
            $dbFaq=dbselectsingle($sql);
            $faq=$dbFaq['data'];
            $keywords=stripslashes($faq['keywords']);
            $title=stripslashes($faq['title']);
            $weight=$faq['weight'];
            $solutiontext=stripslashes($faq['solution_text']);
            $public=stripslashes($faq['public']);
            $brief=stripslashes($faq['solution_brief']);
        }
        print "<form method=post>\n";
        make_text('title',$title,'Title','If this is meant to be a container of sub-faqs, then this is all you need',50);
        make_text('keywords',$keywords,'Keywords','',50);
        make_number('weight',$weight,'Sort order');
        make_checkbox('public',$public,'Public','Check to make this solution available to all employees');
        make_textarea('solutionbrief',$brief,'Brief','Short description of the solution, so person can see if this is the right track.',70,10,true,'','','','','','','limitText(this.form.solutionbrief,254);');
        make_textarea('solutiontext',$solutiontext,'Solution','Full solution to the problem',70,30);
        make_hidden("parentid",$_GET['parentid']);
        make_hidden('solutionid',$_GET['solutionid']);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        //this is only available at the end of a chain, so no worries about children elements
        $id=intval($_GET['solutionid']);
        $sql="DELETE FROM helpdesk_solutions WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($dbDelete['error']=='')
        {
            $sql="DELETE FROM helpdesk_solution_topic_xref WHERE solution_id=$id";
            $dbDelete=dbexecutequery($sql);
            $error=$dbDelete['error'];
            if ($error!='')
            {
                setUserMessage('There was a problem deleting the solution.<br />'.$error,'error');
            } else {
                setUserMessage('The solution has been successfully deleted.','success');
            }
            redirect("?action=list&parentid=$_GET[parentid]");
        } else {
            if ($error!='')
            {
                setUserMessage('There was a problem deleting the ticket.<br />'.$error,'error');
            } else {
                setUserMessage('The ticket has been successfully deleted.','success');
            }
            redirect("?action=list&parentid=$_GET[parentid]");
        }
        
    } else {
        if ($_GET['parentid'])
        {
            $parentid=intval($_GET['parentid']);
        } else {
            $parentid=0;
        }
        $sql="SELECT * FROM helpdesk_solutions WHERE site_id=$siteID AND parentid=$parentid ORDER BY weight, title";
        $dbSolutions=dbselectmulti($sql);
        //if the parentid!=0, get the parent of the parent
        $sql="SELECT parentid FROM helpdesk_solutions WHERE site_id=$siteID AND id=$parentid";
        $dbGrandpa=dbselectsingle($sql);
        if ($dbGrandpa['numrows']>0){$grandpa=$dbGrandpa['data']['parendid'];}else{$grandpa=0;}
        tableStart("<a href='?action=list'>Return to top level</a>,<a href='?action=list&parentid=$grandpa'>Go up to the parent level</a>,<a href='?action=addsolution&parentid=$parentid'>Add new solution or sub-category</a>","Order,Title",7);
        if ($dbSolutions['numrows']>0)
        {
            foreach ($dbSolutions['data'] as $solution)
            {
                //check for a sub. if it exists then we're not allowing delete, just list
                $sql="SELECT * FROM helpdesk_solutions WHERE parentid=$solution[id]";
                $dbSub=dbselectmulti($sql);
                if ($dbSub['numrows']>0)
                {
                    $act="<td><a href='?action=list&parentid=$solution[id]'>List Subs</a></td>";
                } else {
                    $act="<td><a href='?action=delete&parentid=$parentid&solutionid=$solution[id]' class='delete'>Delete</a></td>";
                }
                $solutionid=$solution['id'];
                $title=$solution['title'];
                print "<tr>";
                print "<td>$solution[weight]</td>";
                print "<td>$title</td>";
                print "<td><a href='?action=editsolution&parentid=$parentid&solutionid=$solutionid'>Edit</a></td>";
                print "<td><a href='?action=listimages&parentid=$parentid&solutionid=$solutionid'>Images</a></td>";
                print "<td><a href='?action=list&parentid=$solutionid'>Subs</a></td>";
                print "<td><a href='?action=move&&parentid=$parentid&solutionid=$solutionid'>Move to new parent</a></td>";
                print $act;
                print "</tr>\n";
            }
        }
        tableEnd($dbSolutions);
    }
    
}  

function save_solution($action)
{
    global $siteID;
    $parentid=$_POST['parentid'];
    $solutionid=$_POST['solutionid'];
    $weight=$_POST['weight'];
    $title=addslashes($_POST['title']);
    $solutiontext=addslashes($_POST['solutiontext']);
    $solutionbrief=addslashes($_POST['solutionbrief']);
    if ($_POST['public']){$public=1;}else{$public=0;}
    $keywords=addslashes($_POST['keywords']);
    $keywords=str_replace(";"," ",$keywords);
    $keywords=str_replace(","," ",$keywords);
    $keywords=str_replace("  "," ",$keywords);
    if ($action=='insert')
    {
       $sql="INSERT INTO helpdesk_solutions (parentid,title,keywords,solution_text,solution_brief, public, site_id, weight) VALUES 
       ('$parentid', '$title', '$keywords', '$solutiontext', '$solutionbrief', '$public', '$siteID', '$weight')";
       $dbInsert=dbinsertquery($sql);
       $error=$dbInsert['error'];
       $solutionid=$dbInsert['numrows']; 
    } else {
       $sql="UPDATE helpdesk_solutions SET title='$title', keywords='$keywords', solution_text='$solutiontext', solution_brief='$solutionbrief', weight='$weight', public='$public' WHERE id=$solutionid";
       $dbUpdate=dbexecutequery($sql);
       $error=$dbUpdated['error']; 
    }
     
    if ($error!='')
    {
        setUserMessage('There was a problem saving the solution.<br />'.$error,'error');
    } else {
        setUserMessage('The solution has been successfully saved.','success');
    }
    redirect("?action=list&parentid=$parentid");
    
}

function images($action)
{
    $solutionid=$_GET['solutionid'];
    $imageid=$_GET['imageid'];
    $parentid=$_GET['parentid'];
    if ($action=='add' || $action=='edit')
    {
        if ($action=='edit')
        {
            $sql="SELECT * FROM helpdesk_solutions_images WHERE id=$imageid";
            $dbImage=dbselectsingle($sql);
            $image=$dbImage['data'];
            $fullpath=$image['path'].$image['filename'];
            $caption=stripslashes($image['caption']);
            $button='Update Image';
        } else {
            $button='Save Image';
        }
        print "<form method=post enctype='multipart/form-data'>\n";
        make_textarea('caption',$caption,'Caption','',70,20);
        make_file('screenshot','Image','Add an image',$fullpath);
        make_hidden('imageid',$imageid);
        make_hidden('solutionid',$solutionid);
        make_hidden('parentid',$parentid);
        make_submit('submit',$button);
        
        print "</form>\n";
    } elseif ($action=='view')
    {
        $sql="SELECT * FROM helpdesk_solutions_images WHERE id=$imageid";
        $dbImage=dbselectsingle($sql);
        $image=$dbImage['data'];
        print "<a href='?action=listimages&solutionid=$solutionid&parentid=$parentid'>Return to image list</a><br />\n";
        print "<img src='$image[path]$image[filename]'>";
        
    } elseif ($action=='delete')
    {
        $sql="SELECT * FROM helpdesk_solutions_images WHERE id=$imageid";
        $dbImage=dbselectsingle($sql);
        $image=$dbImage['data'];
        if (file_exists($image['path'].$image['filename']))
        {
            if (unlink($image['path'].$image['filename']))
            {
                $sql="DELETE FROM helpdesk_solutions_images WHERE id=$imageid";
                $dbDelete=dbexecutequery($sql);
                $error=$dbDelete['error'];
                if ($error!='')
                {
                    setUserMessage('There was a problem deleting the image.<br />'.$error,'error');
                } else {
                    setUserMessage('The image has been successfully deleted.','success');
                }
        
                redirect("?action=listimages&solutionid=$solutionid&parentid=$parentid");
            }
        } else {
            //looks like it was never uploaded
            $sql="DELETE FROM helpdesk_solutions_images WHERE id=$imageid";
            $dbDelete=dbexecutequery($sql);
            $error=$dbDelete['error'];
            if ($error!='')
            {
                setUserMessage('There was a problem deleting the image.<br />'.$error,'error');
            } else {
                setUserMessage('The image has been successfully deleted.','success');
            }
        
            redirect("?action=listimages&solutionid=$solutionid&parentid=$parentid");
        }
    } else {
        $sql="SELECT * FROM helpdesk_solutions_images WHERE solution_id=$solutionid";
        $dbImages=dbselectmulti($sql);
         tableStart("<a href='?list&parentid=$parentid'>Return to solution list</a>,<a href='?action=addimage&solutionid=$solutionid&parentid=$parentid'>Add new image</a>","Filename",4);
        if ($dbImages['numrows']>0)
        {
            foreach($dbImages['data'] as $image)
            {
                print "<tr>";
                print "<td>$image[filename]</td>";
                print "<td><a href='?action=editimage&solutionid=$solutionid&parentid=$parentid&imageid=$image[id]'>Edit</a></td>";
                print "<td><a href='?action=viewimage&solutionid=$solutionid&parentid=$parentid&imageid=$image[id]'>View</a></td>";
                print "<td><a href='?action=deleteimage&solutionid=$solutionid&parentid=$parentid&imageid=$image[id]' class='delete' >Delete</a></td>";
                print "</tr>\n";
            }
        }
        tableEnd($dbImages);
    }    
}


function save_image($action)
{
    $folderroot=$_SERVER['DOCUMENT_ROOT'];
    $solutionid=$_POST['solutionid'];
    $parentid=$_POST['parentid'];
    $imageid=$_POST['imageid'];
    $caption=addslashes($_POST['caption']);
    if ($action=='insert')
    {
        $sql="INSERT INTO helpdesk_solutions_images (caption,solution_id) VALUES ('$caption', '$solutionid')";
        $dbImage=dbinsertquery($sql);
        if ($dbImage['error']!='')
        {
            die($dbImage['error']);
        }
        $imageid=$dbImage['numrows'];
    } else {
        $sql="UPDATE helpdesk_solutions_images SET caption='$caption' WHERE id=$imageid";
        $dbImage=dbexecutequery($sql);
    }
    
    if(isset($_FILES))
     { //means we have browsed for a valid file
        foreach($_FILES as $file) {
            switch($file['error']) {
                case 0: // file found
                if($file['name'] != NULL && okFileType($file['type'],'image',$file['name']) != false)  {
                    //get the new name of the file
                    //to do that, we need to push it into the database, and return the last record ID
                   // process the file
                    $date=date("Ym");
                    $path="artwork/helpdeskImages/$date/";
                    if (!file_exists($path))
                    {
                        mkdir($path);
                    }
                    $newname=$file['name'];
                    $newname=str_replace(" ","",$newname);
                    $newname=str_replace("/","",$newname);
                    $newname=str_replace("\\","",$newname);
                    $newname=str_replace("*","",$newname);
                    $newname=str_replace("?","",$newname);
                    $newname=str_replace("!","",$newname);
                    $newname=str_replace("'","",$newname);
                    $newname=str_replace(";","",$newname);
                    $newname=str_replace(":","",$newname);
                    $newname=str_replace("'","",$newname);
                    $newname=str_replace("%","",$newname);
                    $newname=str_replace("\$","",$newname);
                    if(processFile($file,$path,$newname) == true) {
                        $sql="UPDATE helpdesk_solutions_images SET path='artwork/helpdeskImages/$date/', filename='$newname' WHERE id=$imageid";
                        $result=dbinsertquery($sql);
                        $error=$result['error'];
                    } else {
                       $error= 'There was an error inserting the image named '.$file['name'].' into the database. The sql statement was $sql';  
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
        setUserMessage('There was a problem saving the solution.<br />'.$error,'error');
    } else {
        setUserMessage('The solution has been successfully saved.','success');
    }
    redirect("?action=listimages&solutionid=$solutionid&parentid=$parentid");
     
}

function topics()
{
    global $siteID;
    //get all topics
    $solutionid=$_GET['solutionid'];
    $parentid=$_GET['parentid'];
    $sql="SELECT * FROM helpdesk_topics WHERE site_id=$siteID ORDER BY topic_name";
    $dbGroups=dbselectmulti($sql);
    if ($dbGroups['numrows']>0)
    {
        print "<h2>Select the topics that this solution applies to.</h2>\n";
        print "<form method=post>\n";
        foreach($dbGroup['data'] as $topic)
        {
            //see if the employee has this one
            $sql="SELECT * FROM helpdesk_solution_topic_xref WHERE topic_id=$topic[id] AND solution_id=$solutionid";
            $dbExisting=dbselectsingle($sql);
            if ($dbExisting['numrows']>0){$checked=1;}else{$checked=0;}
            print input_checkbox('solution_'.$topic['id'],$checked)." ".$topic['topic_name']."<br />\n";    
        }
        make_hidden('solution_id',$solutionid);
        make_submit('submit','Save Topics');
        print "</form>\n";
    } else {
        print "<a href='?action=list&parentid=$parentid'>Sorry, there are no topics configured yet. Click to return to solution list.</a><br />\n";
    } 
}


function save_topics()
{
    $solutionid=$_POST['solution_id'];
    $parentid=$_POST['parent_id'];
    $values="";
    //clear existing
    $sql="DELETE FROM helpdesk_solution_topic_xref WHERE solution_id=$solutionid";
    $dbDelete=dbexecutequery($sql);
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,6)=='topic_')
        {
            $id=str_replace("topic_","",$key);
            $values.="($solutionid,$id), ";    
        }
    }
    $values=substr($values,0,strlen($values)-2);
    $sql="INSERT INTO helpdesk_solution_topic_xref (solution_id, topic_id) VALUES $values";
    $dbInsert=dbinsertquery($sql);
    $error=$dbInsert['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem saving the topic.<br />'.$error,'error');
    } else {
        setUserMessage('The topic has been successfully saved.','success');
    }
    redirect("?action=list&parentid=$parentid");
   
} 

function changeParent()
{
    global $siteID;
    
    $parentid=$_GET['parentid'];
    $solutionid=$_GET['solutionid'];
    $sql="SELECT id, title FROM helpdesk_solutions WHERE parentid=0 AND site_id=$siteID ORDER BY weight";
    $dbParents=dbselectmulti($sql);
    $parents[0]="TOP LEVEL";
    if ($dbParents['numrows']>0)
    {
        foreach($dbParents['data'] as $p)
        {
            $parents[$p['id']]=$p['title'];    
        }
    }
    print "<form method=post>\n";
    make_select('newparent',$parents[$parentid],$parents,'New parent','Select the new parent for this item');
    make_hidden('parentid',$parentid);
    make_hidden('solutionid',$solutionid);
    make_submit('submit','Move Item');
    print "</form>\n";    
}

function save_move()
{
    $parentid=$_POST['parentid'];
    $solutionid=$_POST['solutionid'];
    $newparent=$_POST['newparent'];
    $sql="UPDATE helpdesk_solutions SET parentid=$newparent WHERE id=$solutionid";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem moving the solution.<br />'.$error,'error');
    } else {
        setUserMessage('The solution has been successfully moved.','success');
    }
    redirect("?action=list&parentid=$newparent");
    
}

footer();
  
?>
