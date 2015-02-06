<?php

namespace Studio\Creator;

use Illuminate\Filesystem\Filesystem;
use Studio\Package;

class SkeletonCreator implements CreatorInterface
{

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Package
     */
    protected $package;

    protected $directoriesToCreate = [
        '',
        'src',
        'tests',
    ];

    protected $filesToCopy = [
        'phpunit.xml',
        '.travis.yml',
        'composer.json',
        ['gitignore.txt', '.gitignore'],
    ];


    public function __construct(Filesystem $files, Package $package)
    {
        $this->files = $files;
        $this->package = $package;
    }

    /**
     * Create the new package.
     *
     * @return \Studio\Package
     */
    public function create()
    {
        $this->createDirectories();
        $this->copyFiles();

        return $this->package;
    }

    protected function createDirectories()
    {
        foreach ($this->directoriesToCreate as $directory) {
            $path = $this->package->getPath() . '/' . $directory;
            $this->files->makeDirectory($path, 0777, true);
        }
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

    protected function copy($stubFile, $targetFile)
    {
        $path = $this->package->getPath();

        $source = $this->getStubPath($stubFile);
        $target = "$path/$targetFile";

        $this->files->copy($source, $target);

        $this->replacePlaceholders($target);
    }

    protected function getStubPath($stubFile)
    {
        return __DIR__ . '/../../stubs/' . $stubFile;
    }

    protected function replacePlaceholders($target)
    {
        $contents = $this->files->get($target);

        $contents = preg_replace_callback(
            '/\{\{([^}]+)\}\}/',
            function ($matches) {
                $method = ucfirst(camel_case($matches[1]));
                $method = "get$method";

                return $this->package->$method();
            },
            $contents
        );

        $this->files->put($target, $contents);
    }

}
