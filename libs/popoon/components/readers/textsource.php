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
// $Id: textsource.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/reader.php");

/**
* Shows the source of a (xml) file
*
* Sends header text/xml or text/plain and the content of the file..
*
* depends on the unix command "file"  for the moment. mime_magic
* could be a solution for 4.3
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: textsource.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class reader_textsource extends reader {


	/**
    * Constructor, does nothing at the moment
    */
	function reader_textsource (&$sitemap) {
		$this->reader($sitemap);
	}

	/**
    * Initiator, called after construction of object
    *
    *  This method will be called in the start element with the attributes from this element
    *
    *  As we just call the parent init method, it's not really needed, 
    *   it's just here for reference
    *
    *  @param $attribs array	associative array with element attributes
    *  @access public
	*/
    function init($attribs)
    {
    	parent::init($attribs);
	}    
	
    /**
    *
    * @access public
    */
    function start()
    {
		$src = $this->getAttrib("src");

		$mimetype = `file -b $src`;
		if (strpos($mimetype,"XML") !== false) {
			header("Content-type: text/xml");
		} else {
			header("Content-type: text/plain");
		}
		print implode("",file ($this->getAttrib("src")));
	}
}


?>
