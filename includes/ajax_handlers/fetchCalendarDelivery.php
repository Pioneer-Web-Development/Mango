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
    
    $sql="SELECT A.*, B.reverse_text, B.pub_color, B.pub_name, C.run_name FROM bindery_jobs A,publications B, publications_runs C  WHERE A.site_id=$siteID AND A.bindery_startdate>='$startOfWeek' AND A.bindery_stopdate<'$endOfWeek' AND A.pub_id=B.id AND A.run_id=C.id AND A.status<>99";
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
            'title' => htmlentities($title),
            'start' => date("Y-m-d H:i",strtotime($schedule['bindery_startdate'])),
            'end' => date("Y-m-d H:i",strtotime($schedule['bindery_stopdate'])),
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
            'url' => "binderyJobs.php?popup=true&action=edit&id=".$schedule['id']
            );
        }

    }
    dbclose();
    //echo json_encode($temp);
echo json_encode($jobs);
?>