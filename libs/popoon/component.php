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
// $Id: component.php 1495 2004-06-01 08:35:03Z chregu $

/**
* Documentation is missing at the moment...
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: component.php 1495 2004-06-01 08:35:03Z chregu $
* @package  popoon
*/

abstract class popoon_component {

	private $debug = False;
    
    private $hasSaxInput = False;
    private $hasSaxOutput = False;    
    private $hasDomInput = False;    
    private $hasDomOutput = False;        
    private $hasXmlStringInput = False;        
    private $hasXmlStringOutput = False;            
	private $params = array();    

	protected function __construct($sitemap)
    {
		$this->sitemap = $sitemap;
    }

	/**
    * Initiator, called after construction of object
    *
    *  This method will be called in the start element with the attributes from this element
    *
    *  @param $attribs array	associative array with element attributes
	*/
	public function init($attribs)
    {
    	$this->attribs = $attribs;

		/*override class default vars from above with attribute from sitemap.xml */        
		foreach($this->attribs as $key => $value)
        {
            if (isset ($this->$key))
            {
				$_attr = $this->getAttrib("$key");
				if ($_attr !== null) {
					$this->$key = $_attr;
				}
			}
		}

		/*override class default vars from above with default-parameters (= with no type-attribute) from sitemap.xml */        
		foreach($this->getParameter("default") as $key => $value)
        {
            if (isset ($this->$key))
            {
				$_attr = $value;
				if ($_attr !== null) {
					$this->$key = $value;
				}
			}
		}

        //if we should debug
        $debug = $this->getAttrib("debug");
        if (!is_null($debug) && $debug!="no"  )
        {
        	$this->debug = True;
            $GLOBALS["_POPOON_globalContainer"]->debugOutput[] = "---";
            $p = get_parent_class($this);
            while ($pnew = get_parent_class($p))
            {
            	if ($pnew == "component") { break;}
                $p = $pnew;
			}          
            $GLOBALS["_POPOON_globalContainer"]->debugOutput[] =$p.": ".get_class($this);

            foreach ($this->attribs as $key => $value)
            {
            	$GLOBALS["_POPOON_globalContainer"]->debugOutput[] = "$key => $value";
            }
			$GLOBALS["_POPOON_globalContainer"]->debugOutput[] = "---";
		}
    }

    protected function getAttrib($attribute, $doNotTranslate = array()) {
	    if (isset($this->attribs["$attribute"]))
        {
				if (! isset($this->sitemap)) {
					die("The class ".get_class($this). " has no sitemap integrated");
				}
			

//				return $this->attribs[$attribute] = $this->sitemap->translateScheme($this->attribs["$attribute"],$doNotTranslate );
				return $this->sitemap->translateScheme($this->attribs["$attribute"],$doNotTranslate );
		}
        else
        {
        	return Null;
		}
	}
    
	public function setParameter($type,$key,$value, $default = NULL) 
	{
		if (!isset($this->params[$type]))
		{
			$this->params[$type] = array();
		}
        if( !$value && $default !== NULL ) {
            $this->params[$type][$key] = $default;
        } else {
            $this->params[$type][$key] = $value;
        }
	}
		
    public function clearParameters() {
        $this->params = array();
    }
	protected function getParameterAll() {
			return $this->params;
	}
	
	protected function getParameterDefault($key = false) {
		return $this->getParameter("default",$key);
	}
	
	protected function getParameter($type = "default",$key = false) {

		if ($key) 
		{
			if (isset($this->params[$type][$key])) 
			{
				return $this->sitemap->translateScheme($this->params[$type][$key]);
			}
			else
			{
				return null;
			}
		}
		else
		{		
			if (isset($this->params[$type])) {
				$retarr = array();
				foreach ($this->params[$type] as $value => $key) 
				{
					$retarr[$this->sitemap->translateScheme($value)] = $this->sitemap->translateScheme($key);
				}
				return $retarr;
			} 
			else 
			{
				return array();
			}
		}
	}
	

    /**
    * Adds an entry to the debug Output
    *  if $this->debug is true
    *
    * @param  mixed  content  text to be added
    */

    protected function printDebug($content)
    {
		if ($this->debug)
        {
			$GLOBALS["_POPOON_globalContainer"]->debugOutput[] = $content;
    	}
    }
    /**
    * Adds an decomposed Array to the debug Output
    *  if $this->debug is true
    *
    * @param  array array Array to be added
    * @param  string description line to be added before array
    * @access private
    */    
    protected function printDebugArray($array,$description = "")
    {
		if ($this->debug)
        {
			$GLOBALS["_POPOON_globalContainer"]->debugOutput[] = $description;
            if (is_array($array))
            {
        	foreach($array as $key => $value)
            {
				$GLOBALS["_POPOON_globalContainer"]->debugOutput[] = "$key => $value";
			}
            }
    	}
    }
    
  
    /* CACHING STUFF */
    /*
       Hannes' comment:
       Here come the default methods. generateKeyDefault() is sort of generic, while
       the other two methods are valid but for components interacting with filesystem.
       It seemed to me that in putting them here I came away with the least amount of
       duplicate code.
       Correct me if this was a bad choice.
    */      
    
    /**
     * Generate unique key from an array and a string (seen abstractly)
     *
     * @param  array  $attribs Array of component attributes
     * @param  string hash key of previous component in pipeline
     * @return string md5 hash key of the mashed parameters
     * @author Hannes Gassert <hannes.gassert@unifr.ch>
     * @see componentCache
     */
    public function generateKeyDefault($attribs, $keybefore){
        $attribs[] = $keybefore;
        return(md5(serialize($attribs)));
    }

    /**
     * Generate validityObject
     *
     * This is common to all file-reading components. As they're probably largest in number,
     * they are allowed to be the default methods.
     *
     * @see  checkvalidity
     * @return  array  $validityObject contains the components attributes plus file modification time and time of last access.
     */
    public function generateValidityDefault(){
        $validityObject = $this->attribs;
        if(isset($validityObject['src'])){
            $validityObject['filemtime'] = filemtime($this->attribs['src']);
            $validityObject['fileatime'] = fileatime($this->attribs['src']);
        }
        return($validityObject);
    }

    /**
     * Check validity of a validityObject from cache: NOT REALLY DONE YET!!
     *
     * It's the same thing as with all validity checkers: there are many more ways to determine
     * cache validity. things like "access + 4 days" (which would also involve sending appropriate http headers)
     * are not implemented at all. but they will be! .)
     * At the very moment, this method checks only if the "live" content is not more recent than the cached one.
     * 
     * @return  bool  true if the validityObject indicates that the cached version can be used, false otherwise.
     * @param   object  validityObject
     */
    public function checkValidityDefault($validityObject){
        return(isset($validityObject['src'])       &&
               isset($validityObject['filemtime']) &&
               file_exists($validityObject['src']) &&
               ($validityObject['filemtime'] >= filemtime($validityObject['src'])));         //! no good ! they're always euqal..
    }


}



?>
