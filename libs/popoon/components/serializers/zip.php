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
* Outputs a ZIP archive
*
* @author   Iván Montes <imontes@imaginocreativa.com>
* @package  popoon
* @todo     implement some kind of streaming so we don't consume lots of memory
*           when compressing large amounts of data.
*
*/
class popoon_components_serializers_zip extends popoon_components_serializer {
    
    public $XmlFormat = "DomDocument";
    protected $contentType = "application/zip";
    
    function __construct (&$sitemap) {
        $this->sitemap = $sitemap;
    }
    
    function init($attribs) {
        parent::init($attribs);
    }
    
    function DomStart(&$xml)
    {
        parent::DomStart($xml);
        
        
        if (! is_object($xml))
        {
            $zipXML = new DOMDocument();
            $zipXML->loadXML($xml);
        } else {
            $zipXML = $xml;
        }
        
        $xpath = new DOMXPath($zipXML);
        
        $xpath->registerNamespace('zip', 'http://apache.org/cocoon/zip-archive/1.0');
        $entries = $xpath->query('/zip:archive/zip:entry');
        
        $zip = new zipfile();
        
        foreach ($entries as $entry) {
            
            if ($entry->getAttribute('src')) {
                $src = $entry->getAttribute('src');
                
                //only get files, the ZIP format doesn't need directory entries
                if ($src[ strlen($src)-1 ] !== '/') {
                    $uri = parse_url($src);
                    if ($uri['scheme'] == 'http') {
                        $simplecache = new popoon_helpers_simplecache();
                        $data = $simplecache->simpleCacheHttpRead($src,1600);
                    } else {
                        //check if it's a popoon scheme (BX_PROJECT_DIR for example)
                        if (file_exists(BX_POPOON_DIR.'components/schemes/'.$uri['scheme'].'.php')) {
                            include_once(BX_POPOON_DIR.'components/schemes/'.$uri['scheme'].'.php');
                            $func = 'scheme_'.$uri['scheme'];
                            if (function_exists($func)) {
                                $src = $func($uri['host'].$uri['path']);
                            }
                        }
                        
                        //try to get the contents
                        $data = @file_get_contents($src);
                    }
                    
                    $zip->addFile( $data, $entry->getAttribute('name') );
                }
            } else {
                //parse the node and convert it into a string
                $data = '';
                foreach ($entry->childNodes as $child) {
                    $data .= $zipXML->saveXML($child);
                }
                
                //if a serializer was specified then use it
                //!!!Should we handle here any errors?
                if ($serializer = $entry->getAttribute('serializer')) {
                    $serializer = "popoon_components_serializers_".$serializer;
                    $objSerializer = new $serializer($this->sitemap);
                    $objSerializer->init( null );
                    ob_start();
                    $objSerializer->DomStart($data);
                    $data = ob_get_contents();
                    ob_end_clean();
                }
                
                $zip->addFile( $data, $entry->getAttribute('name') );
            }
        }
        
        print $zip->file();
    }
}


/**
* Zip file creation class.
* Makes zip files.
*
* Based on :
*
*  http://www.zend.com/codex.php3?id=535&single=1
*  By Eric Mueller <eric@themepark.com>
*
*  http://www.zend.com/codex.php3?id=470&single=1
*  by Denis125 <webmaster@atlant.ru>
*
*  a patch from Peter Listiak <mlady@users.sourceforge.net> for last modified
*  date and time of the compressed file
*
* Official ZIP file format: http://www.pkware.com/appnote.txt
*
* @access  public
*/
class zipfile
{
    /**
    * Array to store compressed data
    *
    * @var  array    $datasec
    */
    var $datasec      = array();
    
    /**
    * Central directory
    *
    * @var  array    $ctrl_dir
    */
    var $ctrl_dir     = array();
    
    /**
    * End of central directory record
    *
    * @var  string   $eof_ctrl_dir
    */
    var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
    
