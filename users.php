<?php
//<!--VERSION: .9 **||**-->

if($_GET['action']=='exportall' || $_GET['action']=='exportemailonly')
{
    include("includes/functions_db.php");
    if($_GET['action']=='exportall'){
        $sql="SELECT A.*, B.department_name, C.position_name FROM users A, user_departments B, user_positions C WHERE A.position_id=C.id AND A.department_id=B.id ORDER BY A.department_id, A.lastname, A.firstname";
    } elseif($_GET['action']=='exportemailonly') {
        $sql="SELECT A.*, B.department_name, C.position_name FROM users A, user_departments B, user_positions C WHERE A.email<>'' AND A.position_id=C.id AND A.department_id=B.id ORDER BY A.department_id, A.lastname, A.firstname";
    }
    $dbStaff=dbselectmulti($sql);
    if($dbStaff['numrows']>0)
    {
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename='employees.csv'");
        print "First Name,Last Name,Department,Title,Phone,Extension,Cell,Email,Master Key,Sub-master Key\n";
        foreach($dbStaff['data'] as $staff)
        {
            print stripslashes($staff['firstname']).",";    
            print stripslashes($staff['lastname']).",";    
            print stripslashes($staff['department_name']).",";    
            print stripslashes($staff['position_name']).",";    
            print stripslashes($staff['business']).",";    
            print stripslashes($staff['extension']).",";    
            print stripslashes($staff['cell']).",";    
            print stripslashes($staff['email']).",";    
            print stripslashes($staff['master_key']).",";    
            print stripslashes($staff['submaster_key'])."\n";    
        }
        
    } else {
        ?>
        <html>
        <body>
        <h2>There was a problem and no staff members where found.</h2>
        <?php 
        echo $dbStaff['error']."<br>";
        ?>
        </body>
        </html>
        <?php
        
    }
    die();
} elseif($_GET['action']=='exportwiki') {
    include("includes/functions_db.php");
    include("includes/config.php");
    exportWiki();
    die();
} elseif($_GET['action']=='emailsig') {
    include("includes/functions_db.php");
    include("includes/config.php");
    emailSig();
    die();
} else {
    include("includes/mainmenu.php") ;

    if ($_POST['submit']=='Add'){
        save_user('insert');
    } elseif ($_POST['submit']=='Change Password'){
        update_password(); 
    } elseif ($_POST['submit']=='Update'){
       save_user('update'); 
    } elseif ($_POST['submit']=='Set Permissions'){
       save_permissions(); 
    } elseif ($_POST['submit']=='Set Publications'){
       save_publications(); 
    } elseif ($_POST['submit']=='Save Sites'){
       save_sites(); 
    } elseif ($_POST['submit']=='Save Rooms'){
       save_rooms(); 
    } elseif ($_POST['submit']=='Set Groups'){
       save_groups(); 
    } elseif ($_POST['submit']=='Reset Password'){
       save_reset(); 
    } else { 
        show_users();
    }
}
function show_users()
{
    global $siteID, $departments, $employeepositions, $carriers;
    
    $sql="SELECT id, extension FROM phone_extensions ORDER BY extension ASC";
    $dbExtensions=dbselectmulti($sql);
    $extensions[0]='Please select';
    if($dbExtensions['numrows']>0)
    {
        foreach($dbExtensions['data'] as $e)
        {
            $extensions[$e['id']]=$e['extension'];    
        }
    }
    
    $sql="SELECT * FROM core_permission_groups ORDER BY group_name";
    $dbPgroups=dbselectmulti($sql);
    $pgroups[0]='Please choose';
    if($dbPgroups['numrows']>0)
    {
        foreach($dbPgroups['data'] as $p)
        {
            $pgroups[$p['id']]=$p['group_name'];    
        }
    }
    $id=($_GET['userid']);
    if ($_GET['action']=='add' || ($_GET['action']=='edit')){
     if ($_GET['action']=='add') {
        $button="Add";
        $allpubs=0;
        $position=0;
        $department=0;
        $pims=1;
        $intranet=1;
        $deleteintranetnews=0;
        $deleteintranetevents=0;
        $deleteintranetdocs=0;
        $editintranetnews=1;
        $editintranetevents=1;
        $editintranetdocs=1;
        $masterkey=0;
        $submasterkey=0;
        $carrier=0;
        $pgroup=0;
        $kiwi=0;
        $mango=1;
        $guava=0;
        $papaya=0;
        $debug=0;
        $tempemployee=0;
        $simplemenus=0;
        $superuser=0;
        $extensionid=0;
      } elseif ($_GET['action']=='edit'){
        $sql="SELECT * FROM users WHERE id=$id";
        $dbresult=dbselectsingle($sql);
        $record=$dbresult['data'];
        $firstname=stripslashes($record['firstname']);
        $lastname=stripslashes($record['lastname']);
        $middlename=stripslashes($record['middlename']);
        $home=stripslashes($record['home']);
        $business=stripslashes($record['business']);
        $cell=stripslashes($record['cell']);
        $carrier=stripslashes($record['carrier']);
        $fax=stripslashes($record['fax']);
        $extension=stripslashes($record['extension']);
        $email=stripslashes($record['email']);
        $emaildomain=stripslashes($record['emaildomain']);
        $username=stripslashes($record['username']);
        $weight=stripslashes($record['weight']);
        $summary=stripslashes($record['summary']);
        $pims=stripslashes($record['pims_access']);
        $intranet=stripslashes($record['intranet_access']);
        $deleteintranetnews=stripslashes($record['delete_intranet_news']);
        $deleteintranetevents=stripslashes($record['delete_intranet_events']);
        $deleteintranetdocs=stripslashes($record['delete_intranet_docs']);
        $editintranetnews=stripslashes($record['edit_intranet_news']);
        $editintranetevents=stripslashes($record['edit_intranet_events']);
        $editintranetdocs=stripslashes($record['edit_intranet_docs']);
        $allpubs=stripslashes($record['allpubs']);
        $admin=stripslashes($record['admin']);
        $mango=stripslashes($record['mango']);
        $kiwi=stripslashes($record['kiwi']);
        $guava=stripslashes($record['guava']);
        $papaya=stripslashes($record['papaya']);
        $pineapple=stripslashes($record['pineapple']);
        $mugshot=stripslashes($record['mugshot']);
        $emailpassword=stripslashes($record['email_password']);
        $netpassword=stripslashes($record['network_password']);
        $notes=stripslashes($record['notes']);
        $tempemployee=$record['temp_employee'];
        $department=$record['department_id'];
        $position=$record['position_id'];
        $masterkey=$record['master_key'];
        $submasterkey=$record['submaster_key'];
        $pgroup=$record['permission_group'];
        $simpletable=$record['simple_tables'];
        $vdsalesid=$record['vision_data_sales_id'];
        $vdsalesname=$record['vision_data_sales_name'];
        $debug=$record['debug_user'];
        $phonepunchdown=$record['phone_punchdown'];
        $simplemenus=$record['simple_menu'];
        $extensionid=$record['extension_id'];
        $superuser=$record['super_user'];
        $bctitle=stripslashes($record['businesscard_title']);
        $button="Update";
    }
    
    
    print "<form method=post enctype='multipart/form-data'>\n";
    print "<div id='tabs'>\n"; //begins wrapper for tabbed content
        
    print "<ul id='userInfo'>\n";
       print "<li><a href='#basics'>Basics</a></li>\n";   
       print "<li><a href='#advanced'>Advanced</a></li>\n";  
    print "</ul>\n";
 
    print "<div id='basics'>\n";
    make_select('department',$departments[$department],$departments,'Department');
    make_select('position',$employeepositions[$position],$employeepositions,'Position');
    make_text('firstname',$firstname,'First Name','',50);
    make_text('middlename',$middlename,'Middle Name','',50);
    make_text('lastname',$lastname,'Last Name','',50,false,false,'','','createUsername();');
    make_text('bctitle',$bctitle,'Title for business cards','Enter a title as it would appear on business cards and email signatures',50);
    make_file('mugshot','Picture','',$mugshot);
    make_text('email',$email,'Email Address','',50);
    make_text('business',$business,'Office Number','This is the full number, not just the extention.','20');
    make_select('extension_id',$extensions[$extensionid],$extensions,'Extension','Select the extension for this employee');
    make_text('extension',$extension,'Extension Number','This is the internal office extention.','10');
    make_text('home',$home,'Home Number');
    make_select('carrier',$carriers[$carrier],$carriers,'Cell Carrier','Select the cell service for this number');
    make_text('cell',$cell,'Cell Number');
    make_checkbox('tempemployee',$tempemployee,'Temp Employee',' Check this an employee who is a temp or contract worker.');
    make_number('hours',$hours,'Weekly Hours');
    make_text('vdsalesid',$vdsalesid,'Vision Data Sales','Enter the sales id of this employee if they are a salesperson.');
    make_text('vdsalesname',$vdsalesname,'Vision Data Sales Name','Enter the vision data sales name exactly as it appears in vision data records.');
    if(checkPermission(1,'function'))
    {
        make_text('emailpassword',$emailpassword,'Email Password','Password for email system');
        make_text('netpassword',$netpassword,'Network Password','Custom network password that user uses');
        make_select('pgroup',$pgroups[$pgroup],$pgroups,'Permission Group','Base set of permissions for this user');
    } else {
        make_hidden('emailpassword',$emailpassword);
        make_hidden('netpassword',$netpassword);
        make_hidden('pgroup',$pgroup);
    }
    print "</div>\n";
    
    print "<div id='advanced'>\n";
    make_text('phonepunchdown',$phonepunchdown,'Phone Punchdown','Block where phone ext is punched down. --will be changed in the future');
    print "<div class='label'>Intranet</div>\n";
    print "<div class='input'>\n";
    print input_checkbox('intranetaccess',$intranet)." Can log in and access the intranet<br />";
    print input_checkbox('editintranetnews',$editintranetnews). "Can add/edit news items in the intranet<br />";
    print input_checkbox('editintranetevents',$editintranetevents). "Can add/edit events in the intranet<br />";
    print input_checkbox('editintranetdocs',$editintranetdocs). "Can add/edit documents in the intranet<br />";
    print input_checkbox('deleteintranetnews',$deleteintranetnews). "Can delete news items in the intranet<br />";
    print input_checkbox('deleteintranetevents',$deleteintranetevents). "Can delete events in the intranet<br />";
    print input_checkbox('deleteintranetdocs',$deleteintranetdocs). "Can delete documents in the intranet<br />";
    print "</div>\n";
    print "<div class='clear'></div>\n";
    print "<div class='label'>Applications</div>\n";
    print "<div class='input'>\n";
    print input_checkbox('mango',$mango)." Grant access to Mango (production) application<br />";
    print input_checkbox('kiwi',$kiwi). " Grant access to Kiwi (advertising) application<br />";
    print input_checkbox('guava',$guava). " Grant access to Guava (editorial) application<br />";
    print input_checkbox('papaya',$papaya). " Grant access to Papaya (circulation) application<br />";
    print input_checkbox('pineapple',$pineapple). " Grant access to Pineapple (business) application<br />";
    print "</div>\n";
    print "<div class='clear'></div>\n";
    print "<div class='label'>Building Keys</div>\n";
    print "<div class='input'>\n";
    print input_checkbox('submasterkey',$submasterkey)." Has a sub-master key<br />";
    print input_checkbox('masterkey',$masterkey). " Has a master key<br />";
    print "</div>\n";
    print "<div class='clear'></div>\n";
    make_checkbox('summary',$summary,'Summary',' Receive daily benchmark summary');
    make_checkbox('allpubs',$allpubs,'All Pubs',' Auto enable all new publications');
    make_checkbox('superuser',$superuser,'Super User','Check if this user is a tech \'super-user\'/ IT Liason for purposes of tech support.');
    if(checkPermission(1,'function'))
    {
        make_checkbox('admin',$admin,'Admin',' User is an admin');
        make_checkbox('debug',$debug,'Debugger',' Check if this user is allowed to see debugging messages.');
    } else {
        make_hidden('admin',$admin);
        make_hidden('debug',$debug);
    }
    make_checkbox('simpletables',$simpletable,'Simple Tables',' Check this for simple tables if the user is having trouble viewing forms in Internet Explorer');
    make_checkbox('simplemenus',$simplemenus,'Simple Menus',' Check this to display the simplified menu system');
    make_text('username',$username,'Username');
    print '<div class="label">Password</div><div class="input">';
    if ($_GET['action']=='add')
    {
        print input_text('password','password','50').'</div>';
    } else {
        print "<a href=\"?action=password&staffid=$id&where=$_GET[where]&order=$_GET[order]\">Change password</a></div>";
    }
    print '<div class="clear"></div>';
    make_textarea('notes',$notes,'Notes','Notes about employee',60,10);
    print "</div>\n";
    
    print "</div>\n";
    make_submit('submit',$button);
    make_hidden('userid',$id);
    print '</div>';
    ?>
        <script type='text/javascript'>
        $(function() {
            $( '#tabs' ).tabs();
        });
        </script>
    <?php 
  } elseif ($_GET['action']=="password"){
    change_password($id);
  } elseif ($_GET['action']=='delete'){
    delete_user();
  } elseif ($_GET['action']=='permissions') {
    permissions();   
  } elseif ($_GET['action']=='pubs') {
    publications();   
  } elseif ($_GET['action']=='rooms') {
    rooms();   
  } elseif ($_GET['action']=='sites') {
    sites();   
  } elseif ($_GET['action']=='groups') {
    groups();   
  } elseif ($_GET['action']=='resetpassword') {
    reset_password();   
  } else {
   
    $sql="SELECT * FROM users ORDER BY lastname";
    $dbUsers=dbselectmulti($sql);
    
    tableStart("<a href='?action=add'>Add new staff member</a>,<a href='?action=exportall'>Export All Employees</a>,<a href='?action=exportemailonly'>Export Employees with email addresses</a>,<a href='?action=exportwiki'>Export to Wiki</a>","Name,Username",11);
    
    if ($dbUsers['numrows']>0)
    {
        foreach ($dbUsers['data'] as $user) {
            $id=$user['id'];
            $firstname=stripslashes($user['firstname']);
            $lastname=stripslashes($user['lastname']);
            $username=stripslashes($user['username']);
            print "<tr><td><a href='?action=edit&userid=$id'>$lastname, $firstname</a></td>";
            //print "<td><a href='?action=password&userid=$id'>Change password</a></td>";
            print "<td>$username</td>";
            print "<td><a href='?action=edit&userid=$id'>Edit</a></td>";
            print "<td><a href='?action=groups&userid=$id'>Groups</a></td>";
            print "<td><a href='?action=rooms&userid=$id'>Chat Rooms</a></td>";
            print "<td><a href='?action=pubs&userid=$id'>Publications</a></td>";
            if (checkPermission('3','item'))
            {
                print "<td><a href='?action=permissions&userid=$id'>Permissions</a></td>";
            } else {
                print "<td></td>";
            }
            if (checkPermission('27','item'))
            {
                print "<td><a href='?action=resetpassword&userid=$id'>Reset Password</a></td>";
            } else {
                print "<td></td>";
            }
            if (checkPermission('23','item'))//grant site access
            {
                print "<td><a href='?action=sites&userid=$id'>Sites</a></td>";
            } else {
                print "<td></td>";
            }
            print "<td><a href='?action=emailsig&userid=$id'>Download Email Signature</a></td>";
            print "<td><a href='?action=delete&userid=$id' class='delete'>Delete</a></td></tr>";       
        }
    }
    tableEnd($dbUsers);
  }
    

}

