<?php
  include("../functions_db.php");
  $keywords=$_POST['keywords'];
  $keywords=str_replace(" ","%' OR keywords LIKE '%",$keywords);
  $sql="SELECT * FROM maintenance_solutions WHERE keywords LIKE '%$keywords%'";
  $dbSolutions=dbselectmulti($sql);
  if($dbSolutions['numrows']>0)
  {
      print "<h3>Results of your search for ".$_POST['keywords']."</h3>\n";
      foreach($dbSolutions['data'] as $solution)
      {
          print "<div style='float:left;'>$solution[solution_brief]</div>
          <div id='solution$solution[id]' style='float:right;margin:4x;padding:4px;font-weight:bold;color:black;background-color:#fcdb67'>View Full Solution</div>
          <div class='clear'></div>\n";
          print "<div id='solution_full_$solution[id]' style='display:none;'>$solution[solution_text]</div>\n";
          print "<script type='text/javascript'>
          \$('#solution$solution[id]').click(function() {
                  \$('#solution_full_$solution[id]').slideToggle('fast', function() {
                  });
                });
                </script>
            ";
          print "<hr>\n";
          
      }
  } else {
      print "<p>Sorry, there are no solutions with those keywords. You may try different keywords if you would like.</p>";
  }
  
  
  dbclose();
?>
