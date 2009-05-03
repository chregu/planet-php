<?php

$groups = array(
	'Nucleus%' => 'Nucleus',
	'%movabletype%' => "Moveable Type",
	"Blogger%" => "Blogger",
	'%kaywa%' => 'Kaywa',
	'%wordpress%'=>'WordPress',
	"%serendipity%" => "Serendipity",
	"Flux%" => "Flux CMS",
	"Movable Type%" => "Moveable Type",
	"U-blog%" => "U-blog",
	"PostNuke%" => "PostNuke",
	'%typepad.com%' => "TypePad",
	'FutureBlogs%' => "FutureBlogs",
	'FeedCreator%' => 'FeedCreator',
	'iBlog%' => 'iBlog'
);



include("../inc/config.inc.php");

$db = MDB2::connect($GLOBALS['BX_config']['dsn']);
$db->query("set names 'utf8'");

$query = "insert into generators (name, gengroup) select  generator,generator from blogs left join generators on generator = name where generator != '' and isnull(name) group by generator;";

$db->query($query);


foreach ($groups as $q => $group) {
$query = "update generators set gengroup = '$group' where name like '$q' and gengroup != '$group'";
$res = $db->query($query);

print "$q => $group \n";
}
print "\n";

$query = "select count(generator) as c , gengroup  from blogs left join generators on generator = name  where inopml = 1 and generator != '' group by gengroup order by c;";
$res = $db->query($query);
$z = 0;
while ($row = $res->fetchRow()) {
	$z += $row[0];
	print $row[0] . " " .$row[1] ."\n";
}

print "$z total\n";
