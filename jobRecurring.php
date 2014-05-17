<?php
//<!--VERSION: .9 **||**-->

include("includes/mainmenu.php") ;                    
include("includes/layoutGenerator.php");

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
} 

switch ($action)
{
    case "add";
    recurring('add');
    break;
    
    case "edit";
    recurring('edit');
    break;
    
    case "delete";
    recurring('delete');
    break;
    
    case "Save Recurring Job";
    save_recurring('insert');
    break;
    
    case "Update Recurring Job";
    save_recurring('update');
    break;
    
    case "clear";
    $recid=intval($_GET['recurringid']);
    clear_future($recid,false);
    break;
    
    case "clearall";
    $recid=intval($_GET['recurringid']);
    clear_future($recid,true);
    break;
    
    default:
    recurring('list');
    break;
}

function recurring($action)
{
    global $pubs, $broadsheetPageWidth,$producttypes, $papertypes, $leadtypes, $laps, $daysofweek, $defaultNewsprintID, $presses, $folders, $defaultLap, $siteID, $defaultFolder, $recurFrequencies, $sizes, $folderpins, $jobTypes;
    $specDates=array("Please Choose",1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
    $runs=array();
    $runs[0]='Please choose';
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Recurring Job";
            $starttime="12:00";
            $days=array();
            $papertype=$defaultNewsprintID;
            $lap=$defaultLap;
            $daysprev=1;
            $folder=$defaultFolder;
            $startdate=date("Y-m-d");
            $enddate=date("Y-m-d",strtotime("+1 year"));
            $recurFreq=0;
            $specified=0;
            $usedraw=0;
            $daysout=0;
            $draw=0;
            $enddatechecked=0;
            $pagewidth=$broadsheetPageWidth;
            $sectionid=0;
            $pubid=0;
            $insertpubid=0;
            $quarterfold=0;
            $rollSize=0;
            $slitter=$GLOBALS['pressDefaultSlitter'];
            $folderpin=$GLOBALS['pressDefaultFolderPin'];
            $pressid=$GLOBALS['defaultPressID'];
            $jobType='newspaper';
        } else {
            $button="Update Recurring Job";
            $recurringid=$_GET['recurringid'];
            $sql="SELECT * FROM jobs_recurring WHERE id=$recurringid";
            $dbJob=dbselectsingle($sql);
            $job=$dbJob['data'];
            $pubid=$job['pub_id'];
            $insertpubid=$job['insert_pub_id'];
            if($insertpubid==0){$insertpubid=$pubid;}
            if ($pubid!=0)
            {
                //means we have an existing pub, need to pull in runs
                $sql="SELECT id, run_name FROM publications_runs WHERE pub_id=$pubid";
                $dbRuns=dbselectmulti($sql);
                if ($dbRuns['numrows']>0)
                {
                    foreach($dbRuns['data'] as $run)
                    {
                        $runs[$run['id']]=$run['run_name'];
                    }
                }
            }
            $pressid=$job['press_id'];
            $runid=$job['run_id'];
            $days=explode("|",$job['days_of_week']);
            $daysout=$job['days_out'];
            $papertype=$job['papertype'];
            $pagewidth=$job['pagewidth'];
            $lap=$job['lap'];
            $sectionid=$job['section_id'];
            $layoutid=$job['layout_id'];
            $folder=$job['folder'];
            $notes=$job['notes'];
            $draw=$job['draw'];
            $daysprev=$job['days_prev'];
            $rollSize=$job['rollSize'];
            $jobType=$job['job_type'];
            $starttime=date("H:i",strtotime($job['start_time']));
            if ($job['specified_date']=='')
            {
                $specified=0;    
            } else {
                $specified=$job['specified_date'];
            }
            $recurFreq=$job['recur_frequency'];
            if ($job['start_date']=='')
            {
                $startdate=date("Y-m-d");    
            } else {
                $startdate=$job['start_date'];
            }
            if ($job['end_date']=='')
            {
                 $enddate=date("Y-m-d");    
            } else {
                $enddate=$job['end_date'];
            }
            $usedraw=$job['use_draw'];
            $enddatechecked=$job['end_date_checked'];
            $slitter=$job['slitter'];
            $quarterfold=$job['quarterfold'];
            $folderpin=$job['folder_pin'];
        }
        
        
        //get section information
        $sql="SELECT * FROM jobs_recurring_sections WHERE id=$sectionid";
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
        print "<form method=post>\n";
        
        print "<div id='tabs'>\n"; //begins wrapper for tabbed content
        
        print "<ul id='jobTabs'>\n";
        print "    <li><a href='#jsetup'>Basic Information</a></li>\n";
        print "    <li><a href='#jsections'>Sections</a></li>\n";
        print "    <li><a href='#jlayout'>Layout</a></li>\n";
        print "</ul>\n";
        
        
        
        
        
        print "<div id='jsetup'>\n";

        
        make_checkbox('active',$job['active'],'Activate','Check to make this job active');
        if(count($presses)>0 && !array_key_exists(0,$presses))
        {
            make_select('pressid',$presses[$pressid],$presses,'Select Press');
        } else {
            make_hidden('pressid',$pressid);
        }
        make_select('pub_id',$pubs[$pubid],$pubs,'Publication');
        make_select('run_id',$runs[$runid],$runs,'Run');
        print '
            <script type="text/javascript">
            $("#pub_id").selectChain({
                target: $("#run_id"),
                type: "post",
                url: "includes/ajax_handlers/fetchRuns.php",
                data: { ajax: true }
            });
             $("#pub_id").change(function(){
                $("#insertpub_id").val($("#pub_id").val());
            })
            </script>
            ';
        make_select('insertpub_id',$pubs[$insertpubid],$pubs,'Insert Publication','If this job inserts back into a different publication, please select it here.');
        make_select('jobtype',$jobTypes[$jobType],$jobTypes,'Job type');
        make_select('papertype',$papertypes[$papertype],$papertypes,'Paper type');
        make_select('papertype_cover',$papertypes[$papertypecover],$papertypes,'Cover paper',"<span style='color:red;font-weight:bold;'>If the outside or one web is on a different paperstock, please select it here.</span>");
        make_select('pagewidth',$pagewidth,$sizes,'Size of a full sheet (newspaper broadsheet page equivalent)');
        make_select('rollSize',$GLOBALS['sizes'][$rollSize],$GLOBALS['sizes'],'Default Roll Width','Size of a full roll for this job.');
        
        make_select('folder',$folders[$folder],$folders,'Folder');
        make_select('lap',$laps[$lap],$laps,'Lap');
        make_select('folderpin',$folderpins[$folderPin],$folderpins,'Type of folder setup');
        make_checkbox('quarterfold',$quarterfold,'Quarterfolding','Check if this is quarterfolded');
        make_checkbox('slitter',$slitter,'Slitter','Check to set slitter to on');
        make_time('start_time',$starttime,'Start Time','Job start time on selected days');
        make_number('daysout',$daysout,'Days out','How far out to create jobs from current day? This is the continual padding between current day and future.');
        make_number('draw',$draw,'Draw','Average draw. Used to calculate run length.');
        make_checkbox('usedraw',$usedraw,'Use draw','Build recurring jobs with this draw, not just to estimate run length');
        make_select('frequency',$recurFrequencies[$recurFreq],$recurFrequencies,'Recurring Frequency','Specify how regular the recurrence is,');
        make_select('specifieddate',$specDates[$specified],$specDates,'Specified Date','Recurrence happens only on specifed date of the month');
        print "<div class='label'>Publication Days</div><div class='input'>\n";
        print "Select the actual PUBLICATION days here, the days previous above controls the print date.<br />\n";
        foreach($daysofweek as $did=>$dname)
        {
            if (in_array($did,$days)){$checked="checked";}
            print "<input type='checkbox' name='day_$did' $checked> $dname<br />\n";
            $checked="";        
        }
        print "</div><div class='clear'></div>\n";
        make_number('days_prev',$daysprev,'Days previous','How many days previous to publication (up to 7) does this job print?');
        make_date('startdate',$startdate,'Recurring Starts','Date that the recurrences begin');
        print "<div class='label'>Recurring Ends</div><div class='input'><small>Date that recurrences end. If unchecked, then continue until disabled.</small><br />\n";
        print input_checkbox('enddatecheck',$enddatechecked).' Check if this recurring job ends after a specified date';
        print input_date('enddate',$enddate);
        print "</div><div class='clear'></div>\n";
        make_textarea('notes',$notes,'Notes','',80,'15');
        make_checkbox('buildnow',1,'Build now','After saving, create future recurring jobs immediately');
        print "</div>\n";
        
        
        
        print "<div id='jsections' >\n";
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
    
    
        print "<div id='jlayout'>\n";
            print "<fieldset>\n";
            print "<legend>Layout Configuration</legend>\n";
            print "<div style='float:left;width: 300px;'>\n";
            print "<input type='button' class='submit' value='Find matching layouts' onClick='getLayouts();'><br />\n";
            print "<p style='font-size:14px;font-weight:bold;'>Possible Layouts:</p>\n";
            print input_select('layouts','Get list',array('Get list'),'','getPressDiagram();');
            print "<input type='button' style='margin-top:150px;' class='submit ' value='Remove Existing Layout' onClick='removeLayout(\"recurring\");'><br />\n";
            print "<span style='color:green;font-weight:bold;' id='laymessage'></span><br />";
            print "<span id='saveLayoutResponse' name='saveLayoutResponse' style='width:250px;'>";
            print "</span>\n";
            print "</div>\n";
            print "<div id='layout_preview' style='float:left;width: 230px;overflow-y:scroll;'>";
                if ($job['layout_id']!=0)
                    {
                        configure($job['layout_id'],true,false,true);
                    }
            print "</div>\n";
            print "</fieldset>\n";
        print "</div>\n";
    
    
        
        
        print "</div>\n";
        make_hidden('recurringid',$recurringid);
        make_hidden('layout_id',$layoutid);
        make_submit('submit',$button);
        print "</form>\n";
        print "</div>\n";
        ?>
        <script type='text/javascript'>
   $(function() {
        $( '#tabs' ).tabs();
    });
    </script>
    <?php
    } elseif ($action=='delete')
    {
        $id=intval($_GET['recurringid']);
        $sql="DELETE FROM jobs_recurring WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the recurrence. '.$error,'error');
        } else {
            setUserMessage('The recurrence has been successfully deleted','success');
        }
        redirect("?action=list");
    } else {
        $sql="SELECT A.*, B.pub_name, C.run_name FROM jobs_recurring A, publications B, publications_runs C WHERE A.site_id=$siteID AND A.pub_id=B.id AND A.run_id=C.id";
        $dbJobs=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new recurring job</a>","Publication,Run Name,Recurring Days",8);
        if ($dbJobs['numrows']>0)
        {
            foreach($dbJobs['data'] as $job)
            {
                $pubname=$job['pub_name'];
                $runname=$job['run_name'];
                $id=$job['id'];
                $days=str_replace("|",", ",$job['days_of_week']);
                $days=str_replace("0","Sunday",$days);
                $days=str_replace("1","Monday",$days);
                $days=str_replace("2","Tuesday",$days);
                $days=str_replace("3","Wednesday",$days);
                $days=str_replace("4","Thursday",$days);
                $days=str_replace("5","Friday",$days);
                $days=str_replace("6","Saturday",$days);
                print "<tr>";
                print "<td>$pubname</td>\n";
                print "<td>$runname</td>\n";
                print "<td>$days</td>\n";
                print "<td><a href='?action=edit&recurringid=$id'>Edit</a></td>";
                print "<td><a href='?action=delete&recurringid=$id' class='delete'>Delete</a></td>";
                print "<td><a href='cronjobs/recurringPressJobs.php?mode=manual&specific=$id' >Build jobs</a></td>";
                print "<td><a href='?action=clear&recurringid=$id' class='delete'>Clear all future jobs (non-edited)</a></td>";
                print "<td><a href='?action=clearall&recurringid=$id' class='delete'>Clear every future job</a></td>";
                print "</tr>\n";
            }    
        }
        tableEnd($dbJobs);
        
    }
    
}

