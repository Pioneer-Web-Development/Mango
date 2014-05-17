<?php
  //handles calls from the job monitor - pagination & plate scripts
  include("../functions_db.php");
  include("../config.php");
  include("../functions_common.php");
  include("../functions_formtools.php");
  
  $action=$_POST['action'];
  $id=$_POST['id'];
  $value=$_POST['value'];
  $type=$_POST['type'];
  switch($action)
  {
      case "plateextra":
      plateextra($id,$type,$value);
      break;
      
      case "getpagedetails":
      getpagedetails($id,$type,$value);
      break;
      
      case "getpageversions":
      getpageversions($id,$type,$value);
      break;
      
      case "getplatedetails":
      getplatedetails($id,$type,$value);
      break;
      
      case "getplateversions":
      getplateversions($id,$type,$value);
      break;
      
      case "settime":
      settime($id,$type,$value);
      break;
      
      case "remakepage":
      remakepage($id);
      break;
      
      case "undoremake":
      undoremake($id,$value);
      break;
      
      case "deadlines":
      deadlines($id);
      break;
      
  }
  
  function plateextra($id,$type,$value)
  {
        
      $jobid=$_POST['jobid'];
      if ($type=='remake')
      {
          $field='plates_remake';
          $sql="UPDATE job_stats SET $field='$value' WHERE job_id=$jobid";
          $dbUpdate=dbexecutequery($sql);
          $error=$dbUpdate['error'];
      } elseif($type=='waste')
      {
          $field='plates_waste';
          $sql="UPDATE job_stats SET $field='$value' WHERE job_id=$jobid";
          $dbUpdate=dbexecutequery($sql);
          $error=$dbUpdate['error']; 
      } elseif($type=='lastpage')
      {
          $field='plateroom_lastpage';
          $sql="UPDATE job_stats SET $field='$value' WHERE job_id=$jobid";
          $dbUpdate=dbexecutequery($sql);
          $error=$dbUpdate['error'];       
      } elseif($type=='lastpagetime')
      {
          $value=date("Y-m-d").' '.$value;
          $field='plateroom_lastpage_time';
          $sql="UPDATE job_stats SET $field='$value' WHERE job_id=$jobid";
          $dbUpdate=dbexecutequery($sql);
          $error=$dbUpdate['error']; 
      }
      if ($error=='')
      {
          print "success|$sql";
      } else {
          print "error|$error";
      }
  }
  
  function deadlines($jobid)
  {
       print "success|";
       $sql="SELECT * FROM jobs WHERE id=$jobid";
       $dbJob=dbselectsingle($sql);
       $job=$dbJob['data']; 
       //gather all the data about this job
       $sql="SELECT * FROM publications WHERE id=$job[pub_id]";
       $dbPub=dbselectsingle($sql);
       $pub=$dbPub['data'];
       $pubname=$pub['pub_name'];
       
       $sql="SELECT * FROM publications_runs WHERE id=$job[run_id]";
       $dbRun=dbselectsingle($sql);
       $run=$dbRun['data'];
       $runname=$run['run_name'];
       $scheduledstart=$job['startdatetime'];
       $dow=date("N", strtotime($scheduledstart));
       $colorlead=$run['last_colorpage_leadtime_'.$dow];
       $pagelead=$run['last_page_leadtime_'.$dow];
       $platelead=$run['last_plate_leadtime_'.$dow];
       $colordeadline=date("H:i",strtotime($scheduledstart."-$colorlead minutes"));     
       $pagedeadline=date("H:i",strtotime($scheduledstart."-$pagelead minutes"));     
       $platedeadline=date("H:i",strtotime($scheduledstart."-$platelead minutes"));     
       $fullcolordeadline=strtotime($scheduledstart."-$colorlead minutes");     
       $fullpagedeadline=strtotime($scheduledstart."-$pagelead minutes");     
       $fullplatedeadline=strtotime($scheduledstart."-$platelead minutes");     
       $now=time();
       //get number of plates still out
       $sql="SELECT COUNT(id) as platesout FROM job_plates WHERE job_id=$jobid AND plate_approval IS Null";
       $dbPlatesOut=dbselectsingle($sql);
       $platesout=$dbPlatesOut['data']['platesout'];         
       //get number of pages still out
       $sql="SELECT COUNT(id) as pagesout FROM job_pages WHERE job_id=$jobid AND page_release IS Null AND current=1";
       $dbPagesOut=dbselectsingle($sql);
       $pagesout=$dbPagesOut['data']['pagesout'];         
       //get number of color pages still out
       $sql="SELECT COUNT(id) as colorout FROM job_pages WHERE job_id=$jobid AND color=1 AND color_release IS Null AND current=1";
       $dbColorOut=dbselectsingle($sql);
       $colorout=$dbColorOut['data']['colorout'];         
       
       print "<li>".date("H:i")." current system time</li>\n";
       print "<li>".date("H:i",strtotime($scheduledstart))." Press start</li>\n";
       print "<li>$platedeadline Last plate release</li>\n";
       if ($now>$fullplatedeadline && $platesout>0)
       {
           print "<li style='font-weight:bold;color:red;'>Passed last plate deadline with $platesout plates remaining</li>";
       } else {
           print "<li>$platesout plates remaining</li>";
       }
       print "<li>$pagedeadline Last page release</li>\n";
       if ($now>$fullpagedeadline && $pagesout>0)
       {
           print "<li style='font-weight:bold;color:red;'>Passed last page deadline with $pagesout pages remaining</li>";
       } else {
           print "<li>$pagesout pages remaining</li>";
       }
       print "<li>$colordeadline Last color release</li>\n";
       if ($now>$fullcolordeadline && $colorout>0)
       {
           print "<li style='font-weight:bold;color:red;'>Passed last color deadline with $colorout color pages remaining</li>";
       } else {
           print "<li>$colorout color pages remaining</li>";
       }
      
  }
  
  
  function settime($id,$type,$value)
  {
     if($value=='now'){$value=date("Y-m-d H:i:s");}else{$value=date("Y-m-d H:i:s",strtotime($value));}
     if ($type=='pageapprove')
     {
         $sql="UPDATE job_pages SET page_release='$value' WHERE id=$id";
         $kind='page';
     } else if ($type=='pagecolor')
     {
         $sql="UPDATE job_pages SET color_release='$value' WHERE id=$id";
         $kind='page';
     } else if ($type=='plateapprove')
     {
         $sql="UPDATE job_plates SET plate_approval='$value', black_approval='$value' WHERE id=$id";
         $kind='plate';
     } else if ($type=='platecolor')
     {
         $sql="UPDATE job_plates SET cyan_approval='$value', 
         magenta_approval='$value', yellow_approval='$value' WHERE id=$id";
         $kind='plate';
     } else if ($type=='plateapproveall')
     {
         $sql="UPDATE job_plates SET plate_approval='$value', cyan_approval='$value', 
         magenta_approval='$value', yellow_approval='$value', black_approval='$value' WHERE id=$id";
         $kind='plate';
     } else if ($type=='plateapprovek')
     {
         $sql="UPDATE job_plates SET black_approval='$value' WHERE id=$id";
         $kind='plate';
     } else if ($type=='plateapprovec')
     {
         $sql="UPDATE job_plates SET cyan_approval='$value' WHERE id=$id";
     } else if ($type=='plateapprovem')
     {
         $sql="UPDATE job_plates SET magenta_approval='$value' WHERE id=$id";
     } else if ($type=='plateapprovey')
     {
         $sql="UPDATE job_plates SET yellow_approval='$value' WHERE id=$id";
     }   
     $dbUpdate=dbexecutequery($sql);
     
     //clear cache if necessary
     if($kind!='')
     {
         // need to know the job id
          $sql="SELECT job_id FROM job_pages WHERE id=$id";
          $dbJob=dbselectsingle($sql);
          $jobid=$dbJob['data']['job_id']; 
          clearCache('jobBoxes'.$jobid);
         
     }
     if($dbUpdate['error']=='')
     {
         print "success|".date("H:i:s",strtotime($value));
     } else {
         print "error|".$dbUpdate['error'];
     }
  }
  
  function remakepage($id)
  {
      global $siteID;
      $sql="SELECT * FROM job_pages WHERE id=$id";
      $dbPage=dbselectsingle($sql);
      if ($dbPage['numrows']>0)
      {
          //set the old page to NOT current
          $sql="UPDATE job_pages SET current=0 WHERE id=$id";
          $dbUpdate=dbexecutequery($sql);
          
          $page=$dbPage['data'];
          $created=date("Y-m-d H:i:s");
          $pagenumber=$page['page_number'];
          $pubid=$page['pub_id'];
          $jobid=$page['job_id'];
          $sectioncode=$page['section_code'];
          $pubcode=$page['pub_code'];
          $pubdate=$page['pub_date'];
          $color=$page['color'];
          $plateid=$page['plate_id'];
          $version=$page['version'];
          $newversion=$version+1;
          $sql="INSERT INTO job_pages (created, page_number, pub_id, job_id, section_code, pub_code, 
          pub_date, color, plate_id, current, remake, original_id, version, site_id) VALUES 
          ('$created', '$pagenumber', '$pubid', '$jobid', '$sectioncode', '$pubcode', '$pubdate', 
          '$color', '$plateid', 1, 1, '$id', '$newversion', '$siteID')";
          $dbInsert=dbinsertquery($sql);
          $newid=$dbInsert['insertid'];
          $error=$dbInsert['error'];
          if ($error=='')
          {
               //set the old plate to NOT current
              $sql="UPDATE job_plates SET current=0 WHERE id=$plateid";
              $dbUpdate=dbexecutequery($sql);
          
              
              print "success|$newid|";
              //now, check to see if there is the remake plate. if not, that means we need to create a new plate version as well
              $sql="SELECT * FROM job_plates WHERE id=$plateid AND remake=1";
              $dbRemakePlate=dbselectsingle($sql);
              if ($dbRemakePlate['numrows']==0 && $plateid!=0)
              {
                  //get info about the plate and copy to a new plate with new version number
                    //then updated all pages on this plate to use the new plate id
                      $sql="SELECT * FROM job_plates WHERE id=$plateid";
                      $dbPlate=dbselectsingle($sql);
                      $plate=$dbPlate['data'];
                      $created=date("Y-m-d H:i:s");
                      $lowpage=$plate['low_page'];
                      $plateversion=$plate['version']+1;
                      $pubid=$plate['pub_id'];
                      $jobid=$plate['job_id'];
                      $sectioncode=$plate['section_code'];
                      $pubcode=$plate['pub_code'];
                      $pubdate=$plate['pub_date'];
                      $color=$plate['color'];
                      $sql="INSERT INTO job_plates (created, low_page, pub_id, job_id, section_code, 
                      pub_code, pub_date, color, remake, current, original_id, version, site_id) VALUES 
                      ('$created', '$lowpage', '$pubid', '$jobid', '$sectioncode', '$pubcode', 
                      '$pubdate','$color', 1, 1, '$plateid', '$plateversion', '$siteID')"; 
                      $dbNewPlate=dbinsertquery($sql);
                      $newplateid=$dbNewPlate['insertid'];
                      if($dbNewPlate['error']!=''){print $dbNewPlate['error'];}
                      print $plateid."|".$newplateid;
                      //update the pages that are of the same version to the new plate id
                      /*
                      if($newplateid!=0 && $plateid!='')
                      {
                          $sql="UPDATE job_pages SET plate_id=$newplateid WHERE plate_id=$plateid";
                          $dbUpdate=dbexecutequery($sql);
                      }
                      */
                      $platenumber=$lowpage;
                      
                    //get all the pages that are on this plate
                    $sql="SELECT DISTINCT(page_number), color FROM job_pages WHERE 
                    plate_id=$newplateid AND page_number<>0 ORDER BY page_number ASC";
                    $dbPlatePages=dbselectmulti($sql);
                    $platecolor='Black';
                    if ($dbPlatePages['numrows']>0)
                    {
                        $ppages="";
                        foreach ($dbPlatePages['data'] as $platepage)
                        {
                            $ppages.=" ".$platepage['page_number'];
                            if($platepage['color']){$platecolor='Full Color';}
                        }
                    }
                    $pid=$newplateid;
                      $platesend="<input type='text' id='platesendtime_$pid' name='platesendtime_$pid' value='' style='width:60px'><script type='text/javascript'>\$('#platesendtime_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','plateapprove',dateText);}});</script>";
                        $platesend.="<img src='artwork/approveCheck.png' width=20 style='padding-top:2px;' id='send$pid' onclick=\"setPaginationTime('$pid','plateapprove','now');\" alt='Approve Plate' title='Click to approve the black for this plate. Also releases plate.'>\n";
                        $colorrelease="<input type='text' id='platecolortime_$pid' name='platecolortime_$pid' value='' style='width:60px' /> <script type='text/javascript'>\$('#platecolortime_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','platecolor',dateText);}});</script>";
                        $colorrelease.="<img src='artwork/approveColorCheck.png' width=20 style='padding-top:2px;' id='platecolorsend$pid' onclick=\"setPaginationTime('$pid','platecolor','now')\" alt='Approve Color' title='Click to approve the color (C,M,Y) for this plate.'>\n";
                        $viewdetails="<a href='#' onclick=\"viewPlateDetails($pid);\">View Details</a>\n";
                                  
                      print "|<div style='float:left;width:170px;margin-left:10px;'>*Plate $platenumber $platecolor<br><small>Pages on plate: $ppages</small></div><div style='float:left;width:300px;'>$platesend $colorrelease $viewdetails</div><div class='clear'></div><div id='plateDetails$pid' style='display:none;margin-left:20px;font-size:10px;border:thin solid black;padding:4px;'></div>|";
                
              } else {
                  print "0|0||";
              }
              $pid=$newid;
              if ($page['color']==1){$color="Full Color";}else{$color="Black";}
              //display the new page block
              $pagesend="<input type='text' id='pagesendtime_$pid' name='pagesendtime_$pid' value='' style='width:60px'><script type='text/javascript'>\$('#pagesendtime_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','pageapprove',dateText);}});</script>";
              $pagesend.="<img src='artwork/approveCheck.png' width=20 style='padding-top:2px;' id='send$pid' onclick=\"setPaginationTime('$pid','pageapprove','now');\" alt='Approve Page'>\n";
              $colorrelease="<input type='text' id='colortime_$pid' name='colortime_$pid' value='' style='width:60px' /> <script type='text/javascript'>\$('#colortime_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','pagecolor',dateText);}});</script>";
              $colorrelease.="<img src='artwork/approveColorCheck.png' width=20 style='padding-top:2px;' id='colorsend$pid' onclick=\"setPaginationTime('$pid','pagecolor','now')\" alt='Release Color'>";
              //$viewsubs="<a href='#' onclick='viewPageSubs($pid);'>View Previous Versions</a>\n\n";
              $viewdetails="<a href='#' onclick=\"viewPageDetails($pid);\">View Details</a>";
              $remakeundo="<span='remakepage$pid'><img src='artwork/redoIcon.png' width=20 style='padding-top:2px;' onclick='resetRemakePage($pid,$id,$newplateid,$plateid);' alt='Undo Remake' title='Undo the remake. Resets page and plate back to the originals.'></span>\n";
              print "<div style='float:left;width:170px;'>*Page $pagenumber - $color</div><div style='float:left;width:300px;'>$pagesend $colorrelease $remakeundo $viewdetails</div><div class='clear'></div><div id='pageDetails$pid' style='display:none;'></div>\n";              
    
              print "|";              
    
              
          } else {
              print "error|".$error;
          }
      } else {
          print "error|Page not found";
      }
  }
  
  
  function undoremake($id,$value)
  {
      //value contains all 4 ids in question
      $values=explode("|",$value);
      $pageid=$values[0];
      $originalpageid=$values[1];
      $plateid=$values[2];
      $originalplateid=$values[3];
      
      $sql="DELETE FROM job_pages WHERE id=$pageid";
      $dbDelete=dbexecutequery($sql);
      $sql="UPDATE job_pages SET current=1 WHERE id=$originalpageid";
      $dbUpdate=dbexecutequery($sql);
      $sql="SELECT * FROM job_pages WHERE id=$originalpageid";
      $dbPage=dbselectsingle($sql);
      $page=$dbPage['data'];
      
      $sql="DELETE FROM job_plates WHERE id=$plateid";
      $dbDelete=dbexecutequery($sql);
      $sql="UPDATE job_plates SET current=1 WHERE id=$originalplateid";
      $dbUpdate=dbexecutequery($sql);
      $sql="SELECT * FROM job_plates WHERE id=$originalplateid";
      $dbPlate=dbselectsingle($sql);
      $plate=$dbPlate['data'];
      print "success|";
      //page block
        $detail="";
        if ($page['page_release']!='')
        {
            $releasetime=date("H:i:s",strtotime($page['page_release']));
            $detail.=$releasetime." - page released by pagination<br>\n";
        } else {
            $releasetime='';
        }
        if ($page['color_release']!='')
        {
            $creleasetime=date("H:i:s",strtotime($page['color_release']));
            $detail.=$creleasetime." - color released by pagination<br>\n";
        } else {
            $creleasetime='';
        }
        if ($page['color']==1){$color="Full Color";}else{$color="Black";}
        $pid=$page['id'];
        $pnumber=$page['page_number'];
        if($page['remake']){$pdisplay='none';}else{$pdisplay='block';}
        if ($page['ftp_receive']!='')
        {
            $detail.=date("D, M jS Y \@ H:i:s",strtotime($page['ftp_receive']))." - page received via FTP<br>\n";
        }
        if ($page['workflow_receive']!='')
        {
            $detail.=date("D, M jS Y \@ H:i:s",strtotime($page['workflow_receive']))." - page received in workflow<br>\n";
        }
        if ($page['page_ripped']!='')
        {
            $detail.=date("D, M jS Y \@ H:i:s",strtotime($page['page_ripped']))." - page successfully ripped<br>\n";;
        }
        if ($page['at_composer']!='')
        {
            $detail.=date("D, M jS Y \@ H:i:s",strtotime($page['at_composer']))." - page delivered to composer<br>\n";
        }
        if ($page['page_composed']!='')
        {
            $detail.=date("D, M jS Y \@ H:i:s",strtotime($page['page_composed']))." - plate composed and ready for approval\n";
        }
        $detail.="<br /><span id='togglesub$pid'>Close</span><script>\$('#togglesub$pid').click(function() {
\$('#pageDetails$pid').slideToggle('fast')});
</script>";
        
        $pagesend="<input type='text' id='pagesendtime_$pid' name='pagesendtime_$pid' value='$releasetime' style='width:60px'><script type='text/javascript'>\$('#pagesendtime_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','pageapprove',dateText);}});</script>";
        $pagesend.="<img src='artwork/approveCheck.png' width=20 style='padding-top:2px;' id='send$pid' onclick=\"setPaginationTime('$pid','pageapprove','now');\" alt='Approve Page' title='Click to approve the black for this page.'>\n";
        $colorrelease="<input type='text' id='colortime_$pid' name='colortime_$pid' value='$creleasetime' style='width:60px' /> <script type='text/javascript'>\$('#colortime_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','pagecolor',dateText);}});</script>";
        $colorrelease.="<img src='artwork/approveColorCheck.png' width=20 style='padding-top:2px;' id='colorsend$pid' onclick=\"setPaginationTime('$pid','pagecolor','now')\" alt='Approve Color' title='Click to approve the color (C,M,Y) for this page.'>\n";
        //$viewsubs="<a href='#' onclick='viewPageSubs($pid);'>View Previous Versions</a>\n\n";
        $viewdetails="<a href='#' onclick=\"viewPageDetails($pid);\">View Details</a>\n";
        if($page['remake'])
        {
            $pageremake="<span='remakepage$pid'><img src='artwork/redoIcon.png' width=20 style='padding-top:2px;' onclick='resetRemakePage($pid,$id,$newplateid,$plateid);' alt='Undo Remake' title='Undo the remake. Resets page and plate back to the originals.'></span>\n";
        } else {
            $pageremake="<span='remakepage$pid' style='display:$rdisplay;'><img src='artwork/remakeIcon.png' width=20 style='padding-top:2px;' onclick='remakePage($pid);' alt='Remake Page' title='Create a new remake version of this page. Will start approval process again.'></span>";
            
        }
        print "<div style='float:left;width:170px;'>Page $pnumber - $color<br>$viewsubs</div><div style='float:left;width:300px;'>$pagesend $colorrelease $pageremake $viewdetails $pdetails</div><div class='clear'></div>";
            print "<div id='pageDetails$pid' style='display:none;margin-left:20px;font-size:10px;border:thin solid black;padding:4px;'>$detail</div>|";
       
       $sql="UPDATE job_pages SET plate_id=$originalplateid WHERE plate_id=$plateid";
       $dbUpdate=dbexecutequery($sql);
        
        //plate
        $detail="";
        $releasetime="";
        $creleasetime="";
        if ($plate['plate_approval']!='')
        {
            $releasetime=date("H:i:s",strtotime($plate['plate_approval']));
            $detail.=$releasetime." - plate approved<br>\n";
        }
        $colors=array("black","cyan","magenta","yellow");
        foreach($colors as $key=>$color)
        {
            $receive=$color."_received";
            $approval=$color."_approval";
            $ctp=$color."_ctp";
            if ($plate[$receive]!='')
            {
                $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$receive]))." - $color plate out of bender<br>\n";
            }
            if ($plate[$approval]!='')
            {
                $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$approval]))." - $color plate waiting for approval<br>\n";
            }
            if ($plate[$ctp]!='')
            {
                $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$ctp]))." - $color plate delivered to platesetter<br>\n";;
            }
        }
        if ($detail==''){$detail="No details at this time.<br>";}else{$detail.="<br>\n";}
        $displayed=false;
        $pid=$plate['id'];
        $plateversion=$plate['version'];
        $platenumber=$plate['low_page'];
        if($plate['remake']){$premake='*';}else{$premake='';}
        //get all the pages that are on this plate
        $sql="SELECT DISTINCT(page_number), color FROM job_pages WHERE plate_id=$pid AND page_number<>0 ORDER BY page_number ASC";
        $dbPlatePages=dbselectmulti($sql);
        $platecolor='Black';
        if ($dbPlatePages['numrows']>0)
        {
            $ppages="";
            foreach ($dbPlatePages['data'] as $platepage)
            {
                $ppages.=" ".$platepage['page_number'];
                if($platepage['color']){$platecolor='Full Color';}
            }
        }
        
        $platesend="<input type='text' id='platesendtime_$pid' name='platesendtime_$pid' value='$releasetime' style='width:60px'><script type='text/javascript'>\$('#platesendtime_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','plateapprove',dateText);}});</script>";
        $platesend.="<img src='artwork/approveCheck.png' width=20 style='padding-top:2px;' id='send$pid' onclick=\"setPaginationTime('$pid','plateapprove','now');\" alt='Approve Plate' title='Click to approve the black for this plate. Also releases plate.'>";
        $colorrelease="<input type='text' id='platecolortime_$pid' name='platecolortime_$pid' value='$creleasetime' style='width:60px' /> <script type='text/javascript'>\$('#platecolortime_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','platecolor',dateText);}});</script>";
        $colorrelease.="<img src='artwork/approveColorCheck.png' width=20 style='padding-top:2px;' id='platecolorsend$pid' onclick=\"setPaginationTime('$pid','platecolor','now')\" alt='Approve Color' title='Click to approve the color (C,M,Y) for this plate.'>";
        $viewdetails="<a href='#' onclick=\"viewPlateDetails($pid);\">View Details</a>";
        print "<div style='float:left;width:170px;margin-left:10px;'>Plate $platenumber $platecolor $premake<br><small>Pages on plate: $ppages</small></div><div style='float:left;width:300px;'>$platesend $colorrelease $viewdetails</div><div class='clear'></div>";
            print "<div id='plateDetails$pid' style='display:none;margin-left:20px;font-size:10px;border:thin solid black;padding:4px;'>$detail</div>";
  }
  
  
  function getpagedetails($id,$type,$value)
  {
        
        $sql="SELECT * FROM job_pages WHERE id=$id";
        $dbPage=dbselectsingle($sql);
        if($dbPage['numrows']>0)
        {
            $page=$dbPage['data'];
            if ($page['page_release']!='')
            {
                $releasetime=date("H:i:s",strtotime($page['page_release']));
                $detail.=$releasetime." - page released by pagination<br>\n";
            }
            if ($page['color_release']!='')
            {
                $creleasetime=date("H:i:s",strtotime($page['color_release']));
                $detail.=$creleasetime." - color released by pagination<br>\n";
            }
            if ($page['ftp_receive']!='')
            {
                $detail.=date("D, M jS Y \@ H:i:s",strtotime($page['ftp_receive']))." - page received via FTP<br>\n";
            }
            if ($page['workflow_receive']!='')
            {
                $detail.=date("D, M jS Y \@ H:i:s",strtotime($page['workflow_receive']))." - page received in workflow<br>\n";
            }
            if ($page['page_ripped']!='')
            {
                $detail.=date("D, M jS Y \@ H:i:s",strtotime($page['page_ripped']))." - page successfully ripped<br>\n";;
            }
            if ($page['at_composer']!='')
            {
                $detail.=date("D, M jS Y \@ H:i:s",strtotime($page['at_composer']))." - page delivered to composer<br>\n";
            }
            if ($page['page_composed']!='')
            {
                $detail.=date("D, M jS Y \@ H:i:s",strtotime($page['page_composed']))." - plate composed and ready for approval\n";
            }
            if($detail==''){$detail='No details available at this time.<br>';}
            print "success|$detail";
        } else {
            print "success|No details available at this time.<br>";
        }
  }
  
  function getplatedetails($id,$type,$value)
  {
        
        $sql="SELECT * FROM job_plates WHERE id=$id";
        $dbPlate=dbselectsingle($sql);
        if($dbPlate['numrows']>0)
        {
            $plate=$dbPlate['data'];
            $detail='';
            if ($plate['plate_approval']!='')
            {
                $releasetime=date("H:i:s",strtotime($plate['plate_approval']));
                $detail.=$releasetime." - plate approved<br>\n";
            }
            $colors=array("black","cyan","magenta","yellow");
            foreach($colors as $key=>$color)
            {
                $receive=$color."_received";
                $approval=$color."_approval";
                $ctp=$color."_ctp";
                if ($plate[$receive]!='')
                {
                    $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$receive]))." - $color plate out of bender<br>\n";
                }
                if ($plate[$approval]!='')
                {
                    $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$approval]))." - $color plate waiting for approval<br>\n";
                }
                if ($plate[$ctp]!='')
                {
                    $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$ctp]))." - $color plate delivered to platesetter<br>\n";;
                }
            }
            if ($detail==''){$detail="No details at this time.<br>";}else{$detail.="<br>\n";}
            print "success|$detail";
        } else {
            print "success|No details available at this time.<br>";
        }
  }
  
  dbclose();
?>
