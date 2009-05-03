<?php
include("../inc/config.inc.php");

$db = MDB2::connect($GLOBALS['BX_config']['dsn']);
$db->query("set names 'utf8'");
$query  = "select entries2words.id from entries2words left join entries on entries.ID = entries_id where entries.ID is null;";
$ids = $db->queryCol($query);


print "entries from entries2words to be deleted: ". count($ids);
print "\n";
if (count($ids) >0) {
$query = "delete from entries2words where id in (".implode(",",$ids).")";
print "$query\n";
$db->query($query);

}

$query  = "select entries2words.id from entries2words left join words on words.ID = words_id where words.ID is null;";
$ids = $db->queryCol($query);

print "words from entries2words to be deleted: ". count($ids);

print "\n";
if (count($ids) >0) {
$query = "delete from entries2words where id in (".implode(",",$ids).")";
print "$query\n";
$db->query($query);
}







