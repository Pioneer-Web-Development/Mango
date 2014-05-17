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
        case "Save Type":
        save_paper_type('insert');
        break;
        
        case "Update Type":
        save_paper_type('update');
        break;
        
        case "add":
        setup_paper('add');
        break;
        
        case "edit":
        setup_paper('edit');
        break;
        
        case "error":
        setup_paper('error');
        break;
        
        case "delete":
        setup_paper('delete');
        break;
        
        case "list":
        setup_paper('list');
        break;
        
        default:
        setup_paper('list');
        break;
        
    } 
    
    

function setup_paper($action)
{
    global $sizes,$paperdata;
    $pds=array();
    $i=0;
    foreach($paperdata as $pd)
    {
        $pds[$i]=$pd['name'];
        $i++;   
    }
    if ($action=='add' || $action=='edit' || $action=='error')
    {
        if ($action=='add')
        {
            $button="Save Type";
            $pricePerTon="0.00";
        } elseif ($action=='error')
        {
            $button="Save Type";
            $common_name=$_POST['common_name'];
            $paper_weight=$_POST['paper_weight'];
            $brightness=$_POST['brightness'];
            $billing=$_POST['billing_code'];
            $paperdataid=$_POST['paperdataid'];
            $pricePerTon=$_POST['price'];
            $gradeCode=$_POST['grade_code'];
            
        } else {
            $button="Update Type";
            $paperid=intval($_GET['paperid']);
            $sql="SELECT * FROM paper_types WHERE id=$paperid";
            $dbPaper=dbselectsingle($sql);
            $paper=$dbPaper['data'];
            $common_name=stripslashes($paper['common_name']);
            $paper_weight=stripslashes($paper['paper_weight']);
            $brightness=stripslashes($paper['paper_brightness']);
            $billing=stripslashes($paper['billing_code']);
            $paperdataid=stripslashes($paper['paperdataid']);
            $pricePerTon=stripslashes($paper['price_per_ton']);
            $gradeCode=stripslashes($paper['grade_code']);
            if ($pricePerTon==''){$pricePerTon="0.00";}
        }
        print "<form method=post>\n";
            if ($action=='error')
            {
                print "<p style='color:red;'>There was a problem with the weight or brightness. These need to be set first!</p>\n";
            }
            make_text('common_name',$common_name,'Common Name','Name you want to show in select boxes and reports',30);
            //make_select('size',$sizes[$size],$sizes,'Std page width');
            make_number('paper_weight',$paper_weight,'Paper Weight','Enter in GSM');
            make_select('paperdata',$pds[$paperdataid],$pds,'Paper Type','Type of stock as specified by mill');
            make_number('brightness',$brightness,'Brightness');
            make_number('price',$pricePerTon,'Price per Metric Ton');
            make_text('billingcode',$billing,'Billing Code','Code used in the billing report to tie to paper');
            make_text('gradecode',$gradeCode,'Grade Code','Code used by the vendor to specify type of paper');
            make_hidden('paperid',$paperid);
            make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $paperid=intval($_GET['paperid']);
        $sql="UPDATE paper_types SET status=99 WHERE id=$paperid";
        $dbUpdate=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM paper_types WHERE status=1 ORDER BY common_name";
        $dbPaper=dbselectmulti($sql);
        tableStart("<a href='?menu=$menu&action=add'>Add new paper</a>","Common Name",3);
        if ($dbPaper['numrows']>0)
        {
            foreach($dbPaper['data'] as $paper)
            {
                $name=$paper['common_name'];
                $paperid=$paper['id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&paperid=$paperid'>Edit</a></td>\n";
                print "<td><a href='?action=delete&paperid=$paperid' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbPaper);
        
    }
      
}



function save_paper_type($action)
{
    $paperid=$_POST['paperid'];
    $common_name=addslashes($_POST['common_name']);
    $paper_weight=addslashes($_POST['paper_weight']);
    $brightness=addslashes($_POST['brightness']);
    $billing=addslashes($_POST['billingcode']);
    $paperdataid=addslashes($_POST['paperdata']);
    $size=addslashes($_POST['size']);
    $pricePerTon=addslashes($_POST['price']);
    $gradeCode=addslashes($_POST['gradecode']);
    if ($paper_weight=='' || $brightness=='')
    {
        setup_paper('error');
        exit();
    }
    if ($action=='insert')
    {
        $sql="INSERT INTO paper_types (paperdataid,billing_code, common_name, paper_weight, 
        paper_brightness, status, price_per_ton, grade_code) VALUES ('$paperdataid','$billing', '$common_name', '$paper_weight', 
        '$brightness', 1, '$pricePerTon', '$gradeCode')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        //get old name first so we can update the rolls
        $sql="SELECT common_name, paper_weight, paper_brightness FROM paper_types WHERE id=$paperid";
        $dbOld=dbselectsingle($sql);
        $oldname=$dbOld['data']['common_name'];
        $oldweight=$dbOld['data']['paper_weight'];
        $oldbrightness=$dbOld['data']['paper_brightness'];
        
        $sql="UPDATE paper_types SET price_per_ton='$pricePerTon', paperdataid='$paperdataid', billing_code='$billing', 
        common_name='$common_name', paper_weight='$paper_weight', paper_brightness='$brightness', grade_code='$gradeCode' WHERE id=$paperid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        
        //now update the rolls
        $sql="UPDATE rolls SET common_name='$common_name', paper_weight='$paper_weight', paper_brightness='$brightness' WHERE common_name='$oldname'";
        $dbUpdate=dbexecutequery($sql);
        $error.=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the paper type','error');
    } else {
        setUserMessage('Paper type successfully saved','success');
    }
    redirect("?action=list");    
}

footer();
?>
