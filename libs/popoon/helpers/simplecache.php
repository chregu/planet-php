<?php
// +----------------------------------------------------------------------+
// | Bitflux CMS                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003 Bitflux GmbH                            |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: simplecache.php 3517 2005-01-25 07:48:35Z chregu $

/**
* api functions. to be used in admin modules
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: simplecache.php 3517 2005-01-25 07:48:35Z chregu $
* @package  admin
*
*/
class popoon_helpers_simplecache
{
    public $cacheDir = null;

    private $bxst    = array();
    private $db      = null;
    private $idField = "ID";

    static function &getInstance()
    {
        static $instance;
        
        if (!isset($instance)) {
            $instance = new popoon_helpers_simplecache();
        }
        return $instance;
    }
    
    /*****************************
    * simpleCache Functions      *
    ******************************/
    
    /* simple cache is a really simple, but very fast cache
    no Garbage Collection and at the moment no file locking is done
    it's used for caching xml->array stuff (mostly config files),
    but can be used for almost everything 
    */
    
    function simpleCacheCheck($file,$group,$param = null, $type="serialize",$lastModified = false)
    {
        if (!$this->cacheDir) {
            $this->cacheDir = BX_PROJECT_DIR . "/tmp/";
        }
        $cacheFile = $this->simpleCacheGenerateName($group,$file,$param);
        $filemtime = @filemtime($cacheFile);
        if ($lastModified && $lastModified < 100000000) {
            $lastModified = time() - $lastModified;
        }
        
        if ($lastModified  && ($lastModified >= $filemtime)) {
            return false;
        }

        if (!$lastModified && (!(file_exists($file)) || filemtime($file) >= $filemtime)) {
            return false;
        }

        /* this can be used, if we want to save the vars as php-file, so caches can cache it...
        include($cacheFile);
        return $var;*/

        if ($type == "serialize") {
            return unserialize($this->readFile($cacheFile));
        }

        if ($type == "file") {
            return $cacheFile;
        }

        if ($type == "plain") {
            return $this->readFile($cacheFile);
        }

        if ($type == "php") {
            include($cacheFile);
            return $var;
        }
    }

    function simpleCacheWrite($file,$group,$param,$data,$type = "serialize")
    {
        if (!$this->cacheDir) {
            $this->cacheDir = BX_PROJECT_DIR . "/tmp/";
        }
        if ($group != "fullpath") {
            $cacheFile = $this->simpleCacheGenerateName($group,$file,$param);
        } else {
            $cacheFile = $file;
        }
        if ($type == "moveFile") {
            if (!file_exists(dirname($cacheFile))) {
                $this->mkpath(dirname($cacheFile));
            }
            rename($data,$cacheFile);
            return;
        }

        $fd = @fopen ($cacheFile,"wb");
        if (!$fd) {
            //if directory does not exist, create it
            if (!(@mkdir(dirname($cacheFile)))) {
                //if we can't generate the dir for the to be cached file
                // try to make it for the whole path
                // this should happen very seldom, or better said, only once 
                // then we have all the needed directories (new one letter dirs
                //  are handled by the statement above, the whole mkpath stuff
                //  is only needed for new group dirs, which do not change often
                $this->mkpath(dirname($cacheFile));
            }
            $fd = fopen ($cacheFile,"wb");
        }
        if ($type == "serialize") {
            fwrite ($fd,serialize($data));
        } elseif ($type == "plain" || $type == "file") {
            fwrite ($fd,$data);
        } else if ($type == "php") {
            fwrite ($fd, '<?php $var = ');
            fwrite($fd,var_export($data,1));
            fwrite ($fd, '?>');        
        } 
        fclose($fd);
    }
    
    function simpleCacheFlush($group = "")
    {
        if (!$this->cacheDir) {
            $this->cacheDir = BX_PROJECT_DIR . "/tmp/";
        }
        
        $this->deleteDir($this->cacheDir . "/" . $group);
    }
    
    function simpleCacheDelete($file,$group,$param)
    {
        if (!$this->cacheDir) {
            $this->cacheDir = BX_PROJECT_DIR . "/tmp/";
        }
        $cacheFile = $this->simpleCacheGenerateName($group, $file, $param);
        unlink($cacheFile);
    }
    
