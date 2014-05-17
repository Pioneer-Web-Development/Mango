<?php
  //script to send alerts for file monitors
  //get the trigger time from preferences
  function fileMonitorAlerts()
  {
      $sql="SELECT fileMonitorTrigger, fileMonitorNotifier, systemEmailFromAddress FROM preferences";
      $dbCheck=dbselectsingle($sql);
      $check=$dbCheck['data']['fileMonitorTrigger'];
      if($check==''){$check='15';}
      $notifier=$dbCheck['data']['fileMonitorNotifier'];
      $from=$dbCheck['data']['systemEmailFromAddress'];
      $checktime=date("Y-m-d H:i:s",strtotime("-$check minutes"));
      $sql="SELECT * FROM file_monitors_registered WHERE active=1 AND last_ping<'$checktime'";
      $dbLate=dbselectmulti($sql);
      if($dbLate['numrows']>0)
      {
          //now, lets see if we already have a monitor alert active
          $sql="SELECT * FROM alerts WHERE alert_name='filemonitor'";
          $dbCheck=dbselectsingle($sql);
          if($dbCheck['numrows']==0)
          {
              $late='';
              foreach($dbLate['data'] as $lateitem)
              {
                  $late.="<li>Monitor at IP ".$lateitem['monitor_ip'].",$lateitem[name], last communicated at ".date("m/d/Y H:i:s",strtotime($lateitem['last_ping']))."</li>";
                  
              }
              $message="The following File Monitors have not communicated in the past $check minutes:<br><br>$late";
              if($notifier!='')
              {
                  $mail = new htmlMimeMail();
                  $mail->setHtml($message);
                  $mail->setFrom($from);
                  $mail->setSubject("MANGO ALERT! File monitors are not communicating with server!");
                  $mail->send(array($notifier));
              } else {
                  $GLOBALS['notes'].="UNABLE TO SEND EMAIL, notification address was blank.<br>";
              }
              $GLOBALS['notes'].="At $checktime + $check minutes the following message was sent:<br>$message<br>";
              
              //add it to the alert
              $sql="INSERT INTO alerts (alert_name) VALUES ('filemonitor')";
              $dbInsert=dbinsertquery($sql);
          } else {
              $GLOBALS['notes'].="At $checktime + $check minutes the monitor was still down but no message was sent.<br>";
          }
          
      } else {
          $GLOBALS['notes'].="No mis-behaving file monitors at ".date("m/d/Y H:i")."<br>\n";
      }
  }
?>
