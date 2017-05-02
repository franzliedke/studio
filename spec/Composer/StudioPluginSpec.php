<?php

namespace spec\Studio\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\ScriptEvents;
use PhpSpec\ObjectBehavior;

class StudioPluginSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Studio\Composer\StudioPlugin');
    }

    function it_is_activatable(Composer $composer, IOInterface $io)
    {
        $this->activate($composer, $io);
    }

    function it_resolves_subscribed_events()
    {
        self::getSubscribedEvents()->shouldReturn([
            ScriptEvents::PRE_UPDATE_CMD => 'unlinkStudioPackages',
            ScriptEvents::POST_UPDATE_CMD => 'symlinkStudioPackages',
            ScriptEvents::POST_INSTALL_CMD => 'symlinkStudioPackages',
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'symlinkStudioPackages'
        ]);
    }

    /**
     * Test if studio does not create symlinks when no studio.json is defined
     */
    function it_doesnt_create_symlinks_without_file($composer, $io, $rootPackage, $filesystem)
    {
        // switch working directory
        chdir(__DIR__);

        // create stubs
        $filesystem->beADoubleOf('Composer\Util\Filesystem');
        $rootPackage->beADoubleOf('Composer\Downloader\PathDownloader');
        $composer->beADoubleOf('Composer\Composer');
        $io->beADoubleOf('Composer\IO\IOInterface');

        // Construct
        $this->beConstructedWith($filesystem);

        // Mock methods
        $composer->getInstallationManager()->willReturn(null);
        $composer->getDownloadManager()->willReturn(null);
        $composer->getPackage()->willReturn($rootPackage);
        $rootPackage->getTargetDir()->willReturn(getcwd());

        // Test
        $this->activate($composer, $io);
        $this->symlinkStudioPackages();
    }

    /**
     * Test if studio does not unlink when no studio.json or .studio/studio.json is defined
     */
    function it_doesnt_unlink_without_files($composer, $io, $rootPackage, $filesystem)
    {
        // switch working directory
        chdir(__DIR__);

        // create stubs
        $filesystem->beADoubleOf('Composer\Util\Filesystem');
        $rootPackage->beADoubleOf('Composer\Downloader\PathDownloader');
        $composer->beADoubleOf('Composer\Composer');
        $io->beADoubleOf('Composer\IO\IOInterface');

        // Construct
        $this->beConstructedWith($filesystem);

        // Mock methods
        $composer->getInstallationManager()->willReturn(null);
        $composer->getDownloadManager()->willReturn(null);
        $composer->getPackage()->willReturn($rootPackage);
        $rootPackage->getTargetDir()->willReturn(getcwd());

        // Test
        $this->activate($composer, $io);
        $this->unlinkStudioPackages();
    }

    /**
     * Test if studio does create symlinks when studio.json is defined
     */
    function it_does_create_symlinks_with_file(
        $composer,
        $io,
        $rootPackage,
        $filesystem,
        $installationManager,
        $downloadManager,
        $pathDownloader
    ) {
        // switch working directory
        chdir(__DIR__ . '/stubs/project-with-path');

        // create stubs
        $filesystem->beADoubleOf('Composer\Util\Filesystem');
        $rootPackage->beADoubleOf('Composer\Downloader\PathDownloader');
        $composer->beADoubleOf('Composer\Composer');
        $io->beADoubleOf('Composer\IO\IOInterface');
        $installationManager->beADoubleOf('Composer\Installer\InstallationManager');
        $downloadManager->beADoubleOf('Composer\Downloader\DownloadManager');
        $pathDownloader->beADoubleOf('Composer\Downloader\PathDownloader');

        // Construct
        //$this->beConstructedWith($filesystem);

        // Mock methods
        $composer->getInstallationManager()->willReturn($installationManager);
        $composer->getDownloadManager()->willReturn($downloadManager);
        $composer->getPackage()->willReturn($rootPackage);
        $rootPackage->getTargetDir()->willReturn(getcwd());
        $downloadManager->getDownloader('path')
            ->willReturn($pathDownloader)
            ->shouldBeCalled();

        $io->write('[Studio] Creating link to library for package acme/library')->shouldBeCalled();

        // Test
        $this->activate($composer, $io);
        $this->symlinkStudioPackages();
    }


    /**
     * Test if studio does unlink when studio.json is defined
     */
    function it_does_unlink_with_file(
        $composer,
        $io,
        $rootPackage,
        $filesystem,
        $installationManager,
        $pathDownloader
    ) {
        // switch working directory
        chdir(__DIR__ . '/stubs/project-with-unload');

        // create stubs
        $filesystem->beADoubleOf('Composer\Util\Filesystem');
        $rootPackage->beADoubleOf('Composer\Downloader\PathDownloader');
        $composer->beADoubleOf('Composer\Composer');
        $io->beADoubleOf('Composer\IO\IOInterface');
        $installationManager->beADoubleOf('Composer\Installer\InstallationManager');
        $pathDownloader->beADoubleOf('Composer\Downloader\PathDownloader');

        // Construct
        $this->beConstructedWith($filesystem);

        // Mock methods
        $composer->getInstallationManager()->willReturn($installationManager);
        $composer->getDownloadManager()->willReturn(null);
        $composer->getPackage()->willReturn($rootPackage);
        $rootPackage->getTargetDir()->willReturn(getcwd());
        $filesystem->isSymlinkedDirectory(null)->willReturn(true)->shouldBeCalled();
        $filesystem->removeDirectory(null)->shouldBeCalled();

        $io->write('[Studio] Removing linked path library for package acme/library')->shouldBeCalled();

        // Test
        $this->activate($composer, $io);
        $this->unlinkStudioPackages();
    }
}
