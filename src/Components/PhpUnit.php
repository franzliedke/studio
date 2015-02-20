<?php

namespace Studio\Components;

class PhpUnit implements ComponentInterface
{

    public function configureComposer($config)
    {
        $config->{'require-dev'}['phpunit/phpunit'] = '4.*';
    }

}
