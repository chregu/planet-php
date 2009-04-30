<?php

/** Class for storing Popoon Config parameter 
 * 
 *  Parameter about Caching et al. are also stored here
 *
 * @author   Christian Stocker <chregu@bitflux.ch>
 * @version  $Id: config.php 2957 2004-11-11 06:49:17Z chregu $
 * @example classes/config_cache.php
 * @package  popoon
 */
 class popoon_classes_config implements ArrayAccess {
     
     /**
      * the class instance
      *
      * this is a singleton class, therefore we save the instance
      * in this static var
      * @static popoon_classes_config
      */
     static $instance = null;
     
     /**
      * Values set with setPopoonValue
      * 
      * @see getPopoonValue
      * @see setPopoonValue
      * @var array
      */
     private $popoonValues = array();
     
     /**
      * Values set with setValue
      * 
      * @see getValue
      * @see setValue
      * @var array
      */
     private $values = array();
     
     public $internalRequest = false;
     public $popoonmap = array();
     
     private $outputCacheCallback = null;
     /**
      * the cache container according to PEAR::Cache
      * @var string
      */
     public $cacheContainer = 'file';
     
     /**
      * cache parameter for PEAR::Cache
      * 
      * we can't set them here to a default value, because we need BX_TEMP_DIR and that
      * doesn't work. It's set in the constructor
      * @see __construct
      * @var array
      */
     public $cacheParams  = null;
     
     /**
      * The expire time of the output cache
      * 
      * After this time, the cache entry gets regenerated
      * @var int in seconds.
      */
     public $outputCacheExpire = 3600;
     
     /**
      * if the output should be saved
      *
      * if this is false, then the output is not cached
      * and generated the next time
      * useful, if you want turn of outputcaching within the sitemap
      *
      * @var bool
      */
     public $outputCacheSave = false;
     
     /** 
      * if static file cache should be used
      *
      *  If that is true, then files served with reader_resource
      *  are always cached and checked against local modification.
      *
      *  The disadvantage is, that we have to load the output cache classes
      *   on every hit, even if no static pages are served...
      *
      * Additionally you get 304 support for staticFiles.
      * 
      * It's save to turn it on, but your mileage may vary with speed improvements
      *
      * *****
      * FIXME: It's turned off by default until we can check it with bytecode caches
      *  The speed improvements are neglectable right now... and 304 support is built-in
      *  in reader_resource
      * *****
      */
     public $staticFileCache = false;
     
     /**
      * The constructor
      * 
      * As this is a singleton class, we don't allow the class to be called
      * directly. use getInstance
      *
      * @see getInstance
      */
     private function __construct() {
         if (defined('BX_TEMP_DIR')) {
             $cd =  BX_TEMP_DIR . '/cache';
         } else {
             $cd = './tmp';
         }
         
         if (defined('BX_POPOON_DIR')) {
             $sm =  BX_POPOON_DIR . '/sitemap/';
         } else {
             $sm = dirname(__FILE__).'/../sitemap/';
         }
         
         $this->cacheParams = array(
                            'cache_dir' => $cd,
                            'encoding_mode'=>'slash'
                            );
         $this->sm2php_xsl_dir = $sm;;
     }
     /**
      * Gets a singleton instance of the this class
      *
      * @return popoon_classes_config an instance of this class
      */
     public static function getInstance () {
        if (!popoon_classes_config::$instance) {
            popoon_classes_config::$instance = new popoon_classes_config();
        } 
        return popoon_classes_config::$instance;
    }
    
   /** 
    * gets a popoon Value
    *
    * returns a value set before with setPopoonValue().
    *
    * @param string $name name of the value
	* @see setPopoonValue
    * @return mixed the value 
    */
    public function getPopoonValue( $name) {
        if (isset($this->popoonValues[$name])) {
            return $this->popoonValues[$name];
        } else {
            return null;
        }
    }
    
    /**
     * Sets a value in the popoon context
     *
     * @param string $name name of the value
     * @see getPopoonValue
     * @return void
     */ 
    
    public function setPopoonValue( $name, $value) {
        $this->popoonValues[$name] = $value;
    }
    
    /**
    * Gets a value for a module
    *
    * @param string $module name of the module
    * @param string $name name of the value
    * @return mixed the value
    */
    public function getValue($module,$name) {
        
        if (!isset($this->values[$module])) {
           return null;
        }
        if (!isset($this->values[$module][$name])) {
           return null;
        }
        return $this->values[$module][$name];
    }
    
    
    /**
    * Sets a value for a module
    *
    * @param string $module name of the module
    * @param string $name name of the value
    * @param mixed $value
    * @return void
    */
    public function setValue($module,$name,$value) {
        if (!isset($this->values[$module])) {
            $this->values[$module] = array();
        }
        $this->values[$module][$name] = $value;
    }
     
    /**
     * Checks, if popoon should do Output Caching
     * 
     * This currently only checks, if an outputCacheCallback
     * is set and if this returns true
     *
     * @see setOutputCacheCallback
     * @see doOutputCacheCallback
     * @return bool 
     */
    public function doOutputCache() {
        
        if ($this->outputCacheCallback && $mode = $this->doOutputCacheCallback()) {
            if ($mode === 304) {
                $this->outputCacheSave = 304;
            } else {
                $this->outputCacheSave = true;
            }
            return true;
        } 
        if ($this->staticFileCache) {
            return true;
        }
        return false;
    }
    /**
     * Disables saving of outputCaching
     * 
     * If this method is called, the outputCache Handler does not
     *  save the result
     * @see doOutputCacheSave
     */
     
    public function disableOutputCaching() {
        $this->outputCacheSave = false;
    }
    
    /**
     * Check, if we should save the outputcache
     *
     * @see disableOutputCaching
     * @return bool
     */
     
    public function doOutputCacheSave($sitemap) {
        // if the outputCacheSave param is set, just return true
        if ($this->outputCacheSave) {
           return  $this->outputCacheSave;
        }
        /* but we want to use outputcaching for all "components
            which define _file-location. always. It's quite safe to use that anyway
            (if you read a file with the resource component for example, it just
             outputs the content of that file, so if we check, if that file changed,
             that should be enough to get a reliable cache hit)
        */
        if ($this->staticFileCache && isset($sitemap->header["_file-location"]) ) {
            return true;
        }
        return false;
    }
    /**
     *  Sets outputcache handler
     *
     * This function will be called within doOutputCache
     * It's only set, if the function exists
     * otherwise it throws an exception
     *
     * @see doOutputCache
     * @example classes/config_cache.php
     * @param string $callback the function to be called
     */
     
    public function setOutputCacheCallback ($callback) {
        if (function_exists($callback)) {
            $this->outputCacheCallback = $callback;
        } else {
            // I'm not sure, if we really want an exception here
            throw new Exception('Function ' . $callback . ' is not defined');
        }
    }
    /**
     * Calls the outputCache Handler
     *
     * This function should return true or false. True if we should use OutputCaching
     * , false if not
     *
     * @see setOutputCacheCallback
     * @return bool
     */
    private function doOutputCacheCallback() {
        return  call_user_func($this->outputCacheCallback);
    }
    
     //ArrayAccess interface
    function offsetGet($off) {
       return $this->$off;
    }
    
    function offsetSet($off,$value) {
        $this->$off = $value;
    }
    
    function offsetExists($off) {
        return isset($this->$off);
    }
    
    function offsetUnset($off) {
        unset($this->$off);
    }
    
}
