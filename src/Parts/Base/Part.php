<?php

namespace Studio\Parts\Base;

use League\Flysystem\Filesystem;
use Studio\Parts\AbstractPart;

class Part extends AbstractPart
{

    public function setupPackage($composer, Filesystem $target)
    {
        $target->createDir('src');
        $target->createDir('tests');

        $this->copyTo(__DIR__ . '/stubs/gitignore.txt', $target, '.gitignore');
    }

}
