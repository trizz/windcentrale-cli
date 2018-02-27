<?php

namespace trizz\WindcentraleApi\Daemons;

use Mosquitto\Client AS MqttClient;
use RuntimeException;

class MqttDaemon extends AbstractDaemon
{
    /**
     * @var MqttClient The MQTT client.
     */
    private $mqtt;

    /**
     * @var string[] The topics to use for publishing data.
     */
    private $topics;

    /**
     * Setup a connection to the configured MQTT broker.
     *
     * @throws \RuntimeException
     */
    public function initialize() : void
    {
        $this->topics = [
            'mill' => str_replace(['$slug$', '$id$'], [$this->millSlug, $this->millId], $this->config->get('mqtt')->get('topic_mill')),
            'production' => str_replace(['$slug$', '$id$'], [$this->millSlug, $this->millId], $this->config->get('mqtt')->get('topic_production')),
            'status' => str_replace(['$slug$', '$id$'], [$this->millSlug, $this->millId], $this->config->get('mqtt')->get('topic_status')),
        ];

        $this->mqtt = new MqttClient($this->config->get('mqtt')->get('client_id'));
        $this->mqtt->onConnect(
            function ($rc, $msg) {
                if ($rc !== 0) {
                    throw new RuntimeException($msg);
                }

                $this->mqtt->publish($this->topics['status'], 'connected', 0);
            }
        );

        $caCertificate = $this->config->get('mqtt')->get('certificate_ca_path');

        if ($caCertificate) {
            $this->mqtt->setTlsInsecure(false);
            $this->mqtt->setTlsCertificates($caCertificate);
        }

        $this->mqtt->setCredentials($this->config->get('mqtt')->get('username'), $this->config->get('mqtt')->get('password'));
        $this->mqtt->setWill($this->topics['status'], 'disconnected', 0, false);
        $this->mqtt->connect($this->config->get('mqtt')->get('host'), $this->config->get('mqtt')->get('port'));
    }

    /**
     * Publish the data to the specified topics.
     */
    public function tick() : void
    {
        $this->mqtt->publish($this->topics['mill'], $this->millData);
        $this->mqtt->publish($this->topics['production'], $this->productionData);
    }

    /**
     * Run the MQTT loop.
     */
    public function loop(): void
    {
        $this->mqtt->loop();
    }
}