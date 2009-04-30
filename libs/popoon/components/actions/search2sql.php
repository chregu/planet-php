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
// $Id: search2sql.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/action.php");
/**
* Class for generating xml document
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: search2sql.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/

class action_search2sql extends action {

	/**
    * Constructor
    *
	*/
	function action_search2sql(&$sitemap) {
		$this->action($sitemap);
	}

	function init() {
	}
	
	function act() {
		$searchFields = $this->getParameter("fields");
		$sql= array();
		$searchtable = $this->getParameter("default","table");
		if ($this->getParameter("default","type") == "like")
		{
		foreach($searchFields as $key => $value) {
		 	$sql[] = $searchtable.".".$key . " LIKE '%" . $value . "%'";
		}
		}
		$query = implode ($sql, " or ");
	
		return array("searchquery" => $query, 
		"searchtable" => $searchtable,
		"searchsection" => $this->getParameter("default","section")
		);
	}
}
