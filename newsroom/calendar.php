<?php
  error_reporting (0);
  //this script imports a CSV file, parses it, then formats it as an RTF file and prompts for download.
  if($_POST['submit']=='Process File')
  {
      process_file();
  } else {
        require ('../includes/functions_db.php');
        require ('../includes/functions_formtools.php');
        require ('../includes/functions_graphics.php');
        require ('../includes/config.php');
        require ('../includes/functions_common.php');
        ?>
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
                    "http://www.w3.org/TR/html4/strict.dtd">  
        <html>
        <head>
        <title>Blox Calendar Event Converter</title>
        <META name="author" content="Joe Hansen <jhansen@idahopress.com>">
        <META name="copyright" content="&copy; <?php echo date("Y"); ?> Pioneer Newspapers Inc - Joe Hansen">
        <META name="robots" content="none">
        <META http-equiv="cache-control" content="no-cache">
        <META http-equiv="pragma" content="no-cache">
        <META http-equiv="content-type" content="text/html; charset=UTF-8">
        <META http-equiv="expires" content="0">
        <?php
        print "<form method=post enctype='multipart/form-data'>\n";
      make_file('original','Source','Please choose the source file.');
      make_submit('submit','Process File');    
      print "</form>\n";
      print "</body></html>\n";
  }
  
