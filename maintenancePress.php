<?php
error_reporting (E_All);
session_start();
include ("includes/functions_db.php");
include ("includes/config.php");
include ("includes/functions_common.php");
include ("includes/functions_formtools.php");

?>
<!DOCTYPE HTML>
<html>
<head>
<title>Press Maintenance</title>
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
</head>
<body>
<div style='width:940px;height:60px;border-bottom:8px solid #AC1D23;padding-bottom:0px;margin-bottom: 0px;'>
    <div style='float:left;'>
        <img src='artwork/mango.png' border=0 width="120">
    </div>
    <div style='margin-left:10px;float:left;font-family:Trebuchet MS;font-size:48px;font-weight:bold;color:#AC1D23;' >
        Press Maintenance
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
        <input type='button' onClick='document.location.href="?ticketonly=true";' value='Submit General Ticket'>
    
        <?php
            
    }
    ?>
    <input type='button' onClick='self.close();' value='Close'>
    </div>
</div>
<div class='clear'></div>

<?php
  
  
  //we should be passed at a minimum the press id
  if (isset($_GET['pressid']))
  {
      $pressid=$_GET['pressid'];
  } else {
      global $pressid;
  }
  
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
    global $pressid;
    if($saved)
    {
        print "<div>Trouble ticket has been submitted</div>";
    }
    if ($_GET['type'] || $_GET['ticketonly'])
      {
          show_maintenance($_GET['type'],$_GET['pressid'],$_GET['equipmentid'],$_GET['componentid'],$_GET['sub'],$_GET['unit'],$saved);
      } else {
          print "<div style='margin-left:auto;margin-right:auto;'>\n";
          build_press_lookup($pressid);
          print "</div>\n";
          //now show other 'press' department equipment
          
          print "<div class='clear'></div>\n";
          $sql="SELECT * FROM equipment WHERE specialist_type='generic' AND equipment_department IN (".$GLOBALS['productionDepartmentID'].','.$GLOBALS['pressDepartmentID'].") ORDER BY equipment_name";
         
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
            print "<span style='font-weight:bold;font-size:16px;float:left;'>Work on other production equipment: </span>";
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
     
     $equipmentid=$_POST['equipmentid'];
     $componentid=$_POST['componentid'];
     $unit=$_POST['unit'];
     $type=$_POST['type'];
     $sub=$_POST['sub'];
     $submittedby=$_POST['submittedby'];
     $priorityid=$_POST['priority'];
     $topic=$_POST['topic'];
     $problem=addslashes($_POST['problem']);
     $attempted=addslashes($_POST['attempted']);
     $full=$problem."<br />".$attempted;
     if($_POST['alertme']){$alertme=1;}else{$alertme=0;}
     $submitdatetime=date("Y-m-d H:i:s");
     
     //construct elaborate unit containing tower/component id, sub and unit
     $unit="c-$componentid|s-$sub|u-$unit";
     
     $sql="INSERT INTO maintenance_tickets (type_id, status_id, priority_id, submitted_by, submitted_datetime, 
     problem, attempt, wants_email, object_type, object_id, object_unit) VALUES ('$topic', '$statusid', 
     '$priorityid', '$submittedby', '$submitdatetime', '$problem', '$attempted', '$alertme', '$type', 
     '$equipmentid', '$unit')";
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

  
function show_maintenance($pressunittype,$pressid,$equipmentid,$componentid,$sub,$unit,$submitted=false)
{
    global $cyan, $magenta, $yellow, $black,$productionStaff,$generalProductionTicketType,$siteID, $pressid;
    $ticketonly=$_GET['ticketonly'];
     
    $helpStatuses=array();
    $sql="SELECT * FROM helpdesk_statuses WHERE site_id=$siteID ORDER BY status_order";
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
    $sql="SELECT * FROM helpdesk_priorities WHERE site_id=$siteID ORDER BY priority_order";
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
    $sql="SELECT * FROM helpdesk_types WHERE site_id=$siteID AND production_specific=1 ORDER BY type_name";
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
   if ($pressunittype!='generic')
   {
       $sql="SELECT * FROM press_towers WHERE id=$componentid";
       $dbTower=dbselectsingle($sql);
       $tower=$dbTower['data'];
       $ttype=$tower['tower_type'];
       print "<p style='font-size:14px;color:#AC1D23;'>$tower[tower_name]</p>\n";
       $equipmentid=$pressid;
       switch ($ttype)
       {
             case 'printing':
                switch ($unit)
                {
                    case 'K':
                    $c=$black;
                    break;
                    case 'C':
                    $c=$cyan;
                    break;
                    case 'M':
                    $c=$magenta;
                    break;
                    case 'Y':
                    $c=$yellow;
                    break;
                }
                print "<div style='margin-left:10px;width:100px;background-color:$c;'><img src='artwork/equipmentImages/pressUnit.jpg' style='margin-left:10px;' border=0 width=80><br /></div>\n";
                print "<div style='margin-left:10px;width:100px;text-align:center;font-weight:bold;'>$unit Unit</div>\n";
             break;
             
             case 'ribbon':
                print "<img src='artwork/equipmentImages/ribbonDeck.jpg' width=80 border=0>";
             break;
             
             case 'folder':
                print "Main: <br />\n";
                print "<img src='artwork/equipmentImages/folderBase.jpg' border=0 height=80>\n";
             break;
            
             case 'former':
                 print "Former: ".str_replace("former_","",$piece)."<br />\n";
                 print "<img src='artwork/equipmentImages/folderFormer.jpg' border=0; height=80>\n";
             break;
        }
       
   } else {
       //lets see if it's a 'specialist type'
       $sql="SELECT * FROM equipment WHERE id=$equipmentid";
       $dbEquipment=dbselectsingle($sql);
       $equipment=$dbEquipment['data'];
       print "<p style='font-size:14px;color:#AC1D23;'>$equipment[equipment_name]</p>\n";
       
       if($equipment['specialist_type']!='generic')
       {
           switch($equipment['specialist_type'])
           {
               case "stacker":
               print "<img src='artwork/equipmentImages/stacker.png' width=80 border=0>";
               break;
               
               case "strapper":
               print "<img src='/artwork/equipmentImages/strapper.png' border=0 width=80>";
               break;
               
               case "splicer":
               print "<img src='artwork/equipmentImages/singlesplicer.png' width=80 border=0>";
               break;
               
               case "counterveyor":
               print "<img src='/artwork/equipmentImages/counterveyor.png' border=0 width=80>";
               break;
           }
       } else {
           //have a generic piece of production equipment. Let's see if there is a related graphic
           $sql="SELECT * FROM equipment_component WHERE id=$componentid";
           $dbComponent=dbselectsingle($sql);
           if($dbComponent['data']['component_image']!='')
           {
               $image=$dbComponent['data']['component_image']; 
               print "<img src='artwork/equipmentImages/$image' width=80 border=0><br>\n";     
           }
           $cequipmentid=$dbComponent['data']['equipment_id'];
           //lets show the name of the component and equipment
           $sql="SELECT * FROM equipment WHERE id=$cequipmentid AND equipment_type='generic'";
           $dbE=dbselectsingle($sql);
           $equipmentname=$dbE['data']['equipment_name'];
           $componentname=$dbComponent['data']['component_name'];
           print "<p style='font-weight:bold;'>$componentname</p>";            
       }
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
      
      print "<div id='report' style='height:580px;'>\n";
       print "<form method=post>\n";
       if ($submitted)
       {
           print "<p style='margin-top:70px;margin-left:80px;color:#AC1D23;'>Your trouble ticket has been submitted.</p>\n";
            
       }
       
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
       make_hidden('unit',$unit);
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
       make_hidden('type',$ttype);
       make_hidden('sub',$sub);
   
       print "</form>\n";
       print "</div>\n";
       
       if(!$ticketonly)
       {
       
       print "<div id='perform' style='height:580px'>\n";
       //we are going to have 2 columns, 3 if this is a press unit. Columns 1 will be part and column
       //3 will be maintenance tasks
       //if it is a printing tower, then we'll have a 10 side and 13 side of the unit
       if($pressunittype!='generic')
       {
           //means we need to dig a little deeper
           //we will need to convert unit type into a componentid
           $sql="SELECT * FROM equipment_component WHERE component_type='$pressunittype'";
           $dbComponent=dbselectsingle($sql);
           if($dbComponent['numrows']>0)
           {
               $genericcomponentid=$dbComponent['data']['id'];
               $sql="SELECT A.*, B.component_id FROM equipment_part A, equipment_part_xref B 
               WHERE A.id=B.part_id AND B.equipment_id='$equipmentid' 
               AND B.component_id='$genericcomponentid' AND B.equipment_type='press'
               ORDER BY part_name";
           } else {
               $sql="";
           }
           $dbPressParts=dbselectmulti($sql);
           $t="AND tower_side=10";
           $colhead='10-side parts:';
           $width="250px";
           $swidth="190px";
           $infowidth='224px';
           $location="Side:10|Piece:$unit";
           $item='_10';
       } else {
           if($componentid=='')
           {
              $sql="SELECT DISTINCT(id) FROM equipment_component WHERE equipment_id=$equipmentid";
           } else {
              $components=$componentid.",";
              $sql="SELECT DISTINCT(id) FROM equipment_component WHERE equipment_id=$equipmentid OR parent_id=$componentid";
           }
           $dbComponents=dbselectmulti($sql);
           if($dbComponents['numrows']>0)
           {
               foreach($dbComponents['data'] as $c)
               {
                   $components.=$c['id'].",";
               }
           }
           $components=substr($components,0,strlen($components)-1);
           if($components=='')
           {
               $sql="SELECT A.*, B.component_id FROM equipment_part A, equipment_part_xref B 
               WHERE A.id=B.part_id AND B.equipment_id='$equipmentid' AND B.equipment_type='$pressunittype'
               ORDER BY part_name";
           } else {
               $sql="SELECT A.*, B.component_id FROM equipment_part A, equipment_part_xref B 
               WHERE A.id=B.part_id AND B.equipment_id='$equipmentid' 
               AND B.component_id IN($components) AND B.equipment_type='$pressunittype'
               ORDER BY part_name";
           }
           $dbPressParts=dbselectmulti($sql);
           $location='';
           $colhead='Parts Maintenance';
           $item='';
           $width='360px';
           $infowidth='335px';
           $swidth='300px';
       }
       //column 1
       print "<div id='col1' style='float:left;width:$width;height:495px;overflow-y:scroll;'>\n";
       print "<p style='font-weight:bold;'>$colhead</p>\n";
       if ($dbPressParts['numrows']>0)
       {
           foreach($dbPressParts['data'] as $presspart)
           {
                   if($componentid=='')
                   {
                       $componentid=$presspart['component_id'];
                   }
                   print "<div id='partholder_$presspart[id]' style='width:$infowidth;float:left;background-color:white;padding:4px;margin-bottom:4px;'>\n";
                   print "<div style='font-size:14px;float:left;width:$swidth;'>$presspart[part_name]<br>\n";
                   //lets see if we can find an open instance of this part
                   $sql="SELECT * FROM part_instances 
                   WHERE equipment_id='$equipmentid' AND component_id='$componentid' 
                   AND part_id=$presspart[id] 
                   AND sub_component_location='$location' AND equipment_type='$pressunittype' AND replaced=0";
                   
                   $dbInstance=dbselectsingle($sql);
                   print "<div id='part_info".$item."_".$presspart['id']."' style='font-size:10px;'>\n";
                   if ($dbInstance['numrows']>0)
                   {
                       $instance=$dbInstance['data'];
                       $installed=date("m/d/Y", strtotime($instance['install_datetime']));
                       $curCount=$instance['cur_count'];
                       $curTime=round($instance['cur_count']/60,2);
                       print "Installed on $installed, current impressions $curCount and $curTime days.<br>";
                       if($presspart['part_life_type']=='impressions')
                       {
                           $lifeCount=$presspart['part_life_impressions'];
                           if($lifeCount<$curCount)
                           {
                               print "<span style='color:red;'>Part is beyond the recommended life of $lifeCount impressions. Please check and replace soon.</span>";
                           } else {
                               print "There are at least ".($lifeCount-$curCount)." impressions remaining before this part reaches its recommended replacement point.";
                           }
                       } else {
                           $lifeCount=$presspart['part_life_days'];
                           if($lifeCount<$curTime)
                           {
                               print "<span style='color:red;'>Part is beyond the recommended life of $lifeCount days. Please check and replace soon.</span>";
                           } else {
                               print "There are at least ".($lifeCount-$curTime)." days remaining before this part reaches its recommended replacement point.";
                           }
                       }
                   } else {
                       print "Not installed on this unit. ";
                   }
                   print "</div><!--closing div info box -->\n";
                   print "</div>\n";
                   print "<div style='margin-left:5px;float:right;'>\n";
                   print "<a title='Part Replacement' href='includes/ajax_handlers/partReplacement.php?action=perform&item=$item&equipmenttype=$pressunittype&equipmentid=$equipmentid&pressid=$pressid&componentid=$componentid&piece=$unit&locationsub=$location&partid=$presspart[id]' class='ajaxload'><img src='artwork/icons/spanner_48.png' border=0 width=24'></a>\n";
                   print "</div>\n";
                   print "<div class='clear'></div>\n";
                   print "</div>\n";
               }
       }
       print "</div>\n"; 
       if ($pressunittype!='generic')
       {
           $item='s13';
           $t="AND tower_side=13";
           $colhead='13-side parts:';
           $location="Side:13|Piece:$unit";
           print "<div id='col2' style='margin-left:10px;float:left;width:$width;height:495px;overflow-y:scroll;'>\n";
           print "<p style='font-weight:bold;'>$colhead</p>\n";
           
           $sql="SELECT * FROM equipment_component WHERE component_type='$pressunittype'";
           $dbComponent=dbselectsingle($sql);
           if($dbComponent['numrows']>0)
           {
               $genericcomponentid=$dbComponent['data']['id'];
               $sql="SELECT A.*, B.component_id FROM equipment_part A, equipment_part_xref B 
               WHERE A.id=B.part_id AND B.equipment_id='$equipmentid' 
               AND B.component_id='$genericcomponentid' AND B.equipment_type='press'
               ORDER BY part_name";
           } else {
               $sql="";
           }
           $dbPressParts=dbselectmulti($sql);
           if ($dbPressParts['numrows']>0)
           {
               foreach($dbPressParts['data'] as $presspart)
                   {
                       if($componentid=='')
                       {
                           $componentid=$presspart['component_id'];
                       }
                       print "<div id='partholder_$presspart[id]' style='width:$infowidth;float:left;background-color:white;padding:4px;margin-bottom:4px;'>\n";
                           print "<div style='font-size:14px;float:left;width:$swidth;'>$presspart[part_name]<br>\n";
                           //lets see if we can find an open instance of this part
                           $sql="SELECT * FROM part_instances 
                           WHERE equipment_id='$equipmentid' AND component_id='$componentid' 
                           AND part_id=$presspart[id] 
                           AND sub_component_location='$location' AND equipment_type='$pressunittype' AND replaced=0";
                           
                           $dbInstance=dbselectsingle($sql);
                           print "<div id='part_info".$item."_".$presspart['id']."' style='font-size:10px;'>\n";
                           if ($dbInstance['numrows']>0)
                           {
                               $instance=$dbInstance['data'];
                               $installed=date("m/d/Y", strtotime($instance['install_datetime']));
                               $curCount=$instance['cur_count'];
                               $curTime=round($instance['cur_count']/60,2);
                               print "Installed on $installed, current impressions $curCount and $curTime days.<br>";
                               if($presspart['part_life_type']=='impressions')
                               {
                                   $lifeCount=$presspart['part_life_impressions'];
                                   if($lifeCount<$curCount)
                                   {
                                       print "<span style='color:red;'>Part is beyond the recommended life of $lifeCount impressions. Please check and replace soon.</span>";
                                   } else {
                                       print "There are at least ".($lifeCount-$curCount)." impressions remaining before this part reaches its recommended replacement point.";
                                   }
                               } else {
                                   $lifeCount=$presspart['part_life_days'];
                                   if($lifeCount<$curTime)
                                   {
                                       print "<span style='color:red;'>Part is beyond the recommended life of $lifeCount days. Please check and replace soon.</span>";
                                   } else {
                                       print "There are at least ".($lifeCount-$curTime)." days remaining before this part reaches its recommended replacement point.";
                                   }
                               }
                           } else {
                               print "Not installed on this unit. ";
                           }
                           print "</div><!--closing div info box -->\n";
                           print "</div>\n";
                           print "<div style='margin-left:5px;float:right;'>\n";
                           print "<a title='Part Replacement' href='includes/ajax_handlers/partReplacement.php?action=perform&item=$item&equipmenttype=$pressunittype&equipmentid=$equipmentid&pressid=$pressid&componentid=$componentid&piece=$unit&locationsub=$location&partid=$presspart[id]' class='ajaxload'><img src='artwork/icons/spanner_48.png' border=0 width=24'></a>\n";
                           print "</div>\n";
                           print "<div class='clear'></div>\n";
                       print "</div>\n";
                   }
           }
           
           print "</div>\n";      
       }
       
       //column 3 - maintenance - tasks will be stacked for printing units
       print "<div id='col3' style='margin-left:10px;float:left;width:$width;height:495px;overflow-y:scroll;'>\n";
          print "<p style='font-weight:bold;'>Maintenance Tasks</p>\n";
          if ($pressunittype!='generic')
          {
               $item='s13';
               $t="AND tower_side=13";
               $colhead='13-side parts:';
               $location="Side:13|Piece:$piece";
               
               $sql="SELECT * FROM equipment_component WHERE component_type='$pressunittype'";
               $dbComponent=dbselectsingle($sql);
               if($dbComponent['numrows']>0)
               {
                   $genericcomponentid=$dbComponent['data']['id'];
                   $sql="SELECT A.*, B.component_id FROM equipment_pm A, equipment_pm_xref B 
                   WHERE A.id=B.pm_id AND B.equipment_id='$equipmentid' 
                   AND B.component_id='$genericcomponentid' AND B.equipment_type='press'";
               } else {
                   $sql="";
               }
           } else {
               if($componentid=='')
               {
                  $sql="SELECT DISTINCT(id) FROM equipment_component WHERE equipment_id=$equipmentid";
               } else {
                  $components=$componentid.",";
                  $sql="SELECT DISTINCT(id) FROM equipment_component 
                  WHERE equipment_id=$equipmentid OR parent_id=$componentid";
               }
               $dbComponents=dbselectmulti($sql);
               if($dbComponents['numrows']>0)
               {
                   foreach($dbComponents['data'] as $c)
                   {
                       $components.=$c['id'].",";
                   }
               }
               $components=substr($components,0,strlen($components)-1);
               if($components=='')
               {
                   $sql="SELECT A.*, B.component_id FROM equipment_pm A, equipment_pm_xref B 
               WHERE A.id=B.pm_id AND B.equipment_id='$equipmentid' 
               AND B.equipment_type='$pressunittype'";
               } else {
                   $sql="SELECT A.*, B.component_id FROM equipment_pm A, equipment_pm_xref B 
               WHERE A.id=B.pm_id AND B.equipment_id='$equipmentid' 
               AND B.component_id IN ($components) AND B.equipment_type='$pressunittype'";
               }
               
           }
           
           $dbPressTasks=dbselectmulti($sql);
           if ($dbPressTasks['numrows']>0)
           {
               foreach($dbPressTasks['data'] as $presstask)
               {
                   print "<div id='task_$presstask[id]' style='background-color:white;padding:4px;margin-bottom:4px;'>\n";
                       print "<div style='float:left;font-size:10px;width:$swidth'>\n";
                       if($ttype=='printing'){$side=' for 10 side';$pmitem='_s10';$location='Side:10|Piece:'.$unit;}else{$side='';$pmitem='';$location='';}
                       print "<span style='font-size:14px;'>$presstask[pm_name]$side</span>\n";
                       //lets see if we can find an open instance of this part
                       $sql="SELECT * FROM pm_instances WHERE equipment_id='$equipmentid' 
                       AND component_id='$componentid' AND pm_id=$presstask[id] 
                       AND sub_component_location='$location' AND completed=0";
                       $dbInstance=dbselectsingle($sql);
                       print "<br /><span id='pm_info".$pmitem."_".$presstask['id']."'>\n";
                       if ($dbInstance['numrows']>0)
                       {
                           $instance=$dbInstance['data'];
                           $installed=date("m/d/Y", strtotime($instance['install_datetime']));
                           if($instance['pm_life_type']=='impressions')
                           {
                               $due="in ".($presstask['pm_life_impressions']-$presstask['cur_count'])." more cycles.";
                           } else {
                               $cur=$presstask['pm_life_days']-round($presstask['cur_count']/(60*24),0);
                               $cur=date("m/d/Y",strtotime("+$cur days"));
                               $due="on ".$cur.".";
                           }
                           print "Performed on $installed. Due again $due";
                       } else {
                           print "Not performed on this unit.";
                       }
                       print "</span><br />\n";
                       print "</div>\n";
                       print "<div style='margin-left:5px;float:right;'>\n";
                       print "<a title='Part Replacement' href='includes/ajax_handlers/partReplacement.php?action=performpm&item=$pmitem&equipmenttype=$pressunittype&equipmentid=$equipmentid&pressid=$pressid&componentid=$componentid&piece=$piece&locationsub=$location&partid=$presstask[id]' class='ajaxload'><img src='artwork/icons/spanner_48.png' border=0 width=24'></a>\n";
                       print "</div>\n";
                       print "<div class='clear'></div>\n";
                   print "</div>\n";
                   
                   if($ttype=='printing')
                   {
                       print "<div id='task_$presstask[id]' style='background-color:white;padding:4px;margin-bottom:4px;'>\n";
                           print "<div style='float:left;font-size:10px;width:$swidth'>\n";
                           if($ttype=='printing'){$side=' for 13 side';$pmitem='_s13';$location='Side:13|Piece:'.$unit;}else{$side='';$pmitem='';$location='';}
                           print "<span style='font-size:14px;'>$presstask[pm_name]$side</span>\n";
                           //lets see if we can find an open instance of this part
                           $sql="SELECT * FROM pm_instances WHERE equipment_id='$equipmentid' 
                           AND component_id='$componentid' AND pm_id=$presstask[id] 
                           AND sub_component_location='$location' AND completed=0";
                           $dbInstance=dbselectsingle($sql);
                           print "<br /><span id='pm_info".$pmitem."_".$presstask['id']."'>\n";
                           if ($dbInstance['numrows']>0)
                           {
                               $instance=$dbInstance['data'];
                               $installed=date("m/d/Y", strtotime($instance['install_datetime']));
                               if($instance['pm_life_type']=='impressions')
                               {
                                   $due="in ".($presstask['pm_life_impressions']-$presstask['cur_count'])." more cycles.";
                               } else {
                                   $cur=$presstask['pm_life_days']-round($presstask['cur_count']/(60*24),0);
                                   $cur=date("m/d/Y",strtotime("+$cur days"));
                                   $due="on ".$cur.".";
                               }
                               print "Performed on $installed. Due again $due";
                           } else {
                               print "Not performed on this unit.";
                           }
                           print "</span><br />\n";
                           print "</div>\n";
                           print "<div style='margin-left:5px;float:right;'>\n";
                           print "<a title='Part Replacement' href='includes/ajax_handlers/partReplacement.php?action=performpm&item=$pmitem&equipmenttype=$pressunittype&equipmentid=$equipmentid&pressid=$pressid&componentid=$componentid&piece=$piece&locationsub=$location&partid=$presstask[id]' class='ajaxload'><img src='artwork/icons/spanner_48.png' border=0 width=24'></a>\n";
                           print "</div>\n";
                           print "<div class='clear'></div>\n";
                       print "</div>\n";
                   }
                   
               }
           }
       print "</div>\n";
       print "</div><!-- closes the perform maintenance tab -->\n";
       } /*closes conditional for ticket only */
           
       /* SEARCH TAB 
       *  utilizes a script called getMaintenanceHelpTopics that queries a script called findTroubleSolutions in ajax_helpers
       *  returns an html block that is rendered in the search_results div
       */
       print "<div id='search'  style='height:510px;'>\n";
       print "<b>Keywords:</b><input type='text' id='keywords' placeholder='Search terms...' style='width:200px;margin-left:20px;margin-right:10px;' />\n";
       print "<input type='button' value='Search' onclick='getMaintenanceHelpTopics(\"press\");'>\n<br><br>\n";
       print "<div id='search_results' style='width:800px;height:470px;overflow-y:scroll;'></div>\n";
       print "</div><!-- closes the search tab -->\n";
       
       
       print "<div id='history'  style='height:510px;'>\n";
        print "<div id='mainthistory' style='float:left;width:400px;height:500px;overflow-y:scroll;'>\n";
            //object_unit is a compliation of fields like:
            $funit="c-$componentid|s-$sub|u-$unit";
            
            print "<p style='font-weight:bold;'>Here is the history of issues for this unit</p>\n";
            $sql="SELECT * FROM maintenance_tickets WHERE object_id='$equipmentid' AND object_unit='$funit' AND object_type='$pressunittype'";
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
        
        if(!$ticketonly)
        {    
        print "<div id='parthistory' style='float:left;margin-left:20px;width:350px;height:500px;overflow-y:scroll;'>\n";
                print "<p style='font-weight:bold;'>Here is the part replacement history for the past 6 months for this unit</p>\n";
                $dateback=date("Y-m-d",strtotime("-6 months"));
                if($pressunittype=='generic')
                {
                    $sql="SELECT B.part_name, A.install_datetime, A.cur_time, A.cur_count, A.sub_component_location  
                    FROM part_instances A, equipment_part B WHERE A.part_id=B.id AND A.equipment_id='$equipmentid' 
                    AND A.component_id='$componentid' AND A.equipment_type='$pressunittype' 
                    AND install_datetime>='$dateback' ORDER BY install_datetime DESC";
                    
                } else {
                    $sql="SELECT B.part_name, A.install_datetime, A.cur_time, A.cur_count, A.sub_component_location  
                    FROM part_instances A, equipment_part B WHERE A.part_id=B.id AND A.equipment_id='$equipmentid' 
                    AND A.component_id='$componentid' AND A.equipment_type='$pressunittype' 
                    AND A.sub_component_location LIKE '%Piece:$unit%' AND install_datetime>='$dateback' ORDER BY install_datetime DESC";
                    
                }
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
                        if($part['sub_component_location']!='')
                        {
                            $loc=explode("|",$part['sub_component_location']);
                            $loc="for ".$loc[0];
                        }
                        print "$partname - installed on $installdate $loc<br>\n";
                        print "<div style='float:left;width:150px;'>Run time: $currenttime days</div>\n";
                        print "<div style='float:left;width:150px;'>Run impressions: $currentcount</div><div class='clear'></div></p>\n";
                        print "</div>\n";
                    }
                }
        print "</div>\n";
      print "</div><!-- closes the history tab -->\n";
        } /* closes conditional for ticket only */
       
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
            //alert('status: ' + statusText + '\n\nresponseText: \n' + responseText + 
            //    '\n\nThe output div should have already been updated with the responseText.');
            
            
        } 
    </script>
     <?php 
      print "</div>\n"; 
         
}
      
