<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;
 
    
if ($_POST['submit'])
{
    $action=$_POST['submit'];
} elseif ($_GET['action'])
{
    $action=$_GET['action'];
}


switch ($action)
{
    case "add":
    workers('add');
    break;
    
    case "edit":
    workers('edit');
    break;
    
    case "delete":
    workers('delete');
    break;
    
    case "Add":
    save_worker('insert');
    break;
    
    case "Update":
    save_worker('update');
    break;
    
    case "viewshifts":
    view_shifts();
    break;
    
    case "addshift":
    shifts('add');
    break;
    
    case "editshift":
    shifts('edit');
    break;
    
    case "deleteshift":
    shifts('delete');
    break;
    
    case "Save Shift":
    save_shift('insert');
    break;
    
    case "Update Shift":
    save_shift('update');
    break;
    
    case "Change week":
    view_shifts('update');
    break;
    
    default:
    workers('list');
    break;
}

 
function workers($action)
{
    global $siteID;
    $sql="SELECT * FROM temp_agencies ORDER BY agency";
    $dbAgencies=dbselectmulti($sql);
    $agencies[0]='Please select';
    if($dbAgencies['numrows']>0)
    {
        foreach($dbAgencies['data'] as $agency)
        {
            $agencies[$agency['id']]=$agency['agency'];
        }
    }
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add";
            $pin=generate_random_string(4,true);
            $agencyid=0;
            $status=1;
            $rate='0.00';
        } else {
            $id=intval($_GET['id']);
            $sql="SELECT * FROM temp_workers WHERE id=$id";
            $dbGroup=dbselectsingle($sql);
            $group=$dbGroup['data'];
            $first=$group['first_name'];
            $middle=$group['middle_name'];
            $last=$group['last_name'];    
            $status=$group['status'];    
            $pin=$group['pin_number'];    
            $agencyid=$group['agency_id'];    
            $rate=$group['rate'];    
            $button="Update";
        }
        print "<form method=post>\n";
        make_select('agencyid',$agencies[$agencyid],$agencies,'Agency');
        make_text('first',$first,'First Name','',50);
        make_text('middle',$middle,'Middle Name','',50);
        make_text('last',$last,'Last Name','',50);
        make_text('pin',$pin,'PIN','',50);
        make_number('rate',$rate,'Hourly Rate','',50);
        make_checkbox('status',$status,'Status','Check to activate');
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="DELETE FROM temp_workers WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM temp_workers ORDER BY last_name, first_name";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new worker</a>","Last Name,First Name,Pin,Status",7);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $id=$group['id'];
                $first=$group['first_name'];
                $last=$group['last_name'];
                $pin=$group['pin_number'];
                if ($group['status']==1){$active="Active";}else{$active="Disabled";}
                print "<tr>";
                print "<td>$last</td>";
                print "<td>$first</td>";
                print "<td>$pin</td>";
                print "<td>$active</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>";
                print "<td><a href='?action=viewshifts&id=$id'>View Shifts</a></td>";
                print "<td><a class='delete' href='?action=delete&id=$id'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbGroups);
        
    }
} 



function save_worker($action)
{
    global $siteID;
    $id=$_POST['id'];
    $first=addslashes($_POST['first']);
    $last=addslashes($_POST['last']);
    $middle=addslashes($_POST['middle']);
    $pin=addslashes($_POST['pin']);
    $agencyid=addslashes($_POST['agencyid']);
    $rate=$_POST['rate'];
    if($_POST['status']){$status=1;}else{$status=0;}
    if($action=='insert')
    {
        $sql="INSERT INTO temp_workers (first_name, middle_name, last_name, status, pin_number, agency_id, 
        rate ) VALUES ('$first', '$middle', '$last', '$status', '$pin', '$agencyid', '$rate')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE temp_workers SET first_name='$first', middle_name='$middle', last_name='$last', 
        agency_id='$agencyid', pin_number='$pin', status='$status', rate='$rate' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the worker','error');
    } else {
        setUserMessage('Worker successfully saved','success');
    }
    redirect("?action=list");    
}


