<?php
//core calendar file
include("includes/mainmenu.php") ;

?>
<body>
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

<style type="text/css">
.unscheduledJob {
    cursor:pointer;
    background-color: #ffffcb;
    color: black;
    width:80%;
    margin-left:auto;
    margin-right:auto;
    margin-bottom:4px;
    border: 1px solid black;
}
</style>
<div id='unscheduled' style='margin-left:70px;margin-right:50px;width:95%;'>
    <?php
    for($i=0;$i<=6;$i++)
    {
        //$date=date("Ymd",strtotime($basedate." +$i days"));
        print "<div style='float:left;width:".floor(100/7)."%;margin-right:2px;text-align:center;background-color:#bbb;'>
        <p class='place'><b>Unscheduled Jobs</b></p>
        <div id='usDate_$i' class='unscheduledHolder'>
        </div>
        </div>\n";
    }
    ?>
    <div class='clear'></div>
</div>

<?php
footer();
?>

