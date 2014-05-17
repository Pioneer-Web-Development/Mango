<?php
//<!--VERSION: .9 **||**-->
session_start();
$userid=$_SESSION['cmsuser']['userid'];
?>
<html>
<head>
<script type="text/javascript" src="includes/functions_pims.js"></script>
<style type='text/css'>
body{
    font-family: Trebuchet MS, Arial, sans-serif;
    font-size:12px;
    padding:5px;
}
.redbutton{
    padding: 5px;
    background-color: #FFDB4F;
    color:#AC1D23;
    font-family:Trebuchet MS;
    font-weight:bold;
    font-size:16px; 
}
.redbutton:hover
{
    background-color: #AC1D23;
    color:#FFDB4F; 
}
.clear{
    clear:both;
}
.input{
    float:left;
    margin-top:2px;
}
.label{
    text-align:left;
    width:100px;
    font-weight:bold;
    float:left;
    margin-top:2px;
}
table {
    width: 510px;
    border-collapse:collapse;
    border:1px solid #FFCA5E;
}
caption {
    text-align: left;
    text-indent: 10px;
    background: url(artwork/Table2/bg_caption.jpg) right top;
    height: 45px;
    color: #FFAA00;
}
thead th {
    background: url(artwork/Table2/bg_th.jpg) no-repeat right;
    height: 47px;
    color: #FFFFFF;
    font-size: 0.8em;
    font-weight: bold;
    padding: 0px 7px;
    margin: 20px 0px 0px;
    text-align: left;
    border-right: 1px solid #FCF1D4;
}
tbody tr {
background: url(artwork/Table2/bg_td1.jpg) repeat-x top;
}
tbody tr.odd {
    background: #FFF8E8 url(artwork/Table2/bg_td2.jpg) repeat-x;
}

tbody th,td {
    font-size: 0.8em;
    line-height: 1.4em;
    color: #777777;
    padding: 10px 7px;
    border-top: 1px solid #FFCA5E;
    border-right: 1px solid #DDDDDD;
    text-align: left;
}
a {
    color: #777777;
    font-weight: bold;
    text-decoration: underline;
}
a:hover {
    color: #F8A704;
    text-decoration: underline;
}
tfoot th {
    background: url(artwork/Table2/bg_total.jpg) repeat-x bottom;
    color: #FFFFFF;
    height: 30px;
}
tfoot td {
    background: url(artwork/Table2/bg_total.jpg) repeat-x bottom;
    color: #FFFFFF;
    height: 30px;
}

</style>
</head>
<body>
<?php
//faq system
//this exists in a popup window
//start off with a search tool for keywords, and a list of top level topics
include("includes/functions_db.php");
include("includes/functions_formtools.php");
include("includes/functions_graphics.php");
include("includes/config.php");

//build some arrays
global $siteID;

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
$sql="SELECT * FROM helpdesk_types WHERE site_id=$siteID ORDER BY type_name";
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

