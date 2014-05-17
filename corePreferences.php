<?php
//<!--VERSION: .9 **||**-->

include("includes/mainmenu.php") ;

global $papertypes, $producttypes, $leadtypes, $folders, $sizes, $siteID, $sites, $pressmen, $pressMonitorLayouts;
global $stickyLocations, $remakeLabels, $folderpins;

/***************************************************
*  Please note: any additional preferences must be added
*  to the database and also to the config file so that they
*  will be available.
* 
*****************************************************/


if ($_POST['submit']=='Save Preferences')
{
    $checkVersionAddress=addslashes($_POST['checkVersionAddress']);
    
    $pressFolders=addslashes($_POST['pressFolders']);
    $pressSpeed=addslashes($_POST['pressSpeed']);
    $pressTowers=addslashes($_POST['pressTowers']);
    $pressSetup=addslashes($_POST['pressSetup']);
    $inserterSetup=addslashes($_POST['inserterSetup']);
    $stitchLead=addslashes($_POST['stitchLead']);
    $stitchSetup=addslashes($_POST['stitchSetup']);
    $workflowSectionCodeLength=addslashes($_POST['workflowSectionCodeLength']);
    $defaultNewsprintID=addslashes($_POST['defaultNewsprintID']);
    $systemEmailFromAddress=addslashes($_POST['systemEmailFromAddress']);
    $insertProducts=addslashes($_POST['insertProducts']);
    $insertSignOff=addslashes($_POST['insertSignOff']);
    $pressid=$_POST['pressid'];
    $folder=$_POST['folder'];
    $leadtype=$_POST['leadtype'];
    $defaultLap=$_POST['defaultLap'];
    $producttype=$_POST['producttype'];
    $defaultInserter=$_POST['defaultInserter'];
    $systemTitle=$_POST['systemTitle'];
    $pressCounterThreshhold=$_POST['pressCounterThreshhold'];
    $pressRunTimeThreshold=$_POST['pressRunTimeThreshold'];
    $broadsheetPageWidth=$sizes[$_POST['broadsheetPageWidth']];
    $broadsheetPageHeight=$_POST['broadsheetPageHeight'];
    $coreDiameter=$_POST['coreDiameter'];
    $newsprintOrderSources=$_POST['newsprintOrderSources'];
    $newsprintOrderSources=str_replace(" ","_",$newsprintOrderSources);
    $newsprintOrderSources=str_replace(";",",",$newsprintOrderSources);
    $manageJobsHoursAhead=$_POST['manageJobsHoursAhead'];
    $systemRootPath=$_POST['systemRootPath'];
    $helpdeskCompleteStatus=$_POST['helpdeskCompleteStatus'];
    $helpdeskHoldStatus=$_POST['helpdeskHoldStatus'];
    $section1_color=$_POST['section1_color'];
    $section2_color=$_POST['section2_color'];
    $section3_color=$_POST['section3_color'];
    $section4_color=$_POST['section4_color'];
    $defaultPubColor=$_POST['default_pub_color'];
    if($_POST['allowScheduleUnconfirmedInserts']){$allowScheduleUnconfirmedInserts=1;}else{$allowScheduleUnconfirmedInserts=0;}
    if($_POST['treatGateFoldasFull']){$treatGateFoldasFull=1;}else{$treatGateFoldasFull=0;}
    if($_POST['presteligence_integration']){$presteligence_integration=1;}else{$presteligence_integration=0;}
    if($_POST['rejectMisses']){$rejectMisses=1;}else{$rejectMisses=0;}
    if($_POST['rejectDoubles']){$rejectDoubles=1;}else{$rejectDoubles=0;}
    if($_POST['attemptRepair']){$attemptRepair=1;}else{$attemptRepair=0;}
    if($_POST['cronSystemEnabled']){$cronSystemEnabled=1;}else{$cronSystemEnabled=0;}
    if($_POST['insertUseLocation']){$insertUseLocation=1;}else{$insertUseLocation=0;}
    if($_POST['captureStopNotes']){$captureStopNotes=1;}else{$captureStopNotes=0;}
    if($_POST['pressJobStartMessages']){$pressJobStartMessages=1;}else{$pressJobStartMessages=0;}
    if($_POST['defaultSlitter']){$defaultSlitter=1;}else{$defaultSlitter=0;}
    if($_POST['askForRollSize']){$askForRollSize=1;}else{$askForRollSize=0;}
    if($_POST['counter_check']){$counterCheck=1;}else{$counterCheck=0;}
    $defaultFolderPin=$_POST['defaultFolderPin'];
    $generalProductionTicketType=$_POST['generalProductionTicketType'];
    $missFault=$_POST['missFault'];
    $doubleFault=$_POST['doubleFault'];
    $gap=$_POST['gap'];
    $deliveryInserter=$_POST['deliveryInserter'];
    $copiesPerBundle=$_POST['copiesPerBundle'];
    $turns=$_POST['turns'];
    $averageHourlyPressWage=$_POST['averageHourlyPressWage'];
    $averageHourlyMailroomWage=$_POST['averageHourlyMailroomWage'];
    $defaultInsertPublication=$_POST['defaultInsertPublication'];
    $serverIPaddress=$_POST['serverIPaddress'];
    $schedulingStartDayOfWeek=$_POST['schedulingStartDayOfWeek'];
    $taxRate=$_POST['taxRate'];
    if($_POST['poEmailVendor']){$poEmailVendor=1;}else{$poEmailVendor=0;}
    if($_POST['debug']){$debug=1;}else{$debug=0;}
    $publisherID=$_POST['publisherID'];
    $poDirectorAmount=$_POST['poDirectorAmount'];
    $poFinanceAmount=$_POST['poFinanceAmount'];
    $poPublisherAmount=$_POST['poPublisherAmount'];
    if (!preg_match("/http:\/\//",$serverIPaddress)) {$serverIPaddress="http://".$serverIPaddress;}
    $pressDepartmentID=$_POST['pressDepartmentID'];
    $mailroomDepartmentID=$_POST['mailroomDepartmentID'];
    $productionDepartmentID=$_POST['productionDepartmentID'];
    $advertisingDepartmentID=$_POST['advertisingDepartmentID'];
    $defaultPressOperatorID=$_POST['defaultPressOperatorID'];
    $financeDepartmentID=$_POST['financeDepartmentID'];
    $editorialDepartmentID=$_POST['editorialDepartmentID'];
    $circulationDepartmentID=$_POST['circulationDepartmentID'];
    $newspaperName=addslashes($_POST['newspaperName']);
    $stitchspeed=addslashes($_POST['stitchSpeed']);
    $newspaperAreaCode=addslashes($_POST['newspaperAreaCode']);
    $newspaperLeadPageCount=addslashes($_POST['newspaperLeadPageCount']);
    $commercialLeadPageCount=addslashes($_POST['commercialLeadPageCount']);
    $siteID=$_POST['siteID'];
    $itDevices=addslashes($_POST['itDevices']);
    $pressMonitorLayout=addslashes($_POST['pressMonitorLayout']);
    $remoteMailHostName=addslashes($_POST['remoteMailHostName']);
    $remoteHelpdeskTicketUsername=addslashes($_POST['remoteHelpdeskTicketUsername']);
    $remoteHelpdeskTicketPassword=addslashes($_POST['remoteHelpdeskTicketPassword']);
    $remoteMaintenanceTicketUsername=addslashes($_POST['remoteMaintenanceTicketUsername']);
    $remoteMaintenanceTicketPassword=addslashes($_POST['remoteMaintenanceTicketPassword']);
    
    $calendarStartAddressing=addslashes($_POST['calendarStartAddressing']);
    $calendarStartPress=addslashes($_POST['calendarStartPress']);
    $calendarStartPackaging=addslashes($_POST['calendarStartPackaging']);
    $calendarStartBindery=addslashes($_POST['calendarStartBindery']);
    $calendarPressSlots=addslashes($_POST['calendarPressSlots']);
    $calendarBinderySlots=addslashes($_POST['calendarBinderySlots']);
    $calendarPackagingSlots=addslashes($_POST['calendarPackagingSlots']);
    $calendarAddressingSlots=addslashes($_POST['calendarAddressingSlots']);
    
    $pressStartMessage=addslashes($_POST['pressStartMessage']);
    $coreServer=addslashes($_POST['coreServer']);
    $resendRateHighestTicket=addslashes($_POST['resendRateHighestTicket']);
    $stickyNoteLocation=addslashes($_POST['stickyNoteLocation']);
    $remakeLabel=addslashes($_POST['remakeLabel']);
    $fileMonitorTrigger=addslashes($_POST['fileMonitorTrigger']);
    $ripMonitorTrigger=addslashes($_POST['ripMonitorTrigger']);
    $alertNotifier=addslashes($_POST['alertNotifier']);
    $wePrintAdvertiserID=addslashes($_POST['wePrintAdvertiserID']);
    $defaultStitcher=addslashes($_POST['defaultStitcher']);
    $defaultState=addslashes($_POST['defaultState']);
    $circRouteStart=addslashes($_POST['circRouteStart']);
    $officeStreetAddress=addslashes($_POST['officeStreetAddress']);
    $officeStreetCity=addslashes($_POST['officeStreetCity']);
    $officeStreetState=addslashes($_POST['officeStreetState']);
    $officeStreetZip=addslashes($_POST['officeStreetZip']);
    $printingStreetAddress=addslashes($_POST['printingStreetAddress']);
    $printingStreetCity=addslashes($_POST['printingStreetCity']);
    $printingStreetState=addslashes($_POST['printingStreetState']);
    $printingStreetZip=addslashes($_POST['printingStreetZip']);
    $googleMapKey=addslashes($_POST['google_map_key']);
    $folderNames=addslashes($_POST['folderNames']);
    
    $addressingSpeed=addslashes($_POST['addressingSpeed']);
    
    if($_POST['lockPressPrint']==''){$lockPressPrint=0;}else{$lockPressPrint=$_POST['lockPressPrint'];}
    if($_POST['lockPressPub']==''){$lockPressPub=0;}else{$lockPressPub=$_POST['lockPressPub'];}
    if($_POST['lockInsertBook']==''){$lockInsertBook=0;}else{$lockInsertBook=$_POST['lockInsertBook'];}
    if($_POST['lockInsertDelete']==''){$lockInsertDelete=0;}else{$lockInsertDelete=$_POST['lockInsertDelete'];}
    if($_POST['lockBinderyStart']==''){$lockBinderyStart=0;}else{$lockBinderyStart=$_POST['lockBinderyStart'];}
    if($_POST['lockPubHours']==''){$lockPubHours=0;}else{$lockPubHours=$_POST['lockPubHours'];}
    
    /* geocode the addresses */
    $addresses[0]['street']=$officeStreetAddress;
    $addresses[0]['city']=$officeStreetCity;
    $addresses[0]['state']=$officeStreetState;
    $addresses[0]['zip']=$officeStreetZip;
    $addresses[1]['street']=$printingStreetAddress;
    $addresses[1]['city']=$printingStreetCity;
    $addresses[1]['state']=$printingStreetState;
    $addresses[1]['zip']=$printingStreetZip;
    if(function_exists('curl_init'))
    {
        $addresses=batch_geocode($addresses,'0','0');
        $officeLat=$addresses[0]['lat'];        
        $officeLon=$addresses[0]['lon'];        
        $printingLat=$addresses[1]['lat'];        
        $printingLon=$addresses[1]['lon'];        
    } else {
        $officeLat=0;        
        $officeLon=0;        
        $printingLat=0;        
        $printingLon=0;
    }
    $sql="UPDATE core_preferences SET newspaperAreaCode='$newspaperAreaCode', 
     allowScheduleUnconfirmedInserts='$allowScheduleUnconfirmedInserts',  
     coreServer='$coreServer',
     press_folders='$pressFolders', 
     press_speed='$pressSpeed', 
     pressStartMessage='$pressStartMessage', 
     press_towers='$pressTowers', 
     press_setup='$pressSetup', 
     inserter_setup='$inserterSetup', 
     stitch_lead='$stitchLead', 
     stitch_setup='$stitchSetup', 
     workflowSectionCodeLength='$workflowSectionCodeLength', 
     averageHourlyPressWage='$averageHourlyPressWage',
     defaultNewsprintID='$defaultNewsprintID', 
     systemEmailFromAddress='$systemEmailFromAddress', 
     pressid='$pressid', 
     defaultFolder='$folder', 
     defaultLead='$leadtype', 
     defaultProduct='$producttype', 
     pressCounterThreshhold='$pressCounterThreshhold',
     treatGateFoldasFull='$treatGateFoldasFull', 
     defaultLap='$defaultLap', 
     insertProducts='$insertProducts', 
     insertSignOff='$insertSignOff', 
     defaultInserter='$defaultInserter', 
     systemTitle='$systemTitle', 
     pressRunTimeThreshold='$pressRunTimeThreshold', 
     averageHourlyMailroomWage='$averageHourlyMailroomWage', 
     broadsheetPageWidth='$broadsheetPageWidth', 
     broadsheetPageHeight='$broadsheetPageHeight', 
     coreDiameter='$coreDiameter', 
     newsprintOrderSources='$newsprintOrderSources', 
     manageJobsHoursAhead='$manageJobsHoursAhead', 
     checkVersionAddress='$checkVersionAddress', 
     systemRootPath='$systemRootPath', 
     miracom_turns='$turns', 
     miracom_copies_per_bundle='$copiesPerBundle', 
     generalProductionTicketType='$generalProductionTicketType', 
     miracom_reject_misses='$rejectMisses', 
     miracom_reject_doubles='$rejectDoubles', 
     miracom_miss_fault='$missFault', 
     schedulingStartDayOfWeek='$schedulingStartDayOfWeek', 
     miracom_double_fault='$doubleFault', 
     miracom_attempt_repair='$attemptRepair', 
     miracom_gap='$gap', 
     miracom_delivery='$deliveryInserter', 
     presteligence_integration='$presteligence_integration', 
     helpdeskCompleteStatus='$helpdeskCompleteStatus',
     helpdeskHoldStatus='$helpdeskHoldStatus',  
     section1_color='$section1_color', 
     section2_color='$section2_color', 
     section3_color='$section3_color', 
     section4_color='$section4_color', 
     itDevices='$itDevices', 
     defaultInsertPublication='$defaultInsertPublication', 
     insertUseLocation='$insertUseLocation', 
     serverIPaddress='$serverIPaddress', 
     publisherID='$publisherID', 
     poEmailVendor='$poEmailVendor', 
     taxRate='$taxRate', 
     poDirectorAmount='$poDirectorAmount', 
     poFinanceAmount='$poFinanceAmount', 
     poPublisherAmount='$poPublisherAmount', 
     defaultPressOperatorID='$defaultPressOperatorID', 
     pressDepartmentID='$pressDepartmentID', 
     productionDepartmentID='$productionDepartmentID', 
     mailroomDepartmentID='$mailroomDepartmentID', 
     resendRateHighestTicket='$resendRateHighestTicket', 
     advertisingDepartmentID='$advertisingDepartmentID', 
     newspaperName='$newspaperName', 
     financeDepartmentID='$financeDepartmentID', 
     editorialDepartmentID='$editorialDepartmentID',       
     circulationDepartmentID='$circulationDepartmentID', 
     stitch_speed='$stitchspeed', 
     commercialLeadPageCount='$commercialLeadPageCount', 
     newspaperLeadPageCount='$newspaperLeadPageCount', 
     cronSystemEnabled='$cronSystemEnabled', 
     pressMonitorLayout='$pressMonitorLayout', 
     calendarStartPress='$calendarStartPress', 
     remoteMailHostName='$remoteMailHostName',
     remoteHelpdeskTicketUsername='$remoteHelpdeskTicketUsername', 
     wePrintAdvertiserID='$wePrintAdvertiserID', 
     remoteHelpdeskTicketPassword='$remoteHelpdeskTicketPassword', 
     defaultStitcher='$defaultStitcher', 
     remoteMaintenanceTicketUsername='$remoteMaintenanceTicketUsername', 
     pressDefaultSlitter='$defaultSlitter', 
     remoteMaintenanceTicketPassword='$remoteMaintenanceTicketPassword', 
     pressDefaultFolderPin='$defaultFolderPin', 
     calendarStartAddressing='$calendarStartAddressing', 
     calendarStartBindery='$calendarStartBindery', 
     calendarStartPackaging='$calendarStartPackaging', 
     calendarPressSlots='$calendarPressSlots', 
     calendarPackagingSlots='$calendarPackagingSlots', 
     calendarBinderySlots='$calendarBinderySlots', 
     calendarAddressingSlots='$calendarAddressingSlots', 
     debug='$debug', 
     stickyNoteLocation='$stickyNoteLocation', 
     remakeLabel='$remakeLabel', 
     ripMonitorTrigger='$ripMonitorTrigger', 
     fileMonitorTrigger='$fileMonitorTrigger', 
     alertNotifier='$alertNotifier',
     defaultState='$defaultState',
     officeStreetAddress='$officeStreetAddress',
     officeStreetCity='$officeStreetCity',
     officeStreetState='$officeStreetState',
     officeStreetZip='$officeStreetZip',
     printingStreetAddress='$printingStreetAddress',
     printingStreetCity='$printingStreetCity',
     printingStreetState='$printingStreetState',
     printingStreetZip='$printingStreetZip',
     circRouteStart='$circRouteStart',
     officeLat='$officeLat',
     officeLon='$officeLon',
     printingLat='$printingLat',
     printingLon='$printingLon',
     addressingSpeed='$addressingSpeed',
     lockPubHours='$lockPubHours',
     lockPressPrint='$lockPressPrint',
     lockPressPub='$lockPressPub',
     lockInsertBook='$lockInsertBook',
     lockInsertDelete='$lockInsertDelete',
     lockBinderyStart='$lockBinderyStart',
     askForRollSize='$askForRollSize',
     google_map_key='$googleMapKey',
     default_pub_color='$defaultPubColor',
     counter_check='$counterCheck',
     folderNames='$folderNames',
     site_id=$siteID";
     $dbUpdate=dbexecutequery($sql);
     $error=$dbUpdate['error'];
     if ($error!='')
     {
        setUserMessage('There was a problem updating the preferences.<br />'.$error,'error');
     } else {
        setUserMessage('The preferences have been successfully updated.','success');
     }
     redirect("?action=saved");
} else {
    $sql="SELECT * FROM core_preferences";
    $dbPrefs=dbselectsingle($sql);
    $prefs=$dbPrefs['data'];
    $siteID=$prefs['site_id'];
    
    $pressFolders=stripslashes($prefs['press_folders']);
    $pressSpeed=stripslashes($prefs['press_speed']);
    $pressTowers=stripslashes($prefs['press_towers']);
    $pressSetup=stripslashes($prefs['press_setup']);
    $inserterSetup=stripslashes($prefs['inserter_setup']);
    $stitchSetup=stripslashes($prefs['stitch_setup']);
    $stitchLead=stripslashes($prefs['stitch_lead']);
    $workflowSectionCodeLength=stripslashes($prefs['workflowSectionCodeLength']);
    $defaultNewsprintID=stripslashes($prefs['defaultNewsprintID']);
    $systemEmailFromAddress=stripslashes($prefs['systemEmailFromAddress']);
    $pressid=stripslashes($prefs['pressid']);
    $folder=stripslashes($prefs['defaultFolder']);
    $leadtype=stripslashes($prefs['defaultLead']);
    $producttype=stripslashes($prefs['defaultProduct']);
    $producttype=stripslashes($prefs['defaultProduct']);
    $insertProducts=stripslashes($prefs['insertProducts']);
    $pressCounterThreshhold=stripslashes($prefs['pressCounterThreshhold']);
    $pressRunTimeThreshold=stripslashes($prefs['pressRunTimeThreshold']);
    $treatGateFoldasFull=stripslashes($prefs['treatGateFoldasFull']);
    $defaultLap=stripslashes($prefs['defaultLap']);
    $insertSignOff=stripslashes($prefs['insertSignOff']);
    $defaultInserter=stripslashes($prefs['defaultInserter']);
    $systemTitle=stripslashes($prefs['systemTitle']);
    $broadsheetPageWidth=stripslashes($prefs['broadsheetPageWidth']);
    $broadsheetPageHeight=stripslashes($prefs['broadsheetPageHeight']);
    $coreDiameter=stripslashes($prefs['coreDiameter']);
    $newsprintOrderSources=stripslashes($prefs['newsprintOrderSources']);
    $manageJobsHoursAhead=stripslashes($prefs['manageJobsHoursAhead']);
    $checkVersionAddress=stripslashes($prefs['checkVersionAddress']);
    $systemRootPath=stripslashes($prefs['systemRootPath']);
    $askForRollSize=stripslashes($prefs['askForRollSize']);
    $folderNames=stripslashes($prefs['folderNames']);
    
    $rejectMisses=stripslashes($prefs['miracom_reject_misses']);
    $rejectDoubles=stripslashes($prefs['miracom_reject_doubles']);
    $missFault=stripslashes($prefs['miracom_miss_fault']);
    $doubleFault=stripslashes($prefs['miracom_double_fault']);
    $attemptRepair=stripslashes($prefs['miracom_attempt_repair']);
    $gap=stripslashes($prefs['miracom_gap']);
    $deliveryInserter=stripslashes($prefs['miracom_delivery']);
    $copiesPerBundle=stripslashes($prefs['miracom_copies_per_bundle']);
    $turns=stripslashes($prefs['miracom_turns']);
    $defaultStitcher=$prefs['defaultStitcher'];
    $counterCheck=$prefs['counter_check'];
    
    $presteligence_integration=stripslashes($prefs['presteligence_integration']);
    $helpdeskCompleteStatus=stripslashes($prefs['helpdeskCompleteStatus']);
    $helpdeskHoldStatus=stripslashes($prefs['helpdeskHoldStatus']);
    $section1_color=stripslashes($prefs['section1_color']);   
    $section2_color=stripslashes($prefs['section2_color']);   
    $section3_color=stripslashes($prefs['section3_color']);   
    $section4_color=stripslashes($prefs['section4_color']);   
    $defaultInsertPublication=stripslashes($prefs['defaultInsertPublication']);   
    $insertUseLocation=stripslashes($prefs['defaultInsertPublication']);   
    $serverIPaddress=stripslashes($prefs['serverIPaddress']);   
    $generalProductionTicketType=stripslashes($prefs['generalProductionTicketType']);   
    $averageHourlyPressWage=stripslashes($prefs['averageHourlyPressWage']);   
    $averageHourlyMailroomWage=stripslashes($prefs['averageHourlyMailroomWage']);   
    $schedulingStartDayOfWeek=stripslashes($prefs['schedulingStartDayOfWeek']);   
    $taxRate=stripslashes($prefs['taxRate']);   
    $poDirectorAmount=stripslashes($prefs['poDirectorAmount']);   
    $poFinanceAmount=stripslashes($prefs['poFinanceAmount']);   
    $poPublisherAmount=stripslashes($prefs['poPublisherAmount']);   
    $publisherID=stripslashes($prefs['publisherID']);   
    $defaultPressOperatorID=stripslashes($prefs['defaultPressOperatorID']);   
    $poEmailVendor=stripslashes($prefs['poEmailVendor']);
    $pressDepartmentID=stripslashes($prefs['pressDepartmentID']);
    $mailroomDepartmentID=stripslashes($prefs['mailroomDepartmentID']);
    $productionDepartmentID=stripslashes($prefs['productionDepartmentID']);
    $advertisingDepartmentID=stripslashes($prefs['advertisingDepartmentID']);
    $newspaperName=stripslashes($prefs['newspaperName']);
    $financeDepartmentID=stripslashes($prefs['financeDepartmentID']);
    $editorialDepartmentID=stripslashes($prefs['editorialDepartmentID']);
    $circulationDepartmentID=stripslashes($prefs['circulationDepartmentID']);
    $stitchspeed=stripslashes($prefs['stitch_speed']);
    $newspaperAreaCode=stripslashes($prefs['newspaperAreaCode']);
    $newspaperLeadPageCount=stripslashes($prefs['newspaperLeadPageCount']);
    $commercialLeadPageCount=stripslashes($prefs['commercialLeadPageCount']);
    $cronSystemEnabled=stripslashes($prefs['cronSystemEnabled']);
    $cronLastCall=stripslashes($prefs['cronLastCall']);
    $pressMonitorLayout=stripslashes($prefs['pressMonitorLayout']);
    $itDevices=stripslashes($prefs['itDevices']);
    $remoteMailHostName=stripslashes($prefs['remoteMailHostName']);
    $remoteHelpdeskTicketUsername=stripslashes($prefs['remoteHelpdeskTicketUsername']);
    $remoteHelpdeskTicketPassword=stripslashes($prefs['remoteHelpdeskTicketPassword']);
    $remoteMaintenanceTicketUsername=stripslashes($prefs['remoteMaintenanceTicketUsername']);
    $remoteMaintenanceTicketPassword=stripslashes($prefs['remoteMaintenanceTicketPassword']);
    
    $calendarStartAddressing=stripslashes($prefs['calendarStartAddressing']);
    $calendarStartPress=stripslashes($prefs['calendarStartPress']);
    $calendarStartPackaging=stripslashes($prefs['calendarStartPackaging']);
    $calendarStartBindery=stripslashes($prefs['calendarStartBindery']);
    $calendarPressSlots=stripslashes($prefs['calendarPressSlots']);
    $calendarPackagingSlots=stripslashes($prefs['calendarPackagingSlots']);
    $calendarBinderySlots=stripslashes($prefs['calendarBinderySlots']);
    $calendarAddressingSlots=stripslashes($prefs['calendarAddressingSlots']);
    
    
    $captureStopNotes=stripslashes($prefs['captureStopNotes']);
    $pressJobStartMessages=stripslashes($prefs['pressJobStartMessages']);
    $pressStartMessage=stripslashes($prefs['pressStartMessage']);
    $coreServer=stripslashes($prefs['coreServer']);
    $resendRateHighestTicket=stripslashes($prefs['resendRateHighestTicket']);
    $stickyNoteLocation=stripslashes($prefs['stickyNoteLocation']);
    $remakeLabel=stripslashes($prefs['remakeLabel']);
    $alertNotifier=stripslashes($prefs['alertNotifier']);
    $fileMonitorTrigger=stripslashes($prefs['fileMonitorTrigger']);
    $ripMonitorTrigger=stripslashes($prefs['ripMonitorTrigger']);
    $allowScheduleUnconfirmedInserts=stripslashes($prefs['allowScheduleUnconfirmedInserts']);
    $wePrintAdvertiserID=stripslashes($prefs['wePrintAdvertiserID']);
    $defaultSlitter=stripslashes($prefs['pressDefaultSlitter']);
    $defaultFolderPin=stripslashes($prefs['pressDefaultFolderPin']);
    $defaultState=stripslashes($prefs['defaultState']);
    $defaultPubColor=stripslashes($prefs['default_pub_color']);
    
    $officeStreetAddress=stripslashes($prefs['officeStreetAddress']);
    $officeStreetCity=stripslashes($prefs['officeStreetCity']);
    $officeStreetState=stripslashes($prefs['officeStreetState']);
    $officeStreetZip=stripslashes($prefs['officeStreetZip']);
    $officeCoords="Lat: ".$prefs['officeLat']." Lon: ".$prefs['officeLon'];
    
    $printingStreetAddress=stripslashes($prefs['printingStreetAddress']);
    $printingStreetCity=stripslashes($prefs['printingStreetCity']);
    $printingStreetState=stripslashes($prefs['printingStreetState']);
    $printingStreetZip=stripslashes($prefs['printingStreetZip']);
    $printingCoords="Lat: ".$prefs['printingLat']." Lon: ".$prefs['printingLon'];
    $circRouteStart=stripslashes($prefs['circRouteStart']);
    
    $lockPubHours=stripslashes($prefs['lockPubHours']);
    $lockPressPrint=stripslashes($prefs['lockPressPrint']);
    $lockPressPub=stripslashes($prefs['lockPressPub']);
    $lockInsertBook=stripslashes($prefs['lockInsertBook']);
    $lockInsertDelete=stripslashes($prefs['lockInsertDelete']);
    $lockBinderyStart=stripslashes($prefs['lockBinderyStart']);
    
    $googleMapKey=stripslashes($prefs['google_map_key']);
    
    $addressingSpeed=stripslashes($prefs['addressingSpeed']);
    
    $debug=$prefs['debug'];  
}
//build press list
$presses=array();
$presses[0]='None defined';
$sql="SELECT * FROM press WHERE site_id=$siteID";
$dbPresses=dbselectmulti($sql);
if ($dbPresses['numrows']>0)
{
    $presses[0]=='Please choose';
    foreach($dbPresses['data'] as $press)
    {
        $presses[$press['id']]=$press['name'];
    }
}
//build inserter list
$inserters=array();
$inserters[0]='None defined';
$sql="SELECT * FROM inserters WHERE site_id=$siteID";
$dbInserters=dbselectmulti($sql);
if ($dbInserters['numrows']>0)
{
    $inserters[0]='Please choose';
    foreach($dbInserters['data'] as $inserter)
    {
        $inserters[$inserter['id']]=$inserter['inserter_name'];
    }
}
//build stitcher list
$stitchers=array();
$stitchers[0]='None defined';
$sql="SELECT * FROM stitchers WHERE site_id=$siteID";
$dbStitchers=dbselectmulti($sql);
if ($dbStitchers['numrows']>0)
{
    $stitchers[0]='Please choose';
    foreach($dbStitchers['data'] as $stitcher)
    {
        $stitchers[$stitcher['id']]=$stitcher['stitcher_name'];
    }
}

