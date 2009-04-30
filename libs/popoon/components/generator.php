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
// $Id: generator.php 1498 2004-06-01 12:51:40Z chregu $

/**
* Class for generating xml document
*
* generators are used to generate the initial XML in <map:generate>
* Not much here at the moment. maybe in the future...
*  I'm not sure, if we really need this 
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: generator.php 1498 2004-06-01 12:51:40Z chregu $
* @package  popoon
*/
abstract class popoon_components_generator extends popoon_component {

	private $entities;

	/**
    * Constructor
    *
    *
	*/
	protected function __construct($sitemap) {
		parent::__construct($sitemap);
	}

	/**
    * Initiator, called after construction of object
    *
    *  This method will be called in the start element with the attributes from this element
    *
    *  @param $attribs array	associative array with element attributes
	*/
    /*public function init($attribs)
    {
    	parent::init($attribs);
	}*/
    
    /**
    * Does the generation of the xml Document
    *
    * The XML-Document is passed by reference, to save copying of large XML-DOcuments
    *  The generated XML-Document can be a DomDocument-Object or a XML-String, but preferred
    *  is DomDocument.
    *
    * @param mixed XML-Document 
    * @returns bool
    */
    public abstract function DomStart(&$xml);
	
	
	/**
	*
	* Encodes Entities in XML-string
	*
	*/
	
	protected function setEntities($ent=NULL) {
		if(is_array($ent)) {
			$this->entities = $ent;
		} else {
		
			$this->entities = array (	'&OElig;' => '&#338;',
                   						'&oelig;' => '&#339;',
                           				'&Scaron;' => '&#352;',
                           				'&scaron;' => '&#353;',
                           				'&Yuml;' => '&#376;',
                           				'&circ;' => '&#710;',
                           				'&tilde;' => '&#732;',
                           				'&ensp;' => '&#8194;',
                           				'&emsp;' => '&#8195;',
                           				'&thinsp;' => '&#8201;',
                           				'&zwnj;' => '&#8204;',
                           				'&zwj;' => '&#8205;',
                           				'&lrm;' => '&#8206;',
                           				'&rlm;' => '&#8207;',
                           				'&ndash;' => '&#8211;',
                           				'&mdash;' => '&#8212;',
                           				'&lsquo;' => '&#8216;',
                           				'&rsquo;' => '&#8217;',
                           				'&sbquo;' => '&#8218;',
                           				'&ldquo;' => '&#8220;',
                           				'&rdquo;' => '&#8221;',
                           				'&bdquo;' => '&#8222;',
                           				'&dagger;' => '&#8224;',
                           				'&Dagger;' => '&#8225;',
                           				'&permil;' => '&#8240;',
                           				'&lsaquo;' => '&#8249;',
                           				'&rsaquo;' => '&#8250;',
                           				'&euro;' => '&#8364;',
                           				'&bull;' => '&#8226;',
                           				'& ' =>'&amp;',
                           				'<' => '&#60;',
										'&lt;' => '&#60;',
										'>' => '&#62;',
										'&gt;' => '&#62;'
										);
		}
	} 
	
	
	protected function entity_decode($val) {
		if($this->entities == NULL || !is_array($this->entities)) {
			$this->setEntities();
		}
		return strtr($val,$this->entities);
    }
	
	
	
	
}
