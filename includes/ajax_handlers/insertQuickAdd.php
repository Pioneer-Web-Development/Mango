<?php
  include("../functions_db.php");
  include("../functions_common.php");
  include("../config.php");
  
  
    //here we will check to see if we have a booked insert
    //few things to check. look for publication, pubdate and advertiser along with count range +/- 2000
    global $advertisers, $pubs;
    $pubid=$_POST['pub'];
    $pubdate=$_POST['pubdate'];
    $count=$_POST['receive_count'];
    $advertiserid=$_POST['advertiser'];
    
    
    //so, first hope is to look for specific pub, pubdate and advertiser
    $sql="SELECT A.advertiser_id, A.buy_count, B.id, B.insert_id, B.pub_id, B.insert_date 
    FROM inserts A inserts_schedule B 
    WHERE A.id=B.insert_id 
    AND A.advertiser_id='$advertiserid' 
    AND A.buy_count>".($count-2000)." 
    AND A.buy_count<".($count+2000)."
    AND B.pub_id=$pubid 
    AND insert_date='$pubdate'";
    $dbFound=dbselectmulti($sql);
    if($dbFound['numrows']>0)
    {
        foreach($dbFound['data'] as $found)
        {
            print "Best match:<br>";
            print "<input type='checkbox' name='sched_$found[id]' />
            <label for='sched_$found[id]'>
            Insert for ".$advertisers[$found['id']]." inserting into ".$pubs[$found['pub_id']]." on 
            ".date("m/d/Y",strtotime($found['pub_date']))." quantity $found[buy_count]
            </label>\n";
        }
    }

  dbclose();
?>          