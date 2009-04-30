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
* Outputs the XML-Document as XML
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: xml.php 3558 2005-02-01 08:15:44Z chregu $
* @package  popoon
*/
class popoon_components_serializers_xml extends popoon_components_serializer {
    
    public $XmlFormat = "Own";
    protected $contentType = "text/xml";
    
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
        if ($this->getParameterDefault("removeDefaultPrefix")) {
                $xml = preg_replace("#(</?)default:#","$1",$xml);
        }
        // Mozilla does not display the XML neatly, if there's a xhtml namespace in it, so we spoof it here (mainly used for XML=1 purposes)
        if ($this->getParameterDefault("trickMozillaDisplay")) {
            print  str_replace("http://www.w3.org/1999/xhtml","http://www.w3.org/1999/xhtml#trickMozillaDisplay",$xml);
        } else {
            print $xml;
        }
    }
}


?>
