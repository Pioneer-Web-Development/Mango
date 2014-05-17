<?php
include("../functions_db.php");
include("../config.php");
include("../functions_common.php");
global $papertypes, $sizes, $pressid, $pressmen, $siteID, $pressDepartmentID, $jobData, $broadsheetPageHeight;
$jobid=$_POST['pjid'];
//pull in job information
$sql="SELECT * FROM jobs WHERE id=$jobid";
$dbJobInfo=dbselectsingle($sql);
$jobinfo=$dbJobInfo['data'];
$pubdate=$jobinfo['pub_date'];
$printdate=$jobinfo['startdatetime'];
$pubid=$jobinfo['pub_id'];
$runid=$jobinfo['run_id'];
$layoutid=$jobinfo['layout_id'];
$folder=$jobinfo['folder'];
$pressid=$jobinfo['press_id'];
if($jobinfo['redo_job_id']!=0)
{
    //means we are in a redo, in which case we actually need the original
    //so, we need to requery with jobid = the redo job id
    $reprintid=$jobid;
    $jobid=$jobinfo['redo_job_id'];
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJobInfo=dbselectsingle($sql);
    $jobinfo=$dbJobInfo['data'];
    $pubdate=$jobinfo['pub_date'];
    $pubid=$jobinfo['pub_id'];
    $runid=$jobinfo['run_id'];
    
} else {
    $reprintid=0;
}
  
//get rid of existing paper for this job
$sql="DELETE FROM job_paper WHERE job_id='$jobid'";
$dbDelete=dbexecutequery($sql);
    
foreach($_POST as $key=>$value)
{
    if (substr($key,0,6)=="tower_")
    {
        $towerid=str_replace("tower_","",$key);
        $ptype=$_POST['t_'.$towerid.'_papertype'];
        $psize=$_POST['t_'.$towerid.'_size'];
        if($ptype!=0){$used=1;}else{$used=0;}
        $towerinfo.="$towerid,$value,$used,$ptype,$psize|";
        
        //only process paper if the papertype is not 0
        if ($ptype!=0)
        {
            //before we move on, we need to calulate everything about the paper being consumed for this tower
            //need roll_size, page_width, page_length, factor, tonnage, price, cost
            //print "Working with size of $psize<br/>\n";
            $sql="SELECT width FROM paper_sizes WHERE id=$psize";
            $dbSize=dbselectsingle($sql);
            $rollwidth=$dbSize['data']['width'];
            //print "Found rollwidth of $rollwidth<br />\n";
            $sql="SELECT * FROM paper_types WHERE id=$ptype";
            $dbPapertype=dbselectsingle($sql);
            $papertype=$dbPapertype['data'];
            $pricePerTon=$papertype['price_per_ton'];
            $pagelength=$GLOBALS['broadsheetPageHeight'];
            $pagewidth=$jobinfo['pagewidth'];
            
            $paperdataid=$papertype['paperdataid'];
            $pagesonroll=round($rollwidth/$pagewidth,0);
            
            
            //add the job_paper record
            $sql="INSERT INTO job_paper (job_id, pub_id, run_id, tower_id, papertype_id, 
                size_id, pub_date, print_date, roll_width, page_width, page_length, price_per_ton, 
                factor, calculated_tonnage, calculated_cost, site_id) VALUES ('$jobid', '$pubid',
                '$runid', '$towerid', '$ptype', '$psize', '$pubdate', '$printdate', '$rollwidth','$pagewidth',
                '$pagelength', '0', '0', '0', '0', '$siteID')";
            $dbInsertPaper=dbinsertquery($sql);
            $error.=$dbInsertPaper['error'];
 
            
        }
            
    }
}
$towerinfo=substr($towerinfo,0,strlen($towerinfo)-1);
//update the stats table
$sql="UPDATE job_stats SET tower_info='$towerinfo' WHERE job_id='$jobid'";
$dbUpdate=dbexecutequery($sql);

if($error!='')
{
    print "<span style='color:red;'>Error saving newsprint</span>";
} else {
    print "<span style='color:green;'>Newsprint saved</span>";
}

dbclose();
?>