//build publication list
$allpubs=array();
$allpubs[0]='None defined';
$sql="SELECT * FROM publications WHERE site_id=$siteID ORDER BY pub_name";
$dbAllPubs=dbselectmulti($sql);
if ($dbAllPubs['numrows']>0)
{
    $allpubs[0]='Please choose';
    foreach($dbAllPubs['data'] as $ap)
    {
        $allpubs[$ap['id']]=$ap['pub_name'];
    }
}
$helpStatuses=array();
$sql="SELECT * FROM helpdesk_statuses WHERE site_id=$siteID ORDER BY status_order";
$dbStatuses=dbselectmulti($sql);
if ($dbStatuses['numrows']>0)
{
  foreach($dbStatuses['data'] as $status)
  {
      $helpStatuses[$status['id']]=$status['status_name'];
  }
} else {
  $helpStatuses[0]="None set!";
}

$helpTypes=array();
$sql="SELECT * FROM helpdesk_types WHERE site_id=$siteID ORDER BY type_name";
$dbStatuses=dbselectmulti($sql);
if ($dbStatuses['numrows']>0)
{
  foreach($dbStatuses['data'] as $status)
  {
      $helpTypes[$status['id']]=$status['type_name'];
  }
} else {
  $helpTypes[0]="None set!";
}

