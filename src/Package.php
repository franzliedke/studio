<?php

namespace Studio;

class Package
{

    protected $vendor;

    protected $name;


    public function __construct($vendor, $name)
    {
        $this->vendor = $vendor;
        $this->name = $name;
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

    public function equals(Package $other)
    {
        return $this->vendor == $other->vendor && $this->name == $other->name;
    }

}
