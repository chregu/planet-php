<?php
include("../inc/config.inc.php");

$db = MDB2::connect($GLOBALS['BX_config']['dsn']);
$db->query("set names 'utf8'");
$query  = "select id from (select words.id, count(entries_id) as c  from words left join entries2words on words_id = words.id group by words.id order by c ) as d where c = 0";


$ids = $db->queryCol($query);

print "Word to be deleted: ". count($ids);

$query = "delete from words where id in (".implode(",",$ids).")";
$db->query($query);

print "\n";

