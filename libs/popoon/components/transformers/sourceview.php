<?PHP
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
// | Author: Christian Stocker <chregu@bitflux.ch                         |
// +----------------------------------------------------------------------+
//
// $Id: sourceview.php 1255 2004-04-22 17:15:25Z chregu $

/**
*/
class popoon_components_transformers_sourceview extends popoon_components_transformer  {
    
    public $XmlFormat = "DomDocument";
    
    private $XMLErrorMessage = "XML-Error. PHP didn't produce valid XML. See Debug-Output for Details.";
    
    private $name = "sourceview";
    
    
    function __construct ($sitemap) {
        
        parent::__construct($sitemap);
    }
    
    
    function init($attribs)
    {
        parent::init($attribs);
    }
    
    
    function DomStart(&$xml)
    {
        $defaultMatch = "//xhtml:code";
        $match = $this->getAttrib('match');
        if(is_null($match)){
            $match = $defaultMatch;
        }
        
        $ctx = $xp = new domxpath($xml);
        $ctx->register_namespace("xhtml","http://www.w3.org/1999/xhtml");
        $res = $ctx->query($match);
        
        if ($res->length > 0) {
            
            foreach($res as $code) {
                
                
                $src = $code->getAttribute("src");             
                if ($src) {
                    $codeText = file_get_contents($src);
                    $lineNumbers = $code->getAttribute("lineNumbers");
                    if ($lineNumbers) {
                        $codeText = $this->highlight($codeText);
                    } else {
                        $codeText = highlight_string($codeText);
                    }
                    $parent = $code->parentNode;
                    
                    $docfrag = new DomDocument();
                    $docfrag->loadXML("<?xml version='1.0' ?><div xmlns='http://www.w3.org/1999/xhtml' class='code'><pre>". str_replace("&nbsp;","&#160;",$codeText)."</pre></div>");
                    
                    
                    //$docfrag_root = $docfrag->documentElement;
                    $children = $docfrag->childNodes;
                    foreach ( $children as $child) {
                        $parent->insertBefore($xml->importNode($child,true), $code);
                    }
                    
                    
                    
                    $parent->removeChild($code);
                }
            }
            
        }
        
    }
    
    function highlight($code) {
        $phpAdded = false;
        if (strpos($code,"<?php") === false) {
            $code = "<?php ".$code;
            $phpAdded = true;
        }
        $code = highlight_string($code,true);
        $code = split("<br />",$code);
        $l = count($code);
        $codeText ="";
        for ($i=0; $i < $l; $i++) {
            
            $codeText .= sprintf("<span style='color: black'>%3d: </span>".$code[$i]."\n",$i);
        }
        if ($phpAdded) {
            $codeText = str_replace('&lt;?</span><span style="color: #0000BB">php '."\n","",$codeText); 
        }
        return $codeText;
        
    }
    
} 


?>
