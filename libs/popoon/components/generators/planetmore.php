<?php


class popoon_components_generators_planetmore extends popoon_components_generator {
    
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
        
        
        $this->db = MDB2::Connect($GLOBALS['BX_config']['dsn']);
        $this->db->query("set names 'utf8'");
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<planet><search>';
        
        $blog = (int) $this->getParameterDefault("blog");
        $entry = (int) $this->getParameterDefault("entry");
       
        $xml .= "<blog>".$blog."</blog>";
        $xml .= "<entry>".$entry."</entry>";
        $xml .= '</search>';
        
        $xml .= '<blog>';
        $res = $this->db->query('select dc_date as lastpost, feeds.id as feed_id, feeds.listID,  feeds.link as feed_link, blogs.link as link, blogs.title as title, blogs.description as description, blogs.lon, blogs.lat, blogs.city, blogs.canton, blogs.country from blogs 
                left join feeds on feeds.blogsID = blogs.ID 
                left join entries on entries.feedsID = feeds.ID
                where blogs.id = '.$blog.' order by dc_date DESC  LIMIT 1' );
                
              
        $xml .= $this->mdbResult2XML($res,"blog",array("title","link","feed_link","description"),$blogRow);
        $xml .= '</blog>';
        if ($entry) {
        $xml .= '<entry>';
        $res = $this->db->query('select id, title, dc_date, dc_creator, tags, link  from entries where id = '.$entry);
        $xml .= $this->mdbResult2XML($res,"entry",array("title","dc_creator","tags","link"),$entryRow);
        $xml .= '</entry>';
        }
        $xml .= '<postlinks>';
        if (isset($entryRow['link'])) {
        
        $res = $this->db->query('
                select distinct(entries.link) as url, entries.dc_date as date, entries.title as entry_title,blogs.title  as blog_title, blogs.link as blog_link from links 
                    left join entries2links on links_id = links.id
                    left join entries on entries.id = entries_id
                     left join feeds on entries.feedsID = feeds.ID
                    left join blogs on feeds.blogsID = blogs.ID
                    where not (entries_id is null) and links.link = '.$this->db->quote($entryRow['link']). ' order by entries.dc_date DESC');
        $xml .= $this->mdbResult2XML($res,"link",array("url","entry_title","blog_link","blog_title"));
        }
        $xml .= '</postlinks>';
        
        
        $xml .= '<bloglinks>';
        if (isset($blogRow['link'])) {
        $res = $this->db->query('
                select distinct(entries.link) as url, entries.dc_date as date, entries.title as entry_title,blogs.title  as blog_title, blogs.link as blog_link  from links 
                    left join entries2links on links_id = links.id
                    left join entries on entries.id = entries_id
                    left join feeds on entries.feedsID = feeds.ID
                    left join blogs on feeds.blogsID = blogs.ID
                    where not (entries_id is null) and links.link like '.$this->db->quote($blogRow['link'].'%'). ' and links.link != '.$this->db->quote($entryRow['link']). 'order by entries.dc_date DESC');
        $xml .= $this->mdbResult2XML($res,"link",array("url","entry_title","blog_link","blog_title"));
        }
        $xml .= '</bloglinks>';
        
        
           $xml .= '<top100>';
           if (isset($blogRow['link'])) {
      /*  $res = $this->db->query('
                select id, rang, rank,blogs,links, tags  from blog_ch.top100 where inopml = 1 and url like  '.$this->db->quote($blogRow['link'].'%'));
         */       
                $res = $this->db->query('
                select id, tags  from ping_blogug_ch.blogs where inopml = 1 and url like  '.$this->db->quote($blogRow['link'].'%'));
        $xml .= $this->mdbResult2XML($res,"link",array("url"));
           }
        $xml .= '</top100>';
        
        /*
        
        $xml .= '<gallery>';
        
        $xml .= '<src>'.str_replace("/","-",trim(substr($blogRow['link'],7),"/")).'.png.html</src>';
        
        $xml .= '</gallery>';
        */
        $xml .= '</planet>';
        
        return $xml;
        
    }
    
    function mdbResult2XML ($res, $rowField, $cdataFields = array(),&$last) {
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
                        /*$value = str_replace("<script","&lt;script",$value);
                        $value = str_replace("<!--","&lt;!--",$value);*/
                        $xml .= '<![CDATA['.str_replace("<![CDATA[","",str_replace("]]>","",$value)).']]>';
                    } else {
                        $xml .= $value;
                    }
                    $xml .= '</'.$key.'>';
                    $last = $row;
                }
                $xml .= '</'.$rowField.'>';
                
                
            }
            
        } 
        else {
            
            $xml = "<!-- \n" .$res->getMessage() . "\n". $res->getUserInfo() ." -->";
        }
        return $xml;
    }
}


?>
