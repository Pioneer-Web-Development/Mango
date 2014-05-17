<?php
//<!--VERSION: .5 ||**||-->//
//this script is designed to build run files for a miracom based inserter
//it will generate 3 files for the miracom system
// name format is pub_code-pub_date-package_name.
// P.txt  -- products -- contains the inserts for the run
// X.txt  -- packages -- basically the zoning
// O.txt  -- orders -- this is trucks/routes/zones (grouping of papers)


/******************************************************************
* file descriptions:
* 
* products file:
* field             description             datatype
* NAME              Product(insert) name    String
* REJECT MISSES     Flag to indicate        Bit 0/1
*                   accept/reject misses
* REJECT DOUBLES    Flag to indicate        Bit 0/1
*                   accept/reject doubles
* MISS FAULT COUNT  Number of consecutive   Number
*                   misses to cause a fault
* DOUBLE FAULT COUNT Numberof consecutive   Number
*                   doubles to cause a fault
* ATTEMPT REPAIR    Number of times to      Number
*                   attempt to repair this
*                   product
* 
* Sample line
* 
* Jacket,1,1,0,3,2
* 
* 
* packages file:
* field             description             data type
* PACKAGE NAME      Package Name            String
* PRODUCT NAME      Product Name            String
* 
* sample line:
* FULL RUN, Jacket
* (duplicate for each insert in this package)
* 
* 
* orders file:
* field             description             data type
* ORDER NAME        Order Name (id)-Route   String
* PACKAGE NAME      Package Name - ties     String
*                   into the packages file
* DRAW              Number of copies for    Number
*                   this order (route)
* GAP               Gap size between routes Number
*                   (spacing in gripper)
* DELIVERY          Output delivery id      Number
*                   1=Primary, 2=Secondary
* SEQ               Sequence Number         Number
*                   order of output
* CPB               Copies per bundle       Number
* TURNS             Number of turns in      Number
*                   bundle (0 is no turns)
* 
* sample file:
* MR2031,FULL,1949,30,1,1,30,1
* 
*/
if($_GET['action']=='generate')
{
    include("includes/functions_db.php");
    $type=$_GET['type'];
    switch ($type)
    {
        case "product":
        build_product_file();
        break;
        
        case "package":
        build_package_file();
        break;
        
        case "order":
        build_order_file();
        break;
        
    }
} else {
    include("includes/mainmenu.php");
     //make sure we have a logged in user...
    if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}
    print "<body>\n<div id='wrapper'>\n";
    if ($_POST['submit'])
    {
        //update the settings for this file
        $packageid=$_POST['package_id'];
        if($_POST['rejectMisses']){$rejectMisses=1;}else{$rejectMisses=0;}
        if($_POST['rejectDoubles']){$rejectDoubles=1;}else{$rejectDoubles=0;}
        $missFault=$_POST['missFault'];
        $doubleFault=$_POST['doubleFault'];
        $attemptRepair=$_POST['attemptRepair'];
        $gap=$_POST['gap'];
        $delivery=$_POST['delivery'];
        $copiesPerBundle=$_POST['copiesPerBundle'];
        $turns=$_POST['turns'];
        $settingid=$_POST['settingid'];
        if ($settingid==0)
        {
            $sql="INSERT INTO jobs_inserter_packages_settings (package_id, turns, copies_per_bundle, reject_misses, reject_doubles, miss_fault, double_fault,attempt_repair, gap, delivery) VALUES('$packageid', '$turns', '$copiesPerBundle', '$rejectMisses', '$rejectDoubles', '$missFault', '$doubleFault', '$attemptRepair', '$gap', '$delivery')";
            $dbInsert=dbinsertquery($sql);
        } else {
            $sql="UPDATE jobs_inserter_packages_settings SET turns='$turns', copies_per_bundle='$copiesPerBundle', reject_misses='$rejectMisses', reject_doubles='$rejectDoubles', miss_fault='$missFault', double_fault='$doubleFault', attempt_repair='$attemptRepair', gap='$gap', delivery='$delivery' WHERE id=$settingid";
            $dbUpdate=dbexecutequery($sql);
        }
        
        //display the links to the files
        print "<div style='padding-left:20px;padding-top:10px;'>\n";
        print "<h2>Click on the links below to download the appropriate files for the inserter:</h2>\n";
        print "&#187;<a href='?action=generate&type=order&packageid=$packageid'>Order File</a><br />\n";
        print "&#187;<a href='?action=generate&type=product&packageid=$packageid'>Product File</a><br />\n";
        print "&#187;<a href='?action=generate&type=package&packageid=$packageid'>Package File</a><br />\n";
        
        print "</div>\n";
            
    } elseif ($_GET['action']=='init')
    {
        //display the setup form
        $packageid=$_GET['packageid'];
        //see if there is any existing settings for this package, otherwise, get the defaults
        $sql="SELECT * FROM jobs_inserter_packages_settings WHERE package_id=$packageid";
        $dbSettings=dbselectsingle($sql);
        if ($dbSettings['numrows']>0)
        {
            $settings=$dbSettings['data'];
            $rejectMisses=$settings['reject_misses']; 
            $rejectDoubles=$settings['reject_doubles']; 
            $missFault=$settings['miss_fault']; 
            $doubleFault=$settings['double_fault']; 
            $attemptRepair=$settings['attempt_repair']; 
            $gap=$settings['gap']; 
            $turns=$settings['turns']; 
            $delivery=$settings['delivery']; 
            $copiesPerBundle=$settings['copies_per_bundle'];
            $settingid=$settings['id']; 
        } else {
            //load defaults from the system preference file
            global $siteID;
            $settingid=0;
            $sql="SELECT * FROM core_preferences WHERE site_id=$siteID";
            $dbPrefs=dbselectsingle($sql);
            $settings=$dbPrefs['data'];
            $rejectMisses=$settings['miracom_reject_misses']; 
            $rejectDoubles=$settings['miracom_reject_doubles']; 
            $missFault=$settings['miracom_miss_fault']; 
            $doubleFault=$settings['miracom_double_fault']; 
            $attemptRepair=$settings['miracom_attempt_repair']; 
            $gap=$settings['miracom_gap']; 
            $turns=$settings['miracom_turns']; 
            $delivery=$settings['miracom_delivery']; 
            $copiesPerBundle=$settings['miracom_copies_per_bundle'];
        }
        print "<form method=post>\n";
        make_checkbox('rejectMisses',$rejectMisses,'Reject Misses','Check to reject misses');
        make_number('missFault',$missFault,'Miss count','Number of misses before rejecting');
        make_checkbox('rejectDoubles',$rejectDoubles,'Reject Doubles','Check to reject doubles');
        make_number('doubleFault',$doubleFault,'Double count','Number of doubles before rejecting');
        make_number('attemptRepair',$attemptRepair,'Repair Attemps','Number of times to attempt repairing before ejecting');
        make_number('gap',$gap,'Gap between routes','Number of empty grippers to set between routes.');
        $inserterDeliveries=array(1=>"Primary",2=>"Secondary");
        make_select('delivery',$inserterDeliveries[$delivery],$inserterDeliveries,'Default delivery','Set default deliver for packages');
        make_number('copiesPerBundle',$copiesPerBundle,'Default copies per bundle','Set the default number of papers in a bundle.');
        make_number('turns',$turns,'Number of turns in a bundle','0 indicates no turns, 1 is for one turn (ex: bundle of 40, 1 turn equals two 20\'s)');
        make_hidden('package_id',$packageid);
        make_hidden('setting_id',$settingid);
        make_submit('submit','Save settings');
        print "</form>\n";
    }
    print "</div>\n";
    print "</body></html>\n";
}

