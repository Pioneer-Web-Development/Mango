<?php
//<!--VERSION: 1.0 **||**-->
session_start();
include '../includes/functions_db.php';
include("../includes/functions_common.php");

                            
if ($_POST['action']=='login') {
    $record=check_user();
    if ($record['errors']!='') {
       show_login($record['errors']);
    } else {
        populate_session($record);
    }
} elseif($_POST['action']=='register')
{
    register_user();
} elseif($_GET['activate']){
    activate_user();
} else {
    //check for cookie first
    if ($_COOKIE['gotcha']){
        //descript with the given key
        $userid=$_COOKIE['gotcha'];
        $sql="SELECT * FROM gotcha_players WHERE id='$userid'"; 
        //for right now we are disabling the other intranet app
        $dbresult=dbselectsingle($sql);
        $rc=$dbresult['numrows'];
        $record=$dbresult['data'];
        if (isset($_GET['r'])){$refer=$_GET['r'];}else{$r='';}
        populate_session($record,$r);
    } else {
        show_login();
    }
}

function register_user()
{
   $email=addslashes($_POST['email']);
   $name=addslashes($_POST['name']);
   $password=addslashes($_POST['password']);
   //check first to see if that email is already in use
   //if it is, send the password to that email account
   $sql="SELECT * FROM gotcha_players WHERE email='$email'";
   $dbCheck=dbselectsingle($sql);
   if($dbCheck['numrows']>0)
   {
       $user=$dbCheck['data'];
       //yep, found one
       
        $to      = $email;
        $subject = 'Your Gotcha password';
        $message = 'Hey, that account is registered already! In case you forgot, here is your password: '.stripslashes($user['password']);
        $headers = 'From: jhansen@idahopress.com' . "\r\n" .
            'Reply-To: jhansen@idahopress.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $result=mail($to, $subject, $message, $headers);
        show_login('That email address is already registered. To help out, we sent a copy of your password to your email account.'); 
   } else {
       //adding a new user, they will have to confirm themselves
       $sql="INSERT INTO gotcha_players (name, email, password, killed, kills, activated, current) VALUES ('$name', '$email', 
       '$password', 0, 0, 0, 1)";
       $dbInsert=dbinsertquery($sql);
       $playerid=$dbInsert['insertid'];
       $to      = $email;
        $subject = 'Please activate your Gotcha account';
        $message = 'Please click on this link to activate your account. If clicking doesnt work, copy and paste it into your browser address bar: <a href="http://10.56.1.10/gotcha/index.php?action=activate&id='.stripslashes($user['id']).'">http://10.56.1.10/gotcha/index.php?action=activate&id='.stripslashes($user['id']).'</a>';
        $headers = 'From: jhansen@idahopress.com' . "\r\n" .
            'Reply-To: jhansen@idahopress.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $result=mail($to, $subject, $message, $headers);
        print "<p>You will be receing an email momentarily. Please click on the activation link to activate your account.</p>";
        
   }
}

function activate_user()
{
    $id=intval($_GET['id']);
    $sql="UPDATE gotcha_players SET activated=1 WHERE id=$id";
    $dbUpdate=dbexecutequery($sql);
    $sql="SELECT * FROM gotcha_players
    $_SESSION['gotcha']['id']=$record['id'];
    $_SESSION['gotcha']['name']=$record['name'];
    $_SESSION['gotcha']['email']=$record['email'];
    
}

