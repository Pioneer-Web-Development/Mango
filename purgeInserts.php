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
    case "Delete":
    delete_inserts();
    break;
    
    default:
    select_inserts();
    break;
}

function select_inserts()
{
    print "<h3>This function will allow you to permanently delete inserts in bulk. Please be sure about this, as this operation is NOT reversible.</h3>";
    print "<p>All inserts preceeding the selected date and for the specified publication will be removed.</p>";
    print "<form method=post>";
    global $pubs;
    make_select('pub',$pubs[0],$pubs,'Publication','Please select the publication for which to remove inserts');
    make_date('date',date("Y-m-d"),'Date','Delete all inserts schedule for before this date');
    make_submit('submit','Delete');
    print "</form>\n";
}

function delete_inserts()
{
    $date=$_POST['date'];
    $pubid=$_POST['pub'];
    
    //first, select the insert id for all inserts_schedule where insert date< date
    $sql="SELECT insert_id FROM inserts_schedule WHERE pub_id='$pubid' AND insert_date<'$date'";
    $dbIDs=dbselectmulti($sql);
    if($dbIDs['numrows']>0)
    {
        //build a list of ids
        foreach($dbIDs['data'] as $id)
        {
            $ids.=$id['insert_id'].",";
        }
        $ids=substr($ids,0,strlen($ids)-1);
        
        //ok, now start deleting
        $sql="DELETE FROM insert_zoning WHERE insert_id IN ($ids)";
        $dbDelete=dbexecutequery($sql);
        $sql="DELETE FROM jobs_packages_inserts WHERE insert_id IN ($ids)";
        $dbDelete=dbexecutequery($sql);
        $sql="DELETE FROM inserts_schedule WHERE insert_id IN ($ids)";
        $dbDelete=dbexecutequery($sql);
        $sql="DELETE FROM inserts WHERE id IN ($ids)";
        $dbDelete=dbexecutequery($sql);
        print "Deleted ".$dbIDs['numrows']." inserts successfully.<br />";
    } else {
        print "There are no inserts scheduled for that publication before $date.<br />";
    }
    print "<br /><br /><a href='?action=again'>Purge for another pub/date combination</a>";
}
footer();
?>