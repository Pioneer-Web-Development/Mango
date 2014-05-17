<?php
  //this file is used to create the XML file for the Blox System audio processing job.
  if ($_POST)
  {
      processform();
  } else {
      displayform();
  }
  
  
  function displayform()
  {
        print "Notes:<br />";
        print "You need to make sure the name of the audio file with no space or extra '.'s except for the .mp3<br />\n";
        print "Then upload to the ftp server /audio directory at town news:<br />\n";
        print "Server: bloxcms.com<br />User: 1800<br />Password: H6432Obm<br />Store the file in the /audio directory.<br />You need to upload the file that will be generated with this form to the same directory at the same time you upload the audio file!<br /><br />\n";
      
        print "<form method=post enctype='multipart/form-data'>\n";
        print "Section: <input type='text' name='section' value='$_POST[section]'><br />\n";
        print "Your Name: <input type='text' name='yourname' value='$_POST[yourname]'><br />\n";
        print "Headline: <input type='text' name='headline' value='$_POST[headline]'><br />\n";
        print "Caption: <textarea name='caption'>$_POST[caption]</textarea><br />\n";
        print "Filename: <input type='text' name='filename' value='$_POST[filename]'><br />\n";
        //print "Audio File: <input type='file' name='audiofile' value=''><br />\n";
        print "<input type=submit name=submit value='Submit'>\n";
        print "</form>\n";  
  }
  
  function processform()
  {
     $date=date("Ymd\This");
      $xml='<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE nitf PUBLIC "-//IPTC-NAA//DTD NITF 3.1//EN"
"http://www.iptc.org/std/NITF/3.4/specification/dtd/nitf-3-4.dtd">

<nitf>
<head>
<docdata management-status="embargoed">
<date.release norm="'.$date.'"/>
</docdata>
<pubdata type="web" position.section="'.$_POST['section'].'" position.sequence="0"/>
</head>
<body>
<body.head>
<hedline>
<hl1>'.addslashes($_POST['headline']).'</hl1>
</hedline>
</body.head>
<body.content>
<media media-type="audio">
<media-reference source="'.$_POST['filename'].'" />
<media-caption>'.addslashes($_POST['caption']).'</media-caption>
<media-producer>'.addslashes($_POST['yourname']).'</media-producer>
</media>
</body.content>
</body>
</nitf>';
     $outfile=explode('.',$_POST['filename']);
     $outfile=$outfile[0].".xml";
     header('Content-Type: text/plain'); // plain text file
     header('Content-Disposition: attachment; filename="'.$outfile.'"');
     echo $xml;
      
  }

?>
