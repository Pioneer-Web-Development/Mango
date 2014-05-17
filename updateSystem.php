<?php
include("includes/mainmenu.php") ;
set_time_limit(0);
print "<body>
<div id='wrapper'>\n";

if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}

print "<form method=post>\n";
print "<input type='checkbox'  name='jobtable'>Update job table to remove continue id's<br />";
print "<input type='checkbox'  name='siteids'>Update site_id in all tables to current site<br />";
print "<input type='checkbox'  name='jobstats'>Look for and remove any duplicate stat records<br />";
print "<input type='checkbox'  name='jobstatsfixer'>Make sure that all jobs have a stat record<br />";
print "<input type='checkbox'  name='partvendors'>Update existing part vendors to new separate table<br />";
print "<input type='submit' value='Upgrade' name='submit'>\n";
print "</form>\n";

if ($_POST)
{
    if ($_POST['jobtable']){update_jobtable();}
    if ($_POST['siteids']){update_siteid();}
    if ($_POST['jobstats']){update_jobstats();}
    if ($_POST['jobstatsfixer']){fix_jobstats();}
    if ($_POST['partvendors']){part_vendors();}
}

function part_vendors()
{
    $sql="SELECT * FROM equipment_part WHERE part_vendor<>0";
    $dbParts=dbselectmulti($sql);
    if($dbParts['numrows']>0)
    {
        foreach($dbParts['data'] as $part)
        {
            $sql="INSERT INTO equipment_part_vendor (part_id, vendor_id, part_number, part_cost) VALUES ('$part[id]', '$part[part_vendor]', '$part[part_number]', '$part[part_cost]')";
            $dbInsert=dbinsertquery($sql);
            if($dbInsert['error']=='')
            {
                //now clear it from the part record
                $sql="UPDATE equipment_part SET part_vendor=0, part_cost='0.00', part_number='' WHERE id=$part[id]";
                $dbUpdate=dbexecutequery($sql);
                print "Updated $part[part_name]<br>";
            } else {
                print $dbInsert['error']."<br>";
            }
        }
    }
}

function update_jobstats()
{
    $sql="SELECT id FROM jobs WHERE status=1";
    $dbJobs=dbselectmulti($sql);
    //now we have all the jobs
    foreach($dbJobs['data'] as $job)
    {
        //now look up the stats. if we have 2 then we need to look
        $sql="SELECT * FROM job_stats WHERE job_id=$job[id]";
        $dbStat=dbselectmulti($sql);
        if($dbStat['numrows']==2)
        {
            //ok, we have two records. The one with the folder set to 0 and startdatetime_goal as Nul is the bad one
            $stat1=$dbStat['data'][0];
            $stat2=$dbStat['data'][1];
            if ($stat1['folder']==0 && $stat1['startdatetime_goal']=='')
            {
                print "Found a duplicate stat record...";
                //now delete the bad one
                $sql="DELETE FROM job_stats WHERE id=$stat1[id]";
                $dbDelete=dbexecutequery($sql);
                print "&nbsp;&nbsp;&nbsp;&nbsp;Deleted bad stat record<br>";
                //ok, this is the bad one
                //see if it has some data though
                if($stat1['plateroom_lastpage']!='')
                {
                    $lastpage=$stat1['plateroom_lastpage'];
                    $lastpagetime=$stat1['plateroom_lastpage_time'];
                    //update the other stat record with the late page info
                    $sql="UPDATE job_stats SET plateroom_lastpage='$lastpage', plateroom_lastpagetime='$lastpagetime' WHERE id=$stat2[id]";
                    $dbUpdate=dbexecutequery($sql);
                    print "&nbsp;&nbsp;&nbsp;&nbsp;Updated job stats with info from bad stat record<br>";
                }
                //finally, make sure the job id references the correct stats record
                $sql="UPDATE jobs SET stats_id=$stat2[id] WHERE id=$job[id]";
                $dbUpdate=dbexecutequery($sql);
                print "&nbsp;&nbsp;&nbsp;&nbsp;Updated job job record with correct stat record id<br>";
                print "<br>";
            }
        }    
    }
}

function fix_jobstats()
{
    $sql="SELECT id FROM jobs WHERE status=1";
    $dbJobs=dbselectmulti($sql);
    //now we have all the jobs
    foreach($dbJobs['data'] as $job)
    {
        if($job['stats_id']==0)
        {
            $sql="INSERT INTO job_stats (job_id) VALUES ('$job[id]')";
            $dbStat=dbinsertquery($sql);
            $statsid=$dbStat['insertid'];
            $sql="UPDATE jobs SET stats_id='$statsid' WHERE id=$job[id]";
            $dbUpdate=dbexecutequery($sql);
        }    
    }
}

function update_siteid()
{
    global $siteID;
    //load all local tables... run through them and update the site_id to the one in the system.
    $tables=dbgettables('mangodb');
    if(count($tables)>0)
    {
        foreach($tables['tables'] as $key=>$tname)
        {
            //$tname=$table['tables'];
            $sql="UPDATE $tname SET site_id=$siteID";
            $dbUpdate=dbexecutequery($sql);
            if ($dbUpdate['error']=='')
            {
                print "<li>Sucessfully updated $tname</li>\n";
            } else {
                print "<li>Problem with $tname</li>\n";
            }
        }
    }
}

function update_jobtable()
{
//this script converts jobs to elmininate the "continue" portion.
  //recurring job scripts, pressJobs.php, etc. all need to be updated at the same time to no
  //longer create duplicate jobs.
  
 //get the job stop time and continue_id from the jobs table
  $sql="SELECT continue_id, enddatetime  FROM jobs WHERE continue_id<>0";
  $dbContinue=dbselectmulti($sql);
  if($dbContinue['numrows']>0)
  {
      foreach($dbContinue['data'] as $job)
      {
          $enddatetime=$job['enddatetime'];
          $id=$job['continue_id'];
          //now update the base job
          $sql="UPDATE jobs SET enddatetime='$enddatetime' WHERE id=$id";
          $dbUpdate=dbexecutequery($sql);
      }
  }
  //then after the updates, delete all the continue_id>0 jobs
  $sql="DELETE FROM jobs_copy WHERE continue_id<>0";
  $dbDelete=dbexecutequery($sql);
  print "Job Table successfully upgraded";
}
dbclose();  
?>
</div>
</body>
</html>
