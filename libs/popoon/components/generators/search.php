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

/**
 * Popoon Generator to deal with Search Engines
 * 
 * Parameter SearchModule in sitemap defines which
 * which Module to use. Till now implementations for
 * Swish-e and MnogoSearch
 * 
 * @author: silvan zurbruegg <silvan@bitflux.ch>
 * @version: $Id: search.php 1255 2004-04-22 17:15:25Z chregu $
 * @package  popoon
 */
 


include_once 'popoon/components/generator.php';

Class generator_search extends generator {

	var $Search;
	var $query;
	var $result;
	var $isError = false;
	var $attribs;
	var $numrows;
	var $currpage;
	var $maxpages;
    
						   
   	/**
   	* Constructor
	*
	* @param object $sitemap
	*/
	function generator_search (&$sitemap) {
		if(is_object(&$sitemap)) {
			$this->generator(&$sitemap);
		}
	}		
	
	
   	/**
    * Initial Call / Interface to sitemap.php
	*
	* loads Search Module and gets Parameter
	* from Sitemap. Sets Classvars
	*
	* @param array $attr
	* @return bool true|false
	*/
	function init($attr) {
		$this->attribs = $attr;
		
		/* Load appropriate Searchmodule , default MnogoSearch */
		$mod = $this->getParameter('default','searchModule');
		$mod = (empty($mod))?'MnogoSearch':$mod;
		$ret = $this->loadSearchModule($mod);
		if(is_array($ret) && isset($ret['error'])) {
			Popoon::raiseError($ret['error']);
		}
		
		$this->query = $this->getParameter('default','query');
		$this->uri = $this->getParameter('default','requestUri');
		$this->currpage = $this->getParameter('module','CurrPage');
		$this->numrows = $this->getParameter('module','NumRows');
		return TRUE;
	}

	
	/**
	* Start Query Processing / generate XML
	* Interface to sitemamp.php
	* takes (string) $xml from sitemap.php by reference
	* xml-string from buildXML() gets appended to $xml
	* @param string xml
	* @return bool true|false
	*/
	function DomStart(&$xml) {
		$res = $this->Search->doQuery($this->query);
		if (is_array($res) && isset($res['error'])) {
			$xml = $this->buildErrorXml($res['error']);
		} else {
			$this->maxpages = $this->getMaxPages();
			$this->result = $this->Search->getResult();
			//var_dump($this->result);
			if (is_array($this->result) && isset($this->result['error'])) {
			
				$xml = $this->buildErrorXml($this->result['error']);
			} else {
				
				$xml = $this->buildXml();
				return TRUE;
			}
		}
	}
	
	
	/**
	* Load appropriate search module
	*
	* Search module has to be a class in generators/search/
	* parameter $mod is name of File/Class to include 
	* @param string Modulename
	* @return object Search
	*/
	function loadSearchModule($mod=NULL) {
		if($mod==NULL) {
			$mod = $this->getParameter('default','searchModule');			
		}
		
		$searchMod = dirname(__FILE__)."/search/".$mod.".php";
		if (file_exists($searchMod)) {
				require_once $searchMod;
				$params = $this->getParameter('module');
				eval("\$this->Search =& new ".$mod."(\$params);");
		} else {
			return array("error"=>"Class $mod not found!");
		}
		
		return $this->Search;
	}
	
	
	/**
	* returns links for 'previous'/'next' buttons to skim
	* through result pages
	* @param string $what 
	* @return string $request 
	*/
	function prevNext($what) {
		$maxp = (($this->maxpages -1) >=0)?($this->maxpages -1):0;
		
		if($what == 'next') {
			if(($this->currpage +1) <= $maxp) {
				$page = $this->currpage +1;
			} else {
				$page = $this->maxpages -1;
			}
		} elseif($what == 'prev') {
			if(($this->currpage -1) >= 1) {
				$page = $this->currpage -1;
			} else {
				$page = $this->currpage;
			}
		}
		
		$request = $this->getParameter('default','RequestUri');
		$request = str_replace("ResCurrPage=","ResCurrPage=".$page,$request);
	
		return $request;
	}
	
	
	/**
	* Calc. Max Pages displayable according to Results found
	* @return int MaxPages
	*/
	function getMaxPages() {
		if(!empty($this->Search->ResFound) && $this->numrows > 0) {
			return ceil($this->Search->ResFound / $this->numrows);
		}
	}
	
	
	/**
	* Calc. which Document number (count) is displayed first
	* @return int FirstDoc
	*/
	function getFirstDoc() {
		return ($this->currpage * $this->numrows)+1;
	}
	
	
	/**
	* Calc. which Document number (count) is the last displayed
	* @return int LastDoc
	*/
	function getLastDoc() {
		if (($this->currpage +1) < $this->maxpages) {
			return ($this->currpage * $this->numrows)+$this->numrows;
		} else {
			return $this->Search->ResFound;
		}
	}
	
	
	/**
	* Create XML from Search Results
	* @return string $dom
	*/
	function BuildXml() {
		$dom = domxml_new_doc("1.0");
		$root = $dom->add_root('SearchResult');
		
		if(is_array($this->result)) {
			// Total Found Results
			$elem = $dom->create_element('Found');
			$elem->set_content(utf8_encode($this->Search->ResFound));
			$root->append_child($elem);
		
			// Total displayed on Page
			$elem = $dom->create_element('Rows');
			$elem->set_content(utf8_encode($this->numrows));
			$root->append_child($elem);
		
			// Current Page
			$elem = $dom->create_element('ResCurrPage');
			$elem->set_content(utf8_encode($this->currpage));
			$root->append_child($elem);
			
			// NextPage 
			$next = $this->prevNext('next');
			$elem = $dom->create_element('NextPage');
			$elem->set_content($next);
			$root->append_child($elem);
		
			// PreviousPage
			$prev = $this->prevNext('prev');
			//echo "<br>".$prev;
			$elem = $dom->create_element('PrevPage');
			$elem->set_content($prev);
			$root->append_child($elem);
		
			// Time used for SearchResult
			$elem = $dom->create_element('Time');
			$elem->set_content(utf8_encode($this->Search->ResTime));
			$root->append_child($elem);
		
			// Maximum Nr. of Pages
			$elem = $dom->create_element('MaxPages');
			$elem->set_content(utf8_encode($this->maxpages));
			$root->append_child($elem);
		
			// First Document
			$elem = $dom->create_element('FDoc');
			$elem->set_content(utf8_encode($this->getFirstDoc()));
			$root->append_child($elem);
		
			// Last Document
			$elem = $dom->create_element('LDoc');
			$elem->set_content(utf8_encode($this->getLastDoc()));
			$root->append_child($elem);
		
			// Query
			$elem = $dom->create_element('Query');
			$elem->set_content(utf8_encode($this->query));
			$root->append_child($elem);
		
			// The Results
			$elem = $dom->create_element('Results');
			$root->append_child($elem);
			if(sizeof($this->result)>0 ) {
				foreach($this->result as $key => $val) {
					$res = $dom->create_element('Result');
            			foreach($val as $k => $v) {
	    					$item = &$dom->create_element($k);
    						$item->set_content(utf8_encode($this->entity_decode(strval($v))));
							$res->append_child($item);
						}
						 
       				$elem->append_child($res);
				}
			}
		}
		
		return $dom;
	}


	/**
	* Create XML in case of Errors
	* @param string $dom
	*/
    function buildErrorXml($error) {
    	$dom = domxml_new_doc("1.0");
		$root = $dom->add_root('SearchResult');
        $root->new_child("error",$error);
        return $dom;
    }    
}










?>
