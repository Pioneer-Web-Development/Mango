<?php
  // camera viewer
include("includes/mainmenu.php") ;  
?>
<div id='tabs' style='width:670px;'>
    <ul>
        <li><a href='#press'>Pressroom</a></li>
        <li><a href='#plate'>Plateroom</a></li>
    </ul>
    
    <div id='press'>
        <applet CODE="video_java.class" NAME="testApplet" WIDTH="640" HEIGHT="480" STYLE="font-size: 10pt; font-family: Arial; border-style: ridge; border-width: 1px; text-align:center">
        <param NAME="PORT" VALUE="5000">
        <param NAME="MODE" VALUE="1">
        <param NAME="DISPLAY_FLIP" VALUE="0">
        <param NAME="codebase" VALUE="http://10.56.0.99">
        </applet> 

        <p ALIGN="center" STYLE="margin-top: 0; margin-bottom: 0">
        <span LANG="EN-US" STYLE=
        "font-size: 10.0pt; font-family: Times New Roman"><font SIZE="2"
        FACE="Geneva, Arial, Helvetica, sans-serif">If no image appears,
        please download Java Applet <a TARGET="_blank" HREF=
        "http://java.com/en/download/windows_automatic.jsp"><span STYLE="color: #0000FF; text-decoration: underline">
        Here</span></a>.</font></span></p>
    
    </div>
    
    <div id='plate'>
        <SCRIPT LANGUAGE="JavaScript">
        // Set the BaseURL to the URL of your camera
        var BaseURL = "http://10.56.0.98/";

        // DisplayWidth & DisplayHeight specifies the displayed width & height of the image.
        // You may change these numbers, the effect will be a stretched or a shrunk image
        var DisplayWidth = "640";
        var DisplayHeight = "480";

        // This is the path to the image generating file inside the camera itself
        var File = "axis-cgi/mjpg/video.cgi?resolution=640x480&clock=0&date=0&text=0";
        // No changes required below this point
        var output = "";
        if ((navigator.appName == "Microsoft Internet Explorer") &&
           (navigator.platform != "MacPPC") && (navigator.platform != "Mac68k"))
        {
          // If Internet Explorer under Windows then use ActiveX 
          output  = '<OBJECT ID="Player" width='
          output += DisplayWidth;
          output += ' height=';
          output += DisplayHeight;
          output += ' CLASSID="CLSID:DE625294-70E6-45ED-B895-CFFA13AEB044" ';
          output += 'CODEBASE="';
          output += BaseURL;
          output += 'activex/AMC.cab#version=4,1,4,0">';
          output += '<PARAM NAME="MediaURL" VALUE="';
          output += BaseURL;
          output += File + '">';
          output += '<param name="MediaType" value="mjpeg-unicast">';
          output += '<param name="ShowStatusBar" value="0">';
          output += '<param name="ShowToolbar" value="0">';
          output += '<param name="AutoStart" value="1">';
          output += '<param name="StretchToFit" value="1">';
          output += '<BR><B>Axis Media Control</B><BR>';
          output += 'The AXIS Media Control, which enables you ';
          output += 'to view live image streams in Microsoft Internet';
          output += ' Explorer, could not be registered on your computer.';
          output += '<BR></OBJECT>';
        } else {
          // If not IE for Windows use the browser itself to display
          theDate = new Date();
          output  = '<IMG SRC="';
          output += BaseURL;
          output += File;
          output += '&dummy=' + theDate.getTime().toString(10);
          output += '" HEIGHT="';
          output += DisplayHeight;
          output += '" WIDTH="';
          output += DisplayWidth;
          output += '" ALT="Camera Image">';
        }
        document.write(output);
        document.Player.ToolbarConfiguration = "play,+snapshot,+fullscreen"

        // Remove the // below to use the code for Motion Detection. 
          // document.Player.UIMode = "MDConfig";
          // document.Player.MotionConfigURL = "/axis-cgi/operator/param.cgi?ImageSource=0"
          // document.Player.MotionDataURL = "/axis-cgi/motion/motiondata.cgi";
        </script>
    </div>
    
</div>

<script type="text/javascript">
  $(function() {
    $( '#tabs' ).tabs();
    });
</script>
<?php
   footer();
?>      