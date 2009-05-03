<?php

$starttime = time();

 include_once("./inc/config.inc.php");
    include_once("MDB2.php");
    include_once("./libs/geo.php");
    $db = MDB2::connect($BX_config['dsn']);
mkdir('tmp/htdocs/');


mysql_select_db("planet_blogug_ch");
$res = mysql_query ("select link from blogs order by changed");
$urls = array();

while ($row = mysql_fetch_assoc($res)) {
    $a = array('html' =>   $row['link']);
    $urls[] = $a;  
}
/*$urls = array();
$urls[] = array('html' =>  'http://blog.bitflux.ch/');
*/
include_once("Benchmark/Timer.php");

$timer = new Benchmark_Timer;

$timer->start();

$STAT['badurls'] = array();
$STAT['badurls2'] = array();
$STAT['bad'] = 0;
$STAT['good'] = 0;
$mts = array();

get_multi_html($urls,false);


    

// add if needed
//doMagpie($urls);
$timer->stop();
foreach($mts as $file => $mt) {
     touch($file,$mt);
}
var_dump($STAT);
$prof = $timer->getProfiling();
foreach($prof as $val) {
    print $val["name"];
    print "\t";
    print $val["diff"];
    print "\n";
    
}
function get_multi_html($sites) {
    $limit = 30;
    global $timer,$starttime;
    print "Total Sites:". count($sites) ."\n";
    for($i = 0; $i <= count($sites); $i += $limit) {
        while (getLoad() > 2) {
            sleep(30);   
        }
        //die after 50 minutes
        if(time() > $starttime+3000 ) {
            print "timeout at $i\n";
            return;
        }
        $timer->setMarker("start $i");
        
       /* print "$i\n";
        print $i +$limit ."\n";
        */
        $urls =  array_slice($sites,$i,$limit);
        $urls2 = array();;
        foreach ($urls as $url) {
            $urls2[] = $url['html'];
        }
        _get_multi_html( $urls2 );
        $timer->setMarker("curl      $i");
        parse_html($urls); 
        $timer->setMarker("parse html $i");
        
    }
    
    
}

function _get_multi_html($urls) {
    global $STAT, $mts;
   
    $mh = curl_multi_init();
    $n = count($urls);
    foreach ($urls as $i => $url) {
        if (!$url) {
            continue;
        }
        $conn[$i]=curl_init($url);
        curl_setopt($conn[$i],CURLOPT_RETURNTRANSFER,1);//return data as string 
        curl_setopt($conn[$i],CURLOPT_FOLLOWLOCATION,1);//follow redirects
        curl_setopt($conn[$i],CURLOPT_MAXREDIRS,2);//maximum redirects
        //has to be set high, otherwise problems with not responding dns servers...
        // (takes the whole block down)
        curl_setopt($conn[$i],CURLOPT_CONNECTTIMEOUT,20);//timeout
        curl_setopt($conn[$i],CURLOPT_TIMEOUT,20);//timeout
        curl_setopt($conn[$i],CURLOPT_NOSIGNAL,true);
        curl_setopt($conn[$i],CURLOPT_FILETIME,true);
        curl_setopt($conn[$i],CURLOPT_USERAGENT,"ping.blogug.ch aggregator 1.0");
        
        $mt = @filemtime("tmp/htdocs/".md5($url).".xml");
        if ($mt) {
            
            curl_setopt($conn[$i],CURLOPT_TIMEVALUE,time());
        }
        $f[$i] = fopen("tmp/htdocs/".md5($url).".xml","w");
        curl_setopt($conn[$i],CURLOPT_FILE,$f[$i]);
        curl_multi_add_handle ($mh,$conn[$i]);
    }
    $i = 0;
    do { $n=curl_multi_exec($mh,$active);
        
        if ($i % 5000 == 0) {
            print "timer $i\n";
            
        }
        $i++;
        usleep(100);
    } while ($active);
   
    foreach ($urls as $i => $url) {
        if (!$url) {
            continue;
        }
        $res[$i]=curl_multi_getcontent($conn[$i]);
        curl_multi_remove_handle($mh,$conn[$i]);
        $foo = curl_getinfo($conn[$i]);
        
        $mt = $foo['filetime'];
        
        
        if ($foo['http_code'] == 200) {
            /*print "good $url";
            print "\n";*/
             $STAT['good'] ++;
        } else {
            
            $STAT['badurls'][] = $url ." ". $foo['http_code'] ;
            $STAT['badurls2'][] = $url;
            $STAT['bad'] ++;
            //unset($urls[$i]);
           
        }
        
        curl_close($conn[$i]);
        //curl_multi_remove_handle($mh,$conn[$i]);
        
        if ($mt > 0) {
            $mts["tmp/htdocs/".md5($url).".xml"] = $mt;
            
        }
        
    }
    
    curl_multi_close($mh);
    return $urls;
    
 
    
}





function parse_html($urls) {
    global $STAT;
    $z = 0;
    
    foreach($urls as $k => $urla) {
        $url = $urla['html'];
        if (!$url or !is_numeric($k)) {
            continue;
        }
        
        if (in_array($url,$STAT['badurls2'])) {
            print "BAD $url\n";
            continue;
        }
        $file = 'tmp/htdocs/'.md5($url).'.xml';
        if (filesize($file) < 10) {
            continue;
        }
        $z++;
        
        $dom = new domdocument();
        $dom->recover=true;
	$dom->preserveWhiteSpace = false;
        /*print "\n". $url."\n";
        print $file."\n";
        */
        //print $content;
        $md5 = md5($content);
        //$md5 = md5_file($file);
        //file_put_contents("/tmp/foo.dat",strip_tags(file_get_contents($file)));
        if (!@$dom->loadHTMLFile($file)) {
            continue;
        }
        $xp = new domxpath($dom);
	$res = $xp->query('/html/head/meta');
	unset($geo);
	foreach ($res as $node) {
		if( $nodeName = $node->getAttribute('name')) {
			if ((stripos($nodeName, 'icbm') !== false) || (stripos($nodeName, 'geo.position') !== false)) {
				$geoStr = $node->getAttribute('content');
				/*$geo = split('[,;]', $geo);
				if (count($geo) == 1) {
					$geo = split(' ', $geo[0]);
				}*/
				if( ! preg_match_all('/(-?\d{1,2}\.\d{2,4})/', $geoStr, $geo) ) {
				    print "GEO REFERENCE WRONG! ";
				    print '"'.$geoStr.'" ';
				    print " $url ";
				    print "\n";
				}

			}
		}
	}
        if (!$geo) {
            print "GEO REFERENCE NOT FOUND! ";
            print " $url ";
            print "\n";
        } else {
				$lat         = floatval(mysql_escape_string(trim($geo[0][0])));
				$lon         = floatval(mysql_escape_string(trim($geo[0][1])));
				$locationArr = getLocalData($lat, $lon);
				$query = "update blogs set lon = '".$lon."',  lat = '".$lat."', city = '".$locationArr['city']."', canton = '".$locationArr['canton']."', country='".$locationArr['country']."', continent='".$locationArr['continent']."' where link = '".mysql_escape_string($url)."'";
				mysql_query($query);
	}
        
    }
}

    
function getLoad() {
    return substr(file_get_contents("/proc/loadavg"),0,4);
}
?>

