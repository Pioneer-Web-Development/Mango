<?php
  /*
  *  inserter packages script
  *  this script handles displaying packages as a popup for the calendar
  *  it also handles simply displaying all created packages. It does not allow editing of inserts directly but will allow
  *  redirection to the insert.php file for insert editing, and arrangement of inserts by directing back to insertPlanner.php
  *  it also handles job ticket printing by redirecting to the inserterJobTicket.php,
  *  package setup (hopper assignement) and data collection/saving
  */
  include("includes/mainmenu.php");
  
  
  if($_POST)
  {
      $action=$_POST['submit'];
  } else {
      $action=$_GET['action'];
  }
  
switch($action)
{
    case "data":
        inserterData();
    break;
    
    case "Save Data":
        save_inserterData();
    break;
    
    case "listpackages":
        packages();
    break;
        
    case "delete":
        delete_package();
    break;
        
    case "settings":
        package_settings();
    break;
    
    case "Update Package Settings":
        save_package_settings();
    break;
    
    case "inserts":
        inserts();
    break;
    
    case "Save Inserts":
        save_inserts();
    break;
    
    case "addpackage":
        package();
    break;
    
    case "Save Package":
        save_package();
    break;
    
    case "edit":
        package();
    break;
    
    default:
        packages();
    break;
}

function inserts()
{
   $packageid=intval($_GET['packageid']);
   print "<a href='?action=list' class='button'>Return to package list</a><br><br>"; 
   print "<div id='packageholder' style='float:left;width:650px;margin-left:100px;'>\n";
   print "<img src='artwork/icons/ajax-loader.gif' style='margin-top:100px;margin-left:300px;'>\n";
   ?>
   <script>
   $(document).ready(function(){
    loadPackageOfInserts(); 
       
   })
   function loadPackageOfInserts()
   {
       $.ajax({
        url: 'includes/ajax_handlers/generateInsertPackageDisplay.php',
        data: {packageid:<?php echo $packageid; ?>,people:0,count:1,zones:1,maxwidth:600},
        dataType: 'html',
        success: function(response){
           $('#packageholder').html(response); 
        }
       })    
      
   }
   </script>
   <?php
   print "</div>\n";
       
}

function save_inserts()
{
     $inserterid=$_POST['inserterid'];
     $packageid=$_POST['packageid'];
     $jacketid=$_POST['jacketid'];
     $values="";
     $userid=$_SESSION['cmsuser']['userid'];
     foreach($_POST as $key=>$value)
     {
         if(substr($key,0,7)=='pocket_')
         {
             $key=str_replace('pocket_','',$key);
             $values.="('$packageid', '$value', '$inserterid', '$key'),";
             
             //update the insert with a status of packaged
             $sql="UPDATE inserts SET planned=1 WHERE id=$value";
             $dbUpdate=dbexecutequery($sql);     
         }
     }
     $values=substr($values,0,strlen($values)-1);
     
     
     $sql="UPDATE jobs_inserter_packages SET jacket_insert_id='$jacketid' WHERE id='$packageid'";
     $dbUpdate=dbexecutequery($sql);
     
     //clear existing inserts for this package
     $sql="DELETE FROM jobs_packages_inserts WHERE package_id=$packageid";
     $dbDelete=dbexecutequery($sql);
     
     $sql="INSERT INTO jobs_packages_inserts (package_id, insert_id, inserter_id, hopper_id) VALUES $values";
     $dbInsert=dbinsertquery($sql);
     $error=$dbInsert['error'];
     
     if($_POST['popout']=='true')
    {
        print "<script>window.close();</script>";
    } else {
        if($error=='')
         {
             setUserMessage('Package inserts have been saved successfully','success');
         } else {
             setUserMessage('There was a problem saving the insert packaging.<br>'.$error,'error');
         }
         redirect("?action=list");
    }
}


function package()
{
    global $pubs;
    //handles hopper assignment, will need to provide two hopper assignments for double-out scenarios
    $packageid=intval($_GET['packageid']);
    $sql="SELECT * FROM jobs_inserter_packages WHERE id=$packageid";
    $dbPackage=dbselectsingle($sql);
    $package=$dbPackage['data'];
    $pubid=$package['pub_id'];
    $runid=$package['run_id'];
    $pubdate=$package['pub_date'];
    if($pubdate==''){$pubdate=date("Y-m-d",strtotime("+1 day"));}
    $pubname=$pubs[$package['pub_id']];
    $packagename=stripslashes($package['package_name']);
    $inserterid=$package['inserter_id'];
    $doubleout=$package['double_out'];
    $starttime=$package['package_startdatetime'];
    if($starttime==''){$starttime=date("Y-m-d H:i",strtotime("+24 hours"));}
    $stoptime=$package['package_stopdatetime'];
    if($stoptime==''){$stoptime=date("Y-m-d H:i",strtotime("+26 hours"));}
    $inserterrequest=$package['inserter_request'];
    if($inserterrequest==''){$inserterrequest=0;}
    $insertruns=array();
    $insertruns[0]='Please choose';
    $sql="SELECT * FROM publications_insertruns WHERE pub_id='$pubid' ORDER BY run_name";
    $dbRuns=dbselectmulti($sql);
    if($dbRuns['numrows']>0)
    {
      foreach($dbRuns['data'] as $run)
      {
          $insertruns[$run['id']]=stripslashes($run['run_name']);
      }
    }
    //grab the inserter data
    $sql="SELECT * FROM inserters";
    $dbInserters=dbselectmulti($sql);
    foreach($dbInserters['data'] as $inserter)
    {
        $inserters[$inserter['id']]=$inserter['inserter_name'];
    }
    
    
    print "<form method=post>\n";
    make_text('name',$packagename,'Package Name','What shall we call this package');
    make_select('pub_id',$pubs[$pubid],$pubs,'Publication','','',false,"getInsertRuns()");
    make_select('run_id',$insertruns[$runid],$insertruns,'Run Name','Select the inserter run for this package');
    make_date('pub_date',$pubdate,'Insertion date','When does it insert?');
    make_select('inserter_id',$inserters[$inserterid],$inserters,'Inserter','Select the inserter for this run');
    make_number('request',$inserterrequest,'Insert Count','How many are we supposed to insert?');
    make_datetime('start',$starttime,'Job start time','When should this package start?');
    make_datetime('stop',$stoptime,'Job stop time','When should this package be completed?');
    make_checkbox('doubleout',$doubleout,'Double Out','If the inserter is capable, is this package going to double-out?');
    make_hidden('packageid',$packageid);
    if($_GET['popup'])
    {
        make_hidden('popout','true');
    }
    make_submit('submit','Save Package');
    print "</form>\n";
}

