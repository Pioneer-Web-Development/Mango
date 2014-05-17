<?php
//this script is designed to handle ajax calls for the inserts.php script
  session_start();
  include("../functions_db.php");
  include("../functions_formtools.php");
  include("../config.php");
  include("../functions_common.php");
  
  if($_POST)
  {
      $action=$_POST['action'];
  } else {
      $action=$_GET['action'];
  }
  
  switch($action)
  {
    case "confirm":
        $form=true;
        confirm();
    break;
    
    case "receive":
        $form=true;
        receive();
        break;
    
    case "schedule":
        $form=true;
        schedule();
    break; 
    
    case "saveconfirm":
        confirm_insert();
    break;
    
    case "savereceive":
        receive_insert();
        break;
    
    case "saveschedule":
        schedule_insert();
    break;
    
    
    case "editpub":
        insertpub('edit');
    break;
    
    case "deletepub":
        deletepub();
    break;
    
    case "saveinsertpub":
        save_insertpub('insert');
    break;
    
    case "updateinsertpub":
        save_insertpub('update');
    break;        
  }
  
 
 
 function deletepub()
 {
     $insertid=intval($_POST['insertid']);
     //ok, work in reverse order
     
     //lets see if this is the main id
     $sql="SELECT main_insert FROM inserts WHERE id=$insertid";
     $dbMain=dbselectsingle($sql);
     if($dbMain['data']['main_insert']==1)
     {
         $main=true;
     } else {
         $main=false;
     }
     //will need to come back later and remove from any insert packages
     
     //delete any zoning
     $sql="DELETE FROM insert_zoning WHERE insert_id='$insertid'";
     $dbDelete=dbexecutequery($sql);
     $error=$dbDelete['error'];
     if($error=='')
     {
         if ($main)
         {
             //don't delete this one, just clear all zoning, pub_id, run_id, insertDate, insertCount
             $sql="UPDATE inserts SET pub_id='0', insert_run_id='0', insert_date=Null, insert_count='0' WHERE id=$insertid";
             $dbUpdate=dbexecutequery($sql);
             $error=$dbUpdate['error'];
         } else {
             //now delete the insert
             $sql="DELETE FROM inserts WHERE id=$insertid";
             $dbDelete=dbexecutequery($sql);
             $error.=$dbDelete['error'];
         }
     }
     if($error=='')
     {
         print "success|";
     } else {
         print "error|$error";
     } 
 }
 
  function insertpub($action)
  {
      global $pubs; 
      $insertid=intval($_POST['insertid']);
      $main=intval($_POST['main']);
      $insertruns[0]='Please choose';
      if($action=='add')
      {
          $insertDate=date("Y-m-d",strtotime("+1 week"));
          $insertCount=0;
          $runid=0;
          $pubid=0;
          $formaction='saveinsertpub';
      } else {
          $sql="SELECT * FROM inserts WHERE id=$insertid";
          $dbInsert=dbselectsingle($sql);
          $insert=$dbInsert['data'];
          $pubid=$insert['pub_id'];
          $runid=$insert['insert_run_id'];
          $insertDate=$insert['insert_date'];
          $insertCount=$insert['insert_count'];
          $formaction='updateinsertpub';
          
          $insertsruns[0]='Please choose';
          $sql="SELECT * FROM publications_insertruns WHERE pub_id='$pubid' ORDER BY run_name";
          $dbRuns=dbselectmulti($sql);
          if($dbRuns['numrows']>0)
          {
              foreach($dbRuns['data'] as $run)
              {
                  $insertruns[$run['id']]=$run['run_name'];
              }
          }
          
      }  
      print "success|";
      print "$pubid|$runid|$insertDate|$insertCount|$formaction|$insertid";
      
      
  }
  

  function save_insertpub($action)
  {
     global $pubs;
     $insertid=$_POST['insertid'];
     $pubid=$_POST['pubid'];
     $runid=$_POST['runid'];
     $insertDate=$_POST['insertDate'];
     $insertCount=$_POST['insertCount'];
     $main=$_POST['maininsert'];
     $zonetotal=$_POST['zonetotal'];
     $zones=$_POST['zones'];
     //find the zones
     //ok, lets do our stuff, first, either update this insert, or create a new one if this is not the main
     if($action=='insert')
     {
         //means it's a new insert, we need to copy the data from the existing one
        $sql="SELECT * FROM inserts WHERE id=$insertid";
        $dbInsert=dbselectsingle($sql);
        $insert=$dbInsert['data'];
        $advertiserid=$insert['advertiser_id'];
        if($advertiserid==''){$advertiserid=0;}
        $salesid=stripslashes($insert['sales_id']);
        if($salesid==''){$salesid=0;}
        $insertTagline=stripslashes($insert['insert_tagline']);
        $receiveDate=stripslashes($insert['receive_date']);
        $shipper=stripslashes($insert['shipper']);
        $printer=stripslashes($insert['printer']);
        $buyCount=stripslashes($insert['buy_count']);
        $received=stripslashes($insert['received']);
        $receiveBy=stripslashes($insert['receive_by']);
        $receiveCount=stripslashes($insert['receive_count']);
        $receiveWeight=stripslashes($insert['receive_weight']);
        $pieceWeight=stripslashes($insert['piece_weight']);
        $productSize=stripslashes($insert['product_size']);
        $pages=stripslashes($insert['pages']);
        if($pages==''){$pages=0;}
        $tabpages=stripslashes($insert['tab_pages']);
        if($tabpages==''){$tabpages=0;}
        $damage=stripslashes($insert['damage']);
        if($damage==''){$damage=0;}
        $insertDamage=stripslashes($insert['insert_damage']);
        $shipType=$insert['ship_type'];
        $shipQuantity=$insert['ship_quantity'];
        $singleSheet=$insert['single_sheet'];
        if($singleSheet==''){$singleSheet=0;}
        $slickSheet=$insert['slick_sheet'];
        if($slickSheet==''){$slickSheet=0;}
        $tagColor=$insert['tag_color'];
        $storageLocation=$insert['storage_location'];
        $insertNotes=$insert['insert_notes'];
        $insertDescription=$insert['insert_description'];
        $receiveBy=$insert['receive_by'];
        $insertionOrder=$insert['insertion_order'];
        $stickyNote=$insert['sticky_note'];
        if($stickyNote==''){$stickyNote=0;}
        $keepRemaining=$insert['keep_remaining'];
        $insertimage=$insert['insert_path'].$insert['insert_image'];
        
        if($siteID==''){$siteID=$GLOBALS['siteID'];}
        $sql="INSERT INTO inserts (advertiser_id, insert_tagline, pub_id, insert_run_id, insert_date, receive_date, received, shipper, printer, insert_count, receive_count, receive_weight, piece_weight, ship_type, ship_quantity, sales_id, product_size, receive_by, pages, tab_pages, insertion_order, single_sheet, slick_sheet, tag_color, storage_location, damage, keep_remaining, insert_notes, insert_damage, insert_description, sticky_note, buy_count, main_insert, group_id, site_id) VALUES ('$advertiserid', '$insertTagline', '$pubid', '$runid', '$insertDate', '$receiveDate', '$received', '$shipper', '$printer', '$insertCount', '$receiveCount', '$receiveWeight', '$pieceWeight', '$shipType', '$shipQuantity', '$salesid', '$productSize', '$receiveBy', '$pages', '$tabpages', '$insertionOrder', '$singleSheet', '$slickSheet', '$tagColor', '$storageLocation', '$damage', '$keepRemaining', '$insertNotes', '$insertDamage', '$insertDescription', '$stickyNote','$buyCount','0','$insertid','$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $insertid=$dbInsert['insertid'];
        
     } else {
         //updating publication, run, insert count and date for an existing insert
         $sql="UPDATE inserts SET pub_id='$pubid', insert_run_id='$runid', insert_date='$insertDate', insert_count='$insertCount' WHERE id=$insertid";
         $dbUpdate=dbexecutequery($sql);
         $error=$dbUpdate['error'];
     }
     $insertNeeded=0;
     if($error=='')
     {
        //ok, next we'll handle zones now that we have the new insert id
         $zones=explode("-",$zones);
         foreach($zones as $key=>$zid)
         {
             $zid=str_replace("check_","",$zid);
             //look up the quantity needed
             $sql="SELECT zone_count FROM publications_insertzones WHERE id=$zid";
             $dbCount=dbselectsingle($sql);
             if($dbCount['error']==''){$insertNeeded+=$dbCount['data']['zone_count'];$zonecount=$dbCount['data']['zone_count'];}else{$zonecount=0;}
             $zoneids.="('$insertid','$zid','$zonecount'),";
             
         }
         $zoneids=substr($zoneids,0,strlen($zoneids)-1);
         //for the sake of ease, we'll just delete any existing insert_zone records and add new ones
         $sql="DELETE FROM insert_zoning WHERE insert_id='$insertid'";
         $dbDelete=dbexecutequery($sql);
         //add the new ones
         $sql="INSERT INTO insert_zoning (insert_id,zone_id, zone_count) VALUES $zoneids";
         $dbInsert=dbinsertquery($sql);
        //find out how many total inserts are needed for the selected zones
        $return="success|";
        $shareid=$insertid;
        if($action=='update')
        {
            $return.="#shared_$insertid|";
        } else {
            $return.="add|<div id='insert$insertid' class='subrow' style='width:530px;'><span id='shared_$insertid'>";
        }
        if($main){$maintext='*MAIN*';}
        $return.= "<span style='float:left;width:200px;'>$maintext".$pubs[$pubid]."</span>";
        $return.= "<span style='float:left;width:80px;'>".$insertDate."</span>";
        $return.= "<span style='float:left;width:70px;'>".$insertCount."</span>";
        $return.= "<span style='float:left;width:90px;'>".$insertNeeded."</span>";
        if($action=='insert')
        {
            $return.="</span>\n";
            $return.="<span style='cursor:pointer;' class='pubFormToggler' onclick='insertEdit($insertid,$main);'>Edit</span>\n";
            $return.="<span style='cursor:pointer;' onclick='insertDelete($insertid,$main);'>Delete</span>\n";
            $return.="<div class='clear'></div>";
        }
        print $return;
    } else {
        print "error|".$error;
    }
        
     
       
  }
  
  
  function receive()
  {
        $shiptypes=array("pallet"=>"Pallet","boxes"=>"Boxes");
        $insertid=intval($_GET['insertid']);
        $sql="SELECT A.*, B.account_name FROM inserts A, accounts B WHERE A.id=$insertid AND A.advertiser_id=B.id";
        $dbInsert=dbselectsingle($sql);
        $insert=$dbInsert['data'];
        $advertisername=stripslashes($insert['account_name']);
        $inserttagline=stripslashes($insert['insert_tagline']);
        $receiveDate=stripslashes($insert['receive_date']);
        $shipper=stripslashes($insert['shipper']);
        $printer=stripslashes($insert['printer']);
        if ($insert['receive_by']!='')
        {
            $receiveby=$insert['receive_by'];
        } else {
            $receiveby=$_SESSION['cmsuser']['userid'];
        }
        $receiveCount=stripslashes($insert['receive_count']);
        $receiveWeight=stripslashes($insert['receive_weight']);
        $damage=stripslashes($insert['damage']);
        $insertDamage=stripslashes($insert['insert_damage']);
        $shipType=$insert['ship_type'];
        $pubid=$insert['pub_id'];
        $shipQuantity=$insert['ship_quantity'];
        $tagColor=$insert['tag_color'];
        $storageLocation=$insert['storage_location'];
        $users=array();
        $users[0]="Please select";
        $sql="SELECT * FROM users ORDER BY firstname, lastname";
        $dbUser=dbselectmulti($sql);
        if($dbUser['numrows']>0)
        {
            foreach($dbUser['data'] as $user)
            {
                $users[$user['id']]=stripslashes($user['firstname']).' '.stripslashes($user['lastname']);
            }
        }
       
        print "<form id='ajaxInsertForm' name='ajaxInsertForm' action='includes/ajax_handlers/insertActions.php' method=post>\n";
        print "<div class='label'>&nbsp;</div><div class='input'>\n";
        if ($_GET['error'])
        {
            print "<span style='color:red;font-weight:bold;'>Please check the box and fill out all information</span><br />\n";    
        }
        
        print "You are confirming receipt of an insert with the following details:<br />\n";
        print "<b>Advertiser:</b> $advertisername<br />\n";
        print "<b>Tagline:</b> $inserttagline<br />\n";
        
        //lets see if it has been scheduled
        $sql="SELECT A.pub_name, B.insert_date FROM publications A, inserts_schedule B WHERE B.insert_id=$insertid AND B.pub_id=A.id";
        $dbSchedule=dbselectmulti($sql);
        if($dbSchedule['numrows']>0)
        {
            print "<div style='float:left;'><b>Scheduled in:</b></div><div style='float:left;margin-left:4px;'>";
            foreach($dbSchedule['data'] as $schedule)
            {
                print stripslashes($schedule['pub_name']).' on '.date("m/d/Y",strtotime($schedule['insert_date']))."<br>";
            }
            print "</div><div style='clear:both;'></div>\n";
        }
        print "<br />";
        print "</div><div class='clear'></div>\n";
        make_checkbox('receive',1,'Receive', ' check to set to received');
        make_select('receivedby',$users[$receiveby],$users,'Received By');
        make_date('receiveDate',$receiveDate,'Date received');
        make_number('receiveCount',$receiveCount,'Receive Count','How many did we get?');
        make_select('shipType',$shiptypes[$shipType],$shiptypes,'Ship type','How did they arrive?');
        make_text('shipQuantity',$shipQuantity,'Ship quantity','How many pallets/boxes?',10,'',false,'','','','return isNumberKey(event);');
        make_number('receiveWeight',$receiveWeight,'Receive Weight','Total weight?');
        make_text('tagColor',$tagColor,'Tag Color','Color of pallet tag');
        if ($GLOBALS['insertUseLocation'])
        {
            $slocations=buildInsertLocations();
            make_select('storageLocation',$slocations[$storageLocation],$slocations,'Storage Location','Storage location code');
        }
        make_text('shipper',$shipper,'Shipper','Who delivered it?');
        make_text('printer',$printer,'Printer','Who printed it?');
        print "<div class='label'>Damage</div><div class='input'>";
        print input_checkbox('damaged',$damage,'toggleInsertDamage();')."Check if the inserts arrived damaged";
        if ($damage){$ddisplay='block';}else{$ddisplay='none;';}
        print "<textarea id='insertDamage' name='insertDamage' cols='40' rows='8' style='display:$ddisplay;'>$insertDamage</textarea>\n";
        print "</div><div class='clear'></div>\n";
        print "<input type='hidden' name='insertid' value='$insertid'>\n";
        print "<input type='hidden' name='action' value='savereceive'>\n";
        print "</form>\n";
        
                ?>
        <script>
        $(document).ready(function() { 
        var options = { 
            //target:        '#output1',   // target element(s) to be updated with server response 
            beforeSubmit:  showRequest,  // pre-submit callback 
            success:       showResponse  // post-submit callback 
        }; 
        $('#ajaxInsertForm').ajaxForm(options); 
        }); 
        </script>
        <?php
  }
  
  
  function confirm()
  {
        $insertid=intval($_GET['insertid']);
        $sql="SELECT A.*, B.account_name FROM inserts A, accounts B WHERE A.id=$insertid AND A.advertiser_id=B.id";
        $dbInsert=dbselectsingle($sql);
        $insert=$dbInsert['data'];
        $pubid=$insert['pub_id'];
        $advertisername=stripslashes($insert['account_name']);
        $inserttagline=stripslashes($insert['insert_tagline']);
        $receivedate=stripslashes($insert['receive_date']);
        $notes=stripslashes($insert['insert_notes']);
        if ($insert['confirm_by']!='' &&$insert['confirm_by']!=0)
        {
            $confirmby=$insert['confirm_by'];
        } else {
            $confirmby=$_SESSION['cmsuser']['userid'];
        }
        $users=array();
        $users[0]="Please select";
        $sql="SELECT * FROM users ORDER BY firstname, lastname";
        $dbUser=dbselectmulti($sql);
        if($dbUser['numrows']>0)
        {
            foreach($dbUser['data'] as $user)
            {
                $users[$user['id']]=stripslashes($user['firstname']).' '.stripslashes($user['lastname']);
            }
        }
       print "<form id='ajaxInsertForm' name='ajaxInsertForm' action='includes/ajax_handlers/insertActions.php' method=post>\n";
        print "<div class='label'>&nbsp;</div><div class='input'>\n";
        if ($_GET['error'])
        {
            print "<span style='color:red;font-weight:bold;'>Please check the box and fill out all information</span><br />\n";    
        }
        
        print "You are confirming an insert with the following details:<br />\n";
        print "Advertiser: $advertisername<br />\n";
        print "Tagline: $inserttagline<br />\n";
        //lets see if it has been scheduled
        $sql="SELECT A.pub_name, B.insert_date FROM publications A, inserts_schedule B WHERE B.insert_id=$insertid AND B.pub_id=A.id";
        $dbSchedule=dbselectmulti($sql);
        if($dbSchedule['numrows']>0)
        {
            print "<div style='float:left;'><b>Scheduled in:</b></div><div style='float:left;margin-left:4px;'>";
            foreach($dbSchedule['data'] as $schedule)
            {
                print stripslashes($schedule['pub_name']).' on '.date("m/d/Y",strtotime($schedule['insert_date']))."<br>";
            }
            print "</div><div style='clear:both;'></div>\n";
        }
        print "<br />";
        print "</div><div class='clear'></div>\n";
        make_checkbox('confirm',1,'Confirm', ' check to confirm');
        make_select('confirm_by',$users[$confirmby],$users,'Confirmed by');
        make_textarea('notes',$notes,'Notes','',40,10);
        print "<input type='hidden' name='insertid' value='$insertid'>\n";
        print "<input type='hidden' name='action' value='saveconfirm'>\n";
        print "</form>\n";
                ?>
        <script>
        $(document).ready(function() { 
        var options = { 
            //target:        '#output1',   // target element(s) to be updated with server response 
            beforeSubmit:  showRequest,  // pre-submit callback 
            success:       showResponse  // post-submit callback 
        }; 
        $('#ajaxInsertForm').ajaxForm(options); 
        }); 
        </script>
        <?php
  }
  
  function schedule()
  {
      global $advertisers;  
      $insertid=intval($_GET['insertid']);
        $sql="SELECT * FROM inserts WHERE id=$insertid";
        $dbInsert=dbselectsingle($sql);
        $insert=$dbInsert['data'];
        $advertisername=$advertisers[$insert['advertiser_id']];
        $pubid=stripslashes($insert['pub_id']);
        $inserttagline=stripslashes($insert['insert_tagline']);
        $receivedate=stripslashes($insert['receive_date']);
        $insertdate=date("Y-m-d",strtotime($insert['insert_date']));
        $notes=stripslashes($insert['insert_notes']);
        if ($insert['scheduled_by']!='' && $insert['scheduled_by']!=0)
        {
            $by=$insert['scheduled_by'];
            
        } else {
            $by=$_SESSION['cmsuser']['userid'];
        }
        $users=array();
        $users[0]="Please select";
        $sql="SELECT * FROM users ORDER BY firstname, lastname";
        $dbUser=dbselectmulti($sql);
        if($dbUser['numrows']>0)
        {
            foreach($dbUser['data'] as $user)
            {
                $users[$user['id']]=stripslashes($user['firstname']).' '.stripslashes($user['lastname']);
            }
        }
        print "<form id='ajaxInsertForm' name='ajaxInsertForm' action='includes/ajax_handlers/insertActions.php' method=post>\n";
        print "<div class='label'>&nbsp;</div><div class='input'>\n";
        if ($_GET['error'])
        {
            print "<span style='color:red;font-weight:bold;'>Please check the box and fill out all information</span><br />\n";    
        }
        print "You are scheduling an insert with the following details:<br />\n";
        print "Publication: $pubs[$pubid]<br />\n";
        print "Advertiser: $advertisername<br />\n";
        print "Tagline: $inserttagline<br />\n";
        print "<br />";
        print "</div><div class='clear'></div>\n";
        /*
        print "<div class='label'>Insert date</div>";
        print "<div class='input'>";
        //build a set of 3 drop down
        $month=date("m",strtotime($insertdate));
        $day=date("d",strtotime($insertdate));
        $year=date("Y",strtotime($insertdate));
        print "<select id='insertdate_month' name='insertdate_month'>\n";
        print "<option value='1' ";
        if ($month==1){print "selected ";}
        print ">January</option>\n";
        print "<option value='2' ";
        if ($month==2){print "selected ";}
        print ">February</option>\n";
        print "<option value='3'";
        if ($month==3){print "selected ";}
        print ">March</option>\n";
        print "<option value='4'";
        if ($month==4){print "selected ";}
        print ">April</option>\n";
        print "<option value='5'";
        if ($month==5){print "selected ";}
        print ">May</option>\n";
        print "<option value='6'";
        if ($month==6){print "selected ";}
        print ">June</option>\n";
        print "<option value='7'";
        if ($month==7){print "selected ";}
        print ">July</option>\n";
        print "<option value='8'";
        if ($month==8){print "selected ";}
        print ">August</option>\n";
        print "<option value='9'";
        if ($month==9){print "selected ";}
        print ">September</option>\n";
        print "<option value='10'";
        if ($month==10){print "selected ";}
        print ">October</option>\n";
        print "<option value='11'";
        if ($month==11){print "selected ";}
        print ">November</option>\n";
        print "<option value='12'";
        if ($month==12){print "selected ";}
        print ">December</option>\n";
        print "</select>\n";
        print " <select id='insertdate_day' name='insertdate_day'>";
        for ($i=1;$i<=31;$i++)
        {
            print "<option value='$i'";
            if($i==$day){print " selected ";}
            print ">$i</option>\n";
        }
        print "</select>\n";
        print " <select id='insertdate_year' name='insertdate_year'>";
        for ($i=2011;$i<=2015;$i++)
        {
            print "<option value='$i'";
            if($i==$day){print " selected ";}
            print ">$i</option>\n";
        }
        print "</select>\n";
        print "</div>\n";
        print "<div class='clear'></div>\n"; 
        */
        print "<div class='label'>Insert Date</div><div class='label'><input class='datepicker' name='insertiondate' id='insertiondate' value='$insertdate' type='text'></div>";
        print "<script type='text/javascript'> \$('#insertiondate').datepicker({dateFormat:'yy-mm-dd'});</script>";
        print "<div class='clear'></div>\n"; 
        //make_date('insert_date',$insertdate,'Insert date');
        make_select('scheduled_by',$users[$by],$users,'Scheduled by');
        make_textarea('notes',$notes,'Notes','',40,10,false);
        print "<input type='hidden' name='insertid' value='$insertid'>\n";
        print "<input type='hidden' name='action' value='saveschedule'>\n";
        print "</form>\n";
        
        ?>
        <script>
        $(document).ready(function() { 
            var options = { 
                //target:        '#output1',   // target element(s) to be updated with server response 
                beforeSubmit:  showRequest,  // pre-submit callback 
                success:       showResponse  // post-submit callback 
            }; 
            $('#ajaxInsertForm').ajaxForm(options); 
        }); 
        </script>
        <?php
  }
  
  
  
