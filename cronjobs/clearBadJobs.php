<?php
function clearBadJobs()
{
    /******************************************************
    * This is a method to delete bad jobs created by drag
    * schedule errors
    * @var mixed
    */
    $sql="DELETE FROM jobs WHERE pub_id=0 AND run_id=0 AND insert_source='dragschedule'";
    $dbDelete=dbexecutequery($sql); 
}
?>
