<?php

namespace Studio\Config;

use Studio\Package;

class Config
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    protected $packages;

    protected $loaded = false;


    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function getPackages()
    {
        if (! $this->loaded) {
            $this->packages = $this->storage->load();
            $this->loaded = true;
        }

        return $this->packages;
    }

    public function addPackage(Package $package)
    {
        // Ensure our packages are loaded
        $this->getPackages();

        $this->packages[$package->getComposerId()] = $package->getPath();
        $this->storage->store($this->packages);
    }

    public function hasPackages()
    {
        // Ensure our packages are loaded
        $this->getPackages();

        return ! empty($this->packages);
    }

    public function removePackage(Package $package)
    {
        // Ensure our packages are loaded
        $this->getPackages();

        $key = $package->getComposerId();

        if (isset($this->packages[$key])) {
            unset($this->packages[$key]);
            $this->storage->store($this->packages);
        }
    }
}