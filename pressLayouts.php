<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;
include("includes/layoutGenerator.php") ;

$sql="SELECT * FROM press_towers WHERE tower_type='printing' AND press_id=$GLOBALS[pressid] ORDER BY tower_order ASC";
$dbTowers=dbselectmulti($sql);
$sql="SELECT * FROM press_towers WHERE tower_type='folder' AND press_id=$GLOBALS[pressid] ORDER BY tower_order ASC";
$dbFolders=dbselectmulti($sql);
if ($dbFolders['numrows']>0)
{
    $foldersFormers=array();
    foreach($dbFolders['data'] as $folder)
    {
        $fcount=$folder['folder_config'];
        $fid=$folder['id'];
        for ($i=1;$i<=$fcount;$i++)
        {
            $foldersFormers[$fid."-".$i]="$folder[tower_name] - former #$i";
        }
    }
}

if ($_POST)
{
    $action=$_POST['submit'];
} elseif (isset($_GET['action']))
{
    $action=$_GET['action']; 
} else {
    $action="listlayouts";
}

switch ($action)
{
    case "addlayout";
    layouts('add');
    break;
    
    case "editlayout";
    layouts('edit');
    break;
    
    case "deletelayout";
    layouts('delete');
    break;
    
    case "duplicatelayout";
    duplicate();
    break;
    
    case "listlayouts";
    layouts('list');
    break;
    
    case "addsection";
    sections('add');
    break;
    
    case "editsection";
    sections('edit');
    break;
    
    case "listsections";
    sections('list');
    break;
    
    case "deletesection";
    sections('delete');
    break;
    
    case "configure";
    print "<div style='width:200px;'>\n";
    configure($_GET['layoutid']);
    print "</div>\n";
    break;
    
    case "Save Configuration";
    save_configuration('insert');
    break;
    
    case "Update Configuration";
    save_configuration('update');
    break;

    case "Save Layout";
    save_layout('insert');
    break;
    
    case "Update Layout";
    save_layout('update');
    break;
    
    case "Save Section";
    save_section('insert');
    break;
    
    case "Update Section";
    save_section('update');
    break;
    
    default:
    layouts('list');
    break;

}


