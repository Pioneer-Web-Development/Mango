<?php
//<!--VERSION: .5 **||**-->
  //photo tools collection
  //Enabling Error Reporting
  if ($_GET['imagefiletype']){$imagefiletype=$_GET['imagefiletype'];}else{$imagefiletype="png";}
  
  //sample of resizing a given image and saving it to a new name
  if ($_GET['mode']=="test")
  
  {
      $test=$_GET['test'];
      
      switch ($test){
      
        case "image";
        doresize();
        break;
        
        case "multi";
        dobox();
        break;
       
        case "pie";
        dopie();
        break;
        
        case "bar";
        dobar();
        break;
        
        case "legend";
        dolegend();
        break;
        
        case "thumbs";
        //now, process the other sizes
            $filesavepath="../artwork/articleimages/08/August";
            $newname="6167.JPG";
            $sizeArray[0]['suffix']="small";
            $sizeArray[0]['destpath']=$filesavepath;
            $sizeArray[0]['format']="jpg";
            $sizeArray[0]['height']="0";
            $sizeArray[0]['width']="125";
            $sizeArray[0]['percent']="0";
            
            $sizeArray[1]['suffix']="medium";
            $sizeArray[1]['destpath']=$filesavepath;
            $sizeArray[1]['format']="jpg";
            $sizeArray[1]['height']="0";
            $sizeArray[1]['width']="250";
            $sizeArray[1]['percent']="0";
            
            $sizeArray[2]['suffix']="large";
            $sizeArray[2]['destpath']=$filesavepath;
            $sizeArray[2]['format']="jpg";
            $sizeArray[2]['height']="0";
            $sizeArray[2]['width']="500";
            $sizeArray[2]['percent']="0";
            doThumbs($newname,$sizeArray);
          break;
      
      }
  }
  
function dopie(){
    $pieces=array(array("value"=>"82","fillcolor"=>"FF0000","label"=>"Apples"),
                  array("value"=>"52.5","fillcolor"=>"0000FF","label"=>"Strawberries"),
                  array("value"=>"50","fillcolor"=>"00FF00","label"=>"Lettuce")
                  );
    $image=create_piechart(100,100,$pieces,1);
    output_image($image);

}

function dobar(){
    $fontfolder=$_SERVER["DOCUMENT_ROOT"]."fonts/";
    $ttffont=$fontfolder."font.ttf";
    $pieces=array(array("value"=>"82","fillcolor"=>"FF0000","label"=>"Apples"),
                  array("value"=>"52.5","fillcolor"=>"0000FF","label"=>"Strawberries"),
                  array("value"=>"50","fillcolor"=>"00FF00","label"=>"Lettuce")
                  );
    $image=create_bargraph(400,300,$pieces,$ttffont,1);
    output_image($image);

}  

function dolegend(){
    $fontfolder=$_SERVER["DOCUMENT_ROOT"]."/cms/includes/fonts/";
    $ttffont=$fontfolder."font.ttf";
    $pieces=array(array("value"=>"82","fillcolor"=>"FF0000","label"=>"Apples"),
                  array("value"=>"52.5","fillcolor"=>"0000FF","label"=>"Strawberries"),
                  array("value"=>"50","fillcolor"=>"00FF00","label"=>"Lettuce")
                  );
    $image=create_legend(100,100,$pieces,$ttffont,1);
    output_image($image);
}
  
//create_line($canvas,0,0,50,50,$backcolor,$forecolor);
//create_elipse($canvas,200,200,50,50,$backcolor,$forecolor);
//create_arc($canvas,200,200,50,50,0,45,$forecolor,$shadowcolor,1);  
function doresize($testimage,$sizeNames){
  //$outputFile="../../cms/artwork/siteart/pay_thumb.jpg";
  $image=load_image($testimage);
  $image=resize_image($testimage,0,50,0);
  $image=imageToGreyscale($image);
  //output the image, if we don't specify a name, it just gets displayed in the browser
  output_image($image,$outputFile,"jpg");
  
  //finally, destroy the image to free up memory
  imagedestroy($image);
}
 
 
function doThumbs($originalimage,$sizeNames){
    //sizeNames is a multi-dimension array
    //[suffix] == tag to filename ie: 5412_small.jpg
    //[format] == format of output, jpg or gif
    //[destpath] == destination path
    //[width] == set to 0 to size height
    //[height] == set to 0 to size width
    //[percent] == only applies if width and height are set to 0
    
    $namepieces=explode(".",$originalimage);
    $name=$namepieces[0];
    
    
    
    foreach($sizeNames as $working){
        $suffix=$working['suffix'];
        $format=$working['format'];
        if ($format==''){$format="jpg";}
        $destpath=$working['destpath'];
        $width=$working['width'];
        $height=$working['height'];
        $percent=$working['percent'];
        $filename=$working['filename'];
        if ($suffix!="")
        {
            $outputFile=$name."_".$suffix.".".$format;
        } else {
            $outputFile=$filename;
        }
        $outputFile=$destpath.$outputFile;
        
        $file2load=$destpath.$originalimage;
        //print "loading file ... $file2load<br>--will save to $outputFile<br>";
        
        //$image=load_image($file2load);
        $image=resize_image($file2load,$width,$height,$percent);
        //output the image, if we don't specify a name, it just gets displayed in the browser
        output_image($image,$outputFile,$format);
        //finally, destroy the image to free up memory
        imagedestroy($image);
    }
} 
 
function dobox()
{  
  //sample of drawing a filled box on a canvas, then adding a line of text
  $fontfolder=$_SERVER["DOCUMENT_ROOT"]."/cms/includes/fonts/";
  $backcolor="66FF22";
  $fillcolor="3366AA";
  $forecolor="FF0000";
  $fontcolor="FF0000";
  $ttffont=$fontfolder."font.ttf";
  $ttffontsize=24; //size of font
  $ttffontangle=30; //angle of text to be drawn
  $textxoffset=20; //offset for text in container in pixels from right
  $textyoffset=250; //offset fot text in container in pixels from top 
  $text="Wow, isn't this cool!"; //text to be displayed
  $filled=0;
  $startx=10;
  $starty=40;
  $boxwidth=200;
  $boxheight=150;
  $width=600;
  $height=400;
  $transparent=0;
  $canvas=create_canvas($width,$height,$backcolor,$transparent);
  
  //image merge data
  $testimage="../../cms/artwork/siteart/paypal_logo.gif";
  
  
  if ($canvas!=null){
      $canvas=create_box($canvas,$startx, $starty, $boxwidth, $boxheight,$fillcolor,$filled);
      
      $canvas=create_text($canvas,$ttffontsize, $ttffontangle, $textxoffset, $textyoffset, $fontcolor, $ttffont, $text);
      
      $canvas=create_arc($canvas,200,200,200,200,0,45,$fillcolor,'filled','',1);
      
      //load an image and put on the page
      //$image=load_image($testimage);
      
      //or you could load in and resize to specific needs
      $image=resize_image($testimage,120,0,0);
      $image=rotate_image($image,90);
      $mergepositionx=400;
      $mergepositiony=250;
      $sourcex=0;
      $sourcey=0;
      $sourcewidth=imagesx($image);
      $sourceheight=imagesy($image);
      $mergepercent=50;
      
      //flip the image horizontally
      $image=flip_image($image,1,0);
      
      //convert it to greyscale
      $image=imageToGreyscale($image);
  
      //merge an image onto the canvas
      $canvas=merge_image($canvas,$image,$mergepositionx,$mergepositiony,$sourcex,$sourcey,$sourcewidth,$sourceheight,$mergepercent);
      
      $polygonpoints= array(
                        40,  50,  // Point 1 (x, y)
                        20,  240, // Point 2 (x, y)
                        40, 100,
                        60,  60,  // Point 3 (x, y)
                        240, 20,  // Point 4 (x, y)
                        50,  40,  // Point 5 (x, y)
                        10,  10   // Point 6 (x, y)
                        );
      $canvas=create_polygon($canvas,$polygonpoints,$fillcolor,1);
      
      
      
      //output the image, if we don't specify a name, it just gets displayed in the browser
      output_image($canvas,"","jpg");
      
      //finally, destroy the image to free up memory
      imagedestroy($canvas);
  }
}  
  
  
  
function hex2int($hex)
{
  /**
 * @param    $hex string        6-digit hexadecimal color
 * @return    array            3 elements 'r', 'g', & 'b' = int color values
 * @desc Converts a 6 digit hexadecimal number into an array of
 *       3 integer values ('r'  => red value, 'g'  => green, 'b'  => blue)
 */
       return array( 'r' => hexdec(substr($hex, 0, 2)), // 1st pair of digits
                      'g' => hexdec(substr($hex, 2, 2)), // 2nd pair
                      'b' => hexdec(substr($hex, 4, 2))  // 3rd pair
                    );
}


