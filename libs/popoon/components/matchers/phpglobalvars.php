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
// $Id: phpglobalvars.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/matcher.php");

/**
* Matches one of PHPs super global variables
*
* (like $_SERVER, $_GET, etc...)
*
* Attributes:
*  var:   name of the var, (eg. SERVER for $_SERVER)
*  key:	  name of the to be matched key (eg. REQUEST_URI)
* 
* Example:
*  <map:match type="phpglobalvars" var="SERVER" key="REQUEST_URI" pattern="*">
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: phpglobalvars.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class matcher_phpglobalvars extends matcher
{

    function matcher_phpglobalvars (&$sitemap) {

		parent::matcher($sitemap);
    }
    
    
	function match($value)
    {
		$varname = $this->getAttrib("var");
        if ($varname)
        {
    		$var = &$GLOBALS["_".$varname];
        }
        else
        {
	        $this->printDebug("No global Variable given");
            return False;
        }
        
        $key = $this->getAttrib("key");
		if (isset ($var[$key]))
        {
			return $this->_match($value,$var[$this->getAttrib("key")]);
		}
        else
        {
        	$this->printDebug("\$_$varname"."[$key] does not exist");
            return False;
		}
	}

}
