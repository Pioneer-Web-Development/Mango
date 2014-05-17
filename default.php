<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;
if ($_SESSION['cmsuser']['accessdenied']==true)
{
    ?>
    <script>
         $.ctNotify('You do not have access to that function. If you feel you should, please contact your system administrator',{type: 'error', isSticky: true, delay: 5000},'right-bottom');
    </script>
       
    <?php
        
    $_SESSION['cmsuser']['accessdenied']=false;
}

if ($GLOBALS['debug']==true){
    print "<input type='button' style='float:right;' value='Show Debug Data' onclick=\"\$('#debuginfo').toggle();\"/><div class='clear'></div>\n";
    print "<div id='debuginfo' style='display:none;width:100%;'>\n";
    print "<h3>Session Information:</h3>\n<pre>\n";
    print_r($_SESSION);
    print "</pre><hr>";
    phpinfo();
    print "</div>";
} 
print "<div style='margin-top:20px;margin-left:auto;margin-right:auto;width:960px;'>\n";
//see if this user has any modules, otherwise, show the defaults
$sql="SELECT A.* FROM dashboard_items A, user_dashboard B WHERE B.user_id=".$_SESSION['cmsuser']['userid']." AND B.module_id=A.id";

$dbBlocks=dbselectmulti($sql);
if($dbBlocks['numrows']>0)
{
    print "<div id='mainContentHolderCol1' class='column'>\n";
    show_module('1');
    print "</div>\n";
    print "<div id='mainContentHolderCol2' class='column'>\n";
    show_module('2'); 
    print "</div>\n";
    print "<div id='mainContentHolderCol3' class='column'>\n";
    show_module('3'); 
    print "</div>\n";
} else {
    //user has no dashboard items, so move on
}
print "<div class='clear'></div>\n";
print "</div>\n";


function show_module($column)
{
    $sql="SELECT A.*, B.collapsed FROM dashboard_items A, user_dashboard B WHERE B.user_id=".$_SESSION['cmsuser']['userid']." AND B.module_id=A.id AND B.module_column='$column' ORDER BY B.module_order";
    $dbModules=dbselectmulti($sql);
    if ($dbModules['numrows']>0){
        foreach ($dbModules['data'] as $module){
           $moduleid=$module['id'];
           $collapsed=$module['collapsed'];
           $function=stripslashes($module['function_name']);
           $blockname=stripslashes($module['dashboard_name']);
           $function($collapsed,$blockname,$moduleid); 
        }
    }
    
}

function mango_news($collapsed,$blockname,$blockid)
{
    global $siteID;
    print "<div id='item_$blockid' class='dragBox'>\n";
    if($collapsed)
    {
        $class='ui-icon-plusthick';
    } else {
        $class='ui-icon-minusthick';
    }
    print "<div id='toggle_$blockid' class='dashboardHeader'>$blockname<span class='ui-icon $class'></span></div>\n";
    if($collapsed)
    {
        print "<div id='box_$blockid' class='dashboardBox' style='display:none;'>\n";
    } else {
        print "<div id='box_$blockid' class='dashboardBox'>\n";
    }
    $dt=date("Y-m-d H:i");
    $sql="SELECT * FROM mango_news WHERE archive_datetime>='$dt' OR sticky=1 ORDER BY urgent DESC, post_datetime DESC";
    if($GLOBALS['debug']){
        print "Looking for news items with $sql<br>";
    }
    $dbNews=dbselectmulti($sql);
    if ($dbNews['numrows']>0)
    {
        foreach($dbNews['data'] as $news)
        {
            //who wrote it?
            $sql="SELECT firstname,lastname FROM users WHERE id=$news[post_by]";
            $dbAuthor=dbselectsingle($sql);
            $author="By: ".$dbAuthor['data']['firstname'].' '.$dbAuthor['data']['lastname'];
            
            if ($news['urgent'])
            {
                print "<p class='dashboardHeadlineUrgent'>$news[headline]</p>\n";
            } else {
                print "<p class='dashboardHeadline'>$news[headline]</p>\n";
            }
            print "<p style='font-size:10px;font-weight:normal'>Author $author</p>\n";
            print "<p style='font-size:10px;font-weight:normal'>Posted ".date("D m/d @ H:i",strtotime($news['post_datetime']))."</p>\n";
            print "<p class='dashboardItem'>$news[message]</p>\n";
        }
    } else {
        print "No news today.";
    }
    
    print "</div>\n<!--close dashboardBox -->\n";
    print "</div><!--close dragbox -->\n";
    if($collapsed)
    {
        ?>
        <script>
        $('toggle_<?php echo $blockid ?>').toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
        $('box_<?php echo $blockid ?>').hide();
        </script>
            
        <?php
    
    }
}

