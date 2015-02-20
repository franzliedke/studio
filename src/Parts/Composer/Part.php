<?php

namespace Studio\Parts\Composer;

use League\Flysystem\Filesystem;
use Studio\Parts\AbstractPart;

class Part extends AbstractPart
{

    public function setupPackage($config, Filesystem $target)
    {
        $config->name = $this->input->ask('Please name this package');
    }

}
