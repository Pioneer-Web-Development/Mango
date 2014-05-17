<?php
  include("includes/mainmenu.php");
  
  
  
  if($_POST)
  {
      process_form();
  } else {
      show_form();
  }
  
  
  function show_form()
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
    
      
      
    $id=intval($_GET['ticketid']);
    $sql="SELECT * FROM maintenance_scheduled WHERE id=$id";
    $dbMaintenance=dbselectsingle($sql);
    $maintenance=$dbMaintenance['data'];
    
    $equipType=$maintenance['equipment_type'];
    $equipID=$maintenance['equipment_id'];
    $goals=stripslashes($maintenance['goals']);
    $notes=stripslashes($maintenance['notes']);
    $total_time=$maintenance['total_time'];
    $total_employees=$maintenance['total_employees'];
    switch ($equipType)
    {
        case "press":
         $equipment=$presses[$equipID];
        break;
        
        case "inserter":
         $equipment=$inserters[$equipID];
        break;
        
        case "stitcher":
         $equipment=$stitchers[$equipID];
        break;
    }
    print "<form method=post>\n";
    
    print "<div id='tabs'>\n";
    print "<ul>\n";
    print "<li><a href='#basic'>Basic Details</a></li>\n";
    print "<li><a href='#tickets'>Maintenance Tickets</a></li>\n";
    print "<li><a href='#pmtasks'>Open PM Tasks</a></li>\n";
    print "<li><a href='#units'>Units Affected</a></li>\n";
    print "</ul>\n";
    print "<div id='basic'>\n";
        print "<div class='label'>Goals</div><div class='input'><p>The following was laid out as the goal of this scheduled maintenance on $equipment</p>\n";
        print $goals;
        print "</div><div class='clear'></div>\n";
        make_number('time',$total_time,'Time Spent','Total person-hours spent on this task (people times time spent in minutes)');
        make_number('employees',$total_employees,'Employees','How many employees were involved?');
        make_textarea('notes',$notes,'Notes','Any notes about the scheduled maintenance?',60,10);
    print "</div>\n";
    
    print "<div id='tickets'>\n";
        $sql="SELECT B.* FROM maintenance_scheduled_tickets A, maintenance_tickets B WHERE A.schedule_id=$id AND A.ticket_id=B.id";
        $dbTickets=dbselectmulti($sql);
        if($dbTickets['numrows']>0)
        {
            global $helpdeskCompleteStatus;
            print "<div style='width:100%;height:600px;overflow-y:auto;'>\n";
            foreach($dbTickets['data'] as $ticket)
            {
                if($ticket['status_id']==$helpdeskCompleteStatus)
                {
                    $bcolor='#99FF99'; 
                } else {
                    $bcolor='#fff';
                }
                print "<div id='ticket$ticket[id]' style='width:100%;padding:10px;margin-bottom:10px;border-bottom: thin solid black;background-color:$bcolor'>\n";
                print "<b>Problem:</b><br>";
                print stripslashes($ticket['problem'])."<br>";
                print "<b>Attempted Resolution:</b><br>";
                print stripslashes($ticket['attempt'])."<br>";
                if($ticket['status_id']!=$helpdeskCompleteStatus)
                {
                    print "<input id='ticketButton$ticket[id]' type=button value='Close ticket' onClick='closeTicket($ticket[id])' />\n";
                }
                print "</div>\n";    
            }
            print "</div>\n";
        }    
    print "</div>\n";
    
    print "<div id='pmtasks'>\n";
    $sql="SELECT A.*, B.component_id FROM equipment_pm A, equipment_pm_xref B 
               WHERE A.id=B.pm_id AND B.equipment_id='$equipmentid' 
               AND B.equipment_type='$pressunittype'";
    
    print "</div>\n";
    
    print "<div id='units'>\n";
        switch($equipType)
        {
            case "press":
                $sql="SELECT * FROM press_towers WHERE press_id=$equipID ORDER BY tower_order";
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
                $sql="SELECT * FROM inserters_hoppers WHERE inserter_id=$equipID ORDER BY hopper_number";
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
                $sql="SELECT * FROM stitchers_hoppers WHERE inserter_id=$equipID ORDER BY hopper_number";
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
        $sql="SELECT * FROM maintenance_scheduled_units WHERE schedule_id=$id";
        $dbUnits=dbselectmulti($sql);
        if($dbUnits['numrows']>0)
        {
            print "<b>The following units were indicated for this scheduled maintenance:</b><br>";
            foreach($dbUnits['data'] as $unit)
            {
                print $units[$unit['unit_id']]."<br>";       
            }
        } else {
            print "No specific units were indicated for this scheduled maintenance.";
        }
    print "</div>\n";
    
    
    make_hidden('scheduleid',$id);
    make_submit('submit','Save');
    print "</div>\n"; 
    print "</form>\n";
    ?>
    <script type='text/javascript'>
        $('#tabs').tabs();
        
        function closeTicket(ticketid)
        {
            $.ajax({
               url: 'includes/ajax_handlers/maintenanceScheduledTicketHandler.php',
               type: "POST",
               data: {type:'closeticket',ticketid:ticketid},
               dataType: 'json',
               success: function(response) {
                   if(response.status=='success')
                   {
                      $('#ticket'+ticketid).css('background-color','#99FF99');
                      $('#ticketButton'+ticketid).hide();   
                   }
               }
            });
            
        }
    </script>
    <?php             
  }
  
  
  function process_form()
  {
      
      $scheduleid=$_POST['scheduleid'];
      $notes=addslashes($_POST['notes']);
      $time=addslashes($_POST['time']);
      $employees=addslashes($_POST['employees']);
      $sql="UPDATE maintenance_scheduled SET total_time='$time', total_employees='$employees', notes='$notes' WHERE id=$scheduleid";
      $dbUpdate=dbexecutequery($sql);
      dbclose();
      ?>
      <script>
      window.close();
      </script>
      <?php
              
  }
  footer();
?>         