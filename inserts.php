<?php
//<!--VERSION: .7 **||**-->

include("includes/mainmenu.php") ;
if($GLOBALS['debug']){print "refer is ".$_SERVER['HTTP_REFERER']."<br>";}

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
    
switch ($action)
{
    case "Save Insert":
    save_insert();
    break;
    
    case "Update Insert":
    save_insert();
    break;
    
    case "Save w/Confirm":
    save_insert('complete');
    break;
    
    case "Confirm Insert":
    confirm_insert();
    break;
    
    case "Schedule Insert":
    save_schedule();
    break;
    
    case "Receive Insert":
    receive_insert();
    break;
    
    case "Cancel and return":
    inserts('list');
    break;
    
    case "Save Zoning":
    save_zones();
    break;
    
    case "zone":
    build_zones('add');
    break;
    
    case "add":
    inserts('add');
    break;
    
    case "edit":
    inserts('edit');
    break;
    
    case "schedule":
    schedule();
    break;
    
    case "editschedule":
    schedule();
    break;
    
    case "deleteschedule":
    delete_schedule();
    break;
    
    case "confirm":
    inserts('confirm');
    break;
    
    case "receive":
    inserts('receive');
    break;
    
    case "delete":
    delete_insert();
    break;
    
    case "import":
    import();
    break;
    
    case "Process Import":
    process_import();
    break;
    
    
    default:
    show_inserts();
    break;
}

