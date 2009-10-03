<?php
/**
 * @author Till Klampaeckel <till@php.net>
 */
class PlanetPEAR_Controller_Index extends PlanetPEAR_Controller_Base
{
    protected $data;
    protected $planet;

    public function index()
    {
        return $this->page(0, null);
    }

    /**
     * page action
     *
     * @param int    $from  The start key.
     * @param string $query Search query.
     *
     * @return array
     */
    public function page($from, $query)
    {
        if (empty($query)) {
            $query = null;
        }
        $this->data['entries'] = $this->planet->getEntries('default', $from, $query);

        return $this->data;
    }

    /**
     * List OPML feed
     *
     * @return void
     */
    public function opml()
    {
        header('Content-Type: text/x-opml');
        header('Content-Type: text/xml');
        $project = PROJECT_NAME_HR;
        $title   = PROJECT_NAME_HR . ' feed list';
        $created = date('r');
        echo <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<opml version="2.0">
 <head>
  <title>{$title}</title>
  <dateCreated>{$created}</dateCreated>
 </head>
 <body>
XML;
        foreach ($this->planet->getFeeds('default') as $data) {
            echo '<outline type="rss" text="'
                . htmlspecialchars($data['title'])
                . '" htmlUrl="'
                . htmlspecialchars($data['blogurl'])
                . '" xmlUrl="'
                . htmlspecialchars($data['feedurl'])
                . '" />' . "\n";
        }
        echo <<< XML
 </body>
</opml>
XML;
        exit(0);
    }
}