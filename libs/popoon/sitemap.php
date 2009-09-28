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
// $Id: sitemap.php 4135 2005-04-28 13:05:48Z chregu $

/**
* Class for doing the sitemap parsing stuff
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: sitemap.php 4135 2005-04-28 13:05:48Z chregu $
* @package  popoon
*/


class popoon_sitemap {
    
    
    private $maps = array();
    public $file = null;
    public $uri = "";
    public $xml = null;
    public $rootFile = null;
    
    /**
    * Contains the header, which will be output just before serializing
    *
    * @var array
    */
    public $header = array();
    
    
    /**
    * HTTP response code which is sent with the first header 
    *
    * @var array
    */
    private $responseCode = NULL;
    
    /**
    * The contenttype
    *
    * should be set within components (resp. serializers) with
    * $this->sitemap->setContentType($type);
    *
    * @var string contenttype
    */
    
    private $contentType = "text/html";
    /**
    * The Directory, where the cached sitemaps should be saved
    *
    * @var string
    */
    public $cacheDir = "/tmp/";
    
    
    /**
    * Contains instance of ComponentCache or false
    *
    * @var mixed
    * @see enableCaching(), disableCaching()
    */
    private $componentCache = false;
    
    public $outputCache = false;
    /**
    * The directory, where the xslt documents for
    * sitemap 2 php translation are.
    *
    * @var string
    */
    private $sm2php_xsl_dir = "sitemap/";
    
    /**
    * The XSL File for transforming sitemap.xsl to a cached php file
    *
    * You shouldn't have to change it.
    *
    * @var string
    */
    private $sm2php_xsl = "sitemap2php.xsl";
    
    private $sm2phpincludes_xsl = "sitemap2phpincludes.xsl";
    
    
    public $options;
    
    /**
    * Construtor
    *
    *  Almost everything happens here. The cached sitemap is generated here, if it
    *   doesn't exist or if it's older
    *  Then this sitemap is included and the code in it is run
    *
    *  @param string $sitemapFile the location of sitemap.xml, can be relativ
    *  @param string $uri the uri of the call, optional (takes _SERVER["REQUEST_URI"] then)
    *  @param array $options options, to be defined
    *  @access public
    *  @return bool
    */
    function __construct($sitemapFile, $uri= null, popoon_classes_config $options = NULL, $maps = NULL) {
        if (!$this->rootFile) {
            $this->rootFile = $sitemapFile;
        }
        //replace class-properties by values in the options-array()
        if ($maps) {
            $this->maps = $maps;
        }
        $this->options = $options;
        
        
        //FIXME use new config object class...
        if (!isset($options['sm2php_xsl_dir']) && isset($options['sm2php_xsl'])) {
            $options['sm2php_xsl_dir'] = dirname($options['sm2php_xsl']);
            $options['sm2php_xsl'] = basename($options['sm2php_xsl']);
        }
        
        foreach($options as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            }
        }
        $this->file = $sitemapFile;
        
        if ($uri === null) {
            $this->uri = $_SERVER["REQUEST_URI"];
        } else {
            $this->uri = $uri;
        }

        //generate paths and ids
        $sitemapRealPath = realpath($sitemapFile);
        if (!$sitemapRealPath) {
            return popoon::raiseError("Sitemap $sitemapFile does not exist",POPOON_ERROR_FATAL);
        }
        $sitemapId = $this->generateSitemapID($sitemapRealPath);

        $this->cacheDir = BX_PROJECT_DIR . "/" . $this->cacheDir;

        $sitemapCachedFile = $this->cacheDir . $sitemapId;
        //check if sitemapCache does exists and if it's older than the sitemap.xml
        if ( (!(file_exists($sitemapCachedFile) && filemtime($sitemapCachedFile) >= filemtime($sitemapRealPath))))
        {
            //if it is, make new sitemapCached file
            $err = $this->sitemap2php($sitemapRealPath,$sitemapCachedFile);
        }
        
