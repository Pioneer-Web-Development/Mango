<?php
include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
    if ($_POST['jssubmitbtn']=='Save Data')
    {
        $action="Save Data";
    }
} else {
    $action=$_GET['action'];
}



switch ($action)
{
     
    case "Save Plan":
    save_plan('insert');
    break;
    
    case "Update Plan":
    save_plan('update');
    break;
    
    case "addplan":
    plans('add');
    break;
    
    case "editplan":
    plans('edit');
    break;
    
    case "deleteplan":
    plans('delete');
    break;
    
    case "packages":
    build_packages();
    break;
    
    case "print":
    ?>
    <script>
    window.open('printouts/insertplan.php?planid=<?php echo intval($_GET['planid']); ?>','Inserter Plan','width=700,height=800,toolbar=0,status=0,location=0,scrollbars=yes');
    </script>
    <?php
    plans('list');    
    break;
    
    
    default:
    plans('list');
    break;
}

function plans($action)
{
    global $siteID, $pubs;
    
    $planid=intval($_GET['planid']);
    
    $inserters=array();
    $inserters[0]="Please choose";
    $sql="SELECT * FROM inserters WHERE site_id=$siteID";
    $dbInserters=dbselectmulti($sql);
    if ($dbInserters['numrows']>0)
    {
        foreach($dbInserters['data'] as $inserter)
        {
            $inserters[$inserter['id']]=$inserter['inserter_name'];    
        }
    }
    if ($action=='add' || $action=='edit')
    {
       if ($action=='add')
       {
            $button='Save Plan';
            $pubid=$GLOBALS['defaultInsertPublication'];
            $runid=0;
            $pubdate=date("Y-m-d",strtotime("+1 day"));
            $inserterid=$GLOBALS['defaultInserter'];
            $address=0;
            $numpackages=1;
       } else {
         $button='Update Plan';  
         $sql="SELECT * FROM jobs_inserter_plans WHERE id=$planid";
         $dbPlan=dbselectsingle($sql);
         $plan=$dbPlan['data'];
         $pubid=$plan['pub_id'];
         $runid=$plan['run_id'];
         $pubdate=$plan['pub_date'];
         $request=$plan['inserter_request']; 
         $inserterid=$plan['inserter_id']; 
         $address=$plan['address']; 
         $numpackages=$plan['num_packages']; 
       }
       
      $runs[0]="Please choose";
      if ($pubid!=0)
      {
          $runsql="SELECT id, run_name FROM publications_insertruns WHERE pub_id=$pubid";
          $dbRuns=dbselectmulti($runsql);
          if ($dbRuns['numrows']>0)
          {
              foreach ($dbRuns['data'] as $insertrun)
              {
                  $runs[$insertrun['id']]=$insertrun['run_name'];
              }
          }
      }
      print "<form method=post>\n";
      make_select('inserter_id',$inserters[$inserterid],$inserters,'Choose Inserter');
      make_select('pub_id',$pubs[$pubid],$pubs,'Choose publication','','',false,"getInsertRuns();");
      make_select('run_id',$runs[$runid],$runs,'Choose Insert run');
      make_date('pubdate',$pubdate,'Publish Date');
      make_slider('num_packages',$numpackages,'# of packages','Total number of packages (includes Main)<br>ATTENTION: Reducing the number of packages once they have been set up will cause all inserts to be resert.',1,10,1);
      make_text('request',$request,'Press Request','Enter the number of papers requested, approximate in final number is not available. This is used to calculate estimated run time.');
      make_checkbox('address',$address,'Address label','Check if we will be printing address labels');
      make_hidden('planid',$planid);
      make_submit('submit',$button);
      print "</form>\n";
   } elseif ($action=='delete')
   {
       $planid=intval($_GET['planid']); 
       $sql="DELETE FROM jobs_inserter_plans WHERE id=$planid";
       $dbDelete=dbexecutequery($sql);
       if ($dbDelete['error']=='')
       {
            //get plan
            $sql="SELECT * FROM jobs_inserter_packages WHERE plan_id=$planid";
            $dbPackages=dbselectsingle($sql);
            if($dbPackages['numrows']>0)
            {
                foreach($dbPackages['data'] as $package)
                {
                    $packid=$package['id'];
                    $sql="SELECT * FROM jobs_packages_inserts WHERE plan_id=$planid AND package_id=$packid";
                    $dbInserts=dbselectmulti($sql);
                    
                    if($dbInserts['numrows']>0)
                    {
                        foreach($dbInserts['data'] as $insert)
                        {
                            $temp=removeInsert($insert['plan_id'],$insert['package_id'],$insert['insert_id'],$insert['insert_type'],$insert['hopper_id']);
                            $deleteids=$insert['id'].",";
                        }
                    }
                    $deleteids=substr($deleteids,0,strlen($deleteids)-1);
                    if($deleteids!='')
                    {
                        $sql="DELETE FROM jobs_packages_inserts WHERE id IN ($deleteids)";
                        $dbDelete=dbexecutequery($sql);
                    }
                    //delete the actual package
                    $sql="DELETE FROM jobs_inserter_packages WHERE id=$packid";
                    $dbDelete=dbexecutequery($sql);
                    $error.=$dbDelete['error']; 
                }
            }
            
            
        } else {
            $error=$dbDelete['error'];
        }
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the packaging plan.<br>'.$error,'error');
        } else {
            setUserMessage('The packaging plan has been successfully deleted.','success');
        }
    
        redirect("?action=list");    
   } else {
       $pubstartdate=date("Y-m-d",strtotime("-1 week"));
       $pubstopdate=date("Y-m-d",strtotime("+1 week"));
       $packstartdate=date("Y-m-d",strtotime("-1 week"));
       $packstopdate=date("Y-m-d",strtotime("+1 week"));
       $pub="pub_id>0";
       if ($_POST['search']=='Search')
       {
            $pubdate="AND pub_date<='".$_POST['pub_stopdate']."' AND pub_date>='".$_POST['pub_startdate']."'";
            $pubid=$_POST['search_pub'];
            if ($pubid!=0)
            {
                $pub="$and pub_id='$pubid'";
             }
            $pubstartdate=$_POST['pub_startdate'];
            $pubstopdate=$_POST['pub_stopdate'];
            
       } else {
           $pubdate="AND pub_date<='$pubstopdate' AND pub_date>='$pubstartdate'";
       }
       $search="<form method=post>\n";
       $search.= "Plans scheduled to publish between<br>";
       $search.=make_date('pub_startdate',$pubstartdate);
       $search.="and<br>";
       $search.=make_date('pub_stopdate',$pubstopdate);
       $search.="<br>Publication<br>";
       $search.=input_select('search_pub',$pubs[$_POST['search_pub']],$pubs);
       $search.="<input type=submit name='search' id='search' value='Search'></input>\n";
       $search.="</form>\n"; 
       
       
       //run sql
       $sql="SELECT * FROM jobs_inserter_plans WHERE $pub $pubdate AND site_id=$siteID ORDER BY pub_date DESC";
       //print $sql;
       $dbPlans=dbselectmulti($sql);
       tableStart("<a href='?action=addplan'>Add Inserter Plan</a>,<a href='inserts.php?action=list'>Show inserts</a>","Publication,Pub Date",8,$search);
       if ($dbPlans['numrows']>0)
       {
           foreach($dbPlans['data'] as $plan)
           {
               print "<tr>\n";
               $planid=$plan['id'];
               $pubid=$plan['pub_id'];
               $inserterid=$plan['inserter_id'];
               $pub=$pubs[$plan['pub_id']];
               $date=date("m/d/Y",strtotime($plan['pub_date']));
               print "<td>$pub</td>";
               print "<td>$date</td>";
               print "<td><a href='?action=packages&planid=$planid&pubid=$pubid'>Build Insert Packages</a></td>";
               print "<td><a href='inserterPackages.php?planid=$planid&pubid=$pubid'>List Packages</a></td>";
               print "<td><a href='?action=editplan&planid=$planid'>Edit</a></td>";
               print "<td><a href='?action=print&planid=$planid'>Print Plan</a></td>";
               print "<td><a class='delete' href='?action=deleteplan&planid=$planid'>Delete</a></td>";
               print "</tr>\n";
           }
       }
       tableEnd($dbPlans); 
   }
}
    
