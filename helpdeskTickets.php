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
$sql="SELECT * FROM helpdesk_types WHERE site_id=$siteID ORDER BY type_name";
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
    global $siteID, $helpPriorities, $helpStatuses, $helpTypes;
    if ($action=='edit')
    {
        $button="Update Ticket";
        $id=$_GET['id'];
        $sql="SELECT * FROM helpdesk_tickets WHERE id=$id";
        $dbTicket=dbselectsingle($sql);
        $ticket=$dbTicket['data'];
        $nameid=$ticket['submitted_by'];
        $submittime=date("m/d/Y @ H:i",strtotime($ticket['submitted_datetime']));
        $status=$ticket['status_id'];
        $priority=$ticket['priority_id'];
        $type=$ticket['type_id'];
        $brief=$ticket['help_brief'];
        $full=$ticket['help_request'];
        $completed_by=$ticket['completed_by'];
        $completed_time=$ticket['completed_time'];
        $image=$ticket['ticketImage_path'].$ticket['ticketImage_filename'];
        
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
        $sql="SELECT * FROM helpdesk_solutions WHERE ticket_id=$id";
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
        //lets just display the ticket information above
        print "<div style='margin-left:100px;'>\n";
        print "<h3>The following $types[$type] ticket was submitted by $submittedby on $submittime.$updated</h3>\n";
        if ($image!=''){$left='400px';}else{$left='600px';}
        print "<div style='float:left;width:$left;'>\n";
        print "<p>Brief: <br />\n";
        print stripslashes($brief);
        print "</p>\n";
        print "<p>Full Ticket: <br />\n";
        print stripslashes($full);
        print "</p>\n";
        print "</div>\n";
        if ($image!='')
        {
            print "<div style='float:left;width:110px;margin-left:10px;'>\n";
            print "<small>Attached image</small><br />\n";
            print "<a href='#' onclick='window.open(\"helpdeskImageDisplay.php?source=ticket&id=$id\",\"Ticket Image\",\"width=520,height=480,toolbar=0,status=0,location=0\");' style='text-decoration:none;'><img src='$image' border=0 width=100></a><br />\n";
            print "</div>\n";
        }
        print "</div>\n";
        print "<div class='clear'></div>\n";
        make_select('priority',$helpPriorities[$priority],$helpPriorities,'Current Priority');
        make_select('status',$helpStatuses[$status],$helpStatuses,'Current Status');
        make_text('title',$title,'Title','Provide a clear title for this solution',50);
        make_text('keywords',$keywords,'Keywords','Provide keywords to help find this solution in the future.',50);
        make_textarea('solution_brief',$solutionbrief,'Brief Solution','Provide a brief summary of the solution.',70,10);
        make_textarea('solution_full',$solutionfull,'Solution','Provide full solution. Please note that you can access the same solution, and add images by using the Helpdesk Solutions<br />menu option. This step merely creates a connection between the initial ticket and the final solution.',70,20);
        make_checkbox('public',$public,'Public','Make the solution to this ticket public.');
        make_hidden('ticketid',$id);
        make_hidden('solutionid',$solutionid);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=$_GET['id'];
        $sql="DELETE FROM helpdesk_tickets WHERE id=$id";
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
        global $helpdeskCompleteStatus;
        //get the completed status id
        $form="<form method=post>\n";
        $form.="<input type='checkbox' value='$_POST[showcompleted]' name='showcompleted'> Show completed tickets as well<br />\n";
        $form.="<input type='submit' class='submit' value='Update list'>\n";
        $form.="</form>\n";
        if (isset($_POST['showcompleted']))
        {
            $sql="SELECT * FROM helpdesk_tickets ORDER BY submitted_datetime DESC, priority_id DESC";
        } else {
            $sql="SELECT * FROM helpdesk_tickets WHERE status_id<>$helpdeskCompleteStatus ORDER BY submitted_datetime DESC, priority_id DESC";
        }
        $dbTickets=dbselectmulti($sql);
        tableStart($form,"Type,Priority,Status,Brief,Submitted",8);
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
                $brief=wordwrap($ticket['help_brief'],50,"<br />",true);
                print "<tr><td>$type</td><td>$priority</td><td>$status</td>";
                print "<td><a href='?action=edit&id=$id'>$brief</a></td>";
                print "<td>Submitted by: $submittedby<br />on $submittime</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                //see if there is an existing solution
                $sql="SELECT * FROM helpdesk_solutions WHERE ticket_id=$ticket[id]";
                $dbSolution=dbselectsingle($sql);
                if ($dbSolution['numrows']>0)
                {
                    $solutionid=$dbSolution['data']['id'];
                    print "<td><a href='helpdeskSolutions.php?action=editsolution&solutionid=$solutionid'>Solution</a></td>\n";
                } else {
                    print "<td>No solution yet</td>";
                }
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
                print "</tr>\n";
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
    
    $sql="SELECT * FROM helpdesk_tickets WHERE id=$ticketid";
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
    $sql="UPDATE helpdesk_tickets SET priority_id=$priority, status_id=$status $cupdate WHERE id=$ticketid";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    
    
    //see if we are inserting a solution
    if ($_POST['solution_brief']!='' && $_POST['title']!='' && $_POST['solution_full']!='')
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
            $sql="INSERT INTO helpdesk_solutions (ticket_id, public, title, keywords, solution_brief, solution_text, site_id) VALUES ($ticketid, $public, '$title', '$keywords', '$solutionbrief', '$solutionfull', '$siteID')";
            $dbInsert=dbinsertquery($sql);
            $error.=$dbInsert['error'];
            $solutionid=$dbInsert['numrows'];
        } else {
            $sql="UPDATE helpdesk_solutions SET public='$public', keywords='$keywords', title='$title', solution_brief='$solutionbrief', solution_text='$solutionfull' WHERE id=$solutionid";
            $dbUpdate=dbexecutequery($sql);
            $error.=$dbUpdate['error'];
            
        }
    }
    if ($status==$helpdeskCompleteStatus && $emailsent==0)//
    {
        //send a message to the person who opened the ticket letting them know that a solution has been made and that their ticket is now complete
        $sql="SELECT A.firstname, A.lastname, A.email FROM users A, helpdesk_tickets B WHERE B.submitted_by=A.id AND B.id=$ticketid";
        $dbPerson=dbselectsingle($sql);
        $person=$dbPerson['data'];
        $email=stripslashes($person['email']);
        $fullname=stripslashes($person['firstname'])." ".stripslashes($person['lastname']);
        //$from="'Mango Help Desk <".$GLOBALS['systemEmailFromAddress'].">'";
        $from=$GLOBALS['systemEmailFromAddress'];
        //$to="$fullname <$email>";
        $to=$email;
        $subject='Your Mango trouble ticket issue has been resolved.';
        $message="<html><head></head><body>\n";
        $message.= "Hi, we just wanted to let you know that we found a solution for the trouble ticket you submitted.\n";
        $message.= "<a href='".$GLOBALS['serverIPaddress'].$GLOBALS['systemRootPath']."helpdeskSubmit.php?action=viewsolution&ticketid=$ticketid'>Click here to link to your solution in the system</a>, or read it below.<br />\n\n";
        $message.="<h2>".$_POST['title']."</h2>\n";
        $message.="<p style='font-weight:bold;'>".$_POST['solution_brief']."</p>\n";
        $message.="<p>".$_POST['solution_full']."</p>\n";
        $message.="<br /><br />Please let us know of any other problems you encounter.<br />\n";
        $message.="Mango Help Desk";
        $message.="</body></html>\n";
        $message = wordwrap($message, 70);

        $mail = new htmlMimeMail();
        
        $mail->setHtml($message);
        $mail->setFrom($from);
        $mail->setSubject($subject);
        $mail->send(array($to));
        $sql="UPDATE helpdesk_tickets SET email_sent=1 WHERE id=$ticketid";
        $dbTicketUpdate=dbexecutequery($sql);
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
