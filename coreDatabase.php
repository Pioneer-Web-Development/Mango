<?php
//<!--VERSION: .9 **||**-->
  //this is the database management toolkit
  //by default it will only allow management the database that the PIMS system is
  //configured to access
  //it will be able to create, drop and alter tables and fields.
  //it will also provide backup and restore functionality and "changelog" style updates

include("includes/mainmenu.php") ;

$dbfieldtypes=array('int','varchar','char','tinyint','text','decimal','date','time','datetime');

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
  
switch ($action)
{
    case "addtable":
    table('add');
    break;
    
    case "edittable":
    table('edit');
    break;
    
    case "deletetable":
    table('delete');
    break;
    
    case "Create Table":
    save_table('create');
    break;
    
    case "Alter Table":
    save_table('alter');
    break;
    
    case "addfield":
    fields('add');
    break;
    
    case "editfield":
    fields('edit');
    break;
    
    case "deletefield":
    fields('delete');
    break;
    
    case "Save Field":
    save_field('insert');
    break;
    
    case "Update Field":
    save_field('update');
    break;
    
    case "listfields":
    fields('list');
    break;
    
    case "listtables":
    table('list');
    break;
    
    default:
    table('list');
    break;
    
}  

function table($action)
{
    global $siteID;
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Create Table";
            $message="<div class='label'></div><div class='input'>When you create a new table, a column called 'id' will automatically be created in the table, set as primary key, auto-incrementing.</div><div class='clear'></div>\n";
            $synch=0;
        } else {
            $id=intval($_GET['id']);
            $button="Alter Table";
            $sql="SELECT * FROM core_system_tables WHERE id=$id";
            $dbTable=dbselectsingle($sql);
            $table=$dbTable['data'];
            $name=stripslashes($table['table_name']);
            $notes=stripslashes($table['table_notes']);
            $synch=stripslashes($table['synch_data']);
            $message="";
        }
        print "<form method=post>\n";
        print $message;
        make_text('name',$name,'Table name');
        make_checkbox('synch',$synch,'Synch Data','Synch data to core site as well as structure');
        make_textarea('notes',$notes,'Notes','Provide any notes you want about this table');
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="SELECT * FROM core_system_tables WHERE id=$id";
        $dbTable=dbselectsingle($sql);
        
        $tablename=stripslashes($dbTable['data']['table_name']);
        $sql="DROP TABLE $tablename";
        $dbDelete=dbexecutequery($sql);
        if ($dbDelete['error']!='')
        {
            setUserMessage('There was a problem deleting that table.<br>'.$dbDelete['error'],'error');
        } else {
            $sql="DELETE FROM core_system_tables WHERE id=$id";
            $dbDelete=dbexecutequery($sql);
            setUserMessage('Table successfully deleted.','success');  
        }
        redirect("?action=listtables");
    } else {
        $sql="SELECT * FROM core_system_tables";
        $dbTables=dbselectmulti($sql);
        if($dbTables['numrows']==0)
        {
          $dbTables=dbgettables('mangodb');
          if ($dbTables['numrows']>0)
          {
             foreach($dbTables['tables'] as $table=>$name)
             {
                $sql="INSERT INTO core_system_tables (table_name, synch_data, site_id) VALUES ('$name',0,'$siteID')";
                $dbInsert=dbinsertquery($sql);
                if($dbInsert['error']!='')
                {
                    print "Problem adding $name to the core system file<br>";
                }
             }
             $sql="SELECT * FROM core_system_tables";
             $dbTables=dbselectmulti($sql);
        
          }  
        } 
        tableStart("<a href='?action=addtable'>Create new table</a>","Table",4);
        if ($dbTables['numrows']>0)
        {
           foreach($dbTables['data'] as $table)
           {
                $id=$table['id'];
                $name=$table['table_name'];
                print "<tr>";
                print "<td>$name</td>";
                print "<td><a href='?action=listfields&id=$id'>Fields</td>";
                print "<td><a href='?action=edittable&id=$id'>Edit</td>";
                print "<td><a href='?action=deletetable&id=$id' class='delete'>Drop</td>";
                print "</tr>\n";    
           } 
        }
        print "</table>\n";
        tableEnd($dbTables);
    }
}  

function save_table($action)
{
     global $siteID; 
    //adding new table
     //add this to the end
     // ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1
     $id=$_POST['id'];
     $tablename=$_POST['name'];
     $notes=addslashes($_POST['notes']);
     if($_POST['synch']){$synch=1;}else{$synch=0;}
     if ($action=='create')
     {
         $sql="CREATE TABLE $tablename 
         (id MEDIUMINT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id)) 
         ENGINE=MyISAM AUTO_INCREMENT=1 
         DEFAULT CHARSET=latin1";
         $dbTable=dbexecutequery($sql);
         $error=$dbTable['error'];
         if($error=='')
         {
             $sql="INSERT INTO core_system_tables (table_name, table_notes, synch_data, site_id) 
             VALUES('$tablename', '$notes', '$synch', '$siteID')";
             $dbInsert=dbinsertquery($sql);
             $error=$dbInsert['error'];
             if($error=='')
             {
                setUserMessage('The new table, '.$tablename.' has been successfully created.','success');    
             } else {
                setUserMessage('There was a problem saving the new table information into the core system tables.<br>'.$error,'error');    
             }
         } else {
            setUserMessage('There was a problem creating the new table.<br>'.$error,'error');    
         }
     } else {
         $sql="SELECT * FROM core_system_tables WHERE id=$id";
         $dbEx=dbselectsingle($sql);
         $orgname=$dbEx['data']['table_name'];
         
         $sql="UPDATE core_system_tables SET table_notes='$notes', synch_data='$synch' WHERE id=$id";
         $dbUpdate=dbexecutequery($sql);
         $error=$dbUpdate['error'];
         if($error=='')
         {
             if ($tablename==$orgname)
             {
                 setUserMessage('Table successfully updated.','success');
             } else {
                 $sql="ALTER TABLE $orgname RENAME $tablename";
                 $dbTable=dbexecutequery($sql);
                 $error=$dbTable['error'];
                 if($error=='')
                 {
                     $sql="UPDATE core_system_tables SET table_name='$tablename' WHERE id=$id";
                     $dbUpdate=dbexecutequery($sql);
                     setUserMessage('Table name has been successfully changed!','success');
                 } else {
                     setUserMessage('There was a problem changing the name of the database table.<br>'.$error,'error');
                 }
             }
         } else {
            setUserMessage('There was a problem updating the core system files.'.$error,'error');   
         }
          
     }
     redirect("?action=listtables");
}

