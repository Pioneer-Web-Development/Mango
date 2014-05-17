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
        case "Save Department":
        save_department('insert');
        break;
        
        case "Update Department":
        save_department('update');
        break;
        
        case "add":
        setup_department('add');
        break;
        
        case "edit":
        setup_department('edit');
        break;
        
        case "delete":
        setup_department('delete');
        break;
        
        case "list":
        setup_department('list');
        break;
        
        default:
        setup_department('list');
        break;
        
    } 
    
    
function setup_department($action)
{
    global $siteID;
    $id=intval($_GET['departmentid']);
    if (isset($_GET['parentid']))
    {
        $parentid=intval($_GET['parentid']);
       
    } else {
        $parentid=0;
    }
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Department";
        } else {
            $button="Update Department";
            $sql="SELECT * FROM user_departments WHERE id=$id";
            $dbDepartment=dbselectsingle($sql);
            $department=$dbDepartment['data'];
            $name=$department['department_name'];
            $gl=$department['dept_gl'];
            $repair=$department['repair_email'];
            $inventory=$department['inventory_email'];
            $escalation=$department['escalation_email'];
        }
        print "<form method=post>\n";
        make_text('department_name',$name,'Department Name');
        make_text('department_gl',$gl,'Department GL#');
        make_text('repair',$repair,'Repair Email','What email address should repair notices be sent to?');
        make_text('inventory',$inventory,'Inventory Email','What email address should inventory updates be sent to?');
        make_text('escalation',$escalation,'Escalation Email','What email address should notices about escalated/unresolved issues be sent to?');
        make_hidden('departmentid',$id);
        make_hidden('parentid',$parentid);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $sql="DELETE FROM user_departments WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
        if ($error!='')
        {
            setUserMessage('There was a problem deleting the department.<br>'.$error,'error');
        } else {
            setUserMessage('Department successfully deleted','success');
        }
        redirect("?action=list&parentid=$parentid");
        
    } else {
        $sql="SELECT * FROM user_departments WHERE site_id=$siteID AND parent_id=$parentid ORDER BY department_name";
        $dbDepartments=dbselectmulti($sql);
        tableStart("<a href='?action=list&parentid=0'>Return to main</a>,<a href='?action=add&parentid=$parentid'>Add new department</a>","Department Name",4);
        if ($dbDepartments['numrows']>0)
        {
            foreach($dbDepartments['data'] as $department)
            {
                $name=$department['department_name'];
                $did=$department['id'];
                print "<tr><td>$name</td>";
                print "<td><a href='?action=edit&departmentid=$did&parentid=$parentid'>Edit</a></td>\n";
                if ($parentid==0)
                {
                    print "<td><a href='?action=list&parentid=$did'>Sub-departments</a></td>\n";
                } else {
                    print "<td></td>\n";
                }
                print "<td><a href='?action=delete&departmentid=$did&parentid=$parentid' class='delete'>Delete</a></td>\n";
            
            }
        }
        tableEnd($dbDepartments);
        
    }
}

function save_department($action)
{
    global $siteID;
    $id=$_POST['departmentid'];
    $parentid=$_POST['parentid'];
    $name=addslashes($_POST['department_name']);
    $gl=addslashes($_POST['department_gl']);
    $repair=addslashes($_POST['repair']);
    $inventory=addslashes($_POST['inventory']);
    $escalation=addslashes($_POST['escalation']);
    if ($action=='insert')
    {
        $sql="INSERT INTO user_departments (department_name, department_gl, parent_id, repair_email, inventory_email, 
        escalation_email, site_id) VALUES ('$name', '$gl', '$parentid', '$repair', '$inventory', '$escalation', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE user_departments SET department_gl='$gl', repair_email='$repair', 
        inventory_email='$inventory', escalation_email='$escalation', department_name='$name' WHERE id=$id";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the department.<br>'.$error,'error');
    } else {
        setUserMessage('Department successfully saved.','success');
    }
   redirect("?action=list&parentid=$parentid");
    
}


footer();
?>
