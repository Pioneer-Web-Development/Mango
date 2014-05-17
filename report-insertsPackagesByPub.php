<?php
if($_POST['output']=='csv')
{
    include("includes/functions_db.php");
    include("includes/functions_common.php");
    show_report('csv');
} else {
    include("includes/mainmenu.php");
    global $pubs;
    $pubs[0]='All publications';
    if($_POST)
    {
        $pub=$_POST['pub'];
        $start=$_POST['start'];
        $end=$_POST['end'];
    } else {
        $pub=0;
        $start=date("Y-m-d");
        $end=date("Y-m-d",strtotime("+1 week"));
    }
    print "<form method=post>\n";
        make_select('pub',$pubs[$pub],$pubs,'Publication');
        make_date('start',$start,'Start Date');
        make_date('end',$end,'End Date');
        make_select('output','screen',array('screen'=>'Screen','csv'=>'Excel'),'Output');
        make_submit('submit','Generate Report');
    print "</form>\n";
    show_report('screen');
} 
  

function show_report($output)
{
    global $pubs;
    $pub=$_POST['pub'];
    $start=$_POST['start'];
    $end=$_POST['end'];
    
    $sql="SELECT * FROM accounts";
    $dbAccounts=dbselectmulti($sql);
    if($dbAccounts['numrows']>0)
    {
        foreach($dbAccounts['data'] as $account)
        {
            $accounts[$account['id']]=stripslashes($account['account_name']);
        }
    }
    
    $sql="SELECT * FROM inserters";
    $dbInserters=dbselectmulti($sql);
    if($dbInserters['numrows']>0)
    {
        foreach($dbInserters['data'] as $inserter)
        {
            $inserters[$inserter['id']]=stripslashes($inserter['inserter_name']);
        }
    }
    switch($output)
    {
        case 'csv':
            $tablestart="Day,Package Date/Time,Pub Date,Package Name,Bundle Size,Jacket,Inserts\n";
            $tableend='';
            $rowstart='';
            $highlightstart='';
            $rowend="\n";
            $newcell=",";
        break;
        
        case 'screen':
            $tablestart="<table class='report-clean-mango'>
            <tr><th>Day</th><th style='width:60px;'>Package Date/Time</th>
            <th style='width:60px;'>Pub Date</th><th style='width:80px;'>Package Name</th><th>Bundle Size</th><th>Jacket</th><th style='width:400px;'>Inserts</th></tr>\n";
            $tableend="</table>\n";
            $highlightstart="<tr style='background-color:#fff000;border-top:2px solid black;margin-top:10px;'><td>";
            $rowstart='<tr><td>';
            $rowend="</td></tr>\n";
            $newcell="</td><td>";
        break;
    }
    if($pub==0)
    {
        $sql="SELECT * FROM jobs_inserter_packages WHERE package_startdatetime>='$start' AND package_stopdatetime<='$end' ORDER BY pub_id, package_date";
    } else {
        $sql="SELECT * FROM jobs_inserter_packages WHERE pub_id=$pub AND package_startdatetime>='$start' AND package_stopdatetime<='$end' ORDER BY pub_id, package_date";
    }
    //print $sql;
    $report='';
    $dbPackages=dbselectmulti($sql);
    if($dbPackages['numrows']>0)
    {
        $report.=$tablestart;
        foreach($dbPackages['data'] as $package)
        {
            $inserts='';
            $jacket='Not set';
            $day=date("D",strtotime($package['package_date']));
            $date=date("d-M H:i",strtotime($package['package_date']));
            $pubDate=date("d-M",strtotime($package['pub_date']));
            $packageName=$package['package_name'];
            
            $packageRunTime=date("m/d H:i",strtotime($package['package_startdatetime']));
            $jacketid=$package['jacket_insert_id'];
            $inserterid=$package['inserter_id'];
            $inserter=$inserters[$inserterid];
            $request=$package['inserter_request'];
            $pubid=$package['pub_id'];
            $pub=$pubs[$package['pub_id']];
            
            //get settings for this package
            $sql="SELECT * FROM jobs_inserter_packages_settings WHERE package_id=$package[id]";
            $dbSettings=dbselectsingle($sql);
            $settings=$dbSettings['data'];
            $bundleSize=$settings['copies_per_bundle'];
            
            //pull in the list of inserts for this package
            $sql="SELECT A.*, B.hopper_number FROM jobs_packages_inserts A, inserters_hoppers B 
            WHERE A.package_id=$package[id] AND A.hopper_id=B.id ORDER BY B.hopper_number ASC";
            $dbInserts=dbselectmulti($sql);
            if($dbInserts['numrows']>0)
            {
                foreach($dbInserts['data'] as $insert)
                {
                    //since we have the possibility of a 0 insert_id... or a package
                    if($insert['insert_id']==$jacketid)
                    {
                        $sql="SELECT * FROM inserts WHERE id=$insert[insert_id]";
                        $dbInsert=dbselectsingle($sql);
                        $advertiserid=$dbInsert['data']['advertiser_id'];
                        $jacket=$accounts[$advertiserid]; 
                    }
                    if($insert['insert_type']=='package')
                    {
                        $sql="SELECT * FROM jobs_inserter_packages WHERE id=$insert[insert_id]";
                        $subpackage=dbselectsingle($sql);
                        $subName=$subpackage['data']['package_name'];
                        $inserts.="Station: $insert[hopper_number] - Package - $subName<br>";  
                    } elseif($insert['insert_type']=='jacket' && $insert['insert_id']!=0)
                    {
                        $sql="SELECT * FROM inserts WHERE id=$insert[insert_id]";
                        $dbInsert=dbselectsingle($sql);
                        $advertiserid=$dbInsert['data']['advertiser_id'];
                        $inserts.="Station: $insert[hopper_number] - Jacket - $accounts[$advertiserid]<br>";
                    } elseif($insert['insert_id']==0)
                    {
                        $inserts.="Station: $insert[hopper_number] - Generic jacket/insert<br>";
                    } else {
                        $sql="SELECT * FROM inserts WHERE id=$insert[insert_id]";
                        $dbInsert=dbselectsingle($sql);
                        $advertiserid=$dbInsert['data']['advertiser_id'];
                        $inserts.="Station: $insert[hopper_number] - $accounts[$advertiserid]<br>";
                    }
                } 
            } else {
                $inserts='None set at this time.';
            }
            
            //$tablestart="Day,Package Date/Time,Pub Date,Package Name,Bundle Size,Jacket,Inserts\n";
             
            $report.=$rowstart;
            $report.=$day;
            $report.=$newcell.$date;
            $report.=$newcell.$pubDate;
            $report.=$newcell.$packageName;
            $report.=$newcell.$bundleSize;
            $report.=$newcell.$jacket;
            $report.=$newcell.$inserts;
            $report.=$rowend;
        }
        $report.=$tableend;
        
        if($output=='csv')
        {
            $filename="packageReport-".date("Y-m-d",strtotime($start)).".csv";
            header('Content-Type: text/plain'); // plain text file
            header('Content-Disposition: attachment; filename="'.$filename.'"');
        }
        print $report;
    } else {
        print "Sorry, there are no packages matching those search criteria.";
    }
}
  
if($_POST['output']=='csv')
{
    dbclose();
} else {
    footer();
}
?>
