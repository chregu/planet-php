<?php

class bxStream {
    
    var $html;
    var $position = 0;
    
    function stream_open ($path, $mode, $options, &$opened_path) {
        $path = $this->getPath($path);
        $this->fd = fopen($path, $mode, $options);
        
        if(!$this->fd) {
            return false;
        } 
        return true;
    }
    
    function getPath($path) {
        $url = parse_url($path);
        return BX_PROJECT_DIR."/data". str_replace($url['scheme'].":/","",$path);
    }
    
    
    function stream_read($count) {
        
        return fread($this->fd,$count);
    }
    
    function stream_write($data) {
        return fwrite($this->fd, $data);
    }
    
    
    function stream_tell() {
        return ftell($this->fd);
    }
    
    function stream_eof() {
        return feof($this->fd);
    }
    
    function stream_seek($offset, $whence) {
        return fseek($this->fd,$offset,$whence);
    }
    
    function stream_close() {
        return fclose($this->fd);
    }
    
    function url_stat($url) {
        $path = $this->getPath($url);
        return stat($path);
    }
    function stream_stat() {
        return fstat($this->fd);
    }
}



?>
