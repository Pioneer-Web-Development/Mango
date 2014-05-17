<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>Press Stop Code Display</title>
<meta name="description" content="Handles stop codes for Mango">
<meta name="viewport" content="width=device-width">
<?php
include("../functions_db.php");
include("../config.php");
include("../functions_common.php");
$base="../../";
//lets load the style sheets
$sql="SELECT * FROM core_system_files WHERE file_type='style' AND head_load=1 AND mango=1 ORDER BY load_order ASC";
$dbStyles=dbselectmulti($sql);
if($dbStyles['numrows']>0)
{
    foreach($dbStyles['data'] as $style)
    {
       $loadfor=explode(",",$style['specific_page']);
       if($style['specific_page']=='' || in_array($scriptname,$loadfor))
       {
           $uptime=strtotime($style['file_moddate']); 
           print "<link rel='stylesheet' type='text/css' href='".$base."styles/$style[file_name]?$uptime' />\n";     
       }       
    }
}

//lets load the javascript files
$sql="SELECT * FROM core_system_files WHERE file_type='script' AND head_load=1 AND mango=1 ORDER BY load_order ASC";
$dbScripts=dbselectmulti($sql);
if($dbScripts['numrows']>0)
{
    foreach($dbScripts['data'] as $script)
    {
        $loadfor=explode(",",$script['specific_page']);
        $uptime=strtotime($script['file_moddate']); 
        if($script['specific_page']=='' || in_array($scriptname,$loadfor))
        {
            print "<script type='text/javascript' src='".$base."includes/jscripts/$script[file_name]?$uptime'></script>\n";     
        } else {
            print "<!-- when loading $script[file_name] looked for $script[specific_page] compared to $scriptname -->\n";
        }       
    }
}
?>
</head>
<body style='overflow:hidden;'>

<?php
  //this file generates all the stop code displays
  //we are always starting here, so we do the insert here, the update will be in the restart function
  $type=$_GET['type'];
  $stopcode=$_GET['stopid'];
  $jobid=$_GET['jobid'];
  if (isset($_GET['pressid']))
  {
      $pressid=$_GET['pressid'];
  } else {
      global $pressid;
  }
  $time=date("Y-m-d H:i:s");
  
      if ($type=='jobnotes')
      {
          print "<div id='jobnotes' style='height:380px;text-align:center;overflow:hidden;text-align:left;overflow:hidden;position:relative;'>\n";
          $sql="SELECT A.notes_press, A.notes_job, A.job_message, B.run_message, C.pub_message FROM jobs A, publications_runs B, publications C WHERE A.id=$jobid AND A.run_id=B.id AND A.pub_id=C.id";
          $dbJobInfo=dbselectsingle($sql);
          $jobinfo=$dbJobInfo['data'];
          print "<form id='pressstop' name='pressstop' action='".$base."includes/ajax_handlers/pressStopCodeHandler.php' method=post onsubmit='javascript:parent.jQuery.fancybox.close();'>\n";
          print "<p style='font-size:18px;font-weight:bold;color:#AC1D23;'>Please enter your job notes:</p>\n";
          print "<h3>Messages</h3>";
          print $GLOBALS['pressStartMessage'];
          print stripslashes($jobinfo['pub_message']."\n".$jobinfo['run_message']."\n".$jobinfo['job_message'])."<br />";
          print "<textarea id='stopnotes' name='stopnotes' cols='50' rows='3' style='width:100%;'>".strip_tags(stripslashes($jobinfo['notes_job']."\n".$jobinfo['notes_press']))."</textarea>\n";
          print "<br /><br />";
         
      } else {
          $sql="INSERT INTO job_stops (job_id, stop_code, stop_datetime) VALUES ('$jobid', '$stopcode', '$time')";
          $dbStop=dbinsertquery($sql);
          $stopid=$dbStop['insertid'];  //get and save the opened stop record so we can save and close the correct one
          $sql="SELECT press_id, notes_press FROM jobs WHERE id=$jobid";
          $dbJobInfo=dbselectsingle($sql);
          $jobinfo=$dbJobInfo['data'];
          $pressid=$jobinfo['press_id'];
          $sql="SELECT * FROM stop_codes WHERE id=$stopcode";
          $dbCode=dbselectsingle($sql);
          $code=$dbCode['data'];
          if ($code['specify']==1)
          {
              print "<div id='withpress' style='width:760px;height:600px;text-align:center;overflow:hidden;position:relative;'>\n";
              print "<form id='pressstop' name='pressstop' action='".$base."includes/ajax_handlers/pressStopCodeHandler.php' method=post onsubmit='javascript:parent.jQuery.fancybox.close();'>\n";
              show_press($pressid);
          } else {
              print "<div id='justquestion' style='width:580px;height:380px;text-align:left;overflow:hidden;position:relative;'>\n";
              print "<form id='pressstop' name='pressstop' action='".$base."includes/ajax_handlers/pressStopCodeHandler.php' method=post onsubmit='javascript:parent.jQuery.fancybox.close();'>\n";
          }
          print "<div style='text-align:left;'>\n";
          print "<p style='font-size:18px;font-weight:bold;'>PRESS STOP INFORMATION COLLECTOR</p>\n";
          if ($code['notes']==1)
          {
              print "<p style='font-size:16px;font-weight:bold;color:#AC1D23;'>".$code['notes_text']."</p>\n";
              print "<textarea id='stopnotes' name='stopnotes' cols=85 rows=4 style='width:100%'></textarea>\n";
          }
          print "<br />";
          print "</div>\n";
      }
      print "<input type='hidden' id='stopinfo' name='stopinfo' size=80 value=''>\n";
      print "<input type='hidden' id='type' name='type' value='$type'>\n";
      print "<input type='hidden' id='jobid' name='jobid' value='$jobid'>\n";
      print "<input type='hidden' id='stopid' name='stopid' value='$stopid' >\n";
      print "<input type='submit' class='submit' style='float:right;' value='Complete Stop &amp; return to the the run'>\n";
      print "</form>\n"; 
      print "</div>\n";
      
  //print "More stuff";
      
  