$allemployees=array();
$allemployees[0]='Please select';
$sql="SELECT * FROM users ORDER BY lastname, firstname";
$dbEmps=dbselectmulti($sql);
if ($dbEmps['numrows']>0)
{
    foreach($dbEmps['data'] as $emp)
    {
        $allemployees[$emp['id']]=$emp['lastname'].", ".$emp['firstname'];
    }
}

global $advertisers;


for($i=0;$i<=23;$i++)
{
    $calstarts[$i]=$i;
}
$calstarts["current"]="Current Hour";

$addresses=array("office"=>"From the office building","printing"=>"From the print facility");

print "<form method=post>\n";
print "<div id='prefTabs'>\n";
print "<ul>
        <li><a href='#general'>General System</a></li>
        <li><a href='#departments'>Departments</a></li>
        <li><a href='#press'>Press</a></li>
        <li><a href='#mailroom'>Mailroom</a></li>
        <li><a href='#bindery'>Bindery &amp; Commercial Printing</a></li>
        <li><a href='#prepress'>Prepress</a></li>
        <li><a href='#circulation'>Circulation</a></li>
        <li><a href='#it'>InfoTech</a></li>
        <li><a href='#tickets'>Trouble Tickets</a></li>
        <li><a href='#inventory'>Inventory &amp; PO</a></li>
        <li><a href='#system'>System</a></li>
        <li><a href='#timelocks'>Time Locks</a></li>
        <li><a href='#miscellaneous'>Miscellaneous</a></li>
        ";
