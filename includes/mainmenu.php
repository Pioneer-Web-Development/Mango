<?php
//<!--VERSION: .9 **||**-->
error_reporting (0);
session_start();
require ('includes/functions_db.php');
require ('includes/functions_formtools.php');
require ('includes/functions_graphics.php');
require ('includes/mail/htmlMimeMail.php');
require ('includes/config.php');
require ('includes/functions_common.php');
if($GLOBALS['debug']){error_reporting (E_ALL ^ E_NOTICE);}else{$GLOBALS['debug']=0;}
$detect = new Mobile_Detect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
if($deviceType!='computer')
{
    header('Location: mobile/');
}
//always get userid from the cookie if it's not set
/*
if($_SESSION['cmsuser']['userid']==''){
    if($_COOKIE['mango'])
    {
        $_SESSION['cmsuser']['userid']=$_COOKIE['mango'];
    } else {
        redirect("index.php");
    }
}
*/
//check page permissions
if (!checkPermission($_SERVER['SCRIPT_NAME']))
{
    $_SESSION['cmsuser']['accessdenied']=true;
    redirect('index.php');
}
//lets see if an app cookie is set, if not, set it to either a posted app, or the default of mango
if($_POST['mangoapp'])
{
   $mangoapp=$_POST['mangoapp'];
}elseif ($_COOKIE['mangoapp'])
{
   $mangoapp=$_COOKIE['mangoapp'];
} else {
   $mangoapp='mango';
}
setcookie("mangoapp", $mangoapp, time()+3600*24*30); 
switch ($mangoapp)
{
    case 'mango':
    $appname='MANGO';
    $appfield='mango';
    $appdesc='Production Management Made Simple';
    break;
    
    case 'kiwi':
    $appname='KIWI';
    $appfield='kiwi';
    $appdesc='Advertising Management Made Simple';
    break;
    
    case 'guava':
    $appname='GUAVA';
    $appfield='guava';
    $appdesc='Editorial Management Made Simple';
    break;
    
    case 'papaya':
    $appname='PAPAYA';
    $appfield='papaya';
    $appdesc='Circulation Management Made Simple';
    break;
    
    case 'pineapple':
    $appname='PINEAPPLE';
    $appfield='pineapple';
    $appdesc='Business Management Made Simple';
    break;  
}


?>
<!DOCTYPE html> 
<html>
<head>
<title><?php echo $appname.' - '.$appdesc; ?></title>
<META name="author" content="Joe Hansen <jhansen@idahopress.com>">
<META name="copyright" content="<?php echo date("Y"); ?> Pioneer News Group Inc - Joe Hansen">
<META name="robots" content="none">
<META http-equiv="cache-control" content="no-cache">
<META http-equiv="pragma" content="no-cache">
<META http-equiv="content-type" content="text/html; charset=UTF-8">
<META http-equiv="expires" content="<?php echo date("r") ?>">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" /> 
<?php if ($mangoapp=='mango'){?>
<script type='text/javascript'>
var calendarStartAddressing='<?php if ($GLOBALS['calendarStartAddressing']=='current'){echo date("G");} else {echo $GLOBALS['calendarStartAddressing'];}?>';
var calendarStartPress='<?php if ($GLOBALS['calendarStartPress']=='current'){echo date("G");} else {echo $GLOBALS['calendarStartPress'];}?>';
var calendarStartPackaging='<?php if ($GLOBALS['calendarStartPackaging']=='current'){echo date("G");} else {echo $GLOBALS['calendarStartPackaging'];}?>';
var calendarStartBindery='<?php if ($GLOBALS['calendarStartBindery']=='current'){echo date("G");} else {echo $GLOBALS['calendarStartBindery'];}?>';
var calendarPackagingSlots=<?php if ($GLOBALS['calendarPackagingSlots']=='current'){echo date("G");} else {echo $GLOBALS['calendarPackagingSlots'];}?>;
var calendarBinderySlots=<?php if ($GLOBALS['calendarBinderySlots']=='current'){echo date("G");} else {echo $GLOBALS['calendarBinderySlots'];}?>;
var calendarPressSlots=<?php if ($GLOBALS['calendarPressSlots']=='current'){echo date("G");} else {echo $GLOBALS['calendarPressSlots'];}?>;
var calendarAddressingSlots=<?php if ($GLOBALS['calendarAddressingSlots']=='current'){echo date("G");} else {echo $GLOBALS['calendarAddressingSlots'];}?>;
// It's important that this JS section is above the line below where dhtmlgoodies-week-planner.js is included
var pressRunTimeThreshold=<?php print $GLOBALS['pressRunTimeThreshold'];?>;  //sets how long to allow a run to be before flagging as a problem
var pressCounterThreshhold=<?php print $GLOBALS['pressCounterThreshhold'];?>;  //sets how long to allow a run to be before flagging as a problem
var taxRate=<?php if($GLOBALS['taxRate']!=''){echo $GLOBALS['taxRate'];}else{echo 6.0;}?>;
</script>
<?php
}


     
?>
<script>
/*this sets some global javascript variables */
var debug=<?php echo $GLOBALS['debug'];?>;
<?php
//look for specific permissions for some global javascript variables
//we go through all permissions that have include_js, then see if the user has any of them
$sql="SELECT * FROM core_permission_list WHERE include_js=1 AND js_varname<>''";
$dbJSPerms=dbselectmulti($sql);
if($dbJSPerms['numrows']>0)
{
    $userid=$_SESSION['cmsuser']['userid'];
    foreach($dbJSPerms['data'] as $perm)
    {
        $permid=$perm['id'];
        //see if the user has this one
        $sql="SELECT * FROM user_permissions WHERE permissionID=$permid AND user_id=$userid AND value=1";
        $dbValid=dbselectsingle($sql);
        if($dbValid['numrows']>0)
        {
            $valid='true';
        } else {
            $valid='false';
        }
        //if the user is "admin" then we automatically grant access
        if($_SESSION['cmsuser']['admin']){
            $valid='true';
        }
        print "var ".stripslashes($perm['js_varname'])." = $valid;\n";
    }
}
?>
</script>
    
