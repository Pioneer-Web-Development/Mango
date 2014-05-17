<?php
//<!--VERSION: 1.0 **||**-->
  session_start();
  error_reporting(0);
  include("includes/functions_db.php");
  include("includes/config.php");
  
  if($_SESSION['cmsuser']['loggedin'])
  {
      $name=$_SESSION['cmsuser']['firstname'];
      $userid=$_SESSION['cmsuser']['userid'];
      if($GLOBALS['debug'])
      {
          print "<pre>";
          print_r($_SESSION);
          print "</pre>\n";
      }
      if (in_array(1,$_SESSION['cmsuser']['permissions']) || in_array(32,$_SESSION['cmsuser']['permissions']))
      {
          $candelete=true;
      }
  } else {
      $name='Anonymouse';
      $userid=0;
      $candelete=false;
  }
  
?>
<html>
    <head>
        <title>Mango Chat V2</title>
        <link rel='stylesheet' type='text/css' href='styles/jquery.cleditor.css' />
        <link rel='stylesheet' type='text/css' href='styles/jquery-ui-1.10.0.custom.css' />
        <style type="text/css" media="screen">
            body{
                font-family:Trebuchet MS;
                font-size: 80%;
                padding:0;
                margin:0;
            }
            .chat_time {
                font-style: italic;
                font-size: 9px;
            }
        </style>
        <script language="javascript" type="text/javascript" src="includes/jscripts/jquery-1.9.0.min.js"></script>
        <script language="javascript" type="text/javascript" src="includes/jscripts/jquery-ui-1.10.0.custom.min.js"></script>
        <script language="javascript" type="text/javascript" src="includes/jscripts/jquery-migrate-1.0.0.js"></script>
        <script language="javascript" type="text/javascript" src="includes/jscripts/jquery.cleditor.js"></script>
        <script language="javascript" type="text/javascript">
         var geditor='';
         $(document).ready(function() {
            geditor = $("#messageentry").cleditor({
              width:        500, // width not including margins, borders or padding
              height:       150, // height not including margins, borders or padding
              controls:     // controls to add to the toolbar
                            "bold italic underline strikethrough subscript superscript | font size " +
                            "style | color highlight removeformat | bullets numbering | outdent " +
                            "indent | alignleft center alignright justify | undo redo | " +
                            "image link unlink | cut copy paste pastetext | print source",
              colors:       // colors in the color popup
                            "FFF FCC FC9 FF9 FFC 9F9 9FF CFF CCF FCF " +
                            "CCC F66 F96 FF6 FF3 6F9 3FF 6FF 99F F9F " +
                            "BBB F00 F90 FC6 FF0 3F3 6CC 3CF 66C C6C " +
                            "999 C00 F60 FC3 FC0 3C0 0CC 36F 63F C3C " +
                            "666 900 C60 C93 990 090 399 33F 60C 939 " +
                            "333 600 930 963 660 060 366 009 339 636 " +
                            "000 300 630 633 330 030 033 006 309 303",    
              useCSS:       false, // use CSS to style HTML when possible (not supported in ie)
              docType:      // Document type contained within the editor
                            '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
              docCSSFile:   // CSS file used to style the document contained within the editor
                            "", 
              bodyStyle:    // style to assign to document body contained within the editor
                            "margin:4px; font:10pt Arial,Verdana; cursor:text"
            })[0];
            var doc = geditor.doc; 
             $(doc).keyup(function(e) { 
                if(e.which==13){
                    geditor.updateTextArea();
                    $("#btn_send_chat").click();
                }
             }); 
             

          });
          var lastid=1;
            function myHandleEvent(e) {
                if (e.type=='keypress' && e.keyCode==13)
                {
                   sendChatText();
                   return false;
                    
                } else {
                   return true;
                }
            }
        
            function startChat() {
                //Set the focus to the Message Box.
                geditor.focus();
                //Start Recieving Messages.
                getChatText();
            }        
            
            function refreshChatText()
            {
                $('#div_chat').html('');
                lastid=0;
                getChatText();
            }
            //Gets the current messages from the server
            function getChatText() {
                var roomid=$('#roomid').val();
                $.ajax({
                  url: "chatHelper.php",
                  type: "POST",
                  cache: false,
                  data: "roomid="+roomid+"&lastid="+lastid+"&action=get",
                  success: function(html){
                      var parts=html.split("|");
                      var error=parts[1];
                      if (error!=''){alert(error);}
                      lastid=parts[0];
                      $("#div_chat").prepend(parts[2]);
                  }
                });
            }
            //Add a message to the chat server.
            function sendChatText() {
                if($('#messageentry').val() == '') {
                    alert("You have not entered a message");
                    return;
                }
                var pirateCheck=$('#pirate');
                if (pirateCheck.checked)
                {
                    var pirate=1;
                } else {
                    var pirate=0;
                }
                var message=$('#messageentry').val();
                message=escape(message);
                var sendername=$('#sender_name').val();
                var senderid=$('#sender_id').val();
                var roomid=$('#roomid').val();
                $.ajax({
                  url: "chatHelper.php",
                  type: "POST",
                  cache: false,
                  data: "roomid="+roomid+"&lastid="+lastid+"&pirate="+pirate+"&message="+message+"&action=post&username="+sendername+"&userid="+senderid,
                  success: function(html){
                      var parts=html.split("|");
                      var error=parts[1];
                      if (error!=''){alert(error);}
                      lastid=parts[0];
                      $("#div_chat").prepend(parts[2]);
                      geditor.clear();
                  }
                });     
            }
            
            function changeChannel()
            {
                $('#div_chat').html('');
                lastid=1;
                getChatText();
            }
            
            //This cleans out the database so we can start a new chat session.
            function resetChat() {
                var roomid=$('#roomid').val();
                $.ajax({
                  url: "chatHelper.php",
                  type: "POST",
                  cache: false,
                  data: "roomid="+roomid+"&lastid="+lastid+"&action=reset",
                  success: function(html){
                      var parts=html.split("|");
                      var error=parts[1];
                      if (error!=''){alert(error);}
                      document.getElementById('messageentry').innerHTML='';
                      var chat_div = document.getElementById('div_chat');
                      chat_div.innerHTML='<div>Chat has been reset</div>';
                      lastid=parts[0];
                
                  }
                });
                   
            }
            function addSmilie(smilie)
            {
                var mysmile='<img src="'+smilie+'" border=0 height=19 />';
                geditor.execCommand('inserthtml',mysmile);
                geditor.updateTextArea();
                $('#smilieholder').hide();
            }
           
            setInterval('getChatText();',2000)
      </script>
        

    </head>
    <body onload="startChat();">
        <div>
        <img style='float:left;' src='artwork/mango.png' border=0 width=120>
        <div style='padding-top:20px;height:50px;font-weight:bold;font-size:36px;margin-left:20px;float:left;color:#AC1D23;'>Mango Chat</div>
        </div>
        <div style='clear:both;height:0px;width:0px;'></div>
        Current Chat: Please note that newest messages are on the top.
        <div id="div_chat" style="height: 300px; width: 500px; overflow: auto; background-color: #FEFE78; border: 1px solid #555555;">
        </div>
            Enter your chat name: <input type="text" id="sender_name" name="sender_name" style="width: 100px;" value="<?php echo $name;?>"/>
            Choose a channel:
            <select id='roomid' name='roomid' onchange="changeChannel();">
            <?php
            if($userid!=0)
            {
                $sql="SELECT DISTINCT (A.id), A.room_name FROM chat_rooms A, user_chatrooms B WHERE (A.id=B.room_id AND B.user_id=$userid) OR default_room=1 ORDER BY default_room DESC";
                print "<!-- room select $sql -->\n";
                $dbRooms=dbselectmulti($sql);
                if($dbRooms['numrows']>0)
                {
                    foreach($dbRooms['data'] as $room)
                    {
                        print "<option value='$room[id]'>".stripslashes($room['room_name'])."</option>\n";
                    }
                } else {
                    $sql="SELECT * FROM chat_rooms WHERE default_room=1";
                    $dbRooms=dbselectsingle($sql);
                    $room=$dbRooms['data'];
                    print "<!-- tried again with $sql -->\n";
                    print "<option value='$room[id]'>".stripslashes($room['room_name'])."</option>\n";
                }
            } else {
                $sql="SELECT * FROM chat_rooms WHERE default_room=1";
                $dbDefault=dbselectsingle($sql);
                $roomid=$dbDefault['data']['id'];
                $roomname=stripslashes($dbDefault['data']['room_name']);
                print "<option value='$roomid'>$roomname</option>\n";
            }
            ?>
            </select> <br>
            <?php getSmilies();?>
            <span style='float:left'>
            <input type="button" name="btn_smilie" id="btn_smilie" value="Add a Smilie" onclick="document.getElementById('smilieholder').style.display='block';" />          
            <input type="button" name="btn_send_chat" id="btn_send_chat" value="Send" onclick="sendChatText();" />
            </span>
            <span style='float:right;'>
            <a class='submit' href='#' style='margin-right:10px;' onclick="javascript:window.open('chatV2.php','_blank','width=520,height=680,toolbar=0,status=0,location=0');return false;">Open new chat window</a>
            </span>
            <span class='clear'></span>
            <br><br>
            <textarea id="messageentry" name="messageentry" onkeydown="keyCapture(this.id,event);"></textarea>
            <input type="button" name="btn_get_chat" id="btn_get_chat" value="Refresh Chat" onclick="refreshChatText();" />
            <?php if ($candelete)
            {
            ?>
            <input type="button" name="btn_reset_chat" id="btn_reset_chat" value="Reset Chat" onclick="resetChat();" />
               
            <?php    
            }
            if (date("md")=='0919')
            {
                ?>
                <input type=checkbox id='pirate' checked disabled=true /> Today be National 'Talk like a pirate' Day!
                <?php
            } else {
                ?>
                <input type=checkbox id='pirate' /> Check to enable pirate-ese
                <?php
            } 
            ?>
            <input type='hidden' id='sender_id' value='<?php echo $userid;?>' />
    
    <script>
        $(function() {
            $("input:button, input:submit, a.submit").button();
        });
    </script>
    
    </body>
    
