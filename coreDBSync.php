<?php
//database synch script
/* core server */
//external DB connection stuff
    



//we always do a full drop and create with records
if ($_GET['direct']=='234*_SNS_DF2kj2kj2k')
{
    include("includes/functions_db.php");
    
    $sql="SELECT * FROM core_preferences";
    $dbPrefs=dbselectsingle($sql);
    $prefs=$dbPrefs['data'];
    $primeIP=$prefs['coreServer'];
    $esql_server=$primeIP;//$primarydb['ip_address'];
    
    $esql_user='remoteadmin';//$primarydb['db_user'];
    $esql_pass='slcrbt5';//$primarydb['db_pass'];
    $esql_database='mangodb';//$primarydb['db_name'];
    $econ='';
    
    init_synch();
} else {
    include("includes/mainmenu.php");
    
    $sql="SELECT * FROM core_preferences";
    $dbPrefs=dbselectsingle($sql);
    $prefs=$dbPrefs['data'];
    $primeIP=$prefs['coreServer'];
    $esql_server=$primeIP;//$primarydb['ip_address'];
    
    $esql_user='remoteadmin';//$primarydb['db_user'];
    $esql_pass='slcrbt5';//$primarydb['db_pass'];
    $esql_database='mangodb';//$primarydb['db_name'];
    $econ='';
    
    if($_POST)
    {
        init_synch();
    } else {
        get_confirmation();
    }
}

function get_confirmation()
{
    edbconnect();
    $sql="SELECT * FROM core_system_tables WHERE synch_data=1";
    $dbSynch=edbselectmulti($sql);
    $sql="SELECT * FROM core_system_tables ORDER BY table_name";
    $dbTables=edbselectmulti($sql);
    edbclose();
    print "<form method=post>\n";
    print "<div class='label'>&nbsp;</div><div class='input'><p>This process will synchronize the structure of the database tables with the CORE UPDATE server. The following tables will also have all of their contents updated for consistency.</p>";
    print "<ul>\n";
    if($dbSynch['numrows']>0)
    {
        foreach($dbSynch['data'] as $table)
        {
            print "  <li>$table[table_name]</li>\n";
        }
    } else {
        print "No tables will have their contents updated.<br>";
    }
    print "</ul>\n";
    $etables[0]='Sync all tables';
    $etables['allsynch']='Sync all above tables';
    if($dbTables['numrows']>0)
    {
        foreach($dbTables['data'] as $table)
        {
            $etables[$table['table_name']]=$table['table_name'];    
        }
    }
    make_select('specificTable',$etables[0],$etables,'Select specific table to sync');
    print "</div><div class='clear'></div>\n";
    make_submit('submit','Start synchronization');
    print "</form>\n";
}

