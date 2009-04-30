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
// $Id: libxslt.php 1255 2004-04-22 17:15:25Z chregu $


/**
* DEPRECATED use xslt transformer instead!
*
* Transforms an XML-Document with the help of libxslt out of domxml
*
* libxslt is the Gnome XSLT-Processor from the libxml project.
* It's integrated in ext/domxml since 4.2 and has the big advantage
*  that it takes a DomDocument Object for processing. Furthermore
*  it's said to be much faster than sablotron.
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: libxslt.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class popoon_components_transformers_libxslt extends popoon_components_transformers_xslt {
    
    public  $classname = "libxslt";    
}


?>
