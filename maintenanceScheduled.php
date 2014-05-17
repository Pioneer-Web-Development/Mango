<?php
//this script is to handle scheduled maintenance tasks for press, inserter, stitchers
include("includes/mainmenu.php");
  
 
if($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
 
switch($action)
{
    case "add":
        maintenance('add');
    break;
    
    case "edit":
        maintenance('edit');
    break;
    
    case "delete":
        maintenance('delete');
    break;
    
    case "Save Maintenance":
    save_maintenance('insert');
    break;
    
    case "Update Maintenance":
        save_maintenance('update');
    break;
    
    
    case "addticket":
        tickets('add');
    break;
    
    case "editticket":
        tickets('edit');
    break;
    
    case "deleteticket":
        tickets('delete');
    break;
    
    case "listtickets":
        tickets('list');
    break;
    
    case "Add Ticket":
    save_ticket('insert');
    break;
    
    case "Change Ticket":
        save_ticket('update');
    break;
    
    case "addunit":
        units('add');
    break;
    
    case "editunit":
        units('edit');
    break;
    
    case "deleteunit":
        units('delete');
    break;
    
    case "listunits":
        units('list');
    break;
    
    case "Add Unit":
    save_unit('insert');
    break;
    
    case "Change Unit":
        save_unit('update');
    break;
    
    default:
        maintenance('list');
    break;
     
} 


function maintenance($action)
{
    $presses=array();
    $presses[0]="Select a press";
    $sql="SELECT * FROM press";
    $db=dbselectmulti($sql);
    if ($db['numrows']>0)
    {
        foreach($db['data'] as $item)
        {
            $presses[$item['id']]=$item['name'];
        }
    }
    $inserters=array();
    $inserters[0]="Select an inserter";
    $sql="SELECT * FROM inserters";
    $db=dbselectmulti($sql);
    if ($db['numrows']>0)
    {
        foreach($db['data'] as $item)
        {
            $inserters[$item['id']]=$item['inserter_name'];
        }
    }
    $stitchers=array();
    $stitchers[0]='Select a stitcher';
    $sql="SELECT * FROM stitchers";
    $db=dbselectmulti($sql);
    if ($db['numrows']>0)
    {
        foreach($db['data'] as $item)
        {
            $stitchers[$item['id']]=$item['stitcher_name'];
        }
    }
    
    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Save Maintenance';
            $pressid=0;
            $inserterid=0;
            $stitcherid=0;
            
            $start=date("Y-m-d")." 8:00";
            $end=date("Y-m-d")." 10:00";
            $total_time=0;
            $total_employees=0;
        } else {
            $scheduleid=intval($_GET['scheduleid']);
            $button='Update Maintenance';
            $sql="SELECT * FROM maintenance_scheduled WHERE id=$scheduleid";
            $dbJob=dbselectsingle($sql);
            $job=$dbJob['data'];
            
            $type=$job['equipment_type'];
            switch ($type)
            {
                case "press":
                 $pressid=$job['equipment_id'];
                 $inserterid=0;
                 $stitcherid=0;
                break;
                
                case "inserter":
                 $inserterid=$job['equipment_id'];
                 $pressid=0;
                 $stitcherid=0;
                break;
                
                case "stitcher":
                 $stitcherid=$job['equipment_id'];
                 $inserterid=0;
                 $pressid=0;
                break;
            }
            $start=$job['starttime'];
            $end=$job['endtime'];
            $goals=stripslashes($job['goals']);
            $notes=stripslashes($job['notes']);
            $total_time=$job['total_time'];
            $total_employees=$job['total_employees'];
            
        }
        print "<form method=post>\n";
        print "<div class='label'>Select equipment</div><div class='input'><small>Please choose only one, the first (left to right) will be selected if multiple are chosen</small><br>\n";
        print "Press: ".make_select('press_id',$presses[$pressid],$presses)."&nbsp;&nbsp;&nbsp;&nbsp;";
        print "Inserter: ".make_select('inserter_id',$inserters[$inserterid],$inserters)."&nbsp;&nbsp;&nbsp;&nbsp;";
        print "Stitcher: ".make_select('stitcher_id',$stitchers[$stitcherid],$stitchers);
        print "</div><div class='clear'></div>\n";
        make_datetime('start',$start,'Maintenance Start Time');
        make_datetime('end',$end,'Maintenance End Time');
        make_number('total_time',$total_time,'Total Time','Total time actually spent (people times elapsed time) on this maintenance');
        make_number('total_employees',$total_employees,'Total Employees','Total number of people who assisted on this task');
        make_textarea('goals',$goals,'Goals','Goals for this scheduled maintenance','60','10');  
        make_textarea('notes',$notes,'Notes','Any notes for after the task(s) have been completed','60','10');  
        make_hidden('scheduleid',$scheduleid);
        make_submit('submit',$button);
        print "</form>\n";
        
    } elseif ($action=='delete')
    {
        $scheduleid=intval($_GET['scheduleid']);
        $sql="DELETE FROM maintenance_scheduled WHERE id=$scheduleid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the scheduled maintenance task.<br>'.$error,'error');
        } else {
            $sql="DELETE FROM maintenance_scheduled_tickets WHERE schedule_id=$scheduleid";
            $dbDelete=dbexecutequery($sql);
            $sql="DELETE FROM maintenance_scheduled_units WHERE schedule_id=$scheduleid";
            $dbDelete=dbexecutequery($sql);
        
            setUserMessage('The scheduled maintenance task has been successfully deleted','success');
        }
        redirect("?action=list");
    } else {
        global $pubids;
        $sql="SELECT * FROM maintenance_scheduled ORDER BY starttime DESC";
        $dbMaintenance=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new scheduled maintenance</a>","Equipment,Start,End",7);
        if ($dbMaintenance['numrows']>0)
        {
            foreach($dbMaintenance['data']as $job)
            {
                $id=$job['id'];
                $type=$job['equipment_type'];
                switch ($type)
                {
                    case "press":
                     $equipment=$presses[$job['equipment_id']];
                    break;
                    
                    case "inserter":
                     $equipment=$inserters[$job['equipment_id']];
                    break;
                    
                    case "stitcher":
                     $equipment=$stitchers[$job['equipment_id']];
                    break;
                }
                $start=date("m/d/Y H:i",strtotime($job['starttime']));
                $end=date("m/d/Y H:i",strtotime($job['endtime']));
                print "<tr>\n";
                print "<td>$equipment</td>\n";
                print "<td>$start</td>\n";
                print "<td>$end</td>\n";
                print "<td><a href='?action=edit&scheduleid=$id'>Edit</a></td>\n";
                print "<td><a href='?action=listtickets&scheduleid=$id'>Tickets</a></td>\n";
                print "<td><a href='?action=listunits&scheduleid=$id'>Affected Units</a></td>\n";
                print "<td><a class='delete' href='?action=delete&scheduleid=$id'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbMaintenance);
    }
}

function save_maintenance($action)
{
    $scheduleid=$_POST['scheduleid'];
    $start=$_POST['start'];
    $end=$_POST['end'];
    $total_time=$_POST['total_time'];
    $total_employees=$_POST['total_employees'];
    $goals=addslashes($_POST['goals']);
    $notes=addslashes($_POST['notes']);
    if($_POST['stitcher_id']!=0)
    {
        $equipmentid=$_POST['stitcher_id'];
        $equipmenttype='stitcher';
    }
    if($_POST['inserter_id']!=0)
    {
        $equipmentid=$_POST['inserter_id'];
        $equipmenttype='inserter';
    }
    if($_POST['press_id']!=0)
    {
        $equipmentid=$_POST['press_id'];
        $equipmenttype='press';
    }
    if ($action=='insert')
    {
        $sql="INSERT INTO maintenance_scheduled (starttime, endtime, equipment_type, equipment_id, goals, notes, total_time, total_employees) VALUES 
        ('$start', '$end', '$equipmenttype', '$equipmentid', '$goals', '$notes', '$total_time', '$total_employees')";
        $dbInsert=dbinsertquery($sql);
        $scheduleid=$dbInsert['insertid'];
        $error=$dbInsert['error'];
    } else {
        //update
        $sql="UPDATE maintenance_scheduled SET starttime='$start', endtime='$end', equipment_type='$equipmenttype', equipment_id='$equipmentid', 
        goals='$goals', notes='$notes', total_time='$total_time', total_employees='$total_employees' WHERE id=$scheduleid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    
    //clear any cached calendar files
    clearCache('presscalendar');
    
    
    if ($error!='')
    {
        setUserMessage('There was a problem adding the scheduled maintenance job.<br>'.$error,'error');
    } else {
        setUserMessage('The scheduled maintenance task has been successfully added.','success');
    }
    redirect("?action=list");
    
}


function tickets($action)
{
    $scheduleid=intval($_GET['scheduleid']);
    if($action=='add' || $action=='edit')
    {
        $completed=$GLOBALS['helpdeskCompleteStatus'];
        $sql="SELECT * FROM maintenance_tickets WHERE status_id<$completed";
        $dbTickets=dbselectmulti($sql);
        $tickets[0]='Please select';
        if($dbTickets['numrows']>0)
        {
            foreach($dbTickets['data'] as $ticket)
            {
                $tickets[$ticket['id']]=substr(stripslashes($ticket['problem']),0,80);    
            }
        }
        if($action=='add')
        {
            $button='Add Ticket';
        } else {
            $button='Change Ticket';
            $id=intval($_GET['id']);
            $sql="SELECT * FROM maintenance_scheduled_tickets WHERE id=$id";
            $dbTicket=dbselectsingle($sql);
            $ticketid=$dbTicket['data']['ticket_id'];
             
        }
        print "<form method=post>\n";
        make_select('ticket_id',$tickets[$ticketid],$tickets,'Ticket','Select an open ticket to be worked on during this scheduled maintenance period');
        make_hidden('scheduleid',$scheduleid);
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } else if ($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="DELETE FROM maintenance_scheduled_tickets WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the ticket.<br>'.$error,'error');
        } else {
            setUserMessage('The ticket has been successfully removed.','success');
        }
        redirect("?action=listtickets&scheduleid=$scheduleid"); 
    } else {
        $sql="SELECT A.*, B.problem FROM maintenance_scheduled_tickets A, maintenance_tickets B WHERE A.schedule_id=$scheduleid AND A.ticket_id=B.id";
        $dbTickets=dbselectmulti($sql);
        tableStart("<a href='?action=list'>Return to schedule maintenance list</a>,<a href='?action=addticket&scheduleid=$scheduleid'>Add new ticket</a>","Ticket Brief",3);
        if($dbTickets['numrows']>0)
        {
            foreach($dbTickets['data'] as $ticket)
            {
                $problem=stripslashes($ticket['problem']);
                $id=$ticket['id'];
                print "<tr>\n";
                print "<td>".wordwrap($problem,120,'<br>',true)."</td>\n";
                print "<td><a href='?action=editticket&scheduleid=$scheduleid&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=deleteticket&scheduleid=$scheduleid&id=$id' class='delete'>Delete</a></td>\n";
                print "</tr>\n";
            }    
        }
        tableEnd($dbTickets);
    }
}

function save_ticket($action)
{
    $ticketid=$_POST['ticket_id'];
    $id=$_POST['id'];
    $scheduleid=$_POST['scheduleid'];
    
    if($action=='insert')
    {
        $sql="INSERT INTO maintenance_scheduled_tickets (schedule_id, ticket_id) VALUES ('$scheduleid', '$ticketid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE maintenance_scheduled_tickets SET ticket_id=$ticketid WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    if ($error!='')
    {
        setUserMessage('There was a problem adding the ticket.<br>'.$error,'error');
    } else {
        setUserMessage('The ticket has been successfully added to the scheduled maintenance window.','success');
    }
    redirect("?action=listtickets&scheduleid=$scheduleid");
}


function units($action)
{
    $scheduleid=intval($_GET['scheduleid']);
    
    $sql="SELECT * FROM maintenance_scheduled WHERE id=$scheduleid";
    $dbSchedule=dbselectsingle($sql);
    $schedule=$dbSchedule['data'];
    $type=$schedule['equipment_type'];
    $equipid=$schedule['equipment_id'];
    switch($type)
    {
        case "press":
            $sql="SELECT * FROM press_towers WHERE press_id=$equipid ORDER BY tower_order";
            $dbUnits=dbselectmulti($sql);
            if($dbUnits['numrows']>0)
            {
                $units[0]='Please select';
                foreach($dbUnits['data'] as $unit)
                {
                    $units[$unit['id']]=stripslashes($unit['tower_name']);
                }
            } else {
                $units[0]='None defined';
            }
        break;
        
        case "inserter":
            $sql="SELECT * FROM inserters_hoppers WHERE inserter_id=$equipid ORDER BY hopper_number";
            $dbUnits=dbselectmulti($sql);
            if($dbUnits['numrows']>0)
            {
                $units[0]='Please select';
                foreach($dbUnits['data'] as $unit)
                {
                    $units[$unit['id']]='Station #'.stripslashes($unit['hopper_number']);
                }
            } else {
                $units[0]='None defined';
            }
        break;
        
        case "stitcher":
            $sql="SELECT * FROM stitchers_hoppers WHERE inserter_id=$equipid ORDER BY hopper_number";
            $dbUnits=dbselectmulti($sql);
            if($dbUnits['numrows']>0)
            {
                $units[0]='Please select';
                foreach($dbUnits['data'] as $unit)
                {
                    $units[$unit['id']]='Station #'.stripslashes($unit['hopper_number']);
                }
            } else {
                $units[0]='None defined';
            }
        break;
    }
    
    if($action=='add' || $action=='edit')
    {
        //look up the equipment type and id from the maintenance scheule table
        
        
        if($action=='add')
        {
            $button='Add Unit';
            $unitid=0;
        } else {
            $button='Change Unit';
            $id=intval($_GET['id']);
            $sql="SELECT * FROM maintenance_scheduled_units WHERE id=$id";
            $dbTicket=dbselectsingle($sql);
            $unitid=$dbTicket['data']['unit_id'];
             
        }
        print "<form method=post>\n";
        make_select('unit_id',$units[$unitid],$units,'Unit','Select the equipment component that will be worked on during this scheduled maintenance.');
        make_hidden('scheduleid',$scheduleid);
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } else if ($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="DELETE FROM maintenance_scheduled_units WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the unit.<br>'.$error,'error');
        } else {
            setUserMessage('The unit has been successfully removed.','success');
        }
        redirect("?action=listunitss&scheduleid=$scheduleid"); 
    } else {
        $sql="SELECT * FROM maintenance_scheduled_units WHERE schedule_id=$scheduleid";
        $dbTickets=dbselectmulti($sql);
        tableStart("<a href='?action=list'>Return to schedule maintenance list</a>,<a href='?action=addunit&scheduleid=$scheduleid'>Add new unit</a>","Unit",3);
        if($dbTickets['numrows']>0)
        {
            foreach($dbTickets['data'] as $ticket)
            {
                $unit=$units[$ticket['unit_id']];
                $id=$ticket['id'];
                print "<tr>\n";
                print "<td>$unit</td>\n";
                print "<td><a href='?action=editunit&scheduleid=$scheduleid&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=deleteunit&scheduleid=$scheduleid&id=$id' class='delete'>Delete</a></td>\n";
                print "</tr>\n";
            }    
        }
        tableEnd($dbTickets);
    }
}

function save_unit($action)
{
    $unitid=$_POST['unit_id'];
    $id=$_POST['id'];
    $scheduleid=$_POST['scheduleid'];
    
    if($action=='insert')
    {
        $sql="INSERT INTO maintenance_scheduled_units (schedule_id, unit_id) VALUES ('$scheduleid', '$unitid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE maintenance_scheduled_units SET unit_id=$unitid WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    if ($error!='')
    {
        setUserMessage('There was a problem adding the unit.<br>'.$error,'error');
    } else {
        setUserMessage('The unit has been successfully added to the scheduled maintenance window.','success');
    }
    redirect("?action=listunits&scheduleid=$scheduleid");
}

footer(); 
?>
