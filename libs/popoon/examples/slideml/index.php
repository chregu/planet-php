<?php
error_reporting(E_ALL);

define('BX_PROJECT_DIR', getcwd());

include_once("popoon/sitemap.php");

$sitemap =& new sitemap ("./sitemap_mod.xml");
?>