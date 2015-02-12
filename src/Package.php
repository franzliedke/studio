<?php

namespace Studio;

class Package
{

    protected $vendor;

    protected $name;

    protected $path;


    public function __construct($vendor, $name, $path)
    {
        $this->vendor = $vendor;
        $this->name = $name;
        $this->path = $path;
    }

    public function getComposerId()
    {
        return $this->vendor . '/' . $this->name;
    }

    public function getVendor()
    {
        return $this->vendor;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->path;
    }

}
