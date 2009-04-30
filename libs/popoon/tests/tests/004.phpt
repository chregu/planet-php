--TEST--
Test 004: libxslt transformer test
--SKIPIF--
--FILE--
<?php
include_once("init.php");

$sitemap = new popoon ("sitemap004.xml","/",$BX_config['popoon']);
?>
--EXPECT--
<html>
<head><title>Parameter Test</title></head>
<body>
<h1>
Hello World
</h1>
<br>
</body>
</html>
