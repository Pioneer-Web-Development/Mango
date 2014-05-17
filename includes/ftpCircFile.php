<?php
include("functions_db.php");
include("config.php");
include("functions_common.php");

$ftp_server='10.62.1.105';
$ftp_user_name='srt14';
$ftp_user_pass='kwiwee64';

// set up basic connection
$conn_id = ftp_connect($ftp_server); 

// login with username and password
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 

// check connection
if ((!$conn_id) || (!$login_result)) { 
        echo "FTP connection has failed!";
        echo "Attempted to connect to $ftp_server for user $ftp_user_name"; 
        exit; 
    } else {
        echo "Connected to $ftp_server, for user $ftp_user_name";
    }
echo "Current directory: " . ftp_pwd($conn_id) . "\n";

//build the date
$month=strtolower(date("M"));
$day=date("d");
$remotefile='14'.$month.$day.'si.';
$possiblesizes=array("10,15,20,25,30,40,50,80,100");
// try to change the directory to somedir
if (ftp_chdir($conn_id, "/app/pbs/exchange/cm/")) {
    echo "Current directory is now: " . ftp_pwd($conn_id) . "<br />\n";
    
    //get list of files in the directory and see if we can find our file
    $list=ftp_nlist($conn_id,"/app/pbs/exchange/cm/");
    print $list;
    
    if (ftp_get($conn_id, "../circdata/circdata.txt", "city candidates.doc", FTP_ASCII)) {
        echo "Successfully written to $local_file\n";
    } else {
        echo "There was a problem\n";
    }
} else { 
    echo "Couldn't change directory\n";
}


/*
14        = company
oct       = month
27        = day
si         = stacker interface
30        = bundle size
*/

// close the FTP stream 
ftp_close($conn_id);
dbclose(); 
?>
