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
// $Id: popoon.php 4282 2005-05-18 05:52:27Z chregu $

/**
* We have different error levels in popoon, it's much like
*  the PHP error handling. With PEAR::raiseError you can
*  declare how severe your error is.
**/
/**
* Won't produce any output ever
* @const POPOON_ERROR_SILENT
* @access public
*/
define('POPOON_ERROR_SILENT', 	1);
/**
* Errors, which are not that grave
* Errors with that level won't disturb popoon. They are more
*  of an informational level
* @const POPOON_ERROR_NOTICE
* @access public
*/
define('POPOON_ERROR_NOTICE', 	2);
/**
* Errors, which should be avoided, but the script is able to run further
* Errors with that level could have bad effects on popoon, but normally don't
* @const POPOON_ERROR_WARNING
* @access public
*/
define('POPOON_ERROR_WARNING', 	4);
/**
* Errors, which stop the execution of popoon
* Errors with that level have severe impact on popoon and the script will be stoped
* @const POPOON_ERROR_WARNING
* @access public
*/
define('POPOON_ERROR_FATAL',   	8);

/**
* Can be used for old deprecated functions..
* @const POPOON_ERROR_DEPRECATED
* @access public
*/
define('POPOON_ERROR_DEPRECATED',   	16);

/**
* No Error-reporting at all....
* @const POPOON_ERROR_NONE
* @access public
*/
define('POPOON_ERROR_NONE', 0);
/**
* Every error-reporting level
* @const POPOON_ERROR_NONE
* @access public
*/
define('POPOON_ERROR_ALL', 31);
/**
* Default error-reporting level
* At the moment = POPOON_ERROR_ALL
* @const POPOON_ERROR_NONE
* @access public
*/
define('POPOON_ERROR_DEFAULT', POPOON_ERROR_ALL);

$GLOBALS['_POPOON_errorLevel']= POPOON_ERROR_DEFAULT;


/**
* Class for doing everything in popoon :)
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: popoon.php 4282 2005-05-18 05:52:27Z chregu $
* @package  popoon
*/
class popoon {

	/**
	* The Directory, where the cached sitemaps should be saved
	*
	* @var string
	*/
	private $cacheDir = "./tmp/";

	/**
	* Error Level

	* It's a static variable so it used in every instance of popoon
	*
	* @var int
	*/
	private $errorLevel = POPOON_ERROR_DEFAULT;

	/**
    * Constructor
    *
	* If $sitemapFile (or more) is provided, the method run() is immediately called.
	*
    * @param string $sitemapFile the location of sitemap.xml, can be relativ
	* @param string $uri the uri of the call, optional (takes _SERVER["REQUEST_URI"] then)
	* @param array $options class variable options
	* @see run()
    * @access public
	*/
    public function __construct ($sitemapFile = null, $uri = null,  $options = NULL) {
        //clean uri (remove //) 
        $uri = htmlspecialchars(preg_replace("#/{2,}#","/",$uri));
        if ($options == NULL) {
            $options = popoon_classes_config::getInstance();
        }
        if ( $options->doOutputCache()) 
        {
            $oc = new popoon_sitemap_outputcache($options);
            $oc->start($uri);
        } 
        if ($sitemapFile !== null)
        {
            $sm = $this->run(realpath($sitemapFile),$uri,$options);
        }

        $this->cacheDir = dirname(dirname(dirname(__FILE__))) . '/tmp/';

        if ( $options->doOutputCacheSave($sm))
        {
            $oc->end($sm, $options->outputCacheExpire);
        }
	}

	/**
	* runs the sitemap
	*
	* More docu, please
	*
    * @param string $sitemapFile the location of sitemap.xml, can be relativ
	* @param string $uri the uri of the call, optional (takes _SERVER["REQUEST_URI"] then)
	* @param array $options class variable options
	* @see popoon(), setOptions
    * @access public
	*/
	function run($sitemapFile, $uri = null, $options = array()) {
		$this->setOptions($options);
		return  new popoon_sitemap($sitemapFile,$uri,$options);

	}