function permissions()
{
    $userid=$_GET['userid'];
    $sql="SELECT * FROM users WHERE id=$userid";
    $dbUser=dbselectsingle($sql);
    $user=$dbUser['data'];
    $sql="SELECT * FROM core_permission_list WHERE type='cms' ORDER BY displayname";
    $dbPermissions=dbselectmulti($sql);
    $sql="SELECT * FROM user_permissions WHERE user_id=$userid";
    $dbStaff=dbselectmulti($sql);
    $staffpermissions=$dbStaff['data'];
    if ($dbPermissions['numrows']>0)
    {
        print "<h4>Please select permissions for $user[firstname] $user[lastname]:</h4>";
        print "<input type='button' value='Select All' onClick=\"checkAllCheckboxes('permList',true);\">\n";  
        print "<input type='button' value='Deselect All' onClick=\"checkAllCheckboxes('permList',false);\"><br />\n";  
        print "<div id='permList'>\n";
        print "<form method=post>\n";
        $i=1;
        //split 3 columns
        $col=round($dbPermissions['numrows']/3,0);
        print "<div style='float:left;width:250px;margin-right:10px;'>\n";
        foreach($dbPermissions['data'] as $permission)
        {
            $pvalue=0;
            if ($dbStaff['numrows']>0)
            {
                foreach($staffpermissions as $staffpermission)
                {
                    if ($permission['id']==$staffpermission['permissionID'])
                    {
                        if ($staffpermission['value']==1)
                        {
                            $pvalue=1;
                        }        
                    }
                }
            }
            print input_checkbox('permission_'.$permission['id'],$pvalue);
            print "<label for='permission_$permission[id]'>&nbsp;&nbsp;".$permission['displayname']."</label><br>";
            if ($i==$col)
            {
                $i=1;
                print "</div>\n";
                print "<div style='float:left;width:250px;margin-right:10px;'>\n";
            } else {
                $i++;
            }
        }
        print "</div><div class='clear'></div>\n";
        print "<div class='label'></div><div class='input'>\n";
        make_hidden('userid',$userid);
        make_submit('submit','Set Permissions');
        print "</form>\n";
        print "</div><div class='clear'></div>\n";
        print "</div>\n";
        
    } else {
       print "Sorry, no permissions have been defined yet.";
    }
    
}