    /**
    * Last offset position
    *
    * @var  integer  $old_offset
    */
    var $old_offset   = 0;
    
    
    /**
    * Converts an Unix timestamp to a four byte DOS date and time format (date
    * in high two bytes, time in low two bytes allowing magnitude comparison).
    *
    * @param  integer  the current Unix timestamp
    *
    * @return integer  the current date in a four byte DOS format
    *
    * @access private
    */
    function unix2DosTime($unixtime = 0) {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
        
        if ($timearray['year'] < 1980) {
            $timearray['year']    = 1980;
            $timearray['mon']     = 1;
            $timearray['mday']    = 1;
            $timearray['hours']   = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        } // end if
        
        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
        ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    } // end of the 'unix2DosTime()' method
    
    
    /**
    * Adds "file" to archive
    *
    * @param  string   file contents
    * @param  string   name of the file in the archive (may contains the path)
    * @param  integer  the current timestamp
    *
    * @access public
    */
    function addFile($data, $name, $time = 0)
    {
        $name     = str_replace('\\', '/', $name);
        
        $dtime    = dechex($this->unix2DosTime($time));
        $hexdtime = '\x' . $dtime[6] . $dtime[7]
        . '\x' . $dtime[4] . $dtime[5]
        . '\x' . $dtime[2] . $dtime[3]
        . '\x' . $dtime[0] . $dtime[1];
        eval('$hexdtime = "' . $hexdtime . '";');
        
        $fr   = "\x50\x4b\x03\x04";
        $fr   .= "\x14\x00";            // ver needed to extract
        $fr   .= "\x00\x00";            // gen purpose bit flag
        $fr   .= "\x08\x00";            // compression method
        $fr   .= $hexdtime;             // last mod time and date
        
        // "local file header" segment
        $unc_len = strlen($data);
        $crc     = crc32($data);
        $zdata   = gzcompress($data);
        $zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
        $c_len   = strlen($zdata);
        $fr      .= pack('V', $crc);             // crc32
        $fr      .= pack('V', $c_len);           // compressed filesize
        $fr      .= pack('V', $unc_len);         // uncompressed filesize
        $fr      .= pack('v', strlen($name));    // length of filename
        $fr      .= pack('v', 0);                // extra field length
        $fr      .= $name;
        
        // "file data" segment
        $fr .= $zdata;
        
        // "data descriptor" segment (optional but necessary if archive is not
        // served as file)
        $fr .= pack('V', $crc);                 // crc32
        $fr .= pack('V', $c_len);               // compressed filesize
        $fr .= pack('V', $unc_len);             // uncompressed filesize
        
        // add this entry to array
        $this -> datasec[] = $fr;
        $new_offset        = strlen(implode('', $this->datasec));
        
        // now add to central directory record
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .= "\x00\x00";                // version made by
        $cdrec .= "\x14\x00";                // version needed to extract
        $cdrec .= "\x00\x00";                // gen purpose bit flag
        $cdrec .= "\x08\x00";                // compression method
        $cdrec .= $hexdtime;                 // last mod time & date
        $cdrec .= pack('V', $crc);           // crc32
        $cdrec .= pack('V', $c_len);         // compressed filesize
        $cdrec .= pack('V', $unc_len);       // uncompressed filesize
        $cdrec .= pack('v', strlen($name) ); // length of filename
        $cdrec .= pack('v', 0 );             // extra field length
        $cdrec .= pack('v', 0 );             // file comment length
        $cdrec .= pack('v', 0 );             // disk number start
        $cdrec .= pack('v', 0 );             // internal file attributes
        $cdrec .= pack('V', 32 );            // external file attributes - 'archive' bit set
        
        $cdrec .= pack('V', $this -> old_offset ); // relative offset of local header
        $this -> old_offset = $new_offset;
        
        $cdrec .= $name;
        
        // optional extra field, file comment goes here
        // save to central directory
        $this -> ctrl_dir[] = $cdrec;
    } // end of the 'addFile()' method
    
    
    /**
    * Dumps out file
    *
    * @return  string  the zipped file
    *
    * @access public
    */
    function file()
    {
        $data    = implode('', $this -> datasec);
        $ctrldir = implode('', $this -> ctrl_dir);
        
        return
        $data .
        $ctrldir .
        $this -> eof_ctrl_dir .
        pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries "on this disk"
        pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries overall
        pack('V', strlen($ctrldir)) .           // size of central dir
        pack('V', strlen($data)) .              // offset to start of central dir
        "\x00\x00";                             // .zip file comment length
    } // end of the 'file()' method
    
} // end of the 'zipfile' class
?>