function validHexColor($input = '000000', $default = '000000') {
    /**
 * @param $input string     6-digit hexadecimal string to be validated
 * @param $default string   default color to be returned if $input isn't valid
 * @return string           the validated 6-digit hexadecimal color
 * @desc returns $input if it is a valid hexadecimal color, 
 *       otherwise returns $default (which defaults to black)
 */
    // A valid Hexadecimal color is exactly 6 characters long
    // and eigher a digit or letter from a to f
    return (eregi('^[0-9a-f]{6}$', $input)) ? $input : $default ;
}


function tintOf($color,$tint)
{
/*
 * @param $color array  3 elements 'r', 'g', & 'b' = int color values
 * @param $tint float   fraction of color $color to return
 * @return array        new color tinted to $tint of $color
 * @desc returns a new color that is a tint of the original
 *       e.g. (255,127,0) w/ a tint of 0.5 will return (127,63,0)
 */
    return array ( 'r' => round(((255-$color['r']) * (1-$tint)) + $color['r']), 
                   'g' => round(((255-$color['g']) * (1-$tint)) + $color['g']),
                   'b' => round(((255-$color['b']) * (1-$tint)) + $color['b']) );
}  
  
  function output_image($canvas,$newFile='',$imagefiletype='png',$quality=75)
  {
    switch ($imagefiletype) {
        case "png":
            if ($newFile!=""){
                ImagePng ($canvas,$newFile);
            } else {
                header ("Content-type: image/$imagefiletype");
                ImagePng ($canvas);
            }
        break;
        
        case "jpg":
            if ($newFile!=""){
                ImageJpeg ($canvas,$newFile,$quality);
            } else {
                header ("Content-type: image/$imagefiletype");
                ImageJpeg ($canvas,null,$quality);
            }
        break;
        
        case "gif":
            if ($newFile!=""){
                ImageGif ($canvas,$newFile);
            } else {
                header ("Content-type: image/$imagefiletype");
                ImageGif ($canvas);
            }
        break;
    }
    
  
  }
  
  
  function get_size($file)
  {
    $image=load_image($file);
    if ($image===false){
        $info['success']=false;
    } else {
        $info['success']=true;
        $info['width']=imagesx($image);
        $info['height']=imagesy($image);
        
    }
    return $info;
  }
  
  
  function load_image ($file)
  {
        # JPEG:
        $im = @imagecreatefromjpeg($file);
        if ($im !== false) { return $im; }

        # GIF:
        $im = @imagecreatefromgif($file);
        if ($im !== false) { return $im; }

        # PNG:
        $im = @imagecreatefrompng($file);
        if ($im !== false) { return $im; }

        # GD File:
        $im = @imagecreatefromgd($file);
        if ($im !== false) { return $im; }

        # GD2 File:
        $im = @imagecreatefromgd2($file);
        if ($im !== false) { return $im; }

        # WBMP:
        $im = @imagecreatefromwbmp($file);
        if ($im !== false) { return $im; }

        # XBM:
        $im = @imagecreatefromxbm($file);
        if ($im !== false) { return $im; }

        # XPM:
        $im = @imagecreatefromxpm($file);
        if ($im !== false) { return $im; }

        # Try and load from string:
        $im = @imagecreatefromstring(file_get_contents($file));
        if ($im !== false) { return $im; }
        return false;
}
  
  function flip_image($image, $vertical, $horizontal)
  {
    $w = imagesx($image);
    $h = imagesy($image);

    if (!$vertical && !$horizontal) return $image;

    $flipped = imagecreatetruecolor($w, $h);

    if ($vertical) {
      for ($y=0; $y<$h; $y++) {
        imagecopy($flipped, $image, 0, $y, 0, $h - $y - 1, $w, 1);
      }
    }

    if ($horizontal) {
      if ($vertical) {
        $image = $flipped;
        $flipped = imagecreatetruecolor($w, $h);
      }

      for ($x=0; $x<$w; $x++) {
        imagecopy($flipped, $image, $x, 0, $w - $x - 1, 0, 1, $h);
      }
    }

    return $flipped;
    imagedestroy($flipped);
  }
  
  
  function rotate_image($image,$angle)
  {
      //only rotates 90 degree increments
      
      $src_x = imagesx($image);
    $src_y = imagesy($image);
    if ($angle == 180)
    {
        $dest_x = $src_x;
        $dest_y = $src_y;
    }
    elseif ($src_x <= $src_y)
    {
        $dest_x = $src_y;
        $dest_y = $src_x;
    }
    elseif ($src_x >= $src_y) 
    {
        $dest_x = $src_y;
        $dest_y = $src_x;
    }
   
    $rotate=imagecreatetruecolor($dest_x,$dest_y);
    imagealphablending($rotate, false);
   
    switch ($angle)
    {
        case 270:
            for ($y = 0; $y < ($src_y); $y++)
            {
                for ($x = 0; $x < ($src_x); $x++)
                {
                    $color = imagecolorat($image, $x, $y);
                    imagesetpixel($rotate, $dest_x - $y - 1, $x, $color);
                }
            }
            break;
        case 90:
            for ($y = 0; $y < ($src_y); $y++)
            {
                for ($x = 0; $x < ($src_x); $x++)
                {
                    $color = imagecolorat($image, $x, $y);
                    imagesetpixel($rotate, $y, $dest_y - $x - 1, $color);
                }
            }
            break;
        case 180:
            for ($y = 0; $y < ($src_y); $y++)
            {
                for ($x = 0; $x < ($src_x); $x++)
                {
                    $color = imagecolorat($image, $x, $y);
                    imagesetpixel($rotate, $dest_x - $x - 1, $dest_y - $y - 1, $color);
                }
            }
            break;
        default: $rotate = $image;
    };
    return $rotate;
  
  }
  
  function create_canvas($width=640,$height=400,$backcolor="FFFFFF",$transparent=1)
  {
      //we are creating a white canvas object of the given size, with true color
      $canvas=imagecreatetruecolor($width,$height) or die ("Could not create the image, width was $width, height was $height and backcolor was $backcolor");
      
      if ($transparent){
          $background = imageColorAllocate ($canvas, 0, 0, 0);
          $background = imageColorTransparent($canvas,$background);
      } else {
          $backcolors = hex2int(validHexColor($backcolor));
          $fill_color = ImageColorAllocate ($canvas, $backcolors['r'],$backcolors['g'],$backcolors['b']);
          imagefill($canvas, 0, 0, $fill_color);
      }
      return $canvas;
  }
  
  
  function create_text($canvas,$ttffontsize, $ttffontangle, $textxoffset, $textyoffset, $textcolor, $ttffont, $text){
    $textcolors = hex2int(validHexColor($textcolor));
    $txt_color = ImageColorAllocate ($canvas, $textcolors['r'],$textcolors['g'],$textcolors['b']);
    ImageTTFText ($canvas, $ttffontsize, $ttffontangle, $textxoffset, $textyoffset, $txt_color, $ttffont, $text);
    return $canvas;
  }
  
  function create_box($canvas,$startx,$starty,$width,$height,$fillcolor='000000',$filled=1,$thickness=2,$dashed=0){
    $fillcolors = hex2int(validHexColor($fillcolor));
    $fill_color = ImageColorAllocate ($canvas, $fillcolors['r'],$fillcolors['g'],$fillcolors['b']);
    $white=ImageColorAllocate ($canvas, 255,255,255);
    if ($filled){
        if ($dashed && $thickness==2){
            $style = array($line_color, $line_color, $line_color, $line_color, $line_color, $white, $white, $white, $white, $white);
            imagesetstyle($canvas, $style);
            imageFilledRectangle($canvas, $startx+2, $starty+1, $width - 2, $height - 2, $fill_color);
            $style = array($line_color); //reset the style
            imagesetstyle($canvas, $style); 
        } elseif ($thickness>1){
            imagesetthickness($canvas,$thickness);
            imageFilledRectangle($canvas, $startx+$thickness, $starty+$thickness, $width - 1-$thickness, $height - 1-$thickness, $fill_color);
            imagesetthickness($canvas,1);
        }
    } else {
        if ($dashed && $thickness==2){
            $style = array($line_color, $line_color, $line_color, $line_color, $line_color, $white, $white, $white, $white, $white);
            imagesetstyle($canvas, $style);
            imageRectangle($canvas, $startx+$thickness-1, $starty+$thickness-1, $width-$thickness, $height-$thickness, $fill_color);
            $style = array($line_color); //reset the style
            imagesetstyle($canvas, $style); 
        } elseif ($thickness>1){
            imagesetthickness($canvas,$thickness);
            imageRectangle($canvas, $startx+$thickness-1, $starty+$thickness-1, $width-$thickness, $height-$thickness, $fill_color);
            imagesetthickness($canvas,1);
        }
    }
    return $canvas;
  }
  
  function create_line($canvas,$startx,$starty,$endx,$endy,$linecolor='000000',$thickness=2,$dashed=0){
    $linecolors = hex2int(validHexColor($linecolor));
    $line_color = ImageColorAllocate ($canvas, $linecolors['r'],$linecolors['g'],$linecolors['b']);
    $white=ImageColorAllocate ($canvas, 255,255,255);
    if ($dashed && $thickness==2){
        $style = array($line_color, $line_color, $line_color, $line_color, $line_color, $white, $white, $white, $white, $white);
        imagesetstyle($canvas, $style);
        imageline($canvas, $startx,$starty,$endx,$endy, IMG_COLOR_STYLED);
        $style = array($line_color); //reset the style
        imagesetstyle($canvas, $style); //reset the default drawing style
    } elseif ($thickness>2){
        imagesetthickness($canvas,$thickness);
        ImageLine($canvas, $startx,$starty,$endx,$endy, $line_color);
        imagesetthickness($canvas,2); //put it back to 1 so it doesn't affect other objects
    }
    
    return $canvas;
  }
  
  function create_ellipse($canvas,$width,$height,$centerx,$centery,$backcolor,$forecolor,$filled=1){
    $fillcolors = hex2int(validHexColor($fillcolor));
    $fill_color = ImageColorAllocate ($canvas, $fillcolors['r'],$fillcolors['g'],$fillcolors['b']);
    if ($filled){
        imagefilledellipse($canvas, $centerx, $centery, $width, $height, $fill_color);
    } else {
        imageellipse($canvas, $centerx, $centery, $width, $height, $fill_color);
    }
    return $canvas;
  }
  
  function merge_image($canvas,$imagetomerge,$mergepositionx,$mergepositiony,$sourcex,$sourcey,$sourcewidth,$sourceheight,$percent){
    imageCopyMerge  ($canvas, $imagetomerge, $mergepositionx,$mergepositiony,$sourcex,$sourcey,$sourcewidth,$sourceheight,$percent);
    return $canvas;
  }
  
  function create_polygon($canvas,$points,$fillcolor,$filled=1){
      $pointcount=count($points)/2;
      $fillcolors = hex2int(validHexColor($fillcolor));
      $fill_color = ImageColorAllocate ($canvas, $fillcolors['r'],$fillcolors['g'],$fillcolors['b']);
        if ($filled){
            imagefilledpolygon($canvas, $points, $pointcount, $fill_color);
        } else {
            imagepolygon($canvas, $points, $pointcount, $fill_color);
        }
    return $canvas;
  } 
  
  
  function create_arc($canvas,$width,$height,$centerx,$centery,$startangle,$endangle,$forecolor,$type='filled',$shadowcolor='',$shadow=0){
    $forecolors = hex2int(validHexColor($forecolor));
    $fill_color = ImageColorAllocate ($canvas, $forecolors['r'],$forecolors['g'],$forecolors['b']);
    if ($shadowcolor=='' && $shadow){
        $shadow = tintOf($fill_color,0.3); 
        $shadow_color = ImageColorAllocate ($canvas, $shadow['r'],$shadow['g'],$shadow['b']);
    }
    
    
    switch ($type){
        case "filled": 
            $type="IMG_ARC_PIE";   //Filled arch
        break;
        case "nofill": 
            $type="IMG_ARC_NOFILL";  //when added as a parameter, makes it unfilled
        break;
        case "edged": 
            $type="IMG_ARC_EDGED"; //no fill
        break;
    }
    // 3D look
    if ($shadow){
            imagefilledarc($canvas, $centerx+5, $centery+15, $width, $height, $startangle, $endangle, $shadow_color, IMG_ARC_PIE);
    }
    imagefilledarc($canvas, $centerx, $centery, $width, $height, $startangle, $endangle, $fill_color, $type);
    
    return $canvas;
  }
  
  function resize_image($sourceFile,$destWidth=0,$destHeight=0,$scale=0,$destX=0,$destY=0,$sourceX=0,$sourceY=0){
      //get root of filename to be used in the newimage
      //print "in resize_image, source is $sourceFile<br>";
      list($sourceWidth, $sourceHeight) = getimagesize($sourceFile);
      if ($scale!=0){
        if ($scale=="100"){
            $destWidth=$sourceWidth;
            $destHeight=$sourceHeight;
        } else {
            $destWidth=$sourceWidth*$scale/100;
            $destHeight=$sourceHeight*$scale/100; 
        }
      } else {
        if ($destWidth==0 and $destHeight!=0){
            $destWidth=$sourceWidth*($destHeight/$sourceHeight);
        } elseif ($destWidth!=0 and $destHeight==0){
            $destHeight=$sourceHeight*($destWidth/$sourceWidth);
        
        }
      } 
    
    $destImage= imagecreatetruecolor($destWidth, $destHeight);
    $sourceImage = load_image($sourceFile);

    // Resizing our image to fit the canvas
    imagecopyresampled($destImage,$sourceImage,$destX,$destY,$sourceX,$sourceY,$destWidth,$destHeight,$sourceWidth,$sourceHeight);
    return $destImage;
}

