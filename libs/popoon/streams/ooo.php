<?php


class OooStream {
    
    var $html;
    var $position = 0;
    function stream_open ($path, $mode, $options, &$opened_path) {
        $url = parse_url($path);
        $path = str_replace($url['scheme'].":/","",$path);
        $this->tmpname = $path;
        $this->fp = fopen($this->tmpname,"w");
        $this->path = $path;
        return true;
    }
    
    function stream_read($count) {
        return false;
    }
    
    function stream_write($data) {
        return fwrite($this->fp,$data);
        
    }
    
  
    function stream_tell() {
        return ftell($this->fp);
    }
    
    function stream_seek($offset, $whence) {
        return fseek($this->fp,$offset, $whence);
    }
    
    
    function stream_close() {
        error_log("close");
        fclose($this->fp);
        $la = exec (escapeshellcmd("unzip -o " . escapeshellarg($this->tmpname). " content.xml -d " . BX_PROJECT_DIR. "/data"));
        if ($la) {
       //unlink($this->tmpname);
        
        $xsl = domxml_xslt_stylesheet_file(BX_POPOON_DIR."/popoon/components/generators/webdav/ooo/ooo2html.xsl");
        
        $xml= domxml_open_file( BX_PROJECT_DIR. "/data/content.xml");
        $xml = $xsl->process($xml);
        $xsl->result_dump_file($xml,str_replace(".sxw",".xhtml",$this->path));
       }
       return true;
    }
    function url_stat() {
        return array();
    }
}



?>
