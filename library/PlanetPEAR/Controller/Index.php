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
        return $this->page(0);
    }

    public function page($from)
    {
        $this->data['entries'] = $this->planet->getEntries('default', $from);

        return $this->data;
    }
}