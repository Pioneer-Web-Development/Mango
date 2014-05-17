<?php
//<!--VERSION: .9 **||**-->

if($_GET['action']=='downloadfile')
{
    include("includes/functions_db.php");
    include("includes/config.php");
    downloadDeviceFile();
} else if($_GET['action']=='fulldetailscsv')
{
    include("includes/functions_db.php");
    include("includes/config.php");
    if($_GET['output']=='wiki')
    {
        deviceDetailWiki();
    } else {
        deviceDetailCSV();
    }
} else {
    include("includes/mainmenu.php") ;

    if ($_POST['submit'])
    {
        $action=$_POST['submit'];
    } elseif ($_GET['action'])
    {
        $action=$_GET['action'];
    } else {
        $action='list';
    }
    
    switch ($action)
    {
        case "add":
        device('add');
        break;
        
        case "edit":
        device('edit');
        break;
        
        case "delete":
        device('delete');
        break;
        
        case "list":
        device('list');
        break;
        
        case "Add":
        save_device('insert');
        break;
        
        case "Update":
        save_device('update');
        break;
        
        case "addsoftware":
        software('add');
        break;
        
        case "editsoftware":
        software('edit');
        break;
        
        case "deletesoftware":
        software('delete');
        break;
        
        case "listsoftware":
        software('list');
        break;
        
        case "Add Software":
        save_software('insert');
        break;
        
        case "Update Software":
        save_software('update');
        break;
        
        case "addfile":
        files('add');
        break;
        
        case "editfile":
        files('edit');
        break;
        
        case "deletefile":
        files('delete');
        break;
        
        case "listfiles":
        files('list');
        break;
        
        case "Add File":
        save_file('insert');
        break;
        
        case "Update File":
        save_file('update');
        break;
        
        case "addpart":
        parts('add');
        break;
        
        case "editpart":
        parts('edit');
        break;
        
        case "deletepart":
        parts('delete');
        break;
        
        case "listparts":
        parts('list');
        break;
        
        case "Add Part":
        save_parts('insert');
        break;
        
        case "Update Part":
        save_parts('update');
        break;
    }

}
 
