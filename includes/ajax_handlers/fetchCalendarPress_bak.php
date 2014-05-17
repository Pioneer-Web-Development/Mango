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
    
    $sql="SELECT A.*, B.run_name, C.pub_name, C.pub_color, C.reverse_text FROM jobs A, publications_runs B, publications C  WHERE A.site_id=$siteID AND A.startdatetime>='$startOfWeek' AND A.enddatetime<'$endOfWeek' AND A.run_id=B.id AND A.pub_id=C.id AND A.status<>99";
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
            $folder=$schedule['folder'];
            $fclass='folder'.$folder;
            $jid="JOB ID: $schedule[id]";
            $description="Folder: $folder<br />Draw: $draw<br />$jid\n";
            $fulldescription="Folder: $folder<br />$title<br>Draw: $draw<br />$jid\n";
            
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
            'title' => $title,
            'start' => date("Y-m-d H:i",strtotime($schedule['startdatetime'])),
            'end' => date("Y-m-d H:i",strtotime($schedule['enddatetime'])),
            'allDay' => false,
            'tags' => $jobticket.$caution.$recurring,
            'description' => $description,
            'fulldetails' => $fulldescription,
            'background' => $backcolor,
            'foreground' => $forecolor,
            'folder' => $schedule['folder'],
            'className' => $classname,
            'recurringid' => $recurringid,
            'surl' => "jobPressPopup.php?id=".$schedule['id']
            );
        }

    }
    dbclose();
    //echo json_encode($temp);
echo json_encode($jobs);
?>