<?php
  //this script looks up roll tags and replies with either the roll id or 0.
  include("functions_db.php");
  $rolltag=$_GET['rolltag'];
  //get all the newsprint vendors
  $sql="SELECT * FROM vendors WHERE newsprint=1";
  $dbVendors=dbselectmulti($sql);
  if ($dbVendors['numrows']>0)
  {
      $rollid=0;
      foreach($dbVendors['data'] as $vendor)
      { 
          
          $vendorid=$vendor['id'];
          $rollremoval=$vendor['rolltag_removal'];
          //ok, what we are going to have to do is check the rolls after massaging the rolltag for each vendor
          $checktag=substr($rolltag,$rollremoval);//this should do it
          $sql="SELECT * FROM rolls WHERE roll_tag='$checktag'";
          $dbRoll=dbselectsingle($sql);
          if ($dbRoll['numrows']>0)
          {
            $rollid=$dbRoll['data']['id'];
          }
      }
      if ($rollid==0)
      {
          //do one last check without removing anything from the rolltag
          $sql="SELECT id, manifest_number FROM rolls WHERE roll_tag='$rolltag'";
          $dbRoll=dbselectsingle($sql);
          if ($dbRoll['numrows']>0)
          {
            $rollid=$dbRoll['data']['id'];
          } 
      }
      if ($rollid<>0)
      {
        print $rollid.'|'.$vendorid;
      } else {
        print "0";
      }
  } else {
      print "0";
  }
?>
