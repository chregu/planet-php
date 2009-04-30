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
// $Id: xml.php 3965 2005-04-14 17:04:59Z bitflux $

/**
* This is a driver for the i18n tranformer
*
* This driver reads from an xml file. It uses the same format as the 
*   one in cocoon. See
*   http://cocoon.apache.org/2.1/userdocs/transformers/i18n-transformer.html#Catalogues+%28Dictionaries%29
*   for some details.
*
* Here's a simple catalog (put it into xml/catalog_de.xml and add src="xml/catalog" to the pipeline as 
*  attribute of <xsl:transform>):
* <?xml version="1.0"?>
* <catalogue xml:lang="de">
*    <message key="Latest Blog Posts">Neuste Blog Posts</message>
* </catalogue>
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: xml.php 3965 2005-04-14 17:04:59Z bitflux $
* @package  popoon
*/


class popoon_components_transformers_i18n_xml {

    protected $catctx = NULL;
    
    function __construct($src,$lang) {
	//cheap win and unix abs path check
        if(defined('BX_OPEN_BASEDIR') && !(substr($src,0,1) == '/' || substr($src,1,1) == ":")) {
            $src = BX_OPEN_BASEDIR.$src;
        }
        if (!$cat = @domdocument::load($src.'_'.$lang.'.xml')) {
            $lang = substr($lang,0,-(strrpos($lang,"_")+1));
            if (!$cat = @domdocument::load($src.'_'.$lang.'.xml')) {
                $cat = @domdocument::load($src.'.xml');
            }
        }
        if($cat instanceof DOMDocument) {
            // resolve xincludes
            $cat->xinclude();
            $this->catctx = new DomXpath($cat);
        }
    }

    function getText($key) {
        if(!isset($this->catctx)) {
            return FALSE;
        }
        $catres = $this->catctx->query('/catalogue/message[@key = "'.$key.'"]');
        if($catres && $catres->length > 0) {
            return $catres->item(0)->nodeValue;
        }
        return false;
    }


}