function save_package()
{
    global $siteID;
    $name=addslashes($_POST['name']);
    $pubid=$_POST['pub_id'];
    $runid=$_POST['run_id'];
    $pubdate=$_POST['pub_date'];
    $inserterid=$_POST['inserter_id'];
    $request=$_POST['request'];
    $startdatetime=$_POST['start'];
    $stopdatetime=$_POST['stop'];
    if($_POST['doubleout']){$doubleout=1;}else{$doubleout=0;}
    $packageid=$_POST['packageid'];
    $runlength=strtotime($stopdatetime)-strtotime($startdatetime);
    $runlength=$runlength/60; //convert to minutes
    if($packageid>0)
    {
        $sql="UPDATE jobs_inserter_packages SET pub_id='$pubid', run_id='$runid', pub_date='$pubdate', inserter_request='$request', 
        package_name='$name', inserter_id='$inserterid', package_startdatetime='$startdatetime', package_stopdatetime='$stopdatetime', package_runlength='$runlength',
        double_out='$doubleout', status=1 WHERE id=$packageid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    } else {
        $sql="INSERT INTO jobs_inserter_packages (pub_id, run_id, pub_date, inserter_request, package_name, inserter_id, package_startdatetime, 
        package_stopdatetime, package_runlength, double_out, status, site_id) VALUES ('$pubid', '$runid', '$pubdate', '$request', '$name', '$inserterid',
        '$startdatetime', '$stopdatetime', '$runlength', '$doubleout', 1, '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $packageid=$dbInsert['insertid'];
        $error=$dbInsert['error'];
        
        //we also need to set up the package settings from the defaults for this inserter
        $sql="SELECT * FROM inserters WHERE id=$inserterid";
        $dbInserter=dbselectsingle($sql);
        $settings=$dbInserter['data'];
        $sql="INSERT INTO jobs_inserter_packages_settings (package_id, reject_misses, reject_doubles, miss_fault, double_fault, attempt_repair, 
        gap, delivery, copies_per_bundle, turns) VALUES ('$packageid', '$settings[reject_misses]', '$settings[reject_doubles]', 
        '$settings[miss_fault]', '$settings[double_fault]', '$settings[attempt_repair]','$settings[gap]', '$settings[delivery]',
        '$settings[copies_per_bundle]','$settings[turns]')";
        $dbInsert=dbinsertquery($sql);
        
    }
    
    
    if($_POST['popout']=='true')
    {
        print "<script>window.close();</script>";
    } else {
        if($error=='')
        {
            if($packageid>0)
            {
                  setUserMessage('Package has been updated successfully.','success');
            } else {
                  setUserMessage('Package has been created successfully.','success');
            }
        } else {
            setUserMessage('There was a problem creating the package.<br>'.$error,'error');
        }
        redirect("?action=list");
    }
}

function delete_package()
{
    //please note that this code is also used in ajax_handlers/saveInsertPackages.php for the delete package function
    //any updates need to be mirrored between the two, with obvious differences in the $response handling
        
    //just need to unassign any existing packages
    //now we need to remove all inserts that were on this package, and get updated package totals
    $packid=intval($_GET['packageid']);
    
    //get plan
    $sql="SELECT * FROM jobs_inserter_packages WHERE id=$packid";
    $dbPackage=dbselectsingle($sql);
    $package=$dbPackage['data'];
    $planid=$package['plan_id'];
    
    $sql="SELECT * FROM jobs_packages_inserts WHERE plan_id=$planid AND package_id=$packid";
    $dbInserts=dbselectmulti($sql);
    
    if($dbInserts['numrows']>0)
    {
        foreach($dbInserts['data'] as $insert)
        {
            $temp=removeInsert($insert['plan_id'],$insert['package_id'],$insert['insert_id'],$insert['insert_type'],$insert['hopper_id']);
            $deleteids=$insert['id'].",";
        }
    }
    $deleteids=substr($deleteids,0,strlen($deleteids)-1);
    if($deleteids!='')
    {
        $sql="DELETE FROM jobs_packages_inserts WHERE id IN ($deleteids)";
        $dbDelete=dbexecutequery($sql);
    }
    //subtract one from the number of package in the jobs_inserter_plans
    $sql="UPDATE jobs_inserter_plans SET num_packages=num_packages-1 WHERE id=$planid";
    $dbUpdate=dbexecutequery($sql);
    $error.=$dbUpdate['error'];
    
    //delete the actual package
    $sql="DELETE FROM jobs_inserter_packages WHERE id=$packid";
    $dbDelete=dbexecutequery($sql);
    $error.=$dbDelete['error'];
    if($error=='')
    {
        setUserMessage('The pacakge was deleted successfully','success');
    } else {
        setUserMessage('There was a problem deleting the package.<br>'.$error,'error');
    }
    redirect("?action=list");
} 
 
