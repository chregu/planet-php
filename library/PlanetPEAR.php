<?php
class PlanetPEAR
{
    protected $db; // MDB2_Common
    protected $queryRestriction; // no idea
    protected $tally = 10; // blog entries per page

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
     * @param string $section default :-)
     *
     * @return array
     * @throws RuntimeException In case of an error from MDB2.
     */
    public function getBlogs($section = 'default')
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

    /**
     * Return the entries for the current scope.
     *
     * @param string $section See feeds table.
     * @param int    $startEntry For the limit query.
     *
     * @return array
     * @throws RuntimeException In case of an error.
     */
    public function getEntries($section = 'default', $startEntry = 0)
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
        ' . $from . ' AND feeds.section = "' . $section . '" ' . $this->queryRestriction . '
        ORDER BY entries.dc_date DESC
        LIMIT '. $startEntry . ',' . $this->tally);

        if (MDB2::isError($res)) {
            throw new RuntimeException($res->getUserInfo(), $res->getCode());
        }
        return $res;
    }

    public function getNavigation($startKey)
    {
        $from = 'FROM entries'
            . ' LEFT JOIN feeds ON entries.feedsID = feeds.ID'
            . ' LEFT JOIN blogs ON feeds.blogsID = blogs.ID'
            . ' WHERE 1';

        $sql = "
        SELECT count(*) $from
        LIMIT $startKey, {$this->tally}
        ";
    }
}
