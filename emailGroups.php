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
    case "addgroup":
    emailgroups('add');
    break;
    
    case "editgroup":
    emailgroups('edit');
    break;
    
    case "deletegroup":
    emailgroups('delete');
    break;
    
    case "Add Group":
    save_group('insert');
    break;
    
    case "Update Group":
    save_group('update');
    break;
    
    case "publications":
    publications();
    break;
    
    case "Set Publications":
    save_publications();
    break;
    
    default:
    emailgroups('list');
    break;
}

 
function emailgroups($action)
{
    global $siteID;
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add Group";
            $groupactive=0;
        } else {
            $groupid=$_GET['groupid'];
            $sql="SELECT * FROM email_groups WHERE id=$groupid";
            $dbGroup=dbselectsingle($sql);
            $group=$dbGroup['data'];
            $groupname=$group['group_name'];
            $groupemail=$group['group_email'];
            $groupactive=$group['group_active'];    
            $button="Update Group";
        }
        print "<form method=post>\n";
        make_text('groupname',$groupname,'Group Name','',50);
        make_text('groupemail',$groupemail,'Email Address','',50);
        make_checkbox('groupactive',$groupactive,'Active','Check to activate');
        make_hidden('groupid',$groupid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $groupid=$_GET['groupid'];
        $sql="DELETE FROM email_groups WHERE id=$groupid";
        $dbDelete=dbexecutequery($sql);
        if ($dbDelete['error']=='')
        {
            $sql="DELETE FROM email_groups_publications WHERE group_id=$groupid";
            $dbDelete=dbexecutequery($sql);
            redirect("?action=listgroups");
        } else {
            print $dbDelete['error'];
        }
        
    } else {
        $sql="SELECT * FROM email_groups WHERE site_id=$siteID";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=addgroup'>Add new email group</a>","Group Name,Active,Email Address",6);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $groupid=$group['id'];
                $groupname=$group['group_name'];
                $groupemail=$group['group_email'];
                if ($group['group_active']==1){$active="Active";}else{$active="Disabled";}
                print "<tr>";
                print "<td>$groupname</td>";
                print "<td>$active</td>";
                print "<td>$groupemail</td>";
                print "<td><a href='?action=publications&groupid=$groupid'>Publications</a></td>";
                print "<td><a href='?action=editgroup&groupid=$groupid'>Edit</a></td>";
                print "<td><a class='delete' href='?action=deletegroup&groupid=$groupid'>Delete</a></td>";
            }
            
        }
        tableEnd($dbGroups);
        
    }
} 

function save_group($action)
{
    global $siteID;
    $groupid=$_POST['groupid'];
    $name=addslashes($_POST['groupname']);
    $email=addslashes($_POST['groupemail']);
    if($_POST['groupactive']){$active=1;}else{$active=0;}
    if($action=='insert')
    {
        $sql="INSERT INTO email_groups (group_name, group_email, group_active,site_id) VALUES 
        ('$name', '$email', '$active', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE email_groups SET group_name='$name', group_email='$email', group_active='$active' WHERE id=$groupid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the email group','error');
    } else {
        setUserMessage('Email group successfully saved','success');
    }
    redirect("?action=listgroups");    
}
  
function publications()
{
    global $siteID;
    $groupid=$_GET['groupid'];
    $sql="SELECT * FROM publications WHERE site_id=$siteID ORDER BY pub_name";
    $dbPublications=dbselectmulti($sql);
    $sql="SELECT * FROM email_groups_publications WHERE group_id=$groupid";
    $dbGroups=dbselectmulti($sql);
    print "<form method=post>\n";
    if ($dbPublications['numrows']>0)
    {
        print "<h4>Please set publications:</h4>";
        foreach($dbPublications['data'] as $publication)
        {
            $pvalue=0;
            if ($dbGroups['numrows']>0)
            {
                foreach($dbGroups['data'] as $grouppub)
                {
                    if ($publication['id']==$grouppub['pub_id'])
                    {
                        if ($grouppub['value']==1)
                        {
                            $pvalue=1;
                        }        
                    }
                }
            }
            print input_checkbox('publication_'.$publication['id'],$pvalue);
            print "&nbsp;&nbsp;".$publication['pub_name']."<br>";
        }
        print "<div class='label'></div><div class='input'>\n";
        print "<input type='hidden' id='where' name='where' value='$_GET[where]'>\n";
        print "<input type='hidden' id='order' name='order' value='$_GET[order]'>\n";
        print "<input type='hidden' id='groupid' name='groupid' value='$groupid'>\n";
        print "<input type='submit' id='submit' name='submit' value='Set Publications'>\n";
        print "</div><div class='clear'></div>\n";
    } else {
       print "Sorry, no publications have been defined yet.";
    }
    print "</form>\n";
}

function save_publications()
{
    $groupid=$_POST['groupid'];
    //start by deleting all existing permissions for this user
    $sql="DELETE FROM email_groups_publications WHERE group_id=$groupid";
    $dbDelete=dbexecutequery($sql);
    $sql="SELECT * FROM publications";
    $dbPublications=dbselectmulti($sql);
    $value="";
    foreach ($dbPublications['data'] as $publication)
    {
        $pvalue=0;
        if ($_POST["publication_$publication[id]"])
        {
            $pvalue=1;
        }
        $value.="('$publication[id]','$groupid','$pvalue'),";
    }
    $value=substr($value,0,strlen($value)-1);
    $sql="INSERT INTO email_groups_publications (pub_id, group_id, value) VALUES $value";
    $dbinsert=dbinsertquery($sql);
    if ($error!='')
    {
        setUserMessage('There was a problem saving the publication for this group','error');
    } else {
        setUserMessage('Publication successfully saved for this group','success');
    }
    redirect("?action=list");//&where=$_POST[where]&order=$_POST[order]");
    
}
footer();
?>