function init_synch()
{
    global $esql_database, $esql_pass, $esql_user, $esql_server;
    
    dbconnect();
    //now get tables of the local database
    $sql="SELECT  A.* FROM core_sites A, core_preferences B WHERE A.id=B.site_id";
    $dbLocal=dbselectsingle($sql);
    if($dbLocal['numrows']==0)
    {
        die('Local site not configured in system sites.');
    }
    $localdb=$dbLocal['data'];
    $ltables=dbgettables($localdb['db_name']);
    //pull the local tables into an array
    $localtables=array();
    foreach($ltables['tables'] as $table)
    {
        if ($table!='')
        {
            $localtables[]=$table; 
        }
    }
    
    $sql="SELECT coreServer, site_id FROM core_preferences";
    $dbSite=dbselectsingle($sql);
    $siteid=$dbSite['data']['site_id'];
    $primeIP=$dbSite['data']['coreServer'];
    //now get tables of the local database
    $sql="SELECT * FROM core_sites WHERE id=$siteid";
    $dbPrimary=dbselectsingle($sql);
    if($dbPrimary['numrows']==0)
    {
        die('Primary site not configured in system sites.');
    }
    $primarydb=$dbPrimary['data'];
    
    dbclose();
    
    edbconnect();
    $sql="SELECT * FROM core_system_tables WHERE synch_data=1";
    $dbSynch=edbselectmulti($sql);
    $always=array();
    if($dbSynch['numrows']>0)
    {
        foreach($dbSynch['data'] as $table)
        {
            $always[]=$table['table_name'];
        }
    }
    
    $etables=edbgettables($esql_database); //first, get all the tables from the main database
    edbclose();  //close the connection

    
    if($_POST['specificTable']!='0')
    {
        if($_POST['specificTable']!='allsynch')
        {
            $always=array($_POST['specificTable']);
        }
        foreach($always as $key=>$table)
        {    
            print "Syncing specific table $table...";
            //syncing a specific table only
            if (in_array($table,$always))
            {
                if($_SERVER['HTTP_HOST']!=$primeIP)
                {
                    print "<ul>\n  <li>We are fully sychronizing $table to the local server, including all records</li>\n";
                    dbconnect();
                    if(in_array($table,$localtables))
                    {
                        print "  <li>Dropping $table from $_SERVER[SERVER_NAME]</li>";
                        $db=dbexecutequery("DROP TABLE $table");
                    }
                    dbclose();
                    if ($db['error']!='' && $db['error']!='Error #: 1065 -- Error message: Query was empty')
                    {
                        print "  <li style='color:red;'>There was a problem dropping the table $table from the local database.<br>$db[error]</li>\n";;
                        print "  <li>...did not sychronize the table.</li>\n</ul>\n";
                    } else {
                        print "  <li>...old table dropped. Creating new one.</li>\n";
                        add_table($table,true);
                        print "  <li>...full synchronization is complete.</li>\n</ul>\n";
                    }
                   
                    
                }
            }
        }
    } else {
        foreach($etables['tables'] as $table)
        {
            
            if ($table!='')
            {
                if (in_array($table,$always))
                {
                    if($_SERVER['HTTP_HOST']!=$primeIP)
                    {
                        print "<ul>\n  <li>We are fully sychronizing $table[table_name] to the local server, including all records</li>\n";
                        dbconnect();
                        if(in_array($table,$localtables))
                        {
                            print "  <li>Dropping $table from $_SERVER[SERVER_NAME]</li>";
                            $db=dbexecutequery("DROP TABLE $table");
                        }
                        dbclose();
                        if ($db['error']!='' && $db['error']!='Error #: 1065 -- Error message: Query was empty')
                        {
                            print "  <li style='color:red;'>There was a problem dropping the table $table from the local database.<br>$db[error]</li>\n";;
                            print "  <li>...did not sychronize the table.</li>\n</ul>\n";
                        } else {
                            print "  <li>...old table dropped. Creating new one.</li>\n";
                            add_table($table,true);
                            print "  <li>...full synchronization is complete.</li>\n</ul>\n";
                        }
                       
                        
                    }
                } elseif (in_array($table,$localtables))
                {
                    //ok, need to compare fields
                    print "Found $table in local tables<br>";
                    synch_tables($table);
                } else {
                    //its a new table! this one is easy!
                    print "<ul>\n  <li>We are adding a new table, $table to the local server</li>\n";
                    add_table($table);
                    print "  <li>...installation of new table is complete.</li>\n</ul>\n";
                } 
            }
           
        }
    }
}