function inserts($action)
{
    global $pubs, $sales, $insertProducts, $advertisers, $wePrintAdvertiserID, $shiptypes;
    global $siteID;
    $advertisers[0]='Please choose(set here to add a new advertiser)';
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Update Insert";
            $insertDate=date("Y-m-d",strtotime("+1 day"));
            $receiveDate=date("Y-m-d");
            $salesid=0;
            $advertiserid=0;
            $keepRemaining=0;
            $receiveBy=$_SESSION['cmsuser']['firstname'];
            $buyCount=0;
            $insertCount=0;
            $receiveCount=0;
            $buyCount=0;
            $receiveWeight='0.00';
            $pieceWeight='0.00';
            $productSize=1;
            $pages=0;
            $tabpages=0;
            $damage=0;
            $shipType='pallet';
            $shipQuantity=0;
            $singleSheet=0;
            $slickSheet=0;
            $stickyNote=0;
            $tagColor='White';
            $storageLocation=0;
            $insertionOrder=0;
            $received=0;
            $pubid=$GLOBALS['defaultInsertPublication'];
        } else {
            $new=0;
            $button="Update Insert";
            $insertid=intval($_GET['insertid']);
            $sql="SELECT * FROM inserts WHERE id=$insertid";
            $dbInsert=dbselectsingle($sql);
            $insert=$dbInsert['data'];
            $advertiserid=$insert['advertiser_id'];
            $salesid=stripslashes($insert['sales_id']);
            $insertTagline=stripslashes($insert['insert_tagline']);
            $receiveDate=stripslashes($insert['receive_date']);
            $insertDate=stripslashes($insert['insert_date']);
            $shipper=stripslashes($insert['shipper']);
            $printer=stripslashes($insert['printer']);
            $buyCount=stripslashes($insert['buy_count']);
            $insertCount=stripslashes($insert['insert_count']);
            $received=stripslashes($insert['received']);
            $receiveBy=stripslashes($insert['receive_by']);
            $receiveCount=stripslashes($insert['receive_count']);
            $receiveWeight=stripslashes($insert['receive_weight']);
            $pieceWeight=stripslashes($insert['piece_weight']);
            $productSize=stripslashes($insert['product_size']);
            $pages=stripslashes($insert['pages']);
            $tabpages=stripslashes($insert['tab_pages']);
            $damage=stripslashes($insert['damage']);
            $insertDamage=stripslashes($insert['insert_damage']);
            $shipType=$insert['ship_type'];
            $shipQuantity=$insert['ship_quantity'];
            $singleSheet=$insert['single_sheet'];
            $slickSheet=$insert['slick_sheet'];
            $tagColor=$insert['tag_color'];
            $storageLocation=$insert['storage_location'];
            $insertNotes=$insert['insert_notes'];
            $insertDescription=$insert['insert_description'];
            $receiveBy=$insert['receive_by'];
            $insertionOrder=$insert['insertion_order'];
            $stickyNote=$insert['sticky_note'];
            $keepRemaining=$insert['keep_remaining'];
            $groupid=$insert['id'];
            $main=$insert['main_insert'];
            $controlNumber=$insert['control_number'];
            $insertimage=$insert['insert_path'].$insert['insert_image'];
        }
        print "<form method=post enctype='multipart/form-data'>\n";
        
        print "<div id='tabs'>\n"; //begins wrapper for tabbed content
        
        print "<ul id='insertInfo'>\n";
        print "<li><a href='#requiredInfo'>Required Insert Information</a></li>\n";   
        print "<li><a href='#insertDescription'>Insert Details</a></li>\n";   
        print "<li><a href='#shipping'>Shipping Details</a></li>\n";   
        print "</ul>\n";
        
        
        
        
        
        print "<div id='requiredInfo'>\n";
            //print "Insertid is still $insertid<br>";
            print "<fieldset>\n";
            print "<legend>Advertiser</legend>\n";
            make_select('salesid',$sales[$salesid],$sales,'Salesperson');
            print "<div class='label'>Advertiser</div><div class='input'>\n";
            print input_select('advertiserid',$advertisers[$advertiserid],$advertisers);
            print "<br />\n<small>If you do not see the advertiser, please enter the name in this box</small><br />\n<input type='text=' name='advertiserName' id='advertiserName' size=20 value='$advertiserName'>\n";
            print "</div><div class='clear'></div>\n";
            make_number('buyCount',$buyCount,'Buy Count','How many has the customer ordered?');
            make_text('insertTagline',$insertTagline,'Insert tagline','Tag line of flyer (ex. 2 day sale)',50);
            make_text('insertionOrder',$insertionOrder,'Insert Order #');
            print "</fieldset>\n";
            
            print "<fieldset>\n";
            print "<legend>Scheduled Publications</legend>\n";
            //lets find all inserts that share this core insert
            print "<div style='float:left;max-width:440px;width:50%'>\n";
                print "<div style='font-weight:bold;font-size:14px;border-bottom:thin solid black;padding-bottom:2px;margin-bottom:2px;'>\n";
                    print "<span style='float:left;width:150px;'>Publication</span>\n";
                    print "<span style='float:left;width:80px;'>Date</span>\n";
                    print "<span style='float:left;width:60px;'>Count</span>\n";
                    print "<span style='float:left;width:90px;'>Zone Count</span>";
                    print "<span style='float:left;width:50px;'>Actions</span>";
                print "<div class='clear'></div>\n";
                print "</div>\n";
                    
                $sql="SELECT * FROM inserts_schedule WHERE insert_id=$insertid ORDER BY insert_date ASC";
                $dbShared=dbselectmulti($sql);
                if($dbShared['numrows']>0)
                {
                    foreach($dbShared['data'] as $shared)
                    {
                        $shareid=$shared['id'];
                        
                        print "<span style='float:left;width:150px;'>$mainlabel".$pubs[$shared['pub_id']]."</span>\n";
                        print "<span style='float:left;width:80px;'>".date("m/d/Y",strtotime($shared['insert_date']))."</span>\n";
                        print "<span style='float:left;width:60px;'>".$shared['insert_quantity']."</span>\n";
                        
                        $sql="SELECT SUM(zone_count) as needed FROM insert_zoning WHERE insert_id=$shareid";
                        $dbCount=dbselectsingle($sql);
                        if($dbCount['error']==''){
                            $insertNeeded=$dbCount['data']['needed'];
                            if($insertNeeded==''){$insertNeeded=0;}
                        }else{
                            $insertNeeded=0;
                        }
                 
                        print "<span style='float:left;width:90px;'>".$insertNeeded."</span>";
                        print "<span style='float:left;width:50px;'>
                        <a href='?action=editschedule&insertid=$insertid&schedid=$shareid'><img src='artwork/icons/pencil_48.png' height=16 alt='Edit'></a>&nbsp;&nbsp;&nbsp;";
                        print "<a href='?action=deleteschedule&insertid=$insertid&schedid=$shareid' class='delete'><img src='artwork/icons/trashcan_glass_128.png' height=16 alt='Delete'></a>\n";
                        print "</span><div class='clear'></div>\n";
                            
                    }
                }
            print "</div>\n";
            print "<div style='float:left;width:50%;'>\n";
                print "<div style='font-weight:bold;font-size:14px;border-bottom:thin solid black;padding-bottom:2px;'>Book this insert into a new publication schedule</div>\n";
                $insertDate=date("Y-m-d",strtotime("+1 week"));
                $insertCount=0;
                $insertruns=array();
                $insertruns[0]='Please choose';
                print "<div style='float:left;margin-right:10px;width:225px;'>\n";
                print "<b>Insert Publication:</b><br>".make_select('pub_id',$pubs[0],$pubs,'','','',false,"getInsertRuns()");
                print "<b>Insert Run Name:</b><br>".make_select('run_id',$insertruns[0],$insertruns,'','','',false,"getInsertRunZones()");
                print "<b>Insertion Date: </br>".make_date('insertDate',$insertDate,'','');
                print "<b>Insertion Quantity:</b><br>".make_number('insertCount',$insertCount,'','');
                print "</div>\n";
                print "<div id='zone_holder' style='float:left;width:300px;'>\n";
                  //generated zones will be insertered here
                print "</div><!--closes the zoneholder div-->\n";
                print "<div class='clear'></div>\n";
                
            
            
            
            print "</div>\n";
            print "</fieldset>\n";
            
            
            print "<fieldset>\n";
            print "<legend>Receiving</legend>\n";
            make_number('controlNumber',$controlNumber,'Control Number','If the insert has been received and you have the control number, enter it here to automatically tie to the received record. If you do this, you do not need to enter other receiving information.');
            make_checkbox('received',$received,'Received','This insert has been received. If not, uncheck and leave receive count at 0');
            make_select('receiveBy',$GLOBALS['productionStaff'][$receiveBy],$GLOBALS['productionStaff'],'Received By');
            make_date('receiveDate',$receiveDate,'Date received');
            make_number('receiveCount',$receiveCount,'Receive Count','How many did we get?');
            print "</fieldset>\n";
            
            print "<fieldset>\n";
            print "<legend>Miscellaneous</legend>\n";
            make_checkbox('keepRemaining',$keepRemaining,'Keep Leftover?','Check to keep leftover for a future run');
            make_checkbox('stickyNote',$stickyNote,'Sticky Note?','Check if product is a sticky note');
            print "</fieldset>\n";
            
        print "</div>\n";    
        
        print "<div id='insertDescription'>\n";
        print "<div id='idleft' style='float:left;'>\n";
            print "<div class='label'>Insert Type</div><div class='input'><small>Insert format, ex: booklet</small><br />\n";
            print "<input type='text' name='pageCount' id='pageCount' value='$pages' size=5 onkeypress='return isNumberKey(event);'> page ";
            print input_select('productSize',$insertProducts[$productSize],$insertProducts);
            print "</div>\n";    
            print "<div class='clear'></div>\n";
            make_number('tabpageCount',$tabpages,'Standard Pages','Standard equivalent pages?');
            make_number('pieceWeight',$pieceWeight,'Piece Weight','Weight of single piece?');
            make_checkbox('singleSheet',$singleSheet,'Single sheet?','Check if product is a single sheet');
            make_checkbox('slickSheet',$slickSheet,'Slick insert?','Check if product is slick');
            make_textarea('insertNotes',$insertNotes,'General Notes','',80,6,true);
            make_textarea('insertDescription',$insertDescription,'Insert description','Full description of insert',80,6,true);
            print "</div>\n";
         print "<div class='ui-widget ui-widget-content ui-corner-all' style='float:right;width:400px;margin-right:10px;border: 1px solid black;padding:10px;'>\n";
        if($insertimage!='' && file_exists($insertimage))
            {
                print "<div style='margin:20px;'>\n";
                print "<img src='$insertimage' border=0 width=350 />\n";
                print "</div>\n";
            }
            make_file('insertimage','Image','Image of insert');        
        print "</div>\n";
        print "<div class='clear'></div>\n";
       print "</div>\n";
        
        print "<div id='shipping'>\n";
            make_number('receiveWeight',$receiveWeight,'Receive Weight','Total weight?',10);
            
            make_text('tagColor',$tagColor,'Tag Color','Color of pallet tag');
            if ($GLOBALS['insertUseLocation'])
            {
                $slocations=buildInsertLocations();
                make_select('storageLocation',$slocations[$storageLocation],$slocations,'Storage Location','Storage location code');
            }
            make_text('shipper',$shipper,'Shipper','Who delivered it?',50);
            make_text('printer',$printer,'Printer','Who printed it?',50);
            make_select('shipType',$shiptypes[$shipType],$shiptypes,'Ship type','How did they arrive?');
            make_number('shipQuantity',$shipQuantity,'Ship quantity','How many pallets/boxes?');
            print "<div class='label'>Damage</div><div class='input'>";
            print input_checkbox('damaged',$damage,'toggleInsertDamage();')."Check if the inserts arrived damaged";
            if ($damage){$ddisplay='block';}else{$ddisplay='none;';}
            print "<textarea id='insertDamage' name='insertDamage' cols='80' rows='8' style='display:$ddisplay;'>$insertDamage</textarea>\n";
            print "</div><div class='clear'></div>\n";
        
        print "</div>\n";
        
        print "<div class='label'></div><div class='input'>\n";
        print "<input type='submit' class='submit' id='submit' name='submit' value='$button'> <input type='submit' class='submit' id='submit' name='submit' value='Save w/Confirm'>\n";
        print "</div>\n";
        print "<div class='clear'></div>\n";
        print "<input type='hidden' name='insertid' value='$insertid'>\n";
        print "</form>\n";
      print "</div><!--closes tabs -->\n";
      print "<div id='dialog'></div>\n";  
      ?>
        <script type='text/javascript'>
        $(function() {
            $( '#tabs' ).tabs();
        });
        </script>
    <?php 
    }
}

