<?php
/**
 * @author Till Klampaeckel <till@php.net>
 */
class PlanetPEAR_Controller_Index extends PlanetPEAR_Controller_Base
{
    public function index()
    {
        return $this->page(0);
    }

    public function page($from)
    {
        $planet = new PlanetPEAR;
        return $planet->getEntries('default', $from);
    }
}