<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
switch ($action)
{
    
    case "add":
    records('add');
    break;
    
    case "edit":
    records('edit');
    break;
    
    case "delete":
    records('delete');
    break;
    
    case "Save":
    save_record('insert');
    break;
    
    case "Update":
    save_record('update');
    break;

    default:
    records('list');
    break;
}


function records($action)
{
    $id=intval($_GET['id']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save";
        } else {
            $button="Update";
            $sql="SELECT * FROM adv_premium_days WHERE id=$id";
            $dbRecord=dbselectsingle($sql);
            $record=$dbRecord['data'];
            $date=stripslashes($record['premium_date']);
            $name=stripslashes($record['premium_name']);
        }
        print "<form method=post>\n";
        make_text('name',$name,'Premium Name','Example: Christmas, Thanksgiving, etc');
        make_date('date',$date,'Date','Date of this premium');
        make_submit('submit',$button);
        make_hidden('id',$id);
        print "</form>\n";
    }elseif ($action=='delete')
    {
        $sql="DELETE FROM adv_premium_days WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the premium date.<br />'.$error,'error');
        } else {
            setUserMessage('The premium date was successfully deleted','success');
        }
        redirect("?action=list");
    } else {
       global $siteID;
        //show all the pubs
       $sql="SELECT * FROM adv_premium_days ORDER BY premium_date";
       $dbRecords=dbselectmulti($sql);
       tableStart("<a href='?action=add'>Add premium</a>","Date, Name",4);
       if ($dbRecords['numrows']>0)
       {
            foreach($dbRecords['data'] as $record)
            {
                $id=$record['id'];
                $name=stripslashes($record['premium_name']);
                $date=date("m/d/Y",strtotime($record['premium_date']));
                print "<tr>";
                print "<td>$date</td>
                <td>$name</td>\n";
                print "<td><a href='?action=edit&id=$id'>Edit</a</td>\n";
                print "<td><a class='delete' href='?action=delete&id=$id'>Delete</a</td>\n";
                print "</tr>\n";
            }
       }
       tableEnd($dbRecords);
    }


}



function save_record($action)
{
    $id=$_POST['id'];
    $name=addslashes($_POST['name']);
    $date=addslashes($_POST['date']);
    $dow=date("N",strtotime($_POST['date']));
    if ($action=='insert')
    {
        $sql="INSERT INTO adv_premium_days (premium_date, premium_name, premium_dow)
         VALUES ('$date', '$name', '$dow')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE adv_premium_days SET premium_date='$date', premium_name='$name', premium_dow='$dow' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the premium date.<br />'.$error,'error');
    } else {
        setUserMessage('The premium date was successfully saved','success');
    }
    redirect("?action=list");
    
}

footer();
?>

