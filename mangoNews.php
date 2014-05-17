<?php
include("includes/mainmenu.php") ;

if ($_POST['submitbutton']=='Add'){
    save_news('insert');
} elseif ($_POST['submitbutton']=='Update'){
    save_news('update'); 
} else {
    show_news();
}

function show_news()
{
    global $siteID;
    $action=$_GET['action'];
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Add';
            $by=$_SESSION['cmsuser']['userid'];
            $weight=99;
            $urgent=0;
            $sticky=0;
            $popup=0;
        } else {
            $id=intval($_GET['id']);
            $sql="SELECT * FROM mango_news WHERE id=$id";
            $dbNews=dbselectsingle($sql);
            $news=$dbNews['data'];
            $by=stripslashes($news['post_by']);
            $headline=stripslashes($news['headline']);
            $message=stripslashes($news['message']);
            $button="Update";
            $urgent=$news['urgent'];
            $sticky=$news['sticky'];
            $sticky=$news['popup'];
        }
        print "<form method=post>\n";
        //get all employees
        $sql="SELECT id, firstname, lastname FROM users WHERE site_id=$siteID ORDER BY firstname, lastname";
        $dbEmployees=dbselectmulti($sql);
        $employees=array();
        $employees[0]='Please choose';
        if ($dbEmployees['numrows']>0)
        {
            foreach($dbEmployees['data'] as $employee)
            {
                $employees[$employee['id']]=$employee['firstname'].' '.$employee['lastname'];
            }
        }
        make_select('postby',$employees[$by],$employees,'Posted by');
        make_checkbox('popup',$popup,'Popup','If checked, this will appear as a popup in the lower right when someone logs in.');
        make_checkbox('urgent',$urgent,'Urgent','Mark this as an critical item. Critical items are highlighted and shown first.');
        make_checkbox('sticky',$sticky,'Sticky','If this is checked, this story will stay alive past the normal 1 week expiration for news items.');
        make_text('headline',$headline,'Headline','',50);
        make_textarea('message',$message,'News','',70,20);
        make_submit('submitbutton',$button);
        make_hidden('id',$id);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="DELETE FROM mango_news WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the news item.<br />'.$error,'error');
        } else {
            setUserMessage('The news item has been successfully deleted.','success');
        }
        redirect('?action=list');
    } else {
        //list the privileges
        $sql="SELECT * FROM mango_news WHERE site_id=$siteID ORDER BY post_datetime DESC";
        $dbNews=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add news</a>","Headlines",3);
        if ($dbNews['numrows']>0)
        {
            foreach($dbNews['data'] as $news)
            {
                $headline=$news['headline'];
                $id=$news['id'];
                print "<tr>";
                print "<td>$headline</td><td><a href='?action=edit&id=$id'>Edit</td>";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>";
                print "</tr>\n";
            
            }
        }
        tableEnd($dbNews);
    }

}


function save_news($action)
{
    global $siteID;
    $by=$_POST['postby'];
    $dt=date("Y-m-d H:i:s");
    $headline=addslashes($_POST['headline']);
    $message=addslashes($_POST['message']);
    if($_POST['urgent']){$urgent=1;}else{$urgent=0;}
    if($_POST['sticky']){$sticky=1;}else{$sticky=0;}
    if($_POST['popup']){$popup=1;}else{$popup=0;}
    $id=$_POST['id'];
    $archive=date("Y-m-d H:i",strtotime($dt."+1 week"));
    if ($action=='insert')
    {
        $sql="INSERT INTO mango_news (urgent,post_by, post_datetime, archive_datetime, headline, message, 
        sticky, popup, site_id) 
        VALUES ('$urgent','$by', '$dt', '$archive', '$headline', '$message', '$sticky', '$popup', '$siteID')";
        $db=dbinsertquery($sql);
            
    } else {
        $sql="SELECT * FROM mango_news WHERE id=$id";
        $dbNews=dbselectsingle($sql);
        $dt=$dbNews['data']['post_datetime'];
        $archive=date("Y-m-d H:i",strtotime($dt."+1 week"));
    
        $sql="UPDATE mango_news SET urgent='$urgent', archive_datetime='$archive', sticky='$sticky', 
        headline='$headline', message='$message', popup='$popup' WHERE id=$id";
        $db=dbexecutequery($sql);
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the news item.<br />'.$error,'error');
    } else {
        setUserMessage('The news item has been successfully saved.','success');
    }
    redirect("?action=list");
    
}

footer();
?>