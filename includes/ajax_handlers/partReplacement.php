<?php
//this script is designed to handle ajax calls for the inserts.php script
  session_start();
  include("../functions_db.php");
  include("../functions_formtools.php");
  include("../config.php");
  include("../functions_common.php");
  
  if($_POST)
  {
      $action=$_POST['action'];
  } else {
      $action=$_GET['action'];
  }
  
  switch($action)
  {
    case "perform":
        $form=true;
        perform_maintenance();
        break;
    
    case "save":
        save_maintenance();
        break;
    
    case "performpm":
        $form=true;
        perform_pm();
        break;
    
    case "save":
        save_maintenance();
        break;    
    
    case "savepm":
        save_pm();
        break;    
  }
  
function perform_maintenance()
{
    global $productionStaff;
    $partid=intval($_GET['partid']);
    $equipmentid=intval($_GET['equipmentid']);
    $equipmenttype=$_GET['equipmenttype'];
    $componentid=intval($_GET['componentid']);
    $subcomponentid=intval($_GET['subcomponentid']);
    $pressid=intval($_GET['pressid']);
    $piece=$_GET['piece'];
    $location=$_GET['locationsub'];
    $item=$_GET['item'];
    
    print "<form id='ajaxRepairForm' name='ajaxRepairForm' action='includes/ajax_handlers/partReplacement.php' method=post>";
    make_datetime('replacedt',date("Y-m-d H:i"),'When performed',"When was this maintenance done?");
    make_select('replaceby',$productionStaff[$_SESSION['cmsuser']['userid']],$productionStaff,'Who lead the task','Choose the person responsible for the maintenance task');
    make_number('replacementtime',0,'Replacement time?','How many total minutes did the job take? (people times job time)');
    make_textarea('replacenotes','','Notes','Notes about anything unsual encountered while performing the task',50,5);
    print "<input type='hidden' name='item' value='$item'>\n";
    print "<input type='hidden' name='partid' value='$partid'>\n";
    print "<input type='hidden' name='equipmentid' value='$equipmentid'>\n";
    print "<input type='hidden' name='equipmenttype' value='$equipmenttype'>\n";
    print "<input type='hidden' name='componentid' value='$componentid'>\n";
    print "<input type='hidden' name='subcomponentid' value='$subcomponentid'>\n";
    print "<input type='hidden' name='pressid' value='$pressid'>\n";
    print "<input type='hidden' name='piece' value='$piece'>\n";
    print "<input type='hidden' name='location' value='$location'>\n";
    print "<input type='hidden' name='action' value='save'>\n";
    print "</form>\n";
}

function perform_pm()
{
    global $productionStaff;
    $partid=intval($_GET['partid']);
    $equipmentid=intval($_GET['equipmentid']);
    $equipmenttype=$_GET['equipmenttype'];
    $componentid=intval($_GET['componentid']);
    $pressid=intval($_GET['pressid']);
    $piece=$_GET['piece'];
    $subcomponentid=intval($_GET['subcomponentid']);
    $location=$_GET['locationsub'];
    $item=$_GET['item'];
    print "<form id='ajaxRepairForm' name='ajaxRepairForm' action='includes/ajax_handlers/partReplacement.php' method=post>";
    make_datetime('replacedt',date("Y-m-d H:i"),'When performed',"When was this maintenance done?");
    
    make_select('replaceby',$productionStaff[$_SESSION['cmsuser']['userid']],$productionStaff,'Who lead the task','Choose the person responsible for the maintenance task');
    make_number('replacementtime',0,'Task time?','How many total minutes did the job take? (people times job time)');
    make_textarea('replacenotes','','Notes','Notes about anything unsual encountered while performing the task',50,8);
    print "<input type='hidden' name='item' value='$item'>\n";
    print "<input type='hidden' name='partid' value='$partid'>\n";
    print "<input type='hidden' name='equipmenttype' value='$equipmenttype'>\n";
    print "<input type='hidden' name='equipmentid' value='$equipmentid'>\n";
    print "<input type='hidden' name='componentid' value='$componentid'>\n";
    print "<input type='hidden' name='subcomponentid' value='$subcomponentid'>\n";
    print "<input type='hidden' name='pressid' value='$pressid'>\n";
    print "<input type='hidden' name='piece' value='$piece'>\n";
    print "<input type='hidden' name='location' value='$location'>\n";
    print "<input type='hidden' name='action' value='savepm'>\n";
    print "</form>\n";
}
  
  

