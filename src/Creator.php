<?php

namespace Studio;

use Illuminate\Filesystem\Filesystem;

class Creator
{

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * The building blocks of the package.
     *
     * @param  array
     */
    protected $blocks = array(
        'SupportFiles',
        'ClassDirectory',
        'TestDirectory',
    );


    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Create a new package stub.
     *
     * @param  Package  $package
     * @return string
     */
    public function create(Package $package)
    {
        $directory = $this->createDirectory($package);

        foreach ($this->blocks as $block)
        {
            $this->{"write{$block}"}($package);
        }

        return $directory;
    }

    /**
     * Write the support files to the package root.
     *
     * @param  Package  $package
     * @return void
     */
    public function writeSupportFiles(Package $package)
    {
        foreach (array('PhpUnit', 'Travis', 'Composer', 'Ignore') as $file)
        {
            $this->{"write{$file}File"}($package);
        }
    }

    /**
     * Write the PHPUnit stub file.
     *
     * @param  Package  $package
     * @return void
     */
    protected function writePhpUnitFile(Package $package)
    {
        $this->copy($package, 'phpunit.xml');
    }

    /**
     * Write the Travis stub file.
     *
     * @param  Package  $package
     * @return void
     */
    protected function writeTravisFile(Package $package)
    {
        $this->copy($package, '.travis.yml');
    }

    /**
     * Write the Composer.json stub file.
     *
     * @param  Package  $package
     * @return void
     */
    protected function writeComposerFile(Package $package)
    {
        $this->copy($package, 'composer.json');
    }

    /**
     * Write the stub .gitignore file for the package.
     *
     * @param  Package  $package
     * @return void
     */
    public function writeIgnoreFile(Package $package)
    {
        $this->copy($package, 'gitignore.txt', '.gitignore');
    }

    /**
     * Create the test directory for the package.
     *
     * @param  Package  $package
     * @return void
     */
    public function writeTestDirectory(Package $package)
    {
        $testDirectory = $this->getTargetPath($package, 'tests');
        $this->files->makeDirectory($testDirectory);

        $targetPath = $this->getTargetPath($package, 'tests/.gitkeep');
        $this->files->put($targetPath, '');
    }

    /**
     * Create the main source directory for the package.
     *
     * @param  Package  $package
     * @return string
     */
    protected function writeClassDirectory(Package $package)
    {
        $path = $this->getPackageDirectory($package) . '/src';

        if ( ! $this->files->isDirectory($path))
        {
            $this->files->makeDirectory($path, 0777, true);
        }

        return $path;
    }

    protected function copy(Package $package, $stubFile, $targetName = null)
    {
        $targetName = $targetName ?: $stubFile;

        $source = $this->getStubPath($stubFile);
        $target = $this->getTargetPath($package, $targetName);

        $this->files->copy($source, $target);

        $this->replacePlaceholders($target, $package);
    }

    protected function getStub($stubFile)
    {
        return $this->files->get($this->getStubPath($stubFile));
    }

    protected function getStubPath($stubFile)
    {
        return __DIR__ . '/../stubs/' . $stubFile;
    }

    protected function getTargetPath(Package $package, $targetFile)
    {
        return $this->getPackageDirectory($package) . '/' . $targetFile;
    }

    protected function getPackageDirectory(Package $package)
    {
        return __DIR__ . '/../' . $package->getVendor() . '/' . $package->getName();
    }

    /**
     * Create a workbench directory for the package.
     *
     * @param  Package  $package
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function createDirectory(Package $package)
    {
        $fullPath = $this->getPackageDirectory($package);

        if ( ! $this->files->isDirectory($fullPath))
        {
            $this->files->makeDirectory($fullPath, 0777, true);

            return $fullPath;
        }

        throw new \InvalidArgumentException("Package exists.");
    }

    protected function replacePlaceholders($target, Package $package)
    {
        $contents = $this->files->get($target);

        $contents = preg_replace_callback(
            '/\{\{([^}]+)\}\}/',
            function ($matches) use ($package) {
                $method = ucfirst(camel_case($matches[1]));
                $method = "get$method";

                return $package->$method();
            },
            $contents
        );

        $this->files->put($target, $contents);
    }

}
