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
// $Id: resource.php 1518 2004-06-03 21:51:55Z chregu $

include_once("popoon/components/reader.php");

/**
 * @author   Christian Stocker <chregu@bitflux.ch>
 * @version  $Id: resource.php 1518 2004-06-03 21:51:55Z chregu $
 * @package  popoon
 */
class popoon_components_readers_resource extends popoon_components_reader {

    public $attribs = array();
    
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
        $src = str_replace("..","",$this->getAttrib('src'));  
        if ($mimetype == "auto") {
                    $mimetype= popoon_helpers_mimetypes::getFromFileLocation($this->sitemap->uri);
        }
        
        if ($mimetype) {
            $this->sitemap->setHeaderAndPrint("Content-Type","$mimetype");
        }
        if (file_exists($src)) {
            $lastModified = filemtime($src);
            $this->sitemap->setHeaderAndPrint("Last-Modified",gmdate('D, d M Y H:i:s T',$lastModified));
            $this->sitemap->setUserData("file-location",$src);
            if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"]) ) {
                if ($lastModified <= strtotime($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
                    header( 'HTTP/1.1 304 Not Modified' );
                    header("X-Popoon-Cache-Status: Resource Reader 304");
                    return true;
                } 
            }
        }
        
        if (!@readfile($src)) {
            header("HTTP/1.0 404 Not Found");
            popoon::raiseError($this->getAttrib('src') . " could not be loaded", POPOON_ERROR_WARNING);
        }     
        
    }
   
}


?>