</html>

<?php
function getSmilies()
{
  //lets load in our smilies 92 of them in total
  $smiliepath="artwork/smilies/";
  // Get array of smilies
    $smiles = array();
    if ($handle = opendir($smiliepath)) {
        while (false !== ($file = readdir($handle))) {
           if ($file != '.' && $file != '..' && substr($file,1) != '.') {
                $smiles[] = $file;
            }
        }
        closedir($handle);
    }
   //now build our div to hold them, and the listing of all the icons
   print "<div id='smilieholder' style='position:absolute;z-index:500000;top:250px;left:10;border:1px solid black;background-color:white;padding:2px;width:250px;height:200px;display:none;'>\n";
   $i=1;
   foreach($smiles as $key=>$smile)
   {
       //we'll do 10 across, then a return 
       $smile=$smiliepath.$smile;
       print "<a href='javascript:;' onClick='addSmilie(\"$smile\");'><img src='$smile' width='19' height='19' style='margin-left:3px;' border=0></a>";
       if ($i==10)
       {
           print "<br />\n";
           $i=0;    
       } else {
           $i++;
       }
   }
   print "<br /><a href='javascript:;' style='font-weight:bold;color:#AC1D23;' onClick='document.getElementById(\"smilieholder\").style.display=\"none\";return false;'>Close Window</a>";
   print "</div>\n";
}
?>
