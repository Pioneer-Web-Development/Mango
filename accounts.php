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
        case "move":
        mover();
        break;
        
        case "Save Account":
        save_account('insert');
        break;
        
        case "Update Account":
        save_account('update');
        break;
        
        case "add":
        manage_accounts('add');
        break;
        
        case "edit":
        manage_accounts('edit');
        break;
        
        case "delete":
        manage_accounts('delete');
        break;
        
        case "list":
        manage_accounts('list');
        break;
        
        default:
        manage_accounts('list');
        break;
        
        
        case "addcontact":
        manage_contacts('add');
        break;
        
        case "editcontact":
        manage_contacts('edit');
        break;
        
        case "deletecontact":
        manage_contacts('delete');
        break;
        
        case "contacts":
        manage_contacts('list');
        break;
        
        case "Save Contact":
        save_contact('insert');
        break;
        
        case "Update Contact":
        save_contact('update');
        break;
        
        case "addnote":
        manage_notes('add');
        break;
        
        case "editnote":
        manage_notes('edit');
        break;
        
        case "deletenote":
        manage_notes('delete');
        break;
        
        case "notes":
        manage_notes('list');
        break;
        
        case "Save Note":
        save_note('insert');
        break;
        
        case "Update Note":
        save_note('update');
        break;
        
        case "addcvd":
        manage_vd('add');
        break;
        
        case "editvd":
        manage_vd('edit');
        break;
        
        case "deletevd":
        manage_vd('delete');
        break;
        
        case "vdaccounts":
        manage_vd('list');
        break;
        
        case "Save VD Account Number":
        save_vd('insert');
        break;
        
        case "Update VD Account Number":
        save_vd('update');
        break;
        
        
        
    } 
     
