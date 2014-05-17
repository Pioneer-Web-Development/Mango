<?php
include("includes/mainmenu.php") ;

if($_POST)
{
    $action=$_POST['submit'];
} else {
    $action=$_GET['action'];
}

switch ($action)
{
    case "Add":
    save_news('insert');
    break;
    
    case "Update":
    save_news('update');
    break;
    
    case "export":
    export();
    break;
    
    case "add":
    show_news('add');
    break;
    
    case "edit":
    show_news('edit');
    break;
    
    case 'delete':
    delete_news('delete');
    break;
    
    case 'list':
    list_news();
    break;
    
    default:
    list_news();
    break;
    
}

function show_news($action)
{
    //get all editorial folks
    $sql="SELECT A.* FROM users A, core_preferences B WHERE A.department_id=B.editorialDepartmentID ORDER BY A.lastname";
    $dbReporters=dbselectmulti($sql);
    $reporters=array();
    $reporters[0]='Please choose';
    if($dbReporters['numrows']>0)
    {
        foreach($dbReporters['data'] as $reporter)
        {
            $reporters[$reporter['id']]=stripslashes($reporter['lastname'].' ,'.$reporter['firstname']);
        }
    }
    
    if ($action=='add')
    {
        $button='Add';
        $authorid=$_SESSION['cmsuser']['userid'];
        
    } else {
        $id=intval($_GET['id']);
        $sql="SELECT * FROM editorial_article_body WHERE id=$id";
        $dbNews=dbselectsingle($sql);
        $news=$dbNews['data'];
        $by=stripslashes($news['post_by']);
        $headline=stripslashes($news['headline']);
        $articleBody=stripslashes($news['article']);
        $pubdate=$news['pub_date'];
        $authorid=$news['author_id'];
        $button="Update";
    }
     print "<div id='articleTabs'>\n";
    print "<ul>
            <li><a href='#articletext'>Article</a></li>
            <li><a href='#media'>Associated Media</a></li>
            <li><a href='#meta'>Meta Data</a></li>
            <li><a href='#tagging'>Flags &amp; Sections</a></li>
            ";
    print "</ul>\n";
       print "<form method=post>\n";
       print "<div id='articletext'>";
            make_select('authorid',$reporters[$authorid],$reporters,'Author','Who wrote this article?');
            make_date('pubdate',$pubdate,'Pub date','When will this article publish?');
            
            make_text('headline',$headline,'Headline','',50);
            print "<div class='label'>News</div><div class='input'>";
            print "<textarea id='articleBody' name='articleBody' cols=80 rows=40>$articleBody</textarea>";
            print "</div><div class='clear'></div>";
       print "</div>";
       
       print "<div id='meta'>";
       
       print "</div>";
       
       print "<div id='media'>";
       /*
       This will be a place to upload multiple items.
       */
       $sql="SELECT * FROM editorial_media WHERE article_id=$id";
       $dbMedia=dbselectmulti($sql);
       print "<div style='float:left;width:500px;'>\n";
       if($dbMedia['numrows']>0)
       {
           foreach($dbMedia['data'] as $media)
           {
               
           }
       }
       print "</div>\n";
       print "<div class='ui-widget ui-widget-content ui-corner-all' style='float:right;margin-right:80px;width:400px;padding:10px;'>\n";
       print "<p class='ui-widget-header ui-corner-all' style='padding:10px;'>Media Editor</p>";
       //on the editing side we are going to have different forms for each media type. Editing an object will expose the 
       //correct one, as will selecting it from the add new dropdown.
       //we will toggle the display with javascript as well as set a variable for which media type is active
       //all updates will be handled via javascript.
       
       //any file over 5mb is going to be flagged and refused. An alert to the user saying that they will need to FTP the file
       //manually to TownNews 
       print "Add a new media file: <select id='newmedia' onChange='toggleMedia();'>";
       print "<option value='image'>Image</option>\n";
       print "<option value='gallery'>Gallery</option>\n";
       print "<option value='audio'>Audio</option>\n";
       print "<option value='link'>Link</option>\n";
       print "<option value='html'>HTML Snippet</option>\n";
       print "</select>\n";
       
       print "<form method='post' enctype='multipart/form-data'>\n";
       //images
       print "<div id='media_image' style='display:none;'>\n";
       print "<p class='ui-widget-header ui-corner-all' style='padding:10px;width:100px;'>Images</p>";
       make_textarea('image_caption','','Caption','Caption for the photo');
       make_date('image_publish_date',date("Y-m-d"),'Publish Date','When should this photo be available?');
       make_date('image_archive_date',date("Y-m-d"),'Archive Date','When should this photo be archived?');
       
       print "</div>\n";
       
       //gallery
       print "<div id='media_gallery' style='display:none;'>\n";
       print "<p class='ui-widget-header ui-corner-all' style='padding:10px;width:100px;'>Gallery</p>";
       
       print "</div>\n";
       
       //audio
       print "<div id='media_audio' style='display:none;'>\n";
       print "<p class='ui-widget-header ui-corner-all' style='padding:10px;width:100px;'>Audio</p>";
       
       print "</div>\n";
       
       //link
       print "<div id='media_link' style='display:none;'>\n";
       print "<p class='ui-widget-header ui-corner-all' style='padding:10px;width:100px;'>Link</p>";
       
       print "</div>\n";
       
       //html
       print "<div id='media_html' style='display:none;'>\n";
       print "<p class='ui-widget-header ui-corner-all' style='padding:10px;width:100px;'>HTML Snippet</p>";
       
       print "</div>\n";
       
       //close form after all divs, so there is only one form an one form submit button
       print "<input type='hidden' id='mediatype' value=''>\n";
       print "<br><input type='submit' id='submitter' name='submitter' value='Save Media' onclick='saveMedia()' />\n";
       print "</form>\n";
       
       print "</div>\n";
       print "<div class='clear'></div>\n";
       
       print "</div>";
       
       print "<div id='tagging'>";
       print "</div>";
     print "</div>";  
        make_submit('submit',$button);
        make_hidden('id',$id);
        print "</form>\n";
        
      ?>
    <script>
        $(function() {
            $( "#articleTabs" ).tabs();
          });
            $("#articleBody").htmlarea({
                toolbar: [
                        ["bold", "italic", "underline", "strikethrough", "|", "subscript", "superscript"],
                        ["increasefontsize", "decreasefontsize"],
                        ["orderedlist", "unorderedlist"],
                        ["indent", "outdent"],
                        ["justifyleft", "justifycenter", "justifyright"],
                        ["link", "unlink", "horizontalrule"],
                        ["cut", "copy", "paste"], ["html"],
                        // custom spellcheck button
                        [{
                                css: "check-spelling-button",
                                text: "Check spelling",
                                action: function(btn) {
                                        // initiate the spellchecker
                                        $(this.editor.body)
                                        .spellchecker({
                                                url: "includesajax_handlers/checkspelling.php",
                                                lang: "en", 
                                                engine: "google",
                                                suggestBoxPosition: "below",
                                                innerDocument: false,
                                                wordlist: {
                                                        action: "after",
                                                        element: $(".jHtmlArea")
                                                },  
                                        })
                                        .spellchecker("check", function(result){
                                                (result) && alert('There are no incorrectly spelled words.');
                                        });
                                }
                        }]
                ]
            });    
            function save_contents()
            {
                //$.cleditor.updateTextArea;
                $("#articleBody").htmlarea("updateTextArea");
                var text=$('#articleBody').val();
                var articleid=$('#id').val();
                if(text!='')
                {
                    $.ajax({
                      url: "includes/ajax_handlers/editorialArticleAutoSave.php",
                      type: "POST",
                      data: ({articleid:articleid,text:text}),
                      dataType: "html",
                      success: function(response){
                          response=response.split("|");
                          if($.trim(response[0])=='error')
                          {
                              alert($.trim(reponse[1]));
                          } else if($.trim(response[0])=='new')
                          {
                              $('#id').val($.trim(response[1]));
                          }
                      }
                    })
                }
            }
            
            function saveMedia()
            {
                
            }
            
            function toggleMedia()
            {
                //close all others
                var type=$('#newmedia').val();
                if(type!='image'){$('#media_image').slideUp();}        
                if(type!='gallery'){$('#media_gallery').slideUp();}        
                if(type!='audio'){$('#media_audio').slideUp();}        
                if(type!='link'){$('#media_link').slideUp();}        
                if(type!='html'){$('#media_html').slideUp();}
                $('#media_'+type).slideDown();       
            }
            
            function loadMedia(id)
            {
                
            }
            
            window.setInterval("save_contents()",30000);
      
    </script>
    <?php
}
function delete_news()
{ 
    $id=intval($_GET['id']);
    $sql="DELETE FROM editorial_article_body WHERE id=$id";
    $dbDelete=dbexecutequery($sql);
    if ($error!='')
    {
        setUserMessage('There was a problem deleting the news item.<br />'.$error,'error');
    } else {
        setUserMessage('The news item has been successfully deleted.','success');
    }
    redirect('?action=list');
}

