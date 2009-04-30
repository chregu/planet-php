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
// $Id: formwizard.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/transformer.php");

/**
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: formwizard.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class transformer_formwizard extends transformer {
    
    var $XmlFormat = "DomDocument";
    var $src = "bla.xml";
    var $lang = "de";
    function transformer_xforms(&$sitemap) {
        $this->transformer($sitemap);
    }
    
    function DomStart(&$xml) {
        parent::DomStart($xml);
        $mafo = $this->getParameterDefault("mafo");
        $this->lang = $this->getParameterDefault("lang");
        $xsl = domxml_xslt_stylesheet_file(BX_BITLIB_DIR."/php/popoon/components/transformers/formwizard/xml2html.xsl");
        
        $this->printDebug("XSL-File: ".$this->src);
        
        $ctxt = $xml->xpath_new_context();
        //        $ctxt->xpath_register_ns("xforms","http://www.w3.org/2002/xforms");
        
        
        $xforms = $ctxt->xpath_eval("//Article[lang='".$this->lang."']//formwizard");
        $wizardnode = $xforms->nodeset[0];
        
        if ($wizardnode) {
            
            
            if (isset($_GET['src'])) {
                $xforms = $ctxt->xpath_eval("//Article[lang='".$this->lang."']//formwizard[@link ='".$_GET['src']."']");
                $wizardnode = $xforms->nodeset[0];
                if ($wizardnode) {
                $subxml = $this->drawOverview($wizardnode);
                  if ($subxml) {
                        $subxml = $xml->imported_node($subxml->document_element(),true);
                        $parent = $wizardnode->parent_node();
                        $parent->replace_child($subxml,$wizardnode);
                    }
                
                }
            } else if ($wizardnode->has_attribute("overview") && $wizardnode->get_attribute("overview") == "yes") {
                foreach ($xforms->nodeset as $wizardnode) {
                    if($wizardnode->has_attribute("mafo")) {
                        if (!($wizardnode->get_attribute("mafo") & $mafo)) {
                            continue;
                        }
                    }
                    $subxml = $this->drawGeneralOverview($wizardnode);
                    if ($subxml) {
                        $subxml = $xml->imported_node($subxml->document_element(),true);
                        $parent = $wizardnode->parent_node();
                        $parent->replace_child($subxml,$wizardnode);
                    }
                    
                }
                
            }
            else { 
                $src = $wizardnode->get_attribute("src");
                $this->config = domxml_open_file(BX_PROJECT_DIR."/$src");
                $confctxt = $this->config->xpath_new_context();
                $confctxt->xpath_register_ns("bxco","http://bitflux.org/config/1.0");
                
                
                if (isset($_POST["thisPage"])) {
                    $allfields = $confctxt->xpath_eval("/bxco:wizard/bxco:screen[@id = '".$_POST["thisPage"]."']//bxco:field");
                    if (isset($allfields->nodeset)) {
                        foreach($allfields->nodeset as $node) {
                            $fields[$node->get_attribute("name")] = $node;
                            if ($node->get_attribute("type") == "checkboxtext") {
                                $fields[$node->get_attribute("name")."_text"] = $node;
                            }
                            else if ($node->get_attribute("type") == "radio") {
                                foreach($node->child_nodes() as $childnode) {
                                    
                                    if ($childnode->node_name() == "option") {
                                        if ($childnode->get_attribute("type") == "text") {
                                            $fields[$childnode->get_attribute("name")."_text"] =  $childnode ;
                                            
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if(!isset($_SESSION["bx_wizard"]) || !is_array($_SESSION["bx_wizard"])) {
                    $_SESSION["bx_wizard"] = array();
                }
                if ( isset($fields) && is_array($fields)) {
                    
                    foreach($fields as $key => $node) {
                        $value = $_POST["bx_fw"][$key];
                        if ($node->get_attribute("required") == "y" &&  strlen(trim($value)) == 0) {
                            $node->set_attribute("error","required");
                            $error = true;
                        }
                        
                        if ($node->get_attribute("type") == "session") {
                            $bx_wizard_fields[$key] = $_SESSION["auth"]["fields"][$node->get_attribute("value")];
                        } else {
                            if (strlen($value) > 0 ) {
                                $node->set_attribute("value",utf8_encode($value));
                            }
                            $bx_wizard_fields[$key] = $value;
                        }
                    }
                   
                    $_SESSION["bx_wizard"] = array_merge($_SESSION["bx_wizard"],$bx_wizard_fields);
                }
                
                $params = array();
                
                
                
                if ($error) {
                    $params['screenid'] = $_POST['thisPage'];
                }
                if (isset($_POST["thisPage"]) && !$error) {
                    $screen= $confctxt->xpath_eval("/bxco:wizard/bxco:screen[@id = '".$_POST["thisPage"]."']");
                    $screen = $screen->nodeset[0];
                    
                    //screen has a method
                    if($screen && $screen->has_attribute("method")) {
                        $method = $screen->get_attribute("method");
                        $this->$method();
                    }
                }
                if (isset($_POST['nextPage']) && !$error) {
                    $params['screenid'] = $_POST['nextPage'];
                }
                $params['lang'] = $this->lang;
      
                $result = $xsl->process($this->config,$params);
                
                $parent = $wizardnode->parent_node();
                $subxml = $result->document_element();
                if ($subxml) {
                    $subxml = $xml->imported_node($subxml,true);
                    $parent->replace_child($subxml,$wizardnode);
                }
                
            }

        }
        
    }
    
    
    
    
    function saveFields() {
        $confctxt = $this->config->xpath_new_context();
        $confctxt->xpath_register_ns("bxco","http://bitflux.org/config/1.0");
        // get all fields
        $allfields = $confctxt->xpath_eval("/bxco:wizard/bxco:screen//bxco:field[@type!= 'msg']");
        if (isset($allfields->nodeset)) {
            foreach($allfields->nodeset as $node) {
                $fields[$node->get_attribute("name")] = $_SESSION["bx_wizard"][$node->get_attribute("name")] ;
                if ($node->get_attribute("type") == "checkboxtext") {
                    $fields[$node->get_attribute("name")."_text"] =  $_SESSION["bx_wizard"][$node->get_attribute("name")."_text"] ;
                } 
                
                else if ($node->get_attribute("type") == "radio") {
                    foreach($node->child_nodes() as $childnode) {
                        
                        if ($childnode->node_name() == "option") {
                            if ($childnode->get_attribute("type") == "text") {
                                $fields[$childnode->get_attribute("name")."_text"] =  $_SESSION["bx_wizard"][$childnode->get_attribute("name")."_text"] ;
                            }
                        }
                    }
                }
                        
                
                
                
            }
        }
        $table = $confctxt->xpath_eval("/bxco:wizard/bxco:screen/@table");
        $table = $table->nodeset[0]->get_content();
        if ($table ) {
            $sql = "insert into `$table` (`";
            $sql .= join("`,`",array_keys($fields)) . "`) ";
            $sql .= "VALUES ('". join("','",$fields) ." ')";
            
            $db = $this->getParameterDefault("db");
            $res = $db->query($sql);
            if (DB::isError($res)) {
                
                print $res->message;
                print "<br/>";
                print $res->userinfo;
            } 
        } else {
            print "no table info found";
        }
        $_SESSION["bx_wizard"] = array();
        return true;
    }
    
    function setError($message) {
        $this->errors = utf8_encode( $message);
    }
    
    function getText($id,$lang = 'de') {
        
       $text = $this->confctxt->xpath_eval("/bxco:wizard/bxco:lang/bxco:entry[@ID='$id']/bxco:text[@lang='$lang']");
       if ($text->nodeset && $text->nodeset[0]) {
           return $text->nodeset[0]->get_content();
        } else { 
            if ($lang != 'de') {
                return $this->getText($id, $lang='de');
            } else {
                return "";
            }
        };
    }
    
    
    function drawGeneralOverview ($wizardnode) {
        $src = $wizardnode->get_attribute("src");
        $this->config = domxml_open_file(BX_PROJECT_DIR."/$src");
        $this->confctxt = $this->config->xpath_new_context();
        $this->confctxt->xpath_register_ns("bxco","http://bitflux.org/config/1.0");  
        $subxml = "<div class='wizardOverview'>";
        $title = $this->confctxt->xpath_eval("/bxco:wizard/bxco:title[@lang = '".$this->lang."']");
        $subxml .= "<a href='./?src=". $wizardnode->get_attribute("link") ."'>". $title->nodeset[0]->get_content()."</a>";
        $subxml .= "</div>";
        $subxml = domxml_open_mem($subxml);
        return $subxml;
    }
        
    function drawOverview($wizardnode) {
        
        $src = $wizardnode->get_attribute("src");
        $this->config = domxml_open_file(BX_PROJECT_DIR."/$src");
        $this->confctxt = $this->config->xpath_new_context();
        $this->confctxt->xpath_register_ns("bxco","http://bitflux.org/config/1.0");   
        
        $subxml = "<div class='wizardOverview'>";
        $title = $this->confctxt->xpath_eval("/bxco:wizard/bxco:title[@lang = '".$this->lang."']");
        $subxml .= "<div class='wizardTitle'>". $title->nodeset[0]->get_content()."</div>";
        $text =   $this->confctxt->xpath_eval("/bxco:wizard/bxco:overview/bxco:text");
    
        
        $dbparam =  $this->confctxt->xpath_eval("/bxco:wizard/bxco:overview/bxco:link");
        $dbparam = $dbparam->nodeset[0];
    //       <link thisname="Login_Name" thatname="Login_Name" type="session2dbfield"/>
        $sessionID = $_SESSION["auth"]["fields"][$dbparam->get_attribute("thisname")];
        $dbID = $dbparam->get_attribute("thatname");
        $db = $this->getParameterDefault("db");
        $table =   $this->confctxt->xpath_eval("/bxco:wizard/bxco:screen/@table");
        $table = $table->nodeset[0]->get_content();
        $count =  $db->getOne("Select count(*) as c from $table where $dbID = '$sessionID'");
        $overview  = $this->confctxt->xpath_eval("/bxco:wizard/bxco:overview");
        $multipleSubmit = $overview->nodeset[0]->get_attribute("multipleSubmit") ;
        if ( $text->nodeset[0] && ($multipleSubmit == "yes" || $count == 0 )) {
            $subxml .="<div class='wizardIntro'>".$this->getText($text->nodeset[0]->get_attribute("name"),$this->lang)."</div>";
            
        }
        if ($overview->nodeset) {
           
            if ( $multipleSubmit == "yes") {
                $subxml .="<div><b>History</b></div>";
                $subxml .= "<div class='wizardStatusInfo'>".$this->getText("sie_haben",$this->lang)." $count ".$this->getText("bogen_ausgefuellt",$this->lang)."</div>";
                $subxml .= "<div><input type='button' onclick='location.href=\"./". $wizardnode->get_attribute("link") ."\"' value='". $this->getText("neuer_ausfuellen",$this->lang)."'/></div>";   
            } else {
                if ($count == 0) {
                    $subxml .= "<div><input type='button' onclick='location.href=\"./". $wizardnode->get_attribute("link") ."\"' value='". $this->getText("noch_ausfuellen",$this->lang)."'/></div>"; 
                } else {
                    $subxml .= "<div class='wizardStatusInfo'>". $this->getText("bereits_ausgefuellt",$this->lang)."</div>";   
                }    
                    
            }
        }
        
        
        $subxml .= "</div>";
        $subxml = domxml_open_mem($subxml);
        return $subxml;
    }
    
    /**
    * after used the challenge, we have to change it. but we don't have the auth class, therefore we
    * I just imported the function to here
    */
    
    
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