function delete_insert()
{
    $insertid=intval($_GET['insertid']);
    $sql="DELETE FROM inserts WHERE id=$insertid";
    $dbDelete=dbexecutequery($sql);
    $error=$dbDelete['error'];
    
    $sql="DELETE FROM inserts_schedule WHERE insert_id=$insertid";
    $dbDelete=dbexecutequery($sql);
    $error.=$dbDelete['error'];
    
    $sql="DELETE FROM insert_zoning WHERE insert_id=$insertid";
    $dbDelete=dbexecutequery($sql);
    $error.=$dbDelete['error'];
    
    $sql="DELETE FROM jobs_packages_inserts WHERE insert_id=$insertid";
    $dbDelete=dbexecutequery($sql);
    $error.=$dbDelete['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem deleting the insert.<br>'.$error,'error');
    } else {
        setUserMessage('Insert has been successfully deleted.','success');
    }
    redirect("?action=list");
}

function show_inserts()
{
   global $pubs, $sales, $insertProducts, $advertisers, $wePrintAdvertiserID, $shiptypes, $siteID;
   
   $sql="SELECT id, run_name FROM publications_runs";
   $dbRuns=dbselectmulti($sql);
   $runs[0]='None';
   if($dbRuns['numrows']>0)
   {
      foreach($dbRuns['data'] as $run)
      {
          $runs[$run['id']]=stripslashes($run['run_name']);
      } 
   }
     //need to build out the search routine.
   //we'll search by advertiser, received date, insert date, status, and pub
   $istatuses=array("Please choose","unscheduled","missing", "received","confirmed","scheduled","planned","inserted");
   $insertdate=date("Y-m-d");
   $advertisers[0]='Please choose';
   if ($_POST['search']=='Search')
   {
        if ($_POST['receive_date']!='')
        { 
            $rdate="AND receive_date>='".$_POST['receive_date']."'";
        } else {
            $rdate='';
        }  
        $dfilter=array();
        $pubid=$_POST['search_pub'];
                
        if ($_POST['insert_date']!='' && $pubid!=0)
        { 
            $sql="SELECT * FROM inserts_schedule WHERE insert_date='$_POST[insert_date]' AND pub_id='$pubid'";
            $dbFilter=dbselectmulti($sql);
            if($dbFilter['numrows']>0)
            {
                foreach($dbFilter['data'] as $filter)
                {
                    $dfilter[]=$filter['insert_id'];
                }
            }
        } elseif($_POST['insert_date']!='')
        {
            $sql="SELECT * FROM inserts_schedule WHERE insert_date='$_POST[insert_date]'";
            $dbFilter=dbselectmulti($sql);
            if($dbFilter['numrows']>0)
            {
                foreach($dbFilter['data'] as $filter)
                {
                    $dfilter[]=$filter['insert_id'];
                }
            }
        } elseif($pubid!=0)
        {
            $sql="SELECT * FROM inserts_schedule WHERE pub_id='$pubid'";
            $dbFilter=dbselectmulti($sql);
            if($dbFilter['numrows']>0)
            {
                foreach($dbFilter['data'] as $filter)
                {
                    if(!in_array($filter['insert_id'],$dfilter))
                    {
                        $dfilter[]=$filter['insert_id'];
                    }
                }
                
            }
            
        }
        if(count($dfilter)>0)
        {
            $dfilter=implode(",",$dfilter);
            $ifilter="AND inserts.id IN ($dfilter)";
        }
        
        $advertiserid=$_POST['search_advertiser'];
        if ($advertiserid>1)
        {
            $advertiser="AND inserts.advertiser_id='$advertiserid'";
        } elseif($advertiserid==$wePrintAdvertiserID)
        {
            $weprinted="AND inserts.weprint_id=>0";
        }
        
        if($_POST['weprint'])
        {
            $weprint='checked';
            $weprinted="AND inserts.weprint_id>=0";
        }else{
            $weprint='';
            $weprinted='';
        }
   
        $status=$_POST['search_status'];
        switch ($istatuses[$status])
        {
            case "missing":
                $status="AND received=0";
            break;
            
            case "received":
                $status="AND received=1";
            break;
            
            case "confirmed":
                $status="AND confirmed=1";
            break;
            
            case "scheduled":
                $status="AND scheduled=1";
            break;
            
            case "un-scheduled":
                $status="AND scheduled=0";
            break;
            
            case "planned":
                $status="AND planned=1";
            break;
            
            case "inserted":
                $status="AND inserted=1";
            break;
           
            default:
                $status='';
            break;    
        }     
   }
   //display search form
   $search="<form method=post>\n";
   $search.= "Insert scheduled to publish on:<br />&nbsp;&nbsp;&nbsp;";
   $search.=input_date('insert_date',$_POST['insert_date']);
   $search.="<br />Advertiser:<br>&nbsp;&nbsp;&nbsp;";
   $search.=input_select('search_advertiser',$advertisers[$_POST['search_advertiser']],$advertisers,false,'',false,'','','',150);
   $search.="<br />Publication:<br>&nbsp;&nbsp;&nbsp;";
   $search.=input_select('search_pub',$pubs[$_POST['search_pub']],$pubs,false,'',false,'','','',150);
   $search.="<br>Received between now and:<br />&nbsp;&nbsp;&nbsp;";
   $search.=input_date('receive_date',$receivedate);
   $search.="<br />Status: ";
   $search.=input_select('search_status',$istatuses[$_POST['search_status']],$istatuses);
   $search.="<br /><input type='checkbox' id='weprint' name='weprint' $weprint /><label for='weprint'> We printed it</label><br>";
   $search.="<br /><input type=submit name='search' id='search' value='Search'></input>\n";
   $search.= "</form>\n"; 
   if ($pub=='' && $rdate=='' && $idate=='' && $status=='' && $advertiser=='')
   {
       $rdate="";
       $status='';
       $week=date("Y-m-d",strtotime("+1 week"));
        $sql="SELECT * FROM inserts_schedule WHERE insert_date>=NOW() AND insert_date<='$week'";
        $dbFilter=dbselectmulti($sql);
        $dfilter='';
        if($dbFilter['numrows']>0)
        {
            $dfilter='';
            foreach($dbFilter['data'] as $filter)
            {
                $dfilter.=$filter['insert_id'].",";
            }
            $dfilter=substr($dfilter,0,strlen($dfilter)-1);
        }
        $idate="AND inserts.id IN ($dfilter)";
        
   }
   $sql="SELECT inserts.*, accounts.account_name FROM inserts, accounts 
   WHERE inserts.advertiser_id=accounts.id AND inserts.clone_id=0  
   $rdate $ifilter $advertiser $status $weprinted 
   ORDER BY account_name LIMIT 500";
   
   if($_POST)
   {
       print "<p style='color:green;font-weight:bold;'>Results displayed are based on the latest search results</p>\n";
       $_SESSION['cmsuser']['queries']['inserts']=$sql;
   } elseif($_SESSION['cmsuser']['queries']['inserts']!='')
   {
       print "<p style='color:green;font-weight:bold;'>Results displayed are based on the latest search results</p>\n";
       $sql=$_SESSION['cmsuser']['queries']['inserts'];
   }
   if($GLOBALS['debug']){ print "Pulling with $sql<br>";}
   $dbInserts=dbselectmulti($sql);
   if($showInsertDate)
   {
        tableStart("<a href='?action=add'>Add insert</a>,<a href='?action=import'>Import VD Manifest</a>","Advertiser,Schedules,Receive Date,Insert Date,Status",11,$search);
   } else {
        tableStart("<a href='?action=add'>Add insert</a>,<a href='?action=import'>Import VD Manifest</a>","Advertiser,Schedules,Receive Date,Status",10,$search);
   }
   if ($dbInserts['numrows']>0)
   {
        foreach($dbInserts['data'] as $insert)
        {
            $insertid=$insert['id'];
            
            //$advertisername=$advertisers[$insert['advertiser_id']];
            if($insert['weprint_id']!=0)
            {
                $sql="SELECT A.pub_name FROM publications A, jobs B WHERE A.id=B.pub_id AND B.id=$insert[weprint_id]";
                $dbPubname=dbselectsingle($sql);
                $wepubname=stripslashes($dbPubname['data']['pub_name']);
                $advertisername="INHOUSE - $wepubname";
            } else {
                $advertisername=stripslashes($insert['account_name']);
            }
            $tagline=stripslashes($insert['insert_tagline']);
            if($insert['receive_date']!='' && $insert['receive_date']!='Null')
            {
                $receivedate=date("m/d/Y",strtotime($insert['receive_date']));
            } else {
                $receivedate='Not received';  
            }
            
            //status section, going from received to inserted
            $status='created';
            if ($insert['confirmed'])
            {
                $status='confirmed';
                $showconfirmed="<span style='color:green;'>Confirmed</span>";
                $notconfirmed="<span style='color:green;'>Confirmed</span>";
            } else 
            {
                $showconfirmed='Confirm';
                $notconfirmed="<a id='lcon_$insertid' title='Confirm Insert' rel='650' href='includes/ajax_handlers/insertActions.php?action=confirm&insertid=$insertid' class='ajaxload'>$showconfirmed</a>";
            }
            if ($insert['scheduled'])
            {
                $status='scheduled';
                $showscheduled="<span style='color:green;'>Scheduled</span>";
                $notscheduled="<a href='?action=schedule&insertid=$insertid&schedid=$schedid'>$showscheduled</a>";
            }else{
                $showscheduled='Schedule';
                $notscheduled="<a href='?action=schedule&insertid=$insertid'>$showscheduled</a>";
            }
            if ($insert['planned']){$status='Planned';}
            if ($insert['inserted']){$status='Inserted';}
            
            if ($insert['received']){
                $notreceived='Received';
            } else {
                $notreceived="<a id='lrec_$insertid' title='Receive Insert' rel='650' style='text-decoration:blink;color:red' href='includes/ajax_handlers/insertActions.php?action=receive&insertid=$insertid' class='ajaxload'>
                Receive</a>";
            }
            print "<tr>\n";
            print "<td><a href='?action=edit&insertid=$insertid'>$advertisername</a>";
            print "<br>$tagline";
            print "</td>\n";
            print "<td>";
            $sql="SELECT * FROM inserts_schedule WHERE insert_id=$insertid";
            $dbSchedules=dbselectmulti($sql);
            if($dbSchedules['numrows']>0)
            {
                $mode='slim';
                if($mode=='select')
                {
                    print "<select>\n";
                    foreach($dbSchedules['data'] as $sched)
                    {
                        print "<option>";
                        print stripslashes($pubs[$sched['pub_id']]);
                        print " - ".date("m/d/Y",strtotime($sched['insert_date']));
                        print " - Count: ".$sched['insert_quantity'];
                        print "</option>";
                    }
                    print "</select>\n<br />\n";
                } elseif($mode=='slim')
                {
                    foreach($dbSchedules['data'] as $sched)
                    {
                        print "<div style='width:300px;margin-bottom:2px;font-size:10px;'>";
                        print stripslashes($pubs[$sched['pub_id']]);
                        print " - ".date("m/d/Y",strtotime($sched['insert_date']));
                        print " - Request: ".$sched['insert_quantity'];
                        print "<a href='?action=editschedule&insertid=$insertid&schedid=$sched[id]'><img src='artwork/icons/pencil_48.png' alt='Edit Schedule' height=16 /></a> ";
                        print "<a href='?action=deleteschedule&insertid=$insertid&schedid=$sched[id]' class='delete'><img src='artwork/icons/trashcan_glass_128.png' alt='Delete Schedule' height=16 /></a>";
                        print "</div>";
                    }
                } else {
                    foreach($dbSchedules['data'] as $sched)
                    {
                        print "<div style='width:200px;border-bottom:thin solid black;padding:bottom:2px;margin-bottom:2px;font-size:10px;'>";
                        print "<div style='float:left;width:150px;'>";
                        print "For: ".stripslashes($pubs[$sched['pub_id']]);
                        print "<br />Date: ".date("m/d/Y",strtotime($sched['insert_date']));
                        print "<br />Insert Request: ".$sched['insert_quantity'];
                        print "</div>";
                        print "<div style='float:right'><a href='?action=editschedule&insertid=$insertid&schedid=$sched[id]'><img src='artwork/icons/pencil_48.png' alt='Edit Schedule' height=16 /></a><br>";
                        print "<a href='?action=deleteschedule&insertid=$insertid&schedid=$sched[id]' class='delete'><img src='artwork/icons/trashcan_glass_128.png' alt='Delete Schedule' height=16 /></a></div>";
                        print "<div class='clear'></div>";
                        print "</div>";
                    }
                }
                    
            }
                print "<a href='?action=schedule&insertid=$insertid' style='font-size:10px;'><img src='artwork/icons/add_48.png' alt='Add another' width=16 /><span style='margin-top:-6px;padding-bottom:6px;'> Add another schedule</span></a>";
                
            print "</td>\n";
            print "<td>$receivedate</td>\n";
            if($showInsertDate)
            {
              print "<td>$insertdate</td>\n";
            }
            print "<td>$status</td>\n";
            print "<td><a href='?action=edit&insertid=$insertid'>Edit</a></td>\n";
            print "<td><span id='rec$insertid'>$notreceived</span></td>\n";
            print "<td><span id='con$insertid'>$notconfirmed</span></td>\n";
            print "<td><a href='?action=delete&insertid=$insertid' class='delete' >Delete</a></td>\n";
            print "</tr>\n";
        }
   }
   print "<div id='loaddialogdiv' style='display:none;'></div>\n";
   $extrascript="
    var ajaxdialog=\$('#loaddialogdiv').dialog({ 
      title: 'Insert Management',        
      autoOpen: false, 
      height: 500, 
      width: 600,
      modal:true,
      buttons: [
          {
            text: 'Cancel',
            click: function() { 
                $(this).dialog('close');
            }
          },
          {
            text: 'Save',
            click: function() { 
                \$('#ajaxInsertForm').submit();
                \$(this).dialog('close');  
            }
          }
      ]
    })
    \$('a.ajaxload').click(function()
    {
        var url = this.href;
        var boxtitle = this.title;
        var dwidth = this.rel;
        var refid=this.id;
        refid=refid.split('_');
        currentid=refid[1];
        currenttype=refid[0];
        ajaxdialog.load(url).dialog('open');
        return false;
    })
";

tableEnd($dbInserts,$extrascript);
?>
<script type='text/javascript'>
    // pre-submit callback 
    function showRequest(formData, jqForm, options) { 
        // formData is an array; here we use \$.param to convert it to a string to display it 
        // but the form plugin does this for you automatically when it submits the data 
        var queryString = $.param(formData); 
     
        // jqForm is a jQuery object encapsulating the form element.  To access the 
        // DOM element for the form do this: 
        // var formElement = jqForm[0]; 
     
        //alert('About to submit: \n\n' + queryString); 
     
        // here we could return false to prevent the form from being submitted; 
        // returning anything other than false will allow the form submit to continue 
        return true; 
    } 
     
    // post-submit callback 
    function showResponse(responseText, statusText, xhr, $form)  { 
        // for normal html responses, the first argument to the success callback 
        // is the XMLHttpRequest object's responseText property 
     
        // if the ajaxForm method was passed an Options Object with the dataType 
        // property set to 'xml' then the first argument to the success callback 
        // is the XMLHttpRequest object's responseXML property 
     
        // if the ajaxForm method was passed an Options Object with the dataType 
        // property set to 'json' then the first argument to the success callback 
        // is the json data object returned by the server 
     
        //alert('status: ' + statusText + '\n\nresponseText: \n' + responseText + 
        //    '\n\nThe output div should have already been updated with the responseText.');
        //alert('type='+currenttype+' and id is '+currentid);
        if(currenttype=='lcon')
        {
           $('#con'+currentid).html('<span style="color:green;">Confirmed</span>');
        } else if(currenttype=='lrec')
        {
           $('#rec'+currentid).html('<span style="color:green;">Received</span>');  
        } else if(currenttype=='lsch')
        {
           $('#sch'+currentid).html('<span style="color:green;">Scheduled</span>');  
        } else if(currenttype=='lzon')
        {
           $('#zon'+currentid).html('<span style="color:green;">Zoned</span>');  
        }
        $('#loaddialogdiv').html('');
    } 
   
</script>
<?php 
}
 
