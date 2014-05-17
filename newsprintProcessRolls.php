<?php
//<!--VERSION: .9 **||**-->

include("includes/mainmenu.php") ;

if ($_POST)
{
    verify_rolls();
} else {
    process_business_rolls();
}

function process_business_rolls()
{
    global $siteID;
    $menu=$_GET['menu'];   
    $bdate=date("Y-m-d");
    print "<form action=\"$_SERVER[PHP_SELF]\" method=post enctype='multipart/form-data'>\n";
    print input_date('batchdate',$bdate,'Batch Date');
    print "<div id='rolls' style='margin-left:70px;'>\n";
    print "Press enter or return, or click 'Add Roll' to add another roll. &nbsp;&nbsp;&nbsp;";
    make_file('rolls','Bulk Import','Upload a file containg rolltags for a full batch');
    print "<input type=button name='addjobroll' id='addjobroll' value='Add roll' onClick='addBusinessRoll();'/>\n";        
    print "<br /><hr>\n";
    print "Roll tag: ".input_text('newroll_1','',20,false,'','','',"newsprintKeyCapture(this.id,event,false,'addrecroll');return false;");
    print "<hr>\n";
    print "</div>\n";
    print "
    <script type='text/javascript'>
    document.getElementById('newroll_1').focus();
    </script>
    ";
    
    print "<input type='hidden' name='lastroll' id='lastroll' value='2' />\n";
    print "<div class='label'></div>\n";
    print "<div class='input'>\n";
    print "<input type='submit' name='submit' id='submit' value='Verify Rolls' />\n";
    print "</div>\n";
    print "<div class='clear'></div>\n";
    print "</form>\n";
}

