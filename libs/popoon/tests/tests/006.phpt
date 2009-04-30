--TEST--
Test 006: aggregator test
--SKIPIF--
--FILE--
<?php
include_once("init.php");

$sitemap = new popoon ("sitemap006.xml","/",$BX_config['popoon']);
?>
--EXPECT--
<?xml version="1.0"?>
<s:slideset xmlns:s="http://www.oscom.org/2002/SlideML/0.9/"><s:slide xmlns:s="http://www.oscom.org/2002/SlideML/0.9/"><s:title>Fragen?</s:title><s:content>Nur keine Hemmungen</s:content></s:slide><s:slide xmlns:s="http://www.oscom.org/2002/SlideML/0.9/"><s:title>Was ist Popoon?</s:title><s:content>
            
            <ul xmlns="http://www.w3.org/1999/xhtml">
                <li>XML Publishing Framework </li>
                <li>Pipeline Processing f&#xFC;r XML-Dokumente</li>
                <li>Sehr modular</li>
                <li>Backend f&#xFC;r Bitflux CMS (aber nicht nur)</li>
                <li>Popoon basiert auf Ideen von Apaches Cocoon</li>
            </ul>
        </s:content></s:slide></s:slideset>
