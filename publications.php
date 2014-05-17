<?php
//<!--VERSION: .9 **||**-->

include("includes/mainmenu.php") ;
include("includes/pbsManifestImport.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}

switch ($action)
{
    case "Save Publication":
    save_pub('insert');
    break;
    
    case "Update Publication":
    save_pub('update');
    break;
    
    case "addpub":
    pubs('add');
    break;
    
    case "editpub":
    pubs('edit');
    break;
    
    case "deletepub":
    deletepub();
    break;
    
    case "Save Run":
    save_run('insert');
    break;
    
    case "Update Run":
    save_run('update');
    break;
    
    case "addrun":
    runs('add');
    break;
    
    case "editrun":
    runs('edit');
    break;
    
    case "deleterun":
    delete_run();
    break;

    case "listruns":
    runs('list');
    break;
    
    case "Save Insert Run":
    save_insertrun('insert');
    break;
    
    case "Update Insert Run":
    save_insertrun('update');
    break;
    
    case "Duplicate Insert Run":
    save_insertrun('duplicate');
    break;
    
    case "addinsertrun":
    insertruns('add');
    break;
    
    case "editinsertrun":
    insertruns('edit');
    break;
    
    case "duplicateinsertrun":
    insertruns('duplicate');
    break;
    
    case "deleteinsertrun":
    insertruns('delete');
    break;

    case "listinsertruns":
    insertruns('list');
    break;
    
    case "Save Zone":
    save_zone('insert');
    break;
    
    case "Update Zone":
    save_zone('update');
    break;
    
    case "addzone":
    zones('add');
    break;
    
    case "editzone":
    zones('edit');
    break;
    
    case "deletezone":
    zones('delete');
    break;

    case "listzones":
    zones('list');
    break;

    case "Save Truck":
    save_truck('insert');
    break;
    
    case "Update Truck":
    save_truck('update');
    break;
    
    case "importtrucks":
        pbsimport();
    break;
    
    case "Load PBS File":
        process_PBSfile();
    break;
    
    case "Load Trucks":
    load_trucks();
    break;
    
    case "addtruck":
    trucks('add');
    break;
    
    case "edittruck":
    trucks('edit');
    break;
    
    case "deletetruck":
    trucks('delete');
    break;

    case "listtrucks":
    trucks('list');
    break;
    
    case "Save Route":
    save_route('insert');
    break;
    
    case "Update Route":
    save_route('update');
    break;
    
    case "addroute":
    routes('add');
    break;
    
    case "editroute":
    routes('edit');
    break;
    
    case "deleteroute":
    routes('delete');
    break;

    case "listroutes":
    routes('list');
    break;

    case "benchmarks":
    benchmarks();
    break;

    case "Save Benchmarks":
    save_benchmarks();
    break;

    case "Delete Run":
    move_run();
    break;

    default:
    pubs('list');
    break;
}

function deletepub()
{
    $pubid=intval($_GET['pubid']);
    $sql="DELETE FROM publications WHERE id=$pubid";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM publications_runs WHERE pub_id=$pubid";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM publications_insertruns WHERE pub_id=$pubid";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM publications_inserttrucks WHERE pub_id=$pubid";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM publications_insertzones WHERE pub_id=$pubid";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM publications_insertroutes WHERE pub_id=$pubid";
    $dbDelete=dbexecutequery($sql);
    if ($error!='')
    {
        setUserMessage('There was a problem deleting the publication.<br />'.$error,'error');
    } else {
        setUserMessage('The publication and all associated items have been successfully deleted.','success');
    }
    redirect("?action=list");
}

function delete_run()
{
    $pubid=intval($_GET['pubid']);
    $runid=intval($_GET['runid']);
    $sql="SELECT * FROM publications_runs WHERE pub_id=$pubid AND id<>$runid AND run_status=1";
    print $sql;
    $dbRuns=dbselectmulti($sql);
    $runs[0]="Dont move, just delete";
    if($dbRuns['numrows']>0)
    {
        foreach($dbRuns['data'] as $run)
        {
            $runs[$run['id']]=stripslashes($run['run_name']);
        }
    }
    //we will present them with a list of other runs to move the jobs that belong to this run to.
    print "<form method=post>\n";
    make_select('newid',$runs[0],$runs,'New run','What run should jobs tied to this run be moved to?');
    make_hidden('pubid',$pubid);
    make_hidden('runid',$runid);
    make_submit('submit',"Delete Run");
    print "</form>\n";
        
    
}

function move_run()
{
    $pubid=$_POST['pubid'];
    $runid=$_POST['runid'];
    $newrunid=$_POST['newid'];
    $sql="UPDATE publications_runs SET run_status=0 WHERE id=$runid";
    $dbDelete=dbexecutequery($sql);
    $error=$dbDelete['error'];
    $sql="UPDATE jobs SET run_id=$newrunid WHERE run_id=$runid";
    $dbMove=dbexecutequery($sql);
    $error.=$dbMove['error'];
    $sql="UPDATE jobs_recurring SET run_id=$newrunid WHERE run_id=$runid";
    $dbMove=dbexecutequery($sql);
    $error.=$dbMove['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem deleting the press run.<br />'.$error,'error');
    } else {
        setUserMessage('The press run has been successfully deleted.','success');
    }
    redirect("?action=listruns&pubid=$pubid"); 
}

function pubs($action)
{
    $sql="SELECT * FROM accounts ORDER BY account_name";
    $dbCustomers=dbselectmulti($sql);
    $customers[0]="Select customer for this publication";
    if($dbCustomers['numrows']>0)
    {
        foreach($dbCustomers['data'] as $customer)
        {
            $customers[$customer['id']]=$customer['account_name'];
        }
    }
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Publication";
            $insertrun=0;
            $sort=1;
            $customerid=0;
            global $prefs;
            $pubcolor=$prefs['default_pub_color'];
        } else {
            $button="Update Publication";
            $pubid=$_GET['pubid'];
            $sql="SELECT * FROM publications WHERE id=$pubid";
            $dbPub=dbselectsingle($sql);
            $pub=$dbPub['data'];
            $pubname=stripslashes($pub['pub_name']);
            $pubcode=stripslashes($pub['pub_code']);
            $altpubcode=stripslashes($pub['alt_pub_code']);
            $circcode=stripslashes($pub['circulation_code']);
            $reversetext=stripslashes($pub['reverse_text']);
            $pubmessage=stripslashes($pub['pub_message']);
            $pubcolor=stripslashes($pub['pub_color']);
            $vdpub=stripslashes($pub['vision_data_pub']);
            $vdedition=stripslashes($pub['vision_data_edition']);
            $insertReceiveEmail=stripslashes($pub['insert_receive_email']);
            $insertrun=$pub['insert_run'];
            $sort=$pub['sort_order'];
            $customerid=$pub['customer_id'];
            
        }
        print "<form method=post>\n";
        make_text('pubname',$pubname,'Publication Name','',50);
        make_text('pubcode',$pubcode,'Publication Code','Default 2-3 character pub code',3);
        make_text('altpubcode',$altpubcode,'Alternate Publication Codes','Alternate publication codes (ex: ET, EB) separated by commas. Be sure these are unique!',50);
        make_select('customer_id',$customers[$customerid],$customers,'Customer','If this publication belongs to an external customer, please select if from the list.');
        make_textarea('pubmessage',$pubmessage,'Pub Message','This message displays on the press monitor window when a job with this publication comes up',60,3,false);
        make_checkbox('insertrun',$insertrun,'Insert run','By default, create an insert run for this publication for each pub date');
        make_text('insert_receive_email',$insertReceiveEmail,'Insert Receive Alert Email','What email address should be alerted when an insert for this publication is received?',50);
        make_text('circcode',$circcode,'Circulation Code','Circulation/Inserter pub code',10);
        make_text('vdpub',$vdpub,'VisionData Pub','Vision Data Pub Code',10);
        make_text('vdedition',$vdedition,'VisionData Edition','Vision Data Edition Code',10);
        make_color('pubcolor',$pubcolor,'Publication color');
        make_checkbox('reversetext',$reversetext,'Reverse','Make text white on this color');
        make_number('sort_order',$sort,'Sort Order','Order to display publications (defaults to alphabetic)');
        make_submit('submit',$button);
        print "<input type='hidden' name='pubid' value='$pubid'>\n";
        print "</form>\n";
        ?>
        <script type="text/javascript">
        $('#pubname').focus();
        </script>
        <?php
            
    } else {
       //show all the pubs
       $sql="SELECT * FROM publications ORDER by sort_order, pub_name";
       $dbPubs=dbselectmulti($sql);
       tableStart("<a href='?action=addpub'>Add new publication</a>","Publication Name",5);
       if ($dbPubs['numrows']>0)
       {
            foreach($dbPubs['data'] as $pub)
            {
                $pubid=$pub['id'];
                $pubname=stripslashes($pub['pub_name']);
                print "<tr><td>$pubname</td>\n";
                print "<td><a href='?action=editpub&pubid=$pubid'>Edit</a></td>\n";
                print "<td><a href='?action=listruns&pubid=$pubid'>Press Runs</a></td>\n";
                print "<td><a href='?action=listinsertruns&pubid=$pubid'>Insert Runs</a></td>\n";
                print "<td><a href='?action=deletepub&pubid=$pubid' class='delete'>Delete</a></td>\n";
                print "</tr>\n";
            }
       }
       tableEnd($dbPubs);
    }


}

