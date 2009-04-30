<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: phpglobalsclean.php 1905 2004-08-03 06:27:11Z chregu $

include_once("phpglobals.php");

/**
* Return a phpglobals 
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: phpglobalsclean.php 1905 2004-08-03 06:27:11Z chregu $
* @package  popoon
* @module   schemes_phpglobals
*/
function scheme_phpglobalsclean($value)
{
   return popoon_classes_externalinput::basicClean(scheme_phpglobals($value));
   
}

function scheme_phpglobalsclean_onSitemapGeneration($value) {
        $var = '$'.str_replace("[","['",str_replace("]","']",$value));
        $value = "'. isset(".$var.") ? popoon_classes_externalinput::basicClean(".$var . ") : NULL .'";
        return $value;
        
    
    
}
