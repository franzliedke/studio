<?php

namespace Studio\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
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

    public function registerStudioPackages()
    {
        $this->targetDir = realpath($this->composer->getPackage()->getTargetDir());

        $config = Config::make("{$this->targetDir}/studio.json");

        if ($config->hasPackages()) {
            $repoManager = $this->composer->getRepositoryManager();
            $composerConfig = $this->composer->getConfig();

            foreach ($config->getPaths() as $path) {
                $this->io->writeError("[Studio] Loading path $path");
                $repoManager->prependRepository(new PathRepository(
                    ['url' => $path],
                    $this->io,
                    $composerConfig
                ));
            }
        }
    }
}
