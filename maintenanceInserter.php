<?php
error_reporting (0);
session_start();
include ("includes/functions_db.php");
include ("includes/config.php");
include ("includes/functions_common.php");
include ("includes/functions_formtools.php");

?>
<!DOCTYPE html>
<html>
<head>
<title>Inserter Maintenance</title>
<?php
$scriptname=end(explode("/",$_SERVER['SCRIPT_NAME']));
//lets load the javascript files
$sql="SELECT * FROM core_system_files WHERE file_type='script' AND head_load=1 ORDER BY load_order ASC";
$dbScripts=dbselectmulti($sql);
if($dbScripts['numrows']>0)
{
    foreach($dbScripts['data'] as $script)
    {
        if($script['specific_page']=='' || $script['specific_page']==$scriptname)
        {
            print "<script type='text/javascript' src='includes/jscripts/$script[file_name]'></script>\n";     
        } else {
            print "<!-- when loading $script[file_name] looked for $script[specific_page] compared to $scriptname -->\n";
        }       
    }
}
//lets load the stylesheets
$sql="SELECT * FROM core_system_files WHERE file_type='style' AND head_load=1 ORDER BY load_order ASC";
$dbStyles=dbselectmulti($sql);
if($dbStyles['numrows']>0)
{
    foreach($dbStyles['data'] as $style)
    {
       if($style['specific_page']=='' || $style['specific_page']==$scriptname)
       {
           print "<link rel='stylesheet' type='text/css' href='styles/$style[file_name]' />\n";
                
       }       
    }
}
?>
<script>
function chgInserter(id)
{
    window.location='?inserterid='+id;
}
</script>
</head>
<body>
<div style='width:940px;height:60px;border-bottom:8px solid #AC1D23;padding-bottom:0px;margin-bottom: 0px;'>
    <div style='float:left;'>
        <img src='artwork/mango.png' border=0 width="120">
    </div>
    <div style='margin-left:10px;float:left;font-family:Trebuchet MS;font-size:48px;font-weight:bold;color:#AC1D23;' >
        Inserter Maintenance
    </div>
    <div style='margin-left:10px;float:right;'>
    <?php
    if (isset($_GET['type']))
    {
        ?>
    <input type='button' onClick='document.location.href="?main";' value='Return to start'>
    <?php
    } else {
        ?>
        <input type='button' onClick='document.location.href="?type=ticket&ticketonly=true&equipmentid=0&componentid=0";' value='Submit General Ticket'>
    
        <?php
            
    } ?>
    <input type='button' onClick='self.close();' value='Close'>
    </div>
</div>
<div class='clear'></div>

<?php
  
  
  if ($_POST)
  {
      if ($_POST['submit']=='Submit Trouble Ticket')
      {
          save_ticket();
      }
  } else {
      init_ticket();
  }

