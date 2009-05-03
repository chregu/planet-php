<?php

class popoon_helpers_globals {
    
    
    static function GET($name) {
        if (isset($_GET[$name])) {
            
		return $_GET[$name];
        } else {
            return "";
        }
        
    }

static function encodedGET($name) {
	return str_replace(" ","%20",self::GET($name));
}
}
