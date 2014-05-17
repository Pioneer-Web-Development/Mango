<?php
/**
 * Init file
 *
 * Init the directory listing class and execute the requested action
 *
 * @copyright  Copyright (c) 2007 Yannick Croissant
 * @license    MIT-style license (see MIT-LICENSE.txt)
 * @version    0.9
 * @link       http://www.k1der.net/blog/country/tag/brooser
 */
setlocale(LC_ALL,'en_UK');
setlocale(LC_NUMERIC,'en_UK');
setlocale(LC_TIME,'en');
error_reporting(0);
require('Brooser.class.php');

try {
    $brooser = new Brooser();

    switch ($brooser->action) {
        // Browse a directory
        case 'browse':
            echo $brooser->browse();
            break;
        // Preview a file
        case 'preview':
            echo $brooser->preview($_POST['file'], $_POST['mimetype']);
            break;
        // Unknown action
        default:
            throw new Exception('The action "'.$brooser->action.'" does not exists');
    }
}
catch (Exception $e) {
    header("HTTP/1.0 500 Internal server error");
    die($e->getMessage());
}