<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;
$helpStatuses=array();
$sql="SELECT * FROM helpdesk_statuses WHERE site_id=$siteID ORDER BY status_order";
$dbStatuses=dbselectmulti($sql);
if ($dbStatuses['numrows']>0)
{
  foreach($dbStatuses['data'] as $status)
  {
      $helpStatuses[$status['id']]=$status['status_name'];
  }
} else {
  $helpStatuses[0]="None set!";
}

$helpPriorities=array();
$sql="SELECT * FROM helpdesk_priorities WHERE site_id=$siteID ORDER BY priority_order";
$dbPriorities=dbselectmulti($sql);
if ($dbPriorities['numrows']>0)
{
  foreach($dbPriorities['data'] as $priority)
  {
      $helpPriorities[$priority['id']]=$priority['priority_name'];
  }
} else {
  $helpPriorities[0]=="None set!";
}

$helpTypes=array();
$sql="SELECT * FROM helpdesk_types WHERE site_id=$siteID AND production_specific=1 ORDER BY type_name";
$dbTypes=dbselectmulti($sql);
if ($dbTypes['numrows']>0)
{
  foreach($dbTypes['data'] as $type)
  {
      $helpTypes[$type['id']]=$type['type_name'];
  }
} else {
  $helpTypes[0]=="None set!";
}

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
    switch ($action)
    {
        case "Update Ticket":
        save_ticket('update');
        break;
        
        case "edit":
        setup_ticket('edit');
        break;
        
        case "delete":
        setup_ticket('delete');
        break;
        
        case "list":
        setup_ticket('list');
        break;
        
        default:
        setup_ticket('list');
        break;
        
    } 
    
    
