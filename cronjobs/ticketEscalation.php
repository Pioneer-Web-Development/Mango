<?php
  if($_GET['mode']=='test')
  {
      include("../includes/functions_db.php");
      include("../includes/config.php");
      include("../includes/functions_common.php");
      include("../includes/mail/htmlMimeMail.php");
      escalateTicket();
  }
 
function escalateTicket()
{
  print "Beginning process...<br>\n";
  global $info;
    /***************************************************************
  * 
  * THIS SCRIPT IS DESIGNED TO TAKE CARE OF GRACEFULLY
  * AGING MAINTENANCE AND TROUBLE TICKETS BY ESCALATING THEM 
  * AT THE GIVEN ESCALATION POINT TO THE NEXT HIGHEST PRIORITY
  * AND TO ALERT THE OWNER OF THE HELPDESK TOPIC
  */
  //do the setup pieces
  //get the owner email address of each helpdesk type
  $sql="SELECT A.id, B.group_email FROM helpdesk_types A, user_groups B WHERE A.group_responsible=B.id";
  $dbGroups=dbselectmulti($sql);
  $owners[0]='tech@idahopress.com';
  if ($dbGroups['numrows']>0)
  {
      foreach($dbGroups['data'] as $group)
      {
          $owners[$group['id']]=$group['group_email'];
      }
  } 
  
  //first, lets pull in the helpdesk priorities
  $sql="SELECT * FROM helpdesk_priorities ORDER BY priority_order";
  $dbPriorities=dbselectmulti($sql);
  $prioritynames=array();
  //loop through the priorities and see if there are any helpdesk tickets that meet the criteria
  $totalhours=0; //this is a running total of the number of hours from the submission time
  global $helpdeskCompleteStatus,$helpdeskHoldStatus;
  $plevels=array();
  $i=0;
  foreach($dbPriorities['data'] as $priority)
  {
      $highest=$priority['id']; //this will end up holding the highest priority, which we can compare against later
      $plevels[$i]=$highest;
      $prioritynames[$i]=$priority['priority_name'];
      $i++;
  }
  $j=0;
  $processed=array();
  foreach($dbPriorities['data'] as $priority)
  {
    $pid=$priority['id'];
    $ptime=$priority['priority_threshold'];
    $totalhours+=$ptime;
    if ($pid==$highest)
    {
        
        $resending="AND NOW()>(addtime(last_email_time,INTERVAL $GLOBALS[resendRateHighestTicket] MINUTE))";
    } else {
        $resending="";
    }
    $sendtime=date("Y-m-d H:i:s");
    //we'll want to see any tickets that match the threshold and are past the hours threshold
    //we'll then update the ticket to the new threshold and send an email to the owner of that ticket type
    $sql="SELECT * FROM helpdesk_tickets WHERE status_id<>$helpdeskCompleteStatus AND priority_id=$pid AND NOW()>(addtime(submitted_datetime,'$totalhours:00:00')) $resending LIMIT 100";
    $dbTickets=dbselectmulti($sql);
    if ($dbTickets['numrows']>0)
    {
        foreach($dbTickets['data'] as $ticket)
        {
            //update the ticket to the next higher priority, if there is a higher one
            if(!in_array($ticket['id'],$processed))
            {
                if ($j<$i)
                {
                    $higherid=$plevels[$j+1];
                    if($higherid==''){$higherid=$plevels[$j];}
                    if($higherid==end($plevels)){$maxed=true;}else{$maxed=false;}
                    if($_GET['mode']=='test')
                    {
                        print "Found a ticket #$ticket[id] that is being upgraded to $higherid.<br>";
                    } else {
                        $info.="Found a ticket #$ticket[id] that is being upgraded to $higherid.";
                    }
                    $sql="UPDATE helpdesk_tickets SET priority_id=$higherid, last_email_time='$sendtime' WHERE id=$ticket[id]";
                    $dbUpdate=dbexecutequery($sql);
                    //now send the email to the owner
                    send_ticket_message($owners[$ticket['type_id']],$ticket,$prioritynames[$j+1],'helpdesk',$maxed);
                    $processed[]=$ticket['id'];
                }    
            }        
        }    
    } else {
        if($_GET['mode']=='test')
        {
            print "No helpdesk tickets found with $sql<br>";
        } else {
            $info.="No helpdesk tickets found with $sql";
        }
    }
    $processed=array();
    $sql="SELECT * FROM maintenance_tickets WHERE status_id<>$helpdeskCompleteStatus AND status_id<>$helpdeskHoldStatus AND priority_id=$pid AND NOW()>(addtime(submitted_datetime,'$totalhours:00:00')) $resending LIMIT 100";
    $dbTickets=dbselectmulti($sql);
    if ($dbTickets['numrows']>0)
    {
        foreach($dbTickets['data'] as $ticket)
        {
            //update the ticket to the next higher priority, if there is a higher one
            if(!in_array($ticket['id'],$processed))
            {
                if ($j<$i)
                {
                    $higherid=$plevels[$j+1];
                    if($higherid==''){$higherid=$plevels[$j];}
                    if($higherid==end($plevels)){$maxed=true;}else{$maxed=false;}
                    if($_GET['mode']=='test')
                    {
                        print "Found a ticket# $ticket[id] that is being upgraded to $higherid.<br>";
                    } else {
                        $info.="Found a ticket# $ticket[id] that is being upgraded to $higherid.";
                    }
                    $sql="UPDATE maintenance_tickets SET priority_id=$higherid, last_email_time='$sendtime' WHERE id=$ticket[id]";
                    $dbUpdate=dbexecutequery($sql);
                    //now send the email to the owner
                    send_ticket_message($owners[$ticket['type_id']],$ticket,$prioritynames[$j+1],'maintenance',$maxed);
                    $processed[]=$ticket['id'];
                }    
            }
        }    
    } else {
        if($_GET['mode']=='test')
        {
            print "<br />No maintenance tickets found with $sql<br>";
        } else {
            $info.="<br />No maintenance tickets found with $sql<br />";
        }
    }
    
    $j++;   
  }
  print "Ending process...<br>\n";
    
}
     
?>