function save_permissions()
{
    $userid=$_POST['userid'];
    //start by deleting all existing permissions for this user
    $sql="DELETE FROM user_permissions WHERE user_id=$userid";
    $dbDelete=dbexecutequery($sql);
    $sql="SELECT * FROM core_permission_list WHERE type='cms' ORDER BY weight";
    $dbPermissions=dbselectmulti($sql);
    $value="";
    foreach ($dbPermissions['data'] as $permission)
    {
        $pvalue=0;
        if ($_POST["permission_$permission[id]"])
        {
            $pvalue=1;
        } else if($permission['auto_enable']==1)
        {
            $pvalue=1;
        }
        $value.="('$permission[id]','$userid','$pvalue'),";
    }
    $value=substr($value,0,strlen($value)-1);
    if($value!='')
    {
        $sql="INSERT INTO user_permissions (permissionID, user_id, value) VALUES $value";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $error='';
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the user permissions.<br>'.$error,'error');
    } else {
        setUserMessage('User permission successfully saved','success');
    }
    redirect("?action=list");
    
}


function publications()
{
    $userid=$_GET['userid'];
    $sql="SELECT * FROM users WHERE id=$userid";
    $dbUser=dbselectsingle($sql);
    $user=$dbUser['data'];
    $sql="SELECT * FROM publications ORDER BY pub_name";
    $dbPublications=dbselectmulti($sql);
    $sql="SELECT * FROM user_publications WHERE user_id=$userid";
    $dbUsers=dbselectmulti($sql);
    if ($dbPublications['numrows']>0)
    {
        print "<h4>Please set publications for $user[firstname] $user[lastname]:</h4>";
        print "<input type='button' value='Select All' onClick=\"checkAllCheckboxes('publist',true);\">\n";  
        print "<input type='button' value='Deselect All' onClick=\"checkAllCheckboxes('publist',false);\"><br />\n";  
        print "<form method=post>\n";
        $i=1;
        //split 2 columns
        $col=round($dbPublications['numrows']/3,0);
        print "<div class='publist' style='float:left;width:250px;margin-right:10px;'>\n";
        foreach($dbPublications['data'] as $publication)
        {
            $pvalue=0;
            if ($dbUsers['numrows']>0)
            {
                foreach($dbUsers['data'] as $userpub)
                {
                    if ($publication['id']==$userpub['pub_id'])
                    {
                        if ($userpub['value']==1)
                        {
                            $pvalue=1;
                        }        
                    }
                }
            }
            print input_checkbox('publication_'.$publication['id'],$pvalue);
            print "&nbsp;&nbsp;<label for='publication_$publication[id]'>".$publication['pub_name']."</label><br>";
            if ($i==$col)
            {
                $i=1;
                print "</div>\n";
                print "<div class='publist' style='float:left;width:250px;margin-right:10px;'>\n";
            } else {
                $i++;
            }
        }
        print "</div><div class='clear'></div>\n";
        make_hidden('userid',$userid);
        print "<div class='label'></div><div class='input'>\n";
        make_submit('submit','Set Publications');
        print "</form>\n";
        print "</div>\n";
        print "<div class='clear'></div></div>\n";
    } else {
       print "Sorry, no publications have been defined yet.";
    }
    
}

