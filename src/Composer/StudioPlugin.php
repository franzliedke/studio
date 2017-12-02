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

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        // TODO: Before update, append Studio path repositories
        return [
            ScriptEvents::POST_UPDATE_CMD => 'symlinkStudioPackages',
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'loadStudioPackagesForDump',
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
        $intersection = $this->getManagedPackages();

        // Create symlinks for all left-over packages in vendor/composer/studio
        $destination = $this->composer->getConfig()->get('vendor-dir') . '/composer/studio';
        (new Filesystem())->emptyDirectory($destination);
        $studioRepo = new InstalledFilesystemRepository(
            new JsonFile($destination . '/installed.json')
        );

        $installationManager = $this->composer->getInstallationManager();

        // Get local repository which contains all installed packages
        $installed = $this->composer->getRepositoryManager()->getLocalRepository();

        foreach ($intersection as $package) {
            $original = $installed->findPackage($package->getName(), '*');

            $installationManager->getInstaller($original->getType())
                ->uninstall($installed, $original);

            $installationManager->getInstaller($package->getType())
                ->install($studioRepo, $package);
        }

        $studioRepo->write();

        // TODO: Run dump-autoload again
    }

    public function loadStudioPackagesForDump()
    {
        $localRepo = $this->composer->getRepositoryManager()->getLocalRepository();
        $intersection = $this->getManagedPackages();

        $packagesToReplace = [];
        foreach ($intersection as $package) {
            $packagesToReplace[] = $package->getName();
        }

        // Remove all packages with same names as one of symlinked packages
        $packagesToRemove = [];
        foreach ($localRepo->getCanonicalPackages() as $package) {
            if (in_array($package->getName(), $packagesToReplace)) {
                $packagesToRemove[] = $package;
            }
        }
        foreach ($packagesToRemove as $package) {
            $localRepo->removePackage($package);
        }

        // Add symlinked packages to local repository
        foreach ($intersection as $package) {
            $localRepo->addPackage(clone $package);
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
        $intersection = $this->getIntersection(
            $this->composer->getRepositoryManager()->getLocalRepository(),
            $managed
        );

        foreach ($intersection as $package) {
            $this->write('Loading package ' . $package->getUniqueName());
        }

        return $intersection;
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
        $this->io->writeError("[Studio] $msg");
    }
}
