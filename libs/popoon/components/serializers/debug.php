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
// $Id: debug.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/serializer.php");

/**
* Outputs the content of the debug stack
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: debug.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class serializer_debug extends serializer {

	var $XmlFormat = "Own";

	function serializer_debug (&$sitemap) {
		$this->serializer($sitemap);
	}

    function init($attribs) {
        $this->sitemap->setContentType("text/html");
        parent::init($attribs);
    }
	
    function DomStart(&$xml)
    {
	    parent::DomStart();

		if ($this->debug)
        {
        	print "<pre>";
			print "Debug Output:<br>";
        	foreach ($GLOBALS["_POPOON_globalContainer"]->debugOutput as $line) {
        		print htmlentities($line) ."\n";
			}
		}
	}
}


?>
