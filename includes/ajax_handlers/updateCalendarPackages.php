<?php
  session_start();
  include("../functions_db.php");
  include("../functions_common.php");
  include("../config.php");
  global $siteID;
  $jobid=intval($_POST['jobid']);
  $dayDelta=intval($_POST['dayDelta']);
  //convert days to minutes
  $dayDelta=$dayDelta*24*60;
  $minuteDelta=intval($_POST['minuteDelta']);
  $totalMinutes=$dayDelta+$minuteDelta;
  
  //get current start/end for the job
  $sql="SELECT package_startdatetime, package_stopdatetime FROM jobs_inserter_packages WHERE id=$jobid";
  $dbJob=dbselectsingle($sql);
  $startdatetime=$dbJob['data']['package_startdatetime'];
  $enddatetime=$dbJob['data']['package_stopdatetime'];
  $newstartdatetime=date("Y-m-d H:i:s",strtotime("$startdatetime +$totalMinutes minutes"));
  $newenddatetime=date("Y-m-d H:i:s",strtotime("$enddatetime +$totalMinutes minutes"));
  
  $response['original']['startdatetime']=$startdatetime;
  $response['original']['stopdatetime']=$enddatetime;
  $response['original']['newstartdatetime']=$newstartdatetime;
  $response['original']['newstopdatetime']=$newenddatetime;
  $response['original']['action']=$_POST['type'];
  $response['original']['jobid']=$_POST['jobid'];
  $response['original']['dayDelta']=$_POST['dayDelta'];
  $response['original']['minuteDelta']=$_POST['minuteDelta'];
  
  if($_POST['type']=='move')
  {
    //means we need to update starttime and endtime
    $sql="UPDATE jobs_inserter_packages SET package_startdatetime='$newstartdatetime', package_stopdatetime='$newenddatetime' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql);
    if ($dbUpdate['error']!='')
    {
        $response['status']='error';
        $response['error_message']=$dbUpdate['error'];
    } else {
        $response['status']='success';
    }
  } elseif($_POST['type']=='resize')
  {
    $sql="UPDATE jobs_inserter_packages SET package_stopdatetime='$newenddatetime' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql);
    if ($dbUpdate['error']!='')
    {
        $response['status']='error';
        $response['error_message']=$dbUpdate['error'];
    } else {
        $response['status']='success';
    }
  } elseif($_POST['type']=='delete')
  {
      $sql="SELECT * FROM jobs_inserter_packages WHERE id=$jobid";
      $dbPackage=dbexecutequery($sql);
      $planid=$dbPackage['data']['plan_id'];
      $packid=$jobid;
      
      //now we also want to clear any inserts that may be in this package
      $sql="SELECT * FROM jobs_packages_inserts WHERE plan_id=$planid AND package_id=$packid";
        $dbInserts=dbselectmulti($sql);
        $response['status']='success';
        $response['inserts_removed']=0;
        if($dbInserts['numrows']>0)
        {
            foreach($dbInserts['data'] as $insert)
            {
                $temp=removeInsert($insert['plan_id'],$insert['package_id'],$insert['insert_id'],$insert['insert_type'],$insert['hopper_id']);
                $response['secondary_id']=$temp['secondary_id'];
                $response['secondary_weight']=$temp['secondary_weight'];
                $response['secondary_count']=$temp['secondary_count'];
                $response['secondary_pages']=$temp['secondary_pages'];
                
                $sql="SELECT A.*, B.account_name FROM inserts A, accounts B WHERE A.id=$insert[insert_id] AND A.advertiser_id=B.id";
                $dbInsertInfo=dbselectsingle($sql);
                $insertInfo=$dbInsertInfo['data'];
                $insertname=stripslashes($insertInfo['account_name'])." ".stripslashes($insertInfo['insert_tagline']);
                $insertname=str_replace("'","",$insertname);
                $insertname="<b>".$insertname."</b>";
                $insertpages=$insertInfo['tab_pages'];
                $request=$insertInfo['insert_quantity'];
                $insertinfo=$insertname."<br><b>Pages: </b>".$insertpages." <b>Request: </b>$request";
                $response['inserts_removed']=$response['inserts_removed']+1;
                $response['inserts'][]=array('insert_id'=>$insert['insert_id'],'insert_name'=>$insertname,'insert_type'=>$insert['insert_type'],'insert_text'=>$insertinfo);
                $deleteids=$insert['id'].",";
            }
        }
        $deleteids=substr($deleteids,0,strlen($deleteids)-1);
        if($deleteids!='')
        {
            $sql="DELETE FROM jobs_packages_inserts WHERE id IN ($deleteids)";
            $dbDelete=dbexecutequery($sql);
        }
        //subtract one from the number of package in the jobs_inserter_plans
        $sql="UPDATE jobs_inserter_plans SET num_packages=num_packages-1 WHERE id=$planid";
        $dbUpdate=dbexecutequery($sql);
        if($dbUpdate['error']!='')
        {
            $response['status']='error';
            $response['error_message'].=$dbUpdate['error'];
        }
        //delete the actual package
        $sql="DELETE FROM jobs_inserter_packages WHERE id=$packid";
        $dbDelete=dbexecutequery($sql);
        if($dbDelete['error']!='')
        {
            $response['status']='error';
            $response['error_message'].=$dbDelete['error'];
        } 
  }
  
  echo json_encode($response);
  dbclose();
?>