<?php
  //this is a test of converting a PDF to a png image file
//configure the database connection
$sql_server='localhost';
$sql_user="classpdf";
$sql_pass="slcrbt5";
$sql_database="townnews_pdf";
$localincoming='incoming';
$localoutgoing='outgoing';
//open the database connection
print "You have reached the Pioneer PDF conversion utility<br />";
dbconnect();
  
switch($_GET['mode'])
{
    case 'input':
    download_files();
    break;
    
    case 'process':
    convert_pdf();
    break;
    
    case 'output':
    upload_files();
    break;
} 
  
  
function convert_pdf()
{
    global $localincoming, $localoutgoing;
    $pdfroot='C:\websites\myidahopress\pdf';
    //lets look and see how many files are waiting to be processed in the database
    $sql="SELECT * FROM process_pdf WHERE status=1";
    $dbFiles=dbselectmulti($sql);
    if ($dbFiles['numrows']>0)
    {
        foreach($dbFiles['data'] as $file)
        {
            
            $pdate=date("Y-m-d H:i:s");
            $newname=explode(".",$file['pdf_file']);
            $newname=$newname[0].'.jpg'; 
            $process="C:\\ImageMagick\\convert -density 200 $pdfroot\\$localincoming\\$file[pdf_file]  -colorspace RGB -scale 600 $pdfroot\\$localoutgoing\\$newname";
            print "Converting file $file[pdf_file] with $process<br />";
            exec($process,$out,$returnval);
            if ($returnval==0)
            {
                unlink($localincoming."/".$file['pdf_file']);
            }
            $updatesql="UPDATE process_pdf SET status=2, process_datetime='$pdate' WHERE id=$file[id]";
            $dbUpdate=dbexecutequery($updatesql);
        }
        //exec('C:\ImageMagick\convert -density 200 form_birth_en.pdf -colorspace RGB -scale 200 testimage.png',$out,$returnval);
    }
}

function download_files()
{
    global $localincoming;
    //we will look at each ftp site and download any present files
    $sql="SELECT * FROM pioneer_sites";
    $dbSites=dbselectmulti($sql);
    if ($dbSites['numrows']>0)
    {
        foreach ($dbSites['data'] as $site)
        {
            //get list of files
            print "Getting list of files for $site[site_name]<br />";
            $files=ftp_action('list','',$site);
            
            print "Found ".count($files)." files for this site<br />";
            $idate=date("Y-m-d H:i:s");
            if ($files)
            {
                print "These are the files that we will be transferring...";
                foreach($files as $key=>$value)
                {
                    print "<li>$value</li>";
                }
                foreach($files as $id=>$filename)
                {
                    if ($filename!='.' && $filename!='..' && $filename!='')
                    {
                        print "Getting file $filename from $site[site_name]<br />";
                        if (ftp_action('get',$filename,$site))
                        {
                            print "Deleting file $filename from $site[site_name]<br />";
                            if (ftp_action('delete',$filename,$site))
                            {
                                print "Successfully deleted from the TN site<br />";
                            } else 
                            {
                                print "Problem deleting the file from the TN server<br />";
                            }   
                            //add this file to the database for this site
                            $noext=explode(".",$filename);
                            $noext=$noext[0];
                            $sql="INSERT INTO process_pdf (file_noext, pdf_file, import_datetime, site_id, status) 
                            VALUES ('$noext', '$filename', '$idate', $site[id],1)";
                            $dbInsert=dbinsertquery($sql);  
                        } 
                    }   
                }
            }    
        }
    }
}

function upload_files()
{
    global $localoutgoing;
    //get a list of the files in the outgoing directory, check which site they go to and upload them, then delete them
    $files=array();
    if ($handle = opendir($localoutgoing)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            $files[]=$file;
        }
    }
    closedir($handle);
    if (count($files)>0)
    {
        
        foreach($files as $key=>$filename)
        {
            $edate=date("Y-m-d H:i:s");
            $cname=str_replace(".jpg","",$filename);
            $sql="SELECT * FROM process_pdf WHERE file_noext='$cname' AND status=2";
            $dbFile=dbselectsingle($sql);
            if ($dbFile['numrows']>0)
            {
                //means this should be a valid file, upload it to the specified site
                $file=$dbFile['data'];
                $sql="SELECT * FROM pioneer_sites WHERE id=$file[site_id]";
                $dbSite=dbselectsingle($sql);
                $site=$dbSite['data'];
                print "Uploading file $filename to $site[site_name]<br />";
                if (ftp_action('put',$filename,$site))
                {
                    //file successfully uploaded, update database and delete local file
                    $sql="UPDATE process_pdf SET export_datetime='$edate', status=3 WHERE id=$file[id]";
                    $dbUpdate=dbexecutequery($sql);
                    unlink($localoutgoing."/".$filename);    
                }
            }
        }
    }
}
    
}


