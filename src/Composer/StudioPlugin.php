<?php

namespace Studio\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\PathRepository;
use Composer\Script\Event;
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
     * @var  string|null
     */
    protected $targetDir;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_INSTALL_CMD => 'registerStudioPackages',
            ScriptEvents::PRE_UPDATE_CMD => 'registerStudioPackages',
        ];
    }

    public function registerStudioPackages(Event $event)
    {
        $this->targetDir = realpath($event->getComposer()->getPackage()->getTargetDir());
        $studioFile = "{$this->targetDir}/studio.json";

        $config = $this->getConfig($studioFile);

        $repoManager = $event->getComposer()->getRepositoryManager();
        $io = $event->getIO();

        if ($config->hasPackages()) {
            foreach ($config->getPackages() as $package => $path) {
                $io->writeError("[Studio] Registering package $package with $path");
                $repoManager->addRepository(new PathRepository(
                    ['url' => $path],
                    $event->getIO(),
                    $event->getComposer()->getConfig()
                ));
            }
        }
    }

    /**
     * Instantiate and return the config object.
     *
     * @param string $file
     * @return Config
     */
    protected function getConfig($file)
    {
        return new Config(new FileStorage($file));
    }
}
