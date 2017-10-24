<?php

namespace trizz\WindcentraleApi\Export;

use Illuminate\Support\Collection;

abstract class AbstractExporter
{
    /**
     * @var Collection
     */
    protected $data;

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param \Illuminate\Support\Collection $data
     *
     * @return \trizz\WindcentraleApi\Export\AbstractExporter
     */
    public function setData(Collection $data) : self
    {
        $this->data = $data;

        return $this;
    }

    abstract public function exportProduction();
    abstract public function exportMillData();
}