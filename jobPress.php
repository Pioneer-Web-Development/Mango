<?php
//<!--VERSION: .91 **||**-->
include("includes/mainmenu.php") ;
if ($_GET['action']=='printpressdata'){
    $onload="onload='window.print();'";
}
print "<body $onload>\n";
print "<div id='wrapper'>\n";

//this script will be used to set daily draw for recurring jobs, or to book jobs
//that will need to be scheduled to appear on the press schedule

if ($_POST)
{
    $action=$_POST['submit'];
    if ($_POST['jssubmit']=='formsubmit')
    {
        save_pressdata();
    }
} else {
    $action=$_GET['action'];
}

 
switch ($action)
{
    case "setcolor":
    color();
    break;
    
    case "setdraw":
    changeDraw();
    break;
    
    case "setlayout":
    layout();
    break;
    
    case "schedulejob":
    schedule();
    break;
    
    case "addjob":
    jobs('add');
    break;
    
    case "editjob":
    jobs('edit');
    break;
    
    case "deletejob":
    check_delete();
    break;
    
    case "whodid":
    if (in_array(1,$_SESSION['cmsuser']['permissions']))
    {
       whodid(); 
    } else {
       jobs('list');
    }
    break;
    
    case "Select this layout":
    save_layout();
    break;
    
    case "Confirm Delete":
    delete_job();
    break;
    
    case "Set Color":
    save_color();
    break;
    
    case "Save Schedule":
    save_schedule();
    break;
    
    case "Change Draw":
    save_draw();
    break;
    
    case "Save Job":
    save_job('insert');
    break;
    
    case "Update Job":
    save_job('update');
    break;
    
    case "pressdata":
    pressdata();
    break;
    
    case "printpressdata":
    print_pressdata();
    break;
    
    case "redojob":
    redo_job_setup();
    break;
    
    case "Process Reprint":
    redo_job_save();
    break;
    
    default:
    show_jobs();
    break;
}

function redo_job_setup()
{
    $jobid=$_GET['jobid'];
    
    //see if there are any other redos
    $sql="SELECT A.*, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.redo_job_id=$jobid AND A.pub_id=B.id AND A.run_id=C.id";
    $dbOthers=dbselectmulti($sql);
    if ($dbOthers['numrows']>0)
    {
        print "<h2>There are some existing reprints for this parent, do you wish to edit their data or create a new reprint job?</h2>\n";
        foreach($dbOthers['data'] as $other)
        {
            $pubname=stripslashes($other['pub_name']);    
            $runname=stripslashes($other['run_name']); 
            $pubdate=date("m/d/Y",strtotime($other['pub_date']));
            $id=$other['id'];
            print "$pubname - $runname for $pubdate <a href='?action=pressdata&jobid=$id'>Edit Job Data</a><br />\n";    
        }
    }
    //get job details
    $sql="SELECT A.*, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.id=$jobid";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    print "<br /><h2>Set up a new reprint for this job</h2>\n";
    print "<form method=post>\n";
    make_number('newdraw',$job['draw'],'How many?','How many copies need to be reprinted?');
    make_textarea('whyreprint','','Why?','Why did we have to re-print this job? Please be specific',70,15);
    make_hidden('jobid',$jobid);
    make_submit('submit','Process Reprint');
    print "</form>\n";
    
    
}

function redo_job_save()
{
    $jobid=$_POST['jobid'];
    $newdraw=$_POST['newdraw'];
    $why=addslashes($_POST['whyreprint']);
    
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    $fields="";
    $values="";
    foreach($job as $key=>$value)
    {
        if ($key=='draw')
        {
            $fields.=$key.",";
            $values.="'".$value."',";
        } elseif ($key=='notes_job')
        {
            $fields.=$key.",";
            $values.="'".$why."<br />".$value."',";
        } elseif($key=='redo_job_id')
        {
            $fields.=$key.",";
            $values.="'".$jobid."',";
        } elseif($key=='insert_source')
        {
            $fields.=$key.",";
            $values.="'autoreprint',";
        } elseif($key=='stats_id')
        {
            $fields.=$key.",";
            $values.="'0',";
        } elseif($key=='id')
        {
            //skip this field!
        } else {
            if ($value=='')
            {
                //skip empties -- this avoids potential problems with null values
            } else {
                $fields.=$key.",";
                $values.="'".$value."',";
            }
        }
    }
    $fields=substr($fields,0,strlen($fields)-1);
    $values=substr($values,0,strlen($values)-1);
    $sql="INSERT INTO jobs ($fields) VALUES ($values)";
    $dbInsert=dbinsertquery($sql);
    if ($dbInsert['error']!='')
    {
        print $dbInsert['error'];
    } else {
        $newid=$dbInsert['numrows'];
        if ($dbInsert['error']=='')
        {
            redirect("?action=pressdata&jobid=$newid");
        }
    }
}

function whodid()
{
    $sql="SELECT id, firstname, lastname FROM users";
    $dbPeople=dbselectmulti($sql);
    $staff=array();
    $staff[0]='Not set';
    $staff[9999]='System Generated';
    if ($dbPeople['numrows']>0)
    {
        foreach($dbPeople['data'] as $person)
        {
            $staff[$person['id']]=$person['firstname']." ".$person['lastname'];
        }   
    } 
    //this function display to the admin who and when each step of the job was completed by
    $jobid=$_GET['jobid'];
    $sql="SELECT A.*, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.id=$jobid AND A.pub_id=B.id AND A.run_id=C.id";
    $dbJob=dbselectsingle($sql);
    if ($dbJob['numrows']>0)
    {
        $job=$dbJob['data'];
        print "<div style='margin-left:30px;margin-top:10px;'>\n";
        $pubdate=date("D, F m Y",strtotime($job['pub_date']));
        print "<p style='font-weight:bold;font-size:16px;'>For ".$job['pub_name']." - ".$job['run_name']." publishing on $pubdate</p>\n";
        
        if ($job['created_time']!='')
        {
            $time=date("m/d/Y \@ H:i",strtotime($job['created_time']));
        } else {
            $time='Not set';
        }
        $by=$staff[$job['created_by']];
        print "<b>Created on:</b>$time<br />\n";
        print "<b>Created by:</b>$by<br /><br />\n";
        
        if ($job['scheduled_time']!='')
        {
            $time=date("m/d/Y \@ H:i",strtotime($job['scheduled_time']));
        } else {
            $time='Not set';
        }
        $by=$staff[$job['scheduled_by']];
        print "<b>Scheduled on:</b>$time<br />\n";
        print "<b>Scheduled by:</b>$by<br /><br />\n";
        
        if ($job['layoutset_time']!='')
        {
            $time=date("m/d/Y \@ H:i",strtotime($job['layoutset_time']));
        } else {
            $time='Not set';
        }
        $by=$staff[$job['layoutset_by']];
        print "<b>Layout set on:</b>$time<br />\n";
        print "<b>Layout set by:</b>$by<br /><br />\n";
        
        if ($job['colorset_time']!='')
        {
            $time=date("m/d/Y \@ H:i",strtotime($job['colorset_time']));
        } else {
            $time='Not set';
        }
        $by=$staff[$job['colorset_by']];
        print "<b>Color set on:</b>$time<br />\n";
        print "<b>Color set by:</b>$by<br /><br />\n";
        
        if ($job['drawset_time']!='')
        {
            $time=date("m/d/Y \@ H:i",strtotime($job['drawset_time']));
        } else {
            $time='Not set';
        }
        $by=$staff[$job['drawset_by']];
        print "<b>Draw set on:</b>$time<br />\n";
        print "<b>Draw set by:</b>$by<br /><br />\n";
        
        if ($job['updated_time']!='')
        {
            $time=date("m/d/Y \@ H:i",strtotime($job['updated_time']));
        } else {
            $time='Not set';
        }
        $by=$staff[$job['updated_by']];
        print "<b>Updated on:</b>$time<br />\n";
        print "<b>Updated by:</b>$by<br /><br />\n";
        
        if ($job['dataset_time']!='')
        {
            $time=date("m/d/Y \@ H:i",strtotime($job['dataset_time']));
        } else {
            $time='Not set';
        }
        $by=$staff[$job['dataset_by']];
        print "<b>Press data entered on:</b>$time<br />\n";
        print "<b>Press data entered by:</b>$by<br /><br />\n";
        
        print "</div>\n";
    } else {
        print "Sorry, that job id no longer exists.";
    }
    
    
}


function color()
{
    //ok, for color we're going to provide a spread of color pages 
    //based on the pages & layout requested earlier
    //get the layout id
    $jobid=intval($_GET['jobid']);
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJob=dbselectsingle($sql);
    $layoutid=$dbJob['data']['layout_id'];
    
    $fullcolors=array('k'=>"K",'c'=>"Full Color",'s'=>"K/Spot");
    $spotcolors=array('k'=>"K",'s'=>"K/Spot");
    
    $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
    $dbJSections=dbselectsingle($sql);
    if ($dbJSections['numrows']>0)
    {
        $sections=$dbJSections['data'];
        $section1_name=$sections['section1_name'];
        $section1_code=$sections['section1_code'];
        $section1_lowpage=$sections['section1_lowpage'];
        $section1_highpage=$sections['section1_highpage'];
        $section1_doubletruck=$sections['section1_doubletruck'];
        $section1_producttype=$sections['section1_producttype'];
        $section1_leadtype=$sections['section1_leadtype'];
        $need_1=$sections['section1_used'];
        
        $section2_name=$sections['section2_name'];
        $section2_code=$sections['section2_code'];
        $section2_lowpage=$sections['section2_lowpage'];
        $section2_highpage=$sections['section2_highpage'];
        $section2_doubletruck=$sections['section2_doubletruck'];
        $section2_producttype=$sections['section2_producttype'];
        $section2_leadtype=$sections['section2_leadtype'];
        $need_2=$sections['section2_used'];
        
        $section3_name=$sections['section3_name'];
        $section3_code=$sections['section3_code'];
        $section3_lowpage=$sections['section3_lowpage'];
        $section3_highpage=$sections['section3_highpage'];
        $section3_doubletruck=$sections['section3_doubletruck'];
        $section3_producttype=$sections['section3_producttype'];
        $section3_leadtype=$sections['section3_leadtype'];
        $need_3=$sections['section3_used'];
        
        
        
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
            print "<div style='border: 1px solid black;padding:2px;margin-bottom:10px;'>\n";
            print "<div style='margin-bottom:6px;padding:2px;background-color:black;color:white;font-weight:bold;font-size:14px;'>\n";
            print "Chosen layout: $name - $notes<br>";
            print "</div>\n";
            print "<button onclick='setAll(\"black\");'>Set all to B/W</button> ";
            print "<button onclick='setAll(\"color\");'>Set all to Color</button><br />";
            print "<form id='settingColor' method=post>\n";
            make_hidden('layout_id',$layoutid);
            make_hidden('job_id',$jobid);
            //lets build a list of pages for this layout and indicate color/bw
            //based on press configuration
            //do it 1 section at a time
            if ($need_1)
            {
                print "<div id='section1_$lid'>\n";
                print "<b>Section 1: $section1_name - $section1_code</b><br>\n";
                $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='$section1_code' ORDER BY page_number ASC";
                $dbPages=dbselectmulti($sql);
                if ($dbPages['numrows']>0)
                {
                    foreach($dbPages['data'] as $page)
                    {
                        print "<div style='width:80px;height:60px;float:left;border:thin solid black;padding:3px;'>\n";
                        print "<p style='margin:0;text-align:center;font-weight:bold;font-size:14px'>$page[page_number]<br>\n";
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
                            print input_select('pageid_'.$page['id'],$fullcolors[$pcolor],$fullcolors);
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
                            print input_select('pageid_'.$page['id'],$spotcolors[$pcolor],$spotcolors);
                        } else {
                            make_hidden('pageid_'.$page['id'],'0');
                            print 'k';
                        }
                        print "</p>\n";
                        print "</div>\n";
                    }
                    print "<div class='clear'></div>\n";
                } else {
                    print "No pages defined for this section.";
                }
                print "</div>\n";
            }
           if ($need_2)
            {
                print "<div id='section2_$lid' >\n";
                print "<b>Section 2: $section2_name - $section2_code</b><br>\n";
                $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='$section2_code' ORDER BY page_number ASC";
                //print "Layout select sql is $sql<br>";
                $dbPages=dbselectmulti($sql);
                if ($dbPages['numrows']>0)
                {
                    foreach($dbPages['data'] as $page)
                    {
                        print "<div style='width:80px;height:60px;float:left;border:thin solid black;padding:3px;'>\n";
                        print "<p style='margin:0;text-align:center;font-weight:bold;font-size:14px'>$page[page_number]<br>\n";
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
                            print input_select('pageid_'.$page['id'],$fullcolors[$pcolor],$fullcolors);
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
                            print input_select('pageid_'.$page['id'],$spotcolors[$pcolor],$spotcolors);
                        } else {
                            make_hidden('pageid_'.$page['id'],'0');
                            print 'k';
                        }
                        print "</p>\n";
                        print "</div>\n";
                    }
                    print "<div class='clear'></div>\n";
                } else {
                    print "No pages defined for this section.";
                }
                print "</div>\n";
            }
           if ($need_3)
            {
                print "<div id='section3_$lid'>\n";
                print "<b>Section 3: $section3_name - $section3_code</b><br>\n";
                $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='$section3_code' ORDER BY page_number ASC";
                //print "Layout select sql is $sql<br>";
           
                $dbPages=dbselectmulti($sql);
                if ($dbPages['numrows']>0)
                {
                    foreach($dbPages['data'] as $page)
                    {
                        print "<div style='width:80px;height:60px;float:left;border:thin solid black;padding:3px;'>\n";
                        print "<p style='margin:0;text-align:center;font-weight:bold;font-size:14px'>$page[page_number]<br>\n";
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
                            print input_select('pageid_'.$page['id'],$fullcolors[$pcolor],$fullcolors);
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
                            print input_select('pageid_'.$page['id'],$spotcolors[$pcolor],$spotcolors);
                        } else {
                            make_hidden('pageid_'.$page['id'],'0');
                            print 'k';
                        }
                        print "</p>\n";
                        print "</div>\n";
                    }
                    print "<div class='clear'></div>\n";
                } else {
                    print "No pages defined for this section.";
                }
                print "</div>\n";
            }
            make_hidden('jobid',$jobid);
            make_submit('submit','Set Color','Select');
            print "</form>\n";
            
            print "</div>\n";
            ?>
            <script>
            function setAll(stat)
            {
                if(stat=='black')
                {
                    $('#settingColor select').val('k');
                }
                if(stat=='color')
                {
                    $('#settingColor select').val('c');
                }
            }
            </script>
            <?php
        } else {
            print "Sorry, you need to specify a press layout first.";
        }    
    } else {
        print "No sections have been set up for this job yet. <a href='?action=editjob&jobid=$_GET[jobid]'>Click this link to set up the sections and pages</a>\n";
    }
       
}

function save_color()
{
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,6)=="pageid")
        {
            $pageid=str_replace("pageid_","",$key);
            $value=$_POST[$key];
            switch($value)
            {
                case "k":
                //black page;
                $sql="UPDATE job_pages SET color=0, spot=0 WHERE id=$pageid";
                break;
                
                case "c":
                //full color
                $sql="UPDATE job_pages SET color=1, spot=0 WHERE id=$pageid";
                break;
                
                case "s":
                //spot color
                $sql="UPDATE job_pages SET color=0, spot=1 WHERE id=$pageid";
                break;
            }
            $dbUpdate=dbexecutequery($sql);
        }    
        
    }
    $jobid=$_POST['jobid'];
    $colortime=date("Y-m-d H:i:s");
    $colorby=$_SESSION['cmsuser']['userid'];
    $sql="UPDATE jobs SET colorset_time='$colortime', colorset_by='$colorby' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql); 
    $error=$dbUpdate['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem saving the color for this job.<br />'.$error,'error');
    } else {
        setUserMessage('The color for this job has been successfully saved.','success');
    }
    redirect("?action=list");  
}

