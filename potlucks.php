<?php
//<!--VERSION: .9 **||**-->
$standalone=true;
include("includes/mainmenu.php") ;

    
if ($_POST['submit'])
{
    $action=$_POST['submit'];
} elseif ($_GET['action'])
{
    $action=$_GET['action'];
} else {
    $action='list';
}


switch ($action)
{
    case "invite":
    invite();
    break;
    
    case "reminder":
    send_reminder();
    break;
    
    case "Send Invite":
    send_invite();
    break;
    
    case "addpotluck":
    potluck('add');
    break;
    
    case "editpotluck":
    potluck('edit');
    break;
    
    case "deletepotluck":
    potluck('delete');
    break;
    
    case "listpotluck":
    potluck('list');
    break;
    
    case "Save Potluck":
    save_potluck('insert');
    break;
    
    case "Update Potluck":
    save_potluck('update');
    break;
    
    case "addparticipant":
    participant('add');
    break;
    
    case "editparticipant":
    participant('edit');
    break;
    
    case "deleteparticipant":
    participant('delete');
    break;
    
    case "listparticipant":
    participant('list');
    break;
    
    case "Save Item":
    save_participant('insert');
    break;
    
    case "Update Item":
    save_participant('update');
    break;
    
    default:
    potluck('list');
    break;
}

 
function potluck($action)
{
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Potluck";
            $datetime=date("Y-m-d H:i");
        } else {
            $id=intval($_GET['potluckid']);
            $sql="SELECT * FROM potluck WHERE id=$id";
            $dbPotluck=dbselectsingle($sql);
            $potluck=$dbPotluck['data'];
            $title=$potluck['potluck_title'];
            $description=$potluck['potluck_description'];
            $datetime=$potluck['potluck_datetime'];    
            $button="Update Potluck";
        }
        print "<form method=post>\n";
        make_text('title',$title,'Title','Brief title for the potluck (ex. Going away potluck for XXXX)',50);
        make_textarea('description',$description,'Description','Fuller description of event, theme ideas, etc.',70,10);
        make_datetime('datetime',$datetime,'Date and Time','');
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $id=intval($_GET['potluckid']);
        $sql="DELETE FROM potluck WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            $sql="DELETE FROM potluck_particpants WHERE potluck_id=$id";
            $dbDelete=dbexecutequery($sql);
            $error=$dbDelete['error'];
            setUserMessage('There was a problem deleting the potluck.<br />'.$error,'error');
        } else {
            setUserMessage('The potluck has been successfully deleted.','success');
        }
        redirect("?action=listpotluck");
        
    } else {
        $sql="SELECT * FROM potluck";
        $dbPotlucks=dbselectmulti($sql);
        tableStart("<a href='?action=addpotluck'>Add new potluck</a>","Potluck Title,Date/Time",7);
        if ($dbPotlucks['numrows']>0)
        {
            foreach($dbPotlucks['data'] as $potluck)
            {
                $id=$potluck['id'];
                $title=$potluck['potluck_title'];
                $datetime=date("m/d/Y H:i",strtotime($potluck['potluck_datetime']));
                print "<tr>";
                print "<td>$title</td>";
                print "<td>$datetime</td>";
                print "<td><a href='?action=listparticipant&potluckid=$id'>Participants</a></td>";
                print "<td><a href='?action=editpotluck&potluckid=$id'>Edit</a></td>";
                print "<td><a href='?action=deletepotluck&potluckid=$id' class='delete'>Delete</a></td>";
                print "<td><a href='?action=invite&potluckid=$id'>Send email invite</a></td>";
                print "<td><a href='?action=reminder&potluckid=$id'>Send reminder</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbPotlucks);
        
    }
} 

function save_potluck($action)
{
    $id=$_POST['id'];
    $title=addslashes($_POST['title']);
    $description=addslashes($_POST['description']);
    $datetime=addslashes($_POST['datetime']);
    if($action=='insert')
    {
        $sql="INSERT INTO potluck (potluck_title, potluck_description, potluck_datetime) VALUES 
        ('$title', '$description', '$datetime')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE potluck SET potluck_title='$title', potluck_description='$description', potluck_datetime='$datetime'  WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the potluck.<br />'.$error,'error');
    } else {
        setUserMessage('The potluck has been successfully saved.','success');
    }
    redirect("?action=listpotluck");
    
}

function participant($action)
{
    $sql="SELECT * FROM users ORDER BY firstname, lastname";
    $dbUsers=dbselectmulti($sql);
    $users=array();
    $users[0]="Please select your name";
    if($dbUsers['numrows']>0)
    {
        foreach($dbUsers['data'] as $data)
        {
            $users[$data['id']]=$data['firstname'].' '.$data['lastname'];
        }
    }
    $potluckid=intval($_GET['potluckid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Item";
            $id=0;
        } else {
            $id=intval($_GET['potpartid']);
            $sql="SELECT * FROM potluck_participants WHERE id=$id";
            $dbPotluck=dbselectsingle($sql);
            $potluck=$dbPotluck['data'];
            $partid=$potluck['participant_id'];
            $item=$potluck['participant_item'];
            $note=$potluck['participant_note'];    
            $button="Update Item";
        }
        print "<form method=post>\n";
        make_select('participant_id',$users[$partid],$users,'Participant','Who will be bringing something?');
        make_text('item',$item,'Item','Item the person is bringing',50);
        make_textarea('note',$note,'Note','Note to attach',70,10);
        make_hidden('potluckid',$potluckid);
        make_hidden('potpartid',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $id=intval($_GET['potpartid']);
        $sql="DELETE FROM potluck_participants WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the participant item.<br />'.$error,'error');
        } else {
            setUserMessage('The participant item has been successfully deleted.','success');
        }
        redirect("?action=listparticipant");
        
    } else {
        //display a block for the potluck details
        $sql="SELECT * FROM potluck WHERE id=$potluckid";
        $dbPotluck=dbselectsingle($sql);
        $potluck=$dbPotluck['data'];
        $date=date("m/d/Y",strtotime($potluck['potluck_datetime']));
        $time=date("H:i",strtotime($potluck['potluck_datetime']));
        $title=$potluck['potluck_title'];
        $desc=$potluck['potluck_description'];
        print "<div class='ui-widget' style='width:700px;margin-top:10px;margin-bottom:10px;margin-left:100px;'>

            <div class='ui-state-highlight ui-corner-all' style='padding: 0pt 0.7em;'>
            <p><strong>$title</strong></p>
            <p><strong>Will be held on $date at $time</strong></p>
            <p>$desc</p></div> 
</div>";
 
        $sql="SELECT A.firstname, A.lastname, B.* FROM users A, potluck_participants B WHERE A.id=B.participant_id AND B.potluck_id=$potluckid";
        $dbParticipants=dbselectmulti($sql);
        tableStart("<a href='?action=addparticipant&potluckid=$potluckid'>Add new item</a><hr>,<a href='?action=listpotluck'>List all potlucks</a>,<a href='?action=addpotluck'>Add new potluck</a>","Name,Bringing",4);
        if ($dbParticipants['numrows']>0)
        {
            foreach($dbParticipants['data'] as $participant)
            {
                $id=$participant['id'];
                $name=$participant['firstname'].' '.$participant['lastname'];
                $bringing=$participant['participant_item'];
                print "<tr>";
                print "<td>$name</td>";
                print "<td>$bringing</td>";
                print "<td><a href='?action=editparticipant&potluckid=$potluckid&potpartid=$id'>Edit</a></td>";
                print "<td><a href='?action=deleteparticipant&potluckid=$potluckid&potpartid=$id' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbParticipants);
        
    }
} 

function save_participant($action)
{
    $potpartid=$_POST['potpartid'];
    $potluckid=$_POST['potluckid'];
    $partid=addslashes($_POST['participant_id']);
    $item=addslashes($_POST['item']);
    $note=addslashes($_POST['note']);
    if($action=='insert')
    {
        $sql="INSERT INTO potluck_participants (participant_id, participant_item, participant_note, potluck_id) VALUES ('$partid', '$item', '$note', '$potluckid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE potluck_participants SET participant_id='$partid', participant_item='$item', participant_note='$note' WHERE id=$potpartid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the participant item.<br />'.$error,'error');
    } else {
        setUserMessage('The participant item has been successfully saved.','success');
    }
    redirect("?action=listparticipant&potluckid=$potluckid");
    
}  

function invite()
{
    $potluckid=intval($_GET['potluckid']);
    //find any already selected users for this potluck
    $sql="SELECT * FROM potluck_invitees WHERE potluck_id='$potluckid'";
    $dbInvited=dbselectmulti($sql);
    $invited=array();
    if($dbInvited['numrows']>0)
    {
        foreach($dbInvited['data'] as $inv)
        {
            $invited[]=$inv['user_id'];
        }   
    }
    print "<form method=post>\n";
    $sql="SELECT * FROM user_departments ORDER BY department_name";
    $dbDepartments=dbselectmulti($sql);
    if($dbDepartments['numrows']>0)
    {
        print "<div id='departments'>\n";
        foreach($dbDepartments['data'] as $department)
        {
            print "<div style='width:150px;float:left;margin-right:4px;padding-right:3px;border:thin solid #666;'>\n";
            print "<span style='font-size:12px;font-weight:bold;'>$department[department_name]</span><br>";
            //get users in this department
            $sql="SELECT * FROM users WHERE department_id=$department[id] AND email<>''";
            $dbUsers=dbselectmulti($sql);
            if($dbUsers['numrows']>0)
            {
                foreach($dbUsers['data'] as $user)
                {
                    if(in_array($user['id'],$invited)){$found=1;}else{$found=0;}
                    print "<span>".make_checkbox('chk_'.$user['id'],$found).' '.$user['firstname'].' '.$user['lastname']."</span><br>";   
                }
            }            
            print "</div>\n";

        }
        print "<div class='clear'></div>\n";
        print "</div>\n";
    }
    //make_textarea('emailaddresses','','Email Addresses','Enter a list of email addresses to send to, one on each line',70,20,false);
    make_textarea('note','','Message','Enter a message to include in the email. A link to sign up will be included automatically',70,10);
    make_hidden('potluckid',$potluckid);
    make_submit('submit','Send Invite');
    print "</form>\n";
}

function send_invite()
{
    include ('includes/mail/htmlMimeMail.php');
    $potluckid=$_POST['potluckid'];
    //lets get our people
    $values="";
    $in="";
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,4)=="chk_")
        {
            $userid=str_replace("chk_","",$key);
            $values.="($potluckid,$userid),";
            $in.=$userid.",";        
        }
    }
    $values=substr($values,0,strlen($values)-1);
    $in=substr($in,0,strlen($in)-1);
    //add the invitees
    $sql="DELETE FROM potluck_invitees WHERE potluck_id=$potluckid";
    $dbDelete=dbexecutequery($sql);
    //print "$sql<br>";
    $sql="INSERT INTO potluck_invitees (potluck_id,user_id) VALUES $values";
    $dbInsert=dbinsertquery($sql);
    //print "$sql<br>$dbInsert[error]";
    //die();
    //now get the email adddresses
    $sql="SELECT email FROM users WHERE id IN ($in)";
    $dbUsers=dbselectmulti($sql);
    if($dbUsers['numrows']>0)
    {
        foreach($dbUsers['data'] as $user)
        {
            $addresses[]=$user['email'];    
        }
        //$addresses=$_POST['emailaddresses'];
        //$addresses=explode("\n",$addresses);
        
        $sql="SELECT * FROM potluck WHERE id=$potluckid";
        $dbPotluck=dbselectsingle($sql);
        $potluck=$dbPotluck['data'];
        $subject=$potluck['potluck_title'].' on '.date("m/d/Y",strtotime($potluck['potluck_datetime'])).' at '.date("H:i",strtotime($potluck['potluck_datetime']));
            
        $message=htmlentities($_POST['note']);
        $message.="\n";
        $message.="\n<a href='".$GLOBALS['serverIPaddress'].$GLOBALS['systemRootPath']."potlucks.php?action=listparticipant&potluckid=$potluckid'>See what else people are bringing to the potluck.</a><br>";
        $message.="\n<a href='".$GLOBALS['serverIPaddress'].$GLOBALS['systemRootPath']."potlucks.php?action=addparticipant&potluckid=$potluckid'>and click here to add what you are bringing to the list.</a>";
        $mail = new htmlMimeMail();
        $mail->setHtml($message);
        $mail->setFrom($GLOBALS['systemEmailFromAddress']);
        $mail->setSubject($subject);
        //print_r($addresses);
        //print "Subject: $subject<br>";
        //print "Message: $message<br>";
        
        $result = $mail->send($addresses,'smtp');
        //print "Result: $result";
        if ($result)
        {
            setUserMessage('The participant invite has been successfully sent.','success');
        } else {
            setUserMessage('There was a problem sending the participant invite.','error');
        }
    } else {
        setUserMessage('There were no users selected to send the email to.','error');
    }
    redirect("?action=listpotluck");
    
}

