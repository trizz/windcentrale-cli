<?php

namespace trizz\WindcentraleApi\Commands;

use React\EventLoop\Factory;
use Symfony\Component\Console\Input\InputArgument;
use trizz\WindcentraleApi\Daemons\AbstractDaemon;
use trizz\WindcentraleApi\Windcentrale;

class Daemon extends AbstractCommand
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
     * @var AbstractDaemon[] Registered daemons.
     */
    private $daemons;

    /**
     * {@inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('daemon')
            ->addArgument('mill', InputArgument::REQUIRED, 'The short name of the mill to get the data for.')
            ->addArgument('daemons', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The daemons that must be executed.')
            ->setDescription(':-)')
            ->setHelp('?:-)');
    }

    /**
     * Run the daemon in a loop.
     *
     * @return int The exit code.
     */
    protected function handle() : int
    {
        $this->checkMillSlug();

        // Set the required values.
        $this->millSlug = $this->input->getArgument('mill');
        $this->millId = $this->config->get('requestedMill')->get('id');
        $this->windcentrale = new Windcentrale($this->config);

        $this->loadDaemons();

        // Create the app loop.
        $loop = Factory::create();

        $loop->addPeriodicTimer(60, function () {
            foreach ($this->daemons as $daemon) {
                $daemon->housekeeping();
            }
        });

        $loop->addPeriodicTimer(5, function () {
            $millData = $this->windcentrale->getMillData($this->millId);
            $productionData = $this->windcentrale->getProductionData($this->millId);

            if ($millData === null || $productionData === null) {
                return;
            }

            foreach ($this->daemons as $daemon) {
                $daemon->setData($millData, $productionData)->tick();
            }
        });

        $loop->addPeriodicTimer(0.1, function () {
            foreach ($this->daemons as $daemon) {
                $daemon->loop();
            }
        });

        // Run the loop.
        $loop->run();

        return 0;
    }

    private function loadDaemons() : void
    {
        $this->daemons = [];
        foreach ($this->input->getArgument('daemons') as $daemonName) {
            $deamonClassName = '\trizz\WindcentraleApi\Daemons\\'.ucfirst($daemonName).'Daemon';
            /** @var \trizz\WindcentraleApi\Daemons\AbstractDaemon $daemonClass */
            $daemonClass = new $deamonClassName($this->config, $this->millSlug, $this->millId);
            $daemonClass->initialize();
            $this->daemons[$daemonName] = $daemonClass;
        }
    }
}