function runs($action)
{
    $pubid=$_GET['pubid'];
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Run";
            $runinserts=1;
            $reportable=1;
            $drawlink=0;
        } else {
            $button="Update Run";
            $runid=$_GET['runid'];
            $sql="SELECT * FROM publications_runs WHERE id=$runid";
            $dbRun=dbselectsingle($sql);
            $run=$dbRun['data'];
            $runname=stripslashes($run['run_name']);
            $runinserts=$run['run_inserts'];
            $productcode=stripslashes($run['run_productcode']);
            $runmessage=stripslashes($run['run_message']);
            $reportable=$run['reportable'];
            $drawlink=$run['allow_draw_link'];
        }
        print "<form method=post>\n";
        if ($runname=='Main')
        {
            print "<div class='label'>Run Name</div><div class='input'>Main</div><input type='hidden' name='runname' value='Main' /><div class='clear'></div>\n";    
        } else {
            make_text('runname',$runname,'Run Name');
        }
        make_text('productcode',$productcode,'Product Code','Enter product code if used.');
        make_checkbox('draw_link',$drawlink,'Link draws','Allow linking to master publication draw based on pub date');
        make_checkbox('runinserts',$runinserts,'Run inserts','This run inserts back into the main run on the same pub date');
        make_textarea('runmessage',$runmessage,'Run Message','This message displays on the press monitor window when a job with this run comes up',60,3,false);
        make_checkbox('reportable',$reportable,'In Reports','Show this run in reports');
        print "<fieldset><legend>Page Flow Targets</legend>\n";
        print "<div class='label'>&nbsp;</div>\n";
        print "<div style='float:left;width:80px;margin-left:10px;font-weight:bold;'>";
        print "Schedule leadtime";
        print "</div>\n";
        print "<div style='float:left;width:80px;margin-left:10px;font-weight:bold;'>";
        print "Run Length";
        print "</div>\n";
        print "<div style='float:left;width:80px;margin-left:10px;font-weight:bold;'>";
        print "Last color page";
        print "</div>\n";
        print "<div style='float:left;width:80px;margin-left:10px;font-weight:bold;'>";
        print "Last page";
        print "</div>\n";
        print "<div style='float:left;width:80px;margin-left:10px;font-weight:bold;'>";
        print "Last plate";
        print "</div>\n";
        print "<div style='float:left;width:80px;margin-left:10px;font-weight:bold;'>";
        print "2 Plates Out";
        print "</div>\n";
        print "<div style='float:left;width:80px;margin-left:10px;font-weight:bold;'>";
        print "3 Plates Out";
        print "</div>\n";
        print "<div style='float:left;width:80px;margin-left:10px;font-weight:bold;'>";
        print "4 Plates Out";
        print "</div>\n";
        print "<div style='float:left;width:80px;margin-left:10px;font-weight:bold;'>";
        print "5 Plates Out";
        print "</div>\n";
        print "<div style='float:left;width:80px;margin-left:10px;font-weight:bold;'>";
        print "6 Plates Out";
        print "</div>\n";
        print "<div style='float:left;width:80px;margin-left:10px;font-weight:bold;'>";
        print "Chase Plate";
        print "</div>\n";
        print "<div style='float:left;width:80px;margin-left:10px;font-weight:bold;'>";
        print "Chase Start";
        print "</div>\n";
        print "<div class='clear'></div>\n";
        for($i=1;$i<=7;$i++)
        {
            print "<div class='label'>";
            switch($i)
            {
                case "1":
                print "Monday";
                break;
                
                case "2":
                print "Tuesday";
                break;
                
                case "3":
                print "Wednesday";
                break;
                
                case "4":
                print "Thursday";
                break;
                
                case "5":
                print "Friday";
                break;
                
                case "6":
                print "Saturday";
                break;
                
                case "7":
                print "Sunday";
                break;
                
                
            }
            print "</div>";
            print "<div style='float:left;width:80px;margin-left:10px;'>";
            print "<input type='text' size=4 id='schedulelead_$i' name='schedulelead_$i' value='".$run['schedule_leadtime_'.$i]."' onkeypress='return isNumberKey(event);'> hrs.";
            print "</div>";
            print "<div style='float:left;width:80px;margin-left:10px;'>";
            print "<input type='text' size=4 id='runlength_$i' name='runlength_$i' value='".$run['run_length_'.$i]."' onkeypress='return isNumberKey(event);'> min.";
            print "</div>";
            print "<div style='float:left;width:80px;margin-left:10px;'>";
            print "<input type='text' size=4 id='lastcolor_$i' name='lastcolor_$i' value='".$run['last_colorpage_leadtime_'.$i]."' onkeypress='return isNumberKey(event);'> min.";
            print "</div>";
            print "<div style='float:left;width:80px;margin-left:10px;'>";
            print "<input type='text' size=4 id='lastpage_$i' name='lastpage_$i' value='".$run['last_page_leadtime_'.$i]."' onkeypress='return isNumberKey(event);'> min.";
            print "</div>";
            print "<div style='float:left;width:80px;margin-left:10px;'>";
            print "<input type='text' size=4 id='lastplate_$i' name='lastplate_$i' value='".$run['last_plate_leadtime_'.$i]."' onkeypress='return isNumberKey(event);'> min.";
            print "</div>";
            print "<div style='float:left;width:80px;margin-left:10px;'>";
            print "<input type='text' size=4 id='last2plate_$i' name='last2plate_$i' value='".$run['plates_2_left_leadtime_'.$i]."' onkeypress='return isNumberKey(event);'> min.";
            print "</div>";
            print "<div style='float:left;width:80px;margin-left:10px;'>";
            print "<input type='text' size=4 id='last3plate_$i' name='last3plate_$i' value='".$run['plates_3_left_leadtime_'.$i]."' onkeypress='return isNumberKey(event);'> min.";
            print "</div>";
            print "<div style='float:left;width:80px;margin-left:10px;'>";
            print "<input type='text' size=4 id='last4plate_$i' name='last4plate_$i' value='".$run['plates_4_left_leadtime_'.$i]."' onkeypress='return isNumberKey(event);'> min.";
            print "</div>";
            print "<div style='float:left;width:80px;margin-left:10px;'>";
            print "<input type='text' size=4 id='last5plate_$i' name='last5plate_$i' value='".$run['plates_5_left_leadtime_'.$i]."' onkeypress='return isNumberKey(event);'> min.";
            print "</div>";
            print "<div style='float:left;width:80px;margin-left:10px;'>";
            print "<input type='text' size=4 id='last6plate_$i' name='last6plate_$i' value='".$run['plates_6_left_leadtime_'.$i]."' onkeypress='return isNumberKey(event);'> min.";
            print "</div>";
            print "<div style='float:left;width:80px;margin-left:10px;'>";
            print "<input type='text' size=4 id='chaseplate_$i' name='chaseplate_$i' value='".$run['chase_plate_aftertime_'.$i]."' onkeypress='return isNumberKey(event);'> min.";
            print "</div>";
            print "<div style='float:left;width:80px;margin-left:10px;'>";
            print "<input type='text' size=4 id='chasestart_$i' name='chasestart_$i' value='".$run['chase_start_aftertime_'.$i]."' onkeypress='return isNumberKey(event);'> min.";
            print "</div>";
            print "<div class='clear'></div>\n";
        }
        print "<div class='label'>Explanation</div><div class='input'>";
        print "<b>Schedule leadtime</b>: How many hours before press start should the job be scheduled (layout selected for recurring jobs)?<br>\n";
        print "<b>Last color page</b>: How many minutes before press start should the last color page be released.<br>\n";
        print "<b>Last page</b>: How many minutes before press start should the last page be released<br>\n"; 
        print "<b>Last plate</b>: How many minutes before press start should the last plate be released?<br>\n"; 
        print "<b>2 Plates Out</b>: How many minutes out should the 2nd to last plate be released?<br>\n"; 
        print "<b>3 Plates Out</b>: How many minutes out should the 3rd to last plate be released?<br>\n"; 
        print "<b>4 Plates Out</b>: How many minutes out should the 4th to last plate be released?<br>\n"; 
        print "<b>5 Plates Out</b>: How many minutes out should the 5th to last plate be released?<br>\n"; 
        print "<b>6 Plates Out</b>: How many minutes out should the 6th to last plate be released?<br>\n"; 
        print "<b>Chase Plate</b>: How many minutes after press start should the last chase plate be released?<br>\n";
        print "<b>Chase Start</b>: How many minutes after first press start should the chase start be?<br>\n";
        
        print "</div><div class='clear'></div>\n";
        print "<input type='button' value='Copy values from Monday to all days' onclick='copyPlateTimeTargets();' />\n";
        print "</fieldset>\n";
        make_submit('submit',$button);
        print "<input type='hidden' name='pubid' value='$pubid'>\n";
        print "<input type='hidden' name='runid' value='$runid'>\n";
        print "</form>\n";
        ?>
        <script type="text/javascript">
        $('#productcode').focus();
        </script>
        <?php
    } else {
       //show all the pubs
       $sql="SELECT * FROM publications_runs WHERE run_status=1 AND pub_id=$pubid ORDER BY run_name";
       $dbRuns=dbselectmulti($sql);
       tableStart("<a href='?action=addrun&pubid=$pubid'>Add run</a>,<a href='?action=list'>Return to main</a>",
       "Run Name,Product Code",6);
       if ($dbRuns['numrows']>0)
       {
            foreach($dbRuns['data'] as $run)
            {
                $runid=$run['id'];
                $runname=stripslashes($run['run_name']);
                $productcode=$run['run_productcode'];
                print "<tr><td>$runname</td><td>$productcode</td>\n";
                print "<td><a href='?action=editrun&pubid=$pubid&runid=$runid'>Edit</a></td>\n";
                print "<td><a href='?action=benchmarks&pubid=$pubid&runid=$runid'>Benchmarks</a></td>\n";
                if ($runname=='Main')
                {
                    print "<td>Not delete-able<br />\n";    
                } else {
                    print "<td><a href='?action=deleterun&pubid=$pubid&runid=$runid' class='delete'>Delete</a></td>\n";
                }
                print "</tr>\n";
            }
       }
       tableEnd($dbRuns);
    }


}

