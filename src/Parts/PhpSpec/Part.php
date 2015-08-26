<?php

namespace Studio\Parts\PhpSpec;

use Studio\Filesystem\Directory;
use Studio\Parts\AbstractPart;

class Part extends AbstractPart
{

    public function setupPackage($composer, Directory $target)
    {
        if ($this->input->confirm('Do you want to set up PhpSpec as a testing tool?')) {
            $composer->{'require-dev'}['phpspec/phpspec'] = '~2.0';

            $namespace = head(array_keys((array) $composer->autoload->{'psr-4'}));
            $namespace = rtrim($namespace, '\\');

            $this->copyTo(
                __DIR__ . '/stubs/phpspec.yml',
                $target,
                'phpspec.yml',
                function ($content) use ($namespace) {
                    return str_replace(': Foo\Bar', ": $namespace", $content);
                }
            );

            $target->makeDir('spec');
        }
    }

}