function upcoming_special_sections($collapsed,$blockname,$blockid)
{
    global $pubids, $siteID, $folders;
    print "<div id='item_$blockid' class='dragBox'>\n";
    if($collapsed)
    {
        $class='ui-icon-plusthick';
    } else {
        $class='ui-icon-minusthick';
    }
    print "<div id='toggle_$blockid' class='dashboardHeader'>$blockname<span class='ui-icon $class'></span></div>\n";
    if($collapsed)
    {
        print "<div id='box_$blockid' class='dashboardBox' style='display:none;'>\n";
    } else {
        print "<div id='box_$blockid' class='dashboardBox'>\n";
    }
     
    $opened=false;
    $start=date("Y-m-d");
    
    $sql="SELECT A.*, B.pub_name FROM special_sections A, publications B WHERE A.site_id=$siteID AND A.pub_id=B.id AND A.insert_date>='$start' AND A.pub_id IN ($pubids) ORDER BY A.insert_date ASC";
    $dbJobs=dbselectmulti($sql);
    $i=0;
    if ($dbJobs['numrows']>0)
    {
        foreach($dbJobs['data'] as $job)
        {
            print "<p class='dashboardHeadline'><a href='specialSections.php?action=edit&sectionid=$job[id]'>$job[pub_name] - $job[section_name]</a><br>\n";
            print "<span class='dashboardItem'>Publishing: $job[insert_date]</span><br>\n";
            print "<span class='dashboardItem'>Prints to ".$folders[$job['folder']]." on ";        
            print date("D m/d @ H:i",strtotime($job['startdatetime']))."</span></p>";
            $i++;
            if($i==10 && $dbJobs['numrows']>10)
            {
                $opened=true;
                print "<p id='uss_toggle_more' class='dashboardToggle'>Show more...</span></p>\n";
                print "<div id='uss_more' style='display:none;'><p id='uss_toggle_less' class='dashboardToggle'>Show less...</span></p>\n";    
            }        
        }
        if ($opened)
        {
            print "</div>\n";
            ?>
             <script>
            $('#uss_toggle_more').click(function() {
                  $('#uss_more').slideToggle('fast', function() {
                      $('#uss_toggle_more').css('display','none')
                  });
                });
            $('#uss_toggle_less').click(function() {
                  $('#uss_more').slideToggle('fast', function() {
                    $('#uss_toggle_more').css('display','block')
                  });
                });
            $("#uss_toggle_more").button();
            $("#uss_toggle_less").button();
            </script>
           <?php
        }
    } else {
        print "<p class='dashboardHeadline'>There are no special sections planned at this time.</p>";
    }
    print "</div>\n<!--close dashboardBox -->\n";
    print "</div><!--close dragbox -->\n";
}


