<?php

include("includes/mainmenu.php") ;
include("includes/job_helper.php") ;

$jobData=nav(); 
print "<div class='ui-widget ui-widget-content ui-corner-all' style='width:100%;height:auto;'>\n";

print "<div id='plateside' style='float:left;width:760px;height:auto;'>";

$layoutid=$jobData['job']['layout_id'];
$pubname=$jobData['pub']['pub_name'];
$pubdate=$jobData['job']['pub_date'];
$pubday=strtolower(date("l",strtotime($jobData['job']['pub_date'])));
$jobname=$jobData['run']['run_name'];
$draw=$jobData['job']['draw'];
$jobid=$jobData['job']['id'];
$runid=$jobData['job']['run_id'];
$platenotes=stripslashes($jobData['job']['plate_notes']);

$sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
$jobsections=dbselectsingle($sql);
$jsections=$jobsections['data'];

//grab stats if available
$sql="SELECT * FROM job_stats WHERE job_id=$jobid";
$dbStats=dbselectsingle($sql);

if ($dbStats['numrows']>0)
{
    $wasteplates=$dbStats['data']['plates_waste'];
    $remakeplates=$dbStats['data']['plates_remake'];
    $lastpage=$dbStats['data']['plateroom_lastpage'];
    if ($dbStats['data']['plateroom_lastpage_time']!='')
    {
        $lastpagetime=date("H:i",strtotime($dbStats['data']['plateroom_lastpage_time']));   
    } else {
        $lastpagetime='';
    }
    if(trim($remakeplates=='')){$remakeplates=0;}
    if(trim($wasteplates=='')){$wasteplates=0;}
    if(trim($lastpage=='')){$lastpage=0;}
} else {
    $wasteplates=0;
    $remakesplates=0;
    $lastpage='';
}
    print "<div class='ui-widget ui-widget-header ui-widget-content ui-corner-all' style='width:750px;margin-left:10px;margin-top:10px;padding:4px;'>\n";
    print "Remake plate count: ";
    print "<input id='remakeplates' type='text' size=5 style='width:50px;' onBlur=\"plateMonitorExtra('remake',$jobid,this.value);\" onkeypress='return isNumberKey(event);' value='$remakeplates' />";
    print "&nbsp;&nbsp;&nbsp;Waste plate count: "; 
    print "<input id='wasteplates' type='text' size=5 style='width:50px;' onBlur=\"plateMonitorExtra('waste',$jobid,this.value);\" onkeypress='return isNumberKey(event);' value='$wasteplates' />";
    print "&nbsp;&nbsp;&nbsp;Last Page: "; 
    print "<input id='lastpage' type='text' size=5 style='width:50px;' onBlur=\"plateMonitorExtra('lastpage',$jobid,this.value);\" value='$lastpage' />";
    print "&nbsp;&nbsp;&nbsp;Last Page Time: "; 
    print "<input id='lastpagetime' type='text' size=5 style='width:60px;' value='$lastpagetime' />";
    print "<script type='text/javascript'>\$('#lastpagetime').timepicker({onClose: function(dateText, inst){plateMonitorExtra('lastpagetime',$jobid,dateText);}});</script>\n";
    print "</div>\n";
    print "<div id='plateside' style='float:left;width:750px;'>\n";
    print "<p style='font-weight:bold;font-size:14px;margin-left:10px;'>Plates for this job:</p>\n";
    $colors=array("black","cyan","magenta","yellow");
    for ($i=1;$i<=3;$i++)
    {
        $sectioncode="section".$i."_code";
        $sectionname="section".$i."_name";
        $sectionlow=$jsections["section".$i."_lowpage"];
        $sectionhigh=$jsections["section".$i."_highpage"];
        $sectionused=$jsections["section".$i."_used"];
        if ($sectionused==1)
        {
            //now get the plates
            $sql="SELECT * FROM job_plates WHERE job_id=$jobid AND section_code='$jsections[$sectioncode]' ORDER BY low_page ASC, version DESC";
            $dbPlates=dbselectmulti($sql);
            if ($dbPlates['numrows']>0)
            {
                print "<div id='plate$pid'>\n";
                print "<div style='float:left;width:170px;margin-left:10px;'><b>Section $jsections[$sectionname]</b></div><div style='float:left;width:200px;'><b>Plate Number</b></div><div class='clear'></div>\n";
                foreach ($dbPlates['data'] as $plate)
                {
                    $detail="";
                    $releasetime="";
                    $receivek='';
                    $receivec='';
                    $receivem='';
                    $receivey='';
                    $approveall='';
                    $approvek='';
                    $approvec='';
                    $approvem='';
                    $approvey='';
                    $receiveall='';
                    if ($plate['plate_approval']!='')
                    {
                        $releasetime=date("H:i:s",strtotime($plate['plate_approval']));
                        $detail.=$releasetime." - plate approved<br>\n";
                    }
                    foreach($colors as $key=>$color)
                    {
                        $receive=$color."_received";
                        $approval=$color."_approval";
                        $ctp=$color."_ctp";
                        if ($plate[$receive]!='')
                        {
                            if($color=="black"){$receivek=date("H:i:s",strtotime($plate[$receive]));}
                            if($color=="cyan"){$receivec=date("H:i:s",strtotime($plate[$receive]));}
                            if($color=="magenta"){$receivem=date("H:i:s",strtotime($plate[$receive]));}
                            if($color=="yellow"){$receivey=date("H:i:s",strtotime($plate[$receive]));}
                            $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$receive]))." - $color plate out of bender<br>\n";
                        }
                        if ($plate[$approval]!='')
                        {
                            if($color=="black"){$approvek=date("H:i:s",strtotime($plate[$approval]));}
                            if($color=="cyan"){$approvec=date("H:i:s",strtotime($plate[$approval]));}
                            if($color=="magenta"){$approvem=date("H:i:s",strtotime($plate[$approval]));}
                            if($color=="yellow"){$approvey=date("H:i:s",strtotime($plate[$approval]));}
                            $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$approval]))." - $color plate waiting for approval<br>\n";
                        }
                        if ($plate[$ctp]!='')
                        {
                            $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$ctp]))." - $color plate delivered to platesetter<br>\n";;
                        }
                    }
                    if ($receivek!='' && $receivec!='' && $receivem!='' && $receivey!=''){$receiveall=$receivek;}
                    if ($approvek!='' && $approvec!='' && $approvem!='' && $approvey!=''){$approveall=$approvek;}
                        
                    if ($detail==''){$detail="No details at this time.<br>";}else{$detail.="<br>\n";}
                    $displayed=false;
                    $pid=$plate['id'];
                    $plateversion=$plate['version'];
                    $platenumber=$plate['low_page'];
                    
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
                    
                    $platek="<input type='text' id='plateapprovek_$pid' name='plateapprovek_$pid' value='$approvek' style='width:60px'><script type='text/javascript'>\$('#plateapprovek_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','plateapprovek',dateText);}});</script>";
                    $platek.="<img src='artwork/approveCheckK.png' width=20 style='padding-top:2px;' onclick=\"setPaginationTime('$pid','plateapprovek','now');\" alt='Approve Black Plate' title='Click to receive the black for this plate.'>\n";
                    $platec="<input type='text' id='plateapprovec_$pid' name='plateapprovec_$pid' value='$approvec' style='width:60px'><script type='text/javascript'>\$('#plateapprovec_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','plateapprovec',dateText);}});</script>";
                    $platec.="<img src='artwork/approveCheckC.png' width=20 style='padding-top:2px;' onclick=\"setPaginationTime('$pid','plateapprovec','now');\" alt='Approve Cyan Plate' title='Click to receive the cyan for this plate.'>\n";
                    $platem="<input type='text' id='plateapprovem_$pid' name='plateapprovem_$pid' value='$approvem' style='width:60px'><script type='text/javascript'>\$('#plateapprovem_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','plateapprovem',dateText);}});</script>";
                    $platem.="<img src='artwork/approveCheckM.png' width=20 style='padding-top:2px;' onclick=\"setPaginationTime('$pid','plateapprovem','now');\" alt='Approve Magenta Plate' title='Click to receive the magenta for this plate.'>\n";
                    $platey="<input type='text' id='plateapprovey_$pid' name='plateapprovey_$pid' value='$approvey' style='width:60px'><script type='text/javascript'>\$('#plateapprovey_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','plateapprovey',dateText);}});</script>";
                    $platey.="<img src='artwork/approveCheckY.png' width=20 style='padding-top:2px;' onclick=\"setPaginationTime('$pid','plateapprovey','now');\" alt='Approve Yellow Plate' title='Click to receieve the yellow for this plate.'>\n";
                    $plateall="<input type='text' id='plateapproveally_$pid' name='plateapproveall_$pid' value='$approveall' style='width:60px'><script type='text/javascript'>\$('#plateapproveall_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','plateapproveall',dateText);}});</script>";
                    $plateall.="<img src='artwork/approveColorCheck.png' width=20 style='padding-top:2px;' onclick=\"setPaginationTime('$pid','plateapproveall','now');\" alt='Approve All Plates' title='Click to receive all colors for plate.'>\n";
                    $viewdetails="<a href='#' onclick=\"viewPlateDetails($pid);\">View Details</a>\n";
                    print "<div id='page$pid'>\n";
                        print "<div style='float:left;width:170px;margin-left:10px;'>Plate $platenumber - ver. $plateversion $platecolor<br><small>Pages on plate: $ppages</small></div><div style='float:left;width:550px;'>$platek $platec $platem $platey $plateall $viewdetails</div><div class='clear'></div>\n";
                        print "<div id='plateDetails$pid' style='display:none;margin-left:20px;font-size:10px;border:thin solid black;padding:4px;'>$detail</div>\n";
                    print "</div>\n";
                }
                print "</div>\n";
            }

        } 

    }

    print "</div>\n";
