<?php
include("../inc/config.inc.php");

$db = MDB2::connect($GLOBALS['BX_config']['dsn']);
$db->query("set names 'utf8'");


$query = "delete from entries where link = '';";

$db->query($query);


$query = "select  id from (select count(*) as c, min(id) as id from entries  group by dc_date,title,feedsID ) as d where d.c > 1;";

$z = 0;
foreach ($db->queryCol($query) as $id) {
$z++;
	$query = "DELETE LOW_PRIORITY from entries where ID = $id;";
	$db->query($query);
	print "$id ";
}




print "\n" . $z . " entries deleted\n";
