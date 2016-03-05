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
    }

    protected function abortDeletion($path)
    {
        $this->output->caution("This will delete the entire $path folder and all files within.");

        return ! $this->output->confirm(
            "<question>Do you really want to scrap the package at $path?</question> ",
            false
        );
    }

}
