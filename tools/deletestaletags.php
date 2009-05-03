<?php
include("../inc/config.inc.php");

$db = MDB2::connect($GLOBALS['BX_config']['dsn']);
$db->query("set names 'utf8'");
$query  = "select id from (select tags.id, count(entries_id) as c  from tags left join entries2tags on tags_id = tags.id group by tags.id order by c ) as d where c = 0";


$ids = $db->queryCol($query);

print "Tags to be deleted: ". count($ids);

$query = "delete from tags where id in (".implode(",",$ids).")";
$db->query($query);

print "\n";

