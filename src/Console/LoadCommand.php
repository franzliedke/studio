<?php

namespace Studio\Console;

use Studio\Package;
use Studio\Config\Config;
use Studio\Shell\Shell;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class LoadCommand extends Command
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $package = Package::fromFolder($input->getArgument('path'));
        $this->config->addPackage($package);

        $output->writeln("<info>Package loaded successfully.</info>");

        $output->writeln("<comment>Dumping autoloads...</comment>");
        Shell::run('composer dump-autoload');
        $output->writeln("<info>Autoloads successfully generated.</info>");
    }

}
