<?php
//<!--VERSION: .9 **||**-->
//pims version control system
include("includes/mainmenu.php");
$coreFilepaths=array("/","/styles/","includes","includesjscripts/");
    
if ($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}

switch ($action)
{
    case "buildpackage":
    build_package();
    break;
    
    case "getfiles":
    get_versions();
    break;
    
    case "delete":
    delete_file();
    break;
    
    case "upgradefiles":
    get_updates();
    break;
    
    case "changelog":
    changelog();
    break;
    
    case "Save File":
    save_changelog();
    break;
    
    case "getcurrentstamp":
    get_current_filedate(intval($_GET['fileid']));
    break;
    
    case "updateallstamps":
    get_current_filedate('all');
    break;
    
    default:
    list_files();
    break;
}

function build_package()
{
    global $siteID, $coreFilepaths;
    $batchtime=date("Y-m-d H:i:s");
    foreach($coreFilepaths as $id=>$path)
    {
        switch($path)
        {
            case "/":
                $type='core';
            break;
            
            case "includes";
                $type='include';
            break;
            
            case "/styles/";
                $type='style';
            break;
            
            case "includesajax_handlers/";
                $type='ajaxhandler';
            break;
            
            case "includesjscripts/";
                $type='script';
            break;
        }
        print "Checking for $type files...<br />";
        $sql="SELECT id, file_name FROM core_system_files WHERE (file_moddate>file_lastupdate) OR (file_lastupdate IS Null) AND file_type='$type'";
        $dbFiles=dbselectmulti($sql);
        if($dbFiles['numrows']>0)
        {
            $zip = new ZipArchive();
            $archive="./upgrade/mangoUpgrade-$type.zip";
            if ($zip->open($archive, ZIPARCHIVE::OVERWRITE) === TRUE) {
            print "<ul>Created archive for $type at $archive<br />";
                foreach($dbFiles['data'] as $file)
                {
                    $fpath=".$path".$file['file_name'];
                    if(file_exists($fpath) && is_file($fpath))
                    {
                        $zip->addFile($fpath) or die ("ERROR: Could not add file: $fpath");   
                        print "<li>Added $file[file_name] from $fpath to the archive</li>\n";
                        $sql="UPDATE core_system_files SET file_lastupdate='$batchtime' WHERE id=$file[id]";
                        $dbUpdate=dbexecutequery($sql);
                    } else {
                        print "<li>$file[file_name] had a problem and was not added.</li>\n";
                        
                    }
                }
                $zip->close();
                print "</ul>\nClosed the archive<br /><br />\n";
            } else {
                     die ("Could not create archive");
            }
        }
                      
    }
} 


function get_current_filedate($id)
{
    if($id=='all')
    {
        $sql="SELECT * FROM core_system_files"; 
    } else {
        $sql="SELECT * FROM core_system_files WHERE id=$id";
    }
    $dbFiles=dbselectmulti($sql);
    if($dbFiles['numrows']>0)
    {
        foreach($dbFiles['data'] as $file)
        {
            $fileid=$file['id'];
            $type=$file['file_type'];
            $name=$file['file_name'];
            
            switch($type)
            {
                case "core":
                    $path='/';
                break;
                
                case "include";
                    $path='includes';
                break;
                
                case "style";
                    $path='/styles/';
                break;
                
                case "ajaxhandler";
                    $path='includesajax_handlers/';
                break;

                case "script";
                    $path='includesjscripts/';
                break;
            }
                  
            $date=date("Y-m-d H:i:s",filemtime($_SERVER['DOCUMENT_ROOT'].$path.$name));
            
            $sql="UPDATE core_system_files SET file_moddate='$date' WHERE id=$fileid";
            $dbUpdate=dbexecutequery($sql);
     
        }
    }
    if($id=='all')
    {
        setUserMessage('All files have been updated with new modification dated.','success');
    } else {
        setUserMessage('File '.$name.' has been updated with a new modification date.','success');
    }
    redirect("?action=list");                
              
}

