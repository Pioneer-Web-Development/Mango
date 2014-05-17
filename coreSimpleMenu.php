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
        case "Save Menu":
        save_group('insert');
        break;
        
        case "Update Menu":
        save_group('update');
        break;
        
        case "add":
        setup_group('add');
        break;
        
        case "edit":
        setup_group('edit');
        break;
        
        case "delete":
        setup_group('delete');
        break;
        
        case "list":
        setup_group('list');
        break;
        
        
        case "addpage":
        pages('add');
        break;
        
        case "editpage":
        pages('edit');
        break;
        
        case "deletepage":
        pages('delete');
        break;
        
        case "listpages":
        pages('list');
        break;
        
        case "Save Page":
        save_page('insert');
        break;
        
        case "Update Page":
        save_page('update');
        break;
        
        
        
        default:
        setup_group('list');
        break;
        
    } 
    
    
function setup_group($action)
{
    $id=intval($_GET['id']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Menu";
            
            $sql="SELECT MAX(sort_order) as mw FROM simple_menu";
            $dbMax=dbselectsingle($sql);
            $order=$dbMax['data']['mw']+1;
        } else {
            $button="Update Group";
            $sql="SELECT * FROM simple_menu WHERE id=$id";
            $dbGroup=dbselectsingle($sql);
            $group=$dbGroup['data'];
            $name=$group['menu_title'];
            $order=$group['sort_order'];
        }
        print "<form method=post>\n";
        make_text('menu_title',$name,'Menu Title','',30);
        make_slider('sort_order',$order,'Sort Order','Order of menu items, left to right',1,100);
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $sql="DELETE FROM simple_menu WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the menu item.<br>'.$error,'error');
        } else {
            setUserMessage('The menu item was successfully deleted.','success');
        }
        redirect("?action=list");
    } else {
        global $siteID;
        $sql="SELECT * FROM simple_menu ORDER BY sort_order";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new menu</a>","Name",4);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $name=$group['menu_title'];
                $id=$group['id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=listpages&menuid=$id'>Menu Items</a></td>\n";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbGroups);
        
    }
}

function save_group($action)
{
    global $siteID;
    $id=$_POST['id'];
    $order=addslashes($_POST['sort_order']);
    $name=addslashes($_POST['menu_title']);
    if ($action=='insert')
    {
        $sql="INSERT INTO simple_menu (menu_title, sort_order) VALUES ('$name', '$order')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE simple_menu SET menu_title='$name', sort_order='$order' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    $error=$dbUpdate['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem saving the menu item.<br>'.$error,'error');
    } else {
        setUserMessage('The menu item was successfully saved','success');
    }
    redirect("?action=list");
}


function pages($action)
{
    $menuid=intval($_GET['menuid']);
    $sql="SELECT * FROM core_pages ORDER BY name ASC";
    $dbSimple=dbselectmulti($sql);
    $pages[0]='Select a menu option';
    if($dbSimple['numrows']>0)
    {
        foreach($dbSimple['data'] as $simple)
        {
            $pages[$simple['id']]=$simple['name'];
        }
    }
    
    if($action=='add' || $action=='edit')
    {
       if($action=='add')
       {
            $button="Save Page";
            $pageid=0;   
       } else {
            $pageid=intval($_GET['pageid']);
            $sql="SELECT * FROM simple_menu_pages WHERE page_id=$pageid AND simple_menu_id=$menuid";
            $dbPage=dbselectsingle($sql);
            $id=$dbPage['data']['id'];
            $button="Update Page";
       }
       print "<form method=post>\n";
       make_select('pageid',$pages[$pageid],$pages,'Page','Select existing page for this menu item');
       make_submit('submit',$button);
       make_hidden('menuid',$menuid);
       make_hidden('id',$id);
       print "</form>\n";
    } elseif($action=='delete')
    {
        $pageid=intval($_GET['pageid']);
        $menuid=intval($_GET['menuid']);
        $sql="DELETE FROM simple_menu_pages WHERE page_id=$pageid AND simple_menu_id='$menuid'";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the menu item.<br>'.$error,'error');
        } else {
            setUserMessage('The menu item was successfully deleted.','success');
        }
        
        redirect("?action=listpages&menuid=$menuid");
    }  else {
        $sql="SELECT * FROM simple_menu_pages WHERE simple_menu_id='$menuid'";
        $dbPages=dbselectmulti($sql);
        tableStart("<a href='?action=addpage&menuid=$menuid'>Add new menu item</a>,<a href='?action=list'>Return to top</a>","Menu name",3);
        if($dbPages['numrows']>0)
        {
            foreach($dbPages['data'] as $page)
            {
                $name=$pages[$page['page_id']];
                $id=$page['page_id'];
                print "<tr>\n";
                print "<td>$name</td>\n";
                print "<td><a href='?action=editpage&menuid=$menuid&pageid=$id'>Edit</a></td>\n";
                print "<td><a href='?action=deletepage&menuid=$menuid&pageid=$id' class='delete'>Delete</a></td>\n";
                print "</tr>\n";    
            }   
        }
        tableEnd($dbPages);
        
    }
}

function save_page($action)
{
    $pageid=$_POST['pageid'];
    $menuid=$_POST['menuid'];
    $id=$_POST['id'];
    
    if($action=='insert')
    {
        $sql="INSERT INTO simple_menu_pages (page_id, simple_menu_id) VALUES ('$pageid', '$menuid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE simple_menu_pages SET page_id='$pageid' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the menu item.<br>'.$error,'error');
    } else {
        setUserMessage('The menu item was successfully saved','success');
    }
    redirect("?action=listpages&menuid=$menuid"); 
}  

footer();
?>
