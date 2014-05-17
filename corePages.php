<?php
include("includes/mainmenu.php") ;

if ($_POST['submit']=='Add'){
    save_page('insert');
} elseif ($_POST['submit']=='Update'){
    save_page('update'); 
} elseif ($_POST['submit']=='Set Permissions'){
    save_permissions(); 
} elseif ($_POST['submit']=='Move Page'){
    move_page(); 
} else {
    show_pages();
}

function show_pages()
{
    $action=$_GET['action'];
    if ($_GET['parentid']){
        $parentid=$_GET['parentid'];
    } else {
        $parentid=0;
    }    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Add';
            $sql="SELECT MAX(weight) as mw FROM core_pages WHERE parent_id=$parentid";
            $dbMax=dbselectsingle($sql);
            $weight=$dbMax['data']['mw']+1;
            $display=1;
            $height=540;
            $width=500;
            $popup=0;
            $primary=0;
            $mango=1;
            $kiwi=0;
            $guava=0;
            $papaya=0;
            $pineapple=0;
        } else {
            $id=$_GET['id'];
            $sql="SELECT * FROM core_pages WHERE id=$id";
            $dbPage=dbselectsingle($sql);
            $page=$dbPage['data'];
            $name=stripslashes($page['name']);
            $filename=stripslashes($page['filename']);
            $weight=stripslashes($page['weight']);
            $popup=stripslashes($page['popup']);
            $width=stripslashes($page['popup_width']);
            $height=stripslashes($page['popup_height']);
            $display=stripslashes($page['display']);
            $primary=stripslashes($page['primary_site_only']);
            $mango=stripslashes($page['mango']);
            $kiwi=stripslashes($page['kiwi']);
            $guava=stripslashes($page['guava']);
            $papaya=stripslashes($page['papaya']);
            $pineapple=stripslashes($page['pineapple']);
            $description=stripslashes($page['description']);
           $button="Update";
        }
        print "<form method='post'>";
        make_text('name',$name,'Name','Menu Item Name',50);
        print "<div class='label'>Script Filename</div><div class='input'><small></small><br>
        <input type='text' name='filename' id='filename' size=50 onblur='checkForFile(\"filename\",\"exists\",\"/\");' value='$filename' /><span id='exists' style='font-weight:bold;color:green;margin-left:10px;'></span><br>
        </div><div class='clear'></div>\n";
        //make_number('weight',$weight,'Sort Order','1=top, higher numbers lower on the page.');
        make_slider('weight',$weight,'Sort Order','1=top, higher numbers lower on the page.',1,100,1);
        make_checkbox('display',$display,'Display?','Check to show this item in the menus');
        make_checkbox('primary',$primary,'Primary Only?','Check to show this item only on the primary site');
        make_checkbox('mango',$mango,'Visible in Mango?','Check to show this page in the Mango (production) system');
        make_checkbox('kiwi',$kiwi,'Visible in Kiwi?','Check to show this page in the Kiwi (advertising) system');
        make_checkbox('guava',$guava,'Visible in Guava?','Check to show this page in the Guava (editorial) system');
        make_checkbox('papaya',$papaya,'Visible in Papaya?','Check to show this page in the Papaya (circulation) system');
        make_checkbox('pineapple',$pineapple,'Visible in Pineapple?','Check to show this page in the Pineapple (business) system');
        
        print "<div class='label'>Popup</div><div class='input'>\n";
        print make_checkbox('popup',$popup)." show this item in a popup window.<br />\n";
        print "Popup width: ".input_text('width',$width,5,false,'','','','','return isNumberKey(event);')."<br />\n";
        print "Popup height: ".input_text('height',$height,5,false,'','','','','return isNumberKey(event);')."<br />\n";
        print "</div><div class='clear'></div>\n";
        make_textarea('description',$description,'Description','This will be used for tooltips or other areas to describe what this menu option is for.',100,5,false);
        make_submit('submit',$button);
        make_hidden('id',$id);
        make_hidden('parentid',$parentid);
        print "</form>\n";
        
    } elseif ($action=='delete')
    {
        $id=$_GET['id'];
        $sql="DELETE FROM core_permission_page WHERE pageID=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        $sql="DELETE FROM core_pages WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error.=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the page.<br>'.$error,'error');
        } else {
            setUserMessage('Page successfully deleted.','success');
        }
    
        redirect("?action=list&parentid=$parentid");
    } elseif ($action=='permissions')
    {
        permissions();
    } elseif ($action=='move')
    {
        move();
    } else {
        //list the pages
        $sql="SELECT * FROM core_pages WHERE parent_id=$parentid ORDER BY weight";
        $dbPages=dbselectmulti($sql);
        if ($parentid==0)
        {
            $options="<a href='?action=add&parentid=$parentid'>Add new menu item</a>";
        } else {
            //see if there is a parent of this parent item that is not 0
            $sql="SELECT * FROM core_pages WHERE id=$parentid";
            $dbParent=dbselectsingle($sql);
            $parent=$dbParent['data']['parent_id'];
            if($parent==0)
            {
                $options="<a href='?action=list&parentid=0'>Return to top level</a>,<a href='?action=add&parentid=$parentid'>Add new menu item</a>";
            } else {
                $options="<a href='?action=list&parentid=0'>Return to top level</a>,<a href='?action=list&parentid=$parent'>Return to parent</a>,<a href='?action=add&parentid=$parentid'>Add new menu item</a>";
            }
        }
        tableStart($options,"Sort Order,Menu Name,Mango,Kiwi,Guava,Papaya,Pineapple",12,'',0);
        if ($dbPages['numrows']>0)
        {
            foreach($dbPages['data'] as $page)
            {
                $name=$page['name'];
                $weight=$page['weight'];
                $id=$page['id'];
                if($page['mango']){$mango="<img src='artwork/icons/accepted_48.png' width=24>";}else{$mango="";}
                if($page['kiwi']){$kiwi="<img src='artwork/icons/accepted_48.png' width=24>";}else{$kiwi="";}
                if($page['guava']){$guava="<img src='artwork/icons/accepted_48.png' width=24>";}else{$guava="";}
                if($page['papaya']){$papaya="<img src='artwork/icons/accepted_48.png' width=24>";}else{$papaya="";}
                if($page['pineapple']){$pineapple="<img src='artwork/icons/accepted_48.png' width=24>";}else{$pineapple="";}
                print "<tr><td>$weight</td><td>$name</td><td style='text-align:center;'>$mango</td><td style='text-align:center;'>$kiwi</td><td style='text-align:center;'>$guava</td><td style='text-align:center;'>$papaya</td><td style='text-align:center;'>$pineapple</td>";
                print "<td><a href='?action=edit&id=$id&parentid=$parentid'>Edit</td>";
                print "<td><a href='?action=list&parentid=$id'>Sub-items</td>";
                print "<td><a href='?action=move&id=$id&parentid=$id'>Move</td>";
                print "<td><a href='?action=permissions&id=$id&parentid=$parentid'>Permissions</td>";
                print "<td><a class='delete' href='?action=delete&id=$id&parentid=$parentid'>Delete</a></td>";
                print "</tr>\n";
            
            }
        }
        tableEnd($dbPages);
    }

}