function save_insert()
{
    global $siteID;
    $insertid=$_POST['insertid'];
    $advertiserName=addslashes($_POST['advertiserName']);
    $advertiserid=$_POST['advertiserid'];
    //if advertiser id is 0 and advertiser name is not blank, lets insert this into the 
    //customers table as an advertiser
    if($advertiserid==0 && $advertiserName!='')
    {
        //lets first see if a variation of this name already exists
        $sql="SELECT * FROM accounts WHERE account_advertiser=1 AND LOWER(account_name)='".strtolower($advertiserName)."'";
        $dbExisting=dbselectsingle($sql);
        if ($dbExisting['numrows']>0)
        {
            $advertiserid=$dbExisting['data']['id'];    
        } else {
            $sql="INSERT INTO accounts (site_id, account_name, account_advertiser) VALUES ('$siteID', '$advertiserName', '1')";
            $dbInsert=dbinsertquery($sql);
            if ($dbInsert['error']=='')
            {
                $advertiserid=$dbInsert['insertid'];
            }
        }
        $advertiserName='';
    }
    $insertTagline=addslashes($_POST['insertTagline']);
    $receiveDate=$_POST['receiveDate'];
    $insertDate=$_POST['insertDate'];
    $salesid=$_POST['salesid'];
    $shipper=addslashes($_POST['shipper']);
    $printer=addslashes($_POST['printer']);
    $buyCount=addslashes($_POST['buyCount']);
    $insertCount=addslashes($_POST['insertCount']);
    
    $controlNumber=$_POST['controlNumber'];
    $receiveCount=addslashes($_POST['receiveCount']);
    $receiveWeight=addslashes($_POST['receiveWeight']);
    $pieceWeight=addslashes($_POST['pieceWeight']);
    $size=addslashes($_POST['productSize']);
    $pages=addslashes($_POST['pageCount']);
    $tabpages=addslashes($_POST['tabpageCount']);
    if($_POST['stickyNote']){$stickyNote=1;}else{$stickyNote=0;}
    if($_POST['damaged']){$damaged=1;}else{$damaged=0;}
    if($_POST['singleSheet']){$singleSheet=1;}else{$singleSheet=0;}
    if($_POST['slickSheet']){$slickSheet=1;}else{$slickSheet=0;}
    if($_POST['received']){$received=1;}else{$received=0;}
    if($_POST['keepRemaining']){$keepRemaining=1;}else{$keepRemaining=0;}
    $insertDamage=addslashes($_POST['insertDamage']);
    $shipType=addslashes($_POST['shipType']);
    $shipQuantity=addslashes($_POST['shipQuantity']);
    $tagColor=addslashes($_POST['tagColor']);
    $storageLocation=addslashes($_POST['storageLocation']);
    $insertNotes=addslashes($_POST['insertNotes']);
    $insertDescription=addslashes($_POST['insertDescription']);
    $receiveBy=addslashes($_POST['receiveBy']);
    $insertionOrder=addslashes($_POST['insertionOrder']);
    $loggedin=date("Y-m-d H:i");
    
    
    if($tabpages=='' || $tabpages==0)
    {
        $tabpages=2*$pages;
    }
    
    if ($action=='complete')
    {
        $action='update';
        $dt=date("Y-m-d H:i:s");
        $by=$_SESSION['cmsuser']['userid'];
        $completevalues="confirmed='1',scheduled='1',receive_datetime='$dt', confirm_by='$by', confirm_datetime='$dt', scheduled_by='$by', scheduled_datetime='$dt',";
    } else {
        $completefields='';
        $completevalues='';
    }
    if($insertid=='' || $insertid==0)
    {
        $sql="INSERT INTO inserts (advertiser_id, site_id) VALUES ($advertiserid, '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $insertid=$dbInsert['insertid'];
    }
    $sql="UPDATE inserts SET advertiser_id='$advertiserid', insert_tagline='$insertTagline', receive_date='$receiveDate', shipper='$shipper', printer='$printer', buy_count='$buyCount', receive_count='$receiveCount', receive_weight='$receiveWeight', piece_weight='$pieceWeight', ship_type='$shipType',  ship_quantity='$shipQuantity', sales_id='$salesid', product_size='$size', pages='$pages',  received='$received', receive_by='$receiveBy', insertion_order='$insertionOrder', single_sheet='$singleSheet', slick_sheet='$slickSheet', tag_color='$tagColor', storage_location='$storageLocation', damage='$damaged', insert_notes='$insertNotes', insert_damage='$insertDamage', insert_description='$insertDescription',  sticky_note='$stickyNote', spawned=0, keep_remaining='$keepRemaining', $completevalues tab_pages='$tabpages', control_number='$controlNumber' WHERE id=$insertid";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
     if(isset($_FILES))
     { //means we have browsed for a valid file
        // check to make sure files were uploaded
        foreach($_FILES as $file) {
            switch($file['error']) {
                case 0: // file found
                if($file['name'] != NULL && okFileType($file['type'],'image',$file['name']) != false)  {
                    //get the new name of the file
                    //to do that, we need to push it into the database, and return the last record ID
                    if ($insertid!=0) {
                        // process the file
                        $ext=explode(".",$file['name']);
                        $ext=$ext[count($ext)-1];
                        $datesuffix=date("YmdHi");
                        $newname="insert_".$id."_".$datesuffix.".".$ext;
                        $folder="artwork/inserts/".date("Ym")."/";
                        if (!file_exists($folder))
                        {
                            mkdir($folder);
                        }
                        if(processFile($file,$folder,$newname) == true) {
                            $picsql="UPDATE inserts SET insert_image='$newname', insert_path='$folder' WHERE id=$insertid";
                            $result=dbexecutequery($picsql);
                        } else {
                           $error.= 'There was an error inserting the image named '.$file['name'].' into the database. The sql statement was $sql';  
                        }
                    } else {
                        $error.= 'There was an error because the main record insertion failed.';
                    }
                }
                break;

                case (1|2):  // upload too large
                $error.= 'file upload is too large for '.$file['name'];
                break;

                case 4:  // no file uploaded
                break;

                case (6|7):  // no temp folder or failed write - server config errors
                $error.= 'internal error - flog the webmaster on '.$file['name'];
                break;
            }
        }
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the insert.<br>'.$error,'error');
    } else {
        setUserMessage('Insert has been successfully saved.','success');
        
    }
    tie_received($controlNumber,$insertid);
    if($_POST['pub_id']!=0)
    {
        //means we have set up a schedule as well
        save_schedule($insertid);
    }
    redirect("?action=list");
}