function layouts($action)
{
    global $siteID;
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $available=1;
            $button="Save Layout";
            $preferred=0;
        } else {
            $id=$_GET['layoutid'];
            $button="Update Layout";
            $sql="SELECT * FROM layout WHERE id=$id";
            $dbLayout=dbselectsingle($sql);
            $layout=$dbLayout['data'];
            $name=stripslashes($layout['layout_name']);
            $notes=stripslashes($layout['layout_notes']);
            $difficulty=stripslashes($layout['difficulty']);
            $available=$layout['available'];
            $preferred=$layout['preferred'];
            $ribbon1=$layout['ribbon1_used'];
            $ribbon2=$layout['ribbon2_used'];
        }
        print "<form name='layouts' enctype='multipart/form-data' method='post'>\n";
        make_text('name',$name,'Layout Name','',30);
        make_text('difficulty',$difficulty,'Run Difficulty','1-10 with 10 being extremely difficult',10,'',false,'','','','return isNumberKey(event);');
        make_textarea('notes',$notes,'Notes','',30,3,false);
        make_checkbox('available',$available,'Make layout available','Makes this a chooseable configuration');
        make_checkbox('preferred',$preferred,'Preferred','Makes this the preferred layout for this page/section choice');
        make_checkbox('ribbon1',$ribbon1,'Ribbon Deck 1','Does this layout require use of Ribbon Deck 1?');
        make_checkbox('ribbon2',$ribbon2,'Ribbon Deck 2','Does this layout require use of Ribbon Deck 2?');
        print "<input type='hidden' name='layoutid' id='layoutid' value='$id'>\n";
        make_submit('submit',$button,'Action');
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $layoutid=intval($_GET['layoutid']);
        $sql="DELETE FROM layout WHERE id=$layoutid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        $sql="DELETE FROM layout_sections WHERE layout_id=$layoutid";
        $dbDelete=dbexecutequery($sql);
        $error.=$dbDelete['error'];
        $sql="DELETE FROM layout_page_config WHERE layout_id=$layoutid";
        $dbDelete=dbexecutequery($sql);
        $error.=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the layout and associated elements.','error');
        } else {
            setUserMessage('Layout and associated elements have been successfully deleted.','success');
        }
        redirect("?action=listlayout");
        
    } else {
        //list the layouts
        $sql="SELECT * FROM layout WHERE site_id=$siteID ORDER BY layout_name";
        $dbLayouts=dbselectmulti($sql);
        tableStart("<a href='?action=addlayout'>Add new layout</a>","Name,Notes",7);
        if ($dbLayouts['numrows']>0)
        {
            foreach($dbLayouts['data'] as $layout)
            {
                $layoutid=$layout['id'];
                $name=$layout['layout_name'];
                $notes=$layout['layout_notes'];
                print "<tr><td>$name</td>";
                print "<td>".wordwrap($notes,50,"<br>",true)."</td>";
                print "<td><a href='?action=editlayout&layoutid=$layoutid'>Edit Layout</a></td>\n";
                print "<td><a href='?action=listsections&layoutid=$layoutid'>Sections</a></td>\n";
                print "<td><a href='?action=configure&layoutid=$layoutid'>Configure Press</a></td>\n";
                print "<td><a href='?action=duplicatelayout&layoutid=$layoutid'>Duplicate Layout</a></td>\n";
                print "<td><a href='?action=deletelayout&layoutid=$layoutid' class='delete'>Delete Layout</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbLayouts);
    }


}

function save_layout($action)
{
    global $siteID;
    $name=addslashes($_POST['name']);
    $notes=addslashes($_POST['notes']);
    $difficulty=addslashes($_POST['difficulty']);
    if ($difficulty==''){$difficulty=0;}
    if ($_POST['available']){$available=1;}else{$available=0;}
    if ($_POST['preferred']){$preferred=1;}else{$preferred=0;}
    if ($_POST['ribbon1']){$ribbon1=1;}else{$ribbon1=0;}
    if ($_POST['ribbon2']){$ribbon2=1;}else{$ribbon2=0;}
    $layoutid=$_POST['layoutid'];
    if ($action=='insert')
    {
        $sql="INSERT INTO layout (layout_name, layout_notes, difficulty, available, preferred, site_id, ribbon1_used, ribbon2_used) 
        VALUES ('$name','$notes', '$difficulty', '$available', '$preferred', '$siteID','$ribbon1', '$ribbon2')";
        $dbInsert=dbinsertquery($sql);
        $id=$dbInsert['numrows'];
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE layout SET layout_name='$name', layout_notes='$notes', difficulty='$difficulty',
        preferred='$preferred', available='$available', ribbon1_used='$ribbon1', ribbon2_used='$ribbon2' WHERE id=$layoutid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the layout','error');
        redirect("?action=listlayouts");
    } else {
        setUserMessage('Layout successfully saved','success');
        if ($action=='insert')
        {
            redirect("?action=listsections&layoutid=$id");
        } else {
            redirect("?action=listlayouts");
        }
    }
        
    
}


function sections($action)
{
    $layoutid=intval($_GET['layoutid']);
    global $sections, $producttypes, $leadtypes, $laps, $balloontypes,$dbTowers, $foldersFormers, $siteID;
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Section";
            $towers=array();
            $doubletruck=0;
            $producttype="Broadsheet";
            $leadtype="Newspaper";
            $sectionnumber="1";
            $lowpage=0;
            $highpage=0;
            $former=1;
            $folder=1;
            $balloon="NA";
        } else {
            $button="Update Section";
            $sectionid=intval($_GET['sectionid']);
            $sql="SELECT * FROM layout_sections WHERE id=$sectionid";
            $dbSection=dbselectsingle($sql);
            $section=$dbSection['data'];
            $sectionnumber=stripslashes($section['section_number']);
            $producttype=stripslashes($section['product_type']);
            $leadtype=stripslashes($section['lead_type']);
            $lowpage=stripslashes($section['low_page']);
            $highpage=stripslashes($section['high_page']);
            $doubletruck=stripslashes($section['doubletruck']);
            $folder=stripslashes($section['folder']);
            $former=stripslashes($section['former']);
            $balloon=stripslashes($section['balloon']);
            $towers=stripslashes($section['towers']);
            $towers=explode("|",$towers);
        }
        print "<form name='sections' enctype='multipart/form-data' method='post'>\n";
        make_select('section_number',$sections[$sectionnumber],$sections,'Section #');
        make_select('product_type',$producttypes[$producttype],$producttypes,'Product type');
        make_select('lead_type',$leadtypes[$leadtype],$leadtypes,'Lead type');
        make_text('low_page',$lowpage,'Low page','Low page number',5,'',false,'','','','return isNumberKey(event);');
        make_text('high_page',$highpage,'High page','High page number',5,'',false,'','','','return isNumberKey(event);');
        make_checkbox('doubletruck',$doubletruck,'Doubletruck','This section has a doubletruck');
        print "<div class='label'>Towers</div>\n";
        print "<div class='input'>\n";
           if ($dbTowers['numrows']>0)
            {
                foreach($dbTowers['data'] as $tower)
                {
                    $checked=false;
                    if (in_array($tower['id'],$towers)){$checked=true;}
                    print input_checkbox("tower_".$tower['id'],$checked).$tower['tower_name']."<br>\n";
                }
            }
            print "</div>\n";
            print "<div class='clear'></div>\n";
        make_select('folder',$foldersFormers[$folder."-".$former],$foldersFormers,'Runs to','Section webbed to folder/former');
        make_select('balloon',$balloontypes[$balloon],$balloontypes,'Balloon to','Section balloons');
        print "<input type='hidden' name='layoutid' id='layoutid' value='$layoutid'>\n";
        print "<input type='hidden' name='sectionid' id='sectionid' value='$sectionid'>\n";
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $id=intval($_GET['sectionid']);
        $sql="DELETE FROM layout_sections WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the section','error');
        } else {
            setUserMessage('Section successfully deleted','success');
        }
        redirect("?action=listsections&layoutid=$layoutid");
        
    } else {
        //list the sections
        $sql="SELECT * FROM layout_sections WHERE layout_id=$layoutid ORDER BY section_number ASC";
        $dbSections=dbselectmulti($sql);
        tableStart("<a href='?action=listlayouts'>Return to main</a>,<a href='?action=addsection&layoutid=$layoutid'>Add new section</a>,<a href='?action=configure&layoutid=$layoutid'>Configure Page Layout</a>","Section #,Product Type,Low Page,High Page",6);
        if ($dbSections['numrows']>0)
        {
            foreach($dbSections['data'] as $section)
            {
                $sectionid=$section['id'];
                $number=$section['section_number'];
                $lowpage=$section['low_page'];
                $highpage=$section['high_page'];
                $product=$producttypes[$section['product_type']];
                print "<tr><td>Section #$number</td><td>$product</td><td>$lowpage</td><td>$highpage</td>";
                print "<td><a href='?action=editsection&layoutid=$layoutid&sectionid=$sectionid'>Edit</a></td>\n";
                print "<td><a href='?action=deletesection&layoutid=$layoutid&sectionid=$sectionid' class='delete'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbSections);
    
    }


}

function save_section($action)
{
    global $dbTowers, $siteID;
    $layoutid=$_POST['layoutid'];
    $sectionid=$_POST['sectionid'];
    $sectionnumber=$_POST['section_number'];
    $producttype=$_POST['product_type'];
    $leadtype=$_POST['lead_type'];
    $lowpage=$_POST['low_page'];
    $highpage=$_POST['high_page'];
    $folderFormer=$_POST['folder'];
    $folderFormer=explode("-",$folderFormer);
    $folder=$folderFormer[0];
    $former=$folderFormer[1];
    $balloon=$_POST['balloon'];
    if ($_POST['doubletruck']){$doubletruck=1;}else{$doubletruck=0;}
    //now check the towers
    $towers="";
    foreach($dbTowers['data'] as $tower)
    {
        if ($_POST['tower_'.$tower['id']]){$towers.=$tower['id']."|";}
    }
    $towers=substr($towers,0,strlen($towers)-1);
    if ($action=='insert')
    {
        $sql="INSERT INTO layout_sections (layout_id,section_number, product_type, lead_type, low_page, high_page, doubletruck, folder,
        former, balloon, towers) VALUES ('$layoutid', '$sectionnumber', '$producttype', '$leadtype', '$lowpage', '$highpage', '$doubletruck',
        '$folder', '$former', '$balloon', '$towers')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];    
    } else {
        $sql="UPDATE layout_sections SET section_number='$sectionnumber', product_type='$producttype', lead_type='$leadtype', low_page='$lowpage',
        high_page='$highpage', doubletruck='$doubletruck', folder='$folder', former='$former', balloon='$balloon', towers='$towers' WHERE id=$sectionid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the layout section','error');
    } else {
        setUserMessage('Layout section successfully saved','success');
    }
    redirect("?action=listsections&layoutid=$layoutid");
    
}