function insertruns($action)
{
    global $daysofweek;
    $pubid=$_GET['pubid'];
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Insert Run";
            $rundefault=0;
            $mainrun=0;
            $runtime='23:00';
            $daysprev=1;
            $sundaycount=0;
            $mondaycount=0;
            $tuesdaycount=0;
            $wednesdaycount=0;
            $thursdaycount=0;
            $fridaycount=0;
            $saturdaycount=0;
        
        } else {
            $button="Update Insert Run";
            $runid=$_GET['runid'];
            $sql="SELECT * FROM publications_insertruns WHERE id=$runid";
            $dbRun=dbselectsingle($sql);
            $run=$dbRun['data'];
            $runname=stripslashes($run['run_name']);
            $rundefault=$run['run_default'];
            $rundays=$run['run_days'];
            $mainrun=$run['main_run'];
            $daysprev=$run['days_prev'];
            $runtime=$run['run_time'];
            $sundaycount=$run['sunday_count'];
            $mondaycount=$run['monday_count'];
            $tuesdaycount=$run['tuesday_count'];
            $wednesdaycount=$run['wednesday_count'];
            $thursdaycount=$run['thursday_count'];
            $fridaycount=$run['friday_count'];
            $saturdaycount=$run['saturday_count'];
        
            if ($rundays!='')
            {
                $rundays=explode(",",$rundays);
                foreach ($rundays as $id=>$dayvalue)
                {
                    switch ($dayvalue)
                    {
                        case 0:
                        $sunday="checked";
                        break;
                        
                        case 1:
                        $monday="checked";
                        break;
                        
                        case 2:
                        $tuesday="checked";
                        break;
                        
                        case 3:
                        $wednesday="checked";
                        break;
                        
                        case 4:
                        $thursday="checked";
                        break;
                        
                        case 5:
                        $friday="checked";
                        break;
                        
                        case 6:
                        $saturday="checked";
                        break;
                        
                        
                    }
                }
            }
        }
        print "<form method=post>\n";
        make_text('runname',$runname,'Run Name');
        print "<div class='label'>Valid Publish Days</div><div class='input'>";
        print "<small>These are the publication days that the plan is valid for</small><br />\n";
        print "<table>\n";
        print "<tr><td><input type='checkbox' name='check_sunday' $sunday>Sunday</td><td><input type='text' name='sunday_count' id='sunday_count' value='$sundaycount'  size='10' onkeypress='return isNumberKey(event);'> average draw</td></tr>\n";
        print "<tr><td><input type='checkbox' name='check_monday' $monday>Monday</td><td><input type='text' name='monday_count' id='monday_count' value='$mondaycount'  size='10' onkeypress='return isNumberKey(event);'> average draw</td></tr>\n";
        print "<tr><td><input type='checkbox' name='check_tuesday' $tuesday>Tuesday</td><td><input type='text' name='tuesday_count' id='tuesday_count' value='$tuesdaycount'  size='10' onkeypress='return isNumberKey(event);'> average draw</td></tr>\n";
        print "<tr><td><input type='checkbox' name='check_wednesday' $wednesday>Wednesday</td><td><input type='text' name='wednesday_count' id='wednesday_count' value='$wednesdaycount'  size='10' onkeypress='return isNumberKey(event);'> average draw</td></tr>\n";
        print "<tr><td><input type='checkbox' name='check_thursday' $thursday>Thusday</td><td><input type='text' name='thursday_count' id='thursday_count' value='$thursdaycount'  size='10' onkeypress='return isNumberKey(event);'> average draw</td></tr>\n";
        print "<tr><td><input type='checkbox' name='check_friday' $friday>Friday</td><td><input type='text' name='friday_count' id='friday_count' value='$fridaycount'  size='10' onkeypress='return isNumberKey(event);'> average draw</td></tr>\n";
        print "<tr><td><input type='checkbox' name='check_saturday' $saturday>Saturday</td><td><input type='text' name='saturday_count' id='saturday_count' value='$saturdaycount'  size='10' onkeypress='return isNumberKey(event);'> average draw</td></tr>\n";
        print "</table>\n";
        print "</div><div class='clear'></div>\n";
        make_slider('days_prev',$daysprev,'Days previous','Number of days prior to publication is the run done?',0,7,1);
        make_time('runtime',$runtime,'Default time','Default time that the job will be run');
        make_checkbox('mainrun',$mainrun,'Main Run','Check to make this the main daily run for the above days of the week');
        make_checkbox('rundefault',$rundefault,'Default','Check to make this the default insert run for this publication');
        make_submit('submit',$button);
        print "<input type='hidden' name='pubid' value='$pubid'>\n";
        print "<input type='hidden' name='runid' value='$runid'>\n";
        print "</form>\n";
    }elseif ($action=='duplicate')
    {
        $button="Duplicate Insert Run";
        $runid=$_GET['runid'];
        print "<form method=post>\n";
        make_text('runname',$runname,'Run Name');
        print "<div class='label'>Valid Days</div><div class='input'>";
        print "<small>These are the days that the plan is valid for</small><br />\n";
        print "<input type='checkbox' name='check_sunday'>Sunday<br />\n";
        print "<input type='checkbox' name='check_monday'>Monday<br />\n";
        print "<input type='checkbox' name='check_tuesday'>Tuesday<br />\n";
        print "<input type='checkbox' name='check_wednesday'>Wednesday<br />\n";
        print "<input type='checkbox' name='check_thursday'>Thusday<br />\n";
        print "<input type='checkbox' name='check_friday'>Friday<br />\n";
        print "<input type='checkbox' name='check_saturday'>Saturday<br />\n";
        print "</div><div class='clear'></div>\n";
        make_checkbox('mainrun',$mainrun,'Main Run','Check to make this the main daily run for the above days of the week');
        make_submit('submit',$button);
        print "<input type='hidden' name='pubid' value='$pubid'>\n";
        print "<input type='hidden' name='runid' value='$runid'>\n";
        print "</form>\n";
    }elseif ($action=='delete')
    {
        $sql="DELETE FROM publications_insertruns WHERE id=$_GET[runid]";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error=='')
        {
            $sql="DELETE FROM publications_insertzones WHERE id=$_GET[runid]";
            $dbDelete=dbexecutequery($sql);
            $sql="DELETE FROM publications_inserttrucks WHERE id=$_GET[runid]";
            $dbDelete=dbexecutequery($sql);
            
        }
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the insert run. Zones and trucks were not removed.<br />'.$error,'error');
        } else {
            setUserMessage('The insert run and associated trucks and zones have been successfully deleted.','success');
        }
        redirect("?action=listinsertruns&pubid=$pubid");
        
    } else {
       //show all the pubs
       $sql="SELECT * FROM publications_insertruns WHERE pub_id=$pubid ORDER BY run_name";
       $dbRuns=dbselectmulti($sql);
       tableStart("<a href='?action=addinsertrun&pubid=$pubid'>Add insert run</a>,<a href='?action=list'>Return to main</a>","Run Name,Default",7);
       if ($dbRuns['numrows']>0)
       {
            foreach($dbRuns['data'] as $run)
            {
                $runid=$run['id'];
                $runname=$run['run_name'];
                if ($run['run_default']){$rundefault="Default";}else{$rundefault="";};
                print "<tr><td>$runname</td>\n";
                print "<td>$rundefault</td>\n";
                print "<td><a href='?action=editinsertrun&pubid=$pubid&runid=$runid'>Edit</a></td>\n";
                print "<td><a href='?action=listzones&pubid=$pubid&runid=$runid'>Manage Zones</a></td>\n";
                print "<td><a href='?action=listtrucks&pubid=$pubid&runid=$runid'>Manage Trucks</a></td>\n";
                print "<td><a href='?action=duplicateinsertrun&pubid=$pubid&runid=$runid'>Duplicate to new day(s)</a></td>\n";
                print "<td><a href='?action=deleteinsertrun&pubid=$pubid&runid=$runid' class='delete'>Delete</a></td>\n";
                print "</tr>\n";
            }
       }
       tableEnd($dbRuns);
    }


}

