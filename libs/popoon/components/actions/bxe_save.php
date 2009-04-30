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
// $Id: bxe_save.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/action.php");
/**
* Class for generating xml document
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: bxe_save.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/

class action_bxe_save extends action {

    /**
    * Constructor
    *
    */
    function action_bxe_save(&$sitemap) {
        $this->action($sitemap);
    }

    function init() {
    }
    
    function act() {
        
        include_once("bitlib/xml/xml2db.php");
        include_once("bitlib/functions/debug.php");
        
        // read data from php://input stream
        $xml = "";
        $fd = fopen("php://input","r");
        while ($line = fread($fd,2048)) {
            $xml .= $line;
        }
        
        $xslfile = BX_BITLIB_DIR."/php/bitlib/admin/plugins/wysiwyg/xsl/xml2dbxml.xsl";
        
        if(function_exists("domxml_xslt_stylesheet_file")) {
            sitemap::var2XMLObject($xml);
            $xsl = domxml_xslt_stylesheet_file($xslfile);
            $xml = $xsl->process($xml, array(), FALSE);
            sitemap::var2XMLString($xml);

        } else {
            $args = array("/_xml" => $xml);
            $argxml = "arg:/_xml";

            $xslproc = xslt_create();
            $xml = xslt_process($xslproc, $argxml, $xslfile, NULL, $args);
        }
        
        $xml2db = new xml_xml2db($this->getParameter("default","db"));

        $xml2db->idField ="ID";
        $xml2db->useDumpNode = True;
        
        $xml2db->insert($xml);
        
        // TODO: Error handling!
        $this->sitemap->setResponseCode(204);
        return array("message" => "Data saved");
        
    }

}

?>
