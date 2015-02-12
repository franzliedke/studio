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

        return $this->makePackage();
    }

    protected function cloneRepository()
    {
        $task = "git clone $this->repo $this->path";
        $this->shell->run($task);
    }

    protected function makePackage()
    {
        $composer = json_decode(file_get_contents($this->path . '/composer.json'));

        list($vendor, $name) = explode('/', $composer->name, 2);
        $description = $composer->description;

        return new Package(
            $vendor,
            $name,
            $description,
            $composer->authors[0]->name,
            $composer->authors[0]->email,
            $this->path
        );
    }

}