function ftp_action($method,$file,$site)
{
    global $localincoming,$localoutgoing;
    $ftp_server="bloxcms.com";
    $ftp_user_name=$site['ftp_user'];
    $ftp_user_pass=$site['ftp_pass'];

    // set up basic connection
    $conn_id = ftp_connect($ftp_server); 

    // login with username and password
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 

    // check connection
    if ((!$conn_id) || (!$login_result)) { 
        echo "FTP connection has failed!<br />";
        echo "Attempted to connect to $ftp_server for user $ftp_user_name<br />"; 
        exit; 
    }

    if ($method=='get' || $method=='list' || $method=='delete')
    {
        $remotefolder="/vdata/pdf/";
    } else {
        $remotefolder='/vdata/';
    }
    ftp_pasv($conn_id, true);
    // try to change the directory to somedir
    if (ftp_chdir($conn_id, $remotefolder)) {
        if ($method=='put')
        {
            // upload the file
            if (ftp_put($conn_id, $file, $localoutgoing."/".$file, FTP_BINARY)) { 
                $test=true;
            } else {
                $test=false;
            }
        }elseif($method=='get')
        {
            if (ftp_get($conn_id, $localincoming."/".$file, $file, FTP_BINARY)) {
                $test=true;
            } else {
                $test=false;
            }
        }elseif($method=='list')
        {
            $contents = ftp_nlist($conn_id, $remotefolder);
            //print "For site $site[site_name]<br />";
            //print_r($contents);
            ftp_close($conn_id);
            if (count($contents)>0 && $contents!='')
            {
                $rfiles=array();
                foreach($contents as $key=>$value)
                {
                    $rfiles[]=str_replace("/vdata/pdf/","",$value);    
                }
                return $rfiles;
            } else 
            {
                return false;
            }
        } elseif($method=='delete')
        {
            if (ftp_delete($conn_id, $remotefolder.$file)) {
              $test=true;
            } else {
              $test=false;
            }

        }
    }
    // close the FTP stream 
    ftp_close($conn_id);
    return $test;      
}


//use this to toggle logging ot sql command for debugging purposes
    
// connects to the database    
function dbconnect() {
    global $sql_server,$sql_user,$sql_pass,$sql_database;
    $con=@mysql_connect($sql_server,$sql_user,$sql_pass);
    
    if (!$con) {
        die('Could not connect to the sql server.<br />The server error message is: ' . mysql_error());
    } else {
        // we have a connection, so select the correct db
        $db_select = @mysql_select_db($sql_database,$con);
        // check to see if the database was selected correctly
            if (!$db_select) {
            // database didn't open correctly so close the connection
            mysql_close($con);
            die('Could not connect to the specified database.<br/>The server error message is:' . mysql_error());
        } else {
            // clear data
            return $con;
        }
    }
}

function log_queries($query)
{
    global $sql_database, $logging;
    if ($logging)
    {
        if ($query!='')
        {
            //first, lets make sure the table exists in the database, else create it
            $checktable=mysql_query("SHOW TABLES FROM $sql_database like 'sql_log'");
            if (mysql_num_rows($checktable)>0)
            {
                //found an existing table
            } else {
                //need to create the log table first
                $logtable="CREATE TABLE `sql_log` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `stamp` datetime DEFAULT NULL,
      `statement` text,
      `scriptname` varchar(255) DEFAULT NULL,
      `type` varchar(50) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";
                $createtable=mysql_query($logtable);
            }
            if (strpos($query,"ELECT")>0){$type='select';}
            if (strpos($query,"ELETE")>0){$type='delete';}
            if (strpos($query,"PDATE")>0){$type='update';}
            if (strpos($query,"; DELETE FROM")>0){$type='possible injection';}
            $sname=$_SERVER['PHP_SELF'];
            $cd=date("Y-m-d H:i:s");
            $query=mysql_real_escape_string($query);
            $sql="INSERT INTO sql_log (stamp, statement, scriptname, type) VALUES ('$cd','$query', '$sname', '$type')";
            $dbresult=mysql_query($sql);
            
        }
    }
}

// executes a database query
function dbexecutequery($query = '',$log=false) {
    $query=str_replace("<!--Session data-->","",$query);
    if ($query != "") {
        if ($log){log_queries($query);}
        if (mysql_query($query)) {
            $result['numrows']= mysql_affected_rows();
            $result['data']='';
            $error=dberror();
            if ($error!='')
            {
                $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
            }
            $result['error']=$error;
        } else {
            $error=dberror();
            $result['numrows']= 0;
            $result['data']='';
            if ($error!='')
            {
                $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
            }
            $result['error']=$error;
        }
    } else {
        $result['numrows']= 0; 
        $result['data']='';
        $result['error']='A blank query was submitted.';
    }
    return $result; 
}

