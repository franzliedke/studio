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
        $creator = $this->makeCreator($input, $output);

        $package = $creator->create();
        $this->config->addPackage($package);

        $path = $package->getPath();
        $output->writeln("<info>Package directory $path created.</info>");

        $output->writeln("<comment>Running composer install for new package...</comment>");
        $this->shell->run('composer install --prefer-dist', $package->getPath());
        $output->writeln("<info>Package successfully created.</info>");

        $output->writeln("<comment>Dumping autoloads...</comment>");
        $this->shell->run('composer dump-autoload');
        $output->writeln("<info>Autoloads successfully generated.</info>");
    }

    /**
     * Build a package creator from the given input options.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return CreatorInterface
     */
    protected function makeCreator(InputInterface $input, OutputInterface $output)
    {
        $name = $this->askForPackageName($input, $output);

        list($vendor, $package) = explode('/', $name, 2);
        $path = $input->getArgument('path');

        if ($input->getOption('git')) {
            return new GitRepoCreator($input->getOption('git'), $path, $this->shell);
        } else {
            $description = $this->askForDescription($input, $output);
            $author = $this->askForAuthorName($input, $output);
            $email = $this->askForAuthorMail($input, $output);

            $package = new Package($vendor, $package, $description, $author, $email, $path);

            return new SkeletonCreator(new Filesystem, $package);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    protected function askForPackageName(InputInterface $input, OutputInterface $output)
    {
        do {
            $name = $this->ask($input, $output, 'Please enter the package name');
        } while (strpos($name, '/') === false);

        return $name;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    protected function askForDescription(InputInterface $input, OutputInterface $output)
    {
        return $this->ask($input, $output, 'Please enter a description for your package');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    protected function askForAuthorName(InputInterface $input, OutputInterface $output)
    {
        return $this->ask($input, $output, 'Please enter your name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    protected function askForAuthorMail(InputInterface $input, OutputInterface $output)
    {
        return $this->ask($input, $output, 'Please enter your email address');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $text
     * @return string
     */
    protected function ask(InputInterface $input, OutputInterface $output, $text)
    {
        $helper = $this->getHelperSet()->get('question');
        $question = new Question("<question>$text</question> ");
        return $helper->ask($input, $output, $question);
    }

}
