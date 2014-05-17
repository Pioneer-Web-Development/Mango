<?php
include("../functions_db.php");
include("../functions_formtools.php");
include("../config.php");
include("../functions_common.php");
$packageid=intval($_GET['packageid']);
if($_GET['people'])
{
    $includepeople=true;
}
if($_GET['count'])
{
    $includecount=true;
}
if($_GET['zones'])
{
    $includezones=true;
}
if($_GET['editing'])
{
    $editing=true;
}
if($_GET['maxwidth'])
{
    $packagewidth=$_GET['maxwidth'];
    $holderwidth=$_GET['maxwidth']-6;
}else {
    $packagewidth=300;
    $holderwidth=294;
}
$sql="SELECT * FROM jobs_inserter_packages WHERE id=$packageid";
$dbPackage=dbselectsingle($sql);
$package=$dbPackage['data'];

$planid=$package['plan_id'];

$sql="SELECT * FROM jobs_inserter_plans WHERE id=$planid";
$dbPlan=dbselectsingle($sql);
$plan=$dbPlan['data'];

$pubid=$plan['pub_id'];
$runid=$plan['run_id'];
$pubdate=$plan['pub_date'];
$pubname=$pubs[$pubid];
$displaydate=date("l m/d/Y",strtotime($pubdate));

//get all the inserts scheduled for this plan
$sql="SELECT B.*, A.insert_quantity, C.account_name FROM inserts_schedule A, inserts B, accounts C 
    WHERE A.insert_id=B.id AND A.pub_id=$pubid AND A.insert_date='$pubdate' AND B.advertiser_id=C.id 
    ORDER BY B.confirmed DESC, C.account_name";
$dbInserts=dbselectmulti($sql);
$inserts=array();
if($dbInserts['numrows']>0)
{
    foreach($dbInserts['data'] as $insert)
    {
        $inserts[$insert['id']]=$insert; //re-key to use the insert id for easier re-lookup
    }
}    

$sql="SELECT * FROM inserters";
$dbInserters=dbselectmulti($sql);
$inserters=array();
if($dbInserters['numrows']>0)
{
    foreach($dbInserters['data'] as $inserter)
    {
        $inserters[$inserter['id']]=$inserter['inserter_name'];
    }
}
if($package['double_out'])
{
    $doubleout=true;
} else {
    $doubleout=false;
}
print "\n\n<div class='package' style='width:".$packagewidth."px'>\n";

if($package['double_out'])
{
    $doubleout=true;
} else {
    $doubleout=false;
}

$sql="SELECT * FROM inserters WHERE id=$package[inserter_id]";
$dbInserter=dbselectsingle($sql);
$inserter=$dbInserter['data'];
$inserterturn=$inserter['inserter_turn'];

//get the inserts for this package
$sql="SELECT * FROM jobs_packages_inserts WHERE plan_id=$planid AND package_id=$package[id]";
$dbPackageInserts=dbselectmulti($sql);

