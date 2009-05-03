<?php

class popoon_helpers_date {
    
    static function daydiff($isodate) {
        $days = floor((time() - strtotime($isodate))/ (3600 * 24));
        if ($days > 7 ) {
            $days = 7;
        }
        return $days;
        
    }
    
}
