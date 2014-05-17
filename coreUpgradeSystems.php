<?php
include("includes/mainmenu.php") ;
error_reporting(E_ALL);
die();
if (!$_POST)
{
    print "<form method=post>\n";
    print "Click the magic button to update all the Mango databases to match the core database";
    $sql="SELECT * FROM core_sites WHERE primary_site=0";
    $dbSites=dbselectmulti($sql);
    print "<ul>The following sites will be updated:";
    foreach($dbSites['data'] as $site)
    {
        print "<li>".$site['site_name']."</li>";
    }
    print "</ul>";
    make_checkbox('database',0,'Update Database','Check to sychronize databases');
    make_checkbox('scanfiles',0,'File versions','Check to have each site update file versions');
    make_checkbox('updatefiles',0,'Update Files','Check to have each site update files');
    make_submit('submit','Synch Databases','Action');
    print "</form>\n";
}else{
    $sql="SELECT * FROM core_sites WHERE primary_site=0";
    $dbSites=dbselectmulti($sql);
    foreach($dbSites['data'] as $site)
    {
        $sitename=$site['site_name'];
        if($_POST['database'])
        {
            $url="http://".stripslashes($site['ip_address']).'coreDBSynch.php?direct=234*_SNS_DF2kj2kj2k';
            $synch=get_web_page($url);
            if ($synch['errmsg']=='')
            {
                print "Successfully synched $sitename <br />";
            }else {
                print "Possible issue synching $sitename <br />Try doing it directly <a href='$url' target='_blank'>$url</a><br />\n";
            }    
        }
        if($_POST['scanfiles'])
        {
            $url=stripslashes($site['ip_address']).'/coreScanFiles.php?direct=234*_SNS_DF2kj2kj2k';
            $synch=get_web_page($url);
            if ($synch['errmsg']=='')
            {
                print "Successfully scanned dates for $sitename <br />";
            }else {
                print "Possible issue scanning files for $sitename <br />Try doing it directly <a href='$url' target='_blank'>$url</a><br />\n";
            }
        }
        if($_POST['updatefiles'])
        {
            $url="http://".stripslashes($site['ip_address']).'coreDBSynch.php?direct=234*_SNS_DF2kj2kj2k';
            $synch=get_web_page($url);
            if ($synch['errmsg']=='')
            {
                print "Successfully synched $sitename <br />";
            }else {
                print "Possible issue synching $sitename <br />Try doing it directly <a href='$url' target='_blank'>$url</a><br />\n";
            }    
        }
        
    }
}

function update_file_versions()
{
    $sql="SELECT * FROM core_sites";
    $dbSites=dbselectmulti($sql);
    foreach($dbSites['data'] as $site)
    {
        $sitename=$site['site_name'];
        
    }
}


function get_web_page( $url )
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );

    $ch      = curl_init( $url );
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    /*,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects)
    */
    //curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}
footer();
?>