<?php
include("../inc/config.inc.php");

$db = MDB2::connect($GLOBALS['BX_config']['dsn']);
$db->query("set names 'utf8'");
$query  = "select id from (select links.id, count(entries_id) as c  from links left join entries2links on links_id = links.id group by links.id order by c ) as d where c = 0";


$ids = $db->queryCol($query);

print "Links to be deleted: ". count($ids);

$query = "delete from links where id in (".implode(",",$ids).")";
$db->query($query);

print "\n";

