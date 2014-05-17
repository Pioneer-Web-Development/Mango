<?php
//newsprint excel sheet importer
include("includes/functions_db.php");
include("includes/functions_formtools.php");

$sql="SELECT * FROM vendors WHERE newsprint=1 ORDER BY vendor_name";
$dbVendors=dbselectmulti($sql);
$vendors=array();
$vendors[0]="Please select vendor";
foreach($dbVendors['data'] as $vendor)
{
    $vendors[$vendor['id']]=$vendor['vendor_name'];
}

//get paper type
$sql="SELECT * FROM paper_types ORDER BY common_name";
$dbPaper=dbselectmulti($sql);
$papertypes=array();
$papertypes[0]="Please select type";
foreach($dbPaper['data'] as $paper)
{
    $papertypes[$paper['id']]=$paper['common_name'];
}

//sizes
$sql="SELECT * FROM paper_sizes ORDER BY width DESC";
$dbSizes=dbselectmulti($sql);
$sizes=array();
$sizes[0]="Please select size";
foreach($dbSizes['data'] as $size)
{
    $sizes[$size['id']]=$size['width'];
}



if ($_POST)
{
    handle_upload();
} else {
    get_info();
}


function get_info()
{
    //need to specify vendor
    //paper type & size
    global $vendors, $papertypes, $sizes;
    
    $itypes=array('received','consumed');
    
    print "<form action='batchNewsprintImport.php' enctype='multipart/form-data' method='post'>\n";
    print "Vendor: ";
    print input_select('vendor',$vendors[$_GET['vendor']],$vendors);
    print "<br>\n";
    
    print "Type: ";
    print input_select('type','',$papertypes);
    print "<br>\n";
    
    print "Size: ";
    print input_select('size','',$sizes);
    print "<br>\n";
    
    print "Bill of Lading Number: ";
    print input_text('bol','',20);
    print "<br>\n";
    
    print "Import Type: ";
    print input_select('import','receive',$itypes);
    print "<br>\n";
    
    print "File: ";
    print "<input type=file id='paperlog' name='paperlog'>\n";
    print "<br>\n";
    print "<input type='submit' name='submit' value='Load rolls'>\n";
    print "</form>\n";
    
    

}

function handle_upload()
{
    global $vendors, $papertypes, $sizes;
    $size=$_POST['size'];
    $sizename=$sizes[$size];
    $type=$_POST['type'];
    $vendor=$_POST['vendor'];
    $import=$_POST['import'];
    $bol=$_POST['bol'];
    $now=date("Y-m-d H:i");
    //get paper details for roll
    $sql="SELECT * FROM paper_types WHERE id=$type";
    $dbPaper=dbselectsingle($sql);
    $paperinfo=$dbPaper['data'];
    
    
    if ($import==0)
    {
        $sql="INSERT INTO orders (vendor_id, order_datetime, order_status, order_code, status) VALUES ('$vendor', '$now', 0, '$bol', 1)";
        $dbOrder=dbinsertquery($sql);
        $orderid=$dbOrder['numrows'];
        //print "Order creation: $sql<br>";
                
        //create the item id
        $sql="INSERT INTO order_items (order_id, paper_type_id, size_id, itemdisplay_order) VALUES ('$orderid', '$type', '$_POST[size]',1)";
        //print "Order item creation: $sql<br>";
        $dbOrderItem=dbinsertquery($sql);
        $orderitemid=$dbOrderItem['numrows'];
    } else {
        $sql="SELECT A.id as orderid, B.id as itemid FROM orders A, order_items B WHERE A.id=B.order_id AND A.order_code='$bol'";
        //print "Order select: $sql<br>";
                
        $dbOrder=dbselectsingle($sql);
        $order=$dbOrder['data'];
        $orderid=$order['orderid'];
        $orderitemid=$order['itemid'];
    }  
    
    //print_r($_FILES);
    $path=$_FILES['paperlog']['tmp_name'];
    //print "For file got: $path";
    $handle = fopen($path, "r");
    if (filesize($path)>0){
     $contents = fread($handle, filesize($path));
    }
    fclose($handle);
    //print "Got for contents:<br>$contents<br><br>";
    $rolls=explode(",,
",$contents);
    //print_r($rolls);
    foreach($rolls as $roll)
    {
        $roll=explode(',, ',$roll);
        $rolltag=trim($roll[0]);
        $rollweight=$roll[1]*1000;
        if ($import==0 && $rolltag!='')
        {
            //Z10112F,, 0.376 ,, Z10112H,, 0.376 ,, Z10112Z
            
            //adding a roll
            $sql="INSERT INTO rolls (order_id, order_item_id, common_name, roll_width, paper_brightness, paper_weight, status, receive_datetime,
            roll_tag, butt_roll, roll_weight, manifest_number) VALUES ('$orderid', '$orderitemid', '$paperinfo[common_name]', '$sizename',
            '$paperinfo[paper_brightness]', '$paperinfo[paper_weight]', 1, '$now','$rolltag',0,'$rollweight','$bol')";
            //print "Inserting: $sql<br>";
            
            $dbRoll=dbinsertquery($sql);
            if ($dbRoll['error']==''){$rolladded++;}
                
        } else {
            //consuming a roll
            if($rolltag!='')
            {
            $sql="UPDATE rolls SET status=2, batch_date='$now' WHERE roll_tag='$rolltag'";
            //print "Updating: $sql<br>";
            
            $dbRoll=dbexecutequery($sql);
            if ($dbRoll['error']==''){$rollupdated++;}
            }
        }
               
    }
    
    print "Added $rolladded rolls<br>Updated $rollupdated rolls";
    print "<br><a href='batchNewsprintImport.php?vendor=$vendor'>Load another file</a>\n";
}



?>
