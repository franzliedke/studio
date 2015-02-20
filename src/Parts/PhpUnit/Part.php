<?php

namespace Studio\Parts\PhpUnit;

use League\Flysystem\Filesystem;
use Studio\Parts\PartInterface;

class Part implements PartInterface
{

    public function setupPackage($config, Filesystem $target)
    {
        $config->{'require-dev'}['phpunit/phpunit'] = '4.*';
    }

}
