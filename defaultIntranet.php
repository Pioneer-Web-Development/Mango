<?php
session_start();
include("includes/functions_db.php");
include("includes/config.php");
include("includes/functions_common.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title><?php echo $GLOBALS['systemTitle']; ?></title>
<META name="author" content="Joe Hansen <jhansen@idahopress.com>">
<META name="copyright" content="&copy; <?php echo date("Y"); ?> Pioneer Newspapers Inc - Joe Hansen">
<META name="robots" content="none">
<META http-equiv="cache-control" content="no-cache">
<META http-equiv="pragma" content="no-cache">
<META http-equiv="content-type" content="text/html; charset=UTF-8">
<META http-equiv="expires" content="0">
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" /> 
<script language="javascript" type="text/javascript" src="includes/functions_pims.js"></script>
<script language="javascript" type="text/javascript" src="includes/scripts/ajax_pageloader.js"></script>
<script language="javascript" type="text/javascript" src="includes/scripts/calendarDateInput.js"></script>
<script type="text/javascript" src="includes/scripts/dhtmlSuite/js/dhtml-suite-for-applications-without-comments.js"></script>
<script type="text/javascript" src="includes/ajax.js"></script>
<SCRIPT type="text/javascript" src="includes/scripts/modalmessage/ajax-dynamic-content.js"></SCRIPT>
<SCRIPT type="text/javascript" src="includes/scripts/modalmessage/modal-message.js"></SCRIPT>
<link rel="stylesheet" type="text/css" href="styles/pims_main.css" />
<link rel="stylesheet" type="text/css" href="includes/scripts/modalmessage/modal-message.css" />
<script type='text/javascript'>
    var messageObj = new DHTML_modalMessage();
    

</script>
</head>
<body>
<div style='width:980px;height:60px;border-bottom:8px solid #AC1D23;padding-bottom:0px;margin-bottom: 0px;'>
    <div style='float:left;'>
        <img src='artwork/mango.png' border=0 width="120">
    </div>
    <div style='margin-left:10px;float:left;font-family:Trebuchet MS;font-size:48px;font-weight:bold;color:#AC1D23;' >
        MangoNET
    </div>
    
    <div style='float:right;'>
    <?php
    if($_SESSION['cmsuser']['pimsallowed'])
    {
        ?>
    <input type='button' class='submit' style='margin-left:34px;' onClick='document.location.href="default.php";' value='Switch to Production View'>
    <?php
    }
    print "<div style='font:weight:normal;font-size:12px;'>Welcome ".$_SESSION['cmsuser']['firstname'].",&nbsp;&nbsp;<a href='logout.php'>  Log out</a>&nbsp;&nbsp;<a href='changePassword.php'>  Change Password</a></div><div style='clear:both;'></div>\n";
    ?>
    </div>
</div>
<div class='clear'></div>

<?php
$doctypes=array("pdf"=>"PDF","word"=>"Word/Writer","excel"=>"Excel/Calc","powerpoint"=>"Powerpoint/Impress","text"=>"Plain Text","image"=>"Image/Picture");
  
//check for delete before loading the rest of the page
if (isset($_GET['deletenews']))
{
    $id=$_GET['id'];
    $sql="DELETE FROM user_news WHERE id=$id";
    $dbDelete=dbexecutequery($sql);
}
if (isset($_GET['deleteevent']))
{
    $id=$_GET['id'];
    $sql="DELETE FROM user_events WHERE id=$id";
    $dbDelete=dbexecutequery($sql);
}
if (isset($_GET['deletedoc']))
{
    $id=$_GET['id'];
    $sql="SELECT * FROM user_docs WHERE id=$id";
    $dbDoc=dbselectsingle($sql);
    $doc=$dbDoc['data'];
    $filepath=$GLOBALS['root']."artwork/intranetdocs/".$doc['filepath'];
    unlink($filepath."/".$doc['filename']);
    $sql="DELETE FROM user_docs WHERE id=$id";
    $dbDelete=dbexecutequery($sql);
}

//add a hidden field with the users department stored in it
print "<input type='hidden' name='departmentid' id='departmentid' value='".$_SESSION['cmsuser']['departmentid']."'>\n";
print "<div id='leftMenuCol' style='float:left;width:200px;'>\n";
showMenu();
print "</div>\n";
print "<div id='mainContent' style='float:left;width:750px;margin-left:10px;background-color:#FFDB4F;padding:5px;'>\n";
showMain();
print "</div>\n";
print "<div class='clear'></div>\n";


function showMenu()
{
    print "<div class='intMenu'><a href='?action=news'>News</a></div>\n";
    print "<div class='intMenu'><a href='?action=documents'>Forms &amp; Documents</a></div>\n";
    print "<div class='intMenu'><a href='?action=directory'>Employee Directory</a></div>\n";
    print "<div class='intMenu'><a href='?action=knowledge'>Knowledge Base</a></div>\n";
    print "<div class='intMenu'><a href='?action=timecard'>Time Card</a></div>\n";
    print "<div class='intMenu'><a href='?action=report'>Report a Problem</a></div>\n";
    print "<div class='intMenu'><a href='?action=profile'>My Profile</a></div>\n";
}

function showMain()
{
    $action=$_GET['action'];
    switch ($action)
    {
        case "news":
            show_news();
        break;
        
        case "documents":
            show_documents();
        break;
        
        case "knowledge":
            show_knowledge();
        break;
        
        case "directory":
            show_directory();
        break;
        
        case "timecard":
            show_timecard();
        break;
        
        case "report":
            report_problem();
        break;
        
        case "profile":
            my_profile();
        break;
        
        default:
            show_news('public');
        break;
    }
    
    
}


function show_news()
{
    global $doctypes;
    print "<div style='float:left;width:66%;padding-right:5px;border-right:1px solid #AC1D23;'>\n";
    $type=$_GET['type'];
    print "<span style='float:left;'><a href='?action=news&type=public'>Show Public</a> or <a href='?action=news&type=dept'>Show Department</a></span><div class='clear'></div>";
    if ($type=='dept')
    {
        $display='MY DEPARTMENT';
    } else {
        $display='COMPANY';
    }
    $cdate=date("Y-m-d");
    $userdept=$_SESSION['cmsuser']['departmentid'];
    print "<span style='float:left;font-size:18px;font-weight:bold;color:#AC1D23'>$display NEWS &amp; INFORMATION</span>\n";
    if ($_SESSION['cmsuser']['admin'] || $_SESSION['cmsuser']['editintranetnews'])
    {
        print "<span style='float:right;margin-right:5px;'><a href='#' onclick=\"window.open('includes/newsEditorDisplay.php','News Editor','width=700,height=550,toolbar=0,status=0,location=0,scrollbars=0,resizable=0');\"><img src='artwork/icons/paper&pencil_48.png' width=32 border=0></a></span>\n";
    }
    print "<div class='clear'></div>\n";
    print "<br />\n";
    if ($type=='dept')
    {
        $dept="AND department_id='$userdept'";         
    }
    $sql="SELECT * FROM user_news WHERE scope='$type' $dept AND expiration_date>='$cdate' ORDER BY post_datetime DESC";
    $dbNews=dbselectmulti($sql);
    if ($dbNews['numrows']>0)
    {
        foreach($dbNews['data'] as $news)
        {
            print "<p class='newsHead'>".$news['headline'];
            print "<span style='float:right;'>\n";
            if ($_SESSION['cmsuser']['admin'] || $_SESSION['cmsuser']['editintranetdocs'])
            {
                print "<a onclick=\"window.open('includes/newsDocumentDisplay.php?action=list&newsid=$news[id]','Document Editor','width=700,height=550,toolbar=0,status=0,location=0,scrollbars=0,resizable=0');\"><img src='artwork/icons/add-file-48x48.png' width=24 border=0></a>";
            }                                                                                                                                                                                                                                                                       
            if ($_SESSION['cmsuser']['admin'] || $_SESSION['cmsuser']['editintranetnews'])
            {
                print "<a onclick=\"window.open('includes/newsEditorDisplay.php?newsid=$news[id]','News Editor','width=700,height=550,toolbar=0,status=0,location=0,scrollbars=0,resizable=0');\"><img src='artwork/icons/paper_content_pencil_48.png' width=24 border=0></a>";
            }
            if ($_SESSION['cmsuser']['admin'] || $_SESSION['cmsuser']['editintranetnews'])
            {
                print "<a style='text-decoration:none;' href='?deletenews&id=$news[id]' onclick='confirmDeleteClick()');\"><img src='artwork/icons/cancel_48.png' width=24 border=0></a>";
            }
            print "</span>\n</p>\n";
            print "<div class='newsArticle'>".stripslashes($news['article'])."</div>\n";
            $sql="SELECT * FROM user_documents WHERE news_id='$news[id]' ORDER BY document_title";
            $dbDocuments=dbselectmulti($sql);
            if ($dbDocuments['numrows']>0)
            {
                foreach($dbDocuments['data'] as $document)
                {
                    print "<span style='float:left;margin-left:10px;'><img src='artwork/icons/floppy_disk_48.png' border=0 width=16 onclick=\"window.open('includes/downloadFile.php?id=$document[id]','Download File','width=50,height=20,toolbar=0,status=0,location=0,scrollbars=0,resizable=0');\"></span>\n";
                    print "<p style='float:left;margin-left:4px;font-size:.8em;'>$document[document_title]</p>\n";
                }
            }
            print "<br />\n";
        }    
    } else {
        print "<br /><span style='float:right;margin-right:5px;'><a href='#' onclick=\"window.open('includes/newsEditorDisplay.php','News Editor','width=700,height=550,toolbar=0,status=0,location=0,scrollbars=0,resizable=0');\">Be the first to post an news item! <img src='artwork/icons/paper&pencil_48.png' width=32 border=0></a></span>\n";
    }
    print "</div>\n";
    print "<div style='margin-left:-1px;border-left:1px solid #AC1D23;padding-left:5px;float:left;width:30%;'>\n";
    print "<span style='float:left;font-size:18px;font-weight:bold;color:#AC1D23'>CALENDAR</span>\n";
    if ($_SESSION['cmsuser']['admin'] || $_SESSION['cmsuser']['editintranetnews'])
    {
        print "<span style='float:right;margin-right:5px;'><a href='#' onclick=\"window.open('includes/newsEventDisplay.php','Event Editor','width=700,height=550,toolbar=0,status=0,location=0,scrollbars=0,resizable=0');\"><img src='artwork/icons/paper&pencil_48.png' width=32 border=0></a></span>\n";
    }
    print "<div class='clear'></div>\n";
    if (isset($_GET['year']))
    {
       $cyear=$_GET['year']; 
    } else {
       $cyear=date("Y");
    }
    if (isset($_GET['month']))
    {
       $cmonth=$_GET['month'];
       $cmonthname=date("F",strtotime("$cyear-$cmonth-01"));
       $working=date("Y-m-d",strtotime("$cyear-$cmonth-01"));
    } else {
       $cmonth=date("m");
       $cmonthname=date("F",strtotime("$cyear-$cmonth-01"));
       $working=date("Y-m-d",strtotime("$cyear-$cmonth-01"));
    }
    $lmonth=date("m",strtotime($working."-1 month"));
    $lyear=date("Y",strtotime($working."-1 month"));
    $lmonthname=date("F",strtotime($working."-1 month"));
    $nmax=date("Y-m-d",strtotime($working."+1 month"));
    $nmonth=date("m",strtotime($working."+1 month"));
    $nyear=date("Y",strtotime($working."+1 month"));
    $nmonthname=date("F",strtotime($working."+1 month"));
    $lastmonth="<a href='?".$type."news&month=$lmonth&year=$lyear'>$lmonthname</a>";
    $currentmonth="&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;$cmonthname&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";
    $nextmonth="<a href='?".$type."news&month=$nmonth&year=$nyear'>$nmonthname</a>";
    print $lastmonth.$currentmonth.$nextmonth."<br />\n";
    print "<br />\n";
    if ($scope=='dept')
    {
        $dept="AND department_id='$userdept'";         
    }
    $sql="SELECT * FROM user_events WHERE event_scope='$type' $dept AND event_datetime>='$working 00:00:01' AND event_datetime<'$nmax' ORDER BY event_datetime DESC";
    $dbEvents=dbselectmulti($sql);
    if ($dbEvents['numrows']>0)
    {
        foreach($dbEvents['data'] as $event)
        {
            print "<p class='eventDate'>".date("l, F jS \@ g:i a",strtotime($event['event_datetime']))."</p>\n";
            print "<p class='eventTitle'>".$event['event_title'];
            print "<span style='font-size:10px;float:right;'>\n";
            if ($_SESSION['cmsuser']['admin'] || $_SESSION['cmsuser']['editintranetevents'])
            {
                print "<a onclick=\"window.open('includes/newsEventDisplay.php?eventid=$event[id]','Event Editor','width=700,height=550,toolbar=0,status=0,location=0,scrollbars=0,resizable=0');\"><img src='artwork/icons/paper_content_pencil_48.png' width=24 border=0></a>";
            }
            if ($_SESSION['cmsuser']['admin'] || $_SESSION['cmsuser']['editintranetevents'])
            {
                print "<a style='text-decoration:none;' href='?deleteevent&id=$event[id]' onclick='confirmDeleteClick()');\"><img src='artwork/icons/cancel_48.png' width=24 border=0></a>";
            }
            print "</span>\n";
            print "<div class='clear'></div>\n";
            print "<div class='eventDescription'>".stripslashes($event['event_description'])."</div>\n";
        }    
    } else {
        print "<br /><span style='float:right;margin-right:5px;'><a href='#' onclick=\"window.open('includes/newsEventDisplay.php','News Editor','width=700,height=550,toolbar=0,status=0,location=0,scrollbars=0,resizable=0');\">Be the first to post an event! <img src='artwork/icons/paper&pencil_48.png' width=32 border=0></a></span>\n";
    }
    print "</div>\n";
    
    print "<div class='clear'></div>\n";
    
}

function show_documents()
{
    global $doctypes;
    $type=$_GET['type'];
    print "<a href='?action=documents&type=public'>Show Public</a> or <a href='?action=documents&type=dept'>Show Department</a>";
    if ($type=='dept')
    {
        $display='MY DEPARTMENT';
    } else {
        $display='COMPANY';
    }
    print "<br /><span style='float:left;font-size:18px;font-weight:bold;color:#AC1D23'>$display DOCUMENTS</span>\n";
    print "<span style='float:right;margin-right:5px;'><a href='#' onclick=\"window.open('includes/newsDocumentDisplay.php','Document Editor','width=700,height=550,toolbar=0,status=0,location=0,scrollbars=0,resizable=0');\"><img src='artwork/icons/add-file-48x48.png' width=32 border=0></a></span>\n";
    print "<div class='clear'></div>\n";
    $sql="SELECT * FROM user_documents WHERE document_scope='$type' ORDER BY document_title";
    $dbDocuments=dbselectmulti($sql);
    if ($dbDocuments['numrows']>0)
    {
        foreach($dbDocuments['data'] as $document)
        {
            print "<span style='float:left;'><img src='artwork/icons/floppy_disk_48.png' border=0 width=32 onclick=\"window.open('includes/downloadFile.php?id=$document[id]','Download File','width=50,height=20,toolbar=0,status=0,location=0,scrollbars=0,resizable=0');\"></span><p style='float:left;margin-left:10px;font-size:1.2em;'>$document[document_title]</p>\n";
            print "<span style='float:right;margin-right:10px;'>";
            if ($_SESSION['cmsuser']['admin'] || $_SESSION['cmsuser']['editintranetdocs'])
            {
                print "<a onclick=\"window.open('includes/newsDocumentDisplay.php?documentid=$document[id]','Document Editor','width=700,height=550,toolbar=0,status=0,location=0,scrollbars=0,resizable=0');\"><img src='artwork/icons/paper_content_pencil_48.png' width=24 border=0></a>";
            }
            if ($_SESSION['cmsuser']['admin'] || $_SESSION['cmsuser']['editintranetdocs'])
            {
                print "<a style='text-decoration:none;' href='?deletedocument&id=$document[id]' onclick='confirmDeleteClick()');\"><img src='artwork/icons/cancel_48.png' width=24 border=0></a>";
            }
            print "</span><div class='clear'></div>\n";
            print "<p style='font-size:.8em;margin-left:42px;font-weight:normal;'>$document[document_description]</p>\n";    
        }
    } else {
        print "<br /><span><a href='#' onclick=\"window.open('includes/newsDocumentDisplay.php','Document Editor','width=700,height=550,toolbar=0,status=0,location=0,scrollbars=0,resizable=0');\">Be the first to post a document! <img src='artwork/icons/add-file-48x48.png' width=32 border=0></a></span>\n";
    }
}

function show_directory()
{
    global $siteID;
    $sql="SELECT A.*, B.department_name, C.position_name FROM users A, user_departments B, user_positions C WHERE A.site_id=$siteID AND A.department_id=B.id AND A.position_id=C.id ORDER BY department_name, lastname, firstname";
    $dbEmployees=dbselectmulti($sql);
    $count=round($dbEmployees['numrows']/3,0)+4;
    $lastdept='';
    print "<div style='float:left;width:250px;line-height:16px;'>\n";
    if ($dbEmployees['numrows']>0)
    {
        $i=0;
        foreach($dbEmployees['data'] as $employee)
        {
            if($employee['department_name']!=$lastdept)
            {
                $lastdept=$employee['department_name'];
                if ($i==0){$top=0;}else{$top=20;}
                print "<p style='margin-top:$top px;font-weight:bold;font-size:16px;padding:4px;color:#AC1D23;'>$lastdept</p>\n";$i++;
            }
            print "<p>$employee[lastname], $employee[firstname] | $employee[position_name]</p>\n";
            print "<p style='margin-left:10px;'>Extension: $employee[extension]</p>\n";
            if ($i==$count)
            {
                print "</div>\n";
                print "<div style='float:left;width:250px;line-height:16px;'>\n";
                print "<p style='font-weight:bold;font-size:16px;padding:4px;color:#AC1D23;'>$lastdept</p>\n";$i=0;
            } else {
                $i++;
            }
        }   
    }
    print "</div><div class='clear'></div>\n"; 
}

function show_knowledge()
{
    print "This feature is coming soon...";
}

function show_timecard()
{
    print "This feature is coming soon...";
}

function my_profile()
{
    print "This feature is coming soon...";
}
dbclose();
?>
</body>
</html>