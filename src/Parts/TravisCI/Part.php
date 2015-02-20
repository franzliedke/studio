<?php

namespace Studio\Parts\TravisCI;

use League\Flysystem\Filesystem;
use Studio\Parts\PartInterface;

class Part implements PartInterface
{

    public function setupPackage($config, Filesystem $target)
    {
        $target->write('.travis.yml', $this->getStubFile('.travis.yml'));
    }

    protected function getStubFile($name)
    {
        return file_get_contents(__DIR__ . "/stubs/$name");
    }

}