function send_reminder()
{
    include ('includes/mail/htmlMimeMail.php');
    $potluckid=intval($_GET['potluckid']);
    
    //now get the email adddresses
    $sql="SELECT A.email, A.firstname, A.lastname, B.participant_item  FROM users A, potluck_participants B  WHERE A.id=B.participant_id AND B.potluck_id=$potluckid";
    $dbUsers=dbselectmulti($sql);
    if($dbUsers['numrows']>0)
    {
        foreach($dbUsers['data'] as $user)
        {
            $addresses[]=$user['email'];
            $items.=$user['firstname'].' is bringing '.$user['participant_item']."<br>\n";    
        }
        //$addresses=$_POST['emailaddresses'];
        //$addresses[]="jhansen@idahopress.com";
        
        $sql="SELECT * FROM potluck WHERE id=$potluckid";
        $dbPotluck=dbselectsingle($sql);
        $potluck=$dbPotluck['data'];
        $subject=$potluck['potluck_title'].' on '.date("m/d/Y",strtotime($potluck['potluck_datetime'])).' at '.date("H:i",strtotime($potluck['potluck_datetime']));
            
        $message=$subject."<br><br>\n\n";
        $message.=$items."<br><br>\n\n";
        //now get a list of everyone and what they are bringing
        
        $message.="\n";
        $message.="\n<a href='".$GLOBALS['serverIPaddress'].$GLOBALS['systemRootPath']."potlucks.php?action=listparticipant&potluckid=$potluckid'>See what else people are bringing to the potluck.</a><br>";
        $message.="\n<a href='".$GLOBALS['serverIPaddress'].$GLOBALS['systemRootPath']."potlucks.php?action=addparticipant&potluckid=$potluckid'>and click here to add what you are bringing to the list.</a>";
        $mail = new htmlMimeMail();
        $mail->setHtml($message);
        $mail->setFrom($GLOBALS['systemEmailFromAddress']);
        $mail->setSubject($subject);
        //print_r($addresses);
        //print "Subject: $subject<br>";
        //print "Message: $message<br>";
        
        $result = $mail->send($addresses,'smtp');
        //print "Result: $result";
        if ($result)
        {
            setUserMessage('The reminder email has been sent successfully.','success');
        } else {
            setUserMessage('There was a problem sending the reminder email.','error');
        }
    } else {
        setUserMessage('There were no users selected to send the email to.'.$dbUsers['error'],'error');
    }
    redirect("?action=listpotluck");
    
}

footer();
?>