function save_pub($action)
{
    global $siteID;
    $pubid=$_POST['pubid'];
    $pubname=addslashes($_POST['pubname']);
    $pubcode=addslashes($_POST['pubcode']);
    $circcode=addslashes($_POST['circcode']);
    $sorder=addslashes($_POST['sort_order']);
    $pubcolor=addslashes($_POST['pubcolor']);
    $altpubcode=addslashes($_POST['altpubcode']);
    $pubmessage=addslashes($_POST['pubmessage']);
    $vdpub=addslashes($_POST['vdpub']);
    $vdedition=addslashes($_POST['vdedition']);
    $customerid=addslashes($_POST['customer_id']);
    $insertReceiveEmail=addslashes($_POST['insert_receive_email']);
    if($_POST['sort_order']==''){$sorder=1;}
    if($_POST['reversetext']){$reversetext=1;}else{$reversetext=0;}
    if ($_POST['insertrun']){$insertrun=1;}else{$insertrun=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO publications (pub_name, pub_code, pub_color,reverse_text, circulation_code, insert_run, site_id, sort_order, alt_pub_code, 
        pub_message, vision_data_pub, vision_data_edition, customer_id, insert_receive_email)
         VALUES ('$pubname', '$pubcode','$pubcolor','$reversetext', '$circcode', '$insertrun', '$siteID', '$sorder', '$altpubcode', '$pubmessage', 
         '$vdpub', '$vdedition', '$customerid', '$insertReceiveEmail')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $pubid=$dbInsert['numrows'];
        //add a default 'Main' run for pressrun
        $sql="INSERT INTO publications_runs (pub_id, run_name) VALUES ('$pubid', 'Main')";
        $dbInsert=dbinsertquery($sql);
        $runid=$dbInsert['numrows'];
        //add a default 'MAIN' run for inserter run
        $sql="INSERT INTO publications_insertruns (pub_id, run_name) VALUES ('$pubid', 'MAIN')";
        $dbInsert=dbinsertquery($sql);
        $runid=$dbInsert['numrows'];
        //now, set default benchmarks for this default run
        $sql="SELECT * FROM benchmarks ORDER BY benchmark_category, benchmark_name";
        $dbBenchmarks=dbselectmulti($sql);
        if ($dbBenchmarks['numrows']>0)
        {
            foreach($dbBenchmarks['data'] as $benchmark)
            {
                if ($benchmark['benchmark_type']=='time')
                {
                    $value="12:00";
                } else {
                    $value="0";
                }
                $sql="INSERT INTO run_benchmarks (run_id, benchmark_id,sunday, monday, tuesday, wednesday, thursday, friday, saturday)
                 VALUES ($runid,$benchmark[id],'$value','$value','$value','$value','$value','$value','$value')";
                $dbInsert=dbinsertquery($sql);
            }
        }
        //now, add this pub to user_publications where user has "allpubs" enabled
        $sql="SELECT id FROM users WHERE allpubs=1";
        $dbUsers=dbselectmulti($sql);
        if ($dbUsers['numrows']>0)
        {
            foreach($dbUsers['data'] as $user)
            {
                $sql="INSERT INTO user_publications (user_id, pub_id, value) VALUES ($user[id], $pubid, 1)";
                $dbInsert=dbinsertquery($sql);
            }
            
        }
        
        
    } else {
        $sql="UPDATE publications SET sort_order='$sorder', insert_run='$insertrun', circulation_code='$circcode', pub_name='$pubname', 
        alt_pub_code='$altpubcode', pub_code='$pubcode', pub_color='$pubcolor', reverse_text='$reversetext', pub_message='$pubmessage', 
        vision_data_pub='$vdpub', vision_data_edition='$vdedition', customer_id='$customerid', insert_receive_email='$insertReceiveEmail' WHERE id=$pubid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    //no longer have to do this with fullcalendar 1.5.4
    //build_pubCSSFile();
    if ($error!='')
    {
        setUserMessage('There was a problem saving the publication.<br />'.$error,'error');
    } else {
        setUserMessage('The publication has been successfully saved.','success');
    }
    redirect("?action=list");
    
}

function build_pubCSSFile()
{
    // no longer have to do this with fullcalendar 1.5.4!!!!!
    //get rid of the existing one
    $sql="SELECT * FROM publications";
    $dbPubs=dbselectmulti($sql);
    $contents="";
    if($dbPubs['numrows']>0)
    {
        foreach($dbPubs['data'] as $pub)
        {
            $back=$pub['pub_color'];
            if($pub['reverse_text'])
            {
                $color="white";
            } else {
                $color="black";
            }
            if($back==''){$back='#FFFFCC';$color='black';}
            $contents.="
.publications$pub[id],
.fc-agenda .publications$pub[id] .fc-event-time,
.fc-agenda .publications$pub[id] .fc-event-bg,
.fc-agenda .publications$pub[id] .fc-event-title,
.publications$pub[id] a {
      background-color: $back !important;
      border: none;
      color: $color;
      }
.publications$pub[id] a
{
    border: thin solid white !important;
}     
.publications$pub[id] a:hover {
      border: 4px solid #FFFF00 !important;
      }

"; 
   
        }
    }
    
    
    if(is_writable('styles/calendarPub.css'))
    {
       $f = fopen('styles/calendarPub.css','w+');
        if($f)
        {
           if (fwrite($f,$contents,strlen($contents)))
           {
               $stamp=time();
               $sql="UPDATE core_preferences SET currentCalendarPubCSSversion='$stamp'";
               $dbUpdate=dbexecutequery($sql);
               print "Updated the stylesheet<br />";
           } else {
               print "Error writing to the file";
           }
           fclose($f);  
        } else {
            print "Error opening the stylesheet for writing";
        }
    } else {
        print "File is not writable<br />";
    }
    
}

function save_run($action)
{
    global $siteID;
    $pubid=$_POST['pubid'];
    $runid=$_POST['runid'];
    if ($_POST['runinserts']){$runinserts=1;}else{$runinserts=0;}
    if ($_POST['reportable']){$reportable=1;}else{$reportable=0;}
    if ($_POST['draw_link']){$drawlink=1;}else{$drawlink=0;}
    $runname=($_POST['runname']);
    $runname=str_replace('"','in.',$runname);
    $runname=addslashes($runname);
    $runmessage=addslashes($_POST['runmessage']);
    $productcode=addslashes($_POST['productcode']);
    
    
    $lastpage=addslashes($_POST['lastpage']);
    $lastplate=addslashes($_POST['lastplate']);
    $lastcolor=addslashes($_POST['lastcolor']);
    $schedulelead=addslashes($_POST['schedulelead']);
    $plates2left=addslashes($_POST['last2plate']);
    $plates3left=addslashes($_POST['last3plate']);
    $plates4left=addslashes($_POST['last4plate']);
    $plates5left=addslashes($_POST['last5plate']);
    $plates6left=addslashes($_POST['last6plate']);
    $chaseplate=addslashes($_POST['chaseplate']);
    $chasestart=addslashes($_POST['chasestart']);
    
    if ($action=='insert')
    {
        $sql="INSERT INTO publications_runs (pub_id, run_name,run_inserts, run_status, run_message, reportable, 
        run_productcode, allow_draw_link,
        last_page_leadtime_1, last_plate_leadtime_1, last_colorpage_leadtime_1, schedule_leadtime_1, plates_2_left_leadtime_1,
        plates_3_left_leadtime_1,plates_4_left_leadtime_1,plates_5_left_leadtime_1,plates_6_left_leadtime_1,
        chase_plate_aftertime_1, chase_start_aftertime_1, run_length_1,
        last_page_leadtime_2, last_plate_leadtime_2, last_colorpage_leadtime_2, schedule_leadtime_2, plates_2_left_leadtime_2,
        plates_3_left_leadtime_2,plates_4_left_leadtime_2,plates_5_left_leadtime_2,plates_6_left_leadtime_2,
        chase_plate_aftertime_2, chase_start_aftertime_2, run_length_2,
        last_page_leadtime_3, last_plate_leadtime_3, last_colorpage_leadtime_3, schedule_leadtime_3, plates_2_left_leadtime_3,
        plates_3_left_leadtime_3,plates_4_left_leadtime_3,plates_5_left_leadtime_3,plates_6_left_leadtime_3,
        chase_plate_aftertime_3, chase_start_aftertime_3, run_length_3,
        last_page_leadtime_4, last_plate_leadtime_4, last_colorpage_leadtime_4, schedule_leadtime_4, plates_2_left_leadtime_4,
        plates_3_left_leadtime_4,plates_4_left_leadtime_4,plates_5_left_leadtime_4,plates_6_left_leadtime_4,
        chase_plate_aftertime_4, chase_start_aftertime_4, run_length_4,
        last_page_leadtime_5, last_plate_leadtime_5, last_colorpage_leadtime_5, schedule_leadtime_5, plates_2_left_leadtime_5,
        plates_3_left_leadtime_5,plates_4_left_leadtime_5,plates_5_left_leadtime_5,plates_6_left_leadtime_5,
        chase_plate_aftertime_5, chase_start_aftertime_5, run_length_5,
        last_page_leadtime_6, last_plate_leadtime_6, last_colorpage_leadtime_6, schedule_leadtime_6, plates_2_left_leadtime_6,
        plates_3_left_leadtime_6,plates_4_left_leadtime_6,plates_5_left_leadtime_6,plates_6_left_leadtime_6,
        chase_plate_aftertime_6, chase_start_aftertime_6, run_length_6,
        last_page_leadtime_7, last_plate_leadtime_7, last_colorpage_leadtime_7, schedule_leadtime_7, plates_2_left_leadtime_7,
        plates_3_left_leadtime_7,plates_4_left_leadtime_7,plates_5_left_leadtime_7,plates_6_left_leadtime_7,
        chase_plate_aftertime_7, chase_start_aftertime_7, run_length_7
        ) VALUES ('$pubid', '$runname', '$runinserts', 1, '$runmessage', '$reportable', '$productcode', '$drawlink' ";
        for($i=1;$i<=7;$i++)
        {
            if($_POST['lastpage_'.$i]!=''){$sql.="'".addslashes($_POST['lastpage_'.$i])."', ";} else {$sql.="'0', ";}
            if($_POST['lastplate_'.$i]!=''){$sql.="'".addslashes($_POST['lastplate_'.$i])."', ";} else {$sql.="'0', ";}
            if($_POST['lastcolor_'.$i]!=''){$sql.="'".addslashes($_POST['lastcolor_'.$i])."', ";} else {$sql.="'0', ";}
            if($_POST['schedulelead_'.$i]!=''){$sql.="'".addslashes($_POST['schedulelead_'.$i])."', ";} else {$sql.="'0', ";}
            if($_POST['last2plate_'.$i]!=''){$sql.="'".addslashes($_POST['last2plate_'.$i])."', ";} else {$sql.="'0', ";}
            if($_POST['last3plate_'.$i]!=''){$sql.="'".addslashes($_POST['last3plate_'.$i])."', ";} else {$sql.="'0', ";}
            if($_POST['last4plate_'.$i]!=''){$sql.="'".addslashes($_POST['last4plate_'.$i])."', ";} else {$sql.="'0', ";}
            if($_POST['last5plate_'.$i]!=''){$sql.="'".addslashes($_POST['last5plate_'.$i])."', ";} else {$sql.="'0', ";}
            if($_POST['last6plate_'.$i]!=''){$sql.="'".addslashes($_POST['last6plate_'.$i])."', ";} else {$sql.="'0', ";}
            if($_POST['chaseplate_'.$i]!=''){$sql.="'".addslashes($_POST['chaseplate_'.$i])."', ";} else {$sql.="'0', ";}
            if($_POST['chasestart_'.$i]!=''){$sql.="'".addslashes($_POST['chasestart_'.$i])."', ";} else {$sql.="'0', ";}
            if($_POST['runlength_'.$i]!=''){$sql.="'".addslashes($_POST['runlength_'.$i])."', ";} else {$sql.="'0', ";}
            
        }
        $sql=substr($sql,0,strlen($sql)-2);
        $sql.=")";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE publications_runs SET run_inserts='$runinserts', run_name='$runname', pub_id='$pubid',
        run_message='$runmessage', reportable='$reportable', run_productcode='$productcode', allow_draw_link='$drawlink', ";
        for($i=1;$i<=7;$i++)
        {
            if($_POST['lastpage_'.$i]!=''){$sql.="last_page_leadtime_$i='".addslashes($_POST['lastpage_'.$i])."', ";}else{$sql.="last_page_leadtime_$i='0', ";}
            if($_POST['lastplate_'.$i]!=''){$sql.="last_plate_leadtime_$i='".addslashes($_POST['lastpage_'.$i])."', ";}else{$sql.="last_plate_leadtime_$i='0', ";}
            if($_POST['lastcolor_'.$i]!=''){$sql.="last_colorpage_leadtime_$i='".addslashes($_POST['lastcolor_'.$i])."', ";}else{$sql.="last_colorpage_leadtime_$i='0', ";}
            if($_POST['schedulelead_'.$i]!=''){$sql.="schedule_leadtime_$i='".addslashes($_POST['schedulelead_'.$i])."', ";}else{$sql.="schedule_leadtime_$i='0', ";}
            if($_POST['chaseplate_'.$i]!=''){$sql.="last_page_leadtime_$i='".addslashes($_POST['chaseplate_'.$i])."', ";}else{$sql.="last_page_leadtime_$i='0', ";}
            if($_POST['chasestart_'.$i]!=''){$sql.="chase_start_aftertime_$i='".addslashes($_POST['chasestart_'.$i])."', ";}else{$sql.="chase_start_aftertime_$i='0', ";}
            if($_POST['last2plate_'.$i]!=''){$sql.="plates_2_left_leadtime_$i='".addslashes($_POST['last2plate_'.$i])."', ";}else{$sql.="plates_2_left_leadtime_$i='0', ";}
            if($_POST['last3plate_'.$i]!=''){$sql.="plates_3_left_leadtime_$i='".addslashes($_POST['last3plate_'.$i])."', ";}else{$sql.="plates_3_left_leadtime_$i='0', ";}
            if($_POST['last4plate_'.$i]!=''){$sql.="plates_4_left_leadtime_$i='".addslashes($_POST['last4plate_'.$i])."', ";}else{$sql.="plates_4_left_leadtime_$i='0', ";}
            if($_POST['last5plate_'.$i]!=''){$sql.="plates_5_left_leadtime_$i='".addslashes($_POST['last5plate_'.$i])."', ";}else{$sql.="plates_5_left_leadtime_$i='0', ";}
            if($_POST['last6plate_'.$i]!=''){$sql.="plates_6_left_leadtime_$i='".addslashes($_POST['last6plate_'.$i])."', ";}else{$sql.="plates_6_left_leadtime_$i='0', ";}
            if($_POST['runlength_'.$i]!=''){$sql.="run_length_$i='".addslashes($_POST['runlength_'.$i])."', ";}else{$sql.="runlength_='0', ";}
            
        } 
        $sql=substr($sql,0,strlen($sql)-2);
        $sql.=" WHERE id=$runid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the press run.<br />'.$error,'error');
    } else {
        setUserMessage('The press run has been successfully saved.','success');
    }
    redirect("?action=listruns&pubid=$pubid");
    
}


function zones($action)
{
    $pubid=intval($_GET['pubid']);
    $runid=intval($_GET['runid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Zone";
            $sql="SELECT MAX(zone_order) as mo FROM publications_insertzones WHERE run_id='$runid'";
            $dbMax=dbselectsingle($sql);
            $max=$dbMax['data']['mo'];
            $zoneorder=$max+1;
            $zonecount=0;
        } else {
            $button="Update Zone";
            $zoneid=$_GET['zoneid'];
            $sql="SELECT * FROM publications_insertzones WHERE id=$zoneid";
            $dbRun=dbselectsingle($sql);
            $run=$dbRun['data'];
            $zonename=stripslashes($run['zone_name']);
            $zoneorder=stripslashes($run['zone_order']);
            $zonezip=stripslashes($run['zone_zip']);
            $zonecount=stripslashes($run['zone_count']);
        }
        print "<form method=post>\n";
        make_text('zonename',$zonename,'Zone Name');
        make_text('zonezip',$zonezip,'Zone Zip');
        make_number('zoneorder',$zoneorder,'Zone Order');
        make_number('zonecount',$zonecount,'Zone Count','Average number of papers in this zone');
        make_submit('submit',$button);
        print "<input type='hidden' name='zoneid' value='$zoneid'>\n";
        print "<input type='hidden' name='pubid' value='$pubid'>\n";
        print "<input type='hidden' name='runid' value='$runid'>\n";
        print "</form>\n";
        ?>
        <script type="text/javascript">
        $('#zonename').focus();
        </script>
        <?php
    }elseif ($action=='delete')
    {
        $zoneid=intval($_GET['zoneid']);
        $sql="DELETE * FROM publications_insertzones WHERE id=$zoneid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the zone.<br />'.$error,'error');
        } else {
            setUserMessage('The zone has been successfully deleted.','success');
        }
        redirect("?action=listzones&pubid=$pubid&runid=$runid");
    } else {
       //show all the pubs
       $sql="SELECT * FROM publications_insertzones WHERE run_id=$runid ORDER BY zone_order";
       $dbRuns=dbselectmulti($sql);
       tableStart("<a href='?action=addzone&pubid=$pubid&runid=$runid'>Add zone</a>,<a href='?action=listinsertruns&pubid=$pubid'>Return to Insert Runs</a>,<a href='?action=list'>Return to main</a>","Zone Order,Zone Name",4);
       if ($dbRuns['numrows']>0)
       {
            foreach($dbRuns['data'] as $run)
            {
                $zoneid=$run['id'];
                $zonename=$run['zone_name'];
                $zoneorder=$run['zone_order'];
                print "<tr><td>$zoneorder</td>\n";
                print "<td>$zonename</td>\n";
                print "<td><a href='?action=editzone&pubid=$pubid&runid=$runid&zoneid=$zoneid'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=deletezone&pubid=$pubid&runid=$runid&zoneid=$zoneid'>Delete</a></td>\n";
                print "</tr>\n";
            }
       }
       tableEnd($dbRuns);
    }
        
}


