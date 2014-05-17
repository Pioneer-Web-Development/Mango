<?php
//this script is to special advertising sections - will be injected into jobs
include("includes/mainmenu.php");
  

if($_POST)
{
    $action=$_POST['submitbutton'];
} else {
    $action=$_GET['action'];
}

     
switch($action)
{
    case "add":
        sections('add');
    break;
    
    case "edit":
        sections('edit');
    break;
    
    case "delete":
        sections('delete');
    break;
    
    case "pjob":
        printjob();
    break;
    
    case "Save Section":
        save_sections('insert');
    break;
    
    case "Update Section":
        save_sections('update');
    break;
    
    default:
        sections('list');
    break;
} 


function sections($action)
{
    global $siteID, $pubs, $pubids,$papertypes;
    
    //get product type
    $ptypes=array();
    $ptypes[0]='Please choose';
    $sql="SELECT * FROM special_section_types WHERE site_id=$siteID ORDER BY product_name";
    $dbP=dbselectmulti($sql);
    if ($dbP['numrows']>0)
    {
        foreach($dbP['data'] as $p)
        {
            $ptypes[$p['id']]=$p['product_name'];
        }
    }
    
    $sectionid=intval($_GET['sectionid']);
    if ($action=='add' || $action=='edit')
    {
        if ($action=='add')
        {
            $button='Save Section';
            $insertdate=date("Y-m-d");
            $pubid=0;
            $sectionname='';
            $flyerdue=date("Y-m-d");
            $glossysales=date("Y-m-d");
            $glossycover=0;
            $glossyclear=date("Y-m-d");
            $salesdeadline=date("Y-m-d");
            $pagelayout=date("Y-m-d");
            $adsclear=date("Y-m-d");
            $pagination=date("Y-m-d");
            $ptype='0';
            $editorialcontent=0;
            $pressdate=date("Y-m-d");
            $draw=0;
            $overrun=0;
            $paper=$GLOBALS['defaultNewsprintID'];
            $bindery=0;
            $notes='';
            $revenueGoal=0;
            $revenueActual=0;
            $outsideJob=0;
            $digitalJob=0;
            $requestPrint=date("Y-m-d");
            $dummyDate=date("Y-m-d");
            
        } else {
            $button='Update Section';
            $sql="SELECT * FROM special_sections WHERE id=$sectionid";
            $dbSection=dbselectsingle($sql);
            $section=$dbSection['data'];
            $insertdate=$section['insert_date'];
            $pubid=$section['pub_id'];
            $sectionname=$section['section_name'];
            $flyerdue=$section['flyer_due'];
            $glossyclear=$section['glossy_clear'];
            $glossysales=$section['glossy_sales'];
            $glossycover=$section['glossy_cover'];
            $salesdeadline=$section['sales_date'];
            $pagelayout=$section['page_layout'];
            $adsclear=$section['ads_clear'];
            $pagination=$section['pagination_date'];
            $ptype=$section['product_type'];
            $editorialcontent=$section['editorial_content'];
            $pressdate=$section['press_date'];
            $draw=$section['draw'];
            $overrun=$section['overrun'];
            $paper=$section['paper_type'];
            $bindery=$section['bindery'];
            $notes=$section['notes'];
            $revenueGoal=$section['revenue_goal'];
            $revenueActual=$section['revenue_actual'];
            $outsideJob=$section['outside_job'];
            $digitalJob=$section['digital_job'];
            $requestPrint=$section['request_printdate'];
            $dummiesTo=$section['dummies_to'];
            $dummyDate=$section['dummy_date'];
        }
        print "<form method=post>\n";
        make_select('pub_id',$pubs[$pubid],$pubs,'Publication','Which publication is this section for?');
        make_text('section_name',$sectionname,'Section name','Name of the special secton',50);
        make_date('insert_date',$insertdate,'Insert date','When does this section publish in the paper?');
        make_date('flyer_due',$flyerdue,'Flyer Due','When is the promotional flyer due?');
        make_checkbox('glossy_cover',$glossycover,'Glossy cover?','Check if this product has a glossy cover');
        make_date('glossy_sales',$glossysales,'Glossy sales deadline','When is the deadline for glossy sales?');
        make_date('glossy_clear',$glossyclear,'Gloss ad clear','When do the glossy ads need to clear?');
        make_checkbox('editorial_content',$editorialcontent,'Editorial','Check if this product requires editorial content.');
        make_checkbox('outside_job',$outsideJob,'Outside printer','Check if this will be printed/produced by a 3rd party printer.');
        make_checkbox('digital_job',$digitalJob,'Digital Job','Check if this will be an online only product (no printing required)');
        make_checkbox('bindery',$bindery,'Stitch &amp; Trim','Check if this product requires stitching and trimming.');
        make_date('sales_date',$salesdeadline,'Sales deadline','Deadline for regular ad sales');
        make_date('page_layout',$pagelayout,'Page Layout','Deadline for page layout');
        make_date('dummy_date',$dummyDate,'Dummy Date','When is the section dummied?');
        make_text('dummies_to',$dummiesTo,'Dummies To','Who gets the dummies?');
        make_date('ads_clear',$adsclear,'Ad clear deadline','Deadline for ads to clear');
        make_date('pagination_date',$pagination,'Pagination Date','Deadline for pagination of the section');
        make_date('request_printdate',$requestPrint,'Requested Press Date','When should this product be scheduled to print?');
        make_select('product_type',$ptypes[$ptype],$ptypes,'Product Type','Type of product');
        make_number('draw',$draw,'Draw','How many copies to produce?');
        make_number('overrun',$overrun,'Overrun','How many copies for other purposes?');
        make_select('paper_type',$papertypes[$paper],$papertypes,'Paper','Type of paper to print on');
        make_number('revenue_goal',$revenueGoal,'Revenue Goal','How much did we hope to make?');
        make_number('revenue_actual',$revenueGoal,'Revenue Actual','How much did we actually make?');
        make_textarea('notes',$notes,'Notes','All notes and additional information about this job.');
        make_submit('submitbutton',$button);
        make_hidden('sectionid',$sectionid);
        print "</form>\n";
    }else if($action=='delete')
    {
        $sql="DELETE FROM special_sections WHERE id=$sectionid";
        $dbDelete=dbexecutequery($sql);
        redirect("?action=list");
    } else {
        $sql="SELECT * FROM special_sections WHERE site_id=$siteID AND pub_id IN ($pubids) ORDER BY insert_date DESC";
        $dbSections=dbselectmulti($sql);
        tableStart("<a href='?action=add'>Add new special section</a>","Month,Product Description,Insertion Date,Outside Job,Sales Deadline,Pagination Date,Product Type",10);
        if ($dbSections['numrows']>0)
        {
            foreach($dbSections['data'] as $section)
            {
                $id=$section['id'];
                $month=date("F",strtotime($section['insert_date']));
                $desc=$section['section_name'];
                $insert=date("D, M j",strtotime($section['insert_date']));
                $sales=date("D, M j",strtotime($section['sales_date']));
                $pagination=date("D, M j",strtotime($section['pagination_date']));
                $ptype=$ptypes[$section['product_type']];
                if($section['outside_job']==1){$outside='Outside Vendor';}else{$outside='We print';}
                print "<tr>\n";
                print "<td>$month</td>\n";
                print "<td>$desc</td>\n";
                print "<td>$insert</td>\n";
                print "<td>$outside</td>\n";
                print "<td>$sales</td>\n";
                print "<td>$pagination</td>\n";
                print "<td>$ptype</td>\n";
                print "<td><a href='?action=edit&sectionid=$id'>Edit</td>\n";
                print "<td><a href='?action=pjob&sectionid=$id'>Convert to Press Job</td>\n";
                print "<td><a href='?action=delete&sectionid=$id' class='delete'>Delete</td>";
                print "</tr>\n";
            }
        }
        tableEnd($dbSections);
    }
}

