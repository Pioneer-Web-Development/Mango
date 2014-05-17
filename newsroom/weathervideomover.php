<?php
  //weather video move
set_time_limit(3600);    
//first, get a list of files from the ftp server
$ftpserver['server']='ftp.myidahopress.com';
$ftpserver['user']='iptadmin';
$ftpserver['password']='111637';
$ftpserver['directory']='/kboi/';


$bloxserver['server']='bloxcms.com';
$bloxserver['user']='1800';
$bloxserver['password']='H6432Obm';
$bloxserver['directory']='/videos/';

if($_GET['mode']=='test')
{
    upload_file('../tests/','0709 Tailgate.mp4',$bloxserver);
}

$bloxMeridianServer['server']='ftp-chicago2.bloxcms.com';
$bloxMeridianServer['user']='1337';
$bloxMeridianServer['password']='Meridian21';
$bloxMeridianServer['directory']='/videos/';

$conn_id = ftp_connect($ftpserver['server']); 

// login with username and password
$login_result = ftp_login($conn_id, $ftpserver['user'], $ftpserver['password']); 

// check connection
if ((!$conn_id) || (!$login_result)) { 
        //echo "FTP connection has failed!";
        //echo "Attempted to connect to $ftp_server for user $ftp_user_name"; 
        //exit;
        //exit;
        return false; 
    } else {
        if($_GET['bug']){echo "Connected to $ftpserver[server], for user $ftpserver[user]<br>";}
    }
if($_GET['bug']){echo "Current directory: " . ftp_pwd($conn_id) . "<br>\n";}

ftp_pasv($conn_id, true);

// try to change the directory to somedir
if (ftp_chdir($conn_id, $ftpserver['directory'])) {
    if($_GET['bug']){echo "Current directory is now: " . ftp_pwd($conn_id) . "<br>\n";}
} else { 
    return false;
    if($_GET['bug']){echo "Couldn't change directory<br>\n";}
}
$contents = ftp_nlist($conn_id, "."); 
print "<h4>Contents</h4><pre>";
print_r($contents);
print "</pre>\n";
$i=0;
$info='';
$info=implode("\n",$contents);
foreach($contents as $key=>$file)
{
    print "Processing file: $file<br />";
    $info.="Processing file: $file<br />";
    $ext=explode(".",$file);
    $ext=end($ext);
    if($ext=='mp4')
    {
        //download the file to the weathervideo folder
        // path to remote file
        // open some file to write to
        $handle = fopen('weathervideo/'.$file, 'w');

        // try to download $remote_file and save it to $handle
        if (ftp_fget($conn_id, $handle, $file, FTP_BINARY, 0)) {
         $info.="successfully written to $local_file\n";
        } else {
         $info.="There was a problem while downloading $remote_file to $local_file\n";
         exit;
        }
        // close the connection and the file handler
        fclose($handle);
        
        //generate the xml file for the video
        //generate a name... if it's before noon it's am
        $hour=date("H");
        $date=date("m-d-Y");
        if($hour<12){$name="KBOI Weather Video AM $date";}else{$name="KBOI Weather Video PM $date";}
        $xml='<?xml version="1.0" encoding="utf-8"?>
        <!DOCTYPE nitf PUBLIC "-//IPTC-NAA//DTD NITF 3.1//EN"
        "http://www.iptc.org/std/NITF/3.4/specification/dtd/nitf-3-4.dtd">
        <nitf>
        <head>
        <docdata management-status="embargoed">
        <doc-id id-string="'.$name.'"/>
        <date.release norm="'.date("Ymd\THms").'"/>
        </docdata>
        <pubdata type="web" position.section="weather" position.sequence="0"/>
        </head>
        <body>
        <body.head>
        <hedline>
        <hl1>'.$name.'</hl1>
        </hedline>
        </body.head>
        <body.content>
        <media media-type="video">
        <media-reference source="'.$file.'" />
        <media-caption>Weather broadcast for '.$date.'</media-caption>
        <media-producer>KBOI News 2</media-producer>
        </media>
        </body.content>
        </body>
        </nitf>';
        $xmlFile = str_replace("mp4","xml",$file);
        $fh = fopen('weathervideo/'.$xmlFile, 'w');
        fwrite($fh, $xml);
        fclose($fh);
        
        //delete file from server
        if (ftp_delete($conn_id, $file)) {
         $info.="$file deleted successful\n";
        } else {
         $info.="could not delete $file\n";
        }
        $toupload[$i]['xml']=$xmlFile;
        $toupload[$i]['mp4']=$file;
        $i++;
        $info.="Added $file to the upload list<br>";
    }
    if($ext=='txt')
    {
        //download the file to the weathervideo folder
        // path to remote file
        // open some file to write to
        $handle = fopen('weathervideo/'.$file, 'w');

        // try to download $remote_file and save it to $handle
        if (ftp_fget($conn_id, $handle, $file, FTP_BINARY, 0)) {
         $info.="successfully written to $local_file\n";
        } else {
         $info.="There was a problem while downloading $remote_file to $local_file\n";
         exit;
        }
        // close the connection and the file handler
        fclose($handle);
        
        //generate the xml file for the video
        //generate a name... if it's before noon it's am
        $hour=date("H");
        $date=date("m-d-Y");
        if($hour<12){$name="KBOI Weather Video AM $date";}else{$name="KBOI Weather Video PM $date";}
        $xml='<?xml version="1.0" encoding="utf-8"?>
        <!DOCTYPE nitf PUBLIC "-//IPTC-NAA//DTD NITF 3.1//EN"
        "http://www.iptc.org/std/NITF/3.4/specification/dtd/nitf-3-4.dtd">
        <nitf>
        <head>
        <docdata management-status="embargoed">
        <doc-id id-string="'.$name.'"/>
        <date.release norm="'.date("Ymd\THms").'"/>
        </docdata>
        <pubdata type="web" position.section="weather" position.sequence="0"/>
        </head>
        <body>
        <body.head>
        <hedline>
        <hl1>'.$name.'</hl1>
        </hedline>
        </body.head>
        <body.content>
        <media media-type="video">
        <media-reference source="'.$file.'" />
        <media-caption>Weather broadcast for '.$date.'</media-caption>
        <media-producer>KBOI News 2</media-producer>
        </media>
        </body.content>
        </body>
        </nitf>';
        $xmlFile = str_replace("mp4","xml",$file);
        $fh = fopen('weathervideo/'.$xmlFile, 'w');
        fwrite($fh, $xml);
        fclose($fh);
        
        //delete file from server
        if (ftp_delete($conn_id, $file)) {
         $info.="$file deleted successful\n";
        } else {
         $info.="could not delete $file\n";
        }
        $toupload[$i]['xml']=$xmlFile;
        $toupload[$i]['txt']=$file;
        $i++;
        $info.="Added $file to the upload list<br>";

    }
}


