<?php

namespace Studio\Creator;

use Illuminate\Filesystem\Filesystem;
use Studio\Components\ComponentInterface;
use Studio\Package;
use Studio\Shell\TaskRunner;

class SkeletonCreator implements CreatorInterface
{

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var TaskRunner
     */
    protected $shell;

    protected $directoriesToCreate = [
        '',
        'src',
        'tests',
    ];

    protected $filesToCopy = [
        'phpunit.xml',
        '.travis.yml',
        ['gitignore.txt', '.gitignore'],
    ];

    /**
     * @var ComponentInterface[]
     */
    protected $components;


    public function __construct(Filesystem $files, $path, TaskRunner $shell)
    {
        $this->files = $files;
        $this->path = $path;
        $this->shell = $shell;
    }

    public function component(ComponentInterface $component)
    {
        $this->components[] = $component;
    }

    /**
     * Create the new package.
     *
     * @return \Studio\Package
     */
    public function create()
    {
        $this->createDirectories();
        $this->initPackage();
        $this->copyFiles();

        $this->installComponents();

        return Package::fromFolder($this->path);
    }

    protected function createDirectories()
    {
        foreach ($this->directoriesToCreate as $directory) {
            $path = $this->path . '/' . $directory;
            $this->files->makeDirectory($path, 0777, true);
        }
    }

    protected function initPackage()
    {
        $this->shell->process('composer init', $this->path)
                    ->run();
    }

    protected function copyFiles()
    {
        foreach ($this->filesToCopy as $files) {
            $files = (array) $files;

            $source = $files[0];
            $target = isset($files[1]) ? $files[1] : $source;

            $this->copy($source, $target);
        }
    }

    protected function installComponents()
    {
        $composerFile = $this->path . '/composer.json';
        $config = json_decode(file_get_contents($composerFile));

        foreach ($this->components as $component) {
            $component->configureComposer($config);
        }

        file_put_contents($composerFile, json_encode($config, JSON_PRETTY_PRINT));
    }

    protected function copy($stubFile, $targetFile)
    {
        $path = $this->path;

        $source = $this->getStubPath($stubFile);
        $target = "$path/$targetFile";

        $this->files->copy($source, $target);
    }

    protected function getStubPath($stubFile)
    {
        return __DIR__ . '/../../stubs/' . $stubFile;
    }

}
