<?php

namespace Studio\Console;

use Illuminate\Filesystem\Filesystem;
use Studio\Shell\TaskRunner;
use Studio\Config\Config;
use Studio\Creator\CreatorInterface;
use Studio\Creator\GitRepoCreator;
use Studio\Creator\SkeletonCreator;
use Studio\Package;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{

    protected $config;

    protected $shell;


    public function __construct(Config $config, TaskRunner $shell)
    {
        parent::__construct();

        $this->config = $config;
        $this->shell = $shell;
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
            )
            ->addOption(
                'git',
                'g',
                InputOption::VALUE_REQUIRED,
                'If set, this will download the given Git repository instead of creating a new one.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $creator = $this->makeCreator($input);

        $package = $creator->create();
        $this->config->addPackage($package);

        $path = $package->getPath();
        $output->writeln("<info>Package directory $path created.</info>");

        $output->writeln("<comment>Running composer install for new package...</comment>");
        $this->shell->run('composer install', $package->getPath());
        $output->writeln("<info>Package successfully created.</info>");

        $output->writeln("<comment>Dumping autoloads...</comment>");
        $this->shell->run('composer dump-autoload');
        $output->writeln("<info>Autoloads successfully generated.</info>");
    }

    /**
     * Build a package creator from the given input options.
     *
     * @param InputInterface $input
     * @return CreatorInterface
     */
    protected function makeCreator(InputInterface $input)
    {
        $name = $input->getArgument('package');

        if (! str_contains($name, '/')) {
            throw new \InvalidArgumentException('Invalid package name');
        }

        list($vendor, $package) = explode('/', $name, 2);
        $path = $input->getOption('path') ?: $package;

        if ($input->hasOption('git')) {
            return new GitRepoCreator($input->getOption('git'), $path, $this->shell);
        } else {
            $author = 'Franz Liedke';
            $email = 'franz@email.org';

            $package = new Package($vendor, $package, $author, $email, $path);

            return new SkeletonCreator(new Filesystem, $package);
        }
    }

}
