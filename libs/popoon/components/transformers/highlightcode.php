<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the 'License');      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an 'AS IS' BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Hannes Gassert <hannes@mediagonal.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: highlightcode.php 1704 2004-07-07 16:59:36Z chregu $

/**
* Highlights any kind of code highlightable bei PEAR::Text_Highlight
*
* Provides colored HTML-output highlighted according to the rules of 
* PEAR::Text_Highlighter. Languages currently built into that package are:
* CSS, PHP, XML, DIFF, JAVASCRIPT, PYTHON, DTD, MYSQL and SQL, others can be
* added by providing a syntax defintion file.
* Note that you have to include the CSS class definitions used by the
* highlighter in order to see the output in all its beauty.
* See
http://pear.php.net/package/Text_Highlighter/docs/0.4.1/Text_Highlighter/tutorial_using.pkg.html 
* 
* @author   Hannes Gassert <hannes@mediagonal.ch>  
* @version  $Id: highlightcode.php 1704 2004-07-07 16:59:36Z chregu $
* @package  popoon
*/
class popoon_components_transformers_highlightcode extends popoon_components_transformer  {
    
    var $XmlFormat = 'DomDocument';
    
    var $XMLErrorMessage = 'XML-Error. Highlighting did not produce valid XML. See Debug-Output for Details.';
    var $name = 'highlightcode';
    
    function __construct () {        
    }
    
    function init($attribs) {
        parent::init($attribs);
    }
    
    function DomStart(&$xml) {
    
        $defaultMatch = '//*[name() = "code"]';
        $match = $this->getAttrib('match');
        if(is_null($match)){
            $match = $defaultMatch;
        }
        
        $ctx = new domxpath($xml);
        $res = $ctx->query($match);
        
        if ($res->length > 0) {
           include_once('Text/Highlighter.php');
        }
        
        foreach($res as $codeFragment) {
           $language = strtoupper($codeFragment->getAttribute('language'));           
           $highlighter = Text_Highlighter::factory($language);
           $ret =  $highlighter->highlight(wordwrap($codeFragment->textContent));
           
           $docfrag = new DomDocument();
                
           $parent = $codeFragment->parentNode;     
           
           if (!@$docfrag->loadHTML($ret)) {
                    $parent->insertBefore($xml->createTextNode($this->XMLErrorMessage),$codeFragment);
                    $this->printDebug('XML Error :');
                    $this->printDebug($ret);
           }
           else{
                    
                    $docfrag_root = $docfrag->documentElement;
                    $children = $docfrag_root->childNodes;
                    foreach ( $children as $child) {
                        $parent->insertBefore($xml->importNode($child,true),$codeFragment);
                    }
            }            
            $parent->removeChild($codeFragment);            
        }
    }
} 

?>
