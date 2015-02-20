<?php

namespace Studio\Parts\Composer;

use League\Flysystem\Filesystem;
use Studio\Parts\AbstractPart;

class Part extends AbstractPart
{

    public function setupPackage($composer, Filesystem $target)
    {
        $composer->name = $this->input->ask('Please name this package');
    }

}