function setup_ticket($action)
{
    global $siteID, $helpPriorities, $helpStatuses, $helpTypes, $helpdeskCompleteStatus;
    if ($action=='edit')
    {
        $button="Update Ticket";
        $id=intval($_GET['id']);
        $sql="SELECT * FROM maintenance_tickets WHERE id=$id";
        $dbTicket=dbselectsingle($sql);
        $ticket=$dbTicket['data'];
        $nameid=$ticket['submitted_by'];
        $submittime=date("m/d/Y @ H:i",strtotime($ticket['submitted_datetime']));
        $status=$ticket['status_id'];
        $priority=$ticket['priority_id'];
        $type=$ticket['type_id'];
        $problem=$ticket['problem'];
        $attempt=$ticket['attempt'];
        $completed_by=$ticket['completed_by'];
        $completed_time=$ticket['completed_time'];
        
        $sql="SELECT firstname, lastname, email FROM users WHERE id=$nameid";
        $dbPerson=dbselectsingle($sql);
        $submittedby=$dbPerson['data']['firstname']." ".$dbPerson['data']['lastname'];
        
        $sql="SELECT firstname, lastname, email FROM users WHERE id=$completed_by";
        $dbPerson=dbselectsingle($sql);
        $completed_by=$dbPerson['data']['firstname']." ".$dbPerson['data']['lastname'];
        
        $sql="SELECT firstname, lastname, email FROM users WHERE id=$updated_by";
        $dbPerson=dbselectsingle($sql);
        $updated_by=$dbPerson['data']['firstname']." ".$dbPerson['data']['lastname'];
        
        if ($ticket['updated_by']!=0)
        {
            $updated_by=$ticket['updated_by'];
            $updated_time=date("m/d/Y @ H:i",strtotime($ticket['updated_datetime']));
            $updated=" It was updated by $updated_by on $updated_time.";
        } else {
            $updated="";
        }
        
        //see if there is solution for this ticket
        $sql="SELECT * FROM maintenance_solutions WHERE ticket_id=$id";
        $dbSolution=dbselectsingle($sql);
        if ($dbSolution['numrows']>0)
        {
            $solution=$dbSolution['data'];
            $keywords=stripslashes($solution['keywords']);
            $solutionfull=stripslashes($solution['solution_text']);
            $solutionbrief=stripslashes($solution['solution_brief']);
            $title=stripslashes($solution['title']);
            $public=stripslashes($solution['public']);
            $solutionid=$solution['id'];
        } else {
            $keywords="";
            $solutiontext="";
            $solutionbrief="";
            $title="";
            $public=0;
            $solutionid=0;
        }
        print "<form method=post>\n";
        
        print "<div id='tabs'>\n";
            print "<ul>\n";
                print "<li><a href='#report'>Report and Notes</a></li>\n";
                print "<li><a href='#final'>Final Solution</a></li>\n";
            print "</ul>\n";
        
        print "<div id='report'>\n";
            //lets just display the ticket information above
            print "<div class='label'>&nbsp;</div><div class='input'>\n";
            print "<h3>The following $types[$type] ticket was submitted by $submittedby on $submittime.$updated</h3>\n";
            if ($image!=''){$left='400px';}else{$left='600px';}
            print "<div style='float:left;width:$left;'>\n";
                print "<p>Issue description: <br />\n";
                print stripslashes($problem);
                print "</p>\n";
                print "<p>Attempted Solution: <br />\n";
                print stripslashes($attempt);
                print "</p>\n";
            print "</div>\n";
            if ($image!='')
            {
                print "<div style='float:left;width:110px;margin-left:10px;'>\n";
                    print "<small>Attached image</small><br />\n";
                    print "<img src='$image' border=0 width=300></a><br />\n";
                print "</div>\n";
            }
            print "</div>\n";
            print "<div class='clear'></div>\n";
            make_select('priority',$helpPriorities[$priority],$helpPriorities,'Current Priority');
            make_select('status',$helpStatuses[$status],$helpStatuses,'Current Status');
            
            
            $users=array();
            $sql="SELECT firstname, lastname, id FROM users ORDER BY lastname";
            $dbUsers=dbselectmulti($sql);
            if($dbUsers['numrows']>0)
            {
                foreach($dbUsers['data'] as $user)
                {
                    $users[$user['id']]=stripslashes($user['firstname'].' '.$user['lastname']);
                }
            }
            //find any existing notes for this ticket, and allow creation of a new one
            $sql="SELECT * FROM maintenance_tickets_notes WHERE ticket_id=$id";
            $dbNotes=dbselectmulti($sql);
            if($dbNotes['numrows']>0)
            {
                print "<div class='label'>Notes</div><div class='input'>";
                foreach($dbNotes['data'] as $note)
                {
                    print "<div style='border:thin solid black;padding:4px;margin-bottom:4px;background-color:white;width:500px;'>\n";
                    print "<span style='font-size:10px;font-style:italic;'>Submitted by ".$users[$note['submitted_by']].' at '.date("m/d/Y H:s",strtotime($note['created_datetime']))."</span><br>";
                    print stripslashes($note['note']);
                    print "</div>\n";
                }
                print "</div><div class='clear'></div>\n";
            }
            make_textarea('newnote','','Add note','Add another note to this ticket',70,8);
        print "</div><!-- closing report -->\n";
        
        print "<div id='final'>\n";
            make_text('title',$title,'Title','Provide a clear title for this solution',50);
            make_text('keywords',$keywords,'Keywords','Provide keywords to help find this solution in the future.',50);
            make_textarea('solution_brief',$solutionbrief,'Brief Solution','Provide a brief summary of the solution.',70,10);
            make_textarea('solution_full',$solutionfull,'Solution','Provide full solution. Please note that you can access the same solution, and add images by using the Maintenance Solutions<br />menu option. This step merely creates a connection between the initial ticket and the final solution.',70,20);
            make_checkbox('public',$public,'Public','Make the solution to this ticket public.');
        print "</div><!--closing final -->\n";
        print "</div><!--closing the tab holder -->\n";
        
        make_hidden('ticketid',$id);
        make_hidden('solutionid',$solutionid);
        make_submit('submit',$button);
        print "</form>\n";
        
        ?>
    <script type='text/javascript'>
   $(function() {
        $( '#tabs' ).tabs();
    });
    </script>
    <?php  
    } elseif($action=='delete') {
        $id=intval($_GET['id']);
        $sql="DELETE FROM maintenance_tickets WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the ticket.<br />'.$error,'error');
        } else {
            setUserMessage('The ticket has been successfully deleted.','success');
        }
        redirect("?action=list");
    } else {
        
        $search="<form method=post>";
        if($_POST['show_completed']){$showcompleted='checked';}
        $search.="<input type=checkbox name='show_completed' $showcompleted><label for='show_completed'>Include completed ticket</label><br>";
        
        $search.="Show tickets for:<br>";
        $helpTypes=array_unshift_assoc($helpTypes,'0','Show All');
        $helpStatuses=array_unshift_assoc($helpStatuses,'0','Show All');
        $helpPriorities=array_unshift_assoc($helpPriorities,'0','Show All');
        $search.=make_select('type',$helpTypes[$_POST['type']],$helpTypes);
        $search.="<br>Show tickets with status of:<br>";
        $search.=make_select('status',$helpStatuses[$_POST['status']],$helpStatuses);
        $search.="<br>Show tickets with priority of:<br>";
        $search.=make_select('priority',$helpPriorities[$_POST['priority']],$helpPriorities);
        $search.="<br><input type='submit' name='submit' value='Search' class='button'>";
        $search.="</form>\n";
        
        if($_POST['submit']=='Search')
        {
            if($_POST['status']!=0)
            {
                $status="status_id='$_POST[status]'";
            } else {
                if($_POST['show_completed']){
                    $status="status_id>0";
                } else {
                    $status="status_id<>'$helpdeskCompleteStatus'";
                }
            }
            if($_POST['priority']!=0)
            {
                $priority="AND priority_id='$_POST[priority]'";
            }
            if($_POST['type']!=0)
            {
                $type="AND type_id='$_POST[type]'";
            }
        } else {
            $status="status_id<>'$helpdeskCompleteStatus'";
        }
        
        $sql="SELECT * FROM maintenance_tickets WHERE $status $priority $type ORDER BY submitted_datetime DESC";
        
        $dbTickets=dbselectmulti($sql);
        tableStart("None","Type,Priority,Status,Brief,Submitted",8,$search);
        if ($dbTickets['numrows']>0)
        {
            foreach($dbTickets['data'] as $ticket)
            {
                $id=$ticket['id'];
                $submitted_by=$ticket['submitted_by'];
                $sql="SELECT * FROM users WHERE id=$submitted_by";
                $dbPerson=dbselectsingle($sql);
                $submittedby=$dbPerson['data']['firstname']." ".$dbPerson['data']['lastname'];
                $submittime=date("m/d/Y @ H:i",strtotime($ticket['submitted_datetime']));
                $priority=$helpPriorities[$ticket['priority_id']];
                $status=$helpStatuses[$ticket['status_id']];
                $type=$helpTypes[$ticket['type_id']];
                $brief="<div style='width:300px;'>$ticket[problem]</div>";
                print "<tr><td>$type</td><td>$priority</td><td>$status</td>";
                print "<td><a href='?action=edit&id=$id'>$brief</a></td>";
                print "<td>Submitted by: $submittedby<br />on $submittime</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                //see if there is an existing solution
                $sql="SELECT * FROM maintenance_solutions WHERE ticket_id=$ticket[id]";
                $dbSolution=dbselectsingle($sql);
                if ($dbSolution['numrows']>0)
                {
                    $solutionid=$dbSolution['data']['id'];
                    print "<td><a href='maintenanceSolutions.php?action=editsolution&solutionid=$solutionid'>Solution</a></td>\n";
                } else {
                    print "<td>No solution yet</td>";
                }
                print "<td>";
                if(checkPermission(1,'function'))
                {
                    print "<a href='?action=delete&id=$id' class='delete'>Delete</a>";
                }
                print "</td>\n";
            
            }
        }
        tableEnd($dbTickets);
        
    }
}