function get_updates()
{
    global $coreFilepaths, $checkVersionAddress;
    $ftp_server='10.56.0.111';
    $ftp_user_name='siteadmin';
    $ftp_user_pass='slcrbt5';

    // set up basic connection
    $conn_id = ftp_connect($ftp_server); 

    // login with username and password
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 

    // check connection
    if ((!$conn_id) || (!$login_result)) { 
            echo "FTP connection has failed!";
            echo "Attempted to connect to $ftp_server for user $ftp_user_name"; 
            exit; 
        } else {
            echo "Connecting to $ftp_server...<br />";
        }
        echo "Current directory: " . ftp_pwd($conn_id) . "<br />\n";
    if (ftp_chdir($conn_id, "/myidahopress/upgrade/")) {
        echo "Changing directory to: " . ftp_pwd($conn_id) . "<br />\n";
    } else { 
        echo "Couldn't change directory!<br />\n";
    }
    
    
    foreach($coreFilepaths as $id=>$path)
    {
        switch($path)
        {
            case "/":
                $type='core';
            break;
            
            case "includes";
                $type='include';
            break;
            
            case "/styles/";
                $type='style';
            break;
            
            case "includesajax_handlers/";
                $type='ajaxhandler';
            break;
            
            case "includesjscripts/";
                $type='script';
            break;
        }
        
        if ($type=='script')
        {
            $archive="mangoUpgrade-$type.zip";
            //for testing purposes, we'll just use script type files
            if (ftp_get($conn_id,"./upgrade/".$archive, $archive, FTP_BINARY)) {
                echo "Successfully downloaded $archive<br />\n";
            } else {
                echo "There was a problem downloading $archive<br />\n";
            }
        }
            
    }
    ftp_close($conn_id);
    
    //now loop again, checking for the presence of an archive and extracting it to the proper folder
    foreach($coreFilepaths as $id=>$path)
    {
        switch($path)
        {
            case "/":
                $type='core';
            break;
            
            case "includes";
                $type='include';
            break;
            
            case "includesajax_handlers/";
                $type='ajaxhandler';
            break;
            
            case "/styles/";
                $type='style';
            break;
            
            case "includesjscripts/";
                $type='script';
            break;
        }
        
        if ($type=='script')
        {
            $archive="./upgrade/mangoUpgrade-$type.zip";
            $afile="mangoUpgrade-$type.zip";
            if(file_exists($archive))
            {
                $zip = new ZipArchive;
                print "<br />Extracting files from $archive<br />\n";
                if ($zip->open($archive) === true)
                {
                    $numFiles = $zip->numFiles;
                    $extracted=0;
                    
                    // iterate over file list
                    // print details of each file
                    
                    for ($i=0; $i<$numFiles; $i++) {
                        $file = $zip->statIndex($i);
                        printf("%s (%d bytes)", $file['name'], $file['size']);
                        print "<br />";
                        $zip->extractTo('./', array($zip->getNameIndex($i)));
         
                        $extracted++;    
                    } 
                    $zip->extractTo('/');
                    $zip->close();
                    print "Successfully upgraded $extracted files to $path<br />\n";
                } else {
                    print "There was a problem opening the archive...<br />\n";
                }
            }
        }

    }
    
}

function build_list()
{
    global $systemRootPath, $siteID;
    if (substr($systemRootPath,strlen($systemRootPath),1)!="/"){$systemRootPath.="/";}
    $sql="SELECT * FROM version_control_files ORDER BY version_pagename";
    $dbFiles=dbselectmulti($sql);
    if ($dbFiles['numrows']>0)
    {
        $output="<?xml version='1.0' ?>\n";
        $output="<items>\n";
        foreach($dbFiles['data'] as $file)
        {
            $output.="<item>\n";
            $output.="<filepath>$file[version_path]</filepath>\n";    
            $output.="<filename>$file[version_pagename]</filename>\n";    
            $output.="<version>$file[version_number]</version>\n";    
            $output.="<dbupdate>$file[sql_update_required]</dbupdate>\n";
            $output.="<update>$file[update_sql]</update>\n";
            $output.="<changelog>$file[version_changelog]</changelog>\n";
            $output.="<checksum>$file[version_checksum]</checksum>\n";
            $output.="</item>\n";    
        }
        $output.="</items>\n";
        file_put_contents("updates/version_info.xml",$output);
        print "Current system version file successfully created. <a href='updates/version_info.xml' target='_blank'>View it</a> or <a href='?action=list'>return to the file/version list.<br />\n";
        
    } else {
        print "No files existed. <a href='?action=list'>Return to main.</a>\n";
    }
}

