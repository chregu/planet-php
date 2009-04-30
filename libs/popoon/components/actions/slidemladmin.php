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
// $Id: slidemladmin.php 1697 2004-07-07 16:17:24Z chregu $

/**
* Class for generating xml document
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: slidemladmin.php 1697 2004-07-07 16:17:24Z chregu $
* @package  popoon
*/

class popoon_components_actions_slidemladmin extends popoon_components_action {

	/**
    * Constructor
    *
	*/
	function __construct(&$sitemap) {
		parent::__construct($sitemap);
	}

	function init() {
	}
	
	function act() {
        
        if (!isset($_GET['action'])) {
            return array("message" => "no action defined");
        }
		$src = $this->getParameterDefault("src");
        $page = $this->getParameterDefault("page");
        $dom = new DomDocument();
        $dom->load($src);
        
        $position = str_replace("slide_","",$page);
        $xp = new DomXpath($dom);
        $result = $xp->query("/s:slideset/s:slide[position() = ".$position."]");
        $slide = $result->item(0);
        if (!$slide) {
            return array("message" => "slide not found");
        }
        if ($_GET['action'] == 'del') {
            $slide->parentNode->removeChild($slide);
            $dom->save($src);
            // Not beauty..
            header("Location: ../$page.html");
            return false;
        }
        
        else if ($_GET['action'] == 'new') {
            $newslide = $dom->createElementNs("http://www.oscom.org/2003/SlideML/1.0/","slide");
            $title  = $dom->createElementNs("http://www.oscom.org/2003/SlideML/1.0/","title");
            $title->appendChild($dom->createTextNode("Slide Title"));
            $content  = $dom->createElementNs("http://www.oscom.org/2003/SlideML/1.0/","content");
            $p = $dom->createElementNs("http://www.w3.org/1999/xhtml","p");
            $p->appendChild($dom->createTextNode("Slide Content"));
            $content->appendChild($p);
            
            $newslide->appendChild($title);
            $newslide->appendChild($content);
            
            $slide->parentNode->insertBefore($newslide,$slide);
            $dom->save($src);
            return array("message" => "new slide added");
        }
        
        
        
        
	}
}
