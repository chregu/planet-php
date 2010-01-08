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
// $Id: xml.php 3558 2005-02-01 08:15:44Z chregu $

/**
* Outputs the XML-Document as text
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: xml.php 3558 2005-02-01 08:15:44Z chregu $
* @package  popoon
*/
class popoon_components_serializers_json extends popoon_components_serializer {
    
    public $XmlFormat = "Own";
    protected $contentType = "text/plain";
    
    function __construct (&$sitemap) {
        $this->sitemap = &$sitemap;
    }
    
    function init($attribs) {
        parent::init($attribs);
    }
    
    function DomStart(&$xml)
    {
        parent::DomStart($xml);
        if (is_object($xml))
        {
            $this->sitemap->hasFinalDom = true;
            $xml = str_replace("HTML","html",$xml->saveXML());
        }
       
        $xmlstring = '<?xml version="1.0"?>';
        if (substr($xml,0,strlen($xmlstring)) == $xmlstring) {
             $xml = substr($xml,strlen($xmlstring));
        }
        print str_replace("\n"," ",$xml);
    }
}


?>
