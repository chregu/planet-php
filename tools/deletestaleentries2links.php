<?php
include("../inc/config.inc.php");

$db = MDB2::connect($GLOBALS['BX_config']['dsn']);
$db->query("set names 'utf8'");
$query  = "select entries2links.id from entries2links left join entries on entries.ID = entries_id where entries.ID is null;";
$ids = $db->queryCol($query);


print "entries from entries2links to be deleted: ". count($ids);
print "\n";
if (count($ids) >0) {
$query = "delete from entries2links where id in (".implode(",",$ids).")";
print "$query\n";
$db->query($query);

}

$query  = "select entries2links.id from entries2links left join links on links.ID = links_id where links.ID is null;";
$ids = $db->queryCol($query);

print "links from entries2links to be deleted: ". count($ids);

print "\n";
if (count($ids) >0) {
$query = "delete from entries2links where id in (".implode(",",$ids).")";
print "$query\n";
$db->query($query);
}







