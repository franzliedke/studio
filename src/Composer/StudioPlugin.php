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

        $config = Config::make("{$this->targetDir}/studio.json");

        if ($config->hasPackages()) {
            $io = $event->getIO();
            $repoManager = $event->getComposer()->getRepositoryManager();
            $composerConfig = $event->getComposer()->getConfig();

            foreach ($config->getPaths() as $path) {
                $io->writeError("[Studio] Loading path $path");
                $repoManager->prependRepository(new PathRepository(
                    ['url' => $path],
                    $io,
                    $composerConfig
                ));
            }
        }
    }
}
