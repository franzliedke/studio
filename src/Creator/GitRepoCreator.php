<?php

namespace Studio\Creator;

use Studio\Package;
use Studio\Shell\Shell;

class GitRepoCreator implements CreatorInterface
{
    protected $repo;

    protected $path;

    protected $options;


    public function __construct($repo, $path, $options = '')
    {
        $this->repo = $repo;
        $this->path = $path;
        $this->options = $options;
    }

    /**
     * Create the new package.
     *
     * @return \Studio\Package
     */
    public function create()
    {
        $this->cloneRepository();

        return Package::fromFolder($this->path);
    }

    protected function cloneRepository()
    {
        Shell::run("git clone $this->options $this->repo $this->path");
    }
}
