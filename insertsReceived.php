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
    
    case "edit":
    inserts('edit');
    break;
    
    case "delete":
    inserts('delete');
    break;
    
    default:
    inserts('list');
    break;
}

function inserts($action)
{
    global $pubs, $sales, $insertProducts, $advertisers, $wePrintAdvertiserID, $shiptypes;
    global $siteID;
    $advertisers[0]='Please choose(set here to add a new advertiser)';
    if($action=='edit')
    {
        $new=0;
        $button="Update Insert";
        $insertid=intval($_GET['id']);
        $sql="SELECT * FROM inserts_received WHERE id=$insertid";
        $dbInsert=dbselectsingle($sql);
        $insert=$dbInsert['data'];
        $advertiserid=$insert['advertiser_id'];
        $insertTagline=stripslashes($insert['insert_tagline']);
        $receiveDate=stripslashes($insert['receive_date']);
        $insertDate=stripslashes($insert['scheduled_pubdate']);
        $pubid=stripslashes($insert['insert_pub_id']);
        $shipper=stripslashes($insert['shipper']);
        $printer=stripslashes($insert['printer']);
        $buyCount=stripslashes($insert['buy_count']);
        $insertCount=stripslashes($insert['insert_count']);
        $received=stripslashes($insert['received']);
        $receiveBy=stripslashes($insert['receive_by']);
        $receiveDate=stripslashes($insert['receive_date']);
        $receiveCount=stripslashes($insert['receive_count']);
        $receiveWeight=stripslashes($insert['receive_weight']);
        $pieceWeight=stripslashes($insert['piece_weight']);
        $productSize=stripslashes($insert['product_size']);
        $pages=stripslashes($insert['pages']);
        $stdpages=stripslashes($insert['std_pages']);
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
        $insertionOrder=$insert['insertion_order'];
        $stickyNote=$insert['sticky_note'];
        $keepRemaining=$insert['keep_remaining'];
        $controlNumber=$insert['control_number'];
        $runability=$insert['runability'];
        $insertimage=$insert['insert_path'].$insert['insert_image'];
        print "<form enctype='multipart/form-data' method=post>\n";
        print "<div id='tabs'>\n";
        
        print "<ul>\n";
        print "<li><a href='#basic'>Basic Information</a></li>\n";
        print "<li><a href='#detail'>More Detail</a></li>\n";
        print "</ul>\n";
        print "<div id='basic'>\n";
            make_text('controlNumber',$controlNumber,'Control #');
            make_select('pub',$pubs[$pubid],$pubs,'Publication');
            make_date('pubdate',$insertDate,'Publication Date');
            print "<div class='label'>Advertiser</div><div class='input'>\n";
            print input_select('advertiserid',$advertisers[$advertiserid],$advertisers);
            print "<br />\n<small>If you do not see the advertiser, please enter the name in this box</small><br />\n<input type='text=' name='advertiserName' id='advertiserName' size=20 value='$advertiserName'>\n";
            print "</div><div class='clear'></div>\n";
            make_text('insertTagline',$insertTagline,'Insert tagline','Tag line of flyer (ex. 2 day sale)',50);
            make_checkbox('received',1,'Received','This insert has been received. If not, uncheck and leave receive count at 0');
            make_select('receiveBy',$GLOBALS['productionStaff'][$receiveBy],$GLOBALS['productionStaff'],'Received By');
            make_date('receiveDate',$receiveDate,'Date received');
            make_number('receiveCount',$receiveCount,'Receive Count','How many did we get?');
            print "<div class='label'>Insert Type</div><div class='input'><small>Insert format, ex: booklet</small><br />\n";
            print "<input type='text' name='pageCount' id='pageCount' value='$pages' size=5 onkeypress='return isNumberKey(event);'> page ";
            print input_select('productSize',$insertProducts[$productSize],$insertProducts);
            print "</div>\n";    
            print "<div class='clear'></div>\n";
            make_number('standardPageCount',$stdpages,'Standard Pages','Standard equivalent pages?');
            make_number('runability',$runability,'Runability','How easy is this insert to run? 1=hard 10=easy');
            make_number('pieceWeight',$pieceWeight,'Piece Weight','Weight of single piece?');
            
        print "</div>\n";
        print "<div id='detail'>\n";
            make_checkbox('stickyNote',$stickyNote,'Sticky Note?','Check if product is a sticky note');
            make_checkbox('singleSheet',$singleSheet,'Single sheet?','Check if product is a single sheet');
            make_checkbox('slickSheet',$slickSheet,'Slick insert?','Check if product is slick');
            make_textarea('insertDescription',$insertDescription,'Insert description','Full description of insert',50,3,false);
            make_text('tagColor',$tagColor,'Tag Color','Color of pallet tag');
            if ($GLOBALS['insertUseLocation'])
            {
                $slocations=buildInsertLocations();
                make_select('storageLocation',$slocations[$storageLocation],$slocations,'Storage Location','Storage location code');
            }
            make_file('insertPhoto','Photo','If you have a photo of the insert cover, upload it.');
            make_text('printer',$printer,'Printer','Not required',50);
            make_text('shipper',$shipper,'Shipper','Not required',50);
            make_select('shipType',$shiptypes[$shipType],$shiptypes,'Ship type','How did they arrive?');
            make_number('shipQuantity',0,'Ship quantity','How many pallets/boxes?');
            print "<div class='label'>Damage</div><div class='input'>";
            print input_checkbox('damaged',$damage,'toggleInsertDamage();')."Check if the inserts arrived damaged";
            if ($damage){$ddisplay='block';}else{$ddisplay='none;';}
            print "<textarea id='insertDamage' name='insertDamage' cols='50' rows='8' style='display:$ddisplay;'>
            $insertDamage</textarea>\n";
            print "</div><div class='clear'></div>\n";
            print "</div>\n";
        print "<input type='submit' id='submit' name='submit' value='Save Insert' style='float:left;margin-left:200px;margin-top:4px;'>\n";
        print "</div>\n";    
        make_hidden('insertid',$insertid);
        print "</form>\n";
        ?>
        <script type="text/javascript">
        $('#tabs').tabs();
        </script>
        <?php
          
    }elseif ($action=='delete')
    {
        $insertid=intval($_GET['id']);
        $sql="DELETE FROM inserts_received WHERE id=$insertid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the received insert.<br>'.$error,'error');
        } else {
            setUserMessage('The receieved insert has been successfully deleted.','success');
        }
        redirect("?action=list");
    } else {
       
       //need to build out the search routine.
       //we'll search by advertiser, received date, insert date, status, and pub
       $insertdate=date("Y-m-d");
       $advertisers[0]='Search all';
       $pubs[0]='Search all';
       if ($_POST['search']=='Search')
       {
            if ($_POST['receive_date']!='')
            { 
                $rdate="AND A.receive_date>='".$_POST['receive_date']."'";
            } else {
                $rdate='';
            }  
            if ($_POST['insert_date']!='')
            { 
                $rdate="AND A.scheduled_pubdate>='".$_POST['insert_date']."'";
            } else {
                $rdate='';
            }  
            $pubid=$_POST['search_pub'];
            if ($pubid!=0)
            {
                $pub="AND A.insert_pub_id='$pubid'";
            }
            $advertiserid=$_POST['search_advertiser'];
            if ($advertiserid>1)
            {
                $advertiser="AND A.advertiser_id='$advertiserid'";
            }
            $controlNumber=$_POST['control'];
            if ($controlNumber!='')
            {
                $control="AND A.control_number='$controlNumber'";
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
       $search.="<br>Control #:<br />&nbsp;&nbsp;&nbsp;";
       $search.=input_text('control',$controlNumber,'Control Number');
       $search.="<br /><input type=submit name='search' id='search' value='Search'></input>\n";
       $search.= "</form>\n"; 
       
       $sql="SELECT A.*, B.account_name FROM inserts_received A, accounts B 
       WHERE A.advertiser_id=B.id AND A.site_id=$siteID $rdate $idate $pub $advertiser $control ORDER BY B.account_name LIMIT 500";
       
       if($_POST)
       {
           print "<p style='color:green;font-weight:bold;'>Results displayed are based on the latest search results</p>\n";
           $_SESSION['cmsuser']['queries']['inserts_received']=$sql;
       } elseif($_SESSION['cmsuser']['queries']['inserts_received']!='')
       {
           print "<p style='color:green;font-weight:bold;'>Results displayed are based on the latest search results</p>\n";
           $sql=$_SESSION['cmsuser']['queries']['inserts_received'];
       }
       if($GLOBALS['debug']){ print "Pulling with $sql<br>";}
       //print "Pulling with $sql<br>";
       $dbInserts=dbselectmulti($sql);
       tableStart("<a href='insertQuickAdd.php?action=add'>Receive insert</a>",
       "Control#,Advertiser,Publication,Receive Date,Insert Date, Matched",12,$search);
       if ($dbInserts['numrows']>0)
       {
            foreach($dbInserts['data'] as $insert)
            {
                $insertid=$insert['id'];
                
                //$advertisername=$advertisers[$insert['advertiser_id']];
                $advertisername=stripslashes($insert['account_name']);
                $tagline=stripslashes($insert['insert_tagline']);
                $control=stripslashes($insert['control_number']);
                if($insert['matched'])
                {
                    $matched='Matched';
                } else {
                    $matched='Not matched'; 
                }
                if($insert['insert_pub_id']==0)
                {
                    $publication='Not specified';    
                } else {
                    $publication=$pubs[$insert['insert_pub_id']];
                }
                $insertdate=date("D m/d/Y",strtotime($insert['scheduled_pubdate']));
                $receivedate=date("D m/d/Y",strtotime($insert['receive_date']));
                print "<tr>\n";
                print "<td><a href='?action=edit&id=$insertid'>$control</a>";
                print "<br>$tagline";
                print "</td>\n";
                print "<td>$advertisername</td>";
                print "<td>$publication</td>";
                print "<td>$receivedate</td>\n";
                print "<td>$insertdate</td>\n";
                print "<td>$matched</td>\n";
                print "<td><a href='?action=edit&id=$insertid'>Edit</a></td>\n";
                print "<td><a href='?action=delete&id=$insertid' class='delete' >Delete</a></td>\n";
                print "</tr>\n";
            }
       }
       
    
    tableEnd($dbInserts);
       
       
    }


}
 
function save_insert()
{
    $insertid=$_POST['insertid'];
    $pub=$_POST['pub'];
    $pubdate=$_POST['pubdate'];
    $advertiserid=$_POST['advertiserid'];
    $advertisername=addslashes($_POST['advertiserName']);
    if($advertiserid==0 && $advertisername!='')
    {
        $sql="INSERT INTO accounts (account_name, account_advertiser, site_id) VALUES ('$advertisername', '1', $siteID)";
        $dbInsert=dbinsertquery($sql);
        $advertiserid=$dbInsert['insertid'];
    }
    $tagline=addslashes($_POST['insertTagling']);
    if($_POST['received']){$received=1;}else{$received=0;}
    if($_POST['singleSheet']){$singleSheet=1;}else{$singleSheet=0;}
    if($_POST['slickSheet']){$slickSheet=1;}else{$slickSheet=0;}
    if($_POST['stickyNote']){$stickyNote=1;}else{$stickyNote=0;}
    if($_POST['damaged']){$damaged=1;}else{$damaged=0;}
    $receiveBy=$_POST['receiveBy'];
    $receiveDate=$_POST['receiveDate'];
    $receiveDateTime=date("Y-m-d H:i");
    $receiveCount=$_POST['receiveCount'];
    $receiveWeight=$_POST['receiveWeight'];
    $pieceWeight=$_POST['pieceWeight'];
    $pageCount=$_POST['pageCount'];
    $productSize=$_POST['productSize'];
    $standardPages=$_POST['standardPageCount'];
    $shipType=$_POST['shipType'];
    $shipQuantity=$_POST['shipQuantity'];
    $insertDescription=addslashes($_POST['insertDescription']);
    $insertDamage=addslashes($_POST['insertDamage']);
    $shipper=addslashes($_POST['shipper']);
    $storageLocation=addslashes($_POST['storageLocation']);
    $tagColor=addslashes($_POST['tagColor']);
    $printer=addslashes($_POST['printer']);
    $controlNumber=addslashes($_POST['controlNumber']);
    $runability=addslashes($_POST['runability']);
    
    
    if($receiveCount==''){$receiveCount=0;}
    if($pageCount==''){$pageCount=0;}
    if($standardPages==''){$standardPages=0;}
    if($shipQuantity==''){$shipQuantity=0;}
    
    $id=$_POST['id'];
    
    $sql="UPDATE inserts_received SET received='$received', receive_date='$receiveDate', receive_count='$receiveCount', 
    shipper='$shipper', printer='$printer', piece_weight='$pieceWeight', ship_type='$shipType', ship_quantity='$shipQuantity', 
    advertiser_id='$advertiserid', product_size='$productSize', pages='$pageCount', std_pages='$standardPages', 
    receive_by='$receiveBy', single_sheet='$singleSheet', slick_sheet='$slickSheet', tag_color='$tagColor', control_number='$controlNumber', storage_location='$storageLocation', damage='$damaged', insert_damage='$insertDamage', insert_tagline='$tagline', runability='$runability', insert_description='$insertDescription', sticky_note='$stickyNote', insert_pub_id='$pub', scheduled_pubdate='$pubdate'  
    WHERE id=$insertid";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    

    
    if(isset($_FILES))
    {
        if(isset($_FILES)) { //means we have browsed for a valid file
        // check to make sure files were uploaded
        foreach($_FILES as $file) {
            switch($file['error']) {
                case 0: // file found
                if($file['name'] != NULL && okFileType($file['type'],'image',$file['name']) != false)  {
                    //get the new name of the file
                    //to do that, we need to push it into the database, and return the last record ID
                    if ($insertid!=0) {
                        $filename=$file['name'];
                        $ofile=$filename;
                        $ext=end(explode(".",$filename));
                        $filename='insert_'.$insertid.'.'.$ext;
                        //check for folder, if not present, create it
                        $foldername=date("Ym");
                        if(!file_exists("artwork/inserts/".$foldername))
                        {
                            mkdir("artwork/inserts/".$filename);
                        }
                        if(processFile($file,"artwork/inserts/",$filename) == true) {
                            $sql="UPDATE inserts_received SET insert_image='$filename', insert_path='$foldername' WHERE id=$insertid";
                            $result=dbexecutequery($sql);
                        } else {
                           $error.= 'There was an error processing the addressing file: '.$file['name'];  
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
    }
    
    if($error=='')
    {
        setUserMessage('Received insert record successfully updated.','success');
    } else {
        setUserMessage('There was a problem updating the received insert record.<br>'.$error,'error');
    }
    redirect("?action=list");
}

  

footer();
?>

