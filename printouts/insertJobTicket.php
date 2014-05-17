<?php
    session_start();
    include("../includes/functions_db.php");
    include("../includes/config.php");
    include("../includes/functions_common.php");
    
    
?>
<!DOCTYPE html>
<html>
<head>
<link>
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
  .noprint {
      display:none;
  }
}
</style>
<?php
$appfield='mango';      
$scriptname=end(explode("/",$_SERVER['SCRIPT_NAME']));
//lets load the style sheets
$sql="SELECT * FROM core_system_files WHERE file_type='style' AND head_load=1 AND $appfield=1 ORDER BY load_order ASC";
$dbStyles=dbselectmulti($sql);
if($dbStyles['numrows']>0)
{
    foreach($dbStyles['data'] as $style)
    {
       $loadfor=explode(",",$style['specific_page']);
       if($style['specific_page']=='' || in_array($scriptname,$loadfor))
       {
           $uptime=strtotime($style['file_moddate']); 
           print "<link rel='stylesheet' type='text/css' href='/styles/$style[file_name]?$uptime' />\n";     
       }       
    }
}

//lets load the javascript files
$sql="SELECT * FROM core_system_files WHERE file_type='script' AND head_load=1 AND $appfield=1 ORDER BY load_order ASC";
$dbScripts=dbselectmulti($sql);
if($dbScripts['numrows']>0)
{
    foreach($dbScripts['data'] as $script)
    {
        $loadfor=explode(",",$script['specific_page']);
        $uptime=strtotime($script['file_moddate']); 
        if($script['specific_page']=='' || in_array($scriptname,$loadfor))
        {
            print "<script type='text/javascript' src='/includes/jscripts/$script[file_name]?$uptime'></script>\n";     
        } else {
            print "<!-- when loading $script[file_name] looked for $script[specific_page] compared to $scriptname -->\n";
        }       
    }
}
?>
</head>
<body>
<div class='noprint'><a href='#' onclick='window.print();return false;'><img src='../artwork/printer.png' width=32 border=0>Print this plan</a><br><br></div>
    
