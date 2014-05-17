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
    //get advertising product types
    $sql="SELECT * FROM adv_products_types ORDER BY name";
    $dbTypes=dbselectmulti($sql);
    $types[0]='Please select';
    if($dbTypes['numrows']>0)
    {
        foreach($dbTypes['data'] as $type)
        {
            $types[$type['id']]=stripslashes($type['name']);
        }
    }
    
    $id=intval($_GET['id']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save";
            $type=1;
            $pmon=1;
            $ptue=1;
            $pwed=1;
            $pthu=1;
            $pfri=1;
            $psat=1;
            $psun=1;
            $ppre=1;
            $rmon=0;
            $rtue=0;
            $rwed=0;
            $rthu=0;
            $rfri=0;
            $rsat=0;
            $rsun=0;
            $rpre=0;
            $umon=0;
            $utue=0;
            $uwed=0;
            $uthu=0;
            $ufri=0;
            $usat=0;
            $usun=0;
            $upre=0;
        } else {
            $button="Update";
            $sql="SELECT * FROM adv_products WHERE id=$id";
            $dbRecord=dbselectsingle($sql);
            $record=$dbRecord['data'];
            $name=stripslashes($record['product_name']);
            $description=stripslashes($record['product_description']);
            $type=stripslashes($record['product_type']);
            $pmon=stripslashes($record['publish_mon']);
            $ptue=stripslashes($record['publish_tue']);
            $pwed=stripslashes($record['publish_wed']);
            $pthu=stripslashes($record['publish_thu']);
            $pfri=stripslashes($record['publish_fri']);
            $psat=stripslashes($record['publish_sat']);
            $psun=stripslashes($record['publish_sun']);
            $ppre=stripslashes($record['publish_pre']);
            $rmon=stripslashes($record['reach_mon']);
            $rtue=stripslashes($record['reach_tue']);
            $rwed=stripslashes($record['reach_wed']);
            $rthu=stripslashes($record['reach_thu']);
            $rfri=stripslashes($record['reach_fri']);
            $rsat=stripslashes($record['reach_sat']);
            $rsun=stripslashes($record['reach_sun']);
            $rpre=stripslashes($record['reach_pre']);
            $umon=stripslashes($record['unique_mon']);
            $utue=stripslashes($record['unique_tue']);
            $uwed=stripslashes($record['unique_wed']);
            $uthu=stripslashes($record['unique_thu']);
            $ufri=stripslashes($record['unique_fri']);
            $usat=stripslashes($record['unique_sat']);
            $usun=stripslashes($record['unique_sun']);
            $upre=stripslashes($record['unique_pre']);
        }
        print "<form method=post>\n";
        make_text('name',$name,'Product Name','Example: Daily News, Weekly Shopper');
        make_select('type',$types[$type],$types,'Product type','Type of product');
        make_textarea('desc',$description,'Description','General description of the product, can be seen in presentations');
        print "<div class='label'>Publication info</div><div class='input'>\n";
        print "<table class='grid'>\n";
        print "<thead><th>Day</th><th>Active</th><th>Reach</th><th>Unique viewers</th></thead>\n";
        print "<tbody>\n";
        print "<tr>\n";
        print "<td>Monday</td><td><label for='pub_mon'><input type='checkbox' name='pub_mon' id='pub_mon'";
        if($pmon){print " checked";}
        print " /></label></td><td><input type='number' value='$rmon' name='reach_mon' id='reach_mon' size=6 /></td>";
        print "<td><input type='number' value='$umon' name='unique_mon' id='unique_mon' size=6 /></td>";
        print "</tr>\n";
        
        print "<tr>\n";
        print "<td>Tuesday</td><td><label for='pub_tue'><input type='checkbox' name='pub_tue' id='pub_tue'";
        if($ptue){print " checked";}
        print " /></label></td><td><input type='number' value='$rtue' name='reach_tue' id='reach_tue' size=6 /></td>";
        print "<td><input type='number' value='$utue' name='unique_tue' id='unique_tue' size=6 /></td>";
        print "</tr>\n";
        
        print "<tr>\n";
        print "<td>Wednesday</td><td><label for='pub_wed'><input type='checkbox' name='pub_wed' id='pub_wed'";
        if($pwed){print " checked";}
        print " /></label></td><td><input type='number' value='$rwed' name='reach_wed' id='reach_wed' size=6 /></td>";
        print "<td><input type='number' value='$uwed' name='unique_wed' id='unique_wed' size=6 /></td>";
        print "</tr>\n";
        
        print "<tr>\n";
        print "<td>Thursday</td><td><label for='pub_thu'><input type='checkbox' name='pub_thu' id='pub_thu'";
        if($pthu){print " checked";}
        print " /></label></td><td><input type='number' value='$rthu' name='reach_thu' id='reach_thu' size=6 /></td>";
        print "<td><input type='number' value='$uthu' name='unique_thu' id='unique_thu' size=6 /></td>";
        print "</tr>\n";
        
        print "<tr>\n";
        print "<td>Friday</td><td><label for='pub_fri'><input type='checkbox' name='pub_fri' id='pub_fri'";
        if($pfri){print " checked";}
        print " /></label></td><td><input type='number' value='$rfri' name='reach_fri' id='reach_fri' size=6 /></td>";
        print "<td><input type='number' value='$ufri' name='unique_fri' id='unique_fri' size=6 /></td>";
        print "</tr>\n";
        
        print "<tr>\n";
        print "<td>Saturday</td><td><label for='pub_sat'><input type='checkbox' name='pub_sat' id='pub_sat'";
        if($psat){print " checked";}
        print " /></label></td><td><input type='number' value='$rsat' name='reach_sat' id='reach_sat' size=6 /></td>";
        print "<td><input type='number' value='$usat' name='unique_sat' id='unique_sat' size=6 /></td>";
        print "</tr>\n";
        
        print "<tr>\n";
        print "<td>Sunday</td><td><label for='pub_sun'><input type='checkbox' name='pub_sun' id='pub_sun'";
        if($psun){print " checked";}
        print " /></label></td><td><input type='number' value='$rsun' name='reach_sun' id='reach_sun' size=6 /></td>";
        print "<td><input type='number' value='$usun' name='unique_sun' id='unique_sun' size=6 /></td>";
        print "</tr>\n";
        
        print "<tr>\n";
        print "<td>Premium</td><td><label for='pub_pre'><input type='checkbox' name='pub_pre' id='pub_pre'";
        if($ppre){print " checked";}
        print " /></label></td><td><input type='number' value='$rpre' name='reach_pre' id='reach_pre' size=6 /></td>";
        print "<td><input type='number' value='$upre' name='unique_pre' id='unique_pre' size=6 /></td>";
        print "</tr>\n";
        print "</tbody>\n";
        print "</table>\n";
        print "</div><div class='clear'></div>\n";
        
        make_submit('submit',$button);
        make_hidden('id',$id);
        print "</form>\n";
    }elseif ($action=='delete')
    {
        $sql="DELETE FROM adv_products WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the product.<br />'.$error,'error');
        } else {
            setUserMessage('The product was successfully deleted','success');
        }
        redirect("?action=list");
    } else {
       global $siteID;
        //show all the pubs
       $sql="SELECT * FROM adv_products ORDER BY product_name";
       $dbRecords=dbselectmulti($sql);
       tableStart("<a href='?action=add'>Add new product</a>","Name",4);
       if ($dbRecords['numrows']>0)
       {
            foreach($dbRecords['data'] as $record)
            {
                $id=$record['id'];
                $name=stripslashes($record['product_name']);
                print "<tr>";
                print "<td>$name</td>";
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
    $desc=addslashes($_POST['desc']);
    $type=addslashes($_POST['type']);
    if($_POST['pub_mon']){$pmon=1;}else{$pmon=0;}
    if($_POST['pub_tue']){$ptue=1;}else{$ptue=0;}
    if($_POST['pub_wed']){$pwed=1;}else{$pwed=0;}
    if($_POST['pub_thu']){$pthu=1;}else{$pthu=0;}
    if($_POST['pub_fri']){$pfri=1;}else{$pfri=0;}
    if($_POST['pub_sat']){$psat=1;}else{$psat=0;}
    if($_POST['pub_sun']){$psun=1;}else{$psun=0;}
    if($_POST['pub_pre']){$ppre=1;}else{$ppre=0;}
    $rmon=$_POST['reach_mon'];
    $rtue=$_POST['reach_tue'];
    $rwed=$_POST['reach_wed'];
    $rthu=$_POST['reach_thu'];
    $rfri=$_POST['reach_fri'];
    $rsat=$_POST['reach_sat'];
    $rsun=$_POST['reach_sun'];
    $rpre=$_POST['reach_pre'];
    
    $umon=$_POST['unique_mon'];
    $utue=$_POST['unique_tue'];
    $uwed=$_POST['unique_wed'];
    $uthu=$_POST['unique_thu'];
    $ufri=$_POST['unique_fri'];
    $usat=$_POST['unique_sat'];
    $usun=$_POST['unique_sun'];
    $upre=$_POST['unique_pre'];
    
    if ($action=='insert')
    {
        $sql="INSERT INTO adv_products (product_name, product_description, product_type, publish_mon, publish_tue, publish_wed, publish_thu, publish_fri, publish_sat, publish_sun, publish_pre, reach_mon, reach_tue, reach_wed, reach_thu, reach_fri, reach_sat, reach_sun, reach_pre, unique_mon, unique_tue, unique_wed, unique_thu, unique_fri, unique_sat, unique_sun, unique_pre)
         VALUES ('$name', '$desc', '$type', '$pmon', '$ptue', '$pwed', '$pthu', '$pfri', '$psat', '$psun', '$ppre', '$rmon', '$rtue', '$rwed', '$rthu', '$rfri', '$rsat', '$rsun', '$rpre',  '$umon', '$utue', '$uwed', '$uthu', '$ufri', '$usat', '$usun', '$upre')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE adv_products_types SET product_name='$name', product_description='$desc', product_type='$type', 
        publish_mon='$pmon', publish_tue='$ptue', publish_wed='$pwed', publish_thu='$pthu', publish_fri='$pfri', 
        publish_sat='$psat', publish_sun='$psun', publish_pre='$ppre', reach_mon='$rmon', reach_tue='$rtue', 
        reach_wed='$rwed', reach_thu='$rthu', reach_fri='$rfri', reach_sat='$rsat', reach_sun='$rsun', reach_pre='$rpre', 
        unique_mon='$umon', unique_tue='$utue', unique_wed='$uwed', unique_thu='$uthu', unique_fri='$ufri', unique_sat='$usat', unique_sun='$usun', unique_pre='$upre'  WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the product.<br />'.$error,'error');
    } else {
        setUserMessage('The product was successfully saved.','success');
    }
    redirect("?action=list");
    
}

footer();
?>

