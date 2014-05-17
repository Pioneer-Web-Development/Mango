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
  case "add":
    actions('add');
  break;
  
  case "edit":
    actions('edit');
  break;
  
  case "delete":
    delete_action(); 
  break;
  
  case "list":
    list_action();
  break;
  
  case "Save":
    save_action('insert');
  break;
  
  case "Update":
    save_action('update');
  break;
}


function actions($action)
{
    if($action=='add')
    {
        $button='Save';    
    } else {
        $id=intval($_GET['id']);
        $button='Update';
    }
  
    print "<form method=post>\n";
    
    make_hidden('id',$id);
    make_submit('submit',$button);
    print "</form>\n";
}

function delete_action()
{
    $id=intval($_GET['id']);
    
    if ($error!='')
    {
        setUserMessage('There was a problem saving the account.<br />'.$error,'error');
    } else {
        setUserMessage('The account was successfully saved','success');
    }
    redirect("?action=list");
}


function list_action()
{
    $dbRecords=dbselectmulti($sql);
    
    $options="<a href='?action=add'>Add new record</a>'";
    $headers="Col 1, Col 2";
    $cols=6;
    $search="<form method=post>\n";
    $search.="<input type='submit' name='submit' value='Search' /><br>";
    $search.="</form>\n";
    
    $extrascripts='';
    $sorcol=0;
    $sortdirection='asc';
    
    tableStart($options,$headers,$cols,$search);
    if($dbRecords['numrows']>0)
    {
        foreach($dbRecords['data'] as $record)
        {
            
        }    
    }
    tableEnd($dbRecords,$extrascripts,$sorcol,$sortdirection);  
}

function save_action($action)
{
    if($action=='insert')
    {
      
    } else {
      
    }

    if ($error!='')
    {
        setUserMessage('There was a problem saving the account.<br />'.$error,'error');
    } else {
        setUserMessage('The account was successfully saved','success');
    }
    redirect("?action=list");

}
footer();
?>
