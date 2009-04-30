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
// $Id: xforms.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/transformer.php");

/**
* Class for handling database inserts via xforms
*
* BIG WARNING, if you have more than one xforms:xform with the same id 
* in one xml document, be sure to set xml:lang with xforms:xform differently
* otherwise it won't work. It's a little bit a mess here 'cause of that.
*
* furthermore, the xforms:* elements do have to be a sibling or 
* siblings-child of the initial xforms:xform
*
* The advise: To not have different xforms:xform with the same id
*  within one xml document.
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: xforms.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class transformer_xforms extends transformer {

    var $XmlFormat = "DomDocument";
    var $src = "components/transformes/xforms.xsl";
    var $errors = "";
    var $minPasswordLength = 4;
    var $allowedDocTypes = array('.doc', '.ppt', '.pdf', '.rtf', '.sxw');
    var $lang = 'de';
    function transformer_xforms(&$sitemap) {
        $this->transformer($sitemap);
    }

    function DomStart(&$xml) {
        parent::DomStart($xml);
        $xsl = domxml_xslt_stylesheet_file($this->src);

        $this->printDebug("XSL-File: ".$this->src);

        $ctxt = $xml->xpath_new_context();
        $ctxt->xpath_register_ns("xforms","http://www.w3.org/2002/xforms");
        $table = $this->getParameterDefault("table");
        //this sdoes not work with different articles riht now... change it...
        $lang='fr';
        $xforms = $ctxt->xpath_eval("//xforms:xform[@id = '$table' and (@xml:lang = '". $this->lang."' or not(@xml:lang))]");
        
    //check for xforms:extension/popoon in main xforms:xform tag
    if(is_object($xforms->nodeset[0]) &&
       is_object($extension =  $ctxt->xpath_eval("xforms:extension/popoon", $xforms->nodeset[0]))){
        
        $xformsChildNodes = $extension->nodeset[0]->child_nodes();
        foreach($xformsChildNodes as $ext) {
            // if mail extension...
            if($ext->node_name() == 'mail') {
                $mail = array();
                $mailChildren = $ext->child_nodes();
                foreach ($mailChildren as $mailEle) {
                    if ($mailEle->type == XML_ELEMENT_NODE) {
                        $mail[$mailEle->node_name()] = $mailEle->get_content();
                    }
                }
            }
        }
    
    }
       
       //check for xform elements.
        foreach ($xforms->nodeset as $xform) {
            $forms =  $ctxt->xpath_eval("..//xforms:*[@xform = '$table' ]",$xform);
            $fields = array();
            $files = array();
            $xelements = array();
            $error = "";
            foreach ($forms->nodeset as $element) {
                $ref = $element->get_Attribute("ref");
                if ($ref) {
                    if (!in_array($ref,$fields)) {
                        array_push($fields,$ref);
                    }
                    if ($popoon = $ctxt->xpath_eval("//xforms:*[@ref = '$ref']/xforms:extension/popoon")) {
                        if (isset($popoon->nodeset[0])) {
                            $xelements[$ref]["popoon"] =  $popoon->nodeset[0];
                        }
                    }
                    if ($mode = $element->get_Attribute("mode")) {
                        $xelements[$ref][$mode] = $element;
                    }
                    $xelements[$ref]["nodeName"] = $element->node_name();
                }


            }
 
            if (count($fields) == 0) {
                return;
            }

            //update
            $db = $this->getParameterDefault("db");
            $idfield = $this->getParameterDefault("idfield");
            $idvalue = $this->getParameterDefault("idvalue");
            if (isset($_POST["popoon_action_xforms"])) {
                $uploaddir = BX_PROJECT_DIR."/www/".$this->getParameter("xslparams","uploaddir");
                $ID = $this->getParameter("xslparams","userID");
                $sqlUpdate = array();
                $magicQuotes = ini_get("magic_quotes_gpc");
                
                foreach($fields as $field) {
                    if ($magicQuotes && isset($_POST[$field]) &&  !is_array($_POST[$field])) {
                        $_POST[$field]  = stripslashes($_POST[$field]);
                    }
                    if (isset($_POST[$field]) && $xelements[$field]["nodeName"] != "select") {
                        $noSql = False;
                        if (isset( $xelements[$field]["nodeName"] ) && $xelements[$field]["nodeName"] == "secret" && isset($xelements[$field]["challenge"])) {
                            $result = $db->getOne("select $field from $table where $idfield = '$idvalue'");
                            if (DB::isError($result)) {
                                popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
                            }
                            if (isset($_POST["response"]) && strlen($_POST["response"]) > 0) {
                                if (md5($result . ":" .$_SESSION["challenge"]) != $_POST["response"]) {
                                    $this->setError("Das alte Passwort ist falsch.");
                                    $noSql = True;
                                }
                            } else {
                                if (md5($_POST["popoon_password"]) != $result) {
                                    $this->setError("Das alte Passwort ist falsch ");
                                    $noSql = True;
                                }
                            }
                            unset($_SESSION["challenge"]);

                        }
                        //password stuff
                        if (isset( $xelements[$field]["nodeName"] )  && $xelements[$field]["nodeName"] == "secret" && isset($xelements[$field]["change_password"])) {
                            if ($_POST[$field] != $_POST["popoon_password_compare_".$field]) {
                                $this->setError("Die beiden neuen Passwoerter sind nicht gleich.");
                                $noSql = True;
                            } else if (strlen($_POST[$field]) < $this->minPasswordLength) {
                                $this->setError("Das neue Passwort muss min. ".$this->minPasswordLength." Zeichen lang sein.");
                                $noSql = True;
                            } else {
                                $_POST[$field] = md5($_POST[$field]);
                            }
                        }

                        if ($noSql != true) {
                            if (isset(  $xelements[$field]["popoon"]) && $xelements[$field]["popoon"]->get_Attribute("nl2br")) {
                                $_POST[$field] = nl2br(strip_tags(rtrim($_POST[$field]),"<a>"));
                            }
                            if (isset(  $xelements[$field]["popoon"]) && $xelements[$field]["popoon"]->get_Attribute("cdata") && substr($_POST[$field],0,9) != "<![CDATA[") {
                                $_POST[$field] = "<![CDATA[".$_POST[$field]."]]>";

                            }
                            if (!ini_get("magic_quotes_gpc")) {
                                array_push($sqlUpdate,"$field = '".mysql_escape_string ($_POST[$field])."'");
                            } else {
                                array_push($sqlUpdate,"$field = '".$_POST[$field]."'");
                            }
                        }   
                    }

                    //select stuff

                    else if (isset( $xelements[$field]["nodeName"] )  && $xelements[$field]["nodeName"] == "select" ) {

                        if (isset($_POST[$field])  && is_array($_POST[$field])) {
                            $_POST[$field] = serialize($_POST[$field]);
                        } else if (isset($_POST[$field])  && $_POST[$field] == "on") {
                            $_POST[$field] = 1;
                        } else {
                            $_POST[$field] = 0;
                        }
                        if (!ini_get("magic_quotes_gpc")) {
                            array_push($sqlUpdate,"$field = '".mysql_escape_string ($_POST[$field])."'");
                        } else {
                            array_push($sqlUpdate,"$field = '".$_POST[$field]."'");
                        }
                    }

                    //FILE upload code
                    else if (isset($_FILES[$field]) && $_FILES[$field]['tmp_name']) {
                        
                        //no error yet for this field
                        $uploadError = FALSE;
                        
                        //image or document? (types to be used: 'img', ')
                        if (isset($_POST["popoon_files_type_$field"])) {                        
                            $type = $_POST["popoon_files_type_$field"];                        
                        } 
                        else {                        
                            $type = 'img';   //img is default type in order not to break backwards compatibility (ok?)                                                                    
                        }
                                                
                        //in any case: urlencode file name
                        $_FILES[$field]['name'] = str_replace("%","$",urlencode($_FILES[$field]['name'] ));
                        $_ext = strtolower(substr( $_FILES[$field]['name'], strrpos($_FILES[$field]['name'], ".")));
                        
                        //IMAGE upload code
                        if($type == 'img') {
                        
                            $imagesize = getimagesize($_FILES[$field]['tmp_name']);
                            if (!is_array($imagesize)) {
                                $this->setError($_FILES[$field]['name'] ." seems not to be an image, not saved");
                                $uploadError = TRUE;
                            } 
                            else {                                                     
                                
                                //check image type and correct extension
                                    switch ($imagesize[2]) {
                                        case 1:
                                            $_extCorrect = ".gif";
                                            break;
                                        case 2:
                                            $_extCorrect = ".jpg";
                                            break;
                                        case 3:
                                            $_extCorrect = ".png";
                                            break;
                                        case 4:
                                            $_extCorrect = ".swf";
                                            break;
                                        default:
                                            $_extCorrect  = false;
                                            break;
                                    }
                                }
                        }
                        
                        
                        //DOCUMENT upload code
                        elseif ($type = 'doc') {
                        
                            if (!in_array($_ext, $this->allowedDocTypes)){                                			    
			    	$this->setError($_ext ." is not in our list of allowed document types. not saved.");
                                $uploadError = TRUE;			    
			    }
                        
                        }
			
			//unknown 'class' of upload
			else {				    
			    	$this->setError($type ." is not in our list of allowed document classes. not saved.");
                                $uploadError = TRUE;				
			}
                                
                        //finish upload: independent of $type, but only if no error occured. on error go on with next field
                        if ($uploadError){                        
                            //continue;                        
                        }
                        
                        if ($_extCorrect && $_extCorrect != $_ext) {
                                   $_FILES[$field]['name'] = $_FILES[$field]['name'] . $_extCorrect;
                        }
                        
                        
                        if (move_uploaded_file($_FILES[$field]['tmp_name'],$uploaddir."/$ID.".$_FILES[$field]['name'])) {
                                if (ini_get("magic_quotes_gpc")) {
                                    array_push($sqlUpdate,"$field = '".mysql_escape_string ($_FILES[$field]['name'])."'");
                                } else {
                                    array_push($sqlUpdate,"$field = '".$_FILES[$field]['name']."'");
                                }
                                // if the old file was different, delete it...
                                if ($_POST["popoon_files_old_$field"] != $_FILES[$field]['name']) {
                                    @unlink ($uploaddir."/$ID.".$_POST["popoon_files_old_$field"]);
                                }
                            } 
                            else {
                                $this->setError($_FILES[$field]['name'] ." could not be saved");
                            }
                        }
                    }

                
                if (count($sqlUpdate) > 0) {

                    $sql = "update $table set ". implode (",",$sqlUpdate) ." where $idfield = '$idvalue'";
                    $result = $db->query($sql);
                    if (DB::isError($result)) {
                        popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);

                    } else {

                        $this->setError("Ihre Daten wurden geaendert.");
                    }
                    if (isset($mail) and isset($mail['to']) ) {
                        if (isset($mail['subject'])) {
                            $subject = $this->replacePlaceholder($mail['subject']); 
                        } else {
                            $subject = "Data was changed by $idvalue";
                        }

                        if (isset($mail['body'])) {
                            $body = $this->replacePlaceholder($mail['body']); 
                        } else {
                            $body = "The following data was changed:\n";
                            $body .= implode ("\n",$sqlUpdate);
                        }
                        $header = '';
                        $from  = '';
                        if (isset($mail['from'])) {
                            $header = 'From: '.$mail['from'];
                            $from = "-f".$mail['from'];
                        }
                        mail($mail['to'],$subject,$body, $header,$from);
                    }
                }
            }

            $sqlfields = implode(",",$fields);

            $result = $db->getRow("select $sqlfields from $table where $idfield = '$idvalue'",DB_FETCHMODE_ASSOC);
            if (DB::isError($result)) {
                popoon::raiseError("DB Error: ".$result->userinfo,POPOON_ERROR_FATAL);
            }
            // TODO
            // instead of this foreach, we could use xelements as well....
            $fields = array();


            foreach ($forms->nodeset as $element) {
                $name = $element->get_Attribute("ref");
                //if we have a value from the sql query, add it to the xml...
                if (!in_array($ref,$fields)) {
                    array_push($fields,$ref);
                }
                if (isset($result[$name]) && (is_array($result[$name]) || (is_string( $result[$name]) && strlen($result[$name]) > 0))) {
                    $valuefields = $element->get_elements_by_tagname("value");
                    if (count($valuefields) > 0 ) {
                        //TODO: delete all xforms:value fields
                    }
                    if (!method_exists($xml,"create_element_ns")) {
                        popoon::raiseError("Your domxml version does not support create_element_ns(), please update to at least PHP 4.3 or ask chregu@bitflux.ch for a 4.2.x version",POPOON_ERROR_FATAL);
                    }
                    $result[$name] = str_replace("<![CDATA[","",$result[$name]);
                    $result[$name] = str_replace("]]>","",$result[$name]);

                    if (isset($xelements[$name]["popoon"]) && $xelements[$name]["popoon"]->get_Attribute("saveAsArray")) {
                        if (is_string($result[$name])) {
                            $result[$name] = unserialize($result[$name]);
                        }
            
            if (is_array($result[$name])) {
                        foreach($result[$name] as $key => $val) {

                            if ($val) {
                                $value = $xml->create_element_ns("http://www.w3.org/2002/xforms","value","xforms");
                                $value->append_child($xml->create_text_node(utf8_encode($val)));
                                $value->set_attribute("name",$name."[".$key."]");
                                $element->append_child($value);
                            }
                        }
	    }

                    } else {
                        if (isset(  $xelements[$name]["popoon"]) && $xelements[$name]["popoon"]->get_Attribute("nl2br")) {
                            $result[$name] = strip_tags($result[$name],"<a>");
                        }
                        $value = $xml->create_element_ns("http://www.w3.org/2002/xforms","value","xforms");
                        $value->append_child($xml->create_text_node(utf8_encode($result[$name])));
                        $element->append_child($value);
                    }
                }

            }
        }
        $params = $this->getParameter("xslparams");
        $params["error"] = $this->errors;

        if (!isset($params["challenge"])) {
            if (!isset($_SESSION["challenge"])) {
                $this->getChallenge();
            }
            $params["challenge"] = $_SESSION["challenge"];
        }

        $params["lang"] = $this->lang;
        $params["projectDir"] = BX_PROJECT_DIR;

        $xml = $xsl->process($xml,$params);

    }

    function setError($message) {
        $this->errors = utf8_encode( $message);
    }


    /**
    * after used the challenge, we have to change it. but we don't have the auth class, therefore we
    * I just imported the function to here
    */

    function getChallenge() {
        $challenge = md5(uniqid("BXCMSpopoon"));
        $_SESSION["challenge"] = $challenge;
        return $challenge;
    }
    
    function replacePlaceholder($string) {
        preg_match_all("#\{([^}]+)\}#",$string,$match);
        if (isset($match[1])) {
            foreach ($match[1] as $key => $value) {
                if ($value == "idvalue") {
                    $string = str_replace('{'.$value.'}',$this->getParameterDefault("idvalue"),$string);
                }
                else if (isset($_POST[$value])) {
                    $string = str_replace('{'.$value.'}',$_POST[$value],$string);
                } else {
                    $string = str_replace('{'.$value.'}',$this->getParameterDefault($value),$string);
                }
            }
        }
        return $string;
    }
}

?>