function packages()
{
   global $siteID, $pubs;
    //list all packages, include search filters for run date, pub date and publication
   $sql="SELECT * FROM jobs_inserter_packages";
   $dbPackages=dbselectmulti($sql);
   $packstartdate=date("Y-m-d",strtotime("-1 week"));
   $packstopdate=date("Y-m-d",strtotime("+1 week"));
   if($_GET['planid'])
   {
       $pub='';
       $pubdate='WHERE plan_id='.intval($_GET['planid']);
   } else {
       if ($_POST['search']=='Search')
       {
            $pubdate="AND package_startdatetime<='".$_POST['stopdate']."' AND package_startdatetime>='".$_POST['startdate']."'";
            $pubid=$_POST['search_pub'];
            if ($pubid!=0)
            {
                $pub="WHERE pub_id='$pubid'";
            } else {
                $pub="WHERE pub_id>'0'";
            }
            $packstartdate=$_POST['startdate'];
            $packstopdate=$_POST['stopdate'];
       } else {
           $pubdate="WHERE package_startdatetime>='$packstartdate' AND package_stopdatetime<='$packstopdate'";
       }
   }
   $search="<form method=post>\n";
   $search.="Pub date<br>";
   $search.= "Packages scheduled to run between<br>";
   $search.=input_date('startdate',$packstartdate);
   $search.="and<br>";
   $search.=input_date('stopdate',$packstopdate);
   $search.="<br>Publication<br>";
   $search.=input_select('search_pub',$pubs[0],$pubs);
   $search.="<input type=submit name='search' id='search' value='Search'></input>\n";
   $search.="</form>\n"; 
   
   
   //run sql
   $sql="SELECT * FROM jobs_inserter_packages $pub $pubdate ORDER BY pub_date DESC";
   if($GLOBALS['debug']){print $sql;}
   $dbPackages=dbselectmulti($sql);
   tableStart("<a href='inserterPlanner.php?action=addplan'>Add a plan</a>,<a href='inserterPlanner.php'>View plans</a>,<a href='inserts.php?action=list'>Show inserts</a>","Package Name, Publication,Package Date,Publication Date",11,$search);
   if ($dbPackages['numrows']>0)
   {
       foreach($dbPackages['data'] as $package)
       {
           print "<tr>\n";
           $planid=$package['id'];
           $pubid=$package['pub_id'];
           $pub=$pubs[$package['pub_id']];
           $date=date("m/d/Y H:i",strtotime($package['package_startdatetime']));
           $pubdate=date("m/d/Y",strtotime($package['pub_date']));
           $name=stripslashes($package['package_name']);
           print "<td>$name</td>";
           print "<td>$pub</td>";
           print "<td>$date</td>";
           print "<td>$pubdate</td>";
           print "<td><a href='?action=edit&packageid=$planid'>Edit Package</a></td>";
           print "<td><a href='?action=settings&packageid=$planid'>Package Settings</a></td>";
           print "<td><a href='#' onclick=\"window.open('printouts/insertpackage.php?packageid=$planid','Package Details','width=700,height=800,toolbar=0,status=0,location=0,scrollbars=yes');\">Print Package</a></td>";
           print "<td><a href='?action=inserts&packageid=$planid'>Inserts</a></td>";
           print "<td><a href='#' onclick=\"window.open('printouts/insertJobTicket.php?packageid=$planid','Packing Job Ticket','width=700,height=800,toolbar=0,status=0,location=0,scrollbars=yes');\">Print Ticket</a></td>";
           print "<td><a href='?action=data&packageid=$planid'>Input Data</a></td>";
           print "<td><a class='delete' href='?action=delete&packageid=$planid'>Delete</a></td>";
           print "</tr>\n";
       }
   }
   tableEnd($dbPackages);
}  

function package_settings()
{
    $packageid=intval($_GET['packageid']);
    
    print "<form id='settings' name='settings' method='post'>\n";
    print "<h3>These are the inserter settings for this package run</h3>\n";
    $sql="SELECT * FROM jobs_inserter_packages_settings WHERE package_id=$packageid";
    $dbSettings=dbselectsingle($sql);
    $settings=$dbSettings['data'];
    if($dbSettings['numrows']='')
    {
        $misses=$settings['reject_misses'];
        $doubles=$settings['reject_doubles'];
        $missfault=$settings['miss_fault'];
        $doublefault=$settings['double_fault'];
        $attemptrepair=$settings['attempt_repair'];
        $gap=$settings['gap'];
        $delivery=$settings['delivery'];
        $copiesperbundle=$settings['copies_per_bundle'];
        $turns=$settings['turns'];
    } else {
        $misses=1;
        $doubles=1;
        $missfault=3;
        $doublefault=5;
        $attemptrepair=1;
        $gap=30;
        $delivery=1;
        $copiesperbundle=40;
        $turns=2;
    }
    
    make_checkbox('reject_misses',$misses,'Reject Misses','Check to reject misses');
    make_checkbox('reject_doubles',$doubles,'Reject doubles','Check to reject doubles');
    make_number('miss_fault',$missfault,'Miss Fault','How many misses before triggering a fault?');
    make_number('double_fault',$doublefault,'Double Fault','How many doubles before triggering a fault?');
    make_number('attempt_repair',$attemptrepair,'Attempt Repair','How many times to attempt repair?');
    make_number('gap',$gap,'Gap','How many papers between routes?');
    make_number('delivery',$delivery,'Delivery','Not sure how to describe this---deb??????');
    make_number('copies_per_bundle',$copiesperbundle,'Copies per bundle','How many papers in each bundle?');
    make_number('turns',$turns,'Turns','How many turns in a bundle?');
    make_hidden('settingsid',$settings['id']);
    make_hidden('packageid',$packageid);
    make_hidden('action','savesettings');
    if($_GET['popup'])
    {
        make_hidden('popout','true');
    }
    make_submit('submit','Update Package Settings');
    print "</form>\n";
}

function save_package_settings()
{
    $settingsid=$_POST['settingsid'];
    $packageid=$_POST['packageid'];
    if($_POST['reject_misses']){$misses=1;}else{$misses=0;}
    if($_POST['reject_doubles']){$doubles=1;}else{$doubles=0;}
    $missfault=$_POST['miss_fault'];
    $doublefault=$_POST['double_fault'];
    $repair=$_POST['attempt_repair'];
    $gap=$_POST['gap'];
    $delivery=$_POST['delivery'];
    $copies=$_POST['copies_per_bundle'];
    $turns=$_POST['turns'];
    if($settingsid==0 || $settingsid=='')
    {
        $sql="INSERT INTO jobs_inserter_packages_settings (miss_fault, double_fault, reject_misses, reject_doubles, 
        attempt_repair, gap, delivery, copies_per_bundle, turns) VALUES ('$missfault', '$doublefault', '$misses', 
        '$doubles', '$repair', '$gap', '$delivery', '$copies', '$turns')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        
    } else {
        $sql="UPDATE jobs_inserter_packages_settings SET miss_fault='$missfault', double_fault='$doublefault', 
        reject_misses='$misses', reject_doubles='$doubles', attempt_repair='$repair', gap='$gap', 
        delivery='$delivery', copies_per_bundle='$copies', turns='$turns' WHERE id=$settingsid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if($_POST['popout']=='true')
    {
        print "<script>window.close();</script>";
    } else {
        if($error=='')
        {
            setUserMessage('Settings successfully saved','success');
        } else {
            setUserMessage('There was a problem updating the settings.<br>'.$error,'error');
        }
        redirect("?action=list");
    }
}
  
