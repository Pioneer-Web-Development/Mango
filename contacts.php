<?php
include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
    switch ($action)
    {
        case "Save Contact":
        save_contact('insert');
        break;
        
        case "Update Contact":
        save_contact('update');
        break;
        
        case "add":
        setup_contacts('add');
        break;
        
        case "edit":
        setup_contacts('edit');
        break;
        
        case "delete":
        setup_contacts('delete');
        break;
        
        case "list":
        setup_contacts('list');
        break;
        
        default:
        setup_contacts('list');
        break;
        
    } 
    
    
function setup_contacts($action)
{
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Contact";
        } else {
            $button="Update Contact";
            $id=$_GET['id'];
            $sql="SELECT * FROM contacts WHERE id=$id";
            $dbSize=dbselectsingle($sql);
            $size=$dbSize['data'];
            $company=$size['contact_company'];
            $name=$size['contact_name'];
            $phone=$size['contact_phone'];
            $cell=$size['contact_cell'];
            $email=$size['contact_email'];
            $notes=$size['contact_notes'];
        }
        print "<form action=\"$_SERVER[PHP_SELF]\" method=post>\n";
        
        print "<div class=\"label\">Company</div>\n";
        print "<div class=\"input\">\n";
        print input_text('company',$company,'30');
        print "</div>\n";
        print "<div class=\"clear\"></div>\n";
        print "<div class=\"label\">Contact Name</div>\n";
        print "<div class=\"input\">\n";
        print input_text('name',$name,'30');
        print "</div>\n";
        print "<div class=\"clear\"></div>\n";
        
        print "<div class=\"label\">Contact Main Phone</div>\n";
        print "<div class=\"input\">\n";
        print input_text('phone',$phone,'30');
        print "</div>\n";
        print "<div class=\"clear\"></div>\n";
        
        print "<div class=\"label\">Contact Cell Phone</div>\n";
        print "<div class=\"input\">\n";
        print input_text('cell',$cell,'30');
        print "</div>\n";
        print "<div class=\"clear\"></div>\n";
        
        print "<div class=\"label\">Contact Email</div>\n";
        print "<div class=\"input\">\n";
        print input_text('email',$email,'30');
        print "</div>\n";
        print "<div class=\"clear\"></div>\n";
        
        print "<div class=\"label\">Contact Notes</div>\n";
        print "<div class=\"input\">\n";
        print input_textarea('notes',$notes,80,20);
        print "</div>\n";
        print "<div class=\"clear\"></div>\n";
        
        print "<div class=\"label\"></div>\n";
        print "<div class=\"input\">\n";
        print "<input type=\"hidden\" name=\"codeid\" value=\"$id\" />\n";
        print "<input type=submit name=submit value=\"$button\" />\n";
        print "</div>\n";
        print "<div class=\"clear\"></div>\n";
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=$_GET['id'];
        $sql="DELETE FROM contacts WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM contacts ORDER BY contact_company ASC, contact_name ASC";
        $dbContacts=dbselectmulti($sql);
        tableStart("<a href='?&action=add'>Add new contact</a>","Company,Contact Name,Contact Phone,Contact Cell",6);
        if ($dbContacts['numrows']>0)
        {
            foreach($dbContacts['data'] as $contact)
            {
                $name=$contact['contact_name'];
                $phone=$contact['contact_phone'];
                $cell=$contact['contact_cell'];
                $company=$contact['contact_company'];
                $id=$contact['id'];
                print "<tr><td>$company</td><td>$name</td><td>$phone</td><td>$cell</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=delete&id=$id'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbContacts);
    }
}

function save_contact($action)
{
    $id=$_POST['codeid'];
    $company=addslashes($_POST['company']);
    $name=addslashes($_POST['name']);
    $phone=addslashes($_POST['phone']);
    $cell=addslashes($_POST['cell']);
    $email=addslashes($_POST['email']);
    $notes=addslashes($_POST['notes']);
    if ($action=='insert')
    {
        $sql="INSERT INTO contacts (contact_company, contact_name, contact_phone, contact_cell, 
        contact_email, contact_notes) VALUES ('$company', '$name', '$phone', '$cell', '$email', '$notes')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE contacts SET contact_cell='$cell', contact_company='$company', contact_name='$name', contact_phone='$phone',
        contact_email='$email', contact_notes='$notes' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the contact.','error');
    } else {
        setUserMessage('Contact successfully saved','success');
    }
    redirect("?action=list");
    
}
footer();
?>