function schedule()
{
    global $pubs;
    $insertid=intval($_GET['insertid']);
    $schedid=intval($_GET['schedid']);
    //now generate the add stuff
    if($schedid>0)
    {
        $sql="SELECT * FROM inserts_schedule WHERE id=$schedid";
        $dbSchedule=dbselectsingle($sql);
        $schedule=$dbSchedule['data'];
        $pubid=$schedule['pub_id'];
        $runid=$schedule['run_id'];
        $insertDate=$schedule['insert_date'];
        $insertCount=$schedule['insert_quantity'];
        //get runs for the pub for the insertruns array
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
    } else {
        $pubid=0;
        $runid=0;
        $insertDate=date("Y-m-d",strtotime("+1 week"));
        $insertCount=0;
        $insertruns=array();
        $insertruns[0]='Please choose';
    }
    
    print "<div style='float:left;margin-right:20px;'>\n";
    print "<form method=post>\n";
    make_select('pub_id',$pubs[$pubid],$pubs,'Publication','','',false,"getInsertRuns()");
    make_select('run_id',$insertruns[$runid],$insertruns,'Run Name','Select the inserter run for this insert','',false,"getInsertRunZones()");
    make_date('insertDate',$insertDate,'Insertion date','When does it insert?');
    make_number('insertCount',$insertCount,'Insert Count','How many are we supposed to insert?');
    print "</div>\n";
    print "<div id='zone_holder' style='float:left;width:500px;'>\n";
      $sql="SELECT * FROM publications_insertzones WHERE run_id=$runid";
      $dbZones=dbselectmulti($sql);
      if($dbZones['numrows']>0)
      {
          print "<p>Please select zones:</p>";
          print "<input type='checkbox' onclick='toggleCheckBoxes(this.checked,\"insertzones\");'>Select / deselect all zones<br />";
          //we are going to format the zones in two columns to conserve space
          $zcount=$dbZones['numrows'];
          $wrapat=round($zcount/3);
          $i=1;
          print "<div style='float:left;width:150px;margin-left:4px;'>\n";
          foreach($dbZones['data'] as $zone)
          {
              //see if this one is checked
              $sql="SELECT * FROM insert_zoning WHERE sched_id=$schedid AND zone_id=$zone[id]";
              $dbCheck=dbselectsingle($sql);
              if($dbCheck['numrows']>0){$checked='checked';$total+=$zone['zone_count'];}else{$checked='';}
              
              print "<input rel='$zone[zone_count]' onClick=\"calcInsertZoneTotal('insertzones')\" class='insertzones' type=checkbox id='check_$zone[id]' name='check_$zone[id]' $checked/> $zone[zone_name]<br />";
              if ($i==$wrapat)
              {
                 print "</div>\n";
                 print "<div style='float:left;width:150px;'>\n";
              }
              $i++;
          }
          print "</div><div class='clear'></div>";
          print "<p><span id='zonetotal' style='font-weight:bold;'>$total</span> total inserts required for selected zones.</p>";
          print "<input type='hidden' id='zoned_total' name='zoned_total' value='$total' />\n";
      }
    print "</div><!--closes the zoneholder div-->\n";
    print "<div class='clear'></div>\n";
    
    make_hidden('insertid',$insertid);
    make_hidden('schedid',$schedid);
    make_submit('submit','Schedule Insert');                                              
    print "</form>\n";                               
}

