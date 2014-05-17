<?php
// Data processing
list($x,$y) = getimagesize($fullpath); // Get image size
$path    = $this->htmlpath('librairies/thumbnail.php');

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
            <dd>'.$y.'px</dd>
        </dl>
    ');