//executes an INSERT query
function dbinsertquery($query = '',$log=false) {
    $query=str_replace("<!--Session data-->","",$query);
    if ($query != "") {
        if ($log){log_queries($query);}
        $dbresult=mysql_query($query);
        if ($dbresult) {
            $result['numrows']= mysql_insert_id();
            $result['insertid']= mysql_insert_id();
            $result['data']='';
            $error=dberror();
            if ($error!='')
            {
                $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
            }
            $result['error']=$error;
        } else {
            $error=dberror();
            $result['numrows']=0;
            $result['insertid']=0;
            $result['data']='';
            if ($error!='')
            {
                $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
            }
            $result['error']=$error;
        }
    } else {
        $result['numrows']=0;
        $result['data']='';
        $error=dberror();
        if ($error!='')
        {
            $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
        }
        $result['error']=$error;
    }
    return $result;
}
// grabs an array of rows from the query results
function dbselectmulti($query='',$log=false){
    $result = array();
    if ($log){log_queries($query);}
    $queryid = mysql_query($query);
    if ($queryid){
        $result['numrows']= mysql_num_rows($queryid);
        while ($row = mysql_fetch_array($queryid, MYSQL_ASSOC))
        {
            if (!empty($row))
            {
                $result['data'][] = $row;
            }
        }
        $error=dberror();
        if ($error!='')
        {
            $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
        }
        $result['error']=$error;
        mysql_free_result($queryid);
        return $result;
    } else {
        $result['numrows']=0;
        $result['data']='';
        $result['error']=dberror();
        return $result;
    }
}
function dbselectsingle($query='',$log=false){
    $result = array();
    if ($log){log_queries($query);}
    $queryid = mysql_query($query);
    if ($queryid) {
        $result['numrows']= mysql_num_rows($queryid);
        $result['data']= mysql_fetch_array($queryid, MYSQL_ASSOC);
        $error=dberror();
        if ($error!='')
        {
            $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
        }
        $result['error']=$error;
         mysql_free_result($queryid);
        return $result;
    } else {
        $error=dberror();
        $result['numrows']=0;
        $result['data']='';
        if ($error!='')
        {
            $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
        }
        $result['error']=$error;
        return $result;
    }
}

function dbgetfields($table='',$log=false){
    $result=array();
    if ($log){log_queries($query);}
    $query=mysql_query("SHOW COLUMNS FROM $table");
    if ($query) {
        $i=0; 
        while ($row = mysql_fetch_array($query, MYSQL_ASSOC))
         {
          if (!empty($row)){
              $result['fields'][] = $row;
              $i++;
          }
         }
        $result['numrows']=$i;
        $error=dberror();
        if ($error!='')
        {
            $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
        }
        $result['error']=$error;
        mysql_free_result($query);
        return $result;
    } else {
        $error=dberror();
        $result['fields']='';
        if ($error!='')
        {
            $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
        }
        $result['error']=$error;
        return $result;
    }
}

function dbfieldexists($table,$field)
{
    $fields=dbgetfields($table);
    foreach($fields['fields'] as $checkfield)
    {
        if ($checkfield['Field']==$field)
        {
            return true;
        }
    }
    return false;
}

function dbgettables($db='idahopress_com'){
    $result=array();
    $query=mysql_query("SHOW TABLES FROM $db");
    if ($query) {
        $i=0;
        while ($row = mysql_fetch_array($query, MYSQL_ASSOC))
         {
          if (!empty($row)){
              $result['tables'][] = $row["Tables_in_$db"];
            $i++;
          }
         }
        $result['numrows']=$i;
        $error=dberror();
        
        if ($error!='')
        {
            $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
        }
        $result['error']=$error;
        mysql_free_result($query);
        return $result;
    } else {
        $error=dberror();
        $result['tables']='';
        if ($error!='')
        {
            $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
        }
        $result['error']=$error;
        return $result;
    }

}
    
// closes the connection to the database
function dbclose(){
        if ($GLOBALS['con']) {
            return (@mysql_close()) ? true : false;
        } else {
            // no connection
            return false;
        }
}
    
// gets error information
function dberror() {
    if (@mysql_errno()==0){
        return "";
    } else{
        return "Error #: ".@mysql_errno()." -- Error message: ".@mysql_error();
    }
}

function cleanInput($input) {
 
    $search = array(
    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
    '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
    '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
    '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
);
 
    $output = preg_replace($search, '', $input);
    return $output;
}
dbclose();  
?>
