<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Installer\PackageEvent;
use Symfony\Component\Process\Process;

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

    public static function postPackageInstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        if ($package->getType() === 'focus-theme') {
            $installer = new self($event->getIO(), $event->getComposer());
            $themeName = $installer->getThemeName($package);
            self::executeArtisanCommand($event->getIO(), "theme:setup {$themeName}");
        }
    }

    public static function postPackageUninstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        if ($package->getType() === 'focus-theme') {
            $installer = new self($event->getIO(), $event->getComposer());
            $themeName = $installer->getThemeName($package);
            self::executeArtisanCommand($event->getIO(), "theme:remove {$themeName}", true);
        }
    }

    protected function getThemeName(PackageInterface $package)
    {
        $packageName = $package->getPrettyName();
        $packageName = str_replace('istvan/', '', $packageName);

        $themeName = str_replace('-', ' ', $packageName);
        $themeName = ucwords($themeName);
        return str_replace(' ', '', $themeName);
    }

    protected static function executeArtisanCommand(IOInterface $io, $command, $ignoreErrors = false)
    {
        $cwd = getcwd();
        $artisanPath = $cwd . '/artisan';

        if (!file_exists($artisanPath)) {
            $io->writeError("<error>Artisan fájl nem található: {$artisanPath}</error>");
            return false;
        }

        $process = new Process(['php', $artisanPath, ...explode(' ', $command)]);
        $process->setTimeout(300);
        $process->setWorkingDirectory($cwd);

        try {
            $io->write("<comment>Végrehajtás: php artisan {$command}</comment>");
            $process->mustRun(function ($type, $buffer) use ($io) {
                $io->write($buffer);
            });
            return true;
        } catch (\Exception $e) {
            if (!$ignoreErrors) {
                $io->writeError("<error>Hiba: {$e->getMessage()}</error>");
            }
            return false;
        }
    }
}