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
        $this->filter = new Zend_Filter_StripTags;
    }

    public function __call($method, $value)
    {
        $var = str_replace('set', '', strtolower($method));

        $this->data[$var] = $value[0];

        return $this;
    }

    public function setDescription($value)
    {
        if (!$value) {
            $value = '';
        }
        $this->data['description'] = $this->filter->filter($value);
        return $this;
    }

    public function toArray()
    {
        return $this->data;
    }
}

