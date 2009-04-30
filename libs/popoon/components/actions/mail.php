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
// $Id: mail.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/action.php");
/**
* Class for generating xml document
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: mail.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/

class action_mail extends action {

	/**
    * Constructor
    *
	*/
	var $templatesrc = "/home/bitlib2/php/popoon/components/actions/querybuilder.xsl";

	function action_mail(&$sitemap) {
		$this->action($sitemap);
		include_once("Mail/mime.php");
		include_once("Mail.php");
	}

	function init($attribs) {
		parent::init($attribs);
	}
	
	function act() {
		$mime = new Mail_mime();
		
		$texts = $this->getParameter("text");
		$body = "";
		foreach($texts as $key => $value)
		{
			$body .=  "$key: $value\n";
		}

		$mime->setTXTBody($body);
		
		$to = $this->getParameter("default","To");
		$headers = $this->getParameter("header");
		foreach($headers as $key => $value)
		{
			$defaultHeaders[$key] = $value;
		}

		$files = $this->getParameter("file");
		foreach($files as $key => $value)
		{
			$mime->addAttachment($value["tmp_name"],$value["type"],$value["name"], true, "base64");
		}


		$body = &$mime->get(array("text_encoding"=>"quoted-printable"));
		$headers = &$mime->headers($defaultHeaders);

		$mail = &Mail::factory('mail');
		$mail->send($to,$headers,$body);
	
		return array("message" => "Ihre Meldung wurde versendet. Besten Dank.");


	}
}
