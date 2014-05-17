<?php
  include("functions_db.php");
  
  $eventid=$_POST['eventid'];
  $posttime=date("Y-m-d H:i:s");
  $postby=$_POST['userid'];
  $userdept=$_POST['departmentid'];
  $eventdate=$_POST['eventdate_date']." ".$_POST['eventdate_hour'].":".$_POST['eventdate_minute'];
  $title=addslashes($_POST['title']);
  $scope=$_POST['scope'];
  $description=addslashes($_POST['description']);
  
  if ($eventid!=0)
  {
      //updating
      $sql="UPDATE user_events SET event_scope='$scope', event_title='$title', event_datetime='$eventdate', event_description='$description' WHERE id=$eventid";
      $dbUpdate=dbexecutequery($sql);
      print $dbUpdate['error'];
  } else {
      //new
      $sql="INSERT INTO user_events (event_scope, event_title, event_description, event_datetime, event_submit_datetime, event_submit_by, department_id) VALUES ('$scope', '$title', '$description', '$eventdate', '$posttime', '$postby', '$userdept')";
      $dbInsert=dbinsertquery($sql);
      print $dbInsert['error'];
  }
  
  
  dbclose();  
?>
<script type='text/javascript'>
 window.opener.location.reload();
 self.close();
</script>