function process_updates()
{
    global $checkVersionAddress, $systemRootPath;
    if (substr($checkVersionAddress,strlen($checkVersionAddress),1)!="/"){$checkVersionAddress.="/";}
    if (substr($systemRootPath,strlen($systemRootPath),1)!="/"){$systemRootPath.="/";}
    $updates=array();
    $i=0;
    if (file_exists($checkVersionAddress."version_info.xml"));
    {
        $xml=simplexml_load_file($checkVersionAddress."version_info.xml");
        $p_cnt = count($xml->item);
        for($i = 0; $i < $p_cnt; $i++) {
          $item = $xml->item[$i];
          //print_r($item);
          $filepath=$item->filepath;
          $filename=$item->filename;
          $version=$item->version;
          $updatesql=$item->dbupdate;
          $updatesql=$item->update;
          $changelog=$item->changelog;
          $checksum=$item->checksum;
          //check for version in database
          $sql="SELECT version_number, version_checksum FROM version_control_files WHERE version_pagename='$filename' AND version_path='$filepath'";
          $dbVersion=dbselectsingle($sql);
          if ($dbVersion['numrows']>0)
          {
              $v=$dbVersion['data']['version_number'];
              if ($v!=$version)
              {
                  $updates[$i]['filepath']=$filepath;
                  $updates[$i]['filename']=$filename;
                  $updates[$i]['version']=$version;
                  $updates[$i]['changelog']=$changelog;
                  $updates[$i]['checksum']=$checksum;
                  $updates[$i]['updates']=$update;
                  $updates[$i]['sql']=$updatesql;
                  $updates[$i]['status']='Update';
                  //now, get the file
                  if (is_writeable($_SYSTEM['DOCUMENT_ROOT']."updates/"))
                  {
                    $temp=file_get_contents($checkVersionAddress.$filename);
                    $outputpath=$_SYSTEM['DOCUMENT_ROOT']."updates/".$filename;
                    if(file_put_contents($outputpath,$temp))
                    {
                        //update the version_control_file table
                        $sql="UPDATE version_control_files SET version_number='$version',
                         update_sql='$updatesql', sql_update_required='$update',
                          version_changelog='$changelog', version_checksum='$checksum' WHERE version_pagename='$filename'
                           AND version_path='$filepath'";
                           $dbUpdate=dbexecutequery($sql);
                                        
                        
                        //now execute the included sql
                          if ($updatesql!='')
                          {
                              $dbExecute=dbexecutequery($updatesql);
                              if ($dbExecute['error']!='')
                              {
                                  $error=addslashes($dbExecute['error']);
                                  $sql="UPDATE version_control_files SET update_sql=CONCAT(update_sql,'Error: $error') WHERE version_pagename='$filename' AND version_path='$filepath'";
                                  $dbUpdate=dbexecutequery($sql);
                                  print "Sorry, there was an error running the page update sql for $filename.<br />\n";
                              } else {
                                  print "Database successfully updated for $filename<br />";
                              }
                          }
                          print "Successfully downloaded $filename to the uploads folder. You now need to move it to the $filepath folder to complete the upgrade.<br />\n";
                      } else {
                          print "Sorry, I was unable to save $filename to $filepath";
                      }
                      
                  } else {
                      print "The updates folder is not writeable. You'll have to fetch the file yourself and upload it manually.";
                  }
                  $temp="";
                  $i++;
              }
          } else {
              $updates[$i]['filepath']=$filepath;
              $updates[$i]['filename']=$filename;
              $updates[$i]['version']=$version;
              $updates[$i]['changelog']=$changelog;
              $updates[$i]['checksum']=$checksum;
              $updates[$i]['update']=$update;
              $updates[$i]['sql']=$updatesql;
              $updates[$i]['status']='New file';
              //now, get the file
              if (is_writeable($_SYSTEM['DOCUMENT_ROOT']."updates/"))
              {
                $temp=file_get_contents($checkVersionAddress.$filename);
                $outputpath=$_SYSTEM['DOCUMENT_ROOT']."updates/".$filename;
                if(file_put_contents($outputpath,$temp))
                {
                  $sql="INSERT INTO version_control_files (version_number, sql_update_required, version_pagename, version_path, version_date, version_changelog, version_checksum,  update_sql) VALUES ('$number', '$update', '$filename', '$filepath', '$date', '$changelog', '$checksum', '$updatesql')";
              $dbUpdate=dbexecutequery($sql);
              
                    //now execute the included sql
                  if ($updatesql!='')
                  {
                      $dbExecute=dbexecutequery($updatesql);
                      if ($dbExecute['error']!='')
                      {
                          $error=addslashes($dbExecute['error']);
                          $sql="UPDATE version_control_files SET update_sql=CONCAT(update_sql,'Error: $error') WHERE version_pagename='$filename' AND version_path='$filepath'";
                          $dbUpdate=dbexecutequery($sql);
                          print "Sorry, there was an error running the page update sql for $filename.<br />\n"; } else {
                          print "Database successfully updated for $filename<br />";
                      }
                  }
                  print "Successfully downloaded $filename to the uploads folder. You now need to move it to the $filepath folder to complete the upgrade.<br />\n";
              } else {
                  print "Sorry, I was unable to save $filename to $filepath";
              }
                  
              } else {
                  print "The updates folder is not writeable. You'll have to fetch the file yourself and upload it manually.";
              }
              $temp="";
              $i++;
          }
        } 
    }
    
}

