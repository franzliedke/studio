<?php

namespace Studio\Console;

use Studio\Config\Config;
use Studio\Package;
use Symfony\Component\Console\Input\InputArgument;

class UnloadCommand extends BaseCommand {
    protected $config;

    public function __construct(Config $config) {
        parent::__construct();

        $this->config = $config;
    }

    protected function configure() {
        $this
            ->setName('unload')
            ->setDescription('Unload a path managed by Studio')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'The path where the package resides'
            );
    }

    protected function fire() {
        $path = $this->input->getArgument('path');

        $package = Package::fromFolder($path);
        $this->config->removePackage($package);

        $this->io->success("Packages matching the path $path won't be loaded by Composer anymore.");
    }

}
