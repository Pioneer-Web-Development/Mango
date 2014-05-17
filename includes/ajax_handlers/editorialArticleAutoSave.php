<?php
  include('../functions_db.php');
  
  $articleid=$_POST['articleid'];
  $text=addslashes($_POST['text']);
  if($articleid!=0)
  {
      $sql="UPDATE editorial_article_body SET article='$text' WHERE id=$articleid";
      $dbUpdate=dbexecutequery($sql);
      if($dbUpdate['error']=='')
      {
          print "success|";
      } else {
          print "error|".$sql;
      }
  } else {
      $sql="INSERT INTO editorial_article_body (article, post_datetime) VALUES ('$text', '".date("Y-m-d H:i:s")."')";
      $dbInsert=dbinsertquery($sql);
      $insertid=$dbInsert['insertid'];
      print "new|$insertid";
  }
?>
