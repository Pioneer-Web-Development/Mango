<?php
include("../functions_db.php");
include("../config.php");
include("../common.php");
include("../functions_formtools.php");
global $papertypes, $sizes;
if($_POST)
{
    $rollid=$_POST['id'];
    $value=$_POST['value'];
    $type=$_POST['type'];
    //break the temp by splitting to figure out what type of value and the id of the roll
    switch($type)
    {
        case "rolltag":
        $field='roll_tag';
        break;
        
        case "rollname":
        $field='common_name';
        $sql="SELECT * FROM paper_types WHERE id=$value";
        $dbPaper=dbselectsingle($sql);
        $paper=$dbPaper['data'];
        $weight=$paper['paper_weight'];
        $brightness=$paper['paper_brightness'];
        $sql="UPDATE rolls SET paper_weight='$weight', paper_brightness='$brightness' WHERE id=$rollid";
        $dbUpdate=dbexecutequery($sql);
        $value=$papertypes[$value];
        break;
        
        case "rollwidth":
        $field='roll_width';
        $value=$sizes[$value];
        break;
        
        case "rollweight":
        $field='roll_weight';
        break;
        
        case "rollmanifest":
        $field='manifest_number';
        break;
        
        case "rolldate":
        $field='receive_datetime';
        $value=date("Y-m-d",strtotime($value));
        break;
        
        case "rollbatch":
        $field='batch_date';
        $value=date("Y-m-d",strtotime($value));
        break;
        
        case "rollstatus":
        $field='status';
        break;
    }
    
    $sql="UPDATE rolls SET $field='$value' WHERE id=$rollid";
    $dbUpdate=dbexecutequery($sql);
    if($dbUpdate['error']=='')
    {
        $statuses=array(1=>"Received",9=>"Consumed",99=>"Deleted");
        if($field=='status')
        {
            //will need to also update batch dates
            if($value=='9')
            {
                $sql="UPDATE rolls SET batch_date='".date("Y-m-d")."' WHERE id=$rollid";
                $dbUpdate=dbexecutequery($sql);
            } elseif($value=='1')
            {
                $sql="UPDATE rolls SET batch_date=NULL WHERE id=$rollid";
                $dbUpdate=dbexecutequery($sql);
            }
            print "updated|";
            print $statuses[$value];
        } else {
            print "success|";
            print $value;
        }
        
    } else {
        print "error!";
    }
}

dbclose();
?>
