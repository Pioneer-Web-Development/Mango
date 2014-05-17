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
        case "Save Customer":
        save_customer('insert');
        break;
        
        case "Update Customer":
        save_customer('update');
        break;
        
        case "add":
        manage_customers('add');
        break;
        
        case "edit":
        manage_customers('edit');
        break;
        
        case "delete":
        manage_customers('delete');
        break;
        
        case "list":
        manage_customers('list');
        break;
        
        default:
        manage_customers('list');
        break;
        
    } 
     
function manage_customers($action)
{
    global $siteID;
    $types=array("advertiser"=>"Advertiser","commercial"=>"Commercial");
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button="Save Customer";
            $type="advertiser";
        } else {
            $button="Update Customer";
            $customerid=intval($_GET['customerid']);
            $sql="SELECT * FROM customers WHERE id=$customerid";
            $dbCustomer=dbselectsingle($sql);
            $customer=$dbCustomer['data'];
            $name=stripslashes($customer['customer_name']);
            $type=stripslashes($customer['customer_type']);
            $vdname=stripslashes($customer['vision_data_name']);
            $vdaccount=stripslashes($customer['vision_data_account']);
        }
        print "<form action=\"$_SERVER[PHP_SELF]\" method=post>\n";
        make_select('customer_type',$types[$type],$types,'Type');
        make_text('customer_name',$name,'Customer Name');
        make_text('vd_name',$vdname,'Name in Vision Data');
        make_text('vd_account',$vdaccount,'Vision Data Account Number');
        make_hidden('customerid',$customerid);
        make_submit('submit',$button);
        print "</form>\n";  
    } elseif($action=='delete') {
        $customerid=intval($_GET['customerid']);
        $sql="DELETE FROM customers WHERE id=$customerid";
        $dbUpdate=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM customers WHERE site_id=$siteID ORDER BY customer_name";
        $dbCustomers=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new customer</a>","Customer Name,Customer Type",4);
        if ($dbCustomers['numrows']>0)
        {
            foreach($dbCustomers['data'] as $customer)
            {
                $name=$customer['customer_name'];
                $type=$customer['customer_type'];
                $customerid=$customer['id'];
                print "<tr><td>$name</td><td>$type</td>";
                if ($customerid==1)
                {
                    //no editing of the WE PRINT customer
                    print "<td></td><td></td>";
                } else {
                    print "<td><a href='?action=edit&customerid=$customerid'>Edit</a></td>\n";
                    print "<td><a class='delete' href='?action=delete&customerid=$customerid'>Delete</a></td>\n";
                }
                print "</tr>\n";
            }
        }
        tableEnd($dbCustomers);
        
    }
       
  
}

function save_customer($action)
{
    global $siteID;
    $customerid=$_POST['customerid'];
    $customer_name=addslashes($_POST['customer_name']);
    $customer_type=addslashes($_POST['customer_type']);
    $vdname=addslashes($_POST['vd_name']);
    $vdaccount=addslashes($_POST['vd_account']);
    if ($action=='insert')
    {
        $sql="INSERT INTO customers (customer_name, customer_type, vision_data_name, vision_data_account, site_id) 
        VALUES ('$customer_name', '$customer_type', '$vdname', '$vdaccount', '$siteID')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE customers SET customer_name='$customer_name', customer_type='$customer_type', vision_data_name='$vdname', 
        vision_data_account='$vdaccount' WHERE id=$customerid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the customer.','error');
    } else {
        setUserMessage('Customer successfully saved','success');
    }
    redirect("?action=list");
    
}

footer();
?>
