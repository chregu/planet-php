<?php


class popoon_components_generators_planet extends popoon_components_generator {
    
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
            $search = trim($search);
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
                $where = " where entries.ID in (".implode(",",$this->db->queryCol($query)).")";
                
                //$where = " where entries.ID in (select  entries_id  from links  left join entries2links on links.id = links_id    where  not (entries_id is null) and links.link  = " .$this->db->quote($search2).")";
                //$where = " where entries.ID in (select  entries_id  from entries2links left join links on links_id = links.id  where  links.link like " .$this->db->quote('%'.$search2.'%').")";
                //$where = " where entries.link like ".$this->db->quote('%'.$search2.'%');
                break;
            
            case "linf:":
            case "inlink:":
                $search2 = substr($search,$poscolon);
                $query = "select  entries_id  from links  left join entries2links on links.id = links_id    where  not (entries_id is null) and links.link like " .$this->db->quote('%'.$search2.'%');
                $where = " where entries.ID in (".implode(",",$this->db->queryCol($query)).")";
                break;
                
            case  "link:":
                $search2 = substr($search,$poscolon);
                if (substr($search2,7) != "http://") {
                    $search2 = "http://".$search2;
                }
                $query = "select  entries_id  from links  left join entries2links on links.id = links_id    where  not (entries_id is null) and links.link like " .$this->db->quote($search2.'%');
                $where = " where entries.ID in (".implode(",",$this->db->queryCol($query)).")";
                
                break;
            
            case  "inurl:":
                $search2 = substr($search,$poscolon);
                $where = " where entries.link like ".$this->db->quote('%'.$search2.'%');
                break;
                