function verify_rolls()
{
    global $siteID;
    $missing=array();
    $alreadyused=array();
    $processed=array();
    $bdate=$_POST['batchdate'];
    $sql="SELECT * FROM accounts WHERE newsprint=1";
    $dbVendors=dbselectmulti($sql);
    if ($dbVendors['numrows']>0)
    {
        $rollid=0;
        if(isset($_FILES) && $_FILES['rolls']['tmp_name']!='')
        {
            $file=$_FILES['rolls']['tmp_name'];
            $contents=file_get_contents($file);
            $lines=explode("\n",$contents);
            foreach($lines as $line)
            {
                $lineparts=explode(",",$line);
                $rolltag=$lineparts[0];
                foreach($dbVendors['data'] as $vendor)
                    { 
                          
                          $vendorid=$vendor['id'];
                          $rollremoval=$vendor['rolltag_removal'];
                          //ok, what we are going to have to do is check the rolls after massaging the rolltag for each vendor
                          $checktag=substr($rolltag,$rollremoval);//this should do it
                          $sql="SELECT id, status FROM rolls WHERE roll_tag='$checktag'";
                          $dbRoll=dbselectsingle($sql);
                          if ($dbRoll['numrows']>0)
                          {
                            $rollid=$dbRoll['data']['id'];
                            $status=$dbRoll['data']['status'];
                            if ($status=='9')
                            {
                                if (!in_array($rolltag,$alreadyused))
                                {
                                    $alreadyused[]=$rolltag;    
                                }
                                
                            } else {
                                //ok, we've found a real roll tag, and it has not been processed to completion by the business office before
                                //so now we just need to update with a status of 9
                                if (!in_array($rolltag,$processed))
                                {
                                    $processed[]=$rolltag;
                                }
                                $sql="UPDATE rolls SET status='9', batch_date='$bdate' WHERE id=$rollid";
                                $dbUpdate=dbexecutequery($sql);
                            }
                          }
                    }
                  if ($rollid==0)
                  {
                      //do one last check without removing anything from the rolltag
                      $sql="SELECT id, status FROM rolls WHERE roll_tag='$rolltag'";
                      $dbRoll=dbselectsingle($sql);
                      if ($dbRoll['numrows']>0)
                      {
                        $rollid=$dbRoll['data']['id'];
                        $status=$dbRoll['data']['status'];
                        if ($status=='9')
                        {
                            if (!in_array($rolltag,$alreadyused))
                                {
                                    $alreadyused[]=$rolltag;    
                                }
                        } else {
                            //ok, we've found a real roll tag, and it has not been processed to completion by the business office before
                            //so now we just need to update with a status of 9
                            if (!in_array($rolltag,$processed))
                                {
                                    $processed[]=$rolltag;
                                }
                            
                            $sql="UPDATE rolls SET status='9', batch_date='$bdate' WHERE id=$rollid";
                            $dbUpdate=dbexecutequery($sql);
                             
                        }
                      } 
                  }
                  if ($rollid==0)
                  {
                      if (!in_array($rolltag,$missing))
                    {
                        $missing[]=$rolltag;
                    }
                                
                  }
            }
            
        } else {
            foreach ($_POST as $key=>$value)
            {
                if (strpos($key,"roll_")>0)
                {
                    $rolltag=strtoupper($value);
                    //ok, we have a roll tag, lets do some looking
                    //first, lets see if it is a valid roll tag
                    foreach($dbVendors['data'] as $vendor)
                    { 
                          
                          $vendorid=$vendor['id'];
                          $rollremoval=$vendor['rolltag_removal'];
                          //ok, what we are going to have to do is check the rolls after massaging the rolltag for each vendor
                          $checktag=substr($rolltag,$rollremoval);//this should do it
                          $sql="SELECT id, status FROM rolls WHERE roll_tag='$checktag'";
                          $dbRoll=dbselectsingle($sql);
                          if ($dbRoll['numrows']>0)
                          {
                            $rollid=$dbRoll['data']['id'];
                            $status=$dbRoll['data']['status'];
                            if ($status=='9')
                            {
                                if (!in_array($rolltag,$alreadyused))
                                {
                                    $alreadyused[]=$rolltag;    
                                }
                                
                            } else {
                                //ok, we've found a real roll tag, and it has not been processed to completion by the business office before
                                //so now we just need to update with a status of 9
                                if (!in_array($rolltag,$processed))
                                {
                                    $processed[]=$rolltag;
                                }
                                $sql="UPDATE rolls SET status='9', batch_date='$bdate' WHERE id=$rollid";
                                $dbUpdate=dbexecutequery($sql);
                            }
                          }
                    }
                  if ($rollid==0)
                  {
                      //do one last check without removing anything from the rolltag
                      $sql="SELECT id, status FROM rolls WHERE roll_tag='$rolltag'";
                      $dbRoll=dbselectsingle($sql);
                      if ($dbRoll['numrows']>0)
                      {
                        $rollid=$dbRoll['data']['id'];
                        $status=$dbRoll['data']['status'];
                        if ($status=='9')
                        {
                            if (!in_array($rolltag,$alreadyused))
                                {
                                    $alreadyused[]=$rolltag;    
                                }
                        } else {
                            //ok, we've found a real roll tag, and it has not been processed to completion by the business office before
                            //so now we just need to update with a status of 9
                            if (!in_array($rolltag,$processed))
                                {
                                    $processed[]=$rolltag;
                                }
                            
                            $sql="UPDATE rolls SET status='9', batch_date='$bdate' WHERE id=$rollid";
                            $dbUpdate=dbexecutequery($sql);
                             
                        }
                      } 
                  }
                  if ($rollid==0)
                  {
                      if (!in_array($rolltag,$missing))
                    {
                        $missing[]=$rolltag;
                    }
                                
                  }
                }
            }
        }
    }
    
    if (count($missing)>0)
    {
        print "<p style='font-weight:bold;font-size:16px;'>The following rolls were not found in the system. The roll tags are either invalid or has not been entered:</p>\n";
        print "<ul>\n";
        foreach($missing as $key=>$value)
        {
            print "<li>$value</li>\n";
        }
        print "</ul>\n";
    }
    if (count($processed)>0)
        print "<p style='font-weight:bold;font-size:16px;'>The following rolls were processed normally:</p>\n";
        {
          print "<ul>\n";
          foreach($processed as $key=>$value)
          {
            print "<li>$value</li>\n";
          }
          print "</ul>\n";  
        }
    //show warning for any missing or used roll tags
    print "<div style='margin-left:70px;'>\n";
    if (count($alreadyused)>0)
    {
        print "<p style='font-weight:bold;font-size:16px;'>The following rolls were already tagged as being used:</p>\n";
        print "<ul>\n";
        foreach($alreadyused as $key=>$value)
        {
            print "<li>$value</li>\n";
        }
        print "</ul>\n";
    }
    
    
        print "<p style='font-weight:normal;font-size:12px;'><a href='$_SERVER[PHP_SELF]'>Click here to process more rolls</a></p>";
    
    print "</div>\n";
}

footer();


?>