function list_news()
{
    $sql="SELECT * FROM editorial_article_body ORDER BY post_datetime DESC";
    $dbNews=dbselectmulti($sql);
    tableStart("<a href='?action=add'>Add news</a>","Headlines",4);
    if ($dbNews['numrows']>0)
    {
        foreach($dbNews['data'] as $news)
        {
            $headline=$news['headline'];
            $id=$news['id'];
            print "<tr>";
            print "<td>$headline</td>";
            print "<td><a href='?action=edit&id=$id'>Edit</td>";
            print "<td><a href='?action=export&id=$id'>Export</td>";
            print "<td><a href='?action=delete&id=$id' class='delete'>Delete</a></td>";
            print "</tr>\n";
        
        }
    }
    tableEnd($dbNews);
}




function save_news($action)
{
    global $siteID;
    $dt=date("Y-m-d H:i:s");
    $headline=addslashes($_POST['headline']);
    $articleBody=addslashes($_POST['articleBody']);
    $id=$_POST['id'];
    $authorid=$_POST['authorid'];
    $pubdate=$_POST['pubdate'];
    if ($action=='insert')
    {
        $sql="INSERT INTO editorial_article_body (post_datetime, headline, article, author_id, pub_date) VALUES ('$dt', '$headline', '$articleBody', '$authorid', '$pubdate')";
        $db=dbinsertquery($sql);
        $error=$db['error'];   
    } else {
        $sql="UPDATE editorial_article_body SET author_id='$authorid', pub_date='$pubdate', headline='$headline', article='$articleBody' WHERE id=$id";
        $db=dbexecutequery($sql);
        $error=$db['error'];
    }
    if ($error!='')
    {
        setUserMessage('There was a problem saving the news item.<br />'.$error,'error');
    } else {
        setUserMessage('The news item has been successfully saved.','success');
    }
    redirect("?action=list");
    
}

