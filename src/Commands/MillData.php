<?php

namespace trizz\WindcentraleApi\Commands;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use trizz\WindcentraleApi\Windcentrale;

class MillData extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('mill_data')
            ->setAliases(['milldata', 'mill'])
            ->addArgument('mill', InputArgument::REQUIRED, 'The short name of the mill to get the data for.')
            ->addOption('export', 'e', InputOption::VALUE_OPTIONAL, 'The name of the exporter.', 'pretty')
            ->setDescription('Get the live mill data.')
            ->setHelp('Get the current live mill data for a specific mill.');
    }

    protected function handle()
    {
        $this->checkMillSlug();

        $millId = $this->config->get('requestedMill')->get('id');
        $windcentrale = new Windcentrale($this->config);

        $this->getExporter($windcentrale->getMillData($millId))->exportMillData();

        return 0;
    }
}