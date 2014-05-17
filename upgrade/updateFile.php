<?php
//get contents and write to the specified file
$paths=array('/includes/'=>"Includes",'/styles/'=>"Styles",'/upgrade/'=>"Upgrade","/includes/jscripts/"=>"Javascripts",'/'=>"Core files");
if($_POST)
{
    getfile();
} else {
    include("../includes/functions_formtools.php");
    print "<form method=post>\n";
    make_select('path',0,$paths,'Select file type');
    make_text('filename','','File to synch');
    make_submit('submit','Process file');
    print "</form>\n";

}

function getfile()
{
    $coreserver="71.39.210.138";
    
    $path=$_POST['path'];
    $file=$_POST['filename'];
    $filename=$path.$file;
    $filename=urlencode($file);
    
    $content=file_get_contents("http://".$coreserver."/upgrade/contentDisplay.php?file=$filename");
    if($content!='')
    {
        $f = @fopen($filename, 'w');
        if (!$f) {
            print "Unable to open file for writing<br>";
        } else {
            $bytes = fwrite($f, $content);
            fclose($f);
            print "File successfully updated";
        }
        /*
        //now put that content into the local file
        if (file_put_contents($path.$file,$content))
        {
            print "File successfully updated";
        } else {
            print "Problem saving the file contents to $path $file";
        }
        */
    } else {
        print "File not found or was empty";
    }
}
?>
