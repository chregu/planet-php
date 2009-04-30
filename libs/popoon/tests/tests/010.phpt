--TEST--
Test 010: db2xml test
--SKIPIF--
--FILE--
<?php
include_once("init.php");

$sitemap = new popoon ("sitemap010.xml","/",$BX_config['popoon']);

?>
--EXPECT--
<root><result><row><id>1</id><name>The Blabbers</name><birth_year>1998</birth_year><birth_place>London</birth_place><genre>Rock'n'Roll</genre></row><row><id>2</id><name>Only Stupids</name><birth_year>1997</birth_year><birth_place>New York</birth_place><genre>Hip Hop</genre></row></result></root>
