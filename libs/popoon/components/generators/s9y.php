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
// $Id: s9y.php 1349 2004-05-12 06:49:05Z chregu $

/**
* This class reads an xml-file from the filesystem
*
*  Reads the xml-file stated in the "src" attribute in map:generate
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: s9y.php 1349 2004-05-12 06:49:05Z chregu $
* @package  popoon
*/
class popoon_components_generators_s9y extends popoon_components_generator {


    /**
    * Constructor, does nothing at the moment
    */
    function __construct ($sitemap) {
        parent::__construct($sitemap);
    }

    /**
    * Initiator, called after construction of object
    *
    *  This method will be called in the start element with the attributes from this element
    *
    *  As we just call the parent init method, it's not really needed, 
    *   it's just here for reference
    *
    *  @param $attribs array    associative array with element attributes
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
        global $serendipity;
        $serendipity['serendipityPath'] = BX_PROJECT_DIR."/s9y/";
        $old_include = @ini_get('include_path');
        $old_dir = getcwd();
	$src =  $this->getAttrib("src");
	if (substr($src,-4) == "html") {
		$_SERVER['QUERY_STRING'] = $src;
		$src="index.php";		
	}
	else if (substr($src,-3) != "php") {
		$src = "index.php";
	}
        chdir($serendipity['serendipityPath']);
        @ini_set('include_path', $serendipity['serendipityPath'] . PATH_SEPARATOR . $serendipity['serendipityPath'] . 'bundled-libs/' . PATH_SEPARATOR . $old_include);
        ob_start();	
	require $src;
	if ($src == "index.php") {
		 serendipity_plugin_api::generate_plugins('right','div');
	}
        $blog_data = ob_get_contents();
        ob_end_clean();
        @ini_set($old_include);
        chdir($old_dir);
        $xml = new DomDocument();
        @$xml->loadHTML($blog_data);
        //$xml = xmldocfile($this->getAttrib("src"));
        return True;
    }

    
    /* CACHING STUFF */

    /**
     * Generate cacheKey
     *
     * Calls the method inherited from 'Component'
     *
     * @param   array  attributes
     * @param   int    last cacheKey
     * @see     generateKeyDefault()
     */
    function generateKey($attribs, $keyBefore){
        return($this->generateKeyDefault($attribs, $keyBefore));
    }

    /** Generate validityObject  
     *
     * This is common to all "readers", you'll find the same code there.
     * I'm thinking about making a method in the class component named generateValidityFile() or alike
     * instead of having the same code everywhere..
     *
     * @author Hannes Gassert <hannes.gassert@unifr.ch>
     * @see  checkvalidity()
     * @return  array  $validityObject contains the components attributes plus file modification time and time of last access.
     */
    function generateValidity(){
        $validityObject = $this->attribs;
        $src = $this->getAttrib("src");
        $validityObject['filemtime'] = filemtime($src);
        $validityObject['fileatime'] = fileatime($src);
        return($validityObject);
    }

    /**
     * Check validity of a validityObject from cache
     *
     * This implements only the most simple form: If there's no fresher version, take that from cache.
     * I guess we'll need some more refined criteria..
     *
     * @return  bool  true if the validityObject indicates that the cached version can be used, false otherwise.
     * @param   object  validityObject
     */
    function checkValidity($validityObject){
        return(isset($validityObject['src'])       &&
               isset($validityObject['filemtime']) &&
               file_exists($validityObject['src']) &&
               ($validityObject['filemtime'] == filemtime($validityObject['src'])));
    }

}


?>