function show_press($pressid)
{
    //now we build it!
   print "<div id='pressholder' style='margin-top:10px;margin-bottom:10px;margin-left:auto;margin-right:auto;'>\n";
       $sql="SELECT * FROM press_towers WHERE press_id=$pressid ORDER BY tower_order";
      $dbTowers=dbselectmulti($sql);
      if ($dbTowers['numrows']>0)
      {
          foreach($dbTowers['data'] as $tower)
          {
             if ($tower['stack_on']==0)
             {
                 print "<div id='tower_$tower[id]' style='height:300px;width:60px;float:left;'>\n";
                 switch ($tower['tower_type'])
                 {
                     case 'printing':
                        print "<div style='height:60px;width:60px;'>&nbsp;</div>\n";
                        build_printing_tower($tower);
                     break;
                     
                     case 'ribbon deck':
                     build_ribbon_tower($tower);
                     break;
                     
                     case 'folder':
                     build_folder_tower($tower);
                     break;
                }
                print "</div>\n";
            }
          }
      }
      print "</div>\n";
      print "<div class='clear'></div>\n";
 }
 
 function build_printing_tower($tower,$stacks=4)
 {
    global $cyan, $magenta, $yellow, $black;
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
                build_printing_tower($stack,4-$stacks-$ucount+1);
            }
        }
    } 
    $ucount=count($units);
    if ($ucount<$stacks)
    {
        while ($ucount<$stacks)
        {
            print "<div style='height:45px;width:60px;'></div>\n";
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
        print "<div style='height:45px;width:60px;background-color:$c'>
        <input type='hidden' id='c$tower[id]_$color' value='0'>
        <img id='$tower[id]_$color' class='imgUnselected' src='".$GLOBALS['base']."artwork/equipmentImages/pressUnit.jpg' style='margin-left:5px;margin-right:5px;' onclick=\"pressStopInfo(this,'tower','$tower[id]','$color');\" border=0 width=50 height=45>
        </div>\n";        
    }
    if ($tower['stack_on']==0)
    {
        $tname=str_replace(" Lower","",$tower['tower_name']);
        $tname=str_replace(" Upper","",$tname);
        print $tname;    
    }      
 }
 
 function build_folder_tower($tower)
 {
    $formers=$tower['folder_config'];
    if ($formers<3)
    {
        $i=$formers;
        while($i<3)
        {
            print "<div style='height:60px;width:60px;'>&nbsp;</div>\n";
            $i++;
        }
    }
    for ($i=1;$i<=$formers;$i++)
    {
       print "<div style='height:60px;width:60px;>
       <input type='hidden' id='c$tower[id]_f$i' value='0'>
       <img id='$tower[id]_f$i' class='imgUnselected' src='".$GLOBALS['base']."artwork/equipmentImages/folderFormer.jpg' onclick=\"pressStopInfo(this,'tower','$tower[id]','former_$i');\"  border=0; height=60>
       </div>";  
    }
    print "<div style='height:60px;width:60px;'>
    <input type='hidden' id='c$tower[id]_base' value='0'>
    <img id='$tower[id]_base' class='imgUnselected' src='".$GLOBALS['base']."artwork/equipmentImages/folderBase.jpg' onclick=\"pressStopInfo(this,'tower','$tower[id]','base');\" border=0 height=60>
    </div>";
    print $tower['tower_name']; 
 }
 
 function build_ribbon_tower($tower)
 {
    
     print "<div style='width:60px;margin-top:154px;'>
     <input type='hidden' id='c$tower[id]_base' value='0'>
     <img id='$tower[id]_base' class='imgUnselected' src='".$GLOBALS['base']."artwork/equipmentImages/ribbonDeck.jpg' onclick=\"pressStopInfo(this,'tower','$tower[id]','base');\" width=60 border=0>
     </div>";
    print $tower['tower_name']; 
 }
 dbclose();
     
?> 
<script type='text/javascript'>
$(function() {
        $("input:button, input:submit, a.submit, a.button").button();
    });
</script>
</body>
</html>