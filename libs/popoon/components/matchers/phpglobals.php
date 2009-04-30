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
// $Id: phpglobals.php 1706 2004-07-07 17:28:53Z chregu $

include_once("popoon/components/matcher.php");

/**
* Matches one of PHPs super global variables
*
* (like $_SERVER, $_GET, etc...)
*
* Attributes:
*  var:   name of the var
*  key:	  name of the to be matched key (eg. REQUEST_URI)
* 
* Example:
*  <map:match type="phpglobals" var="_SERVER[REQUEST_URI]" pattern="*">
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: phpglobals.php 1706 2004-07-07 17:28:53Z chregu $
* @package  popoon
*/
class popoon_components_matchers_phpglobals extends popoon_components_matcher
{

    function matcher_phpglobals (&$sitemap) {

		parent::matcher($sitemap);
    }
    
    
	function match($value)
    {
		$varname = $this->getAttrib("var");


		if (strpos($varname,"[")) {
//			preg_match("/(.*)\[['\"]*([^'\"]*)['\"]*\]/",$varname,$matches);
			preg_match_all("/(.*)(\[['\"]*([^'\"]*)['\"]*\])+/U",$varname,$matches);
            $depth = count($matches[3]);
            if ($matches[1][0] == '_SESSION') {
                @session_start();
            }

			if ($depth == 1 && isset($GLOBALS[$matches[1][0]][$matches[3][0]])) {
				$globalvar = $GLOBALS[$matches[1][0]][$matches[3][0]];
                
			} 
			else if ($depth == 2 && isset($GLOBALS[$matches[1][0]][$matches[3][0]][$matches[3][1]])) {
				$globalvar = $GLOBALS[$matches[1][0]][$matches[3][0]][$matches[3][1]];
			} 

			else 
			{
				return False;
			}
		}
		return $this->_match($value,$globalvar);
	}

}
