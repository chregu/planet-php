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
// $Id: outputcache.php 3605 2005-02-06 12:46:28Z bitflux $

/**
* Class for doing the sitemap parsing stuff
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: outputcache.php 3605 2005-02-06 12:46:28Z bitflux $
* @package  popoon
*/

/** 
* Some words about 304 caching:
*
* It has now quite sophisticated 304 detecting mechanism built in
*
* C-Etag = Cached Etag (md5($content))
* B-Etag = Browser sent etag 
* C-LM = Cached Last Modified
* B-LM = Browser Last Modified
* M-LM = Last Modified in oc.meta
*
* oc.meta = outputcache.meta. Dies directory contains metadata about LM and Etag of 
*  the last cached url.
*  This allows to delete the cache directory (outputcache) completely, but we still now
*   the Etag (md5 of content) and LM of the last generated page.
*
* If url exits in cache and C-ETag = B-Etag and C-LM <= B-LM 
*   send 304
*
* If url does not exist in cache and not in outputcache.meta
*   generate content
*   generate C-Etag = md5($content)
*   if C-Etag = B-Etag
*      send 304
*   save content
*   save oc.meta
*   print content
*   // this method saves us sending the whole content, when the browser already cached this content
*   // it does not make it faster, since we have to generate the content anyway, we just save bandwidth
*
* If url does not exist in cache but in outputcache.meta
*   generate content
*   generate C-Etag = md5($content)
*   if C-Etag = M-Etag
*      set LM to M-LM
*   if C-Etag = B-Etag
*      send 304
*   if C-LM <= B-LM
*      send 304
*   save content
*   print content
*
*   //this method saves us also sending the whole content as above, it additionally keeps the LM if the 
*   // md5 did not change. this is more cosmetics than really saving resources as the above does save
*   // the resources anyway (except the browser doesn't understand Etags..)
*
*   Honestly said, the later 2 parts in the 304 handler is maybe overkill... It's just cosmetics more or 
*    less and will not save much resources. But with newsreaders, which read the rss file every half an 
*    hour or so, it could sum up and save some bandwidth.
* 
*  Maybe I will make the later part with the extra oc.meta files optional... But for Google it's certainly
*   nice to know, when the page was really last modified and not, when the cache was last created ;)
*/



class popoon_sitemap_outputcache {

    public $compression = true;
    
    function __construct(popoon_classes_config $options = NULL) {
        require_once('Cache/Output.php');
        $this->options = $options;
        $options->cacheParams['max_userdata_linelength'] = 0;
        $this->cache = new Cache_Output($options->cacheContainer, $options->cacheParams );
        if ($this->compression) {
            $this->compression = $this->getSupportedCompression();   
        }
    }
    
    function start($uri) {
        $idParams = $_GET;
        if (isset($idParams['SID']))
        {
            unset($idParams['SID']);
        }
        $this->id = str_replace("/","_",$uri).$this->cache->generateID($idParams).$this->compression;
        $this->cacheGroup = 'outputcache';
        if ( $content = $this->cache->start($this->id,$this->cacheGroup) ) {
            if ($this->options->outputCacheSave === true ) {
                $header = unserialize($this->cache->getUserdata($this->id,$this->cacheGroup));
                $etag = $header['ETag'];
                
                if (isset($header['_file-location'])) {
                    if (isset($header['_file-location'])) {
                        if (strtotime($header['Last-Modified']) < filemtime($header['_file-location'])) {
                            header("X-Popoon-Cache-Status: File is newer than cache");
                            return false;
                        }
                    }
                }
                foreach ($header as $key => $value) {
                    if (substr($key,0,1) != "_") { 
                        header("$key: $value");
                    }
                }
                if ($this->check304($etag, $header['Last-Modified'])) {
                    header('HTTP/1.1 304 Not Modified' );
                    header("X-Popoon-Cache-Status: 304");
                    die();
                }
                
                header("X-Popoon-Cache-Status: true");
                print $content;
                die();
            }
        }
    }
    
    function check304($etag, $lastModified) {
        if (isset($_SERVER["HTTP_IF_NONE_MATCH"]) && $_SERVER["HTTP_IF_NONE_MATCH"] != 'None') {
            if (trim($etag,"\"' \t") == trim($_SERVER["HTTP_IF_NONE_MATCH"],"\"' \t")) {
                return true;
            }
        }
        else if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"]) )
        {
            if (strtotime($lastModified) <= strtotime($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
                return true;
            } 
        }
        return false;
    }
    
    function end(&$sitemap, $expire = 3600) {
        $content =  ob_get_contents();
        ob_end_clean();
        $etag =  md5($content);
        $sitemap->setHeader("ETag", '"'.$etag.'"');
        if ($this->options->outputCacheSave !== 304) {
            $metadata = $this->cache->get($this->id.'.meta','outputcache.meta');
            $lastModified = null;
            if ($metadata) {
                if (isset($metadata['Etag'] )&& $metadata['Etag'] == $etag) {
                    $sitemap->setHeaderIfNotExists("Last-Modified",$metadata['Last-Modified']);
                    $lastModified = true;
                } 
            }
            if (!$lastModified) {
                $metadata['Etag'] = $etag;
                $sitemap->setHeaderIfNotExists("Last-Modified",gmdate('D, d M Y H:i:s T'));
                $metadata['Last-Modified'] = $sitemap->header['Last-Modified'];
                $this->cache->container->save($this->id.'.meta', $metadata, 0 ,"outputcache.meta","" );
            }
        } else {
		if (isset( $sitemap->header['Last-Modified'])) {
            $sitemap->setHeaderIfNotExists("Last-Modified", $sitemap->header['Last-Modified']);
		}
        }
        // we don't want phps cache-control stuff, when we do caching
        // there is a "problem" if session_start is used, then PHP adds no-cache http headers
        // we do not want that in outputCaching.
        // Drawback: OutputCAching with sites relying on different sessions-values do not work
        header("Pragma: ");
        header("Cache-Control: ");
        header("Expires: ");

        if ($this->compression) {
                    $sitemap->setHeader("Content-Encoding",$this->compression);
                    $sitemap->setHeader("Vary","Accept-Encoding");
        }
        
        foreach ($sitemap->header as $key => $value) {
            if (substr($key,0,1) != "_") { 
                header("$key: $value");
            }
        }
        if (isset($sitemap->header['Last-Modified']) && $this->check304($etag, $sitemap->header['Last-Modified'])) {
            header( 'HTTP/1.1 304 Not Modified' );
            
            if ($this->options->outputCacheSave !== 304) {
                $this->cache->extSave($this->id, $this->compressContent($content), serialize($sitemap->header), $expire ,$this->cacheGroup);
            }
            die();
        } else {
            $content = $this->compressContent($content);
            print $content;
            if ($this->options->outputCacheSave !== 304) {
                $this->cache->extSave($this->id, $content, serialize($sitemap->header), $expire ,$this->cacheGroup);
            }
        }
    }
    
    function compressContent($content) {
        if ($this->compression) {    
            $len = strlen($content);            
            $crc = crc32($content);
            $content = gzcompress($content, 9);
            return "\x1f\x8b\x08\x00\x00\x00\x00\x00"  . substr($content, 0, strlen($content) - 4) . pack('V', $crc) . pack('V', $len);
        }        
        return $content;
    }
    
    function getSupportedCompression() {
        // check what the client accepts
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {	
	        if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
		        if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip')) {	
				return 'x-gzip';
			}
			return 'gzip';
		}
        }
            
        // no compression
        return '';
        
    } 
    
}
