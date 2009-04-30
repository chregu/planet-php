<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the 'License');      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an 'AS IS' BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: i18n.php 3094 2004-12-02 12:57:59Z philipp $

class popoon_classes_i18n {

    static protected $instances = array();
    
    public static function getDriverInstance($src, $lang, $driver = 'xml') {
        if(!isset(self::$instances[$driver][$lang][$src])) {
            $driverClass = "popoon_components_transformers_i18n_$driver"; 
            self::$instances[$driver][$lang][$src] = new $driverClass($src, $lang);       
            
        }
        return self::$instances[$driver][$lang][$src];
    }
    
}
