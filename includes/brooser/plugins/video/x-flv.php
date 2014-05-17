<?php
// Data processing
require('./librairies/getid3/getid3.php');
$getid3 = new getID3;
$getid3->Analyze($fullpath);

$path    = $this->htmlpath('plugins/video/player_flv/player_flv.swf');
$pathxml = $this->htmlpath('plugins/video/player_flv/flv_config.xml');
$x       = $getid3->info['video']['resolution_x'];
$y       = $getid3->info['video']['resolution_y'];
$time    = $getid3->info['playtime_string'];
$bitrate = round($getid3->info['bitrate']/1000);

// Hack for Windows/Linux encoding (dirty)
if (strpos(PHP_OS,'WIN') !== false) $moviepath = rawurlencode(utf8_encode(utf8_encode($htmlpath)));
else $moviepath = rawurlencode(utf8_encode($htmlpath));

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
            <object
            type="application/x-shockwave-flash"
            data="'.$path.'?flv='.$moviepath.'&amp;width=200&amp;height=160"
            width="200"
            height="160"
            >
                <param name="movie" value="'.$path.'?flv='.$moviepath.'&amp;width=200&amp;height=160" />
                <param name="wmode" value="transparent" />
                <param name="FlashVars" value="configxml='.$pathxml.'" />
                <p>The movie require the <a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash"> Flash 8 plugin</a></p>
            </object>
        </div>
        <h2>More info</h2>
        <dl>
        <dt>Width :</dt>
        <dd>'.$x.'px</dd>
        <dt>Height :</dt>
        <dd>'.$y.'px</dd>
        <dt>Length : </dt>
        <dd>'.$time.'</dd>
        <dt>Bitrate :</dt>
        <dd>'.$bitrate.'kbps</dd>
        </dl>
    ');