print "</ul>\n";
        
print "<div id='general'>\n";
        make_select('siteID',$sites[$siteID],$sites,'Site ID','Site ID/Name');
        make_text('systemEmailFromAddress',$systemEmailFromAddress,'From Email','Enter the email address that indicates where system messages originate',50);
        make_number('manageJobsHoursAhead',$manageJobsHoursAhead,'Manage Jobs Hours Setting','Set the number of hours ahead to show jobs in Manage Jobs screens (ex: 48 for 2 days out)');
        make_text('newspaperName',$newspaperName,'Newspaper Name','Name of the host newspaper','50');
        make_number('newspaperAreaCode',$newspaperAreaCode,'Newspaper Area Code','Default Area Code for the paper');
        make_text('officeStreetAddress',$officeStreetAddress,'Office Street Address','Street address of the office facility. '.$officeCoords);
        make_text('officeStreetCity',$officeStreetCity,'Office City','City of the office facility');
        make_state('officeStreetState',$officeStreetState,'Office State','State of the office facility');
        make_text('officeStreetZip',$officeStreetZip,'Office Zip','Zip of the office facility');
        make_text('printingStreetAddress',$printingStreetAddress,'Printing Street Address','Street address of the printing facility. '.$printingCoords);
        make_text('printingStreetCity',$printingStreetCity,'Printing City','City of the printing facility');
        make_state('printingStreetState',$printingStreetState,'Printing State','State of the printing facility');
        make_text('printingStreetZip',$printingStreetZip,'Printing Zip','Zip of the printing facility');
        
        make_state('defaultState',$defaultState,'Default State','Specify the default state for this property.');
