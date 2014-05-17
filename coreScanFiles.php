<?php
if ($_GET['direct']=='234*_SNS_DF2kj2kj2k')
{
    include("includes/functions_db.php");
    get_pages();
} else {
    include("includes/mainmenu.php") ;
    ?>

<div id="wrapper">
<?php
if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}
    run_page();
    print "</div></body></html>\n";
}

function run_page()
{
    if ($_POST){
        get_pages();
    } else 
    {
        show_pages();
    }
}

function show_pages()
{
   print "<form method=post>\n";
   $sql="SELECT * FROM c2_filedates";
   $dbFiles=dbselectmulti($sql);
   print "<table class='grid'>\n";
   print "<tr><th>Filename</th><th>Folder</th><th>Modification Date</th></tr>\n";
   if ($dbFiles['numrows']>0)
   {
       foreach($dbFiles['data'] as $file)
       {
           print "<tr>";
           print "<td>$file[file_name]</td>\n";
           print "<td>$file[file_folder]</td>\n";
           print "<td>$file[file_date]</td>\n";
           print "</tr>\n";
       }
   }
   print "<tr><th colspan=3><input type='submit' name='submit' value='Click here to re-scan files' /></th></tr>\n";
   print "</table>\n";
   print "</form>\n";
 
}

function get_pages()
{
    //purge database
    $sql="DELETE FROM c2_filedates";
    $dbDelete=dbexecutequery($sql);
    print "Beginning scanning process...<br />";
    $folders=array("CMS"=>"/app/cms","Classifieds"=>"/app/classifieds","CMS Includes"=>"/app/cms/includes");
    foreach($folders as $name=>$directory)
    {
        print "-->Working on $name for $directory<br />";
        if(is_readable($_SERVER['DOCUMENT_ROOT'].$directory))
        {// ... the path is readable
            // we open the directory
            $directory_list = opendir($_SERVER['DOCUMENT_ROOT'].$directory);
            // and scan through the items inside
            while (FALSE !== ($file = readdir($directory_list)))
            {// if the filepointer is not the current directory
                 // or the parent directory
                if($file != '.' && $file != '..')
                {// we build the new path to scan
                    $path = $_SERVER['DOCUMENT_ROOT'].$directory.'/'.$file;
                    // if the path is readable
                    if(is_readable($path))
                    {// we split the new path by directories
                        $subdirectories = explode('/',$path);
                        if(is_file($path))
                        {
                            $last_modified = filemtime($path);
                            $moddate=date("Y-m-d H:i:s",$last_modified);
                            print "$file -- modified on $moddate<br />\n";
                            $sql="INSERT INTO c2_filedates (file_name, file_date, file_folder) VALUES ('$file', '$moddate', '$directory')";
                            $dbInsert=dbexecutequery($sql);
                             
                        }
                    }
                }
            }
            // close the directory
            closedir($directory_list); 
        } else {
            print "!!!!!  Sorry, that directory is not readable at this time.<br />\n";
        }
        
    }
    print "<a href='?action=list'>Click here to view the saved list of files</a><br />\n";
}

footer();
?>