    public function simpleCacheGenerateName($group,$file,$param = array())
    {
        if (!$this->cacheDir) {
            $this->cacheDir = BX_PROJECT_DIR . "/tmp/";
        }
        $md5 = md5($file . serialize($param));
        return ($this->cacheDir . $group . "/" . substr($md5,0,1) . "/" . $md5);
    }
    /**
    * Reads content of remote page
    *
    * If it's already cached, it reads the file from the Cache
    *  otherwise it calls simpleCacheHttpLastModified(), to read and cache it
    * 
    * It also calls the page if expire >= 0, it takes then as expire date
    *  for determing, if it should be read again
    *
    * @param string $url the url of the remote page
    * @param int $expire an unixtime. If last-checked is < than this, it will be checked again 
    *
    */
    public function simpleCacheHttpRead ($url,$expire = -1)
    {
        $cacheFile = $this->simpleCacheGenerateName("simpleCacheHttp",$url);
        // if expire is less than the above number, it's meant to be relative..
        if ($expire < 100000000) {
            $expire = time() - $expire;
        }
        if ($expire >= 0) {
            try {
                $this->simpleCacheHttpLastModified($url, $expire);
            } catch(Exception $e) {
                if (file_exists($cacheFile)) {	
                    //touch the file, so we don't have to read it on every request..
                    touch ($cacheFile);
                    return $this->readFile($cacheFile);
                }
                return "<html><title>Could not load '".htmlspecialchars($url)."': \n" . htmlspecialchars($e->getMessage())."</title></html>";
            }
        }
        if (!file_exists($cacheFile)){
            $this->simpleCacheHttpLastModified($url, $expire);
        }
        return $this->readFile($cacheFile);
    }
    
    public function simpleCacheRemoteArrayRead ($url, $expire = -1)
    {
        if ($content = $this->simpleCacheCheck($url, "simpleCacheRemote",null,"serialize",$expire)) {
            return $content;
        }
        $file = $this->simpleCacheHttpRead($url,$expire);
        $array = explode("\n",trim($file));
        $this->simpleCacheWrite($url,"simpleCacheRemote",null,$array,"serialize");
        return $array;
    }
    
