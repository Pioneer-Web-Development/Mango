<?php
    session_start();
    if($_GET['start'])
    {
       $startOfWeek=date("Y-m-d",$_GET['start']); 
       $startOfWeek=date("Y-m-d",strtotime($startOfWeek."-1 day")); 
    } else {
        $startOfWeek='2010-11-15';
    }
    if($_GET['end'])
    {
       $endOfWeek=date("Y-m-d",$_GET['end']);
       $endOfWeek=date("Y-m-d",strtotime($endOfWeek."+1 day")); 
    } else {
        $endOfWeek='2010-11-21';
    }
    include("../functions_common.php");
    
    //we will first check to see if there is a cached copy of this file before proceeding
    $cache=checkCache('presscalendar',$startOfWeek);
    if($cache)
    {
        header('Content-type: application/json');
        echo $cache;
        die();   
    }
    
    include("../functions_db.php");
    include("../config.php");
    global $siteID, $pubids, $sizes, $papertypes, $folders;
    if(checkPermission(45,'perm'))
    {
        $sql="SELECT A.*, B.run_name, C.pub_name, C.pub_color, C.reverse_text FROM jobs A, publications_runs B, publications C WHERE A.startdatetime>='$startOfWeek' AND A.enddatetime<'$endOfWeek' AND A.run_id=B.id AND A.pub_id=C.id AND A.status<>99";
    } else {
        $sql="SELECT A.*, B.run_name, C.pub_name, C.pub_color, C.reverse_text FROM jobs A, publications_runs B, publications C WHERE A.startdatetime>='$startOfWeek' AND A.enddatetime<'$endOfWeek' AND A.pub_id IN ($pubids) AND A.run_id=B.id AND A.pub_id=C.id AND A.status<>99";
    }
    $dbSchedule=dbselectmulti($sql);
    $pubids=explode(",",$pubids);
    if($_GET['mode']=='test'){print $sql;}
    if ($dbSchedule['numrows']>0)
    {
        foreach ($dbSchedule['data'] as $schedule)
        {
            $title=$schedule['pub_name'].' '.$schedule['run_name'];
            if ($schedule['layout_id']!=0)
            {
                $jobticket="<img src='artwork/printer.png' border=0 width=18>";
            } else {
                $jobticket=""; 
            }
            $caution='';
            /*
            if ($schedule['layout_id']!=0)
            {
                $caution="<img src='artwork/icons/caution_icon.png' border=0 width=18>";
            } else {
                $caution=""; 
            }
            */
            if ($schedule['recurring_id']!=0)
            {
                $recurring="<img src='artwork/icons/repeat_icon_48.png' border=0 width=18 />";
                $recurringid=$schedule['recurring_id'];    
            } else {
                $recurring='';
                $recurringid=0;
            }
        
            $draw=$schedule['draw'];
            $jobid=$schedule['id'];
            $folder=$folders[$schedule['folder']];
            $fclass='Folder: '.$folder;
            $jid="JOB ID: $schedule[id]";
            $description="Folder: $folder<br />Draw: $draw<br />$jid";
            $fulldescription="Folder: $folder<br />$title<br>Draw: $draw<br />$jid";
            
            if ($schedule['pub_color']!='')
            {
                $backcolor=$schedule['pub_color'];
            } else {
                $backcolor="#EEEEEE";
            }
            if ($schedule['reverse_text'])
            {
                $forecolor='#FFFFFF';
            } else {
                $forecolor="#000000";
            }
            
            if($schedule['quarterfold']){$fold="Fold: quarter-fold";}else{$fold="Fold: half-fold";}
            if($schedule['stitch'] || $schedule['trim']){$stitch="Stitch &amp; Trim: Yes";}else{$stitch="Stitch &amp; Trim: No";}
            $sql="SELECT count(id) as pcount FROM job_pages WHERE version=1 AND job_id=$jobid";
            $dbPages=dbselectsingle($sql);
            $pagecount=$dbPages['data']['pcount'];
            if($pagecount==0){$tpages="Total Pages: not set";}else{$tpages="Total pages: ".$pagecount;}
            if($schedule['rollSize']!=0)
            {
              $papersize="Roll Size: ".$sizes[$schedule['rollSize']];
            } else {
              $papersize="Roll Size: not set";
            }
            if($schedule['papertype']!=0)
            {
              $papertype="Paper: ".$papertypes[$schedule['papertype']];
            } else {
              $papertype="Paper: not set";
            }
            
            //get sections and types
            $sql="SELECT * FROM jobs_sections WHERE job_id=$schedule[id]";
            $dbSections=dbselectsingle($sql);
            if($dbSections['numrows']>0)
            {
                $ptypes=array();
                $scodes=array();
                $sections=$dbSections['data'];
                for($i=1;$i<=3;$i++)
                {
                    $rawpages=0;
                    $rawcolorpages=0;
                    $rawspotpages=0;
                    if($sections['section'.$i.'_used']==1)
                    {
                        $sectionformat=$sections['section'.$i.'_producttype'];
                        $sectioncode=$sections['section'.$i.'_code'];
                        $sectioncode=str_replace("0","",$sectioncode);
                        $sectioncode=str_replace(" ","",$sectioncode);
                        switch($sectionformat)
                        {
                            case 0:
                                if(!in_array('Bdsht',$ptypes)){$scodes[]=$sectioncode.'-Bdsht';$ptypes[]='Bdsht';}
                            break;
                            
                            case 1:
                                $broadsheetpages+=$rawpages/2;
                                $broadsheetcolorpages+=$rawcolorpages/2;
                                $broadsheetspotpages+=$rawspotpages/2;
                                if(!in_array('Tab',$ptypes)){$scodes[]=$sectioncode.'-Tab';$ptypes[]='Tab';}
                            break;
                            
                            case 2:
                                $broadsheetpages+=$rawpages/2;
                                $broadsheetcolorpages+=$rawcolorpages/2;
                                $broadsheetspotpages+=$rawspotpages/2;
                                if(!in_array('Tab',$ptypes)){$scodes[]=$sectioncode.'-Tab';$ptypes[]='Tab';}
                            break;
                            
                            case 3:
                                $broadsheetpages+=$rawpages/4;
                                $broadsheetcolorpages+=$rawcolorpages/4;
                                $broadsheetspotpages+=$rawspotpages/4;
                                if(!in_array('Flexi',$ptypes)){$scodes[]=$sectioncode.'-Flexi';$ptypes[]='Flexi';}
                            break;
                        }
                    }
                    
                    
                }
                if(count($scodes)>0){
                    $scodes=trim(implode(",",$scodes),',');
                } else {
                    $scodes='None set';
                }
            }
            
            $tooltip="Folder: $folder<br />$title<br>Draw: $draw<br />Pub Date: ".date("m/d/Y",strtotime($schedule['pub_date']));
            $tooltip.="<br />$fold<br />$papertype<br /><br />$papersize<br />$stitch<br />$tpages<br />Start: ".date("m/d/Y H:i",strtotime($schedule['startdatetime']))."<br />End: ".date("m/d/Y H:i",strtotime($schedule['enddatetime']))."<br />Sections: $scodes<br />$jid";
            
            $classname="publications".$schedule['pub_id'];
            if(in_array($schedule['pub_id'],$pubids)){$mypub=true;}else{$mypub=false;}
            $jobs[]=array(
            'id' => $schedule['id'],
            'title' => $title,
            'start' => date("Y-m-d H:i",strtotime($schedule['startdatetime'])),
            'end' => date("Y-m-d H:i",strtotime($schedule['enddatetime'])),
            'allDay' => false,
            'tags' => $jobticket.$caution.$recurring,
            'description' => $description,
            'fulldetails' => $fulldescription,
            'color' => $backcolor,
            'backgroundColor' => $backcolor,
            'borderColor' => $backcolor,
            'textColor' => $forecolor,
            'folder' => $schedule['folder'],
            'recurringid' => $recurringid,
            'mypub' => $mypub,
            'tooltip' => $tooltip,
            'editable' => $mypub,
            'eventtype' => 'job',
            'surl' => "jobPressPopup.php?id=".$schedule['id']
            );
            // used to have
            //'className' => $classname,
            
        }

    }
    $sql="SELECT * FROM maintenance_scheduled WHERE equipment_type='press' AND starttime>='$startOfWeek' AND endtime<'$endOfWeek'";
    $dbSchedule=dbselectmulti($sql);
    if($_GET['mode']=='test'){print $sql;}
    if ($dbSchedule['numrows']>0)
    {
        foreach ($dbSchedule['data'] as $schedule)
        {
            $jid="Maintenance ID: $schedule[id]";
            $description="Maintenance task.";
            
            $jobs[]=array(
            'id' => $schedule['id'],
            'title' => 'Maintenance',
            'start' => date("Y-m-d H:i",strtotime($schedule['starttime'])),
            'end' => date("Y-m-d H:i",strtotime($schedule['endtime'])),
            'allDay' => false,
            'tags' => '',
            'description' => $description,
            'fulldetails' => $description,
            'color' => '#fff',
            'tooltip' => 'Maintenance task',
            'backgroundColor' => '#000',
            'borderColor' => '#000',
            'textColor' => '#fff',
            'eventtype' => 'maintenance',
            'surl' => ""
            );
            // used to have
            //'className' => $classname,
            
        }

    }
    dbclose();
    $val=json_encode($jobs);
    $wc=setCache('presscalendar',$startOfWeek,$val);
    //$jobs['cacheFilename']=$wc;
    header('Content-type: application/json');
    echo json_encode($jobs);

