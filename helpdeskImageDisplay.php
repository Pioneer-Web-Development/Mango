<html>
<head>
<style>
body{
    padding:5px;
    font-family:Trebuchet MS,sans-serif;
    font-size: 12px;
}
</style>
<body>
<?php
  
  include("includes/functions_db.php");
  $imageid=$_GET['id'];
  $source=$_GET['source'];
  if ($source=='solutions')
  {
    $sql="SELECT path, filename FROM helpdesk_solutions_images WHERE id=$imageid";
  } else {
    $sql="SELECT ticketImage_path as path, ticketImage_filename as filename FROM helpdesk_tickets WHERE id=$imageid";  
  }
  $dbImage=dbselectsingle($sql);
  $image=$dbImage['data'];
  print "<a href='#' onclick='window.print();'><img src='artwork/printer.png' width=32 border=0'>Print this Solution</a><br />\n";  
  print "<img src='$image[path]$image[filename]' border=0 width=500><br /><br />\n";
  print stripslashes($image['caption']);
  
?>
</body>
</html>