            case  "site:":
                $search2 = substr($search,$poscolon);
                if (substr($search2,7) != "http://") {
                    $search2 = "http://".$search2;
                }
                $where = " where entries.link like ".$this->db->quote($search2.'%');
                break;    
            case "tag:":
                $tag = substr($search,$poscolon);
                break;
            default:
                if (strlen($search) <= 3) {
                    $where = " where content_encoded LIKE ".$this->db->quote('%'.$search.'%') ." or entries.description LIKE ".$this->db->quote('%'.$search.'%') ."  or entries.title LIKE ".$this->db->quote('%'.$search.'%') ."  ";
                } else {
                    $where = " where match( entries.content_encoded, entries.title ) against(". $this->db->quote($search) . ") ";
                }
            }
            $xml .= '<string>'.$search .'</string>';
            
        } 
        if ($tag) {
            
            $tag = trim($tag,"/");
            
            if (strpos($tag," ") > 0 && strpos($_SERVER['REQUEST_URI'],"+") !== false ) {
                $tags = explode("+", trim(substr($_SERVER['REQUEST_URI'],strpos($_SERVER['REQUEST_URI'],"tag/") + 4),"/"));
                
                $t = array();
                foreach($tags as $tag) {
                    $t[] = $this->db->quote(urldecode($tag));
                }
                
                $tags = implode(", ",$t);
                $id = $this->db->queryCol(" select t1.id from tags as t1 left join tags as t2 on t1.taggroup = t2.taggroup where t2.tag = ".$this->db->quote($tag)."");
                $tagids = implode(",",$id);
                
                $where = " where entries.id in (select  entries_id from (select entries_id, count(*) as c  from entries2tags left join tags on tags_id = tags.id  where tags.tag in ($tags) group by entries_id) as e where c = ". count($t).")";
            } else {
                if (substr($tag,0,1) == "!") {
                    $id = $this->db->queryCol(" select id from tags where tag = ". $this->db->quote(substr($tag,1)));
                } else {
                    $id = $this->db->queryCol(" select t1.id from tags as t1 left join tags as t2 on t1.taggroup = t2.taggroup where t2.tag = ". $this->db->quote($tag));
                }
                $tagids = implode(",",$id);
          
                $where = " where entries.id in (select  entries_id from entries2tags left join tags on tags_id = tags.id where tags.id in ($tagids))";
                        
            }
            
            $xml .= '<tag>'.$tag .'</tag>';
        } 
        
        if (!isset($where) || !$where) {
            $where = "where  1=1 ";        
        }
        
        $from = 'from entries ';
       /* left join feeds on entries.feedsID = feeds.ID
        left join blogs on feeds.blogsID = blogs.ID
        ';*/

        $query = 'select count(entries.ID) ' . $from . $where . $this->queryRestriction;
       
       
       $from = 'from entries
        left join feeds on entries.feedsID = feeds.ID
        left join blogs on feeds.blogsID = blogs.ID
        ';
       
        $count = $this->db->queryOne($query);
        $xml .= '<count>'.$count.'</count>';
        $xml .= '<start>'.$startEntry.'</start>';
        $xml .= '</search>';
        switch (substr($this->sitemap->uri,0,3)) {
            case "rdf":
            case "rss":
            case "ato":
            case "com":
            $xml .= $this->getEntries( $from.$where, $section ,0);    
            break;
            default:
            $xml .= $this->getEntries( $from.$where , $section ,$startEntry);    
            //$xml .= $this->getEntries( $from." where 1=1", "releases",0);
        }
        
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
            $res = $this->db->query(" select tags.taggroup, count( distinct entries.id) as c  from tags 
                left join entries2tags on tags.id = tags_id 
                left join entries on entries_id = entries.id 
                where tags.hide = 0 and entries.dc_date > date_sub(now(), INTERVAL 3 DAY)  
                group by tags.taggroup order by c DESC LIMIT 30;");
            $xml .= $this->mdbResult2XML($res,"tag",array("taggroup"));
            $xml .= "</tags>";
            

            
        }
        
        if ($this->getParameterDefault("linksList") == "yes") {
          $xml .= '<links>';
            
            $res = $this->db->query("
             select count(entries2links.entries_id ) as c,  links.link from links 
                left join entries2links on links.id = links_id 
                left join entries on entries_id = entries.id                  
                where links.hide = 0 and entries.dc_date > date_sub(now(), INTERVAL 7 DAY)   
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
            unix_timestamp(date_sub(now(), INTERVAL 100 DAY)) as border

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
          
        $cdataFields = array("title","link","tags","content_encoded","blog_title","blog_author","blog_link","guid");
        $res = $this->db->query('
        SELECT entries.ID,
        entries.title,
        entries.link,
        entries.guid,
        entries.tags,
        entries.description as content_encoded,
        DATE_FORMAT(DATE_ADD(entries.dc_date, INTERVAL '.($GLOBALS['BX_config']['webTimezone'] ).' HOUR), "%e.%c.%Y, %H:%i") as dc_date,
        DATE_FORMAT(DATE_ADD(entries.dc_date, INTERVAL '.($GLOBALS['BX_config']['webTimezone'] ).' HOUR), "%Y-%m-%dT%H:%i:00Z") as date_iso,
        DATE_FORMAT(DATE_ADD(entries.dc_date, INTERVAL '.($GLOBALS['BX_config']['webTimezone'] ).' HOUR), "%a, %d %b %Y %T +0000") as date_rfc,
        
        blogs.link as blog_Link,
        blogs.author as blog_Author,
        blogs.ID as blog_id,
        blogs.dontshowblogtitle as blog_dontshowblogtitle,
        if(length(blogs.title) > '. ($this->maxBlogTitleLength + 5) .' , concat(left(blogs.title,'. ($this->maxBlogTitleLength ) .')," ..."), blogs.Title) as blog_Title
        ' . $from . ' and feeds.section = "'.$section.'" '.$this->queryRestriction . ' 
        order by entries.dc_date DESC 
        limit '.$startEntry . ',10');

        $xml = '<entries section="'.$section.'">';
        $xml .= $this->mdbResult2XML($res,"entry",$cdataFields);
        $xml .= '</entries>';
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
    
    static function truncate($inStr, $length = 100, $breakWords = false, $etc = '...') {
        if ($length == 0)
        return '';
        
        if (strlen($inStr) > $length) {
            $length -= strlen($etc);
            if (!$breakWords) {
                $inStr = preg_replace('/\s+?(\S+)?$/', '', substr($inStr, 0, $length + 1));
            }
            
            return substr($inStr, 0, $length)."$etc";
        } else
        return $inStr;
    }
    
}


?>
