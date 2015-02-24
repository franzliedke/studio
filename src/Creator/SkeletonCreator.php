<?php

namespace Studio\Creator;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Studio\Parts\PartInterface;
use Studio\Package;

class SkeletonCreator implements CreatorInterface
{

    /**
     * @var string
     */
    protected $path;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var PartInterface[]
     */
    protected $parts;


    public function __construct($path)
    {
        $this->path = $path;
        $this->filesystem = new Filesystem(new Local($path));
    }

    public function addPart(PartInterface $part)
    {
        $this->parts[] = $part;
    }

    /**
     * Create the new package.
     *
     * @return \Studio\Package
     */
    public function create()
    {
        $this->installParts();

        return Package::fromFolder($this->path);
    }

    protected function installParts()
    {
        $config = new \stdClass();

        foreach ($this->parts as $part) {
            $part->setupPackage($config, $this->filesystem);
        }

        $this->filesystem->write(
            'composer.json',
            json_encode(
                $config,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            )
        );
    }

}