function save_plan($action)
{
    global $siteID;
    $pubid=$_POST['pub_id'];
    $runid=$_POST['run_id'];
    $inserterid=$_POST['inserter_id'];
    $planid=$_POST['planid'];
    $pubdate=$_POST['pubdate'];
    $numpackages=$_POST['num_packages'];
    $request=addslashes($_POST['request']);   
    if($_POST['address']){$address=1;}else{$address=0;}
    if($request==''){$request=10000;}
    //figure out which insert run this ties to
    $pubday=date("w",strtotime($pubdate));
        
   
    
    $sql="SELECT * FROM inserters WHERE id=$inserterid";
    
    
    $dbInserter=dbselectsingle($sql);
    $inserter=$dbInserter['data'];
    
    
    $singleoutspeed=$inserter['single_out_speed'];
    
    //we will default to single out to leave a larger window by default
    $speed=$singleoutspeed; 
    
    
    if($speed>0)
    {
        $runminutes=round(($request/$speed),0)+30; //pad by 30 because... :)
    } else {
        $runminutes=120;
    }
    $packdate=date("Y-m-d",strtotime($pubdate."- 1 day"));
    $packdate=$packdate." 19:00";//set default package start to 7pm the night before publication    
    $packstop=date("Y-m-d H:i",strtotime($packdate."+$runminutes minutes"));
       
    
    if ($action=='insert')
    {
        $sql="INSERT INTO jobs_inserter_plans (inserter_id,pub_id, run_id, pub_date, inserter_request, address, 
        site_id, num_packages) VALUES ('$inserterid', '$pubid', '$runid', '$pubdate', '$request', '$address', 
        '$siteID', '$numpackages')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        if ($error=='')
        {
            //by default on creation of a new plan, we'll auto-create a "MAIN" package with a date 1 day before the pub date
            $planid=$dbInsert['insertid'];
            
            for($i=1;$i<=$numpackages;$i++)
            {
                if($i==1){$pname='Main';}else{$pname='Package #'.$i;} 
                $sql="INSERT INTO jobs_inserter_packages (pub_id, pub_date, package_date, plan_id, inserter_id, 
                package_name, package_startdatetime, package_stopdatetime, inserter_request) VALUES ('$pubid', '$pubdate', 
                '$packdate', '$planid', '$inserterid', '$pname', '$packdate', '$packstop', '$request')";
                $dbInsert=dbinsertquery($sql);
                $error.=$dbInsert['error'];
            }
        }    
    } else {
        //lets see if the number of packages changes
        $sql="SELECT * FROM jobs_inserter_plans WHERE id=$planid";
        $dbCheck=dbselectsingle($sql);
        $initial=$dbCheck['data']['num_packages'];
        if($initial!=$numpackages)
        {
            //ok, this means that we need to delete packages, unset insert bindings
            $sql="DELETE FROM jobs_packages_inserts WHERE plan_id='$planid'";
            $dbDeletePackageInserts=dbexecutequery($sql);
            $sql="DELETE FROM jobs_inserter_packages WHERE plan_id='$planid'";
            $dbDeletePackages=dbexecutequery($sql);
            
            //now create new packages
            for($i=1;$i<=$numpackages;$i++)
            {
                if($i==1){$pname='Main';}else{$pname='Package #'.$i;} 
                $sql="INSERT INTO jobs_inserter_packages (pub_id, pub_date, package_date, plan_id, inserter_id, 
                package_name, package_startdatetime, package_stopdatetime, inserter_request) VALUES ('$pubid', '$pubdate', 
                '$packdate', '$planid', '$inserterid', '$pname', '$packdate', '$packstop', '$request')";
                $dbInsert=dbinsertquery($sql);
                $error.=$dbInsert['error'];
            }
        }
        $sql="UPDATE jobs_inserter_plans SET address='$address', inserter_id='$inserterid', pub_id='$pubid', 
        pub_date='$pubdate', run_id='$runid', inserter_request='$request', num_packages='$numpackages' WHERE id=$planid";
        $dbUpdate=dbexecutequery($sql);
        $error.=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the plan.<br>'.$error,'error');
    } else {
        setUserMessage('Insert plan has been successfully saved.','success');
        
    }
    redirect("?action=list");
    
}