function change_color($image, $old_color, $new_color, $threshold = 15) {
    $image_width = imagesx($image);
    $image_height = imagesy($image);

    // iterate through x axis
    for ($x = 0; $x < $image_width; $x++) {

        // iterate through y axis
        for ($y = 0; $y < $image_height; $y++) {

            // look at current pixel
            $pixel_color = imagecolorat($image, $x, $y);

            if (($pixel_color <= $old_color + $threshold) && ($pixel_color >= $old_color - $threshold)) {
                // replace with new color
                imagesetpixel($image, $x, $y, $new_color);
            }
        }
    }
    
}
  

function create_piechart($width,$height,$piepieces,$labels=1){
    //$piepieces is an array containing 3 elements: value (double), color(hex), label (text)
    //first step is to create the canvas to work on, setting the background color to transparent
    $canvas=create_canvas($width,$height,'',1);
    //now, see what the total is of the values
    $total=0;
    foreach($piepieces as $piece){
        $total=$total+$piece['value'];
    } 
    
    //now we need to calculate the percentage of the 360 degree circle for each piece
    $width=$width*.95;
    $height=$height*.95;
    $pieces=array();
    $startangle=0;
    foreach ($piepieces as $piece){
        $endangle=$startangle+$piece['value']/$total*360;
        $canvas=create_arc($canvas,$width,$height,$width/2,$height/2,$startangle,$endangle,$piece['fillcolor']);
        $startangle=$endangle;
    }
    return $canvas;
}  
 
function create_bargraph($width,$height,$pieces,$font,$label=1){
    //$piepieces is an array containing 3 elements: value (double), color(hex), label (text)
    //first step is to create the canvas to work on, setting the background color to transparent
    $canvas=create_canvas($width,$height,'FFFFFF',0);
    //now scale back to 90%
    $startx=$width*.05; //this gives us a start x point that is slightly offset from the left edge
    $width=$width*.95; //scale the graphic
    //find out which piece has the maximum height
    $maxheight=0;
    foreach($pieces as $piece){
        if ($piece['value']>$maxheight){
            $maxheight=$piece['value'];
        }
    } 
    
    //create background axis
    $canvas=create_line($canvas,$startx,0,$startx,$height); //vertical axis line on left side
    $canvas=create_line($canvas,$startx,$height-1,$width,$height-1); //horizontal axis at bottom
    
    $ratio=$maxheight/$height;
    //create a set of vertically spaced horizontal lines that are light-grey and dashed
    for ($y=$height;$y>=$height/10;$y=$y-($height/10)){
        $canvas=create_line($canvas,$startx,$y,$width,$y,'CCCCCC',2,1);
        $label=$height-$y;
        $label=ceil($label*$ratio);
        $canvas=create_text($canvas,8,0,0,$y,'000000',$font,$label);
    }
    $canvas=create_text($canvas,8,0,0,8,'000000',$font,ceil($height*$ratio));
    //now we need to print the vertical bars
    //first, how many pieces do we have?
    $bars=count($pieces);
    
    //now, we now the width, lets subtract 5 so we have some more room on the left before starting
    //and also 5 from the right for a bit of a margin
    $startx=$startx+20;
    $width=$width-30;
    
    $defaultspacing=30;  //if these add up to less space than we have, just go ahead
    $defaultthickness=20;
    
    $totalneeded=$defaultthickness*$bars+$defaultspacing*($bars-1);
    
    if ($totalneeded>$width){
        //we need to figure out the new spacing and thickness, with minimums of 2 pixels for each
        $widthratio=$totalneeded/$width;
        if (ceil($defaultspacing*$widthratio)<2){$defaultspacing=2;}else{$defaultspacing=ceil($defaultspacing*$widthratio);}
        if (ceil($defaultthickness*$widthratio)<2){$defaultthickness=2;}else{$defaultthickness=ceil($defaultthickness*$widthratio);}
    }
    
    //now we are ready to start printing the bars
    foreach($pieces as $piece){
        $canvas=create_line($canvas,$startx,$height-3,$startx,$height-ceil($piece['value']/$maxheight*$height)+3,$piece['fillcolor'],$defaultthickness,0);
        $startx=$startx+$defaultspacing+$defaultthickness;
    }
     return $canvas;
} 
  

