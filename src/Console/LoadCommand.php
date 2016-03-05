<?php

namespace Studio\Console;

use Studio\Package;
use Studio\Config\Config;
use Studio\Shell\Shell;
use Symfony\Component\Console\Input\InputArgument;

class LoadCommand extends BaseCommand
{

    protected $config;


    public function __construct(Config $config)
    {
        parent::__construct();

        $this->config = $config;
    }

    protected function configure()
    {
        $this
            ->setName('load')
            ->setDescription('Load a package to be managed with Studio')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'The path where the package files are located'
            );
    }

    protected function fire()
    {
        $package = Package::fromFolder($this->input->getArgument('path'));
        $this->config->addPackage($package);

        $this->output->success('Package loaded successfully.');
    }

}
