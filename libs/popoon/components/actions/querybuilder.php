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
// $Id: querybuilder.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/action.php");
/**
* Class for generating xml document
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: querybuilder.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/

class action_querybuilder extends action {

	/**
    * Constructor
    *
	*/
	var $templatesrc = "/home/bitlib2/php/popoon/components/actions/querybuilder.xsl";
    var $searchtable = "Events";
    var $searchsection = "events";
    
	function action_querybuilder(&$sitemap) {
		$this->action($sitemap);
	}

	function init($attribs) {
		parent::init($attribs);
	}
	
	function act() {
	
		$src = $this->getAttrib("src");
		$xml = domxml_open_file($src);
		$xsl = domxml_xslt_stylesheet_file($this->templatesrc);
		$root = $xml->document_element();
		
		$q = $xml->create_element("query");
		
		//this has to be done via sitemap params please...
		$strings = $this->getParameter("string");
		foreach($strings as $key => $value)
		{
			$q->new_child($key,utf8_encode($value));
		}
		$dates = $this->getParameter("date");

		foreach($dates as $key => $value)
		{
			if ($value) {
				$t = explode(".",$value);
			 	$value = mktime (0,0,0,  $t[1], $t[0],$t[2]);
				$value = strftime("%Y-%m-%d",$value);
			}
			$q->new_child($key,$value);
		}


		$root->append_child($q);
		$xsl = domxml_xslt_stylesheet_doc($xsl->process($xml));
		$result = $xsl->process($xml);
//		
		if (function_exists("domxml_xslt_result_dump_mem")) {
			$query = $xsl->result_dump_mem($result);
		} else {
			$query = preg_replace("#<\?xml[^>]*>|\n#","",$result->dump_mem(0,"iso-8859-1"));
			$query = preg_replace("#&gt;#",">",$query);
			$query = preg_replace("#&lt;#","<",$query);

		}
		if ($this->getParameter("default","isempty") == "nohit" && ! trim($query))
		{
			$query = "1 = 0";
		} else {
			$query = "1 = 1 $query";
		}
		return array("searchquery" => $query, 
		"searchtable" => $this->searchtable,
		"searchsection" => $this->searchsection
		);

	}
}
