<?php
//<!--VERSION: 1.0 **||**-->
//configure the database connection
if(strpos($_SERVER['PHP_SELF'],'mobile')>0)
{
   include($_SERVER["DOCUMENT_ROOT"]."/includes/dbconnect.inc");
} else {
   include("dbconnect.inc"); 
}
$dbBackupDirectory='dbbackups/';

//use this to toggle logging ot sql command for debugging purposes
$logging=false;

//open the database connection
dbconnect();
    
// connects to the database    
function dbconnect() {
	global $sql_server,$sql_user,$sql_pass,$sql_database;
    //$con=@mysql_connect($sql_server,$sql_user,$sql_pass);
    $con=@mysql_connect(SQL_SERVER,SQL_USER,SQL_PASS);
	
	if (!$con) {
        die('Could not connect to the sql server.<br />The server error message is: ' . mysql_error());
    } else {
        // we have a connection, so select the correct db
        //$db_select = @mysql_select_db($sql_database,$con);
	    $db_select = @mysql_select_db(SQL_DATABASE,$con);
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
        $result['numrows']='0';
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

function dbbackup($tables = '*')
{
    global $sql_server,$sql_user,$sql_pass,$sql_database,$dbBackupDirectory;
    
    $link = mysql_connect($sql_server,$sql_user,$sql_pass);
    mysql_select_db($sql_database,$link);
    
    //get all of the tables
    if($tables == '*')
    {
        $tables = array();
        $result = mysql_query('SHOW TABLES');
        while($row = mysql_fetch_row($result))
        {
            $tables[] = $row[0];
        }
    }
    else
    {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }
    //at the start, create a backup folder
    $date=date("Ymd");
    if (!file_exists($dbBackupDirectory.$date."/"))
    {
        mkdir($dbBackupDirectory.$date."/",0777);
    }
    //cycle through
    foreach($tables as $table)
    {
        $q1="SELECT * FROM $table";
        $result = mysql_query($q1);
        $num_fields = mysql_num_fields($result);
        
        $return.= 'DROP TABLE '.$table.';';
        $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
        $return.= "\n\n".$row2[1].";\n\n";
        
        for ($i = 0; $i < $num_fields; $i++) 
        {
            while($row = mysql_fetch_row($result))
            {
                $return.= 'INSERT INTO '.$table.' VALUES(';
                for($j=0; $j<$num_fields; $j++) 
                {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n","\\n",$row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                    if ($j<($num_fields-1)) { $return.= ','; }
                }
                $return.= ");\n";
            }
        }
        $return.="\n\n\n";
        //save file
        $backupfile=$dbBackupDirectory.$date."/".$table.'.sql';
        $handle = fopen($backupfile,'w+');
        fwrite($handle,$return);
        fclose($handle);
        $return="";
        print "Successfully backed up $table to $backupfile<br />\n";
    }  
}

function dbrestore($date,$tables = '*')
{
    global $sql_server,$sql_user,$sql_pass,$sql_database,$dbBackupDirectory;
    
    $link = mysql_connect($sql_server,$sql_user,$sql_pass);
    mysql_select_db($sql_database,$link);
    $date=str_replace("-","",$date);
    //get all of the tables
    if($tables == '*')
    {
        $tables = array();
        $handler = opendir($dbBackupDirectory.$date);

        // keep going until all files in directory have been read
        while ($file = readdir($handler)) {
            // if $file isn't this directory or its parent, 
            // add it to the results array
            if ($file != '.' && $file != '..')
                $tables[] = str_replace(".sql","",$file);
        }
    } else {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }
    if (count($tables)>0)
    {
        //cycle through
        foreach($tables as $table)
        {
            //open file
            $backupfile=$dbBackupDirectory.$date."/".$table.".sql";
            print "Loading backup file $backupfile...<br />\n";
            if (file_exists($backupfile))
            {
                $contents = file_get_contents($backupfile);
                $dbRestore=dbexecutequery($contents);
                if ($dbRestore['error']!='')
                {
                    print $dbRestore['error'];
                }    
            } else {
                print "Sorry, that database backup file $backupfile, does not exist<br />\n";
            }
        }
    }  
}


?>
