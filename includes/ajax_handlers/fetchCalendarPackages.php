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
    
    /*
    $sql="SELECT A.*, E.inserter_name, B.run_name, C.pub_name, C.pub_color, C.reverse_text 
    FROM jobs_inserter_packages A, publications_insertruns B, publications C, inserters E 
    WHERE A.package_startdatetime>='$startOfWeek' AND A.package_stopdatetime<'$endOfWeek' 
    AND A.inserter_id=E.id AND A.run_id=B.id AND A.pub_id=C.id";
    */
    
    $sql="SELECT * FROM inserters";
    $dbInserters=dbselectmulti($sql);
    $inserters=array();
    if($dbInserters['numrows']>0)
    {
        foreach($dbInserters['data'] as $inserter)
        {
            $inserters[$inserter['id']]=$inserter['inserter_name'];
        }
    }
    
    $sql="SELECT A.*, B.pub_color, B.reverse_text FROM jobs_inserter_packages A, publications B WHERE A.package_startdatetime>='$startOfWeek' AND A.package_stopdatetime<'$endOfWeek' AND A.pub_id=B.id";
    $dbSchedule=dbselectmulti($sql);
    if($_GET['mode']=='test'){print $sql;}
    if ($dbSchedule['numrows']>0)
    {
        foreach ($dbSchedule['data'] as $schedule)
        {
            
            
            
            $title=$pubs[$schedule['pub_id']];
            $draw=$schedule['inserter_request'];
            $jobid=$schedule['id'];
            $inserter=$inserters[$schedule['inserter_id']];
            $jid="JOB ID: $schedule[id]";
            $description="Inserter: $inserter<br />Draw: $draw<br />$jid\n";
            $fulldescription="Inserter: $inserter<br />$title<br>Draw: $draw<br />$jid\n";
            
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
            'start' => date("Y-m-d H:i",strtotime($schedule['package_startdatetime'])),
            'end' => date("Y-m-d H:i",strtotime($schedule['package_stopdatetime'])),
            'allDay' => false,
            'tags' => $jobticket.$caution.$recurring,
            'description' => $description,
            'fulldetails' => $fulldescription,
            'color' => $backcolor,
            'backgroundColor' => $backcolor,
            'borderColor' => $backcolor,
            'textColor' => $forecolor,
            'inserter' => $inserter,
            'eventtype'=>'job'
            );
            //this line removed to fix weird popup behavior
            //'url' => "inserterPlanner.php?popup=true&action=listpackages&planid=".$schedule['plan_id']."&pubid=".$schedule['pub_id']
            
        }

    }
    
    $sql="SELECT * FROM maintenance_scheduled WHERE equipment_type='inserter' AND starttime>='$startOfWeek' AND endtime<'$endOfWeek'";
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