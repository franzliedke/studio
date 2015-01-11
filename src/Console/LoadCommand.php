<?php

namespace Studio\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('load')
            ->setDescription('Load a package\'s autoloading configuration')
            ->addArgument(
                'package',
                InputArgument::REQUIRED,
                'The name of the package to load'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Thanks, that worked great!');
    }
}
