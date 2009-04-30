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
// $Id: common.php 1255 2004-04-22 17:15:25Z chregu $
/**
* this file contains a set of function which are used in
*    different classes/files.
*
* TODO:
*       Documentation, Error Checking, Examples
*
*    @author   Christian Stocker <chregu@bitflux.ch>
*    @version  $Id: common.php 1255 2004-04-22 17:15:25Z chregu $
*    @package  functions
*    @access   public
*/


class common {

    function getConfigClass ($configFile,$options=Null)
    {
        if (is_string($configFile) || is_array($configFile))
        {
         
            include_once("Config.php");
            if (is_array($configFile))
            {
                foreach ($configFile as $file) {
                    if (!file_exists($file)) {
                        common::raiseError("File $file does not exist", __LINE__,__FILE__,$file);
                    }
                }
            }
            elseif  (!file_exists($configFile) )
            {
                        common::raiseError("File $configFile does not exist", __LINE__,__FILE__,$file);
            }
            $config = new Config("xml");

            if ($options == Null)
            {
                $options= array(    "TakeContent"=>False,
                                    "MasterAttribute"=>False,
                                    "PrintMasterAttribute"=>False,
                                    "IncludeChildren"=>True,
                                    "KeyAttribute"=>"name"
                               );
            }

            $ret = $config->parseInput( $configFile,$options);

            return $config;


        }

        elseif ( get_class ($configFile) == "config") {
            return $configFile;

        }

        else {
            common::raiseError("$configFile is neither a string (filename) nor a config-class-object", __LINE__,__FILE__,$configFile);

        }
    }



    function raiseError(   $msg,$line,$file,$variable)
    {
//        include_once("functions/common.php");
        print "Bitflux CMS Error<br>";
        print "$msg<br>";
        print "File: $file<br>";
        print "Line: $line<br>";

        print "<hr>";
        debug::print_rp($variable);
        die;
    }
    function getDbFromConfig ($config,$path=Null) {
        $config = common::getConfigClass($config);
        return common::getDBFromDsn(common::getDsnFromConfig($config,$path));
    }

	/**
	* get peardb handle out of a string, if it's already a db object, return this
	*
	* it's advised to use use peardb:// scheme handler for using global peardb handlers
	*
	*
	* @param mixed $dsn dsn string or DB object
	* @access public
	* @returns object PEAR_DB 
	*/
    function getDbFromDsn  ($dsn) {
    
        if (is_string($dsn))
		{
        	//check if we have a handle already in the global pool.
            // if yes, return that, thereforee we don't have to create
            // a new one, which is quite expensive 
            if (isset($GLOBALS["_POPOON_globalContainer"]->DB_Handles[$dsn]))
            { 
            	return $GLOBALS["_POPOON_globalContainer"]->DB_Handles[$dsn];
			}
            
            include_once ("DB.php");

            $GLOBALS["_POPOON_globalContainer"]->DB_Handles[$dsn] = DB::Connect($dsn);
            $db = &$GLOBALS["_POPOON_globalContainer"]->DB_Handles[$dsn];
            if (DB::isError($db))
            {

    			return common::raiseError("could not connect to database: <br />\n".$GLOBALS["_POPOON_globalContainer"]->DB_Handles[$dsn]->getMessage(),__FILE__,__LINE__,null);				
            }
            
        }

        elseif (is_object($dsn) && DB::isError($dsn))
        {
   			return popoon::raiseError("could not connect to database:<br />\n".$GLOBALS["_POPOON_globalContainer"]->DB_Handles[$dsn]->getMessage(),POPOON_ERROR_FATAL);
        }

        // if parent class is db_common, then it's already a connected identifier
        elseif (get_parent_class($dsn) == "db_common")
        {
            $db = $dsn;
        }
        return $db;
    }
    
    function getDsnFromConfig ($config,$path = Null )
    {
        $config = common::getConfigClass($config);
        if (! $path ) { $path = "/ibaconfig/db";}
        $db = $config->getValues( $path);
        return  $db[dsn];
    } //end func getDsn
    
    function get_xml($xml,$instring = "    ") {
            $xml = preg_replace("/(\>)\n/","$1",$xml);
            $xml = preg_replace("/\>\s*\</",">\n<",$xml);

            $axml = explode("\n",$xml);

            $indent=-1;
            $xmls = "";
            foreach ($axml as $key => $value) {

                if (preg_match("/<[^\/{1}]/",$value)) {
                    $indent++;
                }
            if ($indent < 0)
                $indent = 0;
                $xmls .= str_repeat($instring,$indent);
                if (preg_match("/\<\//",$value) || preg_match("/\/\>/",$value)|| preg_match("/-->/",$value)) {
                    $indent--;
                }
                $xmls .= trim($value)."\n";
            }
           return $xmls;
    }

}


//haha, aus dem genialen buch "PHP de Luxe"
function handle_pear_error ($error_obj) {
  if (get_class($error_obj) == "db_error")
  {
    print "Datenbank-Fehler:<br>\n";
  }
  else
  {
    print "Sonstiger PEAR-Fehler:<br>\n";
  }
  die ($error_obj->getMessage()."\n<br>".$error_obj->getDebugInfo());
}


