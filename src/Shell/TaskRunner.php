<?php

namespace Studio\Shell;

use Symfony\Component\Process\Process;

class TaskRunner
{

    public function run($task, $directory = null)
    {
        $process = new Process("$task", $directory);
        $process->run();

        if (! $process->isSuccessful()) {
            $error = $process->getErrorOutput();
            throw new \RuntimeException("Error while running Composer: $error");
        }

        return $process->getOutput();
    }

}
