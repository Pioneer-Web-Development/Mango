<?php

  //this script imports tickets from the email server
  require("../includes/mail/functions_pop3mail.php");
  require("../includes/mail/rfc822_addresses.php");
  require("../includes/mail/mime_parser.php");
  if($_GET['mode']=='test')
  {
      include("../includes/functions_db.php");
      include("../includes/config.php");
      include("../includes/functions_common.php");
      include("../includes/mail/htmlMimeMail.php");
      getEmail(); 
  }


function getEmail()
{
    global $remoteMailHostName,$remoteHelpdeskTicketUsername,$remoteHelpdeskTicketPassword,$remoteMaintenanceTicketUsername,$remoteMaintenanceTicketPassword;
    $emails=array();
    stream_wrapper_register('pop3', 'pop3_stream');  /* Register the pop3 stream handler class */

    $user=UrlEncode($remoteHelpdeskTicketUsername);
    $password=UrlEncode($remoteHelpdeskTicketPassword);
    $server=UrlEncode($remoteMailHostName);                         /* Authentication realm or domain            */
    $realm=UrlEncode("");                         /* Authentication realm or domain            */
    $workstation=UrlEncode("");                   /* Workstation for NTLM authentication       */
    $apop=0;                                      /* Use APOP authentication                   */
    $authentication_mechanism=UrlEncode("USER");  /* SASL authentication mechanism             */
    $debug=0;                                     /* Output debug information                  */
    $html_debug=1;                                /* Debug information is in HTML              */
    $message=1;
    $message_file='pop3://'.$user.':'.$password.'@'.$server.'/'.$message.
        '?debug='.$debug.'&html_debug='.$html_debug.'&realm='.$realm.'&workstation='.$workstation.
        '&apop='.$apop.'&authentication_mechanism='.$authentication_mechanism;
    /*
     * Access Gmail POP account
     */
    /*
     $message_file='pop3://'.$user.':'.$password.'@pop.gmail.com:995/1?tls=1&debug=1&html_debug=1';
      */

    $mime=new mime_parser_class;

    /*
     * Set to 0 for not decoding the message bodies
     */
    $mime->decode_bodies = 1;

    $parameters=array(
        'File'=>$message_file,
        
        /* Read a message from a string instead of a file */
        /* 'Data'=>'My message data string',              */

        /* Save the message body parts to a directory     */
        'SaveBody'=>'../artwork/emailattachments/',                            

        /* Do not retrieve or save message body parts     */
        'SkipBody'=>1,
    );
    $success=$mime->Decode($parameters, $decoded);
    $i=1;
    if($success)
    {
        foreach($decoded as $message)
        {
            /*
            echo "<pre>";
            var_dump($decoded[0]);
            echo "</pre>";
            */
            print "<h2>Working on message $i</h2>";
            if($mime->Analyze($message, $results))
            {
                foreach($results as $key=>$value)
                {
                    
                    //print "Key $key = $value<br>";
                    if($key=='Attachments')
                    {
                        foreach($value as $subvalue)
                        {
                            foreach($subvalue as $ftype=>$fvalue)
                            {
                                print "$ftype: $fvalue<br>";
                            }
                        }
                    } elseif ($key=='Subject')
                    {
                        print "Subject: -- $value<br>";
                    } elseif ($key=='Type')
                    {
                        print "Message Type: -- $value<br>";
                    } elseif ($key=='DataFile')
                    {
                        print "Message Contents: -- $value<br>";
                    } elseif ($key=='From')
                    {
                        print "From:<br>";
                        foreach($value as $subvalue)
                        {
                            foreach($subvalue as $stype=>$svalue)
                            {
                                print "$stype: $svalue<br>";
                            }
                        }
                    } elseif ($key=='Alternative')
                    {
                        print "Alternative:<br>";
                        foreach($value as $subvalue)
                        {
                            foreach($subvalue as $stype=>$svalue)
                            {
                                print "$stype: $svalue<br>";
                            }
                        }
                    }
                    //var_dump($message);
                    print "<hr>";
                }
            }
            $i++;
        }
    }
    
    
    $pop3=new pop3_class;
    $pop3->hostname=$remoteMailHostName;             /* POP 3 server host name                      */
    $pop3->port=110;                         /* POP 3 server host port,
                                                usually 110 but some servers use other ports
                                                Gmail uses 995                              */
    $pop3->tls=0;                            /* Establish secure connections using TLS      */
    $user=$remoteHelpdeskTicketUsername;      /* Authentication user name                    */
    $password=$remoteHelpdeskTicketPassword;  /* Authentication password                     */
    $pop3->realm="";                         /* Authentication realm or domain              */
    $pop3->workstation="";                   /* Workstation for NTLM authentication         */
    $apop=0;                                 /* Use APOP authentication                     */
    $pop3->authentication_mechanism="USER";  /* SASL authentication mechanism               */
    $pop3->debug=0;                          /* Output debug information                    */
    $pop3->html_debug=1;                     /* Debug information is in HTML                */
    $pop3->join_continuation_header_lines=1; /* Concatenate headers split in multiple lines */

    if(($error=$pop3->Open())=="")
    {
        if($_GET['mode']=='test'){print "<PRE>Connected to the POP3 server &quot;".$pop3->hostname."&quot;.</PRE>\n";}
        if(($error=$pop3->Login($user,$password,$apop))=="")
        {
            if($_GET['mode']=='test'){print "<PRE>User &quot;$user&quot; logged in.</PRE>\n";}
            if(($error=$pop3->Statistics($messages,$size))=="")
            {
                if($_GET['mode']=='test'){print "<PRE>There are $messages messages in the mail box with a total of $size bytes.</PRE>\n";}
                $result=$pop3->ListMessages("",0);
                if(GetType($result)=="array")
                {
                    for(Reset($result),$message=0;$message<count($result);Next($result),$message++)
                     
                    $result=$pop3->ListMessages("",1);
                    if(GetType($result)=="array")
                    {
                        for(Reset($result),$message=0;$message<count($result);Next($result),$message++)
                            
                        if($messages>0)
                        {
                            if(($error=$pop3->DeleteMessage(1))=="")
                            {
                              if($_GET['mode']=='test'){print "<PRE>Marked message 1 for deletion.</PRE>\n";}
                            }
                            
                        }
                        if($error==""
                        && ($error=$pop3->Close())=="")
                            if($_GET['mode']=='test'){print "<PRE>Disconnected from the POP3 server &quot;".$pop3->hostname."&quot;.</PRE>\n";}
                        
                    }
                    else
                        $error=$result;
                }
                else
                    $error=$result;
            }
        }
    }
    
}

  
function getEmailTickets()
{
    global $remoteMailHostName,$remoteHelpdeskTicketUsername,$remoteHelpdeskTicketPassword,$remoteMaintenanceTicketUsername,$remoteMaintenanceTicketPassword;
    
    
    $pop3=new pop3_class;
    $pop3->hostname=$remoteMailHostName;             /* POP 3 server host name                      */
    $pop3->port=110;                         /* POP 3 server host port,
                                                usually 110 but some servers use other ports
                                                Gmail uses 995                              */
    $pop3->tls=0;                            /* Establish secure connections using TLS      */
    $user=$remoteHelpdeskTicketUsername;      /* Authentication user name                    */
    $password=$remoteHelpdeskTicketPassword;  /* Authentication password                     */
    $pop3->realm="";                         /* Authentication realm or domain              */
    $pop3->workstation="";                   /* Workstation for NTLM authentication         */
    $apop=0;                                 /* Use APOP authentication                     */
    $pop3->authentication_mechanism="USER";  /* SASL authentication mechanism               */
    $pop3->debug=0;                          /* Output debug information                    */
    $pop3->html_debug=1;                     /* Debug information is in HTML                */
    $pop3->join_continuation_header_lines=1; /* Concatenate headers split in multiple lines */

    if(($error=$pop3->Open())=="")
    {
        if($_GET['mode']=='test'){print "<PRE>Connected to the POP3 server &quot;".$pop3->hostname."&quot;.</PRE>\n";}
        if(($error=$pop3->Login($user,$password,$apop))=="")
        {
            if($_GET['mode']=='test'){print "<PRE>User &quot;$user&quot; logged in.</PRE>\n";}
            if(($error=$pop3->Statistics($messages,$size))=="")
            {
                if($_GET['mode']=='test'){print "<PRE>There are $messages messages in the mail box with a total of $size bytes.</PRE>\n";}
                $result=$pop3->ListMessages("",0);
                if(GetType($result)=="array")
                {
                    for(Reset($result),$message=0;$message<count($result);Next($result),$message++)
                      if($_GET['mode']=='test'){print "<PRE>Message ".Key($result)." - ".$result[Key($result)]." bytes.</PRE>\n";}
                    $result=$pop3->ListMessages("",1);
                    if(GetType($result)=="array")
                    {
                        for(Reset($result),$message=0;$message<count($result);Next($result),$message++)
                            if($_GET['mode']=='test'){print "<PRE>Message ".Key($result).", Unique ID - \"".$result[Key($result)]."\"</PRE>\n";}
                        if($messages>0)
                        {
                            if(($error=$pop3->RetrieveMessage(1,$headers,$body,2))=="")
                            {
                                if($_GET['mode']=='test'){print "<PRE>Message 1:\n---Message headers starts below---</PRE>\n";}
                                for($line=0;$line<count($headers);$line++)
                                {
                                    if (strpos($headers[$line],"ubject:")>0)
                                    {
                                        $subject=str_replace("Subject: ","",$headers[$line]);
                                        if($_GET['mode']=='test'){print "Found a subject of $subject <br />";}    
                                    } else {
                                        if($_GET['mode']=='test'){print $headers[$line]."<br>";}
                                    }
                                }
                                
                                if($_GET['mode']=='test'){print  "<PRE>---Message headers ends above---\n---Message body starts below---</PRE>\n";}
                                
                                for($line=0;$line<count($body);$line++)
                                {
                                    if($_GET['mode']=='test')
                                    {
                                        print "<PRE>aa".HtmlSpecialChars($body[$line])."</PRE>\n";
                                    }
                                }
                                if($_GET['mode']=='test'){print "<PRE>---Message body ends above---</PRE>\n";}
                                
                                if(($error=$pop3->DeleteMessage(1))=="")
                                {
                                  if($_GET['mode']=='test'){print "<PRE>Marked message 1 for deletion.</PRE>\n";}
                                   /*
                                    if(($error=$pop3->ResetDeletedMessages())=="")
                                    {
                                        echo "<PRE>Resetted the list of messages to be deleted.</PRE>\n";
                                    }
                                    */
                                }
                            }
                        }
                        if($error==""
                        && ($error=$pop3->Close())=="")
                            if($_GET['mode']=='test'){print "<PRE>Disconnected from the POP3 server &quot;".$pop3->hostname."&quot;.</PRE>\n";}
                        
                    }
                    else
                        $error=$result;
                }
                else
                    $error=$result;
            }
        }
    }
  } 
  

