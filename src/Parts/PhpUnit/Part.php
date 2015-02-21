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

            $target->write('phpunit.xml', $this->getStubFile('phpunit.xml'));
        }
    }

    protected function getStubFile($name)
    {
        return file_get_contents(__DIR__ . "/stubs/$name");
    }

}
