<?php
// Get configuration
if (!include dirname(__FILE__) . '/../inc/config.inc.php') {
    die("No conf.");
}

// Init Router
try {
    $m = Net_URL_Mapper::getInstance();
    $m->connect('index', array('controller' => 'index', 'action' => 'index'));
    $m->connect('index/:from', array('controller' => 'index', 'action' => 'page'));

    #$m->setScriptName('router.php');
    #var_dump($_SERVER['REQUEST_URI']); exit;

    $match = $m->match($_SERVER['REQUEST_URI']);

    if ($match === null) {
        $match = array('controller' => 'planet', 'action' => 'index');
    }
} catch (Net_URL_Mapper_Exception $e) {
    die("Something went wrong.");
}

$planet = new PlanetPEAR;

$controller = 'PlanetPEAR_Controller_' . ucfirst(strtolower($match['controller']));

$controllerObj = new $controller($planet);

if (!isset($match['from'])) {
    $entries = call_user_func(array($controllerObj, $match['action']));
} else {
    $entries = call_user_func_array(array($controllerObj, $match['action']), array($match['from']));
}

$viewData = array(
    'BX_config' => $BX_config,
    'entries'   => $entries,
);

$planet->render('planet.tpl', $viewData);