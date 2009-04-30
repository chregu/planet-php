<?php


class TidyStream {
    
    var $html;
    var $position = 0;
    function stream_open ($path, $mode, $options, &$opened_path) {
        $url = parse_url($path);
        $path = str_replace($url['scheme'].":/","",$path);
        $this->path = $path;
        return true;
    }
    
    function stream_read($count) {
        error_log("no read support yet");
        return false;
    }
    
    function stream_write($data) {
        $this->html .= $data;
        $this->position += strlen($data);
        return strlen($data);
    }
    
  
    function stream_tell() {
        return $this->position;
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
         tidy_setopt("output-xhtml",true);
        tidy_setopt("numeric-entities",true);
        if (!tidy_parse_string($this->html)) {
            error_log("tidy error");
            return false;
        }
        tidy_clean_repair();
        
        $html = tidy_get_output();
        $fp = fopen($this->path,"w");
        fwrite($fp,$html);
        fclose($fp);
        return true;
    }
    function url_stat() {
        return array();
    }
}



?>
