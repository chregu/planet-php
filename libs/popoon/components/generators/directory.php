<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// | Copyright (c) 2003 Mike Hommey                                       |
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
// | Author: Mike Hommey <mh@glandium.org>                                |
// |         Christian Stocker <chregu@bitflux.ch>                        |
// |         Iván Montes <imontes@imaginocreativa.com>                    |
// +----------------------------------------------------------------------+
//
// $Id: directory.php 2990 2004-11-15 19:42:05Z chregu $

include_once('popoon/components/generator.php');

/**
* This class returns xml from directory listing
*
* The "dateFormat" parameter should be a suitable format string for strftime()
*
* @author   Mike Hommey <mh@glandium.org>
* @version  $Id: directory.php 2990 2004-11-15 19:42:05Z chregu $
* @package  popoon
* @note     Reversed sorting using 'directory' is somewhat broken :(
*           include and exclude patterns are a bit limited in my opinion, although I'm not
*           sure how they're handled in cocoon.
*           The only parameter missing from the cocoon implementation is the 'root' one.
*/
class popoon_components_generators_directory extends popoon_components_generator {
    protected $mimeType;
    protected $ns = 'http://apache.org/cocoon/directory/2.0';
    protected $strDateFormat;  // strftime format string
    protected $sortBy;
    protected $sortDir = false;
    protected $sortOrder;
    protected $includePattern = false;
    protected $excludePattern = false;
    
    /**
    * Constructor, does nothing at the moment
    */
    function __construct (&$sitemap) {
        parent::__construct($sitemap);
    }
    
    /**
    * generates an xml-DomDocument out of the xml-file
    *
    * @access public
    * @returns object DomDocument XML-Document
    */
    function DomStart(&$xml) {
        
        $this->mimeType = $this->getParameterDefault('mimeType');
        if ($this->mimeType && !function_exists("mime_content_type")) {
            $this->mimeType = false;
        }
        
        if (! $this->strDateFormat = $this->getParameterDefault('dateFormat')) {
            $this->strDateFormat = '%c';     //use current locale default
        }
        
        $this->includePattern = $this->getParameterDefault('include');
        $this->excludePattern = $this->getParameterDefault('exclude');
        
        //setup some flags to handle the sorting
        $this->sortBy = $this->getParameterDefault('sort');
        if ($this->sortBy === 'date') $this->sortBy = 'lastModified';
        if ($this->sortBy === 'directory') {
            $this->sortBy = 'name';
            $this->sortDir = true;
        }
        $this->sortOrder = (strtolower($this->getParameterDefault('reverse')) === 'true')?-1:1;
        
        
        $src = $this->getAttrib('src');
        $xml = new DOMDocument('1.0');
        $root = $xml->createElementNs($this->ns,'directory');
        $xml->appendChild($root);
        
        if (! $depth = $this->getParameterDefault('depth')) {
            $depth = 1;
        }
        
        if ($this->read_directory($xml,$root,$src, $depth)) {
            $stat = stat($this->sitemap->base_dir.'/'.$src);
            $root->setAttribute('requested','true');
            $root->setAttribute('size',$stat['size']);
            $root->setAttribute('lastModified', $stat['mtime']);
            $root->setAttribute('date', $this->formatDate($stat['mtime']));
            if ($this->sortBy) $root->setAttribute('sort', $this->getParameterDefault('sort'));
            $root->setAttribute('reverse', ($this->sortOrder==1)?'false':'true');
        } else {
            $xml = NULL;
        }
        
        return True;
    }
    