function export()
{
    $id=intval($_GET['id']);
    $sql="SELECT * FROM editorial_article_body WHERE id=$id";
    $dbArticle=dbselectsingle($sql);
    $article=$dbArticle['data'];
    
    $filename=stripslashes($article['headline']);
    $filename=strtolower(str_replace(" ","",$filename));
    //header('Content-Type: text/plain'); // plain text file
    //header('Content-Disposition: attachment; filename="'.$filename.'"');
      
    //generate the header
    $head= "<ASCII-WIN>
<Version:7><FeatureSet:InDesign-Roman><ColorTable:=<Black:COLOR:CMYK:Process:0,0,0,1>>
<DefineCharStyle:Factbox bold=<Nextstyle:Factbox bold><KeyboardShortcut:Shift\+Alt\+Num 9><cTypeface:Bold Condensed><cFont:Myriad Pro>>
<DefineCharStyle:Zapf Dingbat=<Nextstyle:Zapf Dingbat><KeyboardShortcut:Shift\+Num 5><cTypeface:Regular><cSize:8.000000><cBaselineShift:1.000000><cFont:ZapfDingbats BT>>
<DefineParaStyle:NormalParagraphStyle=<Nextstyle:NormalParagraphStyle><cTypeface:Roman><cFont:Verdana>>
<DefineParaStyle:jump-continued from=<BasedOn:NormalParagraphStyle><Nextstyle:jump-continued from><cTypeface:Condensed><cSize:10.000000><pSpaceBefore:5.399999><pSpaceAfter:5.399999><cFont:Myriad Pro><pRuleBelowStroke:0.250000><pRuleBelowOffset:3.024000><pRuleBelowOn:1>>
<DefineParaStyle:body-utopia=<BasedOn:NormalParagraphStyle><Nextstyle:body-utopia><KeyboardShortcut:Shift\+Num 1><cSize:9.500000><cHorizontalScale:0.940000><cAutoPairKern:Optical><cTracking:-15><pFirstLineIndent:12.000000><cLeading:10.200000><cFont:Utopia><pTextAlignment:JustifyLeft>>
";
   $article=stripslashes($article['article']);
   
   //change quotes to hex
   $article=str_replace("\"","<0x201C>",$article);
   $article=str_replace("“","<0x201C>",$article);
   $article=str_replace("”","<0x201D>",$article);
   $article=str_replace("’","<0x2019>",$article);
   
   //now begin the process of converting the html to InDesign markup
   $article=str_replace("<div><ul>","",$article);
   $article=str_replace("</ul></div>","",$article);
   $article=str_replace("<div>","<ParaStyle:body-utopia><cTracking:0><cLeading:10.400000>",$article);
   $article=str_replace("</div>","<cTracking:><cLeading:>\r\n",$article);
   $article=str_replace("<br>","\r\n",$article);
   $article=str_replace("<BR>","\r\n",$article);
   
   //get rid of ul's and div's
   $article=str_replace("</ul>","",$article);
   $article=str_replace("<div>","",$article);
   $article=str_replace("</div>","",$article);
   //convert li's to zapf dingbat bullets
   $article=str_replace("</li>","<cTracking:><cLeading:>\r\n",$article);
   $article=str_replace("<li>","<ParaStyle:body-utopia><CharStyle:Zapf Dingbat><cTracking:0><cLeading:10.400000>n<cTracking:><cLeading:><CharStyle:><cTracking:0><cLeading:10.400000>",$article);
   
   //now bold
   $article=str_replace("</b>","<cTracking:><cLeading:><cTypeface:><cTracking:0><cLeading:10.400000>",$article);
   $article=str_replace("<b>","<cTracking:><cLeading:><cTypeface:Bold><cTracking:0><cLeading:10.400000>",$article);
   //now italic
   $article=str_replace("</i>","<cTracking:><cLeading:><cTypeface:><cTracking:0><cLeading:10.400000>",$article);
   $article=str_replace("<i>","<cTracking:><cLeading:><cTypeface:Italic><cTracking:0><cLeading:10.400000>",$article);
   
   //now write the contents out
   //print $head.$article;
   error_reporting(E_ERROR);
   dbclose();
   $mypath="C:\\tester";
   if (!file_exists($mypath))
   {
    mkdir($mypath,0777,TRUE);
   }
   $filename = $mypath.'\\'.$filename.'.txt';
   $handle = fopen($filename,"x+");
   fwrite($handle,$head.$article);
   fclose($handle);
   print "File has been output";       
}

footer();


?>