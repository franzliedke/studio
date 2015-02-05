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
     * @param Package $package
     * @param string $path
     * @return void
     */
    public function create(Package $package, $path)
    {
        $this->createDirectory($path);

        foreach ($this->blocks as $block)
        {
            $this->{"write{$block}"}($package, $path);
        }
    }

    /**
     * Write the support files to the package root.
     *
     * @param Package $package
     * @param string $path
     * @return void
     */
    public function writeSupportFiles(Package $package, $path)
    {
        foreach (array('PhpUnit', 'Travis', 'Composer', 'Ignore') as $file)
        {
            $this->{"write{$file}File"}($package, $path);
        }
    }

    /**
     * Write the PHPUnit stub file.
     *
     * @param Package $package
     * @param string $path
     * @return void
     */
    protected function writePhpUnitFile(Package $package, $path)
    {
        $this->copy($package, $path, 'phpunit.xml');
    }

    /**
     * Write the Travis stub file.
     *
     * @param Package $package
     * @param string $path
     * @return void
     */
    protected function writeTravisFile(Package $package, $path)
    {
        $this->copy($package, $path, '.travis.yml');
    }

    /**
     * Write the Composer.json stub file.
     *
     * @param Package $package
     * @param string $path
     * @return void
     */
    protected function writeComposerFile(Package $package, $path)
    {
        $this->copy($package, $path, 'composer.json');
    }

    /**
     * Write the stub .gitignore file for the package.
     *
     * @param Package $package
     * @param string $path
     * @return void
     */
    public function writeIgnoreFile(Package $package, $path)
    {
        $this->copy($package, $path, 'gitignore.txt', '.gitignore');
    }

    /**
     * Create the test directory for the package.
     *
     * @param Package $package
     * @param string $path
     * @return void
     */
    public function writeTestDirectory(Package $package, $path)
    {
        $this->createDirectory("$path/tests");
        $this->files->put("$path/tests/.gitkeep", '');
    }

    /**
     * Create the main source directory for the package.
     *
     * @param Package $package
     * @param string $path
     * @return void
     */
    protected function writeClassDirectory(Package $package, $path)
    {
        $this->createDirectory("$path/src");
    }

    protected function copy(Package $package, $path, $stubFile, $targetName = null)
    {
        $targetName = $targetName ?: $stubFile;

        $source = $this->getStubPath($stubFile);
        $target = "$path/$targetName";

        $this->files->copy($source, $target);

        $this->replacePlaceholders($target, $package);
    }

    protected function getStubPath($stubFile)
    {
        return __DIR__ . '/../stubs/' . $stubFile;
    }

    /**
     * Create the given directory.
     *
     * @param string $path
     * @return void
     */
    protected function createDirectory($path)
    {
        $this->files->makeDirectory($path, 0777, true);
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