function layout()
{
    //ok, for color we're going to provide a spread of color pages for differing sections
    //based on the pages requested earlier
    global $siteID;
    $colors=array("K","Full Color");
    $jobid=intval($_GET['jobid']);
    $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
    $dbJSections=dbselectsingle($sql);
    if ($dbJSections['numrows']>0)
    {
        $need=0; //number of needed sections
        $need_1=false;
        $need_2=false;
        $need_3=false;
        
        $sections=$dbJSections['data'];
        $section1_name=$sections['section1_name'];
        $section1_code=$sections['section1_code'];
        $section1_lowpage=$sections['section1_lowpage'];
        $section1_highpage=$sections['section1_highpage'];
        $section1_doubletruck=$sections['section1_doubletruck'];
        $section1_producttype=$sections['section1_producttype'];
        $section1_leadtype=$sections['section1_leadtype'];
        if ($sections['section1_used']){$need++;$need_1=true;}
        
        $section2_name=$sections['section2_name'];
        $section2_code=$sections['section2_code'];
        $section2_lowpage=$sections['section2_lowpage'];
        $section2_highpage=$sections['section2_highpage'];
        $section2_doubletruck=$sections['section2_doubletruck'];
        $section2_producttype=$sections['section2_producttype'];
        $section2_leadtype=$sections['section2_leadtype'];
        if ($sections['section2_used']){$need++;$need_2=true;}
        
        $section3_name=$sections['section3_name'];
        $section3_code=$sections['section3_code'];
        $section3_lowpage=$sections['section3_lowpage'];
        $section3_highpage=$sections['section3_highpage'];
        $section3_doubletruck=$sections['section3_doubletruck'];
        $section3_producttype=$sections['section3_producttype'];
        $section3_leadtype=$sections['section3_leadtype'];
        if ($sections['section3_used']){$need++;$need_3=true;}
        
        //ok, now lets find press layout that may match
        //this may take awhile if the number of press layouts grows large
        $layoutsql="SELECT * FROM layout WHERE available=1 ORDER BY layout_name";
        $dbLayouts=dbselectmulti($layoutsql,true);
        $displaylayouts=array();
        //print "Need $need sections<br />";
        if ($dbLayouts['numrows']>0)
        {
            if($GLOBALS['debug']){
                if ($need_1){print "Need section 1<br>\n";}
                if ($need_2){print "Need section 2<br>\n";}
                if ($need_3){print "Need section 3<br>\n";}
                print "A total of $dbLayout[numrows] layout are available<br />";
            }
            
            
            //loop through the layouts
            foreach ($dbLayouts['data'] as $layout)
            {
                //ok grab sections corresponding to this layout
                if($GLOBALS['debug']){
                    print "Working on layout id: $layout[id]<br />\n";
                }
                $sectionsql="SELECT * FROM layout_sections WHERE layout_id=$layout[id]";
                $dbSections=dbselectmulti($sectionsql);
                $sec=0;
                if ($dbSections['numrows']>0)
                {
                    $found_1=false;
                    $found_2=false;
                    $found_3=false;
                    $sec=$dbSections['numrows'];
                    foreach ($dbSections['data'] as $section)
                    {
                        //this will end up being the highest number section, which we can compare against for # of sections
                        
                        if($GLOBALS['debug']){
                            if($layout['id']==69){
                                print "<h3>This is the one it should find</h3>\n";
                            }
                            print "Checking against: <pre> ";
                            print_r($section);
                            print "</pre><br>\n";
                        }
                        if ($section['section_number']==1 && $section['product_type']==$section1_producttype && $section['lead_type']==$section1_leadtype && $section['doubletruck']>=$section1_doubletruck
                        && $need_1 && $section['low_page']==$section1_lowpage && $section['high_page']==$section1_highpage && $section1_lowpage<>0 && $section1_highpage<>0)
                        {
                            if($GLOBALS['debug']){
                                print "Found a match for section 1<br>";
                            }
                            $found_1=true;
                        }
                        if ($section['section_number']==2 && $section['product_type']==$section2_producttype && $section['lead_type']==$section2_leadtype && $section['doubletruck']>=$section2_doubletruck
                        && $need_2 && $section['low_page']==$section2_lowpage && $section['high_page']==$section2_highpage && $section2_lowpage<>0 && $section2_highpage<>0)
                        {
                            if($GLOBALS['debug']){
                                print "Found a match for section 2<br>";
                            }
                            $found_2=true;
                        }
                        if ($section['section_number']==3 && $section['product_type']==$section3_producttype && $section['lead_type']==$section3_leadtype && $section['doubletruck']>=$section3_doubletruck
                        && $need_3 && $section['low_page']==$section3_lowpage && $section['high_page']==$section3_highpage && $section3_lowpage<>0 && $section3_highpage<>0)
                        {
                            if($GLOBALS['debug']){
                                print "Found a match for section 3<br>";
                            }
                            $found_3=true;
                        }
                    }    
                
                }
                
                //ok, lets see if we have a winner
                /*
                if ($need_1==$found_1 && $need_1){print "Needed and found section 1<br>";}
                if ($need_2==$found_2 && $need_2){print "Needed and found section 2<br>";}
                if ($need_3==$found_3 && $need_3){print "Needed and found section 3<br>";}
                */
                //first check if the number of sections for this layout are more than we need
                //print "For this layout, we need $need sections and this layout has $sec sections<br />\n";
                if ($sec==$need)
                {
                    if ($need_1==$found_1 && $need_2==$found_2 && $need_3==$found_3)
                    {
                        $displaylayouts[$layout['id']]=$layout['layout_name'];
                        if ($layout['preferred']==1){
                           $displaynotes[$layout['id']]="Preferred - ".$layout['layout_notes']; 
                        } else {
                           $displaynotes[$layout['id']]=$layout['layout_notes'];
                        }
                        
                    }
                } //else we have more sections than we need in this layout
            }
        
            if (count($displaylayouts)>0)
            {
                foreach ($displaylayouts as $lid=>$name)
                {
                    print "<div style='border: 1px solid black;padding:2px;margin-bottom:10px;'>\n";
                    print "<div style='margin-bottom:6px;padding:2px;background-color:black;color:white;font-weight:bold;font-size:14px;'>\n";
                    $notes=$displaynotes[$lid];
                    print "Select layout: $name - $notes<br>";
                    print "</div>\n";
                    print "<form method=post>\n";
                    make_hidden('layout_id',$lid);
                    make_hidden('job_id',$jobid);
                    //lets build a list of pages for this layout and indicate color/bw
                    //based on press configuration
                    //do it 1 section at a time
                    if ($need_1)
                    {
                        print "<div id='section1_$lid'>\n";
                        make_hidden('section1','1');
                        make_hidden('section1_name',$section1_name);
                        make_hidden('section1_code',$section1_code);
                        print "<b>Section 1: $section1_name - $section1_code</b><br>\n";
                        $sql="SELECT * FROM layout_sections WHERE layout_id=$lid AND section_number=1";
                        //print "Layout select sql is $sql<br>";
                        $dbLayout=dbselectsingle($sql);
                        $layout=$dbLayout['data'];
                        $towers=str_replace("|",",",$layout['towers']);
                        $sql="SELECT A.page_number, B.color_config, B.tower_name FROM layout_page_config A, press_towers B WHERE A.page_number>0 AND A.layout_id=$lid AND A.tower_id=B.id AND B.id IN ($towers) ORDER BY A.page_number ASC";
                        $dbPages=dbselectmulti($sql);
                        $highpage=$dbPages['data'][0]['page_number'];
                        make_hidden('s1_low',$highpage);
                        foreach($dbPages['data'] as $page)
                        {
                            print "<div style='width:80px;height:60px;float:left;border:thin solid black;padding:3px;'>\n";
                            print "<p style='margin:0;text-align:center;font-weight:bold;font-size:14px'>$page[page_number]</p>\n";
                            print "<p style='text-align:center;font-weight:bold;font-size:10px'>$page[tower_name]<br>";
                            if ($page['color_config']!='K')
                            {
                                //print input_select('s1_'.$page['page_number'],$colors[1],$colors);
                                print "CMYK";
                            } else {
                                make_hidden('s1_'.$page['page_number'],'0');
                                print 'K';
                            }
                            print "</p>\n";
                            if ($page['page_number']>$highpage){$highpage=$page['page_number'];}
                            print "</div>\n";
                        }
                        make_hidden('s1_high',$highpage);
                        print "<div class='clear'></div>\n";
                        print "</div>\n";
                    }
                    
                    if ($need_2)
                    {
                        print "<div id='section2_$lid' >\n";
                        make_hidden('section2','1');
                        make_hidden('section2_name',$section2_name);
                        make_hidden('section2_code',$section2_code);
                        print "<b>Section 2: $section2_name - $section2_code</b><br>\n";
                        $sql="SELECT * FROM layout_sections WHERE layout_id=$lid AND section_number=2";
                        //print "Layout select sql is $sql<br>";
                        $dbLayout=dbselectsingle($sql);
                        $layout=$dbLayout['data'];
                        $towers=str_replace("|",",",$layout['towers']);
                        $sql="SELECT A.page_number, B.color_config, B.tower_name FROM layout_page_config A, press_towers B WHERE A.page_number>0 AND A.layout_id=$lid AND A.tower_id=B.id AND B.id IN ($towers) ORDER BY A.page_number ASC";
                        $dbPages=dbselectmulti($sql);
                        $highpage=$dbPages['data'][0]['page_number'];
                        make_hidden('s2_low',$highpage);
                        foreach($dbPages['data'] as $page)
                        {
                            print "<div style='width:80px;height:60px;float:left;border:thin solid black;padding:3px;'>\n";
                            print "<p style='margin:0;text-align:center;font-weight:bold;font-size:14px'>$page[page_number]</p>\n";
                            print "<p style='text-align:center;font-weight:bold;font-size:10px'>$page[tower_name]<br>\n";
                            if ($page['color_config']!='K')
                            {
                                //print input_select('s2_'.$page['page_number'],$colors[1],$colors);
                                print "CMYK";
                            } else {
                                make_hidden('s2_'.$page['page_number'],'0');
                                print 'K';
                            }
                            print "</p>\n";
                            if ($page['page_number']>$highpage){$highpage=$page['page_number'];}
                            print "</div>\n";
                        }
                        make_hidden('s2_high',$highpage);
                        print "<div class='clear'></div>\n";
                        print "</div>\n";
                    }
                    
                    if ($need_3)
                    {
                        print "<div id='section3_$lid' >\n";
                        make_hidden('section3','1');
                        make_hidden('section3_name',$section3_name);
                        make_hidden('section3_code',$section3_code);
                        print "<b>Section 3: $section3_name - $section3_code</b><br>\n";
                        $sql="SELECT * FROM layout_sections WHERE layout_id=$lid AND section_number=3";
                        //print "Layout select sql is $sql<br>";
                        $dbLayout=dbselectsingle($sql);
                        $layout=$dbLayout['data'];
                        $towers=str_replace("|",",",$layout['towers']);
                        $sql="SELECT A.page_number, B.color_config, B.tower_name FROM layout_page_config A, press_towers B WHERE A.page_number>0 AND A.layout_id=$lid AND A.tower_id=B.id AND B.id IN ($towers) ORDER BY A.page_number ASC";
                        $dbPages=dbselectmulti($sql);
                        $highpage=$dbPages['data'][0]['page_number'];
                        make_hidden('s3_low',$highpage);
                        foreach($dbPages['data'] as $page)
                        {
                            print "<div style='width:80px;height:60px;float:left;border:thin solid black;padding:3px;'>\n";
                            print "<p style='margin:0;text-align:center;font-weight:bold;font-size:14px'>$page[page_number]</p>\n";
                            print "<p style='text-align:center;font-weight:bold;font-size:10px'>$page[tower_name]<br>\n";
                            if ($page['color_config']!='K')
                            {
                               // print input_select('s3_'.$page['page_number'],$page['color_config'],$GLOBALS['colorconfigs']);
                                //print input_select('s3_'.$page['page_number'],$colors[1],$colors);
                                print "CMYK";
                            } else {
                                make_hidden('s3_'.$page['page_number'],'0');
                                print 'K';
                            }
                            print "</p>\n";
                            if ($page['page_number']>$highpage){$highpage=$page['page_number'];}
                            print "</div>\n";
                        }
                        make_hidden('s3_high',$highpage);
                        print "<div class='clear'></div>\n";
                    }
                    print "</div>\n";
                    print "<div class='clear'></div>\n";
                    make_submit('submit','Select this layout','Select');
                    print "</form>\n";
                    print "<div class='clear'></div>\n";
                    print "</div>\n";
                    
                }
            } else {
                displayMessage("Sorry, there are no matching press layouts for the section/page configuration you specified.<br>Please check your configuration and check with the production staff.<br />Some reasons for not finding a match are things like gatefolds and doubletrucks. If you dont need them, dont specify them. Also, for tabs, you do not need to specify a doubletruck position, as it is a given.<br><a href='?action=list'>Return to job list</a>",'error','true',"Delete");
                
            }    
        } else {
            print "No sections have been set up for this job yet. <a href='?action=editjob&jobid=$_GET[jobid]'>Click this link to set up the sections and pages</a>\n";
        }
    }
       
}

function save_layout()
{
    $layoutid=$_POST['layout_id'];
    $jobid=$_POST['job_id'];
    global $siteID;
    
    
    //now, lets create the plates for this job
    //we'll need to get the pub code for the publication
    //also, pub date and section codes for all sections
    $jobsql="SELECT A.*, B.pub_code FROM jobs A, publications B WHERE A.id=$jobid AND A.pub_id=B.id";
    //print "Job select sql:<br>$jobsql<br>";
    $dbJob=dbselectsingle($jobsql);
    $job=$dbJob['data'];

    //get some info about the run selected
    $sql="SELECT * FROM publications_runs WHERE id=$job[run_id]";
    $dbRun=dbselectsingle($sql);
    $runInfo=$dbRun['data'];
    $productcode=$runInfo['run_productcode'];
    
    $pubcode=$job['pub_code'];
    $pubdate=date("Y-md",strtotime($job['pub_date']));
    $pubid=$job['pub_id'];
    
    //we're actually build pages and plates at this time
    //existing pages/plates will be located first, in case it's just a color update
    $layouttime=date("Y-m-d H:i");
    $layoutby=$_SESSION['cmsuser']['userid'];
    $sql="UPDATE jobs SET layout_id=$layoutid, layoutset_time='$layouttime', layoutset_by='$layoutby' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql);
    
    

    $jobsection="SELECT * FROM jobs_sections WHERE job_id=$jobid";
    //print "Job section sql:<br>$jobsection<br>";
    $dbJSection=dbselectsingle($jobsection);
    $jsection=$dbJSection['data'];
    $scode[1]=$jsection['section1_code'];
    $scode[2]=$jsection['section2_code'];
    $scode[3]=$jsection['section3_code'];
    
    //now get layout sections
    $lsql="SELECT * FROM layout_sections WHERE layout_id=$layoutid";
    //print "Job layout sections sql:<br>$lsql<br>";
    $dbLSections=dbselectmulti($lsql);


    //first, delete any potential existing job plates and pages
    $sql="DELETE FROM job_pages WHERE job_id=$jobid";
    $dbDelete=dbexecutequery($sql);
    $sql="DELETE FROM job_plates WHERE job_id=$jobid";
    $dbDelete=dbexecutequery($sql);
    $colorconfigs=$GLOBALS['colorconfigs'];
    if ($dbLSections['numrows']>0)
    {
        foreach ($dbLSections['data'] as $lsection)
        {
            
            $section_number=$lsection['section_number'];
            $towers=$lsection['towers'];
            $towers=explode("|",$towers);
            foreach ($towers as $tower)
            {
                $created=date("Y-m-d H:i:s");
                //lets look up the color for a tower
                $sql="SELECT color_config FROM press_towers WHERE id=$tower";
                //print "Color config: $sql<br>";
                $dbColor=dbselectsingle($sql);
                if ($dbColor['numrows']>0)
                {
                    if ($dbColor['data']['color_config']=='K')
                    {
                        $color=0;
                        $spot=0;
                        $possiblecolor=0;
                        $possiblespot=0;
                    }else if ($dbColor['data']['color_config']=='K/S'){
                        $color=0;
                        $spot=1;
                        $possiblecolor=0;
                        $possiblespot=1;
                    }else{
                        $color=1;
                        $spot=0;
                        $possiblecolor=1;
                        $possiblespot=1;
                    }
                    //print "&nbsp;&nbsp;&nbsp;&nbsp;Checking color: ".$dbColor['data']['color_config']."<br>";
                    $tcolor=array_search($dbColor['data']['color_config'],$colorconfigs,true);
                } else {
                    $color=0;
                    $possiblecolor=0;
                    $tcolor=0;
                }
                
                $plate1="";
                $plate2="";
                $pages1=array();
                $pages2=array();
                $lowpage1=9999; //set arbitrarily high so it gets set immediately to the new page
                $lowpage2=9999; //set arbitrarily high so it gets set immediately to the new page
                //now we need the pages for this layout & tower -- 10 side, then 13 side
                $psql="SELECT * FROM layout_page_config WHERE layout_id=$layoutid AND tower_id=$tower";
                //print "Layout page config: $psql<br>";
                $dbPages=dbselectmulti($psql);
                if ($dbPages['numrows']>0)
                {
                    foreach ($dbPages['data'] as $page)
                    {
                        $side=$page['side'];
                        $page_num=$page['page_number'];
                        if ($page_num!=0)
                        {
                            if ($side==10)
                            {
                                if ($page_num<$lowpage1 && $page_num!=0){$lowpage1=$page_num;}
                                $tpage="$pubid, $jobid,'$scode[$section_number]','$productcode', '$pubcode','$pubdate',$color,$spot,$possiblecolor,$possiblespot,$tower, $tcolor,$page_num, 1,'$created', 1, '$siteID'),";
                                print "&nbsp;&nbsp;&nbsp;Adding with $tpage<br>";
                                $pages1[]=$tpage;
                            } else {
                                if ($page_num<$lowpage2 && $page_num!=0){$lowpage2=$page_num;}
                                $tpage="$pubid, $jobid,'$scode[$section_number]','$productcode', '$pubcode','$pubdate',$color,$spot,$possiblecolor,$possiblespot,$tower, $tcolor, $page_num, 1,'$created', 1, '$siteID'),";
                                $pages2[]=$tpage;
                            }
                        }            
                    
                    }
                    //now we should have 2 items, 2 arrays with pages and a low page number for each plate
                    $plate1="INSERT INTO job_plates (pub_id, job_id, section_code, run_productcode, pub_code, pub_date, low_page, color, spot, version, created, current, site_id) VALUES
                    ($pubid,$jobid, '$scode[$section_number]','$productcode', '$pubcode', '$pubdate','$lowpage1',$color,$spot, 1,'$created', 1, '$siteID')";
                    $dbPlate1=dbinsertquery($plate1);                                             
                    //print "Plate save 1 sql:<br>$plate1<br>";

                    $plate1ID=$dbPlate1['numrows'];
                    $plate2="INSERT INTO job_plates (pub_id, job_id, section_code, run_productcode, pub_code, pub_date, low_page, color, spot, version, created, current, site_id) VALUES
                    ($pubid,$jobid, '$scode[$section_number]','$productcode', '$pubcode', '$pubdate','$lowpage2',$color,$spot, 1,'$created', 1, '$siteID')";
                    $dbPlate2=dbinsertquery($plate2);
                    $plate2ID=$dbPlate2['numrows'];
                    //print "Plate save 2 sql:<br>$plate2<br>";

                    //now insert the pages
                    $values1="";
                    foreach($pages1 as $page)
                    {
                        $values1.="($plate1ID,$page";    
                    }
                    $values1=substr($values1,0,strlen($values1)-1);
                    $page1="INSERT INTO job_pages (plate_id, pub_id, job_id, section_code, run_productcode, pub_code, pub_date, color, spot, possiblecolor, possiblespot, tower_id, tower_color, page_number, version, created, current, site_id) VALUES $values1";
                    $dbPage1=dbinsertquery($page1);
                    //print "Page save 1 sql:<br>$page1<br>";

                    //now insert the pages
                    $values2="";
                    foreach($pages2 as $page)
                    {
                        $values2.="($plate2ID,$page";    
                    }
                    $values2=substr($values2,0,strlen($values2)-1);
                    $page2="INSERT INTO job_pages (plate_id, pub_id, job_id, section_code, run_productcode, pub_code, pub_date, color, spot, possiblecolor, possiblespot, tower_id, tower_color, page_number, version, created, current, site_id) VALUES $values2";
                    $dbPage2=dbinsertquery($page2);
                    //print "Page save 2 sql:<br>$page2<br>";

                }
            }
         }
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the layout.<br />'.$error,'error');
    } else {
        setUserMessage('The layout has been successfully saved.','success');
    }
    redirect("?action=list"); 
}

