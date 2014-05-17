<?php
//<!--VERSION: .9 **||**-->

include("includes/mainmenu.php") ;


if ($_POST['submit']=='Get Rolls')
{
    show_rolls();
} else {
    print "<form method=post>\n";
    print "<h2>Search for a manifest by entering the manifest number, or the roll tag of a roll from the manifest.</h2>\n";
    print "Enter manifest number: ";
    print input_text('manifest','');
    print "<br />\n";
    print "Enter roll tag number: ";
    print input_text('rolltag','');
    print "<br /><input type='submit' name='submit' value='Get Rolls'>\n";
    print "</form>\n";
}

function show_rolls()
{
    global $siteID, $papertypes, $sizes;
    $statuses=array(1=>"Received",9=>"Consumed");
    $manifest=$_POST['manifest'];
    $rolltag=$_POST['rolltag'];
    if ($rolltag!='' && $manifest=='')
    {
          $sql="SELECT * FROM accounts WHERE newsprint=1";
          $dbVendors=dbselectmulti($sql);
          if ($dbVendors['numrows']>0)
          {
              $rollid=0;
              foreach($dbVendors['data'] as $vendor)
              { 
                  
                  $vendorid=$vendor['id'];
                  $rollremoval=$vendor['rolltag_removal'];
                  //ok, what we are going to have to do is check the rolls after massaging the rolltag for each vendor
                  $checktag=substr($rolltag,$rollremoval);//this should do it
                  $sql="SELECT id, manifest_number FROM rolls WHERE roll_tag='$checktag'";
                  $dbRoll=dbselectsingle($sql);
                  if ($dbRoll['numrows']>0)
                  {
                    $rollid=$dbRoll['data']['id'];
                    $manifest=$dbRoll['data']['manifest_number'];
                  }
              }
          }
          if ($rollid==0)
          {
              //do one last check without removing anything from the rolltag
              $sql="SELECT id, manifest_number FROM rolls WHERE roll_tag='$rolltag'";
              $dbRoll=dbselectsingle($sql);
              if ($dbRoll['numrows']>0)
              {
                $rollid=$dbRoll['data']['id'];
                $manifest=$dbRoll['data']['manifest_number'];
              } 
          }
          $sql="SELECT * FROM rolls WHERE manifest_number='$manifest'";
    } elseif ($rolltag=='' && $manifest=='')
    {
        $sql="SELECT * FROM rolls WHERE manifest_number=''";
    } else {
        $sql="SELECT * FROM rolls WHERE manifest_number LIKE '%$manifest%'";
    }
    $dbRolls=dbselectmulti($sql);
    if ($dbRolls['numrows']>0)
    {
        print "<table class='grid'>\n";
        print "<tr><th colspan=8><a href='$_SERVER[PHP_SELF]'>Run another report</a></th></tr>\n";
        print "<tr><th colspan=8>$dbRolls[numrows] total rolls on this manifest</th></tr>\n";
        print "<tr><th>Roll Tag</th><th>Type</th><th>Width</th><th>Weight</th><th>Manifest</th><th>Receive Date</th><th>Batch Process Date</th><th>Status</th></tr>\n";
        foreach($dbRolls['data'] as $roll)
        {
            print "<tr>";
            print "<td><input type='text' size=20 id='rolltag_$roll[id]' value='$roll[roll_tag]'><input type='button' value='Change Rolltag' onclick='changeRollManifest(\"rolltag\",$roll[id],\"field\");'></td>";
            
            print "<td>";
            print input_select('rollname_'.$roll['id'],$roll['common_name'],$papertypes);
            print "<input type='button' value='Change Paper Type' onclick='changeRollManifest(\"rollname\",$roll[id],\"field\");'></td>";
            
            print "<td>";
            print input_select('rollwidth_'.$roll['id'],$roll['roll_width'],$sizes);
            print "<input type='button' value='Change Width' onclick='changeRollManifest(\"rollwidth\",$roll[id],\"field\");'></td>";
            
            print "<td><input type='text' size=10 id='rollweight_$roll[id]' value='".($roll['roll_weight'])."'>kg<input type='button' value='Change Weight' onclick='changeRollManifest(\"rollweight\",$roll[id],\"field\");'></td>";
            
            print "<td><input type='text' size=20 id='rollmanifest_$roll[id]' value='$roll[manifest_number]'><input type='button' value='Change Manifest' onclick='changeRollManifest(\"rollmanifest\",$roll[id],\"field\");'></td>";
            
            print "<td>";
            print  input_date('rolldate_'.$roll['id'],$roll['receive_datetime']);
            print "<input type='button' value='Change Date' onclick='changeRollManifest(\"rolldate\",$roll[id],\"field\");'></td>";
            
            print "<td>";
            
            if ($roll['batch_date']!='')
            {
                print $message.'<br />';
                print input_date('rollbatch_'.$roll['id'],$roll['batch_date']);
                print "<input type='button' value='Change Batch Date' onclick='changeRollManifest(\"rollbatch\",$roll[id],\"field\");'>";
            }
            print "<span id='rollbatchdate_$roll[id]'></span>\n";
            print "</td>\n";
            $status=$roll['status'];
            if ($status==1)
            {
                $stat="<span><span id='rollstatus_$roll[id]'>Received</span><input type='button' value='Consume' onclick='changeRollManifest(\"rollstatus\",$roll[id],9);'>";
                $stat.="<input type='button' value='Delete' onclick='changeRollManifest(\"rollstatus\",$roll[id],99)'>";
                $stat.="</span>";
            }elseif($status==99)
            {
                $stat="<span><span id='rollstatus_$roll[id]'>Deleted</span>";
                $stat.="<input type='button' value='Set to received' onclick='changeRollManifest(\"rollstatus\",$roll[id],1)'>";
                $stat.="</span>";
            } else {
                $stat="<span><span id='rollstatus_$roll[id]'>Consumed</span><input type='button' value='Un-consume' onclick='changeRollManifest(\"rollstatus\",$roll[id],1)'>";
                $stat.="<input type='button' value='Delete' onclick='changeRollManifest(\"rollstatus\",$roll[id],99)'>";
                $stat.="</span>";
            }
            print "<td>$stat</td>";
            print "</tr>\n";  
        }
        print "</table>\n";
    } else {
        print "Sorry, no rolls match that manifest number.<br /><a href='$_SERVER[PHP_SELF]'>Try a different search</a>.";
    } 
}

footer();
?>

