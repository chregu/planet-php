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
// $Id: pwforgotten.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/action.php");
/**
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: pwforgotten.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/

class action_pwforgotten extends action {

	/**
    * Constructor
    *
	*/
	function action_pwforgotten(&$sitemap) {
	
		$this->action($sitemap);
			include_once("Auth/Auth.php");
	}

	
	
	function act() {

	$token = $this->getParameter("default","token");
	session_start();

	if (!$token and !(isset($_SESSION["POPOON"]["pwforgotten"]["stage"]) and  $_SESSION["POPOON"]["pwforgotten"]["stage"] > 0 ))
	{
		return;
	}

	$username = $this->getParameter("default","username");

	print_r($_SESSION);
	include_once("DB.php");
	$this->db = $this->getAttrib("dsn");	

	if ($token == "new")
	{
		$_SESSION["POPOON"]["pwforgotten"]["stage"] = 1;
		return array("token" => "1");
	}
	// if we have a token provided
	else if (strlen($token) == 32)
	{

		$result = $this->db->query("select Login_Name from MyGyndoc where password_temp = '".$token."' and password_time > now() - 3600*24");
		if (DB::isError($result)) {
        	popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
		}
		if ($result->numrows() == 0)
		{
			return ;
		}
		$_SESSION["POPOON"]["pwforgotten"]["token"] = $token;
		if (isset($_SESSION["POPOON"]["pwforgotten"]["username"])) 
		{
			$username = $_SESSION["POPOON"]["pwforgotten"]["username"];
			$_SESSION["POPOON"]["pwforgotten"]["stage"] = 4;
		}
		else
		{		
			$_SESSION["POPOON"]["pwforgotten"]["stage"] = 3;
		}

	}
	
	switch 	($_SESSION["POPOON"]["pwforgotten"]["stage"])
	{
		case 1:
		return $this->sendmail($username);
		
		// 3 is when the doctor comes with a token=md5kkfsduiewurqou in the url
		case 3:
		$_SESSION["POPOON"]["pwforgotten"]["stage"] = 4;
		return array("token" => "3");	
		// 4 is after providing the username a second time
		case 4:
		return $this->checkUsername($username);				
		//username provided and correct, update password
		case 5:
		return $this->updatePassword($_REQUEST["Login_Passwd"]);				


		
		default:
		return;
	}
	
	
	return;
	
	
	
	if ($username && $token == "new") {
	
		//falls usernamen gesetzt ist, email holen
		
		$result = $db->query("select email from MyGyndoc where Login_Name = '".$username."'");
		if (DB::isError($result)) {
        	popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
		}
		if ($result->numrows() == 0)
		{
			return array("token" => $token);
		}
		
		// dann token generieren, in db eintragen und dem user schicken
		else
		{
			
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
            $challenge = md5(uniqid("BXCMSpopoonNewPasswort"));		
			$result = $db->query("update MyGyndoc set password_temp = '".$challenge."', password_time = now() where Login_Name = '".$username."'");
			if (DB::isError($result)) {
    	    	popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
			}

			$mailtext = "http://schering/?token=$challenge";

			mail($row["email"],"Passwort ändern",$mailtext,"From: webmaster@gyndoc.ch", "-fwebmaster@gyndoc.ch");
			$token="ok";
			return array("token" => $token);
		}
		
	}
	// if username und $token sind gesetzt, wir gucken, ob er rein darf
	else if ($username && $token )
	{
	
		$result = $db->query("select Login_Name from MyGyndoc where Login_Name = '".$username."' and password_temp = '".$token."' and password_time > now() - 3600*24");
		if (DB::isError($result)) {
        	popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
		}

		if ($result->numrows() == 0)
		{
			return ;
		}

		if(isset($_REQUEST["popoon_action_xforms"]) && isset($_REQUEST["Login_Passwd"]))
		{
			$row= $result->fetchRow(DB_FETCHMODE_ASSOC);
			$result = $db->query("update MyGyndoc set Login_Passwd = md5('".$_REQUEST["Login_Passwd"]."')");
			if (DB::isError($result)) {
    	    	popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
			}
			return;
		
		}
		else
		{
			return array("token" => $token,"username"=>$username);
		}
		
	}

	else if ($token && $token != "ok") {
		$result = $db->query("select Login_Name from MyGyndoc where password_temp = '".$token."' and password_time > now() - 3600*24");
		if (DB::isError($result)) {
        	popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
		}

		if ($result->numrows() == 0)
		{
			return ;
		}
		return array("token" => $token);

	}
	
	return;
	
}

