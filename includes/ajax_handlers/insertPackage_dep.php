<?php
//insert package ajax handler script
include("../functions_db.php");  
include("../functions_formtools.php");  
include("../config.php");  
include("../common.php");  
if($_POST)
{
    $action=$_POST['action'];
} else {
    $action=$_GET['action'];
}
 
switch($action)
{
    case "insert":
    save_package('insert');
    break;
    
    case "update":
    save_package('update');
    break;
    
    case "delete":
    delete_package();
    break;
    
    case "inserterinfo":
    inserterInfo();
    break;
    
    case 'saveinsert':
    save_insert();
    break;
    
    case 'removeinsert':
    remove_insert();
    break;
    
    case 'savejacket':
    save_jacket();
    break;
    
    case 'removejacket':
    remove_jacket();
    break;
    
    case "savesettings":
    save_package_settings();
    break;
    
} 


function save_package_settings()
{
    $settingid=$_POST['settingid'];
    if($_POST['reject_misses']){$misses=1;}else{$misses=0;}
    if($_POST['reject_doubles']){$doubles=1;}else{$doubles=0;}
    $miss_fault=$_POST['miss_fault'];
    $double_fault=$_POST['double_fault'];
    $attempt_repair=$_POST['attempt_repair'];
    $gap=$_POST['gap'];
    $delivery=$_POST['delivery'];
    $copies_per_bundle=$_POST['copies_per_bundle'];
    $turns=$_POST['turns'];
    $sql="UPDATE jobs_inserter_packages_settings SET miss_fault='$miss_fault', double_fault='$double_fault', attempt_repair='$attempt_repair', gap='$gap', delivery='$delivery', copies_per_bundle='$copies_per_bundle', turns='$turns' WHERE id=$settingid";
    $dbUpdate=dbexecutequery($sql);
    print $dbUpdate['error'];
}

function save_jacket()
{
    $insertid=$_POST['insertid'];
    $packageid=$_POST['packageid'];
    $planid=$_POST['planid'];
    $sql="UPDATE jobs_inserter_packages SET jacket_insert_id='$insertid' WHERE id='$packageid'";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    $sql="UPDATE inserts SET plan_id='$planid', package_id='$packageid' WHERE id='$insertid'";
    $dbUpdate=dbexecutequery($sql);
    $error.=$dbUpdate['error'];
    if($error=='')
    {
        print "success|";
    } else {
        print "error|$error";
    }
    
}

function remove_jacket()
{
    $insertid=$_POST['insertid'];
    $packageid=$_POST['packageid'];
    $sql="UPDATE jobs_inserter_packages SET jacket_insert_id='0' WHERE id='$packageid'";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    $sql="UPDATE jobs_inserter_packages SET jacket_insert_id='0' WHERE jacket_insert_id='$insertid'";
    $dbUpdate=dbexecutequery($sql);
    $error.=$dbUpdate['error'];
    $sql="UPDATE inserts SET plan_id='0', package_id='0' WHERE id='$insertid'";
    $dbUpdate=dbexecutequery($sql);
    $error.=$dbUpdate['error'];
    if($error=='')
    {
        print "success|";
    } else {
        print "error|$error";
    }
    
}


