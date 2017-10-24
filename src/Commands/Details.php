<?php

namespace trizz\WindcentraleApi\Commands;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Details extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('details')
            ->addArgument('mill', InputArgument::REQUIRED, 'The short name of the mill to get the data for.')
            ->addOption('export', 'e', InputOption::VALUE_OPTIONAL, 'The name of the exporter.', 'pretty')
            ->setDescription('Get production and mill data.')
            ->setHelp('Get the current production and mill data for a specific mill.');
    }

    protected function handle()
    {
        $this->checkMillSlug();

        $this->runCommand('mill_data');
        $this->runCommand('production');

        return 0;
    }

    private function runCommand($commandString)
    {
        $command = $this->getApplication()->find($commandString);
        $arguments = [
            'command' => $commandString,
            'mill' => $this->input->getArgument('mill'),
            '--export' => $this->input->getOption('export')
        ];

        $inputData = new ArrayInput($arguments);

        return $command->run($inputData, $this->output);
    }
}