function build_product_file()
{
    
    /******************************************************************
    * file descriptions:
    *
    * 
    * products file:
    * field             description             datatype
    * NAME              Product(insert) name    String
    * REJECT MISSES     Flag to indicate        Bit 0/1
    *                   accept/reject misses
    * REJECT DOUBLES    Flag to indicate        Bit 0/1
    *                   accept/reject doubles
    * MISS FAULT COUNT  Number of consecutive   Number
    *                   misses to cause a fault
    * DOUBLE FAULT COUNT Numberof consecutive   Number
    *                   doubles to cause a fault
    * ATTEMPT REPAIR    Number of times to      Number
    *                   attempt to repair this
    *                   product
    * 
    * Sample line
    * 
    * Jacket,1,1,0,3,2
    */
    //ok, we'll need pub info, inserts for this package and the package settings
    $delimiter=chr(9);
    $eol=chr(10);
    $packageid=$_GET['packageid'];
    $sql="SELECT A.*, B.account_name, C.package_name, D.pub_code FROM inserts A, accounts B, jobs_inserter_packages C, publications D 
    WHERE A.package_id=$packageid AND A.advertiser_id=B.id AND A.package_id=C.id AND A.pub_id=D.id ORDER BY B.account_name";
    $dbInserts=dbselectmulti($sql);
    
    $sql="SELECT * FROM jobs_inserter_packages_settings WHERE package_id=$packageid";
    $dbSettings=dbselectsingle($sql);
    $setting=$dbSettings['data'];
    
    if ($dbInserts['numrows']>0)
    {
        $pubname=stripslashes($dbInserts['data'][0]['pub_code'])."_";
        $pubname.=stripslashes($dbInserts['data'][0]['package_name']);
        $pubdate=date("mdY",strtotime($dbInserts['data'][0]['insert_date']));
        $pubname=str_replace(" ","",$pubname);
        $pubname=str_replace("'","",$pubname);
        $pubname=str_replace("/","",$pubname);
        $pubname=str_replace("\\","",$pubname);
        $pubname=str_replace("!","",$pubname);
        $pubname=str_replace("@","",$pubname);
        $pubname=str_replace("#","",$pubname);
        $pubname=str_replace("?","",$pubname);
        $pubname=str_replace(".","",$pubname);
        $pubname=str_replace(",","",$pubname);
        $pubname=str_replace("*","",$pubname);
        $pubname=str_replace("\$","",$pubname);
        $filename=$pubdate."-".$pubname.".P.txt";
         header('Content-Type: text/plain'); // plain text file
         header('Content-Disposition: attachment; filename="'.$filename.'"');
        $output="";
        foreach($dbInserts['data'] as $insert)
        {
            $insertname=stripslashes($insert['account_name']);
            if ($insertname=='WE PRINT'){$insertname=str_replace("WE PRINT - ","",stripslashes($insert['insert_tagline']));}
            $output.=$insertname.$delimiter.$setting['reject_misses'].$delimiter;
            $output.=$setting['reject_doubles'].$delimiter.$setting['miss_fault'].$delimiter;
            $output.=$setting['double_fault'].$delimiter.$setting['attempt_repair'].$eol;           } 
        print $output;
    } else {
        print "Sorry! No inserts exist for this package. Please set that up before you try again!"; 
    }
}