function shifts($action)
{
    $workerid=intval($_GET['workerid']);
    if($action=='add' || $action=='edit')
    {
       if($action=='add')
       {
           $start='';
           $stop='';
           $approved=0;
           $button='Save Shift';
       } else {
           $shiftid=intval($_GET['shiftid']);
           $sql="SELECT * FROM temp_shifts WHERE id=$shiftid";
           $dbShift=dbselectsingle($sql);
           $shift=$dbShift['data'];
           $start=$shift['time_in'];
           $stop=$shift['time_out'];
           $notes=stripslashes($shift['notes']);
           $approved=$shift['approved'];
           $button='Update Shift';  
       }
       print "<form method=post>\n";
           make_datetime('start',$start,'Start','',15);
           make_datetime('stop',$stop,'Stop','',15);
           make_checkbox('approved',$approved,'Approved','Check if this shift has been approved');
           make_textarea('notes',$notes,'Notes','Notes about the shift');
           make_hidden('shiftid',$shiftid);
           make_hidden('workerid',$workerid);
           make_submit('submit',$button);
       print "</form>\n";
    } elseif($action=='delete')
    {
       $shiftid=intval($_GET['shiftid']);
       $sql="DELETE FROM temp_shifts WHERE id=$shiftid";
       $dbDelete=dbexecutequery($sql);
       redirect("?action=viewshifts&id=$workerid"); 
    }
}