<?php
    
$scriptname=end(explode("/",$_SERVER['SCRIPT_NAME']));
loadHeadFiles('all',$appfield);
?>
<!--[if lte IE 7]>
<style type="text/css">
html .jqueryslidemenu{height: 1%;} /*Holly Hack for IE7 and below*/
</style>
<![endif]-->


</head>
<body>
<?php


if($_GET['popup'] || $_SESSION['kiosk']==true)
{
    //do not load the menu for a popup window mode of a page
} else {
    if($_COOKIE['mangoMenu']=='hidden')
    {
        print "<div id='topholder' style='display:none;width:100%;padding-top:0px;height:50px;margin-bottom:40px;'>";
    } else {
        print "<div id='topholder' style='display:block;width:100%;padding-top:0px;height:50px;margin-bottom:40px;'>";
    }
    print "<div id='toplogographic' style='width:150px;position:absolute;top:10;left:10;z-index:1005;'>";
        switch($mangoapp)
        {
            case 'mango':
            print "<a href='default.php'><img src='artwork/mango.png' border=0 width=130></a>\n";
            break;
            
            case 'kiwi':
            print "<a href='default.php'><img src='artwork/kiwi.png' border=0 width=130></a>\n"; 
            break;
            
            case 'guava':
            print "<a href='default.php'><img src='artwork/guava.png' border=0 width=130></a>\n";
            break;
            
            case 'papaya':
            print "<a href='default.php'><img src='artwork/papaya.png' border=0 height=100></a>\n";
            break;
            
            case 'pineapple':
            print "<a href='default.php'><img src='artwork/pineapple.png' border=0 height=90></a>\n";
            break;
        }
        
    print "</div>\n";
    print "<div id='topeverythingelse' style='width:100%;'>";

    print "<div id='branding' style='margin-left:130px;float:left;'>\n";
    print "<a href='default.php' style='text-decoration:none;font-size:40px;font-weight:bold;color:#AC1D23;vertical-align:center;'>$appname</a>";
    print "<span style='margin-left:10px;font-size:16px;font-style:italic;padding-top:12px;'>$appdesc</span></div>";
    print "<div style='float:right;margin-left:50px;font-size:12px;padding-top:10px;margin-right:20px;'>Welcome ";
    print $_SESSION['cmsuser']['firstname'].",&nbsp;&nbsp;<a href='userProfile.php'>Manage your profile</a>";
    print "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='logout.php'>  Log out</a>&nbsp;&nbsp;<a href='changePassword.php'> 
    Change Password</a><input type='hidden' id='userid' value='".$_SESSION['cmsuser']['userid']."' /><br>";
    $apps=array('mango'=>"Mango (Production)",'kiwi'=>"Kiwi (advertising)",'guava'=>"Guava (editorial)",'papaya'=>'Papaya (circulation)','pineapple'=>'Pineapple (business)');
    print "<div style='float:left;'><form method=post>";
    print "App: ".input_select('mangoapp',$apps[$mangoapp],$apps,'','this.form.submit();');
    print "</form>\n</div>";
    print "<div style='float:right;margin-right:20px;'>".$GLOBALS['newspaperName']."</div>";
    print "</div>";
    
    //print "<div style='clear:both;'></div>";
    print "<div class='clear'></div>\n";
    if($_SESSION['cmsuser']['simplemenu']==1)
    {
        simplemenu();
    } else {
        menu($appfield);
    }
    


    print "</div><!--closes topeverythingelse -->\n";
    print "</div><!--closes topholder -->\n";
    print "<div class='clear'></div>\n";
    print "<input type='hidden' id='debug' value='$_SESSION[debug]' />\n";
    //this area is used to define some quick action menu items
    print "<a href='#' id='toptabmenutrigger' class='trigger right'>&nbsp;</a>\n";
    print "<div id='toptabmenu' class='panel right'>\n";
    print "<span id='toggletop' onclick='toggleTop();' style='font-weight:bold;cursor:pointer;'>[-] Hide header and menu</span>\n";
    print "</div>\n";
}