function save_publications()
{
    $userid=$_POST['userid'];
    //start by deleting all existing permissions for this user
    $sql="DELETE FROM user_publications WHERE user_id=$userid";
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
        $value.="('$publication[id]','$userid','$pvalue'),";
    }
    $value=substr($value,0,strlen($value)-1);
    if($value!='')
    {
        $sql="INSERT INTO user_publications (pub_id, user_id, value) VALUES $value";
        $dbinsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $error='';
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the publication for the user.<br>'.$error,'error');
    } else {
        setUserMessage('Publication for the user successfully saved','success');
    }
    redirect("?action=list");//&where=$_POST[where]&order=$_POST[order]");
    
}

function sites()
{
    $userid=$_GET['userid'];
    $sql="SELECT * FROM core_sites ORDER BY site_name";
    $dbSites=dbselectmulti($sql);
    if ($dbSites['numrows']>0)
    {
        print "<h2>Select the sites that this user is a member of.</h2>\n";
        print "<form method=post>\n";
        foreach($dbSites['data'] as $site)
        {
            //see if the employee has this one
            $sql="SELECT * FROM user_sites WHERE site_id=$site[id] AND user_id=$userid";
            $dbExisting=dbselectsingle($sql);
            if ($dbExisting['numrows']>0){$checked=1;}else{$checked=0;}
            print input_checkbox('site_'.$site['id'],$checked)." <label for='site_$site[id]'>".$site['site_name']."</label><br />\n";    
        }
        make_hidden('userid',$userid);
        make_submit('submit','Save Sites');
        print "</form>\n";
    } else {
        print "<a href='?action=list'>Sorry, there are no sites configured yet. Click to return to user list.</a><br />\n";
    } 
    
}

function save_sites()
{
    $userid=$_POST['userid'];
    $values="";
    //clear existing
    $sql="DELETE FROM user_sites WHERE user_id=$userid";
    $dbDelete=dbexecutequery($sql);
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,5)=='site_')
        {
            $id=str_replace("site_","",$key);
            $values.="($userid,$id), ";    
        }
    }
    $values=substr($values,0,strlen($values)-2);
    if($values!='')
    {
        $sql="INSERT INTO user_sites (user_id, site_id) VALUES $values";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $error='';
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the site for the user.<br>'.$error,'error');
    } else {
        setUserMessage('User site successfully saved','success');
    }
    redirect("?action=list");
}


