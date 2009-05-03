<?php
include_once('../../inc/config.inc.php');
include_once('aggregator.php');
$starttime = time();

$agg = new aggregator();
if (isset($argv[1])) {
    
    $agg->aggregateAllBlogs($argv[1]);
} else {
    
    include_once("MDB2.php");
    
    $db =  MDB2::connect($GLOBALS['BX_config']['dsn']);
    
    $query = "select ID from feeds where active = 1 and get = 1 LIMIT 1";
    $hit = false;
    while ($id = $db->queryOne($query)) {
  	if(time() > $starttime+180 ) {     
        	print "timeout \n";
        	break;
    	}
        $query2 = "update feeds set active = 0 where ID = '$id'";
        $db->query($query2);
        $agg->aggregateAllBlogs($id);

        while (getLoad() > 4) {
            if(time() > $starttime+180 ) {
                print "timeout \n";
                break;  
            }         

            print "load > 4. wait\n";
            sleep(rand(5, 15));
        }
        $hit = true;
    }
    if ($hit) {
        sleep (5);
	exec("find /var/www/planet/tmp/cache/outputcache/ -mindepth 1 -exec  rm -rf {}  \; ");
    }
        

    
}
 

function getLoad() {
    return substr(file_get_contents("/proc/loadavg"),0,4);
}