function menu($appfield)
{
    print "<div id='mainmenu' class='jqueryslidemenu ui-widget'>\n";
    print "<ul>\n";
    global $siteID;
    //get the id of the primary site
    $sql="SELECT id, ip_address FROM core_sites WHERE primary_site=1";
    $dbPrimary=dbselectsingle($sql);
    $primaryid=$dbPrimary['data']['id'];
    $primaryaddress=$dbPrimary['data']['ip_address'];
    if($primaryaddress!=$_SERVER['SERVER_ADDR'] && $_SESSION['cmsuser']['admin']==0)
    {
        $ptest="AND primary_site_only=0";
    } else {
        $ptest='';
    }
    $sql="SELECT * FROM core_pages WHERE parent_id=0 AND $appfield=1 AND display=1 $ptest ORDER BY weight";
    $dbTop=dbselectmulti($sql);
    $sub=array();
    $i=0;
    if ($dbTop['numrows']>0)
    {
        //print "<li><a href='#' onclick=\"window.open('chat.php','Production Chat System','width=520,height=580,toolbar=0,status=0,location=0');return false;\">Chat(use new please)</a></li>\n";
        print "  <li class='ui-state-default'><a href='#' onclick=\"javascript:window.open('chatV2.php?popup=true','Production Chat System','width=520,height=680,toolbar=0,status=0,location=0');return false;\">Chat V2</a></li>\n";
        /*
        //FOR NOW, DISABLE THE INTRANET APPLICATION
        if ($_SESSION['cmsuser']['intranet'])
        {
            print "<li><a href='defaultIntranet.php'>MangoNET</a></li>\n";
        }
        */
        print "  <li><a href='default.php'>DASHBOARD</a></li>\n";
        foreach($dbTop['data'] as $top)
        {
            print "  <li>";
            $file=$top['filename'];
            $name=$top['name'];
            if(substr($file,0,5)=='http:' || substr($file,0,6)=='https:')
            {
                //external web link
                $target="target='_blank'";
            } else {
                $target='';
                $testfile=explode("?",$file);
                $testfile=$testfile[0];
                if (!file_exists($testfile) && $file!='')
                {
                    
                    $name="$name - no file!";
                    $file="#";
                }
            }
            if ($file==''){$file='#';}
            if ($top['popup'])
            {
                $width=$top['popup_width'];
                $height=$top['popup_height'];
                print "<a href='#' onclick=\"window.open('$file?popup=true','$name','width=$width,height=$height,toolbar=no,status=no,location=no,scrollbars=no');return false;\">$name</a>";
            } else 
            {
                print "<a href='$file' $target>$name</a>";
            }
            $sql="SELECT * FROM core_pages WHERE parent_id=$top[id] AND display=1 $ptest ORDER BY weight";
            $dbSub=dbselectmulti($sql);
            if ($dbSub['numrows']>0)
            {
                submenu($top['id'],$appfield,$ptest);
            }
            print "  </li>\n";
            $i++; 
        }
        
    } else {
        print "No top menu items existed.";
    }
    print "</ul>\n";
    print "<br style='clear: left' />\n";
    print "</div>\n";
}

