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
// $Id: xmlvariable.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/generator.php");

/**
* This class reads an xml-file from the filesystem
*
*  Reads the xml-file stated in the "src" attribute in map:generate
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: xmlvariable.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class generator_xmlvariable extends generator {


	/**
    * Constructor, does nothing at the moment
    */
	function generator_xmlvariable () {
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
    * generates an xml-DomDocument out of the xml-file
    *
    * @access public
    * @returns object DomDocument XML-Document
    */
    function DomStart(&$xml)
    {
		$xml = $this->getAttrib("src");
	    if ($this->debug)
        {
			$this->printDebug($xml);
        }
    	return True;
	}
}


?>
