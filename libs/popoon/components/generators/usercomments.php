<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003,2004 Bitflux GmbH                                 |
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
// | Author: Hannes Gassert <hannes@mediagonal.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: usercomments.php 1255 2004-04-22 17:15:25Z chregu $

require_once('popoon/components/generator.php');
require_once('DB.php');


/**
 * Usercomment Generator: A Wrapper around phorum5
 *
 * This generator let's you have "User Contributed Notes" as
 * e.g. on php.net for each page(URI) you published.
 * It was written for the bxcms-docusite.
 * This isn't much more than  a wrapper class for the functionality
 * provided by phorum5(http://phorum.org/)
 * Each URL is assigned a forum, so the first thing we need to do is get a forum_id for
 * the REQUEST_URI. If there's one, we display the corresponding thread, otherwise a simple
 * form is enough.
 *
 * I also made a set of template for phorum5 which make for produce xml output (in fact a mix of simple docbook
 * and some forum-specific markup), for use with this generator.
 * Phorum5 was alpha when I wrote this and, as most software, not written to be embedded in some other software.
 * Therefore you need to add two lines in phorum5/common.php:
 *
 * $PHORUM = array();
 * + $GLOBALS['PHORUM'] =& $PHORUM;
 * + $PHORUM =& $GLOBALS['PHORUM'];
 *
 * This double reference is necessary because phorum does not actually run in the global namespace
 * and because it's inconsistently programmed.
 *
 *
 * @author   Hannes Gassert <hannes@mediagonal.ch>
 * @version  $Id: usercomments.php 1255 2004-04-22 17:15:25Z chregu $
 * @package  popoon
 */
class generator_usercomments extends generator {


    /**
     * How to connect to the phorum database. Should be configurable via an attribute or something in sitemap.xml
     *
     * This value can be configured by system the administrator.
     *
     * @var boolean wheter or not to make the db connection persistent
     * @access public
     */
    var $usePersistentDbConn = true;

    /**
     * Default value for entry in phorum_forums list_length
     *
     * Can be overridden by <map:parameter name='listlength'
     *
     * @var int how many threads to list on one page
     * @access private
     */
    var $defaultListLength = 30;

    /**
     * @var array  bad functions that included files are not supposed to use, since they would break out from being embedded in this generator.
     * @access private
     */
    var $disabledFunctions = array('header', 'exit');

    /**
     * @var boolean  wether or not to apply utf8_encode() to the data we get from phorum. by the way: i hate these encoding all the time..
     * @access private
     */
    var $utf8encode = false;

    /**
     * forumId of a thread
     *
     * All threads belong into a forum, forumId says to which one.
     * We assign a forumId to each URL, this assignment is stored in the database table phorum_url2forum_id
     *
     * @var int
     * @access protected
     */
    var $forumId;

    /**
     * Threaded or flat forum
     *
     * phorum5 supports threaded discussions as well as simple, "flat" fora.
     * You can specify through a <map:parameter name="threaded" value={"true", "false"} entry in sitemap.xml
     * what you want to have. Note that you must not change your sitemap entry from true to false (or vice versa)
     * once after there are fora in the database.
     *
     * The value of this variable is written into the phorum_forums table.
     *
     * @see getForumId()
     * @var int
     * @access protected
     */
    var $threaded = false;


    /**
     * Maps language abbrevs to names. Should be configurable in sitemap.xml via a map:parameter or alike
     *
     * This value can be configured by system the administrator.
     *
     * @var array this is for mapping language abbreviations such as 'en' to the name of a phorum language set.
     * @access private
     */
    var $langAbbrev2langName = array('en' => 'english',
                                     'de_OT' => 'german_other',
                                     ''   => 'german_polite');


    /**
     * @var array  variables to get from post.php to read.php via get for the purpose of redisplaying their values in case of a partially filled in form.
     * @access private
     */
    var $phorumDataFields = array('thread', 'parent_id', 'author', 'email', 'subject', 'body');

    /**
     * @var array information taken from the current environment, which we'll change and restore afterwards
     * @access private
     */
    var $envBackup = array();

    /**
     * @var  id of the thread beloning to the URI requested
     * @access private
     */
    var $forumId;

    /**
     * @var  boolean indicates if we have a thread to display or not.
     * @access private
     */
    var $isEmptyForum = true;

    /**
     * @var  object  PEAR DB object
     * @access private
     */
    var $db;

    /**
     * @var array information about the database, taken from phorum config
     * @access private
     */
    var $dbInfo = array();

    /**
     * @var    string   URL of request being handled
     * @access private
     */
    var $URL = '';


    /**
     * Constructor
     *
     * The standard constructor.
     *
     * @param object sitemap
     */
    function generator_usercomments(&$sitemap) {

        //this is standard..
        $this->generator($sitemap);
    }

