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
// $Id: reader.php 1255 2004-04-22 17:15:25Z chregu $


/**
* Class for generating xml document
*
* generators are used to generate the initial XML in <map:generate>
* Not much here at the moment. maybe in the future...
*  I'm not sure, if we really need this 
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: reader.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class popoon_components_reader extends popoon_component {

	/**
    * Constructor
    *
    * Does nothing the moment
    *
	*/
	function __construct( &$sitemap) {
		parent::__construct($sitemap);
	}

	/**
    * Initiator, called after construction of object
    *
    *  This method will be called in the start element with the attributes from this element
    *
    *  @param $attribs array	associative array with element attributes
    *  @access public
	*/
    function init($attribs)
    {
    	parent::init($attribs);
	}
    
    /**
    * Does the generation of the xml Document
    *
    * The XML-Document is passed by reference, to save copying of large XML-DOcuments
    *  The generated XML-Document can be a DomDocument-Object or a XML-String, but preferred
    *  is DomDocument.
    *
    * @access public
    * @returns bool
    */
    function start()
    {
    }
}