function rooms()
{
    $userid=$_GET['userid'];
    $sql="SELECT * FROM chat_rooms ORDER BY room_name";
    $dbRooms=dbselectmulti($sql);
    if ($dbRooms['numrows']>0)
    {
        print "<h2>Select the chatrooms that this user should have access to:</h2>\n";
        print "<form method=post>\n";
        foreach($dbRooms['data'] as $room)
        {
            //see if the employee has this one
            $sql="SELECT * FROM user_chatrooms WHERE room_id=$room[id] AND user_id=$userid";
            $dbExisting=dbselectsingle($sql);
            if ($dbExisting['numrows']>0){$checked=1;}else{$checked=0;}
            print input_checkbox('room_'.$room['id'],$checked)." <label for='room_$room[id]'>".$room['room_name']."</label><br />\n";    
        }
        make_hidden('userid',$userid);
        make_submit('submit','Save Rooms');
        print "</form>\n";
    } else {
        print "<a href='?action=list'>Sorry, there are no chat rooms configured yet. Click to return to user list.</a><br />\n";
    } 
    
}
function save_rooms()
{
    $userid=$_POST['userid'];
    $values="";
    //clear existing
    $sql="DELETE FROM user_chatrooms WHERE user_id=$userid";
    $dbDelete=dbexecutequery($sql);
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,5)=='room_')
        {
            $id=str_replace("room_","",$key);
            $values.="($userid,$id), ";    
        }
    }
    $values=substr($values,0,strlen($values)-2);
    if($values!='')
    {
        $sql="INSERT INTO user_chatrooms (user_id, room_id) VALUES $values";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        
    } else {
        $error='';
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the chat rooms for the user.<br>'.$error,'error');
    } else {
        setUserMessage('User chat rooms successfully saved','success');
    }
    redirect("?action=list");  
}



function delete_user()
{
    $id=$_GET['userid'];
    $sql="DELETE from users WHERE id=$id";   
    $result=dbexecutequery($sql);
    $error=$result['error'];
    $sql="DELETE from user_permissions WHERE user_id=$id";   
    $result=dbexecutequery($sql);
    $error.=$result['error'];
    $sql="DELETE from user_publications WHERE user_id=$id";   
    $result=dbexecutequery($sql);
    $error.=$result['error'];
    $sql="DELETE from user_groups_xref WHERE user_id=$id";   
    $result=dbexecutequery($sql);
    $error.=$result['error'];
    $sql="DELETE from user_sites WHERE user_id=$id";   
    $result=dbexecutequery($sql);
    $error.=$result['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem deleting the user.<br>'.$error,'error');
    } else {
        setUserMessage('User successfully deleted','success');
    }
    redirect("?action=list");
}

function reset_password()
{
    $userid=$_GET['userid'];
    print "<form method='post'>\n";
    make_password('userpassword',$_POST['userpassword'],'New password');
    make_password('confirmpassword','','Re-enter');
    make_hidden('userid',$userid);
    make_submit('submit','Reset Password');
    print "</form>\n";    
}

function save_reset()
{
    $userid=$_POST['userid'];
    $password=md5($_POST['userpassword']);
    $confirm=md5($_POST['confirmpassword']);
    //lets see if the new and confirming one are the same
    if ($password===$confirm)
    {
        $sql="UPDATE users SET password='$password' WHERE id=$userid";
        $dbUser=dbexecutequery($sql);
        if ($error!='')
        {
            setUserMessage('There was a problem saving the new password','error');
        } else {
            setUserMessage('New password successfully updated','success');
        }
        redirect("?action=edit&userid=$userid");
               
    } else {
        print "Your new passwords do not match!<br />\n";
        reset_password($userid);
    }
}


function change_password($userid='')
{
    if ($userid=='')
    {
        $userid=$_GET['userid'];
    }
    print "<form method='post'>\n";
    make_password('original','','Original Password');
    make_password('userpassword',$_POST['userpassword'],'New password');
    make_password('confirmpassword','','Re-enter');
    make_hidden('userid',$userid);
    make_submit('submit','Change Password');
    print "</form>\n";
}

function update_password()
{
    $userid=$_POST['userid'];
    $password=md5($_POST['userpassword']);
    $confirm=md5($_POST['confirmpassword']);
    $original=md5($_POST['original']);
    $sql="SELECT password FROM users WHERE id=$userid AND password='$original'";
    $dbExisting=dbselectsingle($sql);
    if($dbExisting['numrows']>0)
    {
        //ok, they entered the correct password
        //lets see if the new and confirming one are the same
        if ($password===$confirm)
        {
            $sql="UPDATE users SET password='$password' WHERE id=$userid";
            $dbUser=dbexecutequery($sql);
            if ($error!='')
            {
                setUserMessage('There was a problem updating the user password','error');
            } else {
                setUserMessage('User password successfully updated','success');
            }
            redirect("?action=edit&userid=$userid");       
        } else {
            print "Your new passwords do not match!<br />\n";
            change_password($userid);
        }
        
    } else {
        print "You entered the wrong original password!<br />\n";
        change_password($userid);
    }
    
} 



