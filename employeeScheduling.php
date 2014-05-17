<?php
include("includes/mainmenu.php") ;
?>
<body>

<div id="wrapper">

<?php
if (!checkPermission("$_SERVER[SCRIPT_NAME]")){redirect('default.php?accesserror=true');}

if ($_POST['submitbutton']=='Add'){
    save_schedule('insert');
} elseif ($_POST['submitbutton']=='Update'){
    save_schedule('update'); 
} elseif ($_POST['submitbutton']=='Build Schedule'){
    build_schedule(); 
} else {
    show_schedule();
}

function show_schedule()
{
    global $siteID;
    $action=$_GET['action'];
    
    //need to build a list of dates with the correct day of week
    /*************************************************
    * 
    * 
    * THIS WILL BE DONE LATER!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    * 
    * 
    */
    $sql="SELECT * FROM user_departments ORDER BY department_name";
    $dbDepartments=dbselectmulti($sql);
    $departments=array();
    $department[0]='Please choose';
    if ($dbDepartments['numrows']>0)
    {
        foreach ($dbDepartments['data'] as $department)
        {
            $departments[$department['id']]=$department['department_name'];
        }
    }
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Add';
            $startdate=date("Y-m-d");
            $department=0;
            
        } else {
            $id=$_GET['id'];
            $sql="SELECT * FROM employee_schedules WHERE id=$id";
            $dbSchedule=dbselectsingle($sql);
            $schedule=$dbSchedule['data'];
            $department=stripslashes($schedule['department_id']);
            $startdate=stripslashes($schedule['start_date']);
            $button="Update";
        }
        print "<form method=post>\n";
        make_date('startdate',$startdate,'Start Date','Select the start date for the schedule');
        make_select('department',$departments[$department],$departments,'Department','Choose the department this schedule is for.');
        make_submit('submitbutton',$button);
        make_hidden('scheduleid',$id);
        print "</form>\n";
    } elseif ($action=='build')
    {
        print "<form method=post>\n";
        print "<table class='grid'>\n";
        print "<tr><th style='width:150px;'>Employee</th>";
        $scheduleid=$_GET['id'];
        $sql="SELECT * FROM employee_schedules WHERE id=$scheduleid";
        $dbSchedule=dbselectsingle($sql);
        $schedule=$dbSchedule['data'];
        $departmentid=$schedule['department_id'];
        
        $startdate=$schedule['start_date'];
        $startdatetime=$schedule['start_date']." 00:00";
        $shiftstop=array();
        $enddate=date("Y-m-d",strtotime($startdate."+6 days"));
        $enddatetime=date("Y-m-d H:i",strtotime($startdatetime."+36 hours"));
        while (strtotime($startdate)<=strtotime($enddate))
        {
            $shiftstop[$startdate][0]='Not scheduled';
            $shiftstop[$startdate][1]='Vacation';
            $shiftstop[$startdate][2]='Personal';
            $shiftstop[$startdate][3]='Sick';
            $shiftstop[$startdate][4]='Other';
            $i=10;
            while (strtotime($startdatetime)<=strtotime($enddatetime))
            {
                $d=date("m/d/Y H:i", strtotime($startdatetime));
                $shiftstop[$startdate][$d]=$d;
                $startdatetime=date("Y-m-d H:i",strtotime($startdatetime."+30 minutes"));
                $i++;
            }
            $startdate=date("Y-m-d",strtotime($startdate."+1 day"));
            $enddatetime=date("Y-m-d",strtotime($startdate."+1 day"))." 12:00";
        
        }
        
        $startdate=$schedule['start_date'];
        $startdatetime=$schedule['start_date']." 00:00";
        $shiftstart=array();
        $enddate=date("Y-m-d",strtotime($startdate."+6 days"));
        $enddatetime=date("Y-m-d H:i",strtotime($startdatetime."+23 hours 30 minutes"));
        
        while (strtotime($startdate)<=strtotime($enddate))
        {
            print "<th>".date("D, m/d/Y",strtotime($startdate))."</th>";
            $shiftstart[$startdate][0]='Not scheduled';
            $shiftstart[$startdate][1]='Vacation';
            $shiftstart[$startdate][2]='Personal';
            $shiftstart[$startdate][3]='Sick';
            $shiftstart[$startdate][4]='Other';
            $i=10;
            while (strtotime($startdatetime)<=strtotime($enddatetime))
            {
                $d=date("m/d/Y H:i", strtotime($startdatetime));
                $shiftstart[$startdate][$d]=$d;
                $startdatetime=date("Y-m-d H:i",strtotime($startdatetime."+30 minutes"));
                $i++;
            }
            $startdate=date("Y-m-d",strtotime($startdate."+1 day"));
            $enddatetime=$startdate." 23:59:59";
        
        }
        print "</tr>\n";
        
        //get all employees
        $sql="SELECT id, firstname, lastname FROM users WHERE site_id=$siteID AND department_id=$departmentid ORDER BY firstname, lastname";
        $dbEmployees=dbselectmulti($sql);
        if ($dbEmployees['numrows']>0)
        {
            foreach($dbEmployees['data'] as $employee)
            {
                print "<tr><td>".$employee['firstname'].' '.$employee['lastname']."</td>";
                foreach($shiftstart as $startdate=>$hours)
                {
                    $start=$startdate." 00:00";
                    $end=$startdate." 23:59";
                    $sql="SELECT * FROM employee_schedules_xref WHERE schedule_id=$scheduleid AND user_id=$employee[id] AND shift_start>='$start' AND shift_start<='$end'";
                    $dbShift=dbselectsingle($sql);
                    if ($dbShift['numrows']>0)
                    {
                        $shift=$dbShift['data'];
                        
                        if ($shift['shift_type']=='Normal')
                        {
                            print "Found a normal<br />\n";
                            $starttime=date("m/d/Y H:i",strtotime($shift['shift_start']));    
                            $stoptime=date("m/d/Y H:i",strtotime($shift['shift_stop']));    
                        } elseif ($shift['shift_type']=='Not scheduled') {
                            $starttime=0;
                            $stoptime=0;
                        } elseif ($shift['shift_type']=='Vacation') {
                            $starttime=1;
                            $stoptime=1;
                        } elseif ($shift['shift_type']=='Personal') {
                            $starttime=2;
                            $stoptime=2;
                        } elseif ($shift['shift_type']=='Sick') {
                            $starttime=3;
                            $stoptime=3;
                        } elseif ($shift['shift_type']=='Other') {
                            $starttime=4;
                            $stoptime=4;
                        }
                        
                    } else {
                       $starttime=0; 
                       $stoptime=0; 
                    }
                        print "<td>";
                        print 'Shift start:<br />';
                        print input_select('start_'.$employee['id'].'_date_'.$startdate,$hours[$starttime],$hours)."<br />\n";
                        $stophours=$shiftstop[$startdate];
                        print 'Shift stop:<br />';
                        print input_select('stop_'.$employee['id'].'_date_'.$startdate,$stophours[$stoptime],$stophours);
                        print "</td>";
                }
                print "</tr>";
            }
        }
        print "<tr><th colspan=8><input type='submit' name='submitbutton' id='submitbutton' value='Build Schedule'></th></tr>\n";
        print "</table>\n";
        print "<input type='hidden' name='scheduleid' id='scheduleid' value='$scheduleid'>\n";
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $id=$_GET['id'];
        $sql="DELETE FROM employee_schedules WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $sql="DELETE FROM employee_schedules_xref WHERE schedule_id=$id";
        $dbDelete=dbexecutequery($sql);
        redirect('?action=list');
    } else {
        //list the privileges
        $sql="SELECT * FROM employee_schedules WHERE site_id=$siteID ORDER BY start_date DESC";
        $dbSchedules=dbselectmulti($sql);
        print "<table class='grid'>\n";
        print "<tr><th colspan=5><a href='?action=add'>Add new schedule</a></th></tr>\n";
        print "<tr><th>Department</th><th>Start Date</th><th colspan=3>Action</th></tr>\n";
        if ($dbSchedules['numrows']>0)
        {
            foreach($dbSchedules['data'] as $schedule)
            {
                $department=$departments[$schedule['department_id']];
                $startdate=$schedule['start_date'];
                $id=$schedule['id'];
                print "<tr>";
                print "<td>$department</td><td>$startdate<td><a href='?action=edit&id=$id'>Edit</td>";
                print "<td><a href='?action=build&id=$id'>Build</td>";
                print "<td><a href='?action=delete&id=$id' onClick='return confirmDeleteClick();'>Delete</a></td>";
                print "</tr>\n";
            
            }
        }
        print "<tr><th colspan=5><a href='?action=add'>Add new schedule</a></th></tr>\n";
        print "</table>\n";
    }

}


