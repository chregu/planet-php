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
// $Id: phpsource.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/reader.php");

/**
* Shows the source of a php-file
*
* Shows the source of the php file mentioned in the attribute "src"
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: phpsource.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class reader_phpsource extends reader {


	/**
    * Constructor, does nothing at the moment
    */
	function reader_phpsource (&$sitemap) {
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
    * Just runs the php file
    *
    * @access public
    */
    function start()
    {
		highlight_file($this->getAttrib("src").".php");
	}
}


?>
