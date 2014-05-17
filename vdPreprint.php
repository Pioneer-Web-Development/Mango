<?php
  //this script parses a vision data ad-edit preprint manifest text file into it's proper parts
  if($_POST)
  {
      process_vdPreprintfile();
  } else {
    include("includes/mainmenu.php");
    print "<form method=post enctype='multipart/form-data'>\n";
    make_file('vdfile','Preprint Manifest','Please select the vision data manifest file to upload.');
    make_submit('submit','Process');
    print "</form>\n";
    print "</div>\n";
    footer();
  }
  
function  process_vdPreprintfile()
{
    if(isset($_FILES))
    {
        $file=$_FILES['vdfile']['tmp_name'];
        $contents=file_get_contents($file);
        
        $lines=explode("\n",$contents);
        $badlines=array("Print","AR Ad","Sorte","Profi","-----","Accou");
        $i=0;
        foreach($lines as $line)
        {
            $line=trim($line);
            if(substr($line,0,7)!='Account' && $line!='')
            {
                $lineitems=explode(chr(9),$line);
                $ad[$i]['account_number']=trim($lineitems[0]);    
                $ad[$i]['account_name']=trim($lineitems[1]);    
                $ad[$i]['telephone']=trim($lineitems[2]);    
                $ad[$i]['ad_number']=trim($lineitems[3]);    
                $ad[$i]['run_date']=trim($lineitems[5]);    
                $ad[$i]['publication']=trim($lineitems[12]);    
                $ad[$i]['zone']=trim($lineitems[13]);    
                $ad[$i]['edition']=trim($lineitems[15]);    
                $ad[$i]['section']=trim($lineitems[16]);    
                $ad[$i]['sales']=trim($lineitems[26]);    
                $ad[$i]['description']=trim($lineitems[30])." ".trim($lineitems[31]);    
                $ad[$i]['po_number']=trim($lineitems[32]);    
                $ad[$i]['pages']=trim($lineitems[33]);    
                $ad[$i]['quantity']=trim($lineitems[34]);    
                $ad[$i]['misc']=trim($lineitems[35]);
                $i++;    
            }
        }
        if(count($ad)>0)
        {
            print "<pre>";
            print_r($ad);
            print "</pre><br><br>";    
        }     
    }
    die();
    
}
?>
</body>
</html>