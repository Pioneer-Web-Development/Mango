<?php
  include("functions_db.php");
  include("config.php");
  $id=$_GET['id'];
  $sql="SELECT * FROM user_documents WHERE id=$id";
  $dbDoc=dbselectsingle($sql);
  $doc=$dbDoc['data'];
  $file="../".$doc['file_path'].$doc['file_name'];
  $type=$doc['file_type'];
  
  
  
  header("Content-Type: $type"); // plain text file
  header('Content-Disposition: attachment; filename="'.$doc['file_name'].'"');
  readfile($file);
      
?>