function confirm_insert()
{
    $insertid=$_POST['insertid'];
    if ($_POST['confirm'])
    {
        $by=addslashes($_POST['confirm_by']);
        $notes=addslashes($_POST['notes']);
        $confirmdate=addslashes(date("Y-m-d H:i:s"));
        $sql="UPDATE inserts SET confirmed=1, confirm_by='$by', 
        confirm_datetime='$confirmdate', insert_notes='$notes' WHERE id=$insertid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            print $error;
        } else {
            //redirect("?action=list");
        }
    }
}

function schedule_insert()
{
    /*
    $insertid=$_POST['insertid'];
    $by=addslashes($_POST['scheduled_by']);
    $notes=addslashes($_POST['notes']);
    $insertdate=$_POST['insertdate_year']."-".$_POST['insertdate_month']."-".$_POST['insertdate_day'];
    $insertdate=$_POST['insertiondate'];
    $dt=date("Y-m-d H:i:s");
    $sql="UPDATE inserts SET scheduled=1, scheduled_by='$by', scheduled_datetime='$dt', 
    insert_date='$insertdate', insert_notes='$notes' WHERE id=$insertid";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    if ($error!='')
    {
        print $error;
    } else {
        //redirect("?action=list");
    }
    */
}

function receive_insert()
{
    $insertid=$_POST['insertid'];
    if ($_POST['receive'])
    {
        
        $shipper=addslashes($_POST['shipper']);
        $printer=addslashes($_POST['printer']);
        $receiveCount=addslashes($_POST['receiveCount']);
        $receiveWeight=addslashes($_POST['receiveWeight']);
        if($receiveWeight==''){$receiveWeight='0.00';}
        $pieceWeight=addslashes($_POST['pieceWeight']);
        if($pieceWeight==''){$pieceWeight='0.00';}
        if($_POST['damaged']){$damaged=1;}else{$damaged=0;}
        $insertDamage=addslashes($_POST['insertDamage']);
        $shipType=addslashes($_POST['shipType']);
        $shipQuantity=addslashes($_POST['shipQuantity']);
        $tagColor=addslashes($_POST['tagColor']);
        $storageLocation=addslashes($_POST['storageLocation']);
   
        
        
        $by=$_POST['receivedby'];
        $dt=date("Y-m-d H:i:s");
        $date=date("Y-m-d");
        $sql="UPDATE inserts SET received=1, receive_by='$by', receive_datetime='$dt', receive_date='$date', shipper='$shipper', printer='$printer', receive_count='$receiveCount', receive_weight='$receiveWeight', piece_weight='$pieceWeight', damage='$damaged', insert_damage='$insertDamage', ship_type='$shipType', ship_quantity='$shipQuantity', tag_color='$tagColor', storage_location='$storageLocation' WHERE id=$insertid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            print $error;
        } else {
            //redirect("?action=list");
        }
    }
}

dbclose();
?>

