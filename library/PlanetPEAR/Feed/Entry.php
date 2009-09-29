<?php
class PlanetPEAR_Feed_Entry
{
    protected $data = array(

        //required
        'title' => null,
        'link'  => null,

        // required, only text, no html
        'description' => null,

        // optional
        'guid' => null,

        // optional, original source of the feed entry
        'source' => array(

            // required
            'title' => null,
            'url'   => null,

        )
    );

    public function __construct()
    {
    }

    public function __set($var, $value)
    {
        if (!isset($this->data[$var])) {
            throw new InvalidArgumentException("Unknown index '$var'.");
        }
        $this->data[$var] = $value;

        return $this;
    }

    public function toArray()
    {
        return $this->data;
    }
}

