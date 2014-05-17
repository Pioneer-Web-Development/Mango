<?php
// Data processingé
require('./librairies/getid3/getid3.php');
$getid3 = new getID3;
$getid3->Analyze($fullpath);

$path    = $this->htmlpath('plugins/audio/dewplayer/dewplayer.swf');
$title   = utf8_encode($getid3->info['comments']['title'][0]);
$artist  = utf8_encode($getid3->info['comments']['artist'][0]);
$album   = utf8_encode($getid3->info['comments']['album'][0]);
$year    = $getid3->info['comments']['year'][0];
$track   = $getid3->info['comments']['track'][0];
$time    = $getid3->info['playtime_string'];
$bitrate = round($getid3->info['bitrate']/1000);

// Hack for Windows/Linux encoding (dirty)
if (strpos(PHP_OS,'WIN') !== false) $audiopath = rawurlencode(utf8_encode(utf8_encode($htmlpath)));
else $audiopath = rawurlencode(utf8_encode($htmlpath));

// Json data
$json = array(
    'style' => '
        #brooser-preview .object {
            width:200px;
            margin:1em auto;
        }
    ',
    'script' => '',
    'content' => '
        <div class="object">
            <object type="application/x-shockwave-flash" data="'.$path.'?mp3='.$audiopath.'" width="200" height="20">
                <param name="movie" value="'.$path.'?mp3='.$audiopath.'" />
            </object>
        </div>
        <h2>More info</h2>
        <dl>
            <dt>Title : </dt>
            <dd>'.$title.'</dd>
            <dt>Artist : </dt>
            <dd>'.$artist.'</dd>
            <dt>Album : </dt>
            <dd>'.$album.'</dd>
            <dt>Year : </dt>
            <dd>'.$year.'</dd>
            <dt>Track : </dt>
            <dd>'.$track.'</dd>
            <dt>Length : </dt>
            <dd>'.$time.'</dd>
            <dt>Bitrate : </dt>
            <dd>'.$bitrate.'kbps</dd>
        </dl>
    ');