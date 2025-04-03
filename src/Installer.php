<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Installer extends LibraryInstaller
{
    public function getInstallPath(PackageInterface $package)
    {
        $themeName = $this->getThemeName($package);
        return "Themes/{$themeName}";
    }

    public function supports($packageType)
    {
        return $packageType === 'focus-theme';
    }

    protected function getThemeName(PackageInterface $package)
    {
        $packageName = $package->getPrettyName();
        $packageName = str_replace('istvan/', '', $packageName);

        // Átalakítás PSR-4 kompatibilis névre
        $themeName = str_replace('-', ' ', $packageName); // Kötőjelek helyett szóköz
        $themeName = ucwords($themeName); // Minden szó első betűje nagybetű
        $themeName = str_replace(' ', '', $themeName); // Szóközök eltávolítása

        return $themeName;
    }

    public static function postPackageInstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        if ($package->getType() === 'focus-theme') {
            $themeName = (new self($event->getIO(), $event->getComposer()))->getThemeName($package);
            self::executeArtisanCommand($event->getIO(), "theme:setup {$themeName}");
        }
    }

    public static function postPackageUninstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        if ($package->getType() === 'focus-theme') {
            $themeName = (new self($event->getIO(), $event->getComposer()))->getThemeName($package);
            self::executeArtisanCommand($event->getIO(), "theme:remove {$themeName}");
        }
    }

    protected static function executeArtisanCommand(IOInterface $io, $command)
    {
        $cwd = getcwd();
        $artisanPath = $cwd . '/artisan';

        if (file_exists($artisanPath)) {
            $io->write("Executing: php artisan {$command}");
            system("php {$artisanPath} {$command}", $returnCode);

            if ($returnCode !== 0) {
                $io->writeError("Error executing artisan command: {$command}");
            }
        } else {
            $io->writeError("Artisan file not found at: {$artisanPath}");
        }
    }
}