function process_file()
{
    include('rtf_class/class_rtf.php');

    $file=$_FILES['original']['tmp_name'];
    $contents=file_get_contents($file);
    //break into multiple lines
    
    //loop through every character, look for a quote mark and go into replacement
    $inquote=0;
    for($i=0;$i<=strlen($contents);$i++)
    {
        if(substr($contents,$i,1)=='"')
        {
            if($inquote)
            {
                $inquote=0;
            } else {
                $inquote=1;
            }
        }
        if(substr($contents,$i,1)=="," && $inquote)
        {
            //print "Found a comma and replaced it<br>";
            $part1=substr($contents,0,$i);
            $part2=substr($contents,$i+1);
            $contents=$part1.'|'.$part2;    
        }
        if(substr($contents,$i,1)=="\n" && $inquote)
        {
            //print "Found a backslash<br>";
            $part1=substr($contents,0,$i);
            $part2=substr($contents,$i+1);
            $contents=$part1.' '.$part2;
                
        }
    }
    
    $contents=explode("\n",$contents);
    
    array_shift($contents);
    
    
    if(count($contents)>0)
    {
        $output="";
        foreach($contents as $event)
        {
            $parts=explode(",",$event);
            //print_r($parts);
            /*
            0 = uuid
            1 = title
            2 = start date 
            3 = end date
            4 = start time
            5 = end time
            6 = url 
            7 = description
            8 = contact
            9 = flags   (free, featured are possible values here... )
            10= author_name 
            11= author_email
            12= priority
            13= cost 
            14= website
            15= venue_uuid
            16= venue_name
            17= venue_address
            18= venue_city
            19= venue_state
            20= venue_zip 
            21= venue_country
            22= contact_name 
            23= contact_phone   
            24= contact_email  
            25= published
            
            */
            $title=$parts[1];
            $city=strtoupper($parts[18]);
            if($city!=''){$city.=" - ";}
            $startdate=date("l, M jS",strtotime($parts[2]));
            $starttime=date("g:i a",strtotime($parts[4]));
            $venue=$parts[16];
            $address=trim($parts[17]);
            if($address!=''){
                if(substr($address,strlen($address)-1,1)!='.')$address.=".";
            }
            $description=str_replace("|",",",$parts[7]);
            if($parts[9]=='free')
            {
                $cost='FREE';
            } else {
                $cost=$parts[13];
            }
            if($parts[23]!='')
            {
                $contact="Call ".$parts[23];
                if($parts[24]!='')
                {
                    $contact.=" or email ".$parts[24].".";
                } else {
                    $contact.="."; 
                }
            } elseif($parts[24]!='')
            {
                $contact="Email ".$parts[24].".";
            }
            
            //build the output
            $newline="<STRONG>$city$title:</STRONG> $startdate $starttime, $venue, $address. $description $cost. $contact<BR>";
            $newline=str_replace("\"","",$newline);
            if ($title!="")
            {
                $output.=$newline;
            }
            $title="";
            $city="";
            $startdate="";
            $starttime="";
            $venue="";
            $address="";
            $description="";
            $cost="";
            $contact="";
        }
        
        
        
        /*
        header("Content-Type: text/plain\n");
        header("Content-Disposition: attachment; filename=calendar.txt");
        print $output;
        */
        
        /*
         $rtf = new rtf();
         $rtf->setPaperSize(5);
         $rtf->setPaperOrientation(1);
         $rtf->setDefaultFontFace(1);
         $rtf->setDefaultFontSize(12);
         $rtf->setAuthor("IPT");
         $rtf->setOperator("IPT");
         $rtf->setTitle("RTF Document");
         $rtf->setFilename("calendar_".date("m-d-Y").'.rtf');
         $rtf->addColour("#000000");
         $rtf->addText($output);
         $rtf->getDocument();
        */
        
        header("Content-Type: text/enriched\n");
        header("Content-Disposition: attachment; filename=calendar_".date("m-d-Y").'.rtf');
        $output=htmlToRTF($output);
        ?>{\rtf1\ansi\ansicpg1252\deff0\deflang1033{\fonttbl{\f0\fnil\fcharset0 Arial;}}
{\*\generator Msftedit 5.41.21.2510;}\viewkind4\uc1\pard\sa200\sl276\slmult1\lang9\f0\fs22 <?php
        print $output;
        print " }";
        /*
        SAMPLE OUTPUT FORMAT
        <b>CALDWELL � Canyon County Historical Society meeting:</b> 1 p.m., Faith Lutheran Church, 2915 S. Montana Ave.  Local poet and humorist Art Honey will entertain with his cowboy poetry. Free. Call 476-7611 or e-mail info@canyoncountyhistory.com.
        <b>VENUE_CITY - TITLE:</b> START_DAY START TIME, VENUE NAME, VENUE ADDRESS. DESCRIPTION. COST/FLAG. CONTACT PHONE/EMAIL
        */
    } else {
        print "File was empty";
    }
} 


// Parse the text input to RTF
function htmlToRTF($doc_buffer) {
    $doc_buffer = preg_replace("/<P>(.*?)<\/P>/mi", "\\1\\par ", $doc_buffer);
    $doc_buffer = preg_replace("/<STRONG>(.*?)<\/STRONG>/mi", "\\b \\1\\b0 ", $doc_buffer);
    $doc_buffer = preg_replace("/<EM>(.*?)<\/EM>/mi", "\\i \\1\\i0 ", $doc_buffer);
    $doc_buffer = preg_replace("/<U>(.*?)<\/U>/mi", "\\ul \\1\\ul0 ", $doc_buffer);
    $doc_buffer = preg_replace("/<STRIKE>(.*?)<\/STRIKE>/mi", "\\strike \\1\\strike0 ", $doc_buffer);
    $doc_buffer = preg_replace("/<SUB>(.*?)<\/SUB>/mi", "{\\sub \\1}", $doc_buffer);
    $doc_buffer = preg_replace("/<SUP>(.*?)<\/SUP>/mi", "{\\super \\1}", $doc_buffer);
    $doc_buffer = str_replace("<BR>", "\\par ", $doc_buffer);
    $doc_buffer = str_replace("<TAB>", "\\tab ", $doc_buffer);
    $doc_buffer = str_replace("’", "'", $doc_buffer);
    $doc_buffer = str_replace("–", "-", $doc_buffer);
    
    $doc_buffer = str_replace("\n", "\\par ", $doc_buffer);
        
    return $doc_buffer;
}
?>