function build_package_file()
{
    
    /******************************************************************
    * packages file:
    * field             description             data type
    * PACKAGE NAME      Package Name            String
    * PRODUCT NAME      Product Name            String
    * 
    * sample line:
    * FULL RUN, Jacket
    * (duplicate for each insert in this package)
    * 
    */
    //ok, we'll need pub info, inserts for this package and the package settings
    $delimiter=chr(9);
    $eol=chr(10);
    $packageid=$_GET['packageid'];
    $sql="SELECT A.*, B.account_name FROM inserts A, accounts B WHERE A.package_id=$packageid AND A.advertiser_id=B.id";
    $dbInserts=dbselectmulti($sql);
    
    if ($dbInserts['numrows']>0)
    {
        $sql="SELECT * FROM publications WHERE id=".$dbInserts['data'][0]['pub_id'];
        $dbPub=dbselectsingle($sql);
        $pubcode=$dbPub['data']['pub_code'];
        
        $sql="SELECT * FROM jobs_inserter_packages WHERE id=$packageid";
        $dbPackage=dbselectsingle($sql);
        $packagename=$dbPackage['data']['package_name'];
        
        $pubcode=stripslashes($pubcode)."_";
        $pubcode.=stripslashes($packagename);
        $pubdate=date("mdY",strtotime($dbInserts['data'][0]['insert_date']));
        $filename=$pubdate."-".$pubcode.".P.txt";
         header('Content-Type: text/plain'); // plain text file
         header('Content-Disposition: attachment; filename="'.$filename.'"');
        $output="";
        
        $packages=array();
        foreach($dbInserts['data'] as $insert)
        {
            $sql="SELECT DISTINCT(A.zone_id), zone_name FROM insert_zoning A, publications_insertzones B WHERE A.insert_id=$insert[id] AND A.zone_id=B.id";
            $dbZones=dbselectmulti($sql);
            if ($dbZones['numrows']>0)
            {
                foreach($dbZones['data'] as $zone)
                {
                    $zname=stripslashes($zone['zone_name']);
                    $insertname=stripslashes($insert['account_name']);
                    if ($insertname=='WE PRINT'){$insertname=str_replace("WE PRINT - ","",stripslashes($insert['insert_tagline']));}
                    $output.=$zname.$delimiter.$insertname.$eol;
                }
            } else {
                //probably have a truck that is not zoned
                $sql="SELECT DISTINCT(truck_id), truck_name FROM insert_zoning A, publications_inserttrucks B WHERE A.insert_id=$insert[id] AND A.zone_id=0 AND A.truck_id=B.id";
                $dbTrucks=dbselectmulti($sql);
                if ($dbTrucks['numrows']>0)
                {
                    foreach($dbTrucks['data'] as $truck)
                    {
                        $sql="SELECT truck_name FROM publications_inserttrucks WHERE id=$truck[id]";
                        $dbTname=dbselectsingle($sql);
                        $tname=stripslashes($truck['truck_name']);
                        $insertname=stripslashes($insert['account_name']);
                        if ($insertname=='WE PRINT'){$insertname=str_replace("WE PRINT - ","",stripslashes($insert['insert_tagline']));}
                        $output.=$insertname.$delimiter.$tname.$eol;
                    } 
                } else {
                    //not sure what other options there could be at this point
                }
            }   
        }
        
        
        
        foreach($dbInserts['data'] as $insert)
        {
            $insertname=stripslashes($insert['account_name']);
            if ($insertname=='WE PRINT'){$insertname=str_replace("WE PRINT - ","",stripslashes($insert['insert_tagline']));}
            $output.=$insertname.$delimiter.$insert['zone_name'].$eol;
        } 
        print $output;
    } else {
        print "Sorry! No inserts exist for this package. Please set that up before you try again!"; 
    }
}

