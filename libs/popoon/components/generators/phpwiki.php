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
// $Id: phpwiki.php 1255 2004-04-22 17:15:25Z chregu $
include_once("popoon/components/generator.php");

/**
* This class reads an xml-file from the filesystem
*
*  Reads the xml-file stated in the "src" attribute in map:generate
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: phpwiki.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
*/
class generator_phpwiki extends generator {


    var $theme = 'popoon';
    var $wikiName = 'Bitflux Wiki';
    
    /**
    * Constructor, does nothing at the moment
    */
    function generator_phpwiki (&$sitemap) {
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
    *  @param $attribs array    associative array with element attributes
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
         global $request;
         global $WikiNameRegexp, $KeywordLinkRegexp;

         $old_incl_path = ini_get("include_path");

         ini_set("include_path",$this->getParameterDefault("phpwikiDir").PATH_SEPARATOR.$old_incl_path);


         $HTTP_SERVER_VARS = $_SERVER;

         define ('USE_PREFS_IN_PAGE', false);
         define('THEME', $this->theme);
         define('WikiName', $this->wikiName);
         
         define('PHPWIKI_DIR', BX_BITLIB_DIR.'php/popoon/components/generators/phpwiki');

         include "phpwiki/config.inc.php";

         $GLOBALS['DBParams'] = array(
            'dbtype' => 'SQL',
            'dsn' => $this->getParameterDefault('dsn'),
            'db_session_table'   => 'phpwiki_session',
            'prefix' => 'phpwiki_'
            );

         // disable some error reports
         $old_error_level = error_reporting();
         error_reporting($old_error_level & ~E_USER_NOTICE);

         require_once('phpwiki/WikiRequest.php');
    
    

         $request = new WikiRequest();
         if ($this->getParameterDefault("pagename")) {
             $request->setArg("pagename", $this->getParameterDefault("pagename"));
            }
         /*
          * Allow for disabling of markup cache.
          * (Mostly for debugging ... hopefully.)
          *
          * See also <?plugin WikiAdminUtils action=purge-cache ?>
          */
         if (!defined('WIKIDB_NOCACHE_MARKUP') and $request->getArg('nocache'))
             define('WIKIDB_NOCACHE_MARKUP', $request->getArg('nocache'));

         // Initialize with system defaults in case user not logged in.
         // Should this go into constructor?
         $request->initializeTheme();
         $request->updateAuthAndPrefs();
         $request->possiblyDeflowerVirginWiki();
    
    
         ob_start();

         $validators = array('wikiname' => WIKI_NAME,
                             'args'	=> hash($request->getArgs()),
                             'prefs'	=> hash($request->getPrefs()));

         if (CACHE_CONTROL == 'STRICT') {
             $dbi = $request->getDbh();
             $timestamp = $dbi->getTimestamp();
             $validators['mtime'] = $timestamp;
             $validators['%mtime'] = (int)$timestamp;
         }
         // FIXME: we should try to generate strong validators when possible,
         // but for now, our validator is weak, since equal validators do not
         // indicate byte-level equality of content.  (Due to DEBUG timing output, etc...)
         //
         // (If DEBUG if off, this may be a strong validator, but I'm going
         // to go the paranoid route here pending further study and testing.)
         //
         $validators['%weak'] = true;


        $request->setValidators($validators);


        $request->handleAction();



        $xml = str_replace("&nbsp;","&#160;",preg_replace("#^([^{<\?}]*)<\?#","<?",ob_get_contents()));

        ob_end_clean();

        ini_set("include_path",$old_incl_path);
        error_reporting($old_error_level);
        return True;
    }

    
    /* CACHING STUFF */

    /**
     * Generate cacheKey
     *
     * Calls the method inherited from 'Component'
     *
     * @param   array  attributes
     * @param   int    last cacheKey
     * @see     generateKeyDefault()
     */
    function generateKey($attribs, $keyBefore){
        return($this->generateKeyDefault($attribs, $keyBefore));
    }

    /** Generate validityObject  
     *
     * This is common to all "readers", you'll find the same code there.
     * I'm thinking about making a method in the class component named generateValidityFile() or alike
     * instead of having the same code everywhere..
     *
     * @author Hannes Gassert <hannes.gassert@unifr.ch>
     * @see  checkvalidity()
     * @return  array  $validityObject contains the components attributes plus file modification time and time of last access.
     */
    function generateValidity(){
        $validityObject = $this->attribs;
        $src = $this->getAttrib("src");
        $validityObject['filemtime'] = filemtime($src);
        $validityObject['fileatime'] = fileatime($src);
        return($validityObject);
    }

    /**
     * Check validity of a validityObject from cache
     *
     * This implements only the most simple form: If there's no fresher version, take that from cache.
     * I guess we'll need some more refined criteria..
     *
     * @return  bool  true if the validityObject indicates that the cached version can be used, false otherwise.
     * @param   object  validityObject
     */
    function checkValidity($validityObject){
        return(isset($validityObject['src'])       &&
               isset($validityObject['filemtime']) &&
               file_exists($validityObject['src']) &&
               ($validityObject['filemtime'] == filemtime($validityObject['src'])));
    }

}


?>