function synch_tables($tablename)
{
    print "<ul>\n  <li>Beginning sync of $tablename...</li>\n";
    edbconnect(); //open the external connection briefly to get all tables
    $efields=mysql_fetch_fields($tablename);
    edbclose();  //close external
    $fields=array();
    $sourcefields=array();
    foreach($efields as $field=>$def)
    {
        $sourcefields[]=$field;
        foreach($def as $key=>$value)
        {
            if ($key=='definition')
            {
                 $fields[$field]=$value;
            }
        }
       
    }
    
    //now get the fields for the local table
    dbconnect();
    $lfields=mysql_fetch_fields($tablename);
    $localfields=array();
    foreach($lfields as $field=>$def)
    {
        $localfields[]=$field;
        foreach($def as $key=>$value)
        {
            if ($key=='definition')
            {
                 if ($fields[$field]!=$value)
                 {
                     $newdef=$fields[$field];
                     if ($field=='siteID' || $newdef=='') //if newdef is blank, that means we should drop that column
                     {
                         //drop this little punk!
                         $sql="ALTER TABLE $tablename DROP COLUMN $field";
                         $act="dropping";
                         $to="";
                     } else {
                         $sql="ALTER TABLE $tablename MODIFY $field $newdef";
                         $act="altering";
                         $to="to $newdef";
                     }
                     $mod=dbexecutequery($sql);
                     if($mod['error']=='')
                     {
                        print "  <li>We updated the table by $act $field $to</li>";
                     } else {
                        print "  <li style='color:red;'>There was an error updating $tablename.<br>$mod[error]</li>";
                     }
                 }
            }
        }
       
    }
    
    //now lets see if there are any new fields that need to be added...
    foreach($sourcefields as $sf)
    {
        if (!in_array($sf,$localfields))
        {
            //means we found a field in the source that does not exist in the local
            $newdef=$fields[$sf];
            $sql="ALTER TABLE $tablename ADD $sf $newdef";
            $mod=dbexecutequery($sql);
            if($mod['error']=='')
            {
                print "  <li>We updated $tablename to add $sf $newdef</li>\n";
            
            } else {
                print "  <li style='color:red;'>There was a problem updating $tablename to add $sf $newdef<br>$mod[error]</li>\n";
                
            }
        }
    }
    
    dbclose();
    print "  <li>...completed sync</li>\n</ul>\n";
}

function add_table($tablename,$full=false)
{
    edbconnect(); //open the external connection briefly to get all tables
    $def=edbbackup($tablename,false,true,$full); //get the table definition for this table
    edbclose();  //close external
    
    dbconnect(); //open local
    if ($full)
    {
        $sqls=explode(";\n",$def);
        $count=count($sqls);
        //print "We have $count elements to parse for $tablename<br />";
        for ($i=0;$i<$count;$i++)
        {
            //print "Executing $sqls[$i]<br />\n";
            if($sqls[$i]!='')
            {
                if ($i==0)
                {
                    $db=dbexecutequery($sqls[$i]);
                } else {
                    $db=dbexecutequery($sqls[$i]);
                }
            }
            if ($db['error']!='')
            {
                print "<li style='color:red;'>Adding new record, encountered this problem:<br>".$db['error']."</li>\n";
            }
        }
    } else {
        $addtables=dbexecutequery($def);
    }
    dbclose(); //close local
    if ($addtables['error']!='')
    {
        print "<li style='color:red;'>There was a problem adding the table and records.<br>".$addtables['error']."</li>\n";
    } else {
        print "<li>Successfully synched $tablename by creating the table and adding the necessary records from the CORE system</li>";// using<br />$def<br /><br />\n";
    } 
    
}

function mysql_fetch_fields($table) {
        // LIMIT 1 means to only read rows before row 1 (0-indexed)
        $result = mysql_query("SELECT * FROM $table LIMIT 1");
        $describe = mysql_query("SHOW COLUMNS FROM $table");
        $num = mysql_num_fields($result);
        $output = array();
        for ($i = 0; $i < $num; ++$i) {
                $field = mysql_fetch_field($result, $i);
                // Analyze 'extra' field
                $field->auto_increment = (strpos(mysql_result($describe, $i, 'Extra'), 'auto_increment') === FALSE ? 0 : 1);
                // Create the column_definition
                $field->definition = mysql_result($describe, $i, 'Type');
                if ($field->not_null && !$field->primary_key) $field->definition .= ' NOT NULL';
                if ($field->def) $field->definition .= " DEFAULT '" . mysql_real_escape_string($field->def) . "'";
                if ($field->auto_increment) $field->definition .= ' AUTO_INCREMENT';
                if ($key = mysql_result($describe, $i, 'Key')) {
                        if ($field->primary_key) $field->definition .= ' PRIMARY KEY';
                        else $field->definition .= ' UNIQUE KEY';
                }
                // Create the field length
                $field->len = mysql_field_len($result, $i);
                // Store the field into the output
                $output[$field->name] = $field;
        }
        return $output;
}

