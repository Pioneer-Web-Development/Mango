<?php
//<!--VERSION: .9 **||**-->

function pbsimport($type='')
{
    print "<form method=post enctype='multipart/form-data'>\n";
  //ok we need to choose publication and run
  //since they are insert runs are tied to publication, we'll do an ajax call to build the
  //second select box
  //first, lets get the publications
  if ($type=='standalone')
  {
      global $pubs;
      $runs[0]="Please choose";
      $date=date("Y-m-d",strtotime("+1 day"));
      make_select('pub_id',$pubs[0],$pubs,'Choose publication','','',false,"getInsertRuns();");
      make_select('run_id',$runs[0],$runs,'Insert run');
      make_date('pubdate',$date,'Publish Date');
  } else {
      make_hidden('pub_id',$_GET['pubid']);
      make_hidden('run_id',$_GET['runid']);
      make_hidden('planid',$_GET['planid']);
  }
  make_file('doc','PBS Manifest File');
  make_checkbox('showresults',0,'Results','Check to display results of the import process');
  make_submit('submit','Load PBS File');
  print "</form>\n";
  
  
}

function process_PBSfile($dailyfile=false)
{  
  global $siteID;
    
  $recap=false;
  $truck=false;
  $trucks=array();
  $curroute=0;
  global $summarypubs, $pbspubs, $pbsbadroutes;
  $file=$_FILES['doc']['tmp_name'];
      
      $contents=file_get_contents($file);
      
      //lets build an array of elements based on a line break
      $contents=explode("\n",$contents);
      $badtrucks=array();
      foreach($contents as $line)
      {
          //now lets skip down to the truck recap section
          $line=str_replace("-"," ",$line);
          $oline=$line;
          if ($line=='THE BELLINGHAM HERALD' || $line=='SKAGIT VALLEY HERALD' || $line=="EMMETT MESSENGER-INDEX" || $line=='IDAHO PRESS-TRIBUNE')
          {
              $line="";
              $oline="";
          }
          if (strpos($line,'DATE:')>0)
          {
              $line="";
              $oline="";
          }
          $line=trim($line);
          $line=str_replace("* LEAD BUNDL","",$line);
          $line=str_replace("_","",$line);
          
          $line=str_replace("  ","|",$line);
          $line=str_replace("||","|",$line);
          $line=str_replace("||","|",$line);
          $line=str_replace("||","|",$line);
          $line=str_replace("||","|",$line);
          
          if ($line=='TRUCK RECAP BY PRODUCT')
          {
              $recap=true;
          }
          if ($line=='GRAND') //should be the line at the end of this section
          {
              $recap=false;
          }
          if (strpos($line,'RUCK:')>0)
          {
              $truck=true;
          }
          if (trim($line)=='END OF TRUCK MANIFEST') //should be the line at the end of this section
          {
              $truck=false;
              $truckno="";
              $truckdesc="";
          }
          
          if ($recap) //ok, if this flag is set, we should be in the recap section
          {
              //lets eliminate some miscellaneous lines that appear
              $line=trim($line);
              if (strpos($line,'BELLINGHAM HERALD')>0)
              {
                  $line="";
              }
              if (strpos($line,'VALLEY HERALD')>0)
              {
                  $line="";
              }
              if (strpos($line,'PRESS-TRIBUNE')>0)
              {
                  $line="";
              }
              if (strpos($line,'MESSENGER-INDEX')>0)
              {
                  $line="";
              }
              if (strpos($line,'RECAP BY PRODUCT')>0)
              {
                  $line="";
              }
              if (strpos($line,'THROW')>0)
              {
                  $line="";
              }
              if ($line!='')
              {
                  if (strpos($line,"STANDARDS")>0)
                  {
                    $truckpos=0;
                    $productpos=strpos($oline,"PRODUCT");
                    $drawpos=strpos($oline,"DRAW");
                    
                    
                    //print "Found truck at $truckpos, product at $productpos and draw at $drawpos<br />\n";
                    //print "Found the following seq: $seqpos, tel: $telpos; product: $productpos<br>\n";
                  } else {
                      //ok this is a valid route, we'll explode it by spaces to get the data
                      //need to get rid of all extra spaces
                      //print $oline."<br />\n";
                      $truck=trim(substr($oline,$truckpos,10));
                      $truck=str_replace("*","",trim($truck));
                      $product=trim(substr($oline,$productpos,7));
                      $draw=trim(substr($oline,$drawpos-2,7));
                      //print "<br>Found truck $truck draw of $draw for <br>$oline<br>\n";
                      //now we need to store the truck in the database
                      if (in_array($product,$pbspubs)) //only capture those that are specified
                      {
                          $trucks[$truck]['product']=$product;
                          $trucks[$truck]['totaldraw']=$draw;
                      }
                      //print "Truck: $truck | Product: $product | Draw: $draw<br />\n";
                  }
              }
          }
          
          
          if ($truck)
          {
              /*
              SEQUENCE ROUTE     TELEPHONE NUMBER          PRODUCT     DRAW   BUNDLES     SIZE              DROP LOCATION/INSTRUCTION      RETURNS
              we need to find the position of each of the about elements to use to build our information 
              */
              if (strpos($line,'RUCK:')>0)
              {
                    $pieces=explode("|",$line);
                    //this is the actual truck line. need to get truck number and description
                    $truckno=str_replace("*","",trim($pieces[1]));
                    $truckdesc=trim($pieces[2]);
                    
                    $trucks[$truckno]['truck']=$truckno;
                    $trucks[$truckno]['description']=$truckdesc;
                    if (strpos(strtoupper($truckdesc),"/BARRON")>0)
                    {
                        $badtrucks[]=$truckno;
                    }
                    //if ($truckno=='30MR2915'){print "working on suspect truck now<br>";}
              } elseif (strpos($line,"TELEPHONE NUMBER")>0)
              {
                $seqpos=strpos($oline,"SEQUENCE");
                $routepos=strpos($oline,"ROUTE");
                $telpos=strpos($oline,"TELEPHONE NUMBER");
                $productpos=strpos($oline,"PRODUCT");
                $drawpos=strpos($oline,"DRAW");
                $bundlepos=strpos($oline,"BUNDLES");
                $sizepos=strpos($oline,"SIZE");
                $droppos=strpos($oline,"DROP LOCATION");
                $returnpos=strpos($oline,"RETURNS");
                $inroute=false;
                //print "Found the following seq: $seqpos, tel: $telpos; product: $productpos<br>\n";
              } elseif (strpos($line,'TOTAL STANDARD')>0 || strpos($line,'ACCOUNT NAME')>0 || strpos($line,'STANDARDS KEY SIZE')>0) 
              {
                  //blank line
              } elseif (trim($line)!='') {
                 if (strpos($line,"* BULK *")>0)
                 {
                     $bulk=1;
                 }
                  //should be a regular line
                 if (trim(substr($oline,$routepos,9))!='')
                 {
                    
                    $sequence=trim(substr($oline,$seqpos,8)); 
                    $route=trim(substr($oline,$routepos,9));
                    //if ($truckno=='30MR2915'){print "found a route for the truck $route<br>";}
                    $account=trim(substr($oline,$telpos,25));
                  }   
                    if (trim(substr($oline,$productpos,8))!='')
                     {
                         $product=trim(substr($oline,$productpos,8));
                         //if ($truckno=='30MR2915'){print "found a product for the truck - $product<br>";}
                         if (in_array($product,$pbspubs))
                         {
                            $draw=trim(substr($oline,$drawpos,7));
                            $validroute=true;
                         } else {
                            $product="";
                         } 
                     }
                     if (trim(substr($oline,$telpos,25))!='' && trim(substr($oline,$routepos,9))=='')
                     {
                         $phone=trim(substr($oline,$telpos,25));
                     }
                     $notes.=trim(substr($oline,$droppos,30));
                 
              } else {
                  if ($route!='' && $validroute && !in_array($route,$pbsbadroutes))
                  {
                      $trucks[$truckno]['routes'][$route]['route']=$route;
                      $trucks[$truckno]['routes'][$route]['sequence']=$sequence;
                      $trucks[$truckno]['routes'][$route]['draw']=str_replace(",","",$draw);
                      $trucks[$truckno]['routes'][$route]['product']=$product;
                      $trucks[$truckno]['routes'][$route]['account']=$account;
                      $trucks[$truckno]['routes'][$route]['bulk']=$bulk;
                      $trucks[$truckno]['routes'][$route]['notes']=$notes;
                      $trucks[$truckno]['routes'][$route]['phone']=$phone;
                      
                  }
                  $validroute=false;
                  $phone="";
                  $notes="";
                  $bulk=0;
                  $route='';
                  $draw="";
                  $product="";
              }
              
          }
                   
      }
      if (count($badtrucks)>0)
      {
          foreach ($badtrucks as $key=>$truckno)
          {
              unset($trucks[$truckno]);
          }
      }
      //ok, time to store results in the database
      if ($_GET['action']=='importtrucks')
      {
          //means we are importing base trucks/routes to a run... not live data
          //grab pub and run id
          $pubid=$_GET['pubid'];
          $runid=$_GET['runid'];
          //first, delete any existing trucks and routes for this insert run
          if (count($trucks)>0)
          {
              //ok we have trucks
              $sql="DELETE FROM publications_inserttrucks WHERE pub_id=$pubid AND run_id=$runid";
              $dbDelete=dbexecutequery($sql);
              $sql="DELETE FROM publications_insertroutes WHERE pub_id=$pubid AND run_id=$runid";
              $dbDelete=dbexecutequery($sql);
              //now we'll work through all the trucks. when we insert a truck, grab the insert id
              //to use when inserting the routes for that truck
              $order=10;
              foreach($trucks as $truck)
              {
                  $tname=addslashes($truck['truck']);
                  $tdesc=addslashes($truck['description']);
                  $tdraw=str_replace(",","",$truck['totaldraw']);
                  $truck['order']=$order;
                  $sql="INSERT INTO publications_inserttrucks (pub_id, run_id, truck_order, truck_name, truck_description, average_sunday, average_monday, average_tuesday, average_wednesday, average_thursday, average_friday, average_saturday) VALUES ('$pubid', '$runid', '$order', '$tname', '$tdesc', '$tdraw', '$tdraw', '$tdraw', '$tdraw', '$tdraw', '$tdraw', '$tdraw')";
                  $dbInsertTruck=dbinsertquery($sql);
                  $truckid=$dbInsertTruck['numrows'];
                  //now go through any associated routes
                  if (count($truck['routes'])>0)
                  {
                      foreach($truck['routes'] as $route)
                      {
                          $routenum=addslashes($route['route']);
                          $sequence=addslashes($route['sequence']);
                          $routeaccount=addslashes($route['account']);
                          $phone=addslashes($route['phone']);
                          $notes=addslashes($route['notes']);
                          $bulk=addslashes($route['bulk']);
                          $sql="INSERT INTO publications_insertroutes (pub_id, run_id, truck_id, route_account,  route_number, bulk, route_phone, route_notes, route_sequence) VALUES ('$pubid', '$runid', '$truckid', '$routeaccount', '$routenum', '$bulk', '$phone', '$notes', '$sequence')";
                          $dbRoute=dbinsertquery($sql);
                      }
                  }
                  
                  $order=$order+10;
              }
          }
          print "Successfully imported trucks. <a href='?action=listtrucks&pubid=$pubid&runid=$runid'>Return to truck list</a></h2>\n";
      } else {
          //means we are adding a set of live run data
          $pubid=$_POST['pub_id'];
          $runid=$_POST['run_id'];
          $pubdate=$_POST['pubdate'];
          
          //clear any existing data
          $sql="DELETE FROM jobs_inserter_trucks WHERE pub_id=$pubid AND run_id=$runid AND pub_date='$pubdate'";
          $dbDelete=dbexecutequery($sql);
          $sql="DELETE FROM jobs_inserter_routes WHERE pub_id=$pubid AND run_id=$runid AND pub_date='$pubdate'";
          $dbDelete=dbexecutequery($sql);
          
          
          
          $dayname=strtolower(date("l",strtotime($pubdate)));
          $averagefield="average_$dayname";        
          if (count($trucks)>0)
          {
              //now we'll work through all the trucks. when we insert a truck, grab the insert id
              //to use when inserting the routes for that truck
              $order=10;
              foreach($trucks as $truck)
              {
                  $tdraw=str_replace(",","",$truck['totaldraw']);
                  $tname=$truck['truck'];
                  //basically at this point we are going to compare the new trucks
                  //against the existing database of trucks
                  //we'll pull back two pieces of information, truck_notes and zone_id
                  //if the truck doesn't currently exist we'll store it in the new table with
                  //newtruck set to 1. It will be displayed at the end as trucks that are new
                  //we'll then update the average for the particular day
                  $sql="SELECT id, zone_id, truck_notes, zone_id, truck_name, truck_description, truck_notes, $averagefield FROM publications_inserttrucks WHERE run_id=$runid AND truck_name='$tname'";
                  $dbExisting=dbselectsingle($sql);
                  if ($dbExisting['numrows']>0)
                  {
                      $avg=$dbExisting['data'][$averagefield];
                      $exTruckID=$dbExisting['data']['id'];
                      $zoneID=$dbExisting['data']['zone_id'];
                      $notes=$dbExisting['data']['truck_notes'];
                      $tname=$dbExisting['data']['truck_name'];
                      $tdesc=$dbExisting['data']['truck_description'];
                      $newtruck=0;
                      $avg=round((($avg+$tdraw)/2),0);
                      //$avg=$tdraw; //uncomment this line to 'reset' a truck to a full volume
                      $sql="UPDATE publications_inserttrucks SET $averagefield=$avg, truck_order='$order' WHERE id=$exTruckID";
                      $dbUpdateTruck=dbexecutequery($sql); 
                  } else {
                      $newtruck=1;
                      $exTruckID=0;
                      $zoneID=0;
                      $notes='';
                      $tname=addslashes($truck['truck']);
                      $tdesc=addslashes($truck['description']);
                  }
                  
                  $sql="INSERT INTO jobs_inserter_trucks (pub_date, truck_id, pub_id, run_id, newtruck, truck_order, truck_name, truck_description, truck_notes, zone_id, draw)   VALUES ('$pubdate', '$exTruckID', '$pubid', '$runid', '$newtruck', '$order', '$tname', '$tdesc', '$notes', '$zoneID', '$tdraw')";
                  $dbInsertTruck=dbinsertquery($sql);
                  $truckid=$dbInsertTruck['numrows'];
                  //now go through any associated routes
                  if (count($truck['routes'])>0)
                  {
                      foreach($truck['routes'] as $route)
                      {
                          $routenum=addslashes($route['route']);
                          $sequence=addslashes($route['sequence']);
                          $routeaccount=addslashes($route['account']);
                          $phone=addslashes($route['phone']);
                          $notes=addslashes($route['notes']);
                          $bulk=addslashes($route['bulk']);
                          
                          //lets find if there are any routes that match this one
                          $sql="SELECT * FROM publications_insertroutes WHERE truck_id=$exTruckID AND route_number='$routenum'";
                          $dbExRoute=dbselectsingle($sql);
                          if ($dbExRoute['numrows']>0)
                          {
                              $newroute=0;
                              $exroute=$dbExRoute['data'];
                              $exid=$exroute['id'];
                              $routenum=$exroute['route_number'];
                              //update the existing route with new notes, sequence, phone and notes
                              $sql="UPDATE publications_insertroute SET route_phone='$phone', route_notes='$notes', route_sequence='$sequence' WHERE id=$exid";
                              $dbRUpdate=dbexecutequery($sql);
                          } else {
                              $newroute=1;
                          }
                          $rdraw=str_replace(",","",$route['draw']);
                          if (!$newtruck)
                          {
                            $truckid=$exTruckID;    
                          }
                          $sql="INSERT INTO jobs_inserter_routes (pub_date, route_id, newroute, newtruck, pub_id, run_id, truck_id, route_account, route_number, bulk, route_phone, route_notes, route_sequence, route_draw) VALUES ('$pubdate','$exid','$newroute', '$newtruck', '$pubid', '$runid', '$truckid', '$routeaccount', '$routenum', '$bulk', '$phone', '$notes', '$sequence', '$rdraw')";
                          $dbRoute=dbinsertquery($sql);
                      }
                  }
                  
                  $order=$order+10;
              }
              
              //now lets see if we have any new routes and trucks that we need to check on
              $zones=array();
                $zones[0]='Please choose';
                $sql="SELECT * FROM publications_insertzones WHERE run_id=$runid ORDER BY zone_order";
                $dbZones=dbselectmulti($sql);
                if ($dbZones['numrows']>0)
                {
                    foreach ($dbZones['data'] as $record)
                    {
                        $zones[$record['id']]=$record['zone_name'];
                    }
                }
    
              
              print "<form name='trucks' id='trucks' method=post>\n";
              $sql="SELECT * FROM jobs_inserter_trucks WHERE pub_id='$pubid' AND run_id='$runid' AND pub_date='$pubdate' AND newtruck=1";
              $dbNewTrucks=dbselectmulti($sql);
              if ($dbNewTrucks['numrows']>0)
              {
                    $new=true;
                    print "<div class='label'>Trucks</div><div class='input'>The following are new trucks (any routes for the new truck will automatically be imported)<br />";
                    foreach($dbNewTrucks['data'] as $newtruck)
                    {
                        print "<input type='checkbox' name='truck_$newtruck[id]'>$newtruck[truck_name] &nbsp;|&nbsp;Zone:\n";
                        print "<select name='tzone_$newtruck[id]'>\n";
                        foreach($zones as $zid=>$zname)
                        {
                            print "<option name='$zid' value='$zid'>$zname</option>\n";
                        }
                        print "</select><br />\n";
                    }
                    print "</div><div class='clear'></div>\n";    
              }
              //get only new routes for new trucks
              //new routes for new trucks will be automatically taken care of
              $sql="SELECT * FROM jobs_inserter_routes WHERE pub_id='$pubid' AND run_id='$runid' AND pub_date='$pubdate' AND newtruck=1 AND newroute=1";
              $dbNewRoutes=dbselectmulti($sql);
              if ($dbNewRoutes['numrows']>0)
              {
                print "<div class='label'>Routes</div><div class='input'>The following are new routes for existing trucks<br />";
                foreach($dbNewRoutes['data'] as $newroute)
                {
                    print "<input type='checkbox' name='route_$newroute[id]'>$newroute[route_number]<br />\n";
                }
                print "</div><div class='clear'></div>\n";
                $new=true;    
              }
              if ($new)
              {
                  make_hidden('pubid',$pubid);
                  make_hidden('runid',$runid);
                  make_hidden('pubdate',$pubdate);
                  print "<input type='button' value='Select All' onClick=\"checkAllCheckboxes('trucks');\">\n";  
                  print "<input type='button' value='Deselect All' onClick=\"uncheckAllCheckboxes('trucks');\">\n";  
                  make_submit('submit','Update trucks/routes');
              } else {
                  print "Manifest successfully imported. No new trucks or routes to report.";
              }
              print "</form>\n";
              
          }
          
          
          
      } 
      
      //lets print out all the trucks now
      if ($_POST['showresults'])
      {
          print "<br><br><h2>TRUCK INFORMATION</h2><br><br>";
          $routecount=0;
          $truckcounts=0;
          foreach($trucks as $truck)
          {
              $truckroutes=count($truck['routes']);
              $truckcounts++;
              print "Truck #: ".$truck['truck']." Description: ".$truck['description']." Draw: ".$truck['totaldraw']." Product: ".$truck['product']." containing $truckroutes routes<br>\n";
              //now print out each route on the truck
              foreach ($truck['routes'] as $route)
              {
                print "Route: $route[route] - Draw: $route[draw] - Product: $route[product] - phone $route[phone] - notes - $route[notes]<br />\n";
                $total+=$route['draw'];
                $routecount++;
              }
              print "Calculated total for truck is $total<br />\n";
              $manifesttotal+=$total;
              $total=0;
              
          }
          print "<br><br>Total draw is $manifesttotal";
          print "<br>Total of $truckcounts trucks<br>\n";
          print "<br>Total of $routecount routes<br>\n";
      }
  
}
?>
