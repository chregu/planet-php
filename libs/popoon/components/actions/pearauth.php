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
// $Id: pearauth.php 1485 2004-06-01 06:08:48Z chregu $

include_once("popoon/components/action.php");
/**
* Class for generating xml document
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: pearauth.php 1485 2004-06-01 06:08:48Z chregu $
* @package  popoon
*/

class popoon_components_actions_pearauth extends popoon_components_action {

	/* these values are schering specific, as soon as i changed the schering sitemap.xml
		they will be changed to some more appropriate defaults */
	protected $usertable  = "MyGyndoc";
	protected $usernamecol = "Login_Name";
	protected $passwordcol = "Login_Passwd";
    protected $whereAdd = "";
	protected $dbfields = "ID";
    protected $returnPassword = false;

	/**
    * Constructor
    *
	*/
	function action_pearauth(&$sitemap) {
	
			$this->action($sitemap);
			include_once("Auth/Auth.php");
	}

	
	
	function act() {
	
    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && $this->getParameterDefault('auth_user') == $_SERVER['PHP_AUTH_USER'] && $this->getParameterDefault('auth_pw') == $_SERVER['PHP_AUTH_PW'])
    {
    	session_id("indexer");
        return array("username" => "indexer");
    }
    $params = array(
            "dsn" => $this->getAttrib("dsn"),
            "table" => $this->usertable,
            "usernamecol" => $this->usernamecol,
            "passwordcol" => $this->passwordcol,
			"db_fields" => $this->dbfields,
            "whereAdd" => $this->whereAdd,
            );
    
	$a = new Auth("MDB2", $params, "action_pearauth_logi",false);
    session_start();
    // if the user is not logged in, try to find out his preferred language
	// only do it when not logged in, as he can change it later again
	if (!isset($_SESSION["auth"]["registered"]) || ! $_SESSION["auth"]["registered"] )
	{
		$dolang = true;
	}
    	else
	{
		$dolang = false;
	}

	$a->start();

    $mode = $this->getAttrib("mode") ;
	if ($mode  == "logout" || $mode == "login") {
		$a->logout();
        $_SESSION= array();
		return array("challenge" => $a->getChallenge());
	}

	if ($a->getAuth()) {
        $defaultPass = $this->getParameter("default","defaultPassword");

        if ($defaultPass && $defaultPass == $_SESSION["auth"]["fields"][$this->passwordcol] && $_SESSION['auth']['timestamp'] == $_SESSION['auth']['idle']) {
           if (isset($_SESSION["auth"]["fields"]["KPSprache"])) {
               header("Location: ". str_replace('$lang',$_SESSION["auth"]["fields"]["KPSprache"],$this->getParameter("default","redirectTo")));
           } else {
               header("Location: ". $this->getParameter("default","redirectTo"));
           }
               
        }
		if (!(isset($_SESSION["auth"]["fields"]["ID"]))) 
		{
			$_SESSION["auth"]["fields"]["ID"] = 0;
		}
		if (!isset($_SESSION["challenge"]))
		{	
			$a->getChallenge();
		}

        $username= $a->getUsername();
        if (! SID && (! isset($_COOKIE["username"]) || ($username && $_COOKIE["username"] != $username))) {
            setcookie("username",$username, time() + 5184000,"/" ); // 60days
            $_COOKIE["username"]= $username;
        }
        $returnarray = array ("challenge" => $_SESSION["challenge"], 
					 "username"=>$username,
					 "userID" =>$_SESSION["auth"]["fields"]["ID"], 
					 );
        foreach($_SESSION["auth"]["fields"] as $key => $value)
        {   
            // we don't want the password in the return array
            if ($this->returnPassword || $key != $this->passwordcol) {
                $returnarray[$key] =  utf8_encode($_SESSION["auth"]["fields"][$key]);
            }
        }
        /* don't save password in session */    
        if (isset($_SESSION["auth"]["fields"][$this->passwordcol])) {
            unset($_SESSION["auth"]["fields"][$this->passwordcol]);
        }
        
        
		//if we should do the language change from the database, then provide this info
		// maybe this could be done with SESSIONs as well, but this is something
		// for the next pearauth ...
		if ($dolang && isset($_SESSION["auth"]["fields"]["KPSprache"]))
		{
            $returnarray["lang"] = $_SESSION["auth"]["fields"]["KPSprache"];
		}
		else
		{
            $returnarray["lang"] = null;
		}

		return $returnarray;
        
	} else if (isset($_POST['password'])){
        
        sitemap::setGlobalOptions("error", $this->getParameterDefault("errormessage"));
    }
	if (! SID && (! isset($_COOKIE["username"]) || ($a->username && $_COOKIE["username"] != $a->username))) {
         setcookie("username",$a->username, time() + 5184000 ,"/" ); // 60days
         $_COOKIE["username"]= $a->username;
    }

    }
    

}

function action_pearauth_login() {

		return array("username"=>"kk");
	}
	
