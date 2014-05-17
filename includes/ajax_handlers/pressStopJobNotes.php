<html>
<body>
<?php
  //this file generates all the stop code displays
  //we are always starting here, so we do the insert here, the update will be in the restart function
  include("../functions_db.php");
  include("../config.php");
  include("../functions_common.php");
if($_POST)
{
   $jobid=$_POST['jobid'];
   $notes=addslashes($_POST['stopnotes']);
   $sql="UPDATE jobs SET notes_press='$notes' WHERE id=$jobid";
   $dbUpdate=dbexecutequery($sql);
   dbclose();
} else {
  $jobid=$_GET['jobid'];
  $time=date("Y-m-d H:i:s");
  print "<form id='jobnotesForm' name='jobnotesForm' action='includes/ajax_handlers/pressStopJobNotes.php' method=post>\n";
  
  $sql="SELECT notes_press FROM jobs WHERE id=$jobid";
      $dbJobInfo=dbselectsingle($sql);
      $jobinfo=$dbJobInfo['data'];
      print "<div style='padding:5px;'>\n";
      print "<p style='font-size:12px;font-weight:bold;color:#AC1D23;'>Please enter your job notes, including anything that might have gone wrong:</p>\n";
      print "<textarea id='stopnotes' name='stopnotes' cols='62' rows='10'>".$jobinfo['notes_press']."</textarea>\n";
      print "</div>\n";
  print "<input type='hidden' id='jobid' name='jobid' value='$jobid'>\n";
  print "</form>\n"; 
  print "</div>\n";
  print "<div id='error'></div>\n";
  //print "More stuff";
?>
<script>
// bind form using 'ajaxForm' 
$(document).ready(function() { 
var options = { 
    //target:        '#output1',   // target element(s) to be updated with server response 
    //beforeSubmit:  showRequest,  // pre-submit callback 
    //success:       showResponse  // post-submit callback 

    // other available options: 
    //url:       url         // override for form's 'action' attribute 
    type:      'post'        // 'get' or 'post', override for form's 'method' attribute 
    //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
    //clearForm: true        // clear all form fields after successful submit 
    //resetForm: true        // reset the form after successful submit 

    // $.ajax options can be used here too, for example: 
    //timeout:   3000 
}; 
$('#jobnotesForm').ajaxForm(options);
});
</script>
<?php 
footer();
}
?>