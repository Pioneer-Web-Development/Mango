<?php
error_reporting (0);
session_start();
include ("includes/functions_db.php");
include ("includes/config.php");
include ("includes/functions_common.php");
include ("includes/functions_formtools.php");
include ("includes/layoutGenerator.php");

?>
<!DOCTYPE html>
<html>
<head>
<title>Edit press job</title>
<?php
$scriptname=end(explode("/",$_SERVER['SCRIPT_NAME']));
//lets load the javascript files
$sql="SELECT * FROM core_system_files WHERE file_type='script' AND head_load=1 ORDER BY load_order ASC";
$dbScripts=dbselectmulti($sql);
if($dbScripts['numrows']>0)
{
    foreach($dbScripts['data'] as $script)
    {
        if($script['specific_page']=='' || $script['specific_page']==$scriptname)
        {
            print "<script type='text/javascript' src='includes/jscripts/$script[file_name]'></script>\n";     
        } else {
            print "<!-- when loading $script[file_name] looked for $script[specific_page] compared to $scriptname -->\n";
        }       
    }
}
//lets load the stylesheets
$sql="SELECT * FROM core_system_files WHERE file_type='style' AND head_load=1 ORDER BY load_order ASC";
$dbStyles=dbselectmulti($sql);
if($dbStyles['numrows']>0)
{
    foreach($dbStyles['data'] as $style)
    {
       if($style['specific_page']=='' || $style['specific_page']==$scriptname)
       {
           print "<link rel='stylesheet' type='text/css' href='styles/$style[file_name]' />\n";
                
       }       
    }
}
?>

</head>
<body>
<?php
global $laps,$folders, $defaultPressID, $presses, $papertypes,$pubs, $sizes, $producttypes, $leadtypes, $continuejob, $cjobenddatetime, $siteID, $jobTypes;
global $folderpins;
if (isset($_GET['id']))
{
    $jobid=intval($_GET['id']);
    //now lets get the job information
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    $jobstartdate=date("Y-m-d H:i",strtotime($job['startdatetime']));
    $jobenddate=date("Y-m-d",strtotime($job['enddatetime']));
    
    $jobname=stripslashes($job['job_name']);
    if ($job['pub_date']=='')
    {
        $pubdate=date("Y-m-d",strtotime("+1 day"));
    } else {
        $pubdate=date("Y-m-d",strtotime($job['pub_date']));
    }
    $pressid=$job['press_id'];
    $pubid=$job['pub_id'];
    $insertpubid=$job['insert_pub_id'];
    if($insertpubid==0){$insertpubid=$pubid;}
            
    $runid=$job['run_id'];
    $folder=$job['folder'];
    if ($folder==0){$folder=$GLOBALS['defaultFolder'];}
    $leadtype=$job['leadtype'];
    $papertype=$job['papertype'];
    $lap=$job['lap'];
    $layoutid=$job['layout_id'];
    $draw=$job['draw'];
    $overrun=$job['overrun'];
    if($pubid!=0)
    {
        $runSQL="SELECT * FROM publications_runs WHERE pub_id=$pubid ORDER BY run_name";
        $dbRuns=dbselectmulti($runSQL);
        $runs=array();
        $runs[0]="Please choose press run";
        if ($dbRuns['numrows']>0)
        {
            foreach($dbRuns['data'] as $lrun)
            {
                //$runs[$lrun['id']]=$lrun['run_name'];
                if($lrun['run_productcode']!='')
                {
                    $runs[$lrun['id']]=stripslashes($lrun['run_name']).' -- PC: '.$lrun['run_productcode'];
                } else {
                    $runs[$lrun['id']]=stripslashes($lrun['run_name']);
                }
                $runProductCodes[$run['id']]=$lrun['run_productcode'];
            }
        }        
    } else {
        $runs[0]='Select a publication first';
        $runProductCodes[0]='Select a run';
    }    
        //tab two items
        $quarterfold=$job['quarterfold'];
        $stitch=$job['stitch'];
        $trim=$job['trim'];
        $glossycover=$job['glossy_cover'];
        $glossydraw=$job['glossy_cover_draw'];
        $glossyinside=$job['glossy_insides'];
        $glossyinsidecount=$job['glossy_insides_count'];
        
        
        $coverduedate=$job['cover_date_due'];
        if ($coverduedate==''){$coverduedate=date("Y-m-d");}
        $coverprintdate=$job['cover_date_print'];
        if ($coverprintdate==''){$coverprintdate=date("Y-m-d");}
        $coveroutputdate=$job['cover_date_output'];
        if ($coveroutputdate==''){$coveroutputdate=date("Y-m-d");}
        
        $pagerelease=$job['page_release'];
        if ($pagerelease==''){$pagerelease=date("Y-m-d H:i");}
        $pagerip=$job['page_rip'];
        if ($pagerip==''){$pagerip=date("Y-m-d H:i");}
        $binderystart=$job['bindery_startdate'];
        if ($binderystart==''){$binderystart=date("Y-m-d");}
        $binderydue=$job['bindery_duedate'];
        if ($binderydue==''){$binderydue=date("Y-m-d");}
        $deliverynotes=stripslashes($job['notes_delivery']);
        $binderynotes=stripslashes($job['notes_bindery']);
        $pressnotes=stripslashes($job['notes_press']);
        $insertingnotes=stripslashes($job['notes_inserting']);
        $jobmessage=stripslashes($job['job_message']);
        
        $folderPin=$job['folder_pin'];
        $slitter=$job['slitter'];
        $requiresDelivery=$job['requires_delivery'];
        $requiresAddressing=$job['requires_addressing'];
        $requiresInserting=$job['requires_inserting'];
        $jobType=$job['job_type'];
        $rollSize=$job['rollSize'];
        $paperid=$job['papertype'];
        $runspecial=$job['runspecial'];
        $pagewidth=$job['pagewidth'];
        if ($pagewidth==0 || $pagewidth=='')
        {
            $pagewidth=$GLOBALS['broadsheetPageWidth'];
            $pinit=true;
        }
        //get section information
        $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
        $dbSection=dbselectsingle($sql);
        if ($dbSection['numrows']>0)
        {
            //ok, at least it looks like we have some section data
            $sections=$dbSection['data'];
            $section1_format=$sections['section1_producttype'];
            $section1_low=$sections['section1_lowpage'];
            $section1_high=$sections['section1_highpage'];
            $section1_name=$sections['section1_name'];
            $section1_letter=$sections['section1_code'];
            $section1_doubletruck=$sections['section1_doubletruck'];
            $section1_gatefold=$sections['section1_gatefold'];
            $section1_lead=$sections['section1_leadtype'];
            $section1_overrun=$sections['section1_overrun'];
            $section1_used=$sections['section1_used'];
            
            $section2_format=$sections['section2_producttype'];
            $section2_low=$sections['section2_lowpage'];
            $section2_high=$sections['section2_highpage'];
            $section2_name=$sections['section2_name'];
            $section2_letter=$sections['section2_code'];
            $section2_doubletruck=$sections['section2_doubletruck'];
            $section2_gatefold=$sections['section2_gatefold'];
            $section2_lead=$sections['section2_leadtype'];
            $section2_overrun=$sections['section2_overrun'];
            $section2_used=$sections['section2_used'];
           
            $section3_format=$sections['section3_producttype'];
            $section3_low=$sections['section3_lowpage'];
            $section3_high=$sections['section3_highpage'];
            $section3_name=$sections['section3_name'];
            $section3_letter=$sections['section3_code'];
            $section3_doubletruck=$sections['section3_doubletruck'];
            $section3_gatefold=$sections['section3_gatefold'];
            $section3_lead=$sections['section3_leadtype'];
            $section3_overrun=$sections['section3_overrun'];
            $section3_used=$sections['section3_used'];
            
        } else {
            $section1_used=0;
            $section1_format=0;
            $section1_low=1;
            $section1_high=2;
            $section1_name='A';
            $section1_letter='A';
            $section1_doubletruck=0;
            $section1_gatefold=0;
            $section1_lead=0;
            $section1_overrun=0;
            
            $section2_used=0;
            $section2_format=0;
            $section2_low=1;
            $section2_high=2;
            $section2_name='B';
            $section2_letter='B';
            $section2_doubletruck=0;
            $section2_gatefold=0;
            $section2_lead=0;
            $section2_overrun=0;
            
            $section3_used=0;
            $section3_format=0;
            $section3_low=1;
            $section3_high=2;
            $section3_name='C';
            $section3_letter='C';
            $section3_doubletruck=0;
            $section3_gatefold=0;
            $section3_lead=0;
            $section3_overrun=0;
        }
     
} else {
    die("You can only utilize this scheduler after dragging a job holder on the schedule.");
}
print "<form id='jobsetup' name='jobsetup' method='post' action='jobPressPopup_handler.php'>\n";
    print "<div style='float:right;'>\n";
    if(!$_GET['ne'])
    {
        print "<input type='submit' id='submitbutton' value='Save Job'>\n";
    }
    print "<input type='button' value='Close' onclick='self.close()'>\n";
    print "</div><div class='clear'></div>\n";
