<?php
abstract class PlanetPEAR_Controller_Base
{
    protected $planet;

    public function __construct(PlanetPEAR $planet)
    {
        $this->planet = $planet;
    }
}
