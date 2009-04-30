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
// $Id: BX_BITLIB_DIR.php 1339 2004-05-11 09:47:36Z chregu $

/**
* Reads Options/Values from a config file. can be accessed via
*  config://foobar in sitemap
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: BX_BITLIB_DIR.php 1339 2004-05-11 09:47:36Z chregu $
* @package  popoon
* @module   schemes_config
*/

function scheme_BX_BITLIB_DIR($value)
{
	return BX_BITLIB_DIR.$value;
}

function scheme_BX_BITLIB_DIR_onSitemapGeneration($value) {
    return "'.BX_BITLIB_DIR.'".$value;   
}
	
