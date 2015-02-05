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
use Symfony\Component\Finder\Finder;

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
            $this->autoloadFrom($config->getPackages());
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

    protected function autoloadFrom(array $directories)
    {
        $finder = new Finder();

        // Find all Composer autoloader files in the supervised packages' directories
        // so that we can include and setup all of their dependencies.
        $autoloaders = $finder->in($directories)
                              ->files()
                              ->name('autoload.php')
                              ->depth('<= 3')
                              ->followLinks();

        foreach ($autoloaders as $file) {
            // TODO: Include $file from our vendor/autoload.php
        }
    }
}
