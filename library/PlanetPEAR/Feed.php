<?php
class PlanetPEAR_Feed
{
    /**
     * @var string $type
     */
    protected $type;

    /**
     * @param string $type
     *
     * @return PlanetPEAR_Feed
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Autoloader for Zend Framework
     *
     * @return boolean
     */
    public static function autoload($className)
    {
        return Zend_Loader::loadClass($className);
    }

    public public function getFeedDef()
    {
        return array(
            //required
            'title' => PROJECT_NAME_HR,
            'link'  => PROJECT_URL . '/feed.php?type=' . $this->type,

            // optional
            'lastUpdate' => 'timestamp of the update date',
            'published'  => 'timestamp of the publication date',

            // required
            'charset' => 'utf-8',

            // optional
            'description' => 'Combined feed of ' . PROJECT_NAME_HR,
            'author'      => PROJECT_ADMIN_NAME,
            'email'       => PROJECT_ADMIN_EMAIL,

            // optional, ignored if atom is used
            'webmaster' => PROJECT_ADMIN_EMAIL,

            // optional
            'copyright' => 'All entries are copyright of their authors.',

            'generator' => MAGPIE_USER_AGENT,
            'language'  => 'en',

            // optional, ignored if atom is used
            'ttl'    => 3600,
            'rating' => 'The PICS rating for the channel.',

            // entries
            'entries' => array(),
        );
    }

    /**
     * @return PlanetPEAR_Feed_Entry
     */
    public function createEntry()
    {
        $entry = new PlanetPEAR_Feed_Entry();
        return;
    }
}

