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
        case "Save Carrier":
        save_group('insert');
        break;
        
        case "Update Carrier":
        save_group('update');
        break;
        
        case "add":
        setup_group('add');
        break;
        
        case "edit":
        setup_group('edit');
        break;
        
        case "delete":
        setup_group('delete');
        break;
        
        case "list":
        setup_group('list');
        break;
        
        default:
        setup_group('list');
        break;
        
    } 
    
    
function setup_group($action)
{
    global $cellcarriers; 
    $carriers=array();
    foreach($cellcarriers as $ccar)
    {
        $carriers[$ccar['id']]=$ccar['carrier'];   
    }
    $id=intval($_GET['id']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Carrier";
            $carrier='verizon';
        } else {
            $button="Update Carrier";
            $sql="SELECT * FROM carriers WHERE id=$id";
            $dbGroup=dbselectsingle($sql);
            $group=$dbGroup['data'];
            $fname=stripslashes($group['first_name']);
            $lname=stripslashes($group['last_name']);
            $cell=$group['cell'];
            $carrier=$group['carrier'];
            $email=stripslashes($group['email']);
        }
        print "<form method=post>\n";
        make_text('first_name',$fname,'First Name','',30);
        make_text('last_name',$lname,'Last Name','',30);
        make_text('email',$email,'Email Address','',30);
        make_text('cell',$cell,'Cell Number','',30);
        make_select('carrier',$carriers[$carrier],$carriers,'Service Provider');
        print "<div class='label'>Groups</div><div class='input'>\n";
        $sql="SELECT * FROM carrier_groups ORDER BY group_name";
        $dbGroups=dbselectmulti($sql);
        if ($dbGroups['numrows']>0)
        {
            print "<h2>Select the groups that this carrier is a member of.</h2>\n";
            foreach($dbGroups['data'] as $group)
            {
                //see if the employee has this one
                $sql="SELECT * FROM carrier_groups_xref WHERE group_id=$group[id] AND carrier_id=$id";
                $dbExisting=dbselectsingle($sql);
                if ($dbExisting['numrows']>0){$checked=1;}else{$checked=0;}
                print input_checkbox('group_'.$group['id'],$checked)." ".$group['group_name']."<br />\n";    
            }
            
        }
        print "</div><div class='clear'></div>\n"; 
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $sql="DELETE FROM carrier WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the carrier.<br>'.$error,'error');
        } else {
            //delete any cross references in carrer_groups_xref
            $sql="DELETE FROM carrier_groups_xref WHERE carrier_id=$id";
            $dbDelete=dbexecutequery($sql);
            setUserMessage('Carrier successfully deleted.','success');
        }
        redirect("?action=list");
    } else {
        global $siteID;
        $sql="SELECT * FROM carriers ORDER BY last_name";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new carrier</a>","Last Name, First Name",4);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $fname=$group['first_name'];
                $lname=$group['last_name'];
                $id=$group['id'];
                print "<tr><td>$lname</td><td>$fname</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbGroups);
        
    }
}

function save_group($action)
{
    $userid=$_POST['id'];
    $firstname=addslashes($_POST['first_name']);
    $lastname=addslashes($_POST['last_name']);
    $email=addslashes($_POST['email']);
    $cell=addslashes($_POST['cell']);
    $cell=str_replace(" ","",$cell);
    $cell=str_replace("-","",$cell);
    $cell=str_replace("(","",$cell);
    $cell=str_replace(")","",$cell);
    $cell=str_replace(".","",$cell);
    if(strlen($cell)==7){$cell=$GLOBALS['newspaperAreaCode'].$cell;}
    $carrier=addslashes($_POST['carrier']);
    if ($action=='insert')
    {
        $sql="INSERT INTO carriers (first_name, last_name, cell, carrier, email) VALUES ('$firstname', '$lastname', '$cell', '$carrier', '$email')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $userid=$dbInsert['insertid'];
    } else {
        $sql="UPDATE carriers SET first_name='$firstname', last_name='$lastname', cell='$cell', carrier='$carrier', email='$email' WHERE id=$userid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    
    $values="";
    //clear existing
    $sql="DELETE FROM carrier_groups_xref WHERE carrier_id=$userid";
    $dbDelete=dbexecutequery($sql);
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,6)=='group_')
        {
            $id=str_replace("group_","",$key);
            $values.="($userid,$id), ";    
        }
    }
    $values=substr($values,0,strlen($values)-2);
    if($values!='')
    {
        $sql="INSERT INTO carrier_groups_xref (carrier_id, group_id) VALUES $values";
        $dbInsert=dbinsertquery($sql);
        $error.=$dbInsert['error'];
    } 
    
    if ($error!='')
    {
        setUserMessage('There was a problem saving the carrier.<br>'.$error,'error');
    } else {
        setUserMessage('Carrier successfully saved','success');
    }
    redirect("?action=list");
}

footer();
?>