function init_ticket($saved=false)
{
      global $inserterid;
      if ($_GET['equipmentid'] || $_GET['ticketonly'])
      {
          show_maintenance($_GET['type'],$_GET['equipmentid'],$_GET['componentid'],$saved);
      } else {
          print "<div style='margin-left:auto;margin-right:auto;'>\n";
          //we should be passed at a minimum the press id
          if (isset($_GET['inserterid']))
          {
              $inserterid=$_GET['inserterid'];
          } else {
              $inserterid=$GLOBALS['defaultInserter'];
          }
          $sql="SELECT id,inserter_name FROM inserters";
          $dbInserters=dbselectmulti($sql);
          $inserters=array();
          if($dbInserters['numrows']>1)
          {
              foreach($dbInserters['data'] as $i)
              {
                $inserters[$i['id']]=$i['inserter_name'];    
              }
              print "Please select an inserter: ".input_select('inserterid',$inserters[$inserterid],$inserters,'',"chgInserter(this.value)");
          }
          
          build_inserter($inserterid);
          print "</div>\n";
          //now show other 'mailroom' department equipment
          $sql="SELECT * FROM equipment WHERE equipment_department IN (".$GLOBALS['productionDepartmentID'].','.$GLOBALS['mailroomDepartmentID'].") ORDER BY equipment_name";
          print "<div class='clear'></div>\n";
          $dbEquipment=dbselectmulti($sql);
          if($dbEquipment['numrows']>0)
          {
            $equipment[0]='Select other equipment';
            foreach($dbEquipment['data'] as $eq)
            {
                $equipment[$eq['id']]=stripslashes($eq['equipment_name']);
            }
            print "<div class='clear'></div>\n";
            print "<hr>";
            print "<span style='font-weight:bold;font-size:16px;float:left;'>Work on other mailroom equipment: </span>";
            print "<span style='float:left;margin-left:10px;font-size:24px !important;'>";
            print input_select('equipmentid',$equipment[0],$equipment,false,"if(this.value!=0){\$('#comp').css('display','block');}else{\$('#comp').css('display','none');}");
            print "</span>";
            print "<span id='comp' style='float:left;margin-left:10px;display:none;font-size:24px;'>";
            print input_select('componentid',$components[0],$components);
            print "</span>\n";
            print "<span style='float:left;margin-left:10px'><input type='button' onClick='pressMaintenanceGeneric();' value='Proceed'></span>";
            print "<span class='clear'></span>";
            print '
            <script type="text/javascript">
            $("#equipmentid").selectChain({
                target: $("#componentid"),
                type: "post",
                url: "includes/ajax_handlers/maintenanceComponentHandler.php",
                data: { ajax: true }
            });
            </script>
            ';
            print "</div>\n";  
          }
          
        
      }
}
  
