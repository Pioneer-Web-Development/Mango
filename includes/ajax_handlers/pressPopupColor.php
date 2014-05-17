<?php
  include("../functions_db.php");
  include("../config.php");
  include("../functions_common.php");
  include("../functions_formtools.php");
  $fullcolors=array('k'=>"K",'c'=>"Full Color",'s'=>"K/Spot");
    $spotcolors=array('k'=>"K",'s'=>"K/Spot");
    
    
  $jobid=$_POST['jobid'];
  $layoutid=$_POST['layoutid'];
  
  $pubid=$_POST['pubid'];
  $pubdate=$_POST['pubdate'];
  $runid=$_POST['runid'];
  $sql="UDPATE jobs SET layout_id=$layoutid, pub_id=$pubid, run_id=$runid, pub_date='$pubdate' WHERE id=$jobid";
  $dbUpdate=dbexecutequery($sql);
  
    $section1_used=$_POST['s1need'];
    $section1_letter=$_POST['s1letter'];
    $section1_name=$_POST['s1name'];
    $section1_low=$_POST['s1low'];
    $section1_high=$_POST['s1high'];
    $section1_format=$_POST['s1format'];
    $section1_lead=$_POST['s1lead'];
    $section1_gatefold=$_POST['s1gate'];
    $section1_doubletruck=$_POST['s1double'];
    
    $section2_used=$_POST['s2need'];
    $section2_letter=$_POST['s2letter'];
    $section2_name=$_POST['s2name'];
    $section2_low=$_POST['s2low'];
    $section2_high=$_POST['s2high'];
    $section2_format=$_POST['s2format'];
    $section2_lead=$_POST['s2lead'];
    $section2_gatefole=$_POST['s2gate'];
    $section2_doubletruck=$_POST['s2double'];
    
    $section3_used=$_POST['s3need'];
    $section3_letter=$_POST['s3letter'];
    $section3_name=$_POST['s3name'];
    $section3_low=$_POST['s3low'];
    $section3_high=$_POST['s3high'];
    $section3_format=$_POST['s3format'];
    $section3_lead=$_POST['s3lead'];
    $section3_gatefold=$_POST['s3gate'];
    $section3_doubletruck=$_POST['s3double'];
    
    
    
    $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
    $dbSections=dbselectsingle($sql);
    if ($dbSections['numrows']>0)
    {
        $sectionid=$dbSections['data']['id'];
        //updating an existing section record
        $sql="UPDATE jobs_sections SET section1_name='$section1_name', section1_code='$section1_letter',
        section1_lowpage='$section1_low', section1_highpage='$section1_high', section1_leadtype='$section1_lead', 
        section1_gatefold='$section1_gatefold', section1_doubletruck='$section1_doubletruck', 
        section1_producttype='$section1_format', section2_name='$section2_name', 
        section2_code='$section2_letter', section2_lowpage='$section2_low', 
        section2_highpage='$section2_high', section2_gatefold='$section2_gatefold', 
        section2_doubletruck='$section2_doubletruck', section2_producttype='$section2_format', section2_leadtype='$section2_lead', 
        section3_name='$section3_name', section3_code='$section3_letter',
        section3_lowpage='$section3_low', section3_highpage='$section3_high', 
        section3_gatefold='$section3_gatefold', section3_doubletruck='$section3_doubletruck', 
        section3_producttype='$section3_format', section3_leadtype='$section3_lead', section1_overrun='$section1_overrun',
        section2_overrun='$section2_overrun', section3_overrun='$section3_overrun', section1_used='$section1_used',
        section2_used='$section2_used', section3_used='$section3_used' WHERE id=$sectionid";
        $dbUpdate=dbexecutequery($sql);
        if ($dbUpdate['error']!='')
        {
            $error.="<br>Section update error<br>".$dbUpdate['error'];
        }
    } else {
        //inserting a new section record
        $sql="INSERT INTO jobs_sections (job_id, section1_name, section1_code, section1_lowpage, 
        section1_highpage, section1_gatefold, section1_doubletruck, section1_producttype, section1_leadtype, 
        section2_name, section2_code, section2_lowpage, section2_highpage, 
        section2_gatefold, section2_doubletruck, section2_producttype, 
        section2_leadtype, section3_name, section3_code, section3_lowpage, 
        section3_highpage, section3_gatefold, section3_doubletruck, 
        section3_producttype, section3_leadtype, section1_overrun, section2_overrun,
         section3_overrun, section1_used, section2_used, section3_used) VALUES
        ('$jobid', '$section1_name', '$section1_letter', '$section1_low', '$section1_high', 
        '$section1_gatefold', '$section1_doubletruck', '$section1_format', '$section1_lead',
         '$section2_name', '$section2_letter', '$section2_low', '$section2_high', 
        '$section2_gatefold', '$section2_doubletruck', '$section2_format', '$section2_lead', 
        '$section3_name', '$section3_letter', '$section3_low', '$section3_high', 
        '$section3_gatefold', '$section3_doubletruck', '$section3_format', 
        '$section3_lead', '$section1_overrun', '$section2_overrun', '$section3_overrun',
        '$section1_used', '$section2_used', '$section3_used')";
        $dbInsert=dbinsertquery($sql);
        if ($dbInsert['error']!='')
        {
            $error.="<br>Section insert error<br>".$dbInsert['error'];
        }
    } 
    
    saveLayout($layoutid,$jobid);
     
    $json['status']='success';
    
    //ok, now lets find press layout that may match
    //this may take awhile if the number of press layouts grows large
    $layoutsql="SELECT * FROM layout WHERE id=$layoutid";
    $dbLayouts=dbselectsingle($layoutsql);
    if ($dbLayouts['numrows']>0)
    {
        //$colors=$GLOBALS['colorconfigs'];
        //if ($need_1){print "Need section 1<br>\n";}
        //if ($need_2){print "Need section 2<br>\n";}
        //if ($need_3){print "Need section 3<br>\n";}
        $name=$dbLayouts['data']['layout_name'];
        $notes=$dbLayouts['data']['layout_notes'];
        //ok grab sections corresponding to this layout
        $html.="<div style='border: 1px solid black;padding:2px;margin-bottom:10px;'>\n";
        $html.="<div style='margin-bottom:6px;padding:2px;background-color:black;color:white;font-weight:bold;font-size:14px;'>\n";
        $html.="Chosen layout: $name - $notes<br>";
        $html.="</div>\n";
        
        for($i=1;$i<=3;$i++)
        {
           switch($i)
           {
               case 1:
                $need=$section1_used;
                $name=$section1_name;
                $code=$section1_letter;
                
               break;
               case 2:
                $need=$section2_used;
                $name=$section2_name;
                $code=$section2_letter;
               break;
               case 3:
                $need=$section3_used;
                $name=$section3_name;
                $code=$section3_letter;
               break;
               
           }
           if ($need)
           {
               $html.="<div id='sectionColor_$i'>\n";
                   $html.="<a class='button' onclick='setAllPageColor(\"black\",$i);'>Set all to B/W</a> ";
                   $html.="<a class='button' onclick='setAllPageColor(\"color\",$i);'>Set all to Color</a><br />";
                   $html.="<div id='section".$i."_$layoutid'>\n";
                    $html.="<b>Section $i: $name - $code</b><br>\n";
                    $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='$code' ORDER BY page_number ASC";
                    //print "Layout select sql is $sql<br>";
               
                    $dbPages=dbselectmulti($sql);
                    if ($dbPages['numrows']>0)
                    {
                        foreach($dbPages['data'] as $page)
                        {
                            $html.="<div style='width:80px;height:60px;float:left;border:thin solid black;padding:3px;'>\n";
                            $html.="<p style='margin:0;text-align:center;font-weight:bold;font-size:14px'>$page[page_number]<br>\n";
                            if ($page['possiblecolor']==1)
                            {
                                if($page['color']==1)
                                {
                                    $pcolor='c';
                                }elseif($page['color']==0 && $page['spot']==1)
                                {
                                    $pcolor='s';
                                } else {
                                    $pcolor='k';
                                }
                                $html.=input_select('pageid_'.$page['id'],$fullcolors[$pcolor],$fullcolors);
                            } elseif($page['possiblespot']==1) {
                                switch($page['spot'])
                                {
                                    case 0:
                                    $pcolor='k';
                                    break;
                                    case 1:
                                    $pcolor='s';
                                    break;
                                }
                                $html.=input_select('pageid_'.$page['id'],$spotcolors[$pcolor],$spotcolors);
                            } else {
                                $html.=input_hidden('pageid_'.$page['id'],'0');
                                $html.= 'k';
                            }
                            $html.="</p>\n";
                            $html.="</div>\n";
                        }
                        $html.="<div class='clear'></div>\n";
                    } else {
                        $html.="No pages defined for this section.";
                    }
                    $html.="</div>\n";
                $html.="</div>\n"; 
           }
            
        }
        $html.="</div>\n";
       
    }
    $json['html']=$html;
    
    echo json_encode($json);
    dbclose();