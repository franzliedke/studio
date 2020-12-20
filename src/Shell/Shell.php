<?php

namespace Studio\Shell;

use ReflectionClass;
use RuntimeException;
use Symfony\Component\Process\Process;

class Shell
{
    public static function run($task, $directory = null)
    {
        $process = self::makeProcess($task, $directory);
        $process->setTimeout(3600);

        $process->run();

        if (! $process->isSuccessful()) {
            $command = preg_replace('/ .+$/', '', $task);
            $error = $process->getErrorOutput();
            throw new RuntimeException("Error while running $command: $error");
        }

        return $process->getOutput();
    }

    private static function makeProcess($task, $directory)
    {
        $reflection = new ReflectionClass(Process::class);
        $params = $reflection->getConstructor()->getParameters();
        $type = $params[0]->getType();

        if ($type && $type->getName() === 'array') { // Symfony 5
            return new Process(explode(' ', $task), $directory);
        } else { // Older versions
            return new Process($task, $directory);
        }
    }
}