function create_legend($width,$height,$pieces,$font){
    //first, create a canvas
    $canvas=create_canvas($width,$height,'FFFFFF',0);
    
    //next, draw a box around it
    $canvas=create_box($canvas,0,0,$width,$height,'000000',0,2,1);
    
    

    return $canvas;
}

function imageToGreyscale( $dstImg ) {
   
   /**
 * void imageToGreyscale( $dstImg )
 * Converts the $dstImg image to greyscale.
 * 
 * Created by sn4g <snagnever at gmail dot com>
 *
 * @param gdImageResource $dstImg
 */
 
 
    $imgW = imagesx( $dstImg );
    $imgH = imagesy( $dstImg );
    
    for($y=0;$y<$imgH;$y++)
    {
        for($x=0;$x<$imgW;$x++)
        {
            $dstRGB = imagecolorat($dstImg, $x, $y);

            //$dstA = ($dstRGB >> 24) << 1;
            $dstR = $dstRGB >> 16 & 0xFF;
            $dstG = $dstRGB >> 8 & 0xFF;
            $dstB = $dstRGB & 0xFF;
            
            $newC = ( $dstR + $dstG + $dstB )/3;
                
            $newRGB = imagecolorallocate($dstImg, $newC, $newC, $newC );
            imagesetpixel($dstImg, $x, $y, $newRGB );
        }
    }
    return $dstImg;
}


function imageToGreyscalePalette( $dstImg, $betterQuality = false ) {
  /**
 * void imageToGreyscalePalette( $dstImg )
 * Converts the $dstImg image to greyscale.
 * Palette version, works really really faster, but truncates truecolor images to palette
 * images. So, a PNG32 will be converted to a PNG8. JPEG will lose some colors, and works
 * really great on GIF images. $betterQuality param gets a better quality to the image, but
 * costing about the double of time.
 * Palette version of functions takes about 32 times less time to generate the image.
 * 
 * Created by sn4g <snagnever at gmail dot com>
 *
 * @param gdImageResource $dstImg
 * [@param boolean $betterQuality ]
 */
  
   // if we don't have a palette, we convert it
    if( !($t = imagecolorstotal($dstImg)) )
    {
        $t = 256;
        imagetruecolortopalette($dstImg, true, $t);
        
        if( $betterQuality )
        {
            $imgW = imagesx( $dstImg );
            $imgH = imagesy( $dstImg );
            
            $auxImage = imagecreatetruecolor( $imgW, $imgH );
            imagecopy($auxImage, $dstImg, 0, 0, 0, 0, $imgW, $imgH );
            imagecolormatch($auxImage,$dstImg);
            imagedestroy($auxImage);
        }
    }
    
    for($c=0;$c<$t;$c++)
    {
        $dstRGB = imageColorsForIndex( $dstImg, $c );
        
        //$dstA = ($dstRGB >> 24) << 1;
        $dstR = $dstRGB["red"];// >> 16 & 0xFF;
        $dstG = $dstRGB["green"];// >> 8 & 0xFF;
        $dstB = $dstRGB["blue"];// & 0xFF;
            
        $newC = ( $dstR + $dstG + $dstB )/3;
        
        imagecolorset($dstImg, $c, $newC, $newC, $newC );
    }
}


function imageToSepia( $dstImg, $darkIt = 15 ){
 /*
 * void imageToSepia( $dstImg )
 * Converts the $dstImg image to sepia tone and darks it if asked.
 * 
 * Created by sn4g <snagnever at gmail dot com>
 *
 * @param gdImageResource $dstImg
 * @param integer $darkIt
 */
 
    $imgW = imagesx( $dstImg );
    $imgH = imagesy( $dstImg );
    
    for($y=0;$y<$imgH;$y++)
    {
        for($x=0;$x<$imgW;$x++)
        {
            $dstRGB = imagecolorat($dstImg, $x, $y);

            //$dstA = ($dstRGB >> 24) << 1;
            $dstR = $dstRGB >> 16 & 0xFF;
            $dstG = $dstRGB >> 8 & 0xFF;
            $dstB = $dstRGB & 0xFF;
            
            $newR = ($dstR * 0.393 + $dstG * 0.769 + $dstB * 0.189 ) - $darkIt;
            $newG = ($dstR * 0.349 + $dstG * 0.686 + $dstB * 0.168 ) - $darkIt;
            $newB = ($dstR * 0.272 + $dstG * 0.534 + $dstB * 0.131 ) - $darkIt;
            
            $newR = ($newR>255) ? 255 : ( ($newR<0) ? 0 : $newR );
            $newG = ($newG>255) ? 255 : ( ($newG<0) ? 0 : $newG );
            $newB = ($newB>255) ? 255 : ( ($newB<0) ? 0 : $newB );
            
            //echo "{$newR}, {$newG}, {$newB}<br>";
                
            $newRGB = imagecolorallocate($dstImg, $newR, $newG, $newB );
            imagesetpixel($dstImg, $x, $y, $newRGB );
        }
    }
}



function imageMerge( $dstImg, $srcImg, $fillPercent, $colorsIgnore = false, $colorsIgnoreTolerance = false, $srcImgX = 0, $srcImgY = 0 ) {
    /**
 * void imageMerge();
 * Merges two images on a destiny one.
 * You can set colors to be ignored, and the level of tolerance of
 * simillar colors to be ignore too.
 * 
 * Created by sn4g <snagnever at gmail dot com>
 * 
 * $dstImg - destiny image of merging, gdImageResource
 * $srcImg - image to be merged, gdImageResource
 * $fillPercent - how strong $srcImg will overlay $dstImg. Like an alpha level to $srcImg
 * [$colorIgnore - an array of gd colors to be ignored in the merging, array of gdColorResource]
 * [$colorsIgnoreTolerance - an array containing the tolerance level of the colors on $colorIgnore array]
 * [$srcImgX - the horizontal dislocation of the $srcImg on $dstImg]
 * [$srcImgY - the vertical dislocation of the $srcImg on $dstImg]
 *
 * @param gdImageResource $dstImg
 * @param gdImageResource $srcImg
 * @param integer $fillPercent
 * @param [array $colorsIgnore]
 * @param [array $colorsIgnoreTolerance]
 * @param [integer $srcImgX]
 * @param [integer $srcImgY]
 */
    
    $dstW = imagesx( $dstImg );
    $dstH = imagesy( $dstImg );
    
    $srcW = imagesx( $srcImg );
    $srcH = imagesy( $srcImg );
    
    $dstTransp = imageColorTransparent($dstImg);
    $srcTransp = imageColorTransparent($srcImg);
    
    if( $srcImgX >= $srcW ) trigger_error("The dislocation for \$srcImgW must be lower than source's width, {$srcW}px", E_USER_ERROR);
    if( $srcImgY >= $srcH ) trigger_error("The dislocation for \$srcImgh must be lower than source's height, {$srcH}px", E_USER_ERROR);
    
    for($y=0;$y<$dstH;$y++)
    {
        // If it overruns $srcImg width
        if( $y >= $srcH ) continue;
        
        for($x=0;$x<$dstW;$x++)
        {
            //If it overruns $srcImg height
            if( $x >= $srcW ) continue;
            
            //The subracton of $srcImgX and $srcImgY is for dislocating the image on the destiny
            $xDislocation = $x - $srcImgX;
            $yDislocation = $y - $srcImgY;
            
            if( $xDislocation >= $srcW ) $xDislocation = ($srcW - 1);
            else if ( $xDislocation < 0 ) $xDislocation = 0;
            
            if( $yDislocation >= $srcH ) $yDislocation = ($srcH - 1);
            else if( $yDislocation < 0 ) $yDislocation = 0;
            
            //echo "$xDislocation,$yDislocation for $srcW x $srcH <br>";
            
            
            $dstRGB = imagecolorat($dstImg, $x, $y);
            $srcRGB = imagecolorat($srcImg, $xDislocation, $yDislocation);
            
            // if the pixel is transparent, we'll not 'merge' it
            // should we really ignore destiny image transparent pixels?
            if( $dstRGB == $dstTransp || $srcRGB == $srcTransp )
            {
                continue 1;
            }

            //$dstA = ($dstRGB >> 24) << 1;
            $dstR = $dstRGB >> 16 & 0xFF;
            $dstG = $dstRGB >> 8 & 0xFF;
            $dstB = $dstRGB & 0xFF;
            
            $srcR = $srcRGB >> 16 & 0xFF;
            $srcG = $srcRGB >> 8 & 0xFF;
            $srcB = $srcRGB & 0xFF;
            
            $i = -1;
            while(++$i < count( $colorsIgnore ) )
            {
                
                $ciR = $colorsIgnore[$i] >> 16 & 0xFF;
                $ciG = $colorsIgnore[$i] >> 8 & 0xFF;
                $ciB = $colorsIgnore[$i] & 0xFF;
                
                
                if( isset( $colorsIgnoreTolerance[$i] ) )
                {
                    if( abs( $dstR - $ciR ) <= $colorsIgnoreTolerance[$i] && 
                        abs( $dstG - $ciG ) <= $colorsIgnoreTolerance[$i] && 
                        abs( $dstB - $ciB ) <= $colorsIgnoreTolerance[$i] )
                        {
                            continue 2;
                        }
                }
                else 
                {
                    if( $dstR == $ciR && $dstG == $ciG && $dstB == $dstB )
                    {
                        continue 2;
                    }
                }
                
            
            }
            
            if( ($dstR != $srcR || $dstG != $srcG || $dstB != $srcB) )
            {    
                $newR = $dstR + ( ( $srcR - $dstR ) * $fillPercent/100 );
                $newG = $dstG + ( ( $srcG - $dstG ) * $fillPercent/100 );
                $newB = $dstB + ( ( $srcB - $dstB ) * $fillPercent/100 );
                
                $newRGB = imagecolorallocate($dstImg, $newR, $newG, $newB );
                imagesetpixel($dstImg, $x, $y, $newRGB );
            }
        }
    }
}


