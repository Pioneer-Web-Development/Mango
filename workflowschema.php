<?php
  error_reporting(0);
  include("includes/functions_MSdb.php");
  
  //first, lets get all the tables
  $tables=msdbgettables('WorkflowDB');
  print_r($tables);
  if (count($tables)>0)
  {
    foreach($tables['tables'] as $table)
    {
        print "<table>\n";
        print "<tr><th colspan=3>";
        print "Table: $table[name]";
        print "</th></tr>\n";
        showFields($table['name']);
        print "</table><br>\n";
    }
  }
  
  
  function showFields($tablename)
  {
        $fields=msdbtabledefinition($tablename);
        if ($fields['error']=='')
        {
            print "<tr><th>Name</th><th>Type</th><th>Length</th></tr>\n";
            foreach ($fields as $field)
            {
                print "<tr><th>$field[fieldname]</th><th>$field[fieldtype]</th><th>$field[fieldlength]</th></tr>\n";
            }
        } else {
            print "<tr><th colspan=3>$fields[error]</th></tr>\n";
        }
  
  
  
  }
?>