function trucks($action)
{
    $pubid=$_GET['pubid'];
    $runid=$_GET['runid'];
    //build a list of zones
    $zones=array();
    $zones[0]='Please choose';
    $sql="SELECT * FROM publications_insertzones WHERE run_id=$runid ORDER BY zone_order";
    $dbZones=dbselectmulti($sql);
    if ($dbZones['numrows']>0)
    {
        foreach ($dbZones['data'] as $record)
        {
            $zones[$record['id']]=$record['zone_name'];
        }
    }
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Truck";
            $truckorder=1;
            $zone=0;
        } else {
            $button="Update Truck";
            $truckid=$_GET['truckid'];
            $sql="SELECT * FROM publications_inserttrucks WHERE id=$truckid";
            $dbTruck=dbselectsingle($sql);
            $truck=$dbTruck['data'];
            $truckname=stripslashes($truck['truck_name']);
            $truckorder=stripslashes($truck['truck_order']);
            $zoneid=stripslashes($truck['zone_id']);
            $notes=stripslashes($truck['truck_notes']);
        }
        print "<form method=post>\n";
        make_text('truckname',$truckname,'Truck Name');
        make_select('zoneid',$zones[$zoneid],$zones,'Zone');
        make_text('truckorder',$truckorder,'Truck Order');
        make_textarea('trucknotes',$notes,'Notes','',80,10,true);
        make_submit('submit',$button);
        print "<input type='hidden' name='truckid' value='$truckid'>\n";
        print "<input type='hidden' name='pubid' value='$pubid'>\n";
        print "<input type='hidden' name='runid' value='$runid'>\n";
        print "</form>\n";
        ?>
        <script type="text/javascript">
        $('#truckname').focus();
        </script>
        <?php
    }elseif ($action=='delete')
    {
        $sql="DELETE FROM publications_inserttrucks WHERE id=$_GET[truckid]";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        $sql="DELETE FROM publications_insertroutes WHERE truck_id=$_GET[truckid]&pubid=$_GET[pubid]";
        $dbDelete=dbexecutequery($sql);
        $error.=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the route.<br />'.$error,'error');
        } else {
            setUserMessage('The route has been successfully deleted.','success');
        }
        redirect("?action=listtrucks&pubid=$pubid&runid=$runid");
    } else {
       //show all the pubs
       $sql="SELECT * FROM publications_inserttrucks WHERE run_id=$runid ORDER BY truck_order";
       $dbTrucks=dbselectmulti($sql);
       tableStart("<a href='?action=addtruck&pubid=$pubid&runid=$runid'>Add truck</a>,<a href='?action=importtrucks&pubid=$pubid&runid=$runid'>Import Trucks from PBS</a>,<a href='?action=list'>Return to main</a>","Truck Order,Truck Name,Zone Name",6);
       if ($dbTrucks['numrows']>0)
       {
            foreach($dbTrucks['data'] as $truck)
            {
                $zoneid=$truck['id'];
                $zonename=$zones[$truck['zone_id']];
                $truckorder=$truck['truck_order'];
                $truckname=$truck['truck_name'];
                print "<tr><td>$truckorder</td>\n";
                print "<td>$truckname</td>\n";
                print "<td>$zonename</td>\n";
                print "<td><a href='?action=listroutes&pubid=$pubid&runid=$runid&truckid=$zoneid'>Routes</a></td>\n";
                print "<td><a href='?action=edittruck&pubid=$pubid&runid=$runid&truckid=$zoneid'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=deletetruck&pubid=$pubid&runid=$runid&truckid=$zoneid'>Delete</a></td>\n";
                print "</tr>\n";
            }
       }
       tableEnd($dbTrucks);
    }


}