function imageColorFill( $dstImg, $colorFill, $fillPercent, $colorsIgnore = false, $colorsIgnoreTolerance = false ) {
    /**
 * void imageColorFill();
 * Fill a destiny image with a given color.
 * You can set colors to be ignored, and the level of tolerance of
 * simillar colors to be ignore too.
 * 
 * Created by sn4g <snagnever at gmail dot com>
 * 
 * $dstImg - destiny image of merging, gdImageResource
 * $colorFill - the color of filling, gdColorResource
 * $fillPercent - how strong the $dstImg will be filled with $colorFill. Like an alpha to the color.
 * [$colorIgnore - an array of gd colors to be ignored in the merging, array of gdColorResource]
 * [$colorsIgnoreTolerance - an array containing the tolerance level of the colors on $colorIgnore array]
 *
 * @param gdImageResource $dstImg
 * @param gdImageResource $srcImg
 * @param integer $fillPercent
 * @param [array $colorsIgnore]
 * @param [array $colorsIgnoreTolerance]
 */
    $imgW = imagesx( $dstImg );
    $imgH = imagesy( $dstImg );
    
    $cTransp = imageColorTransparent($dstImg);
    
    for($y=0;$y<$imgH;$y++)
    {
        for($x=0;$x<$imgW;$x++)
        {
            
            $dstRGB = imagecolorat($dstImg, $x, $y);
            
            // if the pixel is transparent, we'll not 'merge' it
            if( $dstRGB == $cTransp )
            {
                continue 1;
            }

            //$dstA = ($dstRGB >> 24) << 1;
            $dstR = $dstRGB >> 16 & 0xFF;
            $dstG = $dstRGB >> 8 & 0xFF;
            $dstB = $dstRGB & 0xFF;
            
            $cR = $colorFill >> 16 & 0xFF;
            $cG = $colorFill >> 8 & 0xFF;
            $cB = $colorFill & 0xFF;
            
            $i = -1;
            while(++$i < count( $colorsIgnore ) )
            {
                $ciR = $colorsIgnore[$i] >> 16 & 0xFF;
                $ciG = $colorsIgnore[$i] >> 8 & 0xFF;
                $ciB = $colorsIgnore[$i] & 0xFF;
                
                
                if( isset( $colorsIgnoreTolerance[$i] ) )
                {
                    if( abs( $dstR - $ciR ) <= $colorsIgnoreTolerance[$i] && 
                        abs( $dstG - $ciG ) <= $colorsIgnoreTolerance[$i] && 
                        abs( $dstB - $ciB ) <= $colorsIgnoreTolerance[$i] )
                        {
                            continue 2;
                        }
                }
                else 
                {
                    if( $dstR == $ciR && $dstG == $ciG && $dstB == $dstB )
                    {
                        continue 2;
                    }
                }
                
            }
            
            if( ($dstR != $cR || $dstG != $cG || $dstB != $cB) )
            {    
                $newR = $dstR + ( ( $cR - $dstR ) * $fillPercent/100 );
                $newG = $dstG + ( ( $cG - $dstG ) * $fillPercent/100 );
                $newB = $dstB + ( ( $cB - $dstB ) * $fillPercent/100 );
                
                $newR = $newR > 255 ? 255 : $newR;
                $newG = $newG > 255 ? 255 : $newG;
                $newB = $newB > 255 ? 255 : $newB;
                
                $newRGB = imagecolorallocate($dstImg, $newR, $newG, $newB );
                imagesetpixel($dstImg, $x, $y, $newRGB );
            }
        }
    }
}


function imageFadeToColor( $dstImg, $fadeColor, $fadeToPercent, $fadeBorder ) {
    /**
 * imageFadeToColor()
 * Creates a fading effect to all sides of the image
 * Depends on: imageFadeToColorTB() and imageFadeToColorRL()
 * 
 * Created by sn4g <snagnever at gmail dot com>
 *
 * $dstImg - destiny image of fading, gdImageResource
 * $fadeColor - the color for fading, gdColorResource
 * $fadeToPercent - the final of fading must be filling that percent.
 * $fadeBorder - width of the border of fading
 * 
 * @param gdImageResource $dstImg
 * @param gdColorResource $fadeColor
 * @param float $fadeToPercent
 * @param integer $fadeBorder
 */
    imageFadeToColorRL($dstImg, $fadeColor, $fadeToPercent, $fadeBorder);
    imageFadeToColorTB($dstImg, $fadeColor, $fadeToPercent, $fadeBorder);
}


function imageFadeToColorRL( $dstImg, $fadeColor, $fadeToPercent, $fadeBorder) {
    /**
 * imageFadeToColorRL()
 * Creates a fading effect to Rigth and Left sides of the image
 * 
 * Created by sn4g <snagnever at gmail dot com>
 *
 * $dstImg - destiny image of fading, gdImageResource
 * $fadeColor - the color for fading, gdColorResource
 * $fadeToPercent - the final of fading must be filling that percent.
 * $fadeBorder - width of the border of fading
 * 
 * @param gdImageResource $dstImg
 * @param gdColorResource $fadeColor
 * @param float $fadeToPercent
 * @param integer $fadeBorder
 */
    $imgW = imagesx( $dstImg );
    $imgH = imagesy( $dstImg );
    
    if( $fadeBorder >= $imgW ) trigger_error("The fade border '\$fadeBorder' must be lower than source's width, {$imgW}px", E_USER_ERROR);
    
    // percentage of fading per pixel line
    $fadePerLine = round($fadeToPercent/$fadeBorder,3);
    
    $curFadePercent = $fadeToPercent;
    for($x=0;$x<=$fadeBorder;$x++)
    {
        for($y=0;$y<$imgH;$y++)
        {
            
            $dstRGB = imagecolorat($dstImg, $x, $y);
            
            //$dstA = ($dstRGB >> 24) << 1;
            $dstR = $dstRGB >> 16 & 0xFF;
            $dstG = $dstRGB >> 8 & 0xFF;
            $dstB = $dstRGB & 0xFF;
            
            $cR = $fadeColor >> 16 & 0xFF;
            $cG = $fadeColor >> 8 & 0xFF;
            $cB = $fadeColor & 0xFF;
            
            $newR = $dstR + ( ( $cR - $dstR ) * $curFadePercent/100 );
            $newG = $dstG + ( ( $cG - $dstG ) * $curFadePercent/100 );
            $newB = $dstB + ( ( $cB - $dstB ) * $curFadePercent/100 );
            
            $newRGB = imagecolorallocate($dstImg, $newR, $newG, $newB );
            imagesetpixel($dstImg, $x, $y, $newRGB );
        }
        $curFadePercent -= $fadePerLine;
    }
    
    
    $curFadePercent = $fadeToPercent;
    for($x=$imgW-1;$x-(($imgW-1)-$fadeBorder)>=0;$x--)
    {
        for($y=0;$y<$imgH;$y++)
        {
            
            $dstRGB = imagecolorat($dstImg, $x, $y);
            
            //$dstA = ($dstRGB >> 24) << 1;
            $dstR = $dstRGB >> 16 & 0xFF;
            $dstG = $dstRGB >> 8 & 0xFF;
            $dstB = $dstRGB & 0xFF;
            
            $cR = $fadeColor >> 16 & 0xFF;
            $cG = $fadeColor >> 8 & 0xFF;
            $cB = $fadeColor & 0xFF;
            
            $newR = $dstR + ( ( $cR - $dstR ) * $curFadePercent/100 );
            $newG = $dstG + ( ( $cG - $dstG ) * $curFadePercent/100 );
            $newB = $dstB + ( ( $cB - $dstB ) * $curFadePercent/100 );
            
            $newRGB = imagecolorallocate($dstImg, $newR, $newG, $newB );
            imagesetpixel($dstImg, $x, $y, $newRGB );
        }
        
        $curFadePercent -= $fadePerLine;
    }    
}


