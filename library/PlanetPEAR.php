<?php
class PlanetPEAR
{
    protected $db; // MDB2_Common
    protected $queryRestriction; // no idea
    protected $tally = 10; // blog entries per page

    /**
     * Request data.
     */
    protected $controller, $action, $from, $query;

    /**
     * @param MDB2_Common $db Optional MDB2 object.
     *
     * @return $this
     */
    public function __construct(MDB2_Common $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $this->db = MDB2::connect($GLOBALS['BX_config']['dsn']);
        }
    }

    /**
     * Render the template.
     *
     * @param string $tpl  The filename.
     * @param array  $vars The template vars.
     *
     * @return boolean
     */
    public function render($tpl, array $vars)
    {
        extract($vars);

        //var_dump($entries); 

        $file = TEMPLATE_DIR . $tpl;

        return include $file;
    }

    /**
     * Return blogs on the planet, which were active in the last 90 days.
     *
     * @param string  $section  default :-)
     * @param boolean $forceAll Force it to return all blogs, not just the active ones.
     *
     * @return array
     * @throws RuntimeException In case of an error from MDB2.
     */
    public function getBlogs($section = 'default', $forceAll = false)
    {
        $today = date('Y-m-d H:00',time());

        $sql = "
        select
            blogs.link as link,
            blogs.title as title,
        blogs.dontshowblogtitle  as dontshowblogtitle,
            feeds.author as author,
            unix_timestamp(max(entries.dc_date)) as maxDate,
            unix_timestamp(date_sub('$today', INTERVAL 90 DAY)) as border
            from blogs left join feeds on feeds.blogsID = blogs.ID
            left join entries on entries.feedsID = feeds.ID
            where entries.dc_date > 0 and feeds.section = " . $this->db->quote($section) . "
            ". $this->queryRestriction . "
            group by blogs.link
            order by maxDate DESC
        ";

        $this->db->setFetchMode(MDB2_FETCHMODE_ASSOC);

        $res = $this->db->queryAll($sql);
        if (MDB2::isError($res)) {
            throw new RuntimeException($res->getUserInfo(), $res->getCode());
        }
        return $res;
    }

    public function getCacheName()
    {
        if ($this->isQuery()) {
            return 'search' . $query;
        }
        return sprintf(
            '%s-%s-%s',
            $this->controller,
            $this->action,
            $this->from
        );
    }

    public function getController()
    {
        return $this->controller;
    }

    /**
     * Fetch and return a list of all feeds
     *
     * @return array Array of arrays. Arrays contain: title, blogurl and feedurl
     */
    public function getFeeds()
    {
        $sql = 'SELECT blogs.link as blogurl, title, feeds.link as feedurl'
            . ' FROM blogs, feeds'
            . ' WHERE blogs.id = feeds.blogsID';
        $this->db->setFetchMode(MDB2_FETCHMODE_ASSOC);

        $res = $this->db->queryAll($sql);
        if (MDB2::isError($res)) {
            throw new RuntimeException($res->getUserInfo(), $res->getCode());
        }
        return $res;
    }

    /**
     * Return the entries for the current scope.
     *
     * @param string $section    See feeds table.
     * @param int    $startEntry For the limit query.
     * @param mixed  $query      null with no search, and string if a search is triggered.
     * @return array
     * @throws RuntimeException In case of an error.
     */
    public function getEntries($section = 'default', $startEntry = 0, $query = null)
    {
        $TZ          = $GLOBALS['BX_config']['webTimezone'];
        $date_select = 'DATE_FORMAT(DATE_ADD(entries.dc_date, INTERVAL %s HOUR), "%s") AS %s';

        $dc_date  = sprintf($date_select, $TZ, '%e.%c.%Y, %H:%i', 'dc_date');
        $date_iso = sprintf($date_select, $TZ, '%Y-%m-%dT%H:%i:00Z', 'date_iso');
        $date_rfc = sprintf($date_select, $TZ, '%a, %d %b %Y %T +0000', 'date_rfc');

        $from = 'FROM entries'
            . ' LEFT JOIN feeds ON entries.feedsID = feeds.ID'
            . ' LEFT JOIN blogs ON feeds.blogsID = blogs.ID'
            . ' WHERE 1';

        $queryRestriction = '';

        static $cdataFields = array(
            "title", "link", "description",
            "content_encoded",
            "blog_title", "blog_author", "blog_link",
            "guid"
        );

        $length = 35; // blog title

        $this->db->setFetchMode(MDB2_FETCHMODE_ASSOC);

        $where = '';
        $tally = $this->tally;

        if ($query !== null) {

            if (strlen($query) <= 3) { // FIXME: this is sucky, sucky

                $sqlQuery = $this->db->quote('%' . $query .'%');

                $where .= " AND content_encoded LIKE {$sqlQuery}";
                $where .= " OR entries.description LIKE {$sqlQuery}";
                $where .= " OR entries.title LIKE {$sqlQuery} ";
            } else {
                $where .= " AND match(entries.description, entries.content_encoded, entries.title)";
                $where .= " against(". $this->db->quote($query) . ") ";
            }

            $startKey = 0;
            $tally    = 100;
        }

        //var_dump($where); exit;

        $res = $this->db->queryAll('
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
        if(length(blogs.title) > '. ($length + 5) .' , concat(left(blogs.title,'. ($length) .')," ..."), blogs.Title) as blog_Title
        ' . $from . ' AND feeds.section = "' . $section . '" ' . $where . $this->queryRestriction . '
        ORDER BY entries.dc_date DESC
        LIMIT '. $startEntry . ',' . $tally);

        if (MDB2::isError($res)) {
            throw new RuntimeException($res->getUserInfo(), $res->getCode());
        }
        return $res;
    }

    /**
     * Return the navigation options for previous and next.
     *
     * @param int $startKey The current start.
     *
     * @return array
     */
    public function getNavigation($startKey)
    {
        $from = 'FROM entries'
            . ' LEFT JOIN feeds ON entries.feedsID = feeds.ID'
            . ' LEFT JOIN blogs ON feeds.blogsID = blogs.ID'
            . ' WHERE 1';

        $sql   = "SELECT count(*) $from";
        $count = $this->db->queryOne($sql);
        if (MDB2::isError($count)) {
            throw new RuntimeException($count->getUserInfo(), $count->getCode());
        }

        $prevKey = null;
        if ($startKey !== 0) {
            $prevKey = $startKey - $this->tally;
            if ($prevKey < 0) {
                $prevKey = null;
            }
        }

        $nextKey = null;
        if ($count > ($startKey + $this->tally)) {
            $nextKey = $startKey + $this->tally;
        }

        $navigation = array(
            'prev' => $prevKey,
            'next' => $nextKey,
        );
        #var_dump($navigation, $startKey); exit;
        return $navigation;
    }



    public function isQuery()
    {
        if (!empty($this->query)) {
            return true;
        }
        return false;
    }

    public function setAction($action)
    {
        $this->action = strtolower($action);
        return $this;
    }

    public function setController($controller)
    {
        $this->controller = ucfirst(strtolower($controller));
        return $this;
    }

    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    public function setQuery($query)
    {
        $this->query = trim($query);
        return $this;
    }
}