function save_shift($action)
{
    $shiftid=intval($_POST['shiftid']);
    $workerid=intval($_POST['workerid']);
    $start=$_POST['start'];
    $stop=$_POST['stop'];
    $notes=addslashes($_POST['notes']);
    if($_POST['approved'])
    {
        $approved=1;
        $approvedBy=$_SESSION['cmsuser']['userid'];
        if($action=='update')
        {
            //we need to see if this was already approved prior to this save. We don't want to update approved by in that case
            $sql="SELECT approved FROM temp_shifts WHERE id=$shiftid";
            $dbCheck=dbselectsingle($sql);
            if($dbCheck['data']['approved']==0)
            {
                //ok, it was not previously approved
                $approvedBy=", approved='$approvedBy'";
            } else {
                $approvedBy='';
            }
        }
    } else {
        $approved=0;
        $approvedBy=0;
        if($action=='update')
        {
            $approvedBy='';
        }
    }
    if($start!='' && $stop!='')
    {
        $seconds=strtotime($stop)-strtotime($start);
    } else {
        $seconds=0;
    }
    if($start=='1969-12-31 17:00'){$start="NULL";}else{$start="'$start'";}
    if($stop=='1969-12-31 17:00' || $stop==''){$stop="NULL";}else{$stop="'$stop'";}
    if($action=='insert')
    {
        $sql="INSERT INTO temp_shifts (temp_id, time_in, time_out, seconds, notes, approved, approved_by) 
        VALUES ($workerid, $start, $stop, $seconds, '$notes', '$approved', '$approvedBy')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE temp_shifts SET time_in=$start, time_out=$stop, seconds=$seconds, notes='$notes', approved='$approved'$approvedBy WHERE id=$shiftid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    if ($error!='')
    {
        setUserMessage('There was a problem saving the shift record.<br>'.$error,'error');
    } else {
        setUserMessage('Shift record successfully saved','success');
    }
    redirect("?action=viewshifts&id=$workerid");
}

function view_shifts()
{
    if($_POST['qdate'])
    {
        $qdate=$_POST['qdate'];
        $workerid=$_POST['id'];
    } else {
        $qdate=date("Y-m-d");
        $workerid=intval($_GET['id']);
    }
    //get worker info
    $sql="SELECT * FROM temp_workers WHERE id=$workerid";
    $dbWorker=dbselectsingle($sql);
    $worker=$dbWorker['data'];
    $name=stripslashes($worker['first_name'].' '.$worker['middle_name'].' '.$worker['last_name']);
    $rate=$worker['rate'];
    
    while (date("w",strtotime($qdate))!=1)
    {
        $qdate=date("Y-m-d",strtotime($qdate."-1 day"));
    }
    
    $enddate=date("Y-m-d",strtotime($qdate."+7 days"));
    $weekEndDate=date("Y-m-d",strtotime($qdate."+6 days"));
    
    print "<span style='margin-left:80px;font-size:10px;'>Please note that 'shifts' run until Monday at noon as the 'end of the week' for calculations of shifts starting Sunday nights.</span><br>\n";
    print "<div style='float:left;width:450px;'>\n";
        print "<form method=post>\n";
        make_date('qdate',$qdate,'Select a date','Monday of specified week will be used.');
        make_submit('submit','Change week');
        make_button('hidesalary','Hide Salary',' ',false,'hideSalary();');
        make_hidden('id',$workerid);
        print "</form>\n";
    print "</div>\n";
    
    print "<div style='float:left;width:250px;'>\n";
        print "<p style='margin-top:0'><b>Records for: $name</b></p>";
        print "<p><span class='salary'><b>Hourly Rate: </b>$rate</span></p>";
    print "</div>\n";
    
    print "<div style='float:left;width:250px;'>\n";
        print "<a href='?action=list'>Return to worker list</a>
        <br>
        <a href='?action=addshift&workerid=$workerid'>Add shift for temp worker</a>";
    print "</div><div class='clear'></div>\n";
    
    $sql="SELECT * FROM temp_shifts WHERE temp_id=$workerid AND time_in>='$qdate 00:01' AND time_out<='$enddate 12:00' ORDER BY time_in ASC";
    $dbGroups=dbselectmulti($sql);
    //get the monday of the current week
    $i=0;
    while($dow!='Mon')
    {
        $dow=date("D",strtotime("-$i day"));
        $startMonday=date("Y-m-d",strtotime("-$i day"));
        $i++;   
    }
    if ($dbGroups['numrows']>0)
    {
        print "<table class='report-clean-mango'>\n";
        print "<tr><th>Time In</th><th>Time Out</th><th>Total Time</th><th>Cost</th><th colspan=3>Actions</th></tr>\n";
        foreach($dbGroups['data'] as $group)
        {
            $id=$group['id'];
            $in=$group['time_in'];
            $out=$group['time_out'];
            if($in!='' && $out!='')
            {
                $seconds=strtotime($out)-strtotime($in);
                $totalTime+=$seconds;
                $time=int2TimeDecimal($seconds);
                if($rate>0)
                {
                    $cost=$time*$rate;
                    $cost="\$".round($cost,2);
                    $temp=explode(".",$cost);
                    if(strlen($temp[1])==1){$cost.="0";}
                } else {
                    $cost='No rate';
                }
                
            } else {
                $time='Open shift';
                $cost='N/A';
            }
            //$time.=" sec: $seconds";
            $dayofweek=date("D",strtotime($in));
            $date=date("Y-m-d",strtotime($in));
            $in=date("m/d/Y H:i D",strtotime($in));
            $out=date("m/d/Y H:i D",strtotime($out));
            if($group['approved']){$checked='checked';$label='Unapprove';}else{$checked='';$label='Approve';}
            
            /*
            if($dayofweek=='Mon' && $date!=$startMonday)
            {
                $startMonday=$date;
                print "<tr><td>New week starting $startMonday</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\n";
            }
            */
            print "<tr>";
            print "<td>$in</td>";
            print "<td>$out</td>";
            print "<td>$time</td>";
            print "<td><span class='salary'>$cost</span></td>";
            print "<td><a href='?action=editshift&workerid=$workerid&shiftid=$id'>Edit</a></td>";
            print "<td><input type='checkbox' id='check_$id' name='check_id' $checked onclick='approveTempShift($workerid,$id);' /><label for='check_$id'><span id='label_$id'>$label</span> shift</label></a></td>";
            print "<td><a class='delete' href='?action=deleteshift&workerid=$workerid&shiftid=$id'>Delete</a></td>";
            print "</tr>\n";
        }
        
    }
    if($totalTime!=''){$totalTime=int2TimeDecimal($totalTime);}
    print "<tr><td colspan=7>Total time worked between $qdate and $weekEndDate is: $totalTime hours</td></tr>\n";
    print "</table>\n";
    ?>
    <script type="text/javascript">
    function hideSalary()
    {
        if($('#hidesalary').val()=='Hide Salary')
        {
            $('#hidesalary').val('Show Salary');
            $('.salary').hide(); 
        } else {
           $('#hidesalary').val('Hide Salary');
           $('.salary').show(); 
        }
        
    }
    $('a.delete').click(function() { 
      var a = this; 
       var $dialog = $('<div id="jConfirm"></div>')
        .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This item will be permanently deleted and cannot be recovered. Are you sure?</p>')
        .dialog({
            autoOpen: true,
            title: 'Are you sure you want to Delete?',
            modal: true,
            buttons: {
                Cancel: function() {
                    $( this ).dialog( "close" );
                    return false;
                },
                'Delete': function() {
                    $( this ).dialog( "close" );
                    window.location = a.href;
                }
                
            },
            open: function() {
                $('.ui-dialog-buttonpane > button:last').focus();
            }
       
        });
        return false;
    })
    
    function approveTempShift(workerid,shiftid)
    {
        $.ajax({
          url: "includes/ajax_handlers/tempWorkers.php",
          type: "POST",
          data: ({action:'approve',workerid:workerid,shiftid:shiftid}),
          dataType: "json",
          success: function(response){
            if(response.status=='success')
            {
                if(response.approved==1)
                {
                   $('#label_'+shiftid).html('Unapprove'); 
                } else {
                   $('#label_'+shiftid).html('Approve');  
                }
                
            } else {
                alertMessage(response.message,'error');
            }  
          }
        }) 
    }
    </script>
    <?php
        
}  
footer();
?>