function save_schedule($insertid=0)
{
     if($insertid==0)
     {
        $insertid=$_POST['insertid'];
     } 
     $schedid=$_POST['schedid'];
     $pubid=$_POST['pub_id'];
     $runid=$_POST['run_id'];
     $insertDate=$_POST['insertDate'];
     $insertCount=$_POST['insertCount'];
     $main=$_POST['maininsert'];
     $zonetotal=$_POST['zonetotal'];
     $zones=$_POST['zones'];
     
     if($schedid==0 || $schedid=='')
     {
         $sql="INSERT INTO inserts_schedule (pub_id, run_id, insert_id, insert_date, insert_quantity) VALUES ('$pubid', '$runid', '$insertid', '$insertDate', '$insertCount')";
         $dbInsert=dbinsertquery($sql);
         $schedid=$dbInsert['insertid'];
         $error=$dbInsert['error'];
     } else {
         $sql="UPDATE inserts_schedule SET pub_id=$pubid, run_id='$runid', insert_date='$insertDate', insert_quantity='$insertCount' WHERE id=$schedid";
         $dbUpdate=dbexecutequery($sql);
         $error=$dbUpdate['error'];
     }
     $userid=$_SESSION['cmsuser']['userid']=$record['id'];
    
     $sql="UPDATE inserts SET scheduled=1, scheduled_by='$userid', scheduled_datetime='".date("Y-m-d H:i")."' WHERE id=$insertid";
     $dbUpdate=dbexecutequery($sql);
     
     if($error=='')
     {
        //ok, next we'll handle zones now that we have the new insert id
         foreach($_POST as $key=>$value)
         {
             if(substr($key,0,6)=='check_')
             {
                 $zid=str_replace("check_","",$key);
                 //look up the quantity needed
                 $sql="SELECT zone_count FROM publications_insertzones WHERE id=$zid";
                 $dbCount=dbselectsingle($sql);
                 if($dbCount['error']==''){$insertNeeded+=$dbCount['data']['zone_count'];$zonecount=$dbCount['data']['zone_count'];}else{$zonecount=0;}
                 $zoneids.="('$schedid', '$insertid','$zid','$zonecount'),";
             }
         }
         $zoneids=substr($zoneids,0,strlen($zoneids)-1);
         //for the sake of ease, we'll just delete any existing insert_zone records and add new ones
         if($zoneids!='')
         {
             $sql="DELETE FROM insert_zoning WHERE sched_id=$schedid";
             $dbDelete=dbexecutequery($sql);
             $error=$dbDelete['error'];
             //add the new ones
             $sql="INSERT INTO insert_zoning (sched_id, insert_id, zone_id, zone_count) VALUES $zoneids";
             $dbInsert=dbinsertquery($sql);
             $error.=$dbInsert['error'];
         }
         if($error=='')
         {
            setUserMessage('Insert has been successfully scheduled.','success');
         } else {
            setUserMessage('There was a problem saving the insert schedule.<br>'.$error,'error');
         }
             
    } else {
           setUserMessage('There was a problem saving the insert schedule.<br>'.$error,'error');
    }
    redirect("?action=list");
    
}

