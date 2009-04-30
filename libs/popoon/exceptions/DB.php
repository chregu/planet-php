<?php
class PopoonDBException extends Exception {
    
    function __construct($err) {
        $this->message = $err->getMessage();
        //don't leak username:password to the outside
        
        $this->userInfo =   preg_replace("#//([^:]*):([^\@^:]*)\@#","//*******:********@",$err->getUserInfo());
         
        parent::__construct();
    }
}
?>
