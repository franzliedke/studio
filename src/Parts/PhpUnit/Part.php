<?php

namespace Studio\Parts\PhpUnit;

use League\Flysystem\Filesystem;
use Studio\Parts\PartInterface;

class Part implements PartInterface
{

    public function setupPackage($config, Filesystem $target)
    {
        $config->{'require-dev'}['phpunit/phpunit'] = '4.*';

        $target->write('phpunit.xml', $this->getStubFile('phpunit.xml'));
    }

    protected function getStubFile($name)
    {
        return file_get_contents(__DIR__ . "/stubs/$name");
    }

}