print "</div>\n";

print "<div id='departments'>\n";
        make_select('advertisingDepartmentID',$departments[$advertisingDepartmentID],$departments,'Advertising','Select the advertising department');
        make_select('productionDepartmentID',$departments[$productionDepartmentID],$departments,'Production','Select the production department');
        make_select('pressDepartmentID',$departments[$pressDepartmentID],$departments,'Press','Select the press department');
        make_select('mailroomDepartmentID',$departments[$mailroomDepartmentID],$departments,'Mailroom','Select the mailroom department');
        make_select('financeDepartmentID',$departments[$financeDepartmentID],$departments,'Finance','Select the finance department');
        make_select('editorialDepartmentID',$departments[$editorialDepartmentID],$departments,'Editorial','Select the editorial department');
        make_select('circulationDepartmentID',$departments[$circulationDepartmentID],$departments,'Circulation','Select the circulation department');
        make_checkbox('cronSystemEnabled',$cronSystemEnabled,'Cron System','Check to enable cron (job processing) system');
        
print "</div>\n";

print "<div id='press'>\n";
        make_number('pressFolders',$pressFolders,'Folders','How many press folders');
        make_number('pressSpeed',$pressSpeed,'Average press speed');
        make_number('pressTowers',$pressTowers,'Press Towers','How many press towers');
        make_number('pressSetup',$pressSetup,'Press Setup','How many minutes to set for setting up press');
        make_number('coreDiameter',$coreDiameter,'Core Diameter','Diameter of newsprint cores');
        make_number('pressCounterThreshhold',$pressCounterThreshhold,'Press Counter Threshhold','Value at which the press counter will alert for a bad number');
        make_number('pressRunTimeThreshold',$pressRunTimeThreshold,'Press Run Length Threshhold','Number of hours at which the press run times will alert for a bad value');
        make_text('newsprintOrderSources',$newsprintOrderSources,'Newprint Order Sources','Enter a list of newsprint order orgination points, separated by a comma "," (ex: pioneer,mcclatchy)',80);
        make_text('folderNames',$folderNames,'Folder Names','If left blank, will just show "Folder 1", "Folder 2", etc in folder drop-downs, otherwise will use names from this list. Enter list of names separated by comma, starting with name for folder 1',80);
        make_select('defaultNewsprintID',$papertypes[$defaultNewsprintID],$papertypes,'Newsprint','Default newsprint type');
        make_select('pressid',$presses[$pressid],$presses,'Press','Select press for this location');
        make_select('folder',$folders[$folder],$folders,'Folder','Default folder');
        make_select('leadtype',$leadtypes[$leadtype],$leadtypes,'Lead type','Default lead type');
        make_select('producttype',$producttypes[$producttype],$producttypes,'Product type','Default product type');
        make_select('defaultLap',$laps[$defaultLap],$laps,'Press Lap','Default lap');
        make_select('defaultFolderPin',$folderpins[$defaultFolderPin],$folderpins,'Press Folder Pin','Default Folder Pin setup');
        make_checkbox('defaultSlitter',$defaultSlitter,'Slitter','If checked, default is run with slitter on');
        make_checkbox('treatGateFoldasFull',$treatGateFoldasFull,'Gatefolds','Treat gatefolds as full pages in calculations');
        make_checkbox('captureStopNotes',$captureStopNotes,'Stop Notes','Display a popup when pressman click \'stop\' requiring input on the job');
        make_checkbox('askForRollSize',$askForRollSize,'Ask for roll size','If checked, display a roll size selection dropdown in job creation.');
        make_checkbox('pressJobStartMessages',$pressJobStartMessages,'Start Messages','Display job or general messages at the top of the screen when a job is started.');
        make_textarea('pressStartMessage',$pressStartMessage,'Press Start Message','Message to display on every job start',50,5,false);
        make_number('averageHourlyPressWage',$averageHourlyPressWage,'Avg. Wage','Average press hourly wage');
        make_select('defaultPressOperatorID',$pressmen[$defaultPressOperatorID],$pressmen,'Default Press Operator','Select the default press operator');
        make_select('pressMonitorLayout',$pressMonitorLayouts[$pressMonitorLayout],$pressMonitorLayouts,'Press Monitor Layout','Select the type of layout to be used in the press monitor window');
        make_color('default_pub_color',$defaultPubColor,'Default Publication Color','Sets a default publication color for creating new publications');
        make_checkbox('counter_check',$counterCheck,'Counter Check','If checked, the system will check against values entered in the press monitor to validate possible bad numbers.');
        