function save_schedule($action)
{
    global $siteID;
    $scheduleid=$_POST['scheduleid'];
    $departmentid=$_POST['department'];
    $startdate=$_POST['startdate'];

    if ($action=='insert')
    {
        $sql="INSERT INTO employee_schedules (department_id, start_date, site_id) VALUES ('$departmentid', '$startdate', '$siteID')";
        $db=dbinsertquery($sql);
            
    } else {
        $sql="UPDATE employee_schedules SET department_id='$departmentid', start_date='$startdate' WHERE id=$scheduleid";
        $db=dbexecutequery($sql);
    }
    if ($db['error']!='')
    {
        print $db['error'];
    } else {
        redirect("?action=list");
    }
}


function build_schedule()
{
    $scheduleid=$_POST['scheduleid'];
    
    //delete all existing entries for the xref table
    $sql="DELETE FROM employee_schedules_xref WHERE schedule_id=$scheduleid";
    $dbDelete=dbexecutequery($sql);
    $sql="INSERT INTO employee_schedules_xref (schedule_id, user_id, shift_start, shift_end, shift_type) VALUES";
    $values="";
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,6)=='start_')
        {
            $pieces=explode("_",$key);
            $stop='stop_'.$pieces[1].'_'.$pieces[2].'_'.$pieces[3];
            if ($value==0)
            {
                $stype='Not scheduled';
                $value=$pieces[3];
            }elseif($value==1)
            {
                $stype='Vacation';
                $value=$pieces[3];
            }elseif($value==2)
            {
                $stype='Personal';
                $value=$pieces[3];
            }elseif($value==3)
            {
                $stype='Sick';
                $value=$pieces[3];
            }elseif($value==4)
            {
                $stype='Other';
                $value=$pieces[3];
            } else {
                $stype='Normal';
            }
            $empid=$pieces[1];
            $value=date("Y-m-d H:i",strtotime($value));
            $startdate=$value;
            $enddate=date("Y-m-d H:i",strtotime($_POST[$stop]));
            $values.="('$scheduleid', '$empid', '$startdate', '$enddate', '$stype'),";
        }    
    }
    $values=substr($values,0,strlen($values)-1);
    $sql.=$values;
    $dbInsert=dbinsertquery($sql);
    if ($dbInsert['error']=='')
    {
        redirect("?action=list");
    } else {
        print $dbInsert['error'];
    }
}

?>
</div>
</form>
</body>
</html>
<?php 
    dbclose();
?>