function check_updates()
{
    global $checkVersionAddress, $siteID;
    $updates=array();
    $i=0;
    if (substr($checkVersionAddress,strlen($checkVersionAddress),1)!="/"){$checkVersionAddress.="/";}
    if (file_exists($checkVersionAddress."version_info.xml"));
    {
        $xml=simplexml_load_file($checkVersionAddress."version_info.xml");
        $p_cnt = count($xml->item);
        for($i = 0; $i < $p_cnt; $i++) {
          $item = $xml->item[$i];
          //print_r($item);
          $filepath=$item->filepath;
          $filename=$item->filename;
          $version=$item->version;
          $update=$item->dbupdate;
          $updatesql=$item->update;
          $changelog=$item->changelog;
          $checksum=$item->checksum;
          //check for version in database
          $sql="SELECT version_number FROM version_control_files WHERE site_id=$siteID AND version_pagename='$filename' AND version_path='$filepath'";
          $dbVersion=dbselectsingle($sql);
          if ($dbVersion['numrows']>0)
          {
              $v=$dbVersion['data']['version_number'];
              if ($v!=$version)
              {
                  $updates[$i]['filepath']=$filepath;
                  $updates[$i]['filename']=$filename;
                  $updates[$i]['version']=$version;
                  $updates[$i]['changelog']=$changelog;
                  $updates[$i]['checksum']=$checksum;
                  $updates[$i]['sql']=$update;
                  $updates[$i]['status']='Update';
                  $i++;
                  
              }
          } else {
              $updates[$i]['filepath']=$filepath;
              $updates[$i]['filename']=$filename;
              $updates[$i]['version']=$version;
              $updates[$i]['changelog']=$changelog;
              $updates[$i]['checksum']=$checksum;
              $updates[$i]['sql']=$update;
              $updates[$i]['status']='New file';
              $i++;
          }
        } 
    }
    if (count($updates)>0)
    {
        print "<table class='grid'>\n";
        print "<tr><th colspan=5>There are updated versions of some files available:</th></tr>\n";
        foreach($updates as $update)
        {
            print "<tr>";
            print "<td>$update[filepath]</td>";
            print "<td>$update[filename]</td>";
            print "<td>$update[version]</td>";
            print "<td>$update[checksum]</td>";
            print "<td>$update[sql]</td>";
            print "<td>$update[status]</td>";
            print "</tr>\n";
        }
        print "<tr><th colspan=5><a href='?action=processupdates'>Click here to download all new files and updates for the system.</a></th></tr>\n";
        print "</table>\n"; 
    } else {
        print "You are currently running the most updated versions of all files. <a href='?action=list'>Return</a>";
    } 
    
}

