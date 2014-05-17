<?php
  //handles general ajax calls that are not specifically tied to a page
  include("../functions_db.php");
  include("../functions_common.php");
  include("../config.php");
  if($_GET['mode']=='test')
  {
    checkForFile();   
  }
  
  
  $action=$_POST['action'];
  
  switch($action)
  {
    case 'checkforfile':
        checkForFile();
    break;
    
    case 'calendarPackageTooltip':
        $packageid=$_POST['id'];
        $qtip="<div style='width:250px;'>\n";
        //generate a list of all inserts in the package
        $sql="SELECT package_name FROM jobs_inserter_packages WHERE id=$packageid";
        $dbPackage=dbselectsingle($sql);
        $sql="SELECT * FROM jobs_packages_inserts WHERE package_id=$packageid ORDER BY insert_type";
        $dbCheck=dbselectmulti($sql);
        if($dbCheck['numrows']>0)
        {
           $qtip.=getPackageContents($qtip,$packageid);  
        } else {
           $qtip.="<br><b>Package: </b>".$dbPackage['data']['package_name'];
           $qtip.=" contains no inserts<br>"; 
        }
        $qtip.="</div>\n";
        $response['qtip']=$qtip;
        $response['status']='success';
        echo json_encode($response);
    break;
        
  }
  
  
  
  function checkForFile()
  {
      $filename=$_POST['filename'];
      $path=urldecode($_POST['path']);
      if($path==''){$path='/';}
      $tocheck=$_SERVER['DOCUMENT_ROOT'].$GLOBALS['systemRootPath'].$path.$filename;
      if(file_exists($tocheck))
      {
          print 'true';
      } else {
          print 'false|'.$tocheck;
      }
  }
  
  dbclose();