function device($action)
{
    $staff=array();
    $staff[0]='Unassigned';
    
    $sql="SELECT id, firstname, lastname FROM users ORDER BY lastname, firstname";
    $dbStaff=dbselectmulti($sql);
    if ($dbStaff['numrows']>0)
    {
        foreach($dbStaff['data'] as $s)
        {
            $staff[$s['id']]=$s['lastname'].', '.$s['firstname'];
        }
    }
    $ipclasses=array();
    $ipclasses[0]='No IP';
    
    $sql="SELECT id, ip_name, ip_lower, ip_upper FROM it_ip_classes ORDER by ip_name";
    $dbC=dbselectmulti($sql);
    if ($dbC['numrows']>0)
    {
        foreach($dbC['data'] as $s)
        {
            $ipclasses[$s['id']]=$s['ip_name'].' - '.$s['ip_lower'].' to '.$s['ip_upper'];
        }
    }
    global $itDevices, $oses;
    if($itDevices!='')
    {
        $itDevices=explode("\n",$itDevices);
        foreach($itDevices as $key=>$value)
        {
            $value=explode("|",$value);
            $dtypes[$value[0]]=$value[1];
        }
    }
    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add";
            $dtype='generic';
            $ipclass=0;
            $assigned=0;
            $os=0;
        } else {
            $id=intval($_GET['id']);
            $sql="SELECT * FROM it_devices WHERE id=$id";
            $dbDevice=dbselectsingle($sql);
            $device=$dbDevice['data'];
            $name=stripslashes($device['device_name']);
            $ipclass=stripslashes($device['device_ip_class']);
            $ip=stripslashes($device['device_ip']);
            $mfg=stripslashes($device['device_mfg']);
            $serial=stripslashes($device['device_serial']);
            $assigned=stripslashes($device['assigned_to']);
            $specs=stripslashes($device['device_specs']);
            $notes=stripslashes($device['device_notes']);
            $location=stripslashes($device['device_location']);
            $dtype=stripslashes($device['device_type']);
            $admin=stripslashes($device['device_admin']);
            $password=stripslashes($device['device_password']);
            $image=stripslashes($device['device_image']);
            $os=stripslashes($device['device_os']);
            $button="Update";
        }
        print "<div style='float:left;width:700px'>\n";
        print "<form method=post enctype='multipart/form-data'>\n";
        make_text('name',$name,'Device Name','',50);
        make_select('dtype',$dtypes[$dtype],$dtypes,'Type of device','Select the type of device');
        make_select('os',$oses[$os],$oses,'OS','Select the OS for this device (if it runs one)');
        make_select('ipclass',$ipclasses[$ipclass],$ipclasses,'IP Class','Select the IP class assigned to this device');
        make_text('ip',$ip,'IP Address','Enter the IP address, if it has one',50);
        make_select('assigned',$staff[$assigned],$staff,'Assign to','Person this device is assigned to');
        make_text('mfg',$mfg,'Manufacturer','Manufacturer of the device',50);
        make_text('serial',$serial,'Serial number','Serial Number of device',50);
        make_text('admin',$admin,'Admin account','Enter the username of the admin account for device (if it has one)',50);
        make_text('password',$password,'Admin password','Enter the password of the admin account for device (if it has one)',50);
        make_textarea('specs',$specs,'Specification','What are the specification for this device?',50,5,false);
        make_textarea('notes',$notes,'Notes','Notes or other serials/addresses for this device',50,5,false);
        make_textarea('location',$location,'Location','Where is this device located?',50,5,false);
        make_file('image','Image','Image of device (show area around it)','artwork/itdevices/'.$image);
        make_checkbox('removeImage',0,'Remove','Remove the image above from this device');
        make_hidden('id',$id);
        make_submit('submit',$button);
        print "</form>\n";
        print "</div>\n";
        print "<div style='float:left;width:520px;margin-left:10px;'>\n";
        if($image!='')
        {
            print "<img src='artwork/itdevices/$image' border=0 width=500 />\n";
        }
        print "</div>\n";
    } elseif($action=='delete')
    {
        $classid=intval($_GET['id']);
        $sql="DELETE FROM it_devices WHERE id=$classid";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the device.<br>'.$error,'error');
        } else {
            setUserMessage('Device has been successfully deleted.','success');
        }
        redirect("?action=list");
    } else {
        if($_POST)
        {
           if($_POST['devicetype']!='0')
           {
               $devicetype="WHERE device_type='$_POST[devicetype]' ";
               $dtype=$_POST['devicetype'];
           }   else {
                $devicetype="";
                $dtype=0;
           }         
           if($_POST['deviceserial']!='')
           {
               if($devicetype!='')
               {
                   $deviceserial="OR device_serial LIKE '%".addslashes($_POST['deviceserial'])."%' ";
               } else {
                   $deviceserial="WHERE device_serial LIKE '%".addslashes($_POST['deviceserial'])."%' ";
               }
           } else {
               $deviceserial='';
           }
        } else {
            $devicetype="";
            $deviceserial="";
            $dtype=0;
        }
        $dtypes[0]='All devices';
        $search="<form method=post>\nDevice Type:<br>";
        $search.=input_select('devicetype',$dtypes[$dtype],$dtypes);
        $search.="<br />Serial #<br />".input_text('deviceserial',$_POST['deviceserial'],20);
        $search.="<br><input type='submit' value='Search'>\n";
        $search.="</form>\n";
        $sql="SELECT * FROM it_devices $devicetype $deviceserial ORDER BY device_name";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new device</a>,<br /><a href='?action=fulldetailscsv'>Download All Devices</a>,<a href='?action=fulldetailscsv&sub=desktoppd'>Download Desktop PCs</a>,<a href='?action=fulldetailscsv&sub=desktopmac'>Download Macs</a>,<a href='?action=fulldetailscsv&sub=laptoppc'>Download Laptops</a>,<a href='?action=fulldetailscsv&sub=mobile'>Download Mobile Devices</a>,<a href='?action=fulldetailscsv&output=wiki'>Download All for Wiki</a>,<hr>Search:<br>$search","Device Name,Type,IP Address,Assigned to",9);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $id=$group['id'];
                $type=$dtypes[$group['device_type']];
                $name=$group['device_name'];
                $ip=$group['device_ip'];
                //see if it has an owner
                if($group['assigned_to']!=0)
                {
                    $userid=$group['assigned_to'];
                    $username=$staff[$userid];
                } else {
                    $username='system';
                }
                print "<tr>";
                print "<td>$name</td>";
                print "<td>$type</td>";
                print "<td>$ip</td>";
                print "<td>$username</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>";
                print "<td><a href='?action=listsoftware&deviceid=$id'>Software</a></td>";
                print "<td><a href='?action=listfiles&deviceid=$id'>Files</a></td>";
                print "<td><a href='?action=listparts&deviceid=$id'>Parts</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbGroups,'',0,'asc','true');
        
    }
} 