function save_insert()
{
    $insertid=$_POST['insertid'];
    $planid=$_POST['planid'];
    $packageid=$_POST['packageid'];
    $tabpages=$_POST['tabpages'];
    $weight=$_POST['pieceweight'];
    
    //we need to check to see if we are dealing with a package being placed into another package
    if(substr($insertid,0,2)=='p-')
    {
        $pid=explode("-",$insertid);
        $pid=$pid[1];
        $t=explode("_",$tabpages); //should be in the format insertcount_pagecount
        
        //ok, we are handed a package which is going to become a sub-package
        //look up the info from the package
        $sql="SELECT * FROM jobs_inserter_packages WHERE id=$pid";
        $dbPackage=dbselectsingle($sql);
        $package=$dbPackage['data'];
        $epackagecount=$package['total_inserts'];
        $epackageweight=$package['total_weight'];
        $epackagepages=$package['tab_pages'];
        
        //now, create a sub package
        $sql="INSERT INTO jobs_inserter_packages_sub (package_parent_id, package_id) VALUES ('$packageid', '$pid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        
        //finally update the package it was dropped onto with the new info
        $sql="UPDATE jobs_inserter_packages SET total_inserts=total_inserts+$epackagecount, tab_pages=tab_pages+$epackagepages, total_weight=total_weight+$epackageweight WHERE id='$packageid'"; 
        $dbUpdate=dbexecutequery($sql);
        $error.=$dbUpdate['error'];
        
        
    } else {
        $sql="UPDATE inserts SET plan_id='$planid', package_id='$packageid' WHERE id='$insertid'";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        $sql="UPDATE jobs_inserter_packages SET total_inserts=total_inserts+1, tab_pages=tab_pages+$tabpages, total_weight=total_weight+$weight WHERE id='$packageid'"; 
        $dbUpdate=dbexecutequery($sql);
        $sql="UPDATE jobs_inserter_packages SET jacket_insert_id=0 WHERE jacket_insert_id='$insertid'"; // this should clear away any jacket settings
        $dbUpdate=dbexecutequery($sql);
        $error.=$dbUpdate['error'];
    }
    if($error=='')
    {
        print "success|";
    } else {
        print "error|$error";
    }
}

function remove_insert()
{
    $insertid=$_POST['insertid'];
    $tabpages=$_POST['tabpages'];
    $packageid=$_POST['packageid'];
    $weight=$_POST['pieceweight'];
    if(substr($insertid,0,2)=='p-')
    {
        $pid=explode("-",$insertid);
        $pid=$pid[1];
        //get info about the sub package
        $sql="SELECT * FROM jobs_inserter_packages WHERE id=$pid";
        $dbSub=dbselectsingle($sql);
        $package=$dbPackage['data'];
        $epackagecount=$package['total_inserts'];
        $epackageweight=$package['total_weight'];
        $epackagepages=$package['tab_pages'];
        
        //now remove the sub package
        $sql="DELETE FROM jobs_inserter_packages_sub WHERE package_parent_id=$packageid AND package_id=$pid";
        $dbDelete=dbexecutequery($sql);
        
        //update the package info
        $sql="UPDATE jobs_inserter_packages SET total_inserts=total_inserts-$epackagecount, tab_pages=tab_pages-$epackagepages, total_weight=total_weight-$epackageweight WHERE id='$packageid'"; 
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error']; 
    } else {
        $sql="UPDATE inserts SET package_id=0, plan_id=0 WHERE id='$insertid'";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        
        $sql="UPDATE jobs_inserter_packages SET total_inserts=total_inserts-1, tab_pages=tab_pages-$tabpages, total_weight=total_weight-$weight WHERE id='$packageid'"; 
        $dbUpdate=dbexecutequery($sql);
        $error.=$dbUpdate['error'];
    }
    if($error=='')
    {
        print "success|";
    } else {
        print "error|$error";
    }
}

function inserterInfo()
{
    $inserterid=intval($_POST['inserterid']);
    $id=intval($_POST['id']);
    $sql="SELECT * FROM inserters WHERE id='$inserterid'";
    $dbInserter=dbselectsingle($sql);
    $inserter=$dbInserter['data'];
    $singleout=$inserter['inserter_single_out'];
    $doubleout=$inserter['inserter_double_out'];
    if($inserter['can_double_out']==1)
    {
        print "choice|$singleout|$doubleout";
    } else {
        print "Single out|$singleout";
    } 
}