function save_insertrun($action)
{
    global $daysofweek, $siteID;
    $daysprev=$_POST['days_prev'];
    $runtime=$_POST['runtime'];
    $pubid=$_POST['pubid'];
    $runid=$_POST['runid'];
    $runname=addslashes($_POST['runname']);
    if ($_POST['rundefault']){$default=1;}else{$default=0;}
    if ($_POST['mainrun']){$mainrun=1;}else{$mainrun=0;}
    $sundaycount=$_POST['sunday_count'];
    $mondaycount=$_POST['monday_count'];
    $tuesdaycount=$_POST['tuesday_count'];
    $wednesdaycount=$_POST['wednesday_count'];
    $thursdaycount=$_POST['thursday_count'];
    $fridaycount=$_POST['friday_count'];
    $saturdaycount=$_POST['saturday_count'];
    $days="";
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,6)=="check_")
        {
            $key=str_replace("check_","",$key);
            switch ($key)
            {
                case "sunday":
                $days.="0,";
                break;
                case "monday":
                $days.="1,";
                break;
                case "tuesday":
                $days.="2,";
                break;
                case "wednesday":
                $days.="3,";
                break;
                case "thursday":
                $days.="4,";
                break;
                case "friday":
                $days.="5,";
                break;
                case "saturday":
                $days.="6,";
                break;
                
            }
        }
        
    }
    $days=substr($days,0,strlen($days)-1);
    if ($action=='insert')
    {
        $sql="INSERT INTO publications_insertruns (main_run,pub_id, run_name, run_default,run_days, days_prev, run_time, site_id, 
        monday_count, tuesday_count, wednesday_count, thursday_count, friday_count, saturday_count, sunday_count)
         VALUES ('$mainrun', '$pubid', '$runname', '$default', '$days', '$daysprev', '$runtime', '$siteID', '$mondaycount', '$tuesdaycount', 
         '$wednesdaycount', '$thursdaycount', '$fridaycount', '$saturdaycount', '$sundaycount')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } elseif($action=='duplicate')
    {
        //means we are copying from another day, we'll need to copy all zones and trucks as well
        $sql="INSERT INTO publications_insertruns (main_run, pub_id, run_name, run_default, run_days, days_prev, runtime, site_id, 
        monday_count, tuesday_count, wednesday_count, thursday_count, friday_count, saturday_count, sunday_count)
         VALUES ('$mainrun', '$pubid', '$runname', '$default', '$days', '$daysprev', '$runtime', '$siteID', '$mondaycount', '$tuesdaycount', 
         '$wednesdaycount', '$thursdaycount', '$fridaycount', '$saturdaycount', '$sundaycount')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $newrunid=$dbInsert['numrows'];
        
        //now select all zones and copy them to the new run id
        $sql="SELECT * FROM publications_insertzones WHERE run_id=$runid";
        $dbCurrent=dbselectmulti($sql);
        $tzones=array();
        if ($dbCurrent['numrows']>0)
        {
            foreach($dbCurrent['data'] as $zone)
            {
                $sql="INSERT INTO publications_insertzones (pub_id, run_id, zone_name, zone_order, zone_zip) VALUES (
                '$pubid', '$newrunid', '$zone[zone_name]', '$zone[zone_order]', '$zone[zone_zip]')";
                $dbNewZone=dbinsertquery($sql);
                $tzone[$zone['id']]=$dbNewZone['insertid'];
            }
        }
        //and now all trucks need to be copied
        $sql="SELECT * FROM publications_inserttrucks WHERE run_id=$runid";
        $dbCurrent=dbselectmulti($sql);
        $ttruck=array();
        if ($dbCurrent['numrows']>0)
        {
            foreach($dbCurrent['data'] as $truck)
            {
                $zoneid=$tzone[$truck['zone_id']]; //convert to the new zone created above 
                $sql="INSERT INTO publications_inserttrucks (pub_id, run_id, zone_id, 
                truck_description, truck_name, truck_order, truck_notes, driver_name, 
                driver_number, average_sunday, average_monday, average_tuesday, 
                average_wednesday, average_thursday, average_friday, average_saturday) VALUES (
                '$pubid', '$newrunid', '$zoneid', '$truck[truck_description]', 
                '$truck[truck_name]', '$truck[truck_order]', '$truck[truck_notes]', 
                '$truck[driver_name]', '$truck[driver_number]', '$truck[average_sunday]', 
                '$truck[average_monday]', '$truck[average_tuesday]', '$truck[average_wednesday]', 
                '$truck[average_thursday]', '$truck[average_friday]', '$truck[average_saturday]')";
                $dbNewTruck=dbinsertquery($sql);
                $ttruck[$truck['id']]=$dbNewTruck['insertid'];
            }
        }
        
        //now we need each route for each truck
        $sql="SELECT * FROM publications_insertroutes WHERE run_id=$runid";
        $dbCurrent=dbselectmulti($sql);
        if ($dbCurrent['numrows']>0)
        {
            foreach($dbCurrent['data'] as $route)
            {
                $truckid=$ttruck[$route['truck_id']]; //convert to the new zone created above 
                $route_number=addslashes($route['route_number']);
                $route_account=addslashes($route['route_account']);
                $route_driver=addslashes($route['route_driver']);
                $route_phone=addslashes($route['route_phone']);
                $route_sequence=addslashes($route['route_sequence']);
                $route_notes=addslashes($route['route_notes']);
                
                $sql="INSERT INTO publications_insertroutes (pub_id, run_id, truck_id, 
                route_number, route_account, route_driver, route_phone, route_sequence, 
                route_notes, bulk) vALUES ('$pubid', '$newrunid', '$truckid', 
                '$route_number', '$route_account', '$route_driver', 
                '$route_phone', '$route_sequence', '$route_notes', 
                '$route[bulk]')";
                $dbNewRoute=dbinsertquery($sql);
                if ($dbNewRoute['error']!='')
                {
                    print $dbNewRoute['error'];
                    die();
                }
            }
        }
        
    } else {
        $sql="UPDATE publications_insertruns SET main_run='$mainrun', run_days='$days', run_name='$runname', 
        run_default='$default', pub_id='$pubid', days_prev='$daysprev', run_time='$runtime', 
        monday_count='$mondaycount', tuesday_count='$tuesdaycount', wednesday_count='$wednesdaycount', 
        thursday_count='$thursdaycount', friday_count='$fridaycount', saturday_count='$saturdaycount', sunday_count='$sundaycount'
         WHERE id=$runid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the insert run','error');
    } else {
        setUserMessage('Insert run successfully saved','success');
    }
    redirect("?action=listinsertruns&pubid=$pubid");
}


