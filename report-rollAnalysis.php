<?php
//<!--VERSION: .9 **||**-->

  //summary report
  //this report duplicates the function in emailsummary.php
  

global $pubs, $siteID;
//make sure we have a logged in user...

if ($_POST)
{
    if ($_POST['submit']=='Generate report')
    {
        include("includes/mainmenu.php");
    } else {
         include ("includes/functions_db.php");
         include ("includes/config.php");
    }
    $pubid=$_POST['pubid'];
    if ($pubid!=0)
    {
        $pub="AND A.pub_id=$pubid";
    } else {
        $pub="";
    }
    $startdate=$_POST['startdate'];
    $enddate=$_POST['enddate'];
    $sql="SELECT A.*, C.pub_name, D.run_name FROM jobs A, publications C, publications_runs D WHERE A.site_id=$siteID AND A.continue_id<>0 AND A.pub_date>='$startdate' AND A.pub_date<='$enddate' $pub AND A.pub_id=C.id AND A.run_id=D.id ORDER BY A.pub_id, A.pub_date ASC";
    //print "Searching with $sql<br />\n";
    $dbJobs=dbselectmulti($sql);
    if ($dbJobs['numrows']>0)
    {
        if ($_POST['submit']=='Generate report')
        {
            build_report('onscreen',$dbJobs['data'],$startdate,$enddate);
        } else {
            build_report('excel',$dbJobs['data'],$startdate,$enddate);
        }   
    } else {
        print "Sorry, there are no matching jobs. <a href='report-counter.php'>Please try searching with different criteria.</a>\n";
    }
    
} else {
    include ("includes/mainmenu.php");
    if (!checkPermission($_SERVER['SCRIPT_NAME'])){redirect('default.php?accesserror=true');}
    global $pubs;
    print "<body>\n";
    print "<div id='wrapper'>\n";
    
    print "<form method=post>\n";
    $startdate=date("Y-m-d",strtotime("-1 month"));
    $enddate=date("Y-m-d");
    make_select('pubid',$pubs[0],$pubs,'Choose publication');
    make_date('startdate',$startdate,'Select Start Pub Date');
    make_date('enddate',$enddate,'Select Ending Pub Date');
    make_submit('submit','Generate report');
    make_submit('submit','Generate report as excel');
    print "</form>\n";
    print "</div>\n";
print "</body>\n";
} 