function save_package()
{
    global $inserterspeed;
    $action=$_POST['action'];
    $jacketid=$_POST['jacketid'];
    $packageid=$_POST['packageid'];
    $pubid=$_POST['pubid'];
    $planid=$_POST['planid'];
    $inserterid=$_POST['inserterid'];
    $pubdate=$_POST['pubdate'];
    $runquantity=$_POST['runquantity'];
    $request=$_POST['planrequest'];
    if ($runquantity==''){$runquantity=0;}
    if ($request==''){$request=0;}
    $name=addslashes($_POST['name']);
    if ($name==''){$name='New package';}
    $startdatetime=$_POST['datetime'];
    $doubleout=$_POST['doubleout'];
    $hoppers=$_POST['hoppers'];
    $packagedate=date("Y-m-d",strtotime($startdatetime));
    //now we need to calculate the finish time
    if ($doubleout)
    {
        $runtime=$request/($GLOBALS['inserterSpeedDouble']/60); //this should give us a number of minutes;
        $runtime=round($runtime,0);
        $runtime+=$GLOBALS['inserterSetup'];
    } else {
        $runtime=$request/($GLOBALS['inserterSpeedSingle']/60); //this should give us a number of minutes;
        $runtime=round($runtime,0);
        $runtime+=$GLOBALS['inserterSetup'];
    }
    if ($runtime<30){$runtime=30;}
    $stopdatetime=date("Y-m-d H:i",strtotime($startdatetime." +$runtime minutes"));
    //print "estimated stoptime $stopdatetime";
    if ($action=='insert')
    {
        $sql="INSERT INTO jobs_inserter_packages (inserter_id, plan_id, pub_date, package_date, package_startdatetime, package_stopdatetime, package_name, package_runlength, double_out, pub_id, jacket_insert_id, inserter_request, hoppers, total_inserts, tab_pages, total_weight) VALUES ('$inserterid','$planid', '$pubdate', '$packagedate', '$startdatetime', '$stopdatetime', '$name', '$runtime', '$doubleout', '$pubid', '$jacketid', '$runquantity', '$hoppers', '0', '0', '0.00')";
         $dbInsert=dbinsertquery($sql);
         $error=$dbInsert['error'];
         $packageid=$dbInsert['insertid'];
         
         //set up the package settings
         $sql="SELECT * FROM inserters WHERE id=$inserterid";
         $dbInserter=dbselectsingle($sql);
         $inserter=$dbInserter['data'];
         $sql="INSERT INTO jobs_inserter_packages_settings (package_id, reject_misses, reject_doubles, miss_fault, double_fault, attempt_repair, gap, delivery, copies_per_bundle, turns) VALUES ('$packageid', '$inserter[reject_misses]','$inserter[reject_doubles]','$inserter[miss_fault]','$inserter[double_fault]','$inserter[attempt_repair]','$inserter[gap]','$inserter[delivery]','$inserter[copies_per_bundle]','$inserter[turns]')";
         $dbInsert=dbinsertquery($sql);
             
    } else {
       $sql="UPDATE jobs_inserter_packages SET inserter_id='$inserterid', pub_date='$pubdate', package_startdatetime='$startdatetime', package_stopdatetime='$stopdatetime', package_name='$name', package_date='$packagedate', inserter_request='$runquantity', jacket_insert_id='$jacketid', double_out='$doubleout', pub_id='$pubid', hoppers='$hoppers' WHERE id=$packageid";
       $dbUpdate=dbexecutequery($sql);
       $error=$dbUpdate['error']; 
    }
    
    
    //update the jacket insert with the package id
    $sql="UPDATE inserts SET package_id='$packageid', plan_id='$planid' WHERE id='$jacketid'";
    $dbUpdate=dbexecutequery($sql);
    if($error=='')
    {
        print "success|$packageid";
    } else {
        print "error|".$error;
    }
    
}

function delete_package()
{
     $packageid=$_POST['packageid'];
     $sql="DELETE FROM jobs_inserter_packages WHERE id=$packageid";
     $dbDelete=dbexecutequery($sql);
     $error=$dbDelete['error'];
     
     //now we also want to clear any inserts that may be in this package
     $sql="UPDATE inserts SET package_id=0, plan_id=0 WHERE package_id=$packageid";
     $dbUpdate=dbexecutequery($sql);
     $error.=$dbUpdate['error'];
     
     //delete any packages that might be subs of this one
     $sql="DELETE FROM jobs_inserter_packages_sub WHERE package_parent_id=$packageid";
     $dbDelete=dbexecutequery($sql);
     $error.=$dbDelete['error'];
                        
     
     if($error=='')
     {
        print "success|";
     } else {
        print "error|$error";
     }
}
dbclose();
?>