function save_zone($action)
{
    $pubid=$_POST['pubid'];
    $runid=$_POST['runid'];
    $zoneid=$_POST['zoneid'];
    $zonename=addslashes($_POST['zonename']);
    $zoneorder=addslashes($_POST['zoneorder']);
    $zonezip=addslashes($_POST['zonezip']);
    $zonecount=addslashes($_POST['zonecount']);
    if ($zoneorder==''){$zoneorder=99;}
    if ($action=='insert')
    {
        $sql="INSERT INTO publications_insertzones (pub_id, run_id, zone_name, zone_order, zone_zip, zone_count)
         VALUES ('$pubid', '$runid', '$zonename', '$zoneorder', '$zonezip', '$zonecount')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE publications_insertzones SET zone_zip='$zonezip', zone_name='$zonename', zone_order='$zoneorder', zone_count='$zonecount' WHERE id=$zoneid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the zone.<br />'.$error,'error');
    } else {
        setUserMessage('The zone has been successfully saved.','success');
    }
    redirect("?action=listzones&pubid=$pubid&runid=$runid");
    
}


function routes($action)
{
    $pubid=$_GET['pubid'];
    $runid=$_GET['runid'];
    $truckid=$_GET['truckid'];
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Route";
            $zoneorder=1;
        } else {
            $button="Update Route";
            $routeid=$_GET['routeid'];
            $sql="SELECT * FROM publications_insertroutes WHERE id=$routeid";
            $dbRoutes=dbselectsingle($sql);
            $route=$dbRoutes['data'];
            $routenumber=stripslashes($route['route_number']);
            $bulk=stripslashes($route['bulk']);
            $account=stripslashes($route['route_account']);
            $driver=stripslashes($route['route_driver']);
            $phone=stripslashes($route['route_phone']);
            $order=stripslashes($route['route_sequence']);
            $notes=stripslashes($route['route_notes']);
        }
        print "<form method=post>\n";
        make_text('routenumber',$routenumber,'Route #');
        make_checkbox('bulk',$bulk,'Bulk','Check for bulk route.');
        make_text('account',$account,'Account Name','',50);
        make_text('driver',$driver,'Driver Name');
        make_text('phone',$phone,'Driver Phone');
        make_text('order',$order,'Sequence');
        make_textarea('notes',$notes,'Notes','Will appear on bundle top',70,20,true);
        make_submit('submit',$button);
        print "<input type='hidden' name='routeid' value='$routeid'>\n";
        print "<input type='hidden' name='truckid' value='$truckid'>\n";
        print "<input type='hidden' name='pubid' value='$pubid'>\n";
        print "<input type='hidden' name='runid' value='$runid'>\n";
        print "</form>\n";
        ?>
        <script type="text/javascript">
        $('#routenumber').focus();
        </script>
        <?php
    }elseif ($action=='delete')
    {
        $sql="DELETE * FROM publications_insertroutes WHERE id=$_GET[routeid]";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the route.<br />'.$error,'error');
        } else {
            setUserMessage('The route has been successfully deleted.','success');
        }
        redirect("?action=listroutes&pubid=$pubid&runid=$runid&truckid=$truckid");
    } else {
       //show all the pubs
       $sql="SELECT * FROM publications_insertroutes WHERE truck_id=$truckid ORDER BY route_sequence";
       $dbRoutes=dbselectmulti($sql);
       tableStart("<a href='?action=addroute&pubid=$pubid&runid=$runid&truckid=$truckid'>Add route</a>,<a href='?action=listtrucks&pubid=$pubid&runid=$runid'>Return to trucks</a>,<a href='?action=list'>Return to main</a>","Sequence,Route Number,Route Driver",5);
       if ($dbRoutes['numrows']>0)
       {
            foreach($dbRoutes['data'] as $route)
            {
                $routeid=$route['id'];
                $routenumber=$route['route_number'];
                $account=$route['route_account'];
                $order=$route['route_sequence'];
                print "<tr><td>$order</td>\n";
                print "<td>$routenumber</td>\n";
                print "<td>$account</td>\n";
                print "<td><a href='?action=editroute&pubid=$pubid&runid=$runid&truckid=$truckid&routeid=$routeid'>Edit</a></td>\n";
                print "<td><a class='delete' href='?action=deleteroute&pubid=$pubid&runid=$runid&truckid=$truckid&routeid=$routeid'>Delete</a></td>\n";
                print "</tr>\n";
            }
       }
       tableEnd($dbRoutes);
    }


}