function save_user($action)
{
    global $siteID;
    $userid=$_POST['userid'];
    $username=trim(addslashes($_POST['username']));
    $firstname=addslashes($_POST['firstname']);
    $lastname=addslashes($_POST['lastname']);
    $middlename=addslashes($_POST['middlename']);
    //we need the id of this record, so...
    $home=addslashes($_POST['home']);
    $business=addslashes($_POST['business']);
    $cell=addslashes($_POST['cell']);
    $fax=addslashes($_POST['fax']);
    $extensionid=addslashes($_POST['extension_id']);
    $extension=addslashes($_POST['extension']);
    $email=addslashes($_POST['email']);
    $title=addslashes($_POST['title']);
    $netpassword=addslashes($_POST['netpassword']);
    $bctitle=addslashes($_POST['bctitle']);
    
    $emailpassword=addslashes($_POST['emailpassword']);
    $phonepunchdown=addslashes($_POST['phonepunchdown']);
    $notes=addslashes($_POST['notes']);
    $password=md5($_POST['password']);
    $position=$_POST['position'];
    $department=$_POST['department'];
    $carrier=$_POST['carrier'];
    $pgroup=$_POST['pgroup'];
    $vdsalesid=$_POST['vdsalesid'];
    $vdsalesname=$_POST['vdsalesname'];
    $cell=str_replace("-","",$cell);
    $cell=str_replace(" ","",$cell);
    $cell=str_replace("(","",$cell);
    $cell=str_replace(")","",$cell);
    $cell=str_replace(".","",$cell);
    if(strlen($cell)<10){$cell=$GLOBALS['newspaperAreaCode'].$cell;}
    if ($_POST['summary']){$summary=1;}else{$summary=0;}
    if ($_POST['allpubs']){$allpubs=1;}else{$allpubs=0;}
    if ($_POST['admin']){$admin=1;}else{$admin=0;}
    if ($_POST['pimsaccess']){$pims=1;}else{$pims=0;}
    if ($_POST['intranetaccess']){$intranet=1;}else{$intranet=0;}
    if ($_POST['deleteintranetnews']){$deleteintranetnews=1;}else{$deleteintranetnews=0;}
    if ($_POST['deleteintranetevents']){$deleteintranetevents=1;}else{$deleteintranetevents=0;}
    if ($_POST['deleteintranetdocs']){$deleteintranetdocs=1;}else{$deleteintranetdocs=0;}
    if ($_POST['editintranetnews']){$editintranetnews=1;}else{$editintranetnews=0;}
    if ($_POST['editintranetevents']){$editintranetevents=1;}else{$editintranetevents=0;}
    if ($_POST['editintranetdocs']){$editintranetdocs=1;}else{$editintranetdocs=0;}
    if ($_POST['submasterkey']){$submasterkey=1;}else{$submasterkey=0;}
    if ($_POST['masterkey']){$masterkey=1;}else{$masterkey=0;}
    if ($_POST['simpletables']){$simple=1;}else{$simple=0;}
    if ($_POST['debug']){$debug=1;}else{$debug=0;}
    if ($_POST['tempemployee']){$tempemployee=1;}else{$tempemployee=0;}
    if ($_POST['superuser']){$superuser=1;}else{$superuser=0;}
    if ($_POST['simplemenus']){$simplemenus=1;}else{$simplemenus=0;}
    
    if ($_POST['mango']){$mango=1;}else{$mango=0;}
    if ($_POST['kiwi']){$kiwi=1;}else{$kiwi=0;}
    if ($_POST['guava']){$guava=1;}else{$guava=0;}
    if ($_POST['papaya']){$papaya=1;}else{$papaya=0;}
    if ($_POST['pineapple']){$pineapple=1;}else{$pineapple=0;}
    
    if ($action=='insert'){
        $sql="INSERT INTO users (allpubs, admin, username, firstname, middlename, lastname, extension, business,
         home, cell, fax, email, position_id, department_id, password, summary, pims_access,
          intranet_access, delete_intranet_news, delete_intranet_events, delete_intranet_docs,
           carrier, master_key, submaster_key, permission_group, site_id, simple_tables, network_password, 
           email_password, notes, vision_data_sales_id, mango, kiwi, guava, papaya, pineapple, debug_user, 
           temp_employee, phone_punchdown, simple_menu, vision_data_sales_name, super_user, businesscard_title, extension_id) 
          VALUES ('$allpubs', '$admin', '$username', '$firstname', '$middlename', '$lastname', 
          '$extension', '$business', '$home', '$cell', '$fax', '$email', '$position', '$department', '$password', 
          '$summary', '$pims', '$intranet', '$deleteintranetnews', '$deleteintranetevents',
           '$deleteintranetdocs','$carrier', '$masterkey', '$submasterkey', '$pgroup', '$siteID', '$simple', 
           '$netpassword', '$emailpassword', '$notes', '$vdsalesid', '$mango', '$kiwi', '$guava', '$papaya', 
           '$pineapple', '$debug', '$tempemployee', '$phonepunchdown', '$simplemenus', '$vdsalesname', '$superuser', '$bctitle', '$extensionid')";
        $dbresult=dbinsertquery($sql);
        $userid=$dbresult['numrows'];
        $error=$dbresult['error'];
        
        
        //set up the default chat room for this user
        $sql="SELECT * FROM chat_rooms WHERE default_room=1";
        $dbDefault=dbselectsingle($sql);
        if($dbDefault['numrows']>0)
        {
            $roomid=$dbDefault['data']['id'];
            $sql="INSERT INTO user_chatrooms (user_id, room_id) VALUES ('$userid', '$roomid')";
            $dbInsertRoom=dbinsertquery($sql);
            $error.=$dbInsertRoom['error'];
        }
        
   } else {
       $sql="UPDATE users SET email_password='$emailpassword', network_password='$netpassword', notes='$notes', carrier='$carrier', username='$username', mango='$mango', kiwi='$kiwi', guava='$guava', papaya='$papaya', businesscard_title='$bctitle', 
       firstname='$firstname', middlename='$middlename', simple_tables='$simple', lastname='$lastname', business='$business', 
       home='$home', cell='$cell', position_id='$position',  department_id='$department', fax='$fax', email='$email', 
       summary='$summary', allpubs='$allpubs', pims_access='$pims', intranet_access='$intranet', extension_id='$extensionid', 
       delete_intranet_news='$deleteintranetnews', delete_intranet_events='$deleteintranetevents', 
       delete_intranet_docs='$deleteintranetdocs',  edit_intranet_news='$editintranetnews', extension='$extension', 
       submaster_key='$submasterkey',  edit_intranet_events='$editintranetevents', edit_intranet_docs='$editintranetdocs', 
       admin='$admin', master_key='$masterkey', carrier='$carrier', permission_group='$pgroup', 
       vision_data_sales_id='$vdsalesid', debug_user='$debug', temp_employee='$tempemployee', super_user='$superuser', 
       phone_punchdown='$phonepunchdown', simple_menu='$simplemenus', vision_data_sales_name='$vdsalesname' WHERE id=$userid";   
        $dbresult=dbexecutequery($sql);
        $error=$dbresult['error'];
   }
   
   if ($allpubs)
   {
       //need to add a record for each publication
       $sql="DELETE FROM user_publications WHERE user_id=$userid";
       $dbDelete=dbexecutequery($sql);
       $sql="SELECT * FROM publications WHERE site_id=$siteID";
       $dbPublications=dbselectmulti($sql);
       $value="";
       foreach ($dbPublications['data'] as $publication)
       {
           $value.="('$publication[id]','$userid','1'),";
       }
       $value=substr($value,0,strlen($value)-1);
       $sql="INSERT INTO user_publications (pub_id, user_id, value) VALUES $value";
       $dbinsert=dbinsertquery($sql);
   }
   
   if($pgroup!=0)
   {
       $perms=array();
       //we have a default permission group for this user
       //first, lets see if they have any existing permissions
       $sql="SELECT * FROM user_permissions WHERE user_id=$userid AND value=1";
       $dbExisting=dbselectmulti($sql);
       if($dbExisting['numrows']>0)
       {
           foreach($dbExisting['data'] as $existing)
           {
               $perms[]=$existing['permissionID'];
           }
       }
       //now clear all existing permissions for this user
       $sql="DELETE FROM user_permissions WHERE user_id=$userid";
       $dbDelete=dbexecutequery($sql);
       
       //now grab the permissions set up for the selected group
       $sql="SELECT permission_id FROM core_permission_group_xref WHERE group_id=$pgroup AND value=1";
       $dbGperms=dbselectmulti($sql);
       if($dbGperms['numrows']>0)
       {
           foreach($dbGperms['data'] as $gperm)
           {
               //don't add it if we already have it in our list
               if(!in_array($gperm['permission_id'],$perms))
               {
                   $perms[]=$gperm['permission_id'];
               }   
           }
       }
       //finally if the count of permissions is greater than 0, insert those permissions for this user
       if (count($perms)>0)
       {
           $values="";
           foreach($perms as $key=>$permid)
           {
              $values.="('$permid','$userid',1),";    
           }
           $values=substr($values,0,strlen($values)-1);
           if($values!='')
           {
               $sql="INSERT INTO user_permissions (permissionID,user_id,value) VALUES $values";
               $dbInsert=dbinsertquery($sql);
               //print "perm insert sql: $sql<br>".$dbInsert['error'];
           }
       }
      
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
                    $path="artwork/userPics/$siteID/";
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
                    $newname="ticket_".$ticketid."_".$newname;
                    if(processFile($file,$path,$newname) == true) {
                        $sql="UPDATE users SET mugshot='$newname' WHERE id=$userid";
                        $result=dbinsertquery($sql);
                        $error.=$result['error'];
                    } else {
                       $error.= 'There was an error inserting the image named '.$file['name'].' into the database. The sql statement was $sql';  
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
        setUserMessage('There was a problem saving the user.<br>'.$error,'error');
    } else {
        setUserMessage('User successfully saved','success');
    }
    redirect("?action=list");    
}

