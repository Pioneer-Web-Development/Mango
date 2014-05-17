<?php
  session_start();
  include('../includes/functions_mobile.php');
  
  mobile_init();
  
  function mobile_page()
  {
      if(isset($_POST))
      {
          print_r($_POST);
          foreach($_POST as $key=>$value)
          {
              if(substr($key,0,5)=="file_")
              {
                  //ok, lets read in the file and save it locally
                  $dfile=file_get_contents($value);
                  $extension=end(explode(".",$value));
                  $filename="../artwork/advertising/file-".$key.'.'.$extension;
                  $handle = fopen($filename, "r+");
                    // In our example we're opening $filename in append mode.
                    // The file pointer is at the bottom of the file hence
                    // that's where $somecontent will go when we fwrite() it.
                    if (!$handle = fopen($filename, 'w+')) {
                         echo "Cannot open file ($filename)";
                         exit;
                    }

                    // Write $somecontent to our opened file.
                    if (fwrite($handle, $dfile) === FALSE) {
                        echo "Cannot write to file ($filename)";
                        exit;
                    }

                    echo "Success, wrote ($somecontent) to file ($filename)";

                    fclose($handle);

              }
          }
      }
      print "On Appointment Page";
      print "<pre>";
      print_r($_SESSION);
      print "</pre>";
      ?>
      <script type="text/javascript" src="https://www.dropbox.com/static/api/1/dropins.js" id="dropboxjs" data-app-key="volear0xchsxak0"></script>
      <form id='myform' method=post enctype='multipart/form-data'>
      <input type="dropbox-chooser" name="selected-file" style="visibility: hidden;"/><br />
      <input type='submit' value='Save Form' />
      </form>
      <input type='button' value='Select Files' onclick='dropboxPicker();' />
      
      <script>
      function dropboxPicker()
      {
          var options = {

            // Required. Called when a user selects an item in the Chooser.
            success: function(files) {
                //alert("Here's the file link:" + files[0].link);
                $.each(files,function(index,file){
                    console.log(file.link);
                    $('#myform').append("<input type='hidden' name='file_"+index+"' value='"+file.link+"' />");
                })
            },

            // Optional. Called when the user closes the dialog without selecting a file
            // and does not include any parameters.
            cancel: function() {

            },

            // Optional. "preview" (default) is a preview link to the document for sharing,
            // "direct" is an expiring link to download the contents of the file. For more
            // information about link types, see Link types below.
            linkType: "direct",// "preview" or "direct",

            // Optional. A value of false (default) limits selection to a single file, while
            // true enables multiple file selection.
            multiselect: true, //true or false,
        
            // Optional. This is a list of file extensions. If specified, the user will
            // only be able to select files with these extensions. You may also specify
            // file types, such as "video" or "images" in the list. For more information,
            // see File types below. By default, all extensions are allowed.
            extensions: ['.pdf', '.doc', '.docx'],
            
        };
        
        /*
            returned data from call
            file = {
                // Name of the file
                "name": "filename.txt",

                // URL to access the file, which varies depending on the linkType specified when the
                // Chooser was triggered
                "link": "https://...",

                // Size of the file in bytes
                "bytes": 464,

                // URL to a 64x64px icon for the file based on the file's extension
                "icon": "https://...",

                // Set of thumbnail URLs generated when the user selects images and videos. It
                // returns three sizes. If the user didn't select an image or video, no
                // thumbnails will be included.
                "thumbnails": {
                    "64x64": "https://...",
                    "200x200": "https://...",
                    "640x480": "https://...",
                }
            }
        */
        Dropbox.choose(options);
      }
      </script>
      <?php
  }
?>