    /**
     * Initiator, called after construction of object
     *
     * We call the parent init method, connect to the phorum database and try to get the threadID.
     *
     * @param $attribs array associative array with element attributes
     * @access public
     */
    function init($attribs)
    {

        $GLOBALS['PHORUM'] = array();

        //this is standard..
        parent::init($attribs);

        //get and check phorum5 install directory
        if (!$this->attribs['phorumroot'] = $this->getParameterDefault('phorumroot')) {

            $this->attribs['phorumroot'] = BX_BITLIB_DIR.'ext/phorum5/';
        }

		if(!is_dir($this->attribs['phorumroot'])) {

            popoon::raiseError('No Usercomments available, phorum5 could not be found in ' . $this->attribs['phorumroot'],
                               POPOON_ERROR_WARNING, __FILE__, __LINE__, null);

		}

        //get REQUEST_URI from sitemap <map:parameter name="RequestURI" .. /> or server environment (sitemap overwrites environment)
        if($paramURI = $this->getParameterDefault('RequestURI')) {
            $this->URL = $paramURI;
        }
        // if there's a ? in the query string, strip it away, we're not interested in the stuff after that
        /* if you need it, use the RequestURI parameter */
        else if (($_querypos = strpos($_SERVER['REQUEST_URI'],"?")) > 0 ) {
            $parsedURL = parse_url(substr($_SERVER['REQUEST_URI'],0,$_querypos));
            $this->URL = $parsedURL['path'];
        } 

        else {
            $parsedURL = parse_url($_SERVER['REQUEST_URI']);
            $this->URL = $parsedURL['path'];
        }


        //set interface language
        $this->setLanguage();

        //switch on/off threaded mode
        $this->setThreaded();

        //get connected to phorum db
        $this->getConnection();

        //find or create of forumId
        $this->getForumId($this->URL);

        $_POST = $_REQUEST;

    }

    /**
     * makes phorum generate an xml string from the data it has, using it's own templating mechanism.
     *
     * @access public
     * @returns XML..
     */
    function DomStart(&$xml)
    {

        $this->prepareEnvironment();

        ob_start();

        //dispatch on request..simple at the moment
        if(($this->isEmptyForum && !isset($_POST['post'])) ||
           (!isset($_POST['post']) && isset($_GET['url']) && (strpos($_GET['url'], 'post.php') !== false)) ||
           (!isset($_POST['post']) && isset($_GET['error']))){

            if(!$this->threaded) {
                $GLOBALS['PHORUM']['DATA']['POST']['thread'] = '1';
            }

            $this->showForm();

        }

        elseif (isset($_POST['post'])) {

            $this->doPost();

        }

        elseif($this->threaded && !isset($_GET['thread']) &&!isset($_GET['error'])) {

            $this->listThreads();
        }

        else {

            $this->doRead();

        }

        $xml = ob_get_contents();

        ob_end_clean();

        //restore environment
        $this->restoreEnvironment();


        if($this->utf8encode){

            //this is due to the short_open_tag problem and, of course, the templating engine of phorum5
            $xml = '<' . "?xml version='1.0' encoding='UTF-8'?>\r\n" . utf8_encode($xml);

        }
        else{

            $xml = '<' . "?xml version='1.0' encoding='ISO-8859-1'?>\r\n" . $xml;

        }

        
    }


    /**
     * Show form
     *
     * This happens if there are no threads for the given url
     */
    function showForm(){

        include($this->attribs['phorumroot'] . '/common.php');
        include($this->attribs['phorumroot'] . '/include/post_form.php');
        
    }

    /**
     * Answers to a request for listing all threads in a forum
     *
     * @access public
     * @returns some kind of xml
     */
    function listThreads(){

        include($this->attribs['phorumroot'] . '/list.php');

    }

    /**
     * Answers to a request for reading a thread
     *
     * @access public
     * @returns some kind of xml
     */
    function doRead(){

        //just include the script, output will be intercepted
        include($this->attribs['phorumroot'] . '/read.php');

    }

