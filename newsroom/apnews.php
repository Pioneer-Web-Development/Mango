<?php
include("../includes/functions_db.php");
set_time_limit(1440);
error_reporting(0);

//get text replacement filters
$sql="SELECT * FROM editorial_ap_replace";
$dbFilters=dbselectmulti($sql);
if($dbFilters['numrows']>0)
{
    $i=0;
    foreach($dbFilters['data'] as $filter)
    {
        $filters[$i]['find']=stripslashes($filter['find_text']);
        $filters[$i]['replace']=stripslashes($filter['replace_text']);
        $i++;
    }
} else {
    $filters=array();
}

if($_GET['mode']=='test')
{
    test();
} else {
    scan();
}
function scan()
{
    
    $sql="SELECT * FROM editorial_ap_feeds";
    $dbFeeds=dbselectmulti($sql);
    if($dbFeeds['numrows']==0)
    {
        die();
    }
    /*
    $sections[]=array("path"=>"AP Idaho State News - No Weather","section"=>"news/state","sites"=>array('nampa'));
    $sections[]=array("path"=>"AP National News Report (A wire)","section"=>"news/national","sites"=>array('nampa'));
    $sections[]=array("path"=>"Webfeeds WorldNews","section"=>"news/world","sites"=>array('nampa'));
    $sections[]=array("path"=>"International News","section"=>"news/world","sites"=>array('nampa'));
    $sections[]=array("path"=>"National News","section"=>"news/national","sites"=>array('nampa'));
    $sections[]=array("path"=>"AP Sports News","section"=>"sports/national","sites"=>array('nampa'));
    */
    
    $base_dir="WebFeedsAgent/WFA/content/";
    
    //we will loop through each sections as section
    //scan for xml files with -entry in the filename, deleting all others
    foreach($dbFeeds['data'] as $section)
    {
        print "Processing with ".$section['tn_section']."<br>";
        $path=$base_dir.$section['feed_name'];
        print "&nbsp;&nbsp;Path is set to $path<br>";
        $tnsection=$section['tn_section'];
        
        $dir = opendir($path);
        while(false != ($file = readdir($dir)))
        {
            if(($file != ".") and ($file != ".."))
            {
                if (strpos($file,"-entry")>0)
                {
                    print "Found a file: $file<br>";
                    if (process_file($path,$file,$section))
                    {
                        print "&nbsp;&nbsp;&nbsp;&nbsp;File processed successfully<br>";
                        myflush();
                        //now upload the file
                        //need to do the upload for each site
                        if (upload_file($path,$file))
                        {
                            //now we can delete this file
                            unlink($path.'/'.$file);
                            print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Article uploaded successfully and was deleted<br>";
                        }
                        
                    } else {
                        unlink($path.'/'.$file);
                        print "Deleted $file<br>";
                    }
                }elseif (strpos(strtolower($file),"jpeg")>0)
                {
                    if (upload_file($path,$file,FTP_BINARY))
                    {
                        //now we can delete this file
                        unlink($path.'/'.$file);
                        print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Photo uploaded successfully and was deleted<br>";
                    } else {
                        unlink($path.'/'.$file);
                        print "Unabled to upload $file - deleted instead<br>";
                    }
                    
                } else {
                    unlink($path.'/'.$file);
                    print "Deleted $file<br>";
                }
            }
            myflush();
        }
    }
} 
function myflush (){
    echo(str_repeat(' ',256));
    // check that buffer is actually set before flushing
    if (ob_get_length()){            
        @ob_flush();
        @flush();
        @ob_end_flush();
    }    
    @ob_start();
}
     