print "<div id='tabs'>\n";
print "<ul id='jobTabs' class='contenttabs'>\n";
print "    <li><a href='#jsetup'>Basic Information</a></li>\n";
print "    <li><a href='#jsections'>Sections</a></li>\n";
print "    <li><a href='#jlayout'>Layout</a></li>\n";
print "    <li><a href='#jcolor'>Color</a></li>\n";
print "    <li><a href='#jbindery'>Bindery</a></li>\n";
print "    <li><a href='#jglossy'>Glossy/Delivery Info</a></li>\n";
print "    <li><a href='#jstats' >Stats</a></li>\n";
print "    <li><a href='#jnotes'>Notes/Messages</a></li>\n";
print "    <li><a href='#jrecur' >Recurring/Duplicate</a></li>\n";
print "</ul>\n";

    
    print "<input type='hidden' name='job_id' id='job_id' value='$jobid'>\n";
    print "<input type='hidden' name='layout_id' id='layout_id' value='$layoutid'>\n";
            
    
    print "<div id='jsetup'>\n";
        
        //source,type,sourceID,destID
        if(count($presses)>0 && !array_key_exists(0,$presses))
        {
            make_select('pressid',$presses[$pressid],$presses,'Select Press');
        } else {
            make_hidden('pressid',$pressid);
        }
        make_select('pub_id',$pubs[$pubid],$pubs,'Publication','','',false);
        
        print "<div class='label'>Run</div>\n";
        print "<div class='input'>\n";
        print input_select('run_id',$runs[$runid],$runs);
        print "<br>If your run does not exist in the list please enter it:<br />";
        print "Run Name: <input type='text' id='run_special' name='run_special' size=30> Product Code: <input type='text' id='run_special_productcode' name='run_special_productcode' size=5>\n";
        print "</div>\n";
        print "<div class='clear'></div>\n";
        
        make_select('insertpub_id',$pubs[$insertpubid],$pubs,'Insert Publication','If this job inserts back into a different publication, please select it here.');
        make_checkbox('createinsert',0,'Create Insert','If checked, this job will be turned into an insert<br />automatically,similar to if the run is set up that way in<br />publications. Only one insert will ever be created per job.');
            //make_number('drawTotal',$draw,'Draw request');
        //make_number('drawOther',$overrun,'Overrun');
        ?>
        <div style='float:left;width:350px;'>
            <div class='label'>
                <label for='drawTotal'>Draw request</label>
            </div>
            <div class='input'>
                <input type="text" id="drawTotal" name="drawTotal" value="<?php echo $draw; ?>" size=10 onkeypress="return isNumberKey(event);" />
            </div>
            <div class='clear'></div>
            <div class='label'>Publish date</div>
                <div class='input'>
                    <input type='text' name='pubdate' id='pubdate' value='<?php echo $pubdate; ?>'/>
                    <?php 
                        if(in_array(39,$_SESSION['cmsuser']['permissions']) || $_SESSION['cmsuser']['admin'])
                        {
                            $minDate=time();
                        } else {
                            $minDate=strtotime("+".$GLOBALS['lockPressPub']." hours");
                        }
                        $minYear=date("Y",$minDate);
                        $minMonth=date("m",$minDate)-1;
                        $minDay=date("d",$minDate);
                    ?>
                    <script type='text/javascript'>
                        $('#pubdate').datepicker({ 
                            dateFormat: 'yy-mm-dd', 
                            minDate: new Date(<?php echo $minYear ?>,<?php echo $minMonth ?>,<?php echo $minDay ?>),
                            onClose: function(date){
                                $('#jobstartdate').datetimepicker({ 
                                    minDate: date 
                                })  
                            } 
                        });
                        
                        </script>
                    
                </div>
            <div class='clear'></div>
        </div>
        <div style='float:left;'>
            <div class='label'>
                <label for='drawOther'>Overrun</label>
            </div>
            <div class='input'>
                <input type="text" id="drawOther" name="drawOther" value="<?php echo $overrun; ?>" size=10 onkeypress="return isNumberKey(event);" />
            </div>
            <div class='clear'></div>
            <div class='label'>Print date/time</div>
                <div class='input'>
                    <input type='text' name='jobstartdate' id='jobstartdate' value='<?php echo $jobstartdate; ?>'/>
                    <script type='text/javascript'>
                    $('#jobstartdate').datetimepicker({ 
                        dateFormat: 'yy-mm-dd', 
                        stepMinute: 5
                        });
                    </script>
            </div>
            <div class='clear'></div>
        </div>
        <div class='clear'></div>
        <?php
            
        make_select('folder',$folders[$folder],$folders,'Folder to use');
        make_select('job_type',$jobTypes[$jobType],$jobTypes,'Type of job');
        make_select('newsprint',$papertypes[$paperid],$papertypes,'Type of paper',"<span style='color:red;font-weight:bold;'>Dont just gloss over this, double check it!");
        make_select('papertype_cover',$papertypes[$papertypecover],$papertypes,'Cover paper',"<span style='color:red;font-weight:bold;'>If the outside or one web is on a different paperstock, please select it here.</span>");
        make_select('pagewidth',$pagewidth,$GLOBALS['sizes'],'Size of a full page','For tabs and tall tabs, this would actually be the total height of the page including margins.<br>For flexis it is the width of a page including margins.<br>Example: 8x10 flexi book. Ends up printed on 34in paper. Individual pages are 8.5in wide with margin. 4-wide on paper');
        if($GLOBALS['askForRollSize'])
        {
            make_select('rollSize',$GLOBALS['sizes'][$rollSize],$GLOBALS['sizes'],'Default Roll Width','Size of a full roll for this job.');
        }
        make_select('lap',$laps[$lap],$laps,'Type of lap');
        make_select('folderpin',$folderpins[$folderPin],$folderpins,'Folder setup');
        make_checkbox('slitter',$slitter,'Slitter','Check to have the slitter on');
        make_checkbox('quarterfold',$quarterfold,'Quarterfold','Check to have product quarterfolded');
    print "</div>\n";
    
    print "<div id='jbindery'>\n";
        print "<fieldset>\n";
        print "<legend>Miscellaneous</legend>\n";
        make_checkbox('requires_addressing',$requiresAddressing,'Requires Addressing','Check if this job needs to have addressing done.');
        make_checkbox('requires_inserting',$requiresInserting,'Will be inserted','Check if this job will be inserted into another product.');
        make_textarea('notes_inserting',$insertingNotes,'Inserting Notes','Any information about inserting',80,5,false);       
        print "</fieldset>\n";
        
        print "<fieldset>\n";
        print "<legend>Page Information</legend>\n";
        make_datetime('pagerelease',$pagerelease,'Page Release','Job release time to prepress for output');
        make_datetime('pagerip',$pagerip,'Page Rip','Deadline for prepress to output files');
        print "</fieldset>\n";
        
        print "<fieldset>\n";
        print "<legend>Bindery Information</legend>\n";
        make_checkbox('trim',$trim,'Trim','Check if this job is trimmed.');
        make_checkbox('stitch',$stitch,'Stitch','Check if this job is stitched.');
        make_date('bindery_start',$binderystart,'Bindery Request Start','When should bindery start?');
        make_date('bindery_due',$binderydue,'Bindery Due','When is the bindery due to complete?');
        make_textarea('notes_bindery',$binderynotes,'Bindery Notes','Bindery Instructions',80,5,false);       
        print "</fieldset>\n";
        
        ?>
        <script type='text/javascript'>
        $('#notes_delivery').change(function(){
            if($('#notes_delivery').val()!='')
            {
                $('#requires_delivery').prop('checked',true);
            } else {
                $('#requires_delivery').prop('checked',false);
            }
        })
        </script>
        <?php
            
        
        
    print "</div>\n";
           
    print "<div id='jsections'>\n";
        print "<div style='float:left;width: 220px;margin-right:10px;'>\n";
        print "<b>Section 1</b><br />\n";
        print input_checkbox('section1_enable',$section1_used)." Check to enable this section<br />\n";
        print "Name: ".input_text('section1_name',$section1_name,'10',false,'toggleSection(1);')."<br />\n";
        print "Letter: ".input_text('section1_letter',$section1_letter,5)."<br />\n";
        print "Low page: ".input_text('section1_low',$section1_low,'5',false,'','','','return isNumberKey(event);')."<br />\n";
        print "High page: ".input_text('section1_high',$section1_high,'5',false,'','','','return isNumberKey(event);')."<br />\n";
        print "Format: ".input_select('section1_format',$producttypes[$section1_format],$producttypes)."<br />\n";
        print "Doubletruck: ".input_checkbox( 'section1_doubletruck',$section1_doubletruck)."<br />\n";
        print "Gatefold: ".input_checkbox( 'section1_gatefold',$section1_gatefold)."<br />\n";
        print "Lead: ".input_select('section1_lead',$leadtypes[$section1_lead],$leadtypes)."<br />\n";
        print "Section overrun: ".input_text('section1_overrun',$section1_overrun,'5',false,'','','','return isNumberKey(event);')."<br />\n";
        print "</div>\n";
        
        print "<div style='float:left;width: 220px;margin-right:10px;'>\n";
        print "<b>Section 2</b><br />\n";
        print input_checkbox('section2_enable',$section2_used)." Check to enable this section<br />\n";
        print "Name: ".input_text('section2_name',$section2_name,'10',false,'toggleSection(2);')."<br />\n";
        print "Letter: ".input_text('section2_letter',$section2_letter,5)."<br />\n";
        print "Low page: ".input_text('section2_low',$section2_low,'5',false,'','','','return isNumberKey(event);')."<br />\n";
        print "High page: ".input_text('section2_high',$section2_high,'5',false,'','','','return isNumberKey(event);')."<br />\n";
        print "Format: ".input_select('section2_format',$producttypes[$section2_format],$producttypes)."<br />\n";
        print "Doubletruck: ".input_checkbox( 'section2_doubletruck',$section2_doubletruck)."<br />\n";
        print "Gatefold: ".input_checkbox( 'section2_gatefold',$section2_gatefold)."<br />\n";
        print "Lead: ".input_select('section2_lead',$leadtypes[$section2_lead],$leadtypes)."<br />\n";
        print "Section overrun: ".input_text('section2_overrun',$section2_overrun,'5',false,'','','','return isNumberKey(event);')."<br />\n";
        print "</div>\n";
        
        print "<div style='float:left;width: 220px;margin-right:10px;'>\n";
        print "<b>Section 3</b><br />\n";
        print input_checkbox('section3_enable',$section3_used)." Check to enable this section<br />\n";
        print "Name: ".input_text('section3_name',$section3_name,'10',false,'toggleSection(3);')."<br />\n";
        print "Letter: ".input_text('section3_letter',$section3_letter,5)."<br />\n";
        print "Low page: ".input_text('section3_low',$section3_low,'5',false,'','','','return isNumberKey(event);')."<br />\n";
        print "High page: ".input_text('section3_high',$section3_high,'5',false,'','','','return isNumberKey(event);')."<br />\n";
        print "Format: ".input_select('section3_format',$producttypes[$section3_format],$producttypes)."<br />\n";
        print "Doubletruck: ".input_checkbox( 'section3_doubletruck',$section3_doubletruck)."<br />\n";
        print "Gatefold: ".input_checkbox( 'section3_gatefold',$section3_gatefold)."<br />\n";
        print "Lead: ".input_select('section3_lead',$leadtypes[$section3_lead],$leadtypes)."<br />\n";
        print "Section overrun: ".input_text('section3_overrun',$section3_overrun,'5',false,'','','','return isNumberKey(event);')."<br />\n";
        print "</div>\n";
        print "<div class='clear'></div>\n";
        
        print "<input type='hidden' name='sectionid' id='sectionid' value=$sectionid>\n";
    
    print "</div>\n";
    
    
    print "<div id='jlayout' >\n";
        print "<fieldset>\n";
        print "<legend>Layout Configuration</legend>\n";
        if(!$_GET['ne'])
        { 
            print "<div style='float:left;width: 300px;'>\n";
            
            print "<input type='button' class='submit' value='Find matching layouts' onClick='getLayouts();'><br />\n";
            print "<span style='color:green;font-weight:bold;' id='laymessage'></span><br />";
            print "<p style='font-size:14px;font-weight:bold;'>Possible Layouts:</p>\n";
            print input_select('layouts','Get list',array('Get list'),'','getPressDiagram()');
            
            print "<br /><input id='setlayoutBtn' type='button' class='button' value='Set Layout' onClick='setLayout()' /><br />";
            print "<input type='button' class='button' style='margin-top:150px;' value='Remove Existing Layout' onClick='removeLayout();'><br />\n";
            
            print "<span id='saveLayoutResponse' name='saveLayoutResponse' style='width:250px;'>";
            print "</span>\n";
            print "</div>\n";
        }
        print "<div id='layout_preview' style='float:left;width: 230px;overflow-y:scroll;'>";
        if ($job['layout_id']!=0)
        {
            configure($job['layout_id'],true,false,true);
        }
        print "</div>\n";
        print "</fieldset>\n";
        
    print "</div>\n";
    
    print "<div id='jcolor'>\n";
        print "<div id='colorSettingArea'>\n";
        if($job['layout_id']!=0)
        {
            show_color($jobid);
        } else {
            print "You need to select a layout first.";
        }
        print "</div>\n";
    print "</div>\n";
    
    
    print "<div id='jglossy'>\n";
        print "<fieldset>\n";
        print "<legend>Glossy Information</legend>\n";
        make_checkbox('glossycover',$glossycover,'Glossy Cover','Check if this job has a glossy cover.');
        make_text('glossydraw',$glossydraw,'Gloss Cover Draw','How many covers (and/or insides) are needed?',10,'',false,'','','','return isNumberKey(event);');
        make_checkbox('glossyinside',$glossyinside,'Glossy Insides','Check if this job has one or more glossy inside sheets.');
        make_text('glossyinsidecount',$glossyinsidecount,'Gloss Inside Pieces','How many glossy inside sheets will there be?',10,'',false,'','','','return isNumberKey(event);');
        make_date('coveroutput',$coveroutputdate,'Cover Output','When do we need to output the cover?');
        make_date('coverprint',$coverprintdate,'Cover Prints','When will the cover print?');
        make_date('coverdue',$coverduedate,'Cover Due by','When do we need the cover back?');
        
        print "</fieldset>\n";
        print "<fieldset>\n";
        print "<legend>Delivery Information</legend>\n";
        make_checkbox('requires_delivery',$requiresDelivery,'Requires Delivery','Check if this job needs to be delivered.');
        make_textarea('notes_delivery',$deliverynotes,'Delivery Notes','Delivery Instructions',80,5,false);       
        print "</fieldset>\n";
        
    print "</div>\n";
    
    
    print "<div id='jstats'>\n";
    print "<b>Calculated stats for this job:</b><br />\n";
    $statsid=$job['stats_id'];
    $buildpaper=false;
    //see if there is a stats record
    $sql="SELECT * FROM job_stats WHERE id=$statsid";
    $dbStats=dbselectsingle($sql);
    if ($dbStats['numrows']>0)
    {
        $stats=$dbStats['data'];
        $actualstarttime=$stats['startdatetime_actual'];
        $startdate=date("Y-m-d H:i",strtotime($actualstarttime));
        
        $actualstoptime=$stats['stopdatetime_actual'];
        $stopdate=date("Y-m-d H:i",strtotime($actualstoptime));
        
        $goodcopytime=$stats['goodcopy_actual'];
        $gooddate=date("Y-m-d H:i",strtotime($goodcopytime));
            
            $counterstart=$stats['counter_start'];
            $counterstop=$stats['counter_stop'];
            $spoilsstartup=$stats['spoils_startup'];
            $spoilsrunning=$stats['spoils_running'];
            $spoilstotal=$stats['spoils_total'];
            $downtime=$stats['total_downtime'];
            $checklist=$stats['checklist_approved'];
            $jobpressoperator=$stats['job_pressoperator'];
            $jobpressmanids=$stats['job_pressman_ids'];
            $wastePlates=$stats['plates_waste'];
            $remakePlates=$stats['plates_remake'];
            if ($job['dataset_time']!='')
            {
                $datatime=$job['dataset_time'];
            } else {
                $datatime=date("Y-m-d H:i");
            }
            
            $jobnotes=stripslashes($job['notes_press']);
            //convert ids to array
            $jobpressmanids=explode("|",$jobpressmanids);
            if ($stats['tower_info']!='')
            {
                $temptower=explode("|",$stats['tower_info']);
                $towers=array();
                foreach($temptower as $tt)
                {
                    $tempt=explode(",",$tt);
                    $towers[$tempt[0]]['id']=$tempt[0];    
                    $towers[$tempt[0]]['tower_name']=$tempt[1];    
                    $towers[$tempt[0]]['used']=$tempt[2];    
                    $towers[$tempt[0]]['papertype']=$tempt[3];    
                    $towers[$tempt[0]]['size']=$tempt[4];
                        
                }                                    
            }
            
        print "<br />\n";
        print "<div style='float:left;width:120px;'>Gross Press:</div>$stats[gross]<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Waste:</div>$stats[waste_percent]%<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Spoils Total:</div>$stats[spoils_total]<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Job time:</div>$stats[run_time] minutes<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Overall avg speed:</div>$stats[run_speed] copies/hr<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Net avg speed:</div>$stats[good_runspeed] copies/hr<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Start Offset:</div>$stats[start_offset] minutes<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Finish Offset:</div>$stats[finish_offset] minutes<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Scheduled Runtime:</div>$stats[sched_runtime] minutes<div style='float:left;'></div><div class='clear'></div>\n";
    print "<br />\n";
    print "<div style='float:left;width:120px;'>Black Pages:</div>$stats[pages_bw]<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Color Pages:</div>$stats[pages_color]<div style='float:left;'></div><div class='clear'></div>\n";
    print "<br />\n";
    print "<div style='float:left;width:120px;'>Black plates:</div>$stats[plates_bw]<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Color Plates:</div>$stats[plates_color]<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Remake Plates:</div>$stats[plates_remake]<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Waste Plates:</div>$stats[plates_waste]<div style='float:left;'></div><div class='clear'></div>\n";
    print "<br />\n";
    print "<div style='float:left;width:120px;'>Last Page:</div>$stats[last_page]<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Last Color:</div>$stats[last_colorpage]<div style='float:left;'></div><div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Last Plate:</div>$stats[last_plate]<div style='float:left;'></div><div class='clear'></div>\n";
    print "<br />\n";
     print "<div style='float:left;width:120px;'>Man hours:</div>$stats[man_hours]<div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Total Tons:</div>$stats[total_rons]<div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Hours/ton:</div>$stats[hours_per_ton]<div class='clear'></div>\n";
    print "<div style='float:left;width:120px;'>Impressions/hour:</div>$stats[impressions_per_hour]<div class='clear'></div>\n";
     $sql="SELECT A.*, B.common_name, C.width FROM job_paper A, paper_types B, paper_sizes C WHERE A.job_id=$jobid AND A.papertype_id=B.id AND A.size_id=C.id";
        $dbRolls=dbselectmulti($sql);
        if ($dbRolls['numrows']>0)
        {
            print "Here is the paper used on this job:<br />\n";
            foreach($dbRolls['data'] as $roll)
            {
                print "$roll[common_name] - $roll[width], $roll[calculated_tonnage]MT - \$$roll[calculated_cost]<br />\n";
                
            }
        }
    } else {
        print "No stats calculated yet.";
    }
    print "</div>\n";
    
    print "<div id='jrecur'>\n";
        print "<fieldset>\n";
        print "<legend>Duplicate this job to a new publication date</legend>\n";
        make_checkbox('copyjob',0,'Copy Job','Create an exact copy of this job for a new publication date. Only choose one of the dates below.');
        make_date('copydate','','Copy to date','Date to copy this job to. This will actually schedule the job with a similar press time/pub date difference to this job.');
        make_date('dup_request_date','','Request Print Date','Date to copy this job to. Job will remain unscheduled and will be in the unscheduled bin for this requested date.');
        print "</fieldset>\n";
        
        
        print "<fieldset>\n";
        print "<legend>Recurring Job Creation</legend>\n";
        global $pubs, $daysofweek, $defaultNewsprintID, $recurFrequencies, $sizes;
        $specDates=array("Please Choose",1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
        $hour="12";
        $minute="00";
        $days=array();
        $daysprev=1;
        $recurFreq=0;
        $specified=0;
        $usedraw=0;
        $daysout=0;
        $enddatechecked=0;

        make_checkbox('makerecur',0,'Create recurrence','After saving this job, create a recurring job that matches and create future recurring jobs immediately');
        make_time('recstart',$starthour,$startminute,'Start Time','Job start time on selected days');
        make_number('daysout',$daysout,'Days out','How far out to create jobs from current day? This is the continual padding between current day and future.');
        make_checkbox('usedraw',$usedraw,'Use draw','Build recurring jobs with the set draw, not just to estimate run length');
        make_select('frequency',$recurFrequencies[$recurFreq],$recurFrequencies,'Recurring Frequency','Specify how regular the recurrence is,');
        make_select('specifieddate',$specDates[$specified],$specDates,'Specified Date','Recurrence happens only on specifed date of the month');
        print "<div class='label'>Publication Days</div><div class='input'>\n";
        print "Select the actual PUBLICATION days here, the days previous above controls the print date.<br />\n";
        print "<div style='float:left;width:120px'>\n";
        $i=1;
        foreach($daysofweek as $did=>$dname)
        {
            if (in_array($did,$days)){$checked="checked";}
            print "<input type='checkbox' name='day_$did' $checked> $dname<br />\n";
            if ($i==2)
            {
                $i=1;
                print "</div><div style='float:left;width:120px;'>\n";
            } else {
                $i++;
            }
            $checked="";        
        }
        print "</div>\n";
        print "</div><div class='clear'></div>\n";
        make_number('days_prev',$daysprev,'Days previous','How many days previous to publication (up to 7) does this job print?');
        make_date('recstartdate',date("Y-m-d"),'Recurring Starts','Date that the recurrences begin');
        print "<div class='label'>Recurring Ends</div><div class='input'><small>Date that recurrences end. If unchecked, then continue until disabled.</small><br />\n";
        print input_checkbox('enddatecheck',$enddatechecked).' Check if this recurring job ends after a specified date';
        print input_date('recenddate',date("Y-m-d",strtotime("+1 year")));
        print "</div><div class='clear'></div>\n";
        make_checkbox('buildnow',1,'Build now','After saving, create future recurring jobs immediately');
        make_hidden('recurringid',$recurringid);
        print "</fieldset>\n";
       
        
     print "</div>\n";
     print "<div id='jnotes'>\n";
        make_textarea('jobmessage',$jobmessage,'Message','Message to display to press crew',80,5,false);
        make_textarea('notes_press',$pressnotes,'Press Notes','General job notes for the press',80.5,false);
     print "</div>\n";   
    

print "</div>\n";
print "</form>\n";
    
    
function show_color($jobid)
{
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $layoutid=$dbJob['data']['layout_id'];
    
    $fullcolors=array('k'=>"K",'c'=>"Full Color",'s'=>"K/Spot");
    $spotcolors=array('k'=>"K",'s'=>"K/Spot");
    
    $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
    $dbJSections=dbselectsingle($sql);
    if ($dbJSections['numrows']>0)
    {
        $sections=$dbJSections['data'];
        $section1_name=$sections['section1_name'];
        $section1_code=$sections['section1_code'];
        $section1_lowpage=$sections['section1_lowpage'];
        $section1_highpage=$sections['section1_highpage'];
        $section1_doubletruck=$sections['section1_doubletruck'];
        $section1_producttype=$sections['section1_producttype'];
        $section1_leadtype=$sections['section1_leadtype'];
        $need_1=$sections['section1_used'];
        
        $section2_name=$sections['section2_name'];
        $section2_code=$sections['section2_code'];
        $section2_lowpage=$sections['section2_lowpage'];
        $section2_highpage=$sections['section2_highpage'];
        $section2_doubletruck=$sections['section2_doubletruck'];
        $section2_producttype=$sections['section2_producttype'];
        $section2_leadtype=$sections['section2_leadtype'];
        $need_2=$sections['section2_used'];
        
        $section3_name=$sections['section3_name'];
        $section3_code=$sections['section3_code'];
        $section3_lowpage=$sections['section3_lowpage'];
        $section3_highpage=$sections['section3_highpage'];
        $section3_doubletruck=$sections['section3_doubletruck'];
        $section3_producttype=$sections['section3_producttype'];
        $section3_leadtype=$sections['section3_leadtype'];
        $need_3=$sections['section3_used'];
        
        
        
        //ok, now lets find press layout that may match
        //this may take awhile if the number of press layouts grows large
        $layoutsql="SELECT * FROM layout WHERE id=$layoutid";
        $dbLayouts=dbselectsingle($layoutsql);
        if ($dbLayouts['numrows']>0)
        {
            //$colors=$GLOBALS['colorconfigs'];
            //if ($need_1){print "Need section 1<br>\n";}
            //if ($need_2){print "Need section 2<br>\n";}
            //if ($need_3){print "Need section 3<br>\n";}
            $name=$dbLayouts['data']['layout_name'];
            $notes=$dbLayouts['data']['layout_notes'];
            //ok grab sections corresponding to this layout
            print "<div style='border: 1px solid black;padding:2px;margin-bottom:10px;'>\n";
            print "<div style='margin-bottom:6px;padding:2px;background-color:black;color:white;font-weight:bold;font-size:14px;'>\n";
            print "Chosen layout: $name - $notes<br>";
            print "</div>\n";
            for($i=1;$i<=3;$i++)
            {
               switch($i)
               {
                   case 1:
                    $need=$need_1;
                    $name=$section1_name;
                    $code=$section1_code;
                    
                   break;
                   case 2:
                    $need=$need_2;
                    $name=$section2_name;
                    $code=$section2_code;
                   break;
                   case 3:
                    $need=$need_3;
                    $name=$section3_name;
                    $code=$section3_code;
                   break;
                   
               }
               if ($need)
               {
                   print "<div id='sectionColor_$i'>\n";
                   print "<a class='button' onclick='setAllPageColor(\"black\",$i);'>Set all to B/W</a> ";
                   print "<a class='button' onclick='setAllPageColor(\"color\",$i);'>Set all to Color</a><br />";
                   print "<div id='section1_$lid'>\n";
                   print "<b>Section $i: $name - $code</b><br>\n";
                   $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='$code' ORDER BY page_number ASC";
                   $dbPages=dbselectmulti($sql);
                   if ($dbPages['numrows']>0)
                   {
                        foreach($dbPages['data'] as $page)
                        {
                            print "<div style='width:80px;height:60px;float:left;border:thin solid black;padding:3px;'>\n";
                            print "<p style='margin:0;text-align:center;font-weight:bold;font-size:14px'>$page[page_number]<br>\n";
                            if ($page['possiblecolor']==1)
                            {
                                if($page['color']==1)
                                {
                                    $pcolor='c';
                                }elseif($page['color']==0 && $page['spot']==1)
                                {
                                    $pcolor='s';
                                } else {
                                    $pcolor='k';
                                }
                                print input_select('pageid_'.$page['id'],$fullcolors[$pcolor],$fullcolors);
                            } elseif($page['possiblespot']==1) {
                                switch($page['spot'])
                                {
                                    case 0:
                                    $pcolor='k';
                                    break;
                                    case 1:
                                    $pcolor='s';
                                    break;
                                }
                                print input_select('pageid_'.$page['id'],$spotcolors[$pcolor],$spotcolors);
                            } else {
                                make_hidden('pageid_'.$page['id'],'0');
                                print 'k';
                            }
                            print "</p>\n";
                            print "</div>\n";
                        }
                        print "<div class='clear'></div>\n";
                    } else {
                        print "No pages defined for this section.";
                    }
                    print "</div>\n";
               print "</div>\n";
             
               }
               
            }
            
            print "</div>\n";
            
        } else {
            print "Sorry, you need to specify a press layout first.";
        }    
    }
}    
    
    
dbclose();

?>

<script type="text/javascript">
$(document).ready(function(){
    $("input:button, input:submit, a.submit, a.button, button, #submit").button();
    $( '#tabs' ).tabs();
    if ($("#pub_id").val()==0)
    {
        //$("input[type=submit]").attr("disabled","disabled");
        $( "input[type=submit]" ).button( "option", "disabled", true );
    }
    $("#pub_id").change(function(){
        $("#insertpub_id").val($("#pub_id").val());
        if ($("#pub_id").val()!=0)
        {
            //$("input[type=submit]").removeAttr("disabled");
            $( "input[type=submit]" ).button( "option", "disabled", false );
        } else {
            //$("input[type=submit]").attr("disabled","disabled");
            $( "input[type=submit]" ).button( "option", "disabled", true );
        } 
    })
    
    $("#pub_id").selectChain({
        target: $("#run_id"),
        type: "post",
        url: "includes/ajax_handlers/fetchRuns.php",
        data: { ajax: true }
    });
})

function setAllPageColor(stat,section)
{
    if(stat=='black')
    {
        $('#sectionColor_'+section+' select').val('k');
    }else if(stat=='color')
    {
        $('#sectionColor_'+section+' select').val('c');
    }
}


function setLayout()
{
    var pubid=$('#pub_id').val();
    var runid=$('#run_id').val();
    var pubdate=$('#pubdate').val();
    var jobid=$('#job_id').val();
    var layoutid=$('#layout_id').val();
    
    var vs1need=$('#section1_enable').prop( "checked" )?1:0;
    var vs2need=$('#section2_enable').prop( "checked" )?1:0;
    var vs3need=$('#section3_enable').prop( "checked" )?1:0;
    var vs1letter=$('#section1_letter').val();
    var vs1name=$('#section1_name').val();
    var vs1low=$('#section1_low').val();
    var vs1high=$('#section1_high').val();
    var vs1format=$('#section1_format').val();
    var vs1lead=$('#section1_lead').val();
    var vs1gate=$('#section1_gatefold').prop( "checked" )?1:0;
    var vs1double=$('#section1_doubletruck').prop( "checked" )?1:0;
    var vs2letter=$('#section2_letter').val();
    var vs2name=$('#section2_name').val();
    var vs2low=$('#section2_low').val();
    var vs2high=$('#section2_high').val();
    var vs2format=$('#section2_format').val();
    var vs2lead=$('#section2_lead').val();
    var vs2gate=$('#section2_gatefold').prop( "checked" )?1:0;
    var vs2double=$('#section2_doubletruck').prop( "checked" )?1:0;
    var vs3letter=$('#section3_letter').val();
    var vs3name=$('#section3_name').val();
    var vs3low=$('#section3_low').val();
    var vs3high=$('#section3_high').val();
    var vs3format=$('#section3_format').val();
    var vs3lead=$('#section3_lead').val();
    var vs3gate=$('#section3_gatefold').prop( "checked" )?1:0;
    var vs3double=$('#section3_doubletruck').prop( "checked" )?1:0;

    
    var $dialog = $('<div id="jConfirm"></div>')
    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Proceeding will remove all existing pages and plates and create new ones based on the currently selected layout. Are you sure you want to continue?</p>')
    .dialog({
        autoOpen: true,
        title: 'Are you sure you want to continue?',
        modal: true,
        buttons: {
            Cancel: function() {
                $( this ).dialog( "close" );
                return false;
            },
            'Set Layout': function() {
                $( this ).dialog( "close" );
                $.ajax({
                  url: "includes/ajax_handlers/pressPopupColor.php",
                  type: "POST",
                  data: ({pubid:pubid,runid:runid,pubdate:pubdate,layoutid:layoutid,jobid:jobid,s1need:vs1need,s1low:vs1low,s1high:vs1high,s1format:vs1format,s1lead:vs1lead,s1double:vs1double,s2need:vs2need,s2low:vs2low,s2high:vs2high,s2format:vs2format,s2lead:vs2lead,s2double:vs2double,s3need:vs3need,s3low:vs3low,s3high:vs3high,s3format:vs3format,s3lead:vs3lead,s3double:vs3double,s1letter:vs1letter,s1name:vs1name,s2letter:vs2letter,s2name:vs2name,s3letter:vs3letter,s3name:vs3name,s1gate:vs1gate,s2gate:vs2gate,s3gate:vs3gate}),
                  dataType: "json",
                  success: function(response){
                     if(response.status=='success')
                     {
                      $('#saveLayoutResponse').html("Layout has been saved. Plates and pages have been created. You may now set color.");
                     $('#colorSettingArea').html(response.html);
                     $("input:button, input:submit, a.submit, a.button, button, #submit").button();
                     } else {
                        $('#saveLayoutResponse').html("An error occurrec while creating the pages and plates."); 
                     }
                     
                  }
               });
            }
            
        },
        open: function() {
            $('.ui-dialog-buttonpane > button:last').focus();
        }
   
    });
    return false;
}

function getLayouts()
{
    //need to build a url based on the selection information provided
    var tosend='';
    var section1=document.getElementById('section1_enable');
    var section2=document.getElementById('section2_enable');
    var section3=document.getElementById('section3_enable');
    if (section1.checked)
    {
        var vs1need=1;
    } else {
        var vs1need=0;
    }
    if (section2.checked)
    {
        var vs2need=1;
    } else {
        var vs2need=0;
    }
    if (section3.checked)
    {
        var vs3need=1;
    } else {
        var vs3need=0;
    }
    var vs1low=document.getElementById('section1_low').value;
    var vs1high=document.getElementById('section1_high').value;
    var vs1format=document.getElementById('section1_format').value;
    var vs1lead=document.getElementById('section1_lead').value;
    var vs1double=document.getElementById('section1_doubletruck').checked?1:0;
    var vs2low=document.getElementById('section2_low').value;
    var vs2high=document.getElementById('section2_high').value;
    var vs2format=document.getElementById('section2_format').value;
    var vs2lead=document.getElementById('section2_lead').value;
    var vs2double=document.getElementById('section2_doubletruck').checked?1:0;
    var vs3low=document.getElementById('section3_low').value;
    var vs3high=document.getElementById('section3_high').value;
    var vs3format=document.getElementById('section3_format').value;
    var vs3lead=document.getElementById('section3_lead').value;
    var vs3double=document.getElementById('section3_doubletruck').checked?1:0;
    
    $.ajax({
      url: 'includes/ajax_handlers/fetchMatchingLayouts.php',
      type: 'get',
      dataType: 'json',
      data: ({s1need:vs1need,s1low:vs1low,s1high:vs1high,s1format:vs1format,s1lead:vs1lead,s1double:vs1double,s2need:vs2need,s2low:vs2low,s2high:vs2high,s2format:vs2format,s2lead:vs2lead,s2double:vs2double,s3need:vs3need,s3low:vs3low,s3high:vs3high,s3format:vs3format,s3lead:vs3lead,s3double:vs3double}),
      success: function(j)
      {
          var options = '';
          for (var i = 0; i < j.length; i++) {
              options += '<option value="' + j[i].id + '">' + j[i].label + '</option>';
          }
          $("#layouts").html(options);  
      },
      error:function (xhr, ajaxOptions, thrownError){
        alert(xhr.status);
        alert(thrownError);
      }
    });
}

function getPressDiagram()
{
    if($('#layouts').val()!=0)
    {
        $.ajax({
          url: "includes/layoutGenerator.php",
          type: "GET",
          data: ({layoutid:$('#layouts').val(),mode:'inc',display:true,save:false}),
          dataType: "html",
          success: function(response){
             $('#layout_id').val($('#layouts').val());
             $('#layout_preview').html(response);
             
          },
           error:function (xhr, ajaxOptions, thrownError){
            alert(xhr.status);
            alert(thrownError);
          } 
       });
   }
}
</script>
</body>
</html>