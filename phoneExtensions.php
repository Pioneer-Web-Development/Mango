<?php
//<!--VERSION: .9 **||**-->
if($_GET['action']=='export')
{
    include("includes/functions_db.php");
    exportWiki();
    dbclose();
    die();
} else {
    include("includes/mainmenu.php") ;
}

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
    switch ($action)
    {
        case "Save":
        save_code('insert');
        break;
        
        case "Update":
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
    $sql="SELECT extension_id, firstname, lastname FROM users";
    $dbEmployees=dbselectmulti($sql);
    $employees[0]='Not assigned';
    if($dbEmployees['numrows']>0)
    {
        foreach($dbEmployees['data'] as $emp)
        {
            if($emp['extension_id']!=0)
            {
                $employees[$emp['extension_id']]=stripslashes($emp['firstname'].' '.$emp['lastname']);
            }
        }
    }
    global $siteID;
    $id=intval($_GET['id']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save";
        } else {
            $button="Update";
            $sql="SELECT * FROM phone_extensions WHERE id=$id";
            $dbCode=dbselectsingle($sql);
            $code=$dbCode['data'];
            $extension=$code['extension'];
            $number=$code['direct_number'];
            $cube=$code['cube_port'];
            $panel=$code['panel_port'];
            $switch=$code['switch_port'];
            $notes=stripslashes($code['notes']);
        }
        print "<form method=post>\n";
        make_text('extension',$extension,'Extension','Internal extension');
        make_text('number',$number,'Direct Dial Number','Phone number excluding areacode');
        make_text('cube',$cube,'Cube Port #','Number for the port the phone is plugged into at the desk');
        make_text('panel',$panel,'Panel Port #','Number for the port in the panel the extension is tied to');
        make_text('switch',$switch,'Switch Port #','Number for the port in the panel the switch is connected to that leads to the panel port.');
        make_textarea('notes',$notes,'Notes','Any notes about this port');
        make_submit('submit',$button);
        make_hidden('id',$id);
        print "</form>\n";  
    } elseif($action=='delete') {
        $sql="DELETE FROM phone_extensions WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the phone extension.','error');
        } else {
            setUserMessage('The phone extension was successfully deleted','success');
        }
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM phone_extensions ORDER BY extension ASC";
        $dbCodes=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new extension</a>,<a href='?action=export&format=wiki'>Export in Wiki Format</a>,<a href='?action=export&format=csv'>Export to CSV format</a>","Extension,Assigned to,Cube Port,Panel Port,Switch Port",7);
        if ($dbCodes['numrows']>0)
        {
            foreach($dbCodes['data'] as $code)
            {
                $extension=$code['extension'];
                $employee=$employees[$code['id']];
                $cube=$code['cube_port'];
                $panel=$code['panel_port'];
                $switch=$code['switch_port'];
                $id=$code['id'];
                print "<tr><td>$extension</td>";
                print "<td>$employee</td>";
                print "<td>$cube</td>";
                print "<td>$panel</td>";
                print "<td>$switch</td>";
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
    $id=$_POST['id'];
    $extension=addslashes($_POST['extension']);
    $number=addslashes($_POST['number']);
    $cube=addslashes($_POST['cube']);
    $panel=addslashes($_POST['panel']);
    $switch=addslashes($_POST['switch']);
    $order=addslashes($_POST['notes']);
    $notes=addslashes($_POST['notes']);
    if ($action=='insert')
    {
        $sql="INSERT INTO phone_extensions (extension, direct_number, cube_port, panel_port, switch_port, notes) VALUES ('$extension', '$number', '$cube', '$panel', '$switch', '$notes')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE phone_extensions SET extension='$extension', direct_number='$number', cube_port='$cube', panel_port='$panel', switch_port='$switch', notes='$notes' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the extension','error');
    } else {
        setUserMessage('The extension was successfully saved','success');
    }
    redirect("?action=list");
    
}

function exportWiki()
{
    $sql="SELECT extension_id, firstname, lastname FROM users";
    $dbEmployees=dbselectmulti($sql);
    $employees[0]='Not assigned';
    if($dbEmployees['numrows']>0)
    {
        foreach($dbEmployees['data'] as $emp)
        {
            if($emp['extension_id']!=0)
            {
                $employees[$emp['extension_id']]=stripslashes($emp['firstname'].' '.$emp['lastname']);
            }
        }
    }
    if($_GET['format']=='wiki')
    {
        header('Content-Type: text/txt'); // as original file
        header('Content-Disposition: attachment; filename="extensions-wiki-'.date("Ymd").$sub.'.txt"');
        $dtype='';
        print "======Extensions/Ports======\nThis is dumped from Mango via the 'Export to Wiki' in the phone extensions section.\n";
        $sql="SELECT * FROM phone_extensions ORDER BY extension ASC";
        $dbExtensions=dbselectmulti($sql);
        if($dbExtensions['numrows']>0)
        {
            print "^Extension^Assigned User^Desk Port^Switch Port^Direct Dial^\n";
            foreach($dbExtensions['data'] as $extension)
            {
                print "| ".stripslashes($extension['extension']).' | ';    
                print $employees[$extension['id']]." | ";    
                print stripslashes($extension['cube_port']).' | ';    
                print stripslashes($extension['switch_port']).' | ';    
                print stripslashes($extension['direct_number']).' | ';    
                print "\n";  
            }
        }
    } else {
        header('Content-Type: text/csv'); // as original file
        header('Content-Disposition: attachment; filename="extensions-wiki-'.date("Ymd").$sub.'.csv"');
        $dtype='';
        print "Extensions/Ports\nThis is dumped from Mango via the 'Export to Wiki' in the phone extensions section.\n";
        $sql="SELECT * FROM phone_extensions ORDER BY extension ASC";
        $dbExtensions=dbselectmulti($sql);
        if($dbExtensions['numrows']>0)
        {
            print "Extension,Assigned User,Desk Port,Switch Port,Direct Dial^\n";
            foreach($dbExtensions['data'] as $extension)
            {
                print "| ".stripslashes($extension['extension']).',';    
                print $employees[$extension['id']].",";    
                print stripslashes($extension['cube_port']).',';    
                print stripslashes($extension['switch_port']).',';    
                print stripslashes($extension['direct_number']);    
                print "\n";  
            }
        }
    }    
}

footer();
?>