function inserterData()
{
    $packageid=intval($_GET['packageid']);
    //get plan details
    $sql="SELECT A.*, B.pub_name FROM jobs_inserter_packages A, publications B WHERE A.id=$packageid AND A.pub_id=B.id";
    $dbPackage=dbselectsingle($sql);
    $package=$dbPackage['data'];
    $pubid=$package['pub_id'];
    $pubdate=$package['pub_date'];
    $displaydate=date("D, m/d/Y",strtotime($package['pub_date']));
    $pubname=$package['pub_name'];
    $packagename=$package['package_name'];
    $planid=$package['plan_id'];
    $inserterid=$package['inserter_id'];
   $sql="SELECT A.* FROM inserts A, jobs_packages_inserts B WHERE B.insert_type='insert' AND B.package_id=$packageid AND B.insert_id=A.id";
   $dbPackageInserts=dbselectmulti($sql);
   if($dbPackageInserts['numrows']>0)
   {  
        //collect all the other job data
        //check for sticky note
       foreach($dbPackageInserts['data'] as $insert)
       {
            if ($insert['sticky_note']){
                $sticky=1;
            }    
       }
   }
   
   //get plan
   $sql="SELECT * FROM jobs_inserter_plans WHERE id=$planid";
   $dbPlan=dbselectsingle($sql);
   $plan=$dbPlan['data'];
   //check for addressing
     
   $address=$plan['address'];
   
   $operators=array();
   $operators[0]="Please choose";
   
   global $mailroomDepartmentID;
   
   $sql="SELECT A.* FROM users A, user_positions B WHERE A.department_id=$mailroomDepartmentID AND A.position_id=B.id AND B.operator=1 ORDER BY firstname";
   $dbOperators=dbselectmulti($sql);
   if ($dbOperators['numrows']>0)
   {
       foreach($dbOperators['data'] as $operator)
       {
           $operators[$operator['id']]=$operator['firstname']." ".$operator['lastname'];
       }
   }
   $feeders=array();
   $feeders[0]="Please choose";
   $sql="SELECT * FROM users WHERE department_id=3 ORDER BY firstname";
   $dbFeeders=dbselectmulti($sql);
   if ($dbFeeders['numrows']>0)
   {
       foreach($dbFeeders['data'] as $feeder)
       {
           $feeders[$feeder['id']]=$feeder['firstname']." ".$feeder['lastname'];
       }
   }
   $feeders[999999]='Temp Employee';
   //lets check to see if we have any existing stats that we are editing
   $sql="SELECT * FROM jobs_inserter_rundata WHERE package_id=$packageid";
   $dbData=dbselectsingle($sql);
   if ($dbData['numrows']>0)
   {
       $data=$dbData['data'];
       $signoff=$data['signoff'];
       $setupby=explode("|",$data['setupby']);
       $runstart1=$data['runstart_one'];
       $runfinish1=$data['runfinish_one'];
       $firstbundle1=$data['firstbundle_one'];
       $lastbundle1=$data['lastbundle_one'];
       $firsttruck1=$data['firsttruck_one'];
       $lasttruck1=$data['lasttruck_one'];
       $runstart2=$data['runstart_two'];
       $runfinish2=$data['runfinish_two'];
       $firstbundle2=$data['firstbundle_two'];
       $lastbundle2=$data['lastbundle_two'];
       $firsttruck2=$data['firsttruck_two'];
       $lasttruck2=$data['lasttruck_two'];
       
       $feedercount=$data['feeders'];
       $runlength=$data['run_length'];
       $feedhours=$data['feed_hours'];
       $feederpiecesperhour=$data['feeder_pieces_per_hour'];
       $calcfeederpiecesperhour=$data['calc_feeder_pieces_per_hour'];
       $totalpiecesperhour=$data['total_pieces_per_hour'];
       $calctotalpiecesperhour=$data['calc_total_pieces_per_hour'];
       
       
       $employeecount=$data['employee_count'];
       $pallets=$data['pallets'];
       $leftover=$data['leftover_jackets'];
       $systemTotalPieces=$data['system_total_pieces'];
       $calcTotalPieces=$data['calc_total_pieces'];
       $dataid=$data['id'];
       $request=$package['inserter_request'];
       $address=$data['address'];
       $addresscount=$data['address_count'];
       $sticky=$data['sticky_note'];
       $stickycount=$data['sticky_note_count'];
       $doubleout=$data['doubleout'];
       $packagesbuilt=$data['packages_built']; 
       $pressrequest=$data['press_request'];
       $notes=$data['notes'];
       $hoppersused=$data['hoppers_used'];
       $calcHoppersUsed=$data['calc_hoppers_used'];
       $calcTotalStaff=$data['calc_total_staff'];
       $dockstaff=explode("|",$data['dock_staff']);
       $stackerstaff=explode("|",$data['stacker_staff']);
       $pressstaff=explode("|",$data['press_staff']);
       $presstemp=$data['press_temp'];
       $docktemp=$data['dock_temp'];
       $stackertemp=$data['stacker_temp'];
       $maildesk=$data['maildesk'];
       
       $temp=$data['other_equipment'];
       $temp=explode("|",$temp);
       $otherequipment=array();
       foreach($temp as $key=>$tf)
       {
           $otherequipment[]=$tf;    
       }
        
       
       $tempfeeders=explode("|",$hopperfeeders);
       $hopperfeeders=array();
       foreach($tempfeeders as $key=>$tf)
       {
           $tf=explode(",",$tf); 
           $hopperfeeders[$tf[0]]=$tf[1];    
       }
        
   } else {
       $setupby=array();                                        
       $signoff=0;
       $runstart1=$package['package_startdatetime'];
       $runfinish1=$package['package_stopdatetime'];
       $runstart2=$package['package_startdatetime'];
       $runfinish2=$package['package_stopdatetime'];
       $pressrequest=$package['inserter_request'];
       $firstbundle1=date("Y-m-d H:i",strtotime($package['package_startdatetime']." + 10 minutes"));
       $firsttruck1=date("Y-m-d H:i",strtotime($package['package_startdatetime']." + 15 minutes"));
       $lastbundle1=date("Y-m-d H:i",strtotime($package['package_stopdatetime']." - 10 minutes"));
       $lasttruck1=date("Y-m-d H:i",strtotime($package['package_stopdatetime']." - 5 minutes"));
       $firstbundle2=date("Y-m-d H:i",strtotime($package['package_startdatetime']." + 10 minutes"));
       $firsttruck2=date("Y-m-d H:i",strtotime($package['package_startdatetime']." + 15 minutes"));
       $lastbundle2=date("Y-m-d H:i",strtotime($package['package_stopdatetime']." - 10 minutes"));
       $lasttruck2=date("Y-m-d H:i",strtotime($package['package_stopdatetime']." - 5 minutes"));
       $dataid=0;
       $notes="";
       $hopperfeeders=array();
       $dockstaff=array();
       $stackerstaff=array();
       $pressstaff=array();
       $packagesbuilt=0;
       $pallets=0;
       $employeecount=0;
       $systemTotalPieces=0;
       $hoppersused=0;
       $leftover=0;
       $stickycount=0;
       $addresscount=0;
       $feedercount=0;
       $runlength=0;
       $feedhours=0;
       $feederpiecesperhour=0;
       $calcfeederpiecesperhour=0;
       $totalpiecesperhour=0;
       $calctotalpiecesperhour=0;
       $calcTotalStaff=0;
       $maildesk=0;
       $presstemp=0;
       $docktemp=0;
       $stackertemp=0;
   }
   
   
   print "<a href='#' onclick='window.print();return false;'><img src='artwork/printer.png' border=0 width=32>Print Report</a><br />\n";
   
   print "<div id='tabs'>\n"; //begins wrapper for tabbed content
   print "<form name='maildata' id='maildata' method=post>\n";
        
   print "<ul id='insertInfo'>\n";
       print "<li><a href='#requiredInfo'>Required Information</a></li>\n";   
       print "<li><a href='#stations'>Package Configuration</a></li>\n";   
       print "<li><a href='#personnel'>Personnel</a></li>\n";   
       print "<li><a href='#stats'>Statistics</a></li>\n";   
   print "</ul>\n";
        
   print "<div id='requiredInfo'>\n";
       print "<div class='label'>Scheduled Start</div><div class='input'>";
       print "Publication: $pubname - Package: $packagename<br />\n";
       print date("D, m/d/Y \@ H:i",strtotime($package['package_startdatetime']));
       print "</div><div class='clear'></div>\n";
       make_select('signoff',$operators[$signoff],$operators,'Insert Signoff');
       print "<div class='label'>Machine Setup By</div><div class='input'>\n";
        if (count($operators)>0)
        {
            foreach($operators as $id=>$name)
            {
                if ($name!='Please choose')
                {
                    if (in_array($id,$setupby))
                    {
                        $checked="checked";
                    }
                    print "<input type='checkbox' id='operator_$id' name='operator_$id' $checked /><label for='operator_$id'> $name</label><br />\n";
                }
                $checked="";
            }
        }
       print "</div><div class='clear'></div>\n";
       make_text('pressrequest',$pressrequest,'How many papers requested','How many papers were requested?',10,'',false,'','','','return isNumberKey(event);');
       make_text('packagesbuilt',$packagesbuilt,'How many papers produced','How many complete papers produced?',10,'',false,'','','','return isNumberKey(event);');
       make_text('employeecount',$employeecount,'How many employees','How many mailroom folks on shift?',10,'',false,'','','','return isNumberKey(event);');
       make_text('pallets',$pallets,'Shrink wrap','How many pallets were shrink wrapped?',10,'',false,'','','','return isNumberKey(event);');
       make_text('hoppers',$hoppersused,'How many stations used','Number of stations used to feed jacket and inserts?',10,'',false,'','','','return isNumberKey(event);');
       make_text('systemtotalpieces',$systemTotalPieces,'Insert Report Total Pieces','How many total pieces shown on inserter report?',10,'',false,'','','','return isNumberKey(event);');
       make_text('leftover',$leftover,'Leftover','How many left over jackets are there?',10,'',false,'','','','return isNumberKey(event);');
       make_checkbox('sticky',$sticky,'Sticky Note','Check if there was a sticky note');
       make_number('stickycount',$stickycount,'Sticky note count','Total count inserted');
       make_checkbox('address',$address,'Addressing','Check if we addressed the products');
       make_number('addresscount',$addresscount,'Addressing count','Total count addressed or labelled');
       if ($candoubleout)
       {
            make_checkbox('doubleout',$doubleout,'Doubleout','Check if we doubled-out');
       }
       print "<div style='float:left;width:400px;'>\n";
       print "<div class='label'>$inserter[side_one_name]</div><div class='input'></div><div class='clear'></div>\n";
       make_datetime('runstart1',$runstart1,'Run Start'); 
       make_datetime('runfinish1',$runfinish1,'Run Finish'); 
       make_datetime('firstbundle1',$firstbundle1,'First Bundle'); 
       make_datetime('lastbundle1',$lastbundle1,'Last Bundle'); 
       make_datetime('firsttruck1',$firsttruck1,'First Truck'); 
       make_datetime('lasttruck1',$lasttruck1,'Last Truck'); 
       print "</div>\n";
       
       
       if ($double)
       {
           $displaydouble='block';
       }else{
           $displaydouble='none';
       }
       make_hidden('displaydouble',$displaydouble);
       print "<div style='float:left;width:400px;display:$displaydouble'>\n";
       print "<div class='label'>$inserter[side_two_name]</div><div class='input'></div><div class='clear'></div>\n";
       make_datetime('runstart2',$runstart2,'Run Start'); 
       make_datetime('runfinish2',$runfinish2,'Run Finish'); 
       make_datetime('firstbundle2',$firstbundle2,'First Bundle'); 
       make_datetime('lastbundle2',$lastbundle2,'Last Bundle'); 
       make_datetime('firsttruck2',$firsttruck2,'First Truck'); 
       make_datetime('lasttruck2',$lasttruck2,'Last Truck'); 
       print "</div>\n";
       
       
   print "</div>\n";
   
   print "<div id='stations'>\n";
       print "<div class='ui-widget-content ui-corner-all' style='width:580px;padding:10px;font-weight:bold;'>
       Please double check that the insert is in the correct station. If not, click the Edit package button to edit the package, then click Reload package. After inserts are correct, specify who ran the station, and the actual number of inserts fed at the station. If an insert had to move during the run, just leave it in the station that it ran in for the majority of the run.
       </div>\n";
       print "<div style='float:left;width:70px;font-weight:bold;'>Station #</div>\n";
       print "<div style='float:left;width:300px;font-weight:bold;'>Details</div>\n";
       print "<div class='clear'></div>\n";
       print "<div id='packageholder' style='float:left;width:650px;'>\n";
       print "<img src='artwork/icons/ajax-loader.gif' style='margin-top:100px;margin-left:300px;'>\n";
       ?>
       <script>
       $(document).ready(function(){
        loadPackageOfInserts(); 
           
       })
       function loadPackageOfInserts()
       {
           $.ajax({
            url: 'includes/ajax_handlers/generateInsertPackageDisplay.php',
            data: {packageid:<?php echo $packageid; ?>,people:1,count:1,editing:true,maxwidth:600},
            dataType: 'html',
            success: function(response){
               $('#packageholder').html(response); 
            }
           })    
          
       }
       </script>
       <?php
       print "</div>\n";
       print "<div style='float:left;width:300px;margin-left:100px;'>\n";
       print "<a href='inserterPlanner.php?action=packages&planid=$planid&pubid=$pubid' target='_blank' class='button'>Edit package/inserts</a>\n";
       print "<br><br><input type='button' onclick='loadPackageOfInserts();' value='Reload package'>\n";
       
       //list other equipment tied to this inserter
       $sql="SELECT * FROM equipment WHERE equipment_tie_type='inserter' AND equipment_tie_id='$inserterid'";
       $dbOtherEquipment=dbselectmulti($sql);
       if($dbOtherEquipment['numrows']>0)
       {
           print "<p style='font-weight:bold;'>Select which of this other equipment was used on this run</p>\n";
           foreach($dbOtherEquipment['data'] as $oe)
           {
               if(in_array($oe['id'],$otherequipment))
               {
                   $checked='checked';
               } else {
                   $checked='';
               }
               print "<input type='checkbox' id='other_$oe[id]' name='other_$oe[id]' $checked /><label for='other_$oe[id]'>".stripslashes($oe['equipment_name'])."</label><br>\n";
           }
       } 
       
       print "</div>\n";
       print "<div class='clear'></div>\n";
   print "</div>\n";
   
   print "<div id='personnel'>\n";
       
       make_select('maildesk',$feeders[$maildesk],$feeders,'Mail Desk');
       //need a dock staff and stacker staff area, just show all staff members
       print "<div class='label'>Stacker Staff</div><div class='input'>\n";
           $col=round((count($feeders))/4,0);
           $i=0;
           print "<div style='float:left;width:180px;'>\n";
           foreach($feeders as $id=>$name)
           {
               if ($id!=0 && $id!=999999)
               {
                   if (in_array($id,$stackerstaff))
                   {
                       $checked='checked';
                   } else {
                       $checked='';
                   }
                   print "<input type='checkbox' id='stacker_$id' name='stacker_$id' $checked /><label for='stacker_$id'>$name</label><br>\n";
                   if ($i==$col)
                    {
                        print "</div>\n";
                        print "<div style='float:left;width:180px;'>\n";
                        $i=0;
                    } else {
                        $i++;
                    }
               }   
           }
           echo "# of temporay workers:<br />".input_text('tempstacker_staff',$stackertemp);
           print "</div>\n";
           print "<div class='clear'></div>\n"; 
           
       print "</div>\n";
       print "<div class='clear'></div>\n";
       
       print "<div class='label'>Dock Staff</div><div class='input'>\n";
           $col=round((count($feeders))/4,0);
           $i=0;
           print "<div style='float:left;width:180px;'>\n";
           foreach($feeders as $id=>$name)
           {
               if ($id!=0 && $id!=999999)
               {
                   if (in_array($id,$dockstaff))
                   {
                       $checked='checked';
                   } else {
                       $checked='';
                   }
                   
                   print "<input type='checkbox' id='dock_$id' name='dock_$id' $checked /><label for='dock_$id'>$name</label><br>\n";
                   if ($i==$col)
                    {
                        print "</div>\n";
                        print "<div style='float:left;width:180px;'>\n";
                        $i=0;
                    } else {
                        $i++;
                    }
               }   
           }
           echo "# of temporay workers:<br />".input_text('tempdock_staff',$docktemp);
           print "</div>\n";
           print "<div class='clear'></div>\n"; 
           
       print "</div>\n";
       print "<div class='clear'></div>\n"; 
       
       
       print "<div class='label'>Press Stacker Staff</div><div class='input'>\n";
           $col=round((count($feeders))/4,0);
           $i=0;
           print "  <div style='float:left;width:180px;'>\n";
           foreach($feeders as $id=>$name)
           {
               if ($id!=0 && $id!=999999)
               {
                   if (in_array($id,$pressstaff))
                   {
                       $checked='checked';
                   } else {
                       $checked='';
                   }
                   
                   print "    <input type='checkbox' id='press_$id' name='press_$id' $checked /><label for='press_$id'>$name</label><br>\n";
                   if ($i==$col)
                    {
                        print "  </div>\n";
                        print "  <div style='float:left;width:180px;'>\n";
                        $i=0;
                    } else {
                        $i++;
                    }
               }   
           }
           echo "# of temporay workers:<br />".input_text('temppress_staff',$presstemp);
           print "  </div>\n";
           print "  <div class='clear'></div>\n"; 
           
       print "</div>\n";
       print "<div class='clear'></div>\n"; 
       
   print "</div>\n";
   
   print "<div id='stats'>\n";
       print "<h2>Calculated Stats</h2>\n";
       
       print "Total Hoppers Used: $calcHoppersUsed<br>\n";
       print "Total Calculated Pieces: $calcTotalPieces<br>\n";
       print "Run length: $runlength mins.<br>\n";
       print "Feed hours: $feedhours<br>\n";
       print "<b>With just feeders being counted:</b><br>\n";
       print "Feeders: $feedercount<br>\n";
       print "Calc pieces/hour: $calcfeederpiecesperhour<br>\n";
       print "<b>Counting all specified mailroom staff:</b><br>\n";
       print "Total calculated staff: $calcTotalStaff<br>\n";
       print "Calc pieces/hour: $calctotalpiecesperhour<br>\n";
   print "</div>\n";
   
   make_hidden('package_startdatetime',$package['package_startdatetime']);
   make_hidden('package_stopdatetime',$package['package_stopdatetime']);
   make_hidden('dataid',$dataid);
   make_hidden('pubdate',$pubdate);
   make_hidden('pubid',$pubid);
   make_hidden('planid',$planid);
   make_hidden('packageid',$packageid);
   make_hidden('calcpieces',$insertcount);
   if($_GET['popup'])
    {
        make_hidden('popout','true');
    }
   
   /************************************************************************
   *  @TODO: need to build in some error checking here and enable the submit button only then...
   */
   print "<div class='clear'></div>\n";
   print "</div>\n";
   make_submit('submit','Save Data');
   print "</form>\n";
   ?>
        <script type='text/javascript'>
        $(function() {
            $( '#tabs' ).tabs();
        });
        </script>
    <?php 
}