    function read_directory($dom, $parent, $directory, $depth = 1) {
        if (is_dir($directory) && ($dh = @opendir($directory))) {
            while (($file = readdir($dh)) !== false) {
                if (($file == ".") || ($file == "..")) continue;
                
                //check include and exclude patterns
                if ($this->includePattern && !preg_match($this->includePattern, $directory.'/'.$file)) continue;
                if ($this->excludePattern && preg_match($this->excludePattern, $directory.'/'.$file)) continue;
                
                $path = $directory.'/'.$file;
                while ($path && is_link($path)) { //get the real file
                    $path = readlink($path);
                }
                
                if (is_file($path)) {
                    $node = $dom->createElementNs($this->ns, 'file');
                } else if (is_dir($path)) {
                    $node = $dom->createElementNs($this->ns, 'directory');
                } else {
                    continue; //just in case :)
                }
                
                $node->setAttribute('name', $file);
                
                $info = $this->getInfo($path);
                foreach ($info as $k => $v) {
                    $node->setAttribute($k, $v);
                }
                
                //performs the sorting
                $done = false;
                if ($this->sortBy) {
                    foreach ($parent->childNodes as $child) {
                        if (
                        ($this->sortDir && $child->tagName == 'file' && $node->tagName == 'directory') ||
                        (strcmp($child->getAttribute($this->sortBy), $node->getAttribute($this->sortBy)) == $this->sortOrder)
                        ) {
                            $node = $parent->insertBefore($node, $child);
                            $done = true;
                            break;
                        }
                    }
                }
                if (! $done) $parent->appendChild($node);
                
                if (is_dir($path) and ($depth > 1)) {
                    $this->read_directory($dom, $node, $path, $depth - 1);
                }
            } //while
            
            closedir($dh);
            return true;
        } else {
            return false;
        }
    }
    
    function formatDate($timestamp) {
        if ($timestamp) return strftime($this->strDateFormat, $timestamp);
    }
    
    /**
    * gets information about a directory entry
    * Tip: Extend this method to add custom metadata to files like MP3, images ...
    *
    * @access public
    * @returns array
    */
    function getInfo($entry) {
        $info = array();
        
        $stat = stat($entry);
        
        //try to fetch details
        if ($this->mimeType) {
            $mimeType = mime_content_type($directory.'/'.$file);
            if (strpos($mimeType, "image") === 0) {
                $size = getimagesize($directory.'/'.$file);
                $info['imageWidth'] = $size[0];
                $info['imageHeight'] = $size[1];
            }
            $info['mimeType'] = $mimeType;
        }
        
        $info['size'] = $stat['size'];
        $info['lastModified'] = $stat['mtime'];
        $info['date'] = $this->formatDate($stat['mtime']);
        
        return $info;
    }
    
    /* CACHING STUFF */
    
    /**
    * Generate cacheKey
    *
    * Calls the method inherited from 'Component'
    *
    * @param   array  attributes
    * @param   int    last cacheKey
    * @see     generateKeyDefault()
    */
    function generateKey($attribs, $keyBefore){
        return($this->generateKeyDefault($attribs, $keyBefore));
    }
    
    /** Generate validityObject
    *
    * This is common to all "readers", you'll find the same code there.
    * I'm thinking about making a method in the class component named generateValidityFile() or alike
    * instead of having the same code everywhere..
    *
    * @author Hannes Gassert <hannes.gassert@unifr.ch>
    * @see  checkvalidity()
    * @return  array  $validityObject contains the components attributes plus file modification time and time of last access.
    */
    function generateValidity(){
        $validityObject = $this->attribs;
        $src = $this->getAttrib('src');
        $validityObject['filemtime'] = filemtime($src);
        $validityObject['fileatime'] = fileatime($src);
        return($validityObject);
    }
    
    /**
    * Check validity of a validityObject from cache
    *
    * This implements only the most simple form: If there's no fresher version, take that from cache.
    * I guess we'll need some more refined criteria..
    *
    * @return  bool  true if the validityObject indicates that the cached version can be used, false otherwise.
    * @param   object  validityObject
    */
    function checkValidity($validityObject){
        return(isset($validityObject['src'])       &&
        isset($validityObject['filemtime']) &&
        file_exists($validityObject['src']) &&
        ($validityObject['filemtime'] == filemtime($validityObject['src'])));
    }
    
}


?>