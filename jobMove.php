<?php

include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}

switch ($action)
{
    case "Move Jobs":
    move_jobs();
    break;
    
    case "Confirm":
    confirm_move();
    break;
    
    default:
    init_move();
    break;
}

function init_move()
{
    global $pubs;
    $runs=array("Please select a publication");
    print "<form method=post>\n";
    print "<div class='label'>Task</div>\n";
    print "<div class='input'>This process moves jobs from one Pub/Run combo to another.<br>Please select a source and destination for the jobs.</div>\n";
    print "<div class='clear'></div>\n";
    make_select('origin_pub_id',$pubs[0],$pubs,'Source Publication','','',false);
        
    print "<div class='label'>Origin Run</div>\n";
    print "<div class='input'>\n";
    print input_select('origin_run_id',$runs[0],$runs);
    print '
        <script type="text/javascript">
        $("#origin_pub_id").selectChain({
            target: $("#origin_run_id"),
            type: "post",
            url: "includes/ajax_handlers/fetchRuns.php",
            data: { ajax: true}
        });
        </script>
        ';
    print "</div>\n";
    print "<div class='clear'></div>\n";
    
    make_select('dest_pub_id',$pubs[0],$pubs,'Desination Publication','','',false);
    print "<div class='label'>Destination Run</div>\n";
    print "<div class='input'>\n";
    print input_select('dest_run_id',$runs[0],$runs);
    print '
        <script type="text/javascript">
        $("#dest_pub_id").selectChain({
            target: $("#dest_run_id"),
            type: "post",
            url: "includes/ajax_handlers/fetchRuns.php",
            data: { ajax: true }
        });
        </script>
        ';
    print "</div>\n";
    print "<div class='clear'></div>\n";
    make_submit('submit',"Move Jobs");   
    print "</form>\n";    
}

function move_jobs()
{
   $originPubID=$_POST['origin_pub_id']; 
   $originRunID=$_POST['origin_run_id']; 
   $destPubID=$_POST['dest_pub_id']; 
   $destRunID=$_POST['dest_run_id'];
   
   $sql="SELECT * FROM jobs WHERE pub_id=$originPubID AND run_id=$originRunID";
   $dbJobs=dbselectmulti($sql);
   print "<form method=post>\n";
       print "<div class='label'>Jobs to move</div>\n";
       print "<div class='input'>$dbJobs[numrows] total jobs selected to be moved</div>\n";
       print "<div class='clear'></div>\n";
       
       make_hidden('origin_pub_id',$originPubID);
       make_hidden('origin_run_id',$originRunID);
       make_hidden('dest_pub_id',$destPubID);
       make_hidden('dest_run_id',$destRunID);
       make_submit('submit',"Confirm");
   print "</form>\n"; 
}

function confirm_move()
{
   $originPubID=$_POST['origin_pub_id']; 
   $originRunID=$_POST['origin_run_id']; 
   $destPubID=$_POST['dest_pub_id']; 
   $destRunID=$_POST['dest_run_id'];
   
   $sql="UPDATE jobs SET pub_id=$destPubID, run_id=$destRunID WHERE pub_id=$originPubID AND run_id=$originRunID";
   $dbUpdate=dbexecutequery($sql);
   print "<div class='label'>Result</div>\n";
   print "<div class='input'>A total of $dbUpdate[numrows] jobs were moved.<br><br></div>\n";
   print "<div class='clear'></div>\n"; 
}

footer();
?>  