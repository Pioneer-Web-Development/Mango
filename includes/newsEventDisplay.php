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
  //determine if we are adding or editing
  if (isset($_GET['eventid']))
  {
      //means we are editing an article
      $eventsid=$_GET['eventid'];
      $sql="SELECT * FROM user_events WHERE id=$eventid";
      $dbArticle=dbselectsingle($sql);
      $article=$dbArticle['data'];
      $scope=$article['event_scope'];
      $title=stripslashes($article['event_title']);
      $description=stripslashes($article['event_description']);
      $eventdate=date("Y-m-d H:i", strtotime($article['event_datetime']));
  } else {
      $scope='dept';
      $title='';
      $description='';
      $eventdate=date("Y-m-d H:i",strtotime("+1 month"));
  }
  
  print "<form name='news' id='news' method=post action='newsEventHandler.php'>\n";
  make_text('title',$title,'Title','',50);
  make_select('scope',$scopes[$scope],$scopes,'Scope','Who can see this event?');
  make_datetime('eventdate',$eventdate,'Date/Time','When does this event happen?');
  make_textarea('description',$description,'Description','',70,10);
  print "<input type='hidden' name='eventid' value='$eventid'>\n";
  print "<input type='hidden' name='userid' value='".$_SESSION['cmsuser']['userid']."'>\n";
  if ($_SESSION['cmsuser']['departmentid']==''){$dept=0;}else{$dept=$_SESSION['cmsuser']['departmentid'];}
  print "<input type='hidden' name='departmentid' value='".$dept."'>\n";
  print "<div class='label'></div>\n";
  print "<div class='input'>\n";
  print "<input type='submit' id='clicker' name='clicker' class='submit' value='Save'>\n";
  print "<input type='button' id='clicker' name='clicker' class='submit' value='Cancel' onclick='self.close();'>\n";
  print "</div>\n";
  print "</form>\n"; 

  dbclose();
?>
