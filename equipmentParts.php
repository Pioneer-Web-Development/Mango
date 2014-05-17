<?php
include("includes/mainmenu.php") ;
   
if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
switch ($action)
{
    case "Save Part":
    save_part('insert');
    break;
    
    case "Update Part":
    save_part('update');
    break;
    
    case "add":
    setup_parts('add');
    break;
    
    case "edit":
    setup_parts('edit');
    break;
    
    case "delete":
    setup_parts('delete');
    break;
    
    case "Add Part":
    add_existing();
    break;
    
    case "remove":
    remove_part();
    break;
    
    case "list":
    setup_parts('list');
    break;
    
    default:
    setup_parts('list');
    break;   
} 

function add_existing()
{
    $type=$_POST['type'];
    $componentid=intval($_POST['componentid']);
    $equipmentid=intval($_POST['equipmentid']);
    $parentid=intval($_POST['parentid']);
    $partid=intval($_POST['partid']);
    $sql="INSERT INTO equipment_part_xref (equipment_type, equipment_id, component_id, part_id, parent_id) VALUES 
    ('$type', '$equipmentid', '$componentid', '$partid', '$parentid')";
    $dbAdd=dbinsertquery($sql);
    if($dbAdd['error']!='')
    {
        setUserMessage('There was a problem adding the existing part to this component.<br>'.$dbAdd['error'],'error');
    } else {
        setUserMessage('The part has been added to this component.','success');
    }
    setup_parts('list'); 
}

function remove_part()
{
    $type=$_GET['type'];
    $componentid=intval($_GET['componentid']);
    $equipmentid=intval($_GET['equipmentid']);
    $parentid=intval($_GET['parentid']);
    $id=intval($_GET['partid']);
    $xrefid=intval($_GET['xrefid']);
    $sql="DELETE FROM equipment_part_xref WHERE id=$xrefid";
    $dbUpdate=dbexecutequery($sql);
    if($dbUpdate['error']!='')
    {
        setUserMessage('There was a problem removing the existing part from this component.<br>'.$dbUpdate['error'],'error');
    } else {
        setUserMessage('The part has been removed from this component.','success');
    }
    setup_parts('list');
}
    