function permissions()
{
    $pageid=$_GET['id'];
    //get the name of the page
    $sql="SELECT * FROM core_pages WHERE id=$pageid";
    $dbPage=dbselectsingle($sql);
    $pagename=$dbPage['data']['name'];
    $parentid=$_GET['parentid'];
    $sql="SELECT * FROM core_permission_list ORDER BY displayname";
    $dbPermissions=dbselectmulti($sql);
    $sql="SELECT * FROM core_permission_page WHERE pageID=$pageid";
    $dbPages=dbselectmulti($sql);
    $pagepermissions=$dbPages['data'];
    print "<form method=post>\n";
    if ($dbPermissions['numrows']>0)
    {
        print "<h4>Please set permissions for the '$pagename' page:</h4>";
        $i=1;
        //split 3 columns
        $col=round($dbPermissions['numrows']/3,0);
        print "<div style='float:left;width:250px;margin-right:10px;'>\n";
        foreach($dbPermissions['data'] as $permission)
        {
            $pvalue=0;
            if ($dbPages['numrows']>0)
            {
                foreach($pagepermissions as $pagepermission)
                {
                    if ($permission['id']==$pagepermission['permissionID'])
                    {
                        if ($pagepermission['value']==1)
                        {
                            $pvalue=1;
                        }        
                    }
                }
            }
            print input_checkbox('permission_'.$permission['id'],$pvalue);
            print "&nbsp;&nbsp;".$permission['displayname']."<br>";
            if ($i==$col)
            {
                $i=1;
                print "</div>\n";
                print "<div style='float:left;width:250px;margin-right:10px;'>\n";
            } else {
                $i++;
            }
        }
        print "</div><div class='clear'></div>\n";
        print "<div class='label'></div><div class='input'>\n";
        print "<input type='hidden' id='pageid' name='pageid' value='$pageid'>\n";
        print "<input type='hidden' id='parentid' name='parentid' value='$parentid'>\n";
        print "<input type='submit' id='submit' name='submit' value='Set Permissions'>\n";
        print "</div><div class='clear'></div>\n";
    } else {
       print "Sorry, no permissions have been defined yet.";
    }
    print "</form>\n";
}

