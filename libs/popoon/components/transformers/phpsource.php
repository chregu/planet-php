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
// | Author: Hannes Gassert <hannes@mediagonal.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: phpsource.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/transformer.php");
/**
* Highlights php code where specified by the 'match' attribute
* The default value for that attribute is "//code[@language='php']", which is
* what I use for Slideml processing.
*
* This code is for example doing the highlighting on 
* http://edu.mediagonal.ch/php/php5-lucerne/slide_21.html
*
* Note that you have to use <![CDATA[ ..code.. ]]> in case you have offending
* strings such as "&" or "<?php" in your code.
*
* Security implications: none I know of
*
* @author   Hannes Gassert <hannes@mediagonal.ch>
* @version  $Id: phpsource.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class transformer_phpsource extends transformer  {

    var $XmlFormat = "DomDocument";
  
    var $XMLErrorMessage = "XML-Error. PHP didn't produce valid XML. See Debug-Output for Details.";
  
    var $name = "phpsource";
  
    
    function transformer_phpsource (&$sitemap) {
		$this->transformer($sitemap);
    }

    
    function init($attribs)
    {
        parent::init($attribs);
    }

    
    function DomStart(&$xml)
    {
        $defaultMatch = "//code[@language='php']";
        $match = $this->getAttrib('match');
        if(is_null($match)){
            $match = $defaultMatch;
        }
        
        $ctx = xpath_new_context($xml);
        $res = $ctx->xpath_eval($match);

        if (count($res->nodeset) > 0) {
        
            foreach($res->nodeset as $phpCode) {
                
                $phpOpenTag  = '<?php';
                $phpCloseTag = '?>';
                
                            
                $phpCodeString =  $phpCode->get_content();
                
                //add <?php if neccessary
                if(!preg_match('/^\s*'. preg_quote($phpOpenTag) .'/', $phpCodeString)){
                    $phpCodeString = "$phpOpenTag\n". $phpCodeString;
                }
                
                //add ? > if need be
                if(!preg_match('/'. preg_quote($phpCloseTag) . '\s*$/', $phpCodeString)){
                    $phpCodeString .= "\n$phpCloseTag" ;
                }
               
                
                
                $phpCodeString = wordwrap($phpCodeString);
                $ret =  highlight_string($phpCodeString, true);
               
                
                $ret = str_replace('&nbsp;', '&#160;', $ret); // any better way to do this?
                
                
                $parent = $phpCode->parent_node();
                
                if (!$docfrag = domxml_open_mem("<?xml version='1.0' ?>". $ret)){
                    
                    $parent->insert_before($xml->create_text_node($this->XMLErrorMessage),$phpCode);
                    $this->printDebug("XML Error :");
                    $this->printDebug($this->PHPErrorMessage);
                    
                }
                else{
    
                        $docfrag_root = $docfrag->document_element();
                        $children = $docfrag_root->child_nodes();
                        foreach ( $children as $child) {
                            $parent->insert_before($child, $phpCode);
                        }
                        
                    }
                
                $parent->remove_child($phpCode);
            }
            
        }
     
    }
  
} 


?>
