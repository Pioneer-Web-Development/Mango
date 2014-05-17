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
        case "Save Monitor":
        save_customer('insert');
        break;
        
        case "Update Monitor":
        save_customer('update');
        break;
        
        case "add":
        manage_customers('add');
        break;
        
        case "edit":
        manage_customers('edit');
        break;
        
        case "delete":
        manage_customers('delete');
        break;
        
        case "list":
        manage_customers('list');
        break;
        
        case "Update Station":
        save_station();
        break;
        
        case "editstation":
        station('edit');
        break;
        
        case "deletestation":
        station('delete');
        break;
        
        case "liststation":
        station('list');
        break;
        
        default:
        manage_customers('list');
        break;
        
    } 

function station($action)
{
    if($action=='edit')
    {
        $id=intval($_GET['id']);
        $sql="SELECT * FROM file_monitors_registered WHERE id=$id";
        $dbStation=dbselectsingle($sql);
        $station=$dbStation['data'];
        print "<form method=post>";
        make_text('name',stripslashes($station['name']),'Name','Descriptive name assigned to this station.',30);
        make_text('ip',$station['monitor_ip'],'IP Address','The ip address of the workstation the monitor is running on.',30,'',true);
        make_checkbox('status',$station['active'],'Active?','Check to make this station active');
        make_text('lastping',date("m/d/Y H:i:s",strtotime($station['last_ping'])),'Last Ping','The time the last communication with this station occurred.',30,'',true);
        make_hidden('id',$id);
        make_submit('submit','Update Station');
        print "</form>\n"; 
    } elseif($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="DELETE FROM file_monitors_registered WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        redirect("?action=liststation");
    }  else {
        $sql="SELECT * FROM file_monitors_registered";
        $dbCustomers=dbselectmulti($sql);
        tableStart("<a href='?action=list'>Return to file inputs</a>","Name,IP Address,Status",5);
        if ($dbCustomers['numrows']>0)
        {
            foreach($dbCustomers['data'] as $customer)
            {
                $id=$customer['id'];
                $ip=$customer['monitor_ip'];
                $name=$customer['name'];
                if($customer['active']==1){$status='Active';}else{$status='Disabled';}
                print "<tr><td>$name</td><td>$ip</td><td>$status</td>";
                print "<td><a href='?action=editstation&id=$id'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=deletestation&id=$id'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbCustomers);
    }
}

function save_station()
{
    $id=$_POST['id'];
    if($_POST['status']){$active=1;}else{$active=0;}
    $name=addslashes($_POST['name']);
    $sql="UPDATE file_monitors_registered SET active=$active, name='$name' WHERE id=$id";
    $dbUpdate=dbexecutequery($sql);
    redirect("?action=liststation");
}

     
function manage_customers($action)
{
    $tables=array('job_pages'=>"Pages",'job_pages_log'=>"Page Log",'job_plates'=>"Plates",'job_plates_log'=>"Plate Log",'job_stats'=>"Job Stats");
    $secpages=array("section"=>"Section first","page"=>"Page first");
    $dateformats=array("mmdd"=>"mmdd","mmddyy"=>"mmddyy","mmddyyyy"=>"mmddyyyy","yyyymmdd"=>"yyyymmdd");
    $colorroutes=array("n/a"=>"N/A","black"=>"B & W","color"=>"Color");
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Monitor";
            $table='job_pages';
            $field='ftp_receive';
            $colorfield='color';
            $secpage='section';
            $dateformat="mmdd";
            $croute='n/a';
        } else {
            $button="Update Monitor";
            $id=$_GET['id'];
            $sql="SELECT * FROM file_monitors WHERE id=$id";
            $dbInput=dbselectsingle($sql);
            $input=$dbInput['data'];
            $file=stripslashes($input['file_status']);
            $display=stripslashes($input['display_status']);
            $delimiter=stripslashes($input['delimiter']);
            $pubpos=stripslashes($input['pub_code_pos']);
            $sectionpos=stripslashes($input['section_code_pos']);
            $productcodepos=stripslashes($input['product_code_pos']);
            $datepos=stripslashes($input['date_pos']);
            $pagepos=stripslashes($input['page_pos']);
            $colorpos=stripslashes($input['color_pos']);
            $table=stripslashes($input['update_table']);
            $field=stripslashes($input['update_field']);
            $colorfield=stripslashes($input['color_field']);
            $secpage=stripslashes($input['section_page']);
            $dateformat=stripslashes($input['date_format']);
            $replacecolor=stripslashes($input['replace_field_with_color']);
            $croute=$input['color_input'];
            $dbFields=dbgetfields($table);
            $fields=array();
            if($dbFields['numrows']>0)
            {
                foreach($dbFields['fields'] as $afield)
                {
                    $fields[$afield['Field']]=$afield['Field'];
                }    
            }
        }
        print "<form method=post>\n";
        make_text('file_status',$file,'File Status', 'The status that is set in the desktop monitoring application',50);
        make_text('display_status',$display,'Display Status', 'The status that is to be tied to the timestamps in the system',50);
        make_select('table',$tables[$table],$tables,'Table', 'Which table should be updated?',50);
        make_select('field',$field,$fields,'Field', 'Which field should be updated?',50);
        print '
            <script type="text/javascript">
            $("#table").selectChain({
                target: $("#field"),
                type: "post",
                url: "includes/ajax_handlers/fetchFileMonitorFields.php",
                data: { ajax: true }
            });
            </script>
            ';
        make_select('croute',$colorroutes[$croute],$colorroutes,'Color Setting','Is this a bw or color route, or independent of color?<br>Setting to BW will change page color value to BW for pages<br>that come in on this route, vice versa for color.');
        make_checkbox('replacecolor',$replacecolor,'Replace Color?','Check to replace the field name with the color of the page/plate');
        
        make_text('delimiter',$delimiter,'Delimiter','What character separates the information in the filename? (Leave blank if none)',5,'',false,'parseFileMonitor()');
        make_text('sample','','Sample Filename','Paste in a sample filename to see how it will look.',50,'',false,'parseFileMonitor()');
        print "<div id='sample_display' style='margin-left:130px;'></div>";
        
        print "<div class='label'>Pub Position</div>";
        print "<div class='input'><small>Enter the position of the pub code (shown in sample), or specify character position (ex: 3-4NOTE! position starts at 0, not 1!)</small><br>";
        print "<input id='pub_pos' name='pub_pos' value='$pubpos' onBlur=\"showFileMonitorPiece(this.id)\" size=10 />\n";
        print " <div id='pub_sample'></div>\n";
        print "</div><div class='clear'></div>\n";
        
        make_select('sectionpage',$secpages[$secpage],$secpages,'Merged Fields','If the section and page information is in the same location in the name, which is first?');
        
        print "<div class='label'>Section Position</div>";
        print "<div class='input'><small>Enter the position of the section code (shown in sample), or specify character position (ex: 3-4 NOTE! position starts at 0, not 1!)</small><br>";
        print "<input id='section_pos' name='section_pos' value='$sectionpos' onBlur=\"showFileMonitorPiece(this.id)\" size=10 />\n";
        print " <div id='section_sample'></div>\n";
        print "</div><div class='clear'></div>\n";
        
        print "<div class='label'>Product Code Position</div>";
        print "<div class='input'><small>Enter the position of the product code (shown in sample), or specify character position (ex: 3-4 NOTE! position starts at 0, not 1!) - if used</small><br>";
        print "<input id='productcode_pos' name='productcode_pos' value='$productcodepos' onBlur=\"showFileMonitorPiece(this.id)\" size=10 />\n";
        print " <div id='productcode_sample'></div>\n";
        print "</div><div class='clear'></div>\n";
        
        print "<div class='label'>Date Position</div>";
        print "<div class='input'><small>Enter the position of the date (shown in sample), or specify character position (ex: 3-4 NOTE! position starts at 0, not 1!)</small><br>";
        print "<input id='date_pos' name='date_pos' value='$datepos' onBlur=\"showFileMonitorPiece(this.id)\" size=10 />\n";
        print " <div id='date_sample'></div>\n";
        print "</div><div class='clear'></div>\n";
        
        make_select('dateformat',$dateformats[$dateformat],$dateformats,'Date Format','Select the format used for dates in the filename.');
        
        print "<div class='label'>Page Position</div>";
        print "<div class='input'><small>Enter the position of the page (shown in sample), or specify character position (ex: 3-4 NOTE! position starts at 0, not 1!)</small><br>";
        print "<input id='page_pos' name='page_pos' value='$pagepos' onBlur=\"showFileMonitorPiece(this.id)\" size=10 />\n";
        print " <div id='page_sample'></div>\n";
        print "</div><div class='clear'></div>\n";
        
        print "<div class='label'>Color Position</div>";
        print "<div class='input'><small>Enter the position of the color designator (shown in sample), or specify character position (ex: 3-4 NOTE! position starts at 0, not 1!)</small><br>";
        print "<input id='color_pos' name='color_pos' value='$colorpos' onBlur=\"showFileMonitorPiece(this.id)\" size=10 />\n";
        print " <div id='color_sample'></div>\n";
        print "</div><div class='clear'></div>\n";
        
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=$_GET['id'];
        $sql="DELETE FROM file_monitors WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the file monitor.','error');
        } else {
            setUserMessage('File monitor successfully deleted','success');
        }
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM file_monitors";
        $dbCustomers=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new file monitor</a>,<a href='?action=liststation'>View Stations</a>","File Status,Display Status",4);
        if ($dbCustomers['numrows']>0)
        {
            foreach($dbCustomers['data'] as $customer)
            {
                $file=$customer['file_status'];
                $display=$customer['display_status'];
                $id=$customer['id'];
                print "<tr><td>$file</td><td>$display</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=delete&id=$id'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbCustomers);
        
    }
       
  
}

function save_customer($action)
{
    $id=$_POST['id'];
    $file=addslashes($_POST['file_status']);
    $display=addslashes($_POST['display_status']);
    $delimiter=addslashes($_POST['delimiter']);
    $pubpos=addslashes($_POST['pub_pos']);
    $sectionpos=addslashes($_POST['section_pos']);
    $productcodepos=addslashes($_POST['productcode_pos']);
    $datepos=addslashes($_POST['date_pos']);
    $pagepos=addslashes($_POST['page_pos']);
    $colorpos=addslashes($_POST['color_pos']);
    $table=addslashes($_POST['table']);
    $field=addslashes($_POST['field']);
    $croute=$_POST['croute'];
    $dateformat=addslashes($_POST['dateformat']);
    $sectionpage=addslashes($_POST['sectionpage']);
    if($_POST['replacecolor']){$replacecolor=1;}else{$replacecolor=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO file_monitors (file_status, display_status, delimiter, pub_code_pos, 
        section_code_pos, product_code_pos, date_pos, page_pos, color_pos, update_table, update_field, section_page, 
        replace_field_with_color, date_format, color_input) VALUES ('$file', '$display', '$delimiter', 
        '$pubpos', '$sectionpos', '$productcodepos', '$datepos', '$pagepos', '$colorpos', '$table', '$field', 
        '$sectionpage', '$replacecolor', '$dateformat', '$croute')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE file_monitors SET file_status='$file', display_status='$display', 
        section_page='$sectionpage', delimiter='$delimiter', pub_code_pos='$pubpos', 
        section_code_pos='$sectionpos', date_pos='$datepos', page_pos='$pagepos', 
        color_pos='$colorpos', update_table='$table', update_field='$field', product_code_pos='$productcodepos',  
        replace_field_with_color='$replacecolor', date_format='$dateformat', color_input='$croute' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the file monitor.<br>'.$error,'error');
    } else {
        setUserMessage('File monitor successfully saved','success');
    }
    redirect("?action=list");
    
}

footer();
?>
