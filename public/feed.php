<?php
/**
 * Configuration file.
 */
if (!include dirname(__FILE__) . '/../inc/config.inc.php') {
    die("No conf.");
}

// BLOCK ACCESS TO FEED
if (defined('SECRET_FEED_HASH')) {
    if (SECRET_FEED_HASH != '') {
        if ($_GET['hash'] != SECRET_FEED_HASH) {
            die('Denied.');
        }
    }
}

require 'PlanetPEAR.php';
require 'PlanetPEAR/Feed.php';

try {
    $planet  = new PlanetPEAR;
    $results = $planet->getEntries();

    $planetFeed = new PlanetPEAR_Feed($_GET['type']);

    // retrieve entries
    foreach ($results as $result) {

        $entry = $planetFeed->createEntry();

        $entry->setTitle($result['title']); 
        $entry->setLink($result['link']);
        $entry->setDescription($result['description']);
        $entry->setGuid($result['guid']);
        $entry->setSource(array(
            'title' => $result['title'],
            'url'   => $result['link'],
        ));

        $planetFeed->addEntry($entry);
    }

    //var_dump($planetFeed); exit;

    echo $planetFeed;

} catch (Exception $e) {
    die('Feed fail.');
}