function build_report($output,$jobs,$startdate,$enddate)
{
    global $papertypes, $sizes;
    
    $reportname="Publication roll consumption report from $startdate to $enddate"; 
    if ($output=='excel')
   {
        
        $filename="Counter_report";
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=".$filename.".xls;");
        header("Content-Type: application/ms-excel");
        header("Pragma: no-cache");
        header("Expires: 0");
    
      
       $tablestart="<?xml version='1.0'?>
    <?mso-application progid='Excel.Sheet'?>
    <Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet' xmlns:html='http://www.w3.org/TR/REC-html40'>
    <Worksheet ss:Name='$reportname'>
    <Table>";
       $tableend="</Table></Worksheet></Workbook>";


       $printer="";
       $rowstart="<Row>";
       $rowend="</Row>";
       $cellstart="<Cell><Data ss:Type='String'>";
       $cellend="</Data></Cell>";
       $break="\r\n";
   } else {
       $css="<link href='styles/pims_main.css' rel='stylesheet' type='text/css'>";
        
       $tablestart="<htmln<head>\n$css\n</head>\n<body>\n<table class='grid'>\n<tr>\n<th><a href='#' onclick='window.print();'><img src='artwork/printer.png' width=32 border=0>Print</a></th><th colspan=7>$reportname</th>\n</tr>\n";
       $tableend="</table></body>\n</html>\n";
       $rowstart="<tr>";
       $rowend="</tr>\n";
       $cellstart="<td>";
       $cellend="</td>";
       $break="<br />\n";
   }
   $rollusage=array();
   //lets look at rolls and calculate an average weight per size and type
   $i=0;
   foreach($sizes as $sid=>$size)
   {
       if ($size!='Please choose')
       {
           foreach($papertypes as $pid=>$papertype)
           {
               if ($papertype!='Please choose')
               {
                   $sql="SELECT AVG(roll_weight) as avgweight FROM rolls WHERE common_name='$papertype' AND roll_width=$size";
                   $dbRoll=dbselectsingle($sql);
                   if ($dbRoll['numrows']>0)
                   {
                       $avgweight=round($dbRoll['data']['avgweight'],2);
                       $rollusage[$size][$papertype]['avgweight']=$avgweight;
                       $rollusage[$size][$papertype]['tonnage']=0;
                       $i++;                               
                   }
               }
           }
       }
   }

   
   
   //first, lets create the header and headings
   print $tablestart;
   print "$rowstart";
   print "$cellstart Pub Name $cellend";
   print "$cellstart Job Name $cellend";
   print "$cellstart Pub Date $cellend";
   foreach($sizes as $sid=>$size)
   {
       if ($size!='Please choose')
       {
        print "$cellstart $size\" $cellend";     
       }
       
   }
   print $rowend;
   
   $i=0;
   
   foreach($jobs as $job)
   {
       
       print $rowstart;
       $pubname=$job['pub_name'];
       print $cellstart.$job['pub_name'].$cellend;
       print $cellstart.$job['run_name'].$cellend;
       print $cellstart.date("D, F d Y",strtotime($job['pub_date'])).$cellend;
       foreach($sizes as $sid=>$size)
       {
           if ($size!='Please choose')
           {
               print $cellstart;
               foreach ($papertypes as $pid=>$papertype)
               {
                   if ($pid!=0)
                   {
                   $sql="SELECT SUM(calculated_tonnage) as tons FROM job_paper WHERE job_id=$job[id] AND papertype_id=$pid and roll_width=$size";
                   //print "Roll sql $sql<br />\n";
                   $dbRolls=dbselectsingle($sql);
                   $tons=$dbRolls['data']['tons'];
                   if ($tons>0)
                    {
                        print "$papertype: $tons MT$break";
                        $c=$rollusage[$size][$papertype]['tonnage'];
                        $rollusage[$size][$papertype]['tonnage']+=$tons;
                    }
                   }    
               }
           }
           print $cellend;
       }
       
       print $rowend;
   }
   print $rowstart;
         print $cellstart;
         print $cellend;
         print $cellstart;
         print $cellend;
         print $cellstart;
         print "Total by size:";
         print $cellend;
         
        foreach($sizes as $sid=>$size)
        {
           if ($size!='Please choose')
           {
               print $cellstart;
               foreach ($papertypes as $pid=>$papertype)
               {
                   if ($pid!=0)
                   {
                    $tons=$rollusage[$size][$papertype]['tonnage'];
                    if ($tons>0)
                        {
                        print "$papertype: ";
                        $average=$rollusage[$size][$papertype]['avgweight'];
                        print $tons;
                        print " MT$break";
                        if ($average>0)
                        {
                            $rolls=round($tons*1000/$average,0);
                            print "$rolls roll eq.$break";
                        }
                    } else {
                        $tons=0;
                    }
                    $ptonnage[$papertype]['tonnage']+=$tons;
                    
                   }    
               }
           }
           print $cellend;
       }
       print $rowend;
       print $rowstart;
       print $cellstart;
       print $cellend;
       print $cellstart;
       print $cellend;
       print $cellstart;
       print "Summary of tonnage by type:";
       print $cellend;
       print $cellstart;
       foreach($ptonnage as $papertype=>$rolls)
       {
           $tons=$rolls['tonnage'];
           print "$papertype: $tons MT<br />\n";
       }
       print $cellend;
       print $rowend; 
   print $tableend; 
}
 
dbclose();  
?>
