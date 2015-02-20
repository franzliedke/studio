<?php

namespace Studio\Parts\PhpUnit;

use Studio\Parts\PartInterface;

class Part implements PartInterface
{

    public function configureComposer($config)
    {
        $config->{'require-dev'}['phpunit/phpunit'] = '4.*';
    }

}
