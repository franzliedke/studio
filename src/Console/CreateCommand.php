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
use Symfony\Component\Console\Question\Question;

class CreateCommand extends Command
{

    protected $config;

    protected $shell;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;


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
        $this->input = $input;
        $this->output = $output;

        $this->createPackage();
    }

    protected function createPackage()
    {
        $creator = $this->makeCreator();

        $package = $creator->create();
        $this->config->addPackage($package);

        $path = $package->getPath();
        $this->output->writeln("<info>Package directory $path created.</info>");

        $this->output->writeln("<comment>Running composer install for new package...</comment>");
        $this->shell->run('composer install --prefer-dist', $package->getPath());
        $this->output->writeln("<info>Package successfully created.</info>");

        $this->output->writeln("<comment>Dumping autoloads...</comment>");
        $this->shell->run('composer dump-autoload');
        $this->output->writeln("<info>Autoloads successfully generated.</info>");
    }

    /**
     * Build a package creator from the given input options.
     *
     * @return CreatorInterface
     */
    protected function makeCreator()
    {
        $name = $this->askForPackageName();

        list($vendor, $package) = explode('/', $name, 2);
        $path = $this->input->getArgument('path');

        if ($this->input->getOption('git')) {
            return new GitRepoCreator($this->input->getOption('git'), $path, $this->shell);
        } else {
            $package = new Package($vendor, $package, $path);

            return new SkeletonCreator(new Filesystem, $package);
        }
    }

    /**
     * @return string
     */
    protected function askForPackageName()
    {
        do {
            $name = $this->ask('Please enter the package name');
        } while (strpos($name, '/') === false);

        return $name;
    }

    /**
     * @param string $text
     * @return string
     */
    protected function ask($text)
    {
        $helper = $this->getHelperSet()->get('question');
        $question = new Question("<question>$text</question> ");
        return $helper->ask($this->input, $this->output, $question);
    }

}