function save_recurring($action)
{
    global $pressSetup, $pressSpeed, $siteID, $sizes;
    $recurringID=$_POST['recurringid'];
    $pubid=$_POST['pub_id'];
    $pressid=$_POST['pressid'];
    $insertpubid=$_POST['insertpub_id'];
    $runid=$_POST['run_id'];
    $starttime=$_POST['start_time'];
    $daysprev=$_POST['days_prev'];
    $papertype=$_POST['papertype'];
    $papertypecover=$_POST['papertype_cover'];
    $draw=$_POST['draw'];
    $pagewidth=$sizes[$_POST['pagewidth']];
    $rollSize=$sizes[$_POST['rollSize']];
    $lap=$_POST['lap'];
    $folder=$_POST['folder'];
    $daysout=$_POST['daysout'];
    $specified=$_POST['specifieddate'];
    $frequency=$_POST['frequency'];
    $startdate=$_POST['startdate'];
    $enddate=$_POST['enddate'];
    $folderpin=$_POST['folderpin'];
    $jobtype=$_POST['jobtype'];
    if ($_POST['active']){$active=1;}else{$active=0;}
    if ($_POST['usedraw']){$usedraw=1;}else{$usedraw=0;}
    if ($_POST['enddatechecked']){$enddatechecked=1;}else{$enddatechecked=0;}
    if ($_POST['quarterfold']){$quarterfold=1;}else{$quarterfold=0;}
    if ($_POST['slitter']){$slitter=1;}else{$slitter=0;}
    $notes=addslashes($_POST['notes']);
    //calculate stop time
    $runtime=ceil($draw/($pressSpeed/60))+$pressSetup;
    $stoptime=date("H:i",strtotime($starttime." + $runtime minutes"));
    
    $daysofweek="";
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,4)=="day_")
        {
            $daysofweek.=str_replace("day_","",$key)."|";
        }
    }
    $daysofweek=substr($daysofweek,0,strlen($daysofweek)-1);
    
    
    
    if ($_POST['section1_enable'])
    {
        $section1_used=1;
        $section1_overrun=$_POST['section1_overrun'];
        $section1_format=$_POST['section1_format'];
        $section1_lead=$_POST['section1_lead'];
        $section1_low=$_POST['section1_low'];
        $section1_high=$_POST['section1_high'];
        $section1_name=addslashes($_POST['section1_name']);
        $section1_letter=addslashes($_POST['section1_letter']);
        if($_POST['section1_doubletruck']){$section1_doubletruck=1;}else{$section1_doubletruck=0;}
        if($_POST['section1_gatefold']){$section1_gatefold=1;}else{$section1_gatefold=0;}
    } else {
        $section1_used=0;
        $section1_overrun=0;
        $section1_format=0;
        $section1_low=0;
        $section1_high=0;
        $section1_name='';
        $section1_letter='';
        $section1_doubletruck=0;
        $section1_gatefold=0;
        $section1_lead=0;
    }
    $tpages=$section1_high-$section1_low;
    if ($_POST['section2_enable'])
    {
        $section2_used=1;
        $section2_overrun=$_POST['section2_overrun'];
        $section2_format=$_POST['section2_format'];
        $section2_lead=$_POST['section2_lead'];
        $section2_low=$_POST['section2_low'];
        $section2_high=$_POST['section2_high'];
        $section2_name=addslashes($_POST['section2_name']);
        $section2_letter=addslashes($_POST['section2_letter']);
        if($_POST['section2_doubletruck']){$section2_doubletruck=1;}else{$section2_doubletruck=0;}
        if($_POST['section2_gatefold']){$section2_gatefold=1;}else{$section2_gatefold=0;}
    } else {
        $section2_used=0;
        $section2_overrun=0;
        $section2_format=0;
        $section2_low=0;
        $section2_high=0;
        $section2_name='';
        $section2_letter='';
        $section2_doubletruck=0;
        $section2_gatefold=0;
        $section2_lead=0;
    }
    $tpages+=$section2_high-$section1_2ow;
    if ($_POST['section3_enable'])
    {
        $section3_used=1;
        $section3_overrun=$_POST['section3_overrun'];
        $section3_format=$_POST['section3_format'];
        $section3_lead=$_POST['section3_lead'];
        $section3_low=$_POST['section3_low'];
        $section3_high=$_POST['section3_high'];
        $section3_name=addslashes($_POST['section3_name']);
        $section3_letter=addslashes($_POST['section3_letter']);
        if($_POST['section3_doubletruck']){$section3_doubletruck=1;}else{$section3_doubletruck=0;}
        if($_POST['section3_gatefold']){$section3_gatefold=1;}else{$section3_gatefold=0;}
    } else {
        $section3_used=0;
        $section3_overrun=0;
        $section3_format=0;
        $section3_low=0;
        $section3_high=0;
        $section3_name='';
        $section3_letter='';
        $section3_doubletruck=0;
        $section3_gatefold=0;
        $section3_lead=0;
    }
    switch ($section1_format)
    {
        case 0:
        if ($section1_high>0)
        {
            $tpages+=$section1_high-$section1_low+1;
        }
        break;
        case 1:
        if ($section1_high>0)
        {
            $tpages+=($section1_high-$section1_low+1)/2;
        }
        break;
        case 2:
        if ($section1_high>0)
        {
            $tpages+=($section1_high-$section1_low+1)/2;
        }
        break;
        case 3:
        if ($section1_high>0)
        {
            $tpages+=($section1_high-$section1_low+1)/4;
        }
        break;
    }
    switch ($section2_format)
    {
        case 0:
        if ($section2_high>0)
        {
            $tpages+=$section2_high-$section2_low+1;
        }
        break;
        case 1:
        if ($section2_high>0)
        {
            $tpages+=($section2_high-$section2_low+1)/2;
        }
        break;
        case 2:
        if ($section2_high>0)
        {
            $tpages+=($section2_high-$section2_low+1)/2;
        }
        break;
        case 3:
        if ($section2_high>0)
        {
            $tpages+=($section2_high-$section2_low+1)/4;
        }
        break;
    }
    switch ($section3_format)
    {
        case 0:
        if ($section3_high>0)
        {
            $tpages+=$section3_high-$section3_low+1;
        }
        break;
        case 1:
        if ($section3_high>0)
        {
            $tpages+=($section3_high-$section3_low+1)/2;
        }
        break;
        case 2:
        if ($section3_high>0)
        {
            $tpages+=($section3_high-$section3_low+1)/2;
        }
        break;
        case 3:
        if ($section3_high>0)
        {
            $tpages+=($section3_high-$section3_low+1)/4;
        }
        break;
    }
    $sectionid=$_POST['section_id'];
    $layoutid=$_POST['layout_id'];
    if($layoutid==''){$layoutid=0;}
    
    
    
    if ($action=='insert')
    {
        $sql="INSERT INTO jobs_recurring (pub_id, insert_pub_id, run_id, notes, days_of_week, papertype, lap, folder, draw, start_time, stop_time, days_out, active, days_prev, pagewidth, site_id,start_date, end_date, end_date_checked, use_draw,recur_frequency, specified_date, layout_id, papertype_cover, slitter, folder_pin, job_type, quarterfold, press_id)
         VALUES ('$pubid','$insertpubid', '$runid', '$notes', '$daysofweek', '$papertype', '$lap', '$folder', '$draw', '$starttime','$stoptime', '$daysout', '$active', '$daysprev', '$pagewidth', '$siteID', '$startdate', '$enddate', '$enddatechecked', '$usedraw', '$frequency', '$specified','$layoutid', '$papertypecover', '$slitter', '$folderpin', '$jobtype', '$quarterfold', '$pressid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $recurringID=$dbInsert['insertid'];
    } else {
        $sql="UPDATE jobs_recurring SET pub_id='$pubid', insert_pub_id='$insertpubid', run_id='$runid', notes='$notes', 
        days_of_week='$daysofweek', papertype='$papertype', lap='$lap', draw='$draw', start_time='$starttime', stop_time='$stoptime', quarterfold='$quarterfold',folder='$folder', days_out='$daysout', papertype_cover='$papertypecover', active='$active', layout_id='$layoutid',  pagewidth='$pagewidth', days_prev='$daysprev', recur_frequency='$frequency', start_date='$startdate', end_date='$enddate', end_date_checked='$enddatechecked', use_draw='$usedraw', specified_date='$specified', slitter='$slitter', job_type='$jobtype',  folder_pin='$folderpin', press_id='$pressid' WHERE id=$recurringID";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    if ($error=='')
    {
        $sql="SELECT * FROM jobs_recurring_sections WHERE id=$sectionid";
        $dbSections=dbselectsingle($sql);
        if ($dbSections['numrows']>0)
        {
            //updating an existing section record
            $sql="UPDATE jobs_recurring_sections SET section1_name='$section1_name', section1_code='$section1_letter',
            section1_lowpage='$section1_low', section1_highpage='$section1_high', section1_leadtype='$section1_lead', 
            section1_gatefold='$section1_gatefold', section1_doubletruck='$section1_doubletruck', 
            section1_producttype='$section1_format', section2_name='$section2_name', 
            section2_code='$section2_letter', section2_lowpage='$section2_low', 
            section2_highpage='$section2_high', section2_gatefold='$section2_gatefold', 
            section2_doubletruck='$section2_doubletruck', section2_producttype='$section2_format', section2_leadtype='$section2_lead', 
            section3_name='$section3_name', section3_code='$section3_letter',
            section3_lowpage='$section3_low', section3_highpage='$section3_high', 
            section3_gatefold='$section3_gatefold', section3_doubletruck='$section3_doubletruck', 
            section3_producttype='$section3_format', section3_leadtype='$section3_lead', section1_overrun='$section1_overrun',
            section2_overrun='$section2_overrun', section3_overrun='$section3_overrun', section1_used='$section1_used',
            section2_used='$section2_used', section3_used='$section3_used' WHERE id=$sectionid";
            $dbUpdate=dbexecutequery($sql);
            if ($dbUpdate['error']!='')
            {
                $error.="<br>Section update error<br>".$dbUpdate['error'];
            }
        } else {
            //inserting a new section record
            $sql="INSERT INTO jobs_recurring_sections (job_id, section1_name, section1_code, section1_lowpage, 
            section1_highpage, section1_gatefold, section1_doubletruck, section1_producttype, section1_leadtype, 
            section2_name, section2_code, section2_lowpage, section2_highpage, 
            section2_gatefold, section2_doubletruck, section2_producttype, 
            section2_leadtype, section3_name, section3_code, section3_lowpage, 
            section3_highpage, section3_gatefold, section3_doubletruck, 
            section3_producttype, section3_leadtype, section1_overrun, section2_overrun,
             section3_overrun, section1_used, section2_used, section3_used) VALUES
            ('$recurringID', '$section1_name', '$section1_letter', '$section1_low', '$section1_high', 
            '$section1_gatefold', '$section1_doubletruck', '$section1_format', '$section1_lead',
             '$section2_name', '$section2_letter', '$section2_low', '$section2_high', 
            '$section2_gatefold', '$section2_doubletruck', '$section2_format', '$section2_lead', 
            '$section3_name', '$section3_letter', '$section3_low', '$section3_high', 
            '$section3_gatefold', '$section3_doubletruck', '$section3_format', 
            '$section3_lead', '$section1_overrun', '$section2_overrun', '$section3_overrun',
            '$section1_used', '$section2_used', '$section3_used')";
            $dbInsert=dbinsertquery($sql);
            $sectionid=$dbInsert['insertid'];
            if ($dbInsert['error']!='')
            {
                $error.="<br>Section insert error<br>".$dbInsert['error'];
            } else {
                $sql="UPDATE jobs_recurring SET section_id='$sectionid' WHERE id=$recurringID";
                $dbUpdate=dbexecutequery($sql);
                $error.=$dbUpdate['error'];
            }
        }
       
        if ($_POST['buildnow'])
        {
            //clear existing
            $date=date("Y-m-d H:i");
            $sql="SELECT id FROM jobs WHERE startdatetime>='$date' AND recurring_id='$recurringID' AND updated_time IS NULL";
            $dbJobs=dbselectmulti($sql);
            $ids='';
            if($dbJobs['numrows']>0)
            {
                foreach($dbJobs['data'] as $job)
                {
                    $ids.=$job['id'].',';
                }
            }
            $ids=substr($ids,0,strlen($ids)-1);
            if($ids!='')
            {
             
                $sql="DELETE FROM jobs WHERE id IN ($ids)";
                $dbDelete=dbexecutequery($sql);
                $error=$dbDelete['error'];
                $sql="DELETE FROM jobs_recurring_sections WHERE job_id IN ($ids)";
                $dbDelete=dbexecutequery($sql);
                $error.=$dbDelete['error'];
                $sql="DELETE FROM jobs_inserter_plans WHERE pub_date>='$date' AND recurring_id='$recid'";
                $dbDelete=dbexecutequery($sql);
                $error.=$dbDelete['error'];
                $sql="DELETE FROM jobs_inserter_packages WHERE pub_date>='$date' AND recurring_id='$recid'";
                $dbDelete=dbexecutequery($sql);
                $error.=$dbDelete['error'];
                
            }
            //build a bunch of jobs now
            include("cronjobs/recurringPressJobs.php");
            init_recurringJob($recurringID); 
        }
        if ($error!='')
        {
            setUserMessage('There was a problem creating the recurrence. '.$error,'error');
        } else {
            setUserMessage('The recurrence has been successfully created','success');
        }
    
        redirect("?action=list");
    } else {
        setUserMessage('There was a problem creating the recurrence. '.$error,'error');
        redirect("?action=list");
    }
    
}

