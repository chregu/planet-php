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
// $Id: mailobfuscator.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/transformer.php");

/**
* Obfuscates Email adresses a little bit
*
* BETTER USE htmlwithmailobfuscator.
*
* THis version doesn't work very well with libxslt and DomDocuments (don't
*  know why exactly.
*
* * * * * * * *
*
* it just replaces
*	$xml=str_replace("mailto:","&#109;&#97;&#105;&#108;&#116;&#111;&#58;",$xml);
*	$xml=str_replace("@","&#64",$xml);
*
* Does only work on XmlStrings and you can't transform it back to a domObject
*  since then it would convert the entities back to the normal values
* I didn't recognize much speed lossess with this module..
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: mailobfuscator.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class transformer_mailobfuscator extends transformer {

    var $XmlFormat = "XmlString";

    function transformer_mailobfuscator (&$sitemap) {
		
		$this->transformer($sitemap);
    }

    function DomStart(&$xml)
    {
        parent::DomStart($xml);
			$xml=str_replace("mailto:","&#109;&#97;&#105;&#108;&#116;&#111;&#58;",$xml);
			$xml=str_replace("@","&#64",$xml);
/*		$ctx = xpath_new_context($xml);
		$result = $ctx->xpath_eval("//a[starts-with('mailto',href)]");
		foreach ($result->nodeset as $node)
		{
			$href = $node->get_attribute("href");
			$node->set_attribute("href",$href);
		
		}*/

//        $xml = $xsl->process($xml,$xslparams,False,"/tmp/profiling");
    }
}


?>
