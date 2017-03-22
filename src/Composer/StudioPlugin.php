<?php

namespace Studio\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\Package;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Studio\Config\Config;

class StudioPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_UPDATE_CMD => 'unlinkStudioPackages',
            ScriptEvents::PRE_INSTALL_CMD => 'unlinkStudioPackages',
            ScriptEvents::POST_UPDATE_CMD => 'symlinkStudioPackages',
            ScriptEvents::POST_INSTALL_CMD => 'symlinkStudioPackages',
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'symlinkStudioPackages'
        ];
    }

    /**
     * Symlink all managed paths by studio
     *
     * This happens just before the autoload generator kicks in except with --no-autoloader
     * In that case we create the symlinks on the POST_UPDATE, POST_INSTALL events
     *
     */
    public function symlinkStudioPackages()
    {
        foreach ($this->getManagedPaths() as $path) {
            $package = $this->createPackageForPath($path);
            $destination = $this->composer->getInstallationManager()->getInstallPath($package);

            // Creates the symlink to the package
            $filesystem = new Filesystem();
            if (!$filesystem->isSymlinkedDirectory($destination)) {
                $this->io->writeError("[Studio] Creating symlink to $path for package " . $package->getName());

                // Download the package from the path with the composer downloader
                $pathDownloader = $this->composer->getDownloadManager()->getDownloader('path');
                $pathDownloader->download($package, $destination);
            }

        }
    }

    /**
     * Removes all symlinks managed by studio
     *
     */
    public function unlinkStudioPackages()
    {
        foreach ($this->getManagedPaths() as $path) {
            $package = $this->createPackageForPath($path);
            $destination = $this->composer->getInstallationManager()->getInstallPath($package);

            $filesystem = new Filesystem();
            if ($filesystem->isSymlinkedDirectory($destination)) {
                $this->io->writeError("[Studio] Removing symlink $path for package " . $package->getName());
                $filesystem->removeDirectory($destination);
            }
        }
    }

    /**
     * Creates package from given path
     *
     * @param string $path
     * @return Package
     */
    private function createPackageForPath($path)
    {
        $packageName = (new JsonFile(realpath($path) . DIRECTORY_SEPARATOR . 'composer.json'))->read()['name'];
        $package = new Package($packageName, 'dev-master', 'dev-master');
        $package->setDistUrl($path);

        return $package;
    }

    /**
     * Get the list of paths that are being managed by Studio.
     *
     * @return array
     */
    private function getManagedPaths()
    {
        $targetDir = realpath($this->composer->getPackage()->getTargetDir());
        $config = Config::make("{$targetDir}/studio.json");

        return $config->getPaths();
    }
}
