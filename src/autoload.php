<?php

use Illuminate\Filesystem\Filesystem;
use Studio\Config\Config;
use Studio\Config\FileStorage;
use Symfony\Component\Finder\Finder;

$finder = new Finder();
$files = new Filesystem();
$config = new Config(new FileStorage(__DIR__.'/../studio.json'));

$directories = $config->getPackages();

if ($directories) {
    // Find all Composer autoloader files in the supervised packages' directories
    // so that we can include and setup all of their dependencies.
    $autoloaders = $finder->in($directories)->files()->name('autoload.php')->depth('<= 3')->followLinks();

    foreach ($autoloaders as $file) {
        $files->requireOnce($file);
    }
}
