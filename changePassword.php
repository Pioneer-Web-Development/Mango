<?php
//<!--VERSION: 1.0 ||**||-->//
//this is a simple script to change the user's password
include("includes/mainmenu.php");

print "<div style='width:350px;height:150px;margin:auto;margin-top:100px;border:1px solid black;padding:10px;background-color:#CCC;'>\n";
if ($_POST['submit']=='Change Password')
{
    update_password();    
} else {
    change_password();
}
print "</div>\n";

function change_password($userid='',$error='')
{
    if ($userid=='')
    {
        $userid=$_SESSION['cmsuser']['userid'];
    }
    if ($error=='match')
    {
        print "<h2>Your password has been successfully updated!</h2>\n";
    } else {
        print "<form method='post'>\n";
        make_password('original','','Original Password');
        make_password('userpassword',$_POST['userpassword'],'New password');
        make_password('confirmpassword','','Re-enter');
        make_hidden('userid',$userid);
        if ($error!='')
        {
            print "<p style='font-color:red;font-weight:bold;'>";
            if ($error=='nomatch')
            {
                print "Your new passwords do not match";
            } else {
                print "You entered the incorrect original password.";
            }
            print "</p>\n";
        }
        make_submit('submit','Change Password');
        print "</form>\n";
    }
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
            change_password($userid,'match');
                   
        } else {
            change_password($userid,'nomatch');
        }
        
    } else {
        change_password($userid,'bad');
    }
    
} 

print "</body>\n";
print "</html>\n";
dbclose();
?>
