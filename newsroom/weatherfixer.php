<?php
  //fix weather script for newsroom
  //this script takes out some unnecessary data
  if ($_POST)
  {
      $cities=array("Amsterdam","Baghdad","Beijing","Beirut","Guatemala","Kuwait","London","Mexico City","Paris","Rome","Sydney","Tehran","Tokyo","Toronto");
      $text=$_POST['weather'];
      $type=$_POST['type'];
      $lines=explode("\n",$text);
      $newtext='';
      if ($type=='global')
      {
          foreach($lines as $line)
          {
              if (trim($line)!='')
              {
                  $parts=explode(";",$line);
                  if (in_array($parts[0],$cities))
                  {
                      $newline=$parts[0].chr(9).$parts[5].chr(9).$parts[6].chr(9).$parts[7];
                      $newtext.=$newline."\n";
                  }
              }
          }
      }
      if ($type=='us')
      {
          foreach($lines as $line)
          {
              if (trim($line)!='')
              {
                  $parts=explode(";",$line);
                  $city=$parts[0];
                  $hi=$parts[1];
                  $low=$parts[2];
                  $precip=$parts[3];
                  $cond=$parts[6];
                  $newline=$city.chr(9).$hi.chr(9).$low.chr(9).$precip.chr(9).$cond;
                  $newtext.=$newline."\n";
              }
          }
      }
      if ($type=='regional')
      {
          foreach($lines as $line)
          {
              if (trim($line)!='')
              {
                  $parts=explode(":",$line);
                  
                  
                  $nums=explode("/",$parts[2]);
                  $station=explode("  ",$parts[1]);
                  $station=trim($station[0]);
                  $max=trim($nums[0]);
                  $min=trim($nums[1]);
                  $prec=trim($nums[2]);
                  $newline=$station.chr(9).$max.chr(9).$min.chr(9).$prec;
                  $newtext.=$newline."\n";
              }
          }
      }
      print "<a href='weatherFixer.php'>Run again</a><br />\n";
      print "<textarea name='weather' rows=40 cols=40>$newtext</textarea>\n";
  } else {
      print "<form method=post>\n";
      print "<select name='type'>\n";
      print "<option value='global'>Global Weather</option>\n";
      print "<option value='us'>US Weather</option>\n";
      print "<option value='regional'>Original Regional</option>\n";
      print "</select>\n";
      print "<input type='submit' value='Fix my text' /><br />\n";
      print "Paste the raw copy here<br />\n";
      print "<textarea name='weather' rows=20 cols=40></textarea>\n";
      print "</form>\n";
  }
  
?>