function imageFadeToColorTB( $dstImg, $fadeColor, $fadeToPercent, $fadeBorder ) {
    
    /**
 * imageFadeToColorTB()
 * Creates a fading effect to Tob and Bottom sides of the image
 * 
 * Created by sn4g <snagnever at gmail dot com>
 *
 * $dstImg - destiny image of fading, gdImageResource
 * $fadeColor - the color for fading, gdColorResource
 * $fadeToPercent - the final of fading must be filling that percent.
 * $fadeBorder - width of the border of fading
 * 
 * @param gdImageResource $dstImg
 * @param gdColorResource $fadeColor
 * @param float $fadeToPercent
 * @param integer $fadeBorder
 */
 
    $imgW = imagesx( $dstImg );
    $imgH = imagesy( $dstImg );
    
    if( $fadeBorder >= $imgH ) trigger_error("The fade border '\$fadeBorder' must be lower than source's height, {$imgH}px", E_USER_ERROR);
    
    // percentage of fading per pixel line
    $fadePerLine = round($fadeToPercent/$fadeBorder,3);
    
    $curFadePercent = $fadeToPercent;
    for($y=0;$y<=$fadeBorder;$y++)
    {
        for($x=0;$x<$imgW;$x++)
        {    
            $dstRGB = imagecolorat($dstImg, $x, $y);
            
            //$dstA = ($dstRGB >> 24) << 1;
            $dstR = $dstRGB >> 16 & 0xFF;
            $dstG = $dstRGB >> 8 & 0xFF;
            $dstB = $dstRGB & 0xFF;
            
            $cR = $fadeColor >> 16 & 0xFF;
            $cG = $fadeColor >> 8 & 0xFF;
            $cB = $fadeColor & 0xFF;
            
            $newR = $dstR + ( ( $cR - $dstR ) * $curFadePercent/100 );
            $newG = $dstG + ( ( $cG - $dstG ) * $curFadePercent/100 );
            $newB = $dstB + ( ( $cB - $dstB ) * $curFadePercent/100 );
            
            $newRGB = imagecolorallocate($dstImg, $newR, $newG, $newB );
            imagesetpixel($dstImg, $x, $y, $newRGB );
        }
        $curFadePercent -= $fadePerLine;
    }
    
    $curFadePercent = $fadeToPercent;
    for($y=$imgH-1;$y-(($imgH-1)-$fadeBorder)>=0;$y--)
    {    
        //echo "{$y} {$curFadePercent}\r\n";
        
        for($x=0;$x<$imgW;$x++)
        {    
            $dstRGB = imagecolorat($dstImg, $x, $y);
            
            //$dstA = ($dstRGB >> 24) << 1;
            $dstR = $dstRGB >> 16 & 0xFF;
            $dstG = $dstRGB >> 8 & 0xFF;
            $dstB = $dstRGB & 0xFF;
            
            $cR = $fadeColor >> 16 & 0xFF;
            $cG = $fadeColor >> 8 & 0xFF;
            $cB = $fadeColor & 0xFF;
            
            $newR = $dstR + ( ( $cR - $dstR ) * $curFadePercent/100 );
            $newG = $dstG + ( ( $cG - $dstG ) * $curFadePercent/100 );
            $newB = $dstB + ( ( $cB - $dstB ) * $curFadePercent/100 );
            
            $newRGB = imagecolorallocate($dstImg, $newR, $newG, $newB );
            imagesetpixel($dstImg, $x, $y, $newRGB );
        }
        $curFadePercent -= $fadePerLine;
    }
}


function imageCutout( $dstImg, $colorIgnore, $colorIgnoreTolerance ) {
    /**
 * imageCutout()
 * Creates a 'cutout' effect on a image -- all gets on greyscale but ONE predefined colors
 *
 * Created by sn4g <snagnever at gmail dot com>
 * 
 * $dstImg - destiny image of cutingout, gdImageResource
 * $colorIgnore - color to ignore, gdColorResource
 * $colorIgnoreTolerance - integer for tolerance of that color, integer
 * 
 * @param gdImageResource $dstImg
 * @param gdColorResource $colorIgnore
 * @param integer $colorIgnoreTolerance
 */
    
    $imgW = imagesx( $dstImg );
    $imgH = imagesy( $dstImg );
    
    $cTransp = imageColorTransparent($dstImg);
    
    /*$colorsIgnore = (array) $colorsIgnore;
    $colorsIgnoreTolerance = (array) $colorsIgnoreTolerance;*/
    
    
    for($y=0;$y<$imgH;$y++)
    {
        for($x=0;$x<$imgW;$x++)
        {
            $dstRGB = imagecolorat($dstImg, $x, $y);
            
            // if the pixel is transparent, we'll not 'merge' it
            if( $dstRGB == $cTransp )
            {
                continue 1;
            }

            //$dstA = ($dstRGB >> 24) << 1;
            $dstR = $dstRGB >> 16 & 0xFF;
            $dstG = $dstRGB >> 8 & 0xFF;
            $dstB = $dstRGB & 0xFF;

            $ciR = $colorIgnore >> 16 & 0xFF;
            $ciG = $colorIgnore >> 8 & 0xFF;
            $ciB = $colorIgnore & 0xFF;
                
            if( isset( $colorIgnoreTolerance ) )
            {
                if( abs( $dstR - $ciR ) <= $colorIgnoreTolerance && 
                    abs( $dstG - $ciG ) <= $colorIgnoreTolerance && 
                    abs( $dstB - $ciB ) <= $colorIgnoreTolerance )
                {
                        continue 1;
                    }
            }
            else if( $dstR == $ciR && $dstG == $ciG && $dstB == $ciB )
            {
                continue 1;
            }

            $newC = ($dstR + $dstG + $dstB )/3;
            $newRGB = imagecolorallocate($dstImg, $newC, $newC, $newC );
            imagesetpixel($dstImg, $x, $y, $newRGB );
        }
    }
}


function imageCutoutMultiple( $dstImg, $colorsIgnore, $colorsIgnoreTolerance ) {
    
    /**
 * imageCutoutMultiple()
 * Creates a 'cutout' effect on a image -- all gets on greyscale but SOME predefined colors
 *
 * Created by sn4g <snagnever at gmail dot com>
 * 
 * $dstImg - destiny image of cutingout, gdImageResource
 * $colorsIgnore - array of colors to ignore, array of gdColorResource
 * $colorsIgnoreTolerance - array of integers for tolerance of those colors, array of integer
 * 
 * @param gdImageResource $dstImg
 * @param array of gdColorResource $colorsIgnore
 * @param array of integer $colorsIgnoreTolerance
 */
    $imgW = imagesx( $dstImg );
    $imgH = imagesy( $dstImg );
    
    $cTransp = imageColorTransparent($dstImg);
    
    $colorsIgnore = (array) $colorsIgnore;
    $colorsIgnoreTolerance = (array) $colorsIgnoreTolerance;
    
    $srcImg = imagecreatetruecolor( $imgW, $imgH );
    imagecopy($srcImg, $dstImg, 0, 0, 0, 0, $imgW, $imgH );
    imagepalettecopy($srcImg, $dstImg);
    
    imageToGreyscale($dstImg);
    
    for($y=0;$y<$imgH;$y++)
    {
        for($x=0;$x<$imgW;$x++)
        {
            $srcRGB = imagecolorat($srcImg, $x, $y);
            $dstRGB = imagecolorat($dstImg, $x, $y);
            
            // if the pixel is transparent, we'll not 'merge' it
            if( $dstRGB == $cTransp )
            {
                continue 1;
            }

            //$dstA = ($dstRGB >> 24) << 1;
            $srcR = $srcRGB >> 16 & 0xFF;
            $srcG = $srcRGB >> 8 & 0xFF;
            $srcB = $srcRGB & 0xFF;

            $i = -1;
            while(++$i < count( $colorsIgnore ) )
            {
                $ciR = $colorsIgnore[$i] >> 16 & 0xFF;
                $ciG = $colorsIgnore[$i] >> 8 & 0xFF;
                $ciB = $colorsIgnore[$i] & 0xFF;
                
                if( isset( $colorsIgnoreTolerance[$i] ) )
                {
                    if( abs( $srcR - $ciR ) <= $colorsIgnoreTolerance[$i] && 
                        abs( $srcG - $ciG ) <= $colorsIgnoreTolerance[$i] && 
                        abs( $srcB - $ciB ) <= $colorsIgnoreTolerance[$i] )
                        {
                            $newRGB = imagecolorallocate($dstImg, $srcR, $srcG, $srcB );
                            imagesetpixel($dstImg, $x, $y, $newRGB );
                        }
                }
                else if( $srcR == $ciR && $srcG == $ciG && $srcB == $ciB )
                {
                        $newRGB = imagecolorallocate($dstImg, $srcR, $srcG, $srcB );
                        imagesetpixel($dstImg, $x, $y, $newRGB );
                }
                
            }
        }
    }
}


