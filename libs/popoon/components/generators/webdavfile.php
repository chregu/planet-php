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
// $Id: webdavfile.php 1518 2004-06-03 21:51:55Z chregu $

include_once("popoon/components/generator.php");

/**
* THIS MODULE IS DEPRECATED,
* It should be used as reader and not as generator/serializer
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: webdavfile.php 1518 2004-06-03 21:51:55Z chregu $
* @package  popoon
*/
class popoon_components_generators_webdavfile extends popoon_components_generator {


    /**
    * Constructor, does nothing at the moment
    */
    function __construct ($sitemap) {
        parent::__construct($sitemap);
    }

   
    function init($attribs)
    {
        parent::init($attribs);
    }    
    
    function DomStart(&$xml)
    {
        
        ob_start();
        include_once("popoon/components/generators/webdav/bxcmsng.php");
        
        //bad hacks for webdav server script
        
        /* $webroot is the path from the sitemap root to the webdav dir..
         forexample if you have popoon serving index.php in 
            http://localhost/popoon/bla/
         and your webdav-dir is in 
            http://localhost/popoon/bla/webdav/
         $webroot has to be "webdav"
        */
        
        // strip slashes
        error_log($_SERVER['REQUEST_METHOD']. " ".  $this->sitemap->uri);
        $webroot = preg_replace("#^/*#","",$this->getParameterDefault("webroot"));
        
        // strip webroot from uri and add it to PATH_INFO
        // this is the place where WebDAV_Server looks for the files in the filesystem
        
        $_SERVER["PATH_INFO"] = str_replace($webroot,"",$this->sitemap->uri);
        // add it to scriptname, otherwise the return in ls are not correct
        // the else is a special case, if it's the root file
        $webrootPrepend = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],"$webroot"));
        if ($webroot || $_SERVER["PATH_INFO"]) {
            $_SERVER["SCRIPT_NAME"] = str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]).$webrootPrepend.$webroot;
        } else {
            $_SERVER["SCRIPT_NAME"] = str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]);
        }
        $w = new HTTP_WebDAV_Server_bxcmsng();
        $w->ServeRequest($this->getParameterDefault("fsroot"));
        //error_log("lll".$_SERVER["SCRIPT_NAME"]);
        $xml = ob_get_contents();
        //error_log($xml);
       ob_end_clean();
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
