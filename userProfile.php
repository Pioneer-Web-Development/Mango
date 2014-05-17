<?php
//<!--VERSION: .9 **||**-->

include("includes/mainmenu.php") ;

print "<body>\n";
print "<div id='wrapper'>\n";

 //make sure we have a logged in user...
if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}

if ($_POST)
{
    save_profile();
} else {
    show_profile();
}

function show_profile()
{
    global $siteID, $departments, $employeepositions;
    $id=$_SESSION['cmsuser']['userid'];
    $sql="SELECT * FROM users WHERE id=$id";
    $dbresult=dbselectsingle($sql);
    $record=$dbresult['data'];
    $firstname=stripslashes($record['firstname']);
    $lastname=stripslashes($record['lastname']);
    $middlename=stripslashes($record['middlename']);
    $home=stripslashes($record['home']);
    $business=stripslashes($record['business']);
    $cell=stripslashes($record['cell']);
    $fax=stripslashes($record['fax']);
    $extension=stripslashes($record['extension']);
    $email=stripslashes($record['email']);
    $carrier=stripslashes($record['carrier']);
    $mugshot=stripslashes($record['mugshot']);
    $department=stripslashes($record['department_id']);
    $position=stripslashes($record['position_id']);
    $button="Save Profile";
    $carriers=array("verizon"=>"Verizon","tmobile"=>"T-Mobile","sprint"=>"Sprint","att"=>"AT&amp;T","virgin"=>"Virgin Mobile","nextel"=>"Nextel","cingular"=>"Cingular",'cricket'=>"Cricket");
    
    
print "<form method=post enctype='multipart/form-data'>\n";
    
print "<div id='userTabs'>\n";
print "<ul>
        <li><a href='#general'>Profile Details</a></li>
        <li><a href='#alerts'>Alerts & Reports</a></li>
        <li><a href='#dashboard'>Dashboard</a></li>
        ";
print "</ul>\n";
        
print "<div id='general'>\n";
        make_select('department',$departments[$department],$departments,'Department');
        make_select('position',$employeepositions[$position],$employeepositions,'Position');
        make_text('firstname',$firstname,'First Name','',50);
        make_text('middlename',$middlename,'Middle Name','',50);
        make_text('lastname',$lastname,'Last Name','',50,false,false,'','','createUsername();');
        make_file('mugshot','Picture','',$mugshot);
        make_text('email',$email,'Email Address','',40);
        make_text('business',$business,'Office Number','This is the full number, not just the extention.','20');
        make_text('extension',$extension,'Extension Number','This is the internal office extention.','10');
        make_text('home',$home,'Home Number');
        make_text('cell',$cell,'Cell Number');
        make_select('carrier',$carriers[$carrier],$carriers,'Cell Carrier');
print "</div>";

print "<div id='alerts'>\n";       
        print "<div class='label'>Email Reports</div><div class='input'>Please select the daily email reports you would like to receive.<br />\n";
        $sql="SELECT * FROM user_reports WHERE user_id=$id";
        $dbUserReports=dbselectmulti($sql);
        $userrep=array();
        if ($dbUserReports['numrows']>0)
        {
            foreach ($dbUserReports['data'] as $userreport)
            {
                $userrep[]=$userreport['report_id'];    
            }
        }
        $sql="SELECT * FROM email_reports";
        $dbReports=dbselectmulti($sql);
        if ($dbReports['numrows']>0)
        {
            $c=round($dbReports['numrows']/2,0);
            $i=0;
            print "<div style='float:left;width:200px;'>\n";
            foreach($dbReports['data'] as $report)
            {
                if (in_array($report['id'],$userrep)){$checked=1;}else{$checked=0;}
                print input_checkbox('er_'.$report['id'],$checked)." ".$report['report_name'];
                if ($i==$c)
                {
                    print "</div><div style='float:left;width:200px;'>\n";
                    $i=0;
                } else {
                    $i++;
                }   
            }
            print "</div><div class='clear'></div>\n";
        }
        print "</div><div class='clear'></div>\n";
        print "<div class='label'>Text Alerts</div><div class='input'>Please choose which publication/run combos you would like text alerts for.<br />\n";
        print "<div id='textalerts'>\n";
        $sql="SELECT * FROM user_textalerts WHERE user_id=$id";
        $dbAlerts=dbselectmulti($sql);
        global $pubs;
        $runs=array();
        print "<span style='float:left;'>Type ".input_select('alerttype','press',array('press'=>'Press Run','inserter'=>'Inserter Runs'),false,'setAlertType();');
        print " Publication: ".input_select('pub_id',$pubs[0],$pubs);
        print "</span><span id='pressruns' style='float:left;display:block;'> Press Run: ".input_select('pressrun_id',$runs[0],$runs);
        print "</span><span id='insertruns' style='float:left;display:none;'> Inserter Run: ".input_select('insertrun_id',$runs[0],$runs)."</span><span class='clear'></span>\n";
        ?>
            <script type="text/javascript">
            function setAlertType()
            {
                var atype=$('#alerttype').val();
                if(atype=='press')
                {
                   $('#pressruns').css({'display':'block'}); 
                   $('#insertruns').css({'display':'none'}); 
                } else {
                   $('#pressruns').css({'display':'none'}); 
                   $('#insertruns').css({'display':'block'});  
                }
            }
            $("#pub_id").selectChain({
                target: $("#pressrun_id"),
                type: "post",
                url: "includes/ajax_handlers/fetchRuns.php",
                data: { ajax: true, zero:1 }
            });
            $("#pub_id").selectChain({
                target: $("#insertrun_id"),
                type: "post",
                url: "includes/ajax_handlers/fetchInsertRuns.php",
                data: { ajax: true, zero:1 }
            });
            </script>
        <?php
        print "<input type='button' value='Add Alert' onclick='addAlert();' style='height:20px;padding:2px;margin-left:4px;font-size:12px;padding-bottom:4px;'><div class='clear'></div><br />\n";
        print "<div id='alerts'>\n";
        if ($dbAlerts['numrows']>0)
        {
            foreach ($dbAlerts['data'] as $alert)
            {
                print "<div id='alert$alert[id]' span='width:500px;font-size:12px;padding-bottom:2px;border-bottom:thin solid black;margin-bottom:2px;'>$alert[alert_name] <img src='artwork/icons/cancel_48.png' border=0 height=24 onclick='deleteAlert($alert[id]);'></div>\n";       
            }
        }
        print "</div>\n";
        print "</div>\n";
        print "</div><div class='clear'></div>\n";
        
        
        make_hidden('userid',$id);
        make_hidden('ref',$_SERVER['HTTP_REFERER']);
print "</div>\n";
print "<div id='dashboard'>\n";
$sql="SELECT * FROM dashboard_items ORDER BY dashboard_name";
$dbDItems=dbselectmulti($sql);
if($dbDItems['numrows']>0)
{
    foreach($dbDItems['data'] as $item)
    {
        //see if the user has this one selected
        $sql="SELECT * FROM user_dashboard WHERE module_id=$item[id]";
        $dbCheck=dbselectsingle($sql);
        if($dbCheck['numrows']>0)
        {
            $found=1;
        } else {
            $found=0;
        }
        make_checkbox('dashboard_'.$item['id'],$found,stripslashes($item['dashboard_name']),' Check to enable this item');
    }
} else {
    print "<p>Sorry, no dashboard items have been configured yet.</p>";
}
print "</div>\n";
    print "</div>";
    make_submit('submit',$button);
    print "</form>\n";
    print '
<script>
    $(function() {
        $( "#userTabs" ).tabs();
    });
    </script>
';
}


