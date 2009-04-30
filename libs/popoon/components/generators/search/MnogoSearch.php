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

/**	
 * Class To Handle Mnogosearch-engine 
 * ( http://search.mnogo.ru/ ) 
 * 
 * @author: silvan zurbruegg <silvan@bitflux.ch>
 * @version: $Id: MnogoSearch.php 3896 2005-04-11 14:48:59Z bitflux $
 * @package  popoon
 */

Class MnogoSearch  {

	protected $dsn;
	protected $query;
    protected $apiversion;
	protected $DBAddr; 
	protected $DBMode;
	protected $MRes;
	protected $Result;
	protected $ResFound;
	protected $ResTime;
	protected $AgentParams = array();
	protected $ResField = array();
	protected $_result;
	protected $currPage;
	protected $maxPages;							
    protected $defSearchMode='single';	
    /**
	* Constructor
	* Checks whether Mnogosearch extension is 
	* available and calls msAll	ocate() to allocate a mnogo-agent
	*
	* @param array $params Parameters from param type 'module' in sitemap
	* @access public
	*/
	function MnogoSearch($params=NULL) {
		if(!extension_loaded('mnogosearch')) {
            Popoon::raiseError('Mnogosearch extension not available!');
			
			/*
 	         * here could be checked 
 	         * whether mnogosearch
 	         * is includeable or dl()'- able
 	         */
			
			
		} else {
			$this->set_apiversion();
			$this->set_ResField();
		    
            if($this->msAllocate($params['dsn'])) {

		$this->set_AgentParam(UDM_PARAM_LOCAL_CHARSET,'UTF-8');
		Udm_Set_Agent_Param($this->MRes,UDM_PARAM_BROWSER_CHARSET,'UTF-8');
		Udm_Set_Agent_Param($this->MRes,UDM_PARAM_CHARSET,'UTF-8');
		Udm_Set_Agent_Param($this->MRes,UDM_PARAM_DETECT_CLONES,UDM_DISABLED);				
		Udm_Set_Agent_Param($this->MRes,UDM_PARAM_MIN_WORD_LEN,3);
/*		 Udm_Set_Agent_Param($this->MRes,UDM_PARAM_HLBEG,"<i>");
		 Udm_Set_Agent_Param($this->MRes,UDM_PARAM_HLEND,"</i>");
*/
				$this->set_MaxResults($params['NumRows']);
				$this->set_CurrPage($params['CurrPage']);
			    	
                $searchmode = (isset($params['SearchMode'])) ? $params['SearchMode'] : 'single';
                $this->set_SearchMode($searchmode); 
			
                
                $weightfact = (isset($params['WeightFactor'])) ? $params['WeightFactor'] : 11;
				$this->set_WeightFactor($weightfact);
			}
		}
	}
	
	
	/**
	* Interface function to Query Search-module
	*
	* Calls _eval_query() to evaluate string and _query()
	* to actually execute the query	
	* @param string $query Querystring
	* @return mixed
	* @access public
	*/
	function doQuery($query=NULL) {
		if ($query!=NULL && is_string($query)) {
			$this->query = $this->_eval_query($query);
		} 
		
		if (!empty($this->query)) {
			if(!$this->_query($this->query)) {
				return array('error'=>'Mnogosearch: Query failed!');
			} else {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}
	
	
	/**
	* Interface function to get Results
	* calls _result() to format results $this->_result
	* @return array $Result
	* @access private
	*/
	function getResult() { 
		$this->_result();
		return $this->Result;
	}
	
	
	/**
	* Parse dsn-string 
	* Extract Address (username:pass@host/dbname)
	* from dbmode (?dbmode=) and set Vars
	* @return boolean true|false
	* @access private
	*/ 	
	function _parse_dsn() {
        if(preg_match("/(.*\/)(\?dbmode=(.*))/i",$this->dsn,$match)) {
            if($match[1]) {
				$this->set_DBAddr($match[1]);
			}
			
			if(ereg("(single|multi|crc|crc-multi)",$match[3])) {
				$this->set_DBMode($match[3]);
			}
		} 
				
		return TRUE;
	}	
	
	
	/**
	* Designated to check malicious queries
	* Does nothing but returning the query
	* @param string $query
	* @return string $query
	* @access private
	*/
	function _eval_query($query) {
		
		/*
		 * Here can be done some query-checking
		 * and convertion of boolean search operators 
		 */
		return urldecode($query);
	}

	
	/**
	* Fetches Result-fields for each Result
	* Result-fields are defined in (array) ResField
	* and format Result-array
	* @return array Result
	* @access private
	*/
	function _result() {
		$this->Result = array();
		if (is_resource($this->_result) && $this->ResFound > 0) {
			
			for($i=0; $i<$this->ResRows; $i++) {
				$row = array();
				foreach($this->ResField as $field) {
					if($fval = udm_get_res_field($this->_result,$i,$field[1])) {
						if ( $field[0] == 'text' || $field[0] == 'title') {
							$fval = $this->ParseDocText($fval);
						}
						$row[$field[0]] = $fval;
					}
				}
				
				$row['nr'] = ($i+1);
				$row['a_view']=preg_replace("/\?*".session_name()."=[a-zA-Z0-9](.*)/","",$row['url']);
				if (isset($row['mod'])) {
					$row['date'] = date("d.m.y",$row['mod']);
				}
                if(!isset($row['title']) && !empty($row['url'])) {
                    $pInfo = pathinfo($row['url']);
				    if(!empty($pInfo['basename'])) {
                        $row['title'] = '::'.$pInfo['basename'];
                    }
                }

				array_push($this->Result,$row);	
			}	
		}
		return $this->Result;
	}
	
	
	/** 
	* Queries Mnogosearch with $query and
	* call _set_ResParam() to set Vars according
	* to Result
	* @param string $query Querystring
	* @return mixed
	* @access private
	*/
	function _query($query) {
		$this->query = $this->_eval_query($query);
        if (!empty($this->query) && is_resource($this->MRes)) {
	Udm_Set_Agent_Param($this->MRes,UDM_PARAM_QUERY,$this->query);
            if($this->_result = udm_find($this->MRes,$this->query)) {
				$this->_set_ResParam('ResRows',UDM_PARAM_NUM_ROWS);
				$this->_set_ResParam('ResFound',UDM_PARAM_FOUND);
				$this->_set_ResParam('ResTime',UDM_PARAM_SEARCHTIME);
				$this->_set_ResParam('ResFDoc',UDM_PARAM_FIRST_DOC);
				$this->_set_ResParam('ResLDoc',UDM_PARAM_LAST_DOC);
                $this->maxPages = ceil($this->ResFound / $this->NumRows);
			return TRUE;
			}
            
            return array("error"=>"mnogosearch:" . udm_Error($this->MRes));;
		
        } else {

			return FALSE;
		}
	}
	
	
	/**
	* Allocates Seach-agent 
	* Different handling of udm_alloc_client() due to different versions
	* @parameter string $dsn 
	* @return object MRes
	* @access public
	*/
	function msAllocate($dsn=NULL) {
		if ($dsn == NULL) {
			return array("error" => 'No DSN for mnogosearch provided.');
		} else if (!extension_loaded('mnogosearch')) {
            return array("error" => 'mnogosearch extension is not loaded/installed.');
        } else {
            $this->set_dsn($dsn);
            $this->_parse_dsn($this->dsn);
		}
		// MnogoSearch <= 3.1.2 doesn't
		// accepts dbmode in dsn
		if ($this->apiversion <= 30130) {
			if(!$this->MRes = udm_alloc_agent($this->DBAddr,$this->DBMode)) {
				return array("error" => 'mnogosearch could not allocate Agent');
			}
		} else {
            if(!$this->MRes = udm_alloc_agent_array(array($this->DBAddr."?dbmode=".$this->DBMode))) {
				
                return array("error" => 'mnogosearch could not allocate Agent');
			}
		}
		return $this->MRes;
	} 	
	
	
	/**
	* Define how many Rows of Results to display
	* defined in $params of constructor
	* @param int $results 
	* @return bool true
	* @access public
	*/
	function set_MaxResults($results) {
		$this->NumRows = $results;
        $this->set_AgentParam(UDM_PARAM_PAGE_SIZE,$results);
		return TRUE;
	}
	
	
	/**
	* Define which Page of Results to display
	* defined in $params of constructor
	* @param int $page 
	* @return bool true
	* @access public
	*/
	function set_CurrPage($page) {
		$this->currPage = $page;
        $this->set_AgentParam(UDM_PARAM_PAGE_NUM,$page);
		return TRUE;
	}
	
	
	/**
	* Defines Searchmode (all|any|bool|phrase)
	* defined in $params of constructor
	* @param string $mode
	* @access public
	*/
	function set_SearchMode($mode) {
		if ($mode=='all' || $mode=='') {
			$mode = (int) UDM_MODE_ALL;
		} elseif ($mode=='any') {
			$mode = (int) UDM_MODE_ANY;
		} elseif ($mode=='bool') {
			$mode = (int) UDM_MODE_BOOL;
		} elseif ($mode=='phrase') {
			$mode = (int) UDM_MODE_PHRASE;
		}
        $this->set_AgentParam(UDM_PARAM_SEARCH_MODE,$mode);
		$this->DBMode = $mode;
        return TRUE;
	}
	
	
	/**
	* Setter func for var dsn
	* @param string $dsn
	* @return bool true
	* @access public
	*/
	function set_dsn($dsn=NULL) {
		if(is_string($dsn)&&$dsn != NULL) {
			$this->dsn = $dsn;
		} elseif (is_array($dsn)) {
            $mode = (isset($dsn['searchmode']) && !empty($dsn['searchmode'])) ? $dsn['searchmode']:$this->defSearchMode;
            $d = sprintf("%s://%s:%s@%s/%s/?dbmode=%s", $dsn['phptype'], $dsn['username'], $dsn['password'], $dsn['hostspec'], $dsn['database'], $mode);
            if ($d) {
                $this->dsn = $d;
            } 
        }
		
		return TRUE;
	}
	
	
	/**
	* Setter func for query
	* @param string @query
	* @return bool true;
	* @access public
	*/
	function set_query($query) {
		if(is_string($query)) {
			$this->query = $query;
			return TRUE;
		}
	}
	
	
	/**
	* Setter func for Db-address part of dsn
	* @param string $dbaddress
	* @return bool true
	* @access public
	*/
	function set_DBAddr($dbaddr) {
		if(is_string($dbaddr)) {
			$this->DBAddr = $dbaddr;		
		}
		return TRUE;
 	}
	
	
	/**
	* Setter func for dbmode-part of dsn
	* @param string $dbmode
	* @return bool true
	* @access public
	*/
	function set_DBMode($dbmode) {
		if(is_string($dbmode)) {
			$this->DBMode = $dbmode;
		}
		return TRUE;
	}
	
	
	/**
	* Setter func for Weight Factor
	* call set_AgentParam() to apply $factor on 
	* Mnogo-ressource
	* @param int $factor
	* @return bool true
	* @access public
	*/
	function set_WeightFactor($factor) {
		$this->set_AgentParam(UDM_PARAM_WEIGHT_FACTOR,intval($factor));
		return TRUE;
	}
	
	
	/**
	* Define fields to be fetched from results
	* array ResField consists of 
	* [0]=>(string)'name',[1]=>(numeric)UDM_FIELD_NAME
	* @param array $opts override default
	* @return bool true
	* @access public
	*/
	function set_ResField($opts=NULL) {
		if(is_array($opts)) {
			$this->ResField = $opts;
		} else { 
			$this->ResField = array(array('count',UDM_FIELD_ORDER),
									array('url',UDM_FIELD_URL),	
									array('content',UDM_FIELD_CONTENT),
									array('title',UDM_FIELD_TITLE),
									array('text',UDM_FIELD_TEXT),
									array('size',UDM_FIELD_SIZE),
									array('rating',UDM_FIELD_RATING),
									array('mod',UDM_FIELD_MODIFIED));	
		}
		
		return TRUE;
	}
	
	
	/**
	* apply search parameters to Mnogosearch ressource
	* @param int $key 
	* @param mixed $val
	* @return bool true
	* @access public
	*/
	function set_AgentParam($key,$val) {
		if(is_numeric($key) && !empty($val)) {
			if(is_resource($this->MRes)) {
				udm_set_agent_param($this->MRes,$key,$val);
			}
		}
		
		return TRUE;
	}
	
	
	/**
	* Get Mnogosearch api-version and set var apiversion
	* @return bool true
	* @access public
	*/
	function set_apiversion() {
		$this->apiversion = udm_api_version();
		return TRUE;		
	}
	
	
	/**
	* Get Result Parameters from Query
	* and set according Var
	* @param string $var Name of Classvar to set
	* @param param $param 
	* @return bool true
	* @access private
	*/
	function _set_ResParam($var,$param) {
		if(is_resource($this->_result)) {
			$this->{$var} = udm_get_res_param($this->_result,$param);
		}

		
		return TRUE;
	}
    
    public function getResultParams() {
        $resprm = array('found'     => $this->ResFound,
                        'currpage'  => $this->currPage,
                        'nextpage'  => $this->getPrevNext('next'),
                        'prevpage'  => $this->getPrevNext('prev'),
                        'maxpages'  => $this->maxPages, 
                        'firstdoc'  => $this->ResFDoc,
                        'lastdoc'   => $this->ResLDoc
                        );
       
        return $resprm;
    }
    
    public function getResultFound() {
        return $this->ResFound;
    }

    public function getPrevNext($what) {
        $maxp = (($this->maxPages -1) >=0)?($this->maxPages -1):0;

        if($what == 'next') {
            if(($this->currPage +1) <= $maxp) {
                $page = $this->currPage +1;
            } else {
                $page = $this->maxPages -1;
            }
        } elseif($what == 'prev') {
        
            if(($this->currPage -1) >= 0) {
                $page = $this->currPage -1;
            } else {
                $page = $this->currPage;
            }
        }
        
        return $page;
    }

function ParseDocText($str){
       
    	$str = str_replace("\2","",$str);
    	$str = str_replace("\3","",$str);
        $str = html_entity_decode($str, ENT_COMPAT, "UTF-8");
        return $str;
}

}


?>
