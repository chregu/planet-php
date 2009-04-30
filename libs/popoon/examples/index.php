<?php

//new interface
if (!isset($_GET['path'])) {
    
    $_GET['path'] = "";
    
}
include_once("../popoon.php");
$sitemap = new popoon ("examples_sitemap.xml", $_GET['path']);

//old interface
/*include_once("popoon/sitemap.php");
$sitemap = new sitemap ("examples_sitemap.xml");
*/


?>
