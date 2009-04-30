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
// $Id: mixedcontent.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/transformer.php");

/**
* Translates XML-Tags withn XML-Elements into one big XML-Document
*
* Needed for the mixed content stuff in popoon     
*
*
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: mixedcontent.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class transformer_mixedcontent extends transformer {

    var $XmlFormat = "Own";
    
    function transformer_mixedcontent () {
    }
    
    function DomStart(&$xml)
    {
        parent::DomStart($xml);
        if (phpversion("domxml") >= 20030405)
        {
            return true;
        }
        sitemap::var2XMLString($xml);

//this line has to be reviewed, i think, it's to much for- and backwards translating... but at least it works now...
/* maybe it's enough if we just translte lt and amp (and the other 4 entities..*/

      $xml = str_replace(array_keys($GLOBALS["_html_trans"]),array_values($GLOBALS["_html_trans"]),$xml);
    }
    
    
    /**
     * Generate cacheKey
     *
     * Calls the method inherited from 'Component'.
     *
     * No parameters in mixedcontent. and no attribs in most of the cases, 
     *  but we add them anyway. Most important is $keybefore
     *  mixed content always produces the same output with the same input,
     *  therefore no need for further checking
     *
     * @param   array  attributes
     * @param   int    last cacheKey
     * @see     generateKeyDefault()
     */
    function generateKey($attribs, $keyBefore){
        //call default method
        return($this->generateKeyDefault($attribs, $keyBefore));
    }

    /** Generate validityObject  
     *
     *  There is no validity to check for
     *  The only thing could be, if the mixedcontent.php changed... but so what...
     *   If you're playing around with that, just turn caching off..
     *
     * @see  checkvalidity()
     * @return  array  $validityObject contains the components attributes plus file modification time and time of last access.
     */
    function generateValidity(){
        return(array());
    }

    /**
     * Check validity of a validityObject from cache:
     *
     * Nothing to check here, it's always valid
     *
     * @return  bool  true if the validityObject indicates that the cached version can be used, false otherwise.
     * @param   object  validityObject
     */
    function checkValidity($validityObject){
        return (true);
    }    

    
}

$GLOBALS['_html_trans']['&lt;'] = '<';
$GLOBALS['_html_trans']['&gt;'] = '>';
$GLOBALS['_html_trans']['&quot;'] = '"';
$GLOBALS['_html_trans']['&apos;'] = "'";



