<?php
//<!--VERSION: .9 **||**-->
//types of special sections
if($_GET['action']=='displaymap')
{
    include("includes/functions_db.php");
    include("includes/config.php");
    include("includes/functions_common.php");
    include("includes/functions_formtools.php");
    show_map();
} else {
    include("includes/mainmenu.php") ;
    if ($_POST)
    {
        $action=$_POST['submit'];
    } else {
        $action=$_GET['action'];
    }
        
    switch ($action)
    {
        case "Save Account":
        save_account('insert');
        break;
        
        case "Update Account":
        save_account('update');
        break;
        
        case "add":
        setup_accounts('add');
        break;
        
        case "edit":
        setup_accounts('edit');
        break;
        
        case "delete":
        setup_accounts('delete');
        break;
        
        case "list":
        setup_accounts('list');
        break;
        
        case "import":
        import();
        break;
        
        case "geocode":
        geocodeBusinesses();
        break;
        
        case "checkgeocode":
        fetchGeocode();
        break;
        
        case "cleardupes":
        clearDupes();
        break;
        
        case "Process Import":
        process_import();
        break;
        
        default:
        setup_accounts('list');
        break;
        
    } 
} 

   
function import()
{
    global $sales;
    print "<form method='post' enctype='multipart/form-data'>\n";
    make_file('addresses','Account file');
    make_submit('submit','Process Import');
    print "</form>\n";   
}   

