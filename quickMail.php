<?php
error_reporting(0);
?>
<html>
<head>
<style>
body{
    padding:5px;
    font-family:Trebuchet MS,sans-serif;
    font-size:12px;
}
.label{
    width:120px;
    float:left;
    margin-top:2px;
    font-weight:bold;
}
.input{
    float:left;
    margin-top:2px;
}
.clear{
    clear:both;
    height:0px;
}
</style>
</head>
<body>
<?php
//<!--VERSION: .5 ||**||-->//
//this script is meant bo be a simple in system mail sender
//it will only allow logged-in users to send mail, and only to a specified list of people

    session_start();
    include ('includes/functions_db.php');
    include ('includes/functions_formtools.php');
    include ('includes/config.php');
    include ('includes/functions_common.php');
    include ('includes/mail/htmlMimeMail.php');
 if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}
 global $siteID;
 //get name and email address of current user
 $userid=$_SESSION['cmsuser']['userid'];
 //can only use this from pims, so only looking at pims users
 $sql="SELECT * FROM users WHERE id=$userid";
 $dbUser=dbselectsingle($sql);
 $user=$dbUser['data'];
 $username=$user['firstname']." ".$user['lastname'];
 $useremail=$user['email'];
 
 //build a list of people
 $sql="SELECT id, firstname, lastname, email, carrier, cell FROM users WHERE site_id=$siteID AND email<>'' ORDER BY lastname";
 $dbPeople=dbselectmulti($sql);
 if ($dbPeople['numrows']>0)
 {
     //build two array, emailaddresses and names
     $names=array();
     $names['none']="Please select recipient";
     foreach($dbPeople['data'] as $person)
     {
         $email='';
         $formatted_number='';
         if ($person['carrier']!='' and strlen($person['cell'])==10)
         {
             
             switch ($person['carrier'])
             {
                case "nextel":
                $formatted_number = $person['cell']."@messaging.nextel.com";
                break;
                
                case "virgin":
                $formatted_number = $person['cell']."@vmobl.com";
                break;
                
                case "cingular":
                $formatted_number = $person['cell']."@cingularme.com";
                break;
                
                case "att":
                $formatted_number = $person['cell']."@txt.att.net";
                break;
                
                case "sprint":
                $formatted_number = $person['cell']."@messaging.sprintpcs.com";
                break;
                
                case "tmobile":
                $formatted_number = $person['cell']."@tmomail.net";
                break;
                
                case "cricket":
                $formatted_number = $person['cell']."@sms.mycricket.com";
                break;
                
                case "verizon":
                $formatted_number = $person['cell']."@vtext.com";
                break;
              }          
         }
         $email=stripslashes($person['email']);
         $names[$email]='Email: '.stripslashes($person['firstname'])." ".stripslashes($person['lastname']);
         if ($formatted_number!='')
         {
            $names[$formatted_number]='Text: '.stripslashes($person['firstname'])." ".stripslashes($person['lastname']);
         }
     }
 } else {
     die("No employees have been defined, so there is no one to send an email to.");
 }
 
 if ($_POST['submit']=='Send Message')
 {
     //handle email submission
     if ($_POST['recipient']!='none')
     {
        $name=$names[$_POST['recipient']];
        if (strpos($name,"ext:")>0)
        {
            $message=$_POST['message'];
        } else {
            $message="<html><head></head><body>";
            $message.=htmlentities($_POST['message'])."</body></html>\n";
        
        }
        $name=str_replace("Email: ","",$name);
        $name=str_replace("Text: ","",$name);
        $address=$_POST['recipient'];
        $emailaddress="$name <$address>";
        
        $subject=htmlentities($_POST['subject']);
        $mail = new htmlMimeMail();
        $mail->setHtml($message);
        $mail->setFrom($GLOBALS['systemEmailFromAddress']);
        $mail->setSubject($subject);
        $mail->setHeader('Sender','Mango');
        print "emailing to $name at $address<br />";
        if ($address!='')
        {
            $result = $mail->send(array($emailaddress),'smtp');
        }
        /*
        $to      = 'jhansen@idahopress.com';
        $subject = 'test message';
        $message = 'this is a test message';
        $headers = 'From: jhansen@idahopress.com' . "\r\n" .
            'Reply-To: jhansen@idahopress.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $result=mail($to, $subject, $message, $headers);
        */
        if ($result)
        {
            print "<h2>Your message has been sent successfully</h2>\n";
            print "<h2><a href='#' onclick='window.close();'>Close window.</a></h2>\n";
        } else {
            print "<h2>There was an error sending your message.</h2>";
            //show email form
             print "<form method=post>";
             make_select('recipient',$names[$_POST['recipient']],$names,'Recipient');
             make_text('subject',$_POST['subject'],'Subject');
             make_textarea('message',$_POST['message'],'Message','',40,20,false);
             make_submit('submit','Send Message');
             print "</form>\n";
        }
            
     } else {
         print "<h3>You need to specify a recipient first!</h3>\n";
         //show email form
         print "<form method=post>";
         make_select('recipient',$names[$_POST['recipient']],$names,'Recipient');
         make_text('subject',$_POST['subject'],'Subject');
         make_textarea('message',$_POST['message'],'Message','',40,20,false);
         make_submit('submit','Send Message');
         print "</form>\n";
     }
 } else {
     //show email form
     print "<form method=post>";
     make_select('recipient',$names[$_POST['recipient']],$names,'Recipient');
     make_text('subject',$_POST['subject'],'Subject');
     make_textarea('message',$_POST['message'],'Message','',40,20,false);
     make_submit('submit','Send Message');
     print "</form>\n";
 }
 
 dbclose();   
?>
</body>
</html>
