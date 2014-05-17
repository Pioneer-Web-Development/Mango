<?php
//<!--VERSION: .91 **||**-->
  $routebreak="###";
  //address fixer thingy
  $sources=array("Bellingham","Stanwood Advertiser","Standwood News");
  if ($_POST)
  {
      $outfile=$_POST['output'];
      if (strtolower(substr($outfile,strlen($outfile)-6,6))!='.a.txt')
      {
          $outfile=explode(".",$outfile);
          $outfile=$outfile[0].".A.txt";
      } elseif (substr($outfile,strlen($outfile)-6,6)=='.a.txt')
      {
          $outfile=explode(".",$outfile);
          $outfile=$outfile[0].".A.txt";
      }
      if ($_POST['source']==0)
      {
         process_bellingham($outfile); 
      } elseif($_POST['source']==1) {
         process_stanwoodadvertiser($outfile); 
      } elseif($_POST['source']==2)
      {
         process_stanwoodnews($outfile);  
      } 
  } else {
    include("includes/mainmenu.php");
    print "<form method=post enctype='multipart/form-data'>\n";
    make_file('original','Source','Please choose the source file to convert to Miracom format.');
    make_text('output','','Output name','What name should be used to save the file?');
    make_select('source',$sources[0],$sources,'Choose filter type','Choose the proper filter to create the label file');
    make_submit('submit','Process');
    print "</form>\n";
    footer();
  }
  
  function process_bellingham($outfile)
  {
      global $routebreak;
      $file=$_FILES['original']['tmp_name'];
      $contents=file_get_contents($file);
      //break into multiple lines
      $contents=explode("\n",$contents);
      $output="";
      $l6filler="          ";
      $length_1=40;
      $length_2=40;
      $length_3=40;
      $length_4=40;
      $length_5=40;
      $length_6=10;
      $recordnum=1;
      $cline='';
      $linecount=count($contents);
      $rb=false;
      header('Content-Type: text/plain'); // plain text file
      header('Content-Disposition: attachment; filename="'.$outfile.'"');
      
      
      for ($i=0;$i<=$linecount;$i++)
      {
          $line=$contents[$i];
          $line=str_replace("\r\n","",$line);
          $line=str_replace("\r","",$line);
          $line=str_replace("\n","",$line);
            
          if($i<$linecount-1)
          {
              $temp=$contents[$i+1];
              $temp=explode(",",$temp);
              $nextline=$temp[0];
          } else {
              $nextline='end';
          }
          if (substr($line,0,10)!="0123456789")
          {
            $lineitems=explode(",",$line);
            //print_r($lineitems);
            $line1=$lineitems[0];
            $line2=$lineitems[1];
            $line3=$lineitems[5];
            $line4=$lineitems[7].' '.$lineitems[8];
            $line5=$lineitems[9].",".$lineitems[10].$lineitems[11].$lineitems[12];
            if ($line1!=$routebreak)
            {
                $recordnum++;
            }
            $line6="$recordnum";
            while (strlen($line6)<$length_6)
            {
                $line6=" ".$line6;
            }
            $line6.=$l6filler;
            while (strlen($line1)<$length_1)
            {
                $line1.=" ";
            }
            while (strlen($line2)<($length_2-strlen($line6)))
            {
                $line2.=" ";
            }
            $line2.=$line6;
            while (strlen($line3)<$length_3)
            {
                $line3.=" ";
            }
            while (strlen($line4)<$length_4)
            {
                $line4.=" ";
            }
            while (strlen($line5)<$length_5)
            {
                $line5.=" ";
            }
            
            //this block puts the routebreak on the last record of the route
            if (trim($line1)!=trim($nextline) && $nextline!='end')
            {    
                $line1=substr($line1,0,strlen($line1)-strlen($routebreak)).$routebreak;
                print $line1.$line2.$line3.$line4.$line5.$routebreak."\r\n";
            } else {
                print $line1.$line2.$line3.$line4.$line5."\r\n";
            }
            
          }
       
      }
      
           
  }
  
  function process_stanwoodadvertiser($outfile)
  {
      global $routebreak;
      $file=$_FILES['original']['tmp_name'];
      $contents=file_get_contents($file);
      //break into multiple lines
      $contents=explode("\n",$contents);
      $output="";
      $l6filler="   ";
      $length_1=40;
      $length_2=40;
      $length_3=40;
      $length_4=40;
      $length_5=40;
      $length_6=5;
      $recordnum=1;
      $cline='';
      $linecount=count($contents);
      header('Content-Type: text/plain'); // plain text file
      header('Content-Disposition: attachment; filename="'.$outfile.'"');
      $rb=false;
      
      
      for ($i=0;$i<=$linecount;$i++)
      {
          $line=$contents[$i];
          if (trim($line)!=",,,,,,,,," && trim($line)!='' && strpos($line,"Label Count")==0)
          {
            $lineitems=explode(",",$line);
            $line1=$lineitems[0];
            $line2=$lineitems[1];
            $line3=$lineitems[3];
            $line4=$lineitems[4];
            $line5=$lineitems[6].",".$lineitems[7]." ".$lineitems[8];
            if($i<$linecount-1)
            {
                $temp=$contents[$i+1];
                $temp=explode(",",$temp);
                $nextline=$temp[0];
            } else {
                $nextline='end';
            }
            $line6="$recordnum";
            if (trim($line1)!='')
            {
                if ($line1!=$routebreak)
                {
                    $recordnum++;
                }
                $line6="$recordnum";
                while (strlen($line6)<$length_6)
                {
                    $line6=" ".$line6;
                }
                $line6.=$l6filler;
                while (strlen($line1)<$length_1)
                {
                    $line1.=" ";
                }
                while (strlen($line2)<($length_2-strlen($line6)))
                {
                    $line2.=" ";
                }
                $line2.=$line6;
                while (strlen($line3)<$length_3)
                {
                    $line3.=" ";
                }
                while (strlen($line4)<$length_4)
                {
                    $line4.=" ";
                }
                while (strlen($line5)<$length_5)
                {
                    $line5.=" ";
                }
                
                //this block puts the routebreak on the last record of the route
                if (trim($line1)!=trim($nextline) && $nextline!='end')
                {    
                    $line1=substr($line1,0,strlen($line1)-strlen($routebreak)).$routebreak;
                    print $line1.$line2.$line3.$line4.$line5.$routebreak."\r\n";
                } else {
                    print $line1.$line2.$line3.$line4.$line5."\r\n";
                }
                /*  //this block puts the routebreak on the first record of the new route
                if ($rb)
                {
                    print $line1.$line2.$line3.$line4.$line5.$routebreak."\r\n";
                } else {
                    print $line1.$line2.$line3.$line4.$line5."\r\n";
                }
                if (trim($line1)!=trim($nextline) && $nextline!='end')
                {
                    $rb=true;     
                } else {
                    $rb=false;
                }
                */
            }
         }
           
      }
     
  }
  
  function process_stanwoodnews($outfile)
  {
      global $routebreak;
      $file=$_FILES['original']['tmp_name'];
      $contents=file_get_contents($file);
      //break into multiple lines
      
      $contents=explode("\n",$contents);
      $output="";
      $l6filler="   ";
      $length_1=40;
      $length_2=40;
      $length_3=40;
      $length_4=40;
      $length_5=40;
      $length_6=5;
      $length_7=40;
      $recordnum=1;
      $cline='';
      $linecount=count($contents);
      header('Content-Type: text/plain'); // plain text file
      header('Content-Disposition: attachment; filename="'.$outfile.'"');
      $rb=false;
      
      
      for ($i=0;$i<=$linecount;$i++)
      {
          $line=$contents[$i];
          $line=str_replace("\"","",$line);
          if (trim($line)!=",,,,,,,,," && trim($line)!='' && strpos($line,"Label Count")==0)
          {
            $lineitems=explode(",",$line);
            $line1=$lineitems[0];
            $line2=$lineitems[1];
            $line3=$lineitems[2];
            $line4=$lineitems[4];
            $line5=$lineitems[6].",".$lineitems[7]." ".$lineitems[8];
            $line7=trim($lineitems[24]);
            
            if($i<$linecount-1)
            {
                $temp=$contents[$i+1];
                $temp=explode(",",$temp);
                $nextline=$temp[0];
                $nextline=str_replace("\"","",$nextline);
            } else {
                $nextline='end';
            }
            $line6="$recordnum";
            if (trim($line1)!='')
            {
                if ($line1!=$routebreak)
                {
                    $recordnum++;
                }
                $line6="$recordnum";
                while (strlen($line6)<$length_6)
                {
                    $line6=" ".$line6;
                }
                $line6.=$l6filler;
                while (strlen($line1)<$length_1)
                {
                    $line1.=" ";
                }
                while (strlen($line2)<($length_2-strlen($line6)))
                {
                    $line2.=" ";
                }
                $line2.=$line6;
                while (strlen($line3)<$length_3)
                {
                    $line3.=" ";
                }
                while (strlen($line4)<$length_4)
                {
                    $line4.=" ";
                }
                while (strlen($line5)<$length_5)
                {
                    $line5.=" ";
                }
                while (strlen($line7)<$length_7)
                {
                    $line7.=" ";
                }
                
                //this block puts the routebreak on the last record of the route
                if (trim($line1)!=trim($nextline) && $nextline!='end')
                {    
                    $line1=substr($line1,0,strlen($line1)-strlen($routebreak)).$routebreak;
                    print $line1.$line2.$line3.$line4.$line5.$line7.$routebreak."\r\n";
                } else {
                    print $line1.$line2.$line3.$line4.$line5.trim($line7)."\r\n";
                }
                
            }
         }
           
      }
     
  }
?>
