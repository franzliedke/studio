<?php

namespace Studio\Config;

use Studio\Package;

class Config
{
    /**
     * @var Storage
     */
    protected $storage;

    protected $paths;

    protected $loaded = false;


    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public static function make($file = null)
    {
        if (is_null($file)) {
            $file = getcwd().'/studio.json';
        }

        return new static(
            new Version1Storage($file)
        );
    }

    public function getPaths()
    {
        if (! $this->loaded) {
            $this->paths = $this->storage->readPaths();
            $this->loaded = true;
        }

        return $this->paths;
    }

    public function addPackage(Package $package)
    {
        // Ensure our packages are loaded
        $this->getPaths();

        $this->paths[] = $package->getPath();
        $this->storage->writePaths($this->paths);
    }

    public function hasPackages()
    {
        // Ensure our packages are loaded
        $this->getPaths();

        return ! empty($this->paths);
    }

    public function removePackage(Package $package)
    {
        // Ensure our packages are loaded
        $this->getPaths();

        $path = $package->getPath();

        if (($key = array_search($path, $this->paths)) !== false) {
            unset($this->paths[$key]);
            $this->storage->store($this->packages);
        }
    }
}