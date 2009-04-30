<?php

    require_once "HTTP/WebDAV/Server/Filesystem.php";
    
    /**
     * Filesystem access using WebDAV
     *
     * @access public
     */
    class HTTP_WebDAV_Server_Popoon extends HTTP_WebDAV_Server_Filesystem
    {
        
        /**
         * PUT method handler
         * 
         * @param  array  parameter passing array
         * @return bool   true on success
         */
        function PUT(&$options) 
        {
            include_once("popoon/streams/bx.php");
            stream_wrapper_register("bx", "bxStream");

            $fspath =  $options["path"];
/*
            if(!@is_dir(dirname($fspath))) {
                return "409 Conflict";
            }
*/
            
            $streamtype = $this->getStreamType($options["path"]);
            
            
            if ($streamtype) {
                $fspath = "$streamtype:/".$fspath;
            }

            //$options["new"] = ! file_exists($fspath);
            $options["new"] = false;
            $fp = fopen($fspath, "w");
            
            return $fp;
        }
        
        function getStreamType($path) {
          
          $extension = substr(trim($path),strrpos(trim($path),".")+1);
          switch($extension) {
              case "html":
                include_once("popoon/streams/tidy.php");
                stream_wrapper_register("tidy", "TidyStream");
                return "tidy";
                break;
              case "sxw":
                include_once("popoon/streams/ooo.php");
                stream_wrapper_register("ooo", "OooStream");
                return "ooo";
              case "wiki":
                include_once("popoon/streams/wiki.php");
                stream_wrapper_register("wiki", "WikiStream");
                return "wiki";  
              default:
                return null;
          }
              
        }
        
        
    }
    
    ?>