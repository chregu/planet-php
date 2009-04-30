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
// $Id: phpprocessorsax.php 1255 2004-04-22 17:15:25Z chregu $

include_once("popoon/components/transformer.php");

/**
* Evaluates php code in form of processing instructions
*
* One can add for example <?php echo "hello world" ?> 
* into the xml code and it gets evaluated. 
*
* Warning: No checking about security risks is done at the moment!
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: phpprocessorsax.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class transformer_phpprocessorsax extends transformer  {

    var $XmlFormat = "XmlString";
    var $PHPErrorMessage = "PHP-Error. See Debug-Output for Details.";
    var $hasXmlStringInput = True;
    var $hasXmlStringOutput = True;
    var $name = "phpprocessorsax";

    function transformer_phpprocessorsax () {
        $this->xml_parser = xml_parser_create();
    }

    function init($attribs)
    {
        xml_set_object($this->xml_parser,&$this);
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_processing_instruction_handler($this->xml_parser,"_piHandler");

        xml_set_default_handler($this->xml_parser,"_defaultHandler");

        $this->xml = "";
        parent::init($attribs);
    }

    function DomStart(&$xml)
    {
        if (!xml_parse($this->xml_parser, $xml)) {
            $errorxml = explode("\n",$xml);
            print "<font color='red'>";
            printf("XML error: %s at line %d\n%d",
                   xml_error_string(xml_get_error_code($this->xml_parser)),
                   xml_get_current_line_number($this->xml_parser),
                   xml_get_current_column_number($this->xml_parser));
            print "</font><pre>";
            for ($i = 0; $i< count($errorxml); $i++)
            {
                if (($i+1) ==  xml_get_current_line_number($this->xml_parser))
                {

                    print "<font color='red'>".($i+1);
                    print ": ".htmlentities($errorxml[$i])."\n";
                    print "--".str_repeat("-",xml_get_current_column_number($this->xml_parser)+strlen(($i+1)))."^\n";
                    print "</font>";

                }
                else
                {
                    print ($i+1);
                    print ": ".htmlentities($errorxml[$i])."\n";
                }
            }
            die();

        }
        $xml = $this->xml;
    }

    function _piHandler($parser,$target,$data)
    {
        if ($target == "php")
        {
            ob_start();
            $ret = @eval($data);

            if ($ret === False)
            {
                $this->xml .= $this->PHPErrorMessage;
                $this->printDebug("PHP Error in line ".xml_get_current_line_number($this->xml_parser).":");
                $this->printDebug(ob_get_contents());
            }
            else
            {
                $this->xml .= ob_get_contents();
            }
            ob_end_clean();

        }
        else
        {
            $this->xml .= "<?$target $data ?>";
        }
    }


    function _defaultHandler($xml_parser,$data)
    {
        $this->xml .= $data;
    }

    /**
       * the following handlers are all covered with the default handler, not needed anymore
       *
       function _startElement($xml_parser,$element,$attribs)
       {
       	$this->xml .= "<$element";
           foreach ($attribs as $key => $value)
           {
           	$this->xml .= "$key=\"$value\"";
    	}
           $this->xml .= ">";	
    }
    function _endElement($xml_parser,$element)
       {
       	$this->xml .= "</$element>";
    }
       
       function _characterDataHandler($xml_parser,$data)
       {
       	$this->xml .= $data;
       }
    */

    /**
     * Generates empty validityObject
     *
     * Transformers were made cachable by default, this one in most cases isn't.
     * So we'll return an empty valdityObject here and return false when checking it.
     * Perhaps the eval()ed code will somehow be enabled to turn on and off its own cachability?
     * Before doing this I'll have to allow both cachable an non-cachable components in a pipeline..
     *
     * @author Hannes Gassert <hannes.gassert@unifr.ch>
     * @return an empty validityObject (aka array)
     */
    function generateValidity(){
        return array();
    }

    /**
     * Overwrite the method inherited from transformer and make this component uncachable by default
     *
     * @return bool false
     */
    function checkValidity(){
        return(false);
    }
} 


?>
