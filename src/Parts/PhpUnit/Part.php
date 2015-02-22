<?php

namespace Studio\Parts\PhpUnit;

use League\Flysystem\Filesystem;
use Studio\Parts\AbstractPart;

class Part extends AbstractPart
{

    public function setupPackage($composer, Filesystem $target)
    {
        if ($this->input->confirm('Do you want to set up PhpUnit as a testing tool?')) {
            $composer->{'require-dev'}['phpunit/phpunit'] = '4.*';

            $this->copyTo(__DIR__ . '/stubs/phpunit.xml', $target);
        }
    }

}