function fields($action)
{
    global $dbfieldtypes;
    $id=intval($_GET['id']);
    $sql="SELECT * FROM core_system_tables WHERE id=$id";
    $dbTable=dbselectsingle($sql);
    $table=$dbTable['data'];
    $tn=$table['table_name'];
    print "Working with table: $tn<br>";
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Field";
            $type='int'; 
        } else {
            $button="Update Field";
            //get table of table
            $fields=dbgetfields($tn);
            //loop until we get the one we want
            $fn=$_GET['field'];
            foreach($fields['fields'] as $field)
            {
                if ($field['Field']==$fn)
                {
                    $name=$field['Field'];
                    $type=$field['Type'];
                    $type=str_replace(")","",$type);
                    $type=explode("(",$type);
                    $length=$type[1];
                    $type=$type[0];
                    $null=$field['Null'];
                    $default=$field['Default'];
                    if ($null=='YES'){$null=1;}else{$null=0;}
                }
            }
        }
        print "<form method=post>\n";
        make_text('newname',$name,'Field Name');
        make_select('type',$type,$dbfieldtypes,'Field Type');
        make_text('length',$length,'Field Length','For decimal, enter like (5,2)',5);
        make_checkbox('null',$null,'Allow null','Check to allow null');
        make_text('default',$default,'Default Value');
        make_hidden('fn',$fn);
        make_hidden('tn',$tn);
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
        
    } elseif ($action=='delete')
    {
        $fn=$_GET['field'];
        $sql="ALTER TABLE $tn DROP $fn";
        $dbField=dbexecutequery($sql);
        if ($dbField['error']=='')
        {
           setUserMessage('Field '.$fn.' in '.$tn.' table was successfully deleted.','success');
        } else {
           setUserMessage('There was a problem deleting the field from the table.<br>'.$dbField['error'],'error'); 
        }
        redirect("?action=listfields&id=$id"); 
    } else {
        //ok, show fields for this table
        $fields=dbgetfields($tn);
        tableStart("<a href='?action=listtables'>Return to tables</a>,<a href='?action=addfield&tn=$tn&id=$id'>Add new field</a>","Field,Type,Extra",6);
        if ($fields['numrows']>0)
        {
            foreach($fields['fields'] as $field)
            {
                print "<tr>";
                print "<td>$field[Field]</td>";
                print "<td>$field[Type]</td>";
                if ($field['Field']=='id')
                {
                    print "<td>$field[Extra]</td>";
                    print "<td>No editing</td>";
                    print "<td>No deleting</td>";
                } else {
                    print "<td>$field[Default]</td>";
                    print "<td><a href='?action=editfield&id=$id&tn=$tn&field=$field[Field]'>Edit</a></td>";
                    print "<td><a href='?action=deletefield&id=$id&tn=$tn&field=$field[Field]' class='delete'>Delete</a></td>";
                }
                print "</tr>\n";
                $fcount++;
            }   
        }
        tableEnd(array('numrows'=>$fcount));
        
    }
}

function save_field($action)
{
    global $dbfieldtypes;
    $orgname=$_POST['fn'];
    $tn=$_POST['tn'];
    $id=$_POST['id'];
    $type=$dbfieldtypes[$_POST['type']];
    $name=$_POST['newname'];
    $length=$_POST['length'];
    $default=$_POST['default'];
    if ($default!=''){$default="DEFAULT '$default'";}
    if($_POST['null']){$null="";}else{$null="NOT NULL";}
    
    $def="$type($length) $null $default"; 
    
    if ($action=='insert')
    {
        //adding a new field
        $sql="ALTER TABLE $tn ADD $name $def";
    } else {
        //altering an existing field
        if ($orgname==$name) //modify
        {
            $sql="ALTER TABLE $tn MODIFY $orgname $def";
        } else {
            //change
            $sql="ALTER TABLE $tn CHANGE $orgname $name $def";
        }
    }
     $dbField=dbexecutequery($sql);
     $error=$dbField['error'];
    if ($error=='')
    {
        setUserMessage('Table has been updated with the new field.','success');
    } else {
        setUserMessage('There was a problem modifying the table.<br>'.$error,'error');
    }
    redirect("?action=listfields&id=$id");
}
  
footer();
  
?>
