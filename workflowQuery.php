<?php
  error_reporting(0);

  include("includes/functions_MSdb.php");
  include("includes/functions_db.php");
  
  $sql="use WorkflowDB;SELECT TOP 500 A.*, B.Filename FROM FileLogs A, Files B WHERE A.FileId=B.FileId ORDER BY DT DESC";
  //$sql="use WorkflowDB;SELECT TOP 2000 A.*, B.Filename FROM FileLogs A, Files B WHERE A.FileId=B.FileId ORDER BY A.DT DESC";
  //print "using $sql<br>";
  $dbLogs=msdbselectmulti($sql);
  //print "Found ".$dbLogs['numrows']." records";
  if ($dbLogs['numrows']>0)
  {
      //print "<table>\n";
      //print "<tr><th>File</th><th>Status</th><th>Message</th><th>Time</th><th>Device</th></tr>\n";
      //grab the very first record to get the max date of this round
      $date=date("Y-m-d H:i:s");
      $sql="UPDATE processing_log SET lastWorkflowDate='$date'";
      $dbWUpdate=dbexecutequery($sql);
      foreach ($dbLogs['data'] as $dlog)
    {
        $status="";
        $itemkind="";
        $sql="";
        $file=$dlog['Filename'];
        if (strpos($file,"_")>0)
        {
            //we have a plate
            //format: IS_00B_3_041209_Y.tif
            $pieces=explode("_",$file);
            $pubcode=$pieces[0];
            $section=$pieces[1];
            $pagenumber=$pieces[2];
            $pubdate=substr($pieces[3],0,4);
            $platecolor=str_replace(".tif","",$pieces[4]);
            $type="Plate $pagenumber $platecolor";
            $itemkind="plate";
        } elseif ((strpos($file,".TIF")>0) && strpos($file,"_")==0)
        {
            //we have a ripped page tiff file
            //format: IS041100D18pdfC00.TIF
            $pubcode=substr($file,0,2);
            $pubdate=substr($file,2,4);
            $section=substr($file,6,3);
            $pagenumber=intval(substr($file,9,2));
            $pagecolor=substr($file,14,1);
            $type="Page $pagenumber $pagcolor";
            $itemkind="page"; 
        } else {
            //must be a page PDF
            //format: IS041000S07.pdf
            $pubcode=substr($file,0,2);
            $pubdate=substr($file,2,4);
            $section=substr($file,6,3);
            $pagenumber=intval(substr($file,9,2));
            $type="Page $pagenumber PDF";
            $itemkind="page";
        }
        $message=$dlog['Message'];
        $time=date("Y-m-d H:i:s",strtotime($dlog['DT']));
        //print "original time: $dlog[DT] now $time<br>";
        $device=$dlog['SystemID'];
        $color=false;
        //lets figure out the status to assign to the plate
        if (strpos($message,"one processing")>0 && $device=="RIP 1")
        {
            //means we have a TIFF file that has just been ripped
            $status="rip complete";
        }
        if (strpos($message,"ile received")>0 && $device=="Plate Approval")
        {
            //means we have a TIFF file that has just been ripped
            $status="plate received";
        }
        if (strpos($message,"copied to:")>0 && $device=="CTP 1")
        {
            //means we have a TIFF file that has been copied to the platesetter
            $status="plateoutput";
        }
        if (strpos($message,"copied to:")>0 && $device=="CTP 2")
        {
            //means we have a TIFF file that has been copied to the platesetter
            $status="plateoutput";
        }
        if (strpos($message,"art processing:")>0 && $device=="Composer")
        {
            //means composer has finished with this tif file
            $status="composer start";
        }
        if (strpos($message,"one processing:")>0 && $device=="Composer")
        {
            //means composer is working on this plate
            $status="composer complete";
        }
        if (strpos($message,"received:")>0 && $device=="Composer")
        {
            //means composer is working on this plate
            $status="composer received";
        }
        if ($message=="File has been approved." && $device=="Plate Approval")
        {
            //means plate has been approved
            $status="plate approved";
        }
        if ($message=="File received: $file" && $device=="Input Router" && $itemkind!='plate' && $pagecolor="K")
        {
            //means we have a TIFF file that has just been ripped
            $status="ripped";
        }
        
        if ($message=="Begin Preflighting phase..." && $device=="Preflight")
        {
            //means we have started preflight
            $status="preflight start";
        }
        
        if ($message=="Finished Preflight..." && $device=="Preflight")
        {
            //means we have finsihed preflight
            $status="preflight complete";
        }
        
        if ($message=="Performing rotate")
        {
            //means plate is being rotated before output
            $status="rotate $platecolor begin";
        }
        if ($message=="Completed bitmap operations.")
        {
            //means plate has finished rotation
            $status="rotate $platecolor complete";
        }
        
        if (strpos($message,"XWorkflow BW2")>0 || strpos($message,"XWorkflow BW")>0)
        {
            $color=false;
            $status="rip start BW";    
        }
        
        if (strpos($message,"XWorkflow Color2")>0 || strpos($message,"XWorkflow Color")>0)
        {
            //means we have a TIFF file that has just been ripped to color
            $color=true;
            $status="rip start Color";
        }
        if (strpos($message,"received: $file")>0 && $device=="PDF")
        {
            //means we have a TIFF file that has just been ripped
            $status="page received";
            //print "$message<br>Received a page<br>";
        }
        
        if ($status!='')
        {
            
            //now lets do our database updates
            $sql="";
            $csql="";
            $psql="";
            $pubdate=date("Y")."-".$pubdate;
            $section=str_replace("0","",$section);
            if ($itemkind=='plate')
            {
                $csql="SELECT * FROM job_plates WHERE pub_code='$pubcode' AND section_code='$section' AND pub_date='$pubdate' AND low_page='$pagenumber' ORDER BY created DESC LIMIT 1"; //the order and limit make sure we're always working with the newest plate (ie for remakes)
                $dbPlate=dbselectsingle($csql);
                if ($dbPlate['numrows']>0)
                {
                    $plateid=$dbPlate['data']['id'];
                    
                    switch ($status)
                    {
                        case "plate approved":
                            $field="plate_approval";
                        break;
                        
                        case "plateoutput":
                            switch ($platecolor)
                            {
                                case "K":
                                    $field="black_ctp";
                                break;
                                case "C":
                                    $field="cyan_ctp";
                                break;
                                case "M":
                                    $field="magenta_ctp";
                                break;
                                case "Y":
                                    $field="yellow_ctp";
                                break;
                            }
                        break;
                        
                    
                    }
                    $sql="UPDATE job_plates SET $field='$time' WHERE id=$plateid";
                    //print "Updating a plate $plateid with $field of $time\n";
                    $dbUpdate=dbexecutequery($sql);
                }
                
            } else {
                //figure out which job_page we are going to be working with
                $psql="SELECT * FROM job_pages WHERE pub_code='$pubcode' AND section_code='$section' AND pub_date='$pubdate' AND page_number='$pagenumber' ORDER BY created DESC LIMIT 1";
                $dbPage=dbselectsingle($psql);
                print "using $psql<br />Found a page $pagenumber with status of $status<br>";
                if ($dbPage['numrows']>0)
                {
                    $pageid=$dbPage['data']['id'];
                    switch ($status)
                    {
                        case "page received":
                            $field="workflow_receive";
                        break;
                        
                        case "ripped":
                            $field="page_ripped";
                        break;
                        
                        case "composer start":
                            $field="at_composer";
                        break;
                        
                        case "composer complete":
                            $field="page_composed";
                        break;
                        
                    
                    }
                    $sql="UPDATE job_pages SET $field='$time' WHERE id=$pageid";
                    $dbUpdate=dbexecutequery($sql);
                   //print "Updated page with $sql<br />\n";
            }
                //print "<tr><td colspan=8>Trying with: $psql<br>and page sql: $sql</td></tr>\n";
            }   
            
        }
  }
     print "</table>\n";
  }
  msdbclose();
  dbclose();
?>