	/**
	* Sets class options
	*
	* Sets options for a class. the array has to be an associative
	* array, where the key is a class-variable and the value is the
	* value. With this method, you can't set class-variable, which
	* were not defined before
	*
	* @param array $options associative array
    * @access public
	*/
	function setOptions($options) {
		foreach($options as $key => $value) {
			if (isset($this->$key)) {
				$this->$key = $value;
			}
		}
	}

	/**
	* Strips the starting xml processing instruction from a xml file
	*
	* This is needed, if we have short_open_tag = On, because then php
	* chokes on it. We don't need it if it's = Off.
	*
	* @param string $xml The XML Document as a string
	* @access public
	* @return string The XML without the PI
	*/
	function stripXmlPI($xml)
	{
		return preg_replace("#^<\?xml[^>]*\?>\s*#","",$xml);;
	}

	/**
	* Save a file to the filesystem
	*
	* @param string $content The content of the file
	* @param string $filename The filename
	* @return bool
	* @access public
	*/
	function saveFile($content,$filename)
	{
		$fd = fopen($filename,"w");
		if (!$fd) {
			popoon::raiseError("Could not save $filename. (In ". __FILE__ .":".__LINE__.")",POPOON_ERROR_FATAL);
			return false;
		}
		fwrite($fd,$content);
		fclose($fd);
		return True;
	}


    /**
    * Trigger a PEAR error
    *
    * To improve performances, the PEAR.php file is included dynamically.
    * The file is only included when an error is triggered. So, in most
    * cases, the file isn't included and perfs are much better.
    *
	* After raising a PEAR_Error we call popoon::handleError(), which does
	*  the main popoon  errorhandling (printing and dying), if PEAR_Error didn't take
	*  any action before
	*
    * @param string $msg error message
    * @param int $code error code
	* @param string $file filename, where the error occured.
	* @param int  $line linenumber, where the error occured.
    * @param int $mode PEAR error mode, should not be used, since this is up to the user...
    * @access public
	* @return object PEAR_Error Object
    */
    function raiseError($msg, $code = null, $file = null, $line = null, $mode = null)
    {
        include_once('PEAR.php');
		if ($file !== null) {
	        $err = PEAR::raiseError(sprintf("%s [%s on line %d].", $msg, $file, $line), $code, $mode );
		} else {
		   $err = PEAR::raiseError(sprintf("%s", $msg), $code, $mode );
		}
		popoon::handleError($err);
		return $err;
    }

    /**
    * Tell whether a value is a PEAR error.
	*
	* This method can be used, so we don't have to load
	* PEAR.php just for checking this...
	*
    *
    * @param   mixed $data   the value to test
    * @access  public
    * @return  bool    true if parameter is an error
    */
    function isError($data)
	{
         return (bool)(is_object($data) &&
                      (get_class($data) == 'PEAR_Error' ||
                      is_subclass_of($data, 'PEAR_Error')));
    }

	/**
	* Handles Popoon errors
	*
	* This method does the error-handling of popoon
	*
	* popoon's error handling is based on PEAR's error handling.
	*  A popoon error is generated with popoon::raiseError(..), which
	*  on the other hand raises a PEAR_Error with PEAR::raiseError(..)
	*  This way, the user can use the PEAR errorhandling which is triggered
	*  at PEAR::raiseError(..). Therefore, if the user sets the
	*  PEAR::setErrorHandling for example to PEAR_ERROR_CALLBACK, he can
	*  intercept popoon-errors before we catch it here.
	*
	* If the PEAR::errorLevel is set to something different
	*  than the default value (PEAR_ERROR_RETURN) nothing won't
	*  happen here at all. Otherwise it will print the error messages
	*  and die if it's FATAL
	*
	* popoon::raiseError will automatically call this method, and therefore
	*  one doesn't have to check for errors within popoon.
	*
	* Performance in ErrorHandling is only important in isError, since we maybe
	*  use this function more often. raiseError and handleError will only be
	*  used at errors and therefore no issue for performance. Maybe one could
	*  export this few popoon::error-functions to an external class.
	*
	* @param object PEAR_Error $data
	* @see raiseError
	* @access public
	*/

