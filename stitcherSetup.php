<?php
//<!--VERSION: .9 **||**-->

include("includes/mainmenu.php") ;

//define some regularly used arrays
$stitcherHopperTypes=array('cover'=>"Cover feeder",'signature'=>"Signature feeder");    
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
    stitcher('add');
    break;
    
    case "edit":
    stitcher('edit');
    break;
    
    case "delete":
    stitcher('delete');
    break;
    
    case "Save Stitcher":
    save_stitcher('insert');
    break;
    
    case "Update Stitcher":
    save_stitcher('update');
    break;
    
    case "Save Hopper":
    save_hopper('insert');
    break;
    
    case "Update Hopper":
    save_hopper('update');
    break;
    
    default:
    stitcher('list');
    break;
    
}

function stitcher($action)
{
    global $siteID;
    $stitcherid=intval($_GET['stitcherid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Save Stitcher';
            $speed=0;
        } else {
            $button='Update Stitcher';
            $sql="SELECT * FROM stitchers WHERE id=$stitcherid";
            $dbStitcher=dbselectsingle($sql);
            $stitcher=$dbStitcher['data'];
            $name=stripslashes($stitcher['stitcher_name']);
            $speed=$stitcher['stitcher_speed'];
        }
        print "<form method=post>\n";
        make_text('name',$name,'Name','Name of machine',50);
        make_number('speed',$speed,'Speed','Average copies per hour for this machine');
        make_hidden('stitcherid',$stitcherid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $sql="DELETE FROM stitchers WHERE id=$hopperid";
        $dbDelete=dbexecutequery($sql);
        $sql="DELETE FROM stitcher_hoppers WHERE stitcher_id=$hopperid";
        $dbDelete=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM stitchers WHERE site_id=$siteID ORDER BY stitcher_name";
        $dbStitchers=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new stitcher</a>","Stitcher Name",4);
        if ($dbStitchers['numrows']>0)
        {
            foreach ($dbStitchers['data'] as $stitcher)
            {
                $stitcherid=$stitcher['id'];
                $name=$stitcher['stitcher_name'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&stitcherid=$stitcherid'>Edit</a></td>";
                print "<td><a href='?action=listhoppers&stitcherid=$stitcherid'>Hoppers</a></td>";
                print "<td><a href='?action=delete&stitcherid=$stitcherid' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbStitchers);
    }
    
}



function save_stitcher($action)
{
    global $siteID;
    $stitcherid=$_POST['stitcherid'];
    $name=addslashes($_POST['name']);
    $speed=$_POST['speed'];
    if ($action=='insert')
    {
        $sql="INSERT INTO stitchers (stitcher_name, stitcher_speed, site_id) VALUES ('$name', '$speed', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE stitchers SET stitcher_name='$name', stitcher_speed='$speed' WHERE id=$stitcherid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        print $error;
    } else {
        redirect("?action=list&stitcherid=$stitcherid");
    }                          
    
}

function hoppers($action)
{
    global $stitcherHopperTypes;
    $stitcherid=intval($_GET['stitcherid']);
    $hopperid=intval($_GET['hopperid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Save Hopper';
            $type="signature";
            $active=1;
            $number=0;
        } else {
            $button='Update Hopper';
            $sql="SELECT * FROM stitchers_hoppers WHERE id=$hopperid";
            $dbHopper=dbselectsingle($sql);
            $hopper=$dbHopper['data'];
            $number=$hopper['hopper_number'];
            $type=$hopper['hopper_type'];
            $active=$hopper['hopper_active'];
           
        }
        print "<form method=post>\n";
        make_select('hopperType',$stitcherHopperTypes[$type],$stitcherHopperTypes,'Type of hopper');
        make_number('hopperNumber',$number,'Hopper #');
        make_checkbox('hopperActive',$active,'Is hopper active','Check to make hopper active');
        make_hidden('hopperid',$hopperid);
        make_hidden('stitcherid',$stitcherid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif ($action=='delete')
    {
        $sql="DELETE FROM stitchers_hoppers WHERE id=$hopperid";
        $dbDelete=dbexecutequery($sql);
        redirect("?action=listhoppers&stitcherid=$stitcherid");
    } else {
        $sql="SELECT * FROM stitchers_hoppers WHERE stitcher_id=$stitcherid ORDER BY hopper_number";
        $dbHoppers=dbselectmulti($sql);
        tableStart("<a href='?action=list'>Return to stitcher list</a>,<a href='?action=addhopper&stitcherid=$stitcherid'>Add new hopper</a>","Hopper Number,Type,Status",5);
        if ($dbHoppers['numrows']>0)
        {
            foreach ($dbHoppers['data'] as $hopper)
            {
                $hopperid=$hopper['id'];
                $number=$hopper['hopper_number'];
                $type=$stitcherHopperTypes[$hopper['hopper_type']];
                if ($hopper['hopper_active']==1){$active="Active";}else{$active="Disabled";}
                print "<tr><td>$number</td>";
                print "<td>$type</td>";
                print "<td>$active</td>";
                print "<td><a href='?action=edithopper&hopperid=$hopperid&stitcherid=$stitcherid'>Edit</a></td>";
                print "<td><a href='?action=deletehopper&hopperid=$hopperid&stitcherid=$stitcherid' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbHoppers);
    }
    
}
function save_hopper($action)
{
    $stitcherid=$_POST['stitcherid'];
    $hopperid=$_POST['hopperid'];
    $hopperNumber=$_POST['hopperNumber'];
    $hopperType=$_POST['hopperType'];
    if ($_POST['hopperActive']){$active=1;}else{$active=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO stitchers_hoppers (stitcher_id, hopper_number, hopper_type, hopper_active) VALUES
        ('$stitcherid', '$hopperNumber', '$hopperType', '$active')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE stitchers_hoppers SET hopper_number='$hopperNumber', hopper_type='$hopperType', 
        hopper_active='$active' WHERE id=$hopperid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        print $error;
    } else {
        redirect("?action=listhoppers&stitcherid=$stitcherid");
    }                          
    
}


footer();  
?>
