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
// $Id: html2html.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/serializer.php");

/**
* Outputs a HTML Document and obfuscates email adresses a little bit
*
* it just replaces
*	$xml=str_replace("mailto:","&#109;&#97;&#105;&#108;&#116;&#111;&#58;",$xml);
*	$xml=str_replace("@","&#64",$xml);
*
* I didn't recognize much speed lossess with this module..
*
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: html2html.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/

class serializer_html2html extends serializer {

    var $XmlFormat = "Own";
    var $contentType = "text/html";

    function serializer_html2html (&$sitemap) {
        $this->sitemap = &$sitemap;
    }
    
    function init($attribs) {
        parent::init($attribs);
    }

    function DomStart(&$xml)
    {
        
        //ebp.ch has a strange html, which doesn't look good on IE, if we only do 
        // html_dump_mem(), so we have to transform here again.. with some special xslss
        $xsl = domxml_xslt_stylesheet_file(BX_BITLIB_DIR."/php/popoon/components/serializers/html2html/html2html.xsl");
        $xsl->process($xml);
        $xml = $xsl->result_dump_mem($xml);
//        $xml = $xml->html_dump_mem();
        if ($this->getParameter('default','obfuscateMails') == 'true') {
        	$xml=str_replace('mailto:','&#109;&#97;&#105;&#108;&#116;&#111;&#58;',$xml);
//            if (!isset($_SERVER['HTTP_USER_AGENT']) || !preg_match("#MSIE.*Mac#", $_SERVER['HTTP_USER_AGENT'])) {
                 $xml=str_replace('@','&#64;',$xml);
 //           }
        }
        print $xml;            
    }
}


?>