function imageCutoutNEW( $dstImg, $colorIgnore, $colorIgnoreTolerance ) {
    /**
 * imageCutoutNEW()
 * Creates a 'cutout' effect on a image -- all gets on greyscale but ONE predefined color.
 * New 'algorythm' of color tolarance.
 *
 * Created by sn4g <snagnever at gmail dot com>
 * 
 * $dstImg - destiny image of cutingout, gdImageResource
 * $colorsIgnore - color to ignore, gdColorResource
 * $colorsIgnoreTolerance - integer for tolerance of that color, integer
 * 
 * @param gdImageResource $dstImg
 * @param gdColorResource $colorIgnore
 * @param integer $colorIgnoreTolerance
 */
    $imgW = imagesx( $dstImg );
    $imgH = imagesy( $dstImg );
    
    $cTransp = imageColorTransparent($dstImg);
    
    for($y=0;$y<$imgH;$y++)
    {
        for($x=0;$x<$imgW;$x++)
        {
            
            $dstRGB = imagecolorat($dstImg, $x, $y);
            
            // if the pixel is transparent, we'll not 'merge' it
            if( $dstRGB == $cTransp )
            {
                continue 1;
            }

            $dstR = $dstRGB >> 16 & 0xFF;
            $dstG = $dstRGB >> 8 & 0xFF;
            $dstB = $dstRGB & 0xFF;

            $ciR = $colorIgnore >> 16 & 0xFF;
            $ciG = $colorIgnore >> 8 & 0xFF;
            $ciB = $colorIgnore & 0xFF;
                
            if( isset( $colorIgnoreTolerance ) )
            {
                if( ( $dstR - $ciR ) >= $colorIgnoreTolerance || 
                    ( $dstG - $ciG ) >= $colorIgnoreTolerance ||
                    ( $dstB - $ciB ) >= $colorIgnoreTolerance )
                    {
                        $newC = ($dstR + $dstB + $dstG)/3;
                        $newRGB = imagecolorallocate($dstImg, $newC, $newC, $newC );
                        imagesetpixel($dstImg, $x, $y, $newRGB );
                    }
            }
            
        }
        
    }
}



function imageCutoutPalette( $dstImg, $colorIgnore, $colorIgnoreTolerance, $betterQuality = false ) {
    /**
 * imageCutoutPalette()
 * Creates a 'cutout' effect on a image -- all gets on greyscale but ONE predefined colors.
 * Palette version, works really really faster, but truncates truecolor images to palette
 * images. So, a PNG32 will be converted to a PNG8. JPEG will lose some colors, and works
 * really great on GIF images. $betterQuality param gets a better quality to the image, but
 * costing about the double of time.
 * Palette version of functions takes about 32 times less time to generate the image.
 *
 * Created by sn4g <snagnever at gmail dot com>
 * 
 * $dstImg - destiny image of cutingout, gdImageResource
 * $colorIgnore - color to ignore, gdColorResource
 * $colorIgnoreTolerance - integer for tolerance of that color, integer
 * $betterQuality - boolean, if we should try to get a better quality
 * 
 * @param gdImageResource $dstImg
 * @param gdColorResource $colorIgnore
 * @param integer $colorIgnoreTolerance
 * @param boolean $betterQuality
 */
    
    $cTransp = imageColorTransparent($dstImg);

    // if we don't have a palette, we convert it
    if( !($t = imagecolorstotal($dstImg)) )
    {
        $t = 256;
        imagetruecolortopalette($dstImg, true, $t);
        
        if( $betterQuality )
        {
            $imgW = imagesx( $dstImg );
            $imgH = imagesy( $dstImg );
            
            $auxImage = imagecreatetruecolor( $imgW, $imgH );
            imagecopy($auxImage, $dstImg, 0, 0, 0, 0, $imgW, $imgH );
            imagecolormatch($auxImage,$dstImg);
            imagedestroy($auxImage);
        }
    }
    
    for( $c=0; $c<$t; $c++ )
    {
        $dstRGB = imageColorsForIndex( $dstImg, $c );
        
        // if the pixel is transparent, we'll not 'merge' it
        if( $dstRGB == $cTransp )
        {
            continue 1;
        }
        
        $dstR = $dstRGB["red"];// >> 16 & 0xFF;
        $dstG = $dstRGB["green"];// >> 8 & 0xFF;
        $dstB = $dstRGB["blue"];// & 0xFF;
        
        $ciR = $colorIgnore >> 16 & 0xFF;
        $ciG = $colorIgnore >> 8 & 0xFF;
        $ciB = $colorIgnore & 0xFF;
            
        if( $colorIgnoreTolerance )
        {
            if( abs( $dstR - $ciR ) <= $colorIgnoreTolerance && 
                abs( $dstG - $ciG ) <= $colorIgnoreTolerance && 
                abs( $dstB - $ciB ) <= $colorIgnoreTolerance )
                {
                    continue 1;
                }
        }
        else if( $dstR == $ciR && $dstG == $ciG && $dstB == $ciB )
        {
            continue 1;
        }

        /*if( ($dstR != $dstB || $dstG != $dstR || $dstB != $dstG) )
        {*/    
            $newC = ($dstR + $dstB + $dstG)/3;
            imagecolorset( $dstImg, $c, $newC, $newC, $newC );
        //}
    }
}


function imageCutoutMultiplePalette( $dstImg, $colorsIgnore, $colorsIgnoreTolerance, $betterQuality = false ) {
    /**
 * imageCutoutPaletteMultiple()
 * Creates a 'cutout' effect on a image -- all gets on greyscale but SOME predefined colors.
 * Palette version, works really really faster, but truncates truecolor images to palette
 * images. So, a PNG32 will be converted to a PNG8. JPEG will lose some colors, and works
 * really great on GIF images. $betterQuality param gets a better quality to the image, but
 * costing about the double of time.
 * Palette version of functions takes about 32 times less time to generate the image.
 *
 * Created by sn4g <snagnever at gmail dot com>
 * 
 * $dstImg - destiny image of cutingout, gdImageResource
 * $colorsIgnore - array of colors to ignore, array of gdColorResource
 * $colorsIgnoreTolerance - array of integers for tolerance of those colors, array of integer
 * $betterQuality - boolean, if we should try to get a better quality
 * 
 * @param gdImageResource $dstImg
 * @param array of gdColorResource $colorsIgnore
 * @param array of integer $colorsIgnoreTolerance
 * @param boolean $betterQuality
 */
    
    $cTransp = imageColorTransparent($dstImg);

    $colorsIgnore = (array) $colorsIgnore;
    $colorsIgnoreTolerance = (array) $colorsIgnoreTolerance;
    
    $imgW = imagesx( $dstImg );
    $imgH = imagesy( $dstImg );

    // if we don't have a palette, we convert it
    if( !($t = imagecolorstotal($dstImg)) )
    {
        $t = 256;
        imagetruecolortopalette($dstImg, true, $t);
        
        if( $betterQuality )
        {
            $srcImg = imagecreatetruecolor( $imgW, $imgH );
            imagecopy($srcImg, $dstImg, 0, 0, 0, 0, $imgW, $imgH );
            imagecolormatch($srcImg,$dstImg);
            imagetruecolortopalette($srcImg, true, $t);
            imagepalettecopy($srcImg, $dstImg);
        }
        else 
        {
            $srcImg = imagecreatetruecolor( $imgW, $imgH );
            imagecopy($srcImg, $dstImg, 0, 0, 0, 0, $imgW, $imgH );
            imagetruecolortopalette($srcImg, true, $t);
            imagepalettecopy($srcImg, $dstImg);
        }
    }
    
    imageToGreyscalePalette($dstImg, $betterQuality);
    
    for( $c=0; $c<$t; $c++ )
    {
        $dstRGB = imageColorsForIndex( $dstImg, $c );
        $srcRGB = imageColorsForIndex( $srcImg, $c );
        
        // if the pixel is transparent, we'll not 'merge' it
        if( $dstRGB == $cTransp )
        {
            continue 1;
        }
        
        //$dstA = ($dstRGB >> 24) << 1;
        $srcR = $srcRGB["red"];// >> 16 & 0xFF;
        $srcG = $srcRGB["green"];// >> 8 & 0xFF;
        $srcB = $srcRGB["blue"];// & 0xFF;
        
        $i = -1;
        while(++$i < count( $colorsIgnore ) )
        {
            $ciR = $colorsIgnore[$i] >> 16 & 0xFF;
            $ciG = $colorsIgnore[$i] >> 8 & 0xFF;
            $ciB = $colorsIgnore[$i] & 0xFF;
            
            
            if( isset( $colorsIgnoreTolerance[$i] ) )
            {
                if( abs( $srcR - $ciR ) <= $colorsIgnoreTolerance[$i] && 
                    abs( $srcG - $ciG ) <= $colorsIgnoreTolerance[$i] && 
                    abs( $srcB - $ciB ) <= $colorsIgnoreTolerance[$i] )
                    {
                        //echo "Caso1\r\n";
                        imagecolorset( $dstImg, $c, $srcR, $srcG, $srcB );
                    }
            }
        
        }

    }
}

