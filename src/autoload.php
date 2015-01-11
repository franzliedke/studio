<?php

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

$finder = new Finder();
$files = new Filesystem();

// TODO: Cache all components that are supervised by studio
$directories = [];

// Find all Composer autoloader files in the supervised packages' directories
// so that we can include and setup all of their dependencies.
$autoloaders = $finder->in($directories)->files()->name('autoload.php')->depth('<= 3')->followLinks();

foreach ($autoloaders as $file) {
    $files->requireOnce($file);
}