function groups()
{
    //get all departments
    global $siteID;
    $userid=$_GET['userid'];
    $sql="SELECT * FROM user_groups WHERE site_id=$siteID ORDER BY group_name";
    $dbGroups=dbselectmulti($sql);
    if ($dbGroups['numrows']>0)
    {
        print "<h2>Select the groups that this employee is a member of.</h2>\n";
        print "<form method=post>\n";
        foreach($dbGroups['data'] as $group)
        {
            //see if the employee has this one
            $sql="SELECT * FROM user_groups_xref WHERE group_id=$group[id] AND user_id=$userid";
            $dbExisting=dbselectsingle($sql);
            if ($dbExisting['numrows']>0){$checked=1;}else{$checked=0;}
            print input_checkbox('group_'.$group['id'],$checked)." ".$group['group_name']."<br />\n";    
        }
        make_hidden('user_id',$userid);
        make_submit('submit','Set Groups');
        print "</form>\n";
    } else {
        print "<a href='?action=list'>Sorry, there are no groups configured yet. Click to return to employee list.</a><br />\n";
    } 
}


function save_groups()
{
    $userid=$_POST['user_id'];
    $values="";
    //clear existing
    $sql="DELETE FROM user_groups_xref WHERE user_id=$userid";
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
        $sql="INSERT INTO user_groups_xref (user_id, group_id) VALUES $values";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $error='';
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the group for the user.<br>'.$error,'error');
    } else {
        setUserMessage('User group successfully saved','success');
    }
    redirect("?action=list");
}
  

