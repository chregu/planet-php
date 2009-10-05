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
} else {
    $query = null;
}

if (!isset($match['from'])) {
    $from = 0;
} else {
    $from = (int) $match['from'];
}

$planet = new PlanetPEAR;
$planet->setController($match['controller']);
$planet->setAction($match['action']);
$planet->setFrom($from);
$planet->setQuery($query);

$cacheName = $planet->getCacheName();
$cacheFile = BX_TEMP_DIR . '/' . $cacheName;

if (!file_exists($cacheFile)) {

    ob_start();

    $controller    = 'PlanetPEAR_Controller_' . $planet->getController();
    $controllerObj = new $controller($planet);

    try {
        $viewData = call_user_func_array(array($controllerObj, $match['action']), array($from, $query));

        $viewData['blogs']     = $planet->getBlogs();
        $viewData['BX_config'] = $BX_config;

        if ($planet->isQuery() === false) {
            $viewData['nav'] = $planet->getNavigation($from);
        } else {
            $viewData['nav'] = array('prev' => null, 'next' => null);
        }

        $planet->render('planet.tpl', $viewData);

    } catch (Exception $e) {
        die("Just come back later.");
    }
    $page = ob_get_contents();
    ob_end_clean();

    $fp = fopen($cacheFile, 'w');
    if ($fp) {
        fwrite($fp, $page);
        fclose($fp);
    }
    echo $page;
} else {
    echo file_get_contents($cacheFile);
}