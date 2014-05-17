<?php
include("includes/mainmenu.php") ;

print "<body>\n";
print "<div id='wrapper'>\n";

 //make sure we have a logged in user...
if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}

if($_POST)
{
    processTags();
} else {
    get_rolltags();
} 
  
  
function get_rolltags()
{
    //build vendor list
    $sql="SELECT * FROM vendors WHERE status=1 AND newsprint=1 ORDER BY vendor_name";
    $dbVendors=dbselectmulti($sql);
    $vendors=array();
    $vendors[0]="Please choose a vendor";
    if ($dbVendors['numrows']>0)
    {
        foreach($dbVendors['data'] as $vendor)
        {
            $vendors[$vendor['id']]=$vendor['vendor_name'];
        }
    
    }
    //paper types
    $sql="SELECT * FROM paper_types ORDER BY common_name";
    $dbPaper=dbselectmulti($sql);
    $papertypes=array();
    $papertypes[0]="Type";
    if ($dbPaper['numrows']>0)
    {
        foreach($dbPaper['data'] as $paper)
        {
            $papertypes[$paper['id']]=$paper['common_name'];
        }
    }
    
    //paper sizes
    $sql="SELECT * FROM paper_sizes ORDER BY width ASC";
    $dbSizes=dbselectmulti($sql);
    $sizes=array();
    $sizes[0]="Size";
    if ($dbSizes['numrows']>0)
    {
        foreach($dbSizes['data'] as $size)
        {
            $sizes[$size['id']]=$size['width'];
        }
    }
    if ($_GET['action']=='new' || $_GET['action']=='edit')
    {
        if ($_GET['action']=='new')
        {
            $vendorid=0;
            $width=11;
            $paperid=0;
            $batchid=0;
        } else {
            $batchid=intval($_GET['batchid']);
            $sql="SELECT * FROM roll_inventory_trueup WHERE id=$batchid";
            $dbBatch=dbselectsingle($sql);
            $batch=$dbBatch['data'];
            $vendorid=$batch['vendor_id'];
            $paperid=$batch['paper_type_id'];
            $width=$batch['roll_width'];
            $tags=$batch['roll_tags'];
        }
        
        
        print "<form method=post>\n";
        make_select('vendor',$vendors[$vendorid],$vendors,'Vendor');
        make_select('papertype',$papertypes[$paperid],$papertypes,'Paper');
        make_select('size',$sizes[$width],$sizes,'Paper');
        make_textarea('rolltags',$tags,'Tags','',60,30,false);
        make_hidden('batchid',$batchid);
        print "<input type='submit' name='submit' value='Save Rolls' />\n";
        print "</form>\n";
    }elseif($_GET['action']=='delete'){
        $batchid=intval($_GET['batchid']);
        $sql="DELETE FROM roll_inventory_trueup WHERE id=$batchid";
        $dbDelete=dbexecutequery($sql);
        redirect("?action=list");
    }elseif($_GET['action']=='analyze'){
        analyze();
    } else {
        print "<table class='grid'>\n";
        print "<tr><th colspan=8><a href='?action=new'>Add new batch</a></th></tr>\n";
        print "<tr><th>Vendor</th><th>Paper Type</th><th>Width</th><th>Date</th><th>Roll count</th><th colspan=3>Action</th></tr>\n";
        $sql="SELECT * FROM roll_inventory_trueup ORDER BY inventory_datetime";
        $dbBatches=dbselectmulti($sql);
        $total=0;
        if ($dbBatches['numrows']>0)
        {
            foreach($dbBatches['data'] as $batch)
            {
                print "<tr>";
                print "<td>".$vendors[$batch['vendor_id']]."</td>";
                print "<td>".$papertypes[$batch['paper_type_id']]."</td>";
                print "<td>".$sizes[$batch['roll_width']]."</td>";
                print "<td>$batch[inventory_datetime]</td>";
                $tags=$batch['roll_tags'];
                $tags=explode("\n",$tags);
                $rollcount=0;
                foreach($tags as $key=>$tag)
                {
                    if (trim($tag)!='')
                    {
                        $rollcount++;
                    }   
                }
                print "<td>$rollcount</td>"; //<br />$batch[roll_tags]
                print "<td><a href='?action=edit&batchid=$batch[id]'>Edit</a></td>";
                print "<td><a href='?action=delete&batchid=$batch[id]'>Delete</a></td>";
                print "<td><a href='?action=analyze&batchid=$batch[id]'>Analyze</a></td>";
                print "</tr>\n";
                $total+=$rollcount;
            }
        }
        print "<tr><th colspan=8>A total of $total rolls in these batches</th></tr>\n";
        print "</table>\n";
    }
}

