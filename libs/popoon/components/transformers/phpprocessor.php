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
// $Id: phpprocessor.php 2870 2004-10-31 09:50:41Z chregu $

/**
* Evaluates php code in form of processing instructions
*
* One can add for example <?php echo "hello world" ?> 
* into the xml code and it gets evaluated. 
*
* Warning: No checking about security risks is done at the moment!
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: phpprocessor.php 2870 2004-10-31 09:50:41Z chregu $
* @package  popoon
*/
class popoon_components_transformers_phpprocessor extends popoon_components_transformer  {
    
   public $XmlFormat = "DomDocument";
   public $PHPErrorMessage = "PHP-Error. See Debug-Output for Details.";
   public $XMLErrorMessage = "XML-Error. PHP didn't produce valid XML. See Debug-Output for Details.";
   public $name = "phpprocessor";
   public $containsPhpPi = false;
    
    function __construct () {
        
    }
    
    function init($attribs)
    {
        parent::init($attribs);
    }
    
    function DomStart(&$xml)
    {
        
        $ctx = new domxpath($xml);
        $res = $ctx->query("//processing-instruction('php')");
        
        if ($res->length > 0) {
            $this->containsPhpPi = true;
        }
        foreach($res as $pi) {
            
            ob_start();
            $ret = @eval($pi->data);
            $parent = $pi->parentNode;
            if ($ret === False)
            {
                $parent->insertBefore($xml->createTextNode($this->PHPErrorMessage),$pi);
                $this->printDebug("PHP Error :");
                $this->printDebug(ob_get_contents());
            }
            else
            {   
                $docfrag = new DomDocument();
                
                
                if (!$docfrag->loadXML("<?xml version='1.0' ?><root>". ob_get_contents()."</root>"))
                {
                    $parent->insertBefore($xml->createTextNode($this->XMLErrorMessage),$pi);
                    $this->printDebug("XML Error :");
                    $this->printDebug(ob_get_contents());
                }
                else
                {
                    
                    $docfrag_root = $docfrag->documentElement;
                    $children = $docfrag_root->childNodes;
                    foreach ( $children as $child) {
                        $parent->insertBefore($xml->importNode($child,true),$pi);
                    }
                }
            }
            $parent->removeChild($pi);
            ob_end_clean();
        }
        
        
        
    }
    /**
     * Generates empty validityObject
     *
     * Transformers were made cachable by default, this one in most cases isn't.
     * So we'll return an empty valdityObject here and return false when checking it.
     * Perhaps the eval()ed code will somehow be enabled to turn on and off its own cachability?
     * Before doing this I'll have to allow both cachable an non-cachable components in a pipeline..
     *
     * @author Hannes Gassert <hannes.gassert@unifr.ch>
     * @return an empty validityObject (aka array)
     */
    function generateValidity(){
        /* if there was a a PHP PI in the xml, do not cache it
            if we return null here, it won't get cached
            this is certainly improvable somehow, as not
            all php pi's can't get cached
        */
        if ($this->containsPhpPi) {
            return null;
        } 
        else {
            return array();
        }
    }

    /**
     * Overwrite the method inherited from transformer and make this component uncachable by default
     *
     * @return bool false
     */
    function checkValidity($validityObject){
        return(true);
    }
} 


?>
