<?php
//this script is designed to scan the local 
include("../includes/functions_db.php");
include ('../includes/config.php');
include ('../includes/functions_common.php');
include ('../includes/mail/htmlMimeMail.php');

//need to make sure volume is mounted on linux box, either in fstab or manually with:
//mount -t cifs //10.56.0.3/inputs /media/workflow -o credentials=/root/.smbcredentials,iocharset=utf8,file_mode=0777,dir_mode=0777

set_time_limit (0); // no time limit for the script
error_reporting(E_ERROR);
$wdirectory="\\\\10.56.0.3\\inputs\\PDFout\\"; //working directory
$wdirectory="//10.56.0.3/inputs/PDFout/"; //working directory
$wdirectory="/media/workflow/PDFout/"; //working directory
$emailaddress='tech@idahopress.com';    
$files=getFiles($wdirectory);  //gives me a list of all files in the root directory of PDF out
$ftp_server='ftp.newswest.com';
$ftp_user_name='oliveftp';
$ftp_user_pass='rs6_zY!n';

/*
print "The following files were found and need to be organized:<br>";
$show=implode("<br>",$files);
print $show;
*/
if(count($files)>0)
{
    foreach($files as $file)
    {
        $directory=$wdirectory;
        $name=end(explode("/",$file));
        $pub=substr($name,0,2);
        $date=substr($name,2,4);
        $month=substr($date,0,2);
        $day=substr($date,2,2);
        $pagedate=$month.$day;
        
        $pagepub=$pub;
        $pagesection=substr($name,6,3);
        $pagenumber=substr($name,9,2);
        $cyear=date("Y");
        $cmonth=date("m");
        
        if($month<$cmonth){$fyear=$cyear+1;}else{$fyear=$cyear;}
        $sqldate="$fyear-$month-$day";
        
        print "<br><br>Attempting to move the file $name to $fyear $pub $date folder";
        
        //see if the year folder exists, if not, create it
        $directory=$directory.$fyear;
        if(!file_exists($directory))
        {
            mkdir($directory);    
        }
        //lets combine some IPT and EMI PUBS
        if($pub=='IT' || $pub=='IB'){$pub='IP';}
        if($pub=='ET' || $pub=='EB'){$pub='EM';}
        
        //now check for pub folder inside
        //directory now appends the pub
        $directory=$directory.'/'.$pub;
        
        if(!file_exists($directory))
        {
            mkdir($directory);    
        }
        
        //now check for date folder inside
        //directory now appends the pub
        $directory=$directory.'/'.$date;
        
        if(!file_exists($directory))
        {
            mkdir($directory);    
        }
        
        //now, the structure should exist. lets move the file to that location
        //lets see if the file exists there. if so, delete the existing file first!
        if(file_exists($directory.'/'.$name))
        {
            print "<br> -- Deleting old version of the file<br>";
            $oldfile=$directory.'/'.$name;
            unset($oldfile);
        }
        if(rename($file, $directory.'/'.$name))
        {
            print " -- moved successfully<br>";
            if(($pub=='IP' || $pub=='EM') && $_GET['ftp']!='false') //if it is one of these pubs, then we need to FTP it to pokie
            {
                $upload[]=array("date"=>$date.substr($fyear,2,2),
                "sqldate"=>$sqldate,
                "page_pub"=>$pagepub,
                "page_section"=>$pagesection,
                "page_date"=>$pagedate,
                "page_number"=>$pagenumber,
                "pub"=>$pub,
                "name"=>$name,
                "directory"=>$directory);
                
                
            }
        } else {
            print " -- file was not moved<br>";
        }
        
    }

    if($_GET['ftp']=='false')
    {
        //all done
    } else {
        if(count($upload)>0)
        {
            print "<br><h4>Now storing these pages in the Daily Pages table<br>";
            $dt=date("Y-m-d H:i:s");
            foreach($upload as $page)
            {
                //first see if the filename exists, if so, just update the time
                $csql="SELECT * FROM daily_pages WHERE filename='$page[name]'";
                $dbCheck=dbselectmulti($csql);
                if($dbCheck['numrows']>0)
                {
                    $sql="UPDATE daily_pages SET upload_time='$dt' WHERE filename='$page[name]'";
                    $dbUpdate=dbexecutequery($sql);
                    print " -- Page exists in database, updating timestamp.<br>";
                } else {
                    $sql="INSERT INTO daily_pages (filename, page_pub, page_section, page_date, page_number, upload_time) 
                    VALUES ('$page[name]','$page[page_pub]','$page[page_section]','$page[page_date]','$page[page_number]','$dt')";
                    $dbInsert=dbinsertquery($sql);
                    if($dbInsert['error']!='')
                    {
                        print "Error inserting the record!<br>".$dbInsert['error'];
                    }
                    print " -- Page was inserted into the database.<br>";
                }
                  
            }
            
            print "<br><h4>Now uploading files to the Newswest FTP server:</h4>";
            
            // set up basic connection
            $conn_id = ftp_connect($ftp_server); 
            
            // login with username and password
            $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 
            
            // turn passive mode on
            ftp_pasv($conn_id, TRUE);
            
            // check connection
            if ((!$conn_id) || (!$login_result)) { 
                    echo "FTP connection has failed!<br>";
                    echo "Attempted to connect to $ftp_server as user $ftp_user_name<br>"; 
                    exit; 
                } else {
                    echo "Connected to $ftp_server, as user $ftp_user_name<br>";
                }
            echo "Base directory is " . ftp_pwd($conn_id) . "<br>";

            // try to change the directory to somedir
            if (ftp_chdir($conn_id, "/Nampa/")) {
                echo "Changed directory to: " . ftp_pwd($conn_id) . "<br />\n";
                
                foreach($upload as $file)
                {
                    if($file['pub']=='EM')
                    {
                        $ftpdir='EMI'.$file['date'];
                    } else {
                        $ftpdir=$file['date'];
                    }
                    if (!ftp_chdir($conn_id, $ftpdir)) { //if the directory can't be changed to, it doesn't exist, so create it!
                        ftp_mkdir($conn_id, $ftpdir);
                        ftp_chdir($conn_id, $ftpdir);//then change to it    
                    }
                    echo "Changed directory to: " . ftp_pwd($conn_id) . "<br />\n";
                    //now ftp put the file
                    $remotefile=$file['name'];
                    $localfile=$file['directory'].'\\'.$file['name'];
                    if (ftp_put($conn_id, $remotefile, $localfile, FTP_BINARY)) {
                        echo "Page successfully uploaded to ftp server<br>";
                    } else {
                        echo "There was a problem uploading the file $localfile to $remotefile.<br>";
                    }
                    print "Setting directory back to root 'Nampa'<br>";
                    ftp_chdir($conn_id, "/Nampa/");  
                }
                
                
            } else { 
                echo "<br><br>Couldn't change directory to Nampa!<br>";
            }
            ftp_close($conn_id);
            checkForAllPages(); //we only check for all pages when there is an IPT or EMI page
        }
    }
} else {
    print "No files at this time.<br>";
}


