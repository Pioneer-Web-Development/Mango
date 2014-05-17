<?php
  //this script ingests an ad manifest of inserts from the Vision Data system.
  
  include("includes/mainmenu.php") ;

  if($_POST)
  {
      process();
  } else {
      print "<form enctype='multipart/form-data' method=post>\n";
      make_file('manifest','Manifest','Select the ad manifest to import');
      make_submit('submit','Import Manifest');
      print "</form>\n";
  }

  function process()
  {
      $manifest=$_FILES['manifest']['tmp_name'];
      $contents=file_get_contents($manifest);
      $contents=explode("\n",$contents);
      $linecount=count($contents);
      for($i=0;$i<$linecount;$i++)
      {
          $line=explode("\t",$contents[$i]);
          if($i==0)
          {
              $heading=$line;
          } else {
              $ads[$i]=$line;
          }
      }
      
      print "<table>";
      print "<tr>";
      foreach($heading as $key=>$header)
      {
          print "<th>$header</th>";
      }
      print "</tr>\n";
      foreach($ads as $ad)
      {
          print "<tr>";
          foreach($ad as $key=>$value)
          {
              print "<td>$value</td>";
          }
          print "</tr>\n";
      }
      print "</table>\n";
  }
  
  
  footer();
?>
