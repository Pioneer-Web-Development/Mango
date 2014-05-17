<?php
  $jobid=$_POST['jobid'];
  $type=$_POST['type'];
  
  
  include('../functions_common.php');
  
  //first, we see if there is a cached version, and serve that, otherwise, generate one
  $cache=checkCache('jobBoxes'.$jobid,$type);
  if($cache)
  {
      print $cache;
      die();
  }
  include('../functions_db.php');
  if ($type=='remakes')
  {
    $sql="SELECT DISTINCT(page_number), section_code, version, workflow_receive FROM job_pages WHERE job_id=$jobid AND remake=1 ORDER BY page_number ASC, version DESC";
    $dbPages=dbselectmulti($sql);
    if ($dbPages['numrows']>0)
    {
        $response.="<table >\n";
        $response.="<tr><th>Section</th><th>Page</th><th>Version</th><th>Receive Time</th></tr>\n";
        foreach($dbPages['data'] as $page)
        {
            $response.= "<tr>\n";
            $response.= "<td>".$page['section_code']."</td>\n";
            $response.= "<td>".$page['page_number']."</td>\n";
            $response.= "<td>".$page['version']."</td>\n";
            $response.= "<td>";
            if ($page['workflow_receive']!='')
            {
                $response.= date("H:i:s",strtotime($page['workflow_receive']));
            } else {
                $response.= "Not received";
            }
            $response.= "</td>";
            $response.= "</tr>\n";
        
        }
        $response.= "</table>\n";
    } else {
        $response.= "No remakes at this time.";
    }
  }
  if ($type=='plateremakes')
  {
    $sql="SELECT DISTINCT(low_page), version, section_code, black_ctp FROM job_plates WHERE version>1 AND job_id=$jobid ORDER BY low_page ASC, version DESC";
    $dbPages=dbselectmulti($sql);
    if ($dbPages['numrows']>0)
    {
        $response.= "<table>\n";
        $response.= "<tr><th>Section</th><th>Plate</th><th>Version</th><th>Output Time</th></tr>\n";
        foreach($dbPages['data'] as $page)
        {
            $response.= "<tr>\n";
            $response.= "<td>".$page['section_code']."</td>\n";
            $response.= "<td>".$page['low_page']."</td>\n";
            $response.= "<td>".$page['version']."</td>\n";
            $response.= "<td>";
            if ($page['black_ctp']!='')
            {
                $response.= date("H:i:s",strtotime($page['black_ctp']));
            } else {
                $response.= "Not received";
            }
            $response.= "</td>";
            $response.= "</tr>\n";
        
        }
        $response.= "</table>\n";
    } else {
        $response.= "No remakes at this time.";
    }
  }
  
  if ($type=='missingpages')
  {
    $sql="SELECT DISTINCT(page_number), section_code, page_number, color FROM job_pages WHERE job_id=$jobid AND page_release is Null ORDER BY section_code ASC, page_number ASC";
    $dbPages=dbselectmulti($sql);
    if ($dbPages['numrows']>0)
    {
        $response.= "<table>\n";
        $response.= "<tr><th>Section</th><th>Page</th><th>Color</th></tr>\n";
        foreach($dbPages['data'] as $page)
        {
            $response.= "<tr>\n";
            $response.= "<td>".$page['section_code']."</td>\n";
            $response.= "<td>".$page['page_number']."</td>\n";
            if ($page['color']==1)
            {
                $color="Full Color";
            }elseif($page['spot']==1) {
                $color="Spot";
            }else{
                $color="Black";
            }
            $response.= "<td>$color</td>";
            $response.= "</tr>\n";
        }
        $response.= "</table>\n";
    } else {
        $response.= "All pages have been received.";
    }
  
  }
  
  if ($type=='missingplates')
  {
    $sql="SELECT DISTINCT(low_page), section_code, low_page, color FROM job_plates WHERE job_id=$jobid AND black_receive is Null AND black_ctp is Null ORDER BY section_code ASC, low_page ASC";
    $dbPages=dbselectmulti($sql);
    if ($dbPages['numrows']>0)
    {
        $response.= "<table>\n";
        $response.= "<tr><th>Section</th><th>Plate</th><th>Color</th></tr>\n";
        foreach($dbPages['data'] as $page)
        {
            $response.= "<tr>\n";
            $response.= "<td>".$page['section_code']."</td>\n";
            $response.= "<td>".$page['low_page']."</td>\n";
            if ($page['color']==1)
            {
                $color="Full Color";
            }elseif($page['spot']==1) {
                $color="Spot";
            }else{
                $color="Black";
            }
            $response.= "<td>$color</td>";
            $response.= "</tr>\n";
        }
        $response.= "</table>\n";
    } else {
        $response.= "All plates have been received.";
    }
  
  }
  
  setCache('jobBoxes'.$jobid,$type,$response);
  
  print $response;