function analyze()
{
    //build vendor list
    $sql="SELECT * FROM vendors WHERE status=1 AND newsprint=1 ORDER BY vendor_name";
    $dbVendors=dbselectmulti($sql);
    $vendors=array();
    $vendors[0]="Please choose a vendor";
    if ($dbVendors['numrows']>0)
    {
        foreach($dbVendors['data'] as $vendor)
        {
            $vendors[$vendor['id']]=$vendor['vendor_name'];
        }
    
    }
    print "<h3><a href='?action=list'>Click here to analyze another batch</a></h3>";
    
    $batchid=intval($_GET['batchid']);
    $sql="SELECT A.*, B.common_name, C.width FROM roll_inventory_trueup A, paper_types B, paper_sizes C 
    WHERE A.id=$batchid AND A.paper_type_id=B.id AND A.roll_width=C.id";
    $dbBatch=dbselectsingle($sql);
    $batch=$dbBatch['data'];
    $tags=$batch['roll_tags'];
    $tags=explode("\n",$tags);
    $sql="SELECT * FROM vendors WHERE newsprint=1";
    $dbVendors=dbselectmulti($sql);
    $inventory=array();
    $consumed=array();
    $missing=array();
    $icount=0;
    $ccount=0;
    $mcount=0;
    $processed=array();
    $found=array();
    if ($dbVendors['numrows']>0)
    {
        
        foreach ($tags as $key=>$tag)
        {
            $rollid=0;
            $rolltag=strtoupper($tag);
            if (!in_array($rolltag,$processed))
            {
                $processed[]=$rolltag;
                if (trim($rolltag)!='')
                {
                    //ok, we have a roll tag, lets do some looking
                    //first, lets see if it is a valid roll tag
                    foreach($dbVendors['data'] as $vendor)
                    { 
                          
                          if (!in_array($rolltag,$found))
                          {
                              $vendorid=$vendor['id'];
                              $rollremoval=$vendor['rolltag_removal'];
                              //ok, what we are going to have to do is check the rolls after massaging the rolltag for each vendor
                              $checktag=substr($rolltag,$rollremoval);//this should do it
                              $checktag=trim($checktag);
                              //print "Scanned Tag - $rolltag -> checking with $checktag<br />";
                              $sql="SELECT A.*, B.vendor_id FROM rolls A, orders B WHERE A.roll_tag='$checktag' AND A.order_id=B.id";
                              $dbRoll=dbselectsingle($sql);
                              if ($dbRoll['numrows']>0)
                              {
                                    //print "------found a roll<br />";
                                    $rollid=$dbRoll['data']['id'];
                                    $status=$dbRoll['data']['status'];
                                    if ($status=='9')
                                    {
                                        if (!in_array($rolltag,$consumed))
                                        {
                                            $consumed[$ccount]['rolltag']=$rolltag;
                                            $consumed[$ccount]['rollinfo']=$dbRoll['data'];
                                            $consumed[$ccount]['vendor']=$vendors[$batch['vendor_id']];
                                            $consumed[$ccount]['size']=$batch['width'];
                                            $consumed[$ccount]['name']=$batch['common_name'];
                                            $ccount++;
                                            $found[]=$rolltag;
                                        }
                                    } else {
                                        if (!in_array($rolltag,$inventory))
                                        {
                                            $inventory[$icount]['rolltag']=$rolltag;
                                            $inventory[$icount]['rollinfo']=$dbRoll['data'];
                                            $inventory[$icount]['vendor']=$vendors[$batch['vendor_id']];
                                            $inventory[$icount]['size']=$batch['width'];
                                            $inventory[$icount]['name']=$batch['common_name'];
                                            $icount++;
                                            $found[]=$rolltag;

                                        }
                                        
                                    }
                              }
                          }
                      }
                      if ($rollid==0)
                      {
                           //do one last check without removing anything from the rolltag
                          $sql="SELECT * FROM rolls WHERE roll_tag='$rolltag'";
                          $dbRoll=dbselectsingle($sql);
                          if ($dbRoll['numrows']>0)
                          {
                              //print "found a rolltag with the full tag -- $rolltag..<br />";
                              $rollid=$dbRoll['data']['id'];
                            $status=$dbRoll['data']['status'];
                            if ($status=='9')
                            {
                                if (!in_array($rolltag,$consumed))
                                    {
                                        $consumed[$ccount]['rolltag']=$rolltag;
                                        $consumed[$ccount]['rollinfo']=$dbRoll['data'];
                                        $consumed[$ccount]['vendor']=$vendors[$batch['vendor_id']];
                                        $consumed[$ccount]['size']=$batch['width'];
                                        $consumed[$ccount]['name']=$batch['common_name'];
                                        $ccount++;
                                    }
                            } else {
                                //ok, we've found a real roll tag, and it has not been processed to completion by the business office before
                                //so now we just need to update with a status of 9
                                if (!in_array($rolltag,$inventory))
                                    {
                                        $inventory[$icount]['rolltag']=$rolltag;
                                        $inventory[$icount]['rollinfo']=$dbRoll['data'];
                                        $inventory[$icount]['vendor']=$vendors[$batch['vendor_id']];
                                        $inventory[$icount]['size']=$batch['width'];
                                        $inventory[$icount]['name']=$batch['common_name'];
                                        $icount++;
                                    }
                                
                            }
                          } 
                      }
                      if ($rollid==0)
                      {
                          //print "------didn't find it at all<br />";
                          if (!in_array($rolltag,$missing))
                          {
                            $missing[$mcount]['rolltag']=$rolltag;
                            $missing[$mcount]['rollinfo']='';
                            $missing[$mcount]['vendor']=$vendors[$batch['vendor_id']];
                            $missing[$mcount]['size']=$batch['width'];
                            $missing[$mcount]['name']=$batch['common_name'];
                            $mcount++;
                            
                          }
                                    
                      }
                }
            
            }
        }
    }
    $dt=date("Y-m-d H:i:s");
                         
    if (count($missing)>0)
    {
        print "<table class='grid'>\n";
        print "<tr><th colspan=8>The following rolls were not found in the system. The roll tags are either invalid or have not been entered:</th></tr>\n";
        print "<tr><th>Roll Tag</th><th>Batch Vendor</th><th>Batch type</th><th>Batch size</th>
        <th>System Vendor</th><th>System rolltag</th><th>System type</th><th>System Size</th><th>Manifest</th><th>Status</th></tr>";
        foreach($missing as $roll)
        {
            $rolltag=$roll['rolltag'];
            $rollinfo=$roll['rollinfo'];
            print "<tr>";
            print "<td>$rolltag</td>";
            print "<td>$roll[vendor]</td>";
            print "<td>$roll[name]</td>";
            print "<td>$roll[size]</td>";
            print "<td>".$vendors[$rollinfo['vendor_id']]."</td>";
            print "<td>$rollinfo[roll_tag]</td>";
            print "<td>$rollinfo[common_name]</td>";
            print "<td>$rollinfo[roll_width]</td>";
            print "<td>$rollinfo[manifest_number]</td>";
            print "<td style='font-color:red;'>MISSING</td>";
            print "</tr>\n";
            
        }
        print "</table>\n";
    }
    print "<br />";
    if (count($inventory)>0)
    {
        print "<table class='grid'>\n";
        print "<tr><th colspan=8>The following rolls were found in the inventory</th></tr>\n";
        print "<tr><th>Roll Tag</th><th>Batch Vendor</th><th>Batch type</th><th>Batch size</th>
        <th>System Vendor</th><th>System rolltag</th><th>System type</th><th>System Size</th><th>Manifest</th><th>Status</th></tr>";
        foreach($inventory as $roll)
        {
            $status='';
            $verror=0;
            $rolltag=$roll['rolltag'];
            $rollinfo=$roll['rollinfo'];
            $rollid=$rollinfo['id'];
            print "<tr>";
            print "<td>$rolltag</td>";
            print "<td>$roll[vendor]</td>";
            print "<td>$roll[name]</td>";
            print "<td>$roll[size]</td>";
            print "<td>".$vendors[$rollinfo['vendor_id']]."</td>";
            print "<td>$rollinfo[roll_tag]</td>";
            print "<td>$rollinfo[common_name]</td>";
            print "<td>$rollinfo[roll_width]</td>";
            print "<td>$rollinfo[manifest_number]</td>";
            if($vendors[$rollinfo['vendor_id']]!=$roll['vendor'])
            {
                $status.='Vendor conflict<br />';
                $verror=1;
            }
            if($rollinfo['common_name']!=$roll['name'])
            {
                $status.='Type conflict<br />';
                $verror+=2;
            }
            if($rollinfo['roll_width']!=$roll['size'])
            {
                $status.='Size conflict<br />';
                $verror+=4;
            }
            if ($status==''){$status='OK';$verror=0;}
            print "<td style='font-color:red;'>$status</td>";
            print "</tr>\n";
            $sql="UPDATE rolls SET validated=1, validated_datetime='$dt', validation_error='$verror' WHERE id=$rollid";
            $dbUpdate=dbexecutequery($sql);
            if ($dbUpdate['error']!='')
            {
                print "<tr><td colspan=6>$dbUpdate[error]</td></tr>\n";
            }
        }
        print "</table>\n";
    }
    print "<br />";
    if (count($consumed)>0)
    {
        print "<table class='grid'>\n";
        print "<tr><th colspan=9>The following rolls were tagged as having been already consumed</th></tr>\n";
        print "<tr><th>Roll Tag</th><th>Batch Vendor</th><th>Batch type</th><th>Batch size</th>
        <th>System Vendor</th><th>System rolltag</th><th>System type</th><th>System Size</th><th>Manifest</th><th>Status</th></tr>";
        foreach($consumed as $roll)
        {
            $status='';
            $verror=0;
            $rolltag=$roll['rolltag'];
            $rollinfo=$roll['rollinfo'];
            $rollid=$rollinfo['id'];
            print "<tr>";
            print "<td>$rolltag</td>";
            print "<td>$roll[vendor]</td>";
            print "<td>$roll[name]</td>";
            print "<td>$roll[size]</td>";
            print "<td>".$vendors[$rollinfo['vendor_id']]."</td>";
            print "<td>$rollinfo[roll_tag]</td>";
            print "<td>$rollinfo[common_name]</td>";
            print "<td>$rollinfo[roll_width]</td>";
            print "<td>$rollinfo[manifest_number]</td>";
            if($vendors[$rollinfo['vendor_id']]!=$roll['vendor'])
            {
                $status.='Vendor conflict<br />';
                $verror=1;
            }
            if($rollinfo['common_name']!=$roll['name'])
            {
                $status.='Type conflict<br />';
                $verror+=2;
            }
            if($rollinfo['roll_width']!=$roll['size'])
            {
                $status.='Size conflict<br />';
                $verror+=4;
            }
            if ($status==''){$status='OK';$verror=0;}
            print "<td style='font-color:red;'>$status</td>";
            print "</tr>\n";
            $sql="UPDATE rolls SET validated=1, validated_datetime='$dt', validation_error='$verror' WHERE id=$rollid";
            $dbUpdate=dbexecutequery($sql);
            if ($dbUpdate['error']!='')
            {
                print "<tr><td colspan=6>$dbUpdate[error]</td></tr>\n";
            }
        }
        print "</table>\n";
    }
}

function processTags()
{
    $batchid=intval($_POST['batchid']);
    $vendorid=addslashes($_POST['vendor']);
    $paperid=addslashes($_POST['papertype']);
    $rollwidth=addslashes($_POST['size']);
    $tags=addslashes($_POST['rolltags']);
    $date=date("Y-m-d H:i");
    if ($batchid==0)
    {
        $sql="INSERT INTO roll_inventory_trueup (vendor_id, paper_type_id, roll_width, roll_tags, inventory_datetime)
         VALUES ('$vendorid', '$paperid', '$rollwidth', '$tags', '$date')";
         $dbInsert=dbinsertquery($sql);
         $error=$dbInsert['error'];
    } else {
        $sql="UPDATE roll_inventory_trueup SET vendor_id='$vendorid', paper_type_id='$paperid', 
        roll_width='$rollwidth', roll_tags='$tags' WHERE id=$batchid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        
    }
    if ($error!='')
    {
        print $error;
    } else {
        redirect("?action=list");
    }
}
dbclose();
?>
</body>
</html>
