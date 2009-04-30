<?php
 
 /** Class for storing Popoon Config parameter 
 * 
 *  Parameter about Caching et al. are also stored here
 *
 * @author   Christian Stocker <chregu@bitflux.ch>
 * @version  $Id: config.php 838 2004-03-16 19:45:10Z  $
 * @example classes/config_cache.php
 * @package  popoon
 */
 
 class popoon_pool {
     /**
     * the class instance
     *
     * this is a singleton class, therefore we save the instance
     * in this static var
     * @static popoon_classes_config
     */
     static $instance = null;
     
     private $configclass;
     
     /**
     * Gets a singleton instance of the this class
     *
     * @return popoon_classes_config an instance of this class
     */
     public static function getInstance ($configclass = "popoon_classes_config") {
         if (!popoon_pool::$instance) {
             popoon_pool::$instance = new popoon_pool($configclass);
             popoon_pool::$instance->configclass = $configclass;
         }
         else if (popoon_pool::$instance->configclass != $configclass) {
             throw new Exception("The Config Class $configclass is not the same as the initially defined one ". popoon_pool::$instance->configclass );
         }
             
         return popoon_pool::$instance;
     }  
     
      /**
      * The constructor
      * 
      * As this is a singleton class, we don't allow the class to be called
      * directly. use getInstance
      *
      * @see getInstance
      */
     private function __construct() {
         
     }
     
     public function __get($name) {
         switch ($name) {
             case "config":
                $c = $this->configclass;
                eval('$this->config = '.$this->configclass.'::getInstance();');
                return $this->config;
                break;
             case "db":
                require_once("MDB2.php");
                if (!isset($this->config->dboptions)) {
                    $this->config->dboptions = NULL;
                }
		
                $this->db = MDB2::connect($this->config->dsn,$this->config->dboptions);
		if (MDB2::isError($this->db)) {
			throw new PopoonDBException($this->db);
		}
                return $this->db;
         }
             
     }
     
 }
 ?>
