<?php

namespace Studio\Parts\Composer;

use League\Flysystem\Filesystem;
use Studio\Parts\AbstractPart;

class Part extends AbstractPart
{

    public function setupPackage($composer, Filesystem $target)
    {
        // Ask for package name
        $composer->name = $this->input->ask(
            'Please name this package',
            function ($value) {
                return preg_match('/[[:alnum:]]+\/[[:alnum:]]+/', $value);
            }
        );

        // Ask for the root namespace
        $namespace = $this->input->ask(
            'Please provide a default namespace (PSR-4)',
            function ($value) {
                return preg_match('/([[:alnum:]]+\\\\?)+/', $value);
            },
            $this->makeDefaultNamespace($composer->name)
        );

        // Normalize and store the namespace
        $namespace = rtrim($namespace, '\\');
        @$composer->autoload->{'psr-4'}->{$namespace} = 'src/';

        // Create an example file
        $this->copyTo(
            __DIR__ . '/stubs/src/Example.php',
            $target,
            'src/Example.php',
            function ($content) use ($namespace) {
                return preg_replace('/namespace[^;]+;/', "namespace $namespace;", $content);
            }
        );
    }

    protected function makeDefaultNamespace($package)
    {
        list($vendor, $name) = explode('/', $package);

        return ucfirst($vendor) . '\\' . ucfirst($name);
    }

}