function upcoming_pressjobs($collapsed,$blockname,$blockid)
{
    global $pubids, $siteID, $folders;
    print "<div id='item_$blockid' class='dragBox'>\n";
    if($collapsed)
    {
        $class='ui-icon-plusthick';
    } else {
        $class='ui-icon-minusthick';
    }
    print "<div id='toggle_$blockid' class='dashboardHeader'>$blockname<span class='ui-icon $class'></span></div>\n";
    if($collapsed)
    {
        print "<div id='box_$blockid' class='dashboardBox' style='display:none;'>\n";
    } else {
        print "<div id='box_$blockid' class='dashboardBox'>\n";
    }
    $opened=false;
    $start=date("Y-m-d H:i");
    $end=date("Y-m-d H:i",strtotime("+24 hours"));

    $sql="SELECT A.*, B.pub_name, C.run_name FROM jobs A, publications B, publications_runs C WHERE A.site_id=$siteID AND A.pub_id=B.id AND A.run_id=C.id AND A.continue_id=0 AND A.status<>99 AND A.startdatetime>='$start' AND A.startdatetime<='$end' AND A.pub_id IN ($pubids) ORDER BY A.startdatetime ASC";
    $dbJobs=dbselectmulti($sql);
    $i=0;
    if ($dbJobs['numrows']>0)
    {
        foreach($dbJobs['data'] as $job)
        {
            print "<p class='dashboardHeadline'><a href='#' onclick=\"window.open('jobPressPopup.php?id=$job[id]','Edit Press Job','width=800,height=900,status=no,location=no,toolbar=no,menubar=no,navigation=no')\">$job[pub_name] - $job[run_name]</a><br>\n";
            print "<span class='dashboardItem'>Publishing: $job[pub_date] with draw of $job[draw]</span><br>\n";
            print "<span class='dashboardItem'>Prints to ".$folders[$job['folder']]." on ";        
            print date("D m/d @ H:i",strtotime($job['startdatetime']))."</span></p>";        
            $i++;
            if($i==10 && $dbJobs['numrows']>10)
            {
                $opened=true;
                print "<p id='pj_toggle_more' class='dashboardToggle'>Show more...</p>\n";
                print "<div id='pj_more' style='display:none;'><p id='pj_toggle_less' class='dashboardToggle'>Show less...</p>\n";
                    
            }        
        }
        if ($opened)
        {
            print "</div>\n";
            ?>
            <script>
            $('#pj_toggle_more').click(function() {
                  $('#pj_more').slideToggle('slow', function() {
                      $('#pj_toggle_more').css('display','none')
                  });
                });
            $('#pj_toggle_less').click(function() {
                  $('#pj_more').slideToggle('slow', function() {
                    $('#pj_toggle_more').css('display','block')
                  });
                });
            $("#pj_toggle_more").button();
            $("#pj_toggle_less").button();
            </script>
           <?php
        }
    } else {
        print "<p class='dashboardHeadline'>Somehow, there are no jobs in the next 24 hours. Must be a time for a party!</p>";
    }
    print "</div>\n<!--close dashboardBox -->\n";
    print "</div><!--close dragbox -->\n";
}

function upcoming_insertpackages($collapsed,$blockname,$blockid)
{
    global $pubids,$siteID, $inserters;
    print "<div id='item_$blockid' class='dragBox'>\n";
    if($collapsed)
    {
        $class='ui-icon-plusthick';
    } else {
        $class='ui-icon-minusthick';
    }
    print "<div id='toggle_$blockid' class='dashboardHeader'>$blockname<span class='ui-icon $class'></span></div>\n";
    if($collapsed)
    {
        print "<div id='box_$blockid' class='dashboardBox' style='display:none;'>\n";
    } else {
        print "<div id='box_$blockid' class='dashboardBox'>\n";
    }
    $opened=false;
    
    $start=date("Y-m-d H:i");
    $end=date("Y-m-d H:i",strtotime("+96 hours"));

    $sql="SELECT A.*, B.pub_name FROM jobs_inserter_packages A, publications B WHERE A.package_startdatetime>='$start' AND A.package_startdatetime<='$end' AND A.pub_id IN ($pubids) AND A.pub_id=B.id ORDER BY A.package_startdatetime ASC";
    $dbPackages=dbselectmulti($sql);
    $i=0;
    if ($dbPackages['numrows']>0)
    {
        foreach($dbPackages['data'] as $job)
        {
            print "<p class='dashboardHeadline'><a href='buildInsertPackage.php?planid=$job[plan_id]&packageid=$job[id]&pubid=$job[pub_id]'>$job[pub_name] - $job[package_name]</a><br>";
            print "<span class='dashboardItem'>Publishes: $job[pub_date] and we need $job[inserter_request]</span><br>";   
            print "<span class='dashboardItem'>Runs on ".$inserters[$job['inserter_id']]." at ";        
            print date("D m/d \@ H:i",strtotime($job['package_startdatetime']))."</span></p>\n";        
            $i++;
            if($i==10 && $dbPackages['numrows']>10)
            {
                $opened=true;
                print "<p><a href='#' id='ip_toggle_more' class='dashboardToggle'>Show more...</a></p>\n";
                print "<div id='ip_more' style='display:none;'><p><a href='#' id='ip_toggle_less' class='dashboardToggle'>Show less...</a></p>\n";    
            }        
        }
        if ($opened)
        {
            print "</div>\n";
            ?>
            <script>
            $('#ip_toggle_more').click(function() {
                  $('#ip_more').slideToggle('fast', function() {
                      $('#ip_toggle_more').css('display','none')
                  });
                });
            $('#ip_toggle_less').click(function() {
                  $('#ip_more').slideToggle('fast', function() {
                    $('#ip_toggle_more').css('display','block')
                  });
                });
            $("#ip_toggle_more").button();
            $("#ip_toggle_less").button();
            </script>
           <?php
        }
    } else {
        print "<p class='dashboardHeadline'>No packages sheduled for the next 48 hours.</p>\n";
    }
    
    print "</div>\n<!--close dashboardBox -->\n";
    print "</div><!--close dragbox -->\n";
}