function sendmail($username)
{
		$result = $this->db->query("select email from MyGyndoc where Login_Name = '".$username."'");
		if (DB::isError($result)) {
        	popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
		}
		if ($result->numrows() == 0)
		{
			return array("error" => "Der username ist nicht in der Datenbank vorhanden");
		}
		
		// dann token generieren, in db eintragen und dem user schicken


			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
            $challenge = md5(uniqid("BXCMSpopoonNewPasswort"));		
			$_SESSION["POPOON"]["pwforgotten"]["token"]	= $challenge;
			$result = $this->db->query("update MyGyndoc set password_temp = '".$challenge."', password_time = now() where Login_Name = '".$username."'");
			if (DB::isError($result)) {
    	    	popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
			}

			$mailtext = "http://schering/?token=$challenge";

			mail($row["email"],"Passwort ändern",$mailtext,"From: webmaster@gyndoc.ch", "-fwebmaster@gyndoc.ch");
			$_SESSION["POPOON"]["pwforgotten"]["username"] = $username;
			return array("token" => 2);


}

function checkUsername($username) 
{

print "<hr/>select Login_Name from MyGyndoc where Login_Name = '".$username."' and password_temp = '".$_SESSION["POPOON"]["pwforgotten"]["token"]	."' and password_time > now() - 3600*24 <hr>";
		$result = $this->db->query("select Login_Name from MyGyndoc where Login_Name = '".$username."' and password_temp = '".$_SESSION["POPOON"]["pwforgotten"]["token"]	."' and password_time > now() - 3600*24");
		if (DB::isError($result)) {
        	popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
		}

		if ($result->numrows() == 0)
		{
			$_SESSION["POPOON"]["pwforgotten"]["stage"] = 4;			
			return array("error" => "Keine passende Username/Token kombination gefunden", "token" => 3);
		}
		$_SESSION["POPOON"]["pwforgotten"]["username"] = $username;		
		$_SESSION["POPOON"]["pwforgotten"]["stage"] = 5;					
		return array("token" => 4);

}

function updatePassword($password) {

			$result = $this->db->query("select ID from MyGyndoc where password_temp = '".$_SESSION["POPOON"]["pwforgotten"]["token"] ."' and Login_Name = '".$_SESSION["POPOON"]["pwforgotten"]["username"] ."'");
			if (DB::isError($result)) {
    	    	popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
			}
			if ($result->numrows() == 0) 
			{
				return array("error" => "TempPassword/Username combination was not found");			
			}
			
			$result = $this->db->query("update MyGyndoc set Login_Passwd = md5('".$password."') where password_temp = '".$_SESSION["POPOON"]["pwforgotten"]["token"] ."' and Login_Name = '".$_SESSION["POPOON"]["pwforgotten"]["username"] ."'");


			if (DB::isError($result)) {
    	    	popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
			}
			$_SESSION["auth"]["registered"] = 1;
			$_SESSION["auth"]["username"] = $_SESSION["POPOON"]["pwforgotten"]["username"];
			$_SESSION["auth"]["timestamp"] = time();			
			$_SESSION["auth"]["idle"] = time();						

			unset($_SESSION["POPOON"]["pwforgotten"]);
		return array("token" => 6 ,"error" => "Ihr Passwort wurde geändert");			
}

}
