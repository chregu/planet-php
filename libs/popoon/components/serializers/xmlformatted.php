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
// $Id: xmlformatted.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/serializer.php");

/**
* Outputs XML-Document as formatted HTML-String
*
* With this Module, it's possible to view a xml document in any
* webbrowser, for example for debugging...
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: xmlformatted.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class popoon_components_serializers_xmlformatted extends popoon_components_serializer {
	var $XmlFormat = "XmlString";
    var $contentType = "text/html";

	function __construct ($sitemap) {
        $this->sitemap = &$sitemap;
	}

    function init($attribs) {
        parent::init($attribs);
    }
	
    function DomStart(&$xml)
    {
		if (is_object($xml))
		{
			$xml = $xml->dump_mem();
		}
		$xml = $this->get_xml($xml);    
        print "<pre>";
    	print htmlentities($xml);
	}
    function get_xml($xml,$instring = "    ") 
    {
            $xml = preg_replace("/(\>)\n/","$1",$xml);
            $xml = preg_replace("/\>\s*\</",">\n<",$xml);

            $axml = explode("\n",$xml);

            $indent=-1;
            $xmls = "";
            foreach ($axml as $key => $value) {

                if (preg_match("/<[^\/{1}]/",$value)) {
                    $indent++;
                }
            if ($indent < 0)
                $indent = 0;
                $xmls .= str_repeat($instring,$indent);
                if (preg_match("/\<\//",$value) || preg_match("/\/\>/",$value)|| preg_match("/-->/",$value)) {
                    $indent--;
                }
                $xmls .= trim($value)."\n";
            }
           return $xmls;
    }

}


?>
