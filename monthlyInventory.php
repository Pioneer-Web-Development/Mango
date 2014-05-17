<?php
  
include("includes/mainmenu.php") ;
 show_form();

function show_form()
{    
    global $monthsOfYear, $inventoryUnitTypes;
    //create a dropdown to select the current month and year
    
    //construct tabs
    //need distinct years from monthly_inventory file
    $sql="SELECT DISTINCT (years) FROM monthly_inventory ORDER BY year ASC";
    $dbYears=dbselectmulti($sql);
    if($dbYears['numrows']>0)
    {
        foreach($dbYears['data'] as $year)
        {
            $years[$year]=$year;
        }
    } else {
        $years[date("Y")]=date("Y");
    }
   
    if($_POST)
    {
        $currentMonth=$_POST['month'];
        $currentYear=$_POST['year'];
    } else {
        $currentMonth=date("m");
        $currentYear=date("Y");
    }
    print "<form method=post>\n";
    print "<div style='float:left;'>Select month to update: ";
    print input_select('month',$monthsOfYear[$currentMonth],$monthsOfYear);
    print "</div>";
    print "<div style='float:left;margin-left:20px;'>Select year to date: ";
    print input_select('year',$years[$currentYear],$years);
    print "</div>";
    print "<div style='float:left;margin-left:20px;'>";
    print "<input type='submit' name='submit' value='Set Month/Year' style='font-size:12px !important;padding:2px !important'/>";
    print "</div>";
    print "<div class='clear'></div>\n";
    print "</form>\n";
   
    //create tabs
    print "<div id='yearTabs'>\n";
        print "<ul>\n";
        $activeTab=0;
        $i=0;
        foreach($years as $key=>$value)
        {
            if($value==$currentYear){$activeTab=$i;}
            print "<li><a href='#tab_$key'>$value</a></li>\n";
            $i++;
        }
        print "</ul>\n";
        
        foreach($years as $key=>$year)
        {
            //now...we're going to make a big grid, months, left to right
            //inventory items top to bottom, there will be a "cell" for each month/item containing: 
            //received field, remaining field, and calculated ending count field
            //we'll do a nice jquery db update as we tab through
            //get items
            $sql="SELECT * FROM monthly_inventory_items ORDER BY name";
            $dbNames=dbselectmulti($sql);
            if($dbNames['numrows']>0)
            {
                print "<div style='width:100%;padding-bottom:3px;margin-bottom:3px;border-bottom:1px solid black;'>\n";
                    print "<div style='float:left;width:100px;text-align:right;font-size:12px;margin-right:10px;font-weight:bold;'>Inventory Items</div>\n";
                    foreach($monthsOfYear as $key=>$month)
                    {
                        if($key==$currentMonth)
                        {
                            print "<div style='float:left;width:100px;'>\n";
                        } else {
                            print "<div style='float:left;width:80px;'>\n";
                        }
                        print $month;
                        print "</div>\n";
                    }
                    print "<div class='clear'></div>\n";
                print "</div>\n";        
                foreach($dbNames['data'] as $item)
                {
                    $first=false;
                    $continuing=false;
                    print "<div style='width:100%;padding-bottom:6px;margin-bottom:6px;border-bottom:1px solid black;'>\n";
                    //display the item
                        print "<div style='float:left;width:100px;text-align:right;margin-right:10px;font-weight:bold;'>\n";
                            print stripslashes($item['name'])."<br>".$inventoryUnitTypes[$item['unit_type']];
                        print "</div>\n";
                        //now loop through the list of months and create the block for each one.
                        //on month==currentMonth we will create the editable one
                        foreach($monthsOfYear as $key=>$month)
                        {
                            //lets see if there is an inventory record
                            $sql="SELECT * FROM monthly_inventory WHERE item_id=$item[id] AND month=$key AND year=$year";
                            $dbInvValues=dbselectsingle($sql);
                            if($dbInvValues['numrows']>0)
                            {
                                if($first==true)
                                {
                                    $continuing=true;
                                } else {
                                    $first=true;
                                }
                                $values=$dbInvValues['data'];
                                $remaining=$values['remaining'];
                                $received=$values['received'];
                                $starting=$values['start'];
                                $consumed=$values['consumed'];
                                //need to get last month ending value
                                /*
                                if($key==1){
                                    //need to go back a year and look at month==12
                                    $sql="SELECT remaining FROM monthly_inventory WHERE item_id=$item[id] AND month=12 AND year=".($year-1);
                                    $dbEnding=dbselectsingle($sql);
                                    if($dbEnding['numrows']>0)
                                    {
                                        $lastEnding=$dbEnding['data']['remaining'];
                                    } else {
                                        //set to starting value
                                        $lastEnding=$item['starting_value'];
                                    }
                                } else {
                                    $sql="SELECT remaining FROM monthly_inventory WHERE item_id=$item[id] AND month=".($key-1)." AND year=$year";
                                    $dbEnding=dbselectsingle($sql);
                                    if($dbEnding['numrows']>0)
                                    {
                                        $lastEnding=$dbEnding['data']['remaining'];
                                    } else {
                                        //set to starting value
                                        $lastEnding=$item['starting_value'];
                                    }
                                }
                                */
                            } else {
                                $remaining=0;
                                $received=0;
                            }
                            if(!$first)
                            {
                                //no older records for this date, need to set last ending to the item starting value
                                if($key==$currentMonth)
                                {
                                    $starting=$item['starting_value'];
                                } else {
                                    $starting=0;     
                                }
                                   
                            }
                            
                            $consumed=($starting+$received)-$remaining;
                            if($key==$currentMonth)
                            {
                                print "<div style='float:left;width:100px;font-size:10px'>\n";
                                print "Starting:<br><span style='width:50px;color:red'>$starting</span><br>";
                                print "Received:<br><input type='text' id='$item[id]_".$year."_".$key."_receive' onkeypress='return isNumberKey(event);' value='$received' style='width:50px;color:green;' onBlur='updateVals(this.id);' /><br>";
                                print "Ending:<br><input type='text' id='$item[id]_".$year."_".$key."_end' onkeypress='return isNumberKey(event);' value='$remaining' style='width:50px;color:red;' onBlur='updateVals(this.id);' /><br>";
                                print "Consumed:<br><span id='$item[id]_".$year."_".$key."_consumed' style='width:50px;color:red'>$consumed</span>";
                            } else {
                                print "<div style='float:left;width:80px;font-size:10px;'>\n";
                                print "Starting:<br><span style='width:50px;color:red'>$starting</span><br>";
                                print "Received:<br><span style='width:50px;color:red'>$received</span><br>";
                                print "Ending:<br><span style='width:50px;color:red'>$remaining</span><br>";
                                print "Consumed:<br><span id='$item[id]_".$year."_".$key."_consumed' style='width:50px;color:red'>$consumed</span>";
                            }
                            print "</div>\n";
                        }
                        print "<div class='clear'></div>\n";
                    print "</div>\n";        
                }
                ?>
                <script type='text/javascript'>
                function updateVals(id)
                {
                    var myValue=$('#'+id).val();
                    if(myValue!='')
                    {
                        $.ajax({
                          url: "includes/ajax_handlers/monthlyInventory.php",
                          type: "POST",
                          data: ({id:id,value:myValue}),
                          dataType: "json",
                          success: function(response){
                              //alert(response.status+' f:'+response.field+' c:'+response.consumed+' le'+response.lastEnding);
                              if(response.status=='success')
                              {
                                var updateID=response.field;
                                var consume=response.consumed;
                                $('#'+updateID).html(consume);
                                if(response.reload){
                                    var a = this; 
                                   var $dialog = $('<div id="jConfirm"></div>')
                                    .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Please refresh to see all updated months.</p>')
                                    .dialog({
                                        autoOpen: false,
                                        title: 'Please refresh',
                                        modal: true,
                                        buttons: {
                                            "Ok": function() {
                                                $( this ).dialog( "close" );
                                                return false;
                                            }
                                        }
                                    });
                                    $dialog.dialog("open");
                                }    
                              } else {
                                  alert(response.error);
                              }
                          }
                        
                        })
                    }
                }
                </script>
                <?php
                    
            } else {
                print "There are no inventory items defined yet. Please do so before proceeding.";
            }
            
        }
    print "</div>\n";
    ?>
    <script type="text/javascript">
        $('#yearTabs').tabs({ active: <?php echo $activeTab; ?> });
    </script>
    <?php
        
}
footer();
?>
