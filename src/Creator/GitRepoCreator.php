<?php

namespace Studio\Creator;

use Studio\Package;
use Studio\Shell\TaskRunner;

class GitRepoCreator implements CreatorInterface
{

    protected $repo;

    protected $path;

    /**
     * @var TaskRunner
     */
    protected $shell;


    public function __construct($repo, $path, TaskRunner $shell)
    {
        $this->repo = $repo;
        $this->path = $path;
        $this->shell = $shell;
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
        $task = "git clone $this->repo $this->path";
        $this->shell->run($task);
    }

}
