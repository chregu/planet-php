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
// $Id: tidy.php 3684 2005-02-16 14:36:31Z silvan $

include_once("popoon/components/transformer.php");

/**
* Transforms an XML-Document with the help of libxslt out of domxml
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: tidy.php 3684 2005-02-16 14:36:31Z silvan $
* @package  popoon
*/
class popoon_components_transformers_tidy extends popoon_components_transformer {

    public $XmlFormat = "XmlString";
    public $classname = "tidy";


    function __construct(&$sitemap) {
        parent::__construct($sitemap);
    }

    function DomStart(&$xml)
    {
        parent::DomStart($xml);
        // default properties
        // can be overwritten from the sitemap with for example
        //  <map:parameter name="wrap" value="80"/>

        $options = array(
            "output-xhtml" => true,
            "clean" => false,
            "wrap" => "350",
            "indent" => true,
            "indent-spaces" => 1,
            "ascii-chars" => false,
            "char-encoding" => "utf8",
            "wrap-attributes" => false,
            "alt-text" => "",
            "doctype" => "loose",
            "numeric-entities" => true,
            "drop-proprietary-attributes" => true
            );

        foreach ($this->getParameter("default") as $key => $value) {
            switch ($value) {
                case "yes":
                case "true":
                    $options[$key] = true;
                    break;
                case "no":
                case "false":
                    $options[$key] = false;
                    break;
                default:
                    $options[$key] = $value;
            }
        }

        if (class_exists("tidy")) {
            $tidy = new tidy();
        }
        if(!$tidy) {
            throw new Exception("Something went wrong with tidy initialisation. Maybe you didn't enable ext/tidy in your PHP installation. Either install it or remove the tidy transformer from your sitemap.xml");
        }
        $charencoding = $options["char-encoding"];
        unset ($options["char-encoding"]);
        $tidy->parseString($xml,$options,$charencoding);
        $tidy->cleanRepair();
        $xml = (string) $tidy;
       unset($tidy);

        if (isset($options['remove-xmlns']) && $options['remove-xmlns']) {
            $xml = preg_replace('/xmlns="[^"]*"/','',$xml);
        }


    }
}


?>
