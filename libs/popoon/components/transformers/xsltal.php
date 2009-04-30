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
// $Id: xsltal.php 1974 2004-08-10 15:08:02Z chregu $


/**
* Translates xmlfile 
*
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: xsltal.php 1974 2004-08-10 15:08:02Z chregu $
* @package  popoon
*/
class popoon_components_transformers_xsltal extends popoon_components_transformer {
    
    public $XmlFormat = "DomDocument";
    public $classname = "xsltal";
    
    /**
    * Constructor, creates xslt_process
    */
    function __construct ($sitemap) {
        parent::__construct($sitemap);
        
    }

    function DomStart(&$xml)
    {
        parent::DomStart($xml);
        
        $talfile = $this->getAttrib("src");
        
        
        $transXsl = new DomDocument();
        $transXsl->load(BX_POPOON_DIR.'/components/transformers/xsltal/tal2xslt.xsl');
        
        $proc = new XsltProcessor();
        $proc->importStylesheet($transXsl);
        
        
        $xslDom = new DomDocument();
        if (!$xslDom->load($talfile)) {
            if (!file_exists($talfile) ) {
                throw new PopoonFileNotFoundException($xslfile);
            } else if (!is_file($talfile)) {
                  throw new PopoonIsNotFileException($talfile);
            } else {
                throw new PopoonXMLParseErrorException($talfile);  
            }
          
        }

        
        $newXsl = $proc->transformToDoc($xslDom);
        
        $xsl = new XsltProcessor();

        if ($this->getParameter("options","registerPhpFunctions")) {
            $xsl->registerPhpFunctions();
        }
        $xsl->importStylesheet($newXsl);
        
        $utfHack = $this->getParameter("options","utfHack");
        $params =$this->getParameter("default");
        
      
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
