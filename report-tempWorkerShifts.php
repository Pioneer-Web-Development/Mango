<?php
//<!--VERSION: .9 **||**-->
if($_POST['output']=='csv')
{
    include("includes/functions_db.php");
    include("includes/functions_common.php");
    show_report();
} else {
    include("includes/mainmenu.php") ;
} 
    
if ($_POST['submit'])
{
    show_report();
} else {
    global $siteID;
    $types=array('csv'=>"Output for excel",'screen'=>"Output to screen");
    $sql="SELECT * FROM temp_agencies ORDER BY agency";
    $dbAgencies=dbselectmulti($sql);
    $agencies[0]='All agencies';
    if($dbAgencies['numrows']>0)
    {
        foreach($dbAgencies['data'] as $agency)
        {
            $agencies[$agency['id']]=$agency['agency'];
        }
    }
    $sql="SELECT * FROM temp_workers ORDER BY last_name, first_name";
    $dbWorkers=dbselectmulti($sql);
    $workers[0]='All temp workers';
    if($dbWorkers['numrows']>0)
    {
        foreach($dbWorkers['data'] as $worker)
        {
            $workers[$worker['id']]=$worker['last_name'].', '.$worker['first_name'].' '.$worker['middle_name'];
        }
    }
    $start=date("Y-m-d",strtotime("-7 days"));
    $stop=date("Y-m-d");
    print "<form method=post>\n";
    make_select('agencyid',$agencies[0],$agencies,'Agencies','Pull for all agencies or specific agency');
    make_select('workerid',$workers[0],$workers,'Workers','Pull for all temp workes or specific worker');
    make_date('start',$start,'Start date','Start with shifts starting on this date');
    make_date('stop',$stop,'End date','End with shifts ending on this date');
    make_checkbox('comments',$_POST['comments'],'Comment','Check to show shift comments');
    make_checkbox('includepay',1,'Include Pay','Check to include salary information &amp; shift wages');
    make_select('output',$types['screen'],$types,'Output','');
    make_submit('submit','Generate Report');
    print "</form>\n"; 
}

  

