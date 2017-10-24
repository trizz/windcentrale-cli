<?php

namespace trizz\WindcentraleApi\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use trizz\WindcentraleApi\Export\AbstractExporter;

abstract class AbstractCommand extends Command
{
    /**
     * @var \Illuminate\Support\Collection The config collection.
     */
    protected $config;

    /**
     * @var SymfonyStyle
     */
    protected $cliStyle;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct($config)
    {
        parent::__construct();
        $this->config = $config;
    }

    /**
     * @param null $data
     *
     * @return \trizz\WindcentraleApi\Export\AbstractExporter
     */
    public function getExporter($data = null) : AbstractExporter
    {
        $exportClassName = sprintf('\\trizz\WindcentraleApi\\Export\\%sExport', ucfirst($this->input->getOption('export')));

        if (!class_exists($exportClassName)) {
            $this->cliStyle->error(sprintf('The defined export [%s] is invalid.', $this->input->getOption('export')));
            $this->cliStyle->newLine();

            exit(2);
        }

        /** @var AbstractExporter $exportClass */
        $exportClass = new $exportClassName($this->config);

        return $data !== null ? $exportClass->setData($data) : $exportClass;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->cliStyle = new SymfonyStyle($input, $output);

        return $this->handle();
    }

    protected function checkMillSlug()
    {
        if (!$this->config->get('mills')->pluck('slug')->contains($this->input->getArgument('mill'))) {
            $this->cliStyle->error(
                sprintf('The passed short mill name [%s] is invalid.', $this->input->getArgument('mill'))
            );
            $this->cliStyle->text('Use \'./windcentrale mills\' to show a list of available windmills.');
            $this->cliStyle->newLine();

            exit(1);
        }

        $this->config->put('requestedMill', $this->config->get('mills')->where('slug', $this->input->getArgument('mill'))->first());
    }

    abstract protected function handle();
}