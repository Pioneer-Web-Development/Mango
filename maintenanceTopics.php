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
        case "Save Topic":
        save_topic('insert');
        break;
        
        case "Update Topic":
        save_topic('update');
        break;
        
        case "add":
        setup_topic('add');
        break;
        
        case "edit":
        setup_topic('edit');
        break;
        
        case "delete":
        setup_topic('delete');
        break;
        
        case "list":
        setup_topic('list');
        break;
        
        default:
        setup_topic('list');
        break;
        
    } 
    
    
function setup_topic($action)
{
    global $siteID;
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Topic";
        } else {
            $button="Update Topic";
            $id=intval($_GET['id']);
            $sql="SELECT * FROM maintenance_topics WHERE id=$id";
            $dbTopic=dbselectsingle($sql);
            $topic=$dbTopic['data'];
            $name=$topic['topic_name'];
        }
        print "<form method=post>\n";
        make_text('topic_name',$name,'Topic Name');
        make_hidden('topicid',$id);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $id=intval($_GET['id']);
        $sql="DELETE FROM maintenance_topics WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the topic.<br />'.$error,'error');
        } else {
            setUserMessage('The topic has been successfully deleted.','success');
        }
    
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM maintenance_topics WHERE site_id=$siteID ORDER BY topic_name";
        $dbTopics=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new topic</a>","Name",3);
        if ($dbTopics['numrows']>0)
        {
            foreach($dbTopics['data'] as $topic)
            {
                $name=$topic['topic_name'];
                $id=$topic['id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&id=$id'>Edit</a></td>\n";
                print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>\n";
                print "</tr>\n";
            }
        }
        tableEnd($dbTopics);
        
    }
}

function save_topic($action)
{
    global $siteID;
    $id=$_POST['topicid'];
    $name=addslashes($_POST['topic_name']);
    if ($action=='insert')
    {
        $sql="INSERT INTO maintenance_topics (topic_name, site_id) VALUES ('$name', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE maintenance_topics SET topic_name='$name' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the topic.<br />'.$error,'error');
    } else {
        setUserMessage('The topic has been successfully saved.','success');
    }
    redirect("?action=list");
    
}

footer();
?>
