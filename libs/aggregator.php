<?php


include_once('MDB2.php');
include_once('utf2entities.php');
//include_once('HTTP/Request.php');

include_once('magpierss/rss_fetch.inc');

class aggregator {

    var $mdb = null;

    var $shortLang = array('english' => 'en', 'german' => 'de', 'french' => 'fr', 'italian' => 'it');//,'croatian' => 'cr');


    function aggregator() {
        $this->__construct();
    }

    function __construct() {
        $this->mdb = MDB2::connect($GLOBALS['BX_config']['dsn']);
        if(MDB2::isError($this->mdb)) {
            die('unable to connect to db');
        }
        $this->mdb->query("set names 'utf8'");
    }

    function aggregateAllBlogs($id = null) {
	if ($id) {
		$where = "where ID = $id";
	}
       $res = $this->mdb->query("select ID,blogsID as blogsid, link, cats, section from feeds $where");
       if (MDB2::isError($res)) {
           print $res->getMessage();
           print "\n";
           print $res->getUserinfo();
           die();
       }
       while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
           //get remote feed from magpie
           $feed = $this->getRemoteFeed($row['link']);
           if(!$feed) {
               continue;
           }
           //check if this blog already exists

  if (!$feed->channel['link']) {
                if (isset($feed->channel['link_'])) {
                   $feed->channel['link'] = $feed->channel['link_'];
                } else if (isset($feed->channel['link_self'])) {
                   $feed->channel['link'] = $feed->channel['link_self'];
                } else if (isset($feed->channel['atom']['link'])) {
                   $feed->channel['link'] = $feed->channel['atom']['link'];
                } else {
               print "NO channel/link... PLEASE FIX THIS\n";
               continue;
                }
           }

           $blog = $this->getBlogEntry($feed->channel['link']);
           if (!$blog) {
               $id = $this->insertBlogEntry($feed);
               print "new Blog: " .$feed->channel['title'] ."\n";
               $newBlog = true;
           } else {
               //TODO: check for changed channel entries
               $id = $blog['id'];
               if ( (
                    ($feed->channel['title'] && $blog['title'] != $feed->channel['title'])
                    || ($blog['link'] != $feed->channel['link'])
                    || ($blog['generator'] != $feed->channel['generator'])
                    || ($blog['sup_id'] != $feed->sup_id)
                    )
               && $row['section'] != 'comments') {
                    $this->updateBlogEntry($feed, $id);
               }
               $newBlog = false;
           }
           // update id, if not the same
           if ($row['blogsid'] != $id) {
               $this->updateFeedBlogID($row['link'], $id);
            }

            //loop through feeds

            foreach ($feed->items as $item) {
                if (!isset($item['link']) && isset($item['link_'])) {
                        $item['link'] = $item['link_'];
                }
		if (isset($item['guid'])) {
                    $guid = $item['guid'];
                } else if (isset($item['id'])) {
                    $guid = $item['id'];
                    $item['guid'] = $item['id'];
                } else {
                    $guid = $item['link'];
                    $item['guid'] = $item['link'];
                }
                $_date = strtotime($this->getDcDate($item,0 , true));

                if ($_date and $_date < time() - 3600 * 24 * 180 ) {
                    continue;
                }
               if (!isset($item['content']['encoded']) && isset($item['atom_content'])) {
                   $item['content']['encoded'] = $item['atom_content'];
               }

                if (isset($item['dc']['subject'])) {
                        $item['category'] = $item['dc']['subject'];
                }
                if (trim($item['category']) != trim($item['title'])) {
                    $item['tags'] = $item['category'];
                } else {
                    $item['tags'] = '';
                }
                if (!isset($item['geo']['long'])) {
                    if (isset($feed->channel['geo']['long'])) {
                        $item['geo']['long'] = round($feed->channel['geo']['long'],4);
                        $item['geo']['lat'] = round($feed->channel['geo']['lat'],4);
                    } else {
                        $item['geo']['long'] = null;
                        $item['geo']['lat'] = null;
                    }
                }

                if (isset($item['enclosure'])) {
                    $item['hasEnclosure'] = 1;
                } else {
                    $item['hasEnclosure'] = 0;
                }
                $item['md5']  = $this->generateMD5($item);

                $feedInDB = $this->getEntry($guid, $item['link']);
                     if (MDB2::isError($feedInDB)) {
            print "DB ERROR: ". $feedInDB->getMessage() . "\n". $feedInDB->getUserInfo(). "\n";
                     }
                if (!$feedInDB) {

                    //check if in olddb
                    if ($this->getEntry($guid, $item['link'],"entries_archive")) {
                        print $item['link'] . "is in the old db\n";
                        continue;
                    }

                    // check for categroy stuff
                    // we only do that for new entries


                    if ($row['cats']) {
                        $cats = explode(",",$row['cats']);
                        $hit = false;
                        foreach ($cats as $cat) {
                            if (strpos($item['category'],$cat) !== false) {
                                $hit = true;
                            }
                        }

                        if (!$hit) {
                            print $item['title'] . " - " . $item['category'] . " not in list\n";
                            continue;
                        }
                    }
                    // insert it in the db
                    $item['tags'] = $this->getTags($item,$feed->channel);
                    $item = $this->truncateEntries($item);
                    $this->insertEntry($item, $row['id'], array("newBlog"=>$newBlog));
                } else if ($item['md5'] != $feedInDB['md5']) {
                    $item['tags'] = $this->getTags($item,$feed->channel);
                    $item = $this->truncateEntries($item);
                    $this->updateEntry($item,$feedInDB['id']);
                }

            }
       }
    }

    function truncateEntries($item) {
        $maxsize = 1000;


        if (!isset($item['content']['encoded'])) {
            $item['content']['encoded'] = $item['description'];
        } else {
            $item['description'] =  $item['content']['encoded'] ;
        }
        if ( strlen($item['description']) > $maxsize + 500) {
            print "TRUNCATE description ". $item['title'] ."\n";

            $morebytes = (strlen($item['description']) - $maxsize);

            $item['description'] = $this->getBody(mb_substr($item['description'],0,$maxsize,'utf-8'));
            $item['description'] .= '<p><i>Truncated by Planet, read more at <a href="'.$item['link'].'">the original</a> (another ' . $morebytes .' bytes)</i></p>';
        }

          // replace images
            $dom = new domdocument();
            $dom->recover = true;
            $dom->loadXML('<body>'.$this->tidyfy('<html><body>'. $item['description'] .'</body></html>').'</body>');

            $xp = new domxpath($dom);
	    $imgs = $xp->query("/body//img");
            foreach ($imgs as $img) {
                $sp = $img->ownerDocument->createElement("span");
                $sp->setAttribute("class","pic");
                $sp->appendChild($sp->ownerDocument->createTextNode(  " [img] "));
                foreach($img->attributes as $attr) {
                    if ( $attr->name != 'src') {
                        $sp->appendChild($attr);
                    }
                    if ($attr->name == "alt" && strlen($attr->value > 1)) {
                        $sp->appendChild( $sp->ownerDocument->createTextNode( ": '".htmlspecialchars($attr->value)."'"));
                    }

                }

                $img->parentNode->insertBefore($sp,$img);

                $title = "Removed. ";
                $orititle = $sp->getAttribute("title");

                if ($orititle)  {
                    $title .= "title: ".$orititle;
                } else {
                    $title .= "src: ".str_replace("http://","",$img->getAttribute("src"));
                }
                $sp->setAttribute("title",$title);
                $img->parentNode->removeChild($img);
            }
            if ($img) {

                $item['description'] = preg_replace("#^<\?[^>]+\>\n#","",str_replace(array("<body>","</body>"),"",$dom->saveXML()));
            }

        return $item;
    }

    function getBody($html) {

        $d = new DomDocument();
        $html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>'.$html.'</body>';
        $d->loadHTML($html);
 	$xp = new domxpath($d);
        $res = $xp->query("/html/body/node()");
        $body = "";
        foreach ($res as $node) {
            $body .= $d->saveXML($node);
        }
        return $body;
    }

    function generateMD5($item) {
        return md5($item['title'] .$item['link'] . $item['description'] .$item['content']['encoded'].$item['tags'].  $item['geo']['long'].   $item['geo']['lat']. $item['hasEnclosure']);
    }

    function updateEntry($item, $entryID) {

       $date =  $this->getDcDate($item, 0,true);
       if (isset($item['enclosure'])) {
                $this->updateEnclosure($item,$entryID);
       }
       $query = "update entries set " .
       " link =  '" .mysql_escape_string(utf2entities($item['link'])) . "'," .
       " title =  '" .mysql_escape_string(utf2entities(strip_tags($item['title']))) . "'," .
       " description=  '" .mysql_escape_string(utf2entities($item['description'])) . "'," .
       " tags=  '" .mysql_escape_string(utf2entities($item['tags'])) . "'," .
       " lon=  '" .mysql_escape_string(($item['geo']['long'])) . "'," .
       " lat=  '" .mysql_escape_string(($item['geo']['lat'])) . "'," .
       " commentRss = '" .mysql_escape_string(($item['commentrss']))  . "'," .
       " hasHCard = '" .mysql_escape_string(($item['hasHCard']))  . "'," .
       " hasEnclosure = '" .mysql_escape_string(($item['hasEnclosure']))  . "'," .
       " hasHListing = '" .mysql_escape_string(($item['hasHListing']))  . "'," .
       " hasHCal = '" .mysql_escape_string(($item['hasHCal']))  . "'," .
       " hasHReview = '" .mysql_escape_string(($item['hasHReview']))  . "'," .
       " hasWerbung = '" .mysql_escape_string(($item['hasWerbung']))  . "'," .
       " content_encoded=  '" . mysql_escape_string(utf2entities($item['content']['encoded'])) . "',";
       if ($date) {
        $query .= " dc_date = '".$date."',";
       }

       $query .= " md5=  '" .$item['md5'] . "' ".
       " where ID = $entryID";
       print "update " . $item['title'] ."\n";
       $res = $this->mdb->query($query);

       if (MDB2::isError($res)) {
            print "DB ERROR: ". $res->getMessage() . "\n". $res->getUserInfo(). "\n";
            return false;
        } else {
            $this->updateTags($item['tags'],$entryID);
            $this->updateLinks($item['links'],$entryID);

//            $this->updateWords($item['words'],$entryID);
            return true;
        }
    }
    function insertEntry($item,$feedID, $options = array()) {
        $id =  $this->mdb->nextID("planet");
        if (isset($options['newBlog']) && $options['newBlog']) {
            $offset = - 3600 * 24; // offset back to 1 day ago.needed for new blogs without pubdate/dcdate
        } else {
            $offset = 0;
        }
        if (!isset($item['guid']) || $item['guid'] == '') {
            $item['guid'] = $item['link'];
        }

        require_once 'Text/LanguageDetect.php';
        if (! $this->langDetect) {
            $this->langDetect = new Text_LanguageDetect;
            $this->langDetect->omitLanguages(array_keys($this->shortLang),true);
        }
        $lang = $this->getLang(strip_tags($item['content']['encoded']. " " . $item['title']),$this->langDetect);
        if (isset($item['enclosure'])) {
                $this->updateEnclosure($item,$id);
        }
        $date =  $this->getDcDate($item, $offset);
        $query = "insert delayed into entries (ID,feedsID, title,link, guid,description,dc_date, dc_creator, content_encoded, tags, hasEnclosure, hasHCard, hasHListing, hasHCal, hasHReview, hasWerbung, lon, lat, commentRss, lang,  md5) VALUES (".        $id . "," .
        $feedID . ",'" .

        mysql_escape_string(utf2entities(strip_tags($item['title']))) . "','" .
        mysql_escape_string(trim($item['link'])) . "','" .
        mysql_escape_string(($item['guid'])) . "','" .
        mysql_escape_string(utf2entities($item['description'])) . "','".
        $date . "','" .
        $item['dc']['creator'] . "','" .
        mysql_escape_string(utf2entities($item['content']['encoded'])) . "','".
        mysql_escape_string(utf2entities($item['tags'])) . "','".
        mysql_escape_string(($item['hasEnclosure'])) . "','".
        mysql_escape_string(($item['hasHCard'])) . "','".
        mysql_escape_string(($item['hasHListing'])) . "','".
        mysql_escape_string(($item['hasHCal'])) . "','".
        mysql_escape_string(($item['hasHReview'])) . "','".
        mysql_escape_string(($item['hasWerbung'])) . "','".
        mysql_escape_string(($item['geo']['long'])) . "','".
        mysql_escape_string(($item['geo']['lat'])) . "','".
        mysql_escape_string(($item['commentrss'])) . "','".
        mysql_escape_string($lang) . "','".
        $item['md5'] . "')";

        print "insert " . $item['title'] ."\n";
        $res = $this->mdb->query($query);

        if (MDB2::isError($res)) {
            print "DB ERROR: ". $res->getMessage() . "\n". $res->getUserInfo(). "\n";
            return false;
        } else {
            $this->updateTags($item['tags'],$id);
            $this->updateLinks($item['links'],$id);

            // delete dupes
            $query = "select id from (select count(*) as c, min(id) as id from entries  where feedsID = $feedID group by dc_date,title,feedsID) as d where d.c > 1";
            foreach ($this->mdb->queryCol($query) as $did) {
                $query = "DELETE LOW_PRIORITY from entries where ID = $did;";
                $this->mdb->query($query);
            }
            //            $this->updateWords($item['words'],$entryID);

            return $id;
        }
    }


    function getLang($text,$l) {

        $lang = $l->detect($text,1);
        $lang = array_keys($lang);

        if (isset($lang[0])) {
            return $this->shortLang[$lang[0]];
        }

        return "nn";

    }

    function getDcDate($item, $nowOffset = 0, $returnNull = false) {
        //we want the dates in UTC... Looks like MySQL can't handle timezones...
        //putenv("TZ=UTC");
 	if (isset($item['dc']['date'])) {
            $dcdate = $this->fixdate($item['dc']['date']);
        } elseif (isset($item['pubdate'])) {
            $dcdate = $this->fixdate($item['pubdate']);
        } elseif (isset($item['published'])) {
            $dcdate = $this->fixdate($item['published']);
        } elseif (isset($item['created'])) {
            $dcdate = $this->fixdate($item['created']);
        } elseif (isset($item['modified'])) {
            $dcdate = $this->fixdate($item['modified']);
        } elseif (isset($item['edited'])) {
            $dcdate = $this->fixdate($item['edited']);
        } elseif (isset($item['updated'])) {
            $dcdate = $this->fixdate($item['updated']);
        } elseif ($returnNull) {
            return NULL;
        } else {
            //TODO: Find a better alternative here
            $dcdate = gmdate("Y-m-d H:i:s O",time() + $nowOffset - 1800);
        }
        return $dcdate;

    }

    function fixdate($date) {
        $date =  preg_replace("/([0-9])T([0-9])/","$1 $2",$date);
        $date =  preg_replace("/([\+\-][0-9]{2}):([0-9]{2})/","$1$2",$date);
 	$time = strtotime($date);
        //if time is too much in the future (more than 1 hours)
        // set it to now()
        if (($time - time()) > 600)  {
                $time = time() - 1800;
        }
        $date =  gmdate("Y-m-d H:i:s O",$time);
        return $date;
    }

    function updateFeedBlogID($url, $id) {

        $query = "update feeds set blogsID = $id where link = '$url'";
        $res = $this->mdb->query($query);
        if (MDB2::isError($res)) {
            print "DB ERROR: ". $res->getMessage() . "\n". $res->getUserInfo(). "\n";
            return false;
        } else {
            return $id;
        }
    }

    function insertBlogEntry($feed) {

        $id =  $this->mdb->nextID("planet");
        $query = "insert into blogs (ID,title,link,generator,sup_id,description) VALUES (".
        $id . ",'" .
        mysql_escape_string($feed->channel['title']) . "','" .
        mysql_escape_string($feed->channel['link']) . "','" .
        mysql_escape_string($feed->channel['generator']) . "','" .
        mysql_escape_string($feed->sup_id) . "','" .
        mysql_escape_string($feed->channel['description']) . "')";
        $res = $this->mdb->query($query);
        if (MDB2::isError($res)) {
            print "DB ERROR: ". $res->getMessage() . "\n". $res->getUserInfo(). "\n";
            return false;
        } else {
            return $id;
        }
    }


     function updateBlogEntry($feed,$id) {


        $query = "update blogs set
        title =  '".mysql_escape_string($feed->channel['title']) . "',
        link = '".mysql_escape_string($feed->channel['link']) . "',
        description = '".mysql_escape_string($feed->channel['description']) . "',
        sup_id = '".mysql_escape_string($feed->sup_id) . "',
        generator = '".mysql_escape_string($feed->channel['generator']) . "' where ID = ". $id;
        $res = $this->mdb->query($query);
        print "Updated blog entry for ". $feed->channel['link']."\n";
        if (MDB2::isError($res)) {
            print "DB ERROR: ". $res->getMessage() . "\n". $res->getUserInfo(). "\n";
            return false;
        } else {
            return $id;
        }
    }

    function getBlogEntry($url) {
         return  $this->mdb->queryRow ("select * from blogs where link = '$url'",null,MDB2_FETCHMODE_ASSOC);
    }

    function getFeedEntry($url) {
         return  $this->mdb->queryRow ("select * from feeds where link = '$url'",null,MDB2_FETCHMODE_ASSOC);
    }
    function getEntry($guid, $link = null, $db="entries") {
        if ($link) {
            return  $this->mdb->queryRow (
            "select * from $db where guid = ".$this->mdb->quote($guid)." UNION " .
            "select * from $db where link = ".$this->mdb->quote($link),
            null,MDB2_FETCHMODE_ASSOC);

        } else {
            return  $this->mdb->queryRow ("select * from $db where guid = ".$this->mdb->quote($guid),null,MDB2_FETCHMODE_ASSOC);
        }
    }


    function getRemoteFeed($url) {
	print "Get $url \n";
        if ($feed = fetch_rss($url)) {
            return $feed;
        } else {
            print "$url is not a valid feed \n";
            return false;
        }

    }

    static function extractTag($href) {
        //according to http://microformats.com/wiki/rel-tag
        $href = trim($href);
        $href = preg_replace("#index\..+$#","",$href);

        $href = str_replace("http://","",$href);
        $href = trim($href,"/");
        $rpos = strrpos($href,"/");
        if ($rpos !== false) {
            $tag = substr($href, ($rpos + 1));
        } else {
            $tag = "";
        }
        $qpos = strpos($tag,"?");
	if ($qpos !== false) {
		$tag = substr($tag,0,$qpos);
	}
        return urldecode($tag);
    }
    function getTagsFromRel($item,$channel) {
        $tags = array();
        $links = array();
        $tagdom = new domdocument();
        $tagdom->recover = true;
        $hasHCal = 0;
        $hasHListing = 0;
        $hasHReview = 0;
        $hasHCard = 0;
        $hasWerbung = 0;
        if (!$tagdom->loadXML( "<body>".str_replace("&","&amp;",$item['content']['encoded'].$item['description'])."</body>")) {

        } else {
            $basePath = $this->getBasePath($channel['link']);
            $xp = new domxpath($tagdom);
            $as = $xp->query("/body//a");

            foreach($as as $node) {

                $tag =  $node->getAttribute("rel");
                $href = trim($node->getAttribute("href"));
                if ($tag && strpos($tag,"tag") !== false) {
                    $tc = self::extractTag($href);
                    if ($tc != trim($item['title'])) {
                        $tags[] = strtolower($tc);
                    }

                } else if ($href && substr($href,0,7) == "http://" && strpos($href, $basePath) !== 0) {
                    $l = trim(html_entity_decode($href,ENT_NOQUOTES,"UTF-8"),"/");
                    if (!$hasWerbung && (strpos($l,'http://www.trigami.com') === 0 || strpos($l,'http://www.blogpay.eu/') === 0)) {
                        $hasWerbung = 1;
                    }
                    else if (!in_array($l,$links)) {
                        $links[] = $l;
                    }
                } else if ($tag && strpos($tag,"nofollow") !== false) {
                }

            }
            $hasHReview = $this->hasMicro('hreview',$xp);
            $hasHCard = $this->hasMicro('vcard',$xp);
            $hasHCal = $this->hasMicro('vevent',$xp);
            $hasHListing = $this->hasMicro('vlisting',$xp);

        }
        return array("tags" =>$tags, "links" => $links,"hasHListing" => $hasHListing, "hasHCal" => $hasHCal, "hasHCard" => $hasHCard, "hasHReview" => $hasHReview, "hasWerbung" => $hasWerbung);
    }


    function hasMicro ($class,$xp) {
        $micro = $xp->query("//*[contains(concat(' ', normalize-space(@class), ' '),' $class ') ]");
            if ($micro->length > 0 ) {
                return 1;
            } else {
                return 0;
            }
    }



    function getBasePath($link) {

             preg_match("#(http://.*)/([^/]*)$#",$link,$matches);
             if (isset($matches[1])) {
                 return $matches[1];
             } else {
                 return $link;
             }
    }
    function mergeTags($tagsArray, $tagsString) {
        $tags = array();
        if ($tagsString) {
            $tags = explode(" , ",strtolower($tagsString));
            $tags = array_merge($tags,$tagsArray);
        } else {
            $tags = $tagsArray;
        }

        $tags = array_unique ($tags);
        return  implode(" , ",$tags);
    }

    function getTags(&$item, $channel) {
        $as = $this->getTagsFromRel($item,$channel);
        //$item['words'] = $this->getWords($item['title'] . " " . $item['content']['encoded']. " " .  $item['description']);
        $item['links'] = array_unique($as['links']);
        $item['hasHReview'] = $as['hasHReview'];
        $item['hasHCard'] = $as['hasHCard'];
        $item['hasHCal'] = $as['hasHCal'];
        $item['hasHListing'] = $as['hasHListing'];
        $item['hasWerbung'] = $as['hasWerbung'];

        //flickr seperates their tags with " "...
        if (strpos($channel['link'],"http://www.flickr.com/") === 0 || strpos($channel['link'],"http://del.icio.us") === 0) {
            $item['tags'] = str_replace(" "," , ",$item['tags']);
        }
        return strip_tags(strtolower($this->mergeTags($as['tags'],$item['tags'])));

    }

    function getWords($string) {
        $string = str_replace(array("ä","ö","ü"),array("ae","oe","ue"),html_entity_decode(strip_tags($string),ENT_COMPAT,'UTF-8'));
        $words = preg_split("#\W#u",$string);
        $words = array_unique($words);

        foreach($words as $i => $word) {
            if (strtolower($word) == $word) {
                 unset($words[$i]);
            }
            if (strlen($word) < 4) {
                 unset($words[$i]);
            }
        }
        return $words;
    }

    function updateTags($tags, $id) {
        if ($tags) {
            $tags = explode(" , ",$tags);


            foreach($tags as $tag) {
                if ($tag) {
                    $t[] = "'".mysql_escape_string(trim($tag))."'";
                }
            }
            $tagsImpl = implode(",",$t);
            $query = "select tag from tags  where tag in (".$tagsImpl. ")";
            $res = $this->mdb->query($query);
            $ids = $res->fetchCol();
            // insert new tags
            foreach ($tags as $value) {
                if ($value && !(in_array($value,$ids))) {
                    $_t = mysql_escape_string(trim($value));
                    $query = "insert into tags ( tag,taggroup) VALUES ('".$_t."','".$_t."')";
                    $res = $this->mdb->query($query);
                }
            }

            $query = "select id from tags  where tag in (".$tagsImpl.")";
            $res = $this->mdb->query($query);
            $ids = $res->fetchCol();
        } else {
            $tags = array();
            $ids = array();
        }
        if (count($ids) > 0) {
            $query = "delete from entries2tags where entries_id = $id and not( tags_id in (".implode(",",$ids)."))";
            $this->mdb->query($query);

            //get old tags

            $query = "select tags_id from entries2tags where entries_id = '".$id."' and ( tags_id in (".implode(",",$ids)."))";
            $res = $this->mdb->query($query);
            $oldids = $res->fetchCol();

        } else {
            //delete all
            $query = "delete from entries2tags where entries_id = ".$id." ";
            $this->mdb->query($query);
            $oldids = array();
        }


        // add new relations
        foreach ($ids as $value) {
            if (!(in_array($value,$oldids))) {
                $query = "insert delayed into entries2tags (entries_id, tags_id) VALUES ($id, $value)";
                $this->mdb->query($query);
            }
        }


    }
    //FIXME merge updateLinks and updateTags
    function updateLinks($links, $id) {
        if (count($links) > 0) {
            foreach($links as $link) {
                if ($link) {
                    $t[] = "'".mysql_escape_string(trim($link))."'";
                }
            }
            $linksImpl = implode(",",$t);
            $query = "select link from links  where link in (".$linksImpl. ")";
            $res = $this->mdb->query($query);
            $ids = $res->fetchCol();
            // insert new links
            foreach ($links as $value) {
                if ($value && !(in_array($value,$ids))) {
                    $query = "insert into links ( link) VALUES ('".mysql_escape_string(trim($value))."')";
                    $res = $this->mdb->query($query);
                }
            }

            $query = "select id from links  where link in (".$linksImpl.")";
            $res = $this->mdb->query($query);
            $ids = $res->fetchCol();
        } else {
            $links = array();
            $ids = array();
        }
        if (count($ids) > 0) {
            $query = "delete from entries2links where entries_id = $id and not( links_id in (".implode(",",$ids)."))";
            $this->mdb->query($query);

            //get old links

            $query = "select links_id from entries2links where entries_id = '".$id."' and ( links_id in (".implode(",",$ids)."))";
            $res = $this->mdb->query($query);
            $oldids = $res->fetchCol();

        } else {
            //delete all
            $query = "delete from entries2links where entries_id = ".$id." ";
            $this->mdb->query($query);
            $oldids = array();
        }


        // add new relations
        foreach ($ids as $value) {
            if (!(in_array($value,$oldids))) {
                $query = "insert delayed into entries2links (entries_id, links_id) VALUES ($id, $value)";
                $this->mdb->query($query);
            }
        }


    }

    function updateWords($words, $id) {
	return true;
        if (count($words) > 0) {
            foreach($words as $i => $word) {
                if ($word) {
                    $word = strtolower($word);
                    $words[$i] = $word;
                    $t[] = "'".mysql_escape_string(trim($word))."'";
                }
            }
            $wordsImpl = implode(",",$t);
            $query = "select word from words  where word in (".$wordsImpl. ")";
            $res = $this->mdb->query($query);
            $ids = $res->fetchCol();
            // insert new words
            foreach ($words as $value) {
                if ($value && !(in_array($value,$ids))) {
                    $query = "insert into words ( word) VALUES ('".mysql_escape_string(trim($value))."')";
                    $res = $this->mdb->query($query);
                }
            }

            $query = "select id from words  where word in (".$wordsImpl.")";
            $res = $this->mdb->query($query);
            $ids = $res->fetchCol();
        } else {
            $words = array();
            $ids = array();
        }
        if (count($ids) > 0) {
            $query = "delete from entries2words where entries_id = $id and not( words_id in (".implode(",",$ids)."))";
            $this->mdb->query($query);

            //get old words

            $query = "select words_id from entries2words where entries_id = '".$id."' and ( words_id in (".implode(",",$ids)."))";
            $res = $this->mdb->query($query);
            $oldids = $res->fetchCol();

        } else {
            //delete all
            $query = "delete from entries2words where entries_id = ".$id." ";
            $this->mdb->query($query);
            $oldids = array();
        }


        // add new relations
        foreach ($ids as $value) {
            if (!(in_array($value,$oldids))) {
                $query = "insert delayed into entries2words (entries_id, words_id) VALUES ($id, $value)";
                $this->mdb->query($query);
            }
        }


    }



     function tidyfy ($string) {
        $tidyOptions = array(
        "output-xhtml" => true,
        "show-body-only" => true,
        "clean" => false,
        "wrap" => "0",
        "indent" => false,
        "indent-spaces" => 1,
        "ascii-chars" => false,
        "wrap-attributes" => false,
        "alt-text" => "",
        "doctype" => "loose",
        "numeric-entities" => true,
        "drop-proprietary-attributes" => true
        );
        $tidy = new tidy();
        if(!$tidy) {
            die("notidy");
            return $string;
        }

        // this preg escapes all not allowed tags...
        $tidy->parseString($string,$tidyOptions,"utf8");
        $tidy->cleanRepair();
        return (string) $tidy;
    }

    function updateEnclosure(&$item,$id) {
        if ($item['enclosure']) {
            $item['hasEnclosure'] = 0;
            $query = "delete from enclosures where entries_id = $id";
            $this->mdb->query($query);
            foreach($item['enclosure'] as $enclosure) {
                if (strpos($enclosure['type'],"audio") === 0 or
                    strpos($enclosure['type'],"video") === 0) {
                    $item['hasEnclosure'] = 1;
                 }
                $query = "insert into enclosures (entries_id,url,length,type) VALUES (
                ".$id.",
                ". $this->mdb->quote($enclosure['url']).",
                ". $this->mdb->quote($enclosure['length']).",
                ". $this->mdb->quote($enclosure['type']).")";
                $this->mdb->query($query);
            }
        }



    }

}