function clear_future($recid,$every=false)
{
    global $siteID;
    $date=date("Y-m-d H:i");
    if($every)
    {
        $sql="SELECT id FROM jobs WHERE startdatetime>='$date' AND recurring_id='$recid'";
    } else {
        $sql="SELECT id FROM jobs WHERE startdatetime>='$date' AND recurring_id='$recid' AND updated_time IS NOT NULL";
    }
    $dbJobs=dbselectmulti($sql);
    $ids='';
    if($dbJobs['numrows']>0)
    {
        foreach($dbJobs['data'] as $job)
        {
            $ids.=$job['id'].',';
        }
        $ids=substr($ids,0,strlen($ids)-1);
        if($ids!='')
        {
            $sql="DELETE FROM jobs WHERE id IN ($ids)";
            $dbDelete=dbexecutequery($sql);
            $error=$dbDelete['error'];
            $sql="DELETE FROM jobs_sections WHERE job_id IN ($ids)";
            $dbDelete=dbexecutequery($sql);
            $error.=$dbDelete['error'];
            $sql="DELETE FROM jobs_inserter_plans WHERE pub_date>='$date' AND recurring_id='$recid'";
            $dbDelete=dbexecutequery($sql);
            $error.=$dbDelete['error'];
            $sql="SELECT id FROM inserts WHERE pub_date>='$date' AND weprint_id IN ($ids)";
            $dbInserts=dbselectmulti($sql);
            if($dbInserts['numrows']>0)
            {
                $insertids='';
                foreach($dbInserts['data'] as $insert)
                {
                   $insertids.=$job['id'].',';
                }
                $insertids=substr($insertids,0,strlen($insertids)-1);
                if($insertids!='')
                {
                    $sql="DELETE FROM inserts WHERE id IN ($insertids)";
                    $dbDelete=dbexecutequery($sql);
                    $error.=$dbDelete['error'];
                    $sql="DELETE FROM inserts_schedule WHERE insert_id IN ($insertids)";
                    $dbDelete=dbexecutequery($sql);
                    $sql="DELETE FROM insert_zoning WHERE insert_id IN ($insertids)";
                    $dbDelete=dbexecutequery($sql);
                    $error.=$dbDelete['error'];
                }
            }
            $sql="DELETE FROM jobs_inserter_packages WHERE pub_date>='$date' AND recurring_id='$recid'";
            $dbDelete=dbexecutequery($sql);
            $error.=$dbDelete['error'];
        }
    }
    
    if ($error!='')
    {
        setUserMessage('There was a problem clearing the future recurrences. '.$error,'error');
    } else {
        setUserMessage('The future recurrences have been successfully deleted','success');
    }
    redirect("?action=list");
}
footer();
?>