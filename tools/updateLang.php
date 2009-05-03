<?php
include_once("../inc/config.inc.php");
include_once("MDB2.php");
$db = MDB2::connect($BX_config['dsn']);


require_once 'Text/LanguageDetect.php';

$l = new Text_LanguageDetect;


$short = array('english' => 'en', 'german' => 'de', 'french' => 'fr', 'italian' => 'it');//,'croatian' => 'cr');
$l->omitLanguages(array_keys($short),true);

$query = " select id, link, content_encoded, title from entries where lang  = ''";

$res = $db->query($query);

while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    
    
    /*print $row['title'] . "\n";
	print $row['link'] ."\n";*/
    $lang =  getLang($row['title']. ' ' . $row['content_encoded'],$short,$l);
    print ".";
    $query = "update entries set lang = '$lang' where id = ".$row['id'];
    $db->query($query);
}


function getLang($text,$short,$l) {
    
    $lang = $l->detect($text,1);
    $lang = array_keys($lang);

    if (isset($lang[0])) {
        return $short[$lang[0]];
    }
	
    return "nn";
    
}