//get username
if ($_SESSION['cmsuser']['helpsource']=='')
{
  if ($_GET['source']=='pims')
  {
      $_SESSION['cmsuser']['helpsource']=='pims';
  } else {
      $_SESSION['cmsuser']['helpsource']=='main';
  }
}
if ($_SESSION['cmsuser']['helpsource']=='main')
{
  $sql="SELECT * FROM employees WHERE id=$userid";
} else {
  $sql="SELECT * FROM cms_staff WHERE id=$userid";
}
$dbEmployee=dbselectsingle($sql);
$firstname=$dbEmployee['data']['firstname'];
if ($_POST['submit']=='Search')
{
  $keywords=addslashes($_POST['keywords']);
  $keywords=str_replace(";"," ",$keywords);
  $keywords=str_replace(","," ",$keywords);
  $keywords=str_replace("  "," ",$keywords);
  $keywords=explode(" ",$keywords);
  //print_r($keywords); 
  if (count($keywords)>1)
  {
      $keycheck="";
      foreach($keywords as $key)
      {
          $keycheck.="keywords LIKE '%$key%' OR ";
      }
      $keycheck=substr($keycheck,0,strlen($keycheck)-4);
  } else {
      $keywords=$keywords[0];
      $keycheck="keywords LIKE '%$keywords%'";
  }
  $sql="SELECT * FROM helpdesk_solutions WHERE site_id=$siteID AND public=1 AND $keycheck";
  //print "checking with<br>$sql<br>\n";
  $dbSolutions=dbselectmulti($sql);
  if ($dbSolutions['numrows']>0)
  {
      print "<h2>The following help topics match some or all of your keywords:</h2>\n";
      
      foreach($dbSolutions['data'] as $solution)
      {
          //check each one to see if there are sub items, if so the href will differ
          $sql="SELECT * FROM helpdesk_solutions WHERE parentid=$solution[id]";
          $dbSub=dbselectmulti($sql);
          if ($dbSub['numrows']>0)
          {
              print "<li><a href='?action=sub&topicid=$solution[id]'>$solution[title]</a></li>\n";
          } else {
              print "<li><a href='?action=view&topicid=$solution[id]'>$solution[title]</a></li>\n";
          }
      }
  } else {
      print "<a href='?action=default'>Sorry, there appears to be nothing available for that topic.</a>\n";
  }
}elseif ($_POST['submit']=='Trouble')
{
  trouble_tickets('add');    
}elseif ($_POST['submit']=='Return')
{
//save the trouble ticket
show_initial();    
}elseif ($_POST['submit']=='Save Ticket')
{
//save the trouble ticket
save_ticket();    
} else {
  if ($_GET['action']=='view')
  {
      show_topic($_GET['type']);
  } elseif($_GET['action']=='submit')
  {
      trouble_tickets('add');
  } elseif($_GET['action']=='editticket')
  {
      trouble_tickets('edit');
  } elseif($_GET['action']=='viewsolution')
  {
      view_solution();
  } elseif ($_GET['action']=='sub'){
      //show a list of sub topics
      $sql="SELECT * FROM helpdesk_solutions WHERE site_id=$siteID AND public=1 AND parentid=$_GET[topicid]";
      $dbFaqs=dbselectmulti($sql);
      if ($dbFaqs['numrows']>0)
      {
          print "<small><a href='?action=default'>Return to main</a></small><br />\n";
          print "<h2>The following helpdesk topics are available:</h2>\n";
          foreach($dbFaqs['data'] as $faq)
          {
              
              //check each one to see if there are sub items, if so the href will differ
              $sql="SELECT * FROM helpdesk_solutions WHERE parentid=$faq[id]";
              $dbSub=dbselectmulti($sql);
              if ($dbSub['numrows']>0)
              {
                  print "<li><a href='?action=sub&topicid=$faq[id]'>$faq[title]</a></li>\n";
              } else {
                  print "<li><a href='?action=view&topicid=$faq[id]'>$faq[title]</a></li>\n";
              }
          }
      } else {
          print "<a href='?action=default'>Sorry, there appears to be nothing available for that topic.</a>\n";
      }
  } else {
      show_initial();
  }
}
function show_initial()
{
  global $helpStatuses, $helpPriorities, $helpTypes, $userid, $firstname;
  $firstname=" ".$firstname;
    //show the inital form
  print "<div style='float:left;width:150px;'><img src='artwork/mango.png' border=0 width=140'></div>\n";
  print "<div style='float:left;margin-top:20px;font-size:24px;font-weight:bold;color:#AC1D23'>Welcome to the help desk\n</div>\n";
  print "<div style='clear:both;height:0px;width:0px;'></div>\n";
  print "<h3>Please enter a keyword to search for, select a topic from the list below, or submit a new trouble ticket.</h3>\n";
  print "<form method=post>\n";
  print "Keyword: <input type='text' name='keywords' value=''>\n";
  print "<button name='submit' value='Search' class='redbutton' onclick='this.form.submit();'>\n";
  print "<span>Search</span>\n";
  print "</button>\n";
  print "<button name='submit' value='Trouble' class='redbutton' onclick='this.form.submit();'>\n";
  print "<span>Submit Trouble Ticket</span>\n";
  print "</button>\n";
  print "</form>\n";
  
  //show any open or closed tickets for this person
  //show status of your trouble ticket
  $ticketid=$_GET['ticketid'];
  $sql="SELECT * FROM helpdesk_tickets WHERE submitted_by=$userid";
  $dbTickets=dbselectmulti($sql);
  if ($dbTickets['numrows']>0)
  {
      print "<table>\n";
      print "<tr><th>Ticket #</th><th>Submit Date/Time</th><th>Priority</th><th>Status</th></tr>\n";
      foreach($dbTickets['data'] as $ticket)
      {
          $brief=wordwrap($ticket['help_brief'],30,'<br />',1);
          print "<tr><td><a href='?action=editticket&ticketid=$ticket[id]'>$ticket[id] - $brief</a></td>";
        print "<td>".date("D, M d @ H:i",strtotime($ticket['submitted_datetime']))."</td>";
        print "<td>".$helpPriorities[$ticket['priority_id']]."</td>";      
        print "<td>".$helpStatuses[$ticket['status_id']]."</td>";
        print "</tr>\n";      
      }
      print "</table>\n";
  } else {
      print "No tickets in the system for you yet. Congrats on leading a trouble-free life!";
  }

  
   
  $sql="SELECT * FROM helpdesk_solutions WHERE site_id=$siteID AND public=1 AND parentid=0 ORDER BY title";
  //print "Checking for solutions with $sql<br />\n";
  $dbSolutions=dbselectmulti($sql);
  if ($dbSolutions['numrows']>0)
  {
      print "<h2>The following helpdesk topics are available:</h2>\n";
      $i=round($dbSolutions['numrows']/2,0);
      print "<div style='float:left;margin-left:10px;'>\n";
      foreach($dbSolutions['data'] as $solution)
      {
          
          //check each one to see if there are sub items, if so the href will differ
          if ($c>$i)
          {
            print "</div><div style='float:left;margin-left:10px;'>\n";    
          }
          $sql="SELECT * FROM helpdesk_solutions WHERE parentid=$solution[id]";
          $dbSub=dbselectmulti($sql);
          if ($dbSub['numrows']>0)
          {
              print "<li><a href='?action=sub&topicid=$solution[id]'>$solution[title]</a></li>\n";
          } else {
              print "<li><a href='?action=view&topicid=$solution[id]'>$solution[title]</a></li>\n";
          }
      }
      print "</div>\n";
  }
  
} 
 
 function view_solution()
 {
     $ticketid=$_GET['ticketid'];
     $sql="SELECT * FROM helpdesk_solutions WHERE ticket_id=$ticketid";
     $dbSolution=dbselectsingle($sql);
     $solution=$dbSolution['data'];
     print "<a href='#' onclick='window.print();'><img src='artwork/printer.png' width=32 border=0'>Print this solution</a><br />\n";
     print "<h2>$solution[title]</h2>\n";
     print "<p style='font-weight:bold;'>$solution[solution_brief]</p>\n";
     print "<p>$solution[solution_text]</p>\n";
     print "<p><a href='?action=return'>Return to main</a></p>\n";  
 }
  
 function trouble_tickets($action)
 {
     global $helpStatuses, $helpPriorities, $helpTypes,$siteID;
     if($_GET['source']=='press')
     {
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
     }
     if ($action=='add')
     {
         if ($_GET['action']=='submit')
         {
             $type=$_GET['type'];
         } else {
             $type=$helpTypes[0];
         }
         $ticketid=0;
         $status=0;
     } else {
         $ticketid=$_GET['ticketid'];
         $sql="SELECT * FROM helpdesk_tickets WHERE id=$ticketid";
         $dbTicket=dbselectsingle($sql);
         $ticket=$dbTicket['data'];
         $brief=stripslashes($ticket['help_brief']);
         $full=stripslashes($ticket['help_request']);
         $type=stripslashes($ticket['type_id']);
         $status=$ticket['status_id'];
         $priority=$ticket['priority_id'];
     }
     //collect a trouble ticket
      print "<form method=post enctype='multipart/form-data'>\n";
      make_hidden('ticketid',$ticketid);
      if ($status!=0)
      {
          print "Currently, your ticket is at <b>".$helpStatuses[$status]."</b> ";
          if ($status==$GLOBALS['helpdeskCompleteStatus'])
          {
            print " - <a href='?action=viewsolution&ticketid=$ticketid'>View the solution.</a>\n";     
          }
          print "<br />\n";
      }
      make_select('helptype',$helpTypes[$type],$helpTypes,'Type of issue','Please select the type of issue you are experiencing');
      make_select('helppriority',$helpPriorities[$priority],$helpPriorities,'Priority');
      if ($_GET['source']=='press')
      {
          make_textarea('helpbrief',$brief,'Brief Outline','Briefy describe the problem',44,3,false,'','','','','','','limitText(this.form.helpbrief,200);');
          make_textarea('helpfull',$full,'Your Fix','What did you try to do to fix the problem?',44,12,false);
          make_hidden('s',1);
      
      } else {
          make_textarea('helpbrief',$brief,'Brief Outline','Briefy describe the problem',44,3,false,'','','','','','','limitText(this.form.helpbrief,200);');
          make_textarea('helpfull',$full,'Full Issue','Please be as specific as you can',44,12,false);
          make_file('attach','Attach a file','Attach a screenshot of the problem if you have one');
      }
      print "<div class='label'>\n";
      print "&nbsp;</div>\n";
      print "<div class='input'>\n";
      print "<button name='submit' value='Save Ticket' class='redbutton' onclick='this.form.submit();'>\n";
      print "<span>Save Ticket</span>\n";
      print "</button>\n";
      print "<button name='submit' value='Return' class='redbutton' onclick='this.form.submit();' >\n";
      print "<span>Cancel and Return</span>\n";
      print "</button>\n";
      print "</div>\n";
      print "</form>\n";
     
     
 } 
 
 function save_ticket()
 {
     global $siteID;
     $ticketid=$_POST['ticketid'];
     $priorityid=$_POST['helppriority'];
     $type=$_POST['helptype'];
     $s=$_POST['s'];
     $brief=addslashes($_POST['helpbrief']);
     $full=addslashes($_POST['helpfull']);
     $userid=$_SESSION['cmsuser']['userid'];
     $ctime=date("Y-m-d H:i");
     if ($s=='1')
     {
        $sql="SELECT * FROM helpdesk_statuses WHERE site_id=$siteID ORDER BY status_order ASC LIMIT 1";
        $dbStatus=dbselectsingle($sql);
        $statusid=$dbStatus['data']['id'];
        $sql="INSERT INTO maintenance_tickets(type_id,status_id,priority_id,submitted_by,
        submitted_datetime,problem,attempt) VALUES ('$type', '$statusid','$priorityid','$userid', '$ctime', '$brief', '$full')";
        $dbInsert=dbinsertquery($sql);
        if ($dbInsert['error']=='')
        {
            print "<script type='text/javascript'>self.close();</script>\n";
            
        } else {
            print "There was a problem saving your help request. You should submit a help request to have the issue fixed ;)";
        }  
     } else {
         if ($ticketid==0)
         {
             //new ticket, assign to the lowest status
             $sql="SELECT * FROM helpdesk_statuses WHERE site_id=$siteID ORDER BY status_order ASC LIMIT 1";
             $dbStatus=dbselectsingle($sql);
             $statusid=$dbStatus['data']['id'];
             $source=$_SESSION['cmsuser']['helpsource'];
             $sql="INSERT INTO helpdesk_tickets (type_id, status_id, priority_id, submitted_by, 
             submitted_datetime, help_brief, help_request, help_source) VALUES ('$type', 
             '$statusid', '$priorityid', '$userid', '$ctime', '$brief', '$full', '$source')";
             $dbInsert=dbinsertquery($sql);
             $ticketid=$dbInsert['numrows'];
             $error=$dbInsert['error'];
         } else {
             $sql="UPDATE helpdesk_tickets SET type_id='$type', priority_id='$priorityid', 
             updated_by='$userid', updated_datetime='$ctime', help_brief='$brief', help_request='$full' WHERE id=$ticketid";
             $dbUpdate=dbexecutequery($sql);
             $error=$dbUpdate['error'];  
         }
         
         if(isset($_FILES))
         { //means we have browsed for a valid file
            foreach($_FILES as $file) {
                switch($file['error']) {
                    case 0: // file found
                    if($file['name'] != NULL && okFileType($file['type'],'image',$file['name']) != false)  {
                        //get the new name of the file
                        //to do that, we need to push it into the database, and return the last record ID
                       // process the file
                        $date=date("Ym");
                        $path="artwork/helpticketImages/$date/";
                        if (!file_exists($path))
                        {
                            mkdir($path);
                        }
                        $newname=$file['name'];
                        $newname=str_replace(" ","",$newname);
                        $newname=str_replace("/","",$newname);
                        $newname=str_replace("\\","",$newname);
                        $newname=str_replace("*","",$newname);
                        $newname=str_replace("?","",$newname);
                        $newname=str_replace("!","",$newname);
                        $newname=str_replace("'","",$newname);
                        $newname=str_replace(";","",$newname);
                        $newname=str_replace(":","",$newname);
                        $newname=str_replace("'","",$newname);
                        $newname=str_replace("%","",$newname);
                        $newname=str_replace("\$","",$newname);
                        $newname="ticket_".$ticketid."_".$newname;
                        if(processFile($file,$path,$newname) == true) {
                            $sql="UPDATE helpdesk_tickets SET ticketImage_path='artwork/helpticketImages/$date/', ticketImage_filename='$newname' WHERE id=$ticketid";
                            $result=dbinsertquery($sql);
                            $error.=$result['error'];
                        } else {
                           $error.= 'There was an error inserting the image named '.$file['name'].' into the database. The sql statement was $sql';  
                        }
                    }
                    break;

                    case (1|2):  // upload too large
                    $error.= 'file upload is too large for '.$file['name'];
                    break;

                    case 4:  // no file uploaded
                    break;

                    case (6|7):  // no temp folder or failed write - server config errors
                    $error.= 'internal error - flog the webmaster on '.$file['name'];
                    break;
                }
            }
         }
         
         if ($error!='')
         {
             print $error;
         } else {
            redirect("?action=default");
         }
     }
 }
  
  function show_topic($topicid)
  {
    print "<small><a href='?action=default'>Return to main</a></small><br />\n";
    print "<div id='solution'>\n";
        $sql="SELECT * FROM helpdesk_solutions WHERE id=$topicid";
        $dbSolution=dbselectsingle($sql);
        $solution=$dbSolution['data'];
        $sql="SELECT * FROM helpdesk_solutions_images WHERE solution_id=$topicid";
        $dbImages=dbselectmulti($sql);
        if ($dbImages['numrows']>0)
        {
            print "<div id='imagelist' style='float:left;width:100px;padding:5px;'>\n";
            foreach($dbImages['data'] as $image)
            {
                print "<a href='#' onclick='window.open(\"helpdeskImageDisplay.php?source=solutions&id=$image[id]\",\"Helpdesk Solutions\",\"width=520,height=480,toolbar=0,status=0,location=0\");' style='text-decoration:none;'><img src='$image[path]$image[filename]' border=0 width=100></a><br /><br />\n";
            }
            print "</div>\n";
            $solwidth=400;
        } else {
            $solwidth=520;
        }
        print "<div style='float:left;width:$solwidth px;'>\n";
        print "<h2>".stripslashes($solution['title'])."</h2>\n";
        print "<span style='text-weight:bold;'>".strip_tags($solution['solution_brief'])."</span><br />\n";
        print stripslashes($solution['solution_text']);    
          
        print "<br /><hr><a href='#' onclick='window.print();'><img src='artwork/printer.png' width=32 border=0'>Print this Solution</a>\n";
        print "</div>\n";  
    print "</div>\n";  
  }
 
 function redirect($url)
 {
   if (!headers_sent())
       header('Location: '.$url);
   else {
       echo '<script type="text/javascript">';
       echo 'document.location.href="'.$url.'";';
       echo '</script>';
       echo '<noscript>';
       echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
       echo '</noscript>';
   }
}
 
 dbclose(); 
?>
</body>
</html>