    /**
     * Answers to a request for posting a new comment
     *
     * @access public
     * @returns some kind of xml
     * @see includeDisableFunctions()
     */
    function doPost(){

        //Phorum expects all data to be in $_POST
        $_POST =& $_REQUEST;

        //check for previous errors
        if(!empty($this->error)){

            $error = $this->error;

        }
        else {

            //include the script, but don't allow it to use header() to redirect somewhere else..and return $error
            $error = $this->includeDisableFunctions($this->attribs['phorumroot'] . '/post.php', $this->disabledFunctions  ,
                                                    'error');
        }

        //pass on error message, if there's one
        if (!empty($error)){

            $getVars = 'error=' . urlencode($error);

            //we need to pass on all fields that were entered to redisplay them
            foreach($this->phorumDataFields as $varName){

                if(isset($_POST[$varName])){

                    $getVars .= '&' . $varName . '=' .  urlencode($_POST[$varName]);

                }

            }

            $getVars .= '&#error';
        }
        else{

            $getVars = '';
            //$this->getForumID();

        }



        //reload..this could probably be done without a HTTP redirection..but phorum does it like this, too..
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $this->URL . '?'. $getVars);
    }

    /**
     * Fetches thread ID belonging to the URL requested.
     *
     * @access public
     * @returns integer thread id from database
     */
    function getForumId(&$URL){


        // "/tutorials/slideml" should be the same as "/tutorials/slideml/" and /tutorial//slideml
        if (substr($URL, -1) == '/'){

            $URL = substr($URL, 0, -1);

        }

        $URL = preg_replace('#//+#', '/', $URL);

		//figure out table names
        $forumTable = $this->dbInfo['table_prefix'] . '_forums';
        $messageTable = $this->dbInfo['table_prefix'] . '_messages';

        //try to fetch forumId. We use the column "name" to store the URL in.
        if($this->checkPhorumDbError($this->forumId  =
                                     $this->db->getOne("SELECT forum_id FROM $forumTable WHERE name = '$URL'") , __LINE__)) {

            //if there's no entry, we'll create one.
            if(is_null($this->forumId)) {

                //set list_length for insertion
                if(!$list_length = $this->getParameterDefault('listlength')){

                    $list_length = $this->defaultListLength;

                }

                //if there's no forum_id for this URL, we'll enter a new one
                $this->forumId = $this->db->nextID($forumTable);

                $this->checkPhorumDbError($this->db->query("INSERT INTO $forumTable SET".
                                                           '  forum_id       = ' . $this->forumId .
                                                           ', name           = "'. $this->URL .'"'.
                                                           ', list_length    ='  . $list_length   .
                                                           ', threaded       ='  . (int) $this->threaded .
                                                           ', language       = "'. $GLOBALS['PHORUM']['language'] .'"'.
                                                           ', last_post_time ='  . date('Y')),
                                          __LINE__) ;
                

                $this->isEmptyForum = true;
            }

            else{

                if($this->checkPhorumDbError($messageCount  =
                                             $this->db->getOne("SELECT COUNT(message_id) FROM $messageTable WHERE forum_id = " . $this->forumId) , __LINE__)) {

                    if($messageCount == 0) {

                        $this->isEmptyForum = true;

                    }
                    else{

                        $this->isEmptyForum = false;

                    }

                }

            }

        }

    }


    /**
     * Tries to connect to the phorum-DB
     *
     * @access private
     * @returns boolean
     */
    function getConnection(){

        //get db config for phorum5 from sitemap.xml
        $dsn = $this->getParameterDefault("dsn");

        $parsedDSN = DB::parseDSN($dsn);

        $PHORUM["DBCONFIG"]=array(

                                  "type"          =>  $parsedDSN["phptype"],
                                  "name"          =>  $parsedDSN["database"],
                                  "server"        =>  $parsedDSN["hostspec"],
                                  "user"          =>  $parsedDSN["username"],
                                  "password"      =>  $parsedDSN["password"],
                                  "table_prefix"  =>  "phorum"

                                  );

        $this->dbInfo = $PHORUM['DBCONFIG'];

        //if this GLOBAL is set, PHORUM won't try to load the config.inc.php
        $GLOBALS["PHORUM_ALT_DBCONFIG"]= $PHORUM['DBCONFIG'];

        //get connected
        return($this->checkPhorumDbError($this->db = DB::connect($dsn, $this->usePersistentDbConn), __LINE__));

    }

    /**
     * Change some environment variables to accomodate phorum5
     *
     * @access private
     * @see restoreEnvironment(), $this->envBackup
     */
    function prepareEnvironment(){

        //backup environment
        $this->envBackup['QUERY_STRING'] = $_SERVER['QUERY_STRING'];
        $this->envBackup['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
        $this->envBackup['cwd'] = getcwd();

        //change environment to make it comfortable for phorum5.
        $_SERVER['QUERY_STRING'] = $this->forumId;
        
        //non-threaded forums only have a single thread for us, it's thread number one.
        if($this->threaded && isset($_GET['thread'])){

            //$threadId = $_GET['thread'];
            if(isset($_GET['url'])){
            
                $_SERVER['QUERY_STRING'] =  substr($_GET['url'], (strpos( $_GET['url'], '?') + 1));

            }

            else {

                $_SERVER['QUERY_STRING'] .= ',' . $_GET['thread'];

            }
        
        }
        elseif(!$this->threaded){

            $_SERVER['QUERY_STRING'] .= ',1';

        }

    
        chdir($this->attribs['phorumroot']);

        //pass error message
        if (isset($_GET['error'])) {

            if(get_magic_quotes_runtime() != 1){

                $_GET['error'] = stripslashes($_GET['error']);

            }

            $GLOBALS['PHORUM']['DATA']['ERROR'] = $_GET['error'];
            
        }

        $GLOBALS['PHORUM']['DATA']['PATH']  = $this->URL;

        //reintroduce what we smuggled via $_GET
        foreach($this->phorumDataFields as $varName){

            if(isset($_GET[$varName])){

                $GLOBALS['PHORUM']['DATA'][$varName] = $_GET[$varName];
                $GLOBALS['PHORUM']['DATA']['POST'][str_replace('_', '', $varName)] = $_GET[$varName];
                
            }
            else{

                $GLOBALS['PHORUM']['DATA'][$varName] = '';

            }

        }

    }

    /**
     * Restore some environment variables
     *
     * @access private
     * @see prepareEnvironment()
     */
    function restoreEnvironment(){

        $_SERVER['QUERY_STRING'] = $this->envBackup['QUERY_STRING']; //what a luck this ain't a constant..
        $_SERVER['REQUEST_URI']  = $this->envBackup['REQUEST_URI'];
        chdir($this->envBackup['cwd']);

    }

    /**
     * Set $PHORUM['language'] according to the URI/Param/..
     *
     * Language is given as a two-letter code, either as first part of the REQUEST_URI or via map:parameter name="language" in sitemap.xml
     * So try to get one of these and translate it into a name understood by phorum.
     *
     * @access public
     * @see langAbbrev2langName
     */
    function setLanguage(){

        //check for map:parameter
        if($lang = $this->getParameterDefault('language')) {

            if (isset($this->langAbbrev2langName[$lang])){
                $GLOBALS['PHORUM']['language'] = $this->langAbbrev2langName[$lang];
            }
        }

        //check for language in the URL
        else {

            $pathParts = split('/', $this->URL);
            $langAbbrev = $pathParts[1];

            if (isset($this->langAbbrev2langName[$langAbbrev])) {

                $GLOBALS['PHORUM']['language'] = $this->langAbbrev2langName[$langAbbrev];
            }
        }

        //if neither of the above succeeded use the default language
        if(empty($GLOBALS['PHORUM']['language'])) {
            $GLOBALS['PHORUM']['language'] = $this->langAbbrev2langName[''];
        }

    }

    /**
     * eval()s the code in $file while commenting out all calls to the functions $functions
     *
     * THIS IS SO GOD DAMNED DIRTY!!
     * Note that this behaves in fact quite different from include() and that the regular expression applied can
     * easily cause syntax errors!
     *
     * @param string filename of php file to be included
     * @param mixed  one or more functions to be disabled
     * @param string name of variable whose value should be returned.
     * @returns mixed  value of variabled named $retvar
     */
    function includeDisableFunctions($file, $disabledFunctions = NULL, $retvar = NULL){

        //if we need to include something this way it's probably dirty code.. so bettern ignore errors
        error_reporting(0);

        if (is_null($disabledFunctions)) @include($file);
        else{

            if (is_array($disabledFunctions)) {

                $disabledFunctions = '(' . implode('|', $disabledFunctions) . ')';

            }

            $regexp = '/' . $disabledFunctions . '\(.*\);/Usi';
            $code = preg_replace($regexp, '/* \0 */', implode('', file($file)));
            eval('?>' . $code);            //yeah! this is *so* evil! :]
        }


        //well..if we came that far we could actually turn on error reporting again..
        error_reporting(E_ALL);

        if (!is_null($retvar) && isset($$retvar)){

            return($$retvar);

        }

    }


    /**
     * Find out wether a threaded- oder flat-mode forum is going to be displayed
     *
     * Try to read ot get the value of a map:parameter named "threaded".
     * If it is present and is set to true, switch on threaded mode.
     * In any other case, leave it off.
     *
     * @access  private
     * @returns integer
     */
    function setThreaded() {

        if(strtolower($this->getParameterDefault('threaded')) == 'true') {

            $this->threaded = true;

        }

        else{

            $this->threaded = false;
        }

        $GLOBALS['PHORUM']['threaded'] =  (int) $this->threaded;

    }


    /**
     * Central error handler for phorum database errors.
     *
     * Just another indirection in the error handling system to save some lines of code.
     * Seems to me like a perversion of the nice error handling system.. (?)
     *
     * @access  private
     * @returns boolean
     */
    function checkPhorumDbError(&$pearDbObject, $line) {

        if (DB::isError($pearDbObject)) {

            popoon::raiseError('User Contributed Notes unavailable due to a database error. The exact reason is: ' . $pearDbObject->getMessage(),
                               POPOON_ERROR_WARNING, __FILE__, $line, null);
            return(false);
        }
        else {

            return(true);

        }

    }

}


?>
