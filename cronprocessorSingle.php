<?php
include ("includes/functions_db.php");
include ("includes/mail/htmlMimeMail.php");
include ("includes/functions_common.php");
include ("includes/config.php");
$cronid=intval($_GET['cronid']);
$oldtime=date("Y-m-d",strtotime("-8 days"));
$currenttime=date("H:i:s");
$currentdate=date("Y-m-d",time());       
$currenttimestamp=date("Y-m-d H:i:s",time());

$futuredate=date("Y-m-d",strtotime("+48 hours"));

$sql="SELECT * FROM core_cron WHERE id=$cronid";
$dbCron=dbselectsingle($sql);
$cronjob=$dbCron['data'];
$script=stripslashes($cronjob['script']);
$scriptFilename=end(explode("/",stripslashes($cronjob['script'])));
$function=stripslashes($cronjob['function']);
$params=stripslashes($cronjob['params']);
$rundate=$currentdate." ".$exectime;
print "Now executing $script...<br />";
if (strpos($script,"ttp://")>0 || substr($script,0,3)=="../" || substr($script,0,1)=="/")
{
    //lets leave these alone as they should be either calls to an http url, or a relative url from the root
    if(substr($script,0,1)=="/")
    {
       $script= $_SERVER{'DOCUMENT_ROOT'} .$script;
    }
} else {
    $script= $_SERVER{'DOCUMENT_ROOT'} . '/cronjobs/'.$script;
    
}
if (is_file($script))
{
    print "Processing...<br>";
    require_once($script);     
    $notes= "Called in $script, and executed $function with parameters of $params<br />\n";
    print $notes;
    if ($function!='')
    {
        $function($params);                  
    }
    
    //now update the cronexecution table
    global $info;
    if ($info!=''){
        $notes="<br />".$info;
    } else {
        print "No notes for this job<br>";
    }
    global $notes;
    if ($notes!=''){
        $info.="<br />".$notes;
    } else {
        print "No notes for this job<br>";
    }
    $status=1;
} else {
    $notes="This job did not run because the script file - $script was not found;";
    $status=2;
}
print $notes;
print "<ul>$job[title] executing $script";
print $info;
print "</ul>";
 
dbclose();
?>