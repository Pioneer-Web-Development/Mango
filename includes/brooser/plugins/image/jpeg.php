<?php
// Data processing
require('./librairies/getid3/getid3.php');
$getid3 = new getID3;
 
$getid3->Analyze($fullpath);

$path    = $this->htmlpath('librairies/thumbnail.php');
$model = $date = '';

$x = $getid3->info['video']['resolution_x'];
$y = $getid3->info['video']['resolution_y'];

// EXIF
if (isset($getid3->info['jpg']['exif']['IFD0']['Model'])) {
    $model = $getid3->info['jpg']['exif']['IFD0']['Model'];
}
if (isset($getid3->info['jpg']['exif']['EXIF']['DateTimeOriginal'])) {
    $date  = strftime(self::DATE_FORMAT,date2Timestamp($getid3->info['jpg']['exif']['EXIF']['DateTimeOriginal']));
}

function date2Timestamp($date)
{
    $date = explode(':',str_replace(' ',':',$date));
    return mktime($date[3],$date[4],$date[5],$date[1],$date[2],$date[0]);
}

// Json data
$json = array(
    'style' => '
        #brooser-preview img {
            max-width:90%;
            max-height:190px;
            margin:1em auto;
            display:block;
        }
    ',
    'script' => '',
    'content' => '
        <img src="'.$path.'?src='.$fullpath.'&amp;width=225&amp;height=190" alt="preview" />
        <h2>More info</h2>
        <dl>
            <dt>Width :</dt>
            <dd>'.$x.'px</dd>
            <dt>Height :</dt>
            <dd>'.$y.'px</dd>'.
            (!empty($model)?'<dt>Model :</dt><dd>'.$model.'</dd>':'').
            (!empty($date)?'<dt>Capture date :</dt><dd>'.$date.'</dd>':'')
        .'</dl>
    ');