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
/*
if ($_SERVER['REMOTE_ADDR'] != "80.218.7.144") {
    header("HTTP/1.1 503 Service Temporarily Unavailable");
	print "<h1>503 Service Temporarily Unavailable</h1>";
    print "Software Maintenance. Please come back in a few minutes";
    die();
}
*/
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // FIXME

ini_set("display_errors", 1); // FIXME: remove

if (!include dirname(__FILE__) . '/../inc/config.inc.php') {
    die("No conf.");
}

ini_set("log_errors", true); // FIXME: expensive

require BX_POPOON_DIR . 'popoon.php';

$sitemap = new popoon(
    BX_PROJECT_DIR . '/sitemap/sitemap.xml',
    '',
    NULL
);