function save_ticket()
 {
     global $siteID;
     $ticketid=$_POST['ticketid'];
     $priorityid=$_POST['helppriority'];
     $type=$_POST['helptype'];
     $s=$_POST['s'];
     $brief=addslashes($_POST['helpbrief']);
     $full=addslashes($_POST['helpfull']);
     $userid=$_SESSION['cmsuser']['userid'];
     $ctime=date("Y-m-d H:i");
     if ($s=='1')
     {
        $sql="SELECT * FROM helpdesk_statuses WHERE site_id=$siteID ORDER BY status_order ASC LIMIT 1";
        $dbStatus=dbselectsingle($sql);
        $statusid=$dbStatus['data']['id'];
        $sql="INSERT INTO maintenance_tickets(type_id,status_id,priority_id,submitted_by,
        submitted_datetime,problem,attempt) VALUES ('$type', '$statusid','$priorityid','$userid', '$ctime', '$brief', '$full')";
        $dbInsert=dbinsertquery($sql);
        if ($dbInsert['error']=='')
        {
            print "<script type='text/javascript'>self.close();</script>\n";
            
        } else {
            print "There was a problem saving your help request. You should submit a help request to have the issue fixed ;)";
        }  
     } else {
         if ($ticketid==0)
         {
             //new ticket, assign to the lowest status
             $sql="SELECT * FROM helpdesk_statuses WHERE site_id=$siteID ORDER BY status_order ASC LIMIT 1";
             $dbStatus=dbselectsingle($sql);
             $statusid=$dbStatus['data']['id'];
             $source=$_SESSION['cmsuser']['helpsource'];
             $sql="INSERT INTO helpdesk_tickets (type_id, status_id, priority_id, submitted_by, 
             submitted_datetime, help_brief, help_request, help_source) VALUES ('$type', 
             '$statusid', '$priorityid', '$userid', '$ctime', '$brief', '$full', '$source')";
             $dbInsert=dbinsertquery($sql);
             $ticketid=$dbInsert['numrows'];
             $error=$dbInsert['error'];
         } else {
             $sql="UPDATE helpdesk_tickets SET type_id='$type', priority_id='$priorityid', 
             updated_by='$userid', updated_datetime='$ctime', help_brief='$brief', help_request='$full' WHERE id=$ticketid";
             $dbUpdate=dbexecutequery($sql);
             $error=$dbUpdate['error'];  
         }
         
         if(isset($_FILES))
         { //means we have browsed for a valid file
            foreach($_FILES as $file) {
                switch($file['error']) {
                    case 0: // file found
                    if($file['name'] != NULL && okFileType($file['type'],'image',$file['name']) != false)  {
                        //get the new name of the file
                        //to do that, we need to push it into the database, and return the last record ID
                       // process the file
                        $date=date("Ym");
                        $path="artwork/helpticketImages/$date/";
                        if (!file_exists($path))
                        {
                            mkdir($path);
                        }
                        $newname=$file['name'];
                        $newname=str_replace(" ","",$newname);
                        $newname=str_replace("/","",$newname);
                        $newname=str_replace("\\","",$newname);
                        $newname=str_replace("*","",$newname);
                        $newname=str_replace("?","",$newname);
                        $newname=str_replace("!","",$newname);
                        $newname=str_replace("'","",$newname);
                        $newname=str_replace(";","",$newname);
                        $newname=str_replace(":","",$newname);
                        $newname=str_replace("'","",$newname);
                        $newname=str_replace("%","",$newname);
                        $newname=str_replace("\$","",$newname);
                        $newname="ticket_".$ticketid."_".$newname;
                        if(processFile($file,$path,$newname) == true) {
                            $sql="UPDATE helpdesk_tickets SET ticketImage_path='artwork/helpticketImages/$date/', ticketImage_filename='$newname' WHERE id=$ticketid";
                            $result=dbinsertquery($sql);
                            $error.=$result['error'];
                        } else {
                           $error.= 'There was an error inserting the image named '.$file['name'].' into the database. The sql statement was $sql';  
                        }
                    }
                    break;

                    case (1|2):  // upload too large
                    $error.= 'file upload is too large for '.$file['name'];
                    break;

                    case 4:  // no file uploaded
                    break;

                    case (6|7):  // no temp folder or failed write - server config errors
                    $error.= 'internal error - flog the webmaster on '.$file['name'];
                    break;
                }
            }
         }
         
         if ($error!='')
         {
             print $error;
         } else {
            redirect("?action=default");
         }
     }
 }


?>
