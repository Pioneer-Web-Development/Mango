<?php
//<!--VERSION: .9 **||**-->

include("includes/mainmenu.php") ;

print "<body>\n";
print "<div id='wrapper'>\n";

 //make sure we have a logged in user...
if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}
global $siteID, $papertypes, $sizes;
    
if ($_POST['submit']=='Get Rolls')
{
    show_rolls();
} else {
    $statuses=array("0"=>"Any", "1"=>"Received","9"=>"Consumed","99"=>"Deleted");
    print "<form method=post>\n";
    make_select('ptype',$papertypes[0],$papertypes,'Paper type');
    make_select('rsize',$sizes[0],$sizes,'Roll size');
    make_select('status',$statuses[1],$statuses,'Status');
    make_checkbox('validated',1,'Validated','Show only validated rolls');
    make_date('back',date("Y-m-d",strtotime("-6 months")),'Received since','How far back to look for received rolls');
    print "<input type='submit' name='submit' value='Get Rolls' />\n";
    print "</form>\n";
}

function show_rolls()
{
    global $siteID, $papertypes, $sizes;
    $name=$papertypes[$_POST['ptype']];
    $size=$sizes[$_POST['rsize']];
    $date=$_POST['back'];
    if ($_POST['status']!=0)
    {
        $status="AND status=$_POST[status]";
    }
    if ($_POST['validated'])
    {
        $valid="AND validated=1";
    } else {
        $valid='';
    }
    if ($_POST['ptype']==0 || $_POST['rsize']==0){die('You must specify a type and size.');}
    $sql="SELECT * FROM rolls WHERE common_name='$name' AND roll_width='$size' AND receive_datetime>='$date' $valid $status";
    $dbRolls=dbselectmulti($sql);
    if ($dbRolls['numrows']>0)
    {
        print "<table class='grid'>\n";
        print "<tr><th colspan=9><a href='$_SERVER[PHP_SELF]'>Run another report</a></th></tr>\n";
        print "<tr><th>Vendor</th><th>Manifest</th><th>Roll Tag</th><th>Type</th><th>Width</th><th>Weight</th><th>Receive Date</th><th>Batch Process Date</th><th>Status</th></tr>\n";
        foreach($dbRolls['data'] as $roll)
        {
            print "<tr>";
            $vid=$roll['order_id'];
            $sql="SELECT B.vendor_name FROM orders A, vendors B WHERE A.id=$vid AND A.vendor_id=B.id";
            $dbVendor=dbselectsingle($sql);
            $vname=$dbVendor['data']['vendor_name'];
            print "<td>$vname</td>\n";
            print "<td><input type='text' size=20 id='rollmanifest_$roll[id]' value='$roll[manifest_number]'><input type='button' value='Change Manifest' onclick='changeRollManifest(\"manifest\",$roll[id]);'></td>";
            
            print "<td><input type='text' size=20 id='rolltag_$roll[id]' value='$roll[roll_tag]'><input type='button' value='Change Rolltag' onclick='changeRollManifest(\"tag\",$roll[id]);'></td>";
            
            print "<td>";
            print input_select('rollname_'.$roll['id'],$roll['common_name'],$papertypes);
            print "<input type='button' value='Change Paper Type' onclick='changeRollManifest(\"name\",$roll[id]);'></td>";
            
            print "<td>";
            print input_select('rollwidth_'.$roll['id'],$roll['roll_width'],$sizes);
            print "<input type='button' value='Change Width' onclick='changeRollManifest(\"width\",$roll[id]);'></td>";
            
            print "<td><input type='text' size=10 id='rollweight_$roll[id]' value='".($roll['roll_weight'])."'>kg<input type='button' value='Change Weight' onclick='changeRollManifest(\"weight\",$roll[id]);'></td>";
            
            
            print "<td>";
            print  input_date('rolldate_'.$roll['id'],$roll['receive_datetime']);
            print "<input type='button' value='Change Date' onclick='changeRollManifest(\"date\",$roll[id]);'></td>";
            
            print "<td>";
            if ($roll['batch_date']!='')
            {
                print $message.'<br />';
                print  input_date('rollbatch_'.$roll['id'],$roll['batch_date']);
                print "<input type='button' value='Change Batch Date' onclick='changeRollManifest(\"batch\",$roll[id]);'>";
            }
            print "</td>\n";
            $status=$roll['status'];
            if ($status==1)
            {
                $stat="<span id='rollstatus_$roll[id]'>Received <input type='button' value='Consume' onclick='toggleRollStatus($roll[id],9);'>";
                $stat.="<input type='button' value='Delete' onclick='toggleRollStatus($roll[id],99);'>";
                $stat.="</span>";
            }elseif($status==99)
            {
                $stat="<span id='rollstatus_$roll[id]'>Deleted ";
                $stat.="<input type='button' value='Set to received' onclick='toggleRollStatus($roll[id],1);'>";
                $stat.="</span>";
            } else {
                $stat="<span id='rollstatus_$roll[id]'>Consumed <input type='button' value='Un-consume' onclick='toggleRollStatus($roll[id],1);'>";
                $stat.="<input type='button' value='Delete' onclick='toggleRollStatus($roll[id],99);'>";
                $stat.="</span>";
            }
            print "<td>$stat</td>";
            print "</tr>\n";  
        }
        print "</table>\n";
    } else {
        print "Sorry, no rolls match that manifest number.<br /><a href='$_SERVER[PHP_SELF]'>Try a different search</a>.";
    } 
}

dbclose();
?>
</div>
</body>
</html>

