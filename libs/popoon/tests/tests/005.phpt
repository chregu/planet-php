--TEST--
Test 005: phpprocessor transformer test
--SKIPIF--
--FILE--
<?php
include_once("init.php");

$sitemap = new popoon ("sitemap005.xml","/",$BX_config['popoon']);
?>
--EXPECT--
<html>
<head><title>sample2html</title></head>
<body>
<h1>
Hello World
</h1>
This is a PHP echo string
</body>
</html>
