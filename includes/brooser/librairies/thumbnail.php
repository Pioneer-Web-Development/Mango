<?php
/**
 * Make a thumbnail
 *
 */

$src     = $_GET['src'];
$width   = (int)$_GET['width'];
$height  = (int)$_GET['height'];
$quality = isset($_GET['quality'])?(int)$_GET['quality']:100;

@set_time_limit(86400);
@ini_set("max_execution_time",86400);

$pathInfo=pathinfo(strtolower($src));
$ext=$pathInfo['extension'];
if ($ext == 'jpg') $ext = 'jpeg';

// Not an image, exit
if(!in_array($ext,array('gif','jpeg','png'))) die();

$srcCreate = 'imagecreatefrom'.$ext;
$image = 'image'.$ext;
$contentType = 'image/'.$ext;

// Calculate dimensions
list($origWidth,$origHeight) = getimagesize($src); // Get image size

if ($origWidth > $origHeight) {
    $coeff = $origWidth  / $width;
} else {
    $coeff = $origHeight / $height;
}			
$miniHeight = round($origHeight / $coeff); // Get thumbnail width
$miniWidth  = round($origWidth  / $coeff); // Get thumbnail height

// Image smaller than requested thumbnail, or not suppored gif image, exit
if ($miniWidth > $origWidth || $miniHeight > $origHeight || ($ext == 'gif' && !function_exists('imagegif'))) {
    header('Content-type:'.$contentType);
    echo file_get_contents($src);
    exit();
}

// GD in action !
$miniatureGD = @imagecreatetruecolor($miniWidth, $miniHeight);                         // Create empty image
if(!is_resource($miniatureGD)) $miniatureGD = imagecreate($miniWidth, $miniHeight);    // The same but dirty

$imageGD = $srcCreate($src);                                                           // Load original image

$resample=@imagecopyresampled($miniatureGD, $imageGD, 0,0,0,0, $miniWidth,$miniHeight, $origWidth,$origHeight);   // Resample
if(!$resample) imagecopyresized($miniatureGD, $imageGD, 0,0,0,0, $miniWidth,$miniHeight, $origWidth,$origHeight); // The same but dirty

header('Content-type:'.$contentType);
echo $image($miniatureGD);  // Send thumbnail

imagedestroy($imageGD);     // Flush
imagedestroy($miniatureGD); // Flush