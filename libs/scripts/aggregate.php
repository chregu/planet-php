#!/usr/bin/env php
<?php
include_once dirname(__FILE__) . '/../../inc/config.inc.php';
include_once 'aggregator.php';

$blogId = null;
if (isset($argv[1]) || !empty($argv[1])) {
    $blogId = (int) $argv[1];
    if ($blogId === 0) {
        echo "Usage ./aggregate.php <int>\n";
        exit(1);
    }
}

$agg = new aggregator();
$agg->aggregateAllBlogs($argv[1]);
if ($agg->isNew() === true) {
    $tmp = dirname(__FILE__) . '/../../tmp';
    foreach (glob($tmp . '/Index-*') as $file) {
        unlink($file);
    }
}

$noti = new lx_notifier();

$url = "http://www.planet-php.net/";
$topicurls = array(
        $url . 'atom/',
);

$hubs = array("http://pubsubhubbub.appspot.com");
$noti->addPubSubHubs($topicurls, $hubs);
    $noti->notifyAll();


