<?php

namespace Studio;

class Package
{

    protected $vendor;

    protected $name;

    protected $author;

    protected $email;


    public function __construct($vendor, $name, $author, $email)
    {
        $this->vendor = $vendor;
        $this->name = $name;
        $this->author = $author;
        $this->email = $email;
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

    public function getAuthor()
    {
        return $this->author;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function equals(Package $other)
    {
        return $this->vendor == $other->vendor && $this->name == $other->name;
    }

}
