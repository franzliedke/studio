<?php

namespace Studio\Parts\TravisCI;

use League\Flysystem\Filesystem;
use Studio\Parts\AbstractPart;

class Part extends AbstractPart
{

    public function setupPackage($composer, Filesystem $target)
    {
        if ($this->input->confirm('Do you want to set up TravisCI as continuous integration tool?')) {
            $target->write('.travis.yml', $this->getStubFile('.travis.yml'));
        }
    }

    protected function getStubFile($name)
    {
        return file_get_contents(__DIR__ . "/stubs/$name");
    }

}
