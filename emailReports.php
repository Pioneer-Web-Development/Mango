<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;

    
if ($_POST['submit'])
{
    $action=$_POST['submit'];
} elseif ($_GET['action'])
{
    $action=$_GET['action'];
}


switch ($action)
{
    case "add":
    reports('add');
    break;
    
    case "edit":
    reports('edit');
    break;
    
    case "delete":
    reports('delete');
    break;
    
    case "Add":
    save_report('insert');
    break;
    
    case "Update":
    save_report('update');
    break;
    
    default:
    reports('list');
    break;
}

 
function reports($action)
{
    global $siteID, $pubs;
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add";
            $groupactive=0;
            $pubid=0;
            $runid=0;
        } else {
            $reportid=$_GET['reportid'];
            $sql="SELECT * FROM email_reports WHERE id=$reportid";
            $dbReport=dbselectsingle($sql);
            $report=$dbReport['data'];
            $name=stripslashes($report['report_name']);
            $description=stripslashes($report['report_description']);
            $filename=stripslashes($report['report_filename']);
            $pubid=$report['pub_id'];
            $runid=$report['run_id'];
            $button="Update";
            if($pubid!=0)
            {
                $runSQL="SELECT * FROM publications_runs WHERE pub_id=$pubid ORDER BY run_name";
                $dbRuns=dbselectmulti($runSQL);
                $runs=array();
                $runs[0]="Please choose run";
                if ($dbRuns['numrows']>0)
                {
                    foreach($dbRuns['data'] as $lrun)
                    {
                        $runs[$lrun['id']]=$lrun['run_name'];
                    }
                }        
            } else {
                $runs[0]='All runs';
            }
        }
        print "<form method=post>\n";
        make_text('name',$name,'Report Name','',50);
        print "<div class='label'>Report Filename</div><div class='input'><small></small><br>
        <input type='text' name='filename' id='filename' size=50 onblur='checkForFile(\"filename\",\"exists\",\"email_templates/\");' value='$filename' /><span id='exists' style='font-weight:bold;color:green;margin-left:10px;'></span><br>
        </div><div class='clear'></div>\n";
        make_select('pub_id',$pubs[$pubid],$pubs,'Publication','Leave at default for all publications');
        make_select('run_id',$runs[$runid],$runs,'Run','Leave unchanged for all runs');
        print '
            <script type="text/javascript">
            $("#pub_id").selectChain({
                target: $("#run_id"),
                type: "post",
                url: "includes/ajax_handlers/fetchRuns.php?zero=1",
                data: { ajax: true }
            });
            </script>
            ';
        make_textarea('description',$description,'Description','',70,10);
        make_hidden('reportid',$reportid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $reportid=$_GET['reportid'];
        $sql="DELETE FROM email_reports WHERE id=$reportid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the email report.'.$error,'error');
        } else {
            setUserMessage('Email report was successfully deleted','success');
        }
        redirect("?action=list");
    
    } else {
        $sql="SELECT * FROM email_reports WHERE site_id=$siteID";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new email report</a>","Report Name",3);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $reportid=$group['id'];
                $name=$group['report_name'];
                print "<tr>";
                print "<td>$name</td>";
                print "<td><a href='?action=edit&reportid=$reportid'>Edit</a></td>";
                print "<td><a class='delete' href='?action=delete&reportid=$reportid'>Delete</a></td>";
            }
            
        }
        tableEnd($dbGroups);
        
    }
} 

function save_report($action)
{
    global $siteID;
    $reportid=$_POST['reportid'];
    $name=addslashes($_POST['name']);
    $desc=addslashes($_POST['description']);
    $filename=addslashes($_POST['filename']);
    $pubid=$_POST['pub_id'];
    $runid=$_POST['run_id'];
    if($action=='insert')
    {
        $sql="INSERT INTO email_reports (report_name, report_description,report_filename,site_id, pub_id, run_id) VALUES 
        ('$name', '$desc','$filename', '$siteID', '$pubid', '$runid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE email_reports SET pub_id='$pubid', run_id='$runid', report_filename='$filename', report_name='$name', report_description='$desc' WHERE id=$reportid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the email report','error');
    } else {
        setUserMessage('Email report successfully saved','success');
    }
    redirect("?action=list");
    
}
 
  
footer();
?>