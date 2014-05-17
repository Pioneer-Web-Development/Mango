<?php
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
    include("../functions_db.php");
    include("../config.php");
    global $siteID;
    
    $sql="SELECT A.*, B.id AS runid, B.schedule_start, B.schedule_stop, C.reverse_text, C.pub_color, C.pub_name, D.run_name FROM bindery_jobs A, bindery_runs B, publications C, publications_runs D  WHERE A.site_id=$siteID AND A.id=B.bindery_id AND B.schedule_start>='$startOfWeek' AND B.schedule_stop<'$endOfWeek' AND A.pub_id=C.id AND A.run_id=D.id AND A.status<>99";
    $dbSchedule=dbselectmulti($sql);
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
            if ($schedule['layout_id']!=0)
            {
                $caution="<img src='artwork/icons/caution_icon.png' border=0 width=18>";
            } else {
                $caution=""; 
            }
            if ($schedule['recurring_id']==0)
            {
                $recurring="<img src='artwork/icons/repeat_icon_48.png' border=0 width=18 />";
                $recurringid=$schedule['recurring_id'];    
            } else {
                $recurring='';
                $recurringid=0;
            }
            $stitcher='Sticher 1'; //@TODO need to show which stitcher is being used for this job
            $draw=$schedule['draw'];
            $jobid=$schedule['id'];
            $jid="JOB ID: $schedule[id]";
            $description="Draw: $draw<br />$jid\n";
            $fulldescription="$title<br>Draw: $draw<br />$jid\n";
            
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
            $classname="publications".$schedule['pub_id'];
            $jobs[]=array(
            'id' => $schedule['id'],
            'runid' => $schedule['runid'],
            'title' => htmlentities($title),
            'start' => date("Y-m-d H:i",strtotime($schedule['schedule_start'])),
            'end' => date("Y-m-d H:i",strtotime($schedule['schedule_stop'])),
            'allDay' => false,
            'tags' => $jobticket.$caution.$recurring,
            'description' => $description,
            'fulldetails' => $fulldescription,
            'color' => $backcolor,
            'backgroundColor' => $backcolor,
            'borderColor' => $backcolor,
            'textColor' => $forecolor,
            'stitcher' => $stitcher,
            'className' => $classname,
            'recurringid' => $recurringid,
            'eventtype'=>'job',
            'sql'=>$sql,
            'surl' => "binderyJobs.php?popup=true&action=edit&id=".$schedule['runid']
            );
        }

    } else {
        $jobs[]=array('status'=>'No jobs','sql'=>$sql);
    }
    $sql="SELECT * FROM maintenance_scheduled WHERE equipment_type='stitcher' AND starttime>='$startOfWeek' AND endtime<'$endOfWeek'";
    $dbSchedule=dbselectmulti($sql);
    if($_GET['mode']=='test'){print $sql;}
    if ($dbSchedule['numrows']>0)
    {
        foreach ($dbSchedule['data'] as $schedule)
        {
            $jid="Maintenance ID: $schedule[id]";
            $description="Maintenance task.\n";
            
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
    //echo json_encode($temp);
echo json_encode($jobs);
?>