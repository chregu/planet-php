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
// $Id: xslt.php 3833 2005-04-06 12:11:47Z chregu $


/**
* Translates xmlfile 
*
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: xslt.php 3833 2005-04-06 12:11:47Z chregu $
* @package  popoon
*/
class popoon_components_transformers_xslt extends popoon_components_transformer {
    
    public $XmlFormat = "DomDocument";
    public $classname = "xslt";
    
    /**
    * Constructor, creates xslt_process
    */
    function __construct ($sitemap) {
        parent::__construct($sitemap);
        
    }

    function DomStart(&$xml)
    {
        parent::DomStart($xml);
        
        $xslfile = $this->getAttrib("src");
        
        $xslDom = new DomDocument();
        if (!$xslDom->load($xslfile)) {
            if (!file_exists($xslfile) ) {
                throw new PopoonFileNotFoundException($xslfile);
            } else if (!is_file($xslfile)) {
                  throw new PopoonIsNotFileException($xslfile);
            } else {
                throw new PopoonXMLParseErrorException($xslfile);  
            }
          
        }

        $xsl = new XsltProcessor();

        if ($this->getParameter("options","registerPhpFunctions")) {
            
            if (($allowed = $this->getParameter("allowedPhpFunctions")) 
                 && (!(!is_array(reset($allowed)) && reset($allowed) == '__all__'))) {
                 foreach ($allowed as $value) {
                    $xsl->registerPhpFunctions($value);
                }
            }
            else { 
                $xsl->registerPhpFunctions();
            }
            
        }
        
	$xsl->importStylesheet($xslDom);
        
	$utfHack = $this->getParameter("options","utfHack");
        $params =$this->getParameter("default");
        
        if ($utfHack) {
            if ($utfHack == 'encode') {
                foreach($params as $key => $value) {
                    $params[$key] = utf8_encode($value);
                }  
            } else {
                foreach($params as $key => $value) {
                    $params[$key] = str_replace(array(chr(4),chr(252)),"",$value);
                }   
            }
            
        }
        
        foreach($params as $key => $value) {
            $xsl->setParameter("",$key,$value);
        }
        $xml = $xsl->transformToDoc($xml);
        if (!$xml) {
            throw new PopoonXSLTParseErrorException( $xslfile);
        }
        
    }
}



?>
