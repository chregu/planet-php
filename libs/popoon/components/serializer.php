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
// $Id: serializer.php 1245 2004-04-21 15:29:39Z chregu $


/**
* Base class for serializers
*
*	Serializers are used for outputting the actual data with <map:serializer>
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: serializer.php 1245 2004-04-21 15:29:39Z chregu $
* @package  popoon
*/

abstract class popoon_components_serializer extends popoon_component {
    protected $contentType = "text/xml";

	protected function _construct($sitemap) {
		parent::__construct($sitemap);
	}

    public function init($attribs) {
        parent::init($attribs);
        $this->sitemap->setContentType($this->contentType);
        $this->sitemap->setCacheHeaders($this->getAttrib("noHttpCaching") == "true");
        foreach ($this->getParameter("header") as $key => $value) {
            if ($key == "HTTP") {
                header("HTTP/1.0 ". $value);
            } else {
                $this->sitemap->setHeader($key,$value);
            }
        }
        
    }

	protected function DomStart(&$xml) {
    }
    

    /* CACHING */

    /**
     * Generate valdityObject
     *
     * ValidityObjects for serializer are made up from the last cacheKey and the 
     * modification time of the serializer itself and it's other attributes.
     * Like it is done now, all serializers are cachable by default, they implement the
     * 'cachable' interface.
     *
     * @return  array  validityObject
     * @param   array  $attribs component attributes
     * @param   string $keyBefore
     * @author Hannes Gassert <hannes.gassert@unifr.ch>
     */
    public function generateValidity(){
        $validityObject = $this->attribs;
        
        /*
        $validityObject['filemtime'] = filemtime('popoon/components/serializers/'. $validityObject['type'].'.php'); //jesses! a hardcoded path! .)
        */
        return($validityObject);
    }

    /**
     * Check validity :)
     *
     * Returns true now, in any case. If there will be serializers who aren't always cachable, they mus provide
     * a checkValidity/generateValdity pair of their own.
     *
     * @param  array  $validityObject needs to have a key 'filemtime'
     */
    public function checkValidity($validityObject){
        /*
        return(isset($validityObject['filemtime']) &&
               ($validityObject['filemtime'] >= filemtime('/path/topopoon/components/serializers/'. $validityObject['type'].'.php')));
        */
        return(true);
    }

    /**
     * Generates cacheKey
     *
     * just an alias for generateKeyDefault
     *
     * @return  string  $cacheKey
     */
    public function generateKey($attribs, $keyBefore){
        return($this->generateKeyDefault($attribs, $keyBefore));
    }

    
}