	function handleError($data)
	{
		if (popoon::isError($data) && $data->getMode() == PEAR_ERROR_RETURN)
		{
            if (isset($this) && isset($this->errorLevel)) {
                $errorLevel = $this->errorLevel;
            // Global error handler
            } elseif (isset($GLOBALS['_POPOON_errorLevel'])) {
				$errorLevel = $GLOBALS['_POPOON_errorLevel'];
            }

			// if code is FATAL and mode is PEAR_ERROR_RETURN, die...
			// if mode is not PEAR_ERROR_RETURN, then the user likes to have the
			//  error mode handled differently and we don't die here
			 if ($data->getCode() & POPOON_ERROR_FATAL & $errorLevel)
			 {
				print("<b>Fatal Error (Popoon)</b>: ".$data->getMessage()."<br />");
				if (function_exists("debug_backrace"))
				{
				$trace = debug_backtrace();
				print "Backtrace: <br/>";

				foreach($trace as $ent)
				{
					if(isset($ent['file']))
						echo $ent['file'].':';
					if(isset($ent['function']))
					{
						echo $ent['function'].'(';
						if(isset($ent['args']))
						{
							$args='';
							foreach($ent['args'] as $arg)
							{
								$args.=$arg.',';
							}
							echo rtrim($args,',');
						}
						echo ') ';
					}
					if(isset($ent['line']))
						echo 'at line '.$ent['line'].' ';
					if(isset($ent['file']))
						echo 'in '.$ent['file']; echo "\n";
				}
				}
				die();
			 }
			 else if ($data->getCode() & POPOON_ERROR_WARNING & $errorLevel)
			 {
				print "<b>Warning (Popoon)</b>: ".$data->getMessage()."<br />";
			 }
			 else if ($data->getCode() & POPOON_ERROR_NOTICE & $errorLevel)
			 {
				print "<b>Notice (Popoon)</b>: ".$data->getMessage() ."<br />";
			 }

		}
	}

	/**
	* sets errorLevel for the popoon class
	*
	* It works like the php-error handling. You have to bit-operate the different
	*  popoon error-levels. At the moment these are
	*
	* POPOON_ERROR_NOTICE, POPOON_ERROR_WARNING, POPOON_ERROR_FATAL.
	*
	* You should not turn off WARNING and FATAL
	*
	* Default is POPOON_ERROR_DEFAULT = 7
	*
	* @param int $level	the wished level
    * @access public
	* @return int old errorLevel;
	*/
	function setErrorLevel($level)
	{
        if (isset($this)) {
			$oldlevel = $this->errorLevel;
            $this->errorLevel = $level;

        } else {
			$oldlevel = $GLOBALS['_POPOON_errorLevel'];
            $GLOBALS['_POPOON_errorLevel'] = $level;
        }
		return $oldlevel;
	}


}

function myErrorHandler ($errno, $errstr, $errfile, $errline) {
	echo "$errno: $errstr in $errfile at line $errline\n";
	if (!function_exists("debug_backtrace")) {
        return;
    }

    echo "Backtrace\n";
    $trace = debug_backtrace();
    foreach ($trace as $ent) {
        if (isset($ent['file'])) {
            echo $ent['file'].':';
        }
		if (isset($ent['function'])) {
            echo $ent['function'].'(';
            if (isset($ent['args'])) {
			    $args='';
                foreach($ent['args'] as $arg) {
                    $args.=$arg.',';
                }
	            echo rtrim($args,',');
		    }
			echo ') ';
        }
	    if (isset($ent['line'])) {
            echo 'at line '.$ent['line'].' ';
        }
	    if (isset($ent['file'])) {
            echo 'in '.$ent['file'];
        }
        echo "<hr>";
    }
}
