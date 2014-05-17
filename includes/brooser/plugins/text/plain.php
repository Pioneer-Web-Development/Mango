<?php
// Data processing
$content=file_get_contents($fullpath,null,null,0,300);

if(isBinary($content)) $content = '';
else $content = htmlentities($content);

/**
  * isBinary
  * Determine if a string contain binary caracters
  *
  * @author     HesaSys Team
  * @copyright  2005-2006 HesaSys Team
  * @license    http://www.fsf.org/licensing/licenses/gpl.txt GNU GPL Version 2
  * @link       http://hesasys.org/
  */
function isBinary($str)
{
    $binary = false;
    for($i = 0; $i < strlen($str); $i++) {
        $chr = ord($str{$i});
        if($chr == 0 or $chr == 255) {
            $binary = true;
            break;
        }
    }
    return $binary;
}

// Json data
$json = array(
    'style' => '
        #brooser-preview textarea {
            overflow:hidden;
            font:9px Verdana, Arial, Helvetica, sans-serif;
            width:90%;
            height:100px;
            border:1px solid #868685;
            background:#D6D6D5;
            margin:1em auto;
            display:block;
        }
        
        #brooser-preview p {
            text-align:center;
        }
    ',
    'script' => '',
    'content' => 
        (!empty($content)?
        '<textarea cols="20" rows="6" readonly="readonly">'.$content.'</textarea>':
        '<p>Preview not available</p>')
    );