function manage_accounts($action)
{
    global $siteID;
    $countries=array("US"=>"US","Canada"=>"Canada");
    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Account";
            $type="advertiser";
            $billing_state=$GLOBALS['defaultState'];
            $billing_country="US";
            $physical_country="US";
            $physical_state=$GLOBALS['defaultState'];
            $newsprint=0;
            $glossyprinter=0;
            $email_po=0;
            $account_vendor=0;
            $account_commercial=0;
            $account_advertiser=0;
        } else {
            $button="Update Account";
            $accountid=intval($_GET['accountid']);
            $sql="SELECT * FROM accounts WHERE id=$accountid";
            $dbAccount=dbselectsingle($sql);
            $account=$dbAccount['data'];
            $name=stripslashes($account['account_name']);
            $vdname=stripslashes($account['vision_data_name']);
            
            $account_number=stripslashes($account['account_number']);
            $email_po=stripslashes($account['email_po']);
            $email_po_address=stripslashes($account['email_po_address']);
            $delivery_notes=stripslashes($account['delivery_notes']);
            $account_notes=stripslashes($account['account_notes']);
            
            $billing_street=stripslashes($account['billing_street']);
            $billing_street2=stripslashes($account['billing_street2']);
            $billing_city=stripslashes($account['billing_city']);
            $billing_state=stripslashes($account['billing_state']);
            $billing_zip=stripslashes($account['billing_zip']);
            $billing_country=stripslashes($account['billing_country']);
            $billing_email=stripslashes($account['billing_email']);
            $billing_phone=stripslashes($account['billing_phone']);
            $billing_fax=stripslashes($account['billing_fax']);
            
            $physical_street=stripslashes($account['physical_street']);
            $physical_street2=stripslashes($account['physical_street2']);
            $physical_city=stripslashes($account['physical_city']);
            $physical_state=stripslashes($account['physical_state']);
            $physical_zip=stripslashes($account['physical_zip']);
            $physical_country=stripslashes($account['physical_country']);
            $physical_email=stripslashes($account['physical_email']);
            $physical_phone=stripslashes($account['physical_phone']);
            $physical_fax=stripslashes($account['physical_fax']);
            
            $newsprint=stripslashes($account['newsprint']);
            $glossyprinter=stripslashes($account['glossyprinter']);
            $rolltag_removal=stripslashes($account['rolltag_removal']);
            
            $account_advertiser=stripslashes($account['account_advertiser']);
            $account_commercial=stripslashes($account['account_commercial']);
            $account_vendor=stripslashes($account['account_vendor']);
            $we_print_account=stripslashes($account['we_print_account']);
            
            $main_phone=stripslashes($account['main_phone']);
            $main_email=stripslashes($account['main_email']);
            $main_web=stripslashes($account['main_web']);
            
        }
        print "<form method=post>\n";
        print "<div id='tabs'>\n";
            print "<ul>\n";
            print "<li><a href='#basic'>Basic Information</a></li>\n";
            print "<li><a href='#billing'>Billing Information</a></li>\n";
            print "<li><a href='#contacts'>Contacts</a></li>\n";
            print "<li><a href='#delivery'>Delivery/Physical Location Information</a></li>\n";
            print "<li><a href='#newsprint'>Newsprint/Specialty Vendor Information</a></li>\n";
            print "</ul>\n";
            print "<div id='basic'>\n";
                make_text('account_name',$name,'Account Name');
                make_text('vd_name',$vdname,'Account Name in Vision Data');
                make_text('account_number',$account_number,"Account Number","Vision data account numbers are entered from the main list under 'Vision Data account numbers'");
                make_text('main_email',$main_email,'Account Email','Email address for general contact.');
                make_text('main_phone',$main_phone,'Account Phone','Phone for general contact.');
                make_text('main_web',$main_web,'Account Website','Website for general contact.');
                
                make_checkbox('account_advertiser',$account_advertiser,'Advertiser Acccount','Check if this is an advertiser account');    
                make_checkbox('account_commercial',$account_commercial,'Commercial Acccount','Check if this is a commercial printing account');    
                make_checkbox('account_vendor',$account_vendor,'Vendor Acccount','Check if this is a vendor account');    

                
                make_checkbox('email_po',$email_po,'Email PO','If checked, vendor can accept emailed purchase orders to the specified address');
                make_text('email_po_address',$email_po_address,'PO Email Address','Email address to be used to send any purchase orders.');
                make_textarea('account_notes',$account_notes,'Account Notes','Notes about the overall account',80,10);
            print "</div>\n";
            
            print "<div id='billing'>\n";
                print "<div class='label'>Billing Address</div><div class='input'><b>Street Address</b><br />";
                print make_text('billing_street',$billing_street,'','',50)."<br /><b>Street Address (line 2)</b><br />";
                print make_text('billing_street2',$billing_street2,'','',50)."<br />";
                print "<div style='width: 250px;float:left;'>
                <b>City: </b><br />";
                print make_text('billing_city',$billing_city,'','',50);
                print "</div><div class='clear'></div><div style='width:205px;float:left;'><b>State: </b><br />";
                print make_state('billing_state',$billing_state,'');
                print "</div><div style='width:150px;float:left;'><b>Zip: </b><br />";
                print make_text('billing_zip',$billing_zip,'','',16);
                print "</div><div class='clear'></div><div style='width:300px;float:left;'><b>Country: </b><br />";
                print make_select('billing_country',$countries[$billing_country],$countries);
                print "</div><div class='clear'></div>\n";
                print "<div style='width:200px;float:left;'><b>Phone: </b><br />";
                print make_text('billing_phone',$billing_phone,'','',20);
                print "</div><div style='width:200px;float:left;'><b>Fax: </b><br />\n";
                print make_text('billing_fax',$billing_fax,'','',20);
                print "</div><div class='clear'></div><div style='width:400px;float:left;'><b>Email: </b><br />\n";
                print make_text('billing_email',$billing_email,'','',50);
                print "</div><div class='clear'></div>\n";
                print "</div><div class='clear'></div>\n";
            print "</div>\n";
            
            print "<div id='contacts'>\n";
                print "<div id='contact_list' style='float:left;width:450px;margin-right:30px;'>\n";
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
                        
                print "</div>\n";
                print "<div style='float:left;'>\n";
                if($action=='edit')
                {
                    //only allow adding new contacts when you have saved the account
                    make_text('contact_title','','Contact Title','',50);
                    make_text('contact_firstname','','Contact First Name','',50);
                    make_text('contact_lastname','','Contact Last Name','',50);
                    make_text('contact_phone','','Phone','',20);
                    make_text('contact_cell','','Cell','',20);
                    make_text('contact_email','','Email','',50);
                    make_textarea('contact_notes','','Notes','',80,10,false);
                    make_hidden('contact_action','add');
                    make_hidden('contact_id','');
                    make_button('submit_contact','Add Contact','','','saveContact();');
                    
                } else {
                    print "You must save the account before you can add contacts to it.";
                }
                print "</div>\n";
                print "<div class='clear'></div>\n";
            print "</div>\n";
        
            print "<div id='delivery'>\n";
                print "<div class='label'>Physical/Delivery Address</div><div class='input'><b>Street Address</b><br />";
                print make_text('physical_street',$physical_street,'','',50)."<br /><b>Street Address (line 2)</b><br />";
                print make_text('physical_street2',$physical_street2,'','',50)."<br />";
                print "<div style='width: 250px;float:left;'>
                <b>City: </b><br />";
                print make_text('physical_city',$physical_city,'','',50);
                print "</div><div class='clear'></div><div style='width:205px;float:left;'><b>State: </b><br />";
                print make_state('physical_state',$physical_state,'');
                print "</div><div style='width:150px;float:left;'><b>Zip: </b><br />";
                print make_text('physical_zip',$physical_zip,'','',16);
                print "</div><div class='clear'></div><div style='width:300px;float:left;'><b>Country: </b><br />";
                print make_select('physical_country',$countries[$physical_country],$countries);
                print "</div><div class='clear'></div>\n";
                print "<div style='width:200px;float:left;'><b>Phone: </b><br />";
                print make_text('physical_phone',$physical_phone,'','',20);
                print "</div><div style='width:200px;float:left;'><b>Fax: </b><br />\n";
                print make_text('physical_fax',$physical_fax,'','',20);
                print "</div><div class='clear'></div><div style='width:400px;float:left;'><b>Email: </b><br />\n";
                print make_text('physical_email',$physical_email,'','',50);
                print "</div><div class='clear'></div>\n";
                print "</div><div class='clear'></div>\n";
                make_textarea('delivery_notes',$delivery_notes,'Delivery Notes','Delivery instructions &amp; information.',80,10);
            print "</div>\n";
            
            print "<div id='newsprint'>\n";
                make_checkbox('we_print_account',$we_print_account,'We Print Account','Check if this is the default account that should be used for all in-house jobs');    
                make_checkbox('newsprint',$newsprint,'Newsprint','Check if this is a newsprint vendor');    
                make_checkbox('glossyprinter',$glossyprinter,'Glossy Printer','Check if this is a glossy print vendor');    
                make_number('rolltag_removal',$rolltagremoval,'Roll Tag Extra','If this is a newsprint vendor, enter the number of characters that are normally<br />removed from the beginning of the roll tag when entered manually. (Ex. AB1)');
            print "</div>\n";
            
        print "</div>\n";
        make_hidden('accountid',$accountid);
        if(checkPermission(43,'function'))
        {
            make_submit('submit',$button);
        } else {
            print "<a href='?action=list' class='button'>Return to account list</a>";
        }
        print "</form>\n";
        
        ?>
        <script type='text/javascript'>
        $('#tabs').tabs();
        
        function deleteContact(contactid)
        {
            $.ajax({
              url: "includes/ajax_handlers/accountContacts.php",
              type: "POST",
              data: ({action:'delete',
                  accountid:$('#accountid').val(),
                  contactid:contactid
              }),
              dataType: "html",
              success: function(response){
                  $('#contact_list').html(response);
              }
            })
        }
        
        function editContact(contactid)
        {
            $.ajax({
              url: "includes/ajax_handlers/accountContacts.php",
              type: "POST",
              data: ({action:'edit',
                  accountid:$('#accountid').val(),
                  contactid:contactid
              }),
              dataType: "json",
              success: function(response){
                  $('#contact_action').val('update');
                  $('#contact_title').val(response.title);
                  $('#contact_firstname').val(response.firstname);
                  $('#contact_lastname').val(response.lastname);
                  $('#contact_cell').val(response.cell);
                  $('#contact_phone').val(response.phone);
                  $('#contact_notes').val(response.notes);
                  $('#contact_email').val(response.email);
                  $('#contact_id').val(contactid);
                  $('#submit_contact').val('Update Contact');
              }
            })
        }
        
        
        function saveContact()
        {
            if($('#contact_name').val()!='')
            {
                var action=$('#contact_action').val();
                $.ajax({
                  url: "includes/ajax_handlers/accountContacts.php",
                  type: "POST",
                  data: ({action:action,
                      firstname:$('#contact_firstname').val(),
                      lastname:$('#contact_lastname').val(),
                      title:$('#contact_title').val(),
                      cell:$('#contact_cell').val(),
                      phone:$('#contact_phone').val(),
                      notes:$('#contact_notes').val(),
                      email:$('#contact_email').val(),
                      contactid:$('#contact_id').val(),
                      accountid:$('#accountid').val()
                  }),
                  dataType: "html",
                  success: function(response){
                      $('#contact_title').val('');
                      $('#contact_firstname').val('');
                      $('#contact_lastname').val('');
                      $('#contact_cell').val('');
                      $('#contact_phone').val('');
                      $('#contact_notes').val('');
                      $('#contact_email').val('');
                      $('#contact_list').html(response);
                      $('#contact_action').val('add');
                      $('#contact_id').val('');
                      $('#submit_contact').val('Add Contact');
                  }
                })
            } else {
                var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You need at a minimum the contact name.</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'Incomplete',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('close');
                        }
                    }]
                })
            }
       
        }
        
        
        </script>
        <?php
          
    } elseif($action=='delete') {
        $accountid=intval($_GET['accountid']);
        $sql="DELETE FROM accounts WHERE id=$accountid";
        $dbUpdate=dbexecutequery($sql);
        $sql="DELETE FROM accounts_contacts WHERE account_id=$accountid";
        $dbUpdate=dbexecutequery($sql);
        $sql="DELETE FROM accounts_vd WHERE account_id=$accountid";
        $dbUpdate=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $types=array('Show All','Advertiser','Commercial','Vendor');
        $search="<form method=post>\n";
        $search.="<b>Filter by type</b><br/>";
        $search.=input_select('type',$types[0],$types);
        $search.="<input type='submit' name='submit' value='Search' />\n";
        $search.="</form>\n";
        
        if($_POST)
        {
            if($_POST['type']!=0)
            {
                switch($_POST['type'])
                {
                    case "1":
                    $filter="AND account_advertiser=1";
                    break;
                    
                    case "2":
                    $filter="AND account_commercial=1";
                    break;
                    
                    case "3":
                    $filter="AND account_vendor=1";
                    break;
                    
                    default:
                    $filter='';
                    break;
                }
            }
        } else {
            $filter='';
        }
        $sql="SELECT * FROM accounts WHERE site_id=$siteID $filter ORDER BY account_name";
        $dbAccounts=dbselectmulti($sql);
        if(checkPermission(43,'function'))
        {
            $addlink="<a href='?action=add'>Add new account</a>,<a href='?action=contacts&accountid=0'>Show generic contacts</a>";
        } else {
            $addlink="<a href='?action=contacts&accountid=0'>Show generic contacts<a/>";
        }
        tableStart($addlink,"Account Name,Vendor,Advertiser,Commercial Print",8,$search);
        
        if ($dbAccounts['numrows']>0)
        {
            foreach($dbAccounts['data'] as $customer)
            {
                $name=stripslashes($customer['account_name']);
                $accountid=$customer['id'];
                
                print "<tr>\n";
                print "<td>$name</td>\n";
                if($customer['account_vendor']){print "<td><img src='artwork/icons/accepted_48.png' width=24></td>\n";}else{print "<td></td>\n";}
                if($customer['account_advertiser']){print "<td><img src='artwork/icons/accepted_48.png' width=24></td>\n";}else{print "<td></td>\n";}
                if($customer['account_commercial']){print "<td><img src='artwork/icons/accepted_48.png' width=24></td>\n";}else{print "<td></td>\n";}
                
                print "<td><a href='?action=edit&accountid=$accountid'>Edit/View</a></td>\n";
                print "<td><a href='?action=contacts&accountid=$accountid'>Contacts</a></td>\n";
                print "<td><a href='?action=vdaccounts&accountid=$accountid'>Vision Data Account #s</a></td>\n";
                print "<td><a class='delete' href='?action=delete&accountid=$accountid'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbAccounts);
        
    }
       
  
}

function save_account($action)
{
    global $siteID;
    $accountid=$_POST['accountid'];
    $account_name=addslashes($_POST['account_name']);
    $account_type=addslashes($_POST['account_type']);
    $vdname=addslashes($_POST['vd_name']);
    $email_po_address=addslashes($_POST['email_po_address']);
    $delivery_notes=addslashes($_POST['delivery_notes']);
    $account_notes=addslashes($_POST['account_notes']);
    $account_number=addslashes($_POST['account_number']);
    $main_phone=addslashes($_POST['main_phone']);
    $main_web=addslashes($_POST['main_web']);
    $main_email=addslashes($_POST['main_email']);
    
    
    $billing_street=addslashes($_POST['billing_street']);
    $billing_street2=addslashes($_POST['billing_street2']);
    $billing_city=addslashes($_POST['billing_city']);
    $billing_state=addslashes($_POST['billing_state']);
    $billing_zip=addslashes($_POST['billing_zip']);
    $billing_country=addslashes($_POST['billing_country']);
    $billing_email=addslashes($_POST['billing_email']);
    $billing_phone=addslashes($_POST['billing_phone']);
    $billing_fax=addslashes($_POST['billing_fax']);
    
    $physical_street=addslashes($_POST['physical_street']);
    $physical_street2=addslashes($_POST['physical_street2']);
    $physical_city=addslashes($_POST['physical_city']);
    $physical_state=addslashes($_POST['physical_state']);
    $physical_zip=addslashes($_POST['physical_zip']);
    $physical_country=addslashes($_POST['physical_country']);
    $physical_email=addslashes($_POST['physical_email']);
    $physical_phone=addslashes($_POST['physical_phone']);
    $physical_fax=addslashes($_POST['physical_fax']);
    
    $rolltag_removal=addslashes($_POST['rolltag_removal']);
    if($rolltag_removal==''){$rolltag_removal=0;}
    if($_POST['newsprint']){$newsprint=1;}else{$newsprint=0;}
    if($_POST['glossyprinter']){$glossyprinter=1;}else{$glossyprinter=0;}
    if($_POST['account_vendor']){$account_vendor=1;}else{$account_vendor=0;}
    if($_POST['account_commercial']){$account_commercial=1;}else{$account_commercial=0;}
    if($_POST['account_advertiser']){$account_advertiser=1;}else{$account_advertiser=0;}
    if($_POST['we_print_account']){$we_print_account=1;}else{$we_print_account=0;}
    if($_POST['email_po']){$email_po=1;}else{$email_po=0;}
            
    if ($action=='insert')
    {
        $sql="INSERT INTO accounts (account_name, account_advertiser, account_commercial, account_vendor, vision_data_name, site_id, email_po, email_po_address, delivery_notes, account_notes, billing_street, billing_street2, billing_city, billing_state, billing_zip, billing_country, billing_email, billing_phone, billing_fax, physical_street, physical_street2, physical_city, physical_state, physical_zip, physical_country, physical_email, physical_phone, physical_fax, account_number, newsprint, glossyprinter, rolltag_removal, we_print_account, main_email, main_phone, main_web) 
        VALUES ('$account_name', '$account_advertiser','$account_commercial', '$account_vendor', '$vdname', '$siteID', '$email_po', '$email_po_address', '$delivery_notes', '$account_notes', '$billing_street', '$billing_street2', '$billing_city', '$billing_state', '$billing_zip', '$billing_country', '$billing_email', '$billing_phone', '$billing_fax', '$physical_street', '$physical_street2', '$physical_city', '$physical_state', '$physical_zip', '$physical_country', '$physical_email', '$physical_phone', '$physical_fax', '$account_number', '$newsprint', '$glossyprinter', '$rolltag_removal', '$we_print_account', '$main_email', '$main_phone', '$main_web')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE accounts SET account_name='$account_name', account_advertiser='$account_advertiser', 
        account_commercial='$account_commercial', account_vendor='$account_vendor', vision_data_name='$vdname', 
        email_po='$email_po', email_po_address='$email_po_address', delivery_notes='$delivery_notes', account_notes='$account_notes', 
        billing_street='$billing_street', billing_street2='$billing_street2', billing_city='$billing_city', billing_state='$billing_state', billing_zip='$billing_zip', billing_country='$billing_country', billing_email='$billing_email', billing_phone='$billing_phone', billing_fax='$billing_fax', physical_street='$physical_street', physical_street2='$physical_street2', physical_city='$physical_city', physical_state='$physical_state', physical_zip='$physical_zip', physical_country='$physical_country', physical_email='$physical_email', physical_phone='$physical_phone', physical_fax='$physical_fax', account_number='$account_number', newsprint='$newsprint', glossyprinter='$glossyprinter', rolltag_removal='$rolltag_removal', we_print_account='$we_print_account', main_phone='$main_phone', main_web='$main_web', main_email='$main_email'  WHERE id=$accountid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the account.<br />'.$error,'error');
    } else {
        setUserMessage('The account was successfully saved','success');
    }
    redirect("?action=list");
    
}


function manage_contacts($action)
{
    $accountid=intval($_GET['accountid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Contact";
        } else {
            $button="Update Contact";
            $id=$_GET['id'];
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
        }
        print "<form method=post>\n";
        make_text('title',$title,'Contact Title','',50);
        make_text('firstname',$firstname,'Contact First Name','',50);
        make_text('lastname',$lastname,'Contact Last Name','',50);
        make_text('phone',$phone,'Phone','',20);
        make_text('cell',$cell,'Cell','',20);
        make_text('email',$email,'Email','',50);
        make_textarea('notes',$notes,'Notes','',80,10);
        make_hidden('accountid',$accountid);
        make_hidden('contactid',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=intval($_GET['id']);
        $sql="DELETE FROM accounts_contacts WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        redirect("?action=contacts&accountid=$accountid");
    } else {
        $sql="SELECT * FROM accounts_contacts WHERE account_id=$accountid ORDER BY contact_lastname ASC";
        $dbContacts=dbselectmulti($sql);
        tableStart("<a href='?action=addcontact&accountid=$accountid'>Add new contact</a>,
        <a href='?action=contacts&accountid=0'>Show Generic Contacts</a>,
        <a href='?action=list'>Return to account list</a>","Name,Phone,Cell #,Email",6);
        if ($dbContacts['numrows']>0)
        {
            foreach($dbContacts['data'] as $contact)
            {
                $name=$contact['contact_firstname'].' '.$contact['contact_lastname'];
                $phone=$contact['contact_phone'];
                $cell=$contact['contact_cell'];
                $email=$contact['contact_email'];
                $id=$contact['id'];
                print "<tr><td>$name</td><td>$phone</td><td>$cell</td><td>$email</td>";
                print "<td><a href='?action=editcontact&id=$id&accountid=$accountid'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=deletecontact&id=$id&accountid=$accountid'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbContacts);
    }
}


function save_contact($action)
{
    $accountid=$_POST['accountid'];
    $contactid=$_POST['contactid'];
    $title=addslashes($_POST['title']);
    $firstname=addslashes($_POST['firstname']);
    $lastname=addslashes($_POST['lastname']);
    $phone=addslashes($_POST['phone']);
    $cell=addslashes($_POST['cell']);
    $email=addslashes($_POST['email']);
    $notes=addslashes($_POST['notes']);
    
    if ($action=='insert')
    {
        $sql="INSERT INTO accounts_contacts (account_id, contact_title, contact_firstname, contact_lastname, contact_phone, contact_cell, contact_email, contact_notes) VALUES ('$accountid', '$title', '$firstname', '$lastname', '$phone', '$cell', '$email', '$notes')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE accounts_contacts SET contact_cell='$cell', contact_title='$title', contact_firstname='$firstname', contact_lastname='$lastname', 
        contact_phone='$phone', contact_email='$email', contact_notes='$notes' WHERE id=$contactid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the contact.<br />'.$error,'error');
    } else {
        setUserMessage('Contact successfully saved','success');
    }
    redirect("?action=contacts&accountid=$accountid");
    
}

function manage_notes($action)
{
    $accountid=intval($_GET['accountid']);
    $contacts[0]='Please select a contact';
    $sql="SELECT * FROM accounts_contacts WHERE account_id=$accountid";
    $dbContacts=dbselectmulti($sql);
    if($dbContacts['numrows']>0)
    {
        foreach($dbContacts['data'] as $contact)
        {
            $contacts[$contact['id']]=stripslashes($contact['contact_firstname'].' '.$contact['contact_lastname']);
        }
    } else {
        $contacts[0]='No contacts set up';
    }
    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Note";
            $contactid=0;
        } else {
            $button="Update Note";
            $id=$_GET['id'];
            $sql="SELECT * FROM accounts_notes WHERE id=$id";
            $dbNote=dbselectsingle($sql);
            $note=$dbNote['data'];
            $text=stripslashes($note['note_text']);
            $contactid=$note['contact_id'];
        }
        print "<form method=post>\n";
        make_select('contactid',$contacts[$contactid],$contacts,'Contact','Select the person you contacted regarding this account note, if anyone');
        make_textarea('notes',$text,'Note','',80,10);
        make_hidden('accountid',$accountid);
        make_hidden('noteid',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=intval($_GET['id']);
        $sql="DELETE FROM accounts_notes WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        redirect("?action=notes&accountid=$accountid");
    } else {
        $sql="SELECT * FROM accounts_notes WHERE account_id=$accountid ORDER BY note_datetime ASC";
        $dbNotes=dbselectmulti($sql);
        tableStart("<a href='?action=addnote&accountid=$accountid'>Add new note</a>,
        <a href='?action=list'>Return to account list</a>","Note taken date/time",3);
        if ($dbNotes['numrows']>0)
        {
            foreach($dbNotes['data'] as $note)
            {
                $notetime=date("m/d/Y H:i",strtotime($note['note_datetime']));
                $id=$contact['id'];
                print "<tr><td>$notetime</td>";
                print "<td><a href='?action=editnote&id=$id&accountid=$accountid'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=deletenote&id=$id&accountid=$accountid'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbNotes);
    }
}


function save_note($action)
{
    $accountid=$_POST['accountid'];
    $noteid=$_POST['noteid'];
    $by=$_SESSION['cmsuser']['userid'];
    $time=date("Y-m-d H:i");
    $text=addslashes($_POST['notes']);
    $contactid=addslashes($_POST['contactid']);
    if ($action=='insert')
    {
        $sql="INSERT INTO accounts_notes (account_id, contact_id, note_datetime, note_by, note) VALUES 
        ('$accountid', '$contactid', '$time', '$by', '$text')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE accounts_notes SET contact_id='$contactid', note='$text' WHERE id=$noteid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the note.<br />'.$error,'error');
    } else {
        setUserMessage('Note successfully saved','success');
    }
    redirect("?action=notes&accountid=$accountid");
    
}

function manage_vd($action)
{
    $accountid=intval($_GET['accountid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save VD Account Number";
        } else {
            $button="Update VD Account Number";
            $id=$_GET['id'];
            $sql="SELECT * FROM accounts_vd WHERE id=$id";
            $dbSize=dbselectsingle($sql);
            $size=$dbSize['data'];
            $vd_account_number=$size['vd_account_number'];
        }
        print "<form method=post>\n";
        make_text('vd_account_number',$vd_account_number,'Vision Data Account Number','',50);
        make_hidden('accountid',$accountid);
        make_hidden('vdid',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=intval($_GET['id']);
        $sql="DELETE FROM accounts_vd WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        redirect("?action=vdaccounts&accountid=$accountid");
    } else {
        $sql="SELECT * FROM accounts_vd WHERE account_id=$accountid";
        $dbContacts=dbselectmulti($sql);
        tableStart("<a href='?action=addvd&accountid=$accountid'>Add new VD Account #</a>,
        <a href='?action=list'>Return to account list</a>","Account Number",3);
        if ($dbContacts['numrows']>0)
        {
            foreach($dbContacts['data'] as $contact)
            {
                $vd_account_number=$contact['vd_account_number'];
                $id=$contact['id'];
                print "<tr><td>$vd_account_number</td>";
                print "<td><a href='?action=editvd&id=$id&accountid=$accountid'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=deletevd&id=$id&accountid=$accountid'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbContacts);
    }
}


function save_vd($action)
{
    $id=$_POST['vdid'];
    $accountid=$_POST['accountid'];
    $vd_account_number=addslashes($_POST['vd_account_number']);
    if ($action=='insert')
    {
        $sql="INSERT INTO accounts_vd (account_id, vd_account_number) VALUES ('$account_id', '$vd_account_number')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE accounts_contacts SET vd_account_number='$vd_account_number', WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the Vision Data account.','error');
    } else {
        setUserMessage('Vision Data account successfully saved','success');
    }
    redirect("?action=vdaccounts&accountid=$accountid");
    
}


function mover()
{
    //moves old contacts, vendors and customers to the new accounts system
    //first, move all customers
    print "<hr>Starting Customers<br />";
    $sql="SELECT * FROM customers";
    $dbCustomers=dbselectmulti($sql);
    if($dbCustomers['numrows']>0)
    {
        foreach ($dbCustomers['data'] as $customer)
        {
            if($customer['customer_type']=='commercial')
            {
                $commercial=1;
            } else {
                $commercial=0;
            }
            if($customer['customer_type']=='advertiser')
            {
                $advertiser=1;
            } else {
                $advertiser=0;
            }
            $vendor=0;
            $sql="INSERT INTO accounts (account_name, account_advertiser, account_commercial, account_vendor, vision_data_name, site_id, we_print_account) 
            VALUES ('".addslashes($customer['customer_name'])."', $advertiser, $commercial, $vendor, '".addslashes($customer['vision_data_name'])."', '$customer[site_id]', 0)";
            $dbInsert=dbinsertquery($sql);
            $accountid=$dbInsert['insertid'];
            
            $error.=$dbInsert['error'];
            if($dbInsert['error']=='')
            {
                //create vd account records if the customer had that field data
                if($customer['vision_data_account']!='')
                {
                    $sql="INSERT INTO accounts_vd (account_id, vd_account_number) VALUES ('$accountid', '$customer[vision_data_account]')";
                    $dbInsert=dbinsertquery($sql);
                }
            }    
        }
    }
     if($error!='')
    {
        print $error;
        $allerror.=$error;
    }else {
        $error='';
    }
    print "Completed processing Customers<br /><br />";
    print "<hr>Starting Vendors<br />";
    $sql="SELECT * FROM vendors";
    $dbVendors=dbselectmulti($sql);
    if($dbVendors['numrows']>0)
    {
        foreach ($dbVendors['data'] as $vendor)
        {
            $sql="INSERT INTO accounts (account_name, account_advertiser, account_commercial, account_vendor,  vision_data_name, site_id, email_po, 
            email_po_address, delivery_notes, account_notes, 
            billing_street, billing_street2, billing_city, billing_state, billing_zip, billing_country, billing_email, billing_phone, billing_fax,
            physical_street, physical_street2, physical_city, physical_state, physical_zip, physical_country, physical_email, physical_phone, 
            physical_fax, 
            account_number, newsprint, glossyprinter, rolltag_removal, we_print_account) 
            VALUES ('".addslashes($vendor['vendor_name'])."', 0, 0, 1, '', '$vendor[site_id]', '$vendor[email_po]', '$vendor[po_email_address]', '', 
            '".addslashes($vendor['vendor_notes'])."', 
            '$vendor[vendor_address]', '', '$vendor[vendor_city]', '$vendor[vendor_state]', '$vendor[vendor_zip]', '$vendor[vendor_country]', 
            '$vendor[vendor_email]', '$vendor[vendor_phone]', '$vendor[vendor_fax]',
            '$vendor[vendor_address]', '', '$vendor[vendor_city]', '$vendor[vendor_state]', '$vendor[vendor_zip]', '$vendor[vendor_country]', 
            '$vendor[vendor_email]', '$vendor[vendor_phone]', '$vendor[vendor_fax]', '', '$vendor[newsprint]', '$vendor[glossyprinter]', 
            '";
            if($vendor['rolltag_removal']!=''){
                $sql.=$vendor['rolltag_removal'];
            }else{
                $sql.='0';
            }
            $sql.="', 0)";
            $dbInsert=dbinsertquery($sql);
            $error.=$dbInsert['error'];
        }
    }
    if($error!='')
    {
        print $error;
        $allerror.=$error;
    }else {
        $error='';
    }
    print "Completed processing Vendors<br /><br />";
    print "<hr>Processing Contacts...<br />";
    
    //and finally, move all contacts over as generic contacts -- account id of 0
    $sql="SELECT * FROM contacts";
    $dbContacts=dbselectmulti($sql);
    if($dbContacts['numrows']>0)
    {
        foreach($dbContacts['data'] as $contact)
        {
           $sql="INSERT INTO accounts_contacts (account_id, contact_name, contact_phone, contact_cell, 
            contact_email, contact_notes) VALUES ('0', '$contact[contact_name]', '$contact[contact_phone]', '$contact[contact_cell]', 
            '$contact[contact_email]', '".addslashes($contact['contact_notes'])."')";
            $dbInsert=dbinsertquery($sql);
            $error.=$dbInsert['error']; 
        }
    }
    if($error!='')
    {
        print $error;
        $allerror.=$error;
    }else {
        $error='';
    }
    print "Completed processing Contacts<br /><br />";
    if($allerror!='')
    {
        print $allerror;
    } else {
        print "All customers, vendors and contacts have been moved to the new accounts system.";
    }
}
footer();
?>