print "</div>\n";

print "<div id='rightrail' style='float:right;width:580px;margin-right:5px;'>\n";


 


 print "<div id='deadlines' style='float:left;width:265px;'>\n";
       print "<p style='font-weight:bold;font-size:14px;margin-left:10px;'>Deadlines and page flow:</p>\n";
       print "<div id='deadlinedata'>\n";
           $scheduledstart=$jobData['job']['startdatetime'];
           $colorlead=$jobData['run']['last_colorpage_leadtime'];
           $pagelead=$jobData['run']['last_page_leadtime'];
           $platelead=$jobData['run']['last_plate_leadtime'];
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
               print "<li style='font-weight:bold;color:red;'>Past last plate deadline with $platesout plates remaining</li>";
           } else {
               print "<li>$platesout plates remaining</li>";
           }
           print "<li>$pagedeadline Last page release</li>\n";
           if ($now>$fullpagedeadline && $pagesout>0)
           {
               print "<li style='font-weight:bold;color:red;'>Past last page deadline with $pagesout pages remaining</li>";
           } else {
               print "<li>$pagesout pages remaining</li>";
           }
           print "<li>$colordeadline Last color release</li>\n";
           if ($now>$fullcolordeadline && $colorout>0)
           {
               print "<li style='font-weight:bold;color:red;'>Past last color deadline with $colorout color pages remaining</li>";
           } else {
               print "<li>$colorout color pages remaining</li>";
           }
       print "</div>\n";
       
      print "<div style='margin-top:10px;'>\n";
    
        
        
        $sql="SELECT press_id, notes_press, notes_job FROM jobs WHERE id=$jobid";
        $dbJobInfo=dbselectsingle($sql);
        $jobinfo=$dbJobInfo['data'];
        print "<h3>Messages</h3>\n";
        $message=$GLOBALS['pressStartMessage'];
        if($jobData['job']['job_message']!='')
        {
            $message.="<li>".stripslashes($jobData['job']['job_message'])."</li>";
        }
        //look at the pub id and see if there is an associated run message
        $sql="SELECT pub_message FROM publications WHERE id=$pubid";
        $dbPubMessage=dbselectsingle($sql);
        $pubmessage=$dbPubMessage['data']['pub_message'];
        if($pubmessage!=''){$message.="<li>".stripslashes($pubmessage)."</li>";}
        
        //look at the run id and see if there is an associated run message
        $sql="SELECT run_message FROM publications_runs WHERE id=$runid";
        $dbRunMessage=dbselectsingle($sql);
        $runmessage=$dbRunMessage['data']['run_message'];
        if($runmessage!=''){$message.="<li>".stripslashes($runmessage)."</li>";}
        
        $message=str_replace(array("\r", "\n", "\t"), '<br>',$message);
        $message=addslashes($message);
        $message="<div style='font-size:16px;'>$message</div>";
        print "<h3>Notes</h3>\n";
        print stripslashes($jobinfo['notes_job']."\n".$jobinfo['notes_press'])."<br />";
          

      print "<p style='font-weight:bold;font-size:14px;margin-left:10px;'>Plate Notes:</p>\n";
       print "<textarea id='platenotes' rows=10 cols=25 />$platenotes</textarea><br />";
      print "<input type='button' onclick=\"saveMonitorNotes('plate');\" value='Save Notes' />";
      print "</div>\n";
 print "</div>\n";     
 
 print "<div style='width:270px;float:left;'>\n";      
       
       print "<div id='pagesout' style='width:265px;'>\n";
          print "<fieldset>\n<legend>Pages Remaining</legend>\n";
          print "<div id='pageslist' style='padding:2px;height:200px;overflow-y:auto;border:1px solid black;background-color:white'>\n";
          pageslist($jobid);
           print "</div>\n";
          print "</fieldset>\n";
      print "</div>\n";
        
      print "<div id='remakes' style='width:265px;'>\n";
        print "<fieldset>\n<legend>Remakes</legend>\n";
        print "<div id='remakeslist' style='padding:2px;height:200px;overflow-y:auto;border:1px solid black;background-color:white'>\n";
        remakeslist($jobid);
        print "</div>\n";
        print "</fieldset>\n";
      print "</div>\n";
        
  print "</div>\n";     
   print "<div class='clear'></div>\n";
        
