<?php
  include("functions_db.php");
  include("config.php");
  include("functions_graphics.php");
  
  $documentid=$_POST['documentid'];
  $newsid=$_POST['newsid'];
  $userdept=$_POST['departmentid'];
  $title=addslashes($_POST['title']);
  $scope=$_POST['scope'];
  $type=$_POST['type'];
  $keywords=$_POST['keywords'];
  $keywords=str_replace("  "," ",$keywords);
  $keywords=str_replace(";"," ",$keywords);
  $keywords=str_replace(","," ",$keywords);
  $keywords=addslashes($keywords);
  $description=addslashes($_POST['description']);
  
  if ($documentid!=0)
  {
      //updating
      $sql="UPDATE user_documents SET document_scope='$scope', document_title='$title', document_keywords='$keywords', 
      document_description='$description' WHERE id=$documentid";
      $dbUpdate=dbexecutequery($sql);
      $error=$dbUpdate['error'];
  } else {
      //new
      $sql="INSERT INTO user_documents (document_scope, document_title, document_description, document_keywords, news_id, department_id) 
      VALUES ('$scope', '$title', '$description', '$keywords', '$newsid', '$userdept')";
      $dbInsert=dbinsertquery($sql);
      $error=$dbInsert['error'];
      $documentid=$dbInsert['numrows'];
  }
  if ($error=='')
  {
      if (isset($_FILES))
      {
        //process any uploaded files
        $file=$_FILES['mydoc'];
        $date=date("Ym");
        $path="artwork/intranetdocs/$date/";
        $fullpath="../".$path;
        if (!file_exists($fullpath))
        {
            mkdir($fullpath);
        }
        $newname=$file['name'];
        $filetype=$file['type'];
        $newname=str_replace(" ","",$newname);
        $newname=str_replace("/","",$newname);
        $newname=str_replace("\\","",$newname);
        $newname=str_replace("*","",$newname);
        $newname=str_replace("?","",$newname);
        $newname=str_replace("!","",$newname);
        $newname=str_replace("'","",$newname);
        $newname=str_replace(";","",$newname);
        $newname=str_replace(":","",$newname);
        $newname=str_replace("'","",$newname);
        $newname=str_replace("%","",$newname);
        $newname=str_replace("\$","",$newname);
        if(processFile($file,$fullpath,$newname) == true) {
            $sql="UPDATE user_documents SET file_type='$filetype', document_type='$type', file_path='$path', file_name='$newname' WHERE id=$documentid";
            $result=dbexecutequery($sql);
            $error.=$result['error'];
        }
               
      }
      if ($error=='')
      {
      ?>
          <script type='text/javascript'>
             window.opener.location.reload();
             self.close();
          </script>
      <?php
      } else {
          print $error;
      }
  } else {
      print $error;
  }
  
  dbclose();  
?>