function get_versions()
{
    global $siteID, $coreFilepaths;
    // "touch" all files before starting, this lets us know which ones were not found
    $sql="UPDATE core_system_files SET touched=0";
    $dbUpdate=dbexecutequery($sql);
    $pages=array();
    $i=0;
    $updated=0;
    $inserted=0;
    $date=date("Y-m-d H:i:s");
    $path=$GLOBALS['systemRootPath'];
    foreach($coreFilepaths as $id=>$path)
    {
        print "Starting with path of $path<br />\n";
        $handler = opendir($_SERVER['DOCUMENT_ROOT'].$path);
        // keep going until all files in directory have been read
        while ($file = readdir($handler))
        {
            // if $file isn't this directory or its parent, 
            // add it to the results array
            if ($file != '.' && $file != '..' && !is_dir($file))
            {
                print "Working with $file<br>";
                $ext=end(explode(".",$file));
                if ($ext=="php" || $ext=='js' || $ext=='css')
                {
                    $date=date("Y-m-d H:i:s",filemtime($_SERVER['DOCUMENT_ROOT'].$path.$file));
                    
                    switch($path)
                    {
                        case "/":
                            $type='core';
                        break;
                        
                        case "includes";
                            $type='include';
                        break;
                        
                        case "/styles/";
                            $type='style';
                        break;
                        
                        case "includesajax_handlers/";
                            $type='ajaxhandler';
                        break;
            
                        case "includesjscripts/";
                            $type='script';
                        break;
                    }
                    $sql="SELECT * FROM core_system_files WHERE file_name='$file' AND file_type='$type'";
                    $dbVersion=dbselectsingle($sql);
                    if ($dbVersion['numrows']>0)
                    {
                        print "--File was found<br>";
                        $id=$dbVersion['data']['id'];
                        $sql="UPDATE core_system_files SET file_mod_date='$date', touched=1 WHERE id=$id";   
                        $dbUpdate=dbexecutequery($sql);
                        $updated++;
                    } else {
                        print "--File was not found<br>";
                        $sql="INSERT INTO core_system_files (file_type, file_name, file_moddate, site_id, head_load, load_order, touched, mango, kiwi, guava) 
                        VALUES ('$type', '$file', '$date', '$siteID', 1, 99, 1, 1, 1, 1)";
                        $dbInsert=dbinsertquery($sql);
                        print "------Problem inserting record $dbInsert[error]<br>";
                        $inserted++;
                    }
                }
            }    
        }
    }
    //now, all the files that are still marked as 'touched'==0 means that they are no longer on the server and thus should be removed
    $sql="DELETE FROM core_system_files WHERE touched=0";
    $dbDelete=dbexecutequery($sql);
    die();
   
     //print "Version files and file versions updated!<br />\n";
    setUserMessage("The scan completed with $updated updates and $inserted new files found.",'success');
    redirect("?action=list");    
}

function delete_file()
{
    $fileid=intval($_GET['fileid']);
    $sql="SELECT * FROM core_system_files WHERE id=$fileid";
    $dbFile=dbselectsingle($sql);
    $file=$dbFile['data'];
    
    $type=$file['file_type'];
    $filename=stripslashes($file['file_name']);
    
    switch($type)
        {
            case "core":
                $path='';
            break;
            
            case "include";
                $path='includes/';
            break;
            
            case "style";
                $path='styles/';
            break;
            
            case "ajaxhandler";
                $type='includes/ajax_handlers/';
            break;
            
            case "script";
                $path='includes/jscripts/';
            break;
        }
     
     if (unlink($path.$filename))
     {
        setUserMessage("The file $filename has been successfully deleted from $path.",'success');
        $sql="DELETE FROM core_system_files WHERE id=$fileid";
        $dbDelete=dbexecutequery($sql);
    } else {
        $sql="DELETE FROM core_system_files WHERE id=$fileid";
        $dbDelete=dbexecutequery($sql);
        setUserMessage('There was a problem deleting the file: '.$path.$filename,'error');
    }
    redirect("?action=list");
        
}