function save_device($action)
{
    $id=$_POST['id'];
    $device=$dbDevice['data'];
    $name=addslashes($_POST['name']);
    $ipclass=addslashes($_POST['ipclass']);
    $ip=addslashes($_POST['ip']);
    $mfg=addslashes($_POST['mfg']);
    $serial=addslashes($_POST['serial']);
    $assigned=addslashes($_POST['assigned']);
    $specs=addslashes($_POST['specs']);
    $notes=addslashes($_POST['notes']);
    $location=addslashes($_POST['location']);
    $dtype=addslashes($_POST['dtype']);
    $admin=addslashes($_POST['admin']);
    $os=addslashes($_POST['os']);
    $password=addslashes($_POST['password']);
    
    if($_POST['removeImage'] && $id!=0)
    {
        $sql="SELECT device_image FROM it_devices WHERE id=$id";
        $dbimage=dbselectsingle($sql);
        $image=$dbimage['data']['device_image'];
        unlink('artwork/itdevices/'.$image);
    }
    
    if($action=='insert')
    {
        $sql="INSERT INTO it_devices (device_name, device_ip_class, device_ip, device_mfg, device_serial, assigned_to, device_specs, device_notes, device_location, device_type,
device_admin, device_password, device_os) VALUES  ('$name', '$ipclass', '$ip', '$mfg', '$serial', '$assigned', '$specs', '$notes', '$location', '$dtype', '$admin', '$password', '$os')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $id=$dbInsert['insertid'];
    } else {
        $sql="UPDATE it_devices SET device_name='$name', device_ip_class='$ipclass', device_ip='$ip', device_mfg='$mfg', device_serial='$serial', assigned_to='$assigned', device_os='$os',  
        device_specs='$specs', device_notes='$notes', device_location='$location', 
        device_type='$dtype', device_admin='$admin', device_password='$password' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
     
   if(isset($_FILES)) { //means we have browsed for a valid file
        // check to make sure files were uploaded
        foreach($_FILES as $file) {
            switch($file['error']) {
                case 0: // file found
                if($file['name'] != NULL && okFileType($file['type'],'image',$file['name']) != false)  {
                    //get the new name of the file
                    //to do that, we need to push it into the database, and return the last record ID
                    if ($id!=0) {
                        $ext=end(explode(".",$file['name']));
                        $filename='devImage_'.$id.'.'.$ext;
                        //check for folder, if not present, create it
                        if(!file_exists("artwork/itdevices/"))
                        {
                            mkdir("artwork/itdevices/");
                        }
                        
                        if(processFile($file,"artwork/itdevices/",$filename) == true) {
                            $sql="UPDATE it_devices SET device_image='$filename' WHERE id=$id";
                            $result=dbexecutequery($sql);
                            $error.=$result['error'];
                        } else {
                           $error.= 'There was an error processing the file: '.$file['name'];  
                        }
                    } else {
                        $error.= 'There was an error because the main record insertion failed.';
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
        setUserMessage('There was a problem saving the device.<br>'.$error,'error');
    } else {
        setUserMessage('Device has been successfully saved.','success');
    }
    redirect("?action=list");
    
} 
 
function files($action)
{
    $deviceid=intval($_GET['deviceid']);
                  
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add File";
        } else {
            $id=intval($_GET['id']);
            $sql="SELECT * FROM it_device_files WHERE id=$id";
            $dbDevice=dbselectsingle($sql);
            $device=$dbDevice['data'];
            $name=stripslashes($device['name']);
            $notes=stripslashes($device['notes']);
            $filename=stripslashes($device['filename']);
            $button="Update File";
        }
        print "<form method=post enctype='multipart/form-data'>\n";
        make_text('name',$name,'Name','Descriptive name for this file');
        make_file('file','File','File (PDF, image, zip, etc)','artwork/itfiles/'.$filename);
        make_textarea('notes',$notes,'Notes','Any notes about this file?',50,5,false);
        make_hidden('id',$id);
        make_hidden('deviceid',$deviceid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="SELECT * FROM it_device_files WHERE id=$id";
        $dbFile=dbselectsingle($sql);
        $filename=stripslashes($dbFile['data']['filename']);
        if(unlink("artwork/itfiles/".$id.'/'.$filename))
        {
            $sql="DELETE FROM it_device_files WHERE id=$id";
            $dbDelete=dbexecutequery($sql);
            $error=$dbDelete['error'];
            if ($error!='')
            {
                setUserMessage('There was a problem deleting the file.<br>'.$error,'error');
            } else {
                setUserMessage('File has been successfully deleted.','success');
            }
        } else {
           setUserMessage('Unable to remove the file from the server. The record was left unchanged.','error');
        }
        redirect("?action=listfiles&deviceid=$deviceid");
    } else {
        $sql="SELECT * FROM it_device_files WHERE device_id=$deviceid ORDER BY post_date";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=addfile&deviceid=$deviceid'>Add new file</a>,<a href='?action=list'>Return to device list</a>","Filename",5);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $id=$group['id'];
                $name=stripslashes($group['name']);
                $filename=stripslashes($group['filename']);
                print "<tr>";
                print "<td>$name</td>";
                print "<td>$filename</td>";
                print "<td><a href='?action=editfile&deviceid=$deviceid&id=$id'>Edit</a></td>";
                print "<td><a href='?action=deletefile&deviceid=$deviceid&id=$id' class='delete'>Delete</a></td>";
                print "<td><a href='?action=downloadfile&deviceid=$deviceid&id=$id'>Download</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbGroups);
        
    }
} 

function save_file($action)
{
    $id=$_POST['id'];
    $deviceid=$_POST['deviceid'];
    $notes=addslashes($_POST['notes']);
    $name=addslashes($_POST['name']);
    $posted=date("Y-m-d H:i");
    if($action=='insert')
    {
        $sql="INSERT INTO it_device_files (name, device_id, notes, post_date) VALUES  ('$name', '$deviceid', '$notes', '$posted')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        $id=$dbInsert['insertid'];
    } else {
        $sql="UPDATE it_device_files SET name='$name', notes='$notes' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
     
   if(isset($_FILES)) { //means we have browsed for a valid file
        // check to make sure files were uploaded
        foreach($_FILES as $file) {
            switch($file['error']) {
                case 0: // file found
                    if ($id!=0) {
                        $filename=$file['name'];
                        //check for folder, if not present, create it
                        if(!file_exists("artwork/itfiles/"))
                        {
                            mkdir("artwork/itfiles/");
                        }
                        if(!file_exists("artwork/itfiles/".$deviceid))
                        {
                            mkdir("artwork/itfiles/".$deviceid);
                        }
                        $filetype=$file['type'];
                        if(processFile($file,"artwork/itfiles/".$deviceid.'/',$filename) == true) {
                            $filename=addslashes($filename);
                            $sql="UPDATE it_device_files SET filename='$filename', ftype='$filetype' WHERE id=$id";
                            $result=dbexecutequery($sql);
                            $error.=$result['error'];
                        } else {
                           $error.= 'There was an error processing the file: '.$file['name'];  
                        }
                    } else {
                        $error.= 'There was an error because the main record insertion failed.';
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
        setUserMessage('There was a problem saving the file.<br>'.$error,'error');
    } else {
        setUserMessage('The file has been successfully saved.','success');
    }
    redirect("?action=listfiles&deviceid=$deviceid");
    
}  


function software($action)
{
    $software=array();
    $software[0]='Choose software package';
    $deviceid=intval($_GET['deviceid']);
            
    $sql="SELECT id, software_name, software_platform, software_version FROM it_software ORDER BY software_name";
    $dbC=dbselectmulti($sql);
    if ($dbC['numrows']>0)
    {
        foreach($dbC['data'] as $s)
        {
            $software[$s['id']]=$s['software_name'].' v.'.$s['software_version'].' for '.$s['software_platform'];
        }
    }
    $software[999999]='Generic Package';
                    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add Software";
            $dtype='generic';
            $ipclass=0;
            $assigned=0;
            $softwareid=0;
            $check='none';
        } else {
            $id=intval($_GET['id']);
            $sql="SELECT * FROM it_device_software WHERE id=$id";
            $dbDevice=dbselectsingle($sql);
            $device=$dbDevice['data'];
            $softwareid=stripslashes($device['software_id']);
            $serial=stripslashes($device['serial_number']);
            $activation=stripslashes($device['activation_key']);
            $name=stripslashes($device['software_name']);
            $notes=stripslashes($device['software_notes']);
            $button="Update Software";
            if($softwareid=='999999'){$check='block';}else{$check='none';}
        }
        print "<form method=post>\n";
        make_select('software_id',$software[$softwareid],$software,'Software package','Select the software package','','',"if(this.value=='999999'){\$('#software_name').show();}else{\$('#software_name').hide();}");
        print "<div id='software_name' style='display:$check;'><div class='label'>Software Name</div><div class='input'>
        <small>Enter name of software. This should be used for one-off applications.</small><br>
        <input type='text' id='name' name='name' value='$name' size=50 /></div><div class='clear'></div></div>\n";
        make_text('serial',$serial,'Serial number','Serial Number of device',50);
        make_text('activation',$activation,'Activation key','Activation key (if needed)',50);
        make_textarea('notes',$notes,'Notes','Notes or other serials/addresses for this device',50,5,false);
        make_hidden('id',$id);
        make_hidden('deviceid',$deviceid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="DELETE FROM it_device_software WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the software.<br>'.$error,'error');
        } else {
            setUserMessage('Software has been successfully deleted.','success');
        }
        redirect("?action=listsoftware&deviceid=$deviceid");
    } else {
        $sql="SELECT * FROM it_device_software WHERE device_id=$deviceid ORDER BY software_id";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=addsoftware&deviceid=$deviceid'>Add new software package</a>,<a href='?action=list'>Return to device list</a>","Software Package,Serial Number",4);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $id=$group['id'];
                if($group['software_id']=='999999')
                {
                    $softwarename=stripslashes($group['software_name']);
                } else {
                    $softwarename=$software[$group['software_id']];
                }
                $serial=$group['serial_number'];
                print "<tr>";
                print "<td>$softwarename</td>";
                print "<td>$serial</td>";
                print "<td><a href='?action=editsoftware&id=$id&deviceid=$deviceid'>Edit</a></td>";
                print "<td><a href='?action=deletesoftware&id=$id&deviceid=$deviceid' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbGroups);
        
    }
} 

function save_software($action)
{
    $id=$_POST['id'];
    $deviceid=$_POST['deviceid'];
    $softwareid=$_POST['software_id'];
    $serial=addslashes($_POST['serial']);
    $activation=addslashes($_POST['activation']);
    $notes=addslashes($_POST['notes']);
    $name=addslashes($_POST['name']);
    if($action=='insert')
    {
        $sql="INSERT INTO it_device_software (software_id,device_id, serial_number, activation_key,
        software_notes, software_name) VALUES ('$softwareid', '$deviceid', '$serial', '$activation','$notes', '$name')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE it_device_software SET software_id='$softwareid', software_name='$name',  
        serial_number='$serial', activation_key='$activation', software_notes='$notes' 
        WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the software.<br>'.$error,'error');
    } else {
        setUserMessage('Software has been successfully saved.','success');
    }
    redirect("?action=listsoftware&deviceid=$deviceid");
    
}

function parts($action)
{
    $parts=array();
    $parts[0]='Choose software package';
    $deviceid=intval($_GET['deviceid']);
            
    $sql="SELECT * FROM equipment_part ORDER BY part_name";
    $dbC=dbselectmulti($sql);
    if ($dbC['numrows']>0)
    {
        foreach($dbC['data'] as $s)
        {
            $parts[$s['id']]=$s['part_name'];
        }
    }
                    
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Add Part";
            $id=0;
        } else {
            $id=intval($_GET['id']);
            $sql="SELECT * FROM equipment_part WHERE id=$id";
            $dbPart=dbselectsingle($sql);
            $part=$dbPart['data'];
            $notes=stripslashes($part['part_notes']);
            $button="Update Software";
        }
        print "<form method=post>\n";
        make_select('partid',$parts[$id],$parts,'Part','Select a part');
        make_textarea('notes',$notes,'Notes','Notes about this part.',50,5,false);
        make_hidden('id',$id);
        make_hidden('deviceid',$deviceid);
        make_submit('submit',$button);
        print "</form>\n";
    } elseif($action=='delete')
    {
        $id=intval($_GET['id']);
        $sql="DELETE FROM it_devices_parts_xref WHERE id=$id";
        $dbDelete=dbexecutequery($sql);
        $error=$dbDelete['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the part.<br>'.$error,'error');
        } else {
            setUserMessage('The part has been successfully deleted','success');
        }
        redirect("?action=listparts&deviceid=$deviceid");
    } else {
        $sql="SELECT A.* FROM equipment_part A, it_devices_parts_xref B WHERE B.part_id=A.id AND B.device_id=$deviceid ORDER BY A.part_name";
        $dbGroups=dbselectmulti($sql);
        tableStart("<a href='?action=addpart&deviceid=$deviceid'>Add new part</a>,<a href='?action=list'>Return to device list</a>","Part",3);
        if ($dbGroups['numrows']>0)
        {
            foreach($dbGroups['data'] as $group)
            {
                $id=$group['id'];
                $softwarename=$group['part_name'];
                print "<tr>";
                print "<td>$softwarename</td>";
                print "<td><a href='?action=editpart&id=$id&deviceid=$deviceid'>Edit</a></td>";
                print "<td><a href='?action=deletepart&id=$id&deviceid=$deviceid' class='delete'>Delete</a></td>";
                print "</tr>\n";
            }
            
        }
        tableEnd($dbGroups);
        
    }
} 

function save_parts($action)
{
    $id=$_POST['id'];
    $deviceid=$_POST['deviceid'];
    $partid=$_POST['partid'];
    $notes=addslashes($_POST['notes']);
    
    $sql="DELETE FROM it_devices_parts_xref WHERE device_id=$deviceid AND part_id=$partid";
    $dbDelete=dbexecutequery($sql);
    
    
    if($action=='insert')
    {
        $sql="INSERT INTO it_devices_parts_xref (device_id, part_id) VALUES ('$deviceid', '$partid')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
        //update the notes for the part
        $sql="UPDATE equipment_part SET part_notes='$notes' WHERE id=$partid";
        $dbUpdate=dbexecutequery($sql);
        $error.=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the part.<br>'.$error,'error');
    } else {
        setUserMessage('Part has been successfully saved.','success');
    }
    redirect("?action=listparts&deviceid=$deviceid");
    
}  


function downloadDeviceFile()
{
    $id=intval($_GET['id']);
    $sql="SELECT * FROM it_device_files WHERE id=$id";
    $dbFile=dbselectsingle($sql);
    $type=$dbFile['data']['ftype'];
    $deviceid=$dbFile['data']['device_id'];
    $filename='artwork/itfiles/'.$deviceid.'/'.stripslashes($dbFile['data']['filename']);
    header('Content-Type: '.$type); // as original file
    header('Content-Disposition: attachment; filename="'.$dbFile['data']['filename'].'"');
    readfile ($filename); 
    dbclose();
    die();
}


function deviceDetailCSV()
{
    global $oses;
    
    $sql="SELECT id,firstname,lastname,email,extension FROM users";
    $dbPeople=dbselectmulti($sql);
    $people[0]='Not assigned to a person';
    if($dbPeople['numrows']>0)
    {
        foreach($dbPeople['data'] as $person)
        {
            $people[$person['id']]=stripslashes($person['firstname'].' '.$person['lastname'].' - '.$person['email'].' - '.$person['extension']);
        }
    }
    
    
    if($_GET['sub'])
    {
        $sql="SELECT * FROM it_devices WHERE device_type='".addslashes($_GET['sub'])."' ORDER BY device_name";
        $sub="-".$_GET['sub'];
    } else {
        $sql="SELECT * FROM it_devices ORDER BY device_type, device_name";
        $sub='';
    }
    $dbDevices=dbselectmulti($sql);
    
    if($dbDevices['numrows']>0)
    {
        header('Content-Type: text/csv'); // as original file
        header('Content-Disposition: attachment; filename="itdevices-'.date("Ymd").$sub.'.csv"');
        print "Device Type,Device Name,IP Address,Manufacturer,Serial,OS,Admin,Password,Assigned User Info\n";
    
        foreach($dbDevices['data'] as $device)
        {
            print stripslashes($device['device_type']).',';    
            print stripslashes($device['device_name']).',';    
            print stripslashes($device['device_ip']).',';    
            print stripslashes($device['device_mfg']).',';    
            print stripslashes($device['device_serial']).',';    
            print $oses[$device['device_os']].',';
            print stripslashes($device['device_admin']).',';    
            print stripslashes($device['device_password']).',';    
            print $people[$device['assigned_to']]."\n";    
                
        }
    }
    dbclose();
    die();
}

function deviceDetailWiki()
{
    global $oses;
    
    $sql="SELECT id,firstname,lastname,email,extension FROM users";
    $dbPeople=dbselectmulti($sql);
    $people[0]='Not assigned to a person';
    if($dbPeople['numrows']>0)
    {
        foreach($dbPeople['data'] as $person)
        {
            $people[$person['id']]=stripslashes($person['firstname'].' '.$person['lastname'].' - '.$person['email'].' - '.$person['extension']);
        }
    }
    
    
    if($_GET['sub'])
    {
        $sql="SELECT * FROM it_devices WHERE device_type='".addslashes($_GET['sub'])."' ORDER BY device_name";
        $sub="-".$_GET['sub'];
    } else {
        $sql="SELECT * FROM it_devices ORDER BY device_type, device_name";
        $sub='';
    }
    $dbDevices=dbselectmulti($sql);
    
    if($dbDevices['numrows']>0)
    {
        header('Content-Type: text/txt'); // as original file
        header('Content-Disposition: attachment; filename="itdevices-wiki-'.date("Ymd").$sub.'.txt"');
        $dtype='';
        print "======Equipment======\nThis is dumped from Mango via the 'Export to Wiki' in the devices section.\n";

        foreach($dbDevices['data'] as $device)
        {
            if($dtype=='' || $dtype!=$device['device_type'])
            {
                if($dtype!='')
                {
                    print '\\\\ \\\\ ';
                }
                $dtype=$device['device_type'];
                print "=====$dtype=====\n";
                print "^Device Name^IP^Assigned User^OS^Location/Details^Specs^RDC ?^In NIA?^\n";
            }
            print "| ".stripslashes($device['device_name']).' | ';    
            print stripslashes($device['device_ip']).' | ';    
            print $people[$device['assigned_to']]." | ";    
            print $oses[$device['device_os']].' | ';
            print str_replace("\r\n","\\\\ ",stripslashes(($device['device_location']))).' | ';    
            print str_replace("\r\n","\\\\ ",stripslashes(($device['device_specs']))).' | ';    
            print " N/A | N/A |\n";  
        }
    }
    dbclose();
    die();
}


footer();