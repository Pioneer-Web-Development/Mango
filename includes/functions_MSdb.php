<?php

//open the database connection
msdbconnect();
    
// connects to the database    
function msdbconnect() {
	$sql_server='10.56.5.95,1433';
	$sql_user="sa";
	$sql_pass="";
	$sql_database="WorkflowDB";
    $con=@mssql_connect($sql_server,$sql_user,$sql_pass);
	
	if (!$con) {
        die('Could not connect to the sql server.<br />The server error message is: ' . mssql_get_last_message());
    } else {
        // we have a connection, so select the correct db
	    $db_select = @mssql_select_db($sql_database,$con);
	    // check to see if the database was selected correctly
	        if (!$db_select) {
		    // database didn't open correctly so close the connection
		    mssql_close($con);
		    die('Could not connect to the specified database.<br/>The server error message is:' . mssql_get_last_message());
	    } else {
	        // clear data
	        return $con;
        }
    }
}
// executes a database query
function msdbexecutequery($query = '') {
	if ($query != "") {
		if (mssql_query($query)) {
            $result['numrows']= mssql_affected_rows();
            $result['data']='';
            $result['error']='';
	    } else {
            $result['numrows']= 0;
            $result['data']='';
            $result['error']=msdberror();
        }
    } else {
    $result['numrows']= 0; 
    $result['data']='';
    $result['error']='A blank query was submitted.';
    }
    return $result; 
}

//executes an INSERT query
function msdbinsertquery($query = '') {
    if ($query != "") {
        $dbresult=mssql_query($query);
        if ($dbresult) {
            $query            = 'select SCOPE_IDENTITY() AS last_insert_id';
            $query_result     = mssql_query($query);
            $query_result    = mssql_fetch_object($query_result);
            mssql_free_result($query_result);
            $result['numrows']= $query_result->last_insert_id;
            $result['insertid']= $query_result->last_insert_id;
            $result['data']='';
            $result['error']=msdberror();
        } else {
            $result['numrows']=0;
            $result['data']='';
            $result['error']=msdberror();
        }
    } else {
        $result['numrows']=0;
        $result['data']='';
        $result['error']=msdberror();
    }
    mssql_free_result($dbresult);
    return $result;
}

// grabs an array of rows from the query results
function msdbselectmulti($query=''){
    $result = array();
    $queryid = mssql_query($query);
    if ($queryid){
        $result['numrows']= mssql_num_rows($queryid);
         while ($row = mssql_fetch_assoc($queryid)) {
          if (!empty($row)){
            $result['data'][] = $row;
            
        }
        }
        $result['error']=msdberror(); 
        mssql_free_result($queryid);
        return $result;
    } else {
        $result['numrows']=0;
        $result['data']='';
        $result['error']=msdberror();
        return $result;
    }
}
function msdbselectsingle($query=''){
    $result = array();
    $queryid = mysql_query($query);
    if ($queryid) {
        $result['numrows']= mssql_num_rows($queryid);
        $result['data']= mssql_fetch_assoc($queryid);
        $result['error']=msdberror();
        mssql_free_result($queryid);
        return $result;
    } else {
        $result['numrows']=0;
        $result['data']='';
        $result['error']=msdberror();
        return $result;
    }
}
//allegedly, sql server returns dates in the correct format
//SELECT CONVERT(varchar, INVOICE_DATE, 121) AS INVOICE_DATE


function msdbgetfields($table=''){
    $result=array();
    $queryid=mssql_query("SHOW COLUMNS FROM $table");
    if ($queryid) {
         while ($row = mssql_fetch_assoc($queryid))
         {
          if (!empty($row)){
            $result['fields'][] = $row;
          }
         }
        $result['error']=msdberror();
        mssql_free_result($queryid);
        return $result;
    } else {
        mssql_free_result($queryid);
        $result['fields']='';
        $result['error']=msdberror();
        return $result;
    }
}

function msdbtabledefinition($table)
{
    $result=array();
    $queryid=mssql_query("SELECT TOP 1 * FROM $table");
    if ($queryid) {
        for($i = 0; $i < mssql_num_fields($queryid); ++$i)
        {
            $result[$i]['fieldname']=mssql_field_name($queryid, $i);
            $result[$i]['fieldtype']=mssql_field_type($queryid, $i);
            $result[$i]['fieldlength']=mssql_field_length($queryid, $i);
            
        }
        mssql_free_result($queryid);
        return $result;
    } else {
        mssql_free_result($queryid);
        $result['error']=msdberror();
        return $result;
    }
}


function msdbfieldexists($table,$field)
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

function msdbgettables($db='idahopress_com'){
    $result=array();
    $queryid=mssql_query("SELECT name FROM dbo.sysobjects WHERE xtype = 'U' ");
    if ($queryid) {
        while ($row = mssql_fetch_assoc($queryid))
         {
          if (!empty($row)){
            $result['tables'][] = $row;
          }
         }
        $result['error']=msdberror();
        mssql_free_result($queryid);
        return $result;
    } else {
        $result['tables']='';
        $result['error']=msdberror();
        return $result;
    }

}
    
// closes the connection to the database
function msdbclose(){
		if ($GLOBALS['con']) {
		    return (@mssql_close()) ? true : false;
		} else {
			// no connection
			return false;
		}
}
	
// gets error information
function msdberror() {
    if (mssql_get_last_message()==''){
        return "";
    } else{
        return "Error :".mssql_get_last_message();
    }
}



?>