function list_files()
{
    global $siteID;
    
    if ($_POST['submit_search'])
    {
        if($_POST['filetype']!='0')
        {
           $filetype="WHERE file_type='$_POST[filetype]' ";
        } else {
            $filetype="";
        }
        $sql="SELECT * FROM core_system_files $filetype ORDER BY file_type, file_name";
        $_SESSION['cmsuser']['last_search_sql']['corefiles']=$sql; 
    } elseif($_SESSION['cmsuser']['last_search_sql']['corefiles']!='')
    {
        $sql=$_SESSION['cmsuser']['last_search_sql']['corefiles'];
    } else {
        $sql="SELECT * FROM core_system_files ORDER BY file_type, file_name";
    }
        
    $ftypes[0]='All files';
    $ftypes['core']="Function files";
    $ftypes['script']="Javascripts";
    $ftypes['style']="CSS Files";
    $ftypes['ajaxhandler']="Ajax Handlers";
    $ftypes['include']="Includes";
    $search="<form method=post>\nFilter by Type:<br>";
    $search.=input_select('filetype',$ftypes[$_POST['filetype']],$ftypes);
    $search.="<br><input name='submit_search' type='submit' value='Search'>\n";
    $search.="</form>\n";
        
    $dbFiles=dbselectmulti($sql);
    tableStart("<a href='?action=updateallstamps'>Update all mod dates</a>,<a href='?action=getfiles'>Get current page versions</a>,<a href='?action=buildpackage'>Build Upgrade Package</a>,<a href='?action=upgradefiles'>Upgrade Files</a>,<a href='?action=changelog&fileid=0'>Add file manually</a>,<br><hr>$search","Type,Filename,Last Modified,Last Updated,Load in head,Load order",9);   
    if ($dbFiles['numrows']>0)
    {
        foreach($dbFiles['data'] as $file)
        {
            print "<tr>\n";
            print "<td>$file[file_type]</td>";
            print "<td>$file[file_name]</td>";
            print "<td>$file[file_moddate]</td>";
            print "<td>$file[file_lastupdate]</td>";
            print "<td>$file[head_load]</td>";
            print "<td>$file[load_order]</td>";
            print "<td><a href='?action=getcurrentstamp&fileid=$file[id]'>Update date stamp</a></td>\n";
            print "<td><a href='?action=changelog&fileid=$file[id]'>Edit File Details</a></td>\n";
            print "<td><a href='?action=delete&fileid=$file[id]' class='delete'>Delete File</a></td>\n";
            print "</tr>\n";
        }    
    }
    tableEnd($dbFiles);
}

