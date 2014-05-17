<?php
  //generates a block of checkboxes for zonings
  include("../functions_db.php");
  include("../config.php");
  include("../functions_common.php");
  include("../functions_formtools.php");
  $runid=intval($_POST['runid']);
  $insertid=intval($_POST['insertid']);
  $schedid=intval($_POST['schedid']);
  $sql="SELECT * FROM publications_insertzones WHERE run_id=$runid";
  $dbZones=dbselectmulti($sql);
  if($dbZones['numrows']>0)
  {
      print "<p>Please select zones:</p>";
      print "<input type='checkbox' onclick='toggleCheckBoxes(this.checked,\"insertzones\");'>Select / deselect all zones<br />";
      //we are going to format the zones in two columns to conserve space
      $zcount=$dbZones['numrows'];
      $wrapat=round($zcount/3);
      print "<div style='float:left;width:150px;margin-left:4px;'>\n";
      $i=1;
      foreach($dbZones['data'] as $zone)
      {
          //see if this one is checked
          if($insertid!='')
          {
              $sql="SELECT * FROM insert_zoning WHERE sched_id=$schedid AND zone_id=$zone[id]";
              $dbCheck=dbselectsingle($sql);
              if($dbCheck['numrows']>0){$checked='checked';$total+=$zone['zone_count'];}else{$checked='';}
          }
          print "<input rel='$zone[zone_count]' onclick='calcInsertZoneTotal(\"insertzones\");' class='insertzones' type=checkbox id='check_$zone[id]' name='check_$zone[id]' $checked/> $zone[zone_name]<br />";
          if ($i==$wrapat)
          {
             print "</div>\n";
             print "<div style='float:left;width:150px;'>\n";
          }
          $i++;
      }
      print "</div><div class='clear'></div>\n";
      print "<p><span id='zonetotal' style='font-weight:bold;'>$total</span> total inserts required for selected zones.</p>";
      print "<input type='hidden' id='zoned_total' name='zoned_total' value='$total' />\n";
  
  } else {
      displayMessage('No zones have been defined for that run yet.','error','true','Ok',180);
  }
?>