function imageColorReplace( $dstImg, $colorsToReplace, $colorsForReplace, $colorsTolerance, $strongness ) {
    $cTransp = imageColorTransparent($dstImg);
    
    $imgW = imagesx( $dstImg );
    $imgH = imagesy( $dstImg );
    
    $colorsToReplace = (array) $colorsToReplace;
    $colorsForReplace = (array) $colorsForReplace;
    $colorsTolerance = (array) $colorsTolerance;
    $strongness = (array) $strongness;

    $srcImg = imagecreatetruecolor( $imgW, $imgH );
    imagecopy($srcImg, $dstImg, 0, 0, 0, 0, $imgW, $imgH );
    imagepalettecopy($srcImg, $dstImg);
    
    for($y=0;$y<$imgH;$y++)
    {
        for($x=0;$x<$imgW;$x++)
        {
            
            $dstRGB = imagecolorat($dstImg, $x, $y);
            $srcRGB = imagecolorat($srcImg, $x, $y);
            
            // if the pixel is transparent, we'll not 'merge' it
            if( $dstRGB == $cTransp )
            {
                continue 1;
            }
            
            $srcR = $srcRGB >> 16 & 0xFF;
            $srcG = $srcRGB >> 8 & 0xFF;
            $srcB = $srcRGB & 0xFF;
            
            $i = -1;
            while(++$i < count( $colorsToReplace ) )
            {
                $crR = $colorsToReplace[$i] >> 16 & 0xFF;
                $crG = $colorsToReplace[$i] >> 8 & 0xFF;
                $crB = $colorsToReplace[$i] & 0xFF;
                
                $cnR = $colorsForReplace[$i] >> 16 & 0xFF;
                $cnG = $colorsForReplace[$i] >> 8 & 0xFF;
                $cnB = $colorsForReplace[$i] & 0xFF;
            
                if( isset( $colorsTolerance[$i] ) )
                {
                    if( (abs( $srcR - $crR ) <= $colorsTolerance[$i] && 
                        abs( $srcG - $crG ) <= $colorsTolerance[$i] &&
                        abs( $srcB - $crB ) <= $colorsTolerance[$i] ) )
                    {
                        /*$nR = $srcR + ( ( $cnR - $srcR ) * $strongness[$i]/100 );
                        $nG = $srcG + ( ( $cnG - $srcG ) * $strongness[$i]/100 );
                        $nB = $srcB + ( ( $cnB - $srcB ) * $strongness[$i]/100 );*/
                        
                        /*$srcC = ($srcR + $srcG + $srcB)/3;
                        $srcR = $srcG = $srcB = $srcC;*/
                        
                        $nR = $cnR * ( $strongness[$i]/100 ) + $srcR * ( 1 - $strongness[$i]/100 ); 
                        $nG = $cnG * ( $strongness[$i]/100 ) + $srcG * ( 1 - $strongness[$i]/100 );
                        $nB = $cnB * ( $strongness[$i]/100 ) + $srcB * ( 1 - $strongness[$i]/100 );
                        
                        /*$nR = $srcR + $cnR * (  $strongness[$i]/100 ); 
                        $nG = $srcG + $cnG * (  $strongness[$i]/100); 
                        $nB = $srcB + $cnB * (  $strongness[$i]/100 );*/
                        
                        /*$nR = $cnR + $srcR * ( 1 - $strongness[$i]/100 ); 
                        $nG = $cnG + $srcG * ( 1 - $strongness[$i]/100 ); 
                        $nB = $cnB + $srcB * ( 1 - $strongness[$i]/100 ); */
                        
                        
                        $nR = round( $nR );
                        $nG = round( $nG );
                        $nB = round( $nB );
                        
                        if( false && $srcR > 100 )
                        {
                        echo "<pre>";
                        echo "\$nR = $srcR + $cnR * ( 100 - $strongness[$i]/100 );\n";
                        echo "\$nG = $srcG + $cnG * ( 100 - $strongness[$i]/100 );\n";
                        echo "\$nB = $srcB + $cnB * ( 100 - $strongness[$i]/100 );\n";
                        
                        var_dump( $nR );
                        var_dump( $nG );
                        var_dump( $nB );}
                        
                        
                        /*$nR = ($nR > 255) ? 255 : $nR;
                        $nG = ($nG > 255) ? 255 : $nG;
                        $nB = ($nB > 255) ? 255 : $nB;*/
                        
                        $nC = imagecolorallocate($dstImg, $nR, $nG, $nB );
                        imagesetpixel( $dstImg, $x, $y, $nC );
                    }
            
                }
            }
        }
    }
}

function imageColorReplacePalette( $dstImg, $colorsToReplace, $colorsForReplace, $colorsTolerance, $strongness, $betterQuality = false ) {
    $cTransp = imageColorTransparent($dstImg);
    
    $imgW = imagesx( $dstImg );
    $imgH = imagesy( $dstImg );
    
    $colorsToReplace = (array) $colorsToReplace;
    $colorsForReplace = (array) $colorsForReplace;
    $colorsTolerance = (array) $colorsTolerance;
    $strongness = (array) $strongness;

    // if we don't have a palette, we convert it
    if( !($t = imagecolorstotal($dstImg)) )
    {
        $t = 256;
        imagetruecolortopalette($dstImg, true, $t);
        
        if( $betterQuality )
        {
            $srcImg = imagecreatetruecolor( $imgW, $imgH );
            imagecopy($srcImg, $dstImg, 0, 0, 0, 0, $imgW, $imgH );
            imagecolormatch($srcImg,$dstImg);
            imagetruecolortopalette($srcImg, true, $t);
            imagepalettecopy($srcImg, $dstImg);
        }
        else 
        {
            $srcImg = imagecreatetruecolor( $imgW, $imgH );
            imagecopy($srcImg, $dstImg, 0, 0, 0, 0, $imgW, $imgH );
            imagetruecolortopalette($srcImg, true, $t);
            imagepalettecopy($srcImg, $dstImg);
        }
    }
    

    
    
    for( $c=0; $c<$t; $c++ )
    {
        $srcRGB = imageColorsForIndex( $srcImg, $c );
        $dstRGB = imageColorsForIndex( $dstImg, $c );
        
        // if the pixel is transparent, we'll not 'merge' it
        if( $dstRGB == $cTransp )
        {
            continue 1;
        }
        
        //$dstA = ($dstRGB >> 24) << 1;
        $srcR = $srcRGB["red"];// >> 16 & 0xFF;
        $srcG = $srcRGB["green"];// >> 8 & 0xFF;
        $srcB = $srcRGB["blue"];// & 0xFF;
        
        $i = -1;
        while(++$i < count( $colorsToReplace ) )
        {
            $crR = $colorsToReplace[$i] >> 16 & 0xFF;
            $crG = $colorsToReplace[$i] >> 8 & 0xFF;
            $crB = $colorsToReplace[$i] & 0xFF;
            
            $cnR = $colorsForReplace[$i] >> 16 & 0xFF;
            $cnG = $colorsForReplace[$i] >> 8 & 0xFF;
            $cnB = $colorsForReplace[$i] & 0xFF;
        
            if( isset( $colorsTolerance[$i] ) )
            {
                if( (abs( $srcR - $crR ) <= $colorsTolerance[$i] && 
                    abs( $srcG - $crG ) <= $colorsTolerance[$i] &&
                    abs( $srcB - $crB ) <= $colorsTolerance[$i] ) )
                    {
                        $nR = $srcR + ( ( $cnR - $srcR ) * $strongness[$i]/100 );
                        $nG = $srcG + ( ( $cnG - $srcG ) * $strongness[$i]/100 );
                        $nB = $srcB + ( ( $cnB - $srcB ) * $strongness[$i]/100 );
                        
                        imagecolorset( $dstImg, $c, $nR, $nG, $nB );
                    }
                    else 
                    {

                        
                    }
            }
        }

    }
}

function okFileType($type,$typelist,$name) {
    global $videotypes, $doctypes, $imagetypes, $audiotypes, $error;
    $filetypes=array();
    if ($typelist=='video'){$filetypes=$videotypes;}
    if ($typelist=='image'){$filetypes=$imagetypes;}
    if ($typelist=='document'){$filetypes=$doctypes;}
    if ($typelist=='audio'){$filetypes=$audiotypes;}
    // if filetypes array is empty then let everything through
    if(count($filetypes) < 1)    {
        return true;
    }
    // if no match is made to a valid file types array then kick it back
    elseif(!in_array($type,$filetypes)){
        $error[] = $name.' is not an acceptable file type. It is '.$type.' and has been ignored.';
            return false;
    } else  {                        
        return true;
    }
}

// function to check and move file
function processFile($file,$destination,$newname) {    
    global $error;
    // set full path/name of file to be moved
    $upload_file = $destination.$newname;
    if(file_exists($upload_file)) {
        unlink($upload_file);
        $error.= $file['name'].' - Filename already existed - I wrote over the original with the new one.';
    }
    if(!move_uploaded_file($file['tmp_name'], $upload_file)) {
        // failed to move file
        $error.= 'File upload failed on '.$newname.' - Please try again';
        return false;
    } else {
        // upload OK - change file permissions
        chmod($upload_file, 0755);
        return true;
    }    
}
    
?>
