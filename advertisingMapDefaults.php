<?php
  include("includes/mainmenu.php");
  if($_POST)
  {
      $sql="SELECT * FROM advertising_map_defaults";
      $dbSettings=dbselectsingle($sql);
      if($dbSettings['numrows']==0){
          $sql="INSERT INTO advertising_map_defaults (map_width) VALUES ('800')";
          $dbInsert=dbinsertquery($sql);
      }
      $width=$_POST['width'];
      $height=$_POST['height'];
      $zoom=$_POST['zoom'];
      $lat=$_POST['default_lat'];
      $lon=$_POST['default_lon'];
      $sql="UPDATE advertising_map_defaults SET map_width='$width', map_height='$height', zoom_level='$zoom', 
      default_lat='$lat', default_lon='$lon'";
      $dbUpdate=dbexecutequery($sql);
      if($dbUpdate['error']=='')
      {
          setUserMessage('Map settings have been updated.','success');
      } else {
          setUserMessage('There was a problem updating the map settings.'.$dbUpdate['error'],'error');
      }
      redirect("?action=list");
  } else {
      
      $sql="SELECT * FROM advertising_map_defaults";
      $dbSettings=dbselectsingle($sql);
      $settings=$dbSettings['data'];
      print "<form method=post>\n";
      make_slider('width',$settings['map_width'],'Map Width','',200,4000,10);
      make_slider('height',$settings['map_height'],'Map Height','',200,4000,10);
      make_slider('zoom',$settings['zoom_level'],'Zoom Level','',0,24,1);
      make_text('default_lat',$settings['default_lat'],'Default Latitude','',14);
      make_text('default_lon',$settings['default_lon'],'Default Longitude','',14);
      make_submit('submit',"Save Settings");
      print "</form>\n";
  }
  
  footer();
?>
