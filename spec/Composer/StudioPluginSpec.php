<?php

namespace spec\Studio\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Repository\ArrayRepository;
use Composer\Repository\RepositoryManager;
use Composer\Repository\WritableArrayRepository;
use Composer\Script\ScriptEvents;
use PhpSpec\ObjectBehavior;

class StudioPluginSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Studio\Composer\StudioPlugin');
    }

    function it_is_activatable(Composer $composer, IOInterface $io, RepositoryManager $repositoryManager)
    {
        // Mock methods
        $composer->getInstallationManager()->willReturn(null);
        $composer->getDownloadManager()->willReturn(null);
        $composer->getPackage()->willReturn(null);
        $composer->getRepositoryManager()->willReturn($repositoryManager);

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
    function it_doesnt_create_symlinks_without_file($composer, $io, $rootPackage, $filesystem, $repositoryManager)
    {
        // switch working directory
        chdir(__DIR__);

        // create stubs
        $filesystem->beADoubleOf('Composer\Util\Filesystem');
        $rootPackage->beADoubleOf('Composer\Package\RootPackage');
        $composer->beADoubleOf('Composer\Composer');
        $io->beADoubleOf('Composer\IO\IOInterface');
        $repositoryManager->beADoubleOf('Composer\Repository\RepositoryManager');

        // Construct
        $this->beConstructedWith($filesystem);

        // Mock methods
        $composer->getInstallationManager()->willReturn(null);
        $composer->getDownloadManager()->willReturn(null);
        $composer->getPackage()->willReturn($rootPackage);
        $composer->getRepositoryManager()->willReturn($repositoryManager);
        $rootPackage->getTargetDir()->willReturn(getcwd());

        // Test
        $this->activate($composer, $io);
        $this->symlinkStudioPackages();
    }

    /**
     * Test if studio does not unlink when no studio.json or .studio/studio.json is defined
     */
    function it_doesnt_unlink_without_files($composer, $io, $rootPackage, $filesystem, $repositoryManager)
    {
        // switch working directory
        chdir(__DIR__);

        // create stubs
        $filesystem->beADoubleOf('Composer\Util\Filesystem');
        $rootPackage->beADoubleOf('Composer\Package\RootPackage');
        $composer->beADoubleOf('Composer\Composer');
        $io->beADoubleOf('Composer\IO\IOInterface');
        $repositoryManager->beADoubleOf('Composer\Repository\RepositoryManager');

        // Construct
        $this->beConstructedWith($filesystem);

        // Mock methods
        $composer->getInstallationManager()->willReturn(null);
        $composer->getDownloadManager()->willReturn(null);
        $composer->getPackage()->willReturn($rootPackage);
        $composer->getRepositoryManager()->willReturn($repositoryManager);
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
        $pathDownloader,
        $repositoryManager,
        $localRepository,
        $libraryPackage,
        $library2Package
    ) {
        // switch working directory
        chdir(__DIR__ . '/stubs/project-with-path');

        // create stubs
        $filesystem->beADoubleOf('Composer\Util\Filesystem');
        $rootPackage->beADoubleOf('Composer\Package\RootPackage');
        $composer->beADoubleOf('Composer\Composer');
        $io->beADoubleOf('Composer\IO\IOInterface');
        $installationManager->beADoubleOf('Composer\Installer\InstallationManager');
        $downloadManager->beADoubleOf('Composer\Downloader\DownloadManager');
        $pathDownloader->beADoubleOf('Composer\Downloader\PathDownloader');
        $repositoryManager->beADoubleOf('Composer\Repository\RepositoryManager');
        $localRepository->beADoubleOf('Composer\Repository\WritableRepositoryInterface');
        $libraryPackage->beADoubleOf('Composer\Package\Package');
        $library2Package->beADoubleOf('Composer\Package\Package');

        $libraryPackage->getName()->willReturn("acme/library");
        $library2Package->getName()->willReturn("acme/library2");

        $localRepository->getPackages()->willReturn([$libraryPackage, $library2Package]);
        $repositoryManager->getLocalRepository()->willReturn($localRepository);

        // Construct
        //$this->beConstructedWith($filesystem);

        // Mock methods
        $composer->getInstallationManager()->willReturn($installationManager);
        $composer->getDownloadManager()->willReturn($downloadManager);
        $composer->getPackage()->willReturn($rootPackage);
        $composer->getRepositoryManager()->willReturn($repositoryManager);
        $rootPackage->getTargetDir()->willReturn(getcwd());
        $downloadManager->getDownloader('path')
            ->willReturn($pathDownloader)
            ->shouldBeCalled();

        $io->write('[Studio] Creating link to ../libs/library for package acme/library')->shouldBeCalled();
        $io->write('[Studio] Creating link to ../libs/library2 for package acme/library2')->shouldNotBeCalled();
        $io->write('[Studio] Creating link to ../libs/another-library for package acme/another-library')->shouldNotBeCalled();

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
        $pathDownloader,
        $repositoryManager
    ) {
        // switch working directory
        chdir(__DIR__ . '/stubs/project-with-unload');

        // create stubs
        $filesystem->beADoubleOf('Composer\Util\Filesystem');
        $rootPackage->beADoubleOf('Composer\Package\RootPackage');
        $composer->beADoubleOf('Composer\Composer');
        $io->beADoubleOf('Composer\IO\IOInterface');
        $installationManager->beADoubleOf('Composer\Installer\InstallationManager');
        $pathDownloader->beADoubleOf('Composer\Downloader\PathDownloader');
        $repositoryManager->beADoubleOf('Composer\Repository\RepositoryManager');

        // Construct
        $this->beConstructedWith($filesystem);

        // Mock methods
        $composer->getInstallationManager()->willReturn($installationManager);
        $composer->getDownloadManager()->willReturn(null);
        $composer->getPackage()->willReturn($rootPackage);
        $composer->getRepositoryManager()->willReturn($repositoryManager);
        $rootPackage->getTargetDir()->willReturn(getcwd());
        $filesystem->isSymlinkedDirectory(null)->willReturn(true)->shouldBeCalled();
        $filesystem->removeDirectory(null)->shouldBeCalled();

        $io->write('[Studio] Removing linked path ../libs/library for package acme/library')->shouldBeCalled();
        $io->write('[Studio] Removing linked path ../libs/library2 for package acme/library2')->shouldNotBeCalled();
        $io->write('[Studio] Removing linked path ../libs/another-library for package acme/another-library')->shouldNotBeCalled();

        // Test
        $this->activate($composer, $io);
        $this->unlinkStudioPackages();
    }

    /**
     * Test if studio does create symlinks when studio.json is defined and contains wildcards path
     */
    function it_does_create_symlinks_with_file_and_wildcard_paths(
        $composer,
        $io,
        $rootPackage,
        $filesystem,
        $installationManager,
        $downloadManager,
        $pathDownloader,
        $repositoryManager,
        $localRepository,
        $libraryPackage,
        $library2Package
    ) {
        // switch working directory
        chdir(__DIR__ . '/stubs/project-with-path-wildcard');

        // create stubs
        $filesystem->beADoubleOf('Composer\Util\Filesystem');
        $rootPackage->beADoubleOf('Composer\Package\RootPackage');
        $composer->beADoubleOf('Composer\Composer');
        $io->beADoubleOf('Composer\IO\IOInterface');
        $installationManager->beADoubleOf('Composer\Installer\InstallationManager');
        $downloadManager->beADoubleOf('Composer\Downloader\DownloadManager');
        $pathDownloader->beADoubleOf('Composer\Downloader\PathDownloader');
        $repositoryManager->beADoubleOf('Composer\Repository\RepositoryManager');
        $localRepository->beADoubleOf('Composer\Repository\WritableRepositoryInterface');
        $libraryPackage->beADoubleOf('Composer\Package\Package');
        $library2Package->beADoubleOf('Composer\Package\Package');

        $libraryPackage->getName()->willReturn("acme/library");
        $library2Package->getName()->willReturn("acme/library2");

        $localRepository->getPackages()->willReturn([$libraryPackage, $library2Package]);
        $repositoryManager->getLocalRepository()->willReturn($localRepository);

        $repositoryManager->getLocalRepository()->willReturn($localRepository);

        // Construct
        //$this->beConstructedWith($filesystem);

        // Mock methods
        $composer->getInstallationManager()->willReturn($installationManager);
        $composer->getDownloadManager()->willReturn($downloadManager);
        $composer->getPackage()->willReturn($rootPackage);
        $composer->getRepositoryManager()->willReturn($repositoryManager);
        $rootPackage->getTargetDir()->willReturn(getcwd());
        $downloadManager->getDownloader('path')
            ->willReturn($pathDownloader)
            ->shouldBeCalled();

        $io->write('[Studio] Creating link to ../libs/library for package acme/library')->shouldBeCalled();
        $io->write('[Studio] Creating link to ../libs/library2 for package acme/library2')->shouldBeCalled();
        $io->write('[Studio] Creating link to ../libs/another-library for package acme/another-library')->shouldNotBeCalled();

        // Test
        $this->activate($composer, $io);
        $this->symlinkStudioPackages();
    }


    /**
     * Test if studio does unlink when studio.json is defined and contains wildcard paths.
     */
    function it_does_unlink_with_file_and_wildcard_paths(
        $composer,
        $io,
        $rootPackage,
        $filesystem,
        $installationManager,
        $pathDownloader,
        $repositoryManager
    ) {
        // switch working directory
        chdir(__DIR__ . '/stubs/project-with-unload-wildcard');

        // create stubs
        $filesystem->beADoubleOf('Composer\Util\Filesystem');
        $rootPackage->beADoubleOf('Composer\Package\RootPackage');
        $composer->beADoubleOf('Composer\Composer');
        $io->beADoubleOf('Composer\IO\IOInterface');
        $installationManager->beADoubleOf('Composer\Installer\InstallationManager');
        $pathDownloader->beADoubleOf('Composer\Downloader\PathDownloader');
        $repositoryManager->beADoubleOf('Composer\Repository\RepositoryManager');

        // Construct
        $this->beConstructedWith($filesystem);

        // Mock methods
        $composer->getInstallationManager()->willReturn($installationManager);
        $composer->getDownloadManager()->willReturn(null);
        $composer->getPackage()->willReturn($rootPackage);
        $composer->getRepositoryManager()->willReturn($repositoryManager);
        $rootPackage->getTargetDir()->willReturn(getcwd());
        $filesystem->isSymlinkedDirectory(null)->willReturn(true)->shouldBeCalled();
        $filesystem->removeDirectory(null)->shouldBeCalled();

        $io->write('[Studio] Removing linked path ../libs/library for package acme/library')->shouldBeCalled();
        $io->write('[Studio] Removing linked path ../libs/library2 for package acme/library2')->shouldBeCalled();
        $io->write('[Studio] Removing linked path ../libs/another-library for package acme/another-library')->shouldBeCalled();

        // Test
        $this->activate($composer, $io);
        $this->unlinkStudioPackages();
    }
}
