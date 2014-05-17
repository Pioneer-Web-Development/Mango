<?php
include("includes/mainmenu.php");

if($_POST)
{
  $action=$_POST['submit'];
} else {
  $action=$_GET['action'];
}

switch($action)
{
  case "":
  break;
  
  default:
  break;
}

function thing($action)
{
    if($action=='add' || $action=='edit')
    {
       if ($action=='add')
       {
           $button='Save';
           //define any default values for fields
           
       } else {
           $button='Update';
           $id=intval($_GET['id']);
           $sql="SELECT * FROM table WHERE id=$id";
           $dbData=dbselectsingle($sql);
           $data=$dbData['data'];
           $name=stripslashes($data['route_name']);
           $number=stripslashes($data['route_number']);
           
       }
       print "<form method=post>\n";
       
       /* data entry field types are 
       *
       * make_text(field name,field value, label, explanatory text, size)
       * make_textarea
       * make_number
       * make_checkbox
       * make_date
       * make_datetime
       * make_time
       * make_color
       * make_select
       * make_slider
       * make_hidden
       * make_submit
       */
       
       make_hidden('id',$id);
       make_submit('submit',$button);
       print "</form>\n";
        
    } elseif($action=='delete')
    {
       $id=intval($_GET['id']);
       $sql="DELETE FROM table WHERE id=$id";
       $dbDelete=dbexecutequery($sql);
       $error=$dbDelete['error'];
       if ($error!='')
       {
           setUserMessage('There was a problem deleting the thing.<br>'.$error,'error');
       } else {
           setUserMessage('Thing was successfully deleted.','success');
       } 
    } else {
       $sql="SELECT * FROM ";
       $dbData=dbselectmulti($sql);
       $search="";
       $options="<a href='?action=add'>Add new thing</a>";
       $headers="ID,Name";
       tableStart($options,$headers,4,$search);
       if($dbData['numrows']>0)
       {
         foreach($dbData['data'] as $data)
         {
             print "<tr>\n";
             print "<td>".stripslashes($data['id'])."</td>\n";
             print "<td>".stripslashes($data['name'])."</td>\n";
             print "<td><a href='?action=edit&id=$data[id]'>Edit</a></td>\n";
             print "<td><a href='?action=delete&id=$data[id]' class='delete'>Delete</a></td>\n";
             print "</tr>\n";
         }    
       }
       tableEnd($dbData);
        
    }  
}
  
function save_thing($action)
{
    $id=$_POST['id'];
    $name=addslashes($_POST['name']);
    
    if ($action=='insert')
    {
        $sql="INSERT INTO table (field) VALUES 
        ('$data')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE table SET field='$data' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the thing.<br>'.$error,'error');
    } else {
        setUserMessage('Thing successfully saved.','success');
    }
    redirect("?action=list"); 
}

footer();
?>
