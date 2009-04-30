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
// $Id: simpleperm.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/action.php");
/**
* Class for generating xml document
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: simpleperm.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/

class action_simpleperm extends action {

    var $db = null;
    /**
       * Constructor
       *
    */
    function action_simpleperm(&$sitemap) {
        $this->action($sitemap);
    }


    function act() {

        $path = $this->getAttrib("path");
        $this->db = $this->getAttrib("dsn");
        $perms = $this->db->getAll("select section, recursive from usersSectionPerm where userID = 0 or userID = ". $this->getParameter("default","userID"),DB_FETCHMODE_ASSOC);

        if (is_null($path)) {
            if (!isset($_GET["path"])) {
                $path = "";
            } else {
                $path = $_GET["path"];
            }
        }
	// quick fix for allowing  /print/ in front of path... 
	// FIXME
	$path = preg_replace("#^/*print/#","",$path);
        return array("simplepermWhere"  => $this->checkPerm($path,$perms)) ;

    }

    function checkPerm($dir, $perms, $action = READ) {
        $where = "1=0 ";
        foreach ($perms as $perm) {

            $where .= "or fulluri = '".$perm["section"]."' ";
        }
        $result = $this->db->query("select fulluri, l, r from Section where $where");
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            $uris[$row["fulluri"]]["l"] = $row["l"];
            $uris[$row["fulluri"]]["r"] = $row["r"];
        }
        $where = " ( 1 = 0";
        foreach ($perms as $perm) {
            if ($perm["recursive"]) {
                $where .= " or (Section.l between ". $uris[$perm["section"]]["l"] . " and ". $uris[$perm["section"]]["r"] .")";
            } else {
                $where .= " or (Section.l = ". $uris[$perm["section"]]["l"] . ")";
            }
        }

        $where .=")  ";
        return $where;
        /*
        $result = $db->query($sql);
        $return = false;
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
        {
        	if (!$return && $row["fulluri"] == $dir) {$return = true;}
        	print $row["ID"] ."\t".$row["fulluri"]."\n";
        }
        return $return;*/
    }

}