function build_order_file()
{
    
    /******************************************************************
    * orders file:
    * field             description             data type
    * ORDER NAME        Order Name (id)-Route   String
    * PACKAGE NAME      Package Name - ties     String
    *                   into the packages file
    * DRAW              Number of copies for    Number
    *                   this order (route)
    * GAP               Gap size between routes Number
    *                   (spacing in gripper)
    * DELIVERY          Output delivery id      Number
    *                   1=Primary, 2=Secondary
    * SEQ               Sequence Number         Number
    *                   order of output
    * CPB               Copies per bundle       Number
    * TURNS             Number of turns in      Number
    *                   bundle (0 is no turns)
    * 
    * sample file:
    * MR2031,FULL,1949,30,1,1,30,1
    * 
    */
    //ok, we'll need pub info, inserts for this package and the package settings
    $delimiter=chr(9);
    $eol=chr(10);
    $packageid=$_GET['packageid'];
    $sql="SELECT A.*, B.account_name, C.package_name, D.pub_code FROM inserts A, accounts B, jobs_inserter_packages C, publications D 
    WHERE A.package_id=$packageid AND A.advertiser_id=B.id AND A.package_id=C.id AND A.pub_id=D.id ORDER BY B.account_name";
    $dbInserts=dbselectmulti($sql);
    
    $sql="SELECT * FROM jobs_inserter_packages_settings WHERE package_id=$packageid";
    $dbSettings=dbselectsingle($sql);
    $setting=$dbSettings['data'];
    
    if ($dbInserts['numrows']>0)
    {
        $pubname=stripslashes($dbInserts['data'][0]['pub_code'])."_";
        $pubname.=stripslashes($dbInserts['data'][0]['package_name']);
        $pubdate=date("mdY",strtotime($dbInserts['data'][0]['insert_date']));
        $pubname=str_replace(" ","",$pubname);
        $pubname=str_replace("'","",$pubname);
        $pubname=str_replace("/","",$pubname);
        $pubname=str_replace("\\","",$pubname);
        $pubname=str_replace("!","",$pubname);
        $pubname=str_replace("@","",$pubname);
        $pubname=str_replace("#","",$pubname);
        $pubname=str_replace("?","",$pubname);
        $pubname=str_replace(".","",$pubname);
        $pubname=str_replace(",","",$pubname);
        $pubname=str_replace("*","",$pubname);
        $pubname=str_replace("\$","",$pubname);
        $filename=$pubdate."-".$pubname.".P.txt";
         header('Content-Type: text/plain'); // plain text file
         header('Content-Disposition: attachment; filename="'.$filename.'"');
        $output="";
        foreach($dbInserts['data'] as $insert)
        {
            $insertname=stripslashes($insert['customer_name']);
            if ($insertname=='WE PRINT'){$insertname=str_replace("WE PRINT - ","",stripslashes($insert['insert_tagline']));}
            $output.=$insertname.$delimiter.$setting['reject_misses'].$delimiter;
            $output.=$setting['reject_doubles'].$delimiter.$setting['miss_fault'].$delimiter;
            $output.=$setting['double_fault'].$delimiter.$setting['attempt_repair'].$eol;           } 
        print $output;
    } else {
        print "Sorry! No inserts exist for this package. Please set that up before you try again!"; 
    }
}

dbclose();
?>
