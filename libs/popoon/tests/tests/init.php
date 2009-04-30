<?php
chdir(dirname(__FILE__));
error_reporting(E_ALL);
chdir("../..");
define('BX_POPOON_DIR',getcwd()."/");
chdir(dirname(__FILE__));
@exec ("rm -r tmp");
@mkdir("tmp");
define('BX_PROJECT_DIR',dirname(__FILE__));

// for PHP5
if (version_compare("4.9.9",phpversion()) < 0) {
       $BX_config['popoon']['sm2php_xsl_dir'] = BX_POPOON_DIR.'sitemap/';
       include_once(BX_POPOON_DIR."/popoon.php");
} 

// for PHP4
// the only difference is, that BX_POPOON_DIR is different..
// and that we have to set the include path for BX_POPOON_DIR
else {
    ini_set("include_path",BX_POPOON_DIR.":".ini_get("include_path"));
    
    $BX_config['popoon']['sm2php_xsl_dir'] = BX_POPOON_DIR.'popoon/sitemap/';
    include_once("popoon/popoon.php");
}
?>