function exportWiki()
{
    global $departments, $employeepositions;
    
    header('Content-Type: text/txt'); // as original file
    header('Content-Disposition: attachment; filename="users-wiki-'.date("Ymd").$sub.'.txt"');
        
    print "======Organizational Contacts======\n";
    
    //first get all super-users
    print "=====IT Liasons=====\n";
    print "^Person^Position^Department^DID Phone^Extension^Cell^Email^Workstation ID^\n";
    $sql="SELECT * FROM users WHERE super_user=1 ORDER BY lastname, firstname";
    $dbSuper=dbselectmulti($sql);
    if($dbSuper['numrows']>0)
    {
        foreach($dbSuper['data'] as $u)
        {
            //look for any assigned equipment
            $sql="SELECT id, device_name, device_type FROM it_devices WHERE assigned_to=$u[id]";
            $dbDevices=dbselectmulti($sql);
            $devices='';
            if($dbDevices['numrows']>0)
            {
                foreach($dbDevices['data'] as $device)
                {
                    $devices.="[[equipment#$device[device_type]|$device[device_name]]]\\\\ ";    
                }
            }
            print "|".stripslashes($u['lastname'].', '.$u['firstname'])." |";   
            print $employeepositions[$u['position_id']]." |";   
            print $departments[$u['department_id']]." |";   
            print $u['business']." |";   
            print $u['extension']." |";   
            print $u['cell']." |";   
            print "[[mailto:".stripslashes($u['email'])."|".stripslashes($u['email'])."]] |"; 
            print $devices." | \n";  
        }    
    }
    
    //now get all department heads
    print "=====Department Heads=====\n";
    print "^Person^Position^Department^DID Phone^Extension^Cell^Email^Workstation ID^\n";
    $sql="SELECT A.* FROM users A, user_positions B WHERE B.director=1 AND A.position_id=B.id ORDER BY A.lastname, A.firstname";
    $dbSuper=dbselectmulti($sql);
    if($dbSuper['numrows']>0)
    {
        foreach($dbSuper['data'] as $u)
        {
            //look for any assigned equipment
            $sql="SELECT id, device_name, device_type FROM it_devices WHERE assigned_to=$u[id]";
            $dbDevices=dbselectmulti($sql);
            $devices='';
            if($dbDevices['numrows']>0)
            {
                foreach($dbDevices['data'] as $device)
                {
                    $devices.="[[equipment#$device[device_type]|$device[device_name]]]\\\\ ";    
                }
            }
            print "|".stripslashes($u['lastname'].', '.$u['firstname'])." |";   
            print $employeepositions[$u['position_id']]." |";   
            print $departments[$u['department_id']]." |";   
            print $u['business']." |";   
            print $u['extension']." |";   
            print $u['cell']." |";   
            print "[[mailto:".stripslashes($u['email'])."|".stripslashes($u['email'])."]] |"; 
            print $devices." | \n";  
        }    
    }
    
    print "===== Employees =====\n";
    print "** :!: Keep sorted by department then alphabetical**\n";

    //now go through each department and show users
    foreach($departments as $deptid=>$deptname)
    {
        print "====$deptname====\n";
        //now get all department heads
        print "^Person^Position^DID Phone^Extension^Cell^Email^Workstation ID^\n";
        $sql="SELECT * FROM users WHERE department_id=$deptid ORDER BY lastname, firstname";
        $dbSuper=dbselectmulti($sql);
        if($dbSuper['numrows']>0)
        {
            foreach($dbSuper['data'] as $u)
            {
                //look for any assigned equipment
                $sql="SELECT id, device_name, device_type FROM it_devices WHERE assigned_to=$u[id]";
                $dbDevices=dbselectmulti($sql);
                $devices='';
                if($dbDevices['numrows']>0)
                {
                    foreach($dbDevices['data'] as $device)
                    {
                        $devices.="[[equipment#$device[device_type]|$device[device_name]]]\\\\ ";    
                    }
                }
                print "|".stripslashes($u['lastname'].', '.$u['firstname'])." |";   
                print $employeepositions[$u['position_id']]." |";   
                print $u['business']." |";   
                print $u['extension']." |";   
                print $u['cell']." |";   
                print "[[mailto:".stripslashes($u['email'])."|".stripslashes($u['email'])."]] |"; 
                print $devices." | \n";  
            }    
        }
    }
}
  
function emailSig()
{
    global $departments;
    $id=intval($_GET['userid']);
    $sql="SELECT * FROM users WHERE id=$id";
    $dbUser=dbselectsingle($sql);
    $user=$dbUser['data'];

    $fname=strtolower(stripslashes($user['firstname'].$user['lastname']));
    header('Content-Type: text/txt'); // as original file
    header('Content-Disposition: attachment; filename="'.$fname.'-sig.html"');
    ?>
    
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<body>
<div align=left>
    <table style="border: #deeede 1px solid;" border=0 cellspacing=0 cellpadding=6 width=auto>
        <tbody>
            <tr>
                <td style="text-align: center;" width=auto>
                    <div style="padding-top: 4px;"><a href="http://www.idahopress.com" target=_blank><img border=0 alt="Idaho Press-Tribune" src="http://www.idahopress.com/app/artwork/IPT_LogoV_empower-fullcolor.jpg" width=120 height=52></a></div>
                    <div style="padding-top: 24px;"><a href="http://www.facebook.com/Idaho.Press.Tribune" target=_blank><img border=0 alt="Follow us on Facebook" src="http://www.idahopress.com/app/artwork/ico-fb.png" width=16 height=16></a> <a href="http://www.twitter.com/IdahoPressTrib" target=_blank><img border=0 alt="Follow us on Twitter" src="http://www.idahopress.com/app/artwork/ico-tw.png" width=16 height=16></a> <a href="https://plus.google.com/104139372003030790047" target=_blank><img border=0 alt="Follow us on Google+" src="http://www.idahopress.com/app/artwork/ico-g+.jpg" width=16 height=16></a></div>
                </td>
                <td style="line-height: 14px; font-size: 10px; font-family: helvetica; color: #79a97f;" cellpadding=4 width=auto>
                    <div style="line-height: 16px; font-size: 13px; font-weight: bold; color: #1c6635;"><?php echo stripslashes($user['firstname']); ?> <?php echo stripslashes($user['lastname']); ?></div>
                    <div style="font-size: 13px; font-style: italic;"><?php echo stripslashes($user['businesscard_title']); ?></div>
                    <div><a style="text-decoration: none; color: #79a97f;" href="mailto:<?php echo stripslashes($user['email']); ?>" target=_blank>E-Mail: <?php echo stripslashes($user['email']); ?></a></div>
                    <div>Ofc: <a style="text-decoration: none; color: #79a97f;" href="tel:<?php echo stripslashes($user['business']); ?>" target=_blank value="+1<?php echo stripslashes($user['business']); ?>"><?php echo stripslashes($user['business']); ?></a></div>
                    <div>Fax: <a style="text-decoration: none; color: #79a97f;" href="tel:<?php echo stripslashes($user['fax']); ?>" target=_blank value="+1<?php echo stripslashes($user['fax']); ?>"><?php echo stripslashes($user['fax']); ?></a></div>
                    <div><a style="text-decoration: none; color: #79a97f;" href="http://www.idahopress.com" target=_blank>Idaho Press-Tribune</a></div>
                    <div><a style="text-decoration: none; color: #79a97f;" href="https://maps.google.com/maps?q=1618+N.+Midland+Blvd.,+Nampa,+ID+83651&hl=en&ll=43.603954,-116.592373&spn=0.024581,0.034075&sll=43.586063,-116.562893&sspn=0.196707,0.272598&t=v&hnear=1618+N+Midland+Blvd,+Nampa,+Idaho+83651&z=15" target=_blank>1618 N. Midland Blvd., Nampa, ID 83651</a></div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</body>
</html>
<?php
}
  
footer();
?>