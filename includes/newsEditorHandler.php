<?php
  include("functions_db.php");
  
  $newsid=$_POST['newsid'];
  $posttime=date("Y-m-d H:i:s");
  $postby=$_POST['userid'];
  $userdept=$_POST['departmentid'];
  $expire=$_POST['expire'];
  $headline=addslashes($_POST['headline']);
  $scope=$_POST['scope'];
  $article=addslashes($_POST['article']);
  
  if ($newsid!=0)
  {
      //updating
      $sql="UPDATE user_news SET scope='$scope', headline='$headline', expiration_date='$expire', article='$article' WHERE id=$newsid";
      $dbUpdate=dbexecutequery($sql);
      print $dbUpdate['error'];
  } else {
      //new
      $sql="INSERT INTO user_news (scope, headline, article, expiration_date, post_datetime, post_by, 
      department_id) VALUES ('$scope', '$headline', '$article', '$expire', '$posttime', '$postby', '$userdept')";
      $dbInsert=dbinsertquery($sql);
      print $dbInsert['error'];
  }
  
  
  dbclose();  
?>
<script type='text/javascript'>
 window.opener.location.reload();
 self.close();
</script>