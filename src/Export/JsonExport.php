<?php

namespace trizz\WindcentraleApi\Export;

class JsonExport extends AbstractExporter
{
    public function exportProduction()
    {
        echo $this->data->toJson()."\n";
    }

    public function exportMillData()
    {
        echo $this->data->toJson()."\n";
    }
}