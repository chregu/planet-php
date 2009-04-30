<?php


class popoon_classes_browser {
    
    static private $BrowserName = "Unknown";
    static private $BrowserSubName = "None";
    static private $Version = "Unknown";
    static private $Platform = "Unknown";
    static private $UserAgent = "Not reported";
    
    static private $initialized = false;
    static private $parsed = false;
    
    private function __construct() {}
    
    static function init() {
        if (!self::$initialized) {
            if (isset( $_SERVER['HTTP_USER_AGENT'])) {
                self::$UserAgent = $_SERVER['HTTP_USER_AGENT'];
            }
            self::$initialized = true;
        }
    }
    
    
    static function isMozilla() {
        return( self::getName() == "mozilla");
    }
    
    static function isMozillaAndHasMidas() {
        if (self::getName() == "mozilla")
        if (stripos(self::$UserAgent,"camino/0.8.")) {
            return false;
        } else {
            return true;
        }
        return false;
    }
    
    
    
    
    static function hasBadCss() {
           self::init();
           $name = self::getName();
           $version = self::getVersion();
           print "$name\n";
           print "$version\n";
           if ($name == "opera" && $version < 8) {
               return true;
           }
           if ($name == "mozilla" && $version < 5) {
               return true;
           }
           return false;
           
    }
    static function isPalm() {
        return (self::getPlatform()=="palm");
    }
    
    static function isMSIEWin() {
        return( self::getName() == "msie" && self::getPlatform()=="windows");
    }
    
    static function isSafari() {
        return( self::getName() == "safari" );
    }
    
    static function getName() {
        self::parse();
        return self::$BrowserName;
    }
    static function getSubName() {
        self::parse();
        return self::$BrowserSubName;
    }
    
    static function getVersion() {
        self::parse();
        return self::$Version;
    }
    
    static function getPlatform() {
        self::parse();
        return self::$Platform;
    }
    
    static function getAgent() {
        self::init();
        return self::$UserAgent;
    }
    
    static function parse(){
        
        if (!self::$parsed) {
            self::init();
            $agent = self::$UserAgent;
            // initialize properties
            $bd['platform'] = "Unknown";
            $bd['browser'] = "Unknown";
            $bd['version'] = "Unknown";
            
            
            // find operating system
            if (eregi("win", $agent)) {
                $bd['platform'] = "windows";
            } elseif (eregi("mac", $agent)) {
                $bd['platform'] = "macintosh";
            } elseif (eregi("linux", $agent)) {
                $bd['platform'] = "linux";
            } elseif (eregi("OS/2", $agent)) {
                $bd['platform'] = "os/2";
            } elseif (eregi("BeOS", $agent)) {
                $bd['platform'] = "beos";
            } elseif (stripos($agent,'PalmOS') !== false) {
                $bd['platform'] = "palm";
            }
            // test for Opera		
            if (eregi("opera",$agent)){
                $val = stristr($agent, "opera");
                if (eregi("/", $val)){
                    $val = explode("/",$val);
                    $bd['browser'] = $val[0];
                    $val = explode(" ",$val[1]);
                    $bd['version'] = $val[0];
                }else{
                    $val = explode(" ",stristr($val,"opera"));
                    $bd['browser'] = $val[0];
                    $bd['version'] = $val[1];
                }
                
                // test for WebTV
            }elseif(eregi("msie",$agent) ){
                $val = explode(" ",stristr($agent,"msie"));
                $bd['browser'] = $val[0];
                $bd['version'] = $val[1];
                
            }
            elseif(eregi("galeon",$agent)){
                $val = explode(" ",stristr($agent,"galeon"));
                $val = explode("/",$val[0]);
                $bd['browser'] = "Mozilla";
                $bd['version'] = $val[1];
                $bd['subbrowser']=$val[0];
                
                // test for Konqueror
            }elseif(eregi("Konqueror",$agent)){
                $val = explode(" ",stristr($agent,"Konqueror"));
                $val = explode("/",$val[0]);
                $bd['browser'] = $val[0];
                $bd['version'] = $val[1];
                
            }elseif(eregi("firebird", $agent)){
                $bd['browser']="Mozilla";
                $bd['subbrowser']="Firefox";
                $val = stristr($agent, "Firebird");
                $val = explode("/",$val);
                $bd['version'] = $val[1];
                
                // test for Firefox
            }elseif(eregi("Firefox", $agent)){
                $bd['browser']="Mozilla";
                $bd['subbrowser'] = "Firefox";
                $val = stristr($agent, "Firefox");
                
                $val = explode("/",$val);
                $bd['version'] = $val[1];
            } elseif(eregi("mozilla",$agent) && eregi("rv:[0-9]\.[0-9]",$agent) && !eregi("netscape",$agent)){
                $bd['browser'] = "Mozilla";
                $bd['subbrowser'] = "Mozilla";
                $val = explode(" ",stristr($agent,"rv:"));
                eregi("rv:[0-9]\.[0-9]\.[0-9]",$agent,$val);
                $bd['version'] = str_replace("rv:","",$val[0]);
                
            }elseif(eregi("safari", $agent)){
                $bd['browser'] = "Safari";
                $val = substr($agent,strpos($agent,"Safari/") + 7);
                $bd['version'] = $val;
                
                // remaining two tests are for Netscape
            }elseif(eregi("netscape",$agent)){
                $val = explode(" ",stristr($agent,"netscape"));
                $val = explode("/",$val[0]);
                $bd['browser'] = $val[0];
                $bd['version'] = $val[1];
                
            }elseif(eregi("mozilla",$agent) && !eregi("rv:[0-9]\.[0-9]\.[0-9]",$agent)){
                $val = explode(" ",stristr($agent,"mozilla"));
                $val = explode("/",$val[0]);
                $bd['browser'] = "Mozilla";
                $bd['subbrowser'] = "Netscape";
                $bd['version'] = $val[1];
            }
            
            // clean up extraneous garbage that may be in the name
            $bd['browser'] = ereg_replace("[^a-z,A-Z]", "", $bd['browser']);
            // clean up extraneous garbage that may be in the version		
            $bd['version'] = ereg_replace("[^0-9,.,a-z,A-Z]", "", $bd['version']);

            // finally assign our properties
            self::$BrowserName = strtolower($bd['browser']);
            if (isset($bd['subbrowser'])) {
                self::$BrowserSubName = strtolower($bd['subbrowser']);
            }
            self::$Version = $bd['version'];
            self::$Platform = $bd['platform'];
            self::$parsed = true;
        }
    }
}
?>
