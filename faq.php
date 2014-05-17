<!DOCTYPE html>
<html>
<head>
<style type='text/css'>
body{
    font-family: Trebuchet MS, Arial, sans-serif;
    font-size:12px;
    padding:10px;
}
.redbutton{
    width:150px;
    height:40px;
    background-color: #FFDB4F;
    color:#AC1D23;
    font-family:Trebuchet MS;
    font-weight:bold;
    font-size:16px; 
}
.redbutton:hover
{
    background-color: #AC1D23;
    color:#FFDB4F; 
}
</style>
<script language="javascript" type="text/javascript" src="includesjscripts/jquery-1.7.2.min.js"></script>
<script language="javascript" type="text/javascript" src="includesjscripts/jquery-ui-1.8.20.custom.min.js"></script>
<script language="javascript" type="text/javascript" src="includesjscripts/jquery.cleditor.js"></script>
<link rel='stylesheet' type='text/css' href='/styles/jquery-ui-1.8.20.custom.css' />
        
</head>
<body>
<?php
  //faq system
  //this exists in a popup window
  //start off with a search tool for keywords, and a list of top level topics
  include("includes/functions_db.php");
  
  if ($_POST['submit']=='Search')
  {
      $keywords=addslashes($_POST['keywords']);
      $keywords=str_replace(";"," ",$keywords);
      $keywords=str_replace(","," ",$keywords);
      $keywords=str_replace("  "," ",$keywords);
      $keywords=explode(" ",$keywords); 
      if (count($keywords)>1)
      {
          $keycheck="";
          foreach($keywords as $key)
          {
              $keycheck.="keywords LIKE '%$key%' OR ";
          }
          $keycheck=substr($keycheck,0,strlen($keycheck)-4);
      } else {
          $keywords=$keywords[0];
          $keycheck="keywords LIKE '%$keywords%'";
      }
      $sql="SELECT * FROM faq WHERE $keycheck";
      $dbFaqs=dbselectmulti($sql);
      if ($dbFaqs['numrows']>0)
      {
          print "<h2>The following faq topics match some or all of your keywords:</h2>\n";
          
          foreach($dbFaqs['data'] as $faq)
          {
              //check each one to see if there are sub items, if so the href will differ
              $sql="SELECT * FROM faq WHERE parentid=$faq[id]";
              $dbSub=dbselectmulti($sql);
              if ($dbSub['numrows']>0)
              {
                  print "<li><a href='?action=sub&topicid=$faq[id]'>$faq[title]</a></li>\n";
              } else {
                  print "<li><a href='?action=view&topicid=$faq[id]'>$faq[title]</a></li>\n";
              }
          }
      } else {
          print "<a href='?action=default'>Sorry, there appears to be nothing available for that topic.</a>\n";
      }
  } else {
      if ($_GET['action']=='view')
      {
          show_topic($_GET['topicid']);
      }elseif ($_GET['action']=='sub'){
          //show a list of sub topics
          $sql="SELECT * FROM faq WHERE parentid=$_GET[topicid]";
          $dbFaqs=dbselectmulti($sql);
          if ($dbFaqs['numrows']>0)
          {
              print "<h2>The following help topics are available:</h2>\n";
              
              foreach($dbFaqs['data'] as $faq)
              {
                  
                  //check each one to see if there are sub items, if so the href will differ
                  $sql="SELECT * FROM faq WHERE parentid=$faq[id]";
                  $dbSub=dbselectmulti($sql);
                  if ($dbSub['numrows']>0)
                  {
                      print "<li><a href='?action=sub&topicid=$faq[id]'>$faq[title]</a></li>\n";
                  } else {
                      print "<li><a href='?action=view&topicid=$faq[id]'>$faq[title]</a></li>\n";
                  }
              }
          } else {
              print "<a href='?action=default'>Sorry, there appears to be nothing available for that topic.</a>\n";
          }
      } else {
          //show the inital form
          print "<div style='float:left;width:150px;'><img src='artwork/mango.png' border=0 width=140'></div>\n";
          print "<div style='float:left;margin-top:20px;font-size:24px;font-weight:bold;color:#AC1D23'>Welcome to the help system.\n</div>\n";
          print "<div style='clear:both;height:0px;width:0px;'></div>\n";
          print "<form method=post>\n";
          print "<h3>Please enter a keyword to search for, or select a topic from the list below.</h3>\n";
          print "Keyword: <input type='text' name='keywords' value=''>\n";
          print "<input type='submit' name='submit' value='Search' class='redbutton'>\n";
          print "</form>\n"; 
          $sql="SELECT * FROM faq WHERE parentid=0";
          $dbFaqs=dbselectmulti($sql);
          if ($dbFaqs['numrows']>0)
          {
              print "<h2>The following faq topics are available:</h2>\n";
              
              foreach($dbFaqs['data'] as $faq)
              {
                  
                  //check each one to see if there are sub items, if so the href will differ
                  $sql="SELECT * FROM faq WHERE parentid=$faq[id]";
                  $dbSub=dbselectmulti($sql);
                  if ($dbSub['numrows']>0)
                  {
                      print "<li><a href='?action=sub&topicid=$faq[id]'>$faq[title]</a></li>\n";
                  } else {
                      print "<li><a href='?action=view&topicid=$faq[id]'>$faq[title]</a></li>\n";
                  }
              }
          }
          
      } 
  }
  
  
  
  function show_topic($topicid)
  {
    print "<div style='float:left;'><small><a href='?action=default'>Return to main</a></small></div>\n";
    print "<div style='float:right;'>
    <a href='#' onclick='window.print();'><img src='artwork/printer.png' width=32 border=0'>Print this FAQ</a>
    </div><div style='clear:both;'></div>\n";  
    
    $sql="SELECT * FROM faq WHERE id=$topicid";
    $dbFaq=dbselectsingle($sql);
    $faq=$dbFaq['data'];
    print "<h2>".stripslashes($faq['title'])."</h2>\n";
    print stripslashes($faq['faq_text']);    
    
    $sql="SELECT * FROM faq_steps WHERE faq_id=$topicid ORDER BY step_order";
    $dbSteps=dbselectmulti($sql);
    if($dbSteps['numrows']>0)
    {
        print "<ol>\n";
        foreach($dbSteps['data'] as $step)
        {
            print "<li><span style='font-weight:bold;'>".stripslashes($step['step_title'])."</span><br>";
            //are there any images for this step?
            
            $sql="SELECT * FROM faq_step_images WHERE step_id='$step[id]' ORDER BY image_order";
            $dbImages=dbselectmulti($sql);
            if($dbImages['numrows']>0)
            {
                print "<div style='float:right;width:200px;margin-left:10px;margin-bottom:10px;'>\n";
                foreach($dbImages['data'] as $image)
                {
                    print "<img src='artwork/faq/$image[image]' alt='$image[image]' width=200 /><br>";
                    print "<small>".stripslashes($image['caption'])."</small><br><hr>";    
                }
                print "</div>\n";   
            }
            print stripslashes($step['step_text']);
            print "</li>\n";
            
        }
        print "</ol>\n";
    }
      
  }
 
 dbclose(); 
?>
</body>
</html>