<?php
//<!--VERSION: .9 **||**-->
function nav()
{
    //lets see which publications this user has access to
    $userid=$_SESSION['cmsuser']['userid'];
    $sql="SELECT * FROM user_publications WHERE user_id=$userid AND value=1";
    $dbPubs=dbselectmulti($sql);
    if ($dbPubs['numrows']>0)
    {
        $pubids="";
        foreach($dbPubs['data'] as $pub)
        {
            $pubids.=$pub['pub_id'].",";
        }
        $pubids=substr($pubids,0,strlen($pubids)-1);
        $pubfilter="AND A.pub_id IN ($pubids)";
    } else {
        $pubfilter="";
    }
    global $siteID;
    
    $jobid=$_GET['jobid'];
    $lastid=$_GET['lastid'];
    $action=$_GET['action'];
    $now=date("Y-m-d H:i");
    $nowdate=date("Y-m-d");
    if ($jobid!=0)
    {
        $sql="SELECT * FROM jobs WHERE id=$jobid";
        $dbCurrentJob=dbselectsingle($sql);
        $start=$dbCurrentJob['data']['startdatetime'];
        $end=$dbCurrentJob['data']['enddatetime'];
        if ($action=='prev')
        {
            $sql="SELECT * FROM jobs A WHERE A.enddatetime<='$start' AND A.status=1 $pubfilter ORDER BY A.enddatetime DESC LIMIT 1";    
        } elseif ($action=='next')
        {
            $sql="SELECT * FROM jobs A WHERE A.startdatetime>='$end' AND A.status=1 $pubfilter ORDER BY A.startdatetime ASC LIMIT 1";
        }
    }elseif($lastid!=0)
    {   
        //ok, we have a lastid, so we're looking for a less or greater job id than the last one
        $sql="SELECT * FROM jobs WHERE id=$lastid";
        $dbLastJob=dbselectsingle($sql);
        $start=$dbLastJob['data']['startdatetime'];
        $end=$dbLastJob['data']['enddatetime'];
        if ($action=='prev')
        {
            $sql="SELECT * FROM jobs A WHERE A.site_id=$siteID AND A.enddatetime<='$start' AND A.continue_id=0 $pubfilter ORDER BY A.enddatetime DESC LIMIT 1";    
        } elseif ($action=='next')
        {
            $sql="SELECT * FROM jobs A WHERE A.site_id=$siteID AND A.startdatetime>='$end' AND A.continue_id=0 $pubfilter ORDER BY A.startdatetime ASC LIMIT 1";
        }
        if ($_GET['bug']){print "<br>Got a last id of $lastid<br>Checking with action of $action and a sql of $sql<br>";}
        $dbCurrentJob=dbselectsingle($sql);
        if ($dbCurrentJob['numrows']==0)
        {
            //no previous or next job, so default to the job closest to now
            $sql="SELECT * FROM jobs A WHERE A.site_id=$siteID AND A.startdatetime>='$now' AND A.continue_id=0 $pubfilter ORDER BY startdatetime ASC LIMIT 1";
            $dbCurrentJob=dbselectsingle($sql);
            if ($dbCurrentJob['numrows']==0)
            {
                //so no jobs beyond this point, just pick the latest job then
                $sql="SELECT * FROM jobs A WHERE A.site_id=$siteID AND A.continue_id=0 $pubfilter ORDER BY A.startdatetime DESC LIMIT 1";
                $dbCurrentJob=dbselectsingle($sql);
            }
        }
    
    } else { 
        //no job id or last id specified, just pick the job closest to now
        $sql="SELECT * FROM jobs A WHERE A.site_id=$siteID AND A.startdatetime>='$now' AND A.continue_id=0 $pubfilter ORDER BY A.startdatetime ASC LIMIT 1";
        $dbCurrentJob=dbselectsingle($sql);
        if ($dbCurrentJob['numrows']==0)
        {
            //so no jobs beyond this point, just pick the latest job then
            $sql="SELECT * FROM jobs A WHERE site_id=$siteID AND continue_id=0 $pubfilter ORDER BY startdatetime DESC LIMIT 1";
            $dbCurrentJob=dbselectsingle($sql);
        }
        
    }
    $jobid=$dbCurrentJob['data']['id'];
    //print "Current id is $jobid";
    //print "Using $sql";
    $job=$dbCurrentJob['data'];
    $jobid=$job['id'];
    $pubdate=date("D F j, Y",strtotime($job['pub_date']));

    //gather all the data about this job
    $sql="SELECT * FROM publications WHERE id=$job[pub_id]";
    $dbPub=dbselectsingle($sql);
    $pub=$dbPub['data'];
    $pubname=$pub['pub_name'];

    $sql="SELECT * FROM publications_runs WHERE id=$job[run_id]";

    $dbRun=dbselectsingle($sql);
    $run=$dbRun['data'];
    $runname=$run['run_name'];

    $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";

    $dbJobSections=dbselectsingle($sql);
    $jsections=$dbJobSections['data']; 
    $prev="<a href='?action=prev&lastid=$jobid'>Previous Job</a>\n";
    $next="<a href='?action=next&lastid=$jobid'>Next Job</a>\n";
        
   
    
    print "<div id='nav' style='width:95%;font-family:Verdana;font-weight:bold;font-size:14px;'>\n";
        print "<div id='nav_prev' style='margin-right:4px;width:120px;text-align:center;float:left;padding:10px;border:thin solid black;background-color:white;'>\n";
            print $prev;
            print $next;
        print "</div>\n";
        print "<div id='nav_center' style='width:auto;float:left;text-align:center;'>\n";
            print "<p style='background-color:black;color:white;font-size:16px;font-weight:bold;margin-left:auto;margin-right:auto;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;'>Current Job is $pubname - $runname run for $pubdate - #$jobid</p>";//save a hidden text field here with the job id
            print "<input type='hidden' id='jobid' name='jobid' value='$jobid'>\n";
            print "<input type='hidden' id='ares' name='ares' value=''>\n";
            //here we will make a quick jump of jobs 24 hours back and 48 hours ahead
            $start=date("Y-m-d H:i",strtotime("-24 hours"));
            $end=date("Y-m-d H:i",strtotime("+48 hours"));
            $sql="SELECT A.*, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.pub_id=B.id AND A.run_id=C.id AND A.continue_id=0 AND A.status<>99 AND A.startdatetime>='$start' AND A.startdatetime<='$end' $pubfilter ORDER BY A.startdatetime ASC";
            //print "$sql<br />";
            
            $dbQuickJobs=dbselectmulti($sql);
            
            if ($dbQuickJobs['numrows']>0)
            {
                print "<select name='quickjob' id='quickjob' onchange='pressQuickJump(this.value);'>\n";
                print "<option id='opt_0' name='opt_0' value='0'>Select quick jump job</option>\n";
                foreach($dbQuickJobs['data'] as $qj)
                {
                    $pname=$qj['pub_name'];
                    $rname=$qj['run_name'];
                    $qjpubdate=date("D F j",strtotime($qj['pub_date']));
                    $starttime=date("m/d H:i",strtotime($qj['startdatetime']));
                    print "<option id='opt_$qj[id]' name='opt_$qj[id]' value='$qj[id]'>$pname - $rname for $qjpubdate, Start: $starttime</option>\n";
                }
                print "</select>\n";
            }
        print "</div>\n";
    print "</div>\n";
    print "<div class='clear'></div>\n";
    print "<div class='clear'></div>\n";
    
    
    //job data array
    $jdata=array('job'=>$job,'pub'=>$pub,'run'=>$run,'sections'=>$jsections);
    return $jdata;
}

?>
