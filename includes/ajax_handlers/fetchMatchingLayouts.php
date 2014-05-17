<?php
//this script takes data from jobPressPopup.php to generate a list of possible layouts

include("../functions_db.php");
include("../config.php");
include("../functions_common.php");
global $siteID;
if($_POST)
{
    if($_POST['type']=='recurring'){
        $jobid=$_POST['jobid'];
        $sql="UPDATE jobs_recurring SET layout_id=0 WHERE id='$jobid'";
        $dbUpdate=dbexecutequery($sql);
        $error.=$dbUpdate['error'];
        
    } else {
        $jobid=$_POST['jobid'];
        //going to remove all layout information, plates, pages for this job.
        $sql="DELETE FROM job_pages WHERE job_id='$jobid'";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if($error=='')
        {
            $sql="DELETE FROM job_plates WHERE job_id='$jobid'";
            $dbDelete=dbexecutequery($sql);
            $error.=$dbDelete['error'];
            if($error=='')
            {
                $sql="UPDATE jobs SET layout_id=0 WHERE id='$jobid'";
                $dbUpdate=dbexecutequery($sql);
                $error.=$dbUpdate['error'];
            }
        }
    }
    $json['type']=$_POST['type'];
    if($error!='')
    {
        $json['status']='error';
        $json['message']=$error;
    } else {
        $json['status']='success';
    }
    echo json_encode($json);
    die();  
} else {
    $need=0; //number of needed sections
    $need_1=$_GET['s1need'];
    $section1_lowpage=$_GET['s1low'];
    $section1_highpage=$_GET['s1high'];
    $section1_doubletruck=$_GET['s1double'];
    $section1_producttype=$_GET['s1format'];
    $section1_leadtype=$_GET['s1lead'];
    if ($need_1){$need++;}
    $need_2=$_GET['s2need'];
    $section2_lowpage=$_GET['s2low'];
    $section2_highpage=$_GET['s2high'];
    $section2_doubletruck=$_GET['s2double'];
    $section2_producttype=$_GET['s2format'];
    $section2_leadtype=$_GET['s2lead'];
    if ($need_2){$need++;}
    //print "s2low=$section2_lowpage s2high=$section2_highpage s2d=$section2_doubletruck s2prod=$section2_producttype s2lead=$section2_leadtype\n";

    $need_3=$_GET['s3need'];
    $section3_lowpage=$_GET['s3low'];
    $section3_highpage=$_GET['s3high'];
    $section3_doubletruck=$_GET['s3double'];
    $section3_producttype=$_GET['s3format'];
    $section3_leadtype=$_GET['s3lead'];
    if ($need_3){$need++;}

    //ok, now lets find press layout that may match
    //this may take awhile if the number of press layouts grows large
    $layoutsql="SELECT * FROM layout WHERE site_id=$siteID AND available=1 ORDER BY layout_name";
    $dbLayouts=dbselectmulti($layoutsql);

    $displaylayouts=array();
    if ($dbLayouts['numrows']>0)
    {
        //if ($need_1){print "Need section 1\n";}
        //if ($need_2){print "Need section 2\n";}
        //if ($need_3){print "Need section 3\n";}
        
        //loop through the layouts
        foreach ($dbLayouts['data'] as $layout)
        {
            //ok grab sections corresponding to this layout
            $sectionsql="SELECT * FROM layout_sections WHERE layout_id=$layout[id]";
            $dbSections=dbselectmulti($sectionsql);
            if ($dbSections['numrows']>0)
            {
                $found_1=false;
                $found_2=false;
                $found_3=false;
                $sec=$dbSections['numrows'];
                foreach ($dbSections['data'] as $section)
                {
                    //this will end up being the highest number section, which we can compare against for # of sections
                   // $sec=$section['section_number'];
                    
                    //print "Checking against: ";
                    //print_r($section);
                    //print "<br><br>\n";
                    if ($section['section_number']==1 && $section['product_type']==$section1_producttype && $section['lead_type']==$section1_leadtype && $section['doubletruck']>=$section1_doubletruck
                    && $section['low_page']==$section1_lowpage && $section['high_page']==$section1_highpage && $section1_lowpage<>0 && $section1_highpage<>0)
                    {
                        //print "Found a match for section 1<br>";
                        $found_1=true;
                    }
                    if ($section['section_number']==2 && $section['product_type']==$section2_producttype && $section['lead_type']==$section2_leadtype && $section['doubletruck']>=$section2_doubletruck
                    && $section['low_page']==$section2_lowpage && $section['high_page']==$section2_highpage && $section2_lowpage<>0 && $section2_highpage<>0)
                    {
                        //print "Found a match for section 2<br>";
                        $found_2=true;
                    }
                    if ($section['section_number']==3 && $section['product_type']==$section3_producttype && $section['lead_type']==$section3_leadtype && $section['doubletruck']>=$section3_doubletruck
                    && $section['low_page']==$section3_lowpage && $section['high_page']==$section3_highpage && $section3_lowpage<>0 && $section3_highpage<>0)
                    {
                        //print "Found a match for section 3<br>";
                        $found_3=true;
                    }
                }    
            
            }
            if ($sec==$need)
            {
                if ($need_1==$found_1 && $need_2==$found_2 && $need_3==$found_3)
                {
                    if ($layout['preferred']==1){
                        $name=$layout['layout_name'].' - preferred';
                    } else {
                        $name=$layout['layout_name'];
                    }
                    $displaylayouts[$layout['id']]=$name;
                    
                }
            } //else we have more sections than we need in this layout
        }

        if (count($displaylayouts)>0)
        {
            $json[]='{"id" : "0", "label" : "Please choose layout"}';
            foreach ($displaylayouts as $lid=>$name)
            {
                //print "obj.options[obj.options.length] = new Option('$name','$lid');\n";
                $json[] = '{"id" : "' . $lid . '", "label" : "' . $name . '"}';
            }
        } else {
            $json[]='{"id" : "0", "label" : "No matches found!"}';
        }   
    } else {
            $json[]='{"id" : "0", "label" : "No layouts found!"}';
            
    }
    echo '[' . implode(',', $json) . ']';
}
dbclose();