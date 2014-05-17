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

table {
    width: 700px;
    border-collapse:collapse;
    border:1px solid #FFCA5E;
}
caption {
    font: 18px Trebuchet MS, Arial, Helvetica, sans-serif;
    text-align: left;
    text-indent: 10px;
    background: url(/styles/images/popuptable_bg_caption.jpg) right top;
    height: 45px;
    color: #FFAA00;
}
thead th {
    background: url(/styles/images/popuptable_bg_th.jpg) no-repeat right;
    height: 47px;
    color: #FFFFFF;
    font-size: 0.8em;
    font-weight: bold;
    padding: 0px 7px;
    margin: 20px 0px 0px;
    text-align: left;
    border-right: 1px solid #FCF1D4;
}
tbody tr {
    background: url(/styles/images/popuptable_bg_td1.jpg) repeat-x top;
}
tbody tr.odd {
    background: #FFF8E8 url(/styles/images/popuptable_bg_td2.jpg) repeat-x;
}

tbody th,td {
    font-size: 0.8em;
    line-height: 1.4em;
    font-family: Trebuchet MS, Arial, Helvetica, sans-serif;
    color: #777777;
    padding: 10px 7px;
    border-top: 1px solid #FFCA5E;
    border-right: 1px solid #DDDDDD;
    text-align: left;
}
a {
    color: #777777;
    font-weight: bold;
    text-decoration: underline;
}
a:hover {
    color: #F8A704;
    text-decoration: underline;
}
tfoot th {
    background: url(/styles/images/popuptable_bg_total.jpg) repeat-x bottom;
    color: #FFFFFF;
    height: 30px;
}
tfoot td {
    background: url(/styles/images/popuptable_bg_total.jpg) repeat-x bottom;
    color: #FFFFFF;
    height: 30px;
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
<div class='printer'><a href='#' onclick='window.print();return false;'><img src='../artwork/printer.png' width=32 border=0>Print Package</a><br><br></div>
    
<?php
    include("../includes/functions_db.php");
    include("../includes/config.php");
    include("../includes/functions_common.php");
    
    global $pubs;
    /*************************************************************
    *  this function will create a printout (width max 700px)
    * 
    *  contents will be the plan information (publication, pub date)
    *  then print a all possible data about the package
    *  package details will include Package Name, Run time, sticky note
    *  inserter selected, double-out or not and a station schematic
    *  showing paired stations (if double out) and the insert for each   
    */
    $packageid=intval($_GET['packageid']);
    
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
    
    $slocations=buildInsertLocations();
    
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
    print "<br><br>";
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
        $odd=1;
        print "<table style='width:700px;'>\n";
        print "<thead><tr><th>Station #</th><th style='width:200px'>Insert Name</th>";
        if($GLOBALS['insertUseLocation'])
        {
            print "<th>Location</th>";
        }
        print "<th>Zones</th><th>Request</th><th>Pages</th><th>Confirmed</th><th>Received</th><th>Sticky Note</th>
        <th>Keep Remaining</th><th>Slick</th><th>We Printed</th></tr></thead>\n";
        
        if($GLOBALS['insertUseLocation'])
        {
            print "<tcaption><tr><td colspan=12>";
        } else {
            print "<tcaption><tr><td colspan=11>";
        }
        print "<b>Package Name: ".stripslashes($package['package_name'])."</b>\n";
    
        print "</td></tr></tcaption>";
        print "<tbody>";
        $fillertds="<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>\n";
        $check="<img src='/artwork/icons/accepted_48.png' alt='True' height=20 />";
        foreach($dbStations['data'] as $station)
        {
            if($odd)
            {
                print "<tr class='odd'>";
               $odd=0; 
            } else {
                print "<tr>";
               $odd=1;
            }
            print "<td>";
            $stationNumber=$station['hopper_number'];
            if($doubleout && $station['hopper_number']<$minDoubleHopper)
            {
                $guessHopper=$maxDoubleHopper-intval($station['hopper_number'])+1;
                
                //ok, lets see if there is a pairing for this double-out setup
                $sql="SELECT * FROM jobs_packages_hopper_pairings WHERE package_id='$package[id]' AND hopper_id='$station[id]'";
                $dbPairing=dbselectsingle($sql);
                if($dbPairing['numrows']>0)
                {
                    $guessHopper=$dbPairing['data']['secondary_value'];   
                }
                print "$guessHopper - ";
                
            }
            if($station['jacket_station'])
            {
                $stationNumber='J-'.$stationNumber;
            }
            print "    ".$stationNumber;
            print "</td>\n";
            
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
                    $insert=$inserts[$insertid];
                    
                } elseif ($inserttype=='package')
                {
                    $insertname="Package: ".stripslashes($packages[$insertid]['package_name']);
                } elseif ($inserttype=='jacket')
                {
                    $insertname="Generic Jacket";
                }
                print "<td>";
                //$insertinfo=addslashes($insertinfo);
                print $insertname;
                print "</td>";
                
                print "<td>";
                //look up zone information
                //now get zoning details
                //need to first get the schedule id, then work to the insert_zoning table with it
                $sql="SELECT * FROM jobs_inserter_plans WHERE id=$planid";
                $dbPlan=dbselectsingle($sql);
                $pubid=$dbPlan['data']['pub_id'];
                $pubdate=$dbPlan['data']['pub_date'];
                
                $sql="SELECT * FROM inserts_schedule WHERE insert_id='$insertid' AND pub_id=$pubid AND insert_date='$pubdate'";
                $dbSchedule=dbselectsingle($sql);
                $scheduleid=$dbSchedule['data']['id'];
                //$qtip.="Trying schedule lookup with $sql<br>";
                
                $sql="SELECT A.zone_name, B.zone_count FROM publications_insertzones A, insert_zoning B 
                WHERE B.sched_id=$scheduleid AND B.insert_id=$insertid AND B.zone_id=A.id";
                //$qtip.="Trying zone lookup with $sql<br>";
                $dbZones=dbselectmulti($sql);
                if($dbZones['data']>0)
                {
                    foreach($dbZones['data'] as $zone)
                    {
                        print "&nbsp;&nbsp;".$zone['zone_name']."<br>";
                    }
                }
                print "</td>";
                
                if ($GLOBALS['insertUseLocation'])
                {
                    print "<td>";
                    if($inserttype!='package')
                    {
                        print $slocations[$insert['storage_location']];
                    }
                    print "</td>";
                }
                
                print "<td>";
                    print $insert['insert_quantity'];
                print "</td>";
                
                print "<td>";
                    print $insert['tab_pages'];
                print "</td>";
                
                print "<td>";
                if($insert['confirmed'])
                {
                    print $check;
                }
                print "</td>";
                
                print "<td>";
                if($insert['received'])
                {
                    print $check;
                }
                print "</td>";
                
                print "<td>";
                if($insert['sticky_note'])
                {
                    print $check;
                }
                print "</td>";
                
                
                print "<td>";
                if($insert['keep_remaining'])
                {
                    print $check;
                }
                print "</td>";
                
                print "<td>";
                if($insert['slick_sheet'])
                {
                    print $check;
                }
                print "</td>";
                
                print "<td>";
                if($insert['weprint_id']>0)
                {
                    print $check;
                }
                print "</td>";
            } else {
                print "<td>";
                print "No insert";
                print "</td>\n";
                
                //and a bunch of blank tds...
               print $fillertds; 
            }
            print "</tr>\n";
                    
            
        }
        print "</tbody>";
        print "</table>\n";
    } else {
        print "Inserter is not configured.";
    }
    print "<div class='clear'></div>\n";
    
    
    
    dbclose();  
?>
</body>
</html>