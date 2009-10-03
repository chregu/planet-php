<?php
// Get configuration
if (!include dirname(__FILE__) . '/../inc/config.inc.php') {
    die("No conf.");
}

// Init Router
try {
    $m = Net_URL_Mapper::getInstance();
    $m->connect('opml', array('controller' => 'index', 'action' => 'opml'));
    $m->connect('index', array('controller' => 'index', 'action' => 'index'));
    $m->connect('index/:from', array('controller' => 'index', 'action' => 'page'));

    #$m->setScriptName('router.php');
    #var_dump($_SERVER['REQUEST_URI']); exit;

    $match = $m->match($_SERVER['REQUEST_URI']);

    if ($match === null) {
        $match = array('controller' => 'index', 'action' => 'index');
    }
} catch (Net_URL_Mapper_Exception $e) {
    die("Something went wrong.");
}

$query = (string) @$_GET['search'];
if (!empty($query)) {
    $match['action'] = 'page';
}

$planet = new PlanetPEAR;

$controller = 'PlanetPEAR_Controller_' . ucfirst(strtolower($match['controller']));

$controllerObj = new $controller($planet);

if (!isset($match['from'])) {
    $from = 0;
} else {
    $from = (int) $match['from'];
}

try {
    $viewData = call_user_func_array(array($controllerObj, $match['action']), array($from, $query));

    $viewData['blogs']     = $planet->getBlogs();
    $viewData['BX_config'] = $BX_config;
    $viewData['nav']       = $planet->getNavigation($from);

    $planet->render('planet.tpl', $viewData);

} catch (Exception $e) {
    die("Just come back later.");
}