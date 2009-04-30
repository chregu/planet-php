<?php
class PopoonIsNotFileException extends Exception {
    
    function __construct($filename) {
        $this->message = "$filename is not a file.";
        parent::__construct();
    }
}
?>
