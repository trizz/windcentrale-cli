<?php

namespace trizz\WindcentraleApi\Daemons;

use Illuminate\Support\Collection;

abstract class AbstractDaemon
{
    protected $millData;

    protected $productionData;

    protected $config;

    protected $millSlug;

    protected $millId;

    public function __construct($config, $millSlug, $millId)
    {
        $this->config = $config;
        $this->millSlug = $millSlug;
        $this->millId = $millId;
    }

    public function setData(Collection $millData, Collection $productionData) : self
    {
        $this->millData = $millData;
        $this->productionData = $productionData;

        return $this;
    }

    abstract public function initialize() : void;

    abstract public function tick() : void;

    public function loop() : void
    {
        //
    }

    public function housekeeping() : void
    {
        //
    }
}