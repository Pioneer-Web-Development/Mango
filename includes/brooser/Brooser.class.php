<?php
/**
 * Directory browser class
 *
 * Browse a server-side directory
 *
 * @copyright  Copyright (c) 2007 Yannick Croissant
 * @license    MIT-style license (see MIT-LICENSE.txt)
 * @version    0.9
 * @link       http://www.k1der.net/blog/country/tag/brooser
 */

class Brooser
{

    const ICON_DIR       = 'icons';
    const MIME_FILE      = 'mime_types.ini';
    const DATE_FORMAT    = '%d/%m/%Y %H:%M';
    const SHOW_DENIED    = true;
    
    protected $_accessFile = 'access.php';

    /**
     * Generates the Brooser object
     */
    public function __construct()
    {
        $this->action     = $this->getAction();
        $this->directory  = $this->getDirectory();
        $this->_mimeTypes = parse_ini_file(self::MIME_FILE);
        $this->_access    = $this->loadAccess();
    }

    /**
     * Get the current action
     *
     * @throws Exception
     */
    protected function getAction()
    {
        if (!isset($_POST['action'])) {
            throw new Exception('The current action cannot be defined');
        }
        return eregi_replace('[^a-z]','',$_POST['action']);
    }

    /**
     * Get the current directory
     *
     * @throws Exception
     */
    protected function getDirectory()
    {    
        $_POST['dir'] = $this->realpath($_POST['dir']);
        if (!isset($_POST['dir'])) {
            throw new Exception('The current directory cannot be defined');
        } else if (!is_dir($_POST['dir'])) {
            throw new Exception('"'.htmlentities($_POST['dir']).'" is not a valid directory');
        }
        return $_POST['dir'];
    }
    
    /**
     * Load the access file
     *
     * @throws Exception
     */
    protected function loadAccess()
    {
        $file = $this->_accessFile;
        
        if (!file_exists($file)) {
            throw new Exception('The access file "'.htmlentities($file).'" do not exists');
        }
        require('./'.$file);
        return array('allow' => $allow, 'denied' => $denied);  
    }
    
    /**
     * Set path for the access file
     */
    public function setAccessFile($file) {
        $this->_accessFile = $file;
        return true;
    }
    
    /**
     * Get path for the access file
     */
    public function getAccessFile($file) {
        return $this->_accessFile;
    }
    
    /**
     * List a directory and retrieve files details
     *
     * @throws Exception
     */
    public function browse($dir='')
    {
        if (empty($dir)) $dir = $this->directory;
        if (!is_dir($dir)) {
            throw new Exception('"'.$dir.'" is not a valid directory');
        }
        if ($this->access($dir)!=true) {
            throw new Exception('"Access to '.$dir.'" is not allowed');
        }
        
        $files = scandir($dir);
        natcasesort($files);
        $dirlist = $filelist = array();
        
        foreach ($files as $file)
        {
            $fullpath = $this->directory.'/'.$file;
            $file = utf8_encode($file);
            $access = $this->access($this->htmlpath($fullpath));
            if(!self::SHOW_DENIED && !$access) continue;
            
            if($file=='.') continue;
            
            if (is_dir($fullpath)) {
                $listing = &$dirlist;
            } else {
                $listing = &$filelist;
            }
            $listing[$file] = array(
                'name'  => $file,
                'date'  => utf8_encode(strftime(self::DATE_FORMAT,filemtime($fullpath))),
                'mime'  => $this->getMimeType($fullpath),
                'icon'  => $this->getIcon($fullpath),
                'size'  => filesize($fullpath),
                'dir'   => $this->htmlpath($this->directory),
                'chmod' => substr(decoct(fileperms($fullpath)),3),
                'access'=> $access
            );
        }
        return json_encode(array_merge($dirlist,$filelist));
    }
    
    /**
     * Detemine the access of the file/directory
     */    
    protected function access($file)
    {
        foreach ($this->_access['allow'] as $rule) {
            if ($this->realpath($rule) == '') continue; // Allowed file do not exist, skipping
            if (strpos($this->realpath($file).'/',$this->realpath($rule).'/')!==false) {
                foreach($this->_access['denied'] as $rule) {
                    if ($this->realpath($rule) == '') continue; // Denied file do not exist, skipping
                    if (strpos($this->realpath($file).'/',$this->realpath($rule).'/')!==false) return false;
                }
                return true;
             }
        }
        return false;
    }

    /**
     * Get the icon associated to the filetype
     */
    protected function getIcon($file)
    {
        if (substr($file, -3, 3)=='/..') {
            return $this->htmlpath(self::ICON_DIR.'/dir_up.png');
        }
        if (is_dir($file)) {
            return $this->htmlpath(self::ICON_DIR.'/dir.png');
        }
        $file = pathinfo($file);
        
        if (isset($file['extension']) && file_exists(realpath(self::ICON_DIR.'/'.$file['extension'].'.png'))) {
            return $this->htmlpath(self::ICON_DIR.'/'.$file['extension'].'.png');
        }
        return $this->htmlpath(self::ICON_DIR.'/default.png');
    }

    /**
     * Get the mime-type of a file
     * return text/plain by default
     */
    protected function getMimeType($file)
    {
        if (is_dir($file)) {
            return 'text/directory';
        }

        $fileinfos = pathinfo(strtolower($file));
        
        if (!isset($fileinfos['extension']) || !isset($this->_mimeTypes[$fileinfos['extension']])) {
            return 'text/plain';
        }
        return $this->_mimeTypes[$fileinfos['extension']];
    }

    /**
     * Get the icon associated to the filetype
     *
     * @throws Exception
     */
    public function preview($file,$mimetype)
    {
        $fullpath = $this->directory.'/'.utf8_decode($file);
        
        if ($this->access($fullpath)!=true) {
            throw new Exception('"Access to '.$file.'" is not allowed');
        }
        
        $htmlpath = $this->htmlpath($this->directory.'/'.utf8_decode($file));
        if (!file_exists($fullpath)) {
            throw new Exception('The file "'.htmlentities($fullpath).'" does not exists');
        }
        
        if (file_exists('plugins/'.$mimetype.'.php')) {
            require('plugins/'.$mimetype.'.php');
        } else {
            require('plugins/text/plain.php');
        }
        return json_encode($json);
    }

    /**
     * Get the filepath relative to the www server root
     */
    protected function htmlpath($path)
    {
        $realpath=str_replace('\\', '/', realpath($path));
        $htmlpathURL='/'.trim(str_replace($_SERVER['DOCUMENT_ROOT'],'',$realpath),'/');
        return $htmlpathURL;
    }
    
    /**
     * Get the absolute filepath
     */
    protected function realpath($path)
    {
        if (strpos($path,"/") === 0 || empty($path)) {
            $path = $_SERVER['DOCUMENT_ROOT'].$path;
        } else {
            $path = realpath($path);
        }
        return str_replace(array('\\','//'),'/',$path);
    }
}