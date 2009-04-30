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
// $Id: peardb.php 1255 2004-04-22 17:15:25Z chregu $
/**
* scheme for a global peardb object...
*
* With this, you can use a peardb:// object as attribut or parameter
* 
* Example:
*
* <map:components>
*	<map:schemes>
*		<map:scheme name="config" path="BX_PROJECT_DIR:///inc/config.inc.php" global="yes" variable="BX_config"/>
*		<map:scheme name="peardb" subname="default" dns="config://dsn" />		
*	</map:schemes>
* </map:components
* ...
* 				<map:generate type="bitflux" 
*		        src="../structure/simple.xml" 
*		        defaultLang="de" 
*		        treeStartID="13"
*		        dsn="peardb://default" 
*				/>
*
*  and then the attribute "dsn" will contain the peardb object.
*  The neat thing about that is, that the peardb object will only be created once, no matter
*  how many times you call it and it's only created, if you call it.
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: peardb.php 1255 2004-04-22 17:15:25Z chregu $
* @package  popoon
* @module   schemes_peardb
*/

function &scheme_peardb($value)
{
	$dsn = $GLOBALS["_POPOON_globalContainer"]->schemes["peardb"][$value]["dsn"];
	if (!isset($GLOBALS["_POPOON_globalContainer"]->DB_Handles[$dsn]))
	{
		include_once("DB.php");
		$GLOBALS["_POPOON_globalContainer"]->DB_Handles[$dsn] = DB::Connect($dsn);
		if (DB::isError($GLOBALS["_POPOON_globalContainer"]->DB_Handles[$dsn] ))
		{
			$_errorMessage = "could not connect to database in scheme peardb:// <br />\n".$GLOBALS["_POPOON_globalContainer"]->DB_Handles[$dsn]->getMessage();
			if (class_exists("popoon")) 
			{
				return popoon::raiseError($_errorMessage,POPOON_ERROR_FATAL);
			}
			else 
			{
				die($_errorMessage);
			}
		}
	}
	return $GLOBALS["_POPOON_globalContainer"]->DB_Handles[$dsn];;		
}
	
