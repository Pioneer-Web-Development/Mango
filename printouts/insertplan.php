<!DOCTYPE html>
<html>
<head>
<style type="text/css">
body {
    font-family: Trebuchet MS, Arial, sans-serif;
    font-size: 12px;
    padding: 0;
    margin: 0;
}
.clear {
    clear: both;
    height: 0px;
}
.package {
    width:320px;
    float:left;
    margin-left:8px;
    border: thin solid black;
    padding: 6px;
    margin-bottom:10px;
}

@media print {
  /* style sheet for print goes here */
  .printer {
      display:none;
  }
}
</style>
</head>
<body>
<div class='printer'><a href='#' onclick='window.print();return false;'><img src='../artwork/printer.png' width=32 border=0'>Print this plan</a><br><br></div>
    
<?php
    include("../includes/functions_db.php");
    include("../includes/config.php");
    include("../includes/functions_common.php");
    
    global $pubs;
    /*************************************************************
    *  this function will create a printout (width max 700px)
    * 
    *  contents will be the plan information (publication, pub date)
    *  then print a two column format of packages
    *  package details will include Package Name, Run time, sticky note
    *  inserter selected, double-out or not and a station schematic
    *  showing paired stations (if double out) and the insert for each   
    */
    $planid=intval($_GET['planid']);

    $sql="SELECT * FROM jobs_inserter_plans WHERE id=$planid";
    $dbPlan=dbselectsingle($sql);
    $plan=$dbPlan['data'];

    $sql="SELECT * FROM jobs_inserter_packages WHERE plan_id=$planid ORDER BY package_startdatetime";
    $dbPackages=dbselectmulti($sql);
    
    $pubid=$plan['pub_id'];
    $runid=$plan['run_id'];
    $pubdate=$plan['pub_date'];
    $pubname=$pubs[$pubid];
    $displaydate=date("m/d/Y",strtotime($pubdate));
    
    
    print "<p style='font-weight:bold;font-size:16px;'>Package Planner - Publication: $pubname for $displaydate</p>\n";
    
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
    if($dbPackages['numrows']>0)
    {
        $packages=array();
        foreach($dbPackages['data'] as $package)
        {
            $packages[$package['id']]=$package; //re-key to use the package id for easier re-lookup
        }
        $counter=0;
        foreach($dbPackages['data'] as $package)
        {
            print "\n\n<div class='package'>\n";
            print "<h2>".stripslashes($package['package_name'])."</h2>\n";
            print "<span>Scheduled to run at ".date("H:i",strtotime($package['package_startdatetime']))." on ".date("l, M jS",strtotime($package['package_startdatetime']))."</span><br>";
            print "<span>Scheduled to run on ".$inserters[$package['inserter_id']]."</span><br>\n";
            if($package['sticky_note_id'])
            {
                print "<span>This package has a sticky note: ".$inserts[$package['sticky_note_id']]['account_name']." ".$inserts[$package['sticky_note_id']]['insert_tagline']."</span><br>\n";
            }
            if($package['double_out'])
            {
                $doubleout=true;
                print "<span>This package is set up for double out</span><br>\n";
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
                
                
                foreach($dbStations['data'] as $station)
                {
                    $stationNumber=$station['hopper_number'];
                    if($doubleout && $station['hopper_number']>$inserterturn)
                    {
                        //don't print it then
                    } else {
                        print "\n\n<div style='width:280px;margin-bottom:2px;margin-left:10px;'>\n";
                            print "   <div style='float:left;width:80px;margin-right:3px;text-align:right;font-weight:bold;font-size:18px;padding-top:4px;'>\n";
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
                                //now we need a little detail about the insert
                                if($inserttype=='insert')
                                {
                                    $insertname=$inserts[$insertid]['account_name'].'-'.$inserts[$insertid]['insert_tagline'];
                                    $insertname=stripslashes($insertname);
                                } elseif ($inserttype=='package')
                                {
                                    $insertname=stripslashes($packages[$insertid]['package_name']); 
                                } elseif ($inserttype=='jacket')
                                {
                                    $insertname="Generic Jacket";
                                }
                            } else {
                                $insertname='none';
                            }
                            
                            //$insertinfo=addslashes($insertinfo);
                            print "  <div id='pack_$package[id]-station_$station[id]' class='station' style='float:left;width:$stationWidth;height:30px;'>\n";
                            print "    ".$insertname;
                            print "\n  </div>\n";
                                
                        print "</div><!--closes the station $station[id] in package $package[id] -->\n";
                        print "<div class='clear'></div>\n";
                    }
                }
                
            } else {
                print "Inserter is not configured.";
            }
            print "<div class='clear'></div>\n";
            
            
            
            
            print "</div><!--closing package-->\n\n\n";
            
            if($counter==1)
            {
                print "<div class='clear'></div>\n";
                $counter=0;    
            } else {
                $counter=1;
            }       
        }    
    }
    
    
    
    dbclose();  
?>
</body>
</html>