<?php

function mobile_init()
{
    include('../includes/functions_db.php');
    include('../includes/functions_common.php');
    include('../includes/config.php');
    ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta content="width=device-width,minimum-scale=1,maximum-scale=1" name="viewport">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../assets/ico/favicon.png">

    <title>Mango for Mobile</title>

    <!-- Bootstrap core CSS -->
    <link href="../includes/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles/jquery-ui-1.10.0.custom.css" rel="stylesheet">
    <link href="../styles/mangoMobileStyles.css?bc=234kd" rel="stylesheet">

    <!-- Custom styles for this template -->
    
  </head>

  <body>

    <div class='container'>
        <?php 
        if($_SESSION['cmsuser']['loggedin'])
        {
           mobile_menu();
           mobile_page(); 
        } else {
            if($_POST)
            {
                check_login();
            } else {
                mobile_login();
            } 
        }
        ?>
    </div>
    
    <?php
    mobile_close();
}

function mobile_menu()
{
    ?>
   <div class='menuTab'>Menu <i class='glyphicon glyphicon-chevron-down'></i></div>
    <div class='drawerMenu'>
    <h3>Menu</h3>
    <ul class="nav nav-pills nav-stacked">
    <li><a href='ads.php'>Ads</a></li>
    <li><a href='appointments.php'>Appointments</a></li>
    <li><a href='proposals.php'>Proposals</a></li>
    </ul>
   </div>
   <?php
}

function check_login()
{
    $username=addslashes($_POST['inputUser']);
    $password=md5($_POST['inputPassword']);
    
    //first lets see if this is a user
    $sql="SELECT * FROM users WHERE username='$username' OR email='$username'";
    $dbCheck=dbselectsingle($sql);
    if($dbCheck['numrows']>0)
    {
        //possible user, lets check for valid password
        $testpass=$dbCheck['data']['password'];
        if($testpass===$password)
        {
            //successful login!
            $record=$dbCheck['data'];
            $dt=date("Y-m-d H:i:s");
            
            $sql="UPDATE users SET last_login='$dt' WHERE id=$record[id]";
            $dbUpdate=dbexecutequery($sql);
            
            $value=$record['id'];
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
            mobile_menu();
            mobile_page(); 
        } else {
            mobile_login(true);
        }
    } else {
        mobile_login(true);
    }
    
}

function mobile_login($failed=false)
{
    ?>
    <div class="content">
      <div class="row">
        <div class="login-form">
          <h2>Login</h2>
          <form class="form-horizontal" role="form" method=post>
              <div class="form-group">
                <label for="inputUser" class="col-lg-2 control-label">Username/Email</label>
                <div class="col-lg-10">
                  <input type="text" class="form-control" id="inputUser" name='inputUser' placeholder="Username or Email">
                </div>
              </div>
              <div class="form-group">
                <label for="inputPassword" class="col-lg-2 control-label">Password</label>
                <div class="col-lg-10">
                  <input type="password" class="form-control" id="inputPassword" name='inputPassword' placeholder="Password">
                </div>
              </div>
              <?php if($failed) { ?>
              <div class="form-group">
                <div class="col-lg-offset-2 col-lg-10">
                  The username/password you entered was incorrect.
                </div>
              </div>
              <?php } ?>
              <div class="form-group">
                <div class="col-lg-offset-2 col-lg-10">
                  <button type="submit" class="btn btn-success">Sign in</button>
                </div>
              </div>
            </form>

        </div>
      </div>
    </div>
    <?php
}

function mobile_close()
{
    ?>
    

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <script src='../includes/jscripts/jquery-1.9.0.min.js'></script>
    <script src='../includes/jscripts/jquery-migrate-1.0.0.js'></script>
    <script src='../includes/jscripts/jquery-ui-1.8.23.custom.min.js'></script>
    <script src='../includes/bootstrap/js/bootstrap.min.js'></script>
    <script src='../includes/jscripts/jquery.touchwipe.min.js'></script>
    <?php
        print "<!-- loading fixed head files -->\n";
        $sql="SELECT * FROM core_preferences";
        $dbPrefs=dbselectsingle($sql);
        $key=stripslashes($dbPrefs['data']['google_map_key']);
        print "<script type='text/javascript' src='http://maps.google.com/maps/api/js?key=$key&sensor=true&libraries=drawing'></script>\n";
    ?>
    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
    <script>
    $(document).ready(function(){
        var windowHeight=$(window).height();
        var userid=<?php echo $_SESSION['cmsuser']['userid'] ?>;
        $(".container").touchwipe({
            wipeLeft: function() { 
               if($(".drawerMenu").css('display')=='block')
               {  
                $( ".menuTab" ).animate({left: "-45px"}, "fast"); $(".drawerMenu").hide("slide", "fast");
               }
            },
        });
        $(".menuTab").touchwipe({
             wipeRight: function() { $( ".menuTab" ).animate({left: "202px"}, "fast");$(".drawerMenu").show("slide", "fast"); },
             wipeLeft: function() { $( ".menuTab" ).animate({left: "-45px"}, "fast"); $(".drawerMenu").hide("slide", "fast");},
             /*
                 wipeUp: function() { alert("up"); },
                 wipeDown: function() { alert("down"); },
                 min_move_x: 20,
                 min_move_y: 20,
                 preventDefaultEvents: true
             */
        });
         $( ".menuTab" ).click(function(event){
           if($(".drawerMenu").css('display')=='none')
           {
               $( ".menuTab" ).animate({left: "202px"}, "fast");
               $(".drawerMenu").show("slide", "fast");
           } else {
               $( ".menuTab" ).animate({left: "-45px"}, "fast");
               $(".drawerMenu").hide("slide", "fast");
           }
         })
        //set the top of the menuTab
        $(".menuTab").css("top",(windowHeight/2)-60);
        
        $( window ).resize(function() {
           windowHeight=$(window).height();
           $(".menuTab").css("top",(windowHeight/2)-60);
         
        })
        
        function initialize() {
            var loc = {};
            var geocoder = new google.maps.Geocoder();
            if(google.loader.ClientLocation) {
                loc.lat = google.loader.ClientLocation.latitude;
                loc.lng = google.loader.ClientLocation.longitude;
                var latlng = new google.maps.LatLng(loc.lat, loc.lng);
                geocoder.geocode({'latLng': latlng}, function(results, status) {
                    if(status == google.maps.GeocoderStatus.OK) {
                        $.ajax({
                          url: "../includes/ajax_handlers/mobileUserBasic.php",
                          type: "POST",
                          data: {action:'geoUser',userid:userid,lat:loc.lat,lng:loc.lng,address:results[0]['formatted_address']},
                          dataType: "json",
                          success: function(response){
                              
                          }
                        });
                    } else {
                        $.ajax({
                          url: "../includes/ajax_handlers/mobileUserBasic.php",
                          type: "POST",
                          data: {action:'geoUser',userid:userid,lat:loc.lat,lng:loc.lng,address:''},
                          dataType: "json",
                          success: function(response){
                              
                          }
                        });
                    };
                });
                
                
            }
        }

        google.load("maps", "3.x", {other_params: "sensor=true", callback:initialize});
    })
    </script>
  </body>
</html>

    <?php
    dbclose();
}  
?>