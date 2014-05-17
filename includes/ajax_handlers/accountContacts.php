<?php
    include("../functions_db.php");
    $action=$_POST['action'];
    switch($action)
    {
        case "add":
            $accountid=$_POST['accountid'];
            $title=addslashes($_POST['title']);
            $firstname=addslashes($_POST['firstname']);
            $lastname=addslashes($_POST['lastname']);
            $phone=addslashes($_POST['phone']);
            $cell=addslashes($_POST['cell']);
            $email=addslashes($_POST['email']);
            $notes=addslashes($_POST['notes']);
            $sql="INSERT INTO accounts_contacts (account_id, contact_title, contact_firstname, contact_lastname, contact_phone, contact_cell, 
            contact_email, contact_notes) VALUES ('$accountid', '$title', '$firstname', '$lastname', '$phone', '$cell', '$email', '$notes')";
            $dbInsert=dbinsertquery($sql);
            $error=$dbInsert['error'];
            
          
            $sql="SELECT * FROM accounts_contacts WHERE account_id=$accountid ORDER BY contact_lastname ASC";
            $dbContacts=dbselectmulti($sql);
            
            if ($dbContacts['numrows']>0)
            {
                print "<table class='report-clean-mango' style='width:400px;'>\n";
                print "<tr><th>Name</th><th>Phone</th><th>Cell</th><th>Email</th><th colspan=2>Actions</th></tr>\n";
                foreach($dbContacts['data'] as $contact)
                {
                    $name=$contact['contact_firstname'].' '.$contact['contact_lastname'];
                    $phone=$contact['contact_phone'];
                    $cell=$contact['contact_cell'];
                    $email=$contact['contact_email'];
                    $id=$contact['id'];
                    print "<tr><td>$name</td><td>$phone</td><td>$cell</td><td>$email</td>";
                    print "<td><a href='#' onClick='editContact($id)'>Edit</a></td>\n";
                    print "<td><a href='#' onClick='deleteContact($id)'>Delete</a></td>\n";
                }
                print "</table>\n";  
            } else {
                print $sql;
            }
        break;
        
        case "delete":
            $accountid=$_POST['accountid'];
            $id=intval($_POST['contactid']);
            $sql="DELETE FROM accounts_contacts WHERE id=$id";
            $dbUpdate=dbexecutequery($sql);
            
            $sql="SELECT * FROM accounts_contacts WHERE account_id=$accountid ORDER BY contact_lastname ASC";
            $dbContacts=dbselectmulti($sql);
            
            if ($dbContacts['numrows']>0)
            {
                print "<table class='report-clean-mango' style='width:400px;'>\n";
                print "<tr><th>Name</th><th>Phone</th><th>Cell</th><th>Email</th><th colspan=2>Actions</th></tr>\n";
                foreach($dbContacts['data'] as $contact)
                {
                    $name=$contact['contact_firstname'].' '.$contact['contact_lastname'];
                    $phone=$contact['contact_phone'];
                    $cell=$contact['contact_cell'];
                    $email=$contact['contact_email'];
                    $id=$contact['id'];
                    print "<tr><td>$name</td><td>$phone</td><td>$cell</td><td>$email</td>";
                    print "<td><a href='#' onClick='editContact($id)'>Edit</a></td>\n";
                    print "<td><a href='#' onClick='deleteContact($id)'>Delete</a></td>\n";
                }
                print "</table>\n"; 
            }
        break;
        
        case "edit":
            $id=$_POST['contactid'];
            $sql="SELECT * FROM accounts_contacts WHERE id=$id";
            $dbSize=dbselectsingle($sql);
            $size=$dbSize['data'];
            $title=$size['contact_title'];
            $firstname=$size['contact_firstname'];
            $lastname=$size['contact_lastname'];
            $phone=$size['contact_phone'];
            $cell=$size['contact_cell'];
            $email=$size['contact_email'];
            $notes=$size['contact_notes'];
            $json=array();
            $json['title']=$title;
            $json['firstname']=$firstname;
            $json['lastname']=$lastname;
            $json['phone']=$phone;
            $json['cell']=$cell;
            $json['email']=$email;
            $json['notes']=$notes;
            echo json_encode($json);
        break;
        
        case "update":
            $accountid=$_POST['accountid'];
            $contactid=$_POST['contactid'];
            $title=addslashes($_POST['title']);
            $firstname=addslashes($_POST['firstname']);
            $lastname=addslashes($_POST['lastname']);
            $phone=addslashes($_POST['phone']);
            $cell=addslashes($_POST['cell']);
            $email=addslashes($_POST['email']);
            $notes=addslashes($_POST['notes']);
            $sql="UPDATE accounts_contacts SET contact_cell='$cell', contact_title='$title', contact_firstname='$firstname', 
            contact_lastname='$lastname', contact_phone='$phone',
            contact_email='$email', contact_notes='$notes' WHERE id=$contactid";
            $dbUpdate=dbexecutequery($sql);
            $error=$dbUpdate['error'];
            print $error;
            $sql="SELECT * FROM accounts_contacts WHERE account_id=$accountid ORDER BY contact_lastname ASC";
            $dbContacts=dbselectmulti($sql);
            
            if ($dbContacts['numrows']>0)
            {
                print "<table class='report-clean-mango' style='width:400px;'>\n";
                print "<tr><th>Name</th><th>Phone</th><th>Cell</th><th>Email</th><th colspan=2>Actions</th></tr>\n";
                foreach($dbContacts['data'] as $contact)
                {
                    $name=$contact['contact_firstname'].' '.$contact['contact_lastname'];
                    $phone=$contact['contact_phone'];
                    $cell=$contact['contact_cell'];
                    $email=$contact['contact_email'];
                    $id=$contact['id'];
                    print "<tr><td>$name</td><td>$phone</td><td>$cell</td><td>$email</td>";
                    print "<td><a href='#' onClick='editContact($id)'>Edit</a></td>\n";
                    print "<td><a href='#' onClick='deleteContact($id)'>Delete</a></td>\n";
                }
                print "</table>\n"; 
            }
        break;
        
    }
    
    
    
    dbclose();
?>
