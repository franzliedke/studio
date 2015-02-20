<?php

namespace Studio\Parts\Base;

use League\Flysystem\Filesystem;
use Studio\Parts\PartInterface;

class Part implements PartInterface
{

    public function setupPackage($config, Filesystem $target)
    {
        $target->createDir('src');
        $target->createDir('tests');

        $target->write('.gitignore', $this->getStubFile('gitignore.txt'));
    }

    protected function getStubFile($name)
    {
        return file_get_contents(__DIR__ . "/stubs/$name");
    }

}
