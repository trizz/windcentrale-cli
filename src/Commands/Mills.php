<?php

namespace trizz\WindcentraleApi\Commands;

class Mills extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('mills')
            ->setDescription('Display the available mills.')
            ->setHelp('Display a list with available mills and their short names to use for other commands.');
    }

    protected function handle()
    {
        $this->cliStyle->title('Available Windcentrale windmills');

        $tableRows = $this->config->get('mills')->map(function ($mill) {
            return [$mill->get('name'), $mill->get('slug')];
        })->toArray();

        $this->cliStyle->table(['Name', 'Short name'], $tableRows);
        $this->cliStyle->text('You can use the \'short name\' for other commands where you must specify a mill name.');

        return 0;
    }
}