#!/usr/bin/env php
<?php
include_once dirname(__FILE__) . '/../../inc/config.inc.php';
include_once 'aggregator.php';

if (!isset($argv[1]) || empty($argv[1])) {
    echo "Usage ./aggregate.php foo\n";
    exit(1);
}

$agg = new aggregator();
$agg->aggregateAllBlogs($argv[1]);

