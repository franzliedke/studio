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
        $this->packages[] = $package;
        $this->storage->store($this->packages);
    }

    public function removePackage(Package $package)
    {
        $this->packages = array_filter($this->packages, function (Package $element) use ($package) {
            return ! $package->equals($element);
        });
        $this->storage->store($this->packages);
    }
}