function save_ticket()
{
     global $siteID; 
     $sql="SELECT * FROM helpdesk_statuses WHERE site_id=$siteID ORDER BY status_order ASC LIMIT 1";
     $dbStatus=dbselectsingle($sql);
     $statusid=$dbStatus['data']['id'];
     
     $componentid=$_POST['componentid'];
     $equipmentid=$_POST['equipmentid'];
     $type=$_POST['type'];
     $location=$_POST['location'];
     $submittedby=$_POST['submittedby'];
     $priorityid=$_POST['priority'];
     $topic=$_POST['topic'];
     $problem=addslashes($_POST['problem']);
     $attempted=addslashes($_POST['attempted']);
     $full=$problem."<br />".$attempted;
     if($_POST['alertme']){$alertme=1;}else{$alertme=0;}
     $submitdatetime=date("Y-m-d H:i:s");
     $sql="INSERT INTO maintenance_tickets (type_id, status_id, priority_id, submitted_by, submitted_datetime, problem, attempt, 
     wants_email, object_type, object_id, object_unit) VALUES ('$topic', '$statusid', '$priorityid', '$submittedby', '$submitdatetime', 
     '$problem', '$attempted', '$alertme', '$type', '$equipmentid', '$componentid')";
     $dbInsert=dbinsertquery($sql);
     if ($dbInsert['error']=='')
     {
        //see if this ticket is highest priority, if so, send an email to the director
         //first, lets pull in the helpdesk priorities
          $sql="SELECT * FROM helpdesk_priorities ORDER BY priority_order DESC LIMIT 1";
          $dbPriorities=dbselectsingle($sql);
          $highest=$dbPriorities['data']['id'];
          if($priorityid==$highest)
          {
              //highest priority!!!! need to send the email
              $sql="SELECT A.id, B.group_email FROM helpdesk_types A, user_groups B WHERE A.group_responsible=B.id";
              $dbGroups=dbselectmulti($sql);
              $owners[0]='tech@idahopress.com';
              if ($dbGroups['numrows']>0)
              {
                  foreach($dbGroups['data'] as $group)
                  {
                      $owners[$group['id']]=$group['group_email'];
                  }
              }
              $ticket['submitted_datetime']=$submitdatetime;
              $ticket['attempt']=$attempted;
              $ticket['problem']=$problem;
              
              send_ticket_message($owners[$ticket['type_id']],$ticket,$dbPriorities['data']['priority_name'],'helpdesk',true);
                    
          }
          
          init_ticket(true);
     } else {
         print $dbInsert['error'];
     }             
}

  
function show_maintenance($equipmenttype,$equipmentid=0,$componentid=0,$submitted=false)
{
    global $productionStaff,$generalProductionTicketType,$siteID, $defaultInserter;
    $ticketonly=$_GET['ticketonly'];
    
    $helpStatuses=array();
    $sql="SELECT * FROM helpdesk_statuses ORDER BY status_order";
    $dbStatuses=dbselectmulti($sql);
    if ($dbStatuses['numrows']>0)
    {
      foreach($dbStatuses['data'] as $status)
      {
          $helpStatuses[$status['id']]=$status['status_name'];
      }
    } else {
      $helpStatuses[0]="None set!";
    }

    $helpPriorities=array();
    $sql="SELECT * FROM helpdesk_priorities ORDER BY priority_order";
    $dbPriorities=dbselectmulti($sql);
    if ($dbPriorities['numrows']>0)
    {
      foreach($dbPriorities['data'] as $priority)
      {
          $helpPriorities[$priority['id']]=$priority['priority_name'];
      }
    } else {
      $helpPriorities[0]=="None set!";
    }

    $helpTypes=array();
    $sql="SELECT * FROM helpdesk_types WHERE production_specific=1 ORDER BY type_name";
    $dbTypes=dbselectmulti($sql);
    if ($dbTypes['numrows']>0)
    {
      foreach($dbTypes['data'] as $type)
      {
          $helpTypes[$type['id']]=$type['type_name'];
      }
    } else {
      $helpTypes[0]=="None set!";
    }   
    
   
   print "<div id='unitdisplay' style='float:left;height:580px;background-color:#FEFE78;width:120px;'>\n";
    if($ticketonly)
   {
      print "<p style='font-weight:bold;'>General Trouble Ticket</p>"; 
   } else {
       if ($equipmenttype=='generic')
       {
            
            //see if there is also a specified component
            if($componentid!=0){
               $sql="SELECT * FROM equipment_component WHERE id=$componentid";
               $dbComponent=dbselectsingle($sql);
               if($dbComponent['data']['component_image']!='')
               {
                   $image=$dbComponent['data']['component_image']; 
                   print "<img src='artwork/equipmentImages/$image' width=80 border=0><br>\n";     
               }
               $componentname=$dbComponent['data']['component_name'];
           }
           //lets show the name of the component and equipment
           $sql="SELECT * FROM equipment WHERE id=$equipmentid AND equipment_type='generic'";
           $dbE=dbselectsingle($sql);
           $equipmentname=$dbE['data']['equipment_name'];
           print "<p style='font-weight:bold;'>$equipmentname<br>$componentname</p>";
       } else if ($equipmenttype=='inserter')
       {
           print "<div style='width:80px;margin-left:auto;margin-right:auto;text-align:center;vertical-align:center;font-weight:bold;'>\n";
           $sql="SELECT * FROM inserters_hoppers WHERE id=$componentid";
           $dbHopper=dbselectsingle($sql);
           $hoppername=$dbHopper['data']['hopper_number'];
           print "Station<br>$hoppername";
           print "</div>\n";                               
       }
   }
   
   print "</div>\n";
   
   
   print "<div id='partsreplace' style='float:left;margin-left:10px;width:830px;height:600px;overflow:hidden;'>\n";
       print "<div id='tabs'>\n";
       print "<ul id='maintenance'>\n";
        print "<li><a href='#report'>Report A Problem</a></li>\n";   
        if(!$ticketonly)
        {
            print "<li><a href='#perform'>Perform Maintenance</a></li>\n";   
        }
        print "<li><a href='#search'>Look for existing solutions!</a></li>\n";   
        if(!$ticketonly)
        {
            print "<li><a href='#history'>View history of this unit</a></li>\n";
        }   
       print "</ul>\n";
      
      print "<div id='report' style='height:510px;'>\n";
       if ($submitted)
       {
           print "<p style='margin-top:10px;margin-bottom:10px;color:#AC1D23;text-align:center;font-weight:bold;font-size:14px;'>Your trouble ticket has been submitted.</p>\n";
       }
       print "<form method=post>\n";
       print "<p style='text-align:center;color:#AC1D23;font-size:16px;'>If you replaced any parts, please indicate that in the 'Perform Maintenance' tab.</p>\n";
       make_select('topic',$helpTypes[$generalProductionTicketType],$helpTypes,'Topic','Select general category for this issue to help categorize it.');
       make_select('priority',$helpPriorities[0],$helpPriorities,'Priority');
       make_textarea('problem','','What<br />is the problem?','Tell us what happened as clearly as possible.',83,6,false);
       make_textarea('attempted','','What did you try?','Tell us how you tried to fix it or your workaround for the problem.',83,6,false);
       print "<div class='label'>Reported By</div><div class='input'>\n";
       print input_select('submittedby',$productionStaff[$_SESSION['cmsuser']['userid']],$productionStaff)." ";
       print input_checkbox('alertme',0)." Send me an email with the solution when the problem is fixed.";
       print "</div><div class='clear'></div>\n";
       make_submit('submit','Submit Trouble Ticket');
       if($ticketonly)
       {
           $equipmentid=0;
           $componentid=0;
           $type='general';
           $unit='';
           $subid=0;
       }
       make_hidden('equipmentid',$equipmentid);
       make_hidden('componentid',$componentid);
       make_hidden('type',$equipmenttype);
       make_hidden('location',"Unit:$unitid|Sub:$subid");
           
        print "</form>\n";
       print "</div>\n";
       
       if(!$ticketonly)
       {
       print "<div id='perform' style='height:510px;'>\n";
       
       print "<div id='col1' style='float:left;width:380px;height:500px;overflow-y:scroll;'>\n";
       print "<p><b>Parts</b></p>\n";
       
       //get all the components that are sub as well
       $components=$componentid.",";
       $sql="SELECT DISTINCT(id) FROM equipment_component WHERE equipment_id=$equipmentid OR parent_id=$componentid";
       $dbComponents=dbselectmulti($sql);
       if($dbComponents['numrows']>0)
       {
           foreach($dbComponents['data'] as $c)
           {
               $components.=$c['id'].",";
           }
       }
       $components=substr($components,0,strlen($components)-1);
       
       $sql="SELECT A.*, B.component_id FROM equipment_part A, equipment_part_xref B WHERE A.id=B.part_id AND B.equipment_id='$equipmentid' AND B.component_id IN($components) AND B.equipment_type='$equipmenttype'";
       $dbParts=dbselectmulti($sql);
       if ($dbParts['numrows']>0)
       {
           foreach($dbParts['data'] as $part)
               {
                   print "<div id='partholder_$part[id]' style='width:330px;background-color:white;padding:4px;margin-bottom:4px;'>\n";
                       print "<div style='font-size:14px;float:left;width:300px;'>$part[part_name]</span>\n";
                           //lets see if we can find an open instance of this part
                           $sql="SELECT * FROM part_instances WHERE equipment_type='$equipmenttype' AND equipment_id='$equipmentid' AND component_id='$componentid' AND sub_component_id='$part[component_id]' AND part_id=$part[id] AND replaced=0";
                           $dbInstance=dbselectsingle($sql);
                           print "<div id='part_info_$part[id]' style='font-size:10px;'>\n";
                           if ($dbInstance['numrows']>0)
                           {
                               $instance=$dbInstance['data'];
                               $installed=date("m/d/Y", strtotime($instance['install_datetime']));
                               $curCount=$instance['cur_count'];
                               $curTime=round($instance['cur_count']/60,2);
                               print "Installed on $installed, current impressions $curCount and $curTime days.<br>";
                               
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
                               
                           } else {
                               print "Not installed on this unit. ";
                           }
                           print "</div><!--closing the info box area -->\n";
                       print "</div>\n";
                       
                       print "<div style='margin-left:5px;float:right;'>\n";
                           print "<a title='Part Replacement' href='includes/ajax_handlers/partReplacement.php?action=perform&item=$item&equipmenttype=$equipmenttype&equipmentid=$equipmentid&componentid=$componentid&subcomponentid=$part[component_id]&partid=$part[id]' class='ajaxload'><img src='artwork/icons/spanner_48.png' border=0 width=24'></a>\n";
                       print "</div>\n";
                       print "<div class='clear'></div>\n";
                   print "</div>\n";
               }
       }
       print "</div>\n";
       //column 2 - maintenance - tasks will be stacked for printing units
       print "<div id='col2' style='margin-left:10px;float:left;width:380px;height:500px;overflow-y:scroll;'>\n";
           print "<p><b>Maintenance Tasks</b></p>\n";
           $sql="SELECT A.*, B.component_id FROM equipment_pm A, equipment_pm_xref B WHERE A.id=B.pm_id AND B.equipment_id='$equipmentid' AND B.component_id IN ($components) AND B.equipment_type='$equipmenttype'";
           $dbTasks=dbselectmulti($sql);
           if ($dbTasks['numrows']>0)
           {
               foreach($dbTasks['data'] as $task)
               {
                   print "<div id='task_$task[id]' style='width:330px;background-color:white;padding:4px;margin-bottom:4px;'>\n";
                       print "<div style='font-size:14px;float:left;'>$task[pm_name]<br>\n";
                       //lets see if we can find an open instance of this part
                       $sql="SELECT * FROM pm_instances WHERE equipment_type='$equipmenttype' AND equipment_id='$equipmentid' AND component_id='$componentid' AND sub_component_id='$part[component_id]' AND pm_id=$task[id] AND replaced=0";
                       $dbInstance=dbselectsingle($sql);
                       print "<div id='pm_info_$task[id]' style='font-size:10px;'>\n";
                       if ($dbInstance['numrows']>0)
                       {
                           $instance=$dbInstance['data'];
                           $curCount=$instance['cur_count'];
                           $curTime=round($instance['cur_count']/60,2);
                           $installed=date("m/d/Y", strtotime($instance['install_datetime']));
                           $duedate=date("m/d/Y",strtotime($installed."+$curTime days"));
                           print "Last performed on $installed<br>";
                           if($task['pm_life_type']=='impressions')
                           {
                               $lifeCount=$task['pm_life_impressions'];
                               if($lifeCount<$curCount)
                               {
                                   print "<span style='color:red;'>Maintence task should have been done at $lifeCount. Please check and perform task soon.</span>";
                               } else {
                                   print "There are at least ".($lifeCount-$curCount)." cycles remaining before this task needs to be done.";
                               }
                           } else {
                               $lifeCount=$task['pm_life_days'];
                               if($lifeCount<$curTime)
                               {
                                   print "<span style='color:red;'>Maintence task should have been done at $lifeCount. Please check and perform task soon.</span>";
                               } else {
                                   print "There are at least ".($lifeCount-$curTime)." days remaining before this task needs to be done.";
                               }
                           }
                       } else {
                           print "Not yet performed on this unit.";
                       }
                       
                       print "</div><!--closing the info box area -->\n";
                       print "</div>\n";
                       
                       print "<div style='margin-left:5px;float:right;'>\n";
                       print "<a title='PM Task' href='includes/ajax_handlers/partReplacement.php?action=performpm&item=$item&equipmenttype=$equipmenttype&equipmentid=$equipmentid&componentid=$componentid]&subcomponentid=$part[component_id]&partid=$task[id]' class='ajaxload'><img src='artwork/icons/spanner_48.png' border=0 width=24'></a>\n";
                       print "</div>\n";
                       
                       print "<div class='clear'></div>\n";
                   print "</div>\n";
               }
           }
       print "</div>\n";
       print "</div><!-- closes the perform maintenance tab -->\n";
       } //closing conditional ticket only
       /* SEARCH TAB 
       *  utilizes a script called getMaintenanceHelpTopics that queries a script called findTroubleSolutions in ajax_helpers
       *  returns an html block that is rendered in the search_results div
       */
       print "<div id='search'  style='height:510px;'>\n";
       print "<b>Keywords:</b><input type='text' id='keywords' placeholder='Search terms...' style='width:200px;margin-left:20px;margin-right:10px;' />\n";
       print "<input type='button' value='Search' onclick='getMaintenanceHelpTopics(\"mailroom\");'>\n<br><br>\n";
       print "<div id='search_results' style='width:800px;height:470px;overflow-y:scroll;'></div>\n";
       print "</div><!-- closes the search tab -->\n";
       
       if(!$ticketonly)
       {
       
       print "<div id='history'  style='height:510px;'>\n";
        print "<div id='mainthistory' style='float:left;width:400px;height:500px;overflow-y:scroll;'>\n";
            print "<p style='font-weight:bold;'>Here is the history of issues for this unit</p>\n";
            $sql="SELECT * FROM maintenance_tickets WHERE object_id='$equipmentid' AND object_unit='$componentid' AND object_type='$equipmenttype'";
            $dbTickets=dbselectmulti($sql);
            if ($dbTickets['numrows']>0)
            {
                foreach($dbTickets['data'] as $ticket)
                {
                    $priority=$helpPriorities[$ticket['priority_id']];
                    $type=$helpTypes[$ticket['type_id']];
                    $id=$ticket['id'];
                    $brief=$ticket['problem'];
                    print "<p class='dashboardHeadline'><a href='maintenanceTickets.php?action=edit&id=$id' target='_parent'>Maintence Ticket # $id</a></p>\n";
                    print "<p style='font-size:12px;'>Priority: $priority<br>\n";        
                    print "Trouble type: $type<br>\n";        
                    print "$brief</p>\n";        
                }
            } else {
                print "<h3>No trouble tickets submitted for this unit yet.</h3>\n";
            }
        print "</div>\n";
            
        print "<div id='parthistory' style='float:left;margin-left:20px;width:350px;height:500px;overflow-y:scroll;'>\n";
                print "<p style='font-weight:bold;'>Here is the part replacement history for the past 6 months for this unit</p>\n";
                $dateback=date("Y-m-d",strtotime("-6 months"));
                $sql="SELECT B.part_name, A.install_datetime, A.cur_time, A.cur_count FROM part_instances A, equipment_part B WHERE A.part_id=B.id AND A.equipment_id='$equipmentid' AND A.component_id='$componentid' AND A.equipment_type='$equipmenttype' AND install_datetime>='$dateback' ORDER BY install_datetime DESC";
                $dbParts=dbselectmulti($sql);
                if ($dbParts['numrows']>0)
                {
                    foreach($dbParts['data'] as $part)
                    {
                        $partname=$part['part_name'];
                        $installdate=$part['install_datetime'];
                        $currenttime=round($part['cur_time']/60/24,2);
                        $currentcount=$part['cur_count'];
                        print "<div style='font-size:10px;margin-bottom:4px;padding-bottom:4px;border-bottom:thin solid black;'>\n";
                        print "$partname - installed on $installdate<br>\n";
                        print "<div style='float:left;width:150px;'>Run time: $currenttime days</div>\n";
                        print "<div style='float:left;width:150px;'>Run impressions: $currentcount</div><div class='clear'></div></p>\n";
                        print "</div>\n";
                    }
                }
        print "</div>\n";
      print "</div><!-- closes the history tab -->\n";
       } //closing ticket only conditional
   print "</div>\n";//ends wrapper for tabbed area
   print "<div id='dialog'></div>\n";  
        ?>
    <script type='text/javascript'>
   $(function() {
        $( '#tabs' ).tabs();
    });
    var ajaxdialog=$("#dialog").dialog({ 
          title: 'Perform Maintenance',        
          autoOpen: false, 
          height: '400', 
          width: 600,
          modal:true,
          buttons: [
              {
                text: 'Cancel',
                click: function() { 
                    $(this).dialog('close');
                }
              },
              {
                text: 'Perform Maintenance',
                click: function() { 
                    $('#ajaxRepairForm').submit();
                    $(this).dialog('close');  
                }
              }
          ]
        })
        $('a.ajaxload').click(function()
        {
            var url = this.href;
            ajaxdialog.load(url).dialog('open');
            return false;
        })
        // post-submit callback 
        function showResponse(responseText, statusText, xhr, $form)  { 
            // for normal html responses, the first argument to the success callback 
            // is the XMLHttpRequest object's responseText property 
         
            // if the ajaxForm method was passed an Options Object with the dataType 
            // property set to 'xml' then the first argument to the success callback 
            // is the XMLHttpRequest object's responseXML property 
         
            // if the ajaxForm method was passed an Options Object with the dataType 
            // property set to 'json' then the first argument to the success callback 
            // is the json data object returned by the server 
            var response=responseText.split("|");
            if ($.trim(response[0])=='success')
            {
                $('#'+$.trim(response[2])).html($.trim(response[1]));
            } else {
                var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+response[1]+'</p>')
                .dialog({
                    autoOpen: true,
                    modal: true,
                    title: 'An error occurred:',
                    buttons:[
                    {
                        text: 'Close',
                        click: function() { 
                            $(this).dialog('destroy');
                        }
                    }]
                })
            }
        } 
    </script>
     <?php 
      print "</div>\n"; 
         
}
      
