<?php
$reportTitle='Weekly Inserts By Pub';
if ($_POST['output']=='excel')
{
    global $siteID;
    include("includes/functions_db.php");
    $format='excel';
} else {
    include("includes/mainmenu.php");
    $format='html';
}

if($_POST['submit']=='Generate Report')
{
    show_report($format,$reportTitle);
} else {
    show_form();
}

function show_form()
{
    global $pubs;
    $
    $outputs=array("html"=>"Screen","excel"=>"Excel");
    print "<form method=post>\n";
    print "<div class='label'>Report</div>
    <div class='input'>
    This report will allow you to generate a report showing all booked inserts for the specified publication and time frame.
    </div>
    <div class='clear'></div>\n";
    make_select('pub',$pubs[0],$pubs,'Publication');
    make_date('start',date("Y-m-d"));
    make_date('end',date("Y-m-d",strtotime("+1 week")));
    make_select('output',$outputs['html'],$outputs,'Output to');
    make_submit('submit','Generate Report');
    print "</form>\n";
}

function show_report($format,$title)
{
    if($format=='excel')
    {
        $tablestart="<?xml version='1.0'?>
    <?mso-application progid='Excel.Sheet'?>
    <Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet' xmlns:html='http://www.w3.org/TR/REC-html40'>
    <Worksheet ss:Name='".$title."'>
    <Table>";
        $rs="<Row>";
        $re="</Row>";
        $cs="<Cell><Data ss:Type='String'>";
        $ce="</Data></Cell>";
    
    } else {
        $tablestart="<table class='report'>
        <thead>$title</thead>
        <tbody>\n";
        $rs="<tr>";
        $re="</tr>";
        $cs="<td>";
        $ce="</td>";
    }    
}


if($_POST['output']=='excel')
{
    dbclose();
} else {
    footer();
}
?>
