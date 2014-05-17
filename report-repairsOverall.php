<?php
/* this report is intended to all you to pull a report of maintenance on any piece of equipment */

include("includes/mainmenu.php") ;
global $pubs, $siteID;
if ($_POST)
{
    generate_report();
} else {
    $equipment[0]='Please select';
    $components[0]='All components';
    print "<form method=post>\n";
    //presses
    $sql="SELECT * FROM press WHERE site_id=$siteID";
    $dbE=dbselectmulti($sql);
    if($dbE['numrows']>0)
    {
        foreach($dbE['data'] as $e)
        {
            $equipment['press_'.$e['id']]=$e['name'];    
        }
    }
    //inserters
    $sql="SELECT * FROM inserters WHERE site_id=$siteID";
    $dbE=dbselectmulti($sql);
    if($dbE['numrows']>0)
    {
        foreach($dbE['data'] as $e)
        {
            $equipment['inserter_'.$e['id']]=$e['inserter_name'];    
        }
    }
    //stitchers
    $sql="SELECT * FROM stitchers WHERE site_id=$siteID";
    $dbE=dbselectmulti($sql);
    if($dbE['numrows']>0)
    {
        foreach($dbE['data'] as $e)
        {
            $equipment['stitcher_'.$e['id']]=$e['stitcher_name'];    
        }
    }
    //misc equipment
    $sql="SELECT * FROM equipment WHERE site_id=$siteID ORDER BY equipment_name";
    $dbE=dbselectmulti($sql);
    if($dbE['numrows']>0)
    {
        foreach($dbE['data'] as $e)
        {
            $equipment['e_'.$e['id']]=$e['equipment_name'];    
        }
    }
    make_select('equipment_id',$equipment[0],$equipment,'Equipment','Select a piece of equipment');
    make_select('component_id',$components[0],$components,'Component','Select a sub-component or leave at all to report on all components for the selected equipment.');
    print '
        <script type="text/javascript">
        $("#equipment_id").selectChain({
            target: $("#component_id"),
            type: "post",
            url: "includes/ajax_handlers/repairGetComponents.php",
            data: { ajax: true }
        });
        </script>
        ';
        
        
    make_submit('submit','Process Form');
    print "</form>\n"; 
}

function generate_report()
{
    $base=$_POST['equipment_id'];
    $componentid=$_POST['component_id'];
    $base=explode("_",$base);
    $etype=$base[0];
    $equipmentid=$base[1];
    switch($etype)
    {
        case "press":
           press_report($equipmentid,$componentid);
        break;
        
        case "inserter":
           inserter_report($equipmentid,$componentid);
        break;
        
        case "stitcher":
           stitcher_report($equipmentid,$componentid);
        break;
        
        case "e":
           equipment_report($equipmentid,$componentid);
        break;
        
    } 
}

function press_report($equipmentid,$componentid)
{
    if($componentid==0)
    {
        //grabbing all towers
        $sql="SELECT * FROM press_towers WHERE press_id=$equipmentid ORDER BY tower_order";
    } else {
        //specific tower selected
        $sql="SELECT * FROM press_towers WHERE press_id=$equipmentid AND id=$componentid";
    }
    if(debug){print "Tower sql is $sql<br>";}
    $dbTowers=dbselectmulti($sql);
    if($dbTowers['numrows']>0)
    {
        print "<table class='grid'>\n";
        print "<tr>
        <th>Part Name</th>
        <th>Location</th>
        <th>Install Date</th>
        <th>Current Impressions</th>
        <th>Current Days</th>
        <th colspan=3>Status</th>
        </tr>";
        foreach($dbTowers['data'] as $tower)
        {
            //get the core component id
            $towertype=$tower['tower_type'];
            $sql="SELECT * FROM equipment_component WHERE equipment_id='$equipmentid' AND component_type='$towertype' AND equipment_type='press'";
            $dbBaseComponent=dbselectsingle($sql);
            $basecomponentid=$dbBaseComponent['data']['id'];
            
            print "<tr><th colspan=8>$tower[tower_name]</th></tr>\n";
            /*
            $sql="SELECT * FROM equipment_component WHERE component_type='$towertype'";
            if(debug){print "&nbsp;&nbsp;Component sql is $sql<br>";}
            $dbComponent=dbselectsingle($sql);
           
            if($dbComponent['numrows']>0)
            {
            */
                $partsql="SELECT A.*, B.component_id FROM equipment_part A, equipment_part_xref B 
                WHERE A.id=B.part_id AND B.equipment_id='$equipmentid' 
                AND B.component_id='$basecomponentid' AND B.equipment_type='press'";
                $dbPressParts=dbselectmulti($partsql);
                if(debug){print "&nbsp;&nbsp;&nbsp;&nbsp;Part sql $partsql<br>";}  
                if ($dbPressParts['numrows']>0)
                {        
                    foreach($dbPressParts['data'] as $presspart)
                    {
                               //lets see if we can find an open instance of this part
                               $sql="SELECT * FROM part_instances WHERE part_id=$presspart[id] AND equipment_id='$equipmentid' AND component_id='$tower[id]'";
                               if(debug){print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Instance sql $sql<br>";}  
                
                               $dbInstances=dbselectmulti($sql);
                               if ($dbInstances['numrows']>0)
                               {
                                   foreach($dbInstances['data'] as $instance)
                                   {
                                       print "<tr>";
                                       print "<td>$presspart[part_name]</td>";
                                       print "<td>$instance[sub_component_location]</td>";
                                       $installed=date("m/d/Y", strtotime($instance['install_datetime']));
                                       $curCount=$instance['cur_count'];
                                       $curTime=round($instance['cur_count']/60,2);
                                       print "<td>$installed</td><td>$curCount</td><td>$curTime days</td>";
                                       print "<td colspan=3>";
                                       if($part['part_life_type']=='impressions')
                                       {
                                           $lifeCount=$part['part_life_impressions'];
                                           if($lifeCount<$curCount)
                                           {
                                               print "<span style='color:red;'>Part is beyond the recommended life of $lifeCount. Please check and replace soon.</span>";
                                           } else {
                                               print "There are at least ".($lifeCount-$curCount)." cycles remaining before this part reaches its recommended replacement point.";
                                           }
                                       } else {
                                           $lifeCount=$part['part_life_days'];
                                           if($lifeCount<$curTime)
                                           {
                                               print "<span style='color:red;'>Part is beyond the recommended life of $lifeCount. Please check and replace soon.</span>";
                                           } else {
                                               print "There are at least ".($lifeCount-$curTime)." days remaining before this part reaches its recommended replacement point.";
                                           }
                                       }
                                       print "</td>\n";
                                       print "</tr>\n";
                                   }
                               } else {
                                   print "<tr><td>$presspart[part_name]</td><td colspan=7>Not installed on this unit. </td></tr>";
                               } 
                       }
                }
        /*    
        } else {
                print "<tr><th colspan=8>No components for for $tower[tower_name]</th></tr>\n";
            } 
            */     
        }
        print "</table>\n";  
    } else {
        print "No towers found.";
    }
    
}

function inserter_report($inserterid,$componentid)
{
    
}

function stitcher_report($stitcherd,$componentid)
{
    
}

function equipment_report($equipmentid,$componentid)
{
    
}

footer();
?>
