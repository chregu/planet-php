<?php

$starttime = time();

   include_once("./inc/config.inc.php");
    include_once("MDB2.php");
    include_once("./libs/geo.php");
    $db = MDB2::connect($BX_config['dsn']);
    //$db = MDB2::connect('mysql://root:@localhost/planet_blogug_ch');

@mkdir('tmp/htdocs/');

//mysql_connect("localhost","root","");
mysql_select_db("planet_blogug_ch");
mysql_query(" update blogs,feeds set blogs.listID = feeds.listID where blogs.ID =  feeds.blogsID;");
$res = mysql_query ("select link, ID from blogs where  listID > 0 and inopml = 1  order by changed ");
//$res = mysql_query ("select link, ID from blogs where ID = 299129   order by changed ");
$urls = array();

while ($row = mysql_fetch_assoc($res)) {
    $a = array('html' =>   $row['link'],'ID' => $row['ID']);
    $urls[] = $a;  
}

 /*$urls = array_slice($urls,0,2);*/
/*$urls = array();
$urls[] = array('html' =>  'http://localhost:8085/blog.bitflux.html');*/
/*$urls = array();
$urls[] = array('html' =>  'http://blog.bitflux.ch/');*/
include_once("Benchmark/Timer.php");

$timer = new Benchmark_Timer;

$timer->start();

$STAT['badurls'] = array();
$STAT['badurls2'] = array();
$STAT['bad'] = 0;
$STAT['good'] = 0;
$mts = array();


//prepare links counter table
mysql_query("delete from htmllinks_cache where changed < date_sub(now(), INTERVAL 7 DAY)");    
    
// query for results:
    
//select count(*) as c , link, incomingblog_id from htmllinks left join blogs on incomingblog_id = blogs.ID group by incomingblog_id order by c;;
    


get_multi_html($urls,false);


    

// add if needed
//doMagpie($urls);
$timer->stop();
foreach($mts as $file => $mt) {
     touch($file,$mt);
}

updateTags();

mysql_query("delete from htmllinks  where changed < date_sub(now(), INTERVAL 200 DAY)");

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
        
        foreach ($urls as $k => $url) {
            $urls2[$url['ID']] = $url['html'];
            
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
            $STAT['urls'][] = $url ." ". $foo['http_code'] ;
            $STAT['badurls2'][] = $url;
            $STAT['bad'] ++;
            //unset($urls[$i]);
           
        }
        $res2 = mysql_query("select ID, last_statuscode from blogs where link = '".$url."'");
        $statusr = mysql_fetch_assoc($res2);
        if (count($statusr) > 0 && $statusr['last_statuscode'] != $foo['http_code']) {
            $query = "update blogs set last_statuscode = ".$foo['http_code'].", last_changed = now(), last_infotext = '".mysql_escape_string(var_export($foo,true))."' where ID = ".$statusr['ID'];
            mysql_query($query);
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
            /*print "GEO REFERENCE NOT FOUND! ";
            print " $url ";
            print "\n";*/
        } else {
            $lat         = floatval(mysql_escape_string(trim($geo[0][0])));
            $lon         = floatval(mysql_escape_string(trim($geo[0][1])));
            $locationArr = getLocalData($lat, $lon);
            $query = "update blogs set lon = '".round($lon,4)."',  lat = '".round($lat,4)."', city = '".$locationArr['city']."', canton = '".$locationArr['canton']."', country='".$locationArr['country']."', continent='".$locationArr['continent']."' where link = '".mysql_escape_string($url)."'";
            mysql_query($query);
        }
        
        $res = $xp->query('/html/body//a');
        $blogs = array();
        
        print $urla['html'];
        print "\n";
	    foreach ($res as $node) {
            $href = $node->getAttribute("href");
            $rel = $node->getAttribute("rel");
            if ($rel && strpos($rel,"nofollow") !== false) {
                continue;
            }
            if (strpos($href,$urla['html']) !== false) {
               continue;
            }
            if ($href && strlen($href) > 10) {
                $blog = getLinkedBlog($href);
                if ($blog && $urla['ID'] != $blog) {
                    if (!isset($blogs[$blog])) {
                        $blogs[$blog] = 1;
                    } else {
                        $blogs[$blog]++;
                    }
                }
            }
         }

         foreach($blogs as $blog => $c) {
             $query = "select id from htmllinks where outgoingblog_id = ".$urla['ID']." and  incomingblog_id = $blog";
             $res = mysql_query($query);
             if (mysql_num_rows($res) == 0) {
                 $query = "insert delayed into  htmllinks (outgoingblog_id, incomingblog_id,count,firsttime) values (".$urla['ID'].", $blog,$c, now())";
             } else {
                    $query = "update htmllinks set changed = now(), count = $c where outgoingblog_id = ".$urla['ID']." and  incomingblog_id = $blog";
             }
             mysql_query($query);
         }
             
         
         
        
    }
  
}

    
function getLinkedBlog($href) {
    
    if (strpos($href,'http') !== 0) {
        return 0;
    }
    $pos = strpos($href,'?');
    if ($pos !== false) {
        $href = substr($href,0,$pos);
    }
    $pos = strpos($href,'#');
    if ($pos !== false) {
        $href = substr($href,0,$pos);
    }
    $res = mysql_query("select blogs_id from htmllinks_cache where link = '".mysql_escape_string($href)."' LIMIT 1");
    if (mysql_num_rows($res) >= 1) {
        $row = mysql_fetch_row($res);
        return  $row[0];
    }
    $res = mysql_query("select id from blogs where listID > 0 and '".mysql_escape_string($href)."' Like concat(link,'%') and length(link) > 8 LIMIT 1;");
    
    $row =   mysql_fetch_row($res);
    $blog = 0;
    if ($row && $row[0]) {
        $blog = $row[0];
    } else {
        // try with or without www
        if (strpos($href,'http://www.') === false) {
            $href2 = str_replace('http://','http://www.',$href);
            $text = "FOUND with www. $href $href2\n";
        } else {
            $href2 = str_replace('http://www.','http://',$href);
            $text = "FOUND without www. $href $href2\n";
        }
        $res = mysql_query("select id,link from blogs where listID > 0 and   '".mysql_escape_string($href2)."' Like concat(link,'%') and length(link) > 8 LIMIT 1;");
        $row =   mysql_fetch_row($res);
        if ($row && $row[0]) {
            $blog = $row[0];
        }
    }
    //$inter = rand(0,6);
    $query = "insert  into htmllinks_cache (blogs_id,link,changed) values ($blog,'".mysql_escape_string($href)."',now())";
    mysql_query($query);
    return $blog;
    
    
    
}
function getLoad() {
    return 0;
    return substr(file_get_contents("/proc/loadavg"),0,4);
}