function process_import()
{
    //get all sales
    $sql="SELECT id, vision_data_sales_name FROM users";
    $dbSales=dbselectmulti($sql);
    $salesreps=array();
    if($dbSales['numrows']>0)
    {
        foreach($dbSales['data'] as $s)
        {
            $salesreps[$s['id']]=stripslashes($s['vision_data_sales_name']);
        }
    } 
    
    //get a list of all current account numbers in the system so we know which is an update and which is a new insert
    $sql="SELECT id, account_number FROM advertising_account_mapping";
    $dbAccounts=dbselectmulti($sql);
    if($dbAccounts['numrows']>0)
    {
        foreach($dbAccounts['data'] as $ex)
        {
            $existingAccounts[]=$ex['account_number'];
        }
    } 
    
    print "Processing...<br>";
    set_time_limit(6000);
    if(isset($_FILES))
    {
        print "Ingesting file...<br>";
        $file=$_FILES['addresses']['tmp_name'];
        $contents=file_get_contents($file);
        $inserted=0;
        $updated=0;
        $lines=explode("\n",$contents);
        $i=0;
        $success=0;
        $accounts=array();
        if(strpos($lines[0],"Advertiser Name")>0)
        {
            array_shift($lines);
        }
        $addednew=false; 
        print "A total of ".count($lines)." records are in the file<br>"; 
        $totalsuccess=0; 
        foreach($lines as $line)
        {
            $line=trim($line);
            $line=convertCSVtoArray($line);
            $dbInsert=array();
            if(trim($line[1])!='')
            {
                $accounts[$i]['account_number']=addslashes(trim($line[0]));    
                $accounts[$i]['account_name']=addslashes(trim($line[1]));    
                $accounts[$i]['address']=addslashes(trim($line[3]).' '.trim($line[4]));    
                $accounts[$i]['city']=addslashes(trim($line[5]));    
                $accounts[$i]['state']=addslashes(trim($line[6]));    
                $accounts[$i]['zip']=addslashes(trim($line[7]));    
                $accounts[$i]['contact']=addslashes(trim($line[9]));    
                $accounts[$i]['phone']=addslashes(trim($line[10]).'-'.trim($line[11]));
                $accounts[$i]['pay_status']=addslashes(trim($line[13]));    
                $accounts[$i]['subscriber']=addslashes(trim($line[16]));    
                $accounts[$i]['sales_rep']=addslashes(trim($line[18]));
                if(in_array(trim($line[18]),$salesreps)){
                    $accounts[$i]['sales_id']=array_search(trim($line[18]),$salesreps);
                } else {
                    $accounts[$i]['sales_id']=0;        
                }
                $accounts[$i]['phone_2']=addslashes(trim($line[19]).'-'.trim($line[20]));
                $accounts[$i]['sic_code']=addslashes(trim($line[22]));    
                $accounts[$i]['category']=addslashes(trim($line[23]));    
                $accounts[$i]['nic_code']=addslashes(trim($line[24]));    
                $accounts[$i]['nic_desc']=addslashes(trim($line[25]));    
                $accounts[$i]['email']=addslashes(trim($line[27])); 
                $accounts[$i]['date_setup']=addslashes(trim($line[28])); 
                if(trim($line[31])!=''){
                    $accounts[$i]['revenue']=addslashes(trim($line[31])); 
                } else {
                    $accounts[$i]['revenue']='0.00'; 
                }
                $i++;
            }
            if($i>500)
            {
                $values='';
                foreach($accounts as $account)
                {
                    if(in_array($account['account_number'],$existingAccounts))
                    {
                        // update the record
                        $sql="UPDATE advertising_account_mapping SET sales_id='$account[sales_id]', account_name='$account[account_name]', 
                        address='$account[address]', city='$account[city]', state='$account[state]', zip='$account[zip]', 
                        phone='$account[phone]', phone_2='$account[phone_2]', contact='$account[contact]', pay_status='$account[pay_status]', 
                        subscriber='$account[subscriber]', category='$account[category]', email='$account[email]',ytd_revenue='$revenue' 
                        WHERE account_number='$account[account_number]'";
                        $dbUpdate=dbexecutequery($sql);
                        if($dbUpdate['error']==''){$totalsuccess++;$updated++;} 
                    } else {
                        $values.="('$account[sales_id]','$account[account_number]','$account[account_name]','$account[address]','$account[city]',
                        '$account[state]','$account[zip]','$account[phone]','$account[phone_2]','$account[contact]','$account[pay_status]',
                        '$account[subscriber]','$account[category]','$account[email]','$account[revenue]'),";
                        $addednew=true;
                    }
                }
                $values=substr($values,0,strlen($values)-1);
                $sql="INSERT INTO advertising_account_mapping (sales_id, account_number, account_name, address, city, state, zip, phone, 
                phone_2, contact, pay_status, subscriber, category, email, ytd_revenue) VALUES $values";
                $dbInsert=dbinsertquery($sql);
                if($dbInsert['error']!='')
                {
                    print "There was an error processing the database import batch:<br>".$dbInsert['error'];
                } else {
                    print "<br><br>";
                    print "Total of $i accounts were processed for this batch.<br>.";
                }
                $totalsuccess+=$i;
                $accounts=array();
                $i=0; 
            }
        }
                      
         
        if(count($accounts)>0)
        {
            $values='';
            foreach($accounts as $account)
            {
                if(in_array($account['account_number'],$existingAccounts))
                {
                    // update the record
                    $sql="UPDATE advertising_account_mapping SET sales_id='$account[sales_id]', account_name='$account[account_name]', 
                    address='$account[address]', city='$account[city]', state='$account[state]', zip='$account[zip]', 
                    phone='$account[phone]', phone_2='$account[phone_2]', contact='$account[contact]', pay_status='$account[pay_status]', 
                    subscriber='$account[subscriber]', category='$account[category]', email='$account[email]',ytd_revenue='$revenue' 
                    WHERE account_number='$account[account_number]'";
                    $dbUpdate=dbexecutequery($sql);
                    if($dbUpdate['error']==''){$totalsuccess++;$updated++;} 
                } else {
                    $values.="('$account[sales_id]','$account[account_number]','$account[account_name]','$account[address]','$account[city]',
                    '$account[state]','$account[zip]','$account[phone]','$account[phone_2]','$account[contact]','$account[pay_status]',
                    '$account[subscriber]','$account[category]','$account[email]','$account[revenue]'),";
                    $addednew=true;
                }
            }
            $values=substr($values,0,strlen($values)-1);
            if($values!='')
            {
                $sql="INSERT INTO advertising_account_mapping (sales_id, account_number, account_name, address, city, state, zip, phone, 
                phone_2, contact, pay_status, subscriber, category, email, ytd_revenue) VALUES $values";
                $dbInsert=dbinsertquery($sql);
                if($dbInsert['error']!='')
                {
                    print "There was an error processing the database import:<br>".$dbInsert['error'];
                } else {
                    $totalsuccess+=count($accounts);
                }
            }
        }
        print "Overall, $totalsuccess records were successfully inserted with $updated records being updated.<br>";
        if($addednew)
        {
            print "Since there were new accounts added, you will need to do batch geocode and then a territory update.<br>";
        }
    } else {
        print "No file was uploaded.";
    }
    print "<br><br><a href='?action=list' class='submit'>Return to menu</a>";
    
    /*
    ob_implicit_flush(true);
    print "Begining file processing...imported: <span id='importcount'></span><br>";
    print "<script>\$('#importcount').html('".($i-1)."')</script>";
    $i++;
        //only update every 25
        if($l==25)
        {
            $l=0;
            print "<script>\$('#importcount').html('$i')</script>";
            for($k = 0; $k < 320000; $k++){echo ' ';} // extra spaces to fill up browser buffer
        } else {
            $l++;
        }
        for($k = 0; $k < 320000; $k++)echo ' '; // extra spaces to fill up browser buffer
     $addresses=batch_geocode($addresses,'0','0',true,'curadd');
            print "<br><br>Integrating geocode information into the accounts...<br>";
            foreach($accounts as $key=>$account)
            {
                $accounts[$key]['lat']=$addresses[$key]['lat'];        
                $accounts[$key]['lon']=$addresses[$key]['lon'];
                if($addresses[$key]['status']=='success'){$success++;}
            }  
    */ 
} 


