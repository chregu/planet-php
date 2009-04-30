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
// $Id: tgz.php 1255 2004-04-22 17:15:25Z chregu $


/**
 * @author   Christian Stocker <chregu@bitflux.ch>
 * @version  $Id: tgz.php 1255 2004-04-22 17:15:25Z chregu $
 * @package  popoon
 */
class popoon_components_readers_tgz extends popoon_components_reader {

    var $attribs = array();
    
	/**
     * Constructor, does nothing at the moment
     */
	function __construct (&$sitemap) {
       parent::__construct($sitemap);
	}

	/**
     * Initiator, called after construction of object
     *
     *  This method will be called in the start element with the attributes from this element
     *
     *  As we just call the parent init method, it's not really needed, 
     *  it's just here for reference
     *
     *  @param $attribs array	associative array with element attributes
     *  @access public
     */
    function init($attribs)
    {
    	parent::init($attribs);
    }   
	
    /**
     * Prints file content 
     *
     * @access public
     */
    function start()
    {
        $mimetype = $this->getAttrib('mime-type');
        $src = $this->getAttrib('src');  
        $this->sitemap->setHeaderAndPrint("Content-Type","application/octet-stream");
        $this->sitemap->setHeaderAndPrint("Content-Disposition","attachement; filename=\"".$this->getAttrib('name')."\""); 
        passthru (escapeshellcmd("tar -czf - $src"));
                  
    }
}


?>
