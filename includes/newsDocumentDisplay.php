<html>
<head>
<script language="javascript" type="text/javascript" src="scripts/test.js"></script>
<script language="javascript" type="text/javascript" src="scripts/calendarDateInput.js"></script>
<script language="javascript" type="text/javascript" src="scripts/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<link rel="stylesheet" type="text/css" href="/styles/pims_main.css" />
<style type='text/css'>
.label{
    width:75px;
}
</style>
<script language="javascript" type="text/javascript">
tinyMCE.init({
    theme : "advanced",
    mode : "textareas",
    editor_selector : "GuiEditor",
    editor_deselector : "noGuiEditor",
    plugins : "table,advlink,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview",
    theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
    theme_advanced_buttons3_add_before : "tablecontrols,separator",
    paste_use_dialog : false,
    theme_advanced_resizing : true,
    paste_auto_cleanup_on_paste : true,
    paste_convert_headers_to_strong : false,
    paste_strip_class_attributes : "all"
});
</script>
</head>
<body>
<?php
  //this scripts builds an add/edit window for creating and editing news items for the intranet system
  session_start();
  include("functions_db.php");
  include("functions_formtools.php");
  $scopes=array("dept"=>"Department","public"=>"Public");
  $doctypes=array("pdf"=>"PDF","word"=>"Word/Writer","excel"=>"Excel/Calc","powerpoint"=>"Powerpoint/Impress","text"=>"Plain Text","image"=>"Image/Picture");
  //determine if we are adding or editing
 
 if ($_GET['action']=='list')
 {
    $newsid=$_GET['newsid'];
    $sql="SELECT * FROM user_documents WHERE news_id=$newsid";
    $dbDocuments=dbselectmulti($sql);
    print "<h2>Documents attached to this news item:</h2>\n";
    if ($dbDocuments['numrows']>0)
    {
        foreach($dbDocuments['data'] as $document)
        {
            print "<p style='font-size:12px;'><a href='?documentid=$document[id]'><img src='../artwork/icons/paper_content_pencil_48.png' width=24 border=0>$document[document_title]</a></p>\n"; 
         
        }
        
    }
    print "<p style='font-size:12px;'><a href='?newsid=$newsid'><img src='../artwork/icons/paper_content_pencil_48.png' width=24 border=0>Add a document for this article</a></p>\n"; 
 } else {
    if (isset($_GET['documentid']))
      {
          //means we are editing an article
          $documentid=$_GET['documentid'];
          $sql="SELECT * FROM user_documents WHERE id=$documentid";
          $dbDocument=dbselectsingle($sql);
          $document=$dbDocument['data'];
          $scope=$document['document_scope'];
          $newsid=$document['news_id'];
          $title=stripslashes($document['document_title']);
          $keywords=stripslashes($document['document_keywords']);
          $type=stripslashes($document['document_type']);
          $description=stripslashes($document['document_description']);
      } else {
          if (isset($_GET['newsid']))
          {
            $newsid=$_GET['newsid'];
          } else {
            $newsid=0;
          }
          $scope='dept';
          $type='pdf';
          $title='';
          $keywords='';
          $description='';
      }
      
      print "<form name='news' id='news' method=post enctype='multipart/form-data' action='newsDocumentHandler.php'>\n";
      make_text('title',$title,'Title','',50);
      make_text('keywords',$keywords,'Keywords','',50);
      make_select('doctype',$doctypes[$type],$doctypes,'Document Type','What type of document is this?');
      make_select('scope',$scopes[$scope],$scopes,'Scope','Who can see this event?');
      make_file('mydoc','Document');
      make_textarea('description',$description,'Description','',70,10);
      print "<input type='hidden' name='documentid' value='$documentid'>\n";
      print "<input type='hidden' name='newsid' value='$newsid'>\n";
      print "<input type='hidden' name='userid' value='".$_SESSION['cmsuser']['userid']."'>\n";
      if ($_SESSION['cmsuser']['departmentid']==''){$dept=0;}else{$dept=$_SESSION['cmsuser']['departmentid'];}
      print "<input type='hidden' name='departmentid' value='".$dept."'>\n";
      print "<div class='label'></div>\n";
      print "<div class='input'>\n";
      print "<input type='submit' id='clicker' name='clicker' class='submit' value='Save'>\n";
      print "<input type='button' id='clicker' name='clicker' class='submit' value='Cancel' onclick='self.close();'>\n";
      print "</div>\n";
      print "</form>\n"; 
 }
  dbclose();
?>