function setup_parts($action)
{
    global $qtypes,$departments, $vendors;
    $type=$_GET['type'];
    $componentid=intval($_GET['componentid']);
    $equipmentid=intval($_GET['equipmentid']);
    $parentid=intval($_GET['parentid']);
    $gls=array();
    $gls[0]='Please set GL#';
    $sql="SELECT * FROM general_ledgers ORDER BY gl_number";
    $dbGL=dbselectmulti($sql);
    if ($dbGL['numrows']>0)
    {
        foreach($dbGL['data'] as $glnum)
        {
            $gls[$glnum['id']]=$glnum['gl_number'].' - '.$glnum['gl_description'];    
        }
    }
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Part";
            $vendorid=0;
            $reorderQuantity=0;
            $inventoryQuantity=0;
            $lastDate=date("Y-m-d");
            $nextDate=date("Y-m-d");
            $currentDate=date("Y-m-d");
            $lastCount=0;
            $nextCount=0;
            $currentCount=0;
            $lifeDays=0;
            $lifeImpressions=0;
            $partcost="0.00";
            $taxable=0;
            $autopo=0;
            $gl=0;
            $qtype='unit';
            $departmentid=0;
            //need to create a random 16 character thing for part id
            $partid='new-'.generate_random_string(16);
            
        } else {
            $button="Update Part";
            $partid=intval($_GET['partid']);
            $sql="SELECT * FROM equipment_part WHERE id=$partid";
            $dbPart=dbselectsingle($sql);
            $part=$dbPart['data'];
            $partname=$part['part_name'];
            $partcost=$part['part_cost'];
            $partnumber=$part['part_number'];
            $notes=$part['part_notes'];
            $image=$part['part_image'];
            $vendorid=$part['part_vendor'];;
            $reorderQuantity=$part['part_reorder_quantity'];
            $inventoryQuantity=$part['part_inventory_quantity'];
            $lifeDays=$part['part_life_days'];
            $lifeImpressions=$part['part_life_impressions'];
            $lifetype=$part['part_life_type'];
            $taxable=$part['part_taxable'];
            $autopo=$part['auto_po'];
            $gl=$part['part_gl'];
            $qtype=$part['part_quantity_type'];
            $departmentid=$part['department_id'];
            if($type==''){$type=$part['equipment_type'];}
            if($equipmentid==0 || $equipmentid==''){$equipmentid=$part['equipment_id'];}
            if($componentid==0 || $componentid==''){$componentid=$part['component_id'];}
        }
        
        $lifetypes=array("impressions"=>"impressions","days"=>"days");
        print "<form action='$_SERVER[PHP_SELF]' method=post enctype='multipart/form-data'>\n";
        make_text('partname',$partname,'Part Name','Descriptive name of the part',50);
        make_select('department',$departments[$departmentid],$departments,'Department','Which department is the primary user of this part?');
        print "<div class='label'>Add new vendor</div><div class='input'>";
        print "<div style='float:left;margin-right:10px;'>Vendor: ".make_select('partvendor',$vendors[$vendorid],$vendors);
        print "</div><div style='float:left;margin-right:10px;'>Part #: ".make_text('partnumber',$partnumber,'','',10);
        print "</div><div style='float:left;margin-right:10px;'>Cost: ".make_number('partcost',$partcost);
        print "</div>";
        print "<input type='button' value='Add Vendor' onclick='addPartVendor();' style='height:20px;padding:2px;margin-left:4px;font-size:12px;padding-bottom:4px;'><div class='clear'></div>\n";
        print "<div id='vendors'>\n";
        //now show all the existing vendors
        $sql="SELECT A.*, B.vendor_name FROM equipment_part_vendor A, accounts B WHERE A.part_id='$partid' AND A.vendor_id=B.id";
        $dbPartVendors=dbselectmulti($sql);
        if($dbPartVendors['numrows']>0)
        {
            print "<div style='border-bottom:thin solid black;margin-bottom:4px;padding-bottom:2px;'>
               <div style='width:300px;float:left;'><b>Vendor</b></div>
               <div style='width:150px;float:left;'><b>Part #</b></div>
               <div style='width:150px;float:left;'><b>Cost</b></div>
               <div class='clear'></div>
            </div>\n";
            foreach($dbPartVendors['data'] as $partvendor)
            {
                $partvendorid=$partvendor['id'];
                print "<div id='vendor_$partvendorid' style='margin-bottom:2px;padding-bottom:2px;border-bottom:thin solid black;'>";
                print "<div style='width:300px;float:left;'>".stripslashes($partvendor['vendor_name'])."</div>";
                print "<div style='width:150px;float:left;'><input type='text' id='part_number_$partvendorid' size=10 value='".stripslashes($partvendor['part_number'])."' /></div>";
                print "<div style='width:150px;float:left;'>\$<input type='text' id='part_cost_$partvendorid' size=10 value='".stripslashes($partvendor['part_cost'])."' /></div>";
                print "<div style='float:left;width:200px;'><div style='float:left;'><input type='button' value='Update' onclick='updatePartVendor(\"$partvendorid\");' style='height:20px;padding:2px;margin-left:4px;font-size:12px;padding-bottom:4px;'><input type='button' value='Delete' onclick='deletePartVendor(\"$partvendorid\");' style='height:20px;padding:2px;margin-left:4px;font-size:12px;padding-bottom:4px;'></div><div id='update_$partvendorid' style='display:none;float:left;'><img src='artwork/icons/accepted_48.png' border=0 height=20 /></div></div><div class='clear'></div>";
                print "</div>";
            }
        }
        print "</div>\n";
        print "</div><div class='clear'></div>\n";
        
        make_checkbox('autopo',$autopo,'Auto PO',' automatically create a po for approval if we hit the reorder point.');
        make_checkbox('taxable',$taxable,'Taxable',' check if this part is taxable');
        
        print "<div class='label'>Part Life</div>\n";
        print "<div class='input'>\n";
        print "Specify how life span of part is measured: ".input_select('lifetype',$lifetypes[$lifetype],$lifetypes);
        print "<br>Life in days (ex: 3 months = 90):<br>\n".input_text('lifeDays',$lifeDays,'10',false,'','','','return isNumberKey(event);');
        print "<br>Life in impressions (ex: every 1,000,000 impressions):<br>\n".input_text('lifeImpressions',$lifeImpressions,'10',false,'','','','return isNumberKey(event);');
        print "</div>\n";
        print "<div class='clear'></div>\n";
        
        make_select('quantityType',$qtypes[$qtype],$qtypes,'Quantity Type','How is this part inventories (ex: unit, gallon, inches)');
        make_number('reorderQuantity',$reorderQuantity,'Reorder Quantity','At what level should the system alert you to reorder?');
        make_number('inventoryQuantity',$inventoryQuantity,'Inventory Quantity','How many are currently in inventory?');
        make_select('partgl',$gls[$gl],$gls,'GL Number','What GL should this part charge to?');
        make_textarea('notes',$notes,'Notes','',70,20); 
        make_file('image','Part Image','',$image);
        make_submit('submit',$button);
        make_hidden('componentid',$componentid);
        make_hidden('partid',$partid);
        make_hidden('type',$type);
        make_hidden('parentid',$parentid);
        make_hidden('equipmentid',$equipmentid);
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=intval($_GET['partid']);
        $sql="DELETE FROM equipment_part WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting this part.<br />'.$error,'error');
        } else {
            $sql="DELETE FROM equpiment_part_xref WHERE part_id=$id";
            $dbDelete=dbexecutequery($sql);
            $sql="DELETE FROM equpiment_part_vendor WHERE part_id=$id";
            $dbDelete=dbexecutequery($sql);
            setUserMessage('The part has been successfully deleted.','success');
        }
        redirect("?action=list&componentid=$componentid&equipmentid=$equipmentid&type=$type");
    } else {
        $parts=array();
        $parts[0]='Select existing part';
        $psql="SELECT * FROM equipment_part";
        $dbExisting=dbselectmulti($psql);
        if($dbExisting['numrows']>0)
        {
            foreach($dbExisting['data'] as $existing)
            {
                $parts[$existing['id']]=stripslashes($existing['part_name']);
            }
        }
        $addpartform="<br><form method=post>Add an existing part to this component<br />";
        $addpartform.=input_select('partid',$parts[0],$parts);
        $addpartform.="<br>";
        $addpartform.="<input type='hidden' name='type' value='$type'>";
        $addpartform.="<input type='hidden' name='componentid' value='$componentid'>";
        $addpartform.="<input type='hidden' name='equipmentid' value='$equipmentid'>";
        $addpartform.="<input type='hidden' name='parentid' value='$parentid'>";
        $addpartform.="<input type='submit' name='submit' value='Add Part' />";
        $addpartform.="</form>";
        if(isset($_GET['type']) && $_GET['type']!='')
        {
            //and look for any parts that have been cross-referenced to this component
            $sql="SELECT A.id, A.part_name, B.id as xrefid FROM equipment_part A, equipment_part_xref B WHERE A.id=B.part_id AND B.equipment_id=$equipmentid AND B.component_id=$componentid AND B.equipment_type='$type' ORDER BY A.part_name";
            $dbExisting=dbselectmulti($sql);
            if($dbExisting['numrows']>0)
            {
                foreach($dbExisting['data'] as $record)
                {
                    $ids.=$record['id'].',';
                }
                $dbAllParts=$dbExisting;
                $ids=substr($ids,0,strlen($ids));
                if(trim($ids)!=''){
                    $ids="AND A.id IN($ids)";
                }
                $sql="SELECT A.id, A.part_name FROM equipment_part A WHERE A.equipment_id=$equipmentid $ids AND A.component_id=$componentid AND A.equipment_type='$type' ORDER BY A.part_name";
                $dbParts=dbselectmulti($sql);
                if($dbParts['numrows']>0)
                {
                    foreach($dbParts['data'] as $p)
                    {
                        $dbAllParts['data'][]=$p;
                    }
                }
            } else
            {
                $sql="SELECT A.id, A.part_name FROM equipment_part A WHERE A.equipment_id=$equipmentid AND A.component_id=$componentid AND A.equipment_type='$type' ORDER BY A.part_name";
                $dbAllParts=dbselectmulti($sql);
            }
            tableStart("<a href='?action=add&componentid=$componentid&equipmentid=$equipmentid&type=$type&parentid=$parentid'>Add new part</a>,<a href='?action=list'>Show all parts</a>,<a href='equipmentPartInventory.php'>Go to parts inventory</a>,<a href='equipmentComponents.php?action=list&equipmentid=$equipmentid&parentid=0&type=$type'>Return to components</a>,<a href='equipment.php'>Return to equipment</a>,$addpartform","Part ID,Part Name",4);
        } else {
            $sql="SELECT A.id, A.part_name FROM equipment_part A ORDER BY A.part_name";
            $dbAllParts=dbselectmulti($sql);
            $addpartform='';
            tableStart("<a href='?action=add'>Add new generic part</a>,<a href='?action=list'>Show all parts</a>,<a href='equipmentPartInventory.php'>Go to parts inventory</a>,,<a href='equipment.php'>Go to equipment</a>,$addpartform","Part ID,Part Name",4);
        }
        if ($dbAllParts['numrows']>0)
        {
            foreach($dbAllParts['data'] as $part)
            {
                $partname=$part['part_name'];
                if ($part['component_name']!='')
                {
                    $componentname=$part['component_name'];
                } else {
                    $componentname='Not specific';    
                }
                if ($part['equipment_name']!='')
                {
                    $equipmentname=$part['equipment_name'];
                } else {
                    $equipmentname='Not specific';    
                }
                $id=$part['id'];
                print "<tr><td>$id</td><td>$partname</td>";
                print "<td><a href='?action=edit&partid=$id&componentid=$componentid&equipmentid=$equipmentid&type=$type'>Edit</a></td>\n";
                if($part['xrefid']!=0)
                {
                    $xrefid=$part['xrefid'];
                    print "<td><a href='?action=remove&xrefid=$xrefid&partid=$id&componentid=$componentid&equipmentid=$equipmentid&type=$type'>Remove Part</a></td>\n";
                } else {
                    print "<td><a href='?action=delete&partid=$id&componentid=$componentid&equipmentid=$equipmentid&type=$type' class='delete'>Delete Part</a></td>\n";
                }
                print "</tr>\n";
            }
        }
        tableEnd($dbAllParts);        
    }
}

