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
// | Author: Philipp Stucki <philipp@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: xmlrpc_server.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/generator.php");
include_once("XML/RPC.php");
include_once("XML/RPC/Server.php");

/**
* generator for handling xmlrpc requests
*
* this generator enables you to act as a xmlrpc server. it's usage is very 
* simple and straightforward.
*
* what it does:
*   just answer requests coming from xmlrpc clients and map them to class 
*   methods
*
* what it needs:
*   XML_RPC from pear
*
* how to implement your own server:
*   you need to extend the base xmlrp_server generator class and add all 
*   the methods you want to expose to the public to the extended class.
*   finally you have to tell xmlrpc_server about which rpc methods match 
*   which class methods. - that's all, your application now speaks xmlrpc. 
*
* here is an example of a very simple xmlrpc generator which implements the
* obligatory hello world example :)

*   class generator_xmlrpc_server_simple extends generator_xmlrpc_server {
*   
*       function generator_xmlrpc_server_simple(&$sitemap) {
*           parent::generator_xmlrpc_server($sitemap);
*           
*           $this->addDispatch('moblog.helloWorld', '_helloWorld');
*       }
*   
*       function _helloWorld($params) {
*           return new XML_RPC_Value('hello world');
*       }
*   }
*
*
* @author   Philipp Stucki <philipp@bitflux.ch>
* @version  $Id: xmlrpc_server.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/

class generator_xmlrpc_server extends generator {

    /**
    * array containing dispatch map
    * @var array
    * @access private
    */
    var $_dispatchMap;
    
    /**
    * xmlrpc server object
    * @var object
    * @access private
    */
    var $_server;

    /**
    * generator id, used when creating a global self-reference.
    * @var string
    * @access private
    */
    var $_generatorID;
    
    function generator_xmlrpc_server(&$sitemap) {
        // generate generator id
        $this->_generatorID = $this->_getGeneratorID();

        $this->generator($sitemap);
    }
    
    function init($attribs) {
        // call parent method
        parent::init($attribs);

        // create a new xmlrpc server
        $this->_server = &new XML_RPC_Server($this->_dispatchMap, FALSE);

        // create a global self-reference for doing callbacks
        $GLOBALS['_popoon_generator_xmlrpc_server'][$this->_generatorID] = &$this;
    }    

    /**
    * create an id for this generator. used when creating a global self-reference.
    * @return string generator id
    */
    function _getGeneratorID() {
        return get_class($this);
    }
    
    function DomStart(&$xml) {
        // parse request
        $r = $this->_server->parseRequest($GLOBALS['HTTP_RAW_POST_DATA']);

        // and serialize the result - that's it.
        $xml = '<?xml version="1.0" ?>'.$r->serialize();
        $this->sitemap->setHeader('Content-length', strlen($xml));
    }
    
    /**
    * adds a method to the dispatch map
    * @param string $methodname name of rpc method
    * @param string $functionName name of function to call
    * @return bool returns true when method has been added to the dispatch map
    */
    function addDispatch($methodName, $functionName) {
        if(method_exists($this, $functionName)) {
            $this->_dispatchMap[$methodName] =  array('function' => '$GLOBALS[\'_popoon_generator_xmlrpc_server\'][\''.$this->_generatorID.'\']->'.$functionName);
            return TRUE;
        }

        return FALSE;
    }
}

?>
