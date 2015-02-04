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
        if ($this->runningInGlobalHomeDir($event)) return;

        $path = $event->getComposer()->getPackage()->getTargetDir();
        $studioFile = "$path/studio.json";

        $config = $this->getConfig($studioFile);

        if ($config->hasPackages()) {
            $packages = $config->getPackages();

            // TODO: Add autoloading rules!
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

    protected function runningInGlobalHomeDir(Event $event)
    {
        $current = getcwd();
        $home = $event->getComposer()->getConfig()->get('home');

        return $current == $home;
    }
}
