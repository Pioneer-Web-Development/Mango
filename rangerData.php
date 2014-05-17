<?php
//ranger data file parser
if($_POST)
{
    include("includes/functions_db.php");
    include("includes/functions_common.php");

    $file=$_FILES['vdata']['tmp_name'];
    $contents=file_get_contents($file);
    //break it up by line
    $lines=explode("\n",$contents);

    array_shift($lines);

    $output='';
    $output='"Salesperson","Phone","Account Name","Account Other","Street","City","State","Zip","First Words","Publications","Category","Classification","Ad Number","Words","Times Run","Start","Stop","Contact","Width","Height","Inches/Lines","Rate","Price","Status"'."\n";
        
    foreach($lines as $line)
    {
        $line=convertCSVtoTSV($line);
        
        $parts=explode("\t",$line);
        
        $salesperson=trim($parts[0]);
        $phone=trim($parts[1]);
        $accountName=trim($parts[2]);
        $address1=trim($parts[3]);
        $address2=trim($parts[4]);
        $address3=trim($parts[5]);
        $address4=trim($parts[6]);
        $firstWords=trim($parts[7]);
        $publications=trim($parts[8]);
        $class=trim($parts[9]);
        $adNumber=trim($parts[10]);
        $wordCount=trim($parts[11]);
        $timesRun=trim($parts[12]);
        $start=trim($parts[13]);
        $stop=trim($parts[14]);
        $contact=trim($parts[15]);
        $width=trim($parts[16]);
        $height=trim($parts[17]);
        $inches=trim($parts[18]);
        $rate=trim($parts[19]);
        $price=trim($parts[20]);
        $status=trim($parts[21]);
        $pubsRun=trim($parts[22]);
        
        
        //look up the main category
        $sql="SELECT id, parent_id, category_name FROM c2_classifieds_categories WHERE category_code='$class'";
        $dbCat=dbselectsingle($sql);
        if($dbCat['numrows']>0)
        {
            $subName=strtoupper($dbCat['data']['category_name']);
            $parentid=$dbCat['data']['parent_id'];
            $sql="SELECT * FROM c2_classifieds_categories WHERE id=$parentid";
            $dbParent=dbselectsingle($sql);
            if($dbParent['numrows']>0)
            {
                $category=strtoupper(stripslashes($dbParent['data']['category_name']));    
            } elseif($dbCat['data']['parent_id']==0) {
                $category=$subName;
            } else {
                $category='Uncategorized';
            }
            
        } else {
            $subName=$class;
            $category='Uncategorized';
        }
        $cityline='';
        $street='';
        $accountOther='';
        
        //now parse the address lines
        if($address4!='')
        {
            $cityline=$address4;
        }
        
        if($address3!='' && $cityline=='')
        {
            $cityline=$address3;    
        }elseif($address3!='' && $cityline!='')
        {
            $street=$address3;
        }
        
        if($address2!='' && $cityline=='')
        {
            $cityline=$address2;    
        }elseif($address2!='' && $cityline!='' && $street=='')
        {
            $street=$address2;
        }
        if($street!='' && $address1!='')
        {
            $accountOther=$address1;
        } elseif($street=='' && $address1!='')
        {
            $accountOther='';
            $street=$address1;
        }
        
        //chop cityline into parts
        if($cityline!='')
        {
            $cityParts=explode(" ",$cityline);
            $zip=array_pop($cityParts); //last element
            $state=array_pop($cityParts); //new last elementh
            $city=implode(" ",$cityParts); //stick the rest back together in case the city has a two word name like "New Plymouth"
        } else {
            $city='No city';
            $state='No state';
            $zip='No zip';
        }
        
        //$output='"Salesperson","Phone","Account Name","Account Other","Street","City","State","Zip","First Words", 
        //"Publications","Category","Classification","Ad Number","Words","Times Run","Start","Stop","Contact",
        //"Width","Height","Inches/Lines","Rate","Price","Status","Pubs Run"'."\n";
        if($accountName!='' && $salesperson!='')
        { 
            $output.='"'.$salesperson.'",';
            $output.='"'.$phone.'",';
            $output.='"'.$accountName.'",';
            $output.='"'.$accountOther.'",';
            $output.='"'.$street.'",';
            $output.='"'.$city.'",';
            $output.='"'.$state.'",';
            $output.='"'.$zip.'",';
            $output.='"'.$firstWords.'",';
            $output.='"'.$publications.'",';
            $output.='"'.$category.'",';
            $output.='"'.$subName.'",';
            $output.='"'.$adNumber.'",';
            $output.='"'.$wordCount.'",';
            $output.='"'.$timesRun.'",';
            $output.='"'.$start.'",';
            $output.='"'.$stop.'",';
            $output.='"'.$contact.'",';
            $output.='"'.$width.'",';
            $output.='"'.$height.'",';
            $output.='"'.$inches.'",';
            $output.='"'.$rate.'",';
            $output.='"'.$price.'",';
            $output.='"'.$status.'"'."\n";
        }
    }

    $outfile="IPT-rangerData-".date("Y-m-d").".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$outfile.'"');
    print $output;
    dbclose();
} else {
    include("includes/mainmenu.php");
    print "<form method=post enctype='multipart/form-data'>\n";
    make_file('vdata','Vision Data File');
    make_submit('submit','Process File');
    print "</form>\n";
    footer();
}
  

?>