function save_configuration($action)
{
    $layoutid=$_POST['layoutid']; 
    foreach ($_POST as $key=>$value)
      {
        if (strpos($key,"_")>0)
        {
            $parts=explode("_",$key);
            $towerid=$parts[1];
            $side=$parts[2];
            $row=$parts[3];
            $column=$parts[4];
            $pagenumber=$value;
            if ($pagenumber==''){$pagenumber=0;}
            $sqlvalues.="('$layoutid', '$towerid','$side','$row','$column','$pagenumber'),";
        }
      }
      $sqlvalues=substr($sqlvalues,0,strlen($sqlvalues)-1);
      //first, delete any possible existing layout
      $sql="DELETE FROM layout_page_config WHERE layout_id=$layoutid";
      $dbDelete=dbexecutequery($sql);
      
      
      $towersql="INSERT INTO layout_page_config (layout_id,tower_id,side,tower_row,tower_column,page_number) VALUES $sqlvalues";
      $dbLayout=dbinsertquery($towersql);
      $error=$dbLayout['error'];
      if ($error!='')
        {
            setUserMessage('There was a problem saving the layout configuration','error');
        } else {
            setUserMessage('Configuration successfully saved','success');
        }
        redirect("?listlayouts");
      

}


function duplicate()
{
    $layoutid=intval($_GET['layoutid']);
    
    //first, duplicate the layout itself
    $sql="SELECT * FROM layout WHERE id=$layoutid";
    $dbLayout=dbselectsingle($sql);
    //build the new sql
    $fields="";
    $values="";
    foreach($dbLayout['data'] as $field=>$value)
    {
        if($field!='id')
        {
            if($field=='layout_name'){$value.=" copy";}
            $fields.=$field.",";
            $values.="'$value',";    
        }
    }
    $fields=substr($fields,0,strlen($fields)-1);
    $values=substr($values,0,strlen($values)-1);
    $layoutsql="INSERT INTO layout ($fields) VALUES ($values)";
    $dbNewLayout=dbinsertquery($layoutsql);
    if($dbNewLayout['error']=='')
    {
        $newlayoutid=$dbNewLayout['insertid'];
        
        //now get sections
        $sql="SELECT * FROM layout_sections WHERE layout_id=$layoutid";
        $dbLayouts=dbselectmulti($sql);
        foreach($dbLayouts['data'] as $layoutsection)
        {
            $fields="";
            $values="";
            foreach($layoutsection as $field=>$value)
            {
                if($field!='id')
                {
                    if($field=='layout_id'){$value=$newlayoutid;}
                    $fields.=$field.",";
                    $values.="'$value',";    
                }
            }
            $fields=substr($fields,0,strlen($fields)-1);
            $values=substr($values,0,strlen($values)-1);
            $sectionsql="INSERT INTO layout_sections ($fields) VALUES ($values)";
            $dbNewSection=dbinsertquery($sectionsql);
        }
        
        //finally, duplicate the layout configuration
        $sql="SELECT * FROM layout_page_config WHERE layout_id=$layoutid";
        $dbConfigs=dbselectmulti($sql);
        foreach($dbConfigs['data'] as $layoutconfigs)
        {
            $fields="";
            $values="";
            foreach($layoutconfigs as $field=>$value)
            {
                if($field!='id')
                {
                    if($field=='layout_id'){$value=$newlayoutid;}
                    $fields.=$field.",";
                    $values.="'$value',";    
                }
            }
            $fields=substr($fields,0,strlen($fields)-1);
            $values=substr($values,0,strlen($values)-1);
            $configsql="INSERT INTO layout_page_config ($fields) VALUES ($values)";
            $dbNewConfig=dbinsertquery($configsql);
        }
        
    } else {
         if ($error!='')
        {
            setUserMessage('There was a problem saving the layout configuration','error');
        } else {
            setUserMessage('Configuration successfully saved','success');
        }
    }
    redirect("?listlayouts");
}

footer();
?>