function missing_inserts($collapsed,$blockname,$blockid)
{
    global $pubids,$siteID;
    print "<div id='item_$blockid' class='dragBox'>\n";
    if($collapsed)
    {
        $class='ui-icon-plusthick';
    } else {
        $class='ui-icon-minusthick';
    }
    print "<div id='toggle_$blockid' class='dashboardHeader'>$blockname<span class='ui-icon $class'></span></div>\n";
    if($collapsed)
    {
        print "<div id='box_$blockid' class='dashboardBox' style='display:none;'>\n";
    } else {
        print "<div id='box_$blockid' class='dashboardBox'>\n";
    }
    $i=0;
    $opened=false;
    $dt=date("Y-m-d",strtotime('-1 month'));
    $sql="SELECT A.*, B.account_name FROM inserts A, accounts B, inserts_schedule C 
    WHERE A.received=0 AND C.insert_id=A.id AND C.insert_date>='$dt' AND A.advertiser_id=B.id ORDER BY C.insert_date LIMIT 50";
    $dbInserts=dbselectmulti($sql);
    if ($dbInserts['numrows']>0)
    {
        foreach ($dbInserts['data'] as $insert)
        {
            print "<p class='dashboardHeadline'><a href='inserts.php?action=edit&insertid=$insert[id]'>$insert[account_name]</a><br>\n";
            print "<span class='dashboardItem'>Publishes on ";
            print date("D m/d",strtotime($insert['insert_date']))."</span><br>\n";
            $sql="SELECT * FROM jobs_inserter_packages WHERE id=$insert[package_id]";
            $dbPackage=dbselectsingle($sql);
            if ($dbPackage['numrows']>0)
            {
                $package=$dbPackage['data'];
                print "<span class='dashboardItem'>Included in a package running ";
                print date("D m/d \@ H:i",strtotime($package['package_startdatetime']))."</p>\n";    
            } else {
                print "<span class='dashboardItem'>Has not been included in a package yet.</span></p>\n";
            }
            
            $i++;
            if($i==10 && $dbInserts['numrows']>10)
            {
                $opened=true;
                print "<p id='mi_toggle_more' class='dashboardToggle'>Show more...</p>\n";
                print "<div id='mi_more' style='display:none;'><p id='mi_toggle_less' class='dashboardToggle'>Show less...</p>\n";    
            }        
        }
        if ($opened)
        {
            print "</div>\n";
            ?>
            <script>
            $('#mi_toggle_more').click(function() {
                  $('#mi_more').slideToggle('fast', function() {
                      $('#mi_toggle_more').css('display','none')
                  });
                });
            $('#mi_toggle_less').click(function() {
                  $('#mi_more').slideToggle('fast', function() {
                    $('#mi_toggle_more').css('display','block')
                  });
                });
            $("#mi_toggle_more").button();
            $("#mi_toggle_less").button();
            </script>
           <?php
        }
    } else {
        print "<p class='dashboardHeadline'>No missing inserts!</p>\n";
    }
    
    print "</div>\n<!--close dashboardBox -->\n";
    print "</div><!--close dragbox -->\n";
}

