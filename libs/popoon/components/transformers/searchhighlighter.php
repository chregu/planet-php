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
// $Id: searchhighlighter.php 3706 2005-03-08 01:30:32Z hannes $

include_once("popoon/components/transformer.php");

/**
* Highlights a search string inside a document
*
* Examines the HTTP referrer and checks for a URL parameter named q.
* If present, the document is rewritten, all occurences of q are wrapped
* into <span class="searchHighlight">, except if they occur inside one of the tags
* listed in $tabooTagNames.
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: searchhighlighter.php 3706 2005-03-08 01:30:32Z hannes $
* @package  popoon
*/
class popoon_components_transformers_searchhighlighter extends popoon_components_transformer {

    public $XmlFormat = "Own";

    /**
    * The tags inside of which highlighting is forbidden.
    * @var array
    * @see highlightConditional()
    */
    public $tabooTagNames = array('title', 'meta', 'script', 'style',);


    function __construct(&$sitemap) {
        parent::__construct($sitemap);
    }

    function DomStart(&$xml)
    {
        if (isset($_SERVER['HTTP_REFERER']) || !isset($_GET['q'])) {
           if (isset($_GET['q'])) {
	     	$query = $_GET['q'];
	     }	else if (isset($_SERVER['HTTP_REFERER'])  && strpos($_SERVER['HTTP_REFERER'],"q=")) {

                $ref = parse_url($_SERVER['HTTP_REFERER']);
                parse_str($ref['query'],$para);
                $query =  $para['q'];
	    }

                if (isset($query)) {
                    parent::DomStart($xml);
                    popoon_sitemap::var2XMLString($xml);
                    $strings = explode(" ", $query);
                    $search = array();
                    foreach ($strings as $st) {
                        $search[] = '#(<([^>\s/]+)>[^<]*)('.trim($st).')#i';
                    }

                    $xml = preg_replace_callback($search, array($this, 'highlightConditional') ,$xml);
                }

        }

    }

    protected function highlightConditional($matches){

        $beforeWanted = $matches[1];
        $tagName = strtolower($matches[2]);
        $wanted  = $matches[3];

        if(!in_array($tagName, $this->tabooTagNames)){
            return $beforeWanted . '<span class="searchHighlight">'.$wanted.'</span>';
        }
        else {
            return $beforeWanted . $wanted;
        }

    }

}


?>
