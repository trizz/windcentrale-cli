<?php

namespace trizz\WindcentraleApi\Daemons;

use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\build_query;
use trizz\WindcentraleApi\Export\CliExport;

class DsmrReaderDaemon extends AbstractDaemon
{
    /**
     * @var CliExport
     */
    private $template;

    /**
     * @var string The timestamp of the last sent message.
     */
    private $lastTimestamp;

    /**
     * @var string The full DSMR Reader API URL.
     */
    private $url;

    /**
     * @var string The API key.
     */
    private $apiKey;

    /**
     * Setup the daemon.
     */
    public function initialize() : void
    {
        $this->template = [
            'electricity_currently_delivered' => 0,
            'electricity_currently_returned' => 0,
            'electricity_delivered_1' => 0,
            'electricity_delivered_2' => 0,
            'electricity_returned_1' => 0,
            'electricity_returned_2' => 0,
            'timestamp' => '',
        ];

        $this->url = $this->config->get('dsmr_reader')->get('url').'/api/v2/datalogger/dsmrreading';
        $this->apiKey = $this->config->get('dsmr_reader')->get('api_key');
    }

    /**
     * Publish the data.
     */
    public function tick() : void
    {
        $timestamp = $this->millData['timestamp'].'+0'.(1 + date('I'));

        // Don't send anything if the timestamp is the same as in the previous tick.
        if ($timestamp === $this->lastTimestamp) {
            return;
        }

        $data = $this->template;

        $data['electricity_returned_2'] = round($this->productionData['day'] * 6 / 1000, 3);
        $data['electricity_currently_returned'] = round($this->millData['powerAbsWd'] * 6 / 1000, 3);
        $data['timestamp'] = $timestamp;

        $this->lastTimestamp = $timestamp;

        $client = new Client();
        $client->post(
            $this->url,
            [
                'headers' => [
                    'X-AUTHKEY' => $this->apiKey,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => build_query($data),
            ]
        );
    }
}