function checkForAllPages()
{
    //this function checks to see if all pages for a specific day are uploaded
    //first, we figure out what day it is
    if(date("H")<12)
    {
        $today=date("md");
    } else {
        $today=date("md",strtotime("+1 day")); //get tomorrows date if we are in the afternoon
    }
    //get all the pages that are scheduled for the IP pub for the found date
    $sql="SELECT * FROM job_pages WHERE pub_code='IP' AND pub_date='$today' AND version=1 ORDER BY pub_code, section_code, page_number";
    $dbPages=dbselectmulti($sql);
    $allfound=true;
    if($dbPages['numrows']>0)
    {
        //loop through the pages and see if each one is in the daily pages table
        foreach($dbPages['data'] as $page)
        {
            $sql="SELECT * FROM daily_pages WHERE page_pub='$page[pub_code]' AND page_section='$page[section_code]' 
            AND page_number='$page[page_number]' AND page_date='$page[pub_date]'";
            $dbUploaded=dbselectmulti($sql);
            if($dbUploaded['numrows']==0){$allfound=false;$missing[]="Pub: $page[pub_code] - Section: $page[section_code] - Page: $page[page_number] - Date: $page[pub_date]<br>";}
        }
    } else {
        //no section set up in Mango yet :(
        $allfound=false;
    }
    if($allfound)
    {
        //all pages have been uploaded! that means we need to send an email to Pokie letting them know
        
        $mail = new htmlMimeMail();
        $mail->setHtml($message);
        $mail->setFrom($GLOBALS['systemEmailFromAddress']);
        $mail->setSubject("All pages uploaded for IPT $today");
        $mail->setHeader('Sender','Mango');
        $result = $mail->send(array($emailaddress),'smtp');
        
    } else {
        //missing pages still
        print "<h3>The following pages are missing from the upload at this time:</h3>";
        $missing=implode("\n",$missing);
        print $missing;
    }
}

function getFiles($directory,$exempt = array('.','..','.ds_store','thumbs.db','.svn'),&$files = array()) { 
    $handle = opendir($directory); 
    while(false !== ($resource = readdir($handle))) { 
        if(!in_array(strtolower($resource),$exempt)) { 
            if(!is_dir($directory.$resource.'/')) 
             $files[] = $directory.$resource; 
        } 
    } 
    closedir($handle); 
    return $files; 
} 




?>
