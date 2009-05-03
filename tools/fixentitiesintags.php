<?php

include("../inc/config.inc.php");

$db = MDB2::connect($GLOBALS['BX_config']['dsn']);
$db->query("set names 'utf8'");
$query  = "select * from tags where tag like '%&#%'";

$res = $db->query($query);

while ($row = $res->fetchROW(MDB2_FETCHMODE_ASSOC)) {

	$query ="update tags set tag = ".$db->quote(html_entity_decode($row['tag'],ENT_NOQUOTES,'utf-8'))." where id = ".$row['id'];
	print $query ."\n";
	$db->query($query);
}