function build_press_lookup($pressid)
{
      //now we build it!
      $sql="SELECT * FROM press_towers WHERE press_id=$pressid ORDER BY tower_order";
      $dbTowers=dbselectmulti($sql);
      $i=0;
      if ($dbTowers['numrows']>0)
      {
          print "<div id='pressholder' style='height:320px;'>\n";
          foreach($dbTowers['data'] as $tower)
          {
             $stacked=false;
             $lastid=0;
             if ($tower['stack_on']==0)
             {
                 print "<div id='tower_$tower[id]' style='height:320px;width:80px;float:left;'>\n";
                 switch ($tower['tower_type'])
                 {
                     case 'printing':
                        print "<div style='height:80px;width:110px;'>&nbsp;</div>\n";
                        build_printing_tower($pressid,$tower);
                     break;
                     
                     case 'ribbon deck':
                     build_ribbon_tower($pressid,$tower);
                     break;
                     
                     case 'folder':
                     build_folder_tower($pressid,$tower);
                     break;
                }
                print "</div>\n";
            } else {
                $stacked=true;
            }
            
          }
          print "</div>\n";
          //now show all the splicers and stackers
          print "<div id='splicerstackerholder' style='margin-top:20px;'>\n";
          foreach($dbTowers['data'] as $tower)
          {
             print "<div style='width:80px;float:left;'>";
             if($tower['stackers']!='')
             {
                 build_stacker($pressid,$tower);
             }
             if($tower['strappers']!='')
             {
                 build_strapper($pressid,$tower);
             }
             if($tower['splicers']!='')
             {
                 build_splicer($pressid,$tower);
             }
             if($tower['counterveyors']!='')
             {
                 build_counterveyor($pressid,$tower);
             }
             print "</div>\n";
          }
          
          print "</div>\n";
          
      }
      
 }
 
 function build_stacker($pressid,$tower)
 {
     //possibility exists for more than one stacker, so we'll just stack em ;)
     
     $stackerids=explode("|",$tower['stackers']);
     if(count($stackerids)>0)
     {
        foreach($stackerids as $key=>$id)
        {
            if($id!='')
            {
                //ok, lets get our stacker!
                print "<div class='equipment' style='width:80px;height:60px;'>
                <a class='equipment' href='?pressid=$pressid&equipmentid=$id&type=generic' style='text-decoration:none;'>
                  <img src='/artwork/equipmentImages/stacker.png' border=0 width=76>
                </a>
                </div>";
            }    
        }    
     }
 }
 
 function build_strapper($pressid,$tower)
 {
     $strapperids=explode("|",$tower['strappers']);
     if(count($strapperids)>0)
     {
        foreach($strapperids as $key=>$id)
        {
            if($id!='')
            {
                //ok, lets get our strapper!
                print "<div class='equipment' style='width:80px;height:60px;'>
                <a class='equipment' href='?pressid=$pressid&equipmentid=$id&type=generic' style='text-decoration:none;'>
                  <img src='/artwork/equipmentImages/strapper.png' border=0 width=76>
                </a>
                </div>";               
            }    
        }    
     }
 }
 
 function build_counterveyor($pressid,$tower)
 {
     $counterids=explode("|",$tower['counterveyors']);
     if(count($counterids)>0)
     {
        foreach($counterids as $key=>$id)
        {
            if($id!='')
            {
                //ok, lets get our counterveyor!
                print "<div class='equipment' style='width:80px;height:60px;'>
                <a class='equipment' href='?pressid=$pressid&equipmentid=$id&type=generic' style='text-decoration:none;'>
                  <img src='/artwork/equipmentImages/counterveyor.png' border=0 width=76>
                </a>
                </div>";
            }    
        }    
     }
 }
 
 function build_splicer($pressid,$tower)
 {
    $splicerids=explode("|",$tower['splicers']);
    $firstid=$splicerids[0];
    //splicers are a special case, there could be two of them in a column if two "towers" are stacked
    //need to find out if something is stacked on this tower
    $sql="SELECT * FROM press_towers WHERE press_id='$pressid' AND stack_on='$tower[id]'";
    $dbStacked=dbselectsingle($sql);
    if($dbStacked['numrows']>0)
    {
        $stacking=true;
        $stacked=$dbStacked['data'];
    }
    
   //ok, lets get our splicer!
    if ($stacking)
    {
       print "<div class='equipment' style='margin-top:5px;float:left;width:40px;height:60px;'>
         <a href='?pressid=$pressid&equipmentid=$firstid&type=generic' style='text-decoration:none;'>
            <img src='artwork/equipmentImages/dualsplicer-left.png' border=0 height=48 width=38>
        </a>
        </div>";
       $splicerid=explode("|",$stacked['splicers']);
       $splicerid=$splicerid[0];
       print "<div class='equipment' style='margin-top:5px;float:left;width:40px;height:60px;'>
       <a href='?pressid=$pressid&equipmentid=$splicerid&type=generic' style='height:80px;width:80px;text-decoration:none;'>
        <img src='artwork/equipmentImages/dualsplicer-right.png' border=0 height=48 width=38>
      </a>
      </div>\n";
      print "<div class='clear'></div>\n";
    } else {
        print "<div class='equipment' style='width:80px;height:60px;'>
        <a href='?pressid=$pressid&equipmentid=$firstid&type=generic' style='height:80px;width:80px;text-decoration:none;'>
            <img src='artwork/equipmentImages/singlesplicer.png' border=0 width=60>
          </a>
          </div>\n";
    }
 }
 
 
 function build_printing_tower($pressid,$tower,$stacks=4)
 {
    global $cyan, $magenta, $yellow, $black, $splicers;
    $config=$tower['color_config'];
    $units=explode("/",$config);
    $units=array_reverse($units);
    $timage='';
    $ucount=count($units);
    if ($ucount<$stacks)
    {
        //lets see if anything stacks on this unit
        $sql="SELECT * FROM press_towers WHERE stack_on=$tower[id]";
        $dbStacks=dbselectmulti($sql);
        if ($dbStacks['numrows']>0)
        {
            foreach($dbStacks['data'] as $stack)
            {
                $stackconfig=$stack['color_config'];
                $stackunits=explode("/",$stackconfig);
                $stacks=count($stackunits);
                build_printing_tower($pressid,$stack,4-$stacks-$ucount+1);
            }
        }
    } 
    $ucount=count($units);
    if ($ucount<$stacks)
    {
        while ($ucount<$stacks)
        {
            print "<div style='height:60px;width:80px;'></div>\n";
            $ucount++;   
        }    
    }
    
    foreach($units as $key=>$color)
    {
        switch ($color)
        {
            case 'K':
            $c=$black;
            break;
            case 'C':
            $c=$cyan;
            break;
            case 'M':
            $c=$magenta;
            break;
            case 'Y':
            $c=$yellow;
            break;
        }
        print "<div class='equipmentitem' style='height:60px;width:80px;'>
          <a href='?type=printing&pressid=$pressid&componentid=$tower[id]&unit=$color' style='text-decoration:none;'>
            <img src='artwork/equipmentImages/pressUnit.png' style='margin-left:5px;background-color:$c' border=0 width=70 height=60>
          </a>
        </div>\n";        
    }
    if ($tower['stack_on']==0)
    {
        $tname=str_replace(" Lower","",$tower['tower_name']);
        $tname=str_replace(" Upper","",$tname);
        print "<div style='width:80px;text-align:center;font-size:10px;font-weight:bold;'>$tname</div>";
    }      
 }
 
 function build_folder_tower($pressid,$tower)
 {
    $formers=$tower['folder_config'];
    $padding=260-intval($formers*60);
    print "<div style='width:80px;height:".$padding."px;'>&nbsp;</div>\n";
    
    for ($i=1;$i<=$formers;$i++)
    {
       print "<div class='equipmentitem' style='height:60px;width:80px;'>
         <a href='?type=former&equipmentid=$pressid&componentid=$tower[id]&sub=former_$i'>
           <img src='artwork/equipmentImages/folderFormer.png' border=0 height=60>
         </a>
       </div>\n";  
    }
    print "<div class='equipmentitem' style='height:60px;width:80px;'>
      <a href='?type=folder&pressid=$pressid&componentid=$tower[id]&sub=base'>
        <img src='artwork/equipmentImages/folderBase.png' border=0 width=80>
      </a>
    </div>\n";
    $tname=stripslashes( $tower['tower_name']);
     print "<div style='width:80px;text-align:center;font-size:10px;font-weight:bold;'>$tname</div>";  
 }
 
 function build_ribbon_tower($pressid,$tower)
 {
     $ribbons=$tower['ribbon_config'];
     //pad out the top
     $padding=280-intval($ribbons*30);
     print "<div style='width:80px;height:".$padding."px;'>&nbsp;</div>\n";
     for($i=$ribbons;$i>=1;$i=$i-1)
     {
        print "<div class='equipmentitem' style='height:30px;'>
          <a href='?type=ribbon&pressid=$pressid&componentid=$tower[id]&sub=ribbon_$i'>
            <img src='artwork/equipmentImages/ribbon_deck_unit.png' width=80 border=0>
          </a>
        </div>\n";
     }
     print "<img src='artwork/equipmentImages/ribbon_deck_base.png' width=80 border=0 />";
     $tname=stripslashes( $tower['tower_name']);
     print "<div style='width:80px;text-align:center;font-size:10px;font-weight:bold;'>$tname</div>"; 
 }
 
 ?>
      <script>
      $('.equipment').mouseover(function(){
          $(this).css('background-color','yellow');
      })
      $('.equipment').mouseout(function(){
          $(this).css('background-color','white');
      })
      
      </script>
      <?php
 footer(); 
?>
