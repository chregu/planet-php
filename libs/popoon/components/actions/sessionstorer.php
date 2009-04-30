<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002 Bitflux GmbH                                 |
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
// $Id: sessionstorer.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/action.php");
/**
* Class for storing sessions in db
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: sessionstorer.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/

class action_sessionstorer extends action {

	/**
    * Constructor
    *
	*/
	function action_sessionstorer(&$sitemap) {
		$this->action($sitemap);
	}

	function init() {
	}
	
	function act() {


        $add = $this->getParameter("default","add");
        $del = $this->getParameter("default","del");
        $sessionvar = $this->getParameter("default","sessionvar");
        $storefield = $this->getParameter("default","storefield");        
        
        if (! isset($_SESSION[$sessionvar])) {
                $_SESSION[$sessionvar] = unserialize($_SESSION["auth"]["fields"][$storefield]);
                if (!is_array($_SESSION[$sessionvar])) {
                    $_SESSION[$sessionvar] = array();
                }
         }
        
        
        if ($add) {
            
            if (! in_array($add, $_SESSION[$sessionvar])) {
                $_SESSION[$sessionvar][] = $add;
                
            }
            // FIXME: We need a global querystring handling class...
            $_SERVER["QUERY_STRING"] = str_replace("add=".$add,"",$_SERVER["QUERY_STRING"]);
        }
        if ($del) {
            if ($del == "all") {
                unset($_SESSION[$sessionvar]);
            }
            elseif (false !== $key = array_search($del, $_SESSION[$sessionvar])) {
                unset($_SESSION[$sessionvar][$key])         ;   
            }
            $_SERVER["QUERY_STRING"] = str_replace("del=".$del,"",$_SERVER["QUERY_STRING"]) ;           
            
        }
        if ($add || $del) {
            $_SERVER["QUERY_STRING"] = preg_replace("/(&)+/","&",$_SERVER["QUERY_STRING"]) ;
            
            $db = $this->getParameter("default","db");
            $sql = "update ". $this->getParameter("default","tablename").' set '. $storefield . ' = \'' . mysql_escape_string(serialize($_SESSION[$sessionvar])) .  '\' where ' . $this->getParameter("default","idfieldname") . ' ="' . $this->getParameter("default","idfieldvalue") . '"';
            $result = $db->query($sql);
        }
    }
}
