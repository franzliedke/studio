<?php

namespace Studio\Shell;

use Symfony\Component\Process\Process;

class TaskRunner
{

    public function process($task, $directory = null)
    {
        $process = new Process("$task", $directory);
        $process->setTimeout(3600);

        return $process;
    }

}
