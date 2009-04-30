--TEST--
Test 007: aggregator with http test
--SKIPIF--
--FILE--
<?php
include_once("init.php");

$sitemap = new popoon ("sitemap007.xml","/",$BX_config['popoon']);
?>
--EXPECT--
<?xml version="1.0"?>
<rdfs><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://my.netscape.com/rdf/simple/0.9/">



<channel>

<title>Slashdot</title>

<link>http://slashdot.org/</link>

<description>News for nerds, stuff that matters</description>

</channel>



<image>

<title>Slashdot</title>

<url>http://images.slashdot.org/topics/topicslashdot.gif</url>

<link>http://slashdot.org/</link>

</image>



<item>

<title>Toyota Offers Automatic Parallel Parking Option</title>

<link>http://slashdot.org/article.pl?sid=04/01/20/018227</link>

</item>



<item>

<title>'Bagle' Worm Heading For A Windows PC Near You</title>

<link>http://slashdot.org/article.pl?sid=04/01/20/0558222</link>

</item>





</rdf:RDF><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://my.netscape.com/rdf/simple/0.9/">

<channel>

<title>php-homepage.de</title>

<link>http://www.php-homepage.de</link>

<description>php-homepage.de - Die deutschsprachige Ressource f&#xFC;r PHP und MySQL</description>

<language>de</language>

</channel>

<item>

<title>CfP LinuxTag 2004</title>

<link>http://www.php-homepage.de/?news=353</link>

</item>

<item>

<title>Porgramm der International PHP Conference</title>

<link>http://www.php-homepage.de/?news=352</link>

</item>

</rdf:RDF></rdfs>
