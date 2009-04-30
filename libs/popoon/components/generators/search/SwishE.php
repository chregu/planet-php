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

/*
 * Class to handle the Swish-e searchengine 
 * ( http://www.swish-e.org ) applies to version 2.2.3 of Swish-e
 * 
 *
 * @author: silvan zurbruegg <silvan@bitflux.ch>
 * @version: $Id: SwishE.php 3692 2005-02-23 23:17:05Z silvan $
 * @package  popoon
 */
 
 
Class SwishE {
	 	
	// privates
	var $_indexfile="";
	var $_pipe=0;
	var $_numrows=0;
	var $_currpage=0;
	var $_mode="";
	var $_query="";
	var $_format="";
	var $_delim="|";
	var $_resp=array();
	var $_swish="";
	var $_swish_app="swish-e";
	var $_which_app="/usr/bin/which";
	
	/**
	* Holds key=Name of ResultField, value=Swishe's shortcut
	* @var array 
	*/
	var $_resv=array(	'count'=>'%c',
						'title'=>'%t',
						'text'=>'%d',
						'url'=>'%p',
						'size'=>'%l',
						'keywords'=>'<keywords>',
						'rating'=>'%r',
						'mod'=>'%D',
						'type'=>'');
				
	
	var $ResFound;
	var $ResTime;
	var $Result;
	
	
	/**
	* Constructor 
	* @params array $params
	*/
	function SwishE($params=NULL) {
		if (is_array($params)) {
			 
			$err = $this->set_swish($params['SwishPath']);
			if(is_array($err) && isset($err['error'])) {
				return $err;
			} else {
				$this->setter('_indexfile',$params['IndexFile'],'string');
				$this->setter('_numrows',$params['NumRows'],'int');
				$this->setter('_currpage',$params['CurrPage'],'int');
				$this->setter('_mode',$params['SearchMode'],'string');
				$this->setter('_resp',explode(',',$params['ResFields']),'array');
			}
		} else {
			return array('error'=>'No Parameters found for SwishE!');
		}
	}
	
	
	/**
	* Interface Function to query search module
	*
	* @param string $query Querystring
	* @return mixed
	* @access public
	*/
	function doQuery($query) {
		if (!empty($query) && is_string($query)) {
			$query = $this->_eval_query($query);
			if (!empty($query)) {
				$exec = $this->_prepare_query($query);
				if ($result = $this->_exec_query($exec)) {
					if (!preg_match("/^err: /",$result)) {
						$this->_parse_result($result);
						return TRUE;
					} else {
						return array('error'=>'Siwsh-e error occured');
					}
				} else {
					return array('error'=>'Could not query Swish-e');
				}
			}
		}
	}
	
	
	/** 
	* Interface Function to get formatted Result
	*
	* @return array $Result
	* @access public
	*/
	function getResult() {
		if(is_array($this->Result)) {
			return $this->Result;
		}
	}
 		

    public function getResultParams() {
        return array('found'    => $this->ResFound,
                     'time'     => $this->ResTime
                     );
    }
    
	/** 
	* General function to set Classvars
	* @param string $var name of variable
	* @param mixed $value value of variable
	* @param string $type Type of variable
	* @return bool true|false
	* @access public
	*/
	function setter($var,$value,$type=NULL) {
		if (!empty($var) && !empty($value)) {
			$type = ($type==NULL)?'string':$type;
			if ($type == "int") {
				$pfunc = "intval";
			} elseif ($type=="string") {
				$pfunc = "is_string";
			} elseif ($type=="numeric") {
				$pfunc = "is_numeric";
			} elseif ($type=="array") {
				$pfunc = "is_array";
			}
				
			eval("\$this->\$var = (\$pfunc(\$value))?\$value:NULL;");
			if($this->{$var} != NULL && isset($this->{$var})) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	} 
		
	
	/** 
	* Evaluate Query
	* Escape malicious elements and catch if there
	* are already boolean elements (and|or|not|near) or 
	* insert them if nescessary
	* @param string $query Querystring
	* @return string $query
	* @access private
	*/
	function _eval_query($query) {
		
		$query = escapeshellcmd($query);
		
		if (preg_match("/\(|\)|\| and | or | not | near /",$query,$matches)) {
			$this->setter('_mode','boolean','string');
		} else {
			if (!empty($this->_mode)) {
					
				$query = trim($query);
				
				switch ($this->_mode) {
					case "all":
						$query = preg_replace("/[[:space:]]/","/ AND /",$query);
					break;
					case "any":
						$query = preg_replace("/[[:space:]]/","/ OR /",$query);
					break;
					case "phrase":
						$query = "\"".$query."\"";
					break;
				}
			}		
		}
		
		return $query;
	}
		
	
	/**
	* Prepares Querystring 
	* Concat query to be piped to Swish-e
	* @param string $query
	* @return string $exec String which is executeable by Swish-e
	* @access private
	*/
	function _prepare_query($query) {
		
		$begin = ($this->_currpage * $this->_numrows)+1;
		$max = $this->_numrows;
		$d = $this->_delim;
		
		$exec = $this->_swish ." -f ".$this->_indexfile." ";
		$exec.= "-b ".$begin." -m ".$max . " -w '".$query . "' -x '";
		
		foreach ($this->_resp as $resp) {
			$exec.= $this->_resv[$resp].$d;
		}
		
		$exec = substr($exec,0,(strlen($exec)-1));
		$exec.= "\\n'";
		
		return $exec;
	}
		
	
	/**
	* Execute Query
	* Opens pipe to Swish-e and executes given query
	* @param string $exec
	* @return string $ret Output of query
	* @access private
	*/	
	function _exec_query($exec) {
		if(is_string($exec)) {
			if(($p = popen($exec,"r"))!=FALSE) {
				$ret = (string)"";
				while($p && !feof($p)) {
					$ret .= fgets($p,2048);
				}
				
				@pclose($p);
				return $ret;
				
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
	
	
	/**
	* Parse Result coming from Swish-e
	* @param string $result Output of Swish-e query
	* @return array $Result formatted array
	* @access private
	*/
	function _parse_result($result) {
	
		preg_match("/\# Number of hits: ([0-9]{1,})/i",$result,$match);
		$this->ResFound = intval($match[1]);
		preg_match("/\# Run time: ([0-9]{1,}.[0-9]{1,})/i",$result,$match);
		$this->ResTime = $match[1];
		
		$result = preg_replace("/\# (.*)\\n/","",$result);
		$result = explode("\n",$result);
		$this->Result = array();
		
		foreach ($result as $res) {
			if(strlen($res)>1) {
				$res = explode($this->_delim,$res);
				$row = array();
				foreach($this->_resp as $k=>$param) {
					$row[$param] = $res[$k];
				}
				
				array_push($this->Result,$row);
			}
		}
		
		return $this->Result;
	}	
	
	
	/** 
	* Sets Swish-e executeable
	* Detection of Swish-e location only for Linux
	* Windows needs to set it with $path
	* @param string $path Path to swish-e executeable
	* @return mixed
	* @access public
	*/
	function set_swish($path=NULL) {
		if (is_string($path) && $path!=NULL) {
				$this->_swish = $path;
		} else {
			if (preg_match("/Unix/i",$_SERVER['SERVER_SOFTWARE'])) {
				eval("\$swish = `\$this->_which_app \$this->_swish_app`;");
				if(!empty($swish)) {
				 	$this->_swish = trim($swish);
					return TRUE;
				} else {
					return array('error'=>'No '.$this->_swish_app.' found');
				}
			} else {
				return array('error'=>'Path to'.$this->_swish_app.' not set');
			}
		}
	}

}
