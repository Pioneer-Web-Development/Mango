<?php
//<!--VERSION: .9 **||**-->
$refresh=true;

include("includes/mainmenu.php") ;
include("includes/job_helper.php") ;

    $jobData=nav();
    $layoutid=$jobData['job']['layout_id'];
    $pubname=$jobData['pub']['pub_name'];
    $pubdate=$jobData['job']['pub_date'];
    $pubday=strtolower(date("l",strtotime($jobData['job']['pub_date'])));
    $jobname=$jobData['run']['run_name'];
    $draw=$jobData['job']['draw'];
    $jobid=$jobData['job']['id'];
    $runid=$jobData['job']['run_id'];
    $pagenotes=stripslashes($jobData['job']['page_notes']);

    $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
    
    $jobsections=dbselectsingle($sql);
    $jsections=$jobsections['data'];
    print "<div class='ui-widget ui-widget-content ui-corner-all' style='width:100%;height: auto;'>\n";
    print "<span>* indicates a remake page or plate</span><br>";
    print "<div id='pageside' style='float:left;width:500px;height:auto;padding-left:10px;'>";
        print "<p style='font-weight:bold;font-size:14px;margin-left:10px;'>Pages for this job:</p>\n";
        for ($i=1;$i<=3;$i++)
        {
            $sectioncode="section".$i."_code";
            $sectionname="section".$i."_name";
            $sectionlow=$jsections["section".$i."_lowpage"];
            $sectionhigh=$jsections["section".$i."_highpage"];
            $sectionused=$jsections["section".$i."_used"];
            if ($sectionused==1)
            {
                //now get the pages
                $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='$jsections[$sectioncode]' AND current=1 ORDER BY page_number ASC";
                $dbPages=dbselectmulti($sql);
                if ($dbPages['numrows']>0)
                {
                    
                    print "<div style='float:left;width:170px;margin-left:10px;'>
                    <b>Section $jsections[$sectionname]</b>
                    </div>
                    <div style='float:left;width:300px;'>
                    <b>Page Number</b>
                    </div>
                    <div class='clear'></div>\n";
                    foreach ($dbPages['data'] as $page)
                    {
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
                        if ($page['color']==1)
                        {
                            $color="Full Color";
                        }elseif($page['spot']==1) {
                            $color="Spot";
                        }else{
                            $color="Black";
                        }
                        
                        
                        $pid=$page['id'];
                        $pversion=$page['version'];
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
                            $originalpageid=$page['original_id'];
                            $plateid=$page['plate_id'];
                            $sql="SELECT * FROM job_plates WHERE id=$plateid";
                            $dbPlate=dbselectsingle($sql);
                            $originalplateid=$dbPlate['data']['original_id'];
                            $premake="*Page";
                            $pageremake="<span id='remakepage$pid'><img src='artwork/redoIcon.png' width=20 style='padding-top:2px;' onclick='resetRemakePage($pid,$originalpageid,$plateid,$originalplateid);' alt='Undo Remake' title='Undo the remake. Resets page and plate back to the originals.'></span>\n";
                        } else {
                            if($GLOBALS['remakeLabel']=='remake')
                            {
                                $pageremake="<span id='remakepage$pid'><img src='artwork/remakeIcon.png' width=20 style='padding-top:2px;' onclick='remakePage($pid);' alt='Remake Page' title='Create a new remake version of this page. Will start approval process again.'></span>\n";
                            } else {
                                $pageremake="<span id='remakepage$pid'><img src='artwork/chaseIcon.png' width=20 style='padding-top:2px;' onclick='remakePage($pid);' alt='Remake Page' title='Create a new chase version of this page. Will start approval process again.'></span>\n";
                            }
                            $premake="Page";
                            
                        }
                        print "<div id='page$pid'>\n";
                            print "<div style='float:left;width:170px;'>$premake $pnumber - $color<br>$viewsubs<br /></div>
                            <div style='float:left;width:300px;'>$pagesend $colorrelease $pageremake $viewdetails $pdetails</div>
                            <div class='clear'></div>\n";
                            print "<div id='pageDetails$pid' style='display:none;margin-left:20px;font-size:10px;border:thin solid black;padding:4px;'>$detail</div>\n";
                        print "</div>\n";              
    
                    }
                }
            } 
        }
        print "<div class='clear'></div>\n";
        print "</div>\n";


        print "<div id='plateside' style='float:left;width:500px;'>\n";
            print "<p style='font-weight:bold;font-size:14px;margin-left:10px;'>Plates for this job:</p>\n";
            $colors=array("black","cyan","magenta","yellow");
            for ($i=1;$i<=3;$i++)
            {
                $sectioncode="section".$i."_code";
                $sectionname="section".$i."_name";
                $sectionlow=$jsections["section".$i."_lowpage"];
                $sectionhigh=$jsections["section".$i."_highpage"];
                if ($jsections[$sectioncode]!='')
                {
                    //now get the pages
                    $sql="SELECT * FROM job_plates WHERE job_id=$jobid AND section_code='$jsections[$sectioncode]' AND current=1 ORDER BY low_page ASC";
                    $dbPlates=dbselectmulti($sql);
                    if ($dbPlates['numrows']>0)
                    {
                        $cpage=0;
                        $insub=false;
                        
                        print "<div id='plates'>\n";
                        print "<div style='float:left;width:170px;margin-left:10px;'><b>Section $jsections[$sectionname]</b></div><div style='float:left;width:200px;'><b>Plate Number</b></div><div class='clear'></div>\n";
                        foreach ($dbPlates['data'] as $plate)
                        {
                            $detail="";
                            $releasetime="";
                            $creleasetime="";
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
                                    $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$receive]))." - $color plate out of bender<br>\n";
                                } 
                                if ($plate[$approval]!='')
                                {
                                    $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$approval]))." - $color plate approved<br>\n";
                                    $creleasetime=date("H:i:s",strtotime($plate[$approval]));
                                } else {
                                    $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$approval]))." - $color plate waiting for aproval<br>\n";
                                    
                                }
                                if ($plate[$ctp]!='')
                                {
                                    $detail.=date("D, M jS Y \@ H:i:s",strtotime($plate[$ctp]))." - $color plate delivered to platesetter<br>\n";
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
                            $platesend.="<img src='artwork/approveCheck.png' width=20 style='padding-top:2px;' id='send$pid' onclick=\"setPaginationTime('$pid','plateapprove','now');\" alt='Approve Plate' title='Click to approve the black for this plate. Also releases plate.'>\n";
                            $colorrelease="<input type='text' id='platecolortime_$pid' name='platecolortime_$pid' value='$creleasetime' style='width:60px' /> <script type='text/javascript'>\$('#platecolortime_$pid').timepicker({onClose: function(dateText, inst){setPaginationTime('$pid','platecolor',dateText);}});</script>";
                            $colorrelease.="<img src='artwork/approveColorCheck.png' width=20 style='padding-top:2px;' id='platecolorsend$pid' onclick=\"setPaginationTime('$pid','platecolor','now')\" alt='Approve Color' title='Click to approve the color (C,M,Y) for this plate.'>\n";
                            $viewdetails="<a href='#' onclick=\"viewPlateDetails($pid);\">View Details</a>\n";
                            if($plate['remake']){$premake="*Plate";}else{$premake="Plate";}
                            print "<div id='plate$pid'>\n";
                                print "<div style='float:left;width:170px;margin-left:10px;'>$premake $platenumber $platecolor<br><small>Pages on plate: $ppages</small></div><div style='float:left;width:300px;'>$platesend $colorrelease $viewdetails</div><div class='clear'></div>\n";
                                print "<div id='plateDetails$pid' style='display:none;margin-left:20px;font-size:10px;border:thin solid black;padding:4px;'>$detail</div>\n";
                            print "</div>\n";
                        }
                        print "</div>\n";
                    }

                } 

            }
            
       print "</div>\n";
        
       print "<div id='deadlines' style='float:left;width:200px;'>\n";
           print "<p style='font-weight:bold;font-size:14px;margin-left:10px;'>Deadlines and page flow:</p>\n";
           print "<div id='deadlinedata'>\n";
               $scheduledstart=$jobData['job']['startdatetime'];
               $dow=date("N",strtotime($scheduledstart));
               $colorlead=$jobData['run']['last_colorpage_leadtime_'.$dow];
               $pagelead=$jobData['run']['last_page_leadtime_'.$dow];
               $platelead=$jobData['run']['last_plate_leadtime_'.$dow];
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
               $sql="SELECT COUNT(id) as pagesout FROM job_pages WHERE job_id=$jobid AND page_release IS Null AND remake=0";
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
          

           
           
           
           
           print "<div style='margin-top:10px;'>\n";
           print "<p style='font-weight:bold;font-size:14px;margin-left:10px;'>Page Notes:</p>\n";
           print "<textarea id='pagenotes' rows=10 cols=25 />$pagenotes</textarea><br />";
           print "<input type='button' onclick=\"saveMonitorNotes('page');\" value='Save Notes' />";
       print "</div>\n";
       
       
       print "</div>\n";
       
      
       print "<div class='clear'></div>\n";
        
print "</div>\n";
/*
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
setInterval('getDeadlineDetails(<?php echo $jobid; ?>)',15000);
</script>
<?php
*/
print "<script type='text/javascript'>setInterval('getDeadlineDetails($jobid)',60000);</script>";
footer();
?>

