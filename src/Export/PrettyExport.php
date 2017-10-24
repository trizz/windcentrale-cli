<?php

namespace trizz\WindcentraleApi\Export;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Output\ConsoleOutput;

class PrettyExport extends AbstractExporter
{
    public function exportProduction()
    {
        $tableRows = $this->data->map(function ($value, $key) {
            return [ucfirst($key), round($value, 2).' Wh', $this->convert($value)];
        })->toArray();

        $table = new Table(new ConsoleOutput());
        $table->setHeaders([
            ['Period', new TableCell('Produced per windshare', ['colspan' => 2])],
        ]);
        $table->addRows($tableRows);
        $table->render();
    }

    public function exportMillData()
    {
        $tableRows = $this->data->map(function ($value, $key) {
            $key = preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $key);

            if (strpos(strtolower($key), 'wind speed') !== false) {
                $value .= ' bft';
            }

            switch (strtolower($key)) {
                case 'kwh':
                case 'kwh forecast':
                    $value = $this->convert($value);
                    break;
                case 'power rel':
                    $key = 'Current power';
                    $value .= '%';
                    break;
                case 'hours run this year':
                    $value = round($value, 2).' (~'.round($value/24).' days)';
                    break;
                case 'pulsating':
                    $value = $value ? 'Yes' : 'No';
                    break;
                case 'power abs tot':
                    $key = 'Current mill total power';
                    $value .= ' kWh';
                    break;
                case 'power abs wd':
                    $key = 'Current total power per windshare';
                    $value .= ' kWh';
                    break;
                case 'run percentage':
                    $value = round($value, 2).'%';
                    break;
            }

            return [ucwords($key), $value];
        })->toArray();

        $table = new Table(new ConsoleOutput());
        $table->setHeaders([
            [new TableCell(sprintf(
                '<comment>%s</comment> - %s',
                $this->config->get('requestedMill')->get('name'),
                date('Y-m-d H:i:s', REQUEST_TIME)
            ), ['colspan' => 2])],
        ]);
        $table->addRows($tableRows)->render();
    }

    private function convert($value)
    {
        $valueKwh = $value/1000;

        // If the value is smaller than 1kWh, return the Wh.
        if ($valueKwh < 1) {
            return round($value, 2).' Wh';
        }

        // If the value is 1000kWh or more, return the mWh.
        if ($valueKwh >= 1000) {
            return round($valueKwh/1000, 2).' MWh';
        }

        return round($valueKwh, 2).' kWh';
    }
}