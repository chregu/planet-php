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
// $Id: transformer.php 1255 2004-04-22 17:15:25Z chregu $

/**
 * Base-class for all the transformers
 *
 * Transformers are used in <map:transform> in sitemap.xml and transform
 * an xml-document to another. 
 * Input and Output is therefore xml and passed by reference to the 
 * method DomStart()
 *
 * @author   Christian Stocker <chregu@bitflux.ch>
 * @version  $Id: transformer.php 1255 2004-04-22 17:15:25Z chregu $
 * @package  popoon
 */

abstract class popoon_components_transformer extends popoon_component {
      
    protected function __construct($sitemap) {
        $this->sitemap = $sitemap;
    }

    function DomStart(&$xml) {
    }

    
    /* CACHING STUFF */
    /* Note about transformers: Allthough 'transformer' implements 'cachable', there are transformers
       that are _not cachable_. 'Xforms' and 'Phpprocessor' should certainly not be cached in  */

    /**
     * Generate cacheKey
     *
     * Calls the method inherited from 'Component', adds significant data from map:parameter, if set.
     *
     * @param   array  attributes
     * @param   int    last cacheKey
     * @see     generateKeyDefault()
     */
    public function generateKey($attribs, $keyBefore){
        //add params?
        if(!empty($this->params)) $attribs['params'] = $this->params;
        //call default method
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
    public function generateValidity(){
        $validityObject = $this->attribs;
        //add params?
        if(!empty($this->params)) $attribs['params'] = $this->params; 
        $src = $this->getAttrib("src"); 
        $validityObject["src"] = $src;     
        if($src){
            $validityObject['filemtime'] = filemtime($src);
            $validityObject['fileatime'] = fileatime($src);
        }
        return($validityObject);
    }

    /**
     * Check validity of a validityObject from cache:
     *
     * This implements only the most simple form: If there's no fresher version, take that from cache.
     * I guess we'll need some more refined criteria.. Parameters from map:parameter are taken into account, if present.
     *
     * @return  bool  true if the validityObject indicates that the cached version can be used, false otherwise.
     * @param   object  validityObject
     */
    public function checkValidity($validityObject){
        //check params?
        if(!empty($this->params)){
            $params = $this->params; ksort($params);
            $c_params = $validityObject['params']; ksort($c_params);
        }
        else{ $params = null;
              $c_params = $params; //equal for sure
        }
        return($params == $c_params                &&
               isset($validityObject['src'])       &&
               isset($validityObject['filemtime']) &&
               file_exists($validityObject['src']) &&
               ($validityObject['filemtime'] >= filemtime($validityObject['src'])));
    }

}
