<?php
  //edit job schedule handler
  error_reporting(E_LL);
  session_start();
  include("includes/functions_db.php");
  include("includes/functions_formtools.php");
  include("includes/config.php");
  include("includes/functions_common.php");
  
if($_POST)
{
    save_job();  
} else {
    die ("You have reached this form by accident.<br /><a href='default.php'>Click here to return</a>");
}
 
function save_job()
{
    global $siteID, $sizes;
    //print_r($_POST);
    $jobid=$_POST['job_id'];
    $pressid=$_POST['pressid'];
    $insertpubid=$_POST['insertpub_id'];
    $pubid=$_POST['pub_id'];
    $runid=$_POST['run_id'];
    $layoutid=$_POST['layout_id'];
    if($layoutid==''){$layoutid=0;}
    $runspecial=addslashes($_POST['run_special']);
    $papertype=$_POST['newsprint'];
    $papertypecover=$_POST['papertype_cover'];
    $pubdate=$_POST['pubdate'];
    $drawOther=$_POST['drawOther'];
    $drawTotal=$_POST['drawTotal'];
    if ($drawOther==''){$drawOther=0;}
    $pagewidth=$sizes[$_POST['pagewidth']];
    $lap=$_POST['lap'];
    if ($_POST['trim']){$trim=1;}else{$trim=0;}
    if ($_POST['stitch']){$stitch=1;}else{$stitch=0;}
    if ($_POST['glossycover']){$glossycover=1;}else{$glossycover=0;}
    if ($_POST['glossyinside']){$glossyinside=1;}else{$glossyinside=0;}
    if ($_POST['slitter']){$slitter=1;}else{$slitter=0;}
    if ($_POST['requires_delivery']){$requiresDelivery=1;}else{$requiresDelivery=0;}
    if ($_POST['requires_addressing']){$requiresAddressing=1;}else{$requiresAddressing=0;}
    if ($_POST['requires_inserting']){$requiresInserting=1;}else{$requiresInserting=0;}
    $folderpin=$_POST['folderpin'];
    $glossydraw=$_POST['glossydraw'];
    $glossyinsidecount=$_POST['glossyinsidecount'];
    if ($glossyinsidecount==''){$glossyinsidecount=0;}
    $coverdue=$_POST['coverdue'];
    $coverouput=$_POST['coveroutput'];
    $coverprint=$_POST['coverprint'];
    $pagerelease=$_POST['pagerelease'];
    $pagerip=$_POST['pagerip'];
    $deliverynotes=addslashes($_POST['notes_delivery']);
    $binderynotes=addslashes($_POST['notes_bindery']);
    $pressnotes=addslashes($_POST['notes_press']);
    $insertingnotes=addslashes($_POST['notes_inserting']);
    $jobmessage=addslashes($_POST['jobmessage']);
    $binderystart=$_POST['bindery_start'];
    $binderydue=$_POST['bindery_due'];
    $folder=$_POST['folder'];
    $jobType=$_POST['job_type'];
    $rollSize=$_POST['rollSize'];
    if($rollSize==''){$rollSize=0;}
    //if we get a run special, we need to add it to the runs for that pub and get the runid to be used later
    if ($runspecial!='')
    {
        $productcode=addslashes($_POST['run_special_productcode']);
        $sql="INSERT INTO publications_runs (pub_id,run_name, run_productcode) VALUES ('$pubid','$runspecial', '$productcode')";
        $dbRunInsert=dbinsertquery($sql);
        $runid=$dbRunInsert['numrows'];
        print $dbRunInsert['error'];
    }
    
    
    //print_r($_POST);
    
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
    
    
    $startdatetime=$_POST['jobstartdate'];
    $runtime=$_POST['drawTotal']/($GLOBALS['pressSpeed']/60); //this should give us a number of minutes;
    $runtime=round($runtime,0);
    $runtime+=$GLOBALS['pressSetup'];
    $stopdatetime=date("Y-m-d H:i",strtotime($startdatetime." +$runtime minutes"));
    $continue=false;
    //update the regular record with the new scheduled start time
    $scheduledtime=date("Y-m-d H:i:s");
    $scheduledby=$_SESSION['cmsuser']['userid'];
    
    $notes=addslashes($_POST['notes']);
    $updatedtime=date("Y-m-d H:i:s");
    $updatedby=$_SESSION['cmsuser']['userid'];
    $sql="UPDATE jobs SET scheduled_time='$scheduledtime', scheduled_by='$scheduledby', folder='$folder', startdatetime='$startdatetime', enddatetime='$stopdatetime', updated_time='$updatedtime', updated_by='$updatedby', job_message='$jobmessage', pub_id='$pubid', run_id='$runid', insert_pub_id='$insertpubid', pub_date='$pubdate', papertype='$papertype', notes_press='$pressnotes', draw='$drawTotal', papertype_cover='$papertypecover', overrun='$drawOther', lap='$lap', trim='$trim', stitch='$stitch', glossy_cover='$glossycover', glossy_cover_draw='$glossydraw', glossy_insides='$glossyinside', glossy_insides_count='$glossyinsidecount', cover_date_output='$coverouput', cover_date_print='$coverprint', cover_date_due='$coverdue', page_release='$pagerelease', page_rip='$pagerip', bindery_startdate='$binderystart', bindery_duedate='$binderydue', notes_delivery='$deliverynotes', rollSize='$rollSize', notes_bindery='$binderynotes', pagewidth='$pagewidth', folder_pin='$folderpin', slitter='$slitter', requires_delivery='$requiresDelivery', requires_addressing='$requiresAddressing', requires_inserting='$requiresInserting', notes_inserting='$insertingnotes', job_type='$jobType', press_id='$pressid' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];                           
    //check for existing jobs_sections and if not present, create if necessary
    if ($error=='')
    {
        $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
        $dbSections=dbselectsingle($sql);
        if ($dbSections['numrows']>0)
        {
            $sectionid=$dbSections['data']['id'];
            //updating an existing section record
            $sql="UPDATE jobs_sections SET section1_name='$section1_name', section1_code='$section1_letter',
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
            $sql="INSERT INTO jobs_sections (job_id, section1_name, section1_code, section1_lowpage, 
            section1_highpage, section1_gatefold, section1_doubletruck, section1_producttype, section1_leadtype, 
            section2_name, section2_code, section2_lowpage, section2_highpage, 
            section2_gatefold, section2_doubletruck, section2_producttype, 
            section2_leadtype, section3_name, section3_code, section3_lowpage, 
            section3_highpage, section3_gatefold, section3_doubletruck, 
            section3_producttype, section3_leadtype, section1_overrun, section2_overrun,
             section3_overrun, section1_used, section2_used, section3_used) VALUES
            ('$jobid', '$section1_name', '$section1_letter', '$section1_low', '$section1_high', 
            '$section1_gatefold', '$section1_doubletruck', '$section1_format', '$section1_lead',
             '$section2_name', '$section2_letter', '$section2_low', '$section2_high', 
            '$section2_gatefold', '$section2_doubletruck', '$section2_format', '$section2_lead', 
            '$section3_name', '$section3_letter', '$section3_low', '$section3_high', 
            '$section3_gatefold', '$section3_doubletruck', '$section3_format', 
            '$section3_lead', '$section1_overrun', '$section2_overrun', '$section3_overrun',
            '$section1_used', '$section2_used', '$section3_used')";
            $dbInsert=dbinsertquery($sql);
            if ($dbInsert['error']!='')
            {
                $error.="<br>Section insert error<br>".$dbInsert['error'];
            }
        }   
    }
    if($error!='')
    {
        print $error;
    }
    
    
    
    
    /***********************************************************************
    * THIS SECTION IS TO BUILD AN INSERT RUN FOR THE APPROPRIATE
    * TYPE OF PUBLICATION AND RUN 
    ************************************************************************/
    if($_POST['createinsert'] || $_POST['requires_inserting'])
    {
        $createinsert=true;
    }
    printJob2Inserter($jobid,$createinsert);
    printJob2Delivery($jobid);
    printJob2Bindery($jobid);
    printJob2Addressing($jobid);
    
    
    //ok, we only save the layout if the $_POST[layout_id] != $job['layout_id']
    $sql="SELECT layout_id FROM jobs WHERE id=$jobid";
    $dbExisting=dbselectsingle($sql);
    if ($dbExisting['data']['layout_id']!=$_POST['layout_id'])
    {
       saveLayout($layoutid,$jobid);
    }
    
    foreach($_POST as $key=>$value)
    {
        if(substr($key,0,7)=='pageid_')
        {
            $pageid=str_replace("pageid_","",$key);
            switch($value)
            {
                case "k":
                //black page;
                $sql="UPDATE job_pages SET color=0, spot=0 WHERE id=$pageid";
                break;
                
                case "c":
                //full color
                $sql="UPDATE job_pages SET color=1, spot=0 WHERE id=$pageid";
                break;
                
                case "s":
                //spot color
                $sql="UPDATE job_pages SET color=0, spot=1 WHERE id=$pageid";
                break;
            }
            $dbUpdate=dbexecutequery($sql); 
        }
    }
    
    
    
    if($error=='' && $_POST['makerecur'])
    {
        save_recurring('insert');   
    }
    if($error=='' && $_POST['copyjob'])
    {
        duplicate_job($jobid);   
    }
    
    //clear any cached calendar files
    clearCache('presscalendar');
    
    /***********************************************************************
    ***********************************************************************/
    //die('System in test mode. Will be returned to full use in just a moment.');
    if($error!='')
    {
        print $error;
    }
    
}
 


  
  
