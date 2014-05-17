<?php
//<!--VERSION: .9 **||**-->
include("includes/mainmenu.php") ;

if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}
switch ($action)
{
    
    case "addbenchmark":
    benchmarks('add');
    break;
    
    case "editbenchmark":
    benchmarks('edit');
    break;
    
    case "deletebenchmark":
    benchmarks('delete');
    break;
    
    case "Save Benchmark":
    save_benchmarks('insert');
    break;
    
    case "Update Benchmark":
    save_benchmarks('update');
    break;

    default:
    benchmarks('list');
    break;
}


function benchmarks($action)
{
    $benchmark_types=array("time"=>"time","number"=>"number");
    $benchmark_categories=array("press"=>"press","pagination"=>"pagination","mailroom"=>"mailroom");
    $id=$_GET['id'];
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Benchmark";
            $displayinlist=1;
        } else {
            $button="Update Benchmark";
            $sql="SELECT * FROM benchmarks WHERE id=$id";
            $dbBenchmark=dbselectsingle($sql);
            $benchmark=$dbBenchmark['data'];
            $benchmark_name=stripslashes($benchmark['benchmark_name']);
            $benchmark_type=stripslashes($benchmark['benchmark_type']);
            $benchmark_category=stripslashes($benchmark['benchmark_category']);
            $benchmark_order=stripslashes($benchmark['benchmark_order']);
            $displayinlist=$benchmark['benchmark_displaylist'];
        }
        print "<form method=post>\n";
        make_text('benchmark_name',$benchmark_name,'Benchmark Name');
        make_text('benchmark_order',$benchmark_order,'Benchmark Order');
        make_select('benchmark_type',$benchmark_type,$benchmark_types,'Type','Type of benchmark');
        make_select('benchmark_category',$benchmark_category,$benchmark_categories,'Category','Category of benchmark');
        make_checkbox('displaylist',$displayinlist,'Display in list','Display in list of benchmarks, or separate');
        make_checkbox('starttime',$starttime,'Start Time','Check if this is the start time benchmark');
        make_checkbox('endtime',$endtime,'End Time','Check if this is the end time benchmark');
        make_submit('submit',$button);
        print "<input type='hidden' name='id' value='$id'>\n";
        print "</form>\n";
    }elseif ($action=='delete')
    {
        $sql="DELETE FROM benchmarks WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        redirect("?action=list");
    } else {
       global $siteID;
        //show all the pubs
       $sql="SELECT * FROM benchmarks WHERE site_id=$siteID ORDER BY benchmark_category, benchmark_order";
       $dbBenchmarks=dbselectmulti($sql);
       tableStart("<a href='?action=addbenchmark'>Add benchmark</a>","Benchmark,Category,Order",5);
       if ($dbBenchmarks['numrows']>0)
       {
            foreach($dbBenchmarks['data'] as $benchmark)
            {
                $id=$benchmark['id'];
                $benchmarkname=$benchmark['benchmark_name'];
                $benchmarkcategory=$benchmark['benchmark_category'];
                $benchmarkorder=$benchmark['benchmark_order'];
                print "<tr><td>$benchmarkname</td><td>$benchmarkcategory</td><td>$benchmarkorder</td>\n";
                print "<td><a href='?action=editbenchmark&id=$id'>Edit</a</td>\n";
                print "<td><a class='delete' href='?action=deletebenchmark&id=$id'>Delete</a</td>\n";
                print "</tr>\n";
            }
       }
       tableEnd($dbBenchmarks);
    }


}



function save_benchmarks($action)
{
    global $siteID;
    $id=$_POST['id'];
    $benchmarkname=addslashes($_POST['benchmark_name']);
    $benchmarkorder=addslashes($_POST['benchmark_order']);
    $benchmarktype=addslashes($_POST['benchmark_type']);
    $benchmarkcategory=addslashes($_POST['benchmark_category']);
    if ($_POST['displaylist']){$displaylist=1;}else{$displaylist=0;}
    if ($_POST['start']){$start=1;}else{$start=0;}
    if ($_POST['end']){$end=1;}else{$end=0;}
    if ($action=='insert')
    {
        $sql="INSERT INTO benchmarks (benchmark_name, benchmark_category, benchmark_type, benchmark_displaylist, benchmark_order, starter, ender, site_id)
         VALUES ('$benchmarkname', '$benchmarkcategory', '$benchmarktype', '$displaylist', '$benchmarkorder', $start, $end, $siteID)";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE benchmarks SET benchmark_displaylist=$displaylist, benchmark_name='$benchmarkname', starter=$start, ender=$end
         benchmark_category='$benchmarkcategory', benchmark_type='$benchmarktype', benchmark_order='$benchmarkorder' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the benchmark','error');
    } else {
        setUserMessage('Benchmark successfully saved','success');
    }
    redirect("?action=list");
    
}

footer();
?>

