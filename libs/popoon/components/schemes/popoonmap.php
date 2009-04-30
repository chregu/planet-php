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
// $Id: popoonmap.php 1494 2004-06-01 08:33:47Z chregu $

/**
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: popoonmap.php 1494 2004-06-01 08:33:47Z chregu $
* @package  popoon
* @module   schemes_config
*/

function scheme_popoonmap($value)
{
    return "not appliable during runtime";
}

function scheme_popoonmap_onSitemapGeneration($value) {
    $var = '$this->options->popoonmap["'.$value.'"]';
      return   "'. isset(".$var.") ? " . $var . " : NULL .'";
   
}
	