function save_recurring($action)
{
    global $pressSetup, $pressSpeed, $siteID, $sizes;
    $recurringID=$_POST['recurringid'];
    $pubid=$_POST['pub_id'];
    $insertpubid=$_POST['insertpub_id'];
    $runid=$_POST['run_id'];
    $notes=str_replace("<br /><input type=\"hidden\" /><!--Session data--><input type=\"hidden\" />","",$_POST['notes']);
    $notes=addslashes($notes);
    $start_time=$_POST['recstart_hour'].":".$_POST['recstart_minute'];
    $daysprev=$_POST['days_prev'];
    $papertype=$_POST['papertype'];
    $draw=$_POST['drawTotal']+$_POST['drawOther'];
    $pagewidth=$sizes[$_POST['pagewidth']];
    $lap=$_POST['lap'];
    $folder=$_POST['folder'];
    $daysout=$_POST['daysout'];
    $specified=$_POST['specifieddate'];
    $frequency=$_POST['frequency'];
    $startdate=$_POST['recstartdate'];
    $enddate=$_POST['recenddate'];
    if ($_POST['active']){$active=1;}else{$active=0;}
    if ($_POST['usedraw']){$usedraw=1;}else{$usedraw=0;}
    if ($_POST['enddatechecked']){$enddatechecked=1;}else{$enddatechecked=0;}
    //calculate stop time
    $runtime=ceil($draw/($pressSpeed/60))+$pressSetup;
    $stop_time=date("H:i",strtotime($start_time." + $runtime minutes"));
    
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
    
    
    
    $sql="INSERT INTO jobs_recurring (pub_id, insert_pub_id, run_id, notes, days_of_week, papertype, lap,  folder, draw, start_time, stop_time, days_out, active, days_prev, pagewidth, site_id, 
        start_date, end_date, end_date_checked, use_draw, recur_frequency, specified_date, layout_id)
         VALUES ('$pubid','$insertpubid','$runid', '$notes', '$daysofweek', '$papertype', '$lap', '$folder', '$draw', '$start_time', '$stop_time', '$daysout', '$active', '$daysprev', '$pagewidth',
           '$siteID', '$startdate', '$enddate', '$enddatechecked', '$usedraw', '$frequency', 
           '$specified','$layoutid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $recurringID=$dbInsert['insertid'];
     //print "adding recurring job with $sql<br />\n";
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
            $sql="INSERT INTO jobs_recurring_sections (job_id, section1_name, section1_code, section1_lowpage, section1_highpage, section1_gatefold, section1_doubletruck, section1_producttype, section1_leadtype,  section2_name, section2_code, section2_lowpage, section2_highpage, 
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
                print "<br>Section insert error<br>".$dbInsert['error'];
            } else {
                $sql="UPDATE jobs_recurring SET section_id='$sectionid' WHERE id=$recurringID";
                $dbUpdate=dbexecutequery($sql);
                print($dbUpdate['error']);
            }
        }
       
       //automatically build all future jobs
        //clear existing
        $date=date("Y-m-d H:i");
        $sql="DELETE FROM jobs WHERE startdatetime>='$date' AND updated_time IS NOT Null AND recurring_id='$recurringID'";
        $dbDelete=dbexecutequery($sql);
        $sql="DELETE FROM jobs_recurring_sections WHERE job_id='$recid'";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        $sql="DELETE FROM jobs_inserter_plans WHERE pub_date>='$date' AND recurring_id='$recurringID'";
        $dbDelete=dbexecutequery($sql);
        $error.=$dbDelete['error'];
        $sql="DELETE FROM jobs_inserter_packages WHERE pub_date>='$date' AND recurring_id='$recurringID'";
        $dbDelete=dbexecutequery($sql);
        $error.=$dbDelete['error'];
        
       if($_POST['buildnow'])
       { 
            //build a bunch of jobs now
           include("cronjobs/recurringPressJobs.php");
           init_recurringJob($recurringID); 
       }
       if($error!='')
       {
           print $error;
       }
    } else {
        print $error;
    }
    
}