$sql="SELECT * FROM inserters_hoppers WHERE inserter_id=$package[inserter_id] ORDER BY hopper_number";
$dbStations=dbselectmulti($sql);
if($dbStations['numrows']>0)
{
    //run through them fast to get counts
    $minDoubleHopper=$inserterturn+1; //one more than where the turn is
    $i=0;
    $stations[0]=0;
    foreach($dbStations['data'] as $station)
    {
        if($i==0)
        {
            $minHopper=$station['hopper_number'];
            $i++;
        }
        $stations[$station['hopper_number']]=$station['id'];
        $maxDoubleHopper=$station['hopper_number']; //keep setting in, the last value will be the largest
    }
    
    global $mailers;
    $insidewidth=$holderwidth-70;
    
    if($package['sticky_note_id']!=0)
    {
        $insertid=$package['sticky_note_id'];
        $weprintid=$inserts[$insertid]['weprint_id'];
        $inserttype='Sticky note';
        $buycount=$inserts[$insertid]['buy_count'];
        $accountname=stripslashes($inserts[$insertid]['account_name']);
        if($weprintid>0)
        {
            $sql="SELECT A.pub_id, B.run_name FROM jobs A, publications_runs B WHERE A.id=$weprintid AND A.run_id=B.id";
            $dbJob=dbselectsingle($sql);
            $accountname=stripslashes($dbJobs['data']['run_name']);
        }
        $request=$inserts[$insertid]['insert_quantity'];
        $insertname=$accountname." ".stripslashes($inserts[$insertid]['insert_tagline']);
        $insertname=str_replace("'","",$binsertname);
        print " Sticky Note: $insertname<br />
        <input type='hidden' name='station_999999_insertid' value='$insertid' style='display:none;' />\n";
        if($includezones)
        {
            //get zone information
            //now get zoning details
            //need to first get the schedule id, then work to the insert_zoning table with it
            $sql="SELECT * FROM inserts_schedule WHERE insert_id='$insertid' AND pub_id=$pubid 
            AND insert_date='$pubdate'";
            $dbSchedule=dbselectsingle($sql);
            $scheduleid=$dbSchedule['data']['id'];
            $sql="SELECT A.zone_name, B.zone_count FROM publications_insertzones A, insert_zoning B 
            WHERE B.sched_id=$scheduleid AND B.insert_id=$insertid AND B.zone_id=A.id";
            $dbZones=dbselectmulti($sql);
            if($dbZones['data']>0)
            {
                print "<br><b>Zones:</b>&nbsp;\n";
                foreach($dbZones['data'] as $zone)
                {
                    print $zone['zone_name']."($zone[zone_count])&nbsp;&nbsp;&nbsp;&nbsp;";
                }
            }    
        }
        
      
    
        if($includepeople)
        {
            print "<br>\n";
            if(count($mailers)>0)
            {
                //see if this person has been saved
                $sql="SELECT * FROM jobs_inserter_rundata_stations WHERE package_id='$packageid' AND station_id='999999'";
                $dbPerson=dbselectsingle($sql);
                if($dbPerson['numrows']>0)
                {
                    $person=$dbPerson['data']['user_id'];
                } else {
                    $person=0;
                }
                if($editing)
                {
                    print input_select('stationd_999999_person',$mailers[$person],$mailers); 
                } else {
                    print $mailers[$person];
                }
               
            }
            
        }
        if($includecount)
        {
            print "<br>";
            //this shows a box with the buy count and allow the actual output quantity to be entered
            //see if this quantity has been saved
            $sql="SELECT * FROM jobs_inserter_rundata_stations WHERE package_id='$packageid' AND station_id='999999'";
            $dbQuantity=dbselectsingle($sql);
            if($dbQuantity['numrows']>0)
            {
                $buycount=$dbQuantity['data']['quantity'];
            }    
            if($editing)
            {
                print "<input type='text' name='stationd_999999_count' id='stationd_999999_count' style='width:90px;' value='$buycount' />\n";
            } else {
                print "Ordered quantity: ".$buycount;
            }
           
        }
    }
            
    foreach($dbStations['data'] as $station)
    {
        $stationNumber=$station['hopper_number'];
        if($doubleout && $station['hopper_number']>$inserterturn)
        {
            //don't print it then
        } else {
            print "<div style='border:thin solid black;
            padding:2px;
            margin-bottom:10px;
            min-height:30px;
            font-family:Trebuchet MS, Arial, sans-serif;
            font-size:12px;
            width: ".$holderwidth."px'>\n";
                print "   <div style='float:left;width:50px;margin-right:3px;text-align:right;font-weight:bold;font-size:18px;padding-top:4px;'>\n";
                if($doubleout && $station['hopper_number']<=$minDoubleHopper)
                {
                    $guessHopper=$maxDoubleHopper-intval($station['hopper_number'])+1;
                    
                    //ok, lets see if there is a pairing for this double-out setup
                    $sql="SELECT * FROM jobs_packages_hopper_pairings WHERE package_id='$package[id]' AND hopper_id='$station[id]'";
                    $dbPairing=dbselectsingle($sql);
                    if($dbPairing['numrows']>0)
                    {
                        $guessHopper=$dbPairing['data']['secondary_value'];   
                    }
                    print "<span id='$station[id]_$package[id]_selectvalue' style='float:left;display:inline;width:40px;background:none;border:none;font-weight:bold;font-size:18px;'>$guessHopper &amp;</span>\n";
                    
                }
                if($station['jacket_station'])
                {
                    $stationNumber='J-'.$stationNumber;
                }
                print "    ".$stationNumber;
                print "\n  </div>\n";
                
                //ok, lets see if we have an insert for this slot
                $sql="SELECT * FROM jobs_packages_inserts WHERE package_id='$package[id]' AND hopper_id='$station[id]'";
                $dbCheckInsert=dbselectsingle($sql);
                if($dbCheckInsert['numrows']>0)
                {
                    //woohoo! there is an insert booked for this package and station
                    $insertid=$dbCheckInsert['data']['insert_id'];
                    $inserttype=$dbCheckInsert['data']['insert_type'];
                    $buycount=$inserts[$insertid]['buy_count'];
                    //now we need a little detail about the insert
                    if($inserttype=='insert')
                    {
                        $insertname=$inserts[$insertid]['account_name'].'-'.$inserts[$insertid]['insert_tagline'];
                        $insertname=stripslashes($insertname);
                    } elseif ($inserttype=='package')
                    {
                        $sql="SELECT * FROM jobs_inserter_packages WHERE id=$insertid";
                        $dbPackageCheck=dbselectsingle($sql);
                        $checkpackage=$dbPackageCheck['data'];
                        $insertname="<b>Package: ".stripslashes($checkpackage['package_name'])."</b>"; 
                        $buycount=stripslashes($checkpackage['inserter_request']); 
                    } elseif ($inserttype=='jacket')
                    {
                        $insertname="Generic Jacket";
                        $insertid='J';
                    }
                    
                } else {
                    $insertname='No insert';
                    $buycount=0;
                }
                print "  <div id='pack_$package[id]-station_$station[id]' class='station' style='float:left;width:".$insidewidth."px;font-size:10px;'>\n";
                print "    <input type='hidden' name='station_$station[id]_insertid' value='$insertid' style='display:none;' />\n";
                
                print "    ".$insertname;
                if($includezones)
                {
                    //get zone information
                    //now get zoning details
                    //need to first get the schedule id, then work to the insert_zoning table with it
                    $sql="SELECT * FROM inserts_schedule WHERE insert_id='$insertid' AND pub_id=$pubid 
                    AND insert_date='$pubdate'";
                    $dbSchedule=dbselectsingle($sql);
                    $scheduleid=$dbSchedule['data']['id'];
                    $sql="SELECT A.zone_name, B.zone_count FROM publications_insertzones A, insert_zoning B 
                    WHERE B.sched_id=$scheduleid AND B.insert_id=$insertid AND B.zone_id=A.id";
                    $dbZones=dbselectmulti($sql);
                    if($dbZones['data']>0)
                    {
                        print "<br><b>Zones:</b>&nbsp;\n";
                        foreach($dbZones['data'] as $zone)
                        {
                            print $zone['zone_name']."($zone[zone_count])&nbsp;&nbsp;&nbsp;&nbsp;";
                        }
                    }    
                }
                
              
            
                if($includepeople)
                {
                    print "<br>\n";
                    if(count($mailers)>0)
                    {
                        //see if this person has been saved
                        $sql="SELECT * FROM jobs_inserter_rundata_stations WHERE package_id='$packageid' AND station_id='$station[id]'";
                        $dbPerson=dbselectsingle($sql);
                        if($dbPerson['numrows']>0)
                        {
                            $person=$dbPerson['data']['user_id'];
                        } else {
                            $person=0;
                        }
                        if($editing)
                        {
                            print input_select('stationd_'.$station['id'].'_person',$mailers[$person],$mailers); 
                        } else {
                            print $mailers[$person];
                        }
                       
                    }
                    
                }
                if($includecount)
                {
                    print "<br>";
                    //this shows a box with the buy count and allow the actual output quantity to be entered
                    //see if this quantity has been saved
                    $sql="SELECT * FROM jobs_inserter_rundata_stations WHERE package_id='$packageid' AND station_id='$station[id]'";
                    $dbQuantity=dbselectsingle($sql);
                    if($dbQuantity['numrows']>0)
                    {
                        $buycount=$dbQuantity['data']['quantity'];
                    }    
                    if($editing)
                    {
                        print "<input type='text' name='stationd_$station[id]_count' id='stationd_$station[id]_count' style='width:90px;' value='$buycount' />\n";
                    } else {
                        print "Ordered quantity: ".$buycount;
                    }
                   
                }
                 print "\n  </div>\n";     
            print "<div style='clear:both;height:0px;'></div>\n";
            print "</div><!--closes the station $station[id] in package $package[id] -->\n";
            
        }
    }
    
} else {
    print "Inserter is not configured.";
}
print "<div class='clear'></div>\n";




print "</div><!--closing package-->\n\n\n";

?>
