<?php
  include("../functions_db.php");
  
  $action=$_POST['action'];
  
  switch($action)
  {
      case "update":
        $jobid=intval($_POST['jobid']);
        $runid=intval($_POST['runid']);
        $start=$_POST['runstart'];
        $stop=$_POST['runstop'];
        $goal=intval($_POST['goal']);
        if($goal==''){$goal=0;}
        if($runid==0)
        {
            //adding a new run
            $sql="INSERT INTO bindery_runs (bindery_id, schedule_start, schedule_stop, produced_goal) VALUES ('$jobid','$start','$stop','$goal')";
            $dbInsert=dbinsertquery($sql);
            if($dbInsert['error']=='')
            {
                $runid=$dbInsert['insertid'];
                $json['status']='success';
                $json['runid']=$runid;
            } else {
                $json['status']='error';
            }
            $json['action']='insert';
            $html="<div id='run_$runid' style='margin-bottom:4px;padding-bottom:4px;border-bottom:thin solid black;width:800px;'>\n";
            $html.="<div id='runinfo_$runid'><div style='width:200px;float:left;'><b>Scheduled start:</b> ".date("m/d/Y H:i",strtotime($start))."</div>";
            $html.="<div style='width:200px;float:left;'><b>Scheduled stop:</b> ".date("m/d/Y H:i",strtotime($stop))."</div>";
            $html.="<div style='width:200px;float:left;'><b>Production Goal:</b> $goal</div></div>";
            $html.="<input type='button' class='button' onClick='editRun($runid);' value='Edit'>";
            $html.="<input type='button' class='button delete' onClick='deleteRun($runid);' value='Delete'>";
            $html.="<div class='clear'></div>\n";
            $html.="</div>\n";
            $json['html']=$html;
            
        } else {
            //updating existing run
            $sql="UPDATE bindery_runs SET schedule_start='$start', schedule_stop='$stop', produced_goal='$goal' WHERE id=$runid";
            $dbUpdate=dbexecutequery($sql);
            if($dbUpdate['error']=='')
            {
                $json['status']='success';
            } else {
                $json['status']='error';
            }
            $html="<div style='width:200px;float:left;'><b>Scheduled start:</b> ".date("m/d/Y H:i",strtotime($start))."</div>";
            $html.="<div style='width:200px;float:left;'><b>Scheduled stop:</b> ".date("m/d/Y H:i",strtotime($stop))."</div>";
            $html.="<div style='width:200px;float:left;'><b>Production Goal:</b> $goal</div>";
            
            $json['action']='update';
            $json['start']=$info['schedule_start'];
            $json['stop']=$info['schedule_stop'];
            $json['goal']=$info['produced_goal'];
            $json['html']=$html;
        }
        
        
      break;
      
      case "edit":
        $runid=intval($_POST['runid']);
        $sql="SELECT * FROM bindery_runs WHERE id=$runid";
        $dbRunInfo=dbselectsingle($sql);
        $info=$dbRunInfo['data'];
        $json['status']='success';
        $json['sql']=$sql;
        $json['start']=$info['schedule_start'];
        $json['stop']=$info['schedule_stop'];
        $json['goal']=$info['produced_goal'];
      break;
      
      case "delete":
        $runid=intval($_POST['runid']);
        $jobid=intval($_POST['jobid']);
        $sql="DELETE FROM bindery_runs WHERE id=$runid";
        $dbDelete=dbexecutequery($sql);
        $json['status']='success';
      break;
      
  }
  
  echo json_encode($json);
?>