function duplicate_job($jobid)
{
    $copydate=$_POST['copydate'];
    $requestdate=$_POST['dup_request_date'];
    //duplicate a job completely
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    
    //we need to figure out how many days earlier the job prints than it publishes and make the same
    //modification for the new  dates
    $pubdate=$job['pub_date'];
    //this is now how many days to subtract from all day stamps
    //print "Starting a duplication job from job id $jobid.<br />\n";
    $fields='';
    $values='';
    foreach($job as $key=>$value)
    {
        if ($key=='id')
        {
            //do nothing here!
        } elseif($key=='stats_id')
        {
            $fields.="$key,";
            $values.="'0',";
        } elseif($key=='pub_date')
        {
            $fields.="$key,";
            $values.="'$copydate',";
        } elseif($key=='insert_source')
        {
            $fields.="$key,";
            $values.="'jobduplicate',";
        } elseif($key=='startdatetime')
        {
            $fields.="$key,";
            
            $values.="'jobduplicate',";
        } elseif(strpos($key,'date')>0)
        {
            if ($value!='')
            {
                $daydiff=dayDiff(strtotime($pubdate),strtotime($value));
                //see if we are working with a date/time or just date
                $existing=$value;
                if(strpos($value,' ')>0)
                {
                    //means a datetime
                    $wt=explode(" ",$value);
                    $wt=$wt[1];
                    $value=date("Y-m-d",strtotime($copydate."-$daydiff days")).' '.$wt;
                } else {
                    $value=date("Y-m-d",strtotime($copydate."-$daydiff days"));
                }
                //print "Changing a $key date from $existing to $value with diff of $daydiff<br />\n";
            }
            $fields.="$key,";
            $values.="'$value',";
        } else {
            $fields.="$key,";
            $values.="'$value',";
        }
        if ($key=='layout_id')
        {
            $layoutid=$value;
        }
    }
    $fields=substr($fields,0,strlen($fields)-1);
    $values=substr($values,0,strlen($values)-1);
    $sql="INSERT INTO jobs ($fields) VALUES ($values)";
    $dbInsertJob=dbinsertquery($sql);
    $newjobid=$dbInsertJob['insertid'];
    if ($dbInsertJob['error']=='')
    {
        //now duplicate the section record
        $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
        $dbJob=dbselectsingle($sql);
        $job=$dbJob['data'];
        $fields='';
        $values='';
        foreach($job as $key=>$value)
        {
            if ($key=='id')
            {
                //do nothing here!
            }elseif($key=='job_id')
            {
                $fields.="$key,";
                $values.="'$newjobid',";
            } else {
                $fields.="$key,";
                $values.="'$value',";
            }
        }
        $fields=substr($fields,0,strlen($fields)-1);
        $values=substr($values,0,strlen($values)-1);
        $sql="INSERT INTO jobs_sections ($fields) VALUES ($values)";
        $dbInsertSections=dbinsertquery($sql);
        
        //create the stat record
        $sql="INSERT INTO job_stats (job_id, added_by) VALUES ('$newjobid', 'jobPressPopup_handler.php - duplicating job, line 912')";
        $dbStat=dbinsertquery($sql);
        $statid=$dbStat['insertid'];
        $sql="UPDATE jobs SET stat_id='$statid' WHERE id=$newjobid";
        $dbUpdate=dbexecutequery($sql);
        
        //now pages & plates
        saveLayout($layoutid,$newjobid);
    } else {
        print $dbInsertJob['error'];
    }
}


  
  dbclose();
  ?>
  <script type='text/javascript'>
    window.opener.refreshCalendar();
    self.close();
  </script>
