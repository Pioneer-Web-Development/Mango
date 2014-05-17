<?php
require('includes/functions_db.php');

$smiles=array(":)"=>"artwork/smilies/01.gif",
            ":("=>"artwork/smilies/02.gif",
            ";)"=>"artwork/smilies/03.gif",
            ":D"=>"artwork/smilies/04.gif",
            ":P"=>"artwork/smilies/09.gif",
            ":O"=>"artwork/smilies/11.gif"
);

$pirates=array("you"=>"ye",
            "yes"=>"aye",
            "is"=>"be",
            "are"=>"be",
            "am"=>"be",
            "no"=>"nay",
            "ok"=>"Arrrg!",
            "yeah"=>"Arrrg!",
            "your"=>"yer",
            "city"=>"port",
            "doing"=>"doin'",
            "bar"=>"tavern",
            "party"=>"pillage",
            "fail"=>"booch",
            "bank"=>"coffer",
            "stop"=>"avast",
            "cool"=>"that be right piratey",
            "girls"=>"lasses",
            "boys"=>"lads",
            "girl"=>"lass",
            "storeroom"=>"hold",
            "car"=>"ship",
            "boy"=>"lad",
            "quickly"=>"smartyly",
            "guy"=>"swab",
            "guys"=>"swabbies",
            "boss"=>"captain",
            "Boss"=>"Captain",
            "dead"=>"gone to Davy Jones' locker",
            "friend"=>"mate",
            "person"=>"landlubber",
            "here"=>"here'bouts",
            "beer"=>"grog",
            "haha"=>"harhar",
            "hehe"=>"Me parrot didna laugh at that",
            "lol"=>'Chortlin chickenfeathers!',
            "left"=>"port",
            "right"=>"starboard",
            "hurry"=>"gangway",
            "hello"=>"ahoy",
            "talk"=>"bluster",
            "huh"=>"What's that swabbie",
            "Hello"=>"Ahoy",
            "goodbye"=>"I'm walking the plank",
            "done"=>"The task has been completed mate!",
            "night"=>"The sun has gone... and so must I to bed!",
            "lmao"=>"laughing heartily",
            "LMAO"=>"laughing heartily",
            "lady"=>"wench"
);
$debug='';
$punctuation=array(".",",","!","?",":",";","'");
$name=addslashes($_POST['username']);
$userid=intval($_POST['userid']);
$roomid=intval($_POST['roomid']);
$action=$_POST['action'];
if($_POST['pirate']){$pirate=1;}
$message=$_POST['message'];
$message=urldecode($message);
$now=date("Y-m-d H:i:s");
//Check to see if a message was sent.
if($_POST['action']=='post') {
    $message=str_replace("\r\n","\n",$message);
    $message=str_replace("\r","\n",$message);
    $message=str_replace("<p></p>","",$message);
    $message=str_replace("<P></P>","",$message);
    $message=str_replace("<P>&nbsp;</P>","",$message);
    $message=str_replace("<p>&nbsp;</p>","",$message);
    $message=str_replace("<p>","",$message);
    $message=str_replace("</p>","",$message);
    $message=str_replace("<P>","",$message);
    $message=str_replace("</P>","",$message);
    $message=str_replace("<br />","",$message);
    $message=str_replace("<br>","",$message);
    $message=str_replace("<BR>","",$message);
    $message=str_replace("&lt;br&gt;","",$message);
    $message=str_replace("&lt;BR&gt;","",$message);
    $message=str_replace("<div></div>","",$message);
    $message=explode("\n",$message);
    foreach($message as $key=>$line)
    {
        if ($line!="")
        {
            $newmessage[]=$line;
        }
    }
    $message=implode("<br>",$newmessage);
    
    //smilie replacement function ;)
    foreach ($smiles as $t1=>$t2)
    {
        $t2="<img src='$t2' border=0 height=19>";
        $message=str_replace($t1,$t2,$message);     
    }
    
    if ($pirate)
    {
       $message=explode(" ",$message);
       foreach($message as $key=>$word)
       {
           $punct=substr($word,strlen($word)-1,1);
           if (in_array($punct,$punctuation))
           {
               $word=substr($word,0,strlen($word)-1);
           } else {
               $punct='';
           } 
           if ($pirates[$word]!='')
           {
               $message[$key]=$pirates[$word].$punct;
           } else {
               $message[$key]=$word;
           }
       }
        $message=implode(" ",$message);
    }
    $message=strip_tags($message,"<br><img><a>");
    $message=addslashes($message);
    if ($name==''){$name='Anon Y. Mouse';}
    
    $sql = "INSERT INTO chat_messages (room_id, user_id, user_name, chat_text, post_datetime) 
    VALUES ('$roomid', '$userid', '$name', '$message', '$now')";
    $dbInsert=dbinsertquery($sql);
    $error=$dbInsert['error'];  
} else if($_POST['action']=='reset') {
    $sql = "DELETE FROM chat_messages WHERE room_id=$roomid";
    $dbDelete=dbexecutequery($sql);
    $error=$dbDelete['error'];
    if($error==''){
        $message='Chat successfully reset';
    } else {
        $message='Trouble resetting chat.';
    }
    $sql = "INSERT INTO chat_messages (room_id, user_id, user_name, chat_text, post_datetime) 
    VALUES ('$roomid', '0', 'System', '$message', '$now')";
    $dbInsert=dbinsertquery($sql);
    
}

$lastid=$_POST['lastid'];


$sql="SELECT * FROM chat_messages WHERE room_id='$roomid' AND id>$lastid ORDER BY post_datetime DESC";
$dbMessages=dbselectmulti($sql);
if($_POST['action']=='get'){$error=$dbMessages['error'];}

$lastsql="SELECT MAX(id) as lastid FROM chat_messages WHERE room_id='$roomid'";
$dbLast=dbselectsingle($lastsql);
$lastid=$dbLast['data']['lastid'];

print $lastid."|".$error."|";    

if($dbMessages['numrows']>0)
{
    foreach($dbMessages['data'] as $message)
    {
        print "<div id='message_$message[id]'>";
        print stripslashes($message['chat_text'])."<br>";
        print "<span style='font-size:10px;font-style:italic;'>Posted by ".stripslashes($message['user_name'])." at ".date("H:i:s",strtotime($message['post_datetime'])).' on '.date("m/d",strtotime($message['post_datetime']));
        print "</span><br>";        
    }
}


dbclose();  
?>