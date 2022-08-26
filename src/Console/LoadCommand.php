<?php

namespace Studio\Console;

use Studio\Config\Config;
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
            ->setDescription('Load a path to be managed with Studio')
            ->addArgument(
                'path',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'The path(s) where the package files are located'
            );
    }

    protected function fire()
    {
        foreach ($this->input->getArgument('path') as $path) {
            $this->config->addPath($path);
            
            $this->io->success("Packages matching the path $path will now be loaded by Composer.");
        }       
    }
}