function save_ticket($action)
{
    global $helpdeskCompleteStatus, $siteID;
    //first, update the ticket
    $ticketid=$_POST['ticketid'];
    $priority=$_POST['priority'];
    $status=$_POST['status'];
    
    $sql="SELECT * FROM maintenance_tickets WHERE id=$ticketid";
    $dbTicket=dbselectsingle($sql);
    $emailsent=$dbTicket['data']['email_sent'];
    
    if ($status==$helpdeskCompleteStatus)
    {
        //get id of currently logged in user
        $completedby=$_SESSION['cmsuser']['userid'];
        $completedtime=date("Y-m-d H:i");
        $cupdate=", completed_by='$completedby', completed_datetime='$completedtime'";
    } else {
        $completedby=0;
        $completedtime='';
    }
    $sql="UPDATE maintenance_tickets SET priority_id=$priority, status_id=$status $cupdate WHERE id=$ticketid";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    
    //see if there is a new note
    $userid=$_SESSION['cmsuser']['userid'];
    $now=date("Y-m-d H:i:s");
    if($_POST['newnote']!='')
    {
        $sql="INSERT INTO maintenance_tickets_notes (ticket_id, created_datetime, submitted_by, note) VALUES 
        ('$ticketid', '$now', '$userid', '".addslashes($_POST['newnote'])."')";
        $dbInsertNote=dbinsertquery($sql);
        $error.=$dbInsertNote['error'];
        
        //send the orginator of the note an update
        $sql="SELECT A.firstname, A.lastname, A.email FROM users A, maintenance_tickets B WHERE B.submitted_by=A.id AND B.id=$ticketid";
        $dbPerson=dbselectsingle($sql);
        $person=$dbPerson['data'];
        $email=stripslashes($person['email']);
        $fullname=stripslashes($person['firstname'])." ".stripslashes($person['lastname']);
        //$from="'Mango Help Desk <".$GLOBALS['systemEmailFromAddress'].">'";
        $from=$GLOBALS['systemEmailFromAddress'];
        //$to="$fullname <$email>";
        $to=$email;
        $subject="Your Mango trouble ticket #$ticketid has been updated.";
        $message="<html><head></head><body>\n";
        $message.= "Hi, we just wanted to let you know that we are looking into the trouble ticket you submitted.\n";
        $message.= "We just added this note to the ticket:<br />\n\n";
        $message.="<p>".$_POST['newnote']."</p>\n";
        $message.="<br /><br />Stay tuned for a final solution.<br />\n";
        $message.="Mango Help Desk";
        $message.="</body></html>\n";
        $message = wordwrap($message, 70);

        $mail = new htmlMimeMail();
        
        $mail->setHtml($message);
        $mail->setFrom($from);
        $mail->setSubject($subject);
        $mail->send(array($to));
        
    }
    
    
    //see if we are inserting a solution
    if ($_POST['solution_brief']!='' || $_POST['title']!='' || $_POST['solution_full']!='')
    {
        $solutionid=$_POST['solutionid'];
        $keywords=addslashes($_POST['keywords']);
        $keywords=str_replace(";"," ",$keywords);
        $keywords=str_replace(","," ",$keywords);
        $keywords=str_replace("  "," ",$keywords);
        $title=addslashes($_POST['title']);
        $solutionbrief=substr(addslashes(str_replace("<input type=\"hidden\" /><!--Session data--><input type=\"hidden\" />","",$_POST['solution_brief'])),0,255);
        $solutionfull=addslashes(str_replace("<input type=\"hidden\" /><!--Session data--><input type=\"hidden\" />","",$_POST['solution_full']));
        if ($_POST['public']){$public=1;}else{$public=0;}
        if ($solutionid==0)
        {
            //inserting
            $sql="INSERT INTO maintenance_solutions (ticket_id, public, title, keywords, solution_brief, 
            solution_text, site_id) VALUES ('$ticketid', $public, '$title', '$keywords', '$solutionbrief', '$solutionfull', '$siteID')";
            $dbInsert=dbinsertquery($sql);
            $error.=$dbInsert['error'];
            $solutionid=$dbInsert['numrows'];
        } else {
            $sql="UPDATE maintenance_solutions SET public='$public', keywords='$keywords', title='$title', solution_brief='$solutionbrief', solution_text='$solutionfull' WHERE id=$solutionid";
            $dbUpdate=dbexecutequery($sql);
            $error.=$dbUpdate['error'];
            
        }
    }
    if ($status==$helpdeskCompleteStatus && $emailsent==0)//
    {
        //send a message to the person who opened the ticket letting them know that a solution has been made and that their ticket is now complete
        $sql="SELECT A.firstname, A.lastname, A.email FROM users A, maintenance_tickets B WHERE B.submitted_by=A.id AND B.id=$ticketid";
        $dbPerson=dbselectsingle($sql);
        $person=$dbPerson['data'];
        $email=stripslashes($person['email']);
        
        
        
        $fullname=stripslashes($person['firstname'])." ".stripslashes($person['lastname']);
        //$from="'Mango Help Desk <".$GLOBALS['systemEmailFromAddress'].">'";
        $from=$GLOBALS['systemEmailFromAddress'];
        //$to="$fullname <$email>";
        $to=$email;
        $subject="Your Mango trouble ticket #$ticketid has been resolved.";
        $message="<html><head></head><body>\n";
        $message.= "Hi, we just wanted to let you know that we found a solution for the trouble ticket you submitted.\n";
        $message.= "Here is the solution to your issue<br />\n\n";
        $message.="<p><a href='http://".$GLOBALS['serverIPaddress']."/maintenanceTickets.php?action=edit&id=$ticketid'>Link to ticket</a></p>\n";
        $message.="<h4>$title</h4>\n";
        $message.="<p style='font-weight:bold;'>$solutionbrief</p>\n";
        $message.="<p>$solutionfull</p>\n";
        $message.="<br /><br />Please let us know of any other problems you encounter.<br />\n";
        $message.="Mango Help Desk";
        $message.="</body></html>\n";
        $message = wordwrap($message, 70);

        $mail = new htmlMimeMail();
        
        $mail->setHtml($message);
        $mail->setFrom($from);
        $mail->setSubject($subject);
        $mail->send(array($to));
        $sql="UPDATE maintenance_tickets SET email_sent=1 WHERE id=$ticketid";
        $dbTicketUpdate=dbexecutequery($sql);
        $error.=$dbTicketUpdate['error'];
        /*
        
        print "To: $to<br />\n";
        print "From: $from<br />\n";
        print "Subject: $subject<br />\n";
        print "Message:<br />$message<br />\n";
        die();
        
        //die($message."<br /><br />".$result);
        $to="Mango Help Desk <".$GLOBALS['systemEmailFromAddress'].">";
        $headers = "From: ".$systemEmailFromAddress. "\r\n".
            'Reply-To: '.$systemEmailFromAddress."\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $result=mail($to, $subject, $message, $headers);
        */
    }
    
    if ($error!='')
    {
        setUserMessage('There was a problem saving the ticket.<br />'.$error,'error');
    } else {
        setUserMessage('The ticket has been successfully saved.','success');
    }
    redirect("?action=list");
    
}


footer();
?>