function delete_schedule()
{
    $insertid=intval($_GET['insertid']);
    $schedid=intval($_GET['schedid']);
    $sql="DELETE FROM inserts_schedule WHERE id=$schedid";
    $dbDelete=dbexecutequery($sql);
    if($dbDelete['error']=='')
    {
        $sql="DELETE FROM insert_zoning WHERE sched_id=$schedid";
        $dbDelete=dbexecutequery($sql);
        if($dbDelete['error']=='')
        {
            setUserMessage('Schedule has been successfully deleted for this insert.','success');
        } else {
            setUserMessage('There was a problem deleting the schedule zoning.<br>'.$dbDelete['error'],'error');
        }
    } else {
        setUserMessage('There was a problem deleting the schedule.<br>'.$dbDelete['error'],'error');
    }
    redirect("?action=list");
}

function import()
{
    print "<form method=post enctype='multipart/form-data'>\n";
    make_file('vdfile','Preprint Manifest','Please select the vision data manifest file to upload.');
    make_submit('submit','Process Import');
    print "</form>\n";
}

function process_import()
{
    print "<p><a href='?action=list'>Return to insert list</a></p>";
    print "<p>Inserted records are checked for duplicate entry and the main insert is updated with the current information.</p>";
    global $siteID;
    if(isset($_FILES))
    {
        $file=$_FILES['vdfile']['tmp_name'];
        $contents=file_get_contents($file);
        $inserted=0;
        $updated=0;
        $lines=explode("\n",$contents);
        $badlines=array("Print","AR Ad","Sorte","Profi","-----","Accou");
        $i=0;
        $ads=array();
        foreach($lines as $line)
        {
            $line=trim($line);
            if(substr($line,0,7)=='Printed')
            {
                print "The file you have attempted to load is not in the correct format. Please check your settings, making sure you are loading the .csv file.<br/>";
                print "<a href='?action=import'>Click here to try again with a different file.</a><br />";
                die();
            }
            if(substr($line,0,7)!='Account' && $line!='')
            {
                $lineitems=explode(chr(9),$line);
                //check for comma rather than tab
                if(count($lineitems)==1)
                {
                    $line=convertCSVtoTSV($line);
                    $lineitems=explode(chr(9),$line);
                }
                if($lineitems[0]!='Account #')
                {
                    $ads[$i]['account_number']=addslashes(str_replace("\"","",trim($lineitems[0])));    
                    $ads[$i]['account_name']=addslashes(str_replace("\"","",trim($lineitems[1])));    
                    $ads[$i]['agency_name']=addslashes(str_replace("\"","",trim($lineitems[2])));    
                    $ads[$i]['telephone']=addslashes(str_replace("\"","",trim($lineitems[3])));    
                    $ads[$i]['ad_number']=str_replace("\"","",trim($lineitems[4]));    
                    $ads[$i]['run_date']=str_replace("\"","",trim($lineitems[6]));    
                    $ads[$i]['publication']=str_replace("\"","",trim($lineitems[13]));    
                    $ads[$i]['zone']=str_replace("\"","",trim($lineitems[14]));    
                    $ads[$i]['edition']=str_replace("\"","",trim($lineitems[16]));    
                    $ads[$i]['section']=str_replace("\"","",trim($lineitems[17]));    
                    $ads[$i]['sales']=str_replace("\"","",trim($lineitems[27]));    
                    $desc=str_replace("\"","",trim($lineitems[32])." ".trim($lineitems[33]));
                    $ads[$i]['description']=addslashes($desc);    
                    //look for a C# or c# in the description, break it into words based on ' ' and look at each start
                    $temp=explode(" ",$desc);
                    $controlnumber='';
                    if(count($temp)>0)
                    {
                        $wordloop=0;
                        foreach($temp as $key=>$word)
                        {
                            if(substr($word,0,2)=='C#' || substr($word,0,2)=='c#')
                            {
                                //print "<b>FOUND!</b>";
                                $controlnumber=str_replace('c#','',$word);
                                $controlnumber=str_replace('C#','',$controlnumber);
                                if(strlen($controlnumber)<3 && $wordloop<count($temp))
                                {
                                    //looks like this is too short, possible space between parts of control number,
                                    //lets append the next word to it
                                    $controlnumber.=$temp[$wordloop+1];    
                                }
                                if(strlen($controlnumber)<5 && $wordloop<(count($temp)-1))
                                {
                                    //looks like this is still too short, possible space between parts of control number,
                                    //lets append the next word to it
                                    $controlnumber.=$temp[$wordloop+2];    
                                }
                                //now uppercase and get rid of hyphens
                                $controlnumber=strtoupper(str_replace("-","",$controlnumber));
                                //print "--><b>$controlnumber</b>";
                            }
                            $wordloop++;
                        }
                    }
                    $ads[$i]['control_number']=$controlnumber;
                    
                    $ads[$i]['po_number']=addslashes(str_replace("\"","",trim($lineitems[34])));    
                    $ads[$i]['pages']=addslashes(str_replace("\"","",trim($lineitems[35])));    
                    $ads[$i]['quantity']=addslashes(str_replace("\"","",trim($lineitems[36])));    
                    $ads[$i]['misc']=addslashes(str_replace("\"","",trim($lineitems[37])));
                    if ($ads[$i]['pages']==''){$ads[$i]['pages']=0;}
                    $i++;
                }
            }
        }
        if($GLOBALS['debug']){ print "Ads found<br><pre>";print_r($ads);print "</pre>\n";}
   
        $dt=date("Y-m-d H:i");
        if(count($ads)>0)
        {
            
            //now insert them in the database
            foreach($ads as $key=>$record)
            {
                if($record['account_number']!='' || $record['account_name']!='')
                {
                    $error='';
                    //figure out the customer_id from the VD account number
                    $sql="SELECT A.id FROM accounts A, accounts_vd B WHERE B.vd_account_number='$record[account_number]' AND A.id=B.account_id";
                    $dbCustomer=dbselectsingle($sql);
                    if($dbCustomer['numrows']>0)
                    {
                        $accountid=$dbCustomer['data']['id'];
                        if($GLOBALS['debug']){ print "Found account id of $accountid for $record[account_number]<br>";}
                        
                    } else {
                        //that vision data advertiser does not exist in the account table. Lets add it
                        $sql="INSERT INTO accounts (site_id, account_name, account_advertiser, vision_data_name) VALUES 
                        ('$siteID', '".addslashes($record['account_name'])."', '1', '".addslashes($record['account_name'])."')";
                        $dbInsert=dbinsertquery($sql);
                        $accountid=$dbInsert['insertid'];
                        //now add the account_vd record
                        $sql="INSERT INTO accounts_vd (account_id, vd_account_number) VALUES ('$accountid','$record[account_number]')";
                        $dbInsertVD=dbinsertquery($sql);
                        if($GLOBALS['debug']){ print "Created a new account record with id $accountid for $record[account_number]<br>&nbsp;&nbsp;&nbsp;&nbsp;The error, if any was: $dbInsert[error]<br />&nbsp;&nbsp;&nbsp;&nbsp;and $dbInsertVD[error]<br />";}
                        
                    }
                    //figure out the customer_id from the VD account number
                    $sql="SELECT * FROM users WHERE vision_data_sales_id='$record[sales]'";
                    $dbSales=dbselectsingle($sql);
                    if($dbSales['numrows']>0)
                    {
                        $salesid=$dbSales['data']['id'];
                    } else {
                        $salesid=0;
                    }
                    //see if there is an ad with this number already existing. if there is, just update the details
                    $sql="SELECT id FROM inserts WHERE ad_number='$record[ad_number]'";
                    $dbCheck=dbselectsingle($sql);
                    
                    
                    $pubsql="SELECT * FROM publications WHERE vision_data_pub='$record[publication]' AND vision_data_edition LIKE '%$record[edition]%'";
                    $dbPub=dbselectsingle($pubsql);
                    if($dbPub['numrows']>0)
                    {
                        $pubid=$dbPub['data']['id'];
                    } else {
                        $pubid=0;
                    }
                    $insertdate=date("Y-m-d",strtotime($record['run_date']));
                    $insertday=date("w",strtotime($record['run_date']));
                    
                    //figure out if there is an insert run for this pub date
                    $runsql="SELECT * FROM publications_insertruns WHERE pub_id='$pubid' AND run_days LIKE '%$insertday%'";
                    $dbRun=dbselectsingle($runsql);
                    if($dbRun['numrows']>0)
                    {
                        $runid=$dbRun['data']['id'];
                    } else {
                        $runid=0;
                    }
                    
                    
                    if($dbCheck['numrows']==0)
                    {
                        //create the insert record
                        $insertsql="INSERT INTO inserts (control_number, advertiser_id, insert_tagline, buy_count, sales_id, pages, received, insertion_order, insert_description, tab_pages, scheduled, created_by, created_datetime, site_id, ad_number) VALUES ('$record[control_number]', $accountid, '$record[description] $record[ad_number]',  '$record[quantity]','$salesid', '$record[pages]', 0, '$record[po_number]', '$record[description] $record[misc]', '$record[pages]', 0, 0, '$dt', '$siteID', '$record[ad_number]')";
                        $dbInsert=dbinsertquery($insertsql);
                        $insertid=$dbInsert['insertid'];
                        if($GLOBALS['debug']){ print "Inserting a new insert  with $insertsql<br>";}
                        $error.=$dbInsert['error'];
                        if($dbInsert['error']=='')
                        {
                            //now create the schedule record
                            //need to look up the pub_id from the publications based on the vision data pub
                            $schedsql="INSERT INTO inserts_schedule (insert_id, pub_id, run_id, insert_quantity, insert_date) VALUES 
                            ('$insertid', '$pubid', '$runid', '$record[quantity]', '$insertdate')";
                            $dbInsert=dbinsertquery($schedsql);
                            $error.=$dbInsert['error'];
                            
                            $inserted++;
                        }
                    } else {
                        $insertid=$dbCheck['data']['id'];
                        $insertsql="UPDATE inserts SET control_number='$record[control_number]', advertiser_id='$accountid', insert_tagline='$record[description] $record[ad_number]',  buy_count='$record[quantity]', sales_id='$salesid', pages='$record[pages]', insertion_order='$record[po_number]', insert_description='$record[description] $record[misc]', tab_pages='$record[pages]', ad_number='$record[ad_number]' WHERE id=$insertid";
                        $dbUpdate=dbexecutequery($insertsql);
                        if($dbUpdate['error']=='')
                        {
                            $updated++;     
                        } else {
                            print "Failed update sql is $insertsql<br>";
                        }
                        $insertdate=date("Y-m-d",strtotime($record['run_date']));
                        if($GLOBALS['debug']){ print "Updating an existing insert  with $insertsql<br>";}
                            
                        //see if there is an existing schedule for this pub and insert id and update accordingly
                        $sql="SELECT * FROM inserts_schedule WHERE insert_id=$insertid AND pub_id=$pubid AND insert_date='$insertdate'";
                        $dbCheck=dbselectsingle($sql);
                        if($dbCheck['numrows']>0)
                        {
                            $schedid=$dbCheck['data']['id'];
                            $schedsql="UPDATE inserts_schedule SET insert_quantity='$record[quantity]' WHERE id=$schedid";
                            $dbUpdate=dbexecutequery($schedsql);
                            if($GLOBALS['debug']){ print "Updating an exisiting schedule for this insert with $schedsql<br>";}
                        } else {
                            $schedsql="INSERT INTO inserts_schedule (insert_id, pub_id, run_id, insert_quantity, insert_date) 
                            VALUES ('$insertid', '$pubid', '$runid', '$record[quantity]', '$insertdate')";
                            $dbInsert=dbinsertquery($schedsql);
                            $error.=$dbInsert['error'];
                            if($GLOBALS['debug']){ print "Inserting a new schedule for this insert with $schedsql<br>";}
                        }
                        
                    }
                    if($GLOBALS['debug'] && $error!=''){ print $error;}
                        
                    //see if there is a matching insert_received record based on the control number
                    tie_received($controlnumber,$insertid);
                    
                    //print "Ran $insertsql for the insert and $schedsql for the schedule and $pubsql for the pub and $runsql for the run<br>";
                } else {
                    if($GLOBALS['debug']){ print "Account number and account name were both blank for record $key<br>";}
                        
                }
            }
            print "Inserted $inserted records and updated $updated.<br>";
            
        } else {
            print "Sorry, there were no ad records found in this file<br>";
        }   
    }
    print "<p><a href='?action=list'>Return to insert list</a></p>";
    if($error!='')
    {
        print $error;
    } 
}