function updateTags() {
   
    $res = mysql_query(" select blogs.ID as id, feeds.listID from feeds left join blogs on blogs.ID = feeds.blogsID group by blogs.ID");
    
    while ($row = mysql_fetch_assoc($res)) {
        $tres = mysql_query("select tags from ping_blogug_ch.blogs where listID = ".$row['listID']);
        $trow =   mysql_fetch_assoc($tres);
        _updatebtags(explode(" ",$trow['tags']),$row['id']);  
    }
    
}

function _updatebtags($btags, $id) {
    global $db;  
    if (empty($id)) {
        return;
    }
    if (count($btags) > 0) {
            
            foreach($btags as $i => $btag) {
                if ($btag) {
                    $btag = strtolower($btag);
                    $btags[$i] = $btag;
                    $t[] = "'".mysql_escape_string(trim($btag))."'";
                }
            }
            if ($t) {
                $btagsImpl = implode(",",$t);
            } else {
                $btagsImpl = "'jkjlkjlkjkljklfasdiouio'";
            }
            
            $query = "select btag from btags  where btag in (".$btagsImpl. ")";
            $res = $db->query($query);
            $ids = $res->fetchCol();
            // insert new btags
            foreach ($btags as $value) {
                if ($value && !(in_array($value,$ids))) {
                    $query = "insert into btags ( btag) VALUES ('".mysql_escape_string(trim($value))."')";
                    $res = $db->query($query);
                }
            }
            
            $query = "select id from btags  where btag in (".$btagsImpl.")";
            $res = $db->query($query);
            $ids = $res->fetchCol();
        } else {
            $btags = array();
            $ids = array();
        }
        if (count($ids) > 0) {
            $query = "delete from blogs2btags where blogs_id = $id and not( btags_id in (".implode(",",$ids)."))";
            $db->query($query);
            
            //get old btags
            
            $query = "select btags_id from blogs2btags where blogs_id = '".$id."' and ( btags_id in (".implode(",",$ids)."))";
            $res = $db->query($query);
            $oldids = $res->fetchCol();
            
        } else {
            //delete all
            $query = "delete from blogs2btags where blogs_id = ".$id." ";
            $db->query($query);
            $oldids = array();
        }
        
        
        // add new relations
        
        foreach ($ids as $value) {
            if (!(in_array($value,$oldids))) {
                $query = "insert delayed into blogs2btags (blogs_id, btags_id) VALUES ($id, $value)";
                print "$query \n";
                $db->query($query);
            }
        }
     
     
    }
    
   
    
?>

