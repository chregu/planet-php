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
// $Id: textfile.php 2992 2004-11-17 11:22:11Z philipp $

/**
* This class reads an text-file from the filesystem
*
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: textfile.php 2992 2004-11-17 11:22:11Z philipp $
* @package  popoon
*/
class popoon_components_generators_textfile extends popoon_components_generator {


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
        $xml = new DomDocument();
        $src = $this->getAttrib("src");
        
        $xmlstr = file_get_contents($src);
        if (!$xmlstr) {
            if (!file_exists($src) ) {
                throw new PopoonFileNotFoundException($src);
            } else if (!is_file($src)) {
                throw new PopoonIsNotFileException($src);
            } 
        }

        if($this->getParameterDefault("escapeLTonly") == "true" OR $this->getParameterDefault("escapeEntities") == "true") {
            if($this->getParameterDefault("escapeEntities") == "true") {
                // escape entities
                $xmlstr = str_replace('&', '&amp;', $xmlstr);
            }
            if($this->getParameterDefault("escapeLTonly") == "true") {
                $xmlstr = str_replace(array("<",">"),array("&lt;","&gt;"),$xmlstr);
            }
            $xmlstr = "<?xml version='1.0' encoding='utf-8' ?><text>".$xmlstr."</text>";
        } else {
            $xmlstr = "<?xml version='1.0' encoding='utf-8' ?><text><![CDATA[".$xmlstr."]]></text>";
        }

        if (! $xml->loadXML($xmlstr)) {
            throw new PopoonXMLParseErrorException($src);  
        }
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