function tie_received($controlNumber,$insertid)
{
    $sql="SELECT * FROM inserts_received WHERE control_number='$controlNumber'";
    $dbReceived=dbselectsingle($sql);
    if($dbReceived['numrows']>0)
    {
        $receivedInsert=$dbReceived['data'];
        if($receivedInsert['matched']==0)
        {
            $sql="UPDATE inserts_received SET matched=1 WHERE id=$receivedInsert[id]";
            $dbUpdate=dbexecutequery($sql);
            
            //now update the insert record with the data from the inserts_received
            
            
            $sql="UPDATE inserts SET received=1, receive_date='$receivedInsert[receive_date]', receive_count='$receivedInsert[receive_count]', receive_datetime='$receivedInsert[receive_datetime]', ship_type='$receivedInsert[ship_type]',
            ship_quantity='$receivedInsert[ship_quantity]', receive_by='$receivedInsert[receive_by]', 
            single_sheet='$receivedInsert[single_sheet]', slick_sheet='$receivedInsert[slick_sheet]',
            tag_color='$receivedInsert[tag_color]', storage_location='$receivedInsert[storage_location]',
            damage='$receivedInsert[damage]', insert_damage='$receivedInsert[insert_damage]',
            sticky_note='$receivedInsert[sticky_note]' WHERE id=$insertid";
            $dbUpdate=dbexecutequery($sql);
        }
        print "For insert $record[account_name] we found a receive record with $controlNumber!<br>";
    }
}

footer();
?>

