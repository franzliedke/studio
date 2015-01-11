<?php

namespace Studio\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;

class AutoloadPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        // ...
    }

    public static function getSubscribedEvents()
    {
        return array(
            'pre-autoload-dump' => 'dumpAutoload',
        );
    }

    public function dumpAutoload(Event $event)
    {
        // For all registered packages, gather their autoload rules...
        // and merge them with the ones in vendor
    }
}