function schedule()
{
    $jobid=$_GET['jobid'];
    $sql="SELECT pub_id, run_id, draw, startdatetime, pub_date, request_printdate FROM jobs WHERE id=$jobid";
    $dbInfo=dbselectsingle($sql);
    $info=$dbInfo['data'];
    $pubdate=date("D m/d/Y",strtotime($info['pub_date']));
    $requestPrintdate=date("D m/d/Y",strtotime($info['request_printdate']));
    $draw=$info['draw'];
    $pubid=$info['pub_id'];
    $runid=$info['run_id'];
    if ($info['startdatetime']=='')
    {
        $info['startdatetime']=date("Y-m-d H:i",strtotime($info['pub_date']." -1 day"));
    }
    print "<form method=post>\n";
    
    print "<div class='label'>Request Print Date</div>\n";
    print "<div class='input'>$requestPrintdate</div>\n";
    print "<div class='clear'></div>\n";
    
    print "<div class='label'>Pub date</div>\n";
    print "<div class='input'>$pubdate</div>\n";
    print "<div class='clear'></div>\n";
    print "<div class='label'>Draw Request</div>\n";
    print "<div class='input'>$draw</div>\n";
    print "<div class='clear'></div>\n";
    make_datetime('start',$info['startdatetime'],'Press Start');
    make_hidden('pubid',$pubid);
    make_hidden('runid',$runid);
    make_hidden('jobid',$jobid);
    make_hidden('draw',$draw);
    make_hidden('pubdate',$info['pub_date']);
    make_submit('submit','Save Schedule');
    print "</form>\n";       
}

function save_schedule()
{
    
    global $siteID;
    $startdatetime=$_POST['start'];
    $runtime=$_POST['draw']/($GLOBALS['pressSpeed']/60); //this should give us a number of minutes;
    $runtime=round($runtime,0);
    $runtime+=$GLOBALS['pressSetup'];
    $jobid=$_POST['jobid'];
    $pubid=$_POST['pubid'];
    $runid=$_POST['runid'];
    $pubdate=$_POST['pubdate'];
    $stopdatetime=date("Y-m-d H:i",strtotime($startdatetime." +$runtime minutes"));
    //update the regular record with the new scheduled start time
    $scheduledtime=date("Y-m-d H:i:s");
    $scheduledby=$_SESSION['cmsuser']['userid'];
    
    $sql="UPDATE jobs SET scheduled_time='$scheduledtime', scheduled_by='$scheduledby', startdatetime='$startdatetime', enddatetime='$stopdatetime' WHERE id=$jobid";
    $dbUpdate=dbexecutequery($sql);
    $error=$dbUpdate['error'];
    if ($error!='')
    {
        setUserMessage('There was a problem scheduling this job.<br />'.$error,'error');
    } else {
        setUserMessage('This job has been successfully scheduled.','success');
    }
    redirect("?action=list");
    
}


function jobs($action)
{
    global $pubs, $presses, $defaultPressID, $producttypes, $papertypes, $leadtypes, $laps, $siteID, $folders, $folderpins, $jobTypes;
    $runs=array();
    $runs[0]='Please choose';
    $runProductCodes[0]='N/A';
    if ($action=='add' || $action=='edit')
    {
        $jobid=$_GET['jobid'];
        if ($action=='add')
        {
            $button="Save Job";
            $pubid=0;
            $runid=0;
            $insertpubid=0;
            $pubdate=date("Y-m-d",strtotime("+1 day"));
            $section1=0;
            $jobType='newspaper';
            $section1_format=0;
            $section1_low=1;
            $section1_high=2;
            $section1_name='A';
            $section1_letter='A';
            $section1_doubletruck=0;
            $section1_gatefold=0;
            $section1_lead=0;
            $section1_overrun=0;
            
            $section2=0;
            $section2_format=0;
            $section2_low=1;
            $section2_high=2;
            $section2_name='B';
            $section2_letter='B';
            $section2_doubletruck=0;
            $section2_gatefold=0;
            $section2_lead=0;
            $section2_overrun=0;
            
            $section3=0;
            $section3_format=0;
            $section3_low=1;
            $section3_high=2;
            $section3_name='C';
            $section3_letter='C';
            $section3_doubletruck=0;
            $section3_gatefold=0;
            $section3_lead=0;
            $section3_overrun=0;
            
            $rollSize=0;
            
            $drawHD=0;
            $drawSC=0;
            $drawMail=0;
            $drawOffice=0;
            $drawCustomer=0;
            $drawOther=0;
            $drawTotal=0;
            $paperid=$GLOBALS['defaultNewsprintID'];
            $quarterfold=0;
            $lap='lap';
            $pagewidth=$GLOBALS['broadsheetPageWidth'];
            $folder=$GLOBALS['defaultFolder'];
            //tab two items
            $stitch=0;
            $trim=0;
            $folderpin=$GLOBALS['pressDefaultFolderPin'];
            $slitter=$GLOBALS['pressDefaultSlitter'];
            $pressid=$defaultPressID;
            $glossycover=0;
            $glossydraw=0;
            $glossyinside=0;
            $glossyinsidecount=0;
            $coverduedate=date("Y-m-d");
            $coveroutputdate=date("Y-m-d");
            $coverprintdate=date("Y-m-d");
            $pagerelease=date("Y-m-d H:i");
            $pagerip=date("Y-m-d H:i");
            $binderystart=date("Y-m-d");
            $binderydue=date("Y-m-d");
            $printRequestDate=date("Y-m-d");
            $papertypecover=0;
            $requiresDelivery=0;
            $requiresAddressing=0;
            $requiresInserting=0;
        } else {
            $button="Update Job";
            $sql="SELECT * FROM jobs WHERE id=$jobid";
            $dbJob=dbselectsingle($sql);
            $job=$dbJob['data'];
            
            $pubid=$job['pub_id'];
            $insertpubid=$job['insert_pub_id'];
            if($insertpubid==0){$insertpubid=$pubid;}
            if ($pubid!=0)
            {
                //means we have an existing pub, need to pull in runs
                $sql="SELECT id, run_name, run_productcode FROM publications_runs WHERE pub_id=$pubid";
                $dbRuns=dbselectmulti($sql);
                if ($dbRuns['numrows']>0)
                {
                    foreach($dbRuns['data'] as $run)
                    {
                        if($run['run_productcode']!='')
                        {
                            $runs[$run['id']]=stripslashes($run['run_name']).' -- PC: '.$run['run_productcode'];
                        } else {
                            $runs[$run['id']]=stripslashes($run['run_name']);
                        }
                        $runProductCodes[$run['id']]=$run['run_productcode'];
                    }
                }
            }
            $runid=$job['run_id'];
            $jobmessage=stripslashes($job['job_message']);
            $pubdate=$job['pub_date'];
            $drawHD=$job['draw_hd'];
            $drawSC=$job['draw_sc'];
            $drawMail=$job['draw_mail'];
            $drawOffice=$job['draw_office'];
            $drawCustomer=$job['draw_customer'];
            $drawOther=$job['draw_other'];
            $drawTotal=$job['draw'];
            $quarterfold=$job['quarterfold'];
            $lap=$job['lap'];
            $notes=$job['notes_job'];
            $folder=$job['folder'];
            $paperid=$job['papertype'];
            $runspecial=$job['runspecial'];
            $slitter=$job['slitter'];
            $folderpin=$job['folder_pin'];
            $pagewidth=$job['pagewidth'];
            $rollSize=$job['rollSize'];
            if ($pagewidth==0 || $pagewidth=='')
            {
                $pagewidth=$GLOBALS['broadsheetPageWidth'];
            }
            //get section information
            $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
            $dbSection=dbselectsingle($sql);
            if ($dbSection['numrows']>0)
            {
                //ok, at least it looks like we have some section data
                $sections=$dbSection['data'];
                $section1_used=$sections['section1_used'];
                $section1_format=$sections['section1_producttype'];
                $section1_low=$sections['section1_lowpage'];
                $section1_high=$sections['section1_highpage'];
                $section1_name=$sections['section1_name'];
                $section1_letter=$sections['section1_code'];
                $section1_doubletruck=$sections['section1_doubletruck'];
                $section1_gatefold=$sections['section1_gatefold'];
                $section1_lead=$sections['section1_leadtype'];
                $section1_overrun=$sections['section1_overrun'];
                if ($section1_high>=2){$section1=1;}
                
                $section2_used=$sections['section2_used'];
                $section2_format=$sections['section2_producttype'];
                $section2_low=$sections['section2_lowpage'];
                $section2_high=$sections['section2_highpage'];
                $section2_name=$sections['section2_name'];
                $section2_letter=$sections['section2_code'];
                $section2_doubletruck=$sections['section2_doubletruck'];
                $section2_gatefold=$sections['section2_gatefold'];
                $section2_lead=$sections['section2_leadtype'];
                $section2_overrun=$sections['section2_overrun'];
                if ($section2_high>=2){$section2=1;}
                
                $section3_used=$sections['section3_used'];
                $section3_format=$sections['section3_producttype'];
                $section3_low=$sections['section3_lowpage'];
                $section3_high=$sections['section3_highpage'];
                $section3_name=$sections['section3_name'];
                $section3_letter=$sections['section3_code'];
                $section3_doubletruck=$sections['section3_doubletruck'];
                $section3_gatefold=$sections['section3_gatefold'];
                $section3_lead=$sections['section3_leadtype'];
                $section3_overrun=$sections['section3_overrun'];
                if ($section3_high>=2){$section3=1;}
                
            } else {
                $section1=0;
                $section1_used=0;
                $section1_format=0;
                $section1_low=1;
                $section1_high=2;
                $section1_name='A';
                $section1_letter='A';
                $section1_doubletruck=0;
                $section1_gatefold=0;
                $section1_lead=0;
                $section1_overrun=0;
                
                $section2_used=0;
                $section2=0;
                $section2_format=0;
                $section2_low=1;
                $section2_high=2;
                $section2_name='B';
                $section2_letter='B';
                $section2_doubletruck=0;
                $section2_gatefold=0;
                $section2_lead=0;
                $section2_overrun=0;
                
                $section3_used=0;
                $section3=0;
                $section3_format=0;
                $section3_low=1;
                $section3_high=2;
                $section3_name='C';
                $section3_letter='C';
                $section3_doubletruck=0;
                $section3_gatefold=0;
                $section3_lead=0;
                $section3_overrun=0;
            }
            
            
            //tab two items
            $stitch=$job['stitch'];
            $trim=$job['trim'];
            $glossycover=$job['glossy_cover'];
            $glossydraw=$job['glossy_cover_draw'];
            $glossyinside=$job['glossy_insides'];
            $glossyinsidecount=$job['glossy_insides_count'];
            $pressid=$job['press_id'];
            
            $coverduedate=$job['cover_date_due'];
            if ($coverduedate==''){$coverduedate=date("Y-m-d");}
            $coverprintdate=$job['cover_date_print'];
            if ($coverprintdate==''){$coverprintdate=date("Y-m-d");}
            $coveroutputdate=$job['cover_date_output'];
            if ($coveroutputdate==''){$coveroutputdate=date("Y-m-d");}
            
            $printRequestDate=$job['request_printdate'];
            if ($printRequestDate==''){$printRequestDate=date("Y-m-d H:i");}
            $pagerelease=$job['page_release'];
            if ($pagerelease==''){$pagerelease=date("Y-m-d H:i");}
            $pagerip=$job['page_rip'];
            if ($pagerip==''){$pagerip=date("Y-m-d H:i");}
            $binderystart=$job['bindery_startdate'];
            if ($binderystart==''){$binderystart=date("Y-m-d");}
            $binderydue=$job['bindery_duedate'];
            if ($binderydue==''){$binderydue=date("Y-m-d");}
            $deliverynotes=stripslashes($job['notes_delivery']);
            $binderynotes=stripslashes($job['notes_bindery']);
            $papertypecover=$job['papertype_cover'];
            $jobType=$job['job_type'];
            $requiresAddressing=$job['requires_addressing'];
            $requiresDelivery=$job['requires_delivery'];
            $requiresInserting=$job['requires_inserting'];
            $notesInserting=$job['notes_inserting'];
        }
        print "<form method=post>\n";
        print "<div id='tabs'>\n"; //begins wrapper for tabbed content
        
        print "<ul id='pressJobInfo'>\n";
        print "<li><a href='#basicInfo'>Basic Job Information</a></li>\n";   
        print "<li><a href='#binderyPrepressInfo'>Bindery, Delivery &amp; Prepress Information</a></li>\n";   
        print "</ul>\n";
        
        
        print "<div id='basicInfo'>\n";
            if(count($presses)>0 && !array_key_exists(0,$presses))
            {
                make_select('pressid',$presses[$pressid],$presses,'Select Press');
            } else {
                make_hidden('pressid',$pressid);
            }
            make_select('pub_id',$pubs[$pubid],$pubs,'Publication');
            print "<div class='label'>Run</div>\n";
            print "<div class='input'>";
            print input_select('run_id',$runs[$runid],$runs);
            //print "Product code: $runProductCodes[$runid]<br />If your run does not exist in the list please enter it:<br />";
            print "<br />If your run does not exist in the list please enter it:<br />";
            print "Run Name: <input type='text' id='run_special' name='run_special' size=30> Product Code: <input type='text' id='run_special_productcode' name='run_special_productcode' size=5>\n";
            print "</div>\n";
            print '
            <script type="text/javascript">
            $(document).ready(function(){
                if ($("#pub_id").val()==0)
                {
                    $("input[type=submit]").attr("disabled","disabled");
                }
            })
            $("#pub_id").change(function(){
                $("#insertpub_id").val($("#pub_id").val());
                if ($("#pub_id").val()!=0)
                {
                    $("input[type=submit]").removeAttr("disabled");
                } else {
                    $("input[type=submit]").attr("disabled","disabled");
                } 
            })
            $("#insertpub_id").change(function(){
                
            })
            
            $("#pub_id").selectChain({
                target: $("#run_id"),
                type: "post",
                url: "includes/ajax_handlers/fetchRuns.php",
                data: { ajax: true }
            });
            </script>
            ';
            
            print "<div class='clear'></div>\n";
            make_select('insertpub_id',$pubs[$insertpubid],$pubs,'Insert Publication','If this job inserts back into a different publication, please select it here.');
            make_checkbox('createinsert',0,'Create Insert','If checked, this job will be turned into an insert automatically, similar to if the run is set up that way in publications. Only one insert will ever be created per job.');
            make_date('pubdate',$pubdate,'Pub date');
            make_date('request_printdate',$printRequestDate,'Requested Print Date');
                print "<div class='label'>Draw Request</div>\n";
                print "<div class='input'>\n";
                    print "<div id='drawblock'>\n";
                    
                    print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                    print "How many copies for home delivery?";
                        print "</div>\n";
                        print "<div style='float:left;'>\n";
                        print input_text('drawHD',$drawHD,8,false,'calcDraw();','','','return isNumberKey(event);');
                        print "</div>\n";
                        print "<div class='clear'></div>\n";
                        
                        print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                        print "How many copies for single copy/racks?";
                        print "</div>\n";
                        print "<div style='float:left;'>\n";
                        print input_text('drawSC',$drawSC,8,false,'calcDraw();','','','return isNumberKey(event);');
                        print "</div>\n";
                        print "<div class='clear'></div>\n";
                        
                        print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                        print "How many copies for mail delivery?";
                        print "</div>\n";
                        print "<div style='float:left;'>\n";
                        print input_text('drawMail',$drawMail,8,false,'calcDraw();','','','return isNumberKey(event);');
                        print "</div>\n";
                        print "<div class='clear'></div>\n";
                        
                        print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                        print "How many copies for office/tearsheets?";
                        print "</div>\n";
                        print "<div style='float:left;'>\n";
                        print input_text('drawOffice',$drawOffice,8,false,'calcDraw();','','','return isNumberKey(event);');
                        print "</div>\n";
                        print "<div class='clear'></div>\n";
                        
                        print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                        print "How many copies for the customer?";
                        print "</div>\n";
                        print "<div style='float:left;'>\n";
                        print input_text('drawCustomer',$drawCustomer,8,false,'calcDraw();','','','return isNumberKey(event);');
                        print "</div>\n";
                        print "<div class='clear'></div>\n";
                        
                        print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                        print "How many copies for other uses?";
                        print "</div>\n";
                        print "<div style='float:left;'>\n";
                        print input_text('drawOther',$drawOther,8,false,'calcDraw();','','','return isNumberKey(event);');
                        print "</div>\n";
                        print "<div class='clear'></div>\n";
                        
                        print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                        print "<p style='font-size:12px;font-weight:bold;'>Total request:</p>If you manually enter a value here,<br />it will clear the others.";
                        print "</div>\n";
                        print "<div style='float:left;'>\n";
                        print input_text('drawTotal',$drawTotal,8,false,'','','','return isNumberKey(event);','manualDraw();');
                        print "</div>\n";
                        print "<div class='clear'></div>\n";
                    print "</div>\n";
                print "</div>\n";
                print "<div class='clear'></div>\n";
                make_select('folder',$folders[$folder],$folders,'Folder to use');
                make_select('job_type',$jobTypes[$jobType],$jobTypes,'Type of job');
                make_select('newsprint',$papertypes[$paperid],$papertypes,'Type of paper',"<span style='color:red;font-weight:bold;'>Dont just gloss over this, double check it!</span>");
                make_select('papertype_cover',$papertypes[$papertypecover],$papertypes,'Cover paper',"<span style='color:red;font-weight:bold;'>If the outside or one web is on a different paperstock, please select it here.</span>");
                //make_select('pagewidth',$pagewidth,$GLOBALS['sizes'],'Full page width','Size of a full sheet (newspaper broadsheet page equivalent)');
                make_select('pagewidth',$pagewidth,$GLOBALS['sizes'],'Size of a full page','For tabs and tall tabs, this would actually be the total height of the page including margins.<br>For flexis it is the width of a page including margins.<br>Example: 8x10 flexi book. Ends up printed on 34in paper. Individual pages are 8.5in wide with margin. 4-wide on paper');
        
                if($GLOBALS['askForRollSize'])
                {
                    make_select('rollSize',$GLOBALS['sizes'][$rollSize],$GLOBALS['sizes'],'Default Roll Width','Size of a full roll for this job.');
                }
                make_select('lap',$laps[$lap],$laps,'Type of lap');
                make_select('folderpin',$folderpins[$folderPin],$folderpins,'Type of folder setup');
                make_checkbox('slitter',$slitter,'Slitter','Check to set slitter to on');
                make_checkbox('quarterfold',$quarterfold,'Quarterfold','Check to have product quarterfolded');
                print "<div class='label'>Section Configuration</div><div class='input'>";
                print "<div style='float:left;width: 220px;margin-right:10px;'>\n";
                print "<b>Section 1</b><br />\n";
                print input_checkbox('section1_enable',$section1_used,'','','','Check to enable this section');
                print "Name: ".input_text('section1_name',$section1_name,'10',false,'toggleSection(1);')."<br />\n";
                print "Letter: ".input_text('section1_letter',$section1_letter,$GLOBALS['workflowSectionCodeLength'])."<br />\n";
                print "Low page: ".make_number('section1_low',$section1_low)."<br />\n";
                print "High page: ".make_number('section1_high',$section1_high)."<br />\n";
                print "Format: ".input_select('section1_format',$producttypes[$section1_format],$producttypes)."<br />\n";
                print input_checkbox('section1_doubletruck',$section1_doubletruck,'','','','Doubletruck')."<br>";
                print input_checkbox('section1_gatefold',$section1_gatefold,'','','','Gatefold')."<br>";
                print "Lead: ".input_select('section1_lead',$leadtypes[$section1_lead],$leadtypes)."<br />\n";
                print "Section overrun: ".make_number('section1_overrun',$section1_overrun)."<br />\n";
                print "</div>\n";
                
                print "<div style='float:left;width: 220px;margin-right:10px;'>\n";
                print "<b>Section 2</b><br />\n";
                print input_checkbox('section2_enable',$section2_used,'','','','Check to enable this section');
                print "Name: ".input_text('section2_name',$section2_name,'10',false,'toggleSection(2);')."<br />\n";
                print "Letter: ".input_text('section2_letter',$section2_letter,$GLOBALS['workflowSectionCodeLength'])."<br />\n";
                print "Low page: ".make_number('section2_low',$section2_low)."<br />\n";
                print "High page: ".make_number('section2_high',$section2_high)."<br />\n";
                print "Format: ".input_select('section2_format',$producttypes[$section2_format],$producttypes)."<br />\n";
                print input_checkbox('section2_doubletruck',$section2_doubletruck,'','','','Doubletruck')."<br>";
                print input_checkbox('section2_gatefold',$section2_gatefold,'','','','Gatefold')."<br>";
                print "Lead: ".input_select('section2_lead',$leadtypes[$section2_lead],$leadtypes)."<br />\n";
                print "Section overrun: ".make_number('section2_overrun',$section2_overrun)."<br />\n";
                print "</div>\n";
                
                print "<div style='float:left;width: 220px;margin-right:10px;'>\n";
                print "<b>Section 3</b><br />\n";
                print input_checkbox('section3_enable',$section3_used,'','','','Check to enable this section');
                print "Name: ".input_text('section3_name',$section3_name,'10',false,'toggleSection(3);')."<br />\n";
                print "Letter: ".input_text('section3_letter',$section3_letter,$GLOBALS['workflowSectionCodeLength'])."<br />\n";
                print "Low page: ".make_number('section3_low',$section3_low)."<br />\n";
                print "High page: ".make_number('section3_high',$section3_high)."<br />\n";
                print "Format: ".input_select('section3_format',$producttypes[$section3_format],$producttypes)."<br />\n";
                print input_checkbox('section3_doubletruck',$section3_doubletruck,'','','','Doubletruck')."<br>";
                print input_checkbox('section3_gatefold',$section3_gatefold,'','','','Gatefold')."<br>";
                print "Lead: ".input_select('section3_lead',$leadtypes[$section3_lead],$leadtypes)."<br />\n";
                print "Section overrun: ".make_number('section3_overrun',$section3_overrun)."<br />\n";
                print "</div>\n";
                print "<div class='clear'></div>\n";
                
                print "</div>\n";
                print "<div class='clear'></div>\n";
                make_textarea('jobmessage',$jobmessage,'Message','Message to be displayed to pressman','70','4',false);
                make_textarea('notes',$notes,'Notes','Job specific notes','70','10');
                
                make_hidden('jobid',$jobid);
            print "</div>\n";
            
            
            print "<div id='binderyPrepressInfo'>\n";
            print "<fieldset>\n";
            print "<legend>Miscellaneous</legend>\n";
            make_checkbox('requires_addressing',$requiresAddressing,'Requires Addressing','Check if this job needs to have addressing done.');
            make_checkbox('requires_inserting',$requiresInserting,'Will be inserterd','Check if this job will be inserted into another product.');
            make_textarea('notes_inserting',$notesInserting,'Inserting Notes','Any information about inserting','60','10',false);       
            make_checkbox('requires_delivery',$requiresDelivery,'Requires Delivery','Check if this job needs to be delivered.');
            make_textarea('notes_delivery',$deliverynotes,'Delivery Notes','Delivery Instructions','60','10',false);       
            
            print "</fieldset>\n";
            
            print "<fieldset>\n";
            print "<legend>Page Information</legend>\n";
            make_datetime('pagerelease',$pagerelease,'Page Release','Job release time to prepress for output');
            make_datetime('pagerip',$pagerip,'Page Rip','Deadline for prepress to output files');
            print "</fieldset>\n";
            
            print "<fieldset>\n";
            print "<legend>Bindery Information</legend>\n";
            make_checkbox('trim',$trim,'Trim','Check if this job is trimmed.');
            make_checkbox('stitch',$stitch,'Stitch','Check if this job is stitched.');
            make_date('bindery_start',$binderystart,'Bindery Request Start','When should bindery start?');
            make_date('bindery_due',$binderydue,'Bindery Due','When is the bindery due to complete?');
            make_textarea('notes_bindery',$binderynotes,'Bindery Notes','Bindery Instructions','60','10',false);       
            print "</fieldset>\n";
            
            print "<fieldset>\n";
            print "<legend>Glossy Information</legend>\n";
            make_checkbox('glossycover',$glossycover,'Glossy Cover','Check if this job has a glossy cover.');
            make_text('glossydraw',$glossydraw,'Gloss Cover Draw','How many covers (and/or insides) are needed?',10,'',false,'','','','return isNumberKey(event);');
            make_checkbox('glossyinside',$glossyinside,'Glossy Insides','Check if this job has one or more glossy inside sheets.');
            make_text('glossyinsidecount',$glossyinsidecount,'Gloss Inside Pieces','How many glossy inside sheets will there be?',10,'',false,'','','','return isNumberKey(event);');
            make_datetime('coveroutput',$coveroutputdate,'Cover Output','When do we need to output the cover?');
            make_datetime('coverprint',$coverprintdate,'Cover Prints','When will the cover print?');
            make_datetime('coverdue',$coverduedate,'Cover Due by','When do we need the cover back?');
            print "</fieldset>\n";
     
            print "</div>\n";
            
            make_submit('submit',$button,'Save',false,'','submitbutton');
            print "</form>\n";
        print "</div>\n";//ends wrapper for tabbed area
       ?>
    <script type='text/javascript'>
        $('#notes_delivery').change(function(){
            if($('#notes_delivery').val()!='')
            {
                $('#requires_delivery').prop('checked',true);
            } else {
                $('#requires_delivery').prop('checked',false);
            }
        })
        $(function() {
            $( '#tabs' ).tabs();
        });
    </script>
      <?php
    }
}