// connects to the external database    
function edbconnect()
{
    global $esql_server,$esql_user,$esql_pass,$esql_database;
    $econ=@mysql_connect($esql_server,$esql_user,$esql_pass);
    if (!$econ) {
        die("Could not connect to the core server.<br>Server: $esql_server<br>Database: $esql_database<br>User: $esql_user<br>Password: $esql_pass<br />The server error message is: " . mysql_error());
    } else {
        // we have a connection, so select the correct db
        $db_select = @mysql_select_db($esql_database,$econ);
        // check to see if the database was selected correctly
            if (!$db_select) {
            // database didn't open correctly so close the connection
            mysql_close($econ);
            die('Could not connect to the specified database on the core server.<br/>The server error message is:' . mysql_error());
        } else {
            // clear data
            return $econ;
        }
    }
}

function edbgetfields($table='',$log=false)
{
    $result=array();
    $query=mysql_query("SHOW COLUMNS FROM ".$table);
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
        $error=edberror();
        if ($error!='')
        {
            $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
        }
        $result['error']=$error;
        mysql_free_result($query);
        return $result;
    } else {
        $error=edberror();
        $result['fields']='';
        if ($error!='')
        {
            $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
        }
        $result['error']=$error;
        return $result;
    }
}


function edbgettables($db='')
{
    $result=array();
    $query=mysql_query("SHOW TABLES");
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
        $error=edberror();
        
        if ($error!='')
        {
            $error="An error occurred while processing. The sql was:<br>$query<br>The error was:<br>$error<br>";
        }
        $result['error']=$error;
        mysql_free_result($query);
        return $result;
    } else {
        $error=edberror();
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
function edbclose()
{
        if ($GLOBALS['econ']) {
            return (@mysql_close()) ? true : false;
        } else {
            // no connection
            return false;
        }
}
    
// gets error information
function edberror()
{
    if (@mysql_errno()==0){
        return "";
    } else{
        return "Error #: ".@mysql_errno()." -- Error message: ".@mysql_error();
    }
}


function edbbackup($tables = '*',$adddrop=true,$output=false,$records=false)
{
    global $esql_server,$esql_user,$esql_pass,$esql_database,$edbBackupDirectory;
    $link = mysql_connect($esql_server,$esql_user,$esql_pass);
    mysql_select_db($esql_database,$link);
    
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
    //cycle through
    foreach($tables as $table)
    {
        if ($records)
        {
            $sql="SELECT COUNT(id) FROM $table";
            $query = mysql_query($sql);
            $numrows= mysql_num_rows($query);
            mysql_free_result($query);
        }
        if ($adddrop)
        {
            $return.= 'DROP TABLE '.$table.';';
        }
        $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
        $return.= "\n\n".$row2[1].";\n\n";
        if ($records)
        {
            if ($numrows>250)
            {
                while($start<$numrows)
                {
                   $start=1;
                   $num=250;
                   $sql="SELECT * FROM $table LIMIT $start, $num";
                   $query = mysql_query($sql);
                   $num_fields = mysql_num_fields($query);
                   for ($i = 0; $i < $num_fields; $i++) 
                    {
                        while($row = mysql_fetch_row($query))
                        {
                            $return.= 'INSERT INTO '.$table.' VALUES(';
                            for($j=0; $j<$num_fields; $j++) 
                            {
                                $row[$j] = addslashes($row[$j]);
                                $row[$j] = ereg_replace("\n","\\n",$row[$j]);
                                if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                                if ($j<($num_fields-1)) { $return.= ','; }
                            }
                            $return.= ");\n";
                        }
                    }
                    mysql_free_result($query);
            
                    $start+=$num; 
                }
            }  else {
                $sql="SELECT * FROM $table";
                $query = mysql_query($sql);
                $num_fields = mysql_num_fields($query);
                for ($i = 0; $i < $num_fields; $i++) 
                {
                    while($row = mysql_fetch_row($query))
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
            }
        }
        $return.="\n\n\n";
        //save file
        
    }
    return $return;
      
}

// grabs an array of rows from the query results
function edbselectmulti($query=''){
    $result = array();
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

if (!$_GET['direct'])
{
    footer();
}
//close both connections
dbclose();
edbclose();
?>