ftp_close($conn_id);

//now upload the files
if($i>0)
{
    $info.="Starting upload process<br>";
    $message='';
    $info.=var_dump($toupload);
    foreach($toupload as $key=>$upload)
    {
        if(upload_file('weathervideo',$upload['mp4'],$bloxMeridianServer))
        {
            upload_file('weathervideo',$upload['xml'],$bloxMeridianServer); 
            $message.="Uploaded file ".$upload['mp4']." at ".date("m/d/Y H:i")."\n";
        }    
        if(upload_file('weathervideo',$upload['mp4'],$bloxserver))
        {
            upload_file('weathervideo',$upload['xml'],$bloxserver); 
            //now delete the files from the local storage
            unlink('weathervideo/'.$upload['xml']);
            unlink('weathervideo/'.$upload['mp4']);
            $message.="Uploaded file ".$upload['mp4']." at ".date("m/d/Y H:i")."\n";
        } else {
            $message.="Unable to upload $upload[mp4] to bloxserver.\n";
        }   
           
    }
    if($message=='')
    {
        $message="There was a problem uploading video files.<br />\n";
    }
    $message.=$info;
    
    $to      = 'tech@idahopress.com';
    $subject = 'Weather Video Auto-upload';
    $headers = 'From: jhansen@idahopress.com' . "\r\n" .
        'Reply-To: jhansen@idahopress.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $result=mail($to, $subject, $message, $headers);
        
}


function upload_file($path,$file,$bloxserver)
{
    global $message;
    $message.="Begining upload process for $bloxserver[server]<br />\n";
    $ftp_server=$bloxserver['server'];
    $ftp_user_name=$bloxserver['user'];
    $ftp_user_pass=$bloxserver['password'];

    // set up basic connection
    $conn_id = ftp_connect($ftp_server); 

    // login with username and password
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 

    // check connection
    if ((!$conn_id) || (!$login_result)) { 
        $message.="FTP connection has failed!\nAttempted to connect to $ftp_server for user $ftp_user_name\n";
        if($_GET['bug']){echo "FTP connection has failed!";echo "Attempted to connect to $ftp_server for user $ftp_user_name";}    
            return false; 
        } else {
            //echo "Connected to $ftp_server, for user $ftp_user_name";
            $message.="Connected to $ftp_server, for user $ftp_user_name\n";
        }
    if($_GET['bug']){echo "Current directory: " . ftp_pwd($conn_id) . "\n";}
    $source_file = $path.'/'.$file;
    $destination_file = $bloxserver['directory'].$file;
    $message.="Source is $source_file \nServer: $ftp_server\n Destination: $destination_file\n";
    
    ftp_pasv($conn_id, true);

    // try to change the directory to somedir
    if (ftp_chdir($conn_id, $bloxserver['directory'])) {
        //echo "Current directory is now: " . ftp_pwd($conn_id) . "<br>\n";
        $message.="Current directory is now: " . ftp_pwd($conn_id) . "\n";
    } else { 
        return false;
        //echo "Couldn't change directory<br>\n";
    }
    // upload the file
    $upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY); //FTP_ASCII OR FTP_BINARY
    // check upload status
    if (!$upload) { 
        $message.="FTP upload has failed for $source_file to $ftp_server as $destination_file.\n";
        echo "FTP upload has failed for $source_file to $ftp_server as $destination_file!";
        return false;
    } else {
        echo "Uploaded $source_file to $ftp_server as $destination_file<br>";
        $message.="Uploaded $source_file to $ftp_server as $destination_file\n";
        return true;
    }

} 
?>
