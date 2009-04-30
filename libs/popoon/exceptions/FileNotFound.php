<?php
class PopoonFileNotFoundException extends Exception {
    
    function __construct($filename) {
        $this->message = "$filename was not found.";
        parent::__construct();
    }
}
?>
