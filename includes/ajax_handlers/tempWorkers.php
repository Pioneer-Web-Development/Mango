<?php
  include("../functions_db.php");
  include("../functions_common.php");
  session_start();
  $action=$_POST['action'];
  $workerid=intval($_POST['workerid']);
  $shiftid=intval($_POST['shiftid']);
  $pin=intval($_POST['pin']); 
  $json['status']='error';
  $json['message']=$action;
  
  $date=date("Y-m-d");
  $hours=date("H");
  $minutes=date("i");
  
  switch ($minutes)
  {
      case ($minutes<8):
      $minutes="00";
      break;
      
      case ($minutes>=8&&$minutes<=15):
      $minutes="15";
      break;
      
      case ($minutes>15 && $minutes<23):
      $minutes="15";
      break;
      
      case ($minutes>=23 && $minutes<=30):
      $minutes="30";
      break;
      
      case ($minutes>30 && $minutes<38):
      $minutes="30";
      break;
      
      case ($minutes>=38 && $minutes<=45):
      $minutes="45";
      break;
      
      case ($minutes>45 && $minutes<53):
      $minutes="45";
      break;
      
      case ($minutes>=53 && $minutes<=59):
      //need to increment hours here
      if($hours<23){
          $hours++;
          $minutes="00";
      } else {
          //oops also need up increment day!
          $date=date("Y-m-d",strtotime("+1 day"));
          $hours="00";
          $minutes="00";
      }
      break;
  }       
  $dt=$date." ".$hours.":".$minutes;
  
  switch($action)
  {
     case "login":
        $sql="SELECT * FROM temp_workers WHERE id=$workerid";
        $dbWorker=dbselectsingle($sql);
        $workerPin=$dbWorker['data']['pin_number'];
        if($workerPin==$pin)
        {
            $json['status']='success';
            
            //find out if we have an open session
            $sql="SELECT * FROM temp_shifts WHERE temp_id=$workerid AND time_out IS Null ORDER BY time_in DESC LIMIT 1";
            $dbShift=dbselectsingle($sql);
            if($dbShift['numrows']>0)
            {
                //we have a record..  ending a current shift
                $json['shifter']='stop';
            } else {
                $json['shifter']='start'; //starting a new shift
            }
        } else {
            $json['status']='error';
            $json['message']='Incorrect Pin';
        }
        echo json_encode($json);
  
     break;
     
     case "approve":
        $userid=$_SESSION['cmsuser']['userid'];
        $sql="SELECT * FROM temp_shifts WHERE id=$shiftid";
        $dbShift=dbselectsingle($sql);
        $shift=$dbShift['data'];
        if($shift['approved']==1)
        {
            //un approving
            $sql="UPDATE temp_shifts SET approved=0, approved_by=0 WHERE id=$shiftid";
            $dbUpdate=dbexecutequery($sql);
            $json['approved']=0;
        } else {
            $sql="UPDATE temp_shifts SET approved=1, approved_by=$userid WHERE id=$shiftid";
            $dbUpdate=dbexecutequery($sql);
            $json['approved']=1;
        }
        $json['status']='success';
        echo json_encode($json);
  
     break;
     
      
     case "start":
        $sql="SELECT * FROM temp_workers WHERE id=$workerid AND pin_number='$pin'";
        $dbWorker=dbselectsingle($sql);
        $workerPin=$dbWorker['data']['pin_number'];
        if($workerPin==$pin)
        {
            // checking again just to make sure nothing weird happened
            $json['status']='success';
            //find out if we have an open session
            $sql="SELECT * FROM temp_shifts WHERE temp_id=$workerid AND time_out IS Null ORDER BY time_in DESC LIMIT 1";
            $dbShift=dbselectsingle($sql);
            if($dbShift['numrows']>0)
            {
                //we have a record..  ending a current shift
                $shiftid=$dbShift['data']['id'];
                $start=strtotime($dbShift['data']['time_in']);
                $stop=strtotime($dt);
                $shift=$stop-$start; //storing in seconds
                $sql="UPDATE temp_shifts SET time_out='$dt' seconds='$shift' WHERE id=$shiftid";
                $dbUpdate=dbexecutequery($sql);
                $error=$dbUpdate['error'];
            } else {
                $json['shifter']='start'; //starting a new shift
                $sql="INSERT INTO temp_shifts (temp_id, time_in) VALUES ('$workerid', '$dt')";
                $dbInsert=dbinsertquery($sql);
                $error=$dbInsert['error'];
            }
            if($error!='')
            {
                $json['status']='error';
                $json['message']=$error;
            }
        } else {
            $json['status']='error';
            $json['message']='Incorrect Pin Entered';
        }
        echo json_encode($json);
  
     break;
     
     case "stop":
        $sql="SELECT * FROM temp_workers WHERE id=$workerid AND pin_number='$pin'";
        $dbWorker=dbselectsingle($sql);
        $workerPin=$dbWorker['data']['pin_number'];
        if($workerPin==$pin)
        {
            // checking again just to make sure nothing weird happened
            $json['status']='success';
            //find out if we have an open session
            $sql="SELECT * FROM temp_shifts WHERE temp_id=$workerid AND time_out IS Null ORDER BY time_in DESC LIMIT 1";
            $dbShift=dbselectsingle($sql);
            if($dbShift['numrows']>0)
            {
                //we have a record..  ending a current shift
                $shiftid=$dbShift['data']['id'];
                $start=strtotime($dbShift['data']['time_in']);
                $stop=strtotime($dt);
                $shift=($stop-$start); //storing in seconds
                $sql="UPDATE temp_shifts SET time_out='$dt', seconds='$shift' WHERE id=$shiftid";
                $dbUpdate=dbexecutequery($sql);
                $error=$dbUpdate['error'];
            } else {
                $json['shifter']='start'; //starting a new shift
                $sql="INSERT INTO temp_shifts (temp_id, time_in) VALUES ('$workerid', '$dt')";
                $dbInsert=dbinsertquery($sql);
                $error=$dbInsert['error'];
            }
            if($error!='')
            {
                $json['status']='error';
                $json['message']=$error;
            }
        } else {
            $json['status']='error';
            $json['message']='Incorrect Pin';
        }
        echo json_encode($json);
  
     break;
     
     case "hours":
           $start=date("m/d/Y H:i",strtotime("-1 month"));
           $stop=date("m/d/Y H:i");
           print "<table class='report'>\n";
           print "<tr><th>In</th><th>Out</th><th>Total Time</th></tr>\n";
           print "<tr><td colspan=3 style='text-align:right;'>Start: $start  End: $stop</td></tr>\n";
            
            $sql="SELECT * FROM temp_shifts 
            WHERE temp_id=$workerid 
            AND time_in>='$start' 
            AND time_out<='$stop' 
            ORDER BY time_in DESC";
            $dbShifts=dbselectmulti($sql);
            if ($dbShifts['numrows']>0)
            {
                $workerTime=0;
                $workerCost=0;
                foreach($dbShifts['data'] as $shift)
                {
                    $id=$shift['id'];
                    $in=$shift['time_in'];
                    $out=$shift['time_out'];
                    if($in!='' && $out!='')
                    {
                        $out=strtotime($out);
                        $in=strtotime($in);
                        $seconds=$out-$in;
                        $time=int2Time($seconds);
                        $seconds=$shift['seconds'];
                        if($rate>0)
                        {
                            $cost=$seconds/3600*$rate;
                            $cost=round($cost,2);
                        } else {
                            $cost=0.00;
                        }
                        
                    } else {
                        $time='Open shift';
                        $cost=0.00;
                    }
                    $workerCost+=$cost;
                    $workerTime+=$seconds;
                    $in=date("m/d/Y H:i",$in);
                    $out=date("m/d/Y H:i",$out);
                    print "<tr>";
                    print "<td>$in</td>";
                    print "<td>$out</td>";
                    print "<td>$time</td>";
                    print "</tr>\n";
                    
                }
                $grandTime+=$workerTime;
                $grandCost+=$workerCost;
                $workerTime=int2Time($workerTime);
                print "<tr>
                <td colspan=3 style='text-align:right;font-weight:bold;'>Time total for period: $workerTime</td>
                </tr>\n";
                
            } else {
                 print "<tr><td colspan=3>You had no shifts in the past 30 days</td></tr>\n";    
            }
           
     break; 
  }
  
  dbclose();
?>          