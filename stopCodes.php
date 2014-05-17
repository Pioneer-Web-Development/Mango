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
        case "Save Code":
        save_code('insert');
        break;
        
        case "Update Code":
        save_code('update');
        break;
        
        case "add":
        setup_codes('add');
        break;
        
        case "edit":
        setup_codes('edit');
        break;
        
        case "delete":
        setup_codes('delete');
        break;
        
        case "list":
        setup_codes('list');
        break;
        
        default:
        setup_codes('list');
        break;
        
    } 
    
    
function setup_codes($action)
{
    global $siteID;
    $cats=array("press"=>"Press","mailroom"=>"Mailroom");
    $id=intval($_GET['id']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Code";
            $order=99;
            $specify=0;
            $cat='press';
            $notes=1;
        } else {
            $button="Update Code";
            $sql="SELECT * FROM stop_codes WHERE id=$id";
            $dbCode=dbselectsingle($sql);
            $code=$dbCode['data'];
            $stopcode=$code['stop_name'];
            $order=$code['stop_order'];
            $specify=$code['specify'];
            $cat=$code['category'];
            $notes=$code['notes'];
            $notestext=stripslashes($code['notes_text']);
        }
        print "<form method=post>\n";
        make_select('category',$cats[$cat],$cats,'Category','Which category does this belong to?');
        make_text('code',$stopcode,'Stop code','This is the text displayed on the button and in reports',20);
        make_number('order',$order,'Order','The order of the buttons on the press console');
        make_checkbox('specify',$specify,'Specify location','Will show a schematic of press to locate where the event occurred.');
        make_checkbox('notes',$notes,'Notes','Indicate if a space for notes will be created.');
        make_text('notetext',$notestext,'Text for notes','Specify the question to be answered in the notes field',100);
        make_submit('submit',$button);
        make_hidden('codeid',$id);
        print "</form>\n";  
    } elseif($action=='delete') {
        $sql="UDPATE stop_codes SET status=99 WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the stop code','error');
        } else {
            setUserMessage('Stop code successfully deleted','success');
        }
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM stop_codes WHERE site_id=$siteID AND status=1 ORDER BY stop_order ASC";
        $dbCodes=dbselectmulti($sql);
        tableStart("<a href='?&action=add'>Add new stop code</a>","Code",3);
        if ($dbCodes['numrows']>0)
        {
            foreach($dbCodes['data'] as $code)
            {
                $name=$code['stop_name'];
                $id=$code['id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbCodes);
        
    }
}

function save_code($action)
{
    global $siteID;
    $id=$_POST['codeid'];
    $code=addslashes($_POST['code']);
    $order=addslashes($_POST['order']);
    $cat=addslashes($_POST['category']);
    $notestext=addslashes($_POST['notetext']);
    if ($_POST['specify']){$specify=1;}else{$specify=0;}
    if ($_POST['notes']){$notes=1;}else{$notes=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO stop_codes (category,notes_text,specify,notes,stop_name, stop_order, site_id) VALUES ('$cat', '$notestext', '$specify', '$notes','$code', '$order', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE stop_codes SET category='$cat', notes_text='$notestext', stop_name='$code', stop_order='$order', specify='$specify', notes='$notes' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the press stop code','error');
    } else {
        setUserMessage('Press stop code successfully saved','success');
    }
    redirect("?action=list");
    
}

footer();
?>
