<?php
// +----------------------------------------------------------------------+
// | Planet PHP                                                           |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003                                                   |
// +----------------------------------------------------------------------+
// | This program is subject to the GPL license.                          |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <developer@bitflux.ch>                          |
// +----------------------------------------------------------------------+
//
// : sitemap.php,v 1.5 2002/03/27 07:59:05 chregu Exp $
//apd_set_session_trace(35);
//print_r($_SERVER['REQUEST_METHOD']);
include('../inc/config.inc.php');
ini_set("log_errors",true); 
$BX_config['popoon']['sm2php_xsl_dir'] = BX_POPOON_DIR.'/popoon/sitemap';
$BX_config['popoon']['cacheDir'] = BX_PROJECT_DIR.'tmp/';
/*$BX_config['popoon']['cacheParams'] = array('cache_dir' => BX_PROJECT_DIR.'/tmp/cache','encoding_mode'=>'slash');
$BX_config['popoon']['cacheContainer'] = 'file';*/

include_once(BX_POPOON_DIR."/popoon/popoon.php");
if (isset($_GET['path'])) {
    $path =    $_GET['path'];
} else {
    $path = "";
}
$sitemap = new popoon (BX_PROJECT_DIR."/admin/sitemap/sitemap.xml",$path,
$BX_config['popoon']
);

?>
 
