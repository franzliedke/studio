<?php

namespace StudioTests\Console;

use PHPUnit\Framework\TestCase;
use Studio\Console\CreateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractConsoleTest extends TestCase
{
    protected function executeCommand(array $arguments, array $inputs = []): CommandTester
    {
        $application = new Application('studio', getenv('APP_VERSION'));
        $application->add(new CreateCommand);

        // this uses a special testing container that allows you to fetch private services
        /** @var Command $command */
        $command = $application->get($this->getCommandFqcn());

        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);
        $commandTester->execute($arguments);

        return $commandTester;
    }

    abstract protected function getCommandFqcn(): string;
}