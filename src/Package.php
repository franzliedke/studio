<?php

namespace Studio;

class Package
{

    protected $vendor;

    protected $name;

    protected $author;

    protected $email;

    protected $path;


    public function __construct($vendor, $name, $author, $email, $path)
    {
        $this->vendor = $vendor;
        $this->name = $name;
        $this->author = $author;
        $this->email = $email;
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

    public function getAuthor()
    {
        return $this->author;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function equals(Package $other)
    {
        return $this->getComposerId() == $other->getComposerId();
    }

}
