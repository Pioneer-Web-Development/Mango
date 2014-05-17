<?php
include('includes/mainmenu.php');
  //fix weather script for newsroom
  //this script takes out some unnecessary data
  if ($_POST)
  {
      $global=array("Baghdad","Beijing","Berlin","Guatemala","Kabul","London","Mexico City","Paris","Rome","Sydney","Tokyo");
      $northwest=array("Billings","Denver","Portland,Ore.","Salt Lake City","Seattle","Spokane");
      $us=array("Albuquerque","Anchorage","Atlanta","Atlantic City","Baltimore","Birmingham","Bismarck","Boston","Buffalo","Casper","Charleston,S.C.","Charlotte,N.C.","Cheyenne","Chicago","Cincinnati","Columbus,Ohio","Dallas-Ft Worth","Des Moines","Detroit","El Paso","Fairbanks","Fargo","Great Falls","Helena","Honolulu","Houston","Indianapolis","Jackson,Miss.","Jacksonville","Juneau","Kansas City","Las Vegas","Little Rock","Los Angeles","Louisville","Memphis","Miami Beach","Milwaukee","Mpls-St Paul","Nashville","New Orleans","New York City","Oklahoma City","Omaha","Orlando","Pendleton","Philadelphia","Phoenix","Pittsburgh","Portland,Maine","Raleigh-Durham","Rapid City","Reno","Richmond","Sacramento","St Louis","San Diego","San Francisco","Sioux Falls","Tucson","Washington,D.C.","Wichita");
      $idaho=array("challis", "coeur d alene","idaho falls","jerome","lewiston","mccall","mtn home afb","nampa","ontario or","pocatello","rexburg","salmon","stanley","twin falls");
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
                  if (in_array($parts[0],$global))
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
                  if (in_array($parts[0],$us))
                  {
                      $newline=$city.chr(9).$hi.chr(9).$low.chr(9).$precip.chr(9).$cond;
                      $newtext.=$newline."\n";
                  }
                  
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
                  if (in_array(strtolower($station),$idaho))
                  {
                      $newline=$station.chr(9).$max.chr(9).$min.chr(9).$prec;
                      $newtext.=$newline."\n";
                  }
              }
          }
      }
      if ($type=='northwest')
      {
          foreach($lines as $line)
          {
              if (trim($line)!='')
              {
                  $parts=explode(";",$line);
                  $max=trim($parts[1]);
                  $min=trim($parts[2]);
                  $prec=trim($parts[3]);
                  if (in_array($parts[0],$northwest))
                  {
                      $newline=$parts[0].chr(9).$max.chr(9).$min.chr(9).$prec;
                      $newtext.=$newline."\n";
                  }
              }
          }
      }
      make_textarea('results',$newtext,'Results','The formatted text',40,20,false);
      print "<div class='label'></div><div class='input'>";
      print "<a href='?action=again' class='submit'>Run again</a><br />\n";
      print "</div><div class='clear'></div>";
      
  } else {
      print "<form method=post>\n";
      print "<div class='label'>Select weather type</div><div class='input'>";
      print "<select name='type'>\n";
      print "<option value='global'>Global Weather</option>\n";
      print "<option value='us'>US Weather</option>\n";
      print "<option value='northwest'>Northwest</option>\n";
      print "<option value='regional'>Regional/Idaho</option>\n";
      print "</select>\n";
      print "</div><div class='clear'></div>";
      make_textarea('weather','','Raw Copy','Paste the raw text to be formatted',40,20,false);
      make_submit('submit','Fix the weather');
      print "</form>\n";
  }
  
function strtotitle($word) // Converts $title to Title Case, and returns the result.
{
    $word=strtolower($word);
    $wordstart=substr($word,0,1);
    $wordstart=strtoupper($wordstart);
    $word=$wordstart.substr($word,1);
    return $word;
} 

footer(); 
?>
