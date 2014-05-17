<?php
session_start();
include("../functions_db.php");
include("../config.php");
include("../mail/htmlMimeMail.php");
global $helpdeskCompleteStatus;

$ticketid=intval($_POST['ticketid']);

$sql="SELECT * FROM maintenance_tickets WHERE id=$ticketid";
$dbTicket=dbselectsingle($sql);
$emailsent=$dbTicket['data']['email_sent'];

if($_POST['type']=='closeticket')
{
    //get id of currently logged in user
    $completedby=$_SESSION['cmsuser']['userid'];
    $completedtime=date("Y-m-d H:i");

    $sql="UPDATE maintenance_tickets SET status_id=$helpdeskCompleteStatus, completed_by='$completedby', completed_datetime='$completedtime' 
    WHERE id=$ticketid";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];

    if ($emailsent==0)//
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
        $json['emailSent']=1;
    } else {
        $json['emailSent']=0;
    }
    $json['status']='success';
} else if($_POST['type']=='move')
{
  $scheduleid=intval($_POST['scheduleid']);
  
  $dayDelta=intval($_POST['dayDelta']);
  //convert days to minutes
  $dayDelta=$dayDelta*24*60;
  $minuteDelta=intval($_POST['minuteDelta']);
  $totalMinutes=$dayDelta+$minuteDelta;
  
  //get current start/end for the job
  $sql="SELECT * FROM maintenance_scheduled WHERE id=$scheduleid";
  $dbJob=dbselectsingle($sql);
  $startdatetime=$dbJob['data']['starttime'];
  $enddatetime=$dbJob['data']['endtime'];
  
  $newstartdatetime=date("Y-m-d H:i:s",strtotime("$startdatetime +$totalMinutes minutes"));
  $newenddatetime=date("Y-m-d H:i:s",strtotime("$enddatetime +$totalMinutes minutes"));
  
  //update
  $sql="UPDATE maintenance_scheduled SET starttime='$newstartdatetime', endtime='$newenddatetime' WHERE id=$scheduleid";
  $dbUpdate=dbexecutequery($sql);
  
  if($dbUpdate['error']!='')
  {
      $json['status']='error';
      $json['message']=$dbUpdate['error'];
  } else {
  
      $json['status']='success';
      $json['dayDelta']=$dayDelta;
      $json['minuteDelta']=$minuteDelta;
      $json['totalMinues']=$totalMinutes;
      $json['oldstart']=$startdatetime;
      $json['oldend']=$enddatetime;
      $json['newstart']=$newstartdatetime;
      $json['newend']=$newenddatetime;
      $json['action']=$_POST['type'];
  }        
} else if($_POST['type']=='resize')
{
  $scheduleid=intval($_POST['scheduleid']);
  
  $dayDelta=intval($_POST['dayDelta']);
  //convert days to minutes
  $dayDelta=$dayDelta*24*60;
  $minuteDelta=intval($_POST['minuteDelta']);
  $totalMinutes=$dayDelta+$minuteDelta;
  
  //get current start/end for the job
  $sql="SELECT * FROM maintenance_scheduled WHERE id=$scheduleid";
  $dbJob=dbselectsingle($sql);
  $startdatetime=$dbJob['data']['starttime'];
  $enddatetime=$dbJob['data']['endtime'];
  
  $newstartdatetime=date("Y-m-d H:i:s",strtotime("$startdatetime"));
  $newenddatetime=date("Y-m-d H:i:s",strtotime("$enddatetime +$totalMinutes minutes"));
  
  //update
  $sql="UPDATE maintenance_scheduled SET endtime='$newenddatetime' WHERE id=$scheduleid";
  $dbUpdate=dbexecutequery($sql);
  
  if($dbUpdate['error']!='')
  {
      $json['status']='error';
      $json['message']=$dbUpdate['error'];
  } else {
  
      $json['status']='success';
      $json['dayDelta']=$dayDelta;
      $json['minuteDelta']=$minuteDelta;
      $json['totalMinues']=$totalMinutes;
      $json['oldstart']=$startdatetime;
      $json['oldend']=$enddatetime;
      $json['newstart']=$newstartdatetime;
      $json['newend']=$newenddatetime;
      $json['action']=$_POST['type'];
  }        
}
print json_encode($json);
?>