function save_route($action)
{
    $pubid=$_POST['pubid'];
    $runid=$_POST['runid'];
    $truckid=$_POST['truckid'];
    $routeid=$_POST['routeid'];
    $routenumber=addslashes($_POST['routenumber']);
    $order=addslashes($_POST['order']);
    $notes=addslashes($_POST['notes']);
    $driver=addslashes($_POST['driver']);
    $account=addslashes($_POST['account']);
    $phone=addslashes($_POST['phone']);
    if ($draw==''){$draw=0;}
    if ($_POST['bulk']){$bulk=1;}else{$bulk=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO publications_insertroutes (pub_id, run_id, truck_id, route_number,
        bulk, route_sequence, route_driver, route_phone, route_notes, route_account)
         VALUES ('$pubid', '$runid', '$truckid', '$routenumber', '$bulk', '$order', '$driver',
          '$phone', '$notes', '$account')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE publications_insertroutes SET route_account='$account', route_number='$routenumber', route_notes='$notes', 
        route_driver='$driver', bulk='$bulk', route_phone='$phone', route_sequence='$order' WHERE id=$routeid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the route.<br />'.$error,'error');
    } else {
        setUserMessage('The route has been successfully added.','success');
    }
    redirect("?action=listroutes&pubid=$pubid&runid=$runid&truckid=$truckid");
    
}


function save_truck($action)
{
    $pubid=$_POST['pubid'];
    $runid=$_POST['runid'];
    $truckid=$_POST['truckid'];
    $zoneid=$_POST['zoneid'];
    $name=addslashes($_POST['truckname']);
    $order=addslashes($_POST['truckorder']);
    $notes=addslashes($_POST['trucknotes']);
    if ($order==''){$order=99;}
    if ($action=='insert')
    {
        $sql="INSERT INTO publications_inserttrucks (pub_id, run_id, zone_id, truck_name, truck_order, truck_notes)
         VALUES ('$pubid', '$runid', '$zoneid', '$name', '$order', '$notes')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE publications_inserttrucks SET truck_notes='$notes', zone_id='$zoneid',
         truck_name='$name', truck_order='$order' WHERE id=$truckid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem adding the truck to the route.<br />'.$error,'error');
    } else {
        setUserMessage('Truck has been successfully added to the route.','success');
    }
    redirect("?action=listtrucks&pubid=$pubid&runid=$runid");
}


function benchmarks()
{
    global $siteID;
    $runid=$_GET['runid'];
    $pubid=$_GET['pubid'];
    //get all the benchmarks
    $sql="SELECT * FROM benchmarks WHERE site_id=$siteID ORDER BY benchmark_category, benchmark_name";
    $dbBenchmarks=dbselectmulti($sql);
    if ($dbBenchmarks['numrows']>0)
    {
        //we'll make a table, then loop through each benchmark and look up the values for that one from the run_benchmark table
        print "<form name='benchmarks' method=post>\n";
        print "<table class='grid'>\n";
        print "<tr><th colspan=11>Please note: The date listed below is the Publication Day of the job, so a Sunday pub will have greater times usually</th></tr>\n";
        print "<tr><th>Benchmark</th><th>Category</th><th>Type</th><th>Sunday</th><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th><th>Action</th></tr>\n";
        foreach($dbBenchmarks['data'] as $benchmark)
        {
            $name=$benchmark['benchmark_name'];
            $type=$benchmark['benchmark_type'];
            $bid=$benchmark['id'];
            $category=$benchmark['benchmark_category'];
            print "<tr><td style='background-color:#999999;font-weight:bold;'>$name</td>";
            print "<td style='background-color:#999999;font-weight:bold;'>$category</td>";
            print "<td style='background-color:#999999;font-weight:bold;'>$type</td>";
            
            //now grab the values for the run_benchmarks for this benchmark id
            $sql="SELECT * FROM run_benchmarks WHERE benchmark_id=$bid AND run_id=$runid ";
            $dbRunInfo=dbselectsingle($sql);
            if ($dbRunInfo['numrows']>0)
            {
                $info=$dbRunInfo['data'];
                $sunday=$info['sunday'];
                $monday=$info['monday'];
                $tuesday=$info['tuesday'];
                $wednesday=$info['wednesday'];
                $thursday=$info['thursday'];
                $friday=$info['friday'];
                $saturday=$info['saturday'];
                $id=$info['id'];
            } else {
                //no record, just use blanks
                $id=0;
                if ($type=='time')
                {
                    $sunday="12:00";
                    $monday="12:00";
                    $tuesday="12:00";
                    $wednesday="12:00";
                    $thursday="12:00";
                    $friday="12:00";
                    $saturday="12:00";
                } else {
                    $sunday=0;
                    $monday=0;
                    $tuesday=0;
                    $wednesday=0;
                    $thursday=0;
                    $friday=0;
                    $saturday=0;
                }
            }
            if ($type=='number'){$numonly="onkeydown='return isNumberKey(event);'";}else{$numonly="";}
            $type.="_";
            $sunday="<input type='text' id='".$type.$bid."_".$id."_sunday' name='".$type.$bid."_".$id."_sunday' value='$sunday' style='width:50px;' $numonly>";
            $monday="<input type='text' id='".$type.$bid."_".$id."_monday' name='".$type.$bid."_".$id."_monday' value='$monday' style='width:50px;' $numonly>";
            $tuesday="<input type='text' id='".$type.$bid."_".$id."_tuesday' name='".$type.$bid."_".$id."_tuesday' value='$tuesday' style='width:50px;' $numonly>";
            $wednesday="<input type='text' id='".$type.$bid."_".$id."_wednesday' name='".$type.$bid."_".$id."_wednesday' value='$wednesday' style='width:50px;' $numonly>";
            $thursday="<input type='text' id='".$type.$bid."_".$id."_thursday' name='".$type.$bid."_".$id."_thursday' value='$thursday' style='width:50px;' $numonly>";
            $friday="<input type='text' id='".$type.$bid."_".$id."_friday' name='".$type.$bid."_".$id."_friday' value='$friday' style='width:50px;' $numonly>";
            $saturday="<input type='text' id='".$type.$bid."_".$id."_saturday' name='".$type.$bid."_".$id."_saturday' value='$saturday' style='width:50px;' $numonly>";
            //now display them
            print "<td>$sunday</td><td>$monday</td><td>$tuesday</td><td>$wednesday</td><td>$thursday</td><td>$friday</td><td>$saturday</td>";
            print "</tr>\n";
        }
        print "<input type='hidden' name='pubid' value='$pubid'>\n";
        print "<input type='hidden' name='runid' value='$runid'>\n";
        print "<tr><th colspan=11><input type='submit' name='submit' value='Save Benchmarks'></th></tr>\n";
        print "</table>\n";
        print "</form>\n";
    } else {
        displayMessage("No benchmarks have been set up yet.");
    }
    
}


function save_benchmarks()
{
    //grab some basics immediately
    global $siteID;
    $runid=$_POST['runid'];
    $pubid=$_POST['pubid'];
    $values=array();
    foreach($_POST as $key=>$value)
    {
        //look for all the _'s
        if (strpos($key,"_")>0)
        {
            $parts=explode("_",$key);
            $type=$parts[0];
            $benchmark_id=$parts[1];
            $runBenchmark_id=$parts[2];
            $dayfield=$parts[3];
            $temp=array("day"=>"$dayfield='$value'","rbid"=>$runBenchmark_id);
            $values[$benchmark_id][]=$temp;
            
        }
    }
    foreach($values as $key=>$set)
    {
        $days="";
        $parts="";
        $fields="";
        $sqlvalues="";
        foreach($set as $item)
        {
            $runBenchmark_id=$item['rbid'];
            if ($runBenchmark_id==0)
            {
                $parts=explode("=",$item['day']);
                $fields.=$parts[0].",";
                $sqlvalues.=$parts[1].",";
            } else {
                $days.=$item['day'].",";        
            }
        }
        if ($runBenchmark_id==0)
        {
            $fields=substr($fields,0,strlen($fields)-1);
            $sqlvalues=substr($sqlvalues,0,strlen($sqlvalues)-1);
            //inserting a new one
            $sql="INSERT INTO run_benchmarks (site_id, run_id, benchmark_id,$fields) VALUES ($siteID, $runid,$key,$sqlvalues)";
            $dbInsert=dbinsertquery($sql);
        } else {
            $days=substr($days,0,strlen($days)-1);
            //updating one
            $sql="UPDATE run_benchmarks SET $days WHERE id=$runBenchmark_id";
            $dbUpdate=dbexecutequery($sql);
        }
        
    }
    setUserMessage('Benchmarks have been updated','success');
    redirect("?action=listruns&pubid=$pubid");
}

footer();
?>