function build_inserter($inserterid)
{
      $sql="SELECT * FROM inserters WHERE id=$inserterid";
      $dbInserter=dbselectsingle($sql);
      $inserter=$dbInserter['data'];
      
      if($inserter['inserter_type']=='oval')
      {
          //this means we have two rows, with half in reverse order
          print "<div id='inserterholder_top' style='margin-bottom:20px;'>\n";
          $sql="SELECT * FROM inserters_hoppers WHERE inserter_id=$inserterid AND hopper_number>$inserter[inserter_turn] ORDER BY hopper_number DESC";
          $dbPockets=dbselectmulti($sql);
          if ($dbPockets['numrows']>0)
          {
              foreach($dbPockets['data'] as $pocket)
              {
                 print "<div id='station_$pocket[id]' class='station' style='float:left;width:80px;height:80px;text-align:center;font-weight:bold;font-size:18px;border:thin solid black;margin-right:2px;'>$pocket[hopper_number]</div>";
                 ?>
                 <script>
                 $(document).ready(function(){
                     $('#station_<?php echo $pocket['id']?>').click(function(){
                        window.location="maintenanceInserter.php?type=inserter&equipmentid=<?php echo $inserterid ?>&componentid=<?php echo $pocket['id']; ?>";    
                     })
                 })
                 </script>
                 <?php
              }
          }
          print "<div class='clear'></div>\n";
          print "</div>\n";
          print "<div id='inserterholder_bottom' style='margin-bottom:20px;'>\n";
          $sql="SELECT * FROM inserters_hoppers WHERE inserter_id=$inserterid AND hopper_number<=$inserter[inserter_turn] ORDER BY hopper_number";
          $dbPockets=dbselectmulti($sql);
          if ($dbPockets['numrows']>0)
          {
              foreach($dbPockets['data'] as $pocket)
              {
              print "<div id='station_$pocket[id]' class='station' style='float:left;width:80px;height:80px;text-align:center;font-weight:bold;font-size:18px;border:thin solid black;margin-right:2px;'>$pocket[hopper_number]</div>";
              ?>
                 <script>
                 $(document).ready(function(){
                     $('#station_<?php echo $pocket['id']?>').click(function(){
                         window.location="maintenanceInserter.php?type=inserter&equipment=<?php echo $inserterid ?>&componentid=<?php echo $pocket['id']; ?>";    
                    })
                 })
                 </script>
                 <?php
              }
          }
          print "<div class='clear'></div>\n";
          print "</div>\n";
      } else {
          //now we build it!
          $sql="SELECT * FROM inserters_hoppers WHERE inserter_id=$inserterid ORDER BY hopper_number";
          
          $dbPockets=dbselectmulti($sql);
          if ($dbPockets['numrows']>0)
          {
              
              print "<div id='inserterholder' style='margin-bottom:20px;'>\n";
              foreach($dbPockets['data'] as $pocket)
              {
                 print "<div id='station_$pocket[id]' class='station' style='float:left;width:80px;height:80px;text-align:center;font-weight:bold;font-size:18px;border:thin solid black;margin-right:2px;'>$pocket[hopper_number]</div>";
                 ?>
                 <script>
                 $(document).ready(function(){
                     $('#station_<?php echo $pocket['id']?>').click(function(){
                         window.location="maintenanceInserter.php?type=inserter&equipmentid=<?php echo $inserterid ?>&componentid=<?php echo $pocket['id']; ?>";    
                     })
                 })
                 </script>
                 <?php
              }
              print "</div>\n";
              
              
          }
      }
     ?>
      <script>
      $('.station').mouseover(function(){
          $(this).css('background-color','yellow');
      })
      $('.station').mouseout(function(){
          $(this).css('background-color','white');
      })
      
      </script>
      <?php
 }
 
footer(); 
?>
