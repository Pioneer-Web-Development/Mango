<?php
include("../functions_db.php");
include("../functions_formtools.php");
include("../config.php");
include("../functions_common.php");
global $pubs;
/*
*   ALL FUNCTIONS REFERENCES IN THE SWITCH LOOP ARE IN THE COMMON.PHP FILE
*/
$response=array();
$response['process_attachments']=0;
switch($_POST['action'])
{
    case 'saveSettings':
        $planid=$_POST['planid'];
        $packid=$_POST['packid'];
        $packdate=$_POST['packdate'];
        $packname=$_POST['packname'];
        $inserterid=$_POST['inserterid'];
        $oinserterid=$_POST['oinserterid'];
        $request=$_POST['request'];
        $orequest=$_POST['orequest'];
        $doubleout=$_POST['doubleout'];
        $odoubleout=$_POST['odoubleout'];
        $stickyid=$_POST['stickyid'];
        if($stickyid==''){$stickyid=0;}
        if($doubleout==''){$doubleout=0;}
        if($request==''){$request=0;}
        $pdate=date("Y-m-d",strtotime($packdate));
       
        $sql="SELECT * FROM inserters WHERE id=$inserterid";
        $dbInserter=dbselectsingle($sql);
        $inserter=$dbInserter['data'];
        $candoubleout=$inserter['can_double_out'];
        $inserterturn=$inserter['inserter_turn'];
        $singleoutspeed=$inserter['single_out_speed'];
        $doubleoutspeed=$inserter['double_out_speed'];
        $singleout=false;

        if($doubleout)
        {
           $speed=$doubleoutspeed; 
        } else {
           $speed=$singleoutspeed; 
        }
        if($speed>0)
        {
            $runminutes=round(($request/$speed),0)+30; //pad by 30 because... :)
        } else {
            $runminutes=120;
        }
        
        $packstop=date("Y-m-d H:i",strtotime($packdate."+$runminutes minutes"));
       if($inserterid!=$oinserterid){$doubleout=0;}
       //first, update the record
       $sql="UPDATE jobs_inserter_packages SET sticky_note_id='$stickyid', inserter_id='$inserterid', inserter_request='$request', package_name='$packname', 
       package_date='$pdate', package_startdatetime='$packdate', package_stopdatetime='$packstop', double_out='$doubleout' WHERE id='$packid'";
       $dbUpdate=dbexecutequery($sql);
       $error=$dbUpdate['error'];
       if($error!='')
       {
           $response['status']='error';
           $response['error_message']=$error;
       } else {
           //now if the inserter id has changed, that means we need to generate a new inserter set
           $response['status']='success';
           if($inserterid!=$oinserterid)
           {
                // build a variable holding the new inserter station blocks
                $newinserter='';
                $newinserter=getInserterHTML($inserterid,$packid);
                
                $response['inserter_html']=$newinserter;
                $response['inserts_removed']=0;
                //now we need to remove all inserts that were on this package, and get updated package totals
                $sql="SELECT * FROM jobs_packages_inserts WHERE plan_id=$planid AND package_id=$packid";
                $dbInserts=dbselectmulti($sql);
                if($dbInserts['numrows']>0)
                {
                    foreach($dbInserts['data'] as $insert)
                    {
                        $temp=removeInsert($insert['plan_id'],$insert['package_id'],$insert['insert_id'],$insert['insert_type'],$insert['hopper_id']);
                        $response['package_id']=$temp['package_id'];
                        $response['weight']=$temp['weight'];
                        $response['count']=$temp['count'];
                        $response['pages']=$temp['pages'];
                        $response['secondary_id']=$temp['secondary_id'];
                        $response['secondary_weight']=$temp['secondary_weight'];
                        $response['secondary_count']=$temp['secondary_count'];
                        $response['secondary_pages']=$temp['secondary_pages'];
                        $response['inserts_removed']=$response['inserts_removed']+1;
                        
                        
                        $sql="SELECT A.*, B.account_name FROM inserts A, accounts B WHERE A.id=$insert[insert_id] AND A.advertiser_id=B.id";
                        $dbInsertInfo=dbselectsingle($sql);
                        $insertInfo=$dbInsertInfo['data'];
                        $insertname=stripslashes($insertInfo['account_name'])." ".stripslashes($insertInfo['insert_tagline']);
                        $insertname=str_replace("'","",$insertname);
                        $insertpages=$insertInfo['tab_pages'];
                        $request=$insert['insert_quantity'];
                        $insertname="<b>$insertname</b>";
                        $insertinfo=$insertname."<br><b>Pages: </b>".$insertpages." <b>Request: </b>$request";    
                        
                        $response['inserts'][]=array('insert_id'=>$insert['insert_id'],'insert_type'=>$insert['insert_type'],'insert_text'=>$insertinfo,'insert_name'=>$insertname);
                        
                    }
                }
            }
            
        }
        
        //now, lets see if this package is saved in another package. If so we will need to find out the package id and station id
        //we need this so we can update the name and info for that display
        $sql="SELECT * FROM jobs_packages_inserts WHERE plan_id=$planid AND insert_id='$packid' AND insert_type='package'";
        $dbCheck=dbselectsingle($sql);
        if($dbCheck['numrows']>0)
        {
            $response['update_package_id']=$dbCheck['data']['package_id'];
            $response['update_station_id']=$dbCheck['data']['hopper_id'];
        }
         
    break;
    
    case 'saveInsert':
        $planid=$_POST['planid']; 
        $packid=$_POST['packid'];
        $insertid=$_POST['insertid']; 
        $inserttype=$_POST['inserttype'];
        $stationid=$_POST['stationid'];
        $inserterid=$_POST['inserterid'];
        $response=addInsert($planid,$packid,$insertid,$inserttype,$stationid,$inserterid);
    break;
    
    
    case 'removeInsert':
        $planid=$_POST['planid']; 
        $packid=$_POST['packid'];
        $insertid=$_POST['insertid']; 
        $inserttype=$_POST['inserttype'];
        $stationid=$_POST['stationid'];
        if($stationid=='sticky')
        {
            $sql="UPDATE jobs_inserter_packages SET sticky_note_id=0 WHERE id=$packid";
            $dbUpdate=dbexecutequery($sql);
            if($dbUpdate['error']=='')
            {
                $response['status']='success';
            } else {
                $response['status']='error';
            }
        } else {
            $response=removeInsert($planid,$packid,$insertid,$inserttype,$stationid);
        }
    break;
    
    case 'saveSticky':
        $planid=$_POST['planid']; 
        $packid=$_POST['packid'];
        $insertid=$_POST['insertid']; 
        $sql="UPDATE jobs_inserter_packages SET sticky_note_id=$insertid WHERE id=$packid";
        $dbUpdate=dbexecutequery($sql);
        if($dbUpdate['error']==00)
        {
            $response['status']='success';
        } else {
            $response['status']='error';
        }
    break;
    
    
    case 'deletePackage':
        //just need to unassign any existing packages
        //now we need to remove all inserts that were on this package, and get updated package totals
        
        //please note that this code is also used in inserterPackages.php for the delete package function
        //any updates need to be mirrored between the two
        
        $planid=$_POST['planid'];
        $packid=$_POST['packid'];
        $sql="SELECT * FROM jobs_packages_inserts WHERE plan_id=$planid AND package_id=$packid";
        $dbInserts=dbselectmulti($sql);
        $response['status']='success';
        $response['inserts_removed']=0;
        if($dbInserts['numrows']>0)
        {
            foreach($dbInserts['data'] as $insert)
            {
                $temp=removeInsert($insert['plan_id'],$insert['package_id'],$insert['insert_id'],$insert['insert_type'],$insert['hopper_id']);
                $response['secondary_id']=$temp['secondary_id'];
                $response['secondary_weight']=$temp['secondary_weight'];
                $response['secondary_count']=$temp['secondary_count'];
                $response['secondary_pages']=$temp['secondary_pages'];
                
                
                
                $sql="SELECT A.*, B.account_name FROM inserts A, account B WHERE A.id=$insert[insert_id] AND A.advertiser_id=B.id";
                $dbInsertInfo=dbselectsingle($sql);
                $insertInfo=$dbInsertInfo['data'];
                $insertname=stripslashes($insertInfo['account_name'])." ".stripslashes($insertInfo['insert_tagline']);
                $insertname=str_replace("'","",$insertname);
                $insertname="<b>".$insertname."</b>";
                $insertpages=$insertInfo['tab_pages'];
                $request=$insertInfo['insert_quantity'];
                $insertinfo=$insertname."<br><b>Pages: </b>".$insertpages." <b>Request: </b>$request";
                $response['inserts_removed']=$response['inserts_removed']+1;
                $response['inserts'][]=array('insert_id'=>$insert['insert_id'],'insert_name'=>$insertname,'insert_type'=>$insert['insert_type'],'insert_text'=>$insertinfo);
                $deleteids=$insert['id'].",";
            }
        }
        $deleteids=substr($deleteids,0,strlen($deleteids)-1);
        if($deleteids!='')
        {
            $sql="DELETE FROM jobs_packages_inserts WHERE id IN ($deleteids)";
            $dbDelete=dbexecutequery($sql);
        }
        //subtract one from the number of package in the jobs_inserter_plans
        $sql="UPDATE jobs_inserter_plans SET num_packages=num_packages-1 WHERE id=$planid";
        $dbUpdate=dbexecutequery($sql);
        if($dbUpdate['error']!='')
        {
            $response['status']='error';
            $response['error_message'].=$dbUpdate['error'];
        }
        //delete the actual package
        $sql="DELETE FROM jobs_inserter_packages WHERE id=$packid";
        $dbDelete=dbexecutequery($sql);
        if($dbDelete['error']!='')
        {
            $response['status']='error';
            $response['error_message'].=$dbDelete['error'];
        }
        
    break;
    
    case 'addPackage':
        $planid=$_POST['planid'];
        
        $sql="SELECT * FROM jobs_inserter_plans WHERE id=$planid";
        $dbPlan=dbselectsingle($sql);
        $plan=$dbPlan['data'];
        $pubdate=$plan['pub_date']; 
        $request=$plan['inserter_request'];
        $inserterid=$plan['inserter_id'];
        $numpackages=$plan['num_packages']; 
        $pubid=$plan['pub_id']; 
        
        $sql="SELECT * FROM inserters WHERE id=$inserterid";
        $dbInserter=dbselectsingle($sql);
        $inserter=$dbInserter['data'];
        $candoubleout=$inserter['can_double_out'];
        $inserterturn=$inserter['inserter_turn'];
        $singleout=false;

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
         
        $pname='Untitled'; 
        $sql="INSERT INTO jobs_inserter_packages (pub_id, pub_date, package_date, plan_id, inserter_id, 
        package_name, package_startdatetime, package_stopdatetime, inserter_request) VALUES ('$pubid', '$pubdate', 
        '$packdate', '$planid', '$inserterid', '$pname', '$packdate', '$packdate', '$request')";
        $dbAddPackage=dbinsertquery($sql);
        if($dbAddPackage['error']=='')
        {
            $response['status']='success';
            $packageid=$dbAddPackage['insertid'];
            //update the count for number of packages for the plan
            $sql="UPDATE jobs_inserter_plans SET num_packages=num_packages+1 WHERE id=$planid";
            $dbUpdate=dbexecutequery($sql);
            if($dbUpdate['error']=='')
            {
                //now, generate the inserter html and we also need to generate the settings area html
                $response['inserter_html']=getInserterHTML($inserterid,$packageid);
                $response['settings_html']=getPackageSettingsHTML($inserterid,$packageid);
                $response['package_id']=$packageid;
            } else {
                $response['status']='error';
                $response['error_message']=$dbUpdate['error'];
            }
        } else {
            $response['status']='error';
            $response['error_message']=$dbAddPackage['error'];
        }
    
    break;
    
    
    case 'updateStation':
        $packageid=$_POST['packageid'];
        $stationid=$_POST['stationid'];
        $inserterid=$_POST['inserterid'];
        $secondaryValue=$_POST['secondaryvalue'];
        
        //get the id of the secondary hopper for that inserter
        if($secondaryValue==0)
        {
            $secondaryID=0;
        } else {
            $sql="SELECT * FROM inserters_hoppers WHERE inserter_id='$inserterid' AND hopper_number='$secondaryValue'";
            $dbID=dbselectsingle($sql);
            $secondaryID=$dbID['data']['id'];
        }
        //see if we have a record
        $sql="SELECT * FROM jobs_packages_hopper_pairings WHERE package_id='$packageid' AND hopper_id='$stationid'";
        $dbCheck=dbselectsingle($sql);
        if($dbCheck['numrows']>0)
        {
            
            $secid=$dbCheck['data']['id'];
            $sql="UPDATE jobs_packages_hopper_pairings SET secondary_id='$secondaryID', secondary_value='$secondaryValue' 
            WHERE id=$secid";
            $dbUpdate=dbexecutequery($sql);
            if($dbUpdate['error']=='')
            {
                $response['status']='success';
            } else {
                $response['status']='error';
                $response['error_message']=$dbUpdate['error'];
            }
            
        } else {
            //create a new record
            $sql="INSERT INTO jobs_packages_hopper_pairings (package_id, hopper_id, secondary_id, secondary_value) VALUES 
            ('$packageid', '$stationid','$secondaryID','$secondaryValue')";
            $dbInsert=dbinsertquery($sql);
            if($dbInsert['error']=='')
            {
                $response['status']='success';
            } else {
                $response['status']='error';
                $response['error_message']=$dbInsert['error'];
            }
        }
        
    break;
    
    case 'insertDetails':
        $objectID=$_POST['id'];
        $planid=$_POST['planid'];
        //see if it's a station. a station will contain a '-' in the middle
        $noinsert=false;
        if(strpos($objectID,'-'))
        {
            //station
            $objectID=explode('-',$objectID);
            $package=explode("_",$objectID[0]);
            $packageid=$package[1];
            $station=explode("_",$objectID[1]);
            $stationid=$station[1];
            $type='Station';
            $id='Package: '.$packageid.' station: '.$stationid;
            
            //going to have to look up the insert in this station
            $sql="SELECT * FROM jobs_packages_inserts WHERE package_id='$packageid' AND hopper_id='$stationid'";
            $dbCheck=dbselectsingle($sql);
            if($dbCheck['numrows']>0)
            {
                $insertid=$dbCheck['data']['insert_id'];
                $type=$dbCheck['data']['insert_type'];
            } else {
                $noinsert=true;
            }
            
        } else {
            //package or insert
            $objectID=explode("_",$objectID);
            $insertid=$objectID[1];
            if($objectID['0']=='package')
            {
                //package
                $type='package';
            } else {
                //insert
                $type='insert';
            }
        }
        
        if($noinsert)
        {
            $qtip='No insert.';
        } else {
            $qtip="<div style='width:250px;font-size:12px;'>\n";
            if($type=='package')
            {
                //generate a list of all inserts in the package
                $sql="SELECT package_name FROM jobs_inserter_packages WHERE id=$insertid";
                $dbPackage=dbselectsingle($sql);
                $sql="SELECT * FROM jobs_packages_inserts WHERE package_id=$insertid ORDER BY insert_type";
                $dbCheck=dbselectmulti($sql);
                if($dbCheck['numrows']>0)
                {
                   $qtip.=getPackageContents($qtip,$insertid);  
                } else {
                   $qtip.="<br><b>Package: </b>".$dbPackage['data']['package_name'];
                   $qtip.=" contains no inserts<br>"; 
                }
            } elseif($type=='insert')
            {
                $qtip.=getInsertToolTip($insertid);
            }
        
        
            $qtip.="</div>\n";
        }
        
        $response['status']='success';
        $response['qtip']=$qtip;
    
    break;
    
    case "cloneinsert":
        $pubid=intval($_POST['pubid']);
        $planid=intval($_POST['planid']);
        $insertid=intval($_POST['insertid']);
        $pubdate=$_POST['pubdate'];
        if($insertid!=0)
        {
            //first step, we clone the insert record
            $sql="SELECT * FROM inserts WHERE id=$insertid";
            $dbInsert=dbselectsingle($sql);
            $insert=$dbInsert['data'];
            $fields='';
            $values='';
            foreach($dbInsert['data'] as $field=>$value)
            {
                if($field!='id')
                {
                    $fields.=$field.",";
                    if($field=='clone_id'){$value=$insertid;}
                    $values.="'".$value."',";
                }
            }
            if($fields!='')
            {
                $fields=rtrim($fields,',');
                $values=rtrim($values,',');
                $sql="INSERT INTO inserts ($fields) VALUES ($values)";
                $response['insert_sql']=$sql;
                $dbInsert=dbinsertquery($sql);
                $newinsertid=$dbInsert['insertid'];
                
                //now clone the insert schedule that would have been for this pub/date and insert
                $sql="SELECT * FROM inserts_schedule WHERE insert_id=$insertid AND pub_id=$pubid AND insert_date='$pubdate'";
                $dbSchedule=dbselectsingle($sql);
                if($dbSchedule['numrows']>0)
                {
                    $fields='';
                    $values='';
                    //clone it just the same
                    foreach($dbSchedule['data'] as $field=>$value)
                    {
                        if($field!='id')
                        {
                            $fields.=$field.",";
                            if($field=='insert_id'){$value=$newinsertid;}
                            $values.="'".$value."',";
                        }
                    }
                    if($fields!='')
                    {
                        $fields=rtrim($fields,',');
                        $values=rtrim($values,',');
                        $sql="INSERT INTO inserts_schedule ($fields) VALUES ($values)";
                        $response['schedule_sql']=$sql;
                        $dbInsert=dbinsertquery($sql);
                        $newscheduleid=$dbInsert['insertid'];
                    }
                }
            
                
                if($insert['weprint_id']>0)
                {
                    $sql="SELECT A.pub_id, B.run_name FROM jobs A, publications_runs B WHERE A.id=$insert[weprint_id] AND A.run_id=B.id";
                    $dbJob=dbselectsingle($sql);
                    $jpubid=$dbJobs['data']['pub_id'];
                    $accountname=stripslashes($dbJobs['data']['run_name']);
                } else {
                    //get account name
                    $sql="SELECT account_name FROM accounts WHERE id=$insert[advertiser_id]";
                    $dbAccount=dbselectsingle($sql);
                    $accountname=stripslashes($insert['account_name']);
                }
                $accountname.=" CLONED";
                $insertname=$accountname." ".stripslashes($insert['insert_tagline']);
                $insertname=str_replace("'","",$insertname);
                $response['insertname']=$insertname;
                $request=$insert['insert_quantity'];
                $insertname="<b>$insertname</b>";
                if(!$insert['received']){$insertname="<span style='color:red;'>$insertname</span>";}
                $insertpages=$insert['tab_pages'];
                $insertinfo=$insertname."<br><b>Pages:</b> $insertpages <b>Request: </b>$request";
                $response['insertinfo']=$insertinfo;
                $response['status']='success';
                $response['original_id']=$insertid;
                $response['new_id']=$newinsertid;
                $response['new_schedule_id']=$newscheduleid;
            }else {
                $response['status']='error';
            }
            
        } else {
            $response['status']='error';
            
        }    
    
    break;
    
    case "removeclone":
        //this will delete an insert, set the original back to no clone and remove the associated schedule
        $insertid=intval($_POST['insertid']);
        $sql="SELECT clone_id FROM inserts WHERE id=$insertid";
        $dbOriginal=dbselectsingle($sql);
        $original=$dbOriginal['data']['clone_id'];
        if($original!=0)
        {
            $sql="UPDATE inserts SET clone_id=0 WHERE id=$original";
            $dbUpdate=dbexecutequery($sql);
            if($dbUpdate['error']=='')
            {
               $sql="DELETE FROM inserts WHERE id=$insertid";
               $dbDelete=dbexecutequery($sql);
               $sql="DELETE FROM inserts_schedule WHERE insert_id=$insertid";
               $dbDelete=dbexecutequery($sql);
               if($dbDelete['error']=='')
               {
                   $response['status']='success';
                   $response['message']='Cloned insert has been removed'; 
                   
               } else {
                   $response['status']='error';
                   $response['message']='Problem updating the original record'; 
                } 
            } else {
               $response['status']='error';
               $response['message']='Problem updating the original record'; 
            }
        } else {
            $response['status']='error';
            $response['message']='The clone id was 0';
        }
    break;
    default:
        $response['status']='error';
        $response['error_message']='No action handler found';
    break;
}
echo json_encode($response);
?>