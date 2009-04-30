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
// $Id: httpauth.php 1680 2004-07-07 13:35:12Z chregu $

include_once("popoon/components/action.php");
/**
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: httpauth.php 1680 2004-07-07 13:35:12Z chregu $
* @package  popoon
*/

class popoon_components_actions_httpauth extends popoon_components_action {

    private $db = null;
    /**
       * Constructor
       *
    */
    function __construct($sitemap) {
        parent::__construct($sitemap);
    }


    function act() {
        
        $user = $this->getParameterDefault("user");
        $password = $this->getParameterDefault("password");

        if (isset($_SERVER['PHP_AUTH_USER'] ) && isset($_SERVER['PHP_AUTH_PW']) && $user == $_SERVER['PHP_AUTH_USER'] && $password == $_SERVER['PHP_AUTH_PW']) {
           return array("message" => "Login Successfull");
       }
       if ($this->getParameterDefault("showlogin") == "true") {
           header("WWW-Authenticate: Basic realm=\"popoon httpauth login\"");
           header("HTTP/1.0 401 Unauthorized");
       }
       
       return false;
    }


}
