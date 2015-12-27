<?php

namespace Studio\Console;

use Studio\Package;
use Studio\Config\Config;
use Studio\Shell\Shell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;

class ScrapCommand extends BaseCommand
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
            ->setName('scrap')
            ->setDescription('Delete a previously created package skeleton')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'The path where the package resides'
            );
    }

    protected function fire()
    {
        $path = $this->input->getArgument('path');

        if ($this->abortDeletion($path)) {
            $this->output->note('Aborted.');
            return;
        }

        $package = Package::fromFolder($path);
        $this->config->removePackage($package);

        $this->output->note('Removing package...');
        $filesystem = new Filesystem;
        $filesystem->remove($path);
        $this->output->success('Package successfully removed.');

        $this->output->note('Dumping autoloads...');
        Shell::run('composer dump-autoload');
        $this->output->success('Autoloads successfully generated.');
    }

    protected function abortDeletion($path)
    {
        return ! $this->output->confirm(
            "<question>Do you really want to scrap the package at $path?</question> ",
            false
        );
    }

}
