<?php
//<!--VERSION: 1.0 **||**-->

include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
    switch ($action)
    {
        case "Save Site":
        save_site('insert');
        break;
        
        case "Update Site":
        save_site('update');
        break;
        
        case "add":
        setup_sites('add');
        break;
        
        case "edit":
        setup_sites('edit');
        break;
        
        case "delete":
        setup_sites('delete');
        break;
        
        case "list":
        setup_sites('list');
        break;
        
        default:
        setup_sites('list');
        break;
        
    } 
    
    
function setup_sites($action)
{
    $siteid=intval($_GET['siteid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Site";
        } else {
            $button="Update Site";
            $sql="SELECT * FROM core_sites WHERE id=$siteid";
            $dbSite=dbselectsingle($sql);
            $site=$dbSite['data'];
            $site_name=stripslashes($site['site_name']);
            $site_url=stripslashes($site['site_url']);
            $dbname=stripslashes($site['db_name']);
            $dbuser=stripslashes($site['db_user']);
            $dbpass=stripslashes($site['db_pass']);
            $ipaddress=stripslashes($site['ip_address']);
            $local=stripslashes($site['local_site']);
            $primary=stripslashes($site['primary_site']);
            $ftpuser=stripslashes($site['ftp_user']);
            $ftppass=stripslashes($site['ftp_pass']);
            $ftpdirectory=stripslashes($site['ftp_directory']);
        }
        print "<form action=\"$_SERVER[PHP_SELF]\" method=post>\n";
        make_text('site_name',$site_name,'Site Name','',50);
        make_text('site_url',$site_url,'Site URL','',50);
        make_text('dbname',$dbname,'DB Name','',50);
        make_text('dbuser',$dbuser,'DB Username','',50);
        make_text('dbpass',$dbpass,'DB Password','',50);
        make_text('ipaddress',$ipaddress,'IP Address of server','',50);
        make_text('ftpuser',$ftpuser,'FTP username','',50);
        make_text('ftppass',$ftppass,'FTP password','',50);
        make_text('ftpdirectory',$ftpdirectory,'FTP base directory','',50);
        make_checkbox('primary',$primary,'Primary?','Check if is this the primary or site that updates are pushed from');
        //make_checkbox('local',$local,'Local?','Check if is this the local site for this install');
        make_hidden('site_id',$siteid);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $sql="DELETE FROM core_sites WHERE id=$siteid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the site.<br>'.$error,'error');
        } else {
            setUserMessage('Site successfully deleted.','success');
        }
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM core_sites ORDER BY site_name";
        $dbSites=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new site</a>","Site Name,Site URL",4);
        if ($dbSites['numrows']>0)
        {
            foreach($dbSites['data'] as $site)
            {
                $siteid=$site['id'];
                $sitename=$site['site_name'];
                $siteurl=$site['site_url'];
                print "<tr><td>$sitename</td><td>$siteurl</td>";
                print "<td><a href='?action=edit&siteid=$siteid'>Edit</a></td>\n";
                print "<td><a href='?action=delete&siteid=$siteid' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbSites);
        
    }
}

function save_site($action)
{
    $siteid=$_POST['site_id'];
    $site_name=addslashes($_POST['site_name']);
    $site_url=addslashes($_POST['site_url']);
    $dbname=addslashes($_POST['dbname']);
    $dbuser=addslashes($_POST['dbuser']);
    $dbpass=addslashes($_POST['dbpass']);
    $ipaddress=addslashes($_POST['ipaddress']);
    $ftpuser=addslashes($_POST['ftpuser']);
    $ftppass=addslashes($_POST['ftppass']);
    $ftpdirectory=addslashes($_POST['ftpdirectory']);
    if($_POST['local']){$local=1;}else{$local=0;}
    if($_POST['primary']){$primary=1;}else{$primary=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO core_sites (site_name, site_url, db_name, db_user, db_pass, ip_address, local_site, 
        primary_site, ftp_user, ftp_pass, ftp_directory) VALUES 
        ('$site_name', '$site_url', '$dbname', '$dbuser', '$dbpass', '$ipaddress', '$local', '$primary', 
        '$ftpuser', '$ftppass', '$ftpdirectory')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE core_sites SET site_name='$site_name', site_url='$site_url', db_name='$dbname', 
        db_user='$dbuser', db_pass='$dbpass', ip_address='$ipaddress', local_site='$local', primary_site='$primary',
        ftp_user='$ftpuser', ftp_pass='$ftppass', ftp_directory='$ftpdirectory' WHERE id=$siteid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the site.<br>'.$error,'error');
    } else {
        setUserMessage('Site successfully saved.','success');
    }
    redirect("?action=list");
}

footer();
?>
