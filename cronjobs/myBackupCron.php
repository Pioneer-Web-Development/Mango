<?php
// This code was created by phpMyBackupPro v.2.2 
// http://www.phpMyBackupPro.net
if($_GET['mode']=='manual')
{
    mysqlBackup();
}
function mysqlBackup()
{
    $GLOBALS['notes'].="Started database backup with phpMyBackup at ".date("m/d/Y H:i").".<br>";
    $_POST['db']=array("mangodb", );
    $_POST['tables']="on";
    $_POST['data']="on";
    $_POST['drop']="on";
    $_POST['zip']="gzip";
    $period=(3600*24)*0;
    $security_key="aa438c72e8e051fbb93c25d9de8f5884";
    // switch to the phpMyBackupPro v.2.2 directory
    @chdir("/var/www/phpMyBackupPro/");
    @include("backup.php");
    @chdir("/var/www/cronjobs/");
    $GLOBALS['notes'].="Finished database backup with phpMyBackup at ".date("m/d/Y H:i").".<br>";
    if($_GET['mode']=='manual')echo $GLOBALS['notes']; 
}
?>