function submenu($menuid,$appfield,$ptest)
{
    print "      <ul>\n";
    $sql="SELECT * FROM core_pages WHERE parent_id=$menuid AND $appfield=1 AND display=1 $ptest ORDER BY weight";
    $dbMenu=dbselectmulti($sql);
    foreach($dbMenu['data'] as $top)
    {
        print "      <li>";
        $file=$top['filename'];
        $name=$top['name'];
        if(substr($file,0,5)=='http:' || substr($file,0,6)=='https:')
        {
            //external web link
            $target="target='_blank'";
        } else {
            $target="";
            $testfile=explode("?",$file);
            $testfile=$testfile[0];
            if (checkPermission($file))
            {
                $lock='';
                $locked=false;    
            } else {
                $locked=true;
                $lock="<div style='display:inline;margin-right:5px;'><img src='/artwork/icons/lock_48.png' height='16' border=0 /></div>"; 
            }
                
            if (!file_exists($testfile) && $file!='')
            {
              $name=$lock."$name - no file!";
              $file="#";
            } else {
              $name=$lock.$name;
            }
        }
        if ($file==''){$file='#';}
        if ($top['popup'])
        {
            $width=$top['popup_width'];
            $height=$top['popup_height'];
            if($locked)
            {
                print "<a href='#'>$name</a>\n";
            } else {
                print "<a href='#' onclick=\"window.open('$file?popup=true','$name','width=$width,height=$height,toolbar=no,status=no,location=no,scrollbars=no');return false;\">$name</a>\n";
            }
        } else 
        {
            if($locked)
            {
                print "<a href='#'>$name</a>\n";
            } else {
                print "<a href='$file' $target>$name</a>\n";    
            }
        }
        $sql="SELECT * FROM core_pages WHERE parent_id=$top[id] AND display=1 $ptest ORDER BY weight";
        $dbSub=dbselectmulti($sql);
        if ($dbSub['numrows']>0)
        {
            submenu($top['id'],$appfield,$ptest);
        }
        print "      </li>\n";
    }        
    print "      </ul>\n"; 
}

function simplemenu()
{
    if($primaryaddress!=$_SERVER['SERVER_ADDR'] && $_SESSION['cmsuser']['admin']==0)
    {
        $ptest="AND primary_site_only=0";
    } else {
        $ptest='';
    }
    
    
    print "<div id='mainmenu' class='jqueryslidemenu ui-widget'>\n";
    print "<ul>\n";
    print "  <li class='ui-state-default'><a href='#' onclick=\"javascript:window.open('chatV2.php','Production Chat System','width=520,height=680,toolbar=0,status=0,location=0');return false;\">Chat V2</a></li>\n";
    print "  <li><a href='default.php'>DASHBOARD</a></li>\n";
    $sql="SELECT * FROM simple_menu ORDER BY sort_order ASC";
    $dbSimple=dbselectmulti($sql);
    if($dbSimple['numrows']>0)
    {
        
        foreach($dbSimple['data'] as $simple)
        {
            print "  <li><a href='#'>".stripslashes($simple['menu_title'])."</a>\n";
            //lets see if there are any sub items
            $sql="SELECT A.* FROM core_pages A, simple_menu_pages B WHERE A.id=B.page_id AND B.simple_menu_id=$simple[id] ORDER BY A.name";
            $dbSub=dbselectmulti($sql);
            if($dbSub['numrows']>0)
            {
                print "      <ul>\n";
                foreach($dbSub['data'] as $sub)
                {
                    print "<li>\n";
                    if (checkPermission($sub['id']))
                    {
                        $lock='';
                        $locked=false;    
                    } else {
                        $locked=true;
                        $lock="<span style='display:inline;margin-right:5px;'><img src='/artwork/icons/lock_48.png' height='16' border=0 /></span>"; 
                    }
                    $file=$sub['filename'];
                    $name=$sub['name'];
                    $testfile=explode("?",$file);
                    $testfile=$testfile[0];
                    if (!file_exists($testfile) && $file!='')
                    {
                      $name=$lock."$name - no file!";
                      $file="#";
                    } else {
                      $name=$lock.$name;
                    }
                    if ($file==''){$file='#';}
        
                    if ($sub['popup'])
                    {
                        $width=$sub['popup_width'];
                        $height=$sub['popup_height'];
                        if($locked)
                        {
                            print "<a href='#'>$name</a>\n";
                        } else {
                            print "<a href='#' onclick=\"window.open('$file?popup=true','$name','width=$width,height=$height,toolbar=no,status=no,location=no,scrollbars=no');return false;\">$name</a>\n";
                        }
                    } else 
                    {
                        if($locked)
                        {
                            print "<a href='#'>$name</a>\n";
                        } else {
                            print "<a href='$file'>$name</a>\n";    
                        }
                    }
                    print "</li>\n";
                       
                    
                    //print "         <li><a href='".stripslashes($sub['filename'])."'>".stripslashes($sub['name'])."</a> $lock</li>\n";
                }
                print "      </ul>\n";
                
                
            }
            print "   </li>\n";
        }
    }    
    print "</ul>";
    print "<br style='clear: left' />\n";
    print "</div>\n";
}

?>
<script>
$.ctNotifyOption({
      position: 'absolute',
      width: '400px',
      anchors:{bottom: 0, right: 0}
    }, 'right-bottom');
$.ctNotifyOption({
      position: 'absolute',
      width: '400px',
      anchors:{bottom: 0, left: 0}
    }, 'left-bottom');
</script>
<?php 
showUserMessages();
?>
<br>
<div id='wrapper'> <!--start the main wrapper for the page content-->
