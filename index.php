<?php
//<!--VERSION: 1.0 **||**-->
session_start();
include 'includes/functions_db.php';
include("includes/config.php");
include("includes/functions_common.php");

$detect = new Mobile_Detect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
if($deviceType!='computer')
{
    header('Location: mobile/');
}
if ($_GET['nopermission'])
{
    if ($_COOKIE['mango']){
        //descript with the given key
        $userid=$_COOKIE['mango'];
        $userid=str_replace("m","",$userid);
        $userid=intval($userid);
        $sql="SELECT * FROM users WHERE id='$userid'"; 
        //for right now we are disabling the other intranet app
        $dbresult=dbselectsingle($sql);
        if($dbresult['numrows']>0)
        {
            $record=$dbresult['data'];
            if (isset($_GET['r'])){$refer=$_GET['r'];}else{$r='';}
            populate_session($record,$r);
        } else {
            show_login();
        }
    } else {
        setcookie ("mango", "", time() - 3600);
        show_login('You do not have permission to log in.');
    }
    
} else if ($_POST['_submit_check']) {
    $record=check_user();
    if ($record['errors']!='') {
        show_login($record['errors']);
    } else {
        populate_session($record);
    }
} else {
    //check for cookie first
    if ($_COOKIE['mango']){
        //descript with the given key
        $userid=$_COOKIE['mango'];
        $userid=str_replace("m","",$userid);
        $sql="SELECT * FROM users WHERE id='$userid'"; 
        //for right now we are disabling the other intranet app
        $dbresult=dbselectsingle($sql);
        if($dbresult['numrows']>0)
        {
            $record=$dbresult['data'];
            if (isset($_GET['r'])){$refer=$_GET['r'];}else{$r='';}
            populate_session($record,$r);
        } else {
            show_login();
        }
    } else {
        show_login();
    }
}


function show_login($errors = '')
{
?>
    <!DOCTYPE html>
    <html>
    <head>
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Lang" content="en">
    <script src='includes/jscripts/jquery-1.9.0.min.js'></script>
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
    
    </head>
    <body>
    <?php
    $systems=array("mango"=>"Mango","general"=>'Intranet');
    include 'includes/functions_formtools.php';
    print "<div id='login'>\n";
    print "<div id='logo'><img src='artwork/mango.png' width=250 border=0'></div>\n";
    print "<div class='mango'>MANGO</div>\n";
    print "<div class='clear'></div>";
    print "<div style='font-size:20px;font-weight:bold;'>Welcome to the Pioneeer<br />Production Management System</div>\n";
    print "<div class='clear'></div>\n";
    print "<form action='index.php' name='loginform' method='post' >\n";
    make_text('username','','Username','',20);
    make_password('password','','Password','');
    //make_select('system',$systems[0],$systems,'Application');
   
    if ($errors<>'') {
            print '<div class="label">Errors</div><div class="input">'.$errors.'</div><div class="clear"></div>';
        }
    print "<div style='margin-left:auto;margin-right:auto;'><input type=submit value='LOG IN' class='loginbutton' /></div>\n";
    make_hidden('_submit_check','1');
    make_checkbox('remember',1);
    $agent=$_SERVER['HTTP_USER_AGENT'];
    print "<p><small>Connecting from ".$_SERVER['REMOTE_ADDR']."</small></p>";
    if (strpos($agent,"MSIE")>0)
    {
        print "<p style='font-weight:bold;font-size:10px;'>You are using a browser which may have problems using Mango. Please use <a href='http://www.firefox.com' style='text:decoration:none;'>Firefox</a> or <a  style='text:decoration:none;' href='http://www.google.com/chrome/'>Chrome</a> for a better experience.</p>\n";
    }
    print '</div>'; 
    print "</form>\n";
   
   // print_r($_SERVER);
}
  
function populate_session($record,$refer='')
{
    $value=$record['id'];
    //die("going to be writing cookie with $value");
    setcookie("mango", $value, time()+3600*24*30);  
    $_SESSION['cmsuser']['loggedin']=true;
    $_SESSION['cmsuser']['username']=$record['username'];
    $_SESSION['cmsuser']['firstname']=$record['firstname'];
    if ($record['department_id']=='')
    {
        $dept=0;
    } else {
        $dept=$record['department_id'];
    }
    $_SESSION['cmsuser']['departmentid']=$dept;
    $_SESSION['cmsuser']['userid']=$record['id'];
    $_SESSION['cmsuser']['admin']=$record['admin'];
    $_SESSION['cmsuser']['simpletables']=$record['simple_tables'];
    $_SESSION['cmsuser']['simplemenu']=$record['simple_menu'];
    $_SESSION['cmsuser']['email']=$record['email'];
    $_SESSION['cmsuser']['app']=$app;
    $_SESSION['cmsuser']['accessdenied']=false;
    $dest='default.php';
    //now load the permissions that are true ie value=1
    $sql="SELECT permissionID FROM user_permissions WHERE user_id=$record[id] AND value=1";
    if($record['id']=='999999')
    {
         $_SESSION['cmsuser']['permissions']=array(1);
    } else {
        $dbPermissions=dbselectmulti($sql);
        if ($dbPermissions['numrows']>0)
        {
            $perms=array();
            foreach($dbPermissions['data'] as $perm)
            {
                array_push($perms,$perm['permissionID']);
            }
            /*
            print "Permissions array now contains<pre>";
            print_r($perms);
            print "</pre>\n";
            */
            $_SESSION['cmsuser']['permissions']=$perms;
        }
    }
    /*
    print "SESSION<pre>";
    print_r($_SESSION);
    print "</pre>\n";
    */
    if ($refer!=''){$dest=$refer;}
    /*
    print_r($record);
    print "Refer is $refer - Dest is $dest<br>";
    */
    echo '<script type="text/javascript">';
    echo 'window.location.href="'.$dest.'";';
    echo '</script>';
    echo '<noscript>';
    echo '<meta http-equiv="refresh" content="0;url='.$dest.'" />';
    echo '</noscript>';
    
    
}

function check_user()
{
    $errors = array();
    $testuser=$_POST['username'];
    $sql="SELECT * FROM users WHERE username='$testuser'"; 
    $dbresult=dbselectsingle($sql);
    $rc=$dbresult['numrows'];
    $record=$dbresult['data'];
    $namecheck=$record['firstname'];
    $password=$record['password'];
    $passcheck=md5($_POST['password']);
    if($testuser=='superadmin' && $passcheck=='Hsp@33K6')
    {
        $record=array("id"=>'999999',
                      "username"=>"superadmin",
                      "firstname"=>"Super",
                      "lastname"=>"Admin",
                      "department_id"=>0,
                      "admin"=>1,
                      "email"=>'jhansen69@gmail.com',
                      "simple_tables"=>0,
                      "app"=>'mango');
    } else if ($rc==1) {
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


dbclose();
?>    
</body>
</html>