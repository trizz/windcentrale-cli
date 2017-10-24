<?php

namespace trizz\WindcentraleApi\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use trizz\WindcentraleApi\Windcentrale;

class Production extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('production')
            ->addArgument('mill', InputArgument::REQUIRED, 'The short name of the mill to get the data for.')
            ->addOption('export', 'e', InputOption::VALUE_OPTIONAL, 'The name of the exporter.', 'pretty')
            ->setDescription('Get production data.')
            ->setHelp('Get the current production data for a specific mill.');
    }

    protected function handle()
    {
        $this->checkMillSlug();

        $millId = $this->config->get('requestedMill')->get('id');
        $windcentrale = new Windcentrale($this->config);

        $this->getExporter($windcentrale->getProductionData($millId))->exportProduction();

        return 0;
    }
}