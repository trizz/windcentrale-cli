<?php

namespace trizz\WindcentraleApi\Export;

use InfluxDB\Client as InfluxDb;
use InfluxDB\Database;
use InfluxDB\Point;

class InfluxExport extends AbstractExporter
{
    /**
     * @var \InfluxDB\Database
     */
    private $influxDb;

    public function __construct($config)
    {
        parent::__construct($config);

        $client = new InfluxDb(
            $config->get('influx')->get('host', 'localhost'),
            $config->get('influx')->get('port', 8086),
            $config->get('influx')->get('username', ''),
            $config->get('influx')->get('password', ''),
            $config->get('influx')->get('ssl', false),
            $config->get('influx')->get('verifySsl', false),
            $config->get('influx')->get('timeout', 0)
        );
        $this->influxDb = $client->selectDB($config->get('influx')->get('database', 'windcentrale'));
    }

    public function exportProduction()
    {
        $points = $this->data->map(function ($value, $key) {
            return new Point('total_'.$key, $value, [], [], REQUEST_TIME);
        })->values()->toArray();

        $this->influxDb->writePoints($points, Database::PRECISION_SECONDS);
    }

    public function exportMillData()
    {
        $this->data = $this->data->forget('timestamp');
        $points = $this->data->map(function ($value, $key) {
            $key = preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', '_$0', $key);
            return new Point(strtolower($key), $value, [], [], REQUEST_TIME);
        })->values()->toArray();

        $this->influxDb->writePoints($points, Database::PRECISION_SECONDS);
    }
}