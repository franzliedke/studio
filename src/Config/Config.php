<?php

namespace Studio\Config;

use Studio\Package;

class Config
{
    protected $packages;

    protected $storage;


    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function getPackages()
    {
        return $this->packages;
    }

    public function addPackage(Package $package)
    {
        $this->packages[] = $package;
    }

    public function removePackage(Package $package)
    {
        $this->packages = array_filter($this->packages, function (Package $element) use ($package) {
            return ! $package->equals($element);
        });
    }
}