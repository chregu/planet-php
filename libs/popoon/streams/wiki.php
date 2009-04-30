<?php


class WikiStream {
    
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
        require_once 'Text/Wiki.php';
      
        $options = array();
        $options['view_url'] = "index.php?page=";
      
        $options['pages'] = array();
        $wiki = new Text_Wiki($options);
      
        $output = $wiki->transform($this->html);
        $html = '<html>

    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
        <title>Text_Wiki::' . $page .'</title>
        <link rel="stylesheet" href="stylesheet.css" type="text/css" />
    </head>
    
    <body>'.
         str_replace("&nbsp;","&#160;",$output) 
        .'
    </body></html>
    ';
        $fp = fopen("bx:/".$this->path,"w");
        fwrite($fp, $this->html);
        fclose($fp);
        
        $fp = fopen(str_replace(".wiki",".xhtml","bx:/".$this->path),"w");
        fwrite($fp,$html);
        fclose($fp);
        
        
        return true;
    }
    function url_stat() {
        return array();
    }
}



?>
