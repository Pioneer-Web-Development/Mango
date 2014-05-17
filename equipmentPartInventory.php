<?php
include("includes/mainmenu.php") ;


    global $departments;
    print "Filter parts by department: ".input_select('deptid',$departments[$_GET['deptid']],$departments,false,"window.location='?deptid='+this.value;");
    if($_GET['deptid']!=0)
    {
        $deptid=$_GET['deptid'];
        $dep="AND department_id=$deptid";
    }
    $sql="SELECT * FROM equipment_part WHERE site_id=$siteID $dep ORDER BY part_name ASC";
    $dbParts=dbselectmulti($sql);
    tableStart("<a href='equipmentParts.php?action=add&type=generic'>Add new generic part</a>","Part ID,Part Name,Current Inventory",6);
    if ($dbParts['numrows']>0)
    {
        foreach($dbParts['data'] as $part)
        {
            $partname=$part['part_name'];
            $id=$part['id'];
            $invcount=$part['part_inventory_quantity'];
            $reorder=$part['part_reorder_quantity'];
            if ($invcount==0)
            {
                $invcount="$invcount <span style='color:red;font-weight:bold'>Alert, you are out of this part!</span>\n";
              } elseif ($invcount<=$reorder)
              {
                  $invcount= "$invcount <span style='color:red;font-weight:bold'>Alert, you need to reorder!</span>\n";
              }
            print "<tr><td>$id</td><td><a href='#' onclick=\"window.open('equipmentPartPopup.php?partid=$id','Part Viewer','width=600,height=650,toolbar=no,status=no,location=no,scrollbars=no');return false;\">$partname</a></td>";
            print "<td><span id='invcount_$id'>$invcount</span></td>";
            print "<td><img src='artwork/icons/subtract_48.png' border=0 width=24 onClick='changePartInventory(\"sub\",$id);'></td>";
            print "<td><img src='artwork/icons/add_48.png' border=0 width=24 onClick='changePartInventory(\"add\",$id);'></td>";
            print "<td><a href='#' onclick=\"window.open('equipmentPartPopup.php?partid=$id','Part Viewer','width=600,height=650,toolbar=no,status=no,location=no,scrollbars=no');return false;\"><img src='artwork/icons/binocular.png' border=0 width=24></a></td>";
            print "</tr>\n";
        }
    }
    tableEnd($dbParts);


footer();