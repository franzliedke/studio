<?php

namespace Studio\Composer;

use Composer\Composer;
use Composer\Downloader\DownloadManager;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Package;
use Composer\Package\RootPackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\WritableRepositoryInterface;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Studio\Config\Config;

/**
 * Class StudioPlugin
 *
 * @package Studio\Composer
 */
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

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DownloadManager
     */
    protected $downloadManager;

    /**
     * @var InstallationManager
     */
    protected $installationManager;

    /**
     * @var RootPackageInterface
     */
    protected $rootPackage;

    /**
     * @var WritableRepositoryInterface
     */
    private $localRepository;

    /**
     * StudioPlugin constructor.
     *
     * @param Filesystem|null $filesystem
     */
    public function __construct(Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->installationManager = $composer->getInstallationManager();
        $this->downloadManager = $composer->getDownloadManager();
        $this->rootPackage = $composer->getPackage();
        $this->localRepository = $composer->getRepositoryManager()->getLocalRepository();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_UPDATE_CMD => 'unlinkStudioPackages',
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
        $studioDir = realpath($this->rootPackage->getTargetDir()) . DIRECTORY_SEPARATOR . '.studio';
        foreach ($this->getManagedPaths() as $path) {
            $resolvedPaths = $this->resolvePath($path);

            foreach ($resolvedPaths as $resolvedPath) {
                $package = $this->createPackageForPath($resolvedPath);
                if (!$package || !$this->isLocalRepositoryPackage($package)) {
                    continue;
                }

                $destination = $this->installationManager->getInstallPath($package);

                // Creates the symlink to the package
                if (!$this->filesystem->isSymlinkedDirectory($destination) &&
                    !$this->filesystem->isJunction($destination)
                ) {
                    $this->io->write("[Studio] Creating link to $resolvedPath for package " . $package->getName());

                    // Create copy of original in the `.studio` directory,
                    // we use the original on the next `composer update`
                    if (is_dir($destination)) {
                        $copyPath = $studioDir . DIRECTORY_SEPARATOR . $package->getName();
                        $this->filesystem->ensureDirectoryExists($copyPath);
                        $this->filesystem->copyThenRemove($destination, $copyPath);
                    }

                    // Download the managed package from its path with the composer downloader
                    $pathDownloader = $this->downloadManager->getDownloader('path');
                    $pathDownloader->download($package, $destination);
                }
            }

        }

        // ensure the `.studio` directory only if we manage paths.
        // without this check studio will create the `.studio` directory
        // in all projects where composer is used
        if (count($this->getManagedPaths())) {
            $this->filesystem->ensureDirectoryExists('.studio');
        }

        // if we have managed paths or did have we copy the current studio.json
        if (count($this->getManagedPaths()) > 0 ||
            count($this->getPreviouslyManagedPaths()) > 0
        ) {
            // If we have the current studio.json copy it to the .studio directory
            $studioFile = realpath($this->rootPackage->getTargetDir()) . DIRECTORY_SEPARATOR . 'studio.json';
            if (file_exists($studioFile)) {
                copy($studioFile, $studioDir . DIRECTORY_SEPARATOR . 'studio.json');
            }
        }
    }

    /**
     * Removes all symlinks managed by studio
     *
     */
    public function unlinkStudioPackages()
    {
        $studioDir = realpath($this->rootPackage->getTargetDir()) . DIRECTORY_SEPARATOR  . '.studio';
        $paths = array_merge($this->getPreviouslyManagedPaths(), $this->getManagedPaths());

        foreach ($paths as $path) {
            $resolvedPaths = $this->resolvePath($path);

            foreach ($resolvedPaths as $resolvedPath) {
                $package = $this->createPackageForPath($resolvedPath);
                if ($package == null) {
                    continue;
                }

                $destination = $this->installationManager->getInstallPath($package);

                if ($this->filesystem->isSymlinkedDirectory($destination) ||
                    $this->filesystem->isJunction($destination)
                ) {
                    $this->io->write("[Studio] Removing linked path $resolvedPath for package " . $package->getName());
                    $this->filesystem->removeDirectory($destination);

                    // If we have an original copy move it back
                    $copyPath = $studioDir . DIRECTORY_SEPARATOR . $package->getName();
                    if (is_dir($copyPath)) {
                        $this->filesystem->copyThenRemove($copyPath, $destination);
                    }
                }
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
       $composerJson = $path . DIRECTORY_SEPARATOR . 'composer.json';

       if (is_readable($composerJson)) {
           $json = (new JsonFile($composerJson))->read();
           $json['version'] = 'dev-master';

           // branch alias won't work, otherwise the ArrayLoader::load won't return an instance of CompletePackage
           unset($json['extra']['branch-alias']);

           $loader = new ArrayLoader();
           $package = $loader->load($json);
           $package->setDistUrl($path);

           return $package;
       }

       return NULL;
    }


    /**
     * Resolve path with glob to an array of existing paths.
     *
     * @param string $path
     * @return string[]
     */
    private function resolvePath($path)
    {
        /** @var string[] $paths */
        $paths = [];

        $realPaths = glob($path);
        foreach ($realPaths as $realPath) {
            if (!in_array($realPath, $paths)) {
                $paths[] = $realPath;
            }
        }

        return $paths;
    }

    /**
     * Check if this package is a dependency (transitive or not) of the root package.
     *
     * @param Package $package
     * @return bool
     */
    private function isLocalRepositoryPackage($package) {
        foreach ($this->localRepository->getPackages() as $localRepositoryPackage) {
            if ($localRepositoryPackage->getName() == $package->getName()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the list of paths that are being managed by Studio.
     *
     * @return array
     */
    private function getManagedPaths()
    {
        $targetDir = realpath($this->rootPackage->getTargetDir());
        $config = Config::make($targetDir . DIRECTORY_SEPARATOR  . 'studio.json');

        return $config->getPaths();
    }

    /**
     * Get last known managed paths by studio
     *
     * @return array
     */
    private function getPreviouslyManagedPaths()
    {
        $targetDir = realpath($this->rootPackage->getTargetDir()) . DIRECTORY_SEPARATOR . '.studio';
        $config = Config::make($targetDir . DIRECTORY_SEPARATOR  . 'studio.json');

        return $config->getPaths();
    }
}