function setup_accounts($action)
{
    global $siteID, $sales;
    $id=intval($_GET['id']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Account";
            
        } else {
            $button="Update Account";
            $sql="SELECT * FROM advertising_account_mapping WHERE id=$id";
            $dbAccount=dbselectsingle($sql);
            $account=$dbAccount['data'];
            $number=stripslashes($account['account_number']);
            $name=stripslashes($account['account_name']);
            $address=stripslashes($account['address']);
            $city=stripslashes($account['city']);
            $state=stripslashes($account['state']);
            $zip=stripslashes($account['zip']);
            $phone=stripslashes($account['phone']);
            $phone2=stripslashes($account['phone_2']);
            $contact=stripslashes($account['contact']);
            $email=stripslashes($account['email']);
            $salesid=stripslashes($account['sales_id']);
        }
        print "<form method=post>\n";
        make_select('salesid',$sales[$salesid],$sales,'Sales Rep');
        make_text('number',$number,'Account Number','',40);
        make_text('name',$name,'Account Name','',40);
        make_text('address',$address,'Street','',40);
        make_text('city',$city,'City','',40);
        make_state('state',$state,'State','',40);
        make_text('zip',$zip,'Zip','',40);
        make_text('contact',$contact,'Contact','',40);
        make_text('email',$email,'Email','',40);
        make_text('phone',$phone,'Phone','',40);
        make_text('phone_2',$phone2,'Phone 2','',40);
        
        make_submit('submit',$button);
        make_hidden('id',$id);
        print "</form>\n";  
    } elseif($action=='delete') {
        $sql="DELETE FROM advertising_account_mapping WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if($error=='')
        {
            setUserMessage('Advertiser account successfully deleted.','success');
        } else {
            setUserMessage('There was a problem deleting the advertiser account.<br>'.$error,'error'); 
        }
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM advertising_account_mapping ORDER BY account_name LIMIT 200";
        $dbTypes=dbselectmulti($sql);
        
        $sql="SELECT * FROM batch_geocodes WHERE status=0";
        $dbOpenBatches=dbselectmulti($sql);
        $openbatches='';
        if($dbOpenBatches['numrows']>0)
        {
            foreach($dbOpenBatches['data'] as $ob)
            {
                $openbatches.="<a href='?action=checkgeocode&id=$ob[id]'>Check status of GeoCode batch #$ob[id]</a>,";
            }
        }
        tableStart("<a href='?action=import'>Import/update Vision Data advertising list</a>,
        <a href='?action=geocode'>Geocode all accounts</a>,$openbatches 
        <a href='advertisingMapping.php'>Generate Map</a>,
        <a href='?action=cleardupes'>Clear duplicate Accounts</a>","Sales Rep,Account Name",4);
        if ($dbTypes['numrows']>0)
        {
            foreach($dbTypes['data'] as $type)
            {
                $id=$type['id'];
                $salesid=$type['sales_id'];
                $account=stripslashes($type['account_name']);
                print "<tr><td>$sales[$salesid]</td><td>$account</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbTypes);
        
    }
}

function save_account($action)
{
    global $siteID;
    $id=$_POST['id'];
    $salesid=addslashes($_POST['salesid']);
    $number=addslashes($_POST['number']);
    $name=addslashes($_POST['name']);
    $street=addslashes($_POST['street']);
    $city=addslashes($_POST['city']);
    $state=addslashes($_POST['state']);
    $zip=addslashes($_POST['zip']);
    $phone=addslashes($_POST['phone']);
    $phone2=addslashes($_POST['phone_2']);
    $contact=addslashes($_POST['contact']);
    $email=addslashes($_POST['email']);
    
    $mapping[0]['street']=$street;
    $mapping[0]['city']=$city;
    $mapping[0]['state']=$state;
    $mapping[0]['zip']=$zip;
    $map=batch_geocode($mapping);
    $lat=$map[0]['lat'];
    $lon=$map[0]['lon'];
    
    if ($action=='insert')
    {
        $sql="INSERT INTO advertising_account_mapping (account_number,sales_id, account_name, address, city, state,zip, phone, phone_2, contact, email, lat, lon) VALUES ('$number', '$salesid', '$name', '$street', '$city', '$state', '$zip', '$phone', '$phone2', '$contact', '$email', '$lat', '$lon')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE advertising_account_mapping SET account_number='$number', sales_id='$salesid', account_name='$name', address='$street', city='$city', state='$state', zip='$zip', phone='$phone', lat='$lat', lon='$lon', phone_2='$phone2', contact='$contact', email='$email' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    $error=$dbUpdate['error'];
    if($error=='')
    {
        setUserMessage('Advertiser account successfully saved.','success');
    } else {
        setUserMessage('There was a problem saving the advertiser account.<br>'.$error,'error'); 
    }
    redirect("?action=list");
    
}


function geocodeBusinesses()
{
    error_reporting(E_ERROR);
    //windows bing map api key    AsUEJjHX1oAE16UX-8mQFmQaE8I4CFXizSHRg6s6vJ35MB6Dm-cVuirpT-XUn3-B
    print "Beginning geocode process<br>";
    //now batch geocode them all
    $key = "AsUEJjHX1oAE16UX-8mQFmQaE8I4CFXizSHRg6s6vJ35MB6Dm-cVuirpT-XUn3-B";
    $url = 'http://spatial.virtualearth.net/REST/v1/Dataflows/Geocode?description=MangoAccounts&input=xml&output=json&key=' . $key;
    // STEP 1 - Create a geocode job
    if($_GET['limit'])
    {
        $limit=intval($_GET['limit']);
    } else {
        $limit=1000;
    }
    $sql="SELECT id, address, city, state, zip FROM advertising_account_mapping WHERE lat IS Null AND geocoding=0 LIMIT $limit";
    $dbAddresses=dbselectmulti($sql);
    
    print "Batch processing $limit accounts - ".$dbAddresses['numrows']." actually selected<br>";
    $data="<GeocodeFeed>\n";
    

    $updateids='';
    foreach($dbAddresses['data'] as $ad)
    {
        $data.='<GeocodeEntity Id="'.$ad['id'].'" xmlns="http://schemas.microsoft.com/search/local/2010/5/geocode">'."\n";
        $data.="<GeocodeRequest Culture='en-US'>\n";
        $data.='<Address AddressLine="'.str_replace("&","&amp;",stripslashes($ad['address'])).'" AdminDistrict="'.stripslashes($ad['state']).'" Locality="'.stripslashes($ad['city']).'" PostalCode="'.stripslashes($ad['zip']).'" />'."\n";
        $data.="</GeocodeRequest>\n";
        $data.="</GeocodeEntity>\n";
        $updateids.="$ad[id],";
        // SAMPLE:  1|en-US||One Microsoft Way|WA||||Redmond|98052
    }
    $updateids=substr($updateids,0,strlen($updateids)-1);
    if($updateids!='')
    {
        $sql="UPDATE advertising_account_mapping SET geocoding=1 WHERE id IN ($updateids)";
        $dbUpdate=dbexecutequery($sql);
        if($dbUpdate['error']!=''){print $dbUpdate['error'];} 
    }
    $data.="</GeocodeFeed>\n";
    // Call custom function to generate an HTTP request and get back an HTTP response
    // This function constructs and sends an HTTP request with a provided URL and data, and returns an HTTP response object 
    // This function uses the php_http extension 
   // Call custom function to generate an HTTP request and get back an HTTP response
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    $response=json_decode($response,true);
    /*
    print "<pre>\n";
    print_r($response);
    print "</pre>\n";
    */
    $jobId=$response['resourceSets'][0]['resources'][0]['id'];
    $jobStatus=$response['resourceSets'][0]['resources'][0]['status'];
    $statusDescription=$response['statusDescription'];
     
    echo "Job Created:<br>";
    echo " Request Status: ".$statusDescription."<br>";
    echo " Job ID: ".$jobId."<br>";
    echo " Job Status: ".$jobStatus."<br><br>";

    if($statusDescription=='Bad Request')
    {
        print "<pre>\n";
        print_r($response);
        print $data;
        print "</pre>\n";
            
    }
    // STEP 2 - Get the status of geocode job(s)
    $sql="INSERT INTO batch_geocodes (job_id, status) VALUES ('$jobId',0)";
    $dbInsert=dbinsertquery($sql);
    $id=$dbInsert['insertid'];
    print "<br><a href='?action=checkgeocode&id=$id'>Check the status of this batch</a><br>";
    print "<br><br><a href='?action=list'>Return to account list</a>";
}

function fetchGeocode()
{
    $key = "AsUEJjHX1oAE16UX-8mQFmQaE8I4CFXizSHRg6s6vJ35MB6Dm-cVuirpT-XUn3-B";
    $id=intval($_GET['id']);
        
    $sql="SELECT * FROM batch_geocodes WHERE id=$id AND status=0";
    $dbJob=dbselectsingle($sql);
    if($dbJob['numrows']>0)
    {
        $jobId=$dbJob['data']['job_id'];
        // Call the API to determine the status of all geocode jobs associated with a Bing Maps key
        echo "Checking status...<br>";
        $checkUrl = "http://spatial.virtualearth.net/REST/v1/Dataflows/Geocode/".$jobId."?output=json&key=".$key;
        
        // Construct the URL to check the job status, including the jobId

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $checkUrl);
        $checkResponse = curl_exec($ch);
        curl_close($ch);
        $checkResponse=json_decode($checkResponse,true);
        /*
        print "<pre>";
            print_r($checkResponse);
        print "</pre>\n";
        */        
        $successful=$checkResponse['resourceSets'][0]['resources'][0]['processedEntityCount'];
        $failures=$checkResponse['resourceSets'][0]['resources'][0]['failedEntityCount'];
        $created=$checkResponse['resourceSets'][0]['resources'][0]['createdDate'];
        $jobStatus=$checkResponse['resourceSets'][0]['resources'][0]['status'];
        $Links = $checkResponse['resourceSets'][0]['resources'][0]['links'];
        foreach ($Links as $Link) {
            if ($Link['name'] == "succeeded") 
            { 
              $successUrl = $Link['url']; 
            }
        }
        echo "created: $created<br>Status:$jobStatus<br>
        <br>Successful Geocodes:$successful<br>
        <br>Failed Geocodes:$failures
        <br>Success url: $successUrl<br><br>";
        
        if($jobStatus=='Completed')
        {  
            
            // STEP 3 - Obtain results from a successfully geocoded set of data
            
            // Access the URL for the successful requests, and convert response to an XML element
            $successUrl .= "?output=xml&key=".$key;
            print "Checking with url: $successUrl<br>";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $successUrl);
            $successReponse = curl_exec($ch);
            if(curl_exec($ch) === false)
            {
                echo 'Curl error: ' . curl_error($ch);
            }
            else
            {
                echo 'Operation completed without any errors.<br>';
                $array = json_decode(json_encode((array)simplexml_load_string($successReponse)),1);
                /*
                print "<pre>";
                print_r($array);
                print "</pre>\n";
                */
                $geoCodedLocations=$array['GeocodeEntity'];
                
                
                foreach($geoCodedLocations as $geolocation)
                {
                    $idAttr=0;
                    $locationLat='';
                    $locationLon='';
                    /*
                    print "<pre>";
                    print_r($geolocation);
                    print "</pre>\n";
                    */
                    $idAttr=$geolocation['@attributes']['Id'];
                    $response=$geolocation['GeocodeResponse'];
                    if(isset($response['RooftopLocation']))
                    {
                        $location=$response['RooftopLocation'];
                        $locationLat=$location['@attributes']['Latitude'];
                        $locationLon=$location['@attributes']['Longitude'];
                    } else {
                        $location=$response['InterpolatedLocation'];
                        $locationLat=$location['@attributes']['Latitude'];
                        $locationLon=$location['@attributes']['Longitude'];
                    }
                    if($locationLon!='' && $idAttr!=0)
                    {
                        //print "For $idAttr we got $locationLat, $locationLon<br>";
                        $sql="UPDATE advertising_account_mapping SET lat='$locationLat', lon='$locationLon', geocoding=0 WHERE id=$idAttr";
                        $dbUpdate=dbexecutequery($sql);
                        if($dbUpdate['error']=='')
                        {
                            $success++;
                        }
                    } 
                }
                $sql="UPDATE batch_geocodes SET status=1 WHERE id=$id";
                $dbUpdate=dbexecutequery($sql); 
            }
            curl_close($ch);
            print "A total of $success updates were successfully made to the database.";
        } else {
            print "<br><a href='?action=checkgeocode&id=$id'>Check the status of this batch again</a>";
       
        }
        
    } else {
        print "That batch job is no longer available<br>";
    }
    print "<br><br><a href='?action=list'>Return to account list</a>";
}


function clearDupes()
{
    //this function is designed to get rid of accidentally duplicated accounts
    //it keeps one with a sales id and a lat/lon
    $sql="SELECT DISTINCT(account_number) FROM advertising_account_mapping";
    $dbAccounts=dbselectmulti($sql);
    if($dbAccounts['numrows']>0)
    {
        foreach($dbAccounts['data'] as $account)
        {
            $sql="SELECT * FROM advertising_account_mapping WHERE account_number='$account[account_number]'";
            $dbTemp=dbselectmulti($sql);
            if($dbTemp['numrows']>1)
            {
                //have more than one, so need to decide which to keep
                $keepid=0;
                foreach($dbTemp['data'] as $temp)
                {
                    $tempid=0;
                    $hasSales=false;
                    $hasGeo=false;
                    if($temp['sales_id']!=0)
                    {
                        $hasSales=true;
                    }
                    if($temp['lat']!='')
                    {
                        $hasGeo=true;
                    }
                    if($hasGeo && $hasSales)
                    {
                        $keepid=$temp['id'];
                    }
                    if($hasGeo) {
                        $keepid=$temp['id'];
                    }
                    $tempid=$temp['id'];
                }
                if($keepid==0){$keepid=$tempid;}
                $sql="DELETE FROM advertising_account_mapping WHERE id<>$keepid AND account_number='$account[account_number]'";
                $dbDelete=dbexecutequery($sql);
            }
        }
    }
}
footer();
?>