<?php
//<!--VERSION: .9 **||**-->

include("includes/mainmenu.php") ;
    
if ($_POST['submit'])
{
    $action=$_POST['submit'];
} elseif ($_GET['action'])
{
    $action=$_GET['action'];
}

switch ($action)
{
    case "addhopper":
    hoppers('add');
    break;
    
    case "edithopper":
    hoppers('edit');
    break;
    
    case "deletehopper":
    hoppers('delete');
    break;
    
    case "listhoppers":
    hoppers('list');
    break;
    
    case "add":
    inserter('add');
    break;
    
    case "edit":
    inserter('edit');
    break;
    
    case "delete":
    inserter('delete');
    break;
    
    case "Save Inserter":
    save_inserter('insert');
    break;
    
    case "Update Inserter":
    save_inserter('update');
    break;
    
    case "Save Hopper":
    save_hopper('insert');
    break;
    
    case "Update Hopper":
    save_hopper('update');
    break;
    
    default:
    inserter('list');
    break;
    
}

function hoppers($action)
{
    global $inserterHopperTypes;
    $inserterid=intval($_GET['inserterid']);
    $hopperid=intval($_GET['hopperid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Save Hopper';
            $type="normal";
            $number=0;
            $active=1;
            $jacket=0;
        } else {
            $button='Update Hopper';
            $sql="SELECT * FROM inserters_hoppers WHERE id=$hopperid";
            $dbHopper=dbselectsingle($sql);
            $hopper=$dbHopper['data'];
            $number=$hopper['hopper_number'];
            $type=$hopper['hopper_type'];
            $active=$hopper['hopper_active'];
            $jacket=$hopper['jacket_station'];
           
        }
        print "<form method=post>\n";
        make_select('hopperType',$inserterHopperTypes[$type],$inserterHopperTypes,'Type of station');
        make_slider('hopperNumber',$number,'Station #','',0,36);
        make_checkbox('hopperActive',$active,'Is station active','Check to make station active');
        make_checkbox('jacketStation',$jacket,'Is this the jacket?','Check if this is the jacket station');
        make_hidden('hopperid',$hopperid);
        make_hidden('inserterid',$inserterid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $sql="DELETE FROM inserters_hoppers WHERE id=$hopperid";
        $dbDelete=dbexecutequery($sql);
        redirect("?action=listhoppers&inserterid=$inserterid");
    } else {
        $sql="SELECT * FROM inserters_hoppers WHERE inserter_id=$inserterid ORDER BY hopper_number";
        $dbHoppers=dbselectmulti($sql);
        tableStart("<a href='?action=list'>Return to inserter list</a>,<a href='?action=addhopper&inserterid=$inserterid'>Add new station</a>","Station Number,Type,Status",5);
        if ($dbHoppers['numrows']>0)
        {
            foreach ($dbHoppers['data'] as $hopper)
            {
                $hopperid=$hopper['id'];
                $number=$hopper['hopper_number'];
                $type=$inserterHopperTypes[$hopper['hopper_type']];
                if ($hopper['hopper_active']==1){$active="Active";}else{$active="Disabled";}
                print "<tr><td>$number</td>";
                print "<td>$type</td>";
                print "<td>$active</td>";
                print "<td><a href='?action=edithopper&hopperid=$hopperid&inserterid=$inserterid'>Edit</a></td>";
                print "<td><a href='?action=deletehopper&hopperid=$hopperid&inserterid=$inserterid' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbHoppers);
    }
    
}

function inserter($action)
{
    global $insertertypes,$inserterFileFormats;
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Save Inserter';
            $singleout=0;
            $doubleout=0;
            $insertertype='inline';
            $inserterhoppers=0;
            $inserterturn=0;
            $inserterformat=0;
            $candoubleout=0;
            $singleoutspeed=5000;
            $doubleoutspeed=5000;
        } else {
            $button='Update Inserter';
            $inserterid=intval($_GET['inserterid']);
            $sql="SELECT * FROM inserters WHERE id=$inserterid";
            $dbInserter=dbselectsingle($sql);
            $inserter=$dbInserter['data'];
            $name=stripslashes($inserter['inserter_name']);
            $insertertype=$inserter['inserter_type'];
            $inserterhoppers=$inserter['inserter_hoppers'];
            $inserterturn=$inserter['inserter_turn'];
            $inserterformat=$inserter['inserter_file_format'];
            $singleout=$inserter['inserter_single_out'];
            $doubleout=$inserter['inserter_double_out'];
            $candoubleout=$inserter['can_double_out'];
            $sideOneName=$inserter['side_one_name'];
            $sideTwoName=$inserter['side_two_name'];
            $singleoutspeed=$inserter['single_out_speed'];
            $doubleoutspeed=$inserter['double_out_speed'];
        }
        print "<form method=post>\n";
        make_text('inserterName',$name,'Inserter name','',30);
        make_select('inserterType',$insertertypes[$insertertype],$insertertypes,'Type of inserter');
        make_select('inserterFormat',$inserterFileFormats[$inserterformat],$inserterFileFormats,'Type of inserter file format');
        make_number('inserterHoppers',$inserterhoppers,'# of Stations');
        make_checkbox('candoubleout',$candoubleout,'Double out','Check if the inserter can double-out');
        make_number('singlespeed',$singleoutspeed,'Single out speed','How fast on average do you run single out?');
        make_number('doublespeed',$doubleoutspeed,'Double out speed','How fast on average do you run double out?');
        make_text('sideOneName',$sideOneName,'Side One Name','For straight machines, machine name');
        make_text('sideTwoName',$sideTwoName,'Side Two Name','For oval machines, side two name');
        make_text('inserterTurn',$inserterturn,'Turn at station#','For oval types, where does first line end?',5,'',false,'','','','return isNumberKey(event);');
        make_number('singleOut',$singleout,'# of pieces into one jacket');
        make_number('doubleOut',$doubleout,'# of pieces into two jackets');
        make_hidden('inserterid',$inserterid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $inserterid=intval($_GET['inserterid']);
        $sql="DELETE FROM inserters WHERE id=$inserterid";
        $dbDelete=dbexecutequery($sql);
        if ($dbDelete['error']=='')
        {
            $sql="DELETE FROM inserters_hoppers WHERE inserter_id='$inserterid'";
            $dbDelete=dbexecutequery($sql);
        }
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM inserters";
        $dbInserters=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new inserter</a>","Name,# Station",5);
        if ($dbInserters['numrows']>0)
        {
            foreach ($dbInserters['data'] as $inserter)
            {
                $id=$inserter['id'];
                $name=$inserter['inserter_name'];
                $hoppers=$inserter['inserter_hoppers'];
                print "<tr><td>$name</td>";
                print "<td>$hoppers</td>";
                print "<td><a href='?action=listhoppers&inserterid=$id'>Configure Stations</a></td>";
                print "<td><a href='?action=edit&inserterid=$id'>Edit</a></td>";
                print "<td><a href='?action=delete&inserterid=$id' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbInserters);
    }
    
}

function save_hopper($action)
{
    $inserterid=$_POST['inserterid'];
    $hopperid=$_POST['hopperid'];
    $hopperNumber=$_POST['hopperNumber'];
    $hopperType=$_POST['hopperType'];
    if ($_POST['hopperActive']){$active=1;}else{$active=0;}
    if ($_POST['jacketStation']){$jacket=1;}else{$jacket=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO inserters_hoppers (inserter_id, hopper_number, hopper_type, hopper_active, jacket_station) VALUES
        ('$inserterid', '$hopperNumber', '$hopperType', '$active', '$jacket')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE inserters_hoppers SET hopper_number='$hopperNumber', hopper_type='$hopperType', 
        hopper_active='$active', jacket_station='$jacket' WHERE id=$hopperid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        print $error;
    } else {
        redirect("?action=listhoppers&inserterid=$inserterid");
    }                          
    
}


function save_inserter($action)
{
    $siteID=$GLOBALS['siteID'];
    $inserterid=$_POST['inserterid'];
    $name=addslashes($_POST['inserterName']);
    $type=$_POST['inserterType'];
    $format=$_POST['inserterFormat'];
    $heads=$_POST['inserterHoppers'];
    $turn=$_POST['inserterTurn'];
    $single=$_POST['singleOut'];
    $double=$_POST['doubleOut'];
    $singleoutspeed=$_POST['singlespeed'];
    $doubleoutspeed=$_POST['doublespeed'];
    $sideOneName=addslashes($_POST['sideOneName']);
    $sideTwoName=addslashes($_POST['sideTwoName']);
    if ($_POST['candoubleout']){$candoubleout=1;}else{$candoubleout=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO inserters (inserter_name, inserter_type, inserter_format, inserter_hoppers,
        inserter_turn, inserter_single_out, inserter_double_out, can_double_out, side_one_name, side_two_name, 
        site_id, single_out_speed, double_out_speed) VALUES 
        ('$name', '$type', '$format', '$heads', '$turn', '$single', '$double', '$candoubleout', '$sideOneName', 
        '$sideTwoName', $siteID, '$singleoutspeed', '$doubleoutspeed')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE inserters SET inserter_name='$name', inserter_type='$type', inserter_format='$format', 
        inserter_hoppers='$heads', inserter_turn='$turn', inserter_single_out='$single', 
        inserter_double_out='$double', can_double_out='$candoubleout', side_one_name='$sideOneName', side_two_name='$sideTwoName'  WHERE id=$inserterid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        print $error;
    } else {
        redirect("?action=list");
    }                          
   
}

footer();
?>