function save_inserterData()
{
    $pubid=$_POST['pubid'];
    $packageid=$_POST['packageid'];
    $planid=$_POST['planid'];
    $dataid=$_POST['dataid'];
    if ($_POST['doubleout']){$doubleout=1;}else{$doubleout=0;}
    if ($_POST['address']){$address=1;}else{$address=0;}
    if ($_POST['sticky']){$sticky=1;}else{$sticky=0;}
    $stickycount=intval($_POST['stickycount']);
    $addresscount=intval($_POST['addresscount']);
    $hoppers=$_POST['hoppers'];
    $calcpieces=$_POST['calcpieces'];
    $pressrequest=$_POST['pressrequest'];
    $packagesbuilt=$_POST['packagesbuilt'];
    $employeecount=$_POST['employeecount'];
    $systemtotalpieces=$_POST['systemtotalpieces'];
    $pallets=$_POST['pallets'];
    $leftover=$_POST['leftover'];
    $signoff=$_POST['signoff'];
    $maildesk=$_POST['maildesk'];
    $presstemp=$_POST['temppress_staff'];
    $stackertemp=$_POST['tempstacker_staff'];
    $docktemp=$_POST['tempdock_staff'];
    $pressmanids="";
    //find pressman now
    $setupby="";
    $dockstaff="";
    $hopperfeeders="";
    $stackerstaff="";
    $pressstaff="";
    $calcStaff=0;
    $feedcount=0;
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,9)=="operator_")
        {
            $key=str_replace("operator_","",$key);
            $setupby.="$key|";
            $calcStaff++;    
        }
        if (substr($key,0,5)=="dock_")
        {
            $key=str_replace("dock_","",$key);
            $dockstaff.="$key|";
            $calcStaff++;    
        }
        if (substr($key,0,8)=="stacker_")
        {
            $key=str_replace("stacker_","",$key);
            $stackerstaff.="$key|";
            $calcStaff++;    
        }
        if (substr($key,0,6)=="press_")
        {
            $key=str_replace("press_","",$key);
            $pressstaff.="$key|";
            $calcStaff++;    
        }
        if (substr($key,0,6)=="other_")
        {
            $key=str_replace("other_","",$key);
            $otherequipment.="$key|";
        }
    }
    $notes=addslashes(str_replace("<input type=\"hidden\" /><!--Session data--><input type=\"hidden\" />","",$_POST['notes']));
    $setupby=substr($setupby,0,strlen($setupby)-1);
    $dockstaff=substr($dockstaff,0,strlen($dockstaff)-1);
    $stackerstaff=substr($stackerstaff,0,strlen($stackerstaff)-1);
    $pressstaff=substr($pressstaff,0,strlen($pressstaff)-1);
    
    //add the temp staff to the total count
    $calcStaff=$calcStaff+$presstemp+$stackertemp+$docktemp;
    if($maildesk!=0){$calcStaff++;}
    
    
    $packagestartdatetime=$_POST['package_startdatetime'];
    $packagestopdatetime=$_POST['package_stopdatetime'];
    $runstart1=$_POST['runstart1'];
    $runfinish1=$_POST['runfinish1'];
    $firstbundle1=$_POST['firstbundle1'];
    $lastbundle1=$_POST['lastbundle1'];
    $firsttruck1=$_POST['firsttruck1'];
    $lasttruck1=$_POST['lasttruck1'];
    if($_POST['displaydouble']=='none')
    {
        if($_POST['runstart2']=='')
        {
            $runstart2=$runstart1;
        } else {
            $runstart2=$_POST['runstart2'];
        }
        if($_POST['runfinish2']=='')
        {
            $runfinish2=$runfinish1;    
        } else {
            $runfinish2=$_POST['runfinish2'];
        }
        if($_POST['firstbundle2']=='')
        {
            $firstbundle2=$firstbundle1;    
        } else {
            $firstbundle2=$_POST['firstbundle2'];
        }
        if($_POST['lastbundle2']=='')
        {
            $lastbundle2=$lastbundle1;    
        } else {
            $lastbundle2=$_POST['lastbundle2'];
        }
        if($_POST['firsttruck2']=='')
        {
            $firsttruck2=$firsttruck1;
        } else {
            $firsttruck2=$_POST['firsttruck2'];
        }
        if($_POST['lasttruck2']=='')
        {
            $lasttruck2=$lasttruck1;
        } else {
            $lasttruck2=$_POST['lasttruck2'];
        }
    } else {
        $runstart2=$_POST['runstart1'];
        $runfinish2=$_POST['runfinish1'];
        $firstbundle2=$_POST['firstbundle1'];
        $lastbundle2=$_POST['lastbundle1'];
        $firsttruck2=$_POST['firsttruck1'];
        $lasttruck2=$_POST['lasttruck1'];
    }
    $s1=strtotime($runstart1);
    $f1=strtotime($runfinish1);
    $s2=strtotime($runstart2);
    $f2=strtotime($runfinish2);
    if ($f2>$f1)
    {
        $fin=$f2;
    } else {
        $fin=$f1;
    }
    if ($s2>$s1)
    {
        $start=$s1;
    } else {
        $start=$s2;
    }
    $runlength=round(($fin-$start)/60,2); //run time in minutes
    
    
    //now look for station information
    $stationvalues="";
    $calchoppers=0;
    $feeders=array();
    foreach($_POST as $key=>$value)
    {
        if(substr($key,0,8)=='station_')
        {
            //this post item holds the station insert id, so it only flags on those stations with an assigned insert
            
            $keyparts=explode("_",$key);
            $stationid=$keyparts[1];
            $iid=$value; //"J" is passed for a generic jacket
            $personid=$_POST['stationd_'.$stationid.'_person'];
            $quantity=$_POST['stationd_'.$stationid.'_count'];
            if($iid=='' || $iid=='J'){$iid=0;}
            if($personid==''){$personid=0;}
            if($stationid==''){$stationid=0;}
            if($quantity==''){$quantity=0;}
            $runningQuantity+=$quantity;
            if($iid!=0)
            {
                $stations[]=$stationid;
                
            }
            if($quantity>0){
                $calchoppers++;
            }
            if(!in_array($personid,$feeders)){$feeders[]=$personid;$feedcount++;$calcStaff++;}
            $stationvalues.="('$packageid', '$stationid', '$iid', '$personid', '$quantity'),";
        }
    }
    
    //update run data with the runningQuantity for the calc_pieces factor
    $calcpieces=$runningQuantity;
    
     
    $stationvalues=substr($stationvalues,0,strlen($stationvalues)-1);
    //delete any existing
    $sql="DELETE FROM jobs_inserter_rundata_stations WHERE package_id='$packageid'";
    $dbDelete=dbexecutequery($sql);
    $error.=$dbDelete['error'];
    //add the new ones
    if($stationvalues!='')
    {
        $sql="INSERT INTO jobs_inserter_rundata_stations (package_id, station_id, insert_id, user_id, quantity) VALUES $stationvalues";
        $dbInsert=dbinsertquery($sql);
        $error.=$dbInsert['error'];
    }
    
    
    
    
    $feedhours=round($runlength*$feedcount/60,2);
    $totalhours=round($runlength*$calcStaff/60,2);
    if ($feedhours!=0)
    {
        $feederpiecesperhour=round($systemtotalpieces/$feedhours,2);
        $calcfeederpiecesperhour=round($calcpieces/$feedhours,2); 
    } else {
        $piecesperhour=0;
        $calcpiecesperhour=0;
    }
    if ($totalhours!=0)
    {
        $totalpiecesperhour=round($systemtotalpieces/$totalhours,2);
        $calctotalpiecesperhour=round($calcpieces/$totalhours,2);
    } else {
        $totalpiecesperhour=0;
        $calctotalpiecesperhour=0;
    }
    
    if($calctotalpiecesperhour==''){$calctotalpiecesperhour=0;}
    if($leftover==''){$leftover=0;}
    if($hopperfeeders==''){$hopperfeeders=0;}
    if($dockstaff==''){$dockstaff=0;}
    if($stackerstaff==''){$stackerstaff=0;}
    if($feedhours==''){$feedhours=0;}
    if($feedcount==''){$feedcount=0;}
    if($feederpiecesperhour==''){$feederpiecesperhour=0;}
    if($calcfeederpiecesperhour==''){$calcfeederpiecesperhour=0;}
    if($totalpiecesperhour==''){$totalpiecesperhour=0;}
    if($runlength==''){$runlength=0;}
    if($presstemp==''){$presstemp=0;}
    if($stackertemp==''){$stackertemp=0;}
    if($docktemp==''){$docktemp=0;}
    if($maildesk==''){$maildesk=0;}
    if($pallets==''){$pallets=0;}
    if($systemtotalpieces==''){$systemtotalpieces=0;}
    if($calcpieces==''){$calcpieces=0;}
    if($pressrequest==''){$pressrequest=0;}
    if($packagesbuilt==''){$packagesbuilt=0;}
    
    
    
    
    
    
    if ($dataid==0)
    {
        $sql="INSERT INTO jobs_inserter_rundata (pub_id, package_id, plan_id, sticky_note, address, 
        doubleout, hoppers_used, leftover_jackets, employee_count, pallets, system_total_pieces, 
        calc_total_pieces, press_request, packages_built, signoff, setupby, package_startdatetime, 
        package_stopdatetime, runstart_one, runfinish_one, firstbundle_one, lastbundle_one, firsttruck_one, 
        lasttruck_one, runstart_two, runfinish_two, firstbundle_two, lastbundle_two, firsttruck_two, lasttruck_two, 
        notes, dock_staff, stacker_staff, feed_hours, feeders, feeder_pieces_per_hour, 
        calc_feeder_pieces_per_hour,total_pieces_per_hour, calc_total_pieces_per_hour, run_length, press_temp, 
        stacker_temp, dock_temp, maildesk, other_equipment, calc_hoppers_used, calc_total_staff, sticky_note_count, address_count) VALUES ('$pubid', '$packageid', '$planid', '$sticky', '$address', 
        '$doubleout', '$hoppers', '$leftover', '$employeecount', '$pallets', '$systemtotalpieces', '$calcpieces', 
        '$pressrequest', '$packagesbuilt', '$signoff', '$setupby', '$packagestartdatetime', '$packagestopdatetime', 
        '$runstart1', '$runfinish1', '$firstbundle1', '$lastbundle1', '$firsttruck1', '$lasttruck1', '$runstart2', 
        '$runfinish2', '$firstbundle2','$lastbundle2', '$firsttruck2', '$lasttruck2', '$notes', 
        '$dockstaff', '$stackerstaff', '$feedhours', '$feedcount', '$feederpiecesperhour', 
        '$calcfeederpiecesperhour', '$totalpiecesperhour', '$calctotalpiecesperhour', '$runlength', '$presstemp', 
        '$stackertemp', '$docktemp', '$maildesk', '$otherequipment', '$calchoppers', '$calcStaff', '$stickycount', '$addresscount')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error']; 
    } else {
        $sql="UPDATE jobs_inserter_rundata SET pub_id='$pubid', package_id='$packageid', plan_id='$planid', 
        sticky_note='$sticky', dock_staff='$dockstaff', press_staff='$pressstaff', calc_total_staff='$calcStaff', 
        stacker_staff='$stackerstaff', address='$address', doubleout='$doubleout', hoppers_used='$hoppers', 
        leftover_jackets='$leftover', employee_count='$employeecount', pallets='$pallets', 
        system_total_pieces='$systemtotalpieces', calc_total_pieces='$calcpieces', press_request='$pressrequest', 
        packages_built='$packagesbuilt', signoff='$signoff', setupby='$setupby', calc_hoppers_used='$calchoppers',
        package_startdatetime='$packagestartdatetime', package_stopdatetime='$packagestopdatetime', 
        runstart_one='$runstart1', runfinish_one='$runfinish1', firstbundle_one='$firstbundle1', 
        lastbundle_one='$lastbundle1', firsttruck_one='$firsttruck1', lasttruck_one='$lasttruck1', 
        runstart_two='$runstart2', runfinish_two='$runfinish2', firstbundle_two='$firstbundle2', 
        lastbundle_two='$lastbundle2', firsttruck_two='$firsttruck2', lasttruck_two='$lasttruck2', 
        notes='$notes', run_length='$runlength', feed_hours='$feedhours', feeders='$feedcount', 
        feeder_pieces_per_hour='$feederpiecesperhour', calc_feeder_pieces_per_hour='$calcfeederpiecesperhour', 
        total_pieces_per_hour='$totalpiecesperhour', calc_total_pieces_per_hour='$calctotalpiecesperhour', 
        press_temp='$presstemp', stacker_temp='$stackertemp', dock_temp='$docktemp', maildesk='$maildesk', 
        other_equipment='$otherequipment', sticky_note_count='$stickycount', address_count='$addresscount' WHERE id=$dataid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    
    
    
    //now apply wear and tear to this station
    if(count($stations)>0)
    {
        inserterWearTear($packageid);
    }
    
    
    if ($error!='')
    {
        setUserMessage('There was a problem saving the insert run data.<br>'.$error,'error');
    } else {
        setUserMessage('Insert run data has been successfully saved.','success');
    }
    if($_POST['popout']=='true')
    {
        print "<script>window.close();</script>";
    } else {
       redirect("?action=list");
    }
}


footer(); 
?>       