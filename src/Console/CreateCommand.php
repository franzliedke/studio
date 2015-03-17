<?php

namespace Studio\Console;

use Studio\Parts\ConsoleInput;
use Studio\Shell\Shell;
use Studio\Config\Config;
use Studio\Creator\CreatorInterface;
use Studio\Creator\GitRepoCreator;
use Studio\Creator\SkeletonCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateCommand extends Command
{

    protected $config;

    protected $partClasses = [
        'Studio\Parts\Base\Part',
        'Studio\Parts\Composer\Part',
        'Studio\Parts\PhpUnit\Part',
        'Studio\Parts\PhpSpec\Part',
        'Studio\Parts\TravisCI\Part',
    ];

    /**
     * @var \Studio\Parts\PartInputInterface
     */
    protected $partInput;


    public function __construct(Config $config)
    {
        parent::__construct();

        $this->config = $config;
    }

    protected function configure()
    {
        $this
            ->setName('create')
            ->setDescription('Create a new package skeleton')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'The path where the new package should be created'
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
        $this->partInput = new ConsoleInput($this->getHelper('dialog'), $output);

        $creator = $this->makeCreator($input);

        $package = $creator->create();
        $this->config->addPackage($package);

        $path = $package->getPath();
        $output->writeln("<info>Package directory $path created.</info>");

        $output->writeln("<comment>Running composer install for new package...</comment>");
        Shell::run('composer install --prefer-dist', $package->getPath());
        $output->writeln("<info>Package successfully created.</info>");

        $output->writeln("<comment>Dumping autoloads...</comment>");
        Shell::run('composer dump-autoload');
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
        $path = $input->getArgument('path');

        if ($input->getOption('git')) {
            return new GitRepoCreator($input->getOption('git'), $path);
        } else {
            $creator = new SkeletonCreator($path);
            $this->installParts($creator);
            return $creator;
        }
    }

    protected function installParts(SkeletonCreator $creator)
    {
        $parts = $this->makeParts();

        foreach ($parts as $part) {
            $creator->addPart($part);
        }
    }

    /**
     * @return \Studio\Parts\AbstractPart[]
     */
    protected function makeParts()
    {
        return array_map(function ($class) {
            return (new $class)->setInput($this->partInput);
        }, $this->partClasses);
    }

}
