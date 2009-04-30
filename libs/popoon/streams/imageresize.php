<?php

/** an Image Resizer Stream 
* not perfect yet, but a start
*
* fopen("irs://path/to/your/300x400/file.jpg");
* 
*  then you get a 300x400 scaled image back
*  or you can use
* 
* fopen("irs://path/to/your/300xX/file.jpg");
* fopen("irs://path/to/your/Xx300/file.jpg");
*
* which will keep the ratio.
*
* fopen("irs://path/to/your/file.jpg/300x300/");
*
* A filter would actually be better, but that's a PHP5 thingie only...
*
* No caching is done either right now
*/

class ImageResizeStream {
    
    var $html;
    var $position = 0;
    function stream_open ($path, $mode, $options, &$opened_path) {
        if ($mode != "r") {
            trigger_error ("ImageResizeStream doesn only have read support", E_USER_WARNING);
            return false;
        }
        $url = parse_url($path);
        
        $path = str_replace($url['scheme'].":/","",$path);
        
        if (preg_match("#/([0-9X]+)x([0-9X]+)/#",$path,$matches)) {
            $endImgWidth = $matches[1];
            $endImgHeight = $matches[2];
            
        } 
        $oriImgFile = str_replace($matches[0],"/",$path);

        $imginfo = getimagesize($oriImgFile);
        $oriImgWidth = $imginfo[0];
        $oriImgHeight = $imginfo[1];
        $oriImgFormat = $imginfo[2];
        $oriImgMime = $imginfo['mime'];
        
        $oriImgRatio = round($oriImgWidth / $oriImgHeight,3);
        if ($endImgHeight == "X") {
            $endImgHeight = round($endImgWidth / $oriImgRatio);
        } else if ($endImgWidth == "X") {
            $endImgWidth = round($endImgHeight * $oriImgRatio); 
        }
        
        $new_image = imagecreatetruecolor($endImgWidth, $endImgHeight);
        $ori_image = imageCreateFromJpeg($oriImgFile);
        
        imagecopyresampled($new_image,$ori_image, 0, 0, 0, 0, $endImgWidth,$endImgHeight, $oriImgWidth, $oriImgHeight);
        
        ob_start();
        imageJpeg($new_image, NULL, 75);
        $this->buffer = ob_get_contents();
        ob_end_clean();
        imagedestroy($new_image);
        imagedestroy($ori_image); 
        
        return true;
    }
    
    function stream_read($count) {
        $ret = substr($this->buffer, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }
    function stream_write($data) {
        error_log("no write support");
        return false;        
    }
    
    
    function stream_tell() {
        return $this->position;
    }
    
    function stream_eof() {
        return $this->position >= strlen($this->buffer);
    }
    
    function stream_seek($offset, $whence) {
        switch ($whence) {
            case SEEK_SET:
            if ($offset < strlen($GLOBALS[$this->varname]) && $offset >= 0) {
                $this->position = $offset;
                return true;
            } else {
                return false;
            }
            break;
            
            case SEEK_CUR:
            if ($offset >= 0) {
                $this->position += $offset;
                return true;
            } else {
                return false;
            }
            break;
            
            case SEEK_END:
            if (strlen($GLOBALS[$this->varname]) + $offset >= 0) {
                $this->position = strlen($GLOBALS[$this->varname]) + $offset;
                return true;
            } else {
                return false;
            }
            break;
            
            default:
            return false;
        }
    }
    
    function stream_close() {
        return true;
    }
    function url_stat() {
        return array();
    }
}



?>
