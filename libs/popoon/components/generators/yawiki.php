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
// $Id: yawiki.php 1357 2004-05-13 10:05:11Z chregu $

/**
* This class integrates primitive yawiki support
*
* First, get yawiki running by itself. It has heavy dependencies on
*  a lot of PEAR Packages
*
* Copy Yawp.conf.php to your document root from your popoon installation
*
* Adjust the config-file. I had to write
*   header     =  /usr/local/apache/htdocs/yawiki/tpl/header.tpl.php
* as 
*   header     =  %DOCUMENT_ROOT%yawiki/tpl/header.tpl.php
* didn't work
*
* add this to your sitemap
    <map:pipeline>
        <map:match   type="uri" pattern="wiki/*">
            <map:generate type="yawiki" src="{1}">
                <map:parameter name="yawikiRoot" value="/usr/local/apache/htdocs/yawiki"/>
            </map:generate>
            <map:transform type="libxslt" src="BX_PROJECT_DIR://themes/{config://theme}/yawiki.xsl"/>
            <map:serialize type="html">
                <map:parameter name="obfuscateMail" value="true"/>
            </map:serialize>
         </map:match>
     </map:pipeline>
*
*
* Test, if it works, then
* adjust the xsl/css to your needs
* 
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: yawiki.php 1357 2004-05-13 10:05:11Z chregu $
* @package  popoon
*/
class popoon_components_generators_yawiki extends popoon_components_generator {
    
    
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
        $yawiki_path = $this->getParameterDefault("yawikiRoot");
        
        $old_dir = getcwd();
        $src =  $this->getAttrib("src");
       /* if (substr($src,-4) == "html") {
            $_SERVER['QUERY_STRING'] = $src;
            $src="index.php";		
        }
        */
        if (substr($src,-3) != "php") {
            $src = "index.php";
        }
        chdir($yawiki_path);
        ob_start();	
        require $src;
        $blog_data = ob_get_contents();
        ob_end_clean();
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