function press_maintenance($collapsed,$blockname,$blockid)
{
    global $siteID;
    print "<div id='item_$blockid' class='dragBox'>\n";
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
    $opened=false;
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
    $i=0;
    if($collapsed)
    {
        $class='ui-icon-plusthick';
    } else {
        $class='ui-icon-minusthick';
    }
    print "<div id='toggle_$blockid' class='dashboardHeader'>$blockname<span class='ui-icon $class'></span></div>\n";
    if($collapsed)
    {
        print "<div id='box_$blockid' class='dashboardBox' style='display:none;'>\n";
    } else {
        print "<div id='box_$blockid' class='dashboardBox'>\n";
    }
    
    $sql="SELECT * FROM maintenance_tickets WHERE status_id<>'$GLOBALS[helpdeskCompleteStatus]' ORDER BY priority_id DESC, submitted_datetime DESC";
    $dbTickets=dbselectmulti($sql);
    print "<a href='#' onclick=\"window.open('helpdeskSubmit.php?action=submit&type=1&source=press','Help Desk','width=580,height=600,toolbar=no,status=no,location=no,scrollbars=no');return false;\">Click here to open a new trouble ticket.</a>\n";
    if ($dbTickets['numrows']>0)
    {
        foreach($dbTickets['data'] as $ticket)
        {
            $priority=$helpPriorities[$ticket['priority_id']];
            $type=$helpTypes[$ticket['type_id']];
            $id=$ticket['id'];
            $brief=$ticket['problem'];
            print "<p class='dashboardHeadline'><a href='maintenanceTickets.php?action=edit&id=$id'>Maintence Ticket # $id</a><br>\n";
            print "<span class='dashboardItem'>Priority: $priority</span><br>\n";        
            print "<span class='dashboardItem'>Trouble type: $type</span><br>\n";        
            print "<span class='dashboardItem'>$brief</span></p>\n";        
            $i++;
            if($i==10 && $dbTickets['numrows']>10)
            {
                $opened=true;
                print "<p id='mt_toggle_more' class='dashboardToggle'>Show more...</p>\n";
                print "<div id='mt_more' style='display:none;'><p id='mt_toggle_less class='dashboardToggle'>Show less...</p>\n";    
            }        
        }
        if ($opened)
        {
            print "</div>\n";
            ?>
            <script>
            $('#mt_toggle_more').click(function() {
                  $('#mt_more').slideToggle('fast', function() {
                      $('#mt_toggle_more').css('display','none')
                  });
                });
            $('#mt_toggle_less').click(function() {
                  $('#mt_more').slideToggle('fast', function() {
                    $('#mt_toggle_more').css('display','block')
                  });
                });
            $("#mt_toggle_more").button();
            $("#mt_toggle_less").button();
            </script>
           <?php
        }
    } else {
        print "No current maintenance tickets are open.";
    }   
    
    
    print "</div>\n<!--close dashboardBox -->";
    print "</div><!--close dragbox -->\n";
}

function short_inventory($collapsed,$blockname,$blockid)
{
    global $siteID;
    print "<div id='item_$blockid' class='dragBox'>\n";
    if($collapsed)
    {
        $class='ui-icon-plusthick';
    } else {
        $class='ui-icon-minusthick';
    }
    print "<div id='toggle_$blockid' class='dashboardHeader'>$blockname<span class='ui-icon $class'></span></div>\n";
    if($collapsed)
    {
        print "<div id='box_$blockid' class='dashboardBox' style='display:none;'>\n";
    } else {
        print "<div id='box_$blockid' class='dashboardBox'>\n";
    }
        $i=0;
        $opened=false;
        $sql="SELECT * FROM equipment_part WHERE part_inventory_quantity<=part_reorder_quantity ORDER BY part_name";
        $dbParts=dbselectmulti($sql);
        if ($dbParts['numrows']>0)
        {
            print "<p class='dashboardHeadline'><a href='purchaseOrders.php?action=add'>Click here to create a purchase order</a><br>\n";
            foreach($dbParts['data'] as $part)
            {
                print "<span class='dashboardHeadline'>".stripslashes($part['part_name'])."</span><br>\n";
                print "<span class='dashboardItem'>Current Inventory: $part[part_inventory_quantity]</span></p>\n";   
                $i++;
                if($i==10 && $dbParts['numrows']>10)
                {
                    $opened=true;
                    print "<p id='si_toggle_more' class='dashboardToggle'>Show more...</p>\n";
                    print "<div id='si_more' style='display:none;'><p id='si_toggle_less' class='dashboardToggle'>Show less...</p>\n";    
                }
            }
            if ($opened)
            {
                print "</div>\n";
                ?>
                <script>
            $('#si_toggle_more').click(function() {
                  $('#si_more').slideToggle('fast', function() {
                      $('#si_toggle_more').css('display','none')
                  });
                });
            $('#si_toggle_less').click(function() {
                  $('#si_more').slideToggle('fast', function() {
                    $('#si_toggle_more').css('display','block')
                  });
                });
            $("#si_toggle_more").button();
            $("#si_toggle_less").button();
            </script>
               <?php
            }
        } else {
            print "<p class='dashboardHeadline'>Inventory looks up to date.</p>\n";
        }
        
    print "</div>\n<!--close dashboardBox -->";
    print "</div><!--close dragbox -->\n";
}

footer();
?>