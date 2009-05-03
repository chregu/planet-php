<?php


class popoon_components_generators_planetch extends popoon_components_generator {
    
    var $maxBlogTitleLength  = 35;
    
    function __construct (&$sitemap) 
    {
        parent::__construct($sitemap);
    }
    
    function init($attribs)
    {
        parent::init($attribs);
       // $this->db = $this->getParameterDefault("db");
        
    }    
    
    function DomStart(&$xml)
    {
	include_once("MDB2.php");
        if (!isset($GLOBALS['BX_config']['webTimezone'])) {
            $GLOBALS['BX_config']['webTimezone'] = $GLOBALS['BX_config']['serverTimezone'];
        }
        if($GLOBALS['BX_config']['webTimezone'] < 0) {
            $TZ = sprintf("-%02d:00",abs($GLOBALS['BX_config']['webTimezone']));
        } else {
            $TZ = sprintf("+%02d:00",abs($GLOBALS['BX_config']['webTimezone']));
        }
        
        $startEntry = $this->getParameterDefault("startEntry");
        $search = $this->getParameterDefault("search");
        
        $tag = $this->getParameterDefault("tag");
        $section = $this->getParameterDefault("section");
        if (!$section) {
            $section = 'default';
        }
        $this->queryRestriction = $this->getParameterDefault("queryRestriction");

        $this->db = MDB2::Connect($GLOBALS['BX_config']['dsn']);
        $this->db->query("set names 'utf8'");
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<planet>';
        $xml .= '<search>';
        
        if ($search) {
            $search = html_entity_decode($search);
            
            $search = trim($search);
            $where = "";
            
            $search2 = $search;
            $where = $this->getSearch($search2);
            
            $xml .= '<string>'.$search .'</string>';
            
        } 
        if ($tag) {
            
            $tag = trim($tag,"/");
            
            if (strpos($tag," ") > 0 && strpos($_SERVER['REQUEST_URI'],"+") !== false ) {
                if (($searchTagPos = strpos($_SERVER['REQUEST_URI'],"search/tag:")) !== false) {
                    $tags = explode("+", trim(substr($_SERVER['REQUEST_URI'],$searchTagPos + 11),"/"));
                    
                } else {
                $tags = explode("+", trim(substr($_SERVER['REQUEST_URI'],strpos($_SERVER['REQUEST_URI'],"tag/") + 4),"/"));
                }
                
                $t = array();
                foreach($tags as $tag) {
                    $t[] = $this->db->quote(urldecode($tag));
                }
                
                $tags = implode(", ",$t);
                $id = $this->db->queryCol(" select t1.id from tags as t1 left join tags as t2 on t1.taggroup = t2.taggroup where t2.tag = ".$this->db->quote($tag)."");
                $tagids = implode(",",$id);
                
                $where = " entries.id in (select  entries_id from (select entries_id, count(*) as c  from entries2tags left join tags on tags_id = tags.id  where tags.tag in ($tags) group by entries_id) as e where c = ". count($t).")";
            } else {
                if (substr($tag,0,1) == "!") {
                    $id = $this->db->queryCol(" select id from tags where tag = ". $this->db->quote(substr($tag,1)));
                } else {
                    $id = $this->db->queryCol(" select t1.id from tags as t1 left join tags as t2 on t1.taggroup = t2.taggroup where t2.tag = ". $this->db->quote($tag));
                }
                $tagids = implode(",",$id);
          
                $where = " entries.id in (select  entries_id from entries2tags left join tags on tags_id = tags.id where tags.id in ($tagids))";
                        
            }
            
            $xml .= '<tag>'.$tag .'</tag>';
        } 
        if (!empty($_GET['publireport']) && $_GET['publireport'] == 'off') {
            if (!isset($where)) {
                $where = "1 = 1";
            }
            $where = "(".$where. ") and hasWerbung = 0"; 
          }

        if (!isset($where) || !$where) {
            $where = "";         
        } else {
            $where = " where $where";
        }
        
        $from = 'from entries ';
       /* left join feeds on entries.feedsID = feeds.ID
        left join blogs on feeds.blogsID = blogs.ID
        ';*/

       
       $from = 'from entries
        left join feeds on entries.feedsID = feeds.ID
        left join blogs on feeds.blogsID = blogs.ID
        ';
        $query = 'select count(entries.ID) ' . $from . $where . $this->queryRestriction;
$count = '1000';
       
//        $count = $this->db->queryOne($query);
        $xml .= '<count>'.$count.'</count>';
        $xml .= '<start>'.$startEntry.'</start>';
        $xml .= '</search>';
        switch (substr($this->sitemap->uri,0,3)) {
            case "rdf":
            case "rss":
            case "geo":
            case "ato":
            case "kml":
            case "com":
		$where .= ' and noncommercial = 0';
            $xml .= $this->getEntries( $from.$where, $section ,0);    
            break;
            default:
            $xml .= $this->getEntries( $from.$where , $section ,$startEntry);    
            //$xml .= $this->getEntries( $from." where 1=1", "releases",0);
        }
        $today = date('Y-m-d H:00',time());
        
        if ($tag) {
            
           $xml .= '<relatedtags>'; 
     
          
          $res = $this->db->query("                
select  d.c, tags.taggroup as tag from (
          
            select count(distinct e2t.entries_id)  as c, e2t.tags_id from entries2tags 
                left join entries2tags as e2t on entries2tags.entries_id = e2t.entries_id 
                where entries2tags.tags_id in ($tagids) and not(e2t.tags_id  in ($tagids)) 
                group by e2t.tags_id  order by c DESC limit 20 ) 
                
                as d
                 left join tags on d.tags_id = tags.id
                 group by tags.taggroup
                 order by d.c DESC LIMIT 10  ");
                
        
        
              $xml .= $this->mdbResult2XML($res,"tag",array("tag"));
            $xml .= "</relatedtags>";
        }
        
        else if ($this->getParameterDefault("tagsList") == "yes") {
            $xml .= '<tags>';
            $res = $this->db->query("
            select tags.taggroup, count( distinct entries.id) as c  from entries  
                left join entries2tags on entries_id = entries.id  
                left join tags on  tags.id = tags_id
                where tags.hide = 0 and entries.dc_date > date_sub('$today', INTERVAL 3 DAY)  
                group by tags.taggroup order by c DESC LIMIT 30; 
            
            ");
            $xml .= $this->mdbResult2XML($res,"tag",array("taggroup"));
            $xml .= "</tags>";

            
        }
        
        if ($this->getParameterDefault("linksList") == "yes") {
          $xml .= '<links>';
                    if (!empty($_GET['publireport']) && $_GET['publireport'] == 'on') {
$publi = "";
} else {
$publi = " and hasWerbung = 0 ";
}

            $res = $this->db->query("
                select count(DISTINCT entries.feedsID) as c,  links.link from entries 
                left join entries2links on entries_id = entries.id 
                left join  links on           links.id = links_id
 		left join feeds on entries.feedsID = feeds.ID 
		left join blogs on feeds.blogsID = blogs.ID    
                where links.hide = 0 and notOnTopList = 0 and entries.dc_date > date_sub('$today', INTERVAL 7 DAY)  $publi
                group by links.id 
                order by c DESC LIMIT 15;
            ");
             
            $xml .= $this->mdbResult2XML($res,"link",array("link"));
            $xml .= "</links>";
        }
            
            
        if ($this->getParameterDefault("feedsList") == "yes") {
            $xml .= '<blogs>';
            
            $res = $this->db->query("
            select 
            blogs.link as link,
            blogs.title as title,
	    blogs.dontshowblogtitle  as dontshowblogtitle,
            blogs.author as author,
            unix_timestamp(max(entries.dc_date)) as maxDate,
            unix_timestamp(date_sub('$today', INTERVAL 100 DAY)) as border

            from blogs left join feeds on feeds.blogsID = blogs.ID
            left join entries on entries.feedsID = feeds.ID
            where entries.dc_date > 0 and feeds.section = '$section'
            ". $this->queryRestriction . "
            group by blogs.link
            order by maxDate DESC"
            
            );
            $xml .= $this->mdbResult2XML($res,"blog",array("link","title","author"));
            $xml .= "</blogs>";
        }
        $delicious = $this->getParameterDefault("deliciousRss");
        if ($delicious) {
            $simplecache = new popoon_helpers_simplecache();

            $simplecache->cacheDir = BX_TEMP_DIR;
            $uri = 'http://del.icio.us:80/rss/'.$delicious;

            $t = $simplecache->simpleCacheHttpRead($uri,1600);
            
            $deldom = new domdocument();
	    if (function_exists("iconv")) {
		    $t = iconv("UTF-8","UTF-8//IGNORE",$t);            
	    }
            if (@$deldom->loadXML($t)) {
                $xml .= preg_replace("#<\?xml[^>]*\?>#","",$deldom->saveXML());
            }

        }
        $xml .= "</planet>";
        return TRUE;
    }
    
    function getEntries($from,$section,$startEntry) {
          $limit = $this->getParameterDefault("postsPerPage");
          if (!$limit) { 
              $limit = 15;
          }
          if (!empty($_GET['limit']) && $_GET['limit'] <= 500) {
              
            $limit = (int) $_GET['limit'];   
          } 
	if (!$startEntry) {$startEntry = 0;}
        $cdataFields = array("title","link","tags","content_encoded","blog_title","blog_author","blog_link","guid");
        $query = 'SELECT entries.ID,
        entries.title,
        entries.link,
        entries.guid,
        entries.tags,
        entries.lon,
        entries.lat,
        entries.hasWerbung,
        entries.description as content_encoded,
        DATE_FORMAT(DATE_ADD(entries.dc_date, INTERVAL '.($GLOBALS['BX_config']['webTimezone'] ).' HOUR), "%e.%c.%Y, %H:%i") as dc_date,
        DATE_FORMAT(entries.dc_date,  "%Y-%m-%dT%H:%i:00Z") as date_iso,
        DATE_FORMAT(entries.dc_date, "%a, %d %b %Y %T +0000") as date_rfc,
        
        blogs.link as blog_Link,
        blogs.author as blog_Author,
        blogs.ID as blog_id,
        blogs.lon as blog_lon,
        blogs.lat as blog_lat,
        blogs.dontshowblogtitle as blog_dontshowblogtitle,
        if(length(blogs.title) > '. ($this->maxBlogTitleLength + 5) .' , concat(left(blogs.title,'. ($this->maxBlogTitleLength ) .')," ..."), blogs.Title) as blog_Title
        ' . $from . ' and feeds.section = "'.$section.'" '.$this->queryRestriction . ' 
        order by entries.dc_date DESC 
        limit '.$startEntry . ','.$limit;
//        print "$query\n";
        $res = $this->db->query($query);
        
        
        
        
       
        $xml = '<entries section="'.$section.'">';
        $xml .= $this->mdbResult2XML($res,"entry",$cdataFields);
        $xml .= '</entries>';
	//this should be done on the input side :)
	$xml = str_replace(array(chr(28),chr(29)),"",$xml);
        return $xml;
        
    }
    
    function mdbResult2XML ($res, $rowField, $cdataFields = array()) {
        $xml = "";
        if(!MDB2::isError($res)) {
            while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                /*if (isset($row['description']) && empty($row['content_encoded'])) {
                    $row['content_encoded'] = utf8_encode($row['description']);
                }*/
                $xml .= '<'.$rowField.'>';
                foreach($row as $key => $value) {
                    $xml .= '<'.$key.'>';
                    if (in_array($key,$cdataFields)) {

                        $value= preg_replace('#(<[^>]+[\s\r\n\"\'])on[a-z][^>]*>#iU',"$1>",html_entity_decode(str_replace('&lt;','&amp;lt;',$value),ENT_COMPAT,"UTF-8"));
                        $value = str_replace("<?","&lt;?",$value);
                        $value = str_replace("<script","&lt;script",$value);
                        $value = str_replace("<!--","&lt;!--",$value);
                        $xml .= '<![CDATA['.str_replace("<![CDATA[","",str_replace("]]>","",$value)).']]>';
                    } else {
                        $xml .= $value;
                    }
                    $xml .= '</'.$key.'>';
                }
                if ($rowField == "entry") { // && $_SERVER['REMOTE_ADDR'] == "212.55.202.195") {
                    $xml .= '<more/>';
                }
             
                $xml .= '</'.$rowField.'>';
                
            }
            
        } 
        else {
            
            $xml = "<!-- \n" .$res->getMessage() . "\n". $res->getUserInfo() ." -->";
        }
        return $xml;
    }
    
    static function truncate($inStr, $length = 100, $breakWords = false, $etc = '..') {
        if ($length == 0)
        return '';
        $inStr = preg_replace("#^http:\/\/(w*\.+)*#","",$inStr);
        if (strlen($inStr) > $length) {
            $length -= strlen($etc);
            if (!$breakWords) {
                $inStr = preg_replace('/\s+?(\S+)?$/', '', substr($inStr, 0, $length + 1));
            }
            
            return substr($inStr, 0, $length)."$etc";
        } else
        return $inStr;
    }
    
    protected function getWhere($search) {
        $poscolon = strpos($search,":")+1; 
            $key = substr($search,0,$poscolon);
            
        switch ($key) {
            case  "linkex:":
                $search2 = substr($search,$poscolon);
                if (strpos($_SERVER['REQUEST_URI'],"%26") !== false) {
                    $qpos = strpos($_SERVER['REQUEST_URI'],"?");
                    if ($qpos === false) {
                        $search2 = substr($_SERVER['REQUEST_URI'],strpos($_SERVER['REQUEST_URI'],"link:")+5);
                    } else {
                        $qpos = -(strlen($_SERVER['REQUEST_URI']) - strpos($_SERVER['REQUEST_URI'],"?"));
                        $search2 = substr($_SERVER['REQUEST_URI'],strpos($_SERVER['REQUEST_URI'],"link:")+5,$qpos);
                    }
                    $search = "link:$search2";
                    
                    $search2 = urldecode($search2);
                }
                if (substr($search2,7) != "http://") {
                    $search2 = "http://".$search2;
                }
                $query = "select  entries_id  from links  left join entries2links on links.id = links_id    where  not (entries_id is null) and links.link = " .$this->db->quote($search2);
                $ids = $this->db->queryCol($query);
                $query = "select id from entries where link = ".$this->db->quote($search2);
                $ids = array_merge($ids,$this->db->queryCol($query));
                $where = " entries.ID in (".implode(",",$ids).")";
                
                //$where = " where entries.ID in (select  entries_id  from links  left join entries2links on links.id = links_id    where  not (entries_id is null) and links.link  = " .$this->db->quote($search2).")";
                //$where = " where entries.ID in (select  entries_id  from entries2links left join links on links_id = links.id  where  links.link like " .$this->db->quote('%'.$search2.'%').")";
                //$where = " where entries.link like ".$this->db->quote('%'.$search2.'%');
                break;
            
            case "linf:":
            case "inlink:":
                $search2 = substr($search,$poscolon);
                $query = "select  entries_id  from links  left join entries2links on links.id = links_id    where  not (entries_id is null) and links.link like " .$this->db->quote('%'.$search2.'%');
                $where = " entries.ID in (".implode(",",$this->db->queryCol($query)).")";
                break;
                
            case  "link:":
                $search2 = substr($search,$poscolon);
                if (substr($search2,7) != "http://") {
                    $search2 = "http://".$search2;
                }
                $query = "select  entries_id  from links  left join entries2links on links.id = links_id    where  not (entries_id is null) and links.link like " .$this->db->quote($search2.'%');
                $where = " entries.ID in (".implode(",",$this->db->queryCol($query)).")";
                
                break;
            
            case  "inurl:":
            case  "insite:":
                $search2 = substr($search,$poscolon);
                $where = " entries.link like ".$this->db->quote('%'.$search2.'%');
                break;
                
            case  "site:":
                $search2 = substr($search,$poscolon);
                if (substr($search2,7) != "http://") {
                    $search2 = "http://".$search2;
                }
                $where = " entries.link like ".$this->db->quote($search2.'%');
                break;    
            case "tag:":
                $tag = substr($search,$poscolon);
                if (substr($tag,0,1) == "!") {
                    $id = $this->db->queryCol(" select id from tags where tag = ". $this->db->quote(substr($tag,1)));
                } else {
                    $id = $this->db->queryCol(" select t1.id from tags as t1 left join tags as t2 on t1.taggroup = t2.taggroup where t2.tag = ". $this->db->quote($tag));
                }
                $tagids = implode(",",$id);
          
                $where = " entries.id in (select  entries_id from entries2tags left join tags on tags_id = tags.id where tags.id in ($tagids))";
                        
                
                break;
            case "btag:":
                $search2 = substr($search,$poscolon);
                $query = "select blogs_id from btags left join blogs2btags on btags.id = blogs2btags.btags_id where btag = ".$this->db->quote(strtolower($search2)); 
                $where = " blogs.ID in (".implode(",",$this->db->queryCol($query)).")";
                break;
            case "id:":
                $search2 = substr($search,$poscolon);
                $where = " feeds.listID = " .$this->db->quote($search2);
                break;
            case "lang:":
                $search2 = substr($search,$poscolon);
                $where = " entries.lang = " .$this->db->quote($search2);
                break;    
            case "city:":
                $search2 = substr($search,$poscolon);
                $where = " blogs.city = " .$this->db->quote($search2);
                break;
            case "canton:":
                $search2 = substr($search,$poscolon);
                $where = " blogs.canton = " .$this->db->quote($search2);
                break;
            case "country:":
                $search2 = substr($search,$poscolon);
                $where = " blogs.country = " .$this->db->quote($search2);
                break;    
            case "pid:":
                $search2 = substr($search,$poscolon);
                $where = " entries.ID = " .$this->db->quote($search2);
                break;
            case "user:":
                $search2 = substr($search,$poscolon);
                $query = "select search from saved where user = ".$this->db->quote($search2);
                $searches = $this->db->queryCol($query);
                $w = array();
                foreach($searches as $search) {
                    $w[] = $this->getSearch($search);
                }
                $where = "(".implode(") or (",$w).")";
                break;
            case "top:":
                
                $today = date('Y-m-d h:00',time());
                $publi = "";
        if (!empty($_GET['publireport']) && $_GET['publireport'] == 'off') {
	$publi = " and hasWerbung = 0 ";
}
                $res = $this->db->queryCol("
                select count(DISTINCT entries.feedsID) as c,  links.link from entries 
                left join entries2links on entries_id = entries.id 
                left join  links on           links.id = links_id
 		left join feeds on entries.feedsID = feeds.ID 
		left join blogs on feeds.blogsID = blogs.ID    

                where links.hide = 0 and notOnTopList = 0 and entries.dc_date > date_sub('$today', INTERVAL 7 DAY) $publi
                group by links.id 
                order by c DESC LIMIT 15;
            ",null,1);


                $where = "(";
                
                foreach($res as $link) {
                    $where .= $this->getWhere("linkex:".substr($link,7)) . " or ";
                }
                $where .= " 1 = 2)  and notOnTopList = 0";
                
                break; 
                case "micro:":
                $search2 = substr($search,$poscolon);
                switch ($search2) {
                    case "listing": 
                    $where = "hasHListing = 1";
                    break;
                    case "enclosure": 
                    case "podcast": 
                    $where = "hasEnclosure = 1";
                    break;
                    case "cal": 
                    case "calendar": 
                    case "event": 
                    $where = "hasHCal = 1";
                    break;
                    case "card": 
                    $where = "hasHCard = 1";
                    break;
                    case "review": 
                    $where = "hasHReview = 1";
                    break;
                    case "all":
                    $where = "hasHListing = 1 or hasHReview = 1 or hasHCard = 1 or hasHCal = 1 or hasEnclosure = 1 or hasHListing = 1";
                    case "formats":
                    $where = "hasHListing = 1 or hasHReview = 1 or hasHCard = 1 or hasHCal = 1 or  hasHListing = 1";
                    
                }
                break;
                
            case "geo:":
            case "geoblogs:":
                //$query = " select max(entries.ID) from entries left join feeds on feedsID = feeds.ID left join blogs on blogsID = blogs.ID where blogs.lon != 0 group by blogs.ID;";
                
                $query = "select max(entries.ID) from blogs left join feeds on blogsID = blogs.ID left join entries on feedsID = feeds.ID where blogs.lon != 0  and not(isnull(entries.ID)) group by blogs.ID;";
                /*$searches = $this->db->queryCol($query);
                $w = array();
                foreach($searches as $search) {
                    $w[] = $this->getSearch($search);
                }*/
                
                $query2 = "select ID from (select (power((blogs.lon - entries.lon),2) + power((blogs.lat - entries.lat),2)) as dist, entries.ID from  blogs left join feeds on blogsID = blogs.ID left join entries on feedsID = feeds.ID where blogs.lon != 0 and not(isnull(entries.ID)) and entries.lon  != blogs.lon and entries.lat != blogs.lat and entries.lon != 0 group by entries.lon, entries.lat, blogs.ID order by entries.ID DESC limit 100) as c where c.dist > 0.00001;";
                
                $where = " entries.ID in (".implode(",",$this->db->queryCol($query)).",".implode(",",$this->db->queryCol($query2)).") ";
         
            
                break;
            default:
            
                if (strlen($search) <= 3) {
                    $where = " content_encoded LIKE ".$this->db->quote('%'.$search.'%') ." or entries.description LIKE ".$this->db->quote('%'.$search.'%') ."  or entries.title LIKE ".$this->db->quote('%'.$search.'%') ."  ";
                } else {
                    $where = " match( entries.content_encoded, entries.title ) against(". $this->db->quote($search) . " IN BOOLEAN MODE) ";
                }
            }
            return $where;
    }
    
    protected function getSearch($search2) {
        $where = "";
           while(strlen($search2) > 0) {
                if ($pos = strpos($search2," or ")) {
                    $s = substr($search2,0,$pos);
                    $where .= "(" . $this->getWhere($s) . ")\n";
                    $search2 = substr($search2,$pos + 4);
                    $where .= " or \n";
                    
                    
                } else if ($pos = strpos($search2," and ") ) {
                    $where .= "(" . $this->getWhere(substr($search2,0,$pos)) . ")\n";
                    $where .= " and \n";
                    $search2 = substr($search2,$pos + 5);
                    
                } else {
                    
                    $where .= "(" . $this->getWhere($search2) .")";
                    $search2 = "";
                }
                $search2 = trim($search2);
            }
            
            return $where;
    }
}


?>
