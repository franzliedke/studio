<?php

namespace Studio\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\CompositeRepository;
use Composer\Repository\InstalledFilesystemRepository;
use Composer\Repository\PathRepository;
use Composer\Repository\WritableRepositoryInterface;
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

    /**
     * @var bool
     */
    protected $studioPackagesLoaded = false;

    /**
     * @var array
     */
    protected $studioPackages = [];

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_UPDATE_CMD   => 'symlinkStudioPackages',
            ScriptEvents::POST_INSTALL_CMD  => 'symlinkStudioPackages',
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'loadStudioPackages',
            ScriptEvents::POST_AUTOLOAD_DUMP => 'revertStudioPackages',
        ];
    }

    /**
     * Symlink all Studio-managed packages
     *
     * After `composer update`, we replace all packages that can also be found
     * in paths managed by Studio with symlinks to those paths.
     */
    public function symlinkStudioPackages()
    {
        // Create symlinks for all left-over packages in vendor/composer/studio
        $destination = $this->composer->getConfig()->get('vendor-dir') . '/composer/studio';
        (new Filesystem())->emptyDirectory($destination);
        $studioRepo = new InstalledFilesystemRepository(
            new JsonFile($destination . '/installed.json')
        );

        $installationManager = $this->composer->getInstallationManager();

        // Get local repository which contains all installed packages
        $installed = $this->composer->getRepositoryManager()->getLocalRepository();

        foreach ($this->getManagedPackages() as $package) {
            $original = $installed->findPackage($package->getName(), '*');
            $originalPackage = $original instanceof AliasPackage ? $original->getAliasOf() : $original;

            // Change the source type to path, to prevent 'The package has modified files'
            if ($originalPackage instanceof CompletePackage) {
                $originalPackage->setInstallationSource('dist');
                $originalPackage->setDistType('path');
            }

            $installationManager->getInstaller($original->getType())->uninstall($installed, $original);
            $installationManager->getInstaller($package->getType())->install($LocalPackagesRepo, $package);
        }

        $studioRepo->write();
    }

    /**
     * Swap installed packages with symlinked versions for autoload dump.
     */
    public function loadStudioPackages()
    {
        $this->registerStudioPackages();

        $this->swapPackages();
    }

    /**
     * Revert swapped package versions when autoload dump is complete.
     */
    public function revertStudioPackages()
    {
        $this->swapPackages(true);
    }

    /**
     * If Studio packages have not already been loaded, we need to determine
     * which ones specified in studio.json are installed for this repo.
     */
    public function registerStudioPackages()
    {
        if(!$this->studioPackagesLoaded) {
            $this->studioPackagesLoaded = true;
            
            $localRepo = $this->composer->getRepositoryManager()->getLocalRepository();

            foreach ($this->getManagedPackages() as $package) {
                $this->write('Loading package ' . $package->getName());
                $this->studioPackages[$package->getName()] = [
                    'studio' => $package
                ];
            }

            foreach ($localRepo->getCanonicalPackages() as $package) {
                if (isset($this->studioPackages[$package->getName()])) {
                    $this->studioPackages[$package->getName()]['original'] = $package;
                }
            }
        }
    }

    /**
     * Remove original packages from local repository manager and replace with
     * studio/symlinked packages.
     *
     * @param bool $revert Revert flag will undo this change.
     */
    protected function swapPackages($revert = false)
    {
        $localRepo = $this->composer->getRepositoryManager()->getLocalRepository();

        foreach ($this->studioPackages as $package) {
            $localRepo->removePackage($package[$revert ? 'studio' : 'original']);
            $localRepo->addPackage(clone $package[$revert ? 'original' : 'studio']);
        }
    }

    /**
     * @param WritableRepositoryInterface $installedRepo
     * @param PathRepository[] $managedRepos
     * @return PackageInterface[]
     */
    private function getIntersection(WritableRepositoryInterface $installedRepo, $managedRepos)
    {
        $managedRepo = new CompositeRepository($managedRepos);

        return array_filter(
            array_map(
                function (PackageInterface $package) use ($managedRepo) {
                    return $managedRepo->findPackage($package->getName(), '*');
                },
                $installedRepo->getCanonicalPackages()
            )
        );
    }

    private function getManagedPackages()
    {
        $composerConfig = $this->composer->getConfig();

        // Get array of PathRepository instances for Studio-managed paths
        $managed = [];
        foreach ($this->getManagedPaths() as $path) {
            $managed[] = new PathRepository(
                ['url' => $path],
                $this->io,
                $composerConfig
            );
        }

        // Intersect PathRepository packages with local repository
        return $this->getIntersection(
            $this->composer->getRepositoryManager()->getLocalRepository(),
            $managed
        );
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

    private function write($msg)
    {
        $this->io->write("[Studio] $msg");
    }
}