function process_file($path,$file,$section)
{
    $tnsection=$section['tn_section'];
    $feedname=$section['feed_name'];
    $contents=file_get_contents($path.'/'.$file);
    
    
    $original=$contents;
    $name=str_replace("-entry.xml","",$file);
    //get the doc id
    
    $article=process_entry($contents);
    
    //see if it exists
    $sql="SELECT * FROM editorial_ap_articles WHERE ap_asset_id='$article[id]'";
    $dbCheck=dbselectsingle($sql);
    $now=date("Y-m-d H:i:s");
    if($dbCheck['numrows']>0)
    {
       $sql="UPDATE editorial_ap_articles SET updated=1, article_text='$article[article]', article_headline='$article[headline]' WHERE id=".$dbCheck['data']['id'];
       $dbUpdate=dbexecutequery($sql);
       print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updated existing record in database";  
    } else {
       $sql="INSERT INTO editorial_ap_articles (ap_asset_id, ap_source, source_original, status, dateline, keywords, consumer_ready, pubdate, editor_info, article_text, article_headline, tn_section, author, updated, import_datetime) VALUES ('$article[id]', '$feedname', '".addslashes($original)."', '$article[status]', '$article[dateline]', '$article[keywords]','$article[consumerReady]', '$article[pubdate]', '".$article['editor_info']."', '".addslashes($article['article'])."', '".addslashes($article['headline'])."','$tnsection', '$article[author]', 0, '$now')";
       $dbInsert=dbinsertquery($sql);
       if($dbInsert['error']!='')
       {
           print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Error adding record to database.<br>".$dbInsert['error']."<br><br>";
       } else {
           print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;New record stored in database";
       }
    }
     
    //apply the correct ManagementID so that the article in Blox gets updated, not added
    $contents=str_replace('<doc-id regsrc="AP"/>','<doc-id id-string="'.$article['id'].'" regsrc="AP"/>',$contents);
    //apply the correct TownNews section tag for this feed
    $contents=str_replace('</body.head>','<pubdata position.section="'.$tnsection.'" />'."\n".$article['mediatags'].'</body.head>',$contents);
    
    //need to add a date release AP file by default only has date.issue, we'll just extract, duplicate and add
    /* 
    <date.release norm="DDMMYYYYTHHMMSSZ"> 
    */
    $contents=str_replace('<date.issue norm="'.stripslashes($article['dateIssued']).'"/>','<date.issue norm="'.stripslashes($article['dateIssued']).'"/>
    <date.release norm="'.stripslashes($article['dateIssued']).'"/>',$contents);
    
    //replace the headline with one that has been filtered against
    $contents=str_replace($article['originalHeadline'],stripslashes($article['headline']),$contents);
    //add an ap_content keyword to the keywords line so that we can filter them in blox
    $contents=str_replace("</docdata>",'<key-list>
    <keyword key="ap_content" />
    </key-list>
    </docdata>',$contents);
    
    /*******************************************************************************
    FLAGS ARE SUPPORTED LIKE THIS: <meta name="tncms-flags" content="breaking,top story" />
    <meta name="asset_type" content="article" />
    <docdata management-status="withheld">
    
      <!-- Document ID this should be uniqe to itself
         updates to this article must retain the same ID
    -->
        <doc-id id-string="4ce5857d2f7d7"/>
        
        <!-- Start Time -->
        <date.release norm="20111130T000000" />        
        <!-- Start Time if the above is not provided -->
        <date.issue norm="20111130T000000" />
        
        <!-- Optional archive time -->
        <date.expire norm="20501130T000000" />        
        
        <!-- Copyright -->
        <doc.copyright holder="TownNews.com" />
        
        <!-- Keywords -->
    <key-list>
      <keyword key="Extended NITF" />
    </key-list>    
    
    <!-- Location Data -->
    <identified-content>
      <location>
        <sublocation>123 4th St.</sublocation>
        <city city-code="61265">Moline</city>
        <state>Illinois</state>
        <country>United States
          <georss:point>11.5725580365 48.1379548096</georss:point>
        </country>        
      </location>
    </identified-content>    
        
    </docdata>
    
    (*************************************************************************************/   
   
    if(stripslashes($article['consumerReady'])=='TRUE' && $article['headline']!='BC-AP Service Guide')
    {
        return file_put_contents($path.'/'.$file,$contents);
    } else {
        return false;
    }
} 


function upload_file($path,$file,$mode=FTP_ASCII)
{
    $ftp_server='bloxcms.com';
    $ftp_user_name='1800';
    $ftp_user_pass='H6432Obm';
    
    // set up basic connection
    $conn_id = ftp_connect($ftp_server); 

    // login with username and password
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 

    // check connection
    if ((!$conn_id) || (!$login_result)) { 
            //echo "FTP connection has failed!";
            echo "&nbsp;&nbsp;&nbsp;Attempted to connect to $ftp_server for user $ftp_user_name<br>"; 
            //exit;
            return false; 
        } else {
            echo "&nbsp;&nbsp;&nbsp;Connected to $ftp_server, for user $ftp_user_name<br>";
        }
    //echo "Current directory: " . ftp_pwd($conn_id) . "<br>\n";
    $source_file = $path.'/'.$file;
    $destination_file = '/articles/'.$file;

    ftp_pasv($conn_id, false);

    // try to change the directory to somedir
    if (ftp_chdir($conn_id, "/articles/")) {
        //echo "Current directory is now: " . ftp_pwd($conn_id) . "<br>\n";
    } else { 
        return false;
        //echo "Couldn't change directory<br>\n";
    }
    // upload the file
    $upload = ftp_put($conn_id, $destination_file, $source_file, $mode); //FTP_ASCII OR FTP_BINARY
    // check upload status
    if (!$upload) { 
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;FTP upload has failed!<br>";
        return false;
    } else {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Uploaded $source_file to $ftp_server as $destination_file<br>";
        return true;
    }
    
}


function test()
{
    $tnsection=$section['section'];
    $feedname=$section['path'];
    $sql="SELECT * FROM editorial_ap_articles WHERE id=150";
    $dbContents=dbselectsingle($sql);
    $contents=$dbContents['data']['source_original'];
    
    $article=process_entry($contents);
    
    
    
}


function process_entry($contents)
{
    global $filters;
    
    $temp=explode("<apnm:ManagementId>",$contents);
    $temp=explode("</apnm:ManagementId>",$temp[1]);
    $article['id']=str_replace("urn:publicid:ap.org:","",$temp[0]);
    
    $temp=explode("<apnm:PublishingStatus>",$contents);
    $temp=explode("</apnm:PublishingStatus>",$temp[1]);
    $article['status']=addslashes($temp[0]);
    
    $temp=explode("<apcm:HeadLine>",$contents);
    $temp=explode("</apcm:HeadLine>",$temp[1]);
    //apply filters to the headlines
    $headline=$temp[0];
    $article['originalHeadline']=$headline;
    if(count($filters)>0)
    {
        foreach($filters as $key=>$filter)
        {
            $headline=str_replace($filter['find'],$filter['replace'],$headline);
        }    
    }
    $article['headline']=addslashes($headline);
    
    $temp=explode("<apcm:DateLine>",$contents);
    $temp=explode("</apcm:DateLine>",$temp[1]);
    $article['dateline']=addslashes($temp[0]);
    
    $temp=explode("<apcm:Keywords>",$contents);
    $temp=explode("</apcm:Keywords>",$temp[1]);
    $article['keywords']=addslashes($temp[0]);
    
    $temp=explode('<block id="Main">',$contents);
    $temp=explode("</block>",$temp[1]);
    $article['article']=addslashes($temp[0]);
    
    $temp=explode('<name>',$contents);
    $temp=explode("</name>",$temp[1]);
    $article['author']=addslashes($temp[0]);
    
    $temp=explode("<apcm:ConsumerReady>",$contents);
    $temp=explode("</apcm:ConsumerReady>",$temp[1]);
    $article['consumerReady']=addslashes($temp[0]);
    
    $temp=explode('<published>',$contents);
    $temp=explode('</published>',$temp[1]);
    $year=substr($temp[0],0,4);
    $month=substr($temp[0],5,2);
    $day=substr($temp[0],8,2);
    $hour=substr($temp[0],11,2);
    $minute=substr($temp[0],14,2);
    $seconds=substr($temp[0],17,2);
    $offset="-7 hours";
    $date="$year-$month-$day $hour:$minute";
    $article['pubdate']=addslashes(date("Y-m-d H:i",strtotime("$date $offset")));
    
    $temp=explode('<ed-msg info="',$contents);
    $temp=explode('"/>',$temp[1]);
    $article['editorInfo']=addslashes($temp[0]);
    
    $temp=explode('<date.issue norm="',$contents);
    $temp=explode('"/>',$temp[1]);
    $article['dateIssued']=addslashes($temp[0]);
               
    
    //now try to pull out media information
    $media=explode('<media id="',$contents);
    $i=0;
    $filenames=array();
    foreach($media as $key=>$item)
    {
       $type=explode('media-type="',$item);
       $type=explode('">',$type[1]);
       $type=$type[0];
       if($type=='Photo')
       {
           $id=explode('<media-metadata id="media-id:',$item);
           $id=explode('" name="id"',$id[1]);
            
           $name=explode('name="OriginalFileName" value="',$item);
           $name=explode('"/>',$name[1]);
           
           $caption=explode('<media-caption id="media-caption:'.$id[0].'">',$item);
           $caption=explode('</media-caption>',$caption[1]);
           
           $article['media'][$i]['type']=$type;
           $article['media'][$i]['id']=$id[0];
           $article['media'][$i]['filename']=$name[0];
           $article['media'][$i]['caption']=$caption[0];
           
           if(!in_array($name[0],$filenames))
           {
               $article['mediatags'].='<media media-type="image">
                <media-reference source="'.$name[0].'"/>
                <media-caption>'.$caption[0].'</media-caption>
                <media-producer></media-producer>
               </media>
               ';
               $filenames[]=$name[0];
           }
           $i++;
       }
       
    }
    
    return $article;
}
dbclose();   
?>