function show_login($errors = '')
{
?>
     <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
            "http://www.w3.org/TR/html4/strict.dtd">
     <html>
     <head>

    <style type="text/css">
    <!--
    body {
        text-align:center;
        font-family:Trebuchet MS, sans-serif;
        font-size:12px;
    }
    .label {
        float: left;
        width: 180px;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 16px;
        font-weight: bold;
        color: #000000;
        margin-top: 5px;
        margin-bottom: 5px;
        text-align: right;
        padding-right: 15px;
    }
    .mango{
        color:#AC1D23;
        font-weight:bold;
        font-size:64px;
        font-family:Tahoma;
        margin-left:20px;
        float:left;
        padding-top:10px;
    }
    .input {
        float: left;
        width: 200px;
        border-left-width: 1px;
        border-left-style: solid;
        border-left-color: #CCCCCC;
        padding-left: 10px;
        text-align: left;
        margin-top: 5px;
        margin-bottom: 5px;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 16px;
        color: #000000;
    }
    #login {
        width: 480px;
        margin-top: 150px;
        margin-right: auto;
        margin-left: auto;
        background-color: #efefef;
        padding: 5px;
        border: 1px solid #000000;
    }
    #logo{
        position:relative;
        top:-50px;
        left:-70px;
        z-index:500;
        height:120px;
        width:200px;
        float:left;
    }
    .clear {
        clear: both;
        height: 0px;
    }
    .loginbutton{
        padding:5px;
        background-color: #FFDB4F;
        color:#AC1D23;
        font-family:Trebuchet MS;
        font-weight:bold;
        font-size:16px; 
    }
    .loginbutton:hover{
        background-color: #AC1D23;
        color:#FFDB4F; 
    }
    -->
    </style>
    <script type='text/javascript' src='../includes/jscripts/jquery-1.6.1.min.js?1305909836'></script>
    <script>
    function toggleName(val)
    {
        if($('#register').is(":checked"))
        {
            $('#nameblock').show();
            $('#action').val('register');
        } else {
            $('#nameblock').hide();
            $('#action').val('login');
        }  
    }
    </script>
    </head>
    <body>
    <?php
    include '../includes/functions_formtools.php';
    print "<div id='login'>\n";
    print "<div class='mango'>GOTCHA!</div>\n";
    print "<div class='clear'></div>";
    print "<div style='font-size:20px;font-weight:bold;'>Welcome to the game<br>of Life and Death!</div>\n";
    print "<div class='clear'></div>\n";
    print "<form name='loginform' method='post'>\n";
    make_text('email','','Email','',20);
    make_password('password','','Password','');
    make_checkbox('register',0,'Register','Create new account','','toggleName()');
    print "<div id='nameblock' style='display:none;'>";
    make_text('name','','Name','',20);
    print "</div>";
    if ($errors<>'') {
            print '<div class="label">Errors</div><div class="input">'.$errors.'</div><div class="clear"></div>';
        }
    print "<div style='margin-left:auto;margin-right:auto;'><input type=submit value='LOG IN' class='loginbutton'></div>\n";
    make_hidden('action','login');
    make_checkbox('remember',1);
    $agent=$_SERVER['HTTP_USER_AGENT'];
    print "<p><small>Connecting from ".$_SERVER['REMOTE_ADDR']."</small></p>";
    print '</div>'; 
    
   // print_r($_SERVER);
}
  
function populate_session($record,$refer='')
{
    $value=$record['id'];
    //die("going to be writing cookie with $value");
    setcookie("gotcha", $value, time()+3600*24*30);  
    $_SESSION['gotcha']['loggedin']=true;
    $_SESSION['gotcha']['id']=$record['id'];
    $_SESSION['gotcha']['name']=$record['name'];
    $_SESSION['gotcha']['email']=$record['email'];
    echo '<script type="text/javascript">';
    echo 'window.location.href="'.$dest.'";';
    echo '</script>';
    echo '<noscript>';
    echo '<meta http-equiv="refresh" content="0;url=gotcha.php" />';
    echo '</noscript>';
    
    
}

function check_user()
{
    $errors = array();
    $email=addslashes($_POST['email']);
    $sql="SELECT * FROM gotcha_players WHERE email='$email'"; 
    $dbresult=dbselectsingle($sql);
    $rc=$dbresult['numrows'];
    $record=$dbresult['data'];
    $namecheck=$record['name'];
    $password=$record['password'];
    $passcheck=addslashes($_POST['password']);

    if ($rc==1) {
        //this is a valid user, so...
        //now to check password validity
        if ($passcheck==$password) {
        //lets populate all the session information here
            populate_session($record);
        } else {
            $record['errors']="Please enter a valid username and/or password!";
        }
        
    } else {
        $record['errors']='Please enter a valid username and/or password.';
    }
    return $record;
} 



 print '</form>';
dbclose();
?>
</body>
</html>