<?php
if ($_GET['mode']=='inc')
{
    require("functions_db.php");
    require("config.php");
    if ($_GET['save']=='true')
    {
        $save=true;
    } else {
        $save=false;
    }
    if ($_GET['display']=='true')
    {
        $display=true;
    } else {
        $display=false;
    }
    configure($_GET['layoutid'],true,$save,$display);
}
                
function configure($layoutid,$standalone=false,$save=false,$display=false)
{
    global $leadtypes,$producttypes,$balloontypes,$sectioncolors, $pressid;
    //get the paeg count for newspaper and commercial leads
    $sql="SELECT * FROM core_preferences LIMIT 1";
    $dbPrefs=dbselectsingle($sql);
    $prefs=$dbPrefs['data'];
    $compages=$prefs['commercialLeadPageCount'];
    $newspages=$prefs['newspaperLeadPageCount'];
    
    $sectionsql="SELECT * FROM layout_sections WHERE layout_id=$layoutid ORDER BY section_number";
    $dbSections=dbselectmulti($sectionsql);
    if ($dbSections['numrows']>0)
    {
        $sections=$dbSections['data'];
        $towersql="SELECT * FROM press_towers WHERE press_id=$pressid ORDER BY tower_order ASC";
        $dbTowers=dbselectmulti($towersql);
        
        //see if this press configuration already exists
        $configsql="SELECT * FROM layout_page_config WHERE layout_id=$layoutid ORDER BY tower_id";
        $dbConfig=dbselectmulti($configsql);
        if ($dbConfig['numrows']>0){$pages=$dbConfig['data'];}else{$pages=array();}
        
        if (!$standalone)
        {
            print "<form name='press_layout_config' enctype='multipart/form-data' action='$_SERVER[SCRIPT_NAME]' method='post'>\n";
        }
        foreach($dbTowers['data'] as $tower)
        {
            if ($tower['tower_type']=='printing')
            {   
                $workingsection=array();
                foreach($sections as $section)
                {
                    $checktowers=explode("|",$section['towers']);
                    if (in_array($tower['id'],$checktowers))
                    {
                        $workingsection=$section;
                    }    
                }
                tower($tower,$pages,$workingsection,$display,$compages,$newspages);    
            } elseif ($tower['tower_type']=='folder')
            {
                foreach($sections as $section)
                {
                    if ($section['folder']==$tower['id'])
                    {
                        print "<div style='width:180px;margin-top:2px;margin-right:2px;border: thin solid black;padding:2px;font-size:12px;'>\n";
                        $snum=$section['section_number'];
                        $tcolor=$sectioncolors[$snum];
                        print "<span style='font-weight:bold;color:$tcolor;'>Section $section[section_number]:</span>\n";
                        $totalpages=$section['high_page']-$section['low_page']+1;
                        $ptype=$producttypes[$section['product_type']];
                        $ltype=$leadtypes[$section['lead_type']];
                        print "<br>$totalpages page $ptype, ";    
                        print "FB $section[former], ";
                        print substr($ltype,0,4).' lead, ';    
                        if ($section['balloon']!='NA')
                        {
                            print "balloon to $section[balloon]\n";
                        }
                        print "</div>\n";    
                    }
                
                }
            }
        }
        if (!$standalone)
        {
            print "<input type=hidden id='layoutid' name='layoutid' value='$layoutid'>\n";
            print "<input type='hidden' id='formname' name='formname' value='presslayout'>\n";
            print "<input type='submit' id='submit' name='submit' value='Save Configuration'>\n";
            print "</form>\n";
        }
        if ($save)
        {
            print "<input type=hidden id='layoutid' name='layoutid' value='$layoutid'>\n";
            print "<input type='hidden' id='formname' name='formname' value='presslayout'>\n";
            print "<input type='button' id='submit' name='submit' value='Use This Layout' onclick='saveSelectedLayout();'>\n";
            print "</form>\n";
        }
        
    } else {
        print "You must define at least one section first";
    }     
}



