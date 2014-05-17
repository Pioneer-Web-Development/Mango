<?php
include("includes/mainmenu.php") ;

if ($_POST['submit']=='Add'){
   save_rack('insert');
} elseif ($_POST['submit']=='Update'){
   save_rack('update'); 
} else { 
    racks();
}

function racks() {
  global $states;
  $icons=array('pay'=>"Pay Location",'free'=>"Free Rack");
  
  if ($_GET['action']=='add' || $_GET['action']=='edit'){
      if ($_GET['action']=='add'){
          $button="Add";
          $lat=0;
          $lon=0;
          $icon='pay';
      } else {
        $id=$_GET['id'];
        $sql="SELECT * FROM circ_racks WHERE id=$id";
        $dbresult=dbselectsingle($sql);
        $record=$dbresult['data'];
        $name=stripslashes($record['location_name']);
        $info=stripslashes($record['info']);
        $street=stripslashes($record['street']);
        $city=stripslashes($record['city']);
        $state=stripslashes($record['state']);
        $zip=stripslashes($record['zip']);
        $icon=stripslashes($record['icon']);
        $lat=stripslashes($record['lat']);
        $lon=stripslashes($record['lon']);
        $button="Update";
      }
        print "<form method=post>\n";
        make_select('icon',$icons[$icon],$icons,'Rack Type');
        make_text('name',$name,'Location Name','',50);
        make_text('street',$street,'Street Address');
        make_text('city',$city,'City');
        make_select('state',$states[$state],$states,'State');
        make_text('zip',$zip,'Zip');
        make_text('lat',$lat,'Lat','Set to zero to have it geo-coded');
        make_text('lon',$lon,'Lon');
        make_textarea('info',$info,'Information about location','This will be displayed in the popup bubble');
        make_submit('submit',$button);
        make_hidden('id',$id);  
        print "</form>\n";
  } elseif ($_GET['action']=='delete'){
    $sql='DELETE FROM circ_racks where id='.intval($_GET['id']);
    $result=dbexecutequery($sql);
    redirect('?action=list');
     
  } else {
    $sql="SELECT * FROM circ_racks ORDER BY location_name";
    $dbresult=dbselectmulti($sql);
    tableStart("<a href='?action=add'>Add new rack</a>","Location Name,Address",4);
    if ($dbresult['numrows']>0){
        foreach ($dbresult['data'] as $record) {
            $name=stripslashes($record['location_name']);
            $address=$record['street']."<br />".$record['city'].' '.$record['state'];
            print "<tr><td>$name</td><td>$address</td>\n";
            print "<td><a href='?action=edit&id=$record[id]'>Edit</a></td>";
            print "<td><a href='?action=delete&id=$record[id]' class='delelte'>Delete</a></td>";
            print "</tr>\n";       
        }
    }
    tableEnd($dbresult);
  }
    
    
}


function save_rack($action) {
    $id=$_POST['id'];
    $name=addslashes($_POST['name']);
    $info=addslashes($_POST['info']);
    $street=addslashes($_POST['street']);
    $city=addslashes($_POST['city']);
    $state=addslashes($_POST['state']);
    $zip=addslashes($_POST['zip']);
    $icon=$_POST['icon'];
    
    if($_POST['lat']==0 || $_POST['lat']=='')
    {
    $map=geocode("$street, $city $state $zip");
    $lat=$map['lat'];
    $lon=$map['lon'];
    } else {
        $lat=$_POST['lat'];
        $lon=$_POST['lon'];
    } 
 
 
     if ($action=='insert'){
       $sql="INSERT INTO circ_racks (location_name, street, city, state, zip, info, lat, lon, icon) VALUES ('$name', '$street', '$city', 
       '$state', '$zip', '$info', '$lat', '$lon', '$icon')";
       $dbresult=dbinsertquery($sql);
       $id=$dbresult['insertid']; 
       $error=$dbresult['error']; 
     } else {
       $sql="UPDATE circ_racks SET location_name='$name', street='$street', city='$city', state='$state', zip='$zip', lat='$lat', 
        lon='$lon', info='$info', icon='$icon' WHERE id=$id";
       $dbresult=dbexecutequery($sql);
       $error=$dbresult['error']; 
     } 
     if ($error!='')
    {
        setUserMessage('There was a problem saving the rack location.<br>'.$error,'error');
    } else {
        setUserMessage('The rack location has been successfully saved','success');
    }
    redirect('?action=list');
    
}

footer();
?>