        $pipelineHit =  $this->runSitemap($sitemapCachedFile);
        return $pipelineHit;
        
    }
    
    /**
    * Runs the cached Sitemap
    *
    * @param string $sitemapCachedFile location of the cached file
    * @return mixed true on success, pear error on error
    */
    function runSitemap($sitemapCachedFile) {
        //include the sitemap file
        $pipelineHit = false;
        include ($sitemapCachedFile);
        $this->pipelineHit = $pipelineHit;
        return $pipelineHit;
    }
    
    public function getOptions($clone = false) {
        return clone $this->options;   
    }
    
    /**
    * Sets a header, which is output just before the serializer
    *
    * @param string $name name of the header
    * @param string $value value of the header
    */
    function setHeader($name, $value) {
        $this->header[$name] = $value;
    }
    
    /**
    * Sets multiple headers which are merged into the existing ones. 
    *
    * @param array $value headers to be set
    */
    function setHeaders($headers) {
        $this->header = array_merge($this->header, $headers);
    }
    
    function setUserData($name, $value) {
        $this->header['_'.$name] = $value;
    }
    
    function getUserData($name) {
        return $this->header['_'.$name];
    }
    
    
    function setHeaderIfNotExists($name, $value) {
        if (!isset($this->header[$name])) {
            $this->setHeader($name,$value);
        }
    }
    
    /**
    * Sets a HTTP response code, which is sent with the first header
    *
    * @param int $value response code value
    */
    function setResponseCode($value) {
        $this->responseCode = $value;
    }
    
    /**
    * Sets a header, and directly outputs it
    *
    * Very useful in DomStart of serializers, as the printHeader()
    *  function is called before...
    *
    * @param string $name name of the header
    * @param string $value value of the header
    */
    function setHeaderAndPrint($name, $value) {
        $this->setHeader($name,$value);
        header("$name: $value");
    }
    
    /**
    * Sets the last modified time
    *
    * @param int $time unixtime last modified
    */
    function setLastModified($time) {
        if ($time > 0) {
            $this->setHeader("Last-Modified",gmdate("r", $time));
        }
    }
    
    /**
    * Sets the content type of the document
    *
    * @param string $type content type
    */
    function setContentType($type) {
        $this->contentType = $type;
    }
    
    /**
    * If noCache is set, disables all http caching 
    * headers according to http://dclp-faq.de/q/q-http-caching.html
    */
    function setCacheHeaders ($noCache) {
        if ($noCache) {
            $date = gmdate("D, d M Y H:i:s");
            $this->setHeader("Expires", $date . " GMT");
            $this->setHeaderIfNotExists("Last-Modified",  $date ." GMT");
            $this->setHeader("Pragma","no-cache");
            $this->setHeader("Cache-Control","no-cache, post-check=0, pre-check=0");
        } else {
            //My Apache 2 sends max-age=10800, which is insanely high.. change that 
            // here to 10 seconds (at least, we have something then, even if not that high)
            $this->setHeaderIfNotExists("Cache-Control",  "public, max-age=10");
        }
        
    }
    
    /**
    * Prints all the header in $this->header
    *
    * this function is called from components/serializer.php in the constructor
    */
    
    function printHeader() {
        $this->setHeader("Content-Type",$this->contentType);
        
        // flag for an already sent response header
        $responseCodeSent = FALSE;
        
        foreach ($this->header as $name => $value) {
            if (substr($name,0,1) != "_") { 
             
            // only send response code with first header
            if($responseCodeSent) {
                header("$name: $value");
            } else {
                header("$name: $value", TRUE, $this->responseCode);
                $responseCodeSent = TRUE;
            }
            }
        }    
        
    }
    
    
    /**
    * generates a unique ID out of a string (path+filename)
    *
    * This method is used for generating a unique ID for every sitemap
    *  which has to be compiled. It just takes the realpath as input,
    *  and returns a unique ID for it.
    * In this case it replaces every DIRECTORY_SEPERATOR with _ for better
    *  debugging. Theoretically one could use md5 as well.
    *
    * @param string $realpath Any string, but in this class normally a fullpath+filename
    * @access public
    * @return string ID
    */
    function generateSitemapID ($realpath) {
        return str_replace(array(DIRECTORY_SEPARATOR,":"),"_",$realpath);
    }
    
    /**
    * Generates the cached sitemap
    *
    * This function generates a php file out of a sitemap.xml with the help
    *  an xsl file (poponn/sitemap/sitemap2xsl.php)
    *
    * Libxslt and Sablotron is supported right now.
    *
    * @param string $sitemapRealPath the absolute location of sitemap.xml
    * @param string $sitemapCachedFile the absolute location of the cached sitemap (php file)
    * @access public
    * @return bool
    */
    function sitemap2php($sitemapRealPath, $sitemapCachedFile) {

        //file_exists and is writable should be the normal case...
        if (! is_writable($sitemapCachedFile))
        {
            if (! realpath(dirname($sitemapCachedFile)))
            {
                if (!mkdir(dirname($sitemapCachedFile)."/")) {
                    return popoon::raiseError("The cache directory ". dirname($sitemapCachedFile)." does not exist" ,POPOON_ERROR_FATAL);
                }
            }
            if (!is_writable(dirname($sitemapCachedFile)))
            {
                return popoon::raiseError("The cache directory  ". realpath(dirname($sitemapCachedFile))." is not writable",POPOON_ERROR_FATAL,__FILE__,__LINE__);
            }
            else if ((file_exists($sitemapCachedFile) && !is_writable($sitemapCachedFile)))
            {
                return popoon::raiseError("The cache file ".realpath($sitemapCachedFile). " is not writable",POPOON_ERROR_FATAL);
            }
        }

        //check if we have domxml/xslt

        $xslDom = new DomDocument();
        $xslDom->load($this->sm2php_xsl_dir."/".$this->sm2php_xsl);

        if (!class_exists("XsltProcessor")) {
            return popoon::raiseError(
                "Popoon doesn't run without XSLT support in PHP.",
                POPOON_ERROR_FATAL,
                __FILE__,
                __LINE__
            );
        }
        $xsl = new XsltProcessor();
        $xsl->importStylesheet($xslDom);
        $xsl->registerPhpFunctions();

        $xslincludesDom = new DomDocument();

        $xslincludesDom->load($this->sm2php_xsl_dir."/".$this->sm2phpincludes_xsl);

        $xslincludes = new XsltProcessor();
        $xslincludes->importStylesheet($xslincludesDom);

        $sm = new DomDocument();
        if (!$sm->load($sitemapRealPath)) {
            if (!file_exists($sitemapRealPath)) {
                throw new PopoonFileNotFoundException($sitemapRealPath);
            }
            throw new PopoonXMLParseErrorException("Could not load $sitemapRealPath");
        }
        $xsl->setParameter("","popoonDir",dirname(__FILE__));

        $result = $xslincludes->transformToDoc($sm);
        $result = $xsl->transformToUri($result,$sitemapCachedFile);
	exit;

        return True;
    }

    function convertXML($object, &$xml) {
        if ($object->XmlFormat == "DomDocument")
        {
            $this->var2XMLObject($xml);
        }
        elseif ($object->XmlFormat == "XmlString")
        {
            $this->var2XMLString($xml);
        }
        return True;
    }
    
    /**
    * Converts it's parameter into a DomDocument object
    *
    * @param  mixed  xmldoc        can either be a XML String or a DomDocument object
    * @return bool
    * @access private
    */
    function var2XMLObject(&$xmldoc)
    {
        if (is_string ($xmldoc))
        {
            
            $xmldom = new DomDocument();
            
            $xmldom->loadXML($xmldoc);
            $xmldoc=$xmldom;
            
            
        }
        if ( strtolower(get_class($xmldoc)) != "domdocument")
        {
            return popoon::raiseError('First parameter to var2XMLObject() is neither a XML String nor a XML DomDocument object. It is: '.
            var_export($xmldoc, true),
            POPOON_ERROR_FATAL);
        }
        return True;
    }
    
    function redirectTo($uri) {
        header("Location: ".popoon_sitemap::translateScheme($uri));
        exit;
    }
    
    /**
    * Converts it's parameter into a XML String
    *
    * @param  mixed  xmldoc        can either be a XML String or a DomDocument object
    * @return bool
    * @access private
    */
    function var2XMLString(&$xmldoc)
    {
        if ( strtolower(get_class($xmldoc)) == "domdocument")
        {
            $xmldoc = $xmldoc->saveXML();
        }
        if (!is_string ($xmldoc))
        {
            return popoon::raiseError('First parameter to var2XMLString() is neither a XML String nor a XML DomDocument object. It is: '.
            var_export($xmldoc, true),
            POPOON_ERROR_FATAL);
        }
        else
        {
            return True;
        }
    }
    
    /**
     * in the array doNotTranslate we can give some values, which should not be
     * translated, as for example http...
     */
    function translateScheme($value, $doNotTranslate = array(), $onSitemapGeneration = false)
    {
        // don't do anything, if we don't have any scheme stuff in the $value;
        // strpos should be rather fast, i assume.
                
        if (is_object($value)
            || is_array($value)
            || strpos($value,":/") === false
            && strpos($value,"{") === false) {

                // this is obviously not a "scheme://"
                return $value;
        }
        
        $scheme = popoon_sitemap::getSchemeParts($value);

        //checks if value  ends with } and starts with { with no { after the first position
        // then we don't need this fairly complicated preg from below and can substitute also arrays and alike
                
        if ( !$onSitemapGeneration
            && substr ($scheme["value"], -1,1) == "}"
            && strrpos ( $scheme["value"], "{") === 0) {

            $scheme["value"] = substr($scheme["value"],1,-1);

            // WTF IS THIS
            $scheme["value"] = $this->maps[substr_count($scheme["value"],'../')][str_replace("../","",$scheme["value"])];

        } else if ($onSitemapGeneration) {
            
            $scheme["value"] = preg_replace(
                "#\{([\./]*([^}]+))\}#e",
                "popoon_sitemap::translateSchemeSubPartsOnSitemapGeneration('$1','$2')",
                $scheme["value"]
            );

        } else {

            $scheme["value"] = preg_replace(
                "#\{([\./]*([^}]+))\}#e",
                "\$this->translateSchemeSubParts('$1','$2')",
                $scheme["value"]
            );

        }

        //var_dump($scheme['value']);

        if (in_array($scheme["scheme"], $doNotTranslate)) {

            return $value;

        } else if ($scheme["scheme"] != 'default') {

            $_scheme_file  = BX_INCLUDE_DIR . 'popoon/components/schemes/';
            $_scheme_file .= $scheme["scheme"]. '.php';

            $_status = include_once $_scheme_file;
            //var_dump("INCLUDE", $_scheme_file, $_status);

            if ($_status === false) {

                return $value;
            }

            if ($onSitemapGeneration) {

                $_function   = "scheme_" . $scheme["scheme"] . "_onSitemapGeneration";
                $_isFunction = function_exists($_function);

                //var_dump("FUNCTION", $_function, $_isFunction, $scheme['value']);

                if ($_isFunction === true) {
                     return call_user_func($_function, $scheme["value"]);
                }
                return $value;

            }

            return call_user_func("scheme_".$scheme["scheme"], $scheme["value"], $this);

        }

        return $scheme["value"];
    }
    
    function translateSchemeSubParts ($value, $value2) {
        if (strpos($value,":/") === false) {
            return $this->maps[substr_count($value,'../')][$value2];
        } else {
            return popoon_sitemap::translateScheme($value);
        }
    }
    
     static function translateSchemeSubPartsOnSitemapGeneration ($value, $value2) {
        if (strpos($value,":/") === false) {
            return '{'.$value.'}';
        } else {
            $newVal = popoon_sitemap::translateScheme($value,array(),true);
            if ($newVal == $value) {
                return '{'.$value.'}';
            } else {
                return $newVal;
            }
            
        }
    }
    
    static function getSchemeParts($value) {
        $scheme = array();
        if (preg_match("#^'(.*)'$#",$value,$match))
        {
            $scheme["scheme"] = "default";
            $scheme["value"] = $match[1];
        }
        elseif (preg_match("#^([_a-zA-Z0-9]+)://(.*)#",$value,$match))
        {
            $scheme["scheme"] = $match[1];
            $scheme["value"] = $match[2];
        }
        else
        {
            $scheme["scheme"] = "default";
            $scheme["value"] = $value;
        }
        return $scheme;
    }
    
    function addMap($map) {
        array_unshift($this->maps,$map);
    }
    
    function removeMap() {
        array_shift($this->maps);
    }
    
    function setGlobalOptions($name, $data) {
        $GLOBALS["_POPOON_globalContainer"]->options[$name] = $data;
    }
    
    function setGlobalOptionsAll( $data) {
        $GLOBALS["_POPOON_globalContainer"]->options = $data;
    }
    
    function getGlobalOptions($name) {
        return $GLOBALS["_POPOON_globalContainer"]->options[$name] ;
    }
    
    function getGlobalOptionsAll() {
        if (isset($GLOBALS["_POPOON_globalContainer"]->options)) {
            return $GLOBALS["_POPOON_globalContainer"]->options ;
        }
        else
        {
            return null;
        }
    }
    
    
    /**
    * Mounts a second sitemap
    *
    * Not a very elegant solution, should be rewritten some day
    *
    */
    private function _mount($attribs)
    {
        $file = popoon_sitemap::translateScheme($attribs["src"]);
        $old_uri = $this->uri;
        
        if (isset($attribs["uri-prefix"]))
        {
            $prefix = popoon_sitemap::translateScheme($attribs["uri-prefix"]);
            if ($prefix)
            {
                $this->uri = preg_replace("#^/*$prefix/*#","",$this->uri, $this->maps);
            }
            
        }
        // I hope, this doesn't have too many sideeffects
        $pipelineHit = $this->__construct($file, $this->uri, $this->options, $this->maps);
        
        
        $this->uri = $old_uri;
        return $pipelineHit;
    }
    
    private function _scheme($attribs)
    {
        if (!isset($GLOBALS["_POPOON_globalContainer"])) {
            $GLOBALS["_POPOON_globalContainer"] = new stdClass();
        }
        if (!isset ($GLOBALS["_POPOON_globalContainer"]->schemes))
        {
            
            $GLOBALS["_POPOON_globalContainer"]->schemes = array();
        }
        $GLOBALS["_POPOON_globalContainer"]->schemes[$attribs["name"]] = array();
        if (isset($attribs["subname"]))
        {
            $GLOBALS["_POPOON_globalContainer"]->schemes[$attribs["name"]][$attribs["subname"]]  = array();
            foreach ($attribs as $value => $key)
            {
                $GLOBALS["_POPOON_globalContainer"]->schemes[$attribs["name"]][$attribs["subname"]][$value] = popoon_sitemap::translateScheme($key);
            }
        }
        else
        {
            foreach ($attribs as $value => $key)
            {
                $GLOBALS["_POPOON_globalContainer"]->schemes[$attribs["name"]][$value] =   popoon_sitemap::translateScheme($key);
            }
        }
        
    }
    
    /**
    * Starts component caching
    *
    * @param  array  Attributes of current pipeline
    * @see    disableCaching(), $componentCache
    */
    function enableCaching($pipelineAttribs){
        include('popoon/components/cache.php');
        $this->componentCache = new ComponentCache($pipelineAttribs, $this);
    }
    
    /**
    * Disables component caching
    *
    * Reset $this->componentCache
    *
    * @see    enableCaching(), $componentCache
    */
    function disableCaching(){
        $this->componentCache = false;
    }
    
    function disableOutputCaching() {
        $this->options->disableOutputCaching();
    }
    
}

function sitemap_formatValues($value) {
    $value = str_replace("'","\'",$value);
    //replace constant() with content
    preg_match_all("#constant\(([^\)]+)\)#",$value,$matches);
    $c = count($matches[0]) ;
    if ($c > 0) {
       for ($i = 0; $i < $c; $i++) {
           if (defined($matches[1][$i])) {
               $value = str_replace($matches[0][$i],"'.".$matches[1][$i].".'",$value);
           } 
       }
    }
    //check if there are any schemes... else we can return here
    if(strpos($value,":/") === false && strpos($value,"{") === false) {
            return sitemap_fixValue($value);
    }
    // translate translatabe scheme      
    $value = popoon_sitemap::translateScheme($value,array(),true);
    
    return sitemap_fixValue($value);
}
    
function sitemap_fixValue($value) {
     $value = "'".$value."'";
    return preg_replace(array("#\.''$#","#^''\.#","#\.''\.#"),"",$value);
}

?>
