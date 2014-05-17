<?php
//core calendar file
include("includes/mainmenu.php") ;

?>
<div id='loading' style='display:none'>loading...</div>
<div id='quickjump' style='z-index:30000;'>
<?php
if($_GET['qdate'])
{
    $qdate=$_GET['qdate'];
} else {
    $qdate=date("Y-m-d",strtotime("+1 week"));
}

$startdate=date("Y-m-d");


while (date("w",strtotime($startdate))!=0)
{
    $startdate=date("Y-m-d",strtotime($startdate."-1 day"));
}
$basedate=$startdate;
$year=date("Y",strtotime($basedate));
$month=date("m",strtotime($basedate));
$date=date("d",strtotime($basedate));

print "Select a week to jump to: <select id='quickdate' onChange='jumpDate(this.value);'>";

for($i=7;$i>0;$i--)
{
    $backdate=date("Y-m-d",strtotime($startdate."-$i weeks"));       
    print "<option id='$backdate' value='$backdate'>$backdate</option>\n";
}
for($i=1;$i<53;$i++)
{
    if($i==1){$selected='selected';}else{$selected='';}
    print "<option id='$startdate' value='$startdate' $selected>$startdate</option>\n";
    $startdate=date("Y-m-d",strtotime($startdate."+1 week"));       
}
print "</select>";
?>
</div>
<div id='calendar'></div>

<?php
footer();
?>