function show_report()
{
    
    $agencyid=intval($_POST['agencyid']);
    $workerid=intval($_POST['workerid']);
    $start=$_POST['start'];
    $stop=$_POST['stop'];
    $shiftstop=date("Y-m-d",strtotime($stop."+1 day"));
    if($_POST['includepay']){$showpay=1;}else{$showpay=0;}
    if($_POST['comments']){$comments=1;}else{$comments=0;}
    if($agencyid==0 && $workerid==0)
    {
        $sql="SELECT * FROM temp_workers ORDER BY last_name, first_name";
    } elseif($workerid!=0) {
        $sql="SELECT * FROM temp_workers WHERE id=$workerid";
    } else {
        $sql="SELECT * FROM temp_workers WHERE agency_id=$agencyid ORDER BY last_name, first_name";
    }
    if($showpay){$colspan=4;}else{$colspan=3;}
                            
    $dbWorkers=dbselectmulti($sql);
    if($dbWorkers['numrows']>0)
    {
        if($_POST['output']=='screen')
        {
            print "<a href='?again'>Run another report</a> | <a href='tempWorkers.php'>Go to worker list</a><br>";
        } else {
            header('Content-Type: text/plain'); // plain text file
            header('Content-Disposition: attachment; filename="IPT-tempReport-'.date("Y-m-d").'.csv"');
        }
        foreach($dbWorkers['data'] as $worker)
        {
            $name=stripslashes($worker['first_name'].' '.$worker['middle_name'].' '.$worker['last_name']);
            $rate=$worker['rate'];
            
            if($_POST['output']=='screen')
            {   print "<table class='report-clean-mango' style='margin-bottom:10px'>\n";
                if($showpay)
                {
                    print "<tr><th>In</th><th>Out</th><th>Total Time</th><th>Cost</th></tr>\n";
                } else {
                    print "<tr><th>In</th><th>Out</th><th>Total Time</th></tr>\n";
                }
                print "<tr><td colspan=2>$name - ";
                if($showpay){print "\$$rate/hr";}
                print "</td><td colspan=2 style='text-align:right;'>Start: $start  End: $stop</td></tr>\n";
            } else {
                print "Name: $name,";
                if($showpay){print "Rate: \$$rate,";}
                print "Start Date: $start,End Date: $stop\n";
                if($showpay)
                {
                    print "In,Out,Total Time,Cost\n";    
                } else {
                    print "In,Out,Total Time\n";    
                }
            }
            //get worker info
            $sql="SELECT * FROM temp_shifts 
            WHERE temp_id=$worker[id] 
            AND time_in>='$start 00:01' 
            AND time_out<='$shiftstop 12:00' 
            ORDER BY time_in ASC";
            $dbShifts=dbselectmulti($sql);
            
            
            if ($dbShifts['numrows']>0)
            {
                $workerTime=0;
                $workerCost=0;
                $commentNumber=0;
                $commentText='';
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
                        $time=int2TimeDecimal($seconds);
                        $seconds=$shift['seconds'];
                        if($rate>0)
                        {
                            $cost=$time*$rate;
                            $cost=round($cost,2);
                            $temp=explode(".",$cost);
                            if(strlen($temp[1])==1){$cost.="0";}
                        } else {
                            $cost=0.00;
                        }
                        
                    } else {
                        $time='Open shift';
                        $cost=0.00;
                    }
                    $workerCost+=$cost;
                    if($time!='Open shift')
                    {
                        $workerTime=$workerTime+$time;
                        //print "adding $time - total is now $workerTime<br />";
                    }
                    $in=date("D m/d/Y H:i",$in);
                    $out=date("D m/d/Y H:i",$out);
                    
                    if($comments && $shift['notes']!='')
                    {
                        $commentNumber++;
                        $in="($commentNumber) ".$in;
                        $commentText.="($commentNumber) ".stripslashes($shift['notes']);
                        if($_POST['output']=='screen')
                        {
                            $commentText.="<br>";
                        } else {
                            $commentText.="    ";
                        }
                    }
                    
                    if($_POST['output']=='screen')
                    {
                        print "<tr>";
                        print "<td>$in</td>";
                        print "<td>$out</td>";
                        print "<td>$time</td>";
                        if($showpay){print "<td>\$$cost</td>";}
                        print "</tr>\n";
                        
                    } else {
                        if($showpay)
                        {
                            print "$in,$out,$time,$cost\n";
                        } else {
                            print "$in,$out,$time\n";    
                        }
                        
                    }
                }
                
                if($workerTime>40)
                {
                    $overtime=($workerTime-40)*.5*$rate;
                    $overtime=round($overtime,2);
                    $workerCost=$workerCost+$overtime;
                } else {
                    $overtime=0;
                }    
                $grandTime+=$workerTime;
                $grandCost+=$workerCost;
                if($_POST['output']=='screen')
                {
                    print "<tr>
                    <td colspan=2 style='text-align:right;font-weight:bold;'>Time total for period: $workerTime</td>";
                    if($showpay)
                    {
                        print "<td colspan=2 style='text-align:right;font-weight:bold;'>Cost total for period: \$$workerCost -- Overtime: \$$overtime</td>";
                    } else {
                        print "<td colspan=2 style='text-align:right;font-weight:bold;'></td>";
                    }
                    print "</tr>\n";
                } else {
                    if($showpay)
                    {
                        print "Total time for period: ,$workerTime,Total cost for worker for period:,$workerCost\n";    
                    } else {
                        print "Total time for period: ,$workerTime\n";    
                    }
                }
            } else {
                if($_POST['output']=='screen')
                {
                    print "<tr><td colspan=4>This worker had no shifts during this time</td></tr>\n";    
                } else {
                    print "This worker had no shifts during this time\n";
                }
               
            }
            if($comments && $commentText!='')
            {
                if ($_POST['output']=='screen')
                {
                    print "<tr><td colspan=$colspan><b>Comments:</b><br>$commentText</td></tr>\n";
                } else {
                    print "Comments:\n $commentText\n"; 
                }
                
            }
            
            if($_POST['output']=='screen')
                {
                   print "</table>\n"; 
                } else {
                   print "\n\n\n\n"; 
                }
        }
        if($_POST['output']=='screen')
        {
            print "<div style='width:400px;margin-top:20px;padding;20px;border:1px solid black;font-size:16px;text:align:center;'>
            Total hours for all workers: $grandTime<br>";
            if($showpay)
            {
                print "Total cost for all workers: \$$grandCost";
            }
            print "</div>\n";
        } else {
            print "Total hours for all workers: $grandTime\n";
            if($showpay)
            {
                print "Total cost for all workers: $grandCost\n";
            }
            die();
        }
                
    }
    
}  
if($_POST['output']!='csv')
{
    footer();    
}            
?>