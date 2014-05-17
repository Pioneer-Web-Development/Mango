<?php
/*
* THIS SCRIPT IS DESIGNED TO ALLOW A MAILROOM WORKER TO QUICKLY RECEIVE AN INSERT FROM A TRUCK
* IT REQUIRES THEY BE ABLE TO LOOK UP THE ADVERTISER.
* THE ONLY REQUIRED INFORMATION IS ADVERTISER
* IT WILL ALLOW GATHERING OF DATE, NUMBER OF PALLETS/BOXES, PIECES, NOTES, AND RECEIVE DATE/TIME/BY
* 
* DATA IS PLACED DIRECTLY INTO INSERTS TABLE WITH A "RECEIVED" FLAG BEING SET TO TRUE.
* WHEN THEY SELECT THE INSERT, THEY WILL HAVE THE OPTION OF SELECTING FROM A LIST OF ALREADY BOOKED ONES
* IF THEY CAN'T FIND A MATCH, THEN A NEW RECORD WILL BE CREATED
* 
* WE WILL ALSO GENERATE A DASHBOARD WIDGET THAT WILL SHOW ALL INSERTS THAT HAVE BEEN RECEIVED BUT NOT BOOKED
* 
* WE WILL ALSO NEED A NEW FIELD IN THE INSERTS TABLE CALLED BOOKED WITH A BOOKED_BY AND A BOOKED_DATETIME
* 
* WE ALSO NEED A GLOBAL PREFERENCE TO TOGGLE AN EMAIL TO SOMEONE (ALSO A PREFERENCE) THAT A NEW INSERT HAS BEEN RECEIVED
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
  case "Save Insert":
    check_insert();    
  break;
  
  default:
    insert();
  break;
}

function insert()
{
    global $pubs, $advertisers, $insertProducts, $shiptypes;
    $receiveBy=$_SESSION['cmsuser']['firstname'];
    print "<form enctype='multipart/form-data' method=post>\n";
    print "<input type='submit' id='submit' name='submit' value='Save Insert' style='float:right;margin-right:30px;margin-bottom:4px;'><div class='clear'></div>\n";
    print "<div id='tabs'>\n";
    
    print "<ul>\n";
    print "<li><a href='#basic'>Basic Information</a></li>\n";
    print "<li><a href='#detail'>More Detail</a></li>\n";
    print "</ul>\n";
    print "<div id='basic'>\n";
        make_text('control_number','','Control Number','Enter the control/tracking number. If left blank, the system will generate one for you (preferred).');
        make_select('pub',$pubs[0],$pubs,'Publication');
        make_date('pubdate',date("Y-m-d",strtotime("+1 day")),'Publication Date');
        print "<div class='label'>Advertiser</div><div class='input'>\n";
        print input_select('advertiserid',$advertisers[0],$advertisers);
        print "<br />\n<small>If you do not see the advertiser, please enter the name in this box</small><br />\n<input type='text=' name='advertiserName' id='advertiserName' size=20 value='$advertiserName'>\n";
        print "</div><div class='clear'></div>\n";
        make_text('insertTagline',$insertTagline,'Insert tagline','Tag line of flyer (ex. 2 day sale)',50);
        make_checkbox('received',1,'Received','This insert has been received. If not, uncheck and leave receive count at 0');
        make_select('receiveBy',$GLOBALS['productionStaff'][$_SESSION['cmsuser']['userid']],$GLOBALS['productionStaff'],'Received By');
        make_date('receiveDate',$receiveDate,'Date received');
        make_number('receiveCount',0,'Receive Count','How many did we get?');
        print "<div class='label'>Insert Type</div><div class='input'><small>Insert format, ex: booklet</small><br />\n";
        print "<input type='text' name='pageCount' id='pageCount' value='0' size=5 onkeypress='return isNumberKey(event);'> page ";
        print input_select('productSize',$insertProducts[$productSize],$insertProducts);
        print "</div>\n";    
        print "<div class='clear'></div>\n";
        make_number('standardPageCount',0,'Standard Pages','Standard equivalent pages?');
        make_number('runability',0,'Runability','Enter the degree of runability for this insert, 1=hard, 10=easy');
        make_number('pieceWeight',0,'Piece Weight','Weight of single piece?');
        
    print "</div>\n";
    print "<div id='detail'>\n";
        make_checkbox('stickyNote',0,'Sticky Note?','Check if product is a sticky note');
        make_checkbox('singleSheet',0,'Single sheet?','Check if product is a single sheet');
        make_checkbox('slickSheet',0,'Slick insert?','Check if product is slick');
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
    print "</div>\n";    
    print "</form>\n";
    ?>
    <script type="text/javascript">
    $('#tabs').tabs();
    </script>
    <?php
        
}
  
function check_insert()
{
    global $siteID, $pubs, $advertisers, $insertProducts, $shiptypes;
    
    $pub=$_POST['pub'];
    $pubdate=$_POST['pubdate'];
    $advertiserid=$_POST['advertiserid'];
    $advertisername=addslashes($_POST['advertiserName']);
    if($advertiserid==0 && $advertisername!='')
    {
        $sql="INSERT INTO accounts (account_name, account_advertiser, site_id) VALUES ('$advertisername', 1, $siteID)";
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
    $runability=addslashes($_POST['runability']);
    
    
    if($receiveCount==''){$receiveCount=0;}
    if($pageCount==''){$pageCount=0;}
    if($standardPages==''){$standardPages=0;}
    if($shipQuantity==''){$shipQuantity=0;}
    
    $id=$_POST['id'];
    
    $sql="INSERT INTO inserts_received (received, receive_date, receive_count, receive_datetime, shipper, 
        printer, piece_weight, ship_type, ship_quantity, advertiser_id, product_size, pages, std_pages, receive_by,  
        single_sheet, slick_sheet, tag_color, storage_location, damage, insert_damage, insert_tagline, 
        insert_description, sticky_note, site_id, insert_pub_id, matched, scheduled_pubdate, runability) 
        VALUES ('$received', '$receiveDate', '$receiveCount', '$receiveDateTime', '$shipper', '$printer',
        '$pieceWeight', '$shipType', '$shipQuantity', '$advertiserid', '$productSize', '$pageCount', '$standardPages', 
        '$receiveBy', '$singleSheet', '$slickSheet', '$tagColor', '$storageLocation', '$damaged', '$insertDamage', 
        '$tagline', '$insertDescription', '$stickyNote', '$siteID', '$pub', 0, '$pubdate', '$runability')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $insertid=$dbInsert['insertid'];
    
    
    
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
                            mkdir("artwork/inserts/".$foldername);
                        }
                        if(processFile($file,"artwork/inserts/".$foldername."/",$filename) == true) {
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
    
    
    if($_POST['control_number']!='')
    {
        $controlNumber=str_replace(" ","",addslashes($_POST['control_number']));
        $controlNumber=str_replace("-","",$controlNumber);
    } else {
        $sql="SELECT * FROM publications WHERE id=$pub";
        $dbPub=dbselectsingle($sql);
        $pubcode=$dbPub['data']['pub_code'];
        $controlNumber=$pubcode.$insertid;
    }
    $controlNumber=strtoupper($controlNumber);
    $sql="UPDATE inserts_received SET control_number='$controlNumber' WHERE id=$insertid";
    $dbUpdate=dbexecutequery($sql);
    
    
    //now, lets see if the pub is set up with an insert receive address, if so, lets send them an email
    if($dbPub['numrows']>0)
    {
        $pubInfo=$dbPub['data'];
        if($pubInfo['insert_receive_email']!='')
        {
            //send an email
            $sendTo=stripslashes($pubInfo['insert_receive_email']);
            $subject=htmlentities("A new insert has been received in production for you.");
            
            $message="Hello, we just wanted to let you know that the following insert has been received:<br /><br />";
            if($_POST['advertiserid']==0)
            {
                $advertisername=$_POST['advertiserName'];
            } else {
                $advertisername=$advertisers[$advertiserid];
            }
            if($pub!=0)
            {
                $pubName=$pubs[$pub];
            } else {
                $pubName='Not specified';
            }
            $message.="It was received on ".date("D m/d/Y H:i",strtotime($receiveDateTime))." by ".$GLOBALS['productionStaff'][$receiveBy]."<br />";
            $message.="The insert has been given a control number of: <b>$controlNumber</b><br />";
            $message.="Please add the following to the description field in the Vision Data ad order: C#$controlNumber <br />";
            $message.="Advertiser Name: $advertisername<br />";
            $message.="Paperwork shows this is for Publication Name: $pubName<br />";
            $message.="Paperwork shows this is for Insert date: ".date("D m/d/Y",strtotime($pubdate))."<br />";
            $message.="It was given a tag line of ".$tagline.".<br />";
            $message.="The product is a $pageCount ".$insertProducts[$productSize].".<br />";
            $message.="A total of $receiveCount were received <br />";
            if($shipType=='pallet')
            {
                $message.="on $shipQuantity pallets.<br />";
            } else {
                $message.="in $shipQuantity boxes.<br />";
            }
            if($tagColor!='')
            {
                $message.="The ".$shipType."s were marked with a $tagColor tag";
            }
            if ($GLOBALS['insertUseLocation'])
            {
                $slocations=buildInsertLocations();
                $message.=" and stored on ".$slocations[$storageLocation]."<br />";
            } else {
                $message.=".<br />";
            }
            if($singleSheet)
            {
                $message.="Product is a single sheet.<br />";
            }
            if($slickSheet)
            {
                $message.="Product is slick which may cause production issues.<br />";
            }
            if($stickyNote)
            {
                $message.="Product is a sticky note.<br />";
            }
            if($damaged)
            {
                $message.="We noted some damage to the inserts when they arrived:<br />$insertDamage<br />";
            }
            if($filename!='')
            {
                $message.="Here is a photograph of the cover: <br />
                <a href='$GLOBALS[serverIPaddress]/artwork/inserts/$foldername/$filename'>
                    Click here to view online at full size.</a><br /><br />
                    <img src='$GLOBALS[serverIPaddress]/artwork/inserts/$foldername/$filename' width=400 />";
            }
            $mail = new htmlMimeMail();
            $mail->setHtml($message);
            $mail->setFrom($GLOBALS['systemEmailFromAddress']);
            $mail->setSubject($subject);
            $mail->setHeader('Sender','Mango');
            if ($sendTo!='')
            {
                $result = $mail->send(array($sendTo),'smtp');
            }
        } else {
            print "No email address defined for who to send the receipt notification to.<br />";
        }
    } else {
        print "Unable to find a corresponding publication for id $pub<br />";
    }
    
    
    print "<div style='margin-left:auto;margin-right:auto;margin-top:20px;width:400px;padding:20px;border:1px solid black;text-align:center;font-size:18px;font-weight:bold;'>
    Please write the following control number on the insert and deliver it to the preprint coordinator or the appropriate bucket:<br>
    <span style='font-size:24px;'>$controlNumber</span><br /><br />
    <a href='?action=add'>Receive another insert</a></div>"; 
    if($error!='')
    {
        print $error;
    }
}

footer();
?>
