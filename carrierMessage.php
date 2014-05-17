<?php
  include("includes/mainmenu.php");
  global $cellcarriers;
  $carriers=array();
    foreach($cellcarriers as $ccar)
    {
        $carriers[$ccar['id']]=$ccar['email'];   
    }    
  if($_POST)
  {
      $message=$_POST['message'];
      $sendall=false;
      $emails=array();
      if($_POST['carrier_id']==0)
      {
          $groups="";
          foreach($_POST as $key=>$value)
          {
              if (substr($key,0,6)=='group_')
              {
                  $id=str_replace("group_","",$key);
                  $groups.="$id,";
                  if($id=='all'){$sendall=true;}    
              }
          }
          $groups=substr($groups,0,strlen($groups)-1);
          if($GLOBALS['debug']){print "Sending to group: $groups<br />";}
          
          //get the users who will receive this email
          if($sendall)
          {
              $sql="SELECT * FROM carriers WHERE cell<>''";
          } else {
              if($groups!='')
              {
                  $sql="SELECT A.* FROM carriers A, carrier_groups_xref B WHERE B.group_id IN ($groups) AND B.carrier_id=A.id AND A.cell<>''"; 
              } else {
                  print "<span style='font-weight:bold;color:red;'>You need to specify at least one group to send the message to.</span><br />";
              }
          }
          if($GLOBALS['debug']){print "Looking for carriers with<br />$sql<br />";}
          
          $dbCars=dbselectmulti($sql);
          if($dbCars['numrows']>0)
          {
              //build the emails
              $emails=array();
              $count=0;
              foreach($dbCars['data'] as $car)
              {
                $emails[]=$car['cell'].$carriers[$car['carrier']]; 
                $count++;   
              }
              
             
          }
      } else {
          //sending to a specific carrier
          $carrierid=intval($_POST['carrier_id']);
          $sql="SELECT * FROM carriers WHERE id=$carrierid";
          $dbCarrier=dbselectsingle($sql);
          $car=$dbCarrier['data'];
          $emails[]=$car['cell'].$carriers[$car['carrier']];
          $count=1;
      }
      if($GLOBALS['debug']){
          print "Attempting these emails<br /><pre>";
          print_r($emails);
          print "</pre>\n";
      }
      $mail = new htmlMimeMail();
      $mail->setText($message);
      $mail->setFrom($GLOBALS['systemEmailFromAddress']);
      $mail->setSubject('Circulation Alert:');
      $mail->setHeader('Sender','Mango');
      $result = $mail->send($emails,'smtp');
      
      if($result)
      {
          print "<span style='font-weight:bold;font-size:14px;color:green;'>Successfully delivered the message to $count carriers.</span><br />";
          
      } else {
          print "<span style='font-weight:bold;font-size:14px;color:red;'>There was a problem sending the alert out.</span><br />";
         
      } 
      show_form();
  } else {
      show_form();   
  }
  
  function show_form()
  {
      print "<form method=post>\n";
      
      print "<h2>Send message to carriers</h2>";
      $sql="SELECT * FROM carrier_groups ORDER BY group_name";
      $dbGroups=dbselectmulti($sql);
      if ($dbGroups['numrows']>0)
      {
          print "<h3>Select the groups that should receive this message.</h3>\n";
          foreach($dbGroups['data'] as $group)
          {
               print input_checkbox('group_'.$group['id'],0,'','',$group['group_name']);    
          }
      }
      print input_checkbox('group_all',0,'','','Send to all groups');    
      
      $sql="SELECT * FROM carriers ORDER BY last_name";
      $dbCarriers=dbselectmulti($sql);
      if($dbCarriers['numrows']>0)
      {
          print "<h3>Send to a specific carrier</h3>\n";
          $cars[0]='Send to a group';
          foreach($dbCarriers['data'] as $car)
          {
              $cars[$car['id']]=stripslashes($car['first_name'].' '.$car['last_name']);
          }
          make_select('carrier_id',$cars[$_POST['carrier_id']],$cars,'Carrier','If you specify a carrier, only that specific carrier will get the text message'); 
      }
      make_textarea('message',$_POST['message'],'Message','Brief message to be sent, remember, this is a text message',50,5,false);
      make_submit('submit','Send Message');
      print "</form>\n"; 
  }
  footer();