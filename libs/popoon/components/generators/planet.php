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
        if ( $redirect = $this->getParameterDefault("redirect")) {
             $this->db = MDB2::Connect($GLOBALS['BX_config']['dsn']);
             $id = $this->url2id($redirect);
             if ($id) {
                 $query = "select link from entries where ID = $id";
                 $link = $this->db->queryOne($query);
                 if ($link) {
                     header("Location: $link", 301);
                     die();
                 }
             }
             
             header("Location: http://planet-php.net/", 302);
             die();
        }
            

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
            if (strlen($search) <= 3) {
                $where = " where content_encoded LIKE ".$this->db->quote('%'.$search.'%') ." or entries.description LIKE ".$this->db->quote('%'.$search.'%') ."  or entries.title LIKE ".$this->db->quote('%'.$search.'%') ."  ";
            } else {
                $where = " where match(entries.description, entries.content_encoded, entries.title ) against(". $this->db->quote($search) . ") ";
	}
            $xml .= '<string>'.$search .'</string>';
           
        } else {
            $where = "where  1=1 ";        }
        
        $from = 'from entries
        left join feeds on entries.feedsID = feeds.ID
        left join blogs on feeds.blogsID = blogs.ID
        ';

	$query = 'select count(entries.ID) ' . $from . $where ." and feeds.section = '$section' ". $this->queryRestriction;
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
        
        
    	$today = date('Y-m-d H:00',time());
        if ($this->getParameterDefault("feedsList") == "yes") {
            $xml .= '<blogs>';
            
            $res = $this->db->query("
            select 
            blogs.link as link,
            blogs.title as title,
	    blogs.dontshowblogtitle  as dontshowblogtitle,
            feeds.author as author,
            unix_timestamp(max(entries.dc_date)) as maxDate,
            unix_timestamp(date_sub('$today', INTERVAL 90 DAY)) as border

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
    
    function getEntries($from,$section,$startEntry)
    {
        $TZ          = $GLOBALS['BX_config']['webTimezone'];
        $date_select = 'DATE_FORMAT(DATE_ADD(entries.dc_date, INTERVAL %s HOUR), "%s") AS %s';

        $dc_date  = sprintf($date_select, $TZ, '%e.%c.%Y, %H:%i', 'dc_date');
        $date_iso = sprintf($date_select, $TZ, '%Y-%m-%dT%H:%i:00Z', 'date_iso');
        $date_rfc = sprintf($date_select, $TZ, '%a, %d %b %Y %T +0000', 'date_rfc');

        static $cdataFields = array(
            "title", "link", "description",
            "content_encoded",
            "blog_title", "blog_author", "blog_link",
            "guid"
        );

        $res = $this->db->query('
        SELECT entries.ID,
        entries.title,
        entries.link,
        entries.guid,
        entries.description,
        entries.content_encoded,
        ' . $dc_date . ',
        ' . $date_iso . ',
        ' . $date_rfc . ',        
        blogs.link as blog_Link,
	feeds.author as blog_Author,
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
                
                if ($rowField == 'blog' &&  $row['maxdate'] < $row['border']) {
                    continue;
                }
                if (empty($row['content_encoded'])) {
                    $row['content_encoded'] = utf8_encode($row['description']);
                }
                
                
                $xml .= '<'.$rowField.'>';
                foreach($row as $key => $value) {
                    $xml .= '<'.$key.'>';
                    if (in_array($key,$cdataFields)) {
			  $value= preg_replace('#(<[^>]+[\s\r\n\"\'])on[a-z][^>]*>#iU',"$1>",html_entity_decode(str_replace('&lt;','&amp;lt;',$value),ENT_COMPAT,"UTF-8"));
			$value = str_replace("<?","&lt;?",$value);
			$value = str_replace("<script","&lt;script",$value);
			$value = str_replace("<style","<span style='display:none;'>",$value);
			$value = str_replace("</style","</span",$value);
                        $xml .= '<![CDATA['.str_replace("<![CDATA[","",str_replace("]]>","",$value)).']]>';
                    } else {
                        $xml .= $value;
                    }
                    $xml .= '</'.$key.'>';
                }
                if ($rowField == 'entry') {
                    $xml .= '<shortid>'.$this->id2url($row['id']) ."</shortid>";
                }
                $xml .= '</'.$rowField.'>';
                
                   
            }
            
            
        } 
        else {
            
            $xml = "<!-- \n" .$res->getMessage() . "\n". $res->getUserInfo() ." -->";
        }
        return $xml;
    }
    
    protected function url2id($short) {        
        $base = 63;
        $symbols = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
        
        $len = strlen($short);
        $total = 0;
        for ($i = 0;  $i < $len;$i++) {
            $pos = strpos($symbols,$short{$i});
            $multi = pow($base,$len - $i - 1);
            $c =  $pos * $multi . "\n";
            $total += $c;
            
        }
        
        return $total;
    }
    
    protected function id2url($val) {
        if (0 == $val) {
            return 0;
        }
        $base = 63;
        $symbols = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
        $result = '';
        $exp = $oldpow = 1;
        while ($val > 0 && $exp < 10) {

            $pow = pow($base, $exp++);

            $mod = ($val % $pow);
            // print $mod ."\n";
            $result = substr($symbols, $mod / $oldpow, 1) . $result;
            $val -= $mod;
            $oldpow = $pow;
        }
        return $result;
    }

}


?>
