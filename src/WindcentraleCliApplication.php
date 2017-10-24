<?php

namespace trizz\WindcentraleApi;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;
use trizz\WindcentraleApi\Commands\Details;
use trizz\WindcentraleApi\Commands\MillData;
use trizz\WindcentraleApi\Commands\Mills;
use trizz\WindcentraleApi\Commands\Production;

class WindcentraleCliApplication extends Application
{
    /**
     * WindcentraleCliApplication constructor.
     */
    public function __construct()
    {
        parent::__construct('Windcentrale CLI', '1.0.0');
        $this->registerAppCommands();
    }

    private function registerAppCommands()
    {
        $config = $this->r_collect(Yaml::parse(file_get_contents(__DIR__.'/../config.yml')));

        $this->addCommands([
            new Production($config),
            new MillData($config),
            new Details($config),
            new Mills($config),
        ]);
    }

    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),

            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'),
            new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output'),
        ));
    }

    private function r_collect($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->r_collect($value);
                $array[$key] = $value;
            }
        }

        return collect($array);
    }
}