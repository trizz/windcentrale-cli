<?php

namespace trizz\WindcentraleApi\Commands;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Collection;
use Mosquitto\Client AS MqttClient;
use React\EventLoop\Factory;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use trizz\WindcentraleApi\Windcentrale;

class MqttDaemon extends AbstractCommand
{
    /**
     * @var int The mill ID.
     */
    private $millId;

    /**
     * @var Windcentrale The Windcentrale API class.
     */
    private $windcentrale;

    /**
     * @var string The mill slug, used for the topic.
     */
    private $millSlug;

    /**
     * {@inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('mqtt-daemon')
            ->addArgument('mill', InputArgument::REQUIRED, 'The short name of the mill to get the data for.')
            ->setDescription(':-)')
            ->setHelp('?:-)');
    }

    /**
     * Run the daemon in a loop.
     *
     * @return int The exit code.
     */
    protected function handle()
    {
        $this->checkMillSlug();

        // Set the required values.
        $this->millSlug = $this->input->getArgument('mill');
        $this->millId = $this->config->get('requestedMill')->get('id');
        $this->windcentrale = new Windcentrale($this->config);

        // Set up a MQTT connection.
        $mqtt = $this->connectMqtt();

        // Create the app loop.
        $loop = Factory::create();

        // Add a 5 second timer that will publish the mill data to the MQTT broker.
        $loop->addPeriodicTimer(5, function () use ($mqtt) {
            $mqtt->publish('windcentrale/'.$this->millSlug.'/mill', $this->windcentrale->getMillData($this->millId));
            $mqtt->publish('windcentrale/'.$this->millSlug.'/production', $this->windcentrale->getProductionData($this->millId));
        });

        // Add a 0.1 second timer that will process the MQTT loop.
        $loop->addPeriodicTimer(0.1, function () use ($mqtt) {
            $mqtt->loop();
        });

        // Run the loop.
        $loop->run();

        return 0;
    }

    /**
     * Setup a connection to the configured MQTT broker.
     *
     * @return MqttClient The conneted MQTT client.
     */
    private function connectMqtt(): MqttClient
    {
        $mqtt = new MqttClient($this->config->get('mqtt')->get('client_id'));
        $mqtt->onConnect(
            function ($rc, $msg) use ($mqtt) {
                if ($rc !== 0) {
                    throw new RuntimeException($msg);
                }

                $mqtt->publish('windcentrale/'.$this->millSlug.'/status', 'connected', 0);
            }
        );

        $caCertificate = $this->config->get('mqtt')->get('certificate_ca_path');

        if ($caCertificate) {
            $mqtt->setTlsInsecure(false);
            $mqtt->setTlsCertificates($caCertificate);
        }

        $mqtt->setCredentials($this->config->get('mqtt')->get('username'), $this->config->get('mqtt')->get('password'));
        $mqtt->setWill('windcentrale/'.$this->millSlug.'/status', 'disconnected', 0, false);
        $mqtt->connect($this->config->get('mqtt')->get('host'), $this->config->get('mqtt')->get('port'));

        return $mqtt;
    }
}