function save_profile()
{
    $userid=$_POST['userid'];
    $username=trim(addslashes($_POST['username']));
    $firstname=addslashes($_POST['firstname']);
    $lastname=addslashes($_POST['lastname']);
    $middlename=addslashes($_POST['middlename']);
    $home=addslashes($_POST['home']);
    $business=addslashes($_POST['business']);
    $cell=addslashes($_POST['cell']);
    $fax=addslashes($_POST['fax']);
    $extension=addslashes($_POST['extension']);
    $email=addslashes($_POST['email']);
    $password=md5($_POST['password']);
    $carrier=$_POST['carrier'];
    $position=$_POST['position'];
    $department=$_POST['department'];
    $cell=str_replace("-","",$cell);
    $cell=str_replace(" ","",$cell);
    $cell=str_replace("(","",$cell);
    $cell=str_replace(")","",$cell);
    $cell=str_replace(".","",$cell);
    if(strlen($cell)<10){$cell=$GLOBALS['newspaperAreaCode'].$cell;}
        
    
    $sql="UPDATE users SET firstname='$firstname', middlename='$middlename', lastname='$lastname', business='$business', home='$home', cell='$cell', position_id='$position', department_id='$department', fax='$fax', email='$email', extension='$extension', carrier='$carrier' WHERE id=$userid";   
    $dbresult=dbexecutequery($sql);
    $error=$dbresult['error'];
    
    //now the email reports
    $values='';
    foreach($_POST as $key=>$value)
    {
        if(substr($key,0,3)=='er_')
        {
            $value=str_replace("er_","",$key);
            $values.="($userid,$value),";
        }   
    }
    $values=substr($values,0,strlen($values)-1);
    //get rid of all previous records
    $sql="DELETE FROM user_reports WHERE user_id=$userid";
    $dbDelete=dbexecutequery($sql);
    //add the new ones
    if ($values!='')
    {
        $sql="INSERT INTO user_reports (user_id,report_id) VALUES $values";
        $dbInsert=dbinsertquery($sql);
    }
    
    //now handle all the dashboard item stuff
    $sql="SELECT id FROM dashboard_items";
    $dbDashboard=dbselectmulti($sql);
    if($dbDashboard['numrows']>0)
    {
        $dashboardids="";
        foreach($dbDashboard['data'] as $d)
        {
            $dashboardids=$d['id'].",";
        }
        //now we have all the ids, we need to remove them as we find them so that in the end we will be able to delete those
        //dashboard items for the user that they have unchecked
        foreach($_POST as $key=>$value)
        {
            if(substr($key,0,10)=='dashboard_')
            {
                $key=str_replace("dashboard_","",$key);
                //see if the user already has this one
                $sql="SELECT * FROM user_dashboard WHERE user_id=$userid AND module_id=$key";
                $dbCheck=dbselectsingle($sql);
                if($dbCheck['numrows']==0)
                {
                    //ok, this is a new one, look up the default column and order for it
                    $sql="SELECT * FROM dashboard_items WHERE id=$key";
                    $dbItem=dbselectsingle($sql);
                    $item=$dbItem['data'];
                    $sql="INSERT INTO user_dashboard (user_id, module_id, module_column, module_order, collapsed) VALUES 
                    ('$userid', '$key', '$item[default_column]', '$item[default_order]', 0)";
                    $dbInsert=dbinsertquery($sql);
                    
                    //now remove it from the list of dashboardids
                    $dashboardids=str_replace("$key,","",$dashboardids);
                }
                    
            }
        }
        
        $dashboardids=substr($dashboardids,0,strlen($dashboardids)-1); //get rid of trailing comma now
        //now, get rid of any user dashboard items that are still in the dashboard ids list
        if($dashboardids!='')
        {
            $sql="DELETE FROM user_dashboard WHERE user_id=$userid AND module_id IN ($dashboardids)";
            $dbDelete=dbexecutequery($sql);
        }
    } else {
        //just skipping in case we don't have any items set up yet, which would cause an error
    }
    
    setUserMessage('Your profile has been updated successfully','success');
    redirect($_POST['ref']);
}
footer();
?>