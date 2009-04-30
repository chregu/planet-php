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

include_once("popoon/components/generator.php");

/**
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: pwforgotten.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class generator_pwforgotten extends generator {


	/**
    * Constructor, does nothing at the moment
    */
	function generator_pwforgotten (&$sitemap) {
		$this->generator($sitemap);
	}

	/**
    * Initiator, called after construction of object
    *
    *  This method will be called in the start element with the attributes from this element
    *
    *  As we just call the parent init method, it's not really needed, 
    *   it's just here for reference
    *
    *  @param $attribs array	associative array with element attributes
    *  @access public
	*/
    function init($attribs)
    {
    	parent::init($attribs);
	}    
	
    /**
    * generates an xml-DomDocument out of the xml-file
    *
    * @access public
    * @returns object DomDocument XML-Document
    */
    function DomStart(&$xml)
    {
		$token = $this->getParameter("default","token");
		$error = $this->getParameter("default","error");
		print  '<font color="red">'.$error.'</font>';

		if ($token == "1")
		{
		$xml = '<para xmlns:xforms="http://www.w3.org/2002/xforms"> 
		<xforms:xform id="MyGyndoc">
            <xforms:submitInfo action="./?token='.$token.'" method="post"/>
        </xforms:xform>
        

<xforms:input xform="MyGyndoc"  ref="username">
  <xforms:label  xml:lang="de">Ihren Usernamen </xforms:label>
</xforms:input>



<xforms:submit xform="MyGyndoc">
  <xforms:caption xml:lang="de">Senden</xforms:caption>
</xforms:submit>
</para>';
		}
		else if ($token == "2")  
		{
		$xml = '<para><span>Mail gesendet</span></para>';
		}

		else if ($token=="3")  
		{
$xml = '<?xml version="1.0" encoding="iso-8859-1"?><para xmlns:xforms="http://www.w3.org/2002/xforms"> 
		<xforms:xform id="MyGyndoc">
            <xforms:submitInfo action="./?token='.$token.'" method="post"/>
        </xforms:xform>
        
<para>Für Ihre Sicherheit müssen Sie bitte nochmals den Namen angeben</para>
<xforms:input xform="MyGyndoc"  ref="username">
  <xforms:label  xml:lang="de">Ihren Usernamen </xforms:label>
</xforms:input>



<xforms:submit xform="MyGyndoc">
  <xforms:caption xml:lang="de">Senden</xforms:caption>
</xforms:submit>
</para>';		
		

		}
		else if ($token=="4")  
		
		{
		
		
		$xml = '<?xml version="1.0" encoding="iso-8859-1"?><para xmlns:xforms="http://www.w3.org/2002/xforms"> <xforms:xform id="MyGyndoc">
            <xforms:submitInfo action="./?token='.$token.'" method="post"/>
        </xforms:xform>
        

<xforms:secret xform="MyGyndoc"  mode="change_password" ref="Login_Passwd">
  <xforms:label position="1" xml:lang="de">Neues Passwort eingeben: </xforms:label>
  <xforms:label position="2" xml:lang="de">Neues Passwort bestätigen: </xforms:label>
  <xforms:hint xml:lang="de">Bitte neues Passwort eingeben. Es wird nicht zu sehen sein beim Eintippen.</xforms:hint>
</xforms:secret>



<xforms:submit xform="MyGyndoc">
  <xforms:caption xml:lang="de">Senden</xforms:caption>
</xforms:submit>
</para>';
}
else 		{
$xml = "<para>something went wrong</para>";


}
    	return True;
	}
}


?>
