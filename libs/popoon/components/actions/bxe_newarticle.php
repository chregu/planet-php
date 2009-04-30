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
// | Author: Philipp Stucki <philipp@bitflux.ch>                          |
// +----------------------------------------------------------------------+
//
// $Id: bxe_newarticle.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/action.php");
/**
* Action to insert a new article via bxe
*
* @author   Philipp Stucki <philipp@bitflux.ch>
* @version  $Id: bxe_newarticle.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/

class action_bxe_newarticle extends action {

    var $db = NULL;
    var $_sequencesTable = '_sequences';
    
    /**
    * Constructor
    *
    */
    function action_bxe_newarticle(&$sitemap) {
        $this->action($sitemap);
    }

    function init() {
    }
    
    /**
    * this is the real action
    *
    * @return object sitemap parameters 
    */
    function act() {
        
        // get all required parameters
        $this->_setDBObject($this->getParameter("default","db"));
        $sectionID = $this->getParameter("default","sectionID");
        
        // read sent xml
        $xml = $this->_getRawXML();
        
        // create a dom object out of read xml string
        $xmlDoc = domxml_open_mem($xml);

        if($sectionID) {

            // get article uri
            $xpRes = $this->_evalXPath($xmlDoc, '/iba/page/Article/uri/text()');
            $nodeSet = $xpRes->nodeset;
            $uri = $this->db->quote($xmlDoc->dump_node($nodeSet[0]));

            // generate article uri
            //$uri = $this->db->quote($this->_generateURI($uri));

            // get article language
            $xpRes = $this->_evalXPath($xmlDoc, '/iba/page/Article/lang/text()');
            $nodeSet = $xpRes->nodeset;
            $lang = $this->db->quote($xmlDoc->dump_node($nodeSet[0]));

            // get the title of the new article
            $xpRes = $this->_evalXPath($xmlDoc, '/iba/page/Article/title/text()');
            $nodeSet = $xpRes->nodeset;
            $title = $this->db->quote($xmlDoc->dump_node($nodeSet[0]));

            // get article author
            $xpRes = $this->_evalXPath($xmlDoc, '/iba/page/Article/username/text()');
            $nodeSet = $xpRes->nodeset;
            $author = $this->db->quote($xmlDoc->dump_node($nodeSet[0]));

            // get article content
            $xpRes = $this->_evalXPath($xmlDoc, '/iba/page/Article/main/child::*');
            $nodeSet = $xpRes->nodeset;
            foreach($nodeSet as $node) {
                $main .= $xmlDoc->dump_node($node);
            }
            $main = $this->db->quote($main);

            // check wether this articlee already exists
            if(($articleID = $this->_getArticleID($sectionID, $uri)) !== FALSE) {

                // update existing article
                $this->_updateArticle($articleID, $title, $lang, $main, $author);
            
            } else {

                // insert a new section
                $newSectionID = $this->_insertSection($sectionID, $uri, $title, $author);
                
                // insert a new document
                $newDocumentID = $this->_insertDocument($newSectionID, $uri, $title, $author);
    
                // insert a new article
                $newArticleID = $this->_insertArticle($newDocumentID, $title, $lang, $author, $main);
            }

            // flush output cache
            $this->_deleteCache();            
        }

        $this->sitemap->setResponseCode(204);
        return array("message" => "Data saved");
    }
    
    /**
    *
    *
    * @param object db  
    */
    function _setDBObject($db) {
        $this->db = &$db;
    }
    
    function _transformXML($xml, $xslfile) {

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
        return $xml;
    }

    /**
    *
    *
    * @param 
    * @return 
    */
    function _getRawXML() {
        // read xml data from php://input stream
        $xml = "";
        $fd = fopen("php://input","r");
        while ($line = fread($fd,2048)) {
            $xml .= $line;
        }
        return $xml;
    }
    
    function _getArticleID($parentSection, $uri) {

        $query = "select Document.ID from Section, Section2Document, Document WHERE Section.foreignsectionid = $parentSection AND Document.uri = $uri and Document.id = Section2Document.foreigndocumentid and Section.id = Section2Document.foreignsectionid";
        $res = $this->db->query($query);

        if(!DB::isError($res)) {
            if($res->numRows() > 0) { 
                $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

                // get document id
                $documentID = $row['ID'];
                
                if(($res = $this->_runQuery("select Article.ID from Article, Document2Object WHERE Document2Object.foreignobjectid = Article.ID AND Document2Object.foreigndocumentid = $documentID")) !== FALSE) {
                    $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                    return $row['ID'];                        
                }
            }
        }
        
        return FALSE;
        
    }

    function _updateArticle($articleID, $title, $lang, $main, $author) {
        $query = "update Article set title=$title, lang=$lang, main=$main, createdby=$author where Article.ID = $articleID";
        if(($res = $this->_runQuery($query)) !== FALSE) {
            return TRUE;
        }
        
        return TRUE;
    }

    function _runQuery($query) {

        $res = $this->db->query($query);
        //var_dump($res);
        if(!DB::isError($res)) {
            return $res;
        }
        return FALSE;
    }
    
    /**
    *
    *
    * @param 
    * @return 
    */
    function _getParentSection($sectionID) {
    
        $query = "select foreignsectionid from Section where ID = $sectionID";
        
        $res = $this->db->query($query);

        if(!DB::isError($res)) {
            if($res->numRows() > 0) { 
                $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                return $row['foreignsectionid'];
            }
        }

        return FALSE;
    }

    /**
    *
    *
    * @param 
    * @return 
    */
    function _insertSection($parentSection, $uri, $title, $author) {

        // get new section id
        $newSectionID = $this->_getNextID('Section');        

        // insert section
        $query = "insert into Section 
            (ID, sectionalias, button, title_de, title_en, foreignsectionid, uri, createdby, created) 
            values ($newSectionID, $newSectionID, 1, $title, $title, $parentSection, $uri, $author, NOW())";

        $res = $this->db->query($query);
        if(!DB::isError($res)) {
            // recalculate sql tree
            $this->_calculateTree();

            return $newSectionID;
        }

        return FALSE;
    }
    
    /**
    *
    *
    * @param 
    * @return 
    */
    function _insertDocument($parentSection, $uri, $title, $author) {

        // get a new document id
        $newDocumentID = $this->_getNextID('Document');

        // insert document
        $query = "insert into Document (ID, uri, title, createdby, created) values ($newDocumentID, $uri, $title, $author, NOW())";
        $res = $this->db->query($query);
        if(!DB::isError($res)) {

            // get a new section2document id
            $fID = $this->_getNextID('Section2Document');

            // assign document to parent section
            $query = "insert into Section2Document (ID, foreignsectionid, foreigndocumentid) values ($fID, $parentSection, $newDocumentID)";
            $res = $this->db->query($query);
            if(!DB::isError($res)) {
                return $newDocumentID;
            }
        }

        return FALSE;
        
    }
    
    /**
    *
    *
    * @param 
    * @return 
    */
    function _insertArticle($parentDocument, $title, $lang, $author, $main) {
        // get a new article id
        $newArticleID = $this->_getNextID('Article');

        // insert article
        $query = "insert into Article (ID, title, lang, main, createdby, created) values ($newArticleID, $title, $lang, $main, $author, NOW())";
        $res = $this->db->query($query);
        if(!DB::isError($res)) {

            // get a new document2object id
            $fID = $this->_getNextID('Document2Object');

            // assign article to parent document
            $query = "insert into Document2Object (ID, objectname, foreigndocumentid, foreignobjectid) values ($fID, 'Article', $parentDocument, $newArticleID)";
            $res = $this->db->query($query);
            if(!DB::isError($res)) {
                return $newArticleID;
            }
        }

        return FALSE;
    }

    /**
    *
    *
    * @param 
    * @return 
    */
    function _getNextID($table) {
        // get a new ID
        $newID = $this->db->nextID($this->_sequencesTable, FALSE);
        
        // insert the new id into sequences2table 
        $query = "insert into Sequences2Table (Sequence, Tablename) values ($newID, '$table')";
        
        $res = $this->db->query($query);
        
        if(!DB::isError($res)) {
            return $newID;
        }
        
        return FALSE;
    }
    
    /**
    * generates an uri from the article title
    *
    * @param 
    * @return 
    */
    function _generateURI($title) {
        
        // replace numeric entities by its characters and vaporise all other lethal characters
        $s = array(
            '/&#x([\dA-F]+);/e',
            '/\n\r\t/'
        );
        $r = array(
            "chr(hexdec('\\1'))",
            ''
        );
        
        $uri = preg_replace($s, $r, $title);
        return urlencode($uri);
    }

    /**
    * recalculates the nested set sql tree
    *
    * @param 
    * @return 
    */
    function _calculateTree() {
        include_once("bitlib/SQL/Tree.php");
        include_once("bitlib/functions/common.php");
        $db = common::getDB($GLOBALS["BX_config"]["dsn"]);
        $t = new SQL_Tree($db);
        $t->FullPath="fulluri";
        $t->Path="uri";
        $t->FullTitlePath="fulltitlepath";
        $t->Title="title_de";
        
        $t->importTree(1);
    }
    
    /**
    * evaluates a xpath expression on a xml string
    *
    * @param 
    * @return 
    */
    function _evalXPath($xmldoc, $xPath) {
        $ctx = xpath_new_context($xmldoc);
        return xpath_eval($ctx, $xPath);
    }
    
    /**
    * deletes the output cache
    *
    * @param 
    * @return 
    */
    function _deleteCache() {
        require_once("Cache/Output.php");
        $cache = new Cache_Output($GLOBALS["BX_config"]["popoon"]["cacheContainer"], $GLOBALS["BX_config"]["popoon"]["cacheParams"] );
        
        // for the time being, just flush everything...
        @$cache->flush('outputcache');
        $cache->flush('');
    }
    
}

?>