function check_delete()
{
    print "<form method=post>\n";
    print "<h2>Deleting a job will also delete all pages and plates, all captured information will be lost. If you are sure, click confirm.</h2>\n";
    make_submit('submit','Confirm Delete');
    make_hidden('jobid',$_GET['jobid']);
    print "</form>\n"; 
}

function show_jobs()
{
    global $manageJobsHoursAhead;
    global $pubs, $producttypes, $papertypes, $leadtypes, $laps, $siteID, $folders, $folderpins;
    $runs=array();
    $runs[0]='Please choose';
    
    //lets see which publications this user has access to
    $userid=$_SESSION['cmsuser']['userid'];
    $sql="SELECT * FROM user_publications WHERE user_id=$userid AND value=1";
    $dbPubs=dbselectmulti($sql);
    if ($dbPubs['numrows']>0)
    {
        $pubids="";
        foreach($dbPubs['data'] as $pub)
        {
            $pubids.=$pub['pub_id'].",";
        }
        $pubids=substr($pubids,0,strlen($pubids)-1);
        $pubfilter=" AND A.pub_id IN ($pubids)";
    } else {
        $pubfilter="";
    }
    if($_POST['pub_id']!=0)
    {
        $runs=array();
        $runs[0]="Please choose";
        $sqlpubid=intval($_POST['pub_id']);
        $sql="SELECT * FROM publications_runs WHERE pub_id='$sqlpubid'";
        $dbRuns=dbselectmulti($sql);
        if($dbRuns['numrows']>0)
        {
            foreach($dbRuns['data'] as $run)
            {
                $runs[$run['id']]=stripslashes($run['run_name']);
            }
        }
    }
    //search by pub, pub date
    if ($_POST['submit_search'])
    {
        //clear the user's last session stored sql
        $_SESSION['cmsuser']['last_search_sql']['pressjobs']='';
        if ($_POST['pub_id']!=0)
        {
            $searchpub="AND A.pub_id=$_POST[pub_id]";
            $and="AND";
        }
        if ($_POST['run_id']!=0)
        {
            $searchpub="AND A.run_id=$_POST[run_id]";
            $and="AND";
        }
        if ($_POST['search_status']!=0)
        {
            $and="AND";
            switch($_POST['search_status'])
            {
                case 1:
                    $searchstatus="AND A.startdatetime=''";
                break;
                
                case 2:
                    $searchstatus="AND A.layout_id=0";
                break;
                
                case 3:
                    $searchstatus="AND A.draw=0";
                break;
                
                case 4:
                    $searchstatus="AND A.stats_id=0";
                break;
                
                case 5:
                    $searchstatus="AND A.stats_id<>0";
                break;
            }
        }
        if ($_POST['search_pubdate'])
        {
            $searchpubdate="AND A.pub_date>='$_POST[startpubdate]' AND A.pub_date<='$_POST[stoppubdate]'";
            $startpubdate=$_POST['startpubdate'];
            $stoppubdate=$_POST['stoppubdate'];
            $and="AND";
        } else {
            $startpubdate=date("Y-m-d");
            $stoppubdate=date("Y-m-d",strtotime("+1 week"));
        }
        if ($_POST['search_printdate'])
        {
            $searchprintdate="AND A.startdatetime>='$_POST[startprintdate] 00:01' AND A.startdatetime<='$_POST[stopprintdate] 23:59'";
            $searchreqprintdate="AND A.request_printdate>='$_POST[startprintdate]' AND A.request_printdate<='$_POST[stopprintdate]'";
            $startprintdate=$_POST['startprintdate'];
            $stopprintdate=$_POST['stopprintdate'];
            $nonulls=true;
        } else {
            $startprintdate=date("Y-m-d");
            $stopprintdate=date("Y-m-d",strtotime("+48 hours"));
            $nonulls=false;
        }
        if ($_POST['hideredo'])
        {
            $hideredo="AND A.redo_job_id=0";
        } else {
            $hideredo="";
        }
        if ($_POST['showunscheduled'])
        {
            $searchshowunscheduled="AND A.startdatetime IS Null";
            $unscheduled="OR (A.pub_id=B.id AND A.status<>99 $searchstatus $searchpub $searchpubdate $hideredo $searchshowunscheduled)";
        } else {
            $searchshowunscheduled="";
        }        
    } else {
        $startpubdate=date("Y-m-d");
        $stoppubdate=date("Y-m-d",strtotime("+$manageJobsHoursAhead hours"));
        $startprintdate=date("Y-m-d");
        $stopprintdate=date("Y-m-d",strtotime("+48 hours"));
        //default searchprint
        $searchpubdate="AND A.pub_date>='$startpubdate' AND A.pub_date<='$stoppubdate'";
        $searchprintdate="AND A.startdatetime>='$startprintdate' AND A.enddatetime<='$stopprintdate'";
        $hideredo="AND A.redo_job_id=0";
    }
    $jobstatuses=array("All","Not scheduled","No layout","No draw","Not done","Printed");
    $search="<form method=post>\n";
    $search.="<b>Publication:</b>";
    $search.="<br />".input_select('pub_id',$pubs[$_POST['pub_id']],$pubs);
    $search.="<br /><b>Run:</b>";
    $search.="<br />".input_select('run_id',$runs[$_POST['run_id']],$runs);
    $search.= '
        <script type="text/javascript">
        $("#pub_id").selectChain({
            target: $("#run_id"),
            type: "post",
            url: "includes/ajax_handlers/fetchRuns.php",
            data: { ajax: true, zero:1 }
        });
        </script>
        ';
    $search.="<br />".input_checkbox('search_pubdate',$_POST['search_pubdate'])." search by pub date";
    $search.="<br /><b>From:</b>";
    if($_SESSION['cmsuser']['simpletables']==1)
    {
        $search.="<input name='startpubdate' type='text' value='$startpubdate' />";
    } else {
        $search.=make_date('startpubdate',$startpubdate);
    }
    $search.="<br /><b>To:</b>&nbsp;&nbsp;&nbsp;&nbsp;";
    if($_SESSION['cmsuser']['simpletables']==1)
    {
        $search.="<input name='stoppubdate' type='text' value='$stoppubdate' />";
    } else {
        $search.=make_date('stoppubdate',$stoppubdate);
    }
    
    
    $search.="<br />".input_checkbox('search_printdate',$_POST['search_printdate'])." search by print date";
    $search.="<br /><b>From:</b>";
    if($_SESSION['cmsuser']['simpletables']==1)
    {
        $search.="<input name='startprintdate' type='text' value='$startprintdate' />";
    } else {
        $search.=make_date('startprintdate',$startprintdate);
    }
    $search.="<br /><b>To:</b>&nbsp;&nbsp;&nbsp;&nbsp;";
    if($_SESSION['cmsuser']['simpletables']==1)
    {
        $search.="<input name='stopprintdate' type='text' value='$stopprintdate' />";
    } else {
        $search.=make_date('stopprintdate',$stopprintdate);
    }
    if($_POST)
    {
        if($_POST['hideredo']){$hideredocheck=1;}else{$hideredocheck=0;}
        if($_POST['showunscheduled']){$showunscheduled=1;}else{$showunscheduled=0;}
    } else {
        $hideredocheck=1;
        $showunscheduled=1;
    }
    $search.="<br /><b>Job Status:</b>";
    $search.="<br />".input_select('search_status',$jobstatuses[$_POST['search_status']],$jobstatuses);
    $search.="<br />".input_checkbox('hideredo',$hideredocheck)." Hide reprint jobs\n";
    $search.="<br />".input_checkbox('showunscheduled',$showunscheduled)." Include unscheduled jobs\n";
    $search.="<br /><input type='submit' name='submit_search' value='Search'>\n";
    $search.="</form>\n";
    
    //have to do a fairly broad query here, as we may not have the run name or any sections configured
    if($nonulls)
    {
        $sql="SELECT A.id, A.layout_id, A.stats_id, A.folder, A.draw, A.run_id, A.startdatetime, A.pub_date, B.pub_name FROM jobs A, publications B WHERE (A.pub_id=B.id AND A.status<>99 $searchstatus $searchpub $searchpubdate $searchprintdate $hideredo $pubfilter)";
        if($showunscheduled){
            $sql.=" OR (A.pub_id=B.id AND A.status<>99 
            AND A.startdatetime IS NULL $searchpubdate $searchreqprintdate $searchpub  $pubfilter $hideredo 
            AND A.site_id=$siteID)";
        }
        $sql.=" ORDER BY A.pub_date ASC, A.pub_id";
    } else {
        $sql="SELECT A.id, A.layout_id, A.stats_id, A.folder, A.draw, A.run_id, A.startdatetime, A.pub_date, B.pub_name FROM jobs A, publications B WHERE (A.pub_id=B.id AND A.status<>99 $searchstatus $searchpub $searchpubdate $searchprintdate $hideredo $pubfilter)";
        if($showunscheduled){
            $sql.=" OR (A.pub_id=B.id AND A.status<>99 
            AND A.startdatetime IS NULL $searchpubdate $searchreqprintdate $searchpub $hideredo 
            AND A.site_id=$siteID $pubfilter)";
        }
        $sql.=" ORDER BY A.pub_date ASC, A.pub_id";
    }
    $sql.=" LIMIT 100"; //never return more than 100 jobs
    if ($_POST['submit_search'])
    {
         $_SESSION['cmsuser']['last_search_sql']['pressjobs']=$sql; 
    } else {
        if ($_SESSION['cmsuser']['last_search_sql']['pressjobs']!='')
        {
            $sql=$_SESSION['cmsuser']['last_search_sql']['pressjobs'];
        }
    }
    if($GLOBALS['debug']){ print "Pulling with $sql<br>";}
    $dbJobs=dbselectmulti($sql);
    $days=round($manageJobsHoursAhead/24,0);
    if ($days>1){$days="$days days";}else{$days="$days day";}
    tableStart("<b>Default search is for all jobs publishing in the next $days.</b>,$dbJobs[numrows] jobs matching your search,<a href='?action=addjob'>Add new press job</a>","Pub Name,Pub Date,Run Name,Draw,Sections,Status",10,$search);
    if ($dbJobs['numrows']>0)
    {
        foreach($dbJobs['data'] as $job)
        {
            $jobid=$job['id'];
            $pubname=stripslashes($job['pub_name']);
            $draw=$job['draw'];
            $pubdate=date("D m/d/Y",strtotime($job['pub_date']));
            $status="";
            //ok, lets check for a run
            if ($job['run_id']!=0)
            {
                $sql="SELECT run_name, run_productcode FROM publications_runs WHERE id=$job[run_id]";
                $dbRun=dbselectsingle($sql);
                $runname=$dbRun['data']['run_name'];
                $rpc=$dbRun['data']['run_productcode'];
                if($rpc!='')
                {
                    $runname.="<br>Prod code: $rpc";
                }
            } else {
                $runname=$job['runspecial'];
            }
            $runname=stripslashes($runname);
            //see which folder the job is running on
            $folder=str_replace("folder","",$job['folder']);
            $runname.="<br />Printing to folder $folder";
            
            //now check for sections
            $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
            $dbSections=dbselectsingle($sql);
            if ($dbSections['numrows']>0)
            {
                $sections=$dbSections['data'];
                $section1="Name: ".$sections['section1_name']."<br />\n";
                $section1.="Letter: ".$sections['section1_code']."<br />\n";
                $section1.="Low page: ".$sections['section1_lowpage']."<br />\n";
                $section1.="High Page: ".$sections['section1_highpage']."<br />\n";
                $section1.="Format: ".$producttypes[$sections['section1_producttype']]."<br />\n";
                $section1.="Lead: ".$leadtypes[$sections['section1_leadtype']]."<br />\n";
                $section1.="Overrun: ".$sections['section1_overrun']."<br />\n";
                if ($sections['section1_doubletruck']){$section1.="Has Doubletruck<br>";}
                if ($sections['section1_gatefold']){$section1.="Has Gatefold";}
                if ($sections['section1_lowpage']==0 && $sections['section1_name']=='' && $sections['section1_code']==''){$section1='Not set';}
                
                $section2="Name: ".$sections['section2_name']."<br />\n";
                $section2.="Letter: ".$sections['section2_code']."<br />\n";
                $section2.="Low page: ".$sections['section2_lowpage']."<br />\n";
                $section2.="High Page: ".$sections['section2_highpage']."<br />\n";
                $section2.="Format: ".$producttypes[$sections['section2_producttype']]."<br />\n";
                $section2.="Lead: ".$leadtypes[$sections['section2_leadtype']]."<br />\n";
                $section2.="Overrun: ".$sections['section2_overrun']."<br />\n";
                if ($sections['section2_doubletruck']){$section2.="Has Doubletruck<br>";}
                if ($sections['section2_gatefold']){$section2.="Has Gatefold";}
                if ($sections['section2_lowpage']==0 && $sections['section2_name']=='' && $sections['section2_code']==''){$section2='Not set';}
                
                $section3="Name: ".$sections['section3_name']."<br />\n";
                $section3.="Letter: ".$sections['section3_code']."<br />\n";
                $section3.="Low page: ".$sections['section3_lowpage']."<br />\n";
                $section3.="High Page: ".$sections['section3_highpage']."<br />\n";
                $section3.="Format: ".$producttypes[$sections['section3_producttype']]."<br />\n";
                $section3.="Lead: ".$leadtypes[$sections['section3_leadtype']]."<br />\n";
                $section3.="Overrun: ".$sections['section3_overrun']."<br />\n";
                if ($sections['section3_doubletruck']){$section3.="Has Doubletruck<br>";}
                if ($sections['section3_gatefold']){$section3.="Has Gatefold";}
                if ($sections['section3_lowpage']==0 && $sections['section3_name']=='' && $sections['section3_code']==''){$section3='Not set';}
                
            } else {
                $section1="Not set";
                $section2="Not set";
                $section3="Not set";
            }
            if ($section1=='Not set' && $section2=='Not set' && $section3=='Not set'){$status="No sections";}
            if ($job['startdatetime']==''){if ($status!=''){$status.="<br>";}$status.="Not scheduled";}
            if ($job['layout_id']=='0'){if ($status!=''){$status.="<br>";}$status.="No layout";}
            if ($draw==0){if ($status!=''){$status.="<br>";}$status.="No draw";}
            if ($status==''){$status='OK';}
            if ($job['stats_id']=='0'){if ($status!=''){$status.="<br>";}$status.="No press data";}
            print "<tr>";
            print "<td>$pubname</td>";
            print "<td>$pubdate</td>";
            print "<td>$runname</td>";
            print "<td>$draw</td>";
            print "<td>$section1<br />";
            print "$section2<br />";
            print "$section3</td>";
            if ($status=='OK' || $status=='OK <br />No stats')
            {
                print "<td style='color:green;font-weight:bold;'>$status</td>";
                
            } else {
                print "<td style='color:red;font-weight:bold;'>$status</td>";
            }
            print "<td colspan=2>\n";
            print "<a href='?action=editjob&jobid=$jobid'>Edit Job</a><br>\n";
            print "<hr>\n";
            print "<a href='?action=setlayout&jobid=$jobid'>Choose layout</a><br>\n";
            print "<a href='?action=setcolor&jobid=$jobid'>Set Color</a><br>\n";
            print "<a href='?action=schedulejob&jobid=$jobid'>Schedule Job</a><br>\n";
            print "<a href='?action=setdraw&jobid=$jobid'>Change Draw</a><br>\n";
            print "<hr>\n";
            print "<a href='jobPressTicket.php?action=print&jobid=$jobid' target='_blank'>Print Job Ticket</a><br>\n";
            print "<a href='?action=pressdata&jobid=$jobid'>Press Data</a><br>\n";
            print "<a href='?action=deletejob&jobid=$jobid' class='delete'>Delete</a><br>\n";
            if (in_array(1,$_SESSION['cmsuser']['permissions']))
            {
                print "<a href='?action=whodid&jobid=$jobid'>Who did it</a><br>";
            }
            print "<a href='?action=redojob&jobid=$jobid'>Reprint Job</a>";
            print "</td>\n";
            print "</tr>\n";        
        }
    }
    tableEnd($dbJobs);
}
function changeDraw()
{
    $jobid=$_GET['jobid'];
    $sql="SELECT A.*, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.id=$jobid AND A.pub_id=B.id AND A.run_id=C.id";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    
    $pubname=$job['pub_name'];
    $runname=$job['run_name'];
    
    $pubdate=date("D, F j Y",strtotime($job['pub_date']));
    $drawHD=$job['draw_hd'];
    $drawSC=$job['draw_sc'];
    $drawMail=$job['draw_mail'];
    $drawOffice=$job['draw_office'];
    $drawCustomer=$job['draw_customer'];
    $drawOther=$job['draw_other'];
    $drawTotal=$job['draw'];
    print "<form method=post>\n";
    print "<div class='label'></div><div class='input'>";
    print "You are setting the draw for $pubname - $runname run publishing on $pubdate";
    print "</div><div class='clear'></div>\n";
    
    print "<div class='label'>Draw Request</div>\n";
        print "<div class='input'>\n";
            print "<div id='drawblock'>\n";
            
            print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
            print "How many copies for home delivery?";
                print "</div>\n";
                print "<div style='float:left;'>\n";
                print input_text('drawHD',$drawHD,8,false,'calcDraw();','','','return isNumberKey(event);');
                print "</div>\n";
                print "<div class='clear'></div>\n";
                
                print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                print "How many copies for single copy/racks?";
                print "</div>\n";
                print "<div style='float:left;'>\n";
                print input_text('drawSC',$drawSC,8,false,'calcDraw();','','','return isNumberKey(event);');
                print "</div>\n";
                print "<div class='clear'></div>\n";
                
                print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                print "How many copies for mail delivery?";
                print "</div>\n";
                print "<div style='float:left;'>\n";
                print input_text('drawMail',$drawMail,8,false,'calcDraw();','','','return isNumberKey(event);');
                print "</div>\n";
                print "<div class='clear'></div>\n";
                
                print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                print "How many copies for office/tearsheets?";
                print "</div>\n";
                print "<div style='float:left;'>\n";
                print input_text('drawOffice',$drawOffice,8,false,'calcDraw();','','','return isNumberKey(event);');
                print "</div>\n";
                print "<div class='clear'></div>\n";
                
                print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                print "How many copies for the customer?";
                print "</div>\n";
                print "<div style='float:left;'>\n";
                print input_text('drawCustomer',$drawCustomer,8,false,'calcDraw();','','','return isNumberKey(event);');
                print "</div>\n";
                print "<div class='clear'></div>\n";
                
                print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                print "How many copies for other uses?";
                print "</div>\n";
                print "<div style='float:left;'>\n";
                print input_text('drawOther',$drawOther,8,false,'calcDraw();','','','return isNumberKey(event);');
                print "</div>\n";
                print "<div class='clear'></div>\n";
                
                print "<div style='width: 200px;font-weight:normal;font-size:10px;float:left;text-align:right;margin-right:5px;'>\n";
                print "<p style='font-size:12px;font-weight:bold;'>Total request:</p>If you manually enter a value here,<br />it will clear the others.";
                print "</div>\n";
                print "<div style='float:left;'>\n";
                print input_text('drawTotal',$drawTotal,8,false,'','','','return isNumberKey(event);','manualDraw();');
                print "</div>\n";
                print "<div class='clear'></div>\n";
            print "</div>\n";
        print "</div>\n";
        print "<div class='clear'></div>\n";
        make_hidden('jobid',$jobid);
        make_submit('submit','Change Draw');
        print "</form>\n";
}