print "</div>\n";

print "<div id='mailroom'>\n";
        make_number('inserterSetup',$inserterSetup,'Inserter Setup','How many minutes to set for setting up inserter');
        make_text('insertProducts',$insertProducts,'Insert types','Enter a list of types of inserts, separated by "," (ex: Broadsheet,Tab,Coupon)',80);
        make_textarea('insertSignOff',$insertSignOff,'Insert Signoff','Message printed on inserter setup to verify inserts',80,10);
        make_select('defaultInserter',$inserters[$defaultInserter],$inserters,'Default Inserter','Default inserter to use for planning');
        make_select('defaultInsertPublication',$allpubs[$defaultInsertPublication],$allpubs,'Default Insert Publication','Default publication to use when adding inserts');
        make_checkbox('insertUseLocation',$insertUseLocation,'Enable Insert Location Tracking','Check to enable the insert location tracking system');
        make_checkbox('allowScheduleUnconfirmedInserts',$allowScheduleUnconfirmedInserts,'Allow unconfirmed Inserts','Check to allow unconfirmed inserts to be put in a package');
        make_number('averageHourlyMailroomWage',$averageHourlyMailroomWage,'Avg. Wage','Average mailroom hourly wage');
        make_select('stickyNoteLocation',$stickyLocations[$stickyNoteLocation],$stickyLocations,'Sticky Note Application','Where are the sticky notes applied? Press or Inserter?');
        make_select('wePrintAdvertiserID',$advertisers[$wePrintAdvertiserID],$advertisers,'WEPRINT Advertiser ID','Which customer account should be used for products printed by core newspaper');

        print "<fieldset><legend>Inserter Settings</legend>";
        make_checkbox('rejectMisses',$rejectMisses,'Reject Misses','Check to enable rejection of misses');
        make_number('missFault',$missFault,'# of misses','# of misses before a fault is reported');
        make_checkbox('rejectDoubles',$rejectDoubles,'Reject Doubles','Check to enable rejection of doubles');
        make_number('doubleFault',$doubleFault,'# of doubles','# of doubles before a fault is reported');
        make_checkbox('attemptRepair',$attemptRepair,'Repair','Check to enable repair attempt');
        make_number('gap',$gap,'Gap between zones','# of grippers to leave empty between zones');
        make_number('copiesPerBundle',$copiesPerBundle,'Copies per Bundle','# of papers in each bundle');
        make_number('turns',$turns,'Turns per Bundle','# of turns in each bundle');
        make_number('deliveryInserter',$deliveryInserter,'Delivery','Unused at this time'); //miracom_delivery field - can't remember what this was for...
        
        print "</fieldset>\n";
        
        make_select('defaultStitcher',$stitchers[$defaultStitcher],$stitchers,'Default Stitcher','Default stitcher to use for planning');
        
