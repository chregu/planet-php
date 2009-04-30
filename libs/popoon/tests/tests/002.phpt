--TEST--
Test 002: xslt test
--SKIPIF--
--FILE--
<?php
include_once("init.php");

$sitemap = new popoon ("sitemap002.xml","/",$BX_config['popoon']);
?>
--EXPECT--
<html>
<head><title>sample2html</title></head>
<body>
<h1>
Hello World
</h1>
<br>
</body>
</html>