function build_packages()
{
    global $pubs;
    $planid=intval($_GET['planid']);
    $sql="SELECT * FROM jobs_inserter_plans WHERE id=$planid";
    $dbPlan=dbselectsingle($sql);
    $plan=$dbPlan['data'];
    
    $pubid=$plan['pub_id'];
    $runid=$plan['run_id'];
    $pubdate=$plan['pub_date'];
    print "<input type='hidden' id='plan_id' value='$planid' />\n";
    print "<input type='hidden' id='pub_id' value='$pubid' />\n";
    print "<input type='hidden' id='pub_date' value='$pubdate' />\n";
    $pubname=$pubs[$pubid];
    $displaydate=date("m/d/Y",strtotime($pubdate));
    
    $sql="SELECT * FROM jobs_inserter_packages WHERE plan_id='$planid' ORDER BY package_name";
    $dbPackages=dbselectmulti($sql);
    
    
    $sql="SELECT B.*, A.insert_quantity, C.account_name FROM inserts_schedule A, inserts B, accounts C 
        WHERE A.insert_id=B.id AND A.pub_id=$pubid AND A.insert_date='$pubdate' AND B.advertiser_id=C.id 
        ORDER BY B.confirmed DESC, C.account_name";
    $dbInserts=dbselectmulti($sql);
        
    //get the inserts. They will then be displayed in a box at the top of the area. 800px for inserts, 200px for packages & jacket placeholder
    $totalpages=0;
    $totalinserts=0;
    if($dbInserts['numrows']>0)
    {
        
        foreach($dbInserts['data'] as $insert)
        {
            $totalpages+=$insert['tab_pages'];
            $totalinserts++;
        }
    }
    
    print "<p style='font-weight:bold;font-size:16px;float:left;'>Package Planner - Publication: $pubname for $displaydate&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total Inserts: $totalinserts &nbsp;&nbsp;&nbsp;Total Tab Pages: $totalpages</p>\n";
    print "<input type='button' value='Create a new package' onclick='addPackage();' style='float:right;margin-right:40px;' />\n";
    print "<div class='clear'></div>\n";    
    print "<b>Legend:</b> Green border = confirmed, Red Border = not confirmed, Red Text = not received, dashed border = sticky note, blue background = cloned insert, Double-click on insert to get details.<br />";
    print "<div class='ui-widget ui-state-default ui-corner-all' style='margin-bottom:10px;'>\n";
        //inserts go here
        
        //for testing, lets just grab 20 random inserts
        //$sql="SELECT B.*, A.insert_quantity FROM inserts_schedule A, inserts B WHERE A.insert_id=B.id ORDER BY RAND() LIMIT 20";
    $stickynotes[0]='None';
    
                
        
    $sql="SELECT B.*, A.insert_quantity, C.account_name FROM inserts_schedule A, inserts B, accounts C 
    WHERE A.insert_id=B.id AND A.pub_id=$pubid AND A.insert_date='$pubdate' AND B.advertiser_id=C.id 
    ORDER BY B.confirmed DESC, C.account_name";
    $dbInserts=dbselectmulti($sql);
    if($dbInserts['numrows']>0)
    {
        ?>
        <style type="text/css">
        .insert, .jacket, .insert-placeholder {
            margin-bottom:4px;
            float:left;
            width:116px;
            height:46px;
            margin-right:5px;
            border: 3px solid green;
            cursor: move;
            padding:2px;
            overflow: hidden;
            font-size:10px !important;
            font-weight:normal;
            background-color: white;
        }
        .package {
            width: 80px;
            height: 24px;
        }
        .notconfirmed {
            border: 3px solid red;
        }
        .station {
            font-weight:bold !important;
            font-size: 12px !important;
            overflow: hidden;
        }
        .sticky {
            border-style: dashed;
        }
        .cloned {
            background-color:#99CCFF;
        }
        
        .used {
            text-decoration: line-through;
            color: #00ff00 !important;
        }
        .stickylabel {
            color: black;
        }
        
        </style>
        <?php
        //build an array of all used inserts
        $sql="SELECT * FROM jobs_packages_inserts WHERE plan_id=$planid";
        $dbScheduled=dbselectmulti($sql);
        $scheduledInserts['insert']=array();
        $scheduledInserts['package']=array();
        if($dbScheduled['numrows']>0)
        {
            foreach($dbScheduled['data'] as $scheduled)
            {
                $scheduledInserts[$scheduled['insert_type']][]=$scheduled['insert_id']; 
            }
        }
        //do the same with packages
        if($dbPackages['numrows']>0)
        {
            foreach($dbPackages['data'] as $package)
            {
                if($package['sticky_note_id']>0)
                {
                    $scheduledInserts['insert'][]=$package['sticky_note_id']; 
                }
            }
        }
        print "<ul id='insertlist' style='float:left;width:80%;margin-right:4px;padding:0;padding-left:5px;padding-right:5px;border-right:thin solid black;'>\n";
        
        
        foreach($dbInserts['data'] as $insert)
        {
            if($insert['weprint_id']>0)
            {
                $sql="SELECT A.pub_id, B.run_name FROM jobs A, publications_runs B WHERE A.id=$insert[weprint_id] AND A.run_id=B.id";
                $dbJobs=dbselectsingle($sql);
                $accountname=stripslashes($dbJobs['data']['run_name']);
            } else {
                $sql="SELECT * FROM accounts WHERE id=$insert[advertiser_id]";
                $dbAccount=dbselectsingle($sql);
                $accountname=stripslashes($dbAccount['data']['account_name']);
            }
            
            
            if($insert['clone_id']!=0){$accountname.=" CLONED";}
            //each insert holder will be 120px wide
            
            if(!in_array($insert['id'],$scheduledInserts['insert']))
            {
                $request=$insert['insert_quantity'];
                $binsertname=$accountname." ".stripslashes($insert['insert_tagline']);
                $binsertname=str_replace("'","",$binsertname);
                
                if((!$insert['confirmed'] && $GLOBALS['allowScheduleUnconfirmedInserts']) || $insert['confirmed'])
                {
                    $notconfirmed='';
                } else {
                    $notconfirmed='notconfirmed';
                }
                if($insert['sticky_note']){$sticky='sticky';$type='sticky';}else{$sticky='';$type='insert';}
                if($insert['clone_id']>0){$cloned='cloned';}else{$cloned='';}
                $insertname="<b>$binsertname</b>";
                if(!$insert['received']){$insertname="<span style=\'color:red;\'>$insertname</span>";}
                $insertpages=$insert['tab_pages'];
                $insertinfo=$insertname."<br><b>Pages:</b> $insertpages <b>Request: </b>$request";
                print "<li id=\"insert_$insert[id]\" rel=\"$insertname\" data-classes=\"insert $notconfirmed $sticky $cloned\" data-type=\"$type\" data-info=\"$insertinfo\" data-name=\"$binsertname\" data-id=\"$insert[id]\" data-clone=\"$insert[clone_id]\" class=\"insert $notconfirmed $sticky $cloned\">";
                print $insertinfo;
                print "</li>\n";
            }
              
                
        }
    } else {
        print "<div class='ui-widget ui-corner-all ui-state-error' style='padding:20px;width:500px;margin-left:auto;margin-right:auto;margin-top:20px;margin-bottom:20px;'>
        Sorry, there are currently no inserts for this publication/date
        </div>";
    }
    print "<div class='clear'></div>\n";
    print "</ul><!--closing insert list -->\n";
    
    
    print "<ul id='packageInsertList' style='float:left;width:15%;min-width:130px;padding:0;margin:10px 0px 0px 10px;text-index:0px;'>\n";
    //add the generic jacket
    print "<li id='jacket_0' class='jacket package' data-type='package' rel='Generic Jacket' style='color:white;background-color:#006600 !important'>\n";
        print '<b>Generic Jacket</b>';
    print "</li>\n";
    if($dbPackages['numrows']>0 && count($scheduledInserts)>0)
    {
        foreach($dbPackages['data'] as $package)
        {
            if(!in_array($package['id'],$scheduledInserts['package']))
            {
                $pname=stripslashes($package['package_name']);
                print "<li id='package_$package[id]' class='insert package' data-type='package' data-id='$package[id]' data-info='$pname' data-name='$pname' data-clone='0' style='font-size:12px;font-weight:bold;text-align:center;vertical-align:center;list-style:none;'>\n";
                    print $pname;
                print "</li>\n";
            } 
        }
       
    }
    print "</ul><!-- closing package as insert list -->\n";
    
    print "<div class='clear'></div>\n";
    
    /*
    if(count($stickynames)>0)
    {
        print "<div style='margin-left:10px;margin-bottom:10px;width:100%;'>\n";
        $stickynames=implode(",",$stickynames);
        print "<b>The following sticky notes have been scheduled for today:</b> $stickynames<br>";
        if($GLOBALS['stickyNoteLocation']=='press')
        {
            print "Please make sure the press/stacker crew is aware that there will be an insert, and make sure they have all the product.<br>";
        } else {
            print "Please edit the appropriate package settings and set the package sticky note accordingly.<br>";
        }
        print "</div>\n";
    } 
    */   
    print "</div><!-- closing the insert area -->\n";
    
    
    //build a list of inserters
    $sql="SELECT * FROM inserters";
    $dbInserters=dbselectmulti($sql);
    $inserters[0]='Please select';
    if($dbInserters['numrows']>0)
    {
        foreach($dbInserters['data'] as $inserter)
        {
            $inserters[$inserter['id']]=stripslashes($inserter['inserter_name']);
        }    
    }
    
            
    
    //get the packages
    //this query is called up in the jackets & packages area
    print "<div id='packagewindow'>\n";
    if($dbPackages['numrows']>0)
    {
        foreach($dbPackages['data'] as $package)
        {
            //generate the package area. we are working in an area 1000px wide - max of 5 packages at 200px with 10px left margin
            $sql="SELECT * FROM inserters WHERE id=$package[inserter_id]";
            $dbInserter=dbselectsingle($sql);
            $inserter=$dbInserter['data'];
            $candoubleout=$inserter['can_double_out'];
            $inserterturn=$inserter['inserter_turn'];
            $singleout=false;
            
            
            //later these will need to be set when loading a saved set of packages
            if($package['double_out'])
            {
               $extraDisplay='none';
               $doubleDisplay='block';
               $stationWidth='113px';
               $doubleoutcheck='checked';
            } else {
               $extraDisplay='block';
               $doubleDisplay='none';
               $stationWidth='180px';
               $doubleoutcheck='';
            }
            
            $extraDisplayed=false;
            
            
            
            print "<div id='packagearea_$package[id]' class='ui-widget' style='width:240px;margin-right:20px;float:left;'>\n";
            
                print "<div class='ui-widget ui-widget-header' style='width:100%;padding:5px;'>\n";
                    print "<div style='float:left;width:200px;'><span id='package_$package[id]_name'>".stripslashes($package['package_name'])."</span><input id='package_$package[id]_nameedit' value='".stripslashes($package['package_name'])."' style='display:none;width:200px;' /></div>";
                    print "<div style='float:right;'><img src='artwork/icons/gear_48.png' alt='Remove Insert' width=24 onClick='expandSettings($package[id]);' /></div>\n";
                    print "<div class='clear'></div>\n";
                    print "<div id='settings_$package[id]' style='width:190px;margin-left:auto;margin-right:auto;display:none;'>\n";
                        print "Run time: ".make_datetime('package_'.$package['id'].'_dt',date("Y-m-d H:i",strtotime($package['package_startdatetime'])));
                        print "<br />Select Inserter:<br>";
                        print "<select id='package_$package[id]_inserter' style='width:190px;'>\n";
                        foreach($inserters as $key=>$insertername)
                        {
                            if($key==$package['inserter_id'])
                            {
                                $selected='selected';
                            } else {
                                $selected='';
                            }
                            print "<option value='$key' $selected>$insertername</option>\n";
                        }
                        print "</select>\n";
                        print "<br>Production draw request:<br><input type='text' id='package_$package[id]_request' style='width:184px;' value='$package[inserter_request]'/>\n";
                        if($candoubleout)
                        {
                            $toggleDoubleCheck='inline';
                        } else {
                            $toggleDoubleCheck='none';
                        }
                        print "<br><span id='package_$package[id]_doubleout_display' style='display:$toggleDoubleCheck'><input type=checkbox id='package_$package[id]_doubleout' name='package_$package[id]_doubleout' $doubleoutcheck/>
                        <label for='package_$package[id]_doubleout'> Double-out package</label></span><br>\n";
                        
                        print "<input type='button' value='Delete' class='delete' onClick='deletePackage($package[id]);' />";
                        print "<input type='button' value='Save' onClick='saveSettings($package[id]);' style='float:right;' />";
                        print "<input type='hidden' id='package_$package[id]_originalinserter' value='$package[inserter_id]' />\n";
                        print "<input type='hidden' id='package_$package[id]_originaldoubleout' value='$package[double_out]' />\n";
                        print "<input type='hidden' id='package_$package[id]_originalrequest' value='$package[inserter_request]' />\n";
                        print "<input type='hidden' id='package_$package[id]_open' value='false' />\n";
                            
                    print "</div><!-- closes settings panel for package $package[id] -->\n";
                print "<div class='clear'></div>\n";
                print "</div><!-- closing the header area for package $package[id]-->\n";
            //add a package stats area
            print "<div class='ui-widget ui-widget-content' style='width:100%;padding:5px;margin-bottom:2px;'>\n";
            print "Page count: <span id='package_$package[id]_pagecount'>$package[tab_pages]</span><br>\n";
            print "# Inserts: <span id='package_$package[id]_insertcount'>$package[total_inserts]</span><br>\n";
            print "Package Weight: <span id='package_$package[id]_weight'>$package[total_weight]</span><br>\n";
            print "</div><!--closing the package stats area -->\n";
                
            
            print "<div id='package_$package[id]_stations'>\n";
            
            
            //here we create a series of drop containers for inserts. One for each station (double-out uses same);
            //we need to know the numbers of stations in the specified inserter
            //get the stations
            $sql="SELECT * FROM inserters_hoppers WHERE inserter_id=$package[inserter_id] ORDER BY hopper_number";
            $dbStations=dbselectmulti($sql);
            if($dbStations['numrows']>0)
            {
                //run through them fast to get counts
                $minDoubleHopper=$inserterturn+1; //one more than where the turn is
                $i=0;
                $stations[0]=0;
                foreach($dbStations['data'] as $station)
                {
                    if($i==0)
                    {
                        $minHopper=$station['hopper_number'];
                        $i++;
                    }
                    $stations[$station['hopper_number']]=$station['id'];
                    $maxDoubleHopper=$station['hopper_number']; //keep setting in, the last value will be the largest
                }
                
                if($package['sticky_note_id']!=0)
                {
                    $sql="SELECT * FROM inserts WHERE id=$package[sticky_note_id]";
                    $dbSN=dbselectsingle($sql);
                    $sn=$dbSN['data'];
                    
                    
                    
                    
                    if($sn['weprint_id']>0)
                    {
                        $sql="SELECT A.pub_id, B.run_name FROM jobs A, publications_runs B WHERE A.id=$sn[weprint_id] AND A.run_id=B.id";
                        $dbJobs=dbselectsingle($sql);
                        $accountname=stripslashes($dbJobs['data']['run_name']);
                    } else {
                        $sql="SELECT * FROM accounts WHERE id=$sn[advertiser_id]";
                        $dbAccount=dbselectsingle($sql);
                        $accountname=stripslashes($dbAccount['data']['account_name']);
                    }
                    
                    $request=$sn['insert_quantity'];
                    $insertname=$accountname." ".stripslashes($sn['insert_tagline']);
                    $insertname=str_replace("'","",$insertname);
                    $insertpages=$insert['tab_pages'];
                    $sinfo=$insertname."<br><b>Pages:</b> $insertpages <b>Request: </b>$request";
                
                    if((!$sn['confirmed'] && $GLOBALS['allowScheduleUnconfirmedInserts']) || $sn['confirmed'])
                    {
                        $notconfirmed='';
                    } else {
                        $notconfirmed='notconfirmed';
                    }
                    if($sn['sticky_note']){$sticky='sticky';$type='sticky';}else{$sticky='';$type='insert';}
                    if($sn['clone_id']>0){$cloned='cloned';}else{$cloned='';}
                    $sclasses='insert '.$notconfirmed.' '.$sticky.' '.$cloned;
                    $sname=$insertname;
                    $sclone=$sn['clone_id'];
                    $sid=$sn['id'];
                } else {
                    $accountname='';
                    $sinfo='';
                    $sname='';
                    $sclone=0;
                    $sid=0;
                    $sclasses='sticky';
                }
                $stickyWidth=(str_replace("px","",$stationWidth)-30)."px";
                
                print "<div style='float:left;font-weight:bold;width:50px;'>Sticky Note</div><div id='sticky_$package[id]' data-handler=\"sticky\" data-classes=\"$sclasses\" data-type=\"sticky\" data-id=\"$sid\" data-name=\"$sname\" data-info=\"$sinfo\" data-clone=\"$sclone\" data-packageid=\"$package[id]\" data-stationid='0' class='station ui-widget ui-widget-content' style='float:left;width:$stickyWidth;height:30px;'>$accountname</div>";
                 print "<div style='float:left;margin-left:4px;width:20px;padding-top:5px;'>
                          <img src='artwork/icons/cancel_gray_48.png' width=20 onclick=\"removeInsert($package[id],'sticky',0);\" />\n</div>\n";
                 print "<div class='clear'></div>\n";
                
                foreach($dbStations['data'] as $station)
                {
                    $stationNumber=$station['hopper_number'];
                    //if(($stationNumber<=$inserterturn && $candoubleout) || $singleout)
                    if($stationNumber>$inserterturn && $candoubleout && !$extraDisplayed)
                    {
                        print "<div id='extraHoppers_$package[id]' style='display:$extraDisplay;'>\n";
                        $extraDisplayed=true;    
                    }
                    
                    print "<div style='width:240px;margin-bottom:2px;'>\n";
                        print "<div class='doubleouts_$package[id]' style='display:$doubleDisplay;float:left;width:60px;margin-right:5px;'>\n";
                        if($candoubleout && $station['hopper_number']<$minDoubleHopper)
                        {
                            $guessHopper=$maxDoubleHopper-intval($station['hopper_number'])+1;
                            
                            //ok, lets see if there is a pairing for this double-out setup
                            $sql="SELECT * FROM jobs_packages_hopper_pairings WHERE package_id='$package[id]' AND hopper_id='$station[id]'";
                            $dbPairing=dbselectsingle($sql);
                            /*
                            print "<pre>\n";
                            print_r($dbPairing);
                            print "</pre>\n";
                            */
                            if($dbPairing['numrows']>0)
                            {
                                $guessHopper=$dbPairing['data']['secondary_value'];   
                            } else {
                                //add a record to jobs_packages_hoppers_pairings
                                $secondStationID=$stations[$guessHopper];
                                $sql="INSERT INTO jobs_packages_hopper_pairings (package_id, hopper_id, secondary_id, secondary_value) VALUES 
                                ('$package[id]', '$station[id]','$secondStationID','$guessHopper')";
                                $dbInsert=dbinsertquery($sql);
                                if($dbInsert['error']!='')
                                {
                                    print $dbInsert['error'];
                                }
                            }
                            
                            print "<div id='$station[id]_$package[id]_locked' style='float:left;width:20px;display:block;padding-top:6px;'>\n  <img onclick='toggleLock($station[id],$package[id],\"open\",$minHopper,$inserterturn,$stationNumber);' src='/artwork/icons/lock_48.png' alt='Locked' height=14/>\n</div>\n";    
                            print "<div id='$station[id]_$package[id]_unlocked' style='float:left;width:20px;display:none;padding-top:6px;'>\n  <img onclick='toggleLock($station[id],$package[id],\"close\",$minHopper,$inserterturn,$stationNumber);' src='/artwork/icons/lock_open_48.png' alt='Locked' height=14/>\n</div>\n";    
                            print "<div id='$station[id]_$package[id]_second' style='float:left;width:40px;padding-top:4px;'>\n";
                            print "<select id='$station[id]_$package[id]_select' style='float:left;display:none;width:40px;background:none;border:none;font-weight:bold;font-size:18px;'>\n";
                            //build select options
                            print "<option value='0'>None</option>\n";
                            for($i=$minDoubleHopper;$i<=$maxDoubleHopper;$i++)
                            {
                                if($i==$guessHopper){$selected='selected';}else{$selected='';}
                                print "<option value='$i' $selected>$i</option>\n";
                            }
                            print "</select>\n";
                            print "<span id='$station[id]_$package[id]_selectvalue' style='float:left;display:inline;width:40px;background:none;border:none;font-weight:bold;font-size:18px;'>$guessHopper</span>\n";
                            
                            print "</div>\n";
                                
                            print "<div class='clear'></div>\n";
                            
                        }
                        print "</div>\n";
                        print "<div style='float:left;width:30px;margin-right:3px;text-align:right;font-weight:bold;font-size:18px;padding-top:4px;'>\n";
                        if($station['jacket_station'])
                        {
                            print 'J-';
                        }
                        print $stationNumber;
                        print "</div>\n";
                        
                        //ok, lets see if we have an insert for this slot
                        $sql="SELECT * FROM jobs_packages_inserts WHERE package_id='$package[id]' AND hopper_id='$station[id]'";
                        $dbCheckInsert=dbselectsingle($sql);
                        if($dbCheckInsert['numrows']>0)
                        {
                            //woohoo! there is an insert booked for this package and station
                            $insertid=$dbCheckInsert['data']['insert_id'];
                            $inserttype=$dbCheckInsert['data']['insert_type'];
                            
                            //now we need a little detail about the insert
                            if($inserttype=='insert')
                            {
                                $sql="SELECT A.*, B.account_name FROM inserts A, accounts B WHERE A.id=$insertid AND A.advertiser_id=B.id";
                                $dbInsertInfo=dbselectsingle($sql);
                                $fullinsertinfo=$dbInsertInfo['data'];
                                $insertname=stripslashes($fullinsertinfo['account_name'])." ".stripslashes($fullinsertinfo['insert_tagline']);
                                $insertname=str_replace("'","",$insertname);
                        
                                $insertpages=$fullinsertinfo['tab_pages'];
                                $request=$insert['insert_quantity'];
                                $insertname="<b>$insertname</b>";
                                $insertinfo=$insertname."<br><b>Pages: </b>".$insertpages." <b>Request: </b>$request";    
                                $cloneid=$fullinsertinfo['clone_id'];
                                $insertclasses='insert';
                                if($fullinsertinfo['sticky_note']){$insertclasses.=' sticky';}
                                if($cloneid>0){$insertclasses.=' cloned';}
                            } elseif ($inserttype=='package')
                            {
                                print "<!-- package found for this station with $insertid as id -->\n";
                                $sql="SELECT * FROM jobs_inserter_packages WHERE id=$insertid";
                                $dbInsertInfo=dbselectsingle($sql);
                                $fullinsertinfo=$dbInsertInfo['data'];
                                $insertname=stripslashes($fullinsertinfo['package_name']);
                                $insertname=str_replace("'","",$insertname);
                                $insertpages=$fullinsertinfo['tab_pages'];
                                $insertinfo=$insertname;
                                $insertclasses='package'; 
                            } elseif ($inserttype=='jacket')
                            {
                                $insertname="Generic Jacket";
                                $insertpages=4;
                                $insertinfo="Generic Jacket";
                                $insertclasses='jacket';
                            }
                            
                        } else {
                            $insertid='0';
                            $inserttype='';
                            $insertinfo='';
                            $insertpages='0';
                            $insertname='';
                            $insertclasses='insert';
                            $cloneid=0;
                        }
                        
                        //$insertinfo=addslashes($insertinfo);
                        print "<div id='pack_$package[id]-station_$station[id]' data-packageid='$package[id]' data-stationid='$station[id]' data-handler='regular' data-info=\"$insertinfo\" data-id=\"$insertid\" data-type=\"$inserttype\" data-classes=\"insert\" data-name=\"$insertname\" data-clone='$cloneid' class='station ui-widget ui-widget-content' style='float:left;width:$stationWidth;height:30px;'>\n";
                        print $insertname;
                        print "</div>\n";
                        print "<input type='hidden' id='pack_$package[id]-station_$station[id]-insert_type' value='$inserttype' />\n";
                        print "<input type='hidden' id='pack_$package[id]-station_$station[id]-insert_id' value='$insertid' />\n";
                        print "<input type='hidden' id='pack_$package[id]-station_$station[id]-insert_info' value='$insertinfo' />\n";
                        print "<input type='hidden' id='pack_$package[id]-station_$station[id]-insert_pages' value='$insertpages' />\n";
                        print "<input type='hidden' id='pack_$package[id]-station_$station[id]-hopper_number' value='$station[hopper_number]' />\n";
                        print "<input type='hidden' id='pack_$package[id]-station_$station[hopper_number]-station_id' value='$station[id]' />\n";
                        print "<div style='float:left;margin-left:4px;width:20px;padding-top:5px;'>
                          <img src='artwork/icons/cancel_gray_48.png' width=20 onclick='removeInsert($package[id],$station[id],0);' />\n</div>\n";
                        print "<div class='clear'></div>\n";
                            
                    print "</div><!--closes the station $station[id] in package $package[id] -->\n";
                        
                }
                if($extraDisplayed)
                {
                    print "</div><!--closing the extra hopper holder for package $package[id] -->\n";    
                }
            } else {
                print "Inserter is not configured.";
            }
            print "<div class='clear'></div>\n";
            print "</div><!-- closes the package stations area -->\n";
            
            
            print "</div><!-- closes the package widget -->\n";
        }
        
    } else {
        print "There are no packages set up yet.";
    }
    print "</div><!--closing package window -->\n";
    ?>
    <script>
        var planID=$('#plan_id').val();
        
                    
        function toggleDoubleOuts(doubleout,packageID)
        {
            if(doubleout==1)
            {
                $('#package_'+packageID+'_stations .station').width('113');
                $('.doubleouts_'+packageID).show();
                $('#extraHoppers_'+packageID).slideUp();
            } else {
                $('.doubleouts_'+packageID).hide();
                $('#extraHoppers_'+packageID).slideDown();
                $('#package_'+packageID+'_stations .station').width('180');
            }
        }
        
        
        function toggleLock(stationID,packageID,type,minHopper,maxHopper,straightHopperNumber,stationNumber)
        {
            if(type=='open')
            {
                //we are unlocking a spinner
                //hide the lock version, enable the spinner
                $('#'+stationID+'_'+packageID+'_locked').hide();
                $('#'+stationID+'_'+packageID+'_unlocked').show();
                $('#'+stationID+'_'+packageID+'_selectvalue').hide();
                $('#'+stationID+'_'+packageID+'_select').show();
            } else {
                //attempting to lock the value. We need to see if any other sliders have this value first!!
                //we don't want the same number used twice :)
                
                var cvalue=$('#'+stationID+'_'+packageID+'_select').val();
                    
                var success=true;
                for(var j=minHopper;j<=maxHopper;j++)
                {
                    
                    //grab the corresponding stationid for this hopper number
                    var currentStationID=$('#pack_'+packageID+'-station_'+j+'-station_id').val();
                    if(currentStationID!=stationID) // don't check the station that is checking
                    {
                        var check=$('#'+currentStationID+'_'+packageID+'_selectvalue').html();
                        if(cvalue==check){
                            success=false;
                            alert('Station number '+j+' second station is currently\npaired with that station for double-out operations.\n\nPlease select a different pairing.');
                        }
                    }    
                }
                
                //if it's ok, hide the unlock version, disable the spinner
                if(success)
                {
                    //do a database update
                    $.ajax({
                        type: "POST",
                        url: "includes/ajax_handlers/saveInsertPackages.php",
                        data:{action: 'updateStation',
                            packageid: packageID,
                            stationid: stationID,
                            inserterid: $('#package_'+packageID+'_originalinserter').val(),
                            secondaryvalue: cvalue
                        },
                        dataType: 'json',
                        success: function(response){
                            //do stuff
                            if(response.status=='success')
                            {
                                $('#'+stationID+'_'+packageID+'_unlocked').hide();
                                $('#'+stationID+'_'+packageID+'_locked').show();
                                $('#'+stationID+'_'+packageID+'_selectvalue').html(cvalue);
                                $('#'+stationID+'_'+packageID+'_selectvalue').show();
                                $('#'+stationID+'_'+packageID+'_select').hide();
                            } else {
                                alert(response.error_message);
                            }
                        }  
                    });
                    
                    
                } else {
                    //put the select value back to the original
                    $('#'+stationID+'_'+packageID+'_select').val($('#'+stationID+'_'+packageID+'_selectvalue').html());
                }
            }   
        }
        
        function expandSettings(packID)
        {
            if($('#package_'+packID+'_open').val()=='false')
            {
                $('#settings_'+packID).slideDown();
                $('#package_'+packID+'_name').hide();   
                $('#package_'+packID+'_nameedit').show();
                $('#package_'+packID+'_open').val('true');
            } else {
                $('#package_'+packID+'_open').val('false');
                $('#settings_'+packID).slideUp();
                $('#package_'+packID+'_name').show();   
                $('#package_'+packID+'_nameedit').hide(); 
            }
               
        }
        
        function saveSettings(packID)
        {
            //do an ajax call to save any changes. This may also mean enabling secondary 
            //(double-out) stations or a whole new setup
            
            var packdate=$('#package_'+packID+'_dt').val();
            var packname=$('#package_'+packID+'_nameedit').val();
            var inserterid=$('#package_'+packID+'_inserter').val();
            var oinserterid=$('#package_'+packID+'_originalinserter').val();
            var request=$('#package_'+packID+'_request').val();
            var orequest=$('#package_'+packID+'_originalrequest').val();
            var doubleout=$('#package_'+packID+'_doubleout').prop("checked");
            if(doubleout){doubleout=1;}else{doubleout=0;}
            var odoubleout=$('#package_'+packID+'_originaldoubleout').val();
            var stickyid=$('#package_'+packID+'_sticky').val();
            
            
            $.ajax({
                type: "POST",
                url: "includes/ajax_handlers/saveInsertPackages.php",
                data:{action: 'saveSettings',
                    planid: planID,
                    packid: packID,
                packdate: packdate,
                packname: packname,
                inserterid: inserterid,
                oinserterid: oinserterid,
                request: request,
                orequest: orequest,
                doubleout:doubleout,
                odoubleout:odoubleout,
                stickyid:stickyid},
                dataType: 'json',
                success: function(response){
                    //do stuff
                    if(response.status=='success')
                    {
                        if(inserterid!=oinserterid)
                        {
                            //nothing to worry about, just check double out toggle and close the settings
                            //update the "original inserter id" field with new value
                            $('#package_'+packID+'_originalinserter').val(inserterid);
                            $('#package_'+packID+'_stations').html(response.inserter_html);
                            if(response.can_double_out)
                            {
                               $('#package_'+packID+'_doubleout_display').show(); 
                            }
                            $( ".station" ).droppable({
                                accept: ".insert, .jacket",
                                activeClass: "ui-state-default",
                                hoverClass: "ui-state-hover",
                                drop: function( event, ui ) {
                                    handleDrop(event,ui)
                                }
                            });
                            
                            if(response.inserts_removed>0)
                            {
                                //need to add all inserts that were attached to the previous inserter back to the pile
                                $.each(response.inserts,function(j,insert){
                                    var newInsert = $("<li id='"+response.inserts[j].insert_type+"_"+response.inserts[j].insert_id+"' class='insert' rel='"+response.inserts[j].insert_name+"'/>");
                                    newInsert.html(response.inserts[j].insert_text);
                                    if(response.inserts[j].insert_type=='insert')
                                    {
                                        $('#insertlist').append(newInsert);
                                    } else {
                                        $('#packageInsertList').append(newInsert);
                                    }
                                });
                                $("#insertlist").sortable({placeholder: "insert-placeholder ui-state-highlight"});
                                $("#packageInsertList").sortable({placeholder: "insert-placeholder ui-state-highlight"});
                                $('#package_'+response.package_id+'_pagecount').html(response.pages);
                                $('#package_'+response.package_id+'_insertcount').html(response.count);
                                $('#package_'+response.package_id+'_weight').html(response.weight);
                            }
                            if(response.secondary_id>0)
                            {
                                $('#package_'+response.secondary_id+'_pagecount').html(response.secondary_pages);
                                $('#package_'+response.secondary_id+'_insertcount').html(response.secondary_count);
                                $('#package_'+response.secondary_id+'_weight').html(response.secondary_weight);
                            }
                            if(response.third_id>0)
                            {
                                $('#package_'+response.third_id+'_pagecount').html(response.third_pages);
                                $('#package_'+response.third_id+'_insertcount').html(response.third_count);
                                $('#package_'+response.third_id+'_weight').html(response.third_weight);
                            }
                            if(response.can_double_out=='1')
                            {
                               $('#package_'+packID+'_doubleout_display').show(); 
                            } else {
                               $('#package_'+packID+'_doubleout_display').hide(); 
                            }
                            
                            //by default for any new station setup, set double out to false
                            $('#package_'+packID+'_doubleout').prop("checked",false);
                            $('#package_'+packID+'_originaldoubleout').val(0);    
                        }
                        if(doubleout!=odoubleout)
                        {
                           toggleDoubleOuts(doubleout,packID);
                           $('#package_'+packID+'_originaldoubleout').val(doubleout);
                        }
                        
                        $('#settings_'+packID).hide();
                        $('#package_'+packID+'_nameedit').hide();
                        $('#package_'+packID+'_name').html(packname);
                        $('#package_'+packID+'_name').show();
                        
                        //update the name of the package in the package holding area
                        $('#package_'+packID).html(packname);
                        $('#package_'+packID).attr("rel",packname);
                        
                        //update the name of the package if it's in another package
                        if(response.update_package_id>0)
                        {
                            $('#pack_'+response.update_package_id+'-station_'+response.update_station_id).html(packname);   
                            $('#pack_'+response.update_package_id+'-station_'+response.update_station_id+'-insert_info').val(packname);   
                        }
                        
                        //see if there are any events that we need to attach
                        if(response.process_attachments)
                        {
                            $.each(response.attach_events,function(j,attach){
                                <?php if($GLOBALS['debug']){ print "console.log('for id='+attach.id+' on -'+attach.action+'- we will be attaching '+attach.afunction);\n";}?>
                                if(attach.action=='direct')
                                {
                                    eval(attach.afunction);
                                } else {
                                    $('#'+attach.id).live(attach.action, function(){
                                        eval(attach.afunction);
                                    });
                                }
                            })
                            
                        }
                        
                    } else {
                        alert(response.error_message);
                    }
                }  
            });
            
            
        }
    
        function removeInsert(packID,stationID,dropperid)
        {
            if(stationID=='sticky')
            {
                var dropper=$('#sticky_'+packID);
                var insertinfo=dropper.data('info');
                var insertname=dropper.data('name');
                var insertid=dropper.data('id');
                var inserttype='sticky';
                var insertclasses=dropper.data('classes');
                var insertclone=dropper.data('clone');
            } else {
                if(dropperid==0)
                {
                   dropperid='pack_'+packID+'-station_'+stationID;
                }
                var dropper=$('#'+dropperid);
                var insertinfo=dropper.data('info');
                var insertname=dropper.data('name');
                var insertid=dropper.data('id');
                var inserttype=dropper.data('type');
                var insertclasses=dropper.data('classes');
                var insertclone=dropper.data('clone');
            }    
            /* 
            "<li id='insert_$insert[id]' rel='$insertname' data-type='$type' data-classes='' data-name='$insertname' data-id='$insert[id]' data-clone='$insert[clone_id]' class='insert $notconfirmed $sticky $cloned'>";
            */
            
            //handle stuff like updating page counts, insert counts and database update
            //now do the work to remove the insert from the database and update package stats accordingly
            $.ajax({
                type: "POST",
                url: "includes/ajax_handlers/saveInsertPackages.php",
                data:{action: 'removeInsert',
                    planid: planID,
                    packid: packID,
                    insertid: insertid,
                    inserttype: inserttype,
                    stationid: stationID
                },
                dataType: 'json',
                success: function(response){
                    if(response.status=='success')
                    {
                        if(inserttype=='jacket')
                        {
                            //for jackets, we just remove the one in the package, since it's just a generic placeholder that doesn't get removed
                        } else {
                            var iDetails='<li id="'+inserttype+'_'+insertid+'" data-type="'+inserttype+'" data-info="'+insertinfo+'" data-name="'+insertname+'" data-id="'+insertid+'" data-clone="'+insertclone+'" data-classes="'+insertclasses+'" class="'+insertclasses+'">'+stripslashes(insertinfo)+"</li>";
                            console.log(iDetails);
                            var newInsert = $(iDetails);
                            if(inserttype=='insert' || inserttype=='sticky')
                            {
                                
                                $('#insertlist').append(newInsert);
                                //re activate all inserts
                                //$("#insertlist").sortable({placeholder: "insert-placeholder ui-state-highlight"});
                                console.log('should have added...');
                            } else {
                                $('#packageInsertList').append(newInsert);
                                //re activate all inserts
                                $("#packageInsertList").sortable({placeholder: "insert-placeholder ui-state-highlight"});
                            }
                            //qtip functionality to display more details about the insert
                            /*$(".insert, .notconfirmed").each(function(index, item){
                                attachQtip(item);
                            });
                            */
                            attachQtip(newInsert);
                    
                        }
                        dropper.empty();
                        dropper.data('id','0');
                        dropper.data('name','');
                        dropper.data('type','');
                        dropper.data('info','');
                        dropper.data('clone','0');
                        dropper.data('classes','');
                        
                        if(stationID!='sticky')
                        {
                            var olddropperinsertid=dropperid+'-insert_id';
                            var olddropperinsertinfo=dropperid+'-insert_info';
                            var olddropperinsertpages=dropperid+'-insert_pages';
                            var olddropperinserttype=dropperid+'-insert_type';
                            $('#'+olddropperinsertid).val('0');
                            $('#'+olddropperinsertinfo).val('');
                            $('#'+olddropperinsertpages).val('');
                            $('#'+olddropperinserttype).val('');
                            
                            $('#package_'+response.package_id+'_pagecount').html(response.pages);
                            $('#package_'+response.package_id+'_insertcount').html(response.count);
                            $('#package_'+response.package_id+'_weight').html(response.weight);
                            
                            if(response.secondary_id>0)
                            {
                                $('#package_'+response.secondary_id+'_pagecount').html(response.secondary_pages);
                                $('#package_'+response.secondary_id+'_insertcount').html(response.secondary_count);
                                $('#package_'+response.secondary_id+'_weight').html(response.secondary_weight);
                            }
                            if(response.third_id>0)
                            {
                                $('#package_'+response.third_id+'_pagecount').html(response.third_pages);
                                $('#package_'+response.third_id+'_insertcount').html(response.third_count);
                                $('#package_'+response.third_id+'_weight').html(response.third_weight);
                            }
                        }
                    } else {
                        alert(response.error_message);
                    }
                }  
            });
            
            
        }
    
        
    
        function handleDrop(event,ui)
        {
            var insert=$(ui.draggable);
            var inserttype=insert.data('type');
            var insertid=insert.data('id');
            var dragtext=insert.data('name');
            var dragclasses=insert.data('classes');
            var dragclone=insert.data('clone');
            var draginfo=insert.html();
            
            var dropper=$(event.target);
            var droptype=dropper.data('handler');
            var dropperid=dropper.prop('id');
            //check for existing insert before storing this one
            //if one exists, remove it and put it back on the insert stack
            var existinginsertid=dropperid+'-insert_id';
            var existinginsertinfo=dropperid+'-insert_info';
            var existinginserttype=dropperid+'-insert_type';
            console.log('dropping '+inserttype+' on a handler for '+droptype);
            if(droptype=='sticky' && (inserttype=='insert' || inserttype=='jacket' || inserttype=='package')){return false;}
            if(droptype=='insert' && inserttype=='sticky'){return false;}
            if(droptype=='sticky')
            {
                if(dropper.data('id')!='0')
                {
                    console.log('thinking we have '+dropper.data('id'));
                    removeInsert(dropper.data('packageid'),'sticky',0);    
                } 
                
            } else {
                if($('#'+existinginsertid).val()!='0')
                {
                    removeInsert(0,0,dropperid);    
                } 
            }
            
                
            var packID=dropper.data('packageid');
            var stationID=dropper.data('stationid');
            
            if(inserttype=='package' && packID==insertid)
            {
                alert('You can not put a\npackage back into itself');
                return false;
            } else if(inserttype=='sticky' && droptype=='regular') {
                alert('You can only place a sticky note into\nthe sticky note slot.');
                return false;
            } else {
                $('#'+existinginsertid).val(insertid);
                $('#'+existinginsertinfo).val(draginfo);
                dropper.html(dragtext);
                dropper.data('text',dragtext);
                dropper.data('id',insertid)
                dropper.data('info',draginfo)
                dropper.data('type',inserttype)
                dropper.data('classes',dragclasses)
                dropper.data('clone',dragclone)
                $('#'+existinginserttype).val(inserttype);
                if(inserttype!='jacket')
                {
                    $(ui.draggable).remove();
                }
                
                if(inserttype=='sticky')
                {
                    $.ajax({
                        type: "POST",
                        url: "includes/ajax_handlers/saveInsertPackages.php",
                        data:{action: 'saveSticky',
                            planid: planID,
                            packid: packID,
                            insertid: insertid,
                        },
                        dataType: 'json',
                        success: function(response){
                            //good enough    
                        }
                    })
                } else {
            
                    //handle stuff like updating page counts, insert counts and database update
                    $.ajax({
                        type: "POST",
                        url: "includes/ajax_handlers/saveInsertPackages.php",
                        data:{action: 'saveInsert',
                            planid: planID,
                            packid: packID,
                            insertid: insertid,
                            inserttype: inserttype,
                            stationid: stationID,
                            inserterid: $('#package_'+packID+'_originalinserter').val()
                        },
                        dataType: 'json',
                        success: function(response){
                            //do stuff
                            if(response.status=='success')
                            {
                                $('#package_'+response.package_id+'_pagecount').html(response.pages);
                                $('#package_'+response.package_id+'_insertcount').html(response.count);
                                $('#package_'+response.package_id+'_weight').html(response.weight);
                                
                                if(response.secondary_id>0)
                                {
                                    $('#package_'+response.secondary_id+'_pagecount').html(response.secondary_pages);
                                    $('#package_'+response.secondary_id+'_insertcount').html(response.secondary_count);
                                    $('#package_'+response.secondary_id+'_weight').html(response.secondary_weight);
                                }
                                if(response.third_id>0)
                                {
                                    $('#package_'+response.third_id+'_pagecount').html(response.third_pages);
                                    $('#package_'+response.third_id+'_insertcount').html(response.third_count);
                                    $('#package_'+response.third_id+'_weight').html(response.third_weight);
                                }
                                            
                            } else {
                                alert(response.error_message);
                            }
                        }  
                    });
                }
            }
        }
        
        
        function deletePackage(packageID)
        {
            var $dialog = $('<div id="jConfirm"></div>')
                .html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This delete this package and return all assigned inserts to the assignement area. Are you sure?</p>')
                .dialog({
                    autoOpen: true,
                    title: 'Are you sure you want to Delete?',
                    modal: true,
                    buttons: {
                        Cancel: function() {
                            $( this ).dialog( "close" );
                            return false;
                        },
                        'Delete': function() {
                            $( this ).dialog( "close" );
                            // do all the deleting stuff
                            $.ajax({
                                type: "POST",
                                url: "includes/ajax_handlers/saveInsertPackages.php",
                                data:{action: 'deletePackage',
                                    planid: planID,
                                    packid: packageID
                                },
                                dataType: 'json',
                                success: function(response){
                                    //do stuff
                                    if(response.status=='success')
                                    {
                                        $('#packagearea_'+packageID).remove();
                                        
                                        //handle removing all inserts & putting them back on the stack
                                        
                                        //then update package stats
                                        if(response.inserts_removed>0)
                                        {
                                            //need to add all inserts that were attached to the previous inserter back to the pile
                                            $.each(response.inserts,function(j,insert){
                                                var newInsert = $("<li id='"+response.inserts[j].insert_type+"_"+response.inserts[j].insert_id+"' class='insert' rel='"+response.inserts[j].insert_name+"' />");
                                                newInsert.html(response.inserts[j].insert_text);
                                                if(response.inserts[j].insert_type=='insert')
                                                {
                                                    $('#insertlist').append(newInsert);
                                                } else {
                                                    $('#packageInsertList').append(newInsert);
                                                }
                                            });
                                            $("#insertlist").sortable({placeholder: "insert-placeholder ui-state-highlight"});
                                            $("#packageInsertList").sortable({placeholder: "insert-placeholder ui-state-highlight"});
                                            $('#package_'+response.package_id+'_pagecount').html(response.pages);
                                            $('#package_'+response.package_id+'_insertcount').html(response.count);
                                            $('#package_'+response.package_id+'_weight').html(response.weight);
                                            //qtip functionality to display more details about the insert
                                            $(".insert, .notconfirmed").each(function(index, item){
                                                attachQtip(item);
                                            });
        
                                        }
                                        if(response.secondary_id>0)
                                        {
                                            $('#package_'+response.secondary_id+'_pagecount').html(response.secondary_pages);
                                            $('#package_'+response.secondary_id+'_insertcount').html(response.secondary_count);
                                            $('#package_'+response.secondary_id+'_weight').html(response.secondary_weight);
                                            
                                            //need to remove it from the packages station list
                                            $('#'+response.second_station).html('');
                                            $('#'+response.second_station+'-insert_type').val('');
                                            $('#'+response.second_station+'-insert_id').val('');
                                            $('#'+response.second_station+'-insert_info').val('');
                                            $('#'+response.second_station+'-insert_page').val('');
                                        }
                                        
                                        if(response.third_id>0)
                                        {
                                            //this one is just a stats update, no deleting
                                            $('#package_'+response.third_id+'_pagecount').html(response.third_pages);
                                            $('#package_'+response.third_id+'_insertcount').html(response.third_count);
                                            $('#package_'+response.third_id+'_weight').html(response.third_weight);
                                            
                                        }
                                        //delete the package from the package holding area
                                        $('#package_'+packageID).remove();  
                                    } else {
                                        alert(response.error_message);
                                    }
                                }  
                            });
                        }
                        
                    },
                    open: function() {
                        $('.ui-dialog-buttonpane > button:last').focus();
                    }
               
                });
                return false;
    
        }
        
        
        function addPackage()
        {
            //this will generate all the necessary html to create a new package widget
            $.ajax({
                type: "POST",
                url: "includes/ajax_handlers/saveInsertPackages.php",
                data:{action: 'addPackage',
                    planid: planID
                },
                dataType: 'json',
                success: function(response){
                    //do stuff
                    if(response.status=='success')
                    {
                        packageid=response.package_id;
                        var newPackage=$("<div class='ui-widget' style='width:240px;margin-right:20px;float:left;' />");
                        newPackage.attr('id','packagearea_'+packageid);
                        newPackage.html(response.settings_html+response.inserter_html);
                        $('#packagewindow').append(newPackage);
                        
                        //now add a package to the package area
                        var newInsert = $("<li id='package_"+packageid+"' class='insert ui-widget ui-widget-content' rel='Untitled'/>");
                        newInsert.html('Untitled');
                        $('#packageInsertList').append(newInsert);
                        //re activate all inserts
                        $("#packageInsertList").sortable({placeholder: "insert-placeholder ui-state-highlight"});
                        
                        //enable the new stations as droppable
                        $( ".station" ).droppable({
                                accept: ".insert, .jacket",
                                activeClass: "ui-state-default",
                                hoverClass: "ui-state-hover",
                                drop: function( event, ui ) {
                                    handleDrop(event,ui)
                                }
                            });
                        
                        
                        //reactivate all buttons as jqueryui buttons
                        $("input:button, input:submit, a.submit, input:file").button();   
                        
                        //see if there are any events that we need to attach
                        if(response.process_attachments)
                        {
                            $.each(response.attach_events,function(j,attach){
                                <?php if($GLOBALS['debug']){ print "console.log('for id='+attach.id+' on -'+attach.action+'- we will be attaching '+attach.afunction);\n";}?>
                                if(attach.action=='direct')
                                {
                                    eval(attach.afunction);
                                } else {
                                $('#'+attach.id).live(attach.action, function(){
                                    eval(attach.afunction);
                                });
                                }
                            })
                            
                        }
                        //qtip functionality to display more details about the insert
                        $(".station").each(function(index, item){
                            attachQtip(item);
                        });
        
                    } else {
                        alert(response.error_message);
                    }
                }  
            });
            
        }
        
        //qtip functionality to display more details about the insert
        $(".insert, .station, .notconfirmed").each(function(index, item){
            attachQtip(item);
        });
        $(function()
        {
            $("#insertlist").sortable({placeholder: "insert-placeholder ui-state-highlight"});
            $("#packageInsertList").sortable({placeholder: "insert-placeholder ui-state-highlight"});
            
            $( ".station" ).droppable({
                accept: ".insert, .jacket",
                activeClass: "ui-state-default",
                hoverClass: "ui-state-hover",
                drop: function( event, ui ) {
                    handleDrop(event,ui)
                }
            });
            
        })
        function attachQtip(item)
        {
            $(item).qtip({
                content: {
                 title: {
                  text: 'Insert Details',
                  button: '<span onclick="return false;">Close</span>'
                 },
                 text: "<img src='artwork/icons/ajax-loader.gif' />", // The text to use whilst the AJAX request is loading
                 ajax: {
                    url: 'includes/ajax_handlers/saveInsertPackages.php',
                    data: { action: 'insertDetails', id: $(item).attr('id'), planid: $('#plan_id').val() },
                    type: 'POST',
                    dataType: 'json',
                    once: false,
                    success: function(response) {
                        if(response.status=='success')
                        {
                            this.set('content.text', response.qtip);
                        }
                    }
                 }
               },
               position: {
                        my: 'left center',
                        at: 'right center',
                        target: $(item)
                    },
                style: {
                    classes: 'ui-tooltip-shadow', // Optional shadow...
                    tip: 'left center' // Tips work nicely with the styles too!
                
                },
                show: {
                    event: 'dblclick',
                    solo: true, // Only show one tooltip at a time
                    effect: false
                },
                hide: 'click mouseleave'
                
            });
            
            if($(item).data('clone')==0)
            {
                $(item).contextMenu('insertCmenu_'+$(item).data('id'),{
                    'Clone Insert': {
                        click: function(item){ // element is the jquery obj clicked on when context menu launched
                            var id=$(item).data('id');
                            
                            $.ajax({
                                type: "POST",
                                url: "includes/ajax_handlers/saveInsertPackages.php",
                                data:{action: 'cloneinsert',
                                    insertid: $(item).data('id'),
                                    inserttype: $(item).data('type'),
                                    planid: $('#plan_id').val(),
                                    pubid: $('#pub_id').val(),
                                    pubdate: $('#pub_date').val()
                                },
                                dataType: 'json',
                                success: function(response){
                                    var newinsert=$('<li id="insert_'+response.new_id+'" data-info="CLONED: '+response.insertname+'" data-type="'+$(item).data('type')+' data-name="CLONED: '+response.insertname+'" data-id="'+response.new_id+'" data-clone="'+$(item).data('id')+'" data-classes="'+$(item).data('classes')+'" class="'+$(item).data('classes')+' cloned">'+response.insertinfo+'</li>');
                                    $('#insertlist').append(newinsert);
                                    attachQtip(newinsert);
                                }
                            })
                            
                            
                            
                                     
                        }
                    }
                })
            } else {
                $(item).contextMenu('insertCmenu_'+$(item).data('id'),{
                    'Remove clone': {
                        click: function(item){ // element is the jquery obj clicked on when context menu launched
                            var id=$(item).data('id');
                            
                            $.ajax({
                                type: "POST",
                                url: "includes/ajax_handlers/saveInsertPackages.php",
                                data:{action: 'removeclone',
                                    insertid: id
                                },
                                dataType: 'json',
                                success: function(response){
                                    $(item).remove();
                                }
                            })
                                     
                        }
                    }
                }) 
            }
            
        }
        
        
    </script>
    <?php
            
                
    
}



footer();
?>