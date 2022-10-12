<?php

namespace Studio\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\PathRepository;
use Composer\Script\ScriptEvents;
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

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_INSTALL_CMD => 'registerStudioPackages',
            ScriptEvents::PRE_UPDATE_CMD => 'registerStudioPackages',
        ];
    }

    /**
     * Register all managed paths with Composer.
     *
     * This function configures Composer to treat all Studio-managed paths as local path repositories, so that packages
     * therein will be symlinked directly.
     */
    public function registerStudioPackages()
    {
        $repoManager = $this->composer->getRepositoryManager();
        $composerConfig = $this->composer->getConfig();

        foreach ($this->getManagedPaths() as $path) {
            $this->io->writeError("[Studio] Loading path $path");

            // Composer v2 always exposes the internal loop, so keep reusing it
            // that is a fixed requirement since Composer >= 2.3
            if (method_exists($this->composer, 'getLoop')) {
                    $repoManager->prependRepository(new PathRepository(
                        ['url' => $path],
                        $this->io,
                        $composerConfig,
                        $this->composer->getLoop()->getHttpDownloader(),
                        $this->composer->getEventDispatcher(),
                        $this->composer->getLoop()->getProcessExecutor()
                    ));
            } else {
                $repoManager->prependRepository(new PathRepository(
                        ['url' => $path],
                        $this->io,
                        $composerConfig
                    ));
            }
        }
    }

    /**
     * Get the list of paths that are being managed by Studio.
     *
     * @return array
     */
    private function getManagedPaths()
    {
        $targetDir = realpath($this->composer->getPackage()->getTargetDir() ?? '');
        $config = Config::make("{$targetDir}/studio.json");

        return $config->getPaths();
    }
}
