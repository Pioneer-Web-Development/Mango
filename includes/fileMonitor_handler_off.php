 <?php
  include("functions_db.php");
  $log=false;
  $monitoring=false;
  $ptime=date("Y-m-d H:i:s");
  //get values from POST statement
  $filename=$_POST['filename'];
  $filestamp=$_POST['creationstamp'];
  $status=$_POST['status'];
  $extra=$_POST['extra']; //could be anything
  $type=$_POST['type']; //could be anything
  
  if($monitoring)
  {
      $sql="INSERT INTO monitorTest (ptime, filename, filestamp,status,extra,type) VALUES ('$ptime', '$filename','$filestamp', '$status', '$extra', '$type')";
      $dbInsert=dbinsertquery($sql);
      $monitorid=$dbInsert['insertid'];
  }
  
  
  if($_POST['pinging']==1)
  {
      $ip=$_POST['ipaddress'];
      
      $sql="SELECT * FROM file_monitors_registered WHERE monitor_ip='$ip'";
      $dbIP=dbselectsingle($sql);
      if($dbIP['numrows']==0)
      {
          //lets register a new one
          $sql="INSERT INTO file_monitors_registered (monitor_ip, active, last_ping) VALUES ('$ip', '1', '$ptime')";
          $dbInsert=dbinsertquery($sql);
      } else {
          $sql="UPDATE file_monitors_registered SET last_ping='$ptime' WHERE monitor_ip='$ip'";
          $dbUpdate=dbexecutequery($sql);
      }
      
      dbclose();
      die();
  }
  
  
  
  
  //clear the traffic log for anything older than 8 hours
  $sql="DELETE FROM file_monitors_traffic WHERE stamp<'".date("Y-m-d H:i:s",strtotime("-8 hours"))."'";
  $dbClear=dbexecutequery($sql);
  
  
  //if this file begings with . then ignore it -- should eliinate ._ and .DS_STORE files
  if(substr($filename,0,1)=='.' || $filename=='Hold')
  {
      dbclose();
      die();
  }
  
  $sql="SELECT * FROM file_monitors_traffic WHERE filename='$filename' AND stamp<'".date("Y-m-d H:i:s",strtotime("-5 minutes"))."'";
  $dbCheck=dbselectsingle($sql);
  if($dbCheck['numrows']>0)
  {
      //not going to work on this one as we've already processed this page
      dbclose();
      die();
  } else {
      $sql="INSERT INTO file_monitors_traffic (filename, stamp, fstatus, ftype, fextra) VALUES ('$filename','$ptime', '$status', '$type', '$extra')";
      $dbInsert=dbinsertquery($sql);
  }
  
  if($monitoring)
  {
      $sql="INSERT INTO monitorTest (ptime, filename, filestamp,status,extra,type) VALUES ('$ptime', '$filename','$filestamp', 
      '$status', '$extra', '$type')";
      $dbInsert=dbinsertquery($sql);
      $monitorid=$dbInsert['insertid'];
  }
  //first thing is to find a matching file_monitor input based on the status
  $sql="SELECT * FROM file_monitors WHERE file_status='$status'";
  $dbMonitor=dbselectsingle($sql);
  if($dbMonitor['numrows']>0)
  {
      //found one ! :)
      $monitor=$dbMonitor['data'];
      $delimiter=$monitor['delimiter'];
      $pagepos=$monitor['page_pos'];
      $datepos=$monitor['date_pos'];
      $sectionpos=$monitor['section_code_pos'];
      $productcodepos=$monitor['product_code_pos'];
      $pubpos=$monitor['pub_code_pos'];
      $colorpos=$monitor['color_pos'];
      $displaystatus=$monitor['display_status'];
      $table=$monitor['update_table'];
      $field=$monitor['update_field'];
      $replacefield=$monitor['replace_field_with_color'];
      $sectionpage=$monitor['section_page'];
      $dateformat=$monitor['date_format'];
      $stamp=date("Y-m-d H:i:s");
      $croute=$monitor['color_input'];
      //ok, lets see if we have a delimiter, which makes it the simplest option
      if($delimiter!='')
      {
          //get rid of file extension on the filename
          $filename=str_replace(".tif","",$filename);
          $filename=str_replace(".TIFF","",$filename);
          $filename=str_replace(".tiff","",$filename);
          $filename=str_replace(".TIF","",$filename);
          $filename=str_replace(".PDF","",$filename);
          $filename=str_replace(".pdf","",$filename);
          
          $parts=explode($delimiter,$filename);
          $pub=$parts[$pubpos];
          $section=$parts[$sectionpos];
          $productcode=$parts[$productcodepos];
          $date=$parts[$datepos];
          $page=$parts[$pagepos];
          
          if($sectionpos==$pagepos)
          {
              //means that section and page are in the same box. Lets move through the characters in that block until we go from letter to number, depending on the setting of section page
              $tempsection=$section;
              if($sectionpage=='section')
              {
                  //section first, so from letter to number
                  $letter=false;
                  $letterchange=0;
                  for($i=0;$i<strlen($section);$i++)
                  {
                    if(!is_numeric(substr($section,$i,1)))
                    {
                        $letter=true;
                    } else if($letter) {
                        $letterchange=$i; 
                    }    
                  }
                  $section=substr($tempsection,0,$letterchange);
                  $page=substr($tempsection,$letterchange);
              } else {
                  //section first, so from letter to number
                  $letter=false;
                  $letterchange=0;
                  for($i=0;$i<strlen($section);$i++)
                  {
                    if(!is_numeric(substr($section,$i,1)))
                    {
                        $letter=true;
                    } else if($letter) {
                        $letterchange=$i; 
                    }    
                  }
                  $page=substr($tempsection,0,$letterchange);
                  $section=substr($tempsection,$letterchange);
              }
              
          }
          
          if(substr($page,0,1)=="0"){$page=substr($page,1);}
          if($colorpos!='')
          {
              $color=$parts[$colorpos];
          } else {
              $color='black';
          }
          if($color=='C'){$color='cyan';}
          if($color=='M'){$color='magenta';}
          if($color=='Y'){$color='yellow';}
          if($color=='K'){$color='black';}
          
          if($replacefield)
          {
              $field=str_replace("black",$color,$field);
          }
          if($log)
        {
            $sql="INSERT INTO sql_log (stamp, statement, scriptname, type) VALUES ('".date("Y-m-d H:i:s")."','Used a delimiter of $delimiter, got pub of $pub and section of $section and date of $date and page $page for file: $filename','fileMonitor_handler.php','manual')";  
          $dbInsert=dbexecutequery($sql);
        }
      } else {
          //have to substr it out instead
          $pubparts=explode("-",$pubpos);
          $publength=$pubparts[1]-$pubparts[0]+1;
          $pub=substr($filename,$pubparts[0],$publength);
          
          $sectionparts=explode("-",$sectionpos);
          $sectionlength=$sectionparts[1]-$sectionparts[0]+1;
          $section=substr($filename,$sectionparts[0],$sectionlength);
          
          $productcodeparts=explode("-",$productcodepos);
          $productcodelength=$productcodeparts[1]-$productcodeparts[0]+1;
          $productcode=substr($filename,$productcodeparts[0],$productcodelength);
          
          $pageparts=explode("-",$pagepos);
          $pagelength=$pageparts[1]-$pageparts[0]+1;
          $page=substr($filename,$pageparts[0],$pagelength);
          if(substr($page,0,1)=="0"){$page=substr($page,1);}
          
          $dateparts=explode("-",$datepos);
          $datelength=$dateparts[1]-$dateparts[0]+1;
          $date=substr($filename,$dateparts[0],$datelength);
          
          if($colorpos!='')
          {
              $colorparts=explode("-",$colorpos);
              $colorlength=$colorparts[1]-$colorparts[0]+1;
              $color=substr($filename,$colorparts[0],$colorlength);
          } else {
              $color='black';
          }
          if($color=='C'){$color='cyan';}
          if($color=='M'){$color='magenta';}
          if($color=='Y'){$color='yellow';}
          if($replacefield)
          {
              $field=str_replace("black",$color,$field);
          }
      } 
      switch ($dateformat)
      {
        case "mmdd":
        $date=date("Y").'-'.$date;
        break;
        
        case "mmddyy":
        $date="20".substr($date,4,2).'-'.substr($date,0,4);
        break;
        
        case "mmddyyyy":
        $date=substr($date,4,4).'-'.substr($date,0,4);
        break;
        
        case "yyyymmdd":
        $date=substr($date,0,4).'-'.substr($date,4,4);
        break;
      }
      
      $section=str_replace("0","",$section); //get rid of leading zeros
      $section=str_replace(" ","",$section); //get rid of leading spaces
      
      //lets double check that the pub code is valid, if not, do a quick lookup for alt pub_codes
      $sql="SELECT * FROM publications WHERE pub_code='$pub'";
      $dbPub=dbselectsingle($sql);
      if($dbPub['numrows']==0)
      {
        if($log)
        {
            $sql="INSERT INTO sql_log (stamp, statement, scriptname, type) VALUES ('".date("Y-m-d H:i:s")."','Did not find pub code of $pub','fileMonitor_handler.php','manual')";  
          $dbInsert=dbexecutequery($sql);
        }
          //means no match! check alt pub codes
          $sql="SELECT * FROM publications WHERE alt_pub_code LIKE '%$pub%'";
          $dbPub=dbselectsingle($sql);
          if($dbPub['numrows']>0)
          {
              //found one!
              $pub=$dbPub['data']['pub_code'];
              if($log)
              {
              $sql="INSERT INTO sql_log (stamp, statement, scriptname, type) VALUES ('".date("Y-m-d H:i:s")."','Found an alternatepub code of $pub','fileMonitor_handler.php','manual')";  
              $dbInsert=dbexecutequery($sql);
              }
          } else {
              if($log)
              {
              $sql="INSERT INTO sql_log (stamp, statement, scriptname, type) VALUES ('".date("Y-m-d H:i:s")."','Unable to find an alternate pubcode for $pub using $sql','fileMonitor_handler.php','manual')";  
              $dbInsert=dbexecutequery($sql);
              }
          }
      }
      
      
      if($type=='Pages')
      {
          if($color!='0'){$color=1;}
          $sql="SELECT * FROM job_pages WHERE pub_code='$pub' AND run_productcode='$productcode' AND section_code='$section' AND pub_date='$date' AND page_number='$page' AND current=1";
          $dbPage=dbselectsingle($sql);
          
          if($croute=='bw')
          {
              $colorset=', color=0 ';
          } elseif($croute=='color')
          {
              $colorset=', color=1 '; 
          } else {
              $colorset='';
          }
          if ($dbPage['numrows']>0)
          {
            $pageid=$dbPage['data']['id'];
            if($table=='job_pages_log')
            {
                 $sql="INSERT INTO job_pages_log (page_id, display_status, display_value) VALUES ('$pageid',
                 '$displaystatus - $extra','$stamp')";
                 $dbInsert=dbinsertquery($sql);
            } else {
                 $sql="UPDATE $table SET $field='$stamp'$colorset WHERE id=$pageid";
                 $dbInsert=dbexecutequery($sql);
                 if($monitoring)
                 {
                     $msql="UPDATE monitorTest SET dbsql='".addslashes($sql)."' WHERE id=$monitorid";
                     $dbUpdate=dbexecutequery($msql);
                 }
                 
                 //lets check to see if any other pages on the same plate are color, if so, plate is color, else bw
                 $sql="SELECT plate_id FROM job_pages WHERE id=$pageid";
                 $dbPlateid=dbselectsingle($sql);
                 $plateid=$dbPlateid['data']['plate_id'];
                 $sql="SELECT * FROM job_pages WHERE plate_id=$plateid AND color=1"; //this gets any color pages on the plate
                 $dbColorCount=dbselectmulti($sql);
                 if($dbColorCount['numrows']>0)
                 {
                     $sql="UPDATE job_plates SET color=1 WHERE id=$plateid";
                 } else {
                     $sql="UPDATE job_plates SET color=0 WHERE id=$plateid";
                 }
                 $dbPlateUpdate=dbexecutequery($sql);
            }
            if($log)
            {
            $sql="INSERT INTO sql_log (stamp, statement, scriptname, type) VALUES ('".date("Y-m-d H:i:s")."','Success: ".addslashes($sql)."','fileMonitor_handler.php','manual')";  
            $dbInsert=dbexecutequery($sql);      
            }
          } else {
            if($log)
            {
             $sql="INSERT INTO sql_log (stamp, statement, scriptname, type) VALUES ('".date("Y-m-d H:i:s")."','Problem page: ".addslashes($sql)." - passed filename of $filename and status of $status','fileMonitor_handler.php','manual')"; 
            $dbInsert=dbexecutequery($sql);  
            }
          }
      } elseif($type=='Plates') {
          $sql="SELECT * FROM job_plates WHERE pub_code='$pub' AND run_productcode='$productcode' AND section_code='$section' AND pub_date='$date' AND low_page='$page' AND current=1";
          $dbPlate=dbselectsingle($sql);
          if ($dbPlate['numrows']>0)
          {
            $plateid=$dbPlate['data']['id'];
            if($table=='job_plates_log')
            {
                 $sql="INSERT INTO job_plates_log (plate_id, display_status, display_value) VALUES ('$plateid',
                 '$displaystatus - $extra','$stamp')";
                 $dbInsert=dbinsertquery($sql);
            } else {
                 $sql="UPDATE $table SET $field='$stamp' WHERE id=$plateid";
                 $dbInsert=dbexecutequery($sql);
                 if($monitoring)
                 {
                     $msql="UPDATE monitorTest SET dbsql='".addslashes($sql)."' WHERE id=$monitorid";
                     $dbUpdate=dbexecutequery($msql);
                 }   
            }
            if($log)
            {
            $sql="INSERT INTO sql_log (stamp, statement, scriptname, type) VALUES ('".date("Y-m-d H:i:s")."','Success: ".addslashes($sql)."','fileMonitor_handler.php','manual')";  
            $dbInsert=dbexecutequery($sql);      
            }
          } else {
            if($log)
            {
             $sql="INSERT INTO sql_log (stamp, statement, scriptname, type) VALUES ('".date("Y-m-d H:i:s")."','Problem plate: ".addslashes($sql)." - passed filename of $filename and status of $status','fileMonitor_handler.php','manual')"; 
            $dbInsert=dbexecutequery($sql);  
            }
          }

      } else {
        if($log)
        {
        $sql="INSERT INTO sql_log (stamp, statement, scriptname, type) VALUES ('".date("Y-m-d H:i:s")."','No type found with ".addslashes($type)."','fileMonitor_handler.php','manual')";
      $dbInsert=dbinsertquery($sql);
        }
      }

  } else {
    if($log)
    {
        $sql="INSERT INTO sql_log (stamp, statement, scriptname, type) VALUES ('".date("Y-m-d H:i:s")."','No monitor found with ".addslashes($sql)."','fileMonitor_handler.php','manual')";
      $dbInsert=dbinsertquery($sql);
    }
  }
  dbclose();
 ?>