function save_maintenance()
{
    $partid=$_POST['partid'];
    $equipmentid=$_POST['equipmentid'];
    $equipmenttype=$_POST['equipmenttype'];
    $componentid=$_POST['componentid'];
    $subcomponentid=$_POST['subcomponentid'];
    $pressid=$_POST['pressid'];
    $piece=$_POST['piece'];
    $location=$_POST['location'];
    $replacementtime=$_POST['replacementtime'];
    $replacenotes=addslashes($_POST['replacenotes']);
    $replaceby=$_POST['replaceby'];
    $item=$_POST['item'];
    //first step is to look up the part to get all the part details like cost
    $sql="SELECT * FROM equipment_part WHERE id=$partid";
    $dbPart=dbselectsingle($sql);
    $part=$dbPart['data'];
    
    
    $nowtime=date("Y-m-d H:i:s");
    $nowtime=$_POST['replacedt'];
    //close out the old part
    
    if($equipmentid==0 || $equipmentid==''){
        $equipmentid=$pressid;
    }
    
    $sql="UPDATE part_instances SET replaced=1, replace_by='$replaceby', replace_datetime='$nowtime',replace_time='$replacementtime', replace_count=1, replace_notes='$replacenotes' 
    WHERE part_id='$partid' AND equipment_id='$equipmentid' AND component_id='$componentid' 
    AND sub_component_id='$subcomponentid' AND equipment_type='$equipmenttype' 
    AND sub_component_location='$location' AND replaced=0";
    $dbUpdate=dbexecutequery($sql);
    if($dbUpdate['numrows']==0)
    {
        //no existing part
    }
    //now create the new part instance
    $sql="INSERT INTO part_instances (part_id, equipment_id, equipment_type, component_id, sub_component_id, sub_component_location, install_datetime, install_count, install_by, life_count, life_time, replaced, cur_time, cur_count) VALUES ('$partid', '$equipmentid', '$equipmenttype', '$componentid', '$subcomponentid', '$location', '$nowtime', '1', '$replaceby', '$part[part_life_impressions]', '$part[part_life_days]',
    0, 0, 0)";
    $dbInsert=dbinsertquery($sql);
    if ($dbInsert['error']=='')
    {
        //successfully created the part
        print "success|Maintenance successful|part_info".$item."_".$partid;
        
        //reduce inventory by one for this part
        $sql="UPDATE equipment_part SET part_inventory_quantity=part_inventory_quantity-1 WHERE id=$partid";
        $dbUpdate=dbexecutequery($sql);
    } else {
        print "error|".$dbInsert['error'];
    }
    
}

function save_pm()
{
    $pmid=$_POST['partid'];
    $equipmentid=$_POST['equipmentid'];
    $equipmenttype=$_POST['equipmenttype'];
    $componentid=$_POST['componentid'];
    $subcomponentid=$_POST['subcomponentid'];
    $pressid=$_POST['pressid'];
    $piece=$_POST['piece'];
    $location=$_POST['location'];
    $replacementtime=$_POST['replacementtime'];
    $replacenotes=addslashes($_POST['replacenotes']);
    $replaceby=$_POST['replaceby'];
    $item=$_POST['item'];
    //first step is to look up the part to get all the part details like cost
    $sql="SELECT * FROM equipment_pm WHERE id=$pmid";
    $dbPart=dbselectsingle($sql);
    $part=$dbPart['data'];
    if($equipmentid==0 || $equipmentid==''){
        $equipmentid=$pressid;
    }
    
    $nowtime=date("Y-m-d H:i:s");
    $nowtime=$_POST['replacedt'];
    //close out the old part
    $sql="UPDATE pm_instances SET replaced=1, replace_by='$replaceby', replace_datetime='$nowtime', 
    replace_count=1, replace_notes='$replacenotes', completed=1 WHERE pm_id='$pmid' AND equipment_id='$equipmentid' AND 
    component_id='$componentid' AND sub_component_id='$subcomponentid' AND equipment_type='$equipmenttype' 
    AND sub_component_location='$location' AND completed=0";
    $dbUpdate=dbexecutequery($sql);
    if($dbUpdate['numrows']==0)
    {
        //no existing part
    }
    //now create the new part instance
    $sql="INSERT INTO pm_instances (pm_id, completed, equipment_id, equipment_type, component_id, sub_component_id, sub_component_location, install_datetime, install_by, life_count, life_time, 
    replaced, replace_cost, cur_time, cur_count) VALUES ('$pmid', 0,'$equipmentid', '$equipmenttype', '$componentid', 
    '$subcomponentid', '$location', '$nowtime', '$replaceby', '$part[pm_life_impressions]', '$part[pm_life_days]','$part[pm_cost]', 0, 0, 0)";
    $dbInsert=dbinsertquery($sql);
    if ($dbInsert['error']=='')
    {
        //successfully created the part
        print "success|Maintenance successful|pm_info".$item."_".$pmid;
    } else {
        print "error|".$dbInsert['error'];
    }
    
}

dbclose();

if ($form)
{
?>
<script>
$(document).ready(function() { 
var options = { 
    //target:        '#output1',   // target element(s) to be updated with server response 
    //beforeSubmit:  showRequest,  // pre-submit callback 
    success:       showResponse  // post-submit callback 

    // other available options: 
    //url:       url         // override for form's 'action' attribute 
    //type:      type        // 'get' or 'post', override for form's 'method' attribute 
    //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
    //clearForm: true        // clear all form fields after successful submit 
    //resetForm: true        // reset the form after successful submit 

    // $.ajax options can be used here too, for example: 
    //timeout:   3000 
}; 

// bind form using 'ajaxForm' 
$('#ajaxRepairForm').ajaxForm(options);
});
</script>
<?php } ?>
