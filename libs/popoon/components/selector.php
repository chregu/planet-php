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
// $Id: selector.php 976 2004-04-02 14:33:01Z chregu $

/**
* This is the Base-Class for all selectors
*
* Selectors are used in <map:select> in sitemap.xml
*
*  Selectors match here much the same as Matchers, this is different to cocoon
*   they don't return the hits, but nevermind :)
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: selector.php 976 2004-04-02 14:33:01Z chregu $
* @package  popoon
* @module	selectors
*/
class popoon_components_selector {
    
	public $debug = False;
	public $hit = False;

	function __construct(&$sitemap) {
		$this->sitemap = &$sitemap;
	}

	function init($attribs)
    {
	    $this->attribs = $attribs;
        $debug = $this->getAttrib("debug");
        if (!is_null($debug) && $debug!="no"  )
        {
        	$this->debug = True;
            $GLOBALS["_POPOON_globalContainer"]->debugOutput[] = "---";
            $GLOBALS["_POPOON_globalContainer"]->debugOutput[] = "selector:".get_class($this);

            foreach ($this->attribs as $key => $value)
            {
            	$GLOBALS["_POPOON_globalContainer"]->debugOutput[] = "$key => $value";
            }
			$GLOBALS["_POPOON_globalContainer"]->debugOutput[] = "---";
		}
    }
    
	function match($value) {
	}

    
	function _match($value,$compare)
    {

	   	$matchtype = $this->getAttrib("matchtype");
        $this->printDebug("match input: $compare");        
        $this->printDebug("match against: $value");        		

		if ($matchtype && method_exists($this,"_match_".$matchtype))
        {
        	$this->printDebug("used matchtype: $matchtype");
        	$matches = call_user_func(array($this,"_match_".$matchtype),$value,$compare);
    	}        
        else
        {
	        $this->printDebug("used matchtype: simple");
        	$matches = $this->_match_simple ($value,$compare);
		}
        if (isset($matches[0]))
        {
        	$this->printDebugArray($matches,"MatchHits:");
        	return $matches[0];
		}
        else
        {
			$this->printDebug("No Match");
        	return False;
		}
    }
    
    
    function _match_simple($value,$compare)
    {
	    $value = str_replace("\*","(.*)",preg_quote($value,"/"));
		preg_match("/$value/",$compare,$matches);
        return $matches;
    }

    function _match_regex($value,$compare)
    {
   		preg_match($value,$compare,$matches);
        return $matches;
    }

    function getAttrib($attribute) {
	    if (isset($this->attribs[$attribute]))
        {
//			return $this->attribs[$attribute] = $this->sitemap->translateScheme($this->attribs[$attribute]);
			return $this->sitemap->translateScheme($this->attribs[$attribute]);
			
		}
        else
        {
        	return Null;
		}
	}

    /**
    * Adds an entry to the debug Output
    *  if $this->debug is true
    *
    * @param  mixed  content  text to be added
    * @access private
    */
    function printDebug($content)
    {
		if ($this->debug)
        {
			$GLOBALS["_POPOON_globalContainer"]->debugOutput[] = $content;
    	}
    }

    /**
    * Adds an decomposed Array to the debug Output
    *  if $this->debug is true
    *
    * @param  array array Array to be added
    * @param  string description line to be added before array
    * @access private
    */    
    function printDebugArray($array,$description = "")
    {
		if ($this->debug)
        {
			$GLOBALS["_POPOON_globalContainer"]->debugOutput[] = $description;
        	foreach($array as $key => $value)
            {
				$GLOBALS["_POPOON_globalContainer"]->debugOutput[] = "$key => $value";
			}
    	}
    }
    
}