function save_draw()
{
   $jobid=$_POST['jobid'];
   $drawHD=$_POST['drawHD'];
   $drawSC=$_POST['drawSC'];
   $drawMail=$_POST['drawMail'];
   $drawOffice=$_POST['drawOffice'];
   $drawCustomer=$_POST['drawCustomer'];
   $drawOther=$_POST['drawOther'];
   $drawTotal=$_POST['drawTotal'];
   $drawtime=date("Y-m-d H:i:s");
   $drawby=$_SESSION['cmsuser']['userid'];
   $sql="UPDATE jobs SET drawset_time='$drawtime', drawset_by='$drawby', draw='$drawTotal', draw_hd='$drawHD', draw_sc='$drawSC', draw_mail='$drawMail', 
   draw_office='$drawOffice', draw_customer='$drawCustomer', draw_other='$drawOther' WHERE id=$jobid";
   $dbUpdate=dbexecutequery($sql);
   $error=$dbUpdate['error'];  
   
   
   $sql="UPDATE inserts SET insert_count='$drawTotal' WHERE weprint_id=$jobid";
   $dbUpdate=dbexecutequery($sql);
   $error.=$dbUpdate['error'];
   if ($error!='')
    {
        setUserMessage('There was a problem saving the draw for this job.<br />'.$error,'error');
    } else {
        setUserMessage('The draw for this job has been successfully set.','success');
    }
    redirect("?action=list");
     
}


function delete_job()
{
    $jobid=$_POST['jobid'];
    $sql="DELETE FROM jobs WHERE id=$jobid";
    $dbDelete=dbexecutequery($sql);
    if ($dbDelete['error']=='')
    {
        $sql="DELETE FROM job_pages WHERE job_id=$jobid";
        $dbDelete=dbexecutequery($sql);
        $sql="DELETE FROM job_plates WHERE job_id=$jobid";
        $dbDelete=dbexecutequery($sql);
        $sql="DELETE FROM inserts WHERE weprint_id=$jobid";
        $dbDelete=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        print $dbDelete['error'];
    }
        
}