function save_permissions()
{
    $pageid=$_POST['pageid'];
    $parentid=$_POST['parentid'];
    //start by deleting all existing permissions for this user
    $sql="DELETE FROM core_permission_page WHERE pageID=$pageid";
    $dbDelete=dbexecutequery($sql);
    $sql="SELECT * FROM core_permission_list ORDER BY weight";
    $dbPermissions=dbselectmulti($sql);
    $value="";
    foreach ($dbPermissions['data'] as $permission)
    {
        $pvalue=0;
        if ($_POST["permission_$permission[id]"])
        {
            $pvalue=1;
        }
        $value.="('$permission[id]','$pageid','$pvalue'),";
    }
    $value=substr($value,0,strlen($value)-1);
    $sql="INSERT INTO core_permission_page (permissionID, pageID, value) VALUES $value";
    $dbinsert=dbinsertquery($sql);
    $error=$dbinsert['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem saving the permission for the page','error');
    } else {
        setUserMessage('Page permission successfully saved','success');
    }
    redirect("?action=list&parentid=$parentid");
    
}


function save_page($action)
{
    global $siteID;
    $name=addslashes($_POST['name']);
    $filename=addslashes($_POST['filename']);
    $weight=addslashes($_POST['weight']);
    $description=addslashes($_POST['description']);
    $id=$_POST['id'];
    $parentid=$_POST['parentid'];
    $height=$_POST['height'];
    $width=$_POST['width'];
    if ($_POST['display']){$display=1;}else{$display=0;}
    if ($_POST['popup']){$popup=1;}else{$popup=0;}
    if ($_POST['primary']){$primary=1;}else{$primary=0;}
    if ($_POST['mango']){$mango=1;}else{$mango=0;}
    if ($_POST['kiwi']){$kiwi=1;}else{$kiwi=0;}
    if ($_POST['guava']){$guava=1;}else{$guava=0;}
    if ($_POST['papaya']){$papaya=1;}else{$papaya=0;}
    if ($_POST['pineapple']){$pineapple=1;}else{$pineapple=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO core_pages (name, filename, parent_id, weight, display, popup, popup_width, popup_height, site_id, 
        primary_site_only, mango, kiwi, guava, papaya, pineapple, description) VALUES 
        ('$name', '$filename', '$parentid', '$weight', $display, $popup, $width, $height, $siteID, '$primary', '$mango', 
        '$kiwi', '$guava', '$papaya', '$pineapple', '$description')";
        $db=dbinsertquery($sql);
        $pageid=$db['insertid'];
    } else {
        $sql="UPDATE core_pages SET name='$name', filename='$filename', parent_id='$parentid', weight='$weight', 
        display=$display, primary_site_only='$primary', popup='$popup', popup_height='$height', popup_width='$width', 
        mango='$mango', kiwi='$kiwi', pineapple='$pineapple', guava='$guava', papaya='$papaya', description='$description' WHERE id=$id";
        $db=dbexecutequery($sql);                                                                                     
    }
    
    
    
    $error=$db['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem saving the page.<br>'.$error,'error');
    } else {
        setUserMessage('Page successfully saved','success');
    }
    redirect("?action=list&parentid=$parentid"); 
}


function move()
{
    $pageid=intval($_GET['id']);
    $sql="SELECT * FROM core_pages WHERE id='$pageid'";
    $dbPage=dbselectsingle($sql);
    $page=$dbPage['data'];
    print "<p>You are moving $page[name]</p>\n";
    $pages[0]='Please select new parent';
    $level='';
    $pages=nested_pages(0,$pages,$level);
    print "<form method=post>\n";
    make_select('newparent',$pages[0],$pages,'New parent','Please select which page to set as the parent of this page');
    make_hidden('pageid',$pageid);
    make_submit('submit','Move Page');
    print "</form>\n";    
}

function nested_pages($parentid,$pages,$level)
{
    $sql="SELECT * FROM core_pages WHERE parent_id=$parentid ORDER BY name";
    $dbPages=dbselectmulti($sql);
    if($dbPages['numrows']>0)
    {
        foreach($dbPages['data'] as $page)
        {
            $pages[$page['id']]=$level.stripslashes($page['name']);
            //see if this page has any sub items
            $sql="SELECT * FROM core_pages WHERE parent_id=$page[id]";
            $dbSubs=dbselectmulti($sql);
            if($dbSubs['numrows']>0)
            {
                $level.="--";
                $pages=nested_pages($page['id'],$pages,$level);
                $level=substr($level,0,strlen($level)-2);
            }
        }
    }
    return $pages; 
       
}

function move_page()
{
    $parentid=intval($_POST['newparent']);
    $pageid=intval($_POST['pageid']);
    $sql="UPDATE core_pages SET parent_id='$parentid' WHERE id='$pageid'";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem moving the page','error');
    } else {
        setUserMessage('Page successfully moved to the new parent.','success');
    }
    redirect("?action=list&parentid=$parentid");  
}

footer();
?>
