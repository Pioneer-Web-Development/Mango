<?php
  //gets full path to a file, reads it, and returns the contents
  $url=urldecode($_GET['file']);
  $temp=file_get_contents($url);
  print $temp;
?>