print "</div>\n";

print "<div id='bindery'>\n";
        make_number('addressingSpeed',$addressingSpeed,'Addressing Speed','Average pieces per hour for offline ink addressing');
        make_number('stitchSpeed',$stitchspeed,'S&T Speed','Average pieces per hour on stitcher');
        make_number('stitchSetup',$stitchSetup,'S&T Setup','How many minutes for stitcher set up for job');
        make_number('stitchLead',$stitchLead,'S&T Padding','How many days padding for stitch jobs');
print "</div>\n";

print "<div id='prepress'>\n";
        make_number('newspaperLeadPageCount',$newspaperLeadPageCount,'Newspaper Lead','Number of broadsheet pages across for a regular newspaper lead');
        make_number('commercialLeadPageCount',$commercialLeadPageCount,'Commercial Lead','Number of broadsheet pages across for a regular commercial lead');
        make_select('broadsheetPageWidth',$broadsheetPageWidth,$sizes,'Page Width','Default broadsheet page size (not image area)');
        make_number('broadsheetPageHeight',$broadsheetPageHeight,'Page Height','Default broadsheet cutoff (not image area)');
        make_number('workflowSectionCodeLength',$workflowSectionCodeLength,'Workflow Code','How many characters in Prestelligence for section codes?');
        make_checkbox('presteligence_integration',$presteligence_integration,'Integration','Check to indicate system integration with Presteligence NewsExtreme System');
        make_select('remakeLabel',$remakeLabels[$remakeLabel],$remakeLabels,'Remake Label','Do you use remake or chase to designate new plates for a job sent after the start?');
print "</div>\n";

print "<div id='it'>\n";
        make_textarea('itDevices',$itDevices,'IT Devices','List the types of IT devices (ex: router|Router). Use the format: dbvalue|Display value.',60,10,false);
        make_number('fileMonitorTrigger',$fileMonitorTrigger,'File Monitor Trigger','After how many minutes of inactivity should an alert be sent?');
        make_number('ripMonitorTrigger',$ripMonitorTrigger,'RIP Monitor Trigger','After how many minutes of inactivity on a page ripping should an alert be sent?');
        make_text('alertNotifier',$alertNotifier,'Who is notified?','What email address should receive an email when an alert is triggered?',50);
       
print "</div>\n";

print "<div id='system'>\n";
        make_text('systemTitle',$systemTitle,'System Title','This is what appears in title bar of the web browser','50');
        make_text('serverIPaddress',$serverIPaddress,'Server Address','IP Address or url of the Mango server',30);
        make_text('checkVersionAddress',$checkVersionAddress,'Update Server','Server and path to system updates location (ex: http://www.myidahopress.com/pims/updates)',50);
        make_text('coreServer',$coreServer,'Core Server','IP address or url to core server (ex: 10.56.1.10)',50);
        make_text('systemRootPath',$systemRootPath,'System Root','Path to root of system (ex: /)',50);
        make_text('google_map_key',$googleMapKey,'Google Map Key','API key',50);
        make_text('cronLastCall',$cronLastCall,'Cron last called','Timestamp for last cron execution call',30,false,true);
        make_checkbox('debug',$debug,'Debug Mode','Check to set Mango in debugging mode');