function changelog()
{
    $types=array("script"=>"Javascript",'core'=>"Core file","include"=>"Core include","style"=>"CSS Style","ajaxhandler"=>"Ajax Handlers");
    $id=intval($_GET['fileid']);
    $sql="SELECT * FROM core_system_files WHERE id=$id";
    $dbChanges=dbselectsingle($sql);
    $changes=$dbChanges['data'];
    $changelog=stripslashes($changes['file_log']);
    $type=$changes['file_type'];
    $filename=stripslashes($changes['file_name']);
    $page=stripslashes($changes['specific_page']);
    $order=$changes['load_order'];
    $head=$changes['head_load'];
    $mango=$changes['mango'];
    $kiwi=$changes['kiwi'];
    $guava=$changes['guava'];
    $papaya=$changes['papaya'];
    $ignore=$changes['ignore_file'];
    $delete=$changes['delete_file'];
    
    if($id==0 || $id=='')
    {
        $head=1;
        $order=99;
        $mango=1;
    }
    print "<form method=post>\n";
    make_select('type',$types[$type],$types,'Type','Type of file','','','changeCurrentPath()');
    print "<div class='label'>Filename</div><div class='input'><small>Full name of file including extension</small><br>
        <input type='text' name='name' id='name' size=50 onblur=\"checkForFile('name','exists',\$('#type').val());\" value='$filename' /><span id='exists' style='font-weight:bold;color:green;margin-left:10px;'></span>
        <input type='button' id='browse' value='Browse Server Files' style='display:inline;margin-left:10px;'>";
        ?>
        <script type='text/javascript'>
        var currentPath='../../';
        function changeCurrentPath()
        {
            var type=$('#type').val();
            if(type=='script')
            {
                currentPath='../..includesjscripts/';
            }
            if(type=='core')
            {
                currentPath='../../';
            }
            if(type=='include')
            {
                currentPath='../..includes';
            }
            if(type=='style')
            {
                currentPath='../../styles/';
            }
            if(type=='ajaxhandler')
            {
                currentPath='../..includesajax_handlers/';
            }
            $('#browse').brooser({
            currentDir      : currentPath
            })
        }
        $('#browse').brooser({
            changeDirAllowed: false,
            currentDir      : currentPath,
            onFinish        : function(file){
                $('#name').val(file);}
            });
        </script>
        <?php print "</div><div class='clear'></div>\n";
    make_multifile('testupload',"artwork/",'Test Multi','This is a multi-upload test');
    make_text('page',$page,'Only for page','Only load this for these specific pages (separate with commas).',50);
    make_checkbox('head',$head,'Head','Include in head declaration on page load');
    make_number('order',$order,'Order','Order of load in head for those files');
    make_checkbox('mango',$mango,'Mango','Check to have this file loaded for the Mango (production) app');
    make_checkbox('kiwi',$kiwi,'Kiwi','Check to have this file loaded for the Kiwi (advertising) app');
    make_checkbox('guava',$guava,'Guava','Check to have this file loaded for the Guava (editorial) app');
    make_checkbox('papaya',$papaya,'Papaya','Check to have this file loaded for the Papaya (circulation) app');
    make_checkbox('ignore',$ignore,'Ignore','Ignore this file when building upgrade packages');
    make_checkbox('delete',$delete,'Delete','Delete this file when upgrading');
    make_textarea('changelog',$changelog,'Changelog','',70,15);
    make_hidden('fileid',$id);
    make_submit('submit','Save File');
    print "</form>\n";
}
  
function save_changelog()
{
    $fileid=$_POST['fileid'];
    if($_POST['ignore']){$ignore=1;}else{$ignore=0;}
    if($_POST['delete']){$delete=1;}else{$delete=0;}
    if($_POST['head']){$head=1;}else{$head=0;}
    if($_POST['mango']){$mango=1;}else{$mango=0;}
    if($_POST['kiwi']){$kiwi=1;}else{$kiwi=0;}
    if($_POST['papaya']){$papaya=1;}else{$papaya=0;}
    if($_POST['guava']){$guava=1;}else{$guava=0;}
    $order=addslashes($_POST['order']);
    $type=addslashes($_POST['type']);
    $page=addslashes($_POST['page']);
    $type=addslashes($_POST['type']);
    $name=addslashes($_POST['name']);
    $changelog=addslashes($_POST['changelog']);
    if($fileid==0)
    {
        $sql="INSERT INTO core_system_files (file_log, ignore_file, delete_file, head_load, load_order, specific_page, file_type, file_name, mango, kiwi, guava, papaya)
         VALUES ('$changelog', '$ignore', '$delete', '$head', '$order', '$page', '$type', '$name', '$mango', '$kiwi', '$guava', '$papaya')";
         $dbInsert=dbinsertquery($sql); 
    } else {
        $sql="UPDATE core_system_files SET file_log='$changelog', ignore_file='$ignore', delete_file='$delete', mango='$mango', kiwi='$kiwi', guava='$guava', papaya='$papaya', head_load='$head', load_order='$order', specific_page='$page', file_type='$type', file_name='$name' WHERE id=$fileid";
    $dbUpdate=dbexecutequery($sql);
        
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the file updates','error');
    } else {
        setUserMessage('File updates successfully saved','success');
    }
    redirect("?action=list");  
} 

 
footer();
?>


