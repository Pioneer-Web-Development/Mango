<?php
//<!--VERSION: .9 **||**-->

global $ordersources, $siteID;
if ($_POST['submit']=='Generate Report')
{
    include("includes/functions_db.php");
    include("includes/functions_common.php");
    include("includes/config.php");
    
    if ($_POST['vendor']!=0){
        $vendor="AND id=$_POST[vendor]";
    } else {
        $vendor="";
    }
    if ($_POST['ordersource']!='0'){
        unset($ordersources);
        $ordersources=array($_POST['ordersource']=>$_POST['ordersource']);
    } 
    if ($_POST['ptype']!=0){
        $ptype="WHERE id=$_POST[ptype]";
    } else {
        $ptype="";
    }
    if ($_POST['psize']!=0){
        $psize="WHERE id=$_POST[psize]";
    }
    if ($_POST['status']==0)
    { 
        $status="";
    } else {
        $status=" AND A.status=$_POST[status] ";
    }
    if ($_POST['ignoresource'])
    {
        $ignore=true;
    }
    
    //now build sql to get rolls
    print "<table class='grid'>\n";
    print "<tr><th><a href='#' onclick='window.print();'><img src='artwork/printer.png' width=32 border=0>Print</a></th><th colspan=4><p style='text-align:center;font-size:18px;font-weight:bold;'>Inventory Report</p></th><th><a href='default.php'>Return to system</a> | <a href='report-newsprintInventory.php'>Run another report</a></th></tr>\n";
    print "<tr><th>Vendor</th>";
    if (!$ignore)
    {
        print "<th>Order Source</th>";    
    }
    print "<th>Common Name</th><th>Size</th><th>Rolls in Inventory</th><th>Actual</th></tr>\n";
    
    //we'll do a series of loops, from vendor down to rolls
    $sql="SELECT * FROM accounts WHERE site_id=$siteID AND newsprint=1 $vendor ORDER BY account_name";
    $dbVendors=dbselectmulti($sql);
    if ($dbVendors['numrows']>0)
    {
        foreach($dbVendors['data'] as $vendor)
        {
            $vendorid=$vendor['id'];
            $vendorname=$vendor['account_name'];
                //now loop through paper types
                    $sql="SELECT * FROM paper_types $ptype WHERE status<>99 ORDER BY common_name";
                    $dbPaperTypes=dbselectmulti($sql);
                    if ($dbPaperTypes['numrows']>0)
                    {
                        foreach($dbPaperTypes['data'] as $papertype)
                        {
                            $commonname=$papertype['common_name'];
                            //now we need sizes
                            $sql="SELECT * FROM paper_sizes $psize WHERE status<>99 ORDER BY width";
                            $dbSizes=dbselectmulti($sql);
                            if ($dbSizes['numrows']>0)
                            {
                                foreach ($dbSizes['data'] as $rollsize)
                                {
                                    $rollwidth=$rollsize['width'];
                                    //now we are finally at the roll level
                                    //now we're going to loop based on order source
                                    if (!$ignore)
                                    {
                                        foreach($ordersources as $source=>$ordersource)
                                        {
                                            if ($ordersource!='Please choose')
                                            {
                                                $sql="SELECT count(A.id) as rollcount FROM rolls A, orders B WHERE A.site_id=$siteID AND A.order_id=B.id AND A.roll_width='$rollwidth' AND A.common_name='$commonname' AND B.order_source='$ordersource' AND B.vendor_id=$vendorid AND A.status=1";
                                                $dbRollCount=dbselectsingle($sql);
                                                $rollcount=$dbRollCount['data']['rollcount'];
                                                if ($rollcount>0)
                                                {
                                                    print "<tr><td>$vendorname</td><td>$ordersource</td><td>$commonname</td><td>$rollwidth</td><td style='text-align:right;'>$rollcount</td><td>__________</td></tr>\n";    
                                                    $totalrolls+=$rollcount;
                                                }
                                            }
                                        }
                                    } else {
                                        $sql="SELECT count(A.id) as rollcount FROM rolls A, orders B WHERE A.site_id=$siteID AND A.order_id=B.id AND A.roll_width='$rollwidth' AND A.common_name='$commonname' AND B.vendor_id=$vendorid AND A.status=1";
                                        $dbRollCount=dbselectsingle($sql);
                                        $rollcount=$dbRollCount['data']['rollcount'];
                                        //$ordersource=$dbRollCount['data']['ordersource'];
                                        if ($rollcount>0)
                                        {
                                            print "<tr><td>$vendorname</td><td>$commonname</td><td>$rollwidth</td><td style='text-align:right;'>$rollcount</td><td>__________</td></tr>\n";    
                                            $totalrolls+=$rollcount;
                                        }
                                    }        
                                    
                                }
                            } else {
                                print "Sorry, there are no roll sizes configured!<br />\n";
                            }
                            
                            
                        }
                    } else {
                        print "Oops, there are no paper types set up.<br />";
                    }
           
            
        }
        print "<tr><td colspan=6>Total rolls showing in inventory $totalrolls</td></tr>\n";
    } else {
        print "Stopping here because there are no vendors set up.<br />";
    }
    print "</table>\n";
    
} else {
    global $siteID, $newsprintVendors;
    include("includes/mainmenu.php") ;
    $scriptpath='http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'] ;
    if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}

    print "<div id='wrapper'>";//build vendor list
    
    //paper types
    $sql="SELECT * FROM paper_types ORDER BY common_name";
    $dbPaper=dbselectmulti($sql);
    $papertypes=array();
    $papertypes[0]="Type";
    if ($dbPaper['numrows']>0)
    {
        foreach($dbPaper['data'] as $paper)
        {
            $papertypes[$paper['id']]=$paper['common_name'];
        }
    }

    //paper sizes
    $sql="SELECT * FROM paper_sizes ORDER BY width ASC";
    $dbSizes=dbselectmulti($sql);
    $papersizes=array();
    $papersizes[0]="Size";
    if ($dbSizes['numrows']>0)
    {
        foreach($dbSizes['data'] as $size)
        {
            $papersizes[$size['id']]=$size['width'];
        }
    }
    
   print "<form method=post>\n";
        print "<div id='search' style='padding-left:20px;'>Vendor: \n";
            print input_select('vendor',$newsprintVendors[0],$newsprintVendors)."<br>\n";
            print "Order Source: ";
            print input_select('ordersource',$ordersources[0],$ordersources)."<br>\n";
            print "Paper type: ";
            print input_select('ptype',$papertypes[0],$papertypes)."<br>\n";
            print "Paper size: ";
            print input_select('psize',$papersizes[0],$papersizes)."<br>\n";
            print "Disregard order source: ";
            print input_checkbox('ignoresource',1)."<br />\n";
            print "<div style='float:left;margin-left:20px;'><input type='submit' name='submit' value='Generate Report' /></div>\n";
            print "<div style='clear:both;'></div>\n";
        print "</div>\n";
    print "</form>\n";
    
}



    


dbclose();
?>