print "</div>\n";

print "<div id='miscellaneous'>\n";
        make_select('publisherID',$allemployees[$publisherID],$allemployees,'Publisher','Please specify the name of the publisher');
        make_select('schedulingStartDayOfWeek',$daysofweek[$schedulingStartDayOfWeek],$daysofweek,'Starting day of week','What day does your week start on?');
        make_color('section1_color',$section1_color,'Section 1 Color','Color used to specify this section');
        make_color('section2_color',$section2_color,'Section 2 Color','Color used to specify this section');
        make_color('section3_color',$section3_color,'Section 3 Color','Color used to specify this section');
        make_color('section4_color',$section4_color,'Section 4 Color','Color used to specify this section');
        print "<fieldset>";
        print "<legend>Calendar Configuration</legend>";
        make_select('calendarStartAddressing',$calstarts[$calendarStartAddressing],$calstarts,'Calendar Start Addressing','Starting time on addressing calendar');
        make_select('calendarStartPress',$calstarts[$calendarStartPress],$calstarts,'Calendar Start Press','Starting time on press calendar');
        make_select('calendarStartBindery',$calstarts[$calendarStartBindery],$calstarts,'Calendar Start Bindery','Starting time on bindery calendar');
        make_select('calendarStartPackaging',$calstarts[$calendarStartPackaging],$calstarts,'Calendar Start Packaging','Starting time on packaging calendar');
        make_number('calendarAddressingSlots',$calendarAddressingSlots,'Calendar Slots Addressing','Number of minutes for each slot on the addressing calendar');
        make_number('calendarPressSlots',$calendarPressSlots,'Calendar Slots Press','Number of minutes for each slot on the press calendar');
        make_number('calendarBinderySlots',$calendarBinderySlots,'Calendar Slots Bindery','Number of minutes for each slot on the bindery calendar');
        make_number('calendarPackagingSlots',$calendarPackagingSlots,'Calendar Slots Packaging','Number of minutes for each slot on the packaging calendar');
        print "</fieldset>";
print "</div>\n";

print "<div id='tickets'>\n";
        make_select('helpdeskCompleteStatus',$helpStatuses[$helpdeskCompleteStatus],$helpStatuses,'Helpdesk Complete','What status indicates that a ticket in the helpdesk system is complete?');
        make_select('helpdeskHoldStatus',$helpStatuses[$helpdeskHoldStatus],$helpStatuses,'Helpdesk Hold','What status indicates that a ticket in the helpdesk system is on hold and alerts should not be sent?');
        make_select('generalProductionTicketType',$helpTypes[$generalProductionTicketType],$helpTypes,'General Press Help Type','What is the general press help desk type?');
        
        make_text('remoteMailHostName',$remoteMailHostName,'Mail Server','Server name or IP address of the remote mail system to scan for trouble tickets',50);
        make_text('remoteHelpdeskTicketUsername',$remoteHelpdeskTicketUsername,'Helpdesk Email','Email address for helpdesk ticket submission',50);
        make_text('remoteHelpdeskTicketPassword',$remoteHelpdeskTicketPassword,'Helpdesk Email Password','Password for the helpdesk email account',50);
        make_text('remoteMaintenanceTicketUsername',$remoteMaintenanceTicketUsername,'Maintenance Email','Email address for maintenance ticket submission',50);
        make_text('remoteMaintenanceTicketPassword',$remoteMaintenanceTicketPassword,'Maintenance Email Password','Password  for the maintenance email account',50);
        make_number('resendRateHighestTicket',$resendRateHighestTicket,'Resend Rate','How many minutes apart should tickets at the highest level of criticality be send after the first?');
        
print "</div>\n";
print "<div id='circulation'>\n";
        make_select('circRouteStart',$addresses[$circRouteStart],$addresses,'Route Start','Where do route directions start from?');       
print "</div>\n";
    
print "<div id='inventory'>\n";
        make_number('taxRate',$taxRate,'Tax Rate','Specify the tax rate');
        make_number('poDirectorAmount',$poDirectorAmount,'Director','Amount at which the po requires department director approval');
        make_number('poFinanceAmount',$poFinanceAmount,'Finance','Amount at which the po requires finance director approval');
        make_number('poPublisherAmount',$poPublisherAmount,'Publisher','Amount at which the po requires publisher approval');
        make_checkbox('poEmailVendor',$poEmailVendor,'Email POs',' Check to allow the system to email purchase orders directly to vendors');
print "</div>\n";

print "<div id='timelocks'>\n";
        make_descriptor("All time locks refer to a number of hours before 'publish' date. Publish date is defined as the date you schedule to publish the job, and the time is 00:01 of that day (just after midnight).");
        make_slider('lockPubHours',$lockPubHours,'Pub Time','What time on the publish day is the point from which to start the count? Ex: 6 would be 6am on the publish date',0,23);
        make_number('lockPressPrint',$lockPressPrint,'Print Deadline','What is the minimum number of hours before publish date that a new job can be scheduled to print?');
        make_number('lockPressPub',$lockPressPub,'Publish Deadline','What is the minimum number of hours before publish date that a new job can be scheduled to publish?<br />Ex: setting up a job for wednesday with a minimum of 24 hours means it has to be set up by midnight (or specified hour) on Monday.');
        make_number('lockInsertBook',$lockInsertBook,'Insert Booking','What is the minimum number of hours before publish date that a new insert can be scheduled? This affects the import process as well. Any inserts imported after the specified time will cause an alert to be issued.');
        make_number('lockInsertDelete',$lockInsertDelete,'Insert Deleting','What is the minimum number of hours before packaging date that an insert car be deleted?<br/>This ensures that production operators make adjustments. It will be checked against the packaging date of the package it is included in.');
        make_number('lockBinderyStart',$lockBinderyStart,'Bindery Start','What is the minimum number of hours before publication that a bindery job can be scheduled?');     
print "</div>\n";

make_submit('submit','Save Preferences');
print "</form>\n";
print "</div>\n";
print '
<script>
    $(function() {
        $( "#prefTabs" ).tabs();
    });
    </script>
';
footer();
?>