function tower($tower,$allpages,$section,$display,$compages,$newspages)
{
      global $producttypes,$leadtypes,$sectioncolors;
      $lead=$section['lead_type'];
      $type=$section['product_type'];
      $slitter=$tower['slitter_config'];
      $tcolor=$sectioncolors[$section['section_number']];
      if ($tcolor==''){$tcolor="#000000";}
      $slittercol=0;
      $readonly="";
      switch ($producttypes[$type])
    {
        case "Broadsheet":
        if ($leadtypes[$lead]=='Commercial')
        {
            $rows=1;
            $cols=$compages;
            if ($slitter=='center')
            {
                $slittercol=1;
            }
        } else {
            $rows=1;
            $cols=$newspages;
            if ($slitter=='gear')
            {
                $slittercol=2;
            } elseif ($slitter=='operator')
            {
                $slittercol=1;
            }
        }
        break;
        
        case "Tab":
        if ($leadtypes[$lead]=='Commercial')
        {
            $rows=2;
            $cols=$compages;
            if ($slitter=='center')
            {
                $slittercol=1;
            }
        } else {
            $rows=2;
            $cols=$newspages;
            if ($slitter=='gear')
            {
                $slittercol=2;
            } elseif ($slitter=='operator')
            {
                $slittercol=1;
            }
        }
        break;
        
        case "Long Tab":
        if ($leadtypes[$lead]=='Commercial')
        {
            $rows=2;
            $cols=2;
            
        } else {
            $rows=2;
            $cols=2;
        }
        if ($slitter=='center')
            {
                $slittercol=1;
            }
        break;
        
        case "Flexi":
        if ($leadtypes[$lead]=='Commercial')
        {
            $rows=2;
            $cols=4;
        } else {
            $rows=2;
            $cols=4;
        }
        if ($slitter=='center')
            {
                $slittercol=2;
            }
        break;
        
        default:
            $rows=1;
            $cols=$newspages;
            $readonly="readonly ";
        break;
    }
    $pages=array();
    if (isset($allpages))
    {
        for ($r=1;$r<=$rows;$r++)
        {
            for ($c=1;$c<=$cols;$c++)
            {
                foreach($allpages as $item)
                {
                    if ($item['tower_id']==$tower['id'])
                    {
                        if ($item['tower_row']==$r)
                        {
                            if ($item['tower_column']==$c)
                            {
                                if ($item['side']=='10')
                                {
                                    $pages['t_'.$tower['id'].'_10_'.$r.'_'.$c]=$item['page_number'];
                                } else {
                                    $pages['t_'.$tower['id'].'_13_'.$r.'_'.$c]=$item['page_number'];
                                }
                             }
                        }
                    }   
                }
            }
        }
    }
    
    
      
    print " <div style='margin-top:4px;padding-top:4px;border-top:1px dotted black;'>\n";
        print "    <div style='float:left;width:70px;color:$tcolor;font-weight:bold;font-size:12px;'>$tower[tower_name]</div>\n";
        print "    <div id='$tower[id]_tower' style='float:left;'>\n";
            build_page_boxes($rows,$cols,$tower['id'],$slittercol,$pages,$readonly,$tcolor,$display);
        print "    </div>\n";
        print "  <div class='clear' style='height:0px;'></div>\n"; 
    print " </div>\n";
    //print " <div class='clear'></div>\n";

}
  
function build_page_boxes($rows,$columns,$towerid,$slittercol,$pages,$readonly,$tcolor,$display)
{
    $width=intval(100/$columns);
    $linewidth=(90+$columns*2)."px";
    $textwidth=($width-8)."px";
    $textleftmargin=(($width-$textwidth)/2-2)."px";
    $width.="px";

    $texttopmargin="1px";
    $textheight="14px";
    $textsize="12px";
    $height.="18px";
    $side='10';
    for ($row=1;$row<=$rows;$row++)
    {
        for ($column=1;$column<=$columns;$column++)
        {
            $name="t_".$towerid."_".$side."_".$row."_".$column;
            $value=$pages[$name];
            if ($slittercol==$column)
            {
                $border="border:thin solid black;border-right:3px solid black";
            } else {
                $border="border:thin solid black";
            }
            if ($display)
            {
                print "      <div id='holder_$name' style='float:left;$border;width:$width;height:$height;text-align:center;'>\n";
                print "          <span style='font-weight:bold;font-size:$textsize;color:$tcolor;width:$textwidth;height:$textheight;'>$value</span>\n";
                print "       </div>\n";
            } else {
                print "      <div id='holder_$name' style='float:left;$border;width:$width;height:$height;'>\n";
                print "          <input type='text' id='$name' name='$name' value='$value' style='padding-top:-5px;vertical-align:top;font-weight:bold;font-size:$textsize;color:$tcolor;width:$textwidth;height:$textheight;margin-left:$textleftmargin;margin-top:$texttopmargin;' onkeypress='return isNumberKey(event);' $readonly>\n";
                print "       </div>\n";
            }
            
        }
        print "      <div class='clear' style='height:0px;'></div>\n";
    }
    print "     <div id='line' style='height:3px;width:$linewidth;background-color:black;'></div>\n";
    $side='13';
    for ($row=1;$row<=$rows;$row++)
    {
        for ($column=1;$column<=$columns;$column++)
        {
            $name="t_".$towerid."_".$side."_".$row."_".$column;
            $value=$pages[$name];
            if ($slittercol==$column)
            {
                $border="border:thin solid black;border-right:3px solid black";
            } else {
                $border="border:thin solid black";
            }
            if ($display)
            {
                print "      <div id='holder_$name' style='float:left;$border;width:$width;height:$height;text-align:center;'>\n";
                print "          <span style='font-weight:bold;font-size:$textsize;color:$tcolor;width:$textwidth;height:$textheight;'>$value</span>\n";
                print "      </div>\n";
            } else {
                print "      <div id='holder_$name' style='float:left;$border;width:$width;height:$height;'>\n";
                print "          <input type='text' id='$name' name='$name' value='$value' style='padding-top:-5px;vertical-align:top;font-weight:bold;font-size:$textsize;color:$tcolor;width:$textwidth;height:$textheight;margin-left:$textleftmargin;margin-top:$texttopmargin;' onkeypress='return isNumberKey(event);' $readonly>\n";
                print "      </div>\n";
            }
        }
        print "      <div class='clear' style='height:0px;'></div>\n"; 
    }  
}
?>
