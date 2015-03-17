<?php

namespace Studio\Console;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Studio\Package;
use Studio\Config\Config;
use Studio\Shell\Shell;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ScrapCommand extends Command
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');

        if ($this->abortDeletion($path, $output)) {
            $output->writeln("<comment>Aborted.</comment>");
            return;
        }

        $package = Package::fromFolder($path);
        $this->config->removePackage($package);

        $output->writeln("<comment>Removing package...</comment>");
        $filesystem = new Filesystem(new Local(getcwd()));
        $filesystem->deleteDir($path);
        $output->writeln("<info>Package successfully removed.</info>");

        $output->writeln("<comment>Dumping autoloads...</comment>");
        Shell::run('composer dump-autoload');
        $output->writeln("<info>Autoloads successfully generated.</info>");
    }

    protected function abortDeletion($path, OutputInterface $output)
    {
        $dialog = $this->getHelper('dialog');

        return ! $dialog->askConfirmation(
            $output,
            "<question>Do you really want to scrap the package at $path? [y|N]</question> ",
            false
        );
    }

}
