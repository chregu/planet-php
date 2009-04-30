<?php
class PopoonXMLParseErrorException extends Exception {
    
    public function __construct($filename) {
        $this->message = "XML Parse Error in $filename";
        $this->userInfo = "";
        // the function check can be removed, once 5.1.0 is really released
        if (version_compare(phpversion(),"5.0.99",">") && function_exists("libxml_get_errors") && libxml_use_internal_errors()  ) {
            $errors = libxml_get_errors();
            if ($errors) {
                foreach ($errors as $error) {
                    
                $this->userInfo .= $error->message;
                if ($error->file) {
                    $this->userInfo .= " in file ".$error->file ." line:".$error->line ;
                }
                $this->userInfo .= "<br/>";
                }
            }
            libxml_clear_errors();
        } else {
            set_error_handler(array($this,"errorHandler"));
            $dom = new DomDocument();
            $dom->load($filename);
            restore_error_handler();
        }
        parent::__construct();
    }
    
    public function errorHandler($errno, $errstr, $errfile, $errline) {
        $pos = strpos($errstr,"]:") ;
        if ($pos) {
            $errstr = substr($errstr,$pos+ 2);
        }
        $this->userInfo .="$errstr<br />\n";
    }
}
?>
