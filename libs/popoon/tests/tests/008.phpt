--TEST--
Test 008: simple action test
--SKIPIF--
--FILE--
<?php
include_once("init.php");

// use ob_start, because otherwise second popoon run complains about already sent headers
ob_start();

//without user set
$sitemap = new popoon ("sitemap008.xml","/",$BX_config['popoon']);

//set user
$_GET["user"] = "foobar";
$sitemap = new popoon ("sitemap008.xml","/",$BX_config['popoon']);


ob_end_flush();

?>
--EXPECT--
<xml>fff</xml>
<html>
<head>popoon test</head>
<body>
<p>Hello World</p>
</body>
</html>
