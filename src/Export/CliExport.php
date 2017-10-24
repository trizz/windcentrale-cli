<?php

namespace trizz\WindcentraleApi\Export;

class CliExport extends AbstractExporter
{
    public function exportProduction()
    {
        $this->export();
    }

    public function exportMillData()
    {
        $this->export();
    }

    private function export()
    {
        $this->data->each(function ($value, $key) {
            echo sprintf('%s: %s', $key, $value)."\n";
        });
    }
}