<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003 Bitflux GmbH                            |
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
// $Id: db2xml.php 1423 2004-05-24 14:08:37Z chregu $


/**
* Module for generating XML-Document with the help of the db2xml Class 
*
* db2xml is formerly known as sql2xml :)
*
* It takes an sql query or everything else db2xml wants as an argument
*  in the src attribute..
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: db2xml.php 1423 2004-05-24 14:08:37Z chregu $
* @package  popoon
*/
class popoon_components_generators_db2xml extends popoon_components_generator {

	var $multipleInput = "no";

	function __construct (&$sitemap) {
		parent::__construct($sitemap);
	}
	
    function DomStart(&$xml)
    {
		$mode = $this->getAttrib("mode");

		if ($mode) {
    		$db2xml = new xml_db2xml($this->getAttrib("dsn"),"bx","$mode");
		} else {
			$db2xml = new xml_db2xml($this->getAttrib("dsn"));
		}
		if ($this->multipleInput == "no")
		{
			$xml = $db2xml->getXMLObject($this->getAttrib("src"));
		}
		else 
		{
			$data = $this->getAttrib("src");
			foreach ($data as $root => $entry) 
			{			
	 			 if (is_array($entry) && isset($entry["SQLquery"])) {
				 	if (isset($entry['tableInfo'])) {
		                $db2xml->setOptions(array("user_tableInfo"=>$entry['tableInfo'],"user_options"=>array('result_root' => "$root")),true);
					} else {	
						$db2xml->setOptions(array("user_tableInfo"=>array(),"user_options"=>array('result_root' => "$root")),true);	                   
					}
					$db2xml->add($entry["SQLquery"] );
					
				 } else {
					$db2xml->setOptions(array("user_tableInfo"=>array(),"user_options"=>array('result_root' => "$root")),true);
					$db2xml->add($entry );
				  }
			
			}
			$xml = $db2xml->getXMLObject();
            
		}
       
       // $db2xml->unlinkReferences();
        unset($db2xml);
        
        
        return True;        
	}
}


?>