function save_sections($action)
{
    global $siteID;
    $sectionid=$_POST['sectionid'];
    $insertdate=$_POST['insert_date'];
    $pubid=$_POST['pub_id'];
    $sectionname=$_POST['section_name'];
    $flyerdue=$_POST['flyer_due'];
    $glossyclear=$_POST['glossy_clear'];
    $glossysales=$_POST['glossy_sales'];
    if ($_POST['glossy_cover']){$glosscover=1;}else{$glossycover=0;}
    if ($_POST['editorial_content']){$editorialcontent=1;}else{$editorialcontent=0;}
    if ($_POST['outside_job']){$outsidejob=1;}else{$outsidejob=0;}
    if ($_POST['digitial_job']){$digitaljob=1;}else{$digitaljob=0;}
    if ($_POST['bindery']){$bindery=1;}else{$bindery=0;}
    $salesdate=$_POST['sales_date'];
    $pagelayout=$_POST['page_layout'];
    $adsclear=$_POST['ads_clear'];
    $pagination=$_POST['pagination_date'];
    $ptype=$_POST['product_type'];
    $pressdate=$_POST['press_date'];
    $draw=$_POST['draw'];
    $overrun=$_POST['overrun'];
    $paper=$_POST['paper_type'];
    $notes=addslashes($_POST['notes']);
    $revenueGoal=$_POST['revenue_goal'];
    $revenueActual=$_POST['revenue_actual'];
    $requestPrint=$_POST['request_printdate'];
    $dummiesTo=addslashes($_POST['dummies_to']);
    $dummyDate=$_POST['dummy_date'];
    if ($action=='insert')
    {
        $sql="INSERT INTO special_sections (pub_id, insert_date, section_name, flyer_due, glossy_clear, glossy_sales, glossy_cover, editorial_content, outside_job, 
        digital_job, bindery, sales_date, page_layout,  ads_clear, pagination_date, product_type, draw, overrun, paper_type, notes, revenue_goal, 
        revenue_actual, site_id, request_printdate, dummies_to, dummy_date) VALUES ('$pubid', '$insertdate', '$sectionname', '$flyerdue','$glossyclear',  '$glossysales', '$glossycover', '$editorialcontent', 
        '$outsidejob', '$digitaljob',  '$bindery', '$salesdate', '$pagelayout', '$adsclear', '$pagination', '$ptype', '$draw', '$overrun', '$paper', 
        '$notes', '$revenueGoal', '$revenueActual', '$siteID', '$requestPrint', '$dummiesTo', '$dummyDate')";
        $dbInsert=dbinsertquery($sql);
        $error=$dbInsert['error'];
    } else {
        $sql="UPDATE special_sections SET pub_id='$pubid', insert_date='$insertdate', section_name='$sectionname', flyer_due='$flyerdue', 
        glossy_clear='$glossyclear', glossy_sales='$glossysales', glossy_cover='$glossycover', editorial_content='$editorialcontent', outside_job='$outsidejob', 
        digital_job='$digitaljob', binder='$bindery', sales_date='$salesdate', page_layout='$pagelayout',  ads_clear='$adsclear', pagination_date='$pagination', 
        product_type='$ptype', draw='$draw', overrun='$overrun', paper_type='$paper', notes='$notes', revenue_goal='$revenueGoal', 
        revenue_actual='$revenueActual', request_printdate='$requestPrint', dummies_to='$dummiesTo', dummy_date='$dummyDate' WHERE id=$sectionid";
        $dbUpdate=dbexecutequery($sql);
        $error=$dbUpdate['error'];
    }
    
    if ($error=='')
    {
        setUserMessage('The special section was saved successfully','success');
    } else {
        setUserMessage('There was a problem saving the special section.<br>'.$error,'error');
    }
    redirect("?action=list");
}

function  printjob ()
{
    //@todo create function for Special Sections to convert to a press run & bindery run if needed
    print "This function has not been set up yet.";
}

footer(); 
?>
