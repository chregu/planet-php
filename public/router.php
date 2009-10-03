<?php
require_once 'Net/URL/Mapper.php';

// Init Router
$m = Net_URL_Mapper::getInstance();
$m->connect('index', array('controller' => 'planet', 'action' => 'index'));
$m->connect('index/:from', array('controller' => 'planet', 'action' => 'page'));

#$m->setScriptName('router.php');
#var_dump($_SERVER['REQUEST_URI']); exit;

$match = $m->match($_SERVER['REQUEST_URI']);

if ($match === null) {
    die("MATCHED NULL");
}
var_dump($match);