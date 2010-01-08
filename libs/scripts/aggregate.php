<?php
include_once('../../inc/config.inc.php');
include_once('aggregator.php');

$agg = new aggregator();
$agg->aggregateAllBlogs($argv[1]);

$noti = new lx_notifier();

$url = "http://www.planet-php.net/";
$topicurls = array(
        $url . 'atom/',
);

$hubs = array("http://pubsubhubbub.appspot.com");
$noti->addPubSubHubs($topicurls, $hubs);
    $noti->notifyAll();

