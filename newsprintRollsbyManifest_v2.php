<?php
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
    $statuses=array(1=>"Received",9=>"Consumed",99=>"Deleted");
    $jstatuses='';
    foreach($statuses as $id=>$value)
    {
        $jstatuses.="'$id':'$value',";
    }
    $jtypes='';
    foreach($papertypes as $id=>$value)
    {
        $jtypes.="'$id':'$value',";
    }
    $jsizes='';
    foreach($sizes as $id=>$value)
    {
        $jsizes.="'$id':'$value',";
    }
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
                  $sql="SELECT id, manifest_number FROM rolls WHERE roll_tag'$checktag'";
                  print "Checking partial with $sql<br />";
                  $dbRoll=dbselectsingle($sql);
                  if ($dbRoll['numrows']>0)
                  {
                    $rollid=$dbRoll['data']['id'];
                    $manifest=$dbRoll['data']['manifest_number'];
                  }
                  $vendors[$vendor['id']]=$vendor['vendor_name'];
              }
              if ($rollid==0)
              {
                  //do one last check without removing anything from the rolltag
                  $sql="SELECT id, manifest_number FROM rolls WHERE roll_tag LIKE '%$rolltag%'";
                  print "Checking full with $sql<br />";
                  $dbRoll=dbselectsingle($sql);
                  if ($dbRoll['numrows']>0)
                  {
                    $rollid=$dbRoll['data']['id'];
                    $manifest=$dbRoll['data']['manifest_number'];
                  } 
              }
          }
          $sql="SELECT * FROM rolls WHERE manifest_number='$manifest'";
          print "Checking for manifest with $sql<br />";
    } elseif ($rolltag=='' && $manifest=='')
    {
        $sql="SELECT * FROM rolls WHERE manifest_number=''";
    } else {
        $sql="SELECT * FROM rolls WHERE manifest_number='$manifest'";
    }
    //calculate total weight
    $wsql="SELECT SUM(roll_weight) as totalweight FROM rolls WHERE manifest_number='$manifest'";
    $dbWeight=dbselectsingle($wsql);
    $tweight=$dbWeight['data']['totalweight'];
    $tweight=($tweight/1000)." MT";
    $dbRolls=dbselectmulti($sql);
    if ($dbRolls['numrows']>0)
    {
        $vid=$dbRolls['data'][0]['order_id'];
        $sql="SELECT B.vendor_name FROM orders A, vendors B WHERE A.id=$vid AND A.vendor_id=B.id";
        $dbVendor=dbselectsingle($sql);
        $vname=$dbVendor['data']['vendor_name'];
        tableStart("<a href='$_SERVER[PHP_SELF]'>Run another report</a>,$dbRolls[numrows] total rolls on this manifest from $vname<br>Total weight on manifest is $tweight","Roll Tag,Type,Width,Weight,Manifest,Receive Date,Batch Process Date,Status",8);
        foreach($dbRolls['data'] as $roll)
        {
            print "<tr>";
            if ($roll['validated']){$validated='<br />Has been validated';}else{$validated='';}
            print "<td>";
            print "<span class='edittext' id='rolltag_$roll[id]'>$roll[roll_tag]</span>";
            //print "<input type='text' size=20 id='rolltag_$roll[id]' value='$roll[roll_tag]'><input type='button' value='Change Rolltag' onclick='changeRollManifest(\"tag\",$roll[id]);'>$validated</td>";
            print "</td>\n";
            print "<td>";
            print "<span class='edittype' id='rollname_$roll[id]'>$roll[common_name]</span>";
            //print input_select('rollname_'.$roll['id'],$roll['common_name'],$papertypes);
            //print "<input type='button' value='Change Paper Type' onclick='changeRollManifest(\"name\",$roll[id]);'></td>";
            print "</td>\n";
             
            print "<td>";
            print "<span class='editsize' id='rollwidth_$roll[id]'>$roll[roll_width]</span>";
            //print input_select('rollwidth_'.$roll['id'],$roll['roll_width'],$sizes);
            //print "<input type='button' value='Change Width' onclick='changeRollManifest(\"width\",$roll[id]);'></td>";
            print "</td>\n";
            
            print "<td>";
            print "<span class='edittext' id='rollweight_$roll[id]'>$roll[roll_weight]</span>kg";
            //print "<input type='text' size=10 id='rollweight_$roll[id]' value='".($roll['roll_weight'])."'>kg";
            //print "<input type='button' value='Change Weight' onclick='changeRollManifest(\"weight\",$roll[id]);'></td>";
            print "</td>\n";
            
            print "<td>";
            print "<span class='edittext' id='rollmanifest_$roll[id]'>$roll[manifest_number]</span>";
            //print "<td><input type='text' size=20 id='rollmanifest_$roll[id]' value='$roll[manifest_number]'><input type='button' value='Change Manifest' onclick='changeRollManifest(\"manifest\",$roll[id]);'></td>";
            print "</td>\n";
            
            print "<td>";
            print "<span class='editdate' id='rolldate_$roll[id]'>$roll[receive_datetime]</span>";
            //print  input_date('rolldate_'.$roll['id'],$roll['receive_datetime']);
            //print "<input type='button' value='Change Date' onclick='changeRollManifest(\"date\",$roll[id]);'></td>";
            print "</td>\n";
            
             print "<td>";
            if ($roll['batch_date']!='')
            {
                print $message.'<br />';
                print "<span class='editdate' id='rollbatch_$roll[id]'>$roll[batch_date]</span>";
                //print  input_date('rollbatch_'.$roll['id'],$roll['batch_date']);
                //print "<input type='button' value='Change Batch Date' onclick='changeRollManifest(\"batch\",$roll[id]);'>";
            }
            print "</td>\n";
            
            /*
            $status=$roll['status'];
            if ($status==1)
            {
                $stat="<span id='rollstatus_$roll[id]'>Received <input type='button' value='Consume' onclick='toggleRollStatus($roll[id],9);'>";
                $stat.="<input type='button' value='Delete' onclick='toggleRollStatus($roll[id],99);'>";
                $stat.="</span>";
            }elseif($status==99)
            {
                $stat="<span id='rollstatus_$roll[id]'>Deleted ";
                $stat.="<input type='button' value='Set to received' onclick='toggleRollStatus($roll[id],1);'>";
                $stat.="</span>";
            } else {
                $stat="<span id='rollstatus_$roll[id]'>Consumed <input type='button' value='Un-consume' onclick='toggleRollStatus($roll[id],1);'>";
                $stat.="<input type='button' value='Delete' onclick='toggleRollStatus($roll[id],99);'>";
                $stat.="</span>";
            }
            */
            print "<td>";
            print "<span class='editstatus' id='rollstatus_$roll[id]'>".$statuses[$roll['status']]."</span>";
            print "</td>";
            
            print "</tr>\n";  
        }
        $script="
            \$('.edittext').editable('includes/ajax_handlers/newsprintManifestUpdates.php',{
                tooltip: 'Click to edit...',
                submit: 'OK',
                cancel: 'Cancel',
                width: '150px'  
            });
            \$('.editstatus').editable('includes/ajax_handlers/newsprintManifestUpdates.php',{
               data   : \" { $jstatuses 'selected':'0'} \",
               type   : 'select',
               submit : 'OK',
               tooltip: 'Click to edit...',
               submit: 'OK',
               cancel: 'Cancel',
               width: '180px'  
            });
            \$('.edittype').editable('includes/ajax_handlers/newsprintManifestUpdates.php',{
               data   : \" { $jtypes 'selected':'0'} \",
               type   : 'select',
               submit : 'OK',
               tooltip: 'Click to edit...',
               submit: 'OK',
               cancel: 'Cancel',
               width: '180px'  
            });
            \$('.editsize').editable('includes/ajax_handlers/newsprintManifestUpdates.php',{
               data   : \" { $jsizes 'selected':'0'} \",
               type   : 'select',
               submit : 'OK',
               tooltip: 'Click to edit...',
               submit: 'OK',
               cancel: 'Cancel',
               width: '180px'  
            });
            \$('.editdate').editable('includes/ajax_handlers/newsprintManifestUpdates.php', {
                 type: 'datepicker',
                 tooltip: 'Click to edit...',
                 event: 'click',
                 submit: 'OK',
                 cancel: 'Cancel',
                 width: '100px'
            });
            \n";
           
        tableEnd($dbRolls,$script);
    } else {
        print "Sorry, no rolls match that manifest/rolltag number.<br /><a href='$_SERVER[PHP_SELF]'>Try a different search</a>.";
    } 
}

footer();
?>

