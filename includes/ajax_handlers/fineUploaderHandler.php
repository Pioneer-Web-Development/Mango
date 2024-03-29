<?php
error_reporting(0);
/****************************************
Example of how to use this uploader class...
You can uncomment the following lines (minus the require) to use these as your defaults.

// list of valid extensions, ex. array("jpeg", "xml", "bmp")
$allowedExtensions = array();
// max file size in bytes
$sizeLimit = 10 * 1024 * 1024;
//the input name set in the javascript
$inputName = 'qqfile'

require('valums-file-uploader/server/php.php');
$uploader = new qqFileUploader($allowedExtensions, $sizeLimit, $inputName);

// Call handleUpload() with the name of the folder, relative to PHP's getcwd()
$result = $uploader->handleUpload('uploads/');

// to pass data through iframe you will need to encode all html tags
header("Content-Type: text/plain");
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

/******************************************/
$baseDir="../../";
if($_POST['action']=='delete')
{
    $path=$_POST['path'];
    $file=$_POST['filename'];
    $json=array();
    $json['baseDir']=$baseDir;
    $json['path']=$path;
    $json['fileName']=$file;
    if(unlink($baseDir.$path.$file))
    {
        $json['status']='success';
    } else {
        $json['status']='error';
    }
    header("Content-Type: text/plain");
    echo htmlspecialchars(json_encode($json), ENT_NOQUOTES);
} else {
    $allowedExtensions = array("jpeg", "xml", "bmp", "png", "jpg", "css", "gif", "js");
    // max file size in bytes
    $sizeLimit = 5000 * 1024 * 1024;
    $uploader = new qqFileUploader($allowedExtensions);
    $dir=$baseDir.$_REQUEST['dir'];
    // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
    $result = $uploader->handleUpload($dir,TRUE); //replace old file

    // to pass data through iframe you will need to encode all html tags
    header("Content-Type: text/plain");
    echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
}


/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    private $inputName;
    
    /**
     * @param string $inputName; defaults to the javascript default: 'qqfile'
     */
    public function __construct($inputName = 'qqfile'){
        $this->inputName = $inputName;
    }
    
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    public function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }

    /**
     * Get the original filename
     * @return string filename
     */
    public function getName() {
        return $_GET[$this->inputName];
    }
    
    /**
     * Get the file size
     * @return integer file-size in byte
     */
    public function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {
    private $inputName;

    /**
     * @param string $inputName; defaults to the javascript default: 'qqfile'
     */
    public function __construct($inputName = 'qqfile'){
        $this->inputName = $inputName;
    }
    
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    public function save($path) {
        return move_uploaded_file($_FILES[$this->inputName]['tmp_name'], $path);
    }
    
    /**
     * Get the original filename
     * @return string filename
     */
    public function getName() {
        return $_FILES[$this->inputName]['name'];
    }
    
    /**
     * Get the file size
     * @return integer file-size in byte
     */
    public function getSize() {
        return $_FILES[$this->inputName]['size'];
    }
}

/**
 * Class that encapsulates the file-upload internals
 */
class qqFileUploader {
    private $allowedExtensions;
    private $sizeLimit;
    private $file;
    private $uploadName;

    /**
     * @param array $allowedExtensions; defaults to an empty array
     * @param int $sizeLimit; defaults to the server's upload_max_filesize setting
     * @param string $inputName; defaults to the javascript default: 'qqfile'
     */
    function __construct(array $allowedExtensions = null, $sizeLimit = null, $inputName = 'qqfile'){
        if($allowedExtensions===null) {
            $allowedExtensions = array();
    	}
    	if($sizeLimit===null) {
    	    $sizeLimit = $this->toBytes(ini_get('upload_max_filesize'));
    	}
    	        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if(!isset($_SERVER['CONTENT_TYPE'])) {
            $this->file = false;	
        } else if (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'multipart/') === 0) {
            $this->file = new qqUploadedFileForm($inputName);
        } else {
            $this->file = new qqUploadedFileXhr($inputName);
        }
    }
    
    /**
     * Get the name of the uploaded file
     * @return string
     */
    public function getUploadName(){
        if( isset( $this->uploadName ) )
            return $this->uploadName;
    }

    /**
     * Get the original filename
     * @return string filename
     */
    public function getName(){
        if ($this->file)
            return $this->file->getName();
    }
    
    /**
     * Internal function that checks if server's may sizes match the
     * object's maximum size for uploads
     */
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die(json_encode(array('error'=>'increase post_max_size and upload_max_filesize to ' . $size)));    
        }        
    }
    
    /**
     * Convert a given size with units to bytes
     * @param string $str
     */
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Handle the uploaded file
     * @param string $uploadDirectory
     * @param string $replaceOldFile=true
     * @returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. $uploadDirectory directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = @$pathinfo['extension'];		// hide notices if extension is empty

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        $ext = ($ext == '') ? $ext : '.' . $ext;
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . DIRECTORY_SEPARATOR . $filename . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        $this->uploadName = $filename . $ext;

        if ($this->file->save($uploadDirectory . DIRECTORY_SEPARATOR . $filename . $ext)){
            return array('success'=>true);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }    
}