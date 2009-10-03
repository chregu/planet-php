<?php
class PlanetPEAR
{
    protected $db;

    public function __construct(MDB2_Common $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $this->db = MDB2::connect($GLOBALS['BX_config']['dsn']);
        }
    }

    public function render($tpl, $vars)
    {
        extract($vars);

        var_dump($vars, $entries); 

        $file = TEMPLATE_DIR . $tpl;

        return include $file;
    }

    /**
     * @param string $section See feeds table.
     *
     * @return array
     * @throws Exception In case of an error.
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

        $length = 35;

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
        ' . $from . ' AND feeds.section = "' . $section . '" ' . $queryRestriction . '
        ORDER BY entries.dc_date DESC
        LIMIT '. $startEntry . ', 10');

        if (MDB2::isError($res)) {
            throw new Exception($res->getUserInfo(), $res->getCode());
        }
        return $res;
    }
}
