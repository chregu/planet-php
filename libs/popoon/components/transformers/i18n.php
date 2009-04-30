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
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: i18n.php 3294 2004-12-29 09:31:01Z chregu $

/**
* A translator, which tries to implement the i18n transformer from cocoon.
*
* See http://cocoon.apache.org/2.1/userdocs/transformers/i18n-transformer.html
*  for an introduction.
*
* If you want to use it, add the following to your sitemap
*
*  <map:transform type="i18n" src="xml/catalog">
*     <map:parameter name="locale" value="{lang}"/>
*     <map:parameter name="driver" value="xml"/>
*  </map:transform>
*
* There are (or will be) different drivers for getting the values,
*  currently only a xml driver is available. See the source comments
*  for more details.
*
* A DB driver is planned.
*  
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: i18n.php 3294 2004-12-29 09:31:01Z chregu $
* @package  popoon
*/
class popoon_components_transformers_i18n extends popoon_components_transformer  {
    
    public $XmlFormat = 'DomDocument';
    
    public $name = 'i18n';
    
    function __construct ($sitemap) {        
        parent::__construct($sitemap);
        if (!defined('I18NNS')) {
            define('I18NNS', 'http://apache.org/cocoon/i18n/2.1');
        }
    }
    
    function init($attribs) {
        parent::init($attribs);
    }
    
    function DomStart(&$xml) {
        
        $src = $this->getAttrib("src");
        $lang = $this->getParameterDefault("locale");
        setlocale(LC_ALL,$lang);
        
        $d = popoon_classes_i18n::getDriverInstance($src, $lang, $this->getParameterDefault("driver"));
        
        $ctx = new domxpath($xml);
        $ctx->registerNamespace("i18n",I18NNS);
        
        
        //check all "normal" i18n: elements
        $res = $ctx->query("//i18n:*");
        foreach($res as $node) {
            switch ($node->localName) {
                    case 'text':
                        $this->methodText($node,$d);
                        break;
                    case 'number':
                        $this->methodNumber($node);
                        break;
                    case 'date-time':
                        $this->methodDateTime($node);
                        break;
            }
        }
        
        //check all i18n attributes
        $res = $ctx->query("//@i18n:attr");
        foreach($res as $node) {
            foreach (explode(" ",$node->value) as $attrName) {
                if ($key = $node->parentNode->getAttribute($attrName)) {
                    if (!$locText = $d->getText($key)) {
                        $locText = $key;
                    } 
                    $node->parentNode->setAttribute($attrName,$locText);
                }
            }
            $node->parentNode->removeAttributeNode($node);
        }

        // check all i18n:translate elements
        $res = $ctx->query("//i18n:translate");
        foreach($res as $node) {
            $this->methodTranslate($node,$ctx);     
        }
        
        
        
    }
    protected function methodText($text,$d) {
        if ($text->hasAttributeNS(I18NNS,"key")) {
            $key = $text->getAttributeNS(I18NNS,"key");   
        } else {
            $key = $text->nodeValue;
        }
        if (!$locText = $d->getText($key)) {
            $locText = $key;
        }
        $text->parentNode->replaceChild($text->ownerDocument->createTextNode( $locText),$text);
    }    

        //i18n:date-time
        /* only i18n:date-time is supported right now
        it uses the strftime format of PHP not the java date format, eg.
        pattern should be "%d:%b:%Y" and not "dd:MMM:yyyy".
        
        short/medium/long/full are also not implemented. Use
        %c, %x and %X as an alternative.
        */


    protected function methodDateTime($node) {
        $pattern = $node->getAttribute("pattern");
        $src = $node->getAttribute("src-pattern");
        $value = $node->getAttribute("value");
        if (!$value) {
            $value = time();
        }
        else if ($src && function_exists("strptime")) {
            $t = strptime($value,$src);
            $value = mktime($t['tm_hour'],$t['tm_min'],$t['tm_sec'],$t['tm_mon'],$t['tm_mday'],$t['tm_year']);
        } else {
            $value = strtotime($value);
        }
        $value = strftime($pattern,$value);
        $node->parentNode->replaceChild($node->ownerDocument->createTextNode($value),$node);
        
    }

        /* <i18n:number type="int-currency-no-unit" value="170374" />
        <i18n:number type="int-currency" value="170374" />
        and
        <i18n:number type="percent" value="1.2" />
        are not supported yet
        */

    protected function methodNumber($node) {
        switch ($node->getAttribute("type")) {
            case "currency":
            if ($digits = $node->getAttribute("fraction-digits")) {
                $value = money_format("%.${digits}n",$node->getAttribute("value"));
            } else {
                $value = money_format("%n",$node->getAttribute("value"));
            }
            break;
            case "printf":
            $value = sprintf($node->getAttribute("pattern"),$node->getAttribute("value"));
        }   
        $node->parentNode->replaceChild($node->ownerDocument->createTextNode($value),$node);
    }
    
    /**
    Example:    
    
    <i18n:translate>    
        <i18n:text>Some {0} was inserted {foo}.</i18n:text>    
        <i18n:param>text </i18n:param>     
        <i18n:param name="foo"><i18n:text>here</i18n:text></i18n:param>
    </i18n:translate>
    
    {1} and {foo} would be the same in the above example..
    
    **/
    
    protected function methodTranslate($node,$ctx) {
        $resParam = $ctx->query("//i18n:param");
        $params = array();
        $i = 0;
        foreach ($resParam as $paramNode) {
            if ($name = $paramNode->getAttribute("name")) {
                $params[$name] = $paramNode->nodeValue;
            }
            $params[$i] =  $paramNode->nodeValue;
            $i++;
            $node->removeChild($paramNode);
        }
        $value = $node->nodeValue;
        $value = preg_replace("/\{([a-zA-Z0-9_]*)\}/e","\$params['$1']",$value);
        $node->parentNode->replaceChild($node->ownerDocument->createTextNode($value),$node);
    }
    
} 

?>
