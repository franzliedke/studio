<?php

namespace Studio\Console;

use Studio\Package;
use Studio\Shell\TaskRunner;
use Studio\Config\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class LoadCommand extends Command
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
        $this->runOnShell('composer dump-autoload');
        $output->writeln("<info>Autoloads successfully generated.</info>");
    }

    protected function runOnShell($task, $workDir = null)
    {
        $process = $this->shell->process($task, $workDir);
        $process->run();

        if (! $process->isSuccessful()) {
            $command = collect(explode(' ', $task))->first();
            $error = $process->getErrorOutput();
            throw new \RuntimeException("Error while running $command: $error");
        }

        return $process->getOutput();
    }

}
