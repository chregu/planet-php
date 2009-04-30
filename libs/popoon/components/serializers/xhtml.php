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
// $Id: xhtml.php 4130 2005-04-28 09:20:39Z chregu $

include_once("popoon/components/serializer.php");

/**
* Documentation is missing at the moment...
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: xhtml.php 4130 2005-04-28 09:20:39Z chregu $
* @package  popoon
*/
class popoon_components_serializers_xhtml extends popoon_components_serializer {

    public $XmlFormat = "Own";
    public $contentType = "text/html; charset=utf-8";

    function __construct (&$sitemap) {
        $this->sitemap = &$sitemap;
    }
    
    function init($attribs) {
        parent::init($attribs);
    }

    function DomStart(&$xml)
    {
        parent::DomStart($xml);
        // if internal request, don't do the usual transformation, just "print" it
        if ($this->sitemap->options->internalRequest) {
            if (is_object($xml)) {
                $this->sitemap->hasFinalDom = true;
            } else {
                print $xml;
            }
            return true;
        }
        $encoding = $this->getParameterDefault("contentEncoding");
        
        if (is_object($xml)) {
                if ($encoding) {
                        $xml->encoding = $encoding;
                }

            $this->sitemap->hasFinalDom = true;
            $xmlstr = $xml->saveXML();
        } else {
            $xmlstr = $xml;
            unset ($xml);
        }
        if ($errhandler = $this->getParameterDefault("outputErrors")) {
            $err = $this->getErrorReporting($errhandler);
            if ($err) {
                $xmlstr = str_replace("</html>",$err."</html>",$xmlstr);
            }
        }
        print $this->cleanXHTML($xmlstr);
        
    }
        
    private function cleanXHTML($xml) {
        /* for some strange reasons, libxml makes an upercase HTML, which the w3c validator doesn't like */
        if ($this->getParameterDefault("stripScriptCDATA") == "true") {
            $xml = $this->stripScriptCDATA($xml);
        }
        if ($this->getParameterDefault("stripBxAttributes") == "true") {
            $xml = $this->stripBxAttributes($xml);   
        }
        if ($this->getParameterDefault("stripXMLDeclaration") == "true") {
		$xml = preg_replace("#<\?xml[^>]*\?>\s*#","",$xml);
        }
        return $this->obfuscateMail(str_replace("DOCTYPE HTML","DOCTYPE html",$xml));
    }

    private function stripScriptCDATA($xml) {
	//strip empty (Whitespace only) CDATA
	$xml = preg_replace("#<!\[CDATA\[\W*\]\]>#","",$xml);
	// strip CDATA just after <script>
        $xml = preg_replace("#(<script[^>]*>)\W*<!\[CDATA\[#","$1",$xml);
	// strip ]]> just before </script>
        return preg_replace("#\]\]>\W*(</script>)#","$1",$xml);
    }
    
    private function stripBxAttributes($xml) {
        return preg_replace("#\sbx[a-zA-Z_]+=\"[^\"]+\"#","",$xml);  
    }
        
    private function obfuscateMail($xml) {
	 if ($this->getParameter('default','obfuscateMail') == 'true') {
                return str_replace('mailto:','&#109;&#97;&#105;&#108;&#116;&#111;&#58;',str_replace('@','&#64;',$xml));
        }
	return $xml;
    }
    
    private function getErrorReporting($class) {
        eval('$err = '.$class.'::getInstance();');
        if ($err->hasErrors()) {
            return $err->getHtml();
        } else {
            return null;
        }
        restore_error_handler();
    }
	
    

        
}


?>
