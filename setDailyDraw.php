<?php
//the purpose of this script it to allow a user to set the draw for all runs for a specified publication and publication date  
include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}

switch($action)
{
    case "set":
    set_draw();
    break;
    
    case "Set Draw":
    save_draw();
    break;
    
    default:
    set_draw();
}

function set_draw()
{
    global $pubs;
    print "<form method=post>\n";
    print "<h2>Global draw set</h2><p>This tool will set the draw for all runs (that have the draw link enabled) for the specified date and publication. Please use with caution!</p>\n";
    make_select('pub',$pubs[0],$pubs,'Publication','Specify publication to set the draw for');
    make_date('pubdate',date("Y-m-d",strtotime("+1 day")),'Pub Date','Publication date to set the draw for');
    make_number('draw','0','Draw','What is the specified draw for all runs?');
    make_submit('submit','Set Draw');
    print "</form>\n";    
}

function save_draw()
{
    global $pubs;
    $date=$_POST['pubdate'];
    $pub=$_POST['pub'];
    $draw=$_POST['draw'];
    if($draw==''){$draw=0;}
    
    
    $sql="UPDATE jobs SET draw='$draw' WHERE pub_id='$pub' AND pub_date='$date' AND run_id IN (SELECT id FROM publications_runs WHERE pub_id='$pub' AND allow_draw_link=1)";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    if($error=='')
    {
        setUserMessage('All jobs for '.$pubs[$pub].' running on '.date("m/d/Y",strtotime($date)).' have been set to a draw of '.$draw,'success');    
    } else {
        setUserMessage('There was a problem setting the draw amount.<br>'.$error,'error');
    }
    redirect("default.php");
}

footer();
?>