function save_part($action)
{
    global $siteID;
    $partid=$_POST['partid'];
    if(strpos($partid,'-')>0)
    {
        $newid=explode('-',$partid);
        $partid=0;
        $tempid=$newid[1];
    }
    $departmentid=$_POST['department'];
    $partgl=$_POST['partgl'];
    $equipmentid=$_POST['equipmentid'];
    $componentid=$_POST['componentid'];
    $type=$_POST['type'];
    $name=addslashes($_POST['partname']);
    $notes=$_POST['notes'];
    $notes=addslashes($notes);
    $lifeDays=addslashes($_POST['lifeDays']);
    $lifeDays=str_replace(" ","",$lifeDays);
    $lifeDays=str_replace("days","",$lifeDays);
    $lifeDays=str_replace("day","",$lifeDays);
    $lifeCount=addslashes($_POST['lifeImpressions']);
    $lifetype=$_POST['lifetype'];
    $quantityType=$_POST['quantityType'];
    $reorderQuantity=addslashes($_POST['reorderQuantity']);
    $inventoryQuantity=addslashes($_POST['inventoryQuantity']);
    if($_POST['taxable']){$taxable=1;}else{$taxable=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO equipment_part (part_taxable, part_name, part_notes, part_life_days, part_life_impressions,
        part_life_type, part_reorder_quantity, part_inventory_quantity, part_quantity_type, site_id, department_id, 
        part_gl, equipment_id, component_id, equipment_type)
         VALUES ('$taxable', '$name', '$notes', '$lifeDays', '$lifeCount', '$lifetype', '$reorderQuantity', 
         '$inventoryQuantity', '$quantityType',  '$siteID', '$departmentid', '$partgl', '$equipmentid', '$componentid', 
         '$type')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $partid=$dbInsert['insertid'];
        $sql="INSERT INTO equipment_part_xref (equipment_id, equipment_type, component_id, part_id, parent_id) 
        VALUES ('$equipmentid', '$type', '$componentid', '$partid', '0')";
        $dbInsert=dbinsertquery($sql);
        
        $nowtime=date("Y-m-d H:i:s");
        
        //as a new part, lets install it on all the appropriate components
        //going to have to loop through every component on the piece of equipment that matches (the location ref)
        //so we will need to look
        
        //lets look up the component and see what kind of item it is.
        $sql="SELECT * FROM equipment_component WHERE id=$componentid";
        $dbComponent=dbselectsingle($sql);
        $componenttype=$dbComponent['data']['component_type'];
        if($componenttype!='generic')
        {
            //ok, we have a non-generic component, so it's possible that it will be installed in multiple instances on the piece of equipment
            //$location="Tower:$towerid|Side:10|Piece:$piece";
            
            switch ($componenttype)
            {
                case "printing":
                    //this is the most complicated version, we need one for each printing couple - 10/13 and each color
                    //get the towers of the press that are of type printing
                    $sql="SELECT * FROM press_towers WHERE press_id=$equipmentid AND tower_type='printing'";
                    $dbTowers=dbselectmulti($sql);
                    if($dbTowers['numrows']>0)
                    {
                        $values="";
                        $sql="INSERT INTO part_instances (part_id, equipment_id, equipment_type, component_id, sub_component_location, install_datetime, install_count, install_by, life_count, life_time, replaced, cur_time, cur_count) VALUES ";
                        foreach($dbTowers['data'] as $tower)
                        {
                            $towerid=$tower['id'];
                            $colors=explode("/",$tower['color_config']);
                            foreach($color as $key=>$color)
                            {
                                $values.="('$partid', '$equipmentid', '$type', '$componentid', 'Tower:$towerid|Side:10|Piece:$color', '$nowtime', '1', '0', '$lifeCount', '$lifeDays', 0, 0, 0),"; 
                                $values.="('$partid', '$equipmentid', '$type', '$componentid', 'Tower:$towerid|Side:13|Piece:$color', '$nowtime', '1', '0', '$lifeCount', '$lifeDays', 0, 0, 0),"; 
                            }
                        }
                        $values=substr($values,0,strlen($values)-1);
                        $dbInsert=dbinsertquery($sql.$values); //add all the parts
                    }
                break;
                
                case "former":
                    $sql="SELECT * FROM press_towers WHERE press_id=$equipmentid AND tower_type='folder'";
                    $dbTowers=dbselectmulti($sql);
                    if($dbTowers['numrows']>0)
                    {
                        $values="";
                        $sql="INSERT INTO part_instances (part_id, equipment_id, equipment_type, component_id, sub_component_location, install_datetime, install_count, install_by, life_count, life_time, replaced, cur_time, cur_count) VALUES ";
                        foreach($dbTowers['data'] as $tower)
                        {
                            $towerid=$tower['id'];
                            $formers=$tower['folder_config'];
                            for($fcount=1;$fcount<=$formers;$fcount++)
                            {
                                $values.="('$partid', '$equipmentid', '$type', '$componentid', 'Tower:$towerid|Former:$fcount', '$nowtime', '1', '0', '$lifeCount', '$lifeDays', 0, 0, 0),";
                            }
                        }
                        $values=substr($values,0,strlen($values)-1);
                        $dbInsert=dbinsertquery($sql.$values); //add all the parts
                    }
                break;
                
                case "ribbon deck":
                    $sql="SELECT * FROM press_towers WHERE press_id=$equipmentid AND tower_type='ribbon deck'";
                    $dbTowers=dbselectmulti($sql);
                    if($dbTowers['numrows']>0)
                    {
                        $values="";
                        $sql="INSERT INTO part_instances (part_id, equipment_id, equipment_type, component_id, sub_component_location, install_datetime, install_count, install_by, life_count, life_time, replaced, cur_time, cur_count) VALUES ";
                        foreach($dbTowers['data'] as $tower)
                        {
                            $towerid=$tower['id'];
                            $ribbons=$tower['ribbon_config'];
                            for($fcount=1;$fcount<=$ribbons;$fcount++)
                            {
                                $values.="('$partid', '$equipmentid', '$type', '$componentid', 'Tower:$towerid|Ribbon:$fcount', '$nowtime', '1', '0', '$lifeCount', '$lifeDays', 0, 0, 0),";
                            }
                        }
                        $values=substr($values,0,strlen($values)-1);
                        $dbInsert=dbinsertquery($sql.$values); //add all the parts
                    }
                break;
                
                case "folder":
                    $sql="SELECT * FROM press_towers WHERE press_id=$equipmentid AND tower_type='folder'";
                    $dbTowers=dbselectmulti($sql);
                    if($dbTowers['numrows']>0)
                    {
                        $values="";
                        $sql="INSERT INTO part_instances (part_id, equipment_id, equipment_type, component_id, sub_component_location, install_datetime, install_count, install_by, life_count, life_time, replaced, cur_time, cur_count) VALUES ";
                        $values.="('$partid', '$equipmentid', '$type', '$componentid', 'Tower:$towerid', '$nowtime', '1', '0', '$lifeCount', '$lifeDays', 0, 0, 0)";
                        $dbInsert=dbinsertquery($sql.$values); //add all the parts
                    }
                break;
                
                case "console":
                    $sql="SELECT * FROM press WHERE id=$equipmentid";
                    $dbPress=dbselectsingle($sql);
                    if($dbPress['numrows']>0)
                    {
                        $values="";
                        $sql="INSERT INTO part_instances (part_id, equipment_id, equipment_type, component_id, sub_component_location, install_datetime, install_count, install_by, life_count, life_time, replaced, cur_time, cur_count) VALUES ";
                        
                        $pressconsoles=$dbPress['data']['consoles'];
                        for($fcount=1;$fcount<=$pressconsoles;$fcount++)
                        {
                            $values.="('$partid', '$equipmentid', '$type', '$componentid', 'Console: $fcount', '$nowtime', '1', '0', '$lifeCount', '$lifeDays', 0, 0, 0),";
                            
                        }
                        $values=substr($values,0,strlen($values)-1);
                        $dbInsert=dbinsertquery($sql.$values); //add all the parts
                    }
                break;
                
                case "splicer":
                    $sql="SELECT * FROM press_towers WHERE press_id=$equipmentid AND has_splicer=1";
                    $dbTowers=dbselectmulti($sql);
                    if($dbTowers['numrows']>0)
                    {
                        $values="";
                        $sql="INSERT INTO part_instances (part_id, equipment_id, equipment_type, component_id, sub_component_location, install_datetime, install_count, install_by, life_count, life_time, replaced, cur_time, cur_count) VALUES ";
                        foreach($dbTowers['data'] as $tower)
                        {
                            $towerid=$tower['id'];
                            $splicer=$tower['splicer_name'];
                            $values.="('$partid', '$equipmentid', '$type', '$componentid', 'Tower:$towerid|Splicer:$splicer', '$nowtime', '1', '0', '$lifeCount', '$lifeDays', 0, 0, 0),";
                            
                        }
                        $values=substr($values,0,strlen($values)-1);
                        $dbInsert=dbinsertquery($sql.$values); //add all the parts
                    }
                break;
                
                case "inserterpocket":
                    $sql="SELECT * FROM inserters WHERE id=$equipmentid";
                    $dbInserter=dbselectsingle($sql);
                    if($dbInserter['numrows']>0)
                    {
                        $values="";
                        $sql="INSERT INTO part_instances (part_id, equipment_id, equipment_type, component_id, sub_component_id, sub_component_location, install_datetime, install_count, install_by, life_count, life_time, replaced, cur_time, cur_count) VALUES ";
                        
                        $sql="SELECT * FROM inserters_hoppers WHERE inserter_id=$equipmentid";
                        $dbStations=dbselectmulti($sql);
                        if($dbStations['numrows']>0)
                        {
                            foreach($dbStations['data'] as $station)
                            {
                                $values.="('$partid', '$equipmentid', '$type', '$station[id]', '$componentid', 'Hopper: $fcount', '$nowtime', '1', '0', '$lifeCount', '$lifeDays', 0, 0, 0),";
                            
                            }
                        }
                        $values=substr($values,0,strlen($values)-1);
                        if($values!='')
                        {
                            $dbInsert=dbinsertquery($sql.$values); //add all the parts    
                        }
                    }
                break;
                
                case "stitcherpocket":
                    $sql="SELECT * FROM stitchers WHERE id=$equipmentid";
                    $dbStitcher=dbselectsingle($sql);
                    if($dbStitcher['numrows']>0)
                    {
                        $values="";
                        $sql="INSERT INTO part_instances (part_id, equipment_id, equipment_type, component_id, sub_component_id, sub_component_location, install_datetime, install_count, install_by, life_count, life_time, replaced, cur_time, cur_count) VALUES ";
                        
                        $sql="SELECT * FROM stitchers_hoppers WHERE stitcher_id=$equipmentid";
                        $dbStations=dbselectmulti($sql);
                        if($dbStations['numrows']>0)
                        {
                            foreach($dbStations['data'] as $station)
                            {
                                $values.="('$partid', '$equipmentid', '$type', '$station[id]', '$componentid', 'Hopper: $fcount', '$nowtime', '1', '0', '$lifeCount', '$lifeDays', 0, 0, 0),";
                            
                            }
                        }
                        $values=substr($values,0,strlen($values)-1);
                        if($values!='')
                        {
                            $dbInsert=dbinsertquery($sql.$values); //add all the parts    
                        }
                    }
                break;
            }
            
        } else {
            //otherwise, the part is unique to this component, go ahead and install one
            $subcomponentid=0;
            $sql="INSERT INTO part_instances (part_id, equipment_id, equipment_type, component_id, sub_component_id, 
            sub_component_location, install_datetime, install_count, install_by, life_count, life_time, replaced, 
            cur_time, cur_count) VALUES ('$id', '$equipmentid', '$type', '$componentid', '$subcomponentid', '', '$nowtime', '1', '0', '$lifeCount', '$lifeDays', 0, 0, 0)";
            $dbInsert=dbinsertquery($sql);
         
        }
        
        //now, fix any vendor records that might have the temp id
        $sql="UPDATE equipment_part_vendor SET part_id='$partid', newid='' WHERE newid='$tempid'";
        $dbUpdate=dbexecutequery($sql);
        
    } else {
        $sql="UPDATE equipment_part SET part_name='$name', part_notes='$notes', part_life_days='$lifeDays', 
        part_life_impressions='$lifeCount', part_life_type='$lifetype', part_reorder_quantity='$reorderQuantity', 
        part_taxable='$taxable', part_inventory_quantity='$inventoryQuantity', part_quantity_type='$quantityType', 
        department_id='$departmentid', part_gl='$partgl' WHERE id=$partid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
     if(isset($_FILES)) { //means we have browsed for a valid file
        // check to make sure files were uploaded
        foreach($_FILES as $file) {
            switch($file['error']) {
                case 0: // file found
                if($file['name'] != NULL && okFileType($file['type'],'image',$file['name']) != false)  {
                    //get the new name of the file
                    //to do that, we need to push it into the database, and return the last record ID
                    if ($partid!=0) {
                        // process the file
                        $ext=explode(".",$file['name']);
                        $ext=$ext[count($ext)-1];
                        $datesuffix=date("YmdHi");
                        $newname="part$partid_$datesuffix.$ext";
                        if(processFile($file,"artwork/equipmentImages/",$newname) == true) {
                            $sql="UPDATE equipment_part SET part_image='$newname' WHERE id=$partid";
                            $result=dbexecutequery($sql);
                        } else {
                           $error.= 'There was an error processing the image named '.$file['name'];  
                        }
                    } else {
                        $error.= 'There was an error because the main record insertion failed.';
                    }
                }
                break;

                case (1|2):  // upload too large
                $error.= 'file upload is too large for '.$file['name'];
                break;

                case 4:  // no file uploaded
                break;

                case (6|7):  // no temp folder or failed write - server config errors
                $error.= 'internal error - flog the webmaster on '.$file['name'];
                break;
            }
        }
     }
    if ($error!='')
    {
        setUserMessage('There was a problem saving this part.<br />'.$error,'error');
    } else {
        setUserMessage('The part has been successfully saved.','success');
    }
    redirect("?action=list&equipmentid=$equipmentid&parentid=$parentid&componentid=$componentid&type=$type");
    
}



footer();
?>
