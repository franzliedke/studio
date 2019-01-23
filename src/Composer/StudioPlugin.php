<?php

namespace Studio\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreCommandRunEvent;
use Composer\Repository\PathRepository;
use Composer\Script\ScriptEvents;
use Studio\Config\Config;
use Studio\Config\FileStorage;

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
     * @var array
     */
    const PLUGGED_COMMANDS = [
        'create-project',
        'install',
        'update',
        'require'
    ] ;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::PRE_COMMAND_RUN => 'registerStudioPackages',
        ];
    }

    /**
     * Register all managed paths with Composer.
     *
     * This function configures Composer to treat all Studio-managed paths as local path repositories, so that packages
     * therein will be symlinked directly.
     */
    public function registerStudioPackages(PreCommandRunEvent $event)
    {
        if (! in_array($event->getCommand(), static::PLUGGED_COMMANDS)) {
            return;
        }

        $repoManager = $this->composer->getRepositoryManager();
        $composerConfig = $this->composer->getConfig();

        foreach ($this->getManagedPaths() as $path) {
            $this->io->writeError("[Studio] Loading path $path");

            $repoManager->prependRepository(new PathRepository(
                ['url' => $path],
                $this->io,
                $composerConfig
            ));
        }
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
