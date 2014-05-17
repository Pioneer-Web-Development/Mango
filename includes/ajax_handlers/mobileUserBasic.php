<?php
  include("../functions_db.php");
  $action=$_POST['action'];
  
  switch($action)
  {
    case "geoUser":
    
        $userid=intval($_POST['userid']);
        $lat=addslashes($_POST['lat']);
        $lng=addslashes($_POST['lng']);
        $address=addslashes($_POST['address']);
        $sql="UPDATE users SET last_login_lat='$lat', last_login_lng='$lng', last_login_address='$address' WHERE id=$userid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        $json['userid']=$userid;
        $json['address']=$address;
        $json['lat']=$lat;
        $json['lng']=$lng;
        if($error=='')
        {
            $json['status']='success';
        } else {
            $json['status']='error';
            $json['sql']=$sql;
            $json['error']=$error;
        }
    break;
        
  }
  
  echo json_encode($json);
  dbclose();