<?php
    
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
    $packageid=intval($_GET['packageid']);

    
    $sql="SELECT * FROM jobs_inserter_packages WHERE id=$packageid";
    $dbPackages=dbselectsingle($sql);
    $package=$dbPackages['data'];
    
    $planid=$package['plan_id'];
    
    $sql="SELECT * FROM jobs_inserter_plans WHERE id=$planid";
    $dbPlan=dbselectsingle($sql);
    $plan=$dbPlan['data'];

    $pubid=$plan['pub_id'];
    $runid=$plan['run_id'];
    $pubdate=$plan['pub_date'];
    $pubname=$pubs[$pubid];
    
    
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
    
    $displaydate=date("D, m/d/Y",strtotime($package['pub_date']));
    $schedstart=date("D, m/d/Y H:i",strtotime($package['package_startdatetime']));
    $pubname=$package['pub_name'];
    $packagename=$package['package_name'];
    $keepers=array();
    $hasstickynote=false;
    $stickynote="";
    
    $inserterid=$package['inserter_id'];
    $sql="SELECT * FROM inserters WHERE id=$inserterid";
    $dbInserter=dbselectsingle($sql);
    $inserter=$dbInserter['data'];
   
    $sql="SELECT FROM jobs_packages_inserts A, inserts B WHERE A.insert_type='insert' AND A.package_id='$packageid' AND A.insert_id=B.id";
    $dbPackageInserts=dbselectmulti($sql);
    
    
    print "<div id='layout' style='width:800px;height:900px;font-family:Trebuch MS, Arial, sans-serif;font-weight:normal;font-size:12px;'>\n";
    print "<p style='text-align:center;font-weight:bold;font-size:16px;'>Inserter ticket: $pubname $packagename, Publish: $displaydate, Scheduled Start: $schedstart</p>\n";
    if($hasstickynote)
    {
        print "<div style='width:300px;margin:10px;border:2px solid black;padding:4px;text-align:center;'>Sticky Notes:$stickynote</div>";
    }
    print "<div id='leftside' style='float:left;'>\n";
    print "<div id='packageholder' style='width:400px;'>";
    print "<img src='/artwork/icons/ajax-loader.gif' style='margin-top:200px;margin-left:200px;' /><br>";
    ?>
    <script>
       $(document).ready(function(){
        loadPackageOfInserts(); 
           
       })
       function loadPackageOfInserts()
       {
           $.ajax({
            url: '/includes/ajax_handlers/generateInsertPackageDisplay.php',
            data: {packageid:<?php echo $packageid; ?>,people:0,count:1,zones:1,editing:0,maxwidth:360},
            dataType: 'html',
            success: function(response){
               $('#packageholder').html(response); 
            }
           })    
          
       }
   </script>
   <?php
      
    print "</div>\n";
    print "<div class='clear'></div>\n"; 
    print "Total Stations Used: $package[total_inserts]<br />\n";
    print "Total Tab Pages: $package[tab_pages]<br />\n";
    
     print "<div style='margin-top:10px;background-color:white;padding:4px;'>\n";
        //pull in any checklist items
        $sql="SELECT * FROM checklist WHERE checklist_category='Mailroom' ORDER BY checklist_order ASC";
        $dbCheck=dbselectmulti($sql);
        if ($dbCheck['numrows']>0)
        {
            print "<p style='font-weight:bold;text-size:12px;'>Daily Checklist items</p>\n";
            foreach($dbCheck['data'] as $item)
            {
                print "<div class='checkbox'></div><div class='checkitem'>$item[checklist_item]</div><div style='clear:both;'></div>\n";
                
            }
        }   
     print "</div>\n";  //closes the checklist
     
     if(count($keepers)>0)
     {
         print "<div style='width:300px;margin:10px;border:2px solid black;padding:4px;text-align:left;'>
         <ul>Save leftover product for these:";
         foreach($keepers as $key=>$insert)
         {
             print "<li>$insert</li>";
         }
         print "</ul></div>";
     }
   
   print "</div>\n";  //closes the left side
     
   print "<div id='rightside' style='float:left;width:380px;font-weight:normal;font-size:12px;'>\n"; //open the right side
   
   //check for sticky note
   if ($dbInsert['numrows']>0)
   {
       foreach($dbInserts['data'] as $insert)
       {
            if ($insert['sticky_note']){
                $sticky=true;
                print "<p style='font-weight:bold;font-size:12px;'>This package run has a sticky note for $insert[account_name]</p>\n";
            }    
       }
   }
   print "<p><b>This run is scheduled to start:</b></p>\n";
   $start=date("D, m/d/Y \@ H:i",strtotime($package['package_startdatetime']));
   print "<p>$start</p>\n";
   print "<p><b>Request to produce:</b>&nbsp;$package[inserter_request]</p>\n";
   
   if ($GLOBALS['insertSignOff']!='')
   {
       print "<p style='font-weight:bold;margin-top:10px;width:320px;border-top:thin solid black;'>INSERT SIGN-OFF POLICY:</p>\n";
       print stripslashes($GLOBALS['insertSignOff']);
   } else {
       print "<p style='font-weight:bold;margin-top:10px;width:320px;border-top:thin solid black;'>Inserted signed off by:</p>\n";
   }
   print "<br />Signed:<p style='width:200px;border-bottom:thin solid black;margin-top:10px;'>&nbsp;</p>\n";
   print "<p style='font-weight:bold;margin-top:10px;width:320px;'>MACHINE SETUP BY:</p>\n";
   print "<p style='width:200px;border-bottom:thin solid black;margin-top:10px;'>&nbsp;</p>\n";
   print "<p style='width:200px;border-bottom:thin solid black;margin-top:10px;'>&nbsp;</p>\n";
   print "<p style='font-weight:bold;margin-top:10px;width:320px;'>&nbsp;</p>\n";
   print "<div style='float:left;font-weight:bold;width:200px;'>How many employees:</div><div style='float:left;width:100px;border-bottom:thin solid black;height:19px;'>&nbsp;</div><div style='clear:both;height:1px;'></div>\n";
   print "<div style='float:left;font-weight:bold;width:200px;'>How many pallets<br />shrink-wrapped:</div><div style='float:left;width:100px;border-bottom:thin solid black;height:19px;'>&nbsp;</div><div style='clear:both;height:1px;'></div>\n";
   print "<div style='float:left;font-weight:bold;width:200px;'>Leftover Jackets:</div><div style='float:left;width:100px;border-bottom:thin solid black;height:19px;'>&nbsp;</div><div style='clear:both;height:1px;'></div>\n";
   print "<div style='float:left;font-weight:bold;width:200px;'>Total pieces:<br /><small>From inserter report</small></div><div style='float:left;width:100px;border-bottom:thin solid black;height:19px;'>&nbsp;</div><div style='clear:both;height:1px;'></div>\n";
   print "<div style='float:left;font-weight:bold;width:200px;'>Did we address it:</div><div style='float:left;width:100px;border-bottom:thin solid black;height:19px;'>&nbsp;</div><div style='clear:both;height:1px;'></div>\n";
   
   //now make spots for times
   //3 floated divs
   print "<div style='float:left;width:100px;font-weight:bold;'>\n";
       print "<p style='height:20px;'><b>Times</b></p>\n";
       print "<p style='height:20px;'>Run Start:</p>\n";
       print "<p style='height:20px;'>Run Finish:</p>\n";
       print "<p style='height:20px;'>First Bundle:</p>\n";
       print "<p style='height:20px;'>Last Bundle:</p>\n";
       print "<p style='height:20px;'>First Truck:</p>\n";
       print "<p style='height:20px;'>Last Truck:</p>\n";
   print "</div>\n";
   print "<div style='float:left;width:90px;margin-left:5px;'>\n";
       print "<p style='height:20px;font-weight:bold;'>$inserter[side_one_name]</p>\n";;
       print "<p style='width:90px;border-bottom:thin solid black;height:19px;background-color:yellow;'>&nbsp;</p>\n";
       print "<p style='width:90px;border-bottom:thin solid black;height:19px;background-color:yellow;'>&nbsp;</p>\n";
       print "<p style='width:90px;border-bottom:thin solid black;height:19px;background-color:yellow;'>&nbsp;</p>\n";
       print "<p style='width:90px;border-bottom:thin solid black;height:19px;background-color:yellow;'>&nbsp;</p>\n";
       print "<p style='width:90px;border-bottom:thin solid black;height:19px;background-color:yellow;'>&nbsp;</p>\n";
       print "<p style='width:90px;border-bottom:thin solid black;height:19px;background-color:yellow;'>&nbsp;</p>\n";
   print "</div>\n";
   if ($double)
   {
       print "<div style='float:left;width:90px;margin-left:5px;'>\n";
           print "<p style='height:20px;font-weight:bold;'>$inserter[side_two_name]</p>\n";;
           print "<p style='width:90px;border-bottom:thin solid black;height:19px;background-color:yellow;'>&nbsp;</p>\n";
           print "<p style='width:90px;border-bottom:thin solid black;height:19px;background-color:yellow;'>&nbsp;</p>\n";
           print "<p style='width:90px;border-bottom:thin solid black;height:19px;background-color:yellow;'>&nbsp;</p>\n";
           print "<p style='width:90px;border-bottom:thin solid black;height:19px;background-color:yellow;'>&nbsp;</p>\n";
           print "<p style='width:90px;border-bottom:thin solid black;height:19px;background-color:yellow;'>&nbsp;</p>\n";
           print "<p style='width:90px;border-bottom:thin solid black;height:19px;background-color:yellow;'>&nbsp;</p>\n";
       print "</div>\n";
   }
   print "<div style='clear:both;height:1px;'></div>\n";    
   
   
   print "</div>\n"; //closes the right side    
   print "<div style='clear:both;'></div>\n";
   //now some notes Lines:
    print "<p style='font-weight:bold;font-size:14px;'>NOTES:</p>\n";
    print "<div style='width:670px;height:20px;border-bottom:thin solid black;'></div>\n";
    print "<div style='width:670px;height:20px;border-bottom:thin solid black;'></div>\n";
    print "<div style='width:670px;height:20px;border-bottom:thin solid black;'></div>\n";
    print "<div style='width:670px;height:20px;border-bottom:thin solid black;'></div>\n"; 
        
    
    
footer();
?>