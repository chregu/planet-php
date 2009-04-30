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
// $Id: requestmethod.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/selector.php");

/**
* Matches an  uri 
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: requestmethod.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class selector_requestmethod extends selector
{

    function selector_requestmethod (&$sitemap)
    {
		parent::selector($sitemap);
    }

	function match($value)
    {
		return $this->_match($value,$_SERVER["REQUEST_METHOD"]);
	}

}
