<?php

namespace Studio\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Studio\Config\Config;
use Studio\Config\FileStorage;

class AutoloadPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        // ...
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'dumpAutoload',
        ];
    }

    public function dumpAutoload(Event $event)
    {
        if ($this->hasStudioPackages($event->getComposer())) {
            // TODO: Add autoloading rules
        }
    }

    /**
     * Determine whether the current package has any Studio packages to keep track of.
     *
     * @param Composer $composer
     * @return bool
     */
    protected function hasStudioPackages(Composer $composer)
    {
        $path = $composer->getPackage()->getTargetDir();
        $studioFile = "$path/studio.json";

        return $this->getConfig($studioFile)->hasPackages();
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
