<?php
error_reporting (0);
session_start();
include ("includes/functions_db.php");
include ("includes/config.php");
include ("includes/functions_formtools.php");
include ("includes/layoutGenerator.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
<title>Edit press job</title>
<style type="text/css">
body{
    margin:5px;
    font-family:Trebuchet MS;
    font-size:.8em;    
    background-color:#DDDDDD;
}

</style>

<script type="text/javascript" src='includes/scripts/ajax_pageloader.js'></script>
<script type="text/javascript" src='includes/scripts/javascript_toolkit.js'></script>
<script type="text/javascript" src='includes/scripts/calendarDateInput.js'></script>
<script type="text/javascript" src='includes/scripts/tabs/tabcontent.js'></script>
<script type="text/javascript" src='includes/scripts/dhtmlSuite/js/ajax.js'></script>
<script type="text/javascript" src='includes/scripts/dhtmlSuite/js/dhtml-suite-for-applications-without-comments.js'></script>
<link rel="stylesheet" type="text/css" href="styles/pims_main.css"></script>
</head>
<body>
<?php
 $partid=$_GET['partid'];
    $partid=$_GET['partid'];
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
//pull a list of vendors
$sql="SELECT * FROM vendors WHERE newsprint=0 ORDER BY vendor_name";
$dbVendors=dbselectmulti($sql);
$vendors=array();
$vendors[0]="Please select vendor";
if ($dbVendors['numrows']>0)
{
    foreach($dbVendors['data'] as $vendor)
    {
        $vendors[$vendor['id']]=$vendor['vendor_name'];
    }
}
$lifetypes=array("impressions"=>"impressions","days"=>"days");
print "<form action='$_SERVER[PHP_SELF]' method=post enctype='multipart/form-data'>\n";
make_text('partname',$partname,'Part Name','Descriptive name of the part',50);
make_select('partvendor',$vendors[$vendorid],$vendors,'Part Vendor','Who supplies this part?');
make_text('partnumber',$partnumber,'Part Number','The part number for vendor referencing',10);
make_number('partcost',$partcost,'Part Cost','Unit cost for part');
make_checkbox('taxable',$taxable,'Taxable',' check if this part is taxable');
print "<div class='label'>Part Life</div>\n";
print "<div class='input'>\n";
print "Specify how life span of part is measured: ".input_select('lifetype',$lifetypes[$lifetype],$lifetypes);
print "<br>Life in days (ex: 3 months = 90):<br>\n".input_text('lifeDays',$lifeDays,'10',false,'','','','return isNumberKey(event);');
print "<br>Life in impressions (ex: every 1,000,000 impressions):<br>\n".input_text('lifeImpressions',$lifeImpressions,'10',false,'','','','return isNumberKey(event);');
print "</div>\n";
print "<div class='clear'></div>\n";

make_number('reorderQuantity',$reorderQuantity,'Reorder Quantity','At what level should the system alert you to reorder?');
make_number('inventoryQuantity',$inventoryQuantity,'Inventory Quantity','How many are currently in inventory?');
make_textarea('notes',$notes,'Notes','',50,5);
print "<div class='label'></div><div class='input'><input type='button' class='submit' value='Close' onlick='javascript:self.close();'></div>\n";
dbclose();       
?>
</body>
</html>