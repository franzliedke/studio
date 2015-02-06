<?php

namespace Studio\Creator;

class GitRepoCreator implements CreatorInterface
{

    protected $repo;

    protected $path;


    public function __construct($repo, $path)
    {
        $this->repo = $repo;
        $this->path = $path;
    }

    /**
     * Create the new package.
     *
     * @return \Studio\Package
     */
    public function create()
    {
        // TODO: Clone repository into path.
    }

}
