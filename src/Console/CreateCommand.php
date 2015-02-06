<?php

namespace Studio\Console;

use Studio\Composer\TaskRunner;
use Studio\Config\Config;
use Studio\Creator;
use Studio\Package;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{

    protected $config;

    protected $creator;

    protected $composer;


    public function __construct(Config $config, Creator $creator, TaskRunner $composer)
    {
        parent::__construct();

        $this->config = $config;
        $this->creator = $creator;
        $this->composer = $composer;
    }

    protected function configure()
    {
        $this
            ->setName('create')
            ->setDescription('Create a new package skeleton')
            ->addArgument(
                'package',
                InputArgument::REQUIRED,
                'The name of the package to create'
            )
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_REQUIRED,
                'If set, this will overwrite the default path'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $package = $this->makePackage($input);

        $this->creator->create($package);
        $this->config->addPackage($package);

        $path = $package->getPath();
        $output->writeln("<info>Package directory $path created.</info>");

        $output->writeln("<comment>Running composer install for new package...</comment>");
        $result = $this->composer->run('install', $package->getPath());
        $output->write($result);

        $output->writeln("<info>Package successfully created.</info>");
    }

    protected function makePackage(InputInterface $input)
    {
        $name = $input->getArgument('package');
        $author = 'Franz Liedke';
        $email = 'franz@email.org';

        if (! str_contains($name, '/')) {
            throw new \InvalidArgumentException('Invalid package name');
        }

        list($vendor, $package) = explode('/', $name, 2);
        $path = $input->getOption('path') ?: $package;

        return new Package($vendor, $package, $author, $email, $path);
    }
}
