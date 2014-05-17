<?php
  //script to send alerts for file monitors
  //get the trigger time from preferences
  
  function monitorAlerts()
  {
      $dt=date("Y-m-d H:i:s");
      $sql="SELECT ripMonitorTrigger, fileMonitorTrigger, alertNotifier, systemEmailFromAddress FROM core_preferences";
      $dbCheck=dbselectsingle($sql);
      $filecheck=$dbCheck['data']['fileMonitorTrigger'];
      if($filecheck==''){$filecheck='15';}
      $ripcheck=$dbCheck['data']['ripMonitorTrigger'];
      if($ripcheck==''){$ripcheck='15';}
      $notifier=$dbCheck['data']['alertNotifier'];
      $from=$dbCheck['data']['systemEmailFromAddress'];
      $filechecktime=date("Y-m-d H:i:s",strtotime("-$filecheck minutes"));
      $ripchecktime=date("Y-m-d H:i:s",strtotime("-$ripcheck minutes"));
      $ripcheckendtime=date("Y-m-d H:i:s",strtotime($ripchecktime."-60 minutes"));
      $sql="SELECT * FROM file_monitors_registered WHERE active=1 AND last_ping<'$filechecktime'";
      $dbLate=dbselectmulti($sql);
      if($dbLate['numrows']>0)
      {
          //now, lets see if we already have a monitor alert active
          $sql="SELECT * FROM system_alerts WHERE alert_type='filemonitor'";
          $dbCheck=dbselectsingle($sql);
          if($dbCheck['numrows']==0)
          {
              $late='';
              foreach($dbLate['data'] as $lateitem)
              {
                  $late.="<li>Monitor at IP ".$lateitem['monitor_ip'].",$lateitem[name], last communicated at ".date("m/d/Y H:i:s",strtotime($lateitem['last_ping']))."</li>";
                  
              }
              $message="The following File Monitors have not communicated in the past $filecheck minutes:<br><br>$late<br>";
              $GLOBALS['notes'].="At $filechecktime + $filecheck minutes the following message was sent:<br>$message<br>";
              
              //add it to the alert
              $sql="INSERT INTO system_alerts (alert_type, alert_datetime, alert_message) VALUES ('filemonitor', '$dt', '$message')";
              $dbInsert=dbinsertquery($sql);
          } else {
              $GLOBALS['notes'].="At $filechecktime + $filecheck minutes the monitor was still down but no message was sent.<br>";
          }
          
      } else {
          $GLOBALS['notes'].="No mis-behaving file monitors at ".date("m/d/Y H:i")."<br>\n";
      }
      if($ripcheck>0)
      {
          $sql="SELECT * FROM system_alerts WHERE alert_type='ripmonitor'";
          $dbCheck=dbselectsingle($sql);
          if($dbCheck['numrows']==0)
          {
              
              //now check to make sure the RIP is processing files properly
              //we will look for any pages that have been received in the last ripMonitorTrigger minutes that have not been received back
              $sql="SELECT * FROM job_pages WHERE workflow_receive<='$ripchecktime' AND workflow_receive>='$ripcheckendtime' AND pub_code<>'' AND section_code<>'' AND pub_date<>'' AND page_number<>'' AND page_ripped IS Null";
              $dbPages=dbselectmulti($sql);
              if($dbPages['numrows']>0)
              {
                  //looks like we have some pages stuck in the rip
                  //build a list of the pages
                  $pagelist='';
                  foreach($dbPages['data'] as $p)
                  {
                    $pagelist.=' Page ID: '.$p['id'].' '.$p['pub_code'].' ' .$p['section_code'].' ' .$p['pub_date'].' ' .$p['page_number'].',';   
                  }
                  $pagelist=substr($pagelist,0,strlen($pagelist)-1);
                  $ripmessage="There appears to be a problem with the Rip. $dbPages[numrows] page ($pagelist) were received more than $ripcheck minutes ago and have not yet been received back from the rip.<br>";
                  $GLOBALS['notes'].=$ripmessage;
                  $sql="INSERT INTO system_alerts (alert_type, alert_datetime, alert_message) VALUES ('ripmonitor', '$dt', '$ripmessage')";
                  $dbInsert=dbinsertquery($sql);
                  $message.=$ripmessage;
              } else {
                  $GLOBALS['notes'].="As of ".date("m/d/Y H:i")." the rip is performing properly.<br>\n";
              }
          }
      }
      if($message!='')
      {
          if($notifier!='')
          {
              $mail = new htmlMimeMail();
              $mail->setHtml($message);
              $mail->setFrom($from);
              $mail->setSubject("MANGO ALERT!");
              $mail->send(array($notifier));
              $GLOBALS['notes'].="Sent out an email to $notifier with a message of $message<br>";
          } else {
              $GLOBALS['notes'].="UNABLE TO SEND EMAIL, notification address was blank.<br>";
          }
      } else {
          $GLOBALS['notes'].="All systems performing normally at this time.<br>";
      }            
  }
?>