print "</div>\n";

print "<div class='clear'></div>\n";
print "</div>\n";

function remakeslist($jobid)
{
    $sql="SELECT DISTINCT(page_number), section_code, version, workflow_receive FROM job_pages WHERE job_id=$jobid AND version>1 ORDER BY page_number ASC, version DESC";
    $dbPages=dbselectmulti($sql);
    if ($dbPages['numrows']>0)
    {
        print "<table>\n";
        print "<tr><th>Section</th><th>Page</th><th>Version</th><th>Receive Time</th></tr>\n";
        foreach($dbPages['data'] as $page)
        {


            print "<tr>\n";
            print "<td>".$page['section_code']."</td>\n";
            print "<td>".$page['page_number']."</td>\n";
            print "<td>".$page['version']."</td>\n";
            print "<td>";
            if ($page['workflow_receive']!='')
            {
                print date("H:i:s",strtotime($page['workflow_receive']));
            } else {
                print "Not received";
            }
            print "</td>";
            print "</tr>\n";
        
        }
        print "</table>\n";
    } else {
        print "No remakes at this time.";
    }
}

function pageslist($jobid)
{
    $sql="SELECT DISTINCT(page_number), section_code, page_number, color FROM job_pages WHERE job_id=$jobid AND page_release is Null ORDER BY section_code ASC, page_number ASC";
    $dbPages=dbselectmulti($sql);
    if ($dbPages['numrows']>0)
    {
        print "<table>\n";
        print "<tr><th>Section</th><th>Page</th><th>Color</th></tr>\n";
        foreach($dbPages['data'] as $page)
        {
            print "<tr>\n";
            print "<td>".$page['section_code']."</td>\n";
            print "<td>".$page['page_number']."</td>\n";
            if ($page['color']){print "<td>Full color</td>";}else{print "<td>Black</td>";}
            print "</tr>\n";
        }
        print "</table>\n";
    } else {
        print "All pages have been received.";
    }
}        
print "</div>\n";
print "</div>\n";

?>
<script>
$(document).ready(function() 
{
   // By suppling no content attribute, the library uses each elements title attribute by default
   $('img').qtip({
      content: {
         text: false // Use each elements title attribute
      },
      show: { solo: true },
      hide: { when: 'inactive', delay: 1500 },
      style: 'cream' // Give it some style
   });
   
   // NOTE: You can even omit all options and simply replace the regular title tooltips like so:
   // $('#content a[href]').qtip();
});
setInterval('getDeadlineDetails(<?php echo $jobid; ?>)',60000);
</script>
<?php
footer();
?>
