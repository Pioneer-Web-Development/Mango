<?php
// Data processing
require('./librairies/getid3/getid3.php');
$getid3 = new getID3;

$getid3->Analyze($fullpath);

$dirlist = $filelist = array();
uksort($getid3->info['iso']['files'],'strnatcasecmp');

foreach($getid3->info['iso']['files'] as $file=>$size) {
    // Separate dirs and files
    if(is_array($size)) {
        $icon = $this->htmlpath(self::ICON_DIR.'/dir.png');
        $listing = &$dirlist;
    } else {
        $icon = $this->getIcon($file);
        $listing = &$filelist;
    }        
    $listing[] = '<li><img src="'.$icon.'" />'.$file.'</li>';
}
$listing = implode('',array_merge($dirlist,$filelist));

// Json data
$json = array(
    'style' => '
        #brooser-preview ul {
            list-style:none;
            line-height:1.8em;
            border:1px solid #CCC;
            background:#FFF;
            width:170px;
        }
        
        #brooser-preview li {
            white-space:nowrap;
        }
        
        #brooser-preview img {
            vertical-align:middle;
            padding:0 3px 2px 5px;
        }
    ',
    'script' => '',
    'content' => '
        <dl>
            <dt>Content : </dt>
            <dd>
                <ul>'.$listing.'</ul>
            </dd>
        </dl>
    ');