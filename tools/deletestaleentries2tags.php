<?php
include("../inc/config.inc.php");

$db = MDB2::connect($GLOBALS['BX_config']['dsn']);
$db->query("set names 'utf8'");
$query  = "select entries2tags.id from entries2tags left join entries on entries.ID = entries_id where entries.ID is null;";
$ids = $db->queryCol($query);


print "entries from entries2tags to be deleted: ". count($ids);
print "\n";
if (count($ids) >0) {
$query = "delete from entries2tags where id in (".implode(",",$ids).")";
print "$query\n";
$db->query($query);

}

$query  = "select entries2tags.id from entries2tags left join tags on tags.ID = tags_id where tags.ID is null;";
$ids = $db->queryCol($query);

print "tags from entries2tags to be deleted: ". count($ids);

print "\n";
if (count($ids) >0) {
$query = "delete from entries2tags where id in (".implode(",",$ids).")";
print "$query\n";
$db->query($query);
}







