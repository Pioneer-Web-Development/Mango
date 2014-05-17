<?php  
// This script was created by phpMyBackupPro v.2.4 (http://www.phpMyBackupPro.net)
// In order to work probably, it must be saved in the directory /var/www/cronjobs/.
$_POST['db']=array("mangodb", );
$_POST['tables']="on";
$_POST['data']="on";
$_POST['drop']="on";
$period=(3600*24)*0;
$security_key="58e0d911b3d610326c09ab9ec3a41ae5";
// switch to the phpMyBackupPro v.2.4 directory
@chdir("/var/www/phpMyBackupPro/");
@include("backup.php");
// switch back to the directory containing this script
@chdir("/var/www/cronjobs/");
?>