    public function simpleCacheRemoteImplodeRead ($url, $implode = "|", $expire = -1)
    {
        if ($content = $this->simpleCacheCheck($url, "simpleCacheRemote",null,"plain",$expire)) {
            return $content;
        }
        $file = $this->simpleCacheHttpRead($url,$expire);
        $s = str_replace("\n",$implode,trim($file));
        $this->simpleCacheWrite($url,"simpleCacheRemote",null,$s,"plain");
        return $s;
    }
    
    
    /**
    * Checks the last modified date of an external page and returns the page
    *
    * But only, if it was checked before (unixtime) $expire, otherwise it
    *  just returns the the cached modfied date
    *
    * Furthermore it honors 304 answers and caches the response, if it was an
    *  200 answer
    *
    * E-tag is not supported yet, will come later and can be saved in file.timestamp
    * (but it's much more expensive to read a file, than just to get the mtime of it)
    *
    * If expire == 0, it always requests from the server (but still asks for 304)
    *
    * @param string $url the url to be checked
    * @param int $expire an unixtime. If last-checked is < than this, it will be checked again
    * @returns int unixtime of last modified
    */
    public function simpleCacheHttpLastModified($url,  $expire = 1, $proxy = "")
    {
        $cacheFile = $this->simpleCacheGenerateName("simpleCacheHttp",$url);

        $cacheFile_mtime             = @filemtime($cacheFile);
        $cacheFileLastModified       = $cacheFile.".lastmodified";
        $cacheFileLastModified_mtime = @filemtime($cacheFileLastModified);

        /* if we checked the cache later than expire time, just return LastModified date
        This way, we can prevent to ask the http-server on every hit, if we set
        for example  $expire = now() - 1 hour, we only check the server every hour.
        It would be quite stupid to ask the server on every request, even a 304 answer
        needs an established connection, which is really slow for doing on every request
        */

        if ($cacheFile_mtime && $expire > 0 && $cacheFile_mtime > $expire) {
            if ($cacheFileLastModified_mtime) {
                return $cacheFileLastModified_mtime ;
            } 
            return $cacheFile_mtime;
        }

        // if we checked a long time ago, try to get it
        include_once("HTTP/Request.php");
        $req = new HTTP_Request($url,array("timeout" => 5));
        $req->addHeader("User-Agent",'Popoon HTTP Fetcher+Cacher $Rev: 3517 $ (http://popoon.org)');
            
        if ($cacheFileLastModified_mtime) {
            $req->addHeader("If-Modified-Since",gmdate("D, d M Y H:i:s \G\M\T",$cacheFileLastModified_mtime));
        }
        if ($proxy) {
                
            $proxy = parse_url('http://'.$proxy);
            if (!isset($proxy['user'])) {
                $proxy['user'] = null;
            }
            if (!isset($proxy['pass'])) {
                $proxy['pass'] = null;
            }
            if (!isset($proxy['port'])) {
                $proxy['port'] = 8080;
            }
            $req->setProxy($proxy['host'], $proxy['port'], $proxy['user'], $proxy['pass']);   
        }
        $req->sendRequest();
            
        $respCode = $req->getResponseCode();
        if ($respCode == 200) {
            // check if we have a a last-modified response...                 
            if ($lastModifiedResponse = $req->getResponseHeader("last-modified")) {
                    
                $lastmodified = strtotime($lastModifiedResponse);
                // check if modified date changed, if yes, save it and touch the lastmodified file
                if ($lastmodified != $cacheFileLastModified_mtime) {
                    $this->simpleCacheWrite($cacheFile,"fullpath",null,$req->getResponseBody(),"plain");
                    touch($cacheFileLastModified,$lastmodified);
                }  
            } 
            /* if we don't have a last-modified header, we compare the md5 fingerprint to the one 
            we cached. This takes evt. more time, _but_ we first save one filewrite if it's the same
            and - more importantly - we can return the modified date of the first successfull
            retrieval. This will help a lot with st2xml and compo caching in popoon
            TODO: E-Tag caching
            */
            else {
                $_newcontent = $req->getResponseBody();
                if (file_exists($cacheFile)) {
                    $md5_oldcontent = md5($this->readFile($cacheFile));
                    $md5_newcontent = md5($_newcontent);
                } 
                else {
                    $md5_oldcontent = false;
                }
                    
                // if content is the same, we can return the mtime of the timestamp cache file
                if ($md5_oldcontent && $md5_oldcontent == $md5_newcontent) {
                    $lastmodified = $cacheFileLastModified_mtime;
                } 
                // otherwise write it and touch the lastmodified file
                else {
                        
                    $this->simpleCacheWrite($cacheFile,"fullpath",null,$_newcontent,"plain");
                    $lastmodified = time();
                    touch($cacheFileLastModified,$lastmodified);
                }
            }
            if (!touch($cacheFile, time())) {
                trigger_error("$cacheFile not touchable",E_USER_WARNING);
            }
            return $lastmodified;
        }

        // if a 304 came back, content didn't change... no need to get it, just touch the file
        if ($respCode == 304) {
            touch($cacheFile, time());
            return $cacheFileLastModified_mtime;
        }
        throw new Exception("SimpleCache HTTP Load Error. HTTP Error Code: $respCode", $respCode);
    }
    
    
    
    /**
    * reads a file and returns the content
    * 
    * file_get_contents is slightly faster than fopen/fread/fclose, but
    * only available in php4.3
    *
    * @param string $file 
    *
    * @return mixed string or boolean
    */
    function readFile($file)
    {
        return file_get_contents($file);
    }
    
    /**
     * creates a full path, similar to mkdir -p.
     *
     * @param string $path The path to create.
     *
     * @return void
     */
    function mkpath($path)
    {
        $dirs = explode("/", $path);
        $path = $dirs[0];

        for ($i = 1;$i < count($dirs);$i++) {
            $path .= "/" . $dirs[$i];
            if(!is_dir($path)) {
                mkdir($path);
            }
        }
    }
    
    
    /**
    * Deletes a directory and all files in it.
    *
    * @param    string  directory
    * @return   integer number of removed files
    * @throws   Boolean
    */
    function deleteDir($dir)
    {
        if (!($dh = opendir($dir))) {
            return false;
        }
        
        $num_removed = 0;
        $file = readdir($dh);
        while ($file !== false) {
            if ('.' == $file || '..' == $file) {
                $file = readdir($dh);
                continue;
            }
            
            $file = $dir . $file;
            if (is_dir($file)) {
                $file .= '/';
                $num = $this->deleteDir($file . '/');
                if (is_int($num))
                $num_removed += $num;
            } else {
                if (unlink($file))
                $num_removed++;
            }
            $file = readdir($dh);
        }
        // according to php-manual the following is needed for windows installations.
        closedir($dh);
        unset( $dh);
        
        if (realpath($dir) != realpath($this->cacheDir)) {  //delete the sub-dir entries  itself also, but not the cache-dir.
            rmDir($dir);
            $num_removed++;
        }
        
        return $num_removed;
    } // end func deleteDir   
}