function save_job($action)
{
    global $siteID, $sizes;
    $jobid=$_POST['jobid'];
    $pubid=$_POST['pub_id'];
    $pressid=$_POST['pressid'];
    $insertpubid=$_POST['insertpub_id'];
    $runid=$_POST['run_id'];
    $runspecial=addslashes($_POST['run_special']);
    $papertype=$_POST['newsprint'];
    $papertypecover=$_POST['papertype_cover'];
    $pubdate=$_POST['pubdate'];
    $drawHD=$_POST['drawHD'];
    $drawSC=$_POST['drawSC'];
    $drawMail=$_POST['drawMail'];
    $drawOffice=$_POST['drawOffice'];
    $drawCustomer=$_POST['drawCustomer'];
    $drawOther=$_POST['drawOther'];
    $drawTotal=$_POST['drawTotal'];
    if($drawTotal==''){$drawTotal=0;}
    $pagewidth=$sizes[$_POST['pagewidth']];
    $lap=$_POST['lap'];
    $folder=$_POST['folder'];
    if ($_POST['trim']){$trim=1;}else{$trim=0;}
    if ($_POST['stitch']){$stitch=1;}else{$stitch=0;}
    if ($_POST['glossycover']){$glossycover=1;}else{$glossycover=0;}
    if ($_POST['glossyinside']){$glossyinside=1;}else{$glossyinside=0;}
    if ($_POST['quarterfold']){$quarterfold=1;}else{$quarterfold=0;}
    if ($_POST['slitter']){$slitter=1;}else{$slitter=0;}
    $folderpin=$_POST['folderpin'];
    $glossydraw=$_POST['glossydraw'];
    $glossyinsidecount=$_POST['glossyinsidecount'];
    $jobmessage=addslashes($_POST['jobmessage']);
    $notesInserting=addslashes($_POST['notes_inserting']);
    $coverdue=$_POST['coverdue'];
    $coverouput=$_POST['coveroutput'];
    $coverprint=$_POST['coverprint'];
    $pagerelease=$_POST['pagerelease'];
    $pagerip=$_POST['pagerip'];
    $deliverynotes=addslashes($_POST['notes_delivery']);
    $binderynotes=addslashes($_POST['notes_bindery']);
    $binderystart=$_POST['bindery_start'];
    $binderydue=$_POST['bindery_due'];
    $printRequestDate=$_POST['request_printdate'];
    $jobType=$_POST['job_type'];
    $rollSize=$_POST['rollSize'];
    if($rollSize==''){$rollSize=0;}
    if ($_POST['requires_delivery']){$requiresDelivery=1;}else{$requiresDelivery=0;}
    if ($_POST['requires_addressing']){$requiresAddressing=1;}else{$requiresAddressing=0;}
    if ($_POST['requires_inserting']){$requiresInserting=1;}else{$requiresInserting=0;}
    //if we get a run special, we need to add it to the runs for that pub and get the runid to be used later
    if ($runspecial!='')
    {
        $productcode=addslashes($_POST['run_special_productcode']);
        $sql="INSERT INTO publications_runs (pub_id,run_name, run_productcode) VALUES ('$pubid','$runspecial', '$productcode')";
        $dbRunInsert=dbinsertquery($sql);
        $runid=$dbRunInsert['numrows'];
    }
    
    
    //print_r($_POST);
    $section1_used=0;
    $section2_used=0;
    $section3_used=0;
    
    if ($_POST['section1_enable'])
    {
        $section1_used=1;
        $section1_overrun=$_POST['section1_overrun'];
        $section1_format=$_POST['section1_format'];
        $section1_lead=$_POST['section1_lead'];
        $section1_low=$_POST['section1_low'];
        $section1_high=$_POST['section1_high'];
        $section1_name=addslashes($_POST['section1_name']);
        $section1_letter=addslashes($_POST['section1_letter']);
        if($_POST['section1_doubletruck']){$section1_doubletruck=1;}else{$section1_doubletruck=0;}
        if($_POST['section1_gatefold']){$section1_gatefold=1;}else{$section1_gatefold=0;}
    } else {
        $section1_overrun=0;
        $section1_format=0;
        $section1_low=0;
        $section1_high=0;
        $section1_name='';
        $section1_letter='';
        $section1_doubletruck=0;
        $section1_gatefold=0;
        $section1_lead=0;
    }
    $tpages=$section1_high-$section1_low;
    if ($_POST['section2_enable'])
    {
        $section2_used=1;
        $section2_overrun=$_POST['section2_overrun'];
        $section2_format=$_POST['section2_format'];
        $section2_lead=$_POST['section2_lead'];
        $section2_low=$_POST['section2_low'];
        $section2_high=$_POST['section2_high'];
        $section2_name=addslashes($_POST['section2_name']);
        $section2_letter=addslashes($_POST['section2_letter']);
        if($_POST['section2_doubletruck']){$section2_doubletruck=1;}else{$section2_doubletruck=0;}
        if($_POST['section2_gatefold']){$section2_gatefold=1;}else{$section2_gatefold=0;}
    } else {
        $section2_overrun=0;
        $section2_format=0;
        $section2_low=0;
        $section2_high=0;
        $section2_name='';
        $section2_letter='';
        $section2_doubletruck=0;
        $section2_gatefold=0;
        $section2_lead=0;
    }
    $tpages+=$section2_high-$section1_2ow;
    if ($_POST['section3_enable'])
    {
        $section3_used=1;
        $section3_overrun=$_POST['section3_overrun'];
        $section3_format=$_POST['section3_format'];
        $section3_lead=$_POST['section3_lead'];
        $section3_low=$_POST['section3_low'];
        $section3_high=$_POST['section3_high'];
        $section3_name=addslashes($_POST['section3_name']);
        $section3_letter=addslashes($_POST['section3_letter']);
        if($_POST['section3_doubletruck']){$section3_doubletruck=1;}else{$section3_doubletruck=0;}
        if($_POST['section3_gatefold']){$section3_gatefold=1;}else{$section3_gatefold=0;}
    } else {
        $section3_overrun=0;
        $section3_format=0;
        $section3_low=0;
        $section3_high=0;
        $section3_name='';
        $section3_letter='';
        $section3_doubletruck=0;
        $section3_gatefold=0;
        $section3_lead=0;
    }
    switch ($section1_format)
    {
        case 0:
        if ($section1_high>0)
        {
            $tpages+=$section1_high-$section1_low+1;
        }
        break;
        case 1:
        if ($section1_high>0)
        {
            $tpages+=($section1_high-$section1_low+1)/2;
        }
        break;
        case 2:
        if ($section1_high>0)
        {
            $tpages+=($section1_high-$section1_low+1)/2;
        }
        break;
        case 3:
        if ($section1_high>0)
        {
            $tpages+=($section1_high-$section1_low+1)/4;
        }
        break;
    }
    switch ($section2_format)
    {
        case 0:
        if ($section2_high>0)
        {
            $tpages+=$section2_high-$section2_low+1;
        }
        break;
        case 1:
        if ($section2_high>0)
        {
            $tpages+=($section2_high-$section2_low+1)/2;
        }
        break;
        case 2:
        if ($section2_high>0)
        {
            $tpages+=($section2_high-$section2_low+1)/2;
        }
        break;
        case 3:
        if ($section2_high>0)
        {
            $tpages+=($section2_high-$section2_low+1)/4;
        }
        break;
    }
    switch ($section3_format)
    {
        case 0:
        if ($section3_high>0)
        {
            $tpages+=$section3_high-$section3_low+1;
        }
        break;
        case 1:
        if ($section3_high>0)
        {
            $tpages+=($section3_high-$section3_low+1)/2;
        }
        break;
        case 2:
        if ($section3_high>0)
        {
            $tpages+=($section3_high-$section3_low+1)/2;
        }
        break;
        case 3:
        if ($section3_high>0)
        {
            $tpages+=($section3_high-$section3_low+1)/4;
        }
        break;
    }
    

    $notes=addslashes($_POST['notes']);
    if ($action=='insert')
    {
        $createdtime=date("Y-m-d H:i:s");
        $createdby=$_SESSION['cmsuser']['userid'];
   
        $sql="INSERT INTO jobs (created_time,created_by,pub_id, insert_pub_id, run_id, pub_date, draw_hd, draw_sc, draw_mail, 
        draw_office, draw_customer, draw_other, draw, papertype, notes_job, lap, trim, stitch, glossy_cover, glossy_cover_draw, 
        glossy_insides, glossy_insides_count, cover_date_output, cover_date_print, cover_date_due, page_release, page_rip, 
        bindery_startdate, bindery_duedate, notes_bindery, notes_delivery, pagewidth, site_id, insert_source, folder, quarterfold, 
        job_message, papertype_cover, slitter, folder_pin, request_printdate, requires_addressing, requires_delivery, job_type, rollSize, requires_inserting, notes_inserting, press_id) 
        VALUES ('$createdtime', '$createdby', '$pubid', '$insertpubid', 
        '$runid', '$pubdate', '$drawHD', '$drawSC', '$drawMail',  '$drawOffice', '$drawCustomer', '$drawOther', '$drawTotal', 
        '$papertype', '$notes', '$lap',  '$trim', '$stitch', '$glossycover', '$glossydraw','$glossyinside', '$glossyinsidecount', 
        '$coverouput', '$coverdue', '$coverprint', '$pagerelease', '$pagerip', '$binderystart', '$binderydue', '$binderynotes', 
        '$deliverynotes', '$pagewidth', '$siteID', 'managejobs', '$folder', '$quarterfold', '$jobmessage',
         '$papertypecover', '$slitter', '$folderpin', '$printRequestDate', '$requiresAddressing', '$requiresDelivery', '$jobType', '$rollSize', '$requiresInserting', '$notesInserting', '$pressid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $jobid=$dbInsert['numrows'];
        
        $sql="INSERT INTO job_stats (job_id, added_by) VALUES ('$jobid', 'jobPress.php line 2201')";
        $dbStat=dbinsertquery($sql);
        $statid=$dbStat['insertid'];
        $sql="UPDATE jobs SET stat_id='$statid' WHERE id=$jobid";
        $dbUpdate=dbexecutequery($sql);
        
        
    } else {
        $updatedtime=date("Y-m-d H:i:s");
        $updatedby=$_SESSION['cmsuser']['userid'];
        $sql="UPDATE jobs SET updated_time='$updatedtime', updated_by='$updatedby', pub_id='$pubid', insert_pub_id='$insertpubid', 
        run_id='$runid', job_message='$jobmessage', pub_date='$pubdate', papertype='$papertype', notes_job='$notes', 
        draw='$drawTotal', papertype_cover='$papertypecover', draw_hd='$drawHD', draw_sc='$drawSC', draw_mail='$drawMail', 
        lap='$lap', draw_office='$drawOffice', draw_customer='$drawCustomer', draw_other='$drawOther', trim='$trim', 
        stitch='$stitch', glossy_cover='$glossycover', glossy_cover_draw='$glossydraw', glossy_insides='$glossyinside', 
        glossy_insides_count='$glossyinsidecount', cover_date_output='$coverouput', cover_date_print='$coverprint', 
        cover_date_due='$coverdue', page_release='$pagerelease', page_rip='$pagerip', bindery_startdate='$binderystart', 
        bindery_duedate='$binderydue', notes_delivery='$deliverynotes', quarterfold='$quarterfold', notes_bindery='$binderynotes', notes_inserting='$notesInserting', requires_inserting='$requiresInserting', pagewidth='$pagewidth', folder='$folder', slitter='$slitter', folder_pin='$folderpin', request_printdate='$printRequestDate', requires_delivery='$requiresDelivery', requires_addressing='$requiresAddressing', job_type='$jobType', rollSize='$rollSize', press_id='$pressid' WHERE id=$jobid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }                           
    
    
    //check for existing jobs_sections and if not present, create if necessary
    if ($error=='')
    {
        $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
        $dbSections=dbselectsingle($sql);
        if ($dbSections['numrows']>0)
        {
            $sectionid=$dbSections['data']['id'];
            //updating an existing section record
            $sql="UPDATE jobs_sections SET section1_used='$section1_used', section1_name='$section1_name', section1_code='$section1_letter',
            section1_lowpage='$section1_low', section1_highpage='$section1_high', section1_leadtype='$section1_lead', 
            section1_gatefold='$section1_gatefold', section1_doubletruck='$section1_doubletruck', 
            section1_producttype='$section1_format', section2_used='$section2_used', section2_name='$section2_name', 
            section2_code='$section2_letter', section2_lowpage='$section2_low', 
            section2_highpage='$section2_high', section2_gatefold='$section2_gatefold', 
            section2_doubletruck='$section2_doubletruck', section2_producttype='$section2_format', section2_leadtype='$section2_lead', 
            section3_used='$section3_used', section3_name='$section3_name', section3_code='$section3_letter',
            section3_lowpage='$section3_low', section3_highpage='$section3_high', 
            section3_gatefold='$section3_gatefold', section3_doubletruck='$section3_doubletruck', 
            section3_producttype='$section3_format', section3_leadtype='$section3_lead', section1_overrun='$section1_overrun',
            section2_overrun='$section2_overrun', section3_overrun='$section3_overrun' WHERE id=$sectionid";
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
    }
    
    
    //check if we need to build an insert or inserting package for this job
    printJob2Inserter($jobid,$_POST['createinsert']);
    printJob2Delivery($jobid);
    printJob2Bindery($jobid);
    printJob2Addressing($jobid);
    //clear any cached calendar files
    clearCache('presscalendar');
    
    /***********************************************************************
    ***********************************************************************/
    if ($error!='')
    {
        setUserMessage('There was a problem saving the this job.<br />'.$error,'error');
    } else {
        setUserMessage('This job has been successfully saved.','success');
    }
    redirect("?action=list");
    
}
 

function pressdata()
{
    global $papertypes, $sizes, $pressid, $pressmen, $siteID, $pressDepartmentID;
    //need all press towers
    $sql="SELECT * from press_towers WHERE press_id=$pressid AND tower_type='printing' ORDER BY tower_order";
    $dbTowers=dbselectmulti($sql);
    $towers=$dbTowers['data'];
    
    
    //need the operators
    $operators=array();
    $operators[0]='Please choose';
    $sql="SELECT A.id, A.firstname, A.lastname FROM users A, user_positions B WHERE A.department_id=$pressDepartmentID AND A.position_id=B.id AND B.operator=1 ORDER BY A.lastname";
    $dbPress=dbselectmulti($sql);
    if ($dbPress['numrows']>0)
    {
        foreach ($dbPress['data'] as $p)
        {
            $operators[$p['id']]=$p['firstname']." ".$p['lastname'];
        }
    }
    
    
    //now need
    
    $jobid=intval($_GET['jobid']);
    $sql="SELECT A.stats_id, A.pub_date, A.draw, A.startdatetime, A.layout_id, A.enddatetime, A.dataset_time, A.notes_press, A.pub_id, A.run_id, A.papertype, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.id=$jobid AND A.pub_id=B.id AND A.run_id=C.id";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    $statsid=$job['stats_id'];
    $pressid=$job['press_id'];
    //see if there is a stats record
    $sql="SELECT * FROM job_stats WHERE job_id=$jobid";
    $dbStats=dbselectsingle($sql);
    if ($dbStats['numrows']>0)
    {
        $stats=$dbStats['data'];
        $statsid=$stats['id'];
        $actualstarttime=$stats['startdatetime_actual'];
        $startdate=date("Y-m-d H:i",strtotime($actualstarttime));
        
        $actualstoptime=$stats['stopdatetime_actual'];
        $stopdate=date("Y-m-d H:i",strtotime($actualstoptime));
        
        $goodcopytime=$stats['goodcopy_actual'];
        $gooddate=date("Y-m-d H:i",strtotime($goodcopytime));
        
        $counterstart=$stats['counter_start'];
        $counterstop=$stats['counter_stop'];
        $spoilsstartup=$stats['spoils_startup'];
        $spoilsrunning=$stats['spoils_running'];
        $spoilstotal=$stats['spoils_total'];
        $downtime=$stats['total_downtime'];
        $checklist=$stats['checklist_approved'];
        $jobpressoperator=$stats['job_pressoperator'];
        $jobpressmanids=$stats['job_pressman_ids'];
        $wastePlates=$stats['plates_waste'];
        $remakePlates=$stats['plates_remake'];
        if ($job['dataset_time']!='')
        {
            $datatime=$job['dataset_time'];
        } else {
            $datatime=date("Y-m-d H:i");
        }
        $setupstart=$stats['setup_start'];
        $setupstop=$stats['setup_stop'];
        
        $jobnotes=stripslashes($job['notes_press']);
        //convert ids to array
        $jobpressmanids=explode("|",$jobpressmanids);
        
    } else {
        $datatime=date("Y-m-d H:i");
        $startdate=date("Y-m-d H:i",strtotime($job['startdatetime']));
        $stopdate=date("Y-m-d H:i",strtotime($job['enddatetime']));
        $gooddate=date("Y-m-d H:i",strtotime($job['startdatetime']));
        $counterstart=0;
        $counterstop=0;
        $spoilsstartup=0;
        $spoilsrunning=0;
        $spoilstotal=0;
        $downtime="0";
        $checklist=0;
        $jobpressoperator=0;
        $jobpressmanids=array();
        $jobnotes='';
        $wastePlates=0;
        $remakePlates=0;
        $setupstart='';
        $setupstop='';
    }
    
    //get default paper type for the job
        $newtowers=array();
        foreach($towers as $tower)
        {
            //see if this tower is used
            $towerid=$tower['id'];
            $sql="SELECT * FROM layout_sections WHERE layout_id=$job[layout_id] AND towers LIKE '%$towerid%'";
            $dbUsed=dbselectsingle($sql);
            if ($dbUsed['numrows']>0)
            {
                $z=$dbUsed['data']['towers'];
                $z=explode("|",$z);
                $found=0;
                foreach($z as $key=>$v)
                {
                    if($towerid==$v){$found=1;}   
                }
                $newtowers[$tower['id']]['used']=$found;        
            } else {
                $newtowers[$tower['id']]['used']=0;
            }
            $newtowers[$tower['id']]['tower_name']=$tower['tower_name'];
            $newtowers[$tower['id']]['id']=$tower['id'];
            $newtowers[$tower['id']]['papertype']=$job['papertype'];
            //now to figure out how many pages on the plate - thus the paper size
            $layoutid=$job['layout_id'];
            $towerid=$tower['id'];
            $sql="SELECT * FROM layout_page_config WHERE layout_id=$layoutid AND tower_id=$towerid AND side='10' AND tower_row=1";
            $dbPages=dbselectmulti($sql);
            $pcount=0;
            if($dbPages['numrows']>0)
            {
                foreach($dbPages['data'] as $pages)
                {
                    if ($pages['page_number']!='0'){$pcount++;}    
                }
                
                //we're assuming everything is a newspaper lead. for commercial
                //leads, we'll just assume the press crew will set it.
                //On 9/27/12 changed from using GLOBALS[broadsheetPageWidth] to the actual page width defined in the job
                //$rollwidth=$GLOBALS['broadsheetPageWidth']*$pcount;
                //find the index in the sizes array with this roll width
                
                $rollwidth=$job['pagewidth']*$pcount;
                $rollwidthmin=$rollwidth-.1;
                $rollwidthmax=$rollwidth+.1;
                
                //find the index in the sizes array with this roll width
                $sql="SELECT id FROM paper_sizes WHERE width>='$rollwidthmin' AND width<='$rollwidthmax' AND status=1";
                $dbSizeFind=dbselectsingle($sql);
                if($dbSizeFind['numrows']>0)
                {
                    $rollwidthid=$dbSizeFind['data']['id'];
                } else {
                    $rollwidthid='Please choose';
                }
                $newtowers[$tower['id']]['size']=$rollwidthid;
            } else {
                $newtowers[$tower['id']]['size']='Please choose';
            }
            
        }
        $towers=$newtowers;
        $temptower=explode("|",$stats['tower_info']);
        foreach($temptower as $tt)
        {
            $tempt=explode(",",$tt);
            $towers[$tempt[0]]['id']=$tempt[0];    
            $towers[$tempt[0]]['tower_name']=$tempt[1];    
            $towers[$tempt[0]]['used']=$tempt[2];    
            $towers[$tempt[0]]['papertype']=$tempt[3];    
            $towers[$tempt[0]]['size']=$tempt[4];
        }
    
    print "<form id='pressdata' name='pressdata' method=post>\n";
    print "<div id='wrap'>\n";
    print "<div style='float:left;'>\n";
        print "<a href='?action=printpressdata&jobid=$jobid'><img src='artwork/icons/printer_48.png' border=0 width=32>Print Report</a><br />\n";
        print "<div class='label'>Publication</div><div class='input'>$job[pub_name]</div><div class='clear'></div>\n";
        print "<div class='label'>Run</div><div class='input'>$job[run_name]</div><div class='clear'></div>\n";
        print "<div class='label'>Draw</div><div class='input'>$job[draw]</div><div class='clear'></div>\n";
        make_datetime('setupstart',$setupstart,'Setup Start Time','When did setup start?');
        make_datetime('setupstop',$setupstop,'Setup Stop Time','When did setup stop?');
        make_datetime('starttime',$startdate,'Start Time','Actual Start Time');
        make_datetime('goodtime',$gooddate,'Good Copy Time','Actual Good Copy Time');
        make_datetime('stoptime',$stopdate,'Stop Time','Actual Stop Time');
        make_text('counterstart',$counterstart,'Counter START','Enter the starting press counter',15,'',false,'','','pressDataCheckZero(this.id);','return isNumberKey(event);');
        print "<div class='label'>Counter ENDING</div><div class='input'><small>Enter the ending press counter</small><br />";
        print input_text('counterstop',$counterstop,15,false,'','','pressCounterCheck();','return isNumberKey(event);');
        print "<div id='counteralert'></div>\n";
        print "</div><div class='clear'></div>\n";
        
        make_text('spoilsstartup',$spoilsstartup,'Spoils STARTUP','Enter the number of startup spoiled copies',15,'',false,'','','pressDataCheckZero(this.id);','return isNumberKey(event);');
        make_text('wastePlates',$wastePlates,'Wasted Plates','Enter the number of plates wasted',5,'',false,'','','','return isNumberKey(event);');
        make_text('remakePlates',$remakePlates,'Remake Plates','Enter the number of plates remade',5,'',false,'','','','return isNumberKey(event);');
        make_text('downtime',$downtime,'Total downtime','Enter the number minutes the press was down during the run<br />(extended peiods only, ie. more than 5 minutes)',15,'',false,'','','pressDataCheckZero(this.id);','return isNumberKey(event);');
        make_select('checklist',$operators[$checklist],$operators,'Daily checklist','Daily checklist approved by');
        make_select('pressoperator',$operators[$jobpressoperator],$operators,'Lead pressman');
        //now create a list of all pressman
        print "<div class='label'>Pressmen on shift</div><div class='input'>\n";
        if (count($pressmen)>0)
        {
            $col=round((count($pressmen))/3,0)+1;
            $i=0;
            print "<div style='float:left;width:180px;'>\n";
            foreach($pressmen as $id=>$name)
            {
                if ($name!='Please choose')
                {
                    if (in_array($id,$jobpressmanids))
                    {
                        $checked="checked";
                    }
                    print "<input type='checkbox' id='pressman_$id' name='pressman_$id' $checked> $name<br />\n";
                }
                if ($i==$col)
                {
                    print "</div>\n";
                    print "<div style='float:left;width:180px;'>\n";
                    $i=1;
                } else {
                    $i++;
                }
                $checked="";
            }
            print "</div>\n"; //listing the pressman
           print "<div class='clear'></div>\n"; 
        }
        print "</div>\n"; // close the pressman area
        print "<div class='clear'></div>\n";
         
        //now the press towers so we can specify roll sizes and types by tower
        print "<div class='label'>Paper Used</div><div class='input'>\n";
        if (count($towers)>0)
        {
            foreach ($towers as $tower)
            {
                if ($tower['used'])
                {
                    $ptype=$tower['papertype'];
                    $psize=$tower['size'];
                } else {
                    $ptype=0;
                    $psize=0;
                }
                if($tower['tower_name']!='')
                {
                    print "<div style='width:110px;float:left;'>$tower[tower_name]: <input type=hidden name='tower_$tower[id]' id='tower_$tower[id]' value='$tower[tower_name]'></div>";
                    print "<div style='float:left;width:220px;'>\n";
                    print input_select("t_$tower[id]_papertype",$papertypes[$ptype],$papertypes)." ";
                    print "</div>";
                    print "<div style='float:left;width:200px;'>\n";
                    print input_select("t_$tower[id]_size",$sizes[$psize],$sizes);
                    print "</div><div class='clear'></div>\n";
                }
            }    
        }
        print "</div>\n"; //listing the towers
        print "<div class='clear'></div>\n";
        
        
        
        make_textarea('pressnotes',$jobnotes,'Notes','Enter any details of rolls, press problems, or issues during the run',80,20);   
        
        print "</div>\n";
    print "</div>\n";


    print "<div class='ui-widget ui-widget-content ui-corner-all' style='float:right;width:300px;margin-right:10px;border: 1px solid black;padding:5px;'>\n";
        //calculated stats area
        print "<b>Calculated stats for this job:</b><br />\n";
        print "<br />\n";
        print "<div style='float:left;width:120px;'>Gross Press:</div>$stats[gross]<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Waste:</div>$stats[waste_percent]%<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Spoils Total:</div>$stats[spoils_total]<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Job time:</div>$stats[run_time] minutes<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Overall avg speed:</div>$stats[run_speed] copies/hr<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Net avg speed:</div>$stats[good_runspeed] copies/hr<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Start Offset:</div>$stats[start_offset] minutes<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Finish Offset:</div>$stats[finish_offset] minutes<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Scheduled Runtime:</div>$stats[sched_runtime] minutes<div style='float:left;'></div><div class='clear'></div>\n";
        print "<br />\n";
        print "<div style='float:left;width:120px;'>Black Pages:</div>$stats[pages_bw]<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Color Pages:</div>$stats[pages_color]<div style='float:left;'></div><div class='clear'></div>\n";
        print "<br />\n";
        print "<div style='float:left;width:120px;'>Black plates:</div>$stats[plates_bw]<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Color Plates:</div>$stats[plates_color]<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Remake Plates:</div>$stats[plates_remake]<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Waste Plates:</div>$stats[plates_waste]<div style='float:left;'></div><div class='clear'></div>\n";
        print "<br />\n";
        print "<div style='float:left;width:120px;'>Last Page:</div>$stats[last_page]<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Last Color:</div>$stats[last_colorpage]<div style='float:left;'></div><div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Last Plate:</div>$stats[last_plate]<div style='float:left;'></div><div class='clear'></div>\n";
        print "<br />\n";
         print "<div style='float:left;width:120px;'>Man hours:</div>$stats[man_hours]<div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Total Tons:</div>$stats[total_rons]<div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Hours/ton:</div>$stats[hours_per_ton]<div class='clear'></div>\n";
        print "<div style='float:left;width:120px;'>Impressions/hour:</div>$stats[impressions_per_hour]<div class='clear'></div>\n";
        print "<br />\n";
        $sql="SELECT A.*, B.common_name, C.width FROM job_paper A, paper_types B, paper_sizes C WHERE A.job_id=$jobid AND A.papertype_id=B.id AND A.size_id=C.id";
        $dbRolls=dbselectmulti($sql);
        if ($dbRolls['numrows']>0)
        {
            print "Here is the paper used on this job:<br />\n";
            foreach($dbRolls['data'] as $roll)
            {
                print "$roll[common_name] - $roll[width], $roll[calculated_tonnage]MT - \$$roll[calculated_cost]<br />\n";
                
            }
        }
    print "</div>";
    print "</div>"; //closing the whole wrap div
    print "<div class='clear'></div>\n";
    
    
    make_checkbox('override',0,'Override','Only check this if you get an error saving, and you HAVE TO save.');
    make_hidden('dataset',$datatime);
    make_hidden('schedule_start',$job['startdatetime']);
    make_hidden('schedule_stop',$job['enddatetime']);
    make_hidden('draw',$job['draw']);
    make_hidden('statsid',$statsid);
    make_hidden('jobid',$jobid);
    make_hidden('jssubmit','formsubmit');
    print "<div class='label'></div><div class='input'><input type='button' name='jssubmitbtn' value='Save Press Data' onclick='checkPressData();'>\n"; 
    print "</div><div class='clear'></div>\n";
    print "</form>\n";
}

function print_pressdata()
{
    global $papertypes, $sizes, $pressid, $pressmen;
    //need all press towers
    $sql="SELECT * from press_towers WHERE press_id=$pressid AND tower_type='printing' ORDER BY tower_order";
    $dbTowers=dbselectmulti($sql);
    $towers=$dbTowers['data'];
    
    
    //need the operators
    $operators=array();
    $operators[0]='Please choose';
    $sql="SELECT A.* FROM employees A, employee_positions B WHERE A.position=B.id AND B.operator=1 ORDER BY firstname";
    $dbPress=dbselectmulti($sql);
    if ($dbPress['numrows']>0)
    {
        foreach ($dbPress['data'] as $p)
        {
            $operators[$p['id']]=$p['firstname']." ".$p['lastname'];
        }
    }
    
    
    //now need
    $buildpaper=false;
    $jobid=$_GET['jobid'];
    $sql="SELECT A.stats_id, A.pub_date, A.draw, A.startdatetime, A.enddatetime, A.notes_press, A.pub_id, A.run_id, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.id=$jobid AND A.pub_id=B.id AND A.run_id=C.id";
    $dbJob=dbselectsingle($sql);
    $job=$dbJob['data'];
    $statsid=$job['stats_id'];
    //see if there is a stats record
    $sql="SELECT * FROM job_stats WHERE id=$statsid";
    $dbStats=dbselectsingle($sql);
    if ($dbStats['numrows']>0)
    {
        $stats=$dbStats['data'];
        $actualstarttime=$stats['startdatetime_actual'];
        $startdate=date("Y-m-d H:i",strtotime($actualstarttime));
        
        $actualstoptime=$stats['stopdatetime_actual'];
        $stopdate=date("Y-m-d H:i",strtotime($actualstoptime));
        
        $goodcopytime=$stats['goodcopy_actual'];
        $gooddate=date("Y-m-d H:i",strtotime($goodcopytime));
        
        $counterstart=$stats['counter_start'];
        $counterstop=$stats['counter_stop'];
        $spoilsstartup=$stats['spoils_startup'];
        $spoilsrunning=$stats['spoils_running'];
        $spoilstotal=$stats['spoils_total'];
        $downtime=$stats['total_downtime'];
        $checklist=$stats['checklist_approved'];
        $jobpressoperator=$stats['job_pressoperator'];
        $jobpressmanids=$stats['job_pressman_ids'];
        $wastePlates=$stats['plates_waste'];
        $remakePlates=$stats['plates_remakes'];
        $jobnotes=stripslashes($job['notes_press']);
        //convert ids to array
        $jobpressmanids=explode("|",$jobpressmanids);
        if ($stats['tower_info']!='')
        {
            $buildpaper=false;
            $temptower=explode("|",$stats['tower_info']);
            $towers=array();
            foreach($temptower as $tt)
            {
                $tempt=explode(",",$tt);
                $towers[$tempt[0]]['id']=$tempt[0];    
                $towers[$tempt[0]]['tower_name']=$tempt[1];    
                $towers[$tempt[0]]['used']=$tempt[2];    
                $towers[$tempt[0]]['papertype']=$tempt[3];    
                $towers[$tempt[0]]['size']=$tempt[4];    
            }                                    
        } else {
            $buildpaper=true;
        }
    }
    /* 
    if ($buildpaper)
    {
        //get default paper type for the job
        $newtowers=array();
        
        foreach($towers as $tower)
        {
            //see if this tower is used
            $towerid=$tower['id'];
            $sql="SELECT * FROM layout_sections WHERE layout_id=$job[layout_id] AND towers LIKE '%$towerid%'";
            $dbUsed=dbselectmulti($sql);
            if ($dbUsed['numrows']>0)
            {
                $newtowers[$tower['id']]['used']=1;        
            } else {
                $newtowers[$tower['id']]['used']=0;
            }
            $newtowers[$tower['id']]['tower_name']=$tower['tower_name'];
            $newtowers[$tower['id']]['id']=$tower['id'];
            $newtowers[$tower['id']]['papertype']=$job['papertype'];
            //now to figure out how many pages on the plate - thus the paper size
            $layoutid=$job['layout_id'];
            $towerid=$tower['id'];
            $sql="SELECT * FROM layout_page_config WHERE layout_id=$layoutid AND tower_id=$towerid AND side='10' AND tower_row=1";
            $dbPages=dbselectmulti($sql);
            $pcount=0;
            if($dbPages['numrows']>0)
            {
                foreach($dbPages['data'] as $pages)
                {
                    if ($pages['page_number']!='0'){$pcount++;}    
                }
                
                //we're assuming everything is a newspaper lead. for commercial
                //leads, we'll just assume the press crew will set it.
                $rollwidth=$GLOBALS['broadsheetPageWidth']*$pcount;
                $newtowers[$tower['id']]['size']=$rollwidth;
            } else {
                $newtowers[$tower['id']]['size']='Please choose';
            }
            
        }
        $towers=$newtowers;
    }
    */
    //print "<form id='pressdata' name='pressdata' method=post>\n";
    print "<div class='label'>Publication</div><div class='input'>$job[pub_name]</div><div class='clear'></div>\n";
    print "<div class='label'>Run</div><div class='input'>$job[run_name]</div><div class='clear'></div>\n";
    print "<div class='label'>Draw</div><div class='input'>$job[draw]</div><div class='clear'></div>\n";
    print "<div class='label'>Start Time</div><div class='input'>".date("D, m/d/Y H:i",strtotime($startdate))."</div><div class='clear'></div>\n";
    print "<div class='label'>Good Copy Time</div><div class='input'>".date("D, m/d/Y H:i",strtotime($gooddate))."</div><div class='clear'></div>\n";
    print "<div class='label'>Stop Time</div><div class='input'>".date("D, m/d/Y H:i",strtotime($stopdate))."</div><div class='clear'></div>\n";
    print "<div class='label'>Counter Start</div><div class='input'>$counterstart</div><div class='clear'></div>\n";
    print "<div class='label'>Counter Start</div><div class='input'>$counterstop</div><div class='clear'></div>\n";
    print "<div class='label'>Spoils STARTUP</div><div class='input'>$spoilsstartup</div><div class='clear'></div>\n";
    print "<div class='label'>Total downtime</div><div class='input'>$downtime</div><div class='clear'></div>\n";
    print "<div class='label'>Operator</div><div class='input'>$operators[$jobpressoperator]</div><div class='clear'></div>\n";
    //now create a list of all pressman
    print "<div class='label'>Pressmen on shift</div><div class='input'>\n";
    if (count($pressmen)>0)
    {
        foreach($pressmen as $id=>$name)
        {
            if ($name!='Please choose')
            {
                if (in_array($id,$jobpressmanids))
                {
                    print "$name<br />\n";
                }
            }
        }
    }
    print "</div><div class='clear'></div>\n";
    //calculated stats area
    print "<b>Calculated stats for this job:</b><br />\n";
    print "<br />\n";
    print "<div class='label'>Gross Press:</div><div class='input'>$stats[gross]</div><div class='clear'></div>\n";
    print "<div class='label'>Waste:</div><div class='input'>$stats[waste_percent]%</div><div class='clear'></div>\n";
    print "<div class='label'>Spoils Total:</div><div class='input'>$stats[spoils_total]</div><div class='clear'></div>\n";
    print "<div class='label'>Job time:</div><div class='input'>$stats[run_time] minutes</div><div class='clear'></div>\n";
    print "<div class='label'>Overall avg speed:</div><div class='input'>$stats[run_speed] copies/hr</div><div class='clear'></div>\n";
    print "<div class='label'>Net avg speed:</div><div class='input'>$stats[good_runspeed] copies/hr</div><div class='clear'></div>\n";
    print "<div class='label'>Start Offset:</div><div class='input'>$stats[start_offset] minutes</div><div class='clear'></div>\n";
    print "<div class='label'>Finish Offset:</div><div class='input'>$stats[finish_offset] minutes</div><div class='clear'></div>\n";
    print "<div class='label'>Scheduled Runtime:</div><div class='input'>$stats[sched_runtime] minutes</div><div class='clear'></div>\n";
    print "<br />\n";
    print "<div class='label'>Black Pages:</div><div class='input'>$stats[pages_bw]</div><div class='clear'></div>\n";
    print "<div class='label'>Color Pages:</div><div class='input'>$stats[pages_color]</div><div class='clear'></div>\n";
    print "<br />\n";
    print "<div class='label'>Black plates:</div><div class='input'>$stats[plates_bw]</div><div class='clear'></div>\n";
    print "<div class='label'>Color Plates:</div><div class='input'>$stats[plates_color]</div><div class='clear'></div>\n";
    print "<div class='label'>Remake Plates:</div><div class='input'>$stats[plates_remake]</div><div class='clear'></div>\n";
    print "<div class='label'>Waste Plates:</div><div class='input'>$stats[plates_waste]</div><div class='clear'></div>\n";
    print "<br />\n";
    print "<div class='label'>Last Page:</div><div class='input'>$stats[last_page]</div><div class='clear'></div>\n";
    print "<div class='label'>Last Color:</div><div class='input'>$stats[last_colorpage]</div><div class='clear'></div>\n";
    print "<div class='label'>Last Plate:</div><div class='input'>$stats[last_plate]</div><div class='clear'></div>\n";
    print "<div class='label'>Man hours:</div><div class='input'>$stats[man_hours]</div><div class='clear'></div>\n";
    print "<div class='label'>Total Tons:</div><div class='input'>$stats[total_rons]</div><div class='clear'></div>\n";
    print "<div class='label'>Hours/ton:</div><div class='input'>$stats[hours_per_ton]</div><div class='clear'></div>\n";
    print "<div class='label'>Impressions/hour:</div><div class='input'>$stats[impressions_per_hour]</div><div class='clear'></div>\n";
    
    //now the press towers so we can specify roll sizes and types by tower
    print "<div class='label'>Paper Used</div><div class='input'>\n";
    if (count($towers)>0)
    {
        foreach ($towers as $tower)
        {
            if ($tower['used'])
            {
                $ptype=$tower['papertype'];
                $psize=$tower['size'];
            
            print "$tower[tower_name]: <input type=hidden name='tower_$tower[id]' id='tower_$tower[id]' value='$tower[tower_name]'>";
            print $papertypes[$ptype]." ";
            print $sizes[$psize];
            print "<br />\n";
            }
        }    
    }
    $sql="SELECT A.*, B.common_name, C.width FROM job_paper A, paper_types B, paper_sizes C WHERE A.job_id=$jobid AND A.papertype_id=B.id AND A.size_id=C.id";
    $dbRolls=dbselectmulti($sql);
    if ($dbRolls['numrows']>0)
    {
        print "Here is the paper used on this job:<br />\n";
        foreach($dbRolls['data'] as $roll)
        {
            print "$roll[common_name] - $roll[width], $roll[calculated_tonnage]MT - \$$roll[calculated_cost]<br />\n";
            
        }
    }
    print "</div><div class='clear'></div>\n";
    
    /*********************************************************************
    * NEED TO ADD A SECTION HERE TO DEAL WITH BENCHMARKS IF THEY ARE ENABLED
    *********************************************************************/
    print "<div class='label'>Notes:</div><div class='input'>$jobnotes<div class='clear'></div>\n";
    print "</div><div class='clear'></div>\n";
    //print "</form>\n";
}

 
function save_pressdata()
{
    //print_r($_POST);
    //ok, here goes the fun
    //grab the easy stuff first
    global $siteID;
    $schedstart=$_POST['schedule_start'];    
    $schedstop=$_POST['schedule_stop'];    
    $jobid=$_POST['jobid'];    
    $draw=$_POST['draw'];    
    $statsid=$_POST['statsid'];    
    $remakePlates=$_POST['remakePlates'];    
    $wastePlates=$_POST['wastePlates'];    
    $notes=addslashes($_POST['pressnotes']);
    $actualstart=$_POST['starttime'];
    $actualstop=$_POST['stoptime'];
    $goodtime=$_POST['goodtime'];
    if($_POST['setup_start']!='')
    {
        $setupstart=$_POST['setup_start'];
    } else {
        $setupstart='';
    }
    if($_POST['setup_stop']!='')
    {
        $setupstop=$_POST['setup_stop'];
    } else {
        $setupstop='';
    }
    if($setupstart!='' && $setupstop!='')
    {
        $setuptime=round((strtotime($setupstop)-strtotime($setupstart))/60,0);
    } else {
        $setuptime=0;
    }
    
    //math for run length calculation
    //get minutes between actual stop and start
    $start=strtotime($actualstart);
    $stop=strtotime($actualstop);
    
    $startoffset=round(($start-strtotime($schedstart))/60,2);
    $finishoffset=round(($stop-strtotime($schedstop))/60,2);
    $schedruntime=round((strtotime($schedstop)-strtotime($schedstart))/60,2);
    $printdate=date("Y-m-d",strtotime($startD));
    $downtime=$_POST['downtime'];
    $runningtime=round(($stop-$start)/60,2); //should give us running time in decimal minutes
    $goodrunningtime=$runningtime-$downtime;
    
    $counterstart=$_POST['counterstart'];
    $counterstop=$_POST['counterstop'];
    $spoilsstartup=$_POST['spoilsstartup'];
    
    //calculations for waste, speeds, spoils
    $gross=$counterstop-$counterstart;
    $spoilstotal=$gross-$draw;
    $spoilsrunning=$spoilstotal-$spoilsstartup;
    $wastepercent=round($spoilstotal/$draw*100,2);
    
    $runspeed=round($gross/($runningtime/60),0);
    $goodrunspeed=round($gross/($goodrunningtime/60),0);
    
    $checkapproved=$_POST['checklist'];
    $pressoperator=$_POST['pressoperator'];
    $pressmanids="";
    //find pressman now
    $pressmancount=0;
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,9)=="pressman_")
        {
            $key=str_replace("pressman_","",$key);
            $pressmanids.="$key|";
            $pressmancount++;    
        }
    }
    $pressmanids=substr($pressmanids,0,strlen($pressmanids)-1);
    
    //pull in job information
    $sql="SELECT * FROM jobs WHERE id=$jobid";
    $dbJobInfo=dbselectsingle($sql);
    $jobinfo=$dbJobInfo['data'];
    $pubdate=$jobinfo['pub_date'];
    $pubid=$jobinfo['pub_id'];
    $runid=$jobinfo['run_id'];
    $layoutid=$jobinfo['layout_id'];
    $folder=$jobinfo['folder'];
    $pressid=$jobinfo['press_id'];
    if($jobinfo['redo_job_id']!=0)
    {
        //means we are in a redo, in which case we actually need the original
        //so, we need to requery with jobid = the redo job id
        $reprintid=$jobid;
        $jobid=$jobinfo['redo_job_id'];
        $sql="SELECT * FROM jobs WHERE id=$jobid";
        $dbJobInfo=dbselectsingle($sql);
        $jobinfo=$dbJobInfo['data'];
        $pubdate=$jobinfo['pub_date'];
        $pubid=$jobinfo['pub_id'];
        $runid=$jobinfo['run_id'];
        
    } else {
        $reprintid=0;
    }
    //clear any entries from job_paper so we can recalculate in case someone moved something
    $sql="DELETE FROM job_paper WHERE job_id=$jobid";
    $dbDelete=dbexecutequery($sql);
    
    
    $layoutid=$jobinfo['layout_id'];
    $sql="SELECT * FROM layout_sections WHERE layout_id=$layoutid ORDER BY section_number";
    $dbLayout=dbselectmulti($sql);
    
    //get job section information
    $sql="SELECT * FROM jobs_sections WHERE job_id=$jobid";
    $dbJobSections=dbselectsingle($sql);
    $jobSections=$dbJobSections['data'];
    $overrun=0;
    $sinfo=array();
    foreach($dbLayout['data'] as $lay)
    {
        $sinfo[$lay['section_number']]['towers']=explode("|",$lay['towers']);
        $sinfo[$lay['section_number']]['overrun']=$jobSections['data']['section'.$lay['section_number'].'_overrun'];
        if ($jobSections['data']['section'.$lay['section_number'].'_overrun']>0)
        {
            $overrun=$jobSections['data']['section'.$lay['section_number'].'_overrun'];
        }
    }
    
    //work on newsprint and towers
    //first, build the towers info string
    //this loop is where we should account for overruns, since we are processing tower by 
    //tower and we know which towers were used for the section
    $towerinfo="";
    $totaltons=0;
    //update the folder and ribbon decks used for this job
    $lsql="SELECT * FROM layout WHERE id=$layoutid";
    $dbLayout=dbselectsingle($lsql);
    if ($jobinfo['data_collected']!=6)
    {    
        if ($dbLayout['data']['ribbon1_used']){$r1="OR press_towers.tower_name='Ribbon Deck 1'";}else{$r1='';}
        if ($dbLayout['data']['ribbon2_used']){$r2="OR press_towers.tower_name='Ribbon Deck 2'";}else{$r2='';}
        $sql="UPDATE press_towers SET impressions=impressions+$gross, running_time=running_time+".round($runningtime)." WHERE press_id=$pressid AND (tower_name='Folder $folder' $r1 $r2)";
        $dbUpdateFolder=dbexecutequery($sql);
        $error=$dbUpdateFolder['error'];
        //print "folder update with $sql<br />\n";
        
        
        /*
        $sql="UPDATE part_instances press_towers SET cur_time=cur_time+20, cur_count=cur_count+57000 WHERE press_parts.tower_id=press_towers.id AND (press_towers.tower_name='Folder $folder' $r1 $r2) AND part_instances.source_id=press_parts.id";
        $dbTowerPartUpdate=dbexecutequery($sql);
        $error.=$dbTowerPartsUpdate['error'];
        */
    }
    
    foreach($_POST as $key=>$value)
    {
        if (substr($key,0,6)=="tower_")
        {
            $towerid=str_replace("tower_","",$key);
            $ptype=$_POST['t_'.$towerid.'_papertype'];
            $psize=$_POST['t_'.$towerid.'_size'];
            if($ptype!=0){$used=1;}else{$used=0;}
            $towerinfo.="$towerid,$value,$used,$ptype,$psize|";
            
            //adjust gross by tower by removing the overrun quantity
            
            //only process paper if the papertype is not 0
            if ($ptype!=0)
            {
                //tick over the impressions only if it's never been done
                if ($jobinfo['data_collected']!=6)
                {
                    $sql="UPDATE press_towers SET impressions=impressions+$gross, running_time=running_time+".round($runningtime)." WHERE id=$towerid";
                    $dbTowerUpdate=dbexecutequery($sql);
                    //print "Tower update with $sql<br />\n";
                    /*
                    $sql="UPDATE part_instances, press_parts SET cur_time=cur_time+20, cur_count=cur_count+57000 WHERE press_parts.tower_id=$towerid AND part_instances.source_id=press_parts.id";
                    $dbTowerPartsUpdate=dbexecutequery($sql);
                    $error.=$dbTowerPartsUpdate['error'];
                    */
                }
                
                //before we move on, we need to calulate everything about the paper being consumed for this tower
                
                //need roll_size, page_width, page_length, factor, tonnage, price, cost
                //print "Working with size of $psize<br/>\n";
                $sql="SELECT width FROM paper_sizes WHERE id=$psize";
                $dbSize=dbselectsingle($sql);
                $rollwidth=$dbSize['data']['width'];
                //print "Found rollwidth of $rollwidth<br />\n";
                $sql="SELECT * FROM paper_types WHERE id=$ptype";
                $dbPapertype=dbselectsingle($sql);
                $papertype=$dbPapertype['data'];
                $pricePerTon=$papertype['price_per_ton'];
                $pagelength=$GLOBALS['broadsheetPageHeight'];
                
                //we are storing the actual page withd in the database, so
                /*
                $pagewidthid=$jobinfo['pagewidth'];
                $sql="SELECT width FROM paper_sizes WHERE id=$pagewidthid";
                $dbSize=dbselectsingle($sql);
                $pagewidth=$dbSize['data']['width'];
                */
                $pagewidth=$jobinfo['pagewidth'];
                
                $paperdataid=$papertype['paperdataid'];
                $pagesonroll=round($rollwidth/$pagewidth,0);
                
                
                //convert gsm to basisweight
                $basisweight=gsmToBasisweight($papertype['paper_weight'],$paperdataid);
                //get pages per pound
                $factor=newsprintPagesPerPound($basisweight,$pagewidth,$pagelength,$paperdataid);
                $factor=round($factor,5);
                //calculate tonnage
                //pages on roll * gross / factor should give us tonnage
                $tonnage=round($pagesonroll*$gross/$factor,2); //is in pounds right now
                //convert $tonnage to MT
                $tonnage=round(poundsToKilograms($tonnage)/1000,2); //should be in MT now
                $totaltons+=$tonnage;
                $cost=round($tonnage*$pricePerTon,2);
                /*
                print "This is what we have so far<br>Price per ton: $pricePerTon<br />";
                print "pdataid is: $paperdataid<br>";
                print "ptype is: $ptype<br>";
                print "psize is: $psize<br>";
                print "roll width: $rollwidth<br>";
                print "page length: $pagelength<br>";
                print "page width: $pagewidth<br>";
                print "pages on roll: $pagesonroll<br>";
                print "basisweight: $basisweight<br>";
                print "factor: $factor<br>";
                print "tonnage: $tonnage<br>";
                print "cost: $cost<br>";
                */
                $sql="INSERT INTO job_paper (job_id, pub_id, run_id, tower_id, papertype_id, 
                size_id, pub_date, print_date, roll_width, page_width, page_length, price_per_ton, 
                factor, calculated_tonnage, calculated_cost, site_id) VALUES ('$jobid', '$pubid', '$runid', '$towerid', '$ptype', '$psize', '$pubdate', '$printdate', '$rollwidth', '$pagewidth',
                 '$pagelength', '$pricePerTon', '$factor', '$tonnage', '$cost', '$siteID')";
                $dbInsertPaper=dbexecutequery($sql);
                $error.=$dbInsertPaper['error'];
            }
                
        }
    }
    $towerinfo=substr($towerinfo,0,strlen($towerinfo)-1);
    //calculate plates
    $sql="SELECT * FROM job_plates WHERE job_id=$jobid AND color=0 AND version=1";
    $dbBWplates=dbselectmulti($sql);
    $plates_bw=$dbBWplates['numrows'];
    $sql="SELECT * FROM job_plates WHERE job_id=$jobid AND color=1 AND version=1";
    $dbColorplates=dbselectmulti($sql);
    if ($dbColorplates['numrows']>0)
    {
        //if there is even one page on the plate that is color, the plate is color... lets check
        foreach($dbColorplates['data'] as $plate)
        {
            $plateid=$plate['id'];
            $sql="SELECT id FROM job_pages WHERE color=1 AND plate_id=$plateid";
            $dbCheckColor=dbselectmulti($sql);
            if($dbCheckColor['numrows']>0)
            {
                $plates_color+=3;
            }
            $plates_bw+=1;
        }
        
    }
    
    //calculate pages
    //need to look up section format to convert pages to standard pages
    $pages_bw=0;
    $pages_color=0;
    for($i=1;$i<=3;$i++)
    {
        $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='".$jobSections['section'.$i.'_code']."' AND color=0 AND version=1";
        $dbBWpages=dbselectmulti($sql);
        $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND section_code='".$jobSections['section'.$i.'_code']."' AND color=1 AND version=1";
        $dbColorpages=dbselectmulti($sql);
        $secname="section".$i."_producttype";
        $b=$dbBWpages['numrows'];
        $c=$dbColorpages['numrows'];
        if ($jobSections[$secname]==1 || $jobSections[$secname]==2)
        {
            //tab 
            if (($b2/2)<1)
            {
                $pages_bw+=0;   
            } else {
                $pages_bw+=ceil($b/2);
            
            }
            if (($c2/2)<1)
            {
                $pages_color+=0;   
            } else {
                $pages_color+=ceil($c/2);
            }
            
        }elseif($jobSections['section'.$i.'_producttype']==3)
        {
           //flexi
           if (($b/4)<0)
           {
               $pages_bw+=0;
           } else {
               $pages_bw+=ceil($b/4);
           }
            if (($c/4)<0)
           {
               $pages_color+=0;
           } else {
               $pages_color+=ceil($c/4);
           }
            
        }else{
            //broadsheet
            $pages_bw+=$b;
            $pages_color+=$c;
        }
        if (!$GLOBALS['treatGateFoldasFull'])
        {
            if ($jobSections['section'.$i.'_gatefold']){$pages_color--;}   
        }
        
    }
    $totalpages=$pages_bw+$pages_color;
    $totalimpressions=$totalpages*$gross;
    $manhours=($pressmancount*$runningtime)/60;
    if ($totaltons!=0)
    {
        $hoursperton=round($manhours/$totaltons,2);
    } else {
        $hoursperton=0;
    }
    if ($manhours!=0)
    {
        $impressionsperhour=round($impressionsperhour/$manhours);
    } else {
        $impressionsperhour=0;
    }
    //now get plate and page times
    $sql="SELECT * FROM job_plates WHERE job_id=$jobid ORDER BY black_receive DESC LIMIT 1";
    $dbLastPlate=dbselectsingle($sql);
    $lastPlate=$dbLastPlate['data']['black_receive'];
    
    $sql="SELECT * FROM job_pages WHERE job_id=$jobid ORDER BY page_release DESC LIMIT 1";
    $dbLastPage=dbselectsingle($sql);
    $lastPage=$dbLastPage['data']['page_release'];
    
    $sql="SELECT * FROM job_pages WHERE job_id=$jobid AND color=1 ORDER BY color_release DESC LIMIT 1";
    $dbLastColor=dbselectsingle($sql);
    $lastColor=$dbLastColor['data']['color_release'];
    
    
    //last thing before saving
    //if this is a reprint job we want to make sure to reset the job id to
    //the reprint id, otherwise we're going to mess with the original data
    //so....
    if ($reprintid!=0)
    {
        $jobid=$reprintid;
    }
    
    if ($lastColorPage!='')
    {
        $lastColorField='last_colorpage,';
        $lastColorValue="'$lastColor',";
        $lastColorUpdate="last_colorpage='$lastColor',";
    }
    if ($lastPage!='')
    {
        $lastPageField='last_page,';
        $lastPageValue="'$lastPage',";
        $lastPageUpdate="last_page='$lastPage',";
    }
    if ($lastPlate!='')
    {
        $lastPlateField='last_plate,';
        $lastPlateValue="'$lastPlate',";
        $lastPlateUpdate="last_plate='$lastPlate',";
    }
    if ($remakePlates==''){$remakePlates=0;}
    if ($wastePlates==''){$wastePlates=0;}
    //lets update the stats table now
    if($statsid==0 || $statsid=='')
    {
        //somehow didn't get created!
        $sql="INSERT INTO job_stats (job_id, added_by) VALUES ($jobid, 'jobPress.php line 3208')";
        $dbInsert=dbinsertquery($sql);
        $statsid=$dbInsert['insertid'];
        $sql="UPDATE jobs SET stats_id=$statsid WHERE id=$jobid";
        $dbUpdate=dbexecutequery($sql);
    }
    
    //updating an existing stat file
        $sql="UPDATE job_stats SET folder='$folder', startdatetime_goal='$schedstart',
         startdatetime_actual='$actualstart', stopdatetime_goal='$schedstop', 
         stopdatetime_actual='$actualstop', run_time='$runningtime', run_speed='$runspeed', 
         good_runspeed='$goodrunspeed', counter_start='$counterstart',  
         counter_stop='$counterstop', spoils_startup='$spoilsstartup', gross='$gross', 
         spoils_running='$spoilsrunning', spoils_total='$spoilstotal', draw='$draw', 
         goodcopy_actual='$goodtime', total_downtime='$downtime', waste_percent='$wastepercent',  
        checklist_approved='$checkapproved', job_pressoperator='$pressoperator', 
        job_pressman_ids='$pressmanids', job_pressman_count='$pressmancount',
        plates_bw='$plates_bw', plates_color='$plates_color', start_offset='$startoffset', 
        finish_offset='$finishoffset', sched_runtime='$schedruntime', pages_color='$pages_color',
        pages_bw='$pages_bw', $lastPlateUpdate $lastPageUpdate $lastColorUpdate plates_remake='$remakePlates', 
        man_hours='$manhours', total_tons='$totaltons', hours_per_ton='$hoursperton', impressions_per_hour='$impressionsperhour', 
        plates_waste='$wastePlates', tower_info='$towerinfo', setup_start='$setupstart', setup_stop='$setupstop', 
        setup_time='$setuptime' WHERE id=$statsid";
        $dbUpdate=dbexecutequery($sql);
         $error.=$dbUpdate['error'];
    
    $datatime=date("Y-m-d H:i:s");
    $databy=$_SESSION['cmsuser']['userid'];
   
    $jobsql="UPDATE jobs SET data_collected=1, dataset_time='$datatime', dataset_by='$databy', notes_press='$notes', stats_id='$statsid' WHERE id=$jobid";
    $dbJobUpdate=dbexecutequery($jobsql);
    $error.=$dbJobUpdate['error'];
    //die();
    /*********************************************************************
    * NEED TO ADD A SECTION HERE TO DEAL WITH BENCHMARKS IF THEY ARE ENABLED
    *********************************************************************/
    //die();
    if ($error!='')
    {
        setUserMessage('There was a problem updating the job information.<br />'.$error,'error');
    } else {
        setUserMessage('The job information has been successfully saved.','success');
    }
    redirect("?action=list");
    
                               
    
} 
dbclose();
  