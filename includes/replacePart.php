<?php
  session_start();
  //this script handles the replacements of parts in the maintenance system
  include("functions_db.php");
  include("config.php");
  include("functions_common.php");
  global $averageHourlyPressWage;
    $partid=$_GET['partid'];
    $time=$_GET['time'];
    $type=$_GET['type'];
    $replace_cost=round($averageHourlyPressWage*$time/60,2);
    //get the details of the part from the right place
    if ($type=='press')
    {
        $sql="SELECT * FROM press_parts WHERE id=$partid";
        $dbPartDetails=dbselectsingle($sql);
        $partdetails=$dbPartDetails['data'];
        
        //see if there is an existing instance
        $sql="SELECT * FROM part_instances WHERE source_id=$partid AND source_type='presstemplate' AND replaced=0";
        $dbInstance=dbselectsingle($sql);
        $by=$_SESSION['cmsuser']['userid'];
        $dt=date("Y-m-d H:i:s");
        if ($dbInstance['numrows']>0)
        {
            $instance=$dbInstance['data'];
            $instanceid=$dbInstance['id'];
            //ok, one exists, we need to update it, and close it out
            $sql="UPDATE part_instances SET replace_time=current_time, replace_count=current_count, replaced=1, replace_by='$by', replace_datetime='$dt', replace_cost='$replace_cost' WHERE id=$instanceid";
            $dbUpdate=dbexecutequery($sql);
            
            //add a record to the instance log
            $sql="INSERT INTO part_instance_log (instance_id,event_datetime,event_type) VALUES ($instanceid,'$dt','Replaced with new part')";
            $dbInsert=dbinsertquery($sql);
               
        }
        //get the part info
        $sql="SELECT * FROM equipment_part WHERE id=$partdetails[part_id]";
        $dbPart=dbselectsingle($sql);
        $part=$dbPart['data'];
        $lifecount=$part['part_life_impressions'];
        $lifetime=$part['part_life_days'];
        
        //now, take one out of inventory
        $sql="UPDATE equipment_part SET part_inventory_quantity=part_inventory_quantity-1 WHERE id=$partdetails[part_id]";
        $dbUpdateInventory=dbexecutequery($sql);
        
        //now, make a new instance of the part
        $sql="INSERT INTO part_instances (part_id, equipment_id, source_id, source_type, install_datetime, install_by, cur_time, cur_count, life_count, life_time, replaced) VALUES ($partdetails[part_id],$partdetails[tower_id],$partdetails[id],'presstemplate', '$dt', '$by', 0,0, '$lifecount', '$lifetime', 0)";
        $dbNew=dbinsertquery($sql);
        $newid=$dbNew['numrows'];
        //add a record to the instance log
        $sql="INSERT INTO part_instance_log (instance_id,event_datetime,event_type) VALUES ($newid,'$dt','Installed new part')";
        $dbInsert=dbinsertquery($sql);
        if($dbNew['error']=='')
        {
            print "New part installed successfully.";
        } else {
            print "There was an error creating the part";
        }
    }
  
  
  dbclose();
?>
