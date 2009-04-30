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
// $Id: html.php 1449 2004-05-25 09:57:12Z chregu $


/**
* Documentation is missing at the moment...
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: html.php 1449 2004-05-25 09:57:12Z chregu $
* @package  popoon
*/
class popoon_components_serializers_html extends popoon_components_serializer {

    public $XmlFormat = "Own";
    public $contentType = "text/html";

    function __construct ($sitemap) {
        $this->sitemap = $sitemap;
    }
    
    function init($attribs) {
        parent::init($attribs);
    }

    function DomStart(&$xml)
    {
        
        if (is_object($xml))
        {
               $xml = $xml->saveHTML();
        }
        else if ($this->getAttrib("mode") == "dom") {
            sitemap::var2XMLObject($xml);
               $xml = $xml->saveHTML();
        }
        // strlen is wrong, if we use transsid, because php adds SID after every link
        // SID checks, if cookies are allowed or not
        if (!defined('SID') || (defined('SID') && SID == "")) {
            //$this->sitemap->setHeaderAndPrint("Content-Length",strlen($xml));
        }
        print $this->obfuscateMail($xml);            
    }
  function obfuscateMail($xml) {
         if ($this->getParameter('default','obfuscateMail') == 'true') {
                return str_replace('mailto:','&#109;&#97;&#105;&#108;&#116;&#111;&#58;',str_replace('@','&#64